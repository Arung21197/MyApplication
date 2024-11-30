
<?php if (isset($data['respCode'])): ?>
    <h2>Payment failed with response code: <?= $data['respCode'] ?></h2>
<?php else: ?>
    <h2>Payment result not available.</h2>
<?php endif; ?>