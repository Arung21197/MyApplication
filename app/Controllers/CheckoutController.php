<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class CheckoutController extends \CodeIgniter\Controller {
    public function index() {
        // Assuming you want to display the last placed order or all orders
        $orderModel = new \App\Models\OrderModel();
       
        // You can get the latest order or all orders; depending on your need
        // Here, we assume you want to show the last order
        $order = $orderModel->orderBy('created_at', 'desc')->first();

        // Pass the order data to the view
        return view('checkout/index', ['order' => $order]);
    }

    public function proceedToPay() {
        $orderId = $this->request->getPost('order_id');

         // Log order ID to check if it's being passed correctly
        log_message('error', 'Received Order ID: ' . $orderId);
      // Retrieve the order from the database
        $orderModel = new \App\Models\OrderModel();
        $order = $orderModel->find($orderId);
        
        if (!$order) {
            // Log an error or notify the user if the order does not exist
            log_message('error', 'Order not found! Order ID: ' . $orderId);
            return redirect()->back()->with('error', 'Order not found!');
        }
        // Call PayTabs API to initiate payment

        $paymentData = $this->payWithPayTabs($order);

        log_message('info', 'PayTabs API Response: ' . json_encode($paymentData));

        if ($paymentData['status'] === 'success' && isset($paymentData['payment_url']) && !empty($paymentData['payment_url'])) {
            // Redirect user to PayTabs payment page
            //log_message('info', 'PayTabs Redirect URL: ' . $responseData['redirect_url']);
            log_message('info', 'Redirecting to PayTabs: ' . $paymentData['payment_url']);
            return redirect()->to($paymentData['payment_url']);
        } else {
            // Handle error
            log_message('error', 'Payment initiation failed: ' . json_encode($paymentData));
            return redirect()->back()->with('error', 'Payment initiation failed!');
        }
        // Here, you'd include the PayTabs API integration code (refer to their documentation).
        $paymentModel = new \App\Models\PaymentModel();
        $paymentResponse = $this->payWithPayTabs($order);
        
        $paymentModel->save([
            'order_id' => $orderId,
            'payment_status' => $paymentResponse['status'],
            'payment_response' => json_encode($paymentResponse)
        ]);
        
        return view('/checkout/payment_result', ['response' => $paymentResponse]);
    }

    private function payWithPayTabs($order) {
        // Implement PayTabs payment integration here
        // Use the PayTabs API for hosted payment page (iFrame mode)
        // Reference the PayTabs documentation to implement this
        // PayTabs API credentials
        $profileId = '132344';
        $serverKey = 'SWJ992BZTN-JHGTJBWDLM-BZJKMR2ZHT';

        $paymentRequest = [
            'profile_id' => 132344,
            'tran_type' => 'sale', // Transaction type
            'tran_class' => 'ecom', // E-commerce transaction
            'cart_id' =>  $order['id'], // Unique cart ID
            'cart_description' => $order['id'],
            'cart_currency' => 'EGP', // Egyptian Pound
            'cart_amount' => $order['total_price'], // Total order amount
            'callback' => base_url('/checkout/paymentCallback'), // Callback URL for asynchronous updates
            'return' => base_url('/checkout/paymentResult'), // Return URL for user redirection
            'customer_details' => [
                'name' => 'John Doe', // Replace with actual customer data
                'email' => 'johndoe@example.com',
                'phone' => '0123456789',
                'country' => 'EG'
            ]
        ];

        // $paymentRequest = [
        //     'profile_id' => '132344',
        //     'tran_type' => 'sale',
        //     'tran_class' => 'ecom',
        //     'cart_id' => '140',
        //     'cart_description' => 'Test Order',
        //     'cart_currency' => 'EGP',
        //     'cart_amount' => 100,
        //     'callback' => 'https://yourdomain.com/callback',
        //     'return' => 'https://yourdomain.com/return',
        //     'customer_details' => [
        //         'name' => 'Test User',
        //         'email' => 'test@example.com',
        //         'phone' => '01000000000',
        //         'country' => 'EG',
        //     ],
        // ];

        // PayTabs API Endpoint for Egypt
      //  $apiUrl = 'https://secure-egypt.paytabs.com';
      $apiUrl ='https://secure-egypt.paytabs.com/payment/request';
        // Send request to PayTabs
        $client = \Config\Services::curlrequest([
            'base_uri' => 'https://secure-egypt.paytabs.com',
            'verify' => false, // Disable SSL verification for testing
        ]);

        try {
            $response = $client->post($apiUrl, [
                'headers' => [
                    'Authorization' => $serverKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $paymentRequest,
            ]);
        
            $responseData = json_decode($response->getBody(), true);
        
           // log_message('info', 'PayTabs API Request: ' . json_encode($paymentRequest));
            log_message('info', 'PayTabs API Response: ' . $response->getBody());

            log_message('info', 'PayTabs API Response: ' . json_encode($responseData));
            
            if (isset($responseData['redirect_url']) && isset($responseData['tran_ref'])) {
                            return [
                    'status' => 'success',
                    'payment_url' => $responseData['redirect_url'],
                ];
            } else {
                log_message('error', 'PayTabs API Error: ' . json_encode($responseData));
                return [
                    'status' => 'error',
                    'message' => $responseData['message'] ?? 'Payment request failed',
                ];
            }
        } catch (\Exception $e) {
            log_message('error', 'PayTabs Exception: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'An error occurred while processing the payment request.',
            ];
        }
        
    }
    
    /**
     * Callback Method for PayTabs asynchronous updates
     */
    public function paymentCallback() {
        $paymentData = $this->request->getPost();
    
        if (!empty($paymentData)) {
            log_message('error', 'Payment Callback Data: ' . json_encode($paymentData));
    
            if (isset($paymentData['cart_id'], $paymentData['respStatus'])) {
                $dataToSave = [
                    'order_id' => $paymentData['cart_id'],
                    'payment_status' => $paymentData['respStatus'],
                    'payment_response' => json_encode($paymentData),
                ];
    
                $paymentModel = new \App\Models\PaymentModel();
    
                if (!$paymentModel->insert($dataToSave)) {
                    log_message('error', 'Failed to save payment data: ' . json_encode($paymentModel->errors()));
                } else {
                    log_message('info', 'Payment status saved for order: ' . $paymentData['cart_id']);
                }
            } else {
                log_message('error', 'Missing essential data in callback: ' . json_encode($paymentData));
            }
        } else {
            log_message('error', 'No POST data received in callback.');
        }
    
        return $this->response->setJSON(['status' => 'success']);
    }
    

    /**
     * Return Method for user redirection after payment
     */
    public function paymentResult() {
        $cartId = $this->request->getGet('cart_id');
    
        if (!$cartId) {
            log_message('error', 'Missing cart_id in return URL.');
            return view('checkout/failure', ['error' => 'Transaction ID missing.']);
        }
    
        $paymentModel = new \App\Models\PaymentModel();
        $payment = $paymentModel->where('order_id', $cartId)->first();
    
        if (!$payment) {
            log_message('error', 'No payment record found for order: ' . $cartId);
            return view('checkout/failure', ['error' => 'Payment record not found.']);
        }
    
        if ($payment['payment_status'] === '100') {
            return view('checkout/success', ['data' => $payment]);
        } else {
            return view('checkout/failure', ['data' => $payment]);
        }
    }
    
    
    
    public function getPayTabsIframeUrl() {
        // Check if the order_id is passed
        $orderId = $this->request->getPost('order_id');
        if (!$orderId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Order ID is missing.'
            ]);
        }
    
        // Try to retrieve the order from the database
        $orderModel = new \App\Models\OrderModel();
        $order = $orderModel->find($orderId);
    
        if (!$order) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Order not found.'
            ]);
        }
    
        // Now, try to call the PayTabs API
        try {
            $paymentData = $this->payWithPayTabs($order);
    
            // Check the response from the PayTabs API
            log_message('error', 'PayTabs API Response: ' . json_encode($paymentData)); // Log the full response
    
            if ($paymentData['status'] === 'success') {
                return $this->response->setJSON([
                    'status' => 'success',
                    'payment_url' => $paymentData['payment_url']
                ]);
            } else {
                // Log the error from the PayTabs API
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $paymentData['message'] ?? 'Payment request failed.'
                ]);
            }
        } catch (\Exception $e) {
            // Log the exception message
            log_message('error', 'PayTabs API Exception: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'An error occurred while processing the payment request.'
            ]);
        }
    }    
}
