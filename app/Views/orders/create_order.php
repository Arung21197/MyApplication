<h2>Create New Order</h2>

<form method="POST" action="<?= site_url('orders/storeOrder'); ?>">
    <label for="product_id">Product:</label>
    <select name="product_id" id="product_id" required>
        <option value="" disabled selected>Select a product</option>
        <?php foreach ($products as $product): ?>
            <option value="<?= $product['id']; ?>">
                <?= htmlspecialchars($product['name']); ?> - <?= htmlspecialchars($product['price']); ?> USD
            </option>
        <?php endforeach; ?>
    </select>

    <label for="quantity">Quantity:</label>
    <input type="number" name="quantity" id="quantity" min="1" required>

    <button type="submit">Place Order</button>
</form>

