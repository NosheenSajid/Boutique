<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

ensureProductReviewsTable();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['add_to_cart'])) {

        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

        addToCart($productId, $quantity);

        flash('success', 'Product added to cart.');

        redirect('product.php?id=' . $productId);
    }


    if (isset($_POST['add_to_wishlist'])) {

        if (!isLoggedIn()) {

            flash(
                'error',
                'Please log in to add items to your wishlist.'
            );

            redirect('login.php');
        }

        $productId = (int) ($_POST['product_id'] ?? 0);

        addToWishlist($productId);

        flash(
            'success',
            'Product saved to wishlist.'
        );

        redirect('product.php?id=' . $productId);
    }
}


$productId = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

$productSlug = trim(
    (string) ($_GET['slug'] ?? '')
);

$product = null;


if ($productId !== false && $productId !== null) {

    $stmt = $pdo->prepare(
        'SELECT id, category_id, name, slug, description, price, sale_price, stock, sku, main_image, status
         FROM products
         WHERE id = :id
         LIMIT 1'
    );

    $stmt->execute([
        'id' => $productId
    ]);

    $product = $stmt->fetch();

} elseif ($productSlug !== '') {

    $stmt = $pdo->prepare(
        'SELECT id, category_id, name, slug, description, price, sale_price, stock, sku, main_image, status
         FROM products
         WHERE slug = :slug
         LIMIT 1'
    );

    $stmt->execute([
        'slug' => $productSlug
    ]);

    $product = $stmt->fetch();
}


$reviews = [];
$averageRating = 0;


if ($product) {

    $reviewsStmt = $pdo->prepare(
        'SELECT
            pr.rating,
            pr.title,
            pr.comment,
            pr.created_at,
            u.name AS reviewer_name
         FROM product_reviews pr
         INNER JOIN users u ON u.id = pr.user_id
         WHERE pr.product_id = :product_id
         ORDER BY pr.created_at DESC'
    );

    $reviewsStmt->execute([
        'product_id' => (int) $product['id']
    ]);

    $reviews = $reviewsStmt->fetchAll();

    if ($reviews) {
        $averageRating =
            array_sum(
                array_column($reviews, 'rating')
            ) / count($reviews);
    }
}


$error = flash('error');
$success = flash('success');

?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/product.css">


