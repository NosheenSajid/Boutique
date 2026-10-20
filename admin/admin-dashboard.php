<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/admin-header.php';

requireAdminLogin();

$admin = $_SESSION['admin'] ?? null;

$productCount = (int) $pdo->query(
    'SELECT COUNT(*) FROM products'
)->fetchColumn();

$orderCount = (int) $pdo->query(
    'SELECT COUNT(*) FROM orders'
)->fetchColumn();

$customerCount = (int) $pdo->query(
    'SELECT COUNT(*) FROM users'
)->fetchColumn();

$revenue = (float) $pdo->query(
    "SELECT COALESCE(SUM(total), 0)
     FROM orders
     WHERE status <> 'cancelled'"
)->fetchColumn();

$statusRows = $pdo->query(
    'SELECT status, COUNT(*) AS total
     FROM orders
     GROUP BY status
     ORDER BY total DESC'
)->fetchAll();

$recentOrders = $pdo->query(
    'SELECT id, order_number, customer_name, total, status, created_at
     FROM orders
     ORDER BY created_at DESC
     LIMIT 6'
)->fetchAll();

$salesRows = $pdo->query(
    "SELECT DATE(created_at) AS sale_date,
            COALESCE(SUM(total), 0) AS total
     FROM orders
     WHERE status <> 'cancelled'
       AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
     GROUP BY DATE(created_at)
     ORDER BY sale_date ASC"
)->fetchAll();

$salesByDate = [];

foreach ($salesRows as $row) {
    $salesByDate[(string) $row['sale_date']] = (float) $row['total'];
}

$maxSales = max(1, $salesByDate ? max($salesByDate) : 0);

function dashboardEscape($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin-dashboard.css">

<section class="admin-dashboard">

    <!-- Dashboard header -->
    <div class="dashboard-header">
        <div>
            <p class="dashboard-label">Admin Dashboard</p>

            <h1>
                Welcome back,
                <?= dashboardEscape($admin['name'] ?? 'Admin') ?>
            </h1>
        </div>
    </div>

    <!-- Statistics -->
    <div class="dashboard-stats">

        <div class="dashboard-card">
            <p class="stat-title">Products</p>
            <p class="stat-value">
                <?= number_format($productCount) ?>
            </p>
        </div>

        <div class="dashboard-card">
            <p class="stat-title">Orders</p>
            <p class="stat-value">
                <?= number_format($orderCount) ?>
            </p>
        </div>

        <div class="dashboard-card">
            <p class="stat-title">Customers</p>
            <p class="stat-value">
                <?= number_format($customerCount) ?>
            </p>
        </div>

        <div class="dashboard-card">
            <p class="stat-title">Net Sales</p>
            <p class="stat-value revenue">
                <?= formatMoney($revenue) ?>
            </p>
        </div>

    </div>

    <!-- Sales chart and order status -->
    <div class="dashboard-panels">

        <!-- Sales chart -->
        <div class="dashboard-panel">

            <div class="panel-heading">
                <div>
                    <h2>Sales, Last 7 Days</h2>

                    <p class="panel-description">
                        Revenue from non-cancelled orders.
                    </p>
                </div>

                <a
                    href="<?= dashboardEscape(BASE_URL) ?>admin/admin-orders.php"
                    class="dashboard-text-link"
                >
                    View Orders
                </a>
            </div>

            <div class="sales-chart">

                <?php for ($dayOffset = 6; $dayOffset >= 0; $dayOffset--): ?>

                    <?php
                    $date = (new DateTimeImmutable(
                        "-{$dayOffset} days"
                    ))->format('Y-m-d');

                    $amount = $salesByDate[$date] ?? 0;

                    $height = $amount > 0
                        ? max(8, (int) round(($amount / $maxSales) * 100))
                        : 3;

                    $barDate = date('D', strtotime($date));
                    ?>

                    <div class="sales-day">

                        <span class="sales-amount">
                            <?= $amount > 0
                                ? number_format($amount / 1000, 1) . 'k'
                                : '0' ?>
                        </span>

                        <div class="sales-bar-area">
                            <div
                                class="sales-bar"
                                style="height: <?= $height ?>%;"
                                title="<?= dashboardEscape($date) ?>: <?= dashboardEscape(formatMoney($amount)) ?>"
                            ></div>
                        </div>

                        <span class="sales-day-label">
                            <?= dashboardEscape($barDate) ?>
                        </span>

                    </div>

                <?php endfor; ?>

            </div>

        </div>

        <!-- Order status -->
        <div class="dashboard-panel">

            <h2>Order Status</h2>

            <div class="status-list">

                <?php foreach ($statusRows as $statusRow): ?>

                    <?php
                    $statusTotal = (int) $statusRow['total'];

                    $statusWidth = $orderCount > 0
                        ? min(
                            100,
                            max(
                                4,
                                (int) round(
                                    ($statusTotal / $orderCount) * 100
                                )
                            )
                        )
                        : 0;
                    ?>

                    <div class="status-item">

                        <div class="status-info">

                            <span class="status-name">
                                <?= dashboardEscape($statusRow['status']) ?>
                            </span>

                            <strong class="status-count">
                                <?= number_format($statusTotal) ?>
                            </strong>

                        </div>

                        <div class="status-track">
                            <div
                                class="status-progress"
                                style="width: <?= $statusWidth ?>%;"
                            ></div>
                        </div>

                    </div>

                <?php endforeach; ?>

                <?php if (!$statusRows): ?>
                    <p class="panel-description">No orders yet.</p>
                <?php endif; ?>

            </div>

        </div>

    </div>

    <!-- Recent orders -->
    <div class="dashboard-panel recent-orders">

        <div class="panel-heading">

            <h2>Recent Orders</h2>

            <a
                href="<?= dashboardEscape(BASE_URL) ?>admin/admin-products.php"
                class="dashboard-btn dashboard-btn-dark"
            >
                Manage Products
            </a>

        </div>

        <div class="orders-table-wrapper">

            <table class="orders-table">

                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($recentOrders as $order): ?>

                        <tr>

                            <td class="order-number">
                                #<?= dashboardEscape($order['order_number']) ?>
                            </td>

                            <td>
                                <?= dashboardEscape($order['customer_name']) ?>
                            </td>

                            <td class="order-total">
                                <?= formatMoney($order['total']) ?>
                            </td>

                            <td>
                                <span class="order-status">
                                    <?= dashboardEscape($order['status']) ?>
                                </span>
                            </td>

                            <td>
                                <?= dashboardEscape(
                                    date(
                                        'M j, Y',
                                        strtotime((string) $order['created_at'])
                                    )
                                ) ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                    <?php if (!$recentOrders): ?>

                        <tr>
                            <td colspan="5" class="empty-orders">
                                No orders yet.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</section>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>