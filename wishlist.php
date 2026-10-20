<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

if (!isLoggedIn()) {
    flash('error', 'Please log in to view your wishlist.');
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int) ($_POST['product_id'] ?? 0);

    if (isset($_POST['remove_from_wishlist'])) {
        removeFromWishlist($productId);
        flash('success', 'Item removed from wishlist.');
        redirect('wishlist.php');
    }

    if (isset($_POST['add_to_cart_from_wishlist'])) {
        addToCart($productId, 1);
        flash('success', 'Product added to cart from wishlist.');
        redirect('wishlist.php');
    }
}

$wishlist = getWishlistItems();

?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/wishlist.css">

<section class="wishlist-section">

    <div class="wishlist-header">
        <p class="wishlist-label">Saved items</p>
        <h1 class="wishlist-title">Wishlist</h1>
    </div>

    <?php if (!$wishlist): ?>

        <div class="wishlist-empty">
            Your wishlist is empty.
            <a href="<?= BASE_URL ?>shop.php" class="wishlist-empty-link">
                Browse products
            </a>
        </div>

    <?php else: ?>

        <div class="wishlist-grid">

            <?php foreach ($wishlist as $item): ?>

                <?php
                $productId = (int) $item['product_id'];
                $displayPrice = (float) ($item['sale_price'] ?? $item['price']);
                $name = htmlspecialchars((string) $item['name']);
                $image = htmlspecialchars((string) $item['main_image']);
                ?>

                <article class="wishlist-card">

                    <div class="wishlist-image-container">
                        <img
                            src="<?= $image ?>"
                            alt="<?= $name ?>"
                            class="wishlist-image"
                        >
                    </div>

                    <div class="wishlist-info">

                        <div class="wishlist-product-header">
                            <h2 class="wishlist-product-name">
                                <?= $name ?>
                            </h2>

                            <span class="wishlist-heart">♥</span>
                        </div>

                        <p class="wishlist-price">
                            PKR <?= number_format($displayPrice, 2) ?>
                        </p>

                        <div class="wishlist-actions">

                            <form
                                method="post"
                                action="<?= BASE_URL ?>wishlist.php"
                                class="wishlist-cart-form"
                            >
                                <input
                                    type="hidden"
                                    name="product_id"
                                    value="<?= $productId ?>"
                                >

                                <button
                                    type="submit"
                                    name="add_to_cart_from_wishlist"
                                    class="wishlist-cart-button"
                                >
                                    Add to cart
                                </button>
                            </form>

                            <form
                                method="post"
                                action="<?= BASE_URL ?>wishlist.php"
                                class="wishlist-remove-form"
                            >
                                <input
                                    type="hidden"
                                    name="product_id"
                                    value="<?= $productId ?>"
                                >

                                <button
                                    type="submit"
                                    name="remove_from_wishlist"
                                    class="wishlist-remove-button"
                                >
                                    Remove
                                </button>
                            </form>

                        </div>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>