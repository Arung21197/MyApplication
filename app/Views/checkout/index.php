<h2>Checkout</h2>
<?php if ($order): ?>
    <p><strong>Product ID:</strong> <?= $order['product_id'] ?></p>
    <p><strong>Quantity:</strong> <?= $order['quantity'] ?></p>
    <p><strong>Total Price:</strong> <?= $order['total_price'] ?></p>
    <p><strong>Status:</strong> <?= $order['status'] ?></p>
<?php else: ?>
    <p>No order found. Please make sure you have placed an order.</p>
<?php endif; ?>

<!-- Form to show the order ID, which will be used for the AJAX request -->
<form id="checkout-form">
    <label for="order_id">Order ID:</label>
    <input type="number" name="order_id" id="order_id" value="<?= $order['id']; ?>" readonly>
    
    <!-- This is the button that triggers the AJAX request -->
    <button type="button" id="proceed-to-pay">Proceed to Payment</button>
</form>

<!-- Div to show the PayTabs iframe after payment initiation -->
<div id="payment-form" style="display:none;">
    <iframe id="paytabs-iframe" src="" style="width:100%; height:500px;"></iframe>
</div>

<!-- Include jQuery for AJAX -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script>
    // Trigger the AJAX request when the "Proceed to Payment" button is clicked
    $('#proceed-to-pay').on('click', function() {
        var order_id = $('#order_id').val(); // Get the order ID value

        // Send an AJAX POST request to the controller to get the PayTabs iframe URL
        $.ajax({
            url: '/checkout/getPayTabsIframeUrl', // The URL to the controller method
            method: 'POST',
            data: { order_id: order_id },
            success: function(response) {
                // If the response status is success, load the iframe
                if (response.status === 'success') {
                  window.location.href = response.payment_url;
                    //$('#paytabs-iframe').attr('src', 'https://secure-egypt.paytabs.com/payment/page/5DB8CF3E82E488EB48DC1DA7C3293EB73589EF018E4883B3693755BF/start');
                   // $('#paytabs-iframe').attr('src', response.payment_url); // Set iframe URL
                    $('#payment-form').show(); // Show the payment form (iframe)
                } else {
                    // Display the error message
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred, please try again.');
            }
        });
    });
</script>