<section class="product-section">

    <?php if (!$product): ?>

        <!-- Product Not Found -->

        <div class="product-not-found">

            <h1 class="product-not-found-title">
                Product not found
            </h1>

            <p class="product-not-found-text">
                The requested product could not be found in the catalog.
            </p>

            <a
                href="<?= BASE_URL ?>shop.php"
                class="product-back-button"
            >
                Back to shop
            </a>

        </div>


    <?php else: ?>


        <!-- Breadcrumb -->

        <nav class="product-breadcrumb">

            <a href="<?= BASE_URL ?>">
                Home
            </a>

            <span class="product-breadcrumb-separator">
                /
            </span>

            <a href="<?= BASE_URL ?>shop.php">
                Shop
            </a>

            <span class="product-breadcrumb-separator">
                /
            </span>

            <span class="product-breadcrumb-current">
                <?= htmlspecialchars((string) $product['name']) ?>
            </span>

        </nav>


        <!-- Flash Messages -->

        <?php if ($error): ?>

            <div class="product-alert product-alert-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <?php if ($success): ?>

            <div class="product-alert product-alert-success">
                <?= htmlspecialchars($success) ?>
            </div>

        <?php endif; ?>


        <!-- Product Main -->

        <div class="product-main">


            <!-- Product Image -->

            <div class="product-image-column">

                <div class="product-image-wrapper">

                    <img
                        src="<?= htmlspecialchars((string) $product['main_image']) ?>"
                        alt="<?= htmlspecialchars((string) $product['name']) ?>"
                        class="product-main-image"
                    >

                </div>

            </div>


            <!-- Product Information -->

            <div class="product-info">

                <p class="product-label">
                    Product
                </p>

                <h1 class="product-title">
                    <?= htmlspecialchars((string) $product['name']) ?>
                </h1>


                <!-- Price -->

                <div class="product-price-row">

                    <?php

                        $originalPrice = (float) $product['price'];

                        $salePrice =
                            $product['sale_price'] !== null
                                ? (float) $product['sale_price']
                                : null;

                    ?>


                    <?php if ($salePrice !== null): ?>

                        <span class="product-original-price">
                            PKR <?= number_format($originalPrice, 2) ?>
                        </span>

                        <span class="product-sale-price">
                            PKR <?= number_format($salePrice, 2) ?>
                        </span>

                    <?php else: ?>

                        <span class="product-regular-price">
                            PKR <?= number_format($originalPrice, 2) ?>
                        </span>

                    <?php endif; ?>


                    <span class="product-stock">
                        <?= (int) $product['stock'] > 0
                            ? 'In stock'
                            : 'Out of stock'
                        ?>
                    </span>

                </div>


                <!-- Description -->

                <p class="product-description">
                    <?= nl2br(htmlspecialchars((string) $product['description'])) ?>
                </p>


                <!-- Size -->

                <div class="product-size-section">

                    <label class="product-size-label">
                        Size
                    </label>

                    <div class="product-size-options">

                        <?php foreach (['S', 'M', 'L', 'XL'] as $size): ?>

                            <button
                                type="button"
                                class="product-size-button"
                            >
                                <?= htmlspecialchars($size) ?>
                            </button>

                        <?php endforeach; ?>

                    </div>

                </div>


                <!-- Actions -->

                <div class="product-actions">


                    <!-- Add To Cart -->

                    <form
                        method="post"
                        action="<?= BASE_URL ?>product.php?id=<?= (int) $product['id'] ?>"
                        class="product-cart-form"
                    >

                        <input
                            type="hidden"
                            name="product_id"
                            value="<?= (int) $product['id'] ?>"
                        >

                        <input
                            type="hidden"
                            name="quantity"
                            value="1"
                        >

                        <button
                            type="submit"
                            name="add_to_cart"
                            class="product-cart-button"
                        >
                            Add to cart
                        </button>

                    </form>


                    <!-- Wishlist -->

                    <form
                        method="post"
                        action="<?= BASE_URL ?>product.php?id=<?= (int) $product['id'] ?>"
                        class="product-wishlist-form"
                    >

                        <input
                            type="hidden"
                            name="product_id"
                            value="<?= (int) $product['id'] ?>"
                        >

                        <button
                            type="submit"
                            name="add_to_wishlist"
                            class="product-wishlist-button"
                        >
                            Save for later
                        </button>

                    </form>

                </div>


                <!-- Product Details -->

                <div class="product-details">

                    <h2 class="product-details-title">
                        Product details
                    </h2>

                    <ul class="product-details-list">

                        <li>
                            • SKU:
                            <?= htmlspecialchars(
                                (string) ($product['sku'] ?? 'N/A')
                            ) ?>
                        </li>

                        <li>
                            • Stock:
                            <?= (int) $product['stock'] ?>
                            available
                        </li>

                        <li>
                            • Product status:
                            <?= (int) $product['status'] === 1
                                ? 'Active'
                                : 'Inactive'
                            ?>
                        </li>

                    </ul>

                </div>

            </div>

        </div>


        <!-- Reviews -->

        <section class="reviews-section">


            <!-- Review Summary -->

            <div class="reviews-summary">

                <p class="reviews-label">
                    Customer reviews
                </p>


                <div class="reviews-rating-row">

                    <span class="reviews-average">
                        <?= number_format($averageRating, 1) ?>
                    </span>

                    <span class="reviews-count">
                        / 5 from
                        <?= count($reviews) ?>
                        review<?= count($reviews) === 1 ? '' : 's' ?>
                    </span>

                </div>


                <div class="reviews-stars">

                    <?= str_repeat(
                        '★',
                        (int) round($averageRating)
                    ) ?>

                    <span class="reviews-stars-empty">

                        <?= str_repeat(
                            '★',
                            5 - (int) round($averageRating)
                        ) ?>

                    </span>

                </div>


                <p class="reviews-help">

                    <a href="<?= BASE_URL ?>orders.php">
                        Review purchased products
                    </a>

                    from your orders.

                </p>

            </div>


            <!-- Review List -->

            <div class="reviews-list">

                <?php foreach ($reviews as $review): ?>

                    <article class="review-card">

                        <div class="review-header">

                            <div>

                                <h3 class="review-title">
                                    <?= htmlspecialchars(
                                        (string) (
                                            $review['title']
                                                ?: 'Customer review'
                                        )
                                    ) ?>
                                </h3>

                                <p class="review-meta">

                                    <?= htmlspecialchars(
                                        (string) $review['reviewer_name']
                                    ) ?>

                                    ·

                                    <?= htmlspecialchars(
                                        date(
                                            'M j, Y',
                                            strtotime(
                                                (string) $review['created_at']
                                            )
                                        )
                                    ) ?>

                                </p>

                            </div>


                            <span class="review-rating">

                                <?= str_repeat(
                                    '★',
                                    (int) $review['rating']
                                ) ?>

                            </span>

                        </div>


                        <p class="review-comment">

                            <?= nl2br(
                                htmlspecialchars(
                                    (string) $review['comment']
                                )
                            ) ?>

                        </p>

                    </article>

                <?php endforeach; ?>


                <?php if (!$reviews): ?>

                    <div class="no-reviews">
                        No reviews yet. Be the first to review this product.
                    </div>

                <?php endif; ?>

            </div>

        </section>


    <?php endif; ?>

</section>


<?php require_once __DIR__ . '/includes/footer.php'; ?>