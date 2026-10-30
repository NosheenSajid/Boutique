<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_from_cart'])) {
        $productId = (int) ($_POST['product_id'] ?? 0);

        if ($productId > 0) {
            removeFromCart($productId);
            flash('success', 'Item removed from cart.');
        }

        redirect('cart.php');
    }

    if (isset($_POST['update_cart_quantity'])) {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $qty = max(1, (int) ($_POST['quantity'] ?? 1));

        if ($productId > 0) {
            setCartQuantity($productId, $qty);
        }

        redirect('cart.php');
    }
}

$cartItems = getCartItems();
$subtotal = 0;

foreach ($cartItems as $item) {
    $price = (float) ($item['sale_price'] ?? $item['price']);
    $subtotal += $price * (int) $item['quantity'];
}

$shipping = $subtotal > 0 ? 500 : 0;
$total = $subtotal + $shipping;

?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/cart.css">


<section class="cart-page">

    <div class="cart-container">

        <!-- =========================================
             PAGE HEADER
        ========================================== -->

        <div class="cart-header">

            <p class="cart-eyebrow">
                Your bag
            </p>

            <h1>
                Shopping cart
            </h1>

        </div>


        <!-- =========================================
             MAIN CART CONTENT
        ========================================== -->

        <div class="cart-layout">


            <!-- =====================================
                 CART ITEMS
            ====================================== -->

            <div class="cart-items">

                <?php if (!$cartItems): ?>

                    <div class="empty-cart">

                        Your cart is empty.

                        <a href="<?= BASE_URL ?>shop.php">
                            Continue shopping
                        </a>

                    </div>

                <?php else: ?>

                    <?php foreach ($cartItems as $item): ?>

                        <?php
                            $unitPrice = (float) ($item['sale_price'] ?? $item['price']);
                            $itemTotal = $unitPrice * (int) $item['quantity'];
                        ?>


                        <!-- =================================
                             SINGLE CART ITEM
                        ================================== -->

                        <div class="cart-item">


                            <!-- Product Image -->

                            <img
                                src="<?= htmlspecialchars((string) $item['main_image']) ?>"
                                alt="<?= htmlspecialchars((string) $item['name']) ?>"
                                class="cart-item-image"
                            >


                            <!-- Product Content -->

                            <div class="cart-item-content">


                                <!-- Item Top -->

                                <div class="cart-item-top">

                                    <div class="cart-item-info">

                                        <h2 class="cart-item-name">
                                            <?= htmlspecialchars((string) $item['name']) ?>
                                        </h2>

                                        <p class="cart-item-quantity">
                                            Qty:
                                            <?= (int) $item['quantity'] ?>
                                        </p>

                                    </div>


                                    <!-- Remove -->

                                    <form
                                        method="post"
                                        action="<?= BASE_URL ?>cart.php"
                                        class="remove-form"
                                    >

                                        <input
                                            type="hidden"
                                            name="product_id"
                                            value="<?= (int) $item['product_id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="remove_from_cart"
                                            class="remove-button"
                                        >
                                            Remove
                                        </button>

                                    </form>

                                </div>


                                <!-- Item Bottom -->

                                <div class="cart-item-bottom">


                                    <!-- Quantity Controls -->

                                    <div class="quantity-controls">


                                        <!-- Decrease -->

                                        <form
                                            method="post"
                                            action="<?= BASE_URL ?>cart.php"
                                            class="quantity-form"
                                        >

                                            <input
                                                type="hidden"
                                                name="product_id"
                                                value="<?= (int) $item['product_id'] ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="quantity"
                                                value="<?= max(1, (int) $item['quantity'] - 1) ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="update_cart_quantity"
                                                class="quantity-button"
                                                aria-label="Decrease quantity"
                                            >
                                                −
                                            </button>

                                        </form>


                                        <!-- Current Quantity -->

                                        <span class="quantity-value">
                                            <?= (int) $item['quantity'] ?>
                                        </span>


                                        <!-- Increase -->

                                        <form
                                            method="post"
                                            action="<?= BASE_URL ?>cart.php"
                                            class="quantity-form"
                                        >

                                            <input
                                                type="hidden"
                                                name="product_id"
                                                value="<?= (int) $item['product_id'] ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="quantity"
                                                value="<?= (int) $item['quantity'] + 1 ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="update_cart_quantity"
                                                class="quantity-button"
                                                aria-label="Increase quantity"
                                            >
                                                +
                                            </button>

                                        </form>

                                    </div>


                                    <!-- Item Total -->

                                    <p class="cart-item-price">
                                        PKR
                                        <?= number_format($itemTotal, 2) ?>
                                    </p>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>


            <!-- =====================================
                 ORDER SUMMARY
            ====================================== -->

            <aside class="cart-summary">

                <h2>
                    Order summary
                </h2>


                <!-- Summary -->

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

                <div class="cart-total">

                    <span>
                        Total
                    </span>

                    <span>
                        PKR <?= number_format((int) $total) ?>
                    </span>

                </div>


                <!-- Checkout -->

                <a
                    href="<?= BASE_URL ?>checkout.php"
                    class="checkout-button"
                >
                    Proceed to checkout
                </a>


                <!-- Continue Shopping -->

                <a
                    href="<?= BASE_URL ?>shop.php"
                    class="continue-shopping"
                >
                    Continue shopping
                </a>

            </aside>

        </div>

    </div>

</section>


<?php require_once __DIR__ . '/includes/footer.php'; ?>