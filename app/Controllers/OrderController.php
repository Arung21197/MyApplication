<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class OrderController extends Controller
{
    public function index()
    {
        // Get all orders
        $orderModel = new \App\Models\OrderModel();
        $data['orders'] = $orderModel->findAll();
        return view('orders/index', $data);
    }

    public function createOrder()
    {
        // Get all products for the create order page
        $productModel = new \App\Models\ProductModel();
        $data['products'] = $productModel->findAll();

        return view('orders/create_order', $data);
    }

    public function storeOrder()
    {
        // Get POST data
        $productId = $this->request->getPost('product_id');
        $quantity = $this->request->getPost('quantity');
        // Validate input
        if (!$productId || !$quantity || $quantity <= 0) {
        return redirect()->back()->with('error', 'Invalid input!');
         }
        // Log productId and quantity after they're defined
        log_message('error', 'Product ID: ' . $productId);
        log_message('error', 'Quantity: ' . $quantity);

        // Proceed with order creation
        $orderModel = new \App\Models\OrderModel();
        $productModel = new \App\Models\ProductModel();

        // Retrieve product data
        $product = $productModel->find($productId);

        // Check if product exists
        if (!$product) {
            log_message('error', 'Product not found!');
            return redirect()->back()->with('error', 'Product not found!');
        }

        // Calculate total price
        $totalPrice = $product['price'] * $quantity;
        // Log the order data before saving
    log_message('error', 'Order Data to Save: ' . print_r([
        'product_id' => $productId,
        'quantity' => $quantity,
        'total_price' => $totalPrice,
        'status' => 'pending'
    ], true));

    // Save the order and log success or failure
    try {
        $isSaved = $orderModel->save([
            'product_id' => $productId,
            'quantity' => $quantity,
            'total_price' => $totalPrice,
            'status' => 'pending'
        ]);

        if ($isSaved) {
            log_message('error', 'Order saved successfully!');
        } else {
            log_message('error', 'Failed to save the order.');
        }
    } catch (\Exception $e) {
        // Log any exception if something goes wrong
        log_message('error', 'Error saving order: ' . $e->getMessage());
        return redirect()->back()->with('error', 'An error occurred while placing the order.');
    }
        // Redirect to the checkout page
        return redirect()->to('/checkout')->with('success', 'Order created successfully!');;
    }
}
