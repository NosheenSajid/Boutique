<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

$cartItems = getCartItems();
$orderSummary = [];
$subtotal = 0;

foreach ($cartItems as $item) {
    $itemPrice = (float) ($item['sale_price'] ?? $item['price']);
    $itemQuantity = (int) $item['quantity'];

    $orderSummary[] = [
        'product_id' => (int) $item['product_id'],
        'name' => (string) $item['name'],
        'qty' => $itemQuantity,
        'price' => $itemPrice,
    ];

    $subtotal += $itemPrice * $itemQuantity;
}

$shipping = $subtotal > 0 ? 500 : 0;
$total = $subtotal + $shipping;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['customer_name'] ?? ''));
    $email = trim((string) ($_POST['customer_email'] ?? ''));
    $phone = trim((string) ($_POST['customer_phone'] ?? ''));
    $address = trim((string) ($_POST['shipping_address'] ?? ''));
    $city = trim((string) ($_POST['shipping_city'] ?? ''));
    $postalCode = trim((string) ($_POST['shipping_postal_code'] ?? ''));
    $paymentMethod = trim((string) ($_POST['payment_method'] ?? 'cod'));

    if (!verifyCsrf((string) ($_POST['csrf_token'] ?? ''))) {
        flash('error', 'Security token expired. Please try again.');
    } elseif (!$orderSummary) {
        flash('error', 'Your cart is empty. Add a product before placing an order.');
    } elseif (
        $name === '' ||
        $phone === '' ||
        $address === '' ||
        $city === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        flash('error', 'Please complete all required checkout fields with a valid email address.');
    } elseif (!in_array($paymentMethod, ['cod', 'card', 'bank_transfer'], true)) {
        flash('error', 'Please choose a valid payment method.');
    } else {
        try {
            $pdo->beginTransaction();

            $orderNumber = 'ORD-' . date('YmdHis') . '-' . random_int(100, 999);

            $userId = isLoggedIn()
                ? (int) $_SESSION['user']['id']
                : null;

            $insertOrder = $pdo->prepare(
                'INSERT INTO orders (
                    user_id,
                    order_number,
                    customer_name,
                    customer_email,
                    customer_phone,
                    shipping_address,
                    shipping_city,
                    shipping_postal_code,
                    subtotal,
                    shipping_cost,
                    total,
                    payment_method,
                    status
                ) VALUES (
                    :user_id,
                    :order_number,
                    :customer_name,
                    :customer_email,
                    :customer_phone,
                    :shipping_address,
                    :shipping_city,
                    :shipping_postal_code,
                    :subtotal,
                    :shipping_cost,
                    :total,
                    :payment_method,
                    \'pending\'
                )'
            );

            $insertOrder->execute([
                'user_id' => $userId,
                'order_number' => $orderNumber,
                'customer_name' => $name,
                'customer_email' => $email,
                'customer_phone' => $phone,
                'shipping_address' => $address,
                'shipping_city' => $city,
                'shipping_postal_code' => $postalCode !== '' ? $postalCode : null,
                'subtotal' => $subtotal,
                'shipping_cost' => $shipping,
                'total' => $total,
                'payment_method' => $paymentMethod,
            ]);

            $orderId = (int) $pdo->lastInsertId();

            $insertItem = $pdo->prepare(
                'INSERT INTO order_items (
                    order_id,
                    product_id,
                    product_name,
                    product_price,
                    quantity,
                    subtotal
                ) VALUES (
                    :order_id,
                    :product_id,
                    :product_name,
                    :product_price,
                    :quantity,
                    :subtotal
                )'
            );

            $updateStock = $pdo->prepare(
                'UPDATE products
                 SET stock = stock - :quantity_to_remove
                 WHERE id = :id
                 AND stock >= :quantity_available'
            );

            foreach ($orderSummary as $item) {
                $updateStock->execute([
                    'quantity_to_remove' => $item['qty'],
                    'quantity_available' => $item['qty'],
                    'id' => $item['product_id'],
                ]);

                if ($updateStock->rowCount() !== 1) {
                    throw new RuntimeException(
                        'One of the selected products is no longer available in the requested quantity.'
                    );
                }

                $insertItem->execute([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'product_price' => $item['price'],
                    'quantity' => $item['qty'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);
            }

            $pdo->prepare(
                'DELETE ci
                 FROM cart_items ci
                 INNER JOIN cart c ON c.id = ci.cart_id
                 WHERE ' .
                (
                    isLoggedIn()
                        ? 'c.user_id = :user_id'
                        : 'c.session_id = :session_id'
                )
            )->execute(
                isLoggedIn()
                    ? ['user_id' => (int) $_SESSION['user']['id']]
                    : ['session_id' => session_id()]
            );

            $pdo->commit();

            redirect('order-details.php?id=' . $orderId);
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            flash('error', $exception->getMessage());
        }
    }
}

$error = flash('error');
$checkoutUser = currentUser() ?? [];

?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/checkout.css">

