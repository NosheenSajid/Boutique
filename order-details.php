<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$order = null;

if ($orderId) {
    $stmt = $pdo->prepare('SELECT id, order_number, created_at, status, subtotal, shipping_cost, total FROM orders WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $orderId]);
    $order = $stmt->fetch() ?: null;
}

if (!$order) {
    flash('error', 'Order not found.');
    redirect('orders.php');
}

$itemsStmt = $pdo->prepare('SELECT oi.product_name AS name, oi.product_price AS price, oi.quantity AS qty, p.main_image FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = :order_id ORDER BY oi.id ASC');
$itemsStmt->execute(['order_id' => $orderId]);
$orderItems = $itemsStmt->fetchAll();

$subtotal = (float) $order['subtotal'];
$total = (float) $order['total'];
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/order-details.css">

<section class="order-details-section">
    <div class="order-details-header">
        <div>
            <p class="order-details-label">Order details</p>
            <h1 class="order-details-title">
                Order #<?= htmlspecialchars((string) $order['order_number']) ?>
            </h1>
        </div>

        <span class="order-status">
            <?= htmlspecialchars((string) $order['status']) ?>
        </span>
    </div>

    <div class="order-details-grid">
        <div class="order-items">
            <?php foreach ($orderItems as $item): ?>
                <div class="order-item">
                    <?php if (!empty($item['main_image'])): ?>
                        <img
                            src="<?= htmlspecialchars((string) $item['main_image']) ?>"
                            alt="<?= htmlspecialchars((string) $item['name']) ?>"
                            class="order-item-image"
                        >
                    <?php else: ?>
                        <div class="order-item-no-image">
                            No image
                        </div>
                    <?php endif; ?>

                    <div class="order-item-content">
                        <div>
                            <h2 class="order-item-name">
                                <?= htmlspecialchars($item['name']) ?>
                            </h2>

                            <p class="order-item-quantity">
                                Qty: <?= htmlspecialchars((string) $item['qty']) ?>
                            </p>
                        </div>

                        <p class="order-item-price">
                            PKR <?= number_format((int) ($item['qty'] * $item['price'])) ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <aside class="order-summary">
            <h2 class="order-summary-title">
                Summary
            </h2>

            <div class="order-summary-details">
                <div class="order-summary-row">
                    <span>Order date</span>
                    <span>
                        <?= htmlspecialchars(date('M j, Y', strtotime((string) $order['created_at']))) ?>
                    </span>
                </div>

                <div class="order-summary-row">
                    <span>Subtotal</span>
                    <span>
                        PKR <?= number_format((int) $subtotal) ?>
                    </span>
                </div>

                <div class="order-summary-row">
                    <span>Shipping</span>
                    <span>
                        PKR <?= number_format((float) $order['shipping_cost'], 2) ?>
                    </span>
                </div>

                <div class="order-summary-row">
                    <span>Tax</span>
                    <span>PKR 0</span>
                </div>
            </div>

            <div class="order-summary-total">
                <div class="order-total-row">
                    <span>Total</span>
                    <span>
                        PKR <?= number_format((int) $total) ?>
                    </span>
                </div>
            </div>
        </aside>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>