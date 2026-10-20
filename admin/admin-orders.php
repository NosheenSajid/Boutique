<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/admin-header.php';

requireAdminLogin();

$allowedStatuses = [
    'pending',
    'confirmed',
    'processing',
    'shipped',
    'delivered',
    'cancelled'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrf((string) ($_POST['csrf_token'] ?? ''))) {
        flash('error', 'Security token expired. Please try again.');
    } else {

        $orderId = (int) ($_POST['order_id'] ?? 0);
        $status = (string) ($_POST['status'] ?? '');

        if ($orderId > 0 && in_array($status, $allowedStatuses, true)) {

            $stmt = $pdo->prepare(
                'UPDATE orders
                 SET status = :status, updated_at = NOW()
                 WHERE id = :id'
            );

            $stmt->execute([
                'status' => $status,
                'id' => $orderId
            ]);

            flash('success', 'Order status updated.');
        }
    }

    redirect('admin/admin-orders.php');
}

$orders = $pdo->query(
    'SELECT
        o.id,
        o.order_number,
        o.customer_name,
        o.customer_email,
        o.total,
        o.status,
        o.created_at,
        GROUP_CONCAT(
            CONCAT(
                COALESCE(p.name, "Product"),
                "|||",
                COALESCE(p.main_image, ""),
                "|||",
                COALESCE(oi.quantity, 1)
            )
            SEPARATOR "~~~"
        ) AS order_products
     FROM orders o
     LEFT JOIN order_items oi ON oi.order_id = o.id
     LEFT JOIN products p ON p.id = oi.product_id
     GROUP BY
        o.id,
        o.order_number,
        o.customer_name,
        o.customer_email,
        o.total,
        o.status,
        o.created_at
     ORDER BY o.created_at DESC'
)->fetchAll();

$error = flash('error');
$success = flash('success');

?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin-orders.css"/>
<section class="orders-page">

    <div class="orders-header">

        <div>
            <h1>Orders</h1>
            <p>Review customers and manage order fulfillment.</p>
        </div>

    </div>

    <?php if ($error): ?>
        <div class="orders-message orders-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="orders-message orders-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="orders-card">
        <div class="orders-table-wrapper">

            <table class="orders-table">

                <thead>
                    <tr>
                        <th>Products</th>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Placed</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($orders as $order): ?>

                        <tr>
                            <td>
    <div class="order-products">
        <?php
        $orderProducts = [];

        if (!empty($order['order_products'])) {
            foreach (explode('~~~', $order['order_products']) as $item) {
                $parts = explode('|||', $item);

                if (count($parts) === 3) {
                    $orderProducts[] = [
                        'name' => $parts[0],
                        'image' => $parts[1],
                        'quantity' => (int) $parts[2],
                    ];
                }
            }
        }
        ?>

        <?php if ($orderProducts): ?>
            <?php foreach ($orderProducts as $item): ?>
                <div class="order-product-item">
                    <?php if ($item['image'] !== ''): ?>
                        <img
                            src="<?= htmlspecialchars(
                                $item['image'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            alt="<?= htmlspecialchars(
                                $item['name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            class="order-product-image"
                            loading="lazy"
                        >
                    <?php else: ?>
                        <div class="order-product-placeholder">
                            No image
                        </div>
                    <?php endif; ?>

                    <div class="order-product-details">
                        <span class="order-product-name">
                            <?= htmlspecialchars(
                                $item['name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </span>

                        <span class="order-product-quantity">
                            Qty: <?= $item['quantity'] ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <span class="order-email">No product details</span>
        <?php endif; ?>
    </div>

                            </td>

                            <td>
                                <div class="order-number">
                                    #<?= htmlspecialchars(
                                        (string) $order['order_number']
                                    ) ?>
                                </div>

                                <div class="order-email">
                                    <?= htmlspecialchars(
                                        (string) $order['customer_email']
                                    ) ?>
                                </div>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    (string) $order['customer_name']
                                ) ?>
                            </td>

                          
                            <td>
                                <?= formatMoney($order['total']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    date(
                                        'M j, Y',
                                        strtotime(
                                            (string) $order['created_at']
                                        )
                                    )
                                ) ?>
                            </td>

                            <td>
                                <form
                                    method="post"
                                    class="order-status-form"
                                >
                                    <?= csrfField() ?>

                                    <input
                                        type="hidden"
                                        name="order_id"
                                        value="<?= (int) $order['id'] ?>"
                                    >

                                    <select
                                        name="status"
                                        class="order-status"
                                        aria-label="Order status"
                                        data-original-status="<?= htmlspecialchars(
                                            (string) $order['status'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        required
                                    >

                                        <?php foreach ($allowedStatuses as $status): ?>

                                            <option
                                                value="<?= htmlspecialchars($status) ?>"
                                                <?= $status === $order['status']
                                                    ? 'selected'
                                                    : '' ?>
                                            >
                                                <?= htmlspecialchars(
                                                    ucfirst($status)
                                                ) ?>
                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                    <button
                                        type="submit"
                                        class="order-save-btn"
                                        hidden
                                    >
                                        Save
                                    </button>

                                    <noscript>
                                        <button
                                            type="submit"
                                            class="order-save-btn"
                                        >
                                            Save
                                        </button>
                                    </noscript>

                                </form>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                    <?php if (!$orders): ?>

                        <tr>
                            <td colspan="5" class="orders-empty">
                                No orders have been placed yet.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>
    </div>

</section>

<script src="<?= BASE_URL ?>assets/javascript/admin-orders.js"></script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>