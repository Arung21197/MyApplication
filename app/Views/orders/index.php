
<h2>My Orders</h2>
<ul>
    <?php foreach($orders as $order): ?>
        <li>Order ID: <?= $order['id']; ?> - Status: <?= $order['status']; ?></li>
    <?php endforeach; ?>
</ul>
<a href="<?= site_url('orders/create'); ?>">Create New Order</a>