<section class="checkout-page">

    <div class="checkout-container">

        <div class="checkout-header">
            <p class="checkout-eyebrow">
                Secure checkout
            </p>

            <h1>
                Checkout
            </h1>
        </div>

        <?php if ($error): ?>
            <div class="checkout-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="checkout-layout">

            <!-- =========================================
                 CHECKOUT FORM
            ========================================== -->

            <form id="checkout-form" method="post" class="checkout-form">

                <?= csrfField() ?>

                <!-- Contact Information -->

                <div class="checkout-card">

                    <h2>
                        Contact information
                    </h2>

                    <div class="checkout-grid">

                        <div class="form-group">

                            <label>
                                Email
                            </label>

                            <input
                                type="email"
                                name="customer_email"
                                required
                                value="<?= htmlspecialchars((string) ($checkoutUser['email'] ?? '')) ?>"
                                placeholder="you@example.com"
                            >

                        </div>

                        <div class="form-group">

                            <label>
                                Phone
                            </label>

                            <input
                                type="tel"
                                name="customer_phone"
                                required
                                value="<?= htmlspecialchars((string) ($checkoutUser['phone'] ?? '')) ?>"
                                placeholder="03xx-xxxxxxx"
                            >

                        </div>

                    </div>

                </div>

                <!-- Shipping Address -->

                <div class="checkout-card">

                    <h2>
                        Shipping address
                    </h2>

                    <div class="checkout-grid">

                        <div class="form-group full-width">

                            <label>
                                Full name
                            </label>

                            <input
                                type="text"
                                name="customer_name"
                                required
                                value="<?= htmlspecialchars((string) ($checkoutUser['name'] ?? '')) ?>"
                                placeholder="Your name"
                            >

                        </div>

                        <div class="form-group full-width">

                            <label>
                                Address
                            </label>

                            <input
                                type="text"
                                name="shipping_address"
                                required
                                value="<?= htmlspecialchars((string) ($checkoutUser['address'] ?? '')) ?>"
                                placeholder="Street address"
                            >

                        </div>

                        <div class="form-group">

                            <label>
                                City
                            </label>

                            <input
                                type="text"
                                name="shipping_city"
                                required
                                value="<?= htmlspecialchars((string) ($checkoutUser['city'] ?? '')) ?>"
                                placeholder="Karachi"
                            >

                        </div>

                        <div class="form-group">

                            <label>
                                Postal code
                            </label>

                            <input
                                type="text"
                                name="shipping_postal_code"
                                value="<?= htmlspecialchars((string) ($checkoutUser['postal_code'] ?? '')) ?>"
                                placeholder="74000"
                            >

                        </div>

                    </div>

                </div>

                <!-- Payment -->

                <div class="checkout-card">

                    <h2>
                        Payment
                    </h2>

                    <div class="payment-options">

                        <label class="payment-option">

                            <input
                                type="radio"
                                name="payment_method"
                                value="cod"
                                checked
                            >

                            <span>
                                Cash on delivery
                            </span>

                        </label>

                        <label class="payment-option">

                            <input
                                type="radio"
                                name="payment_method"
                                value="card"
                            >

                            <span>
                                Credit / debit card
                            </span>

                        </label>

                        <label class="payment-option">

                            <input
                                type="radio"
                                name="payment_method"
                                value="bank_transfer"
                            >

                            <span>
                                Bank transfer
                            </span>

                        </label>

                    </div>

                </div>

            </form>

            <!-- =========================================
                 ORDER SUMMARY
            ========================================== -->

            <aside class="order-summary">

                <h2>
                    Your order
                </h2>

                <!-- Order Items -->

                <div class="order-items">

                    <?php foreach ($orderSummary as $item): ?>

                        <div class="order-item">

                            <span>
                                <?= htmlspecialchars($item['name']) ?>
                                ×
                                <?= htmlspecialchars((string) $item['qty']) ?>
                            </span>

                            <span>
                                PKR
                                <?= number_format((int) ($item['qty'] * $item['price'])) ?>
                            </span>

                        </div>

                    <?php endforeach; ?>

                </div>

                <!-- Summary Details -->

                <div class="summary-details">

                    <div class="summary-row">

                        <span>
                            Subtotal
                        </span>

                        <span>
                            PKR <?= number_format((int) $subtotal) ?>
                        </span>

                    </div>

                    <div class="summary-row">

                        <span>
                            Shipping
                        </span>

                        <span>
                            PKR <?= number_format((int) $shipping) ?>
                        </span>

                    </div>

                    <div class="summary-row">

                        <span>
                            Tax
                        </span>

                        <span>
                            PKR 0
                        </span>

                    </div>

                </div>

                <!-- Total -->

                <div class="total-row">

                    <span>
                        Total
                    </span>

                    <span>
                        PKR <?= number_format((int) $total) ?>
                    </span>

                </div>

                <!-- Place Order -->

                <button
                    type="submit"
                    form="checkout-form"
                    <?= !$orderSummary ? 'disabled' : '' ?>
                    class="place-order-btn"
                >
                    Place order
                </button>

            </aside>

        </div>

    </div>

</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>