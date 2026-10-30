<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_cart'])) {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

        addToCart($productId, $quantity);
        flash('success', 'Product added to cart.');
        redirect('shop.php');
    }

    if (isset($_POST['add_to_wishlist'])) {
        if (!isLoggedIn()) {
            flash('error', 'Please log in to add items to your wishlist.');
            redirect('login.php');
        }

        $productId = (int) ($_POST['product_id'] ?? 0);
        addToWishlist($productId);
        flash('success', 'Product saved to wishlist.');
        redirect('shop.php');
    }
}

$selectedCategory = isset($_GET['category']) ? trim((string) $_GET['category']) : 'all';
$selectedCategory = $selectedCategory !== '' ? $selectedCategory : 'all';

$selectedSort = isset($_GET['sort']) ? trim((string) $_GET['sort']) : 'featured';

$searchTerm = trim((string) ($_GET['q'] ?? ''));

$categoriesStmt = $pdo->prepare(
    'SELECT id, name, slug FROM categories WHERE status = 1 ORDER BY name ASC'
);

$categoriesStmt->execute();

$categories = $categoriesStmt->fetchAll();

$fallbackCategories = [
    ['name' => 'All', 'slug' => 'all'],
    ['name' => 'New Arrivals', 'slug' => 'new-arrivals'],
    ['name' => 'Best Sellers', 'slug' => 'best-sellers'],
    ['name' => 'Mens', 'slug' => 'mens'],
    ['name' => 'Womens', 'slug' => 'womens'],
    ['name' => 'Accessories', 'slug' => 'accessories'],
];

$availableCategories = $categories ?: $fallbackCategories;

$selectedCategoryId = null;

if ($selectedCategory !== 'all') {
    foreach ($availableCategories as $category) {
        if (($category['slug'] ?? '') === $selectedCategory) {
            $selectedCategoryId = (int) ($category['id'] ?? 0);
            break;
        }
    }
}

$query = 'SELECT id, name, slug, price, sale_price, main_image, stock, status, category_id FROM products WHERE status = 1';

$params = [];

if ($searchTerm !== '') {
    $query .= ' AND (name LIKE :search_name OR slug LIKE :search_slug OR description LIKE :search_description)';

    $searchPattern = '%' . $searchTerm . '%';

    $params[':search_name'] = $searchPattern;
    $params[':search_slug'] = $searchPattern;
    $params[':search_description'] = $searchPattern;
}

if ($selectedCategoryId !== null && $selectedCategoryId > 0) {
    $query .= ' AND category_id = :category_id';
    $params[':category_id'] = $selectedCategoryId;
}

switch ($selectedSort) {
    case 'price_asc':
        $query .= ' ORDER BY price ASC, id DESC';
        break;

    case 'price_desc':
        $query .= ' ORDER BY price DESC, id DESC';
        break;

    case 'newest':
        $query .= ' ORDER BY id DESC';
        break;

    case 'featured':
    default:
        $query .= ' ORDER BY id ASC';
        break;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);

$products = $stmt->fetchAll();

?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/shop.css">
<section class="shop-section">

    <div class="shop-header">

        <div class="shop-header-content">

            <p class="shop-label">
                Collection
            </p>

            <h1 class="shop-title">
                <?= $searchTerm !== '' ? 'Search results' : 'Shop all products' ?>
            </h1>

            <?php if ($searchTerm !== ''): ?>

                <p class="shop-search-info">
                    Showing results for
                    “<?= htmlspecialchars($searchTerm) ?>”.
                </p>

            <?php endif; ?>

        </div>


        <form
            method="get"
            action="<?= BASE_URL ?>shop.php"
            class="sort-form"
        >

            <input
                type="hidden"
                name="category"
                value="<?= htmlspecialchars($selectedCategory, ENT_QUOTES, 'UTF-8') ?>"
            >

            <?php if ($searchTerm !== ''): ?>

                <input
                    type="hidden"
                    name="q"
                    value="<?= htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8') ?>"
                >

            <?php endif; ?>


            <select
                name="sort"
                onchange="this.form.submit()"
                class="sort-select"
            >

                <option
                    value="featured"
                    <?= $selectedSort === 'featured' ? 'selected' : '' ?>
                >
                    Sort: Featured
                </option>

                <option
                    value="newest"
                    <?= $selectedSort === 'newest' ? 'selected' : '' ?>
                >
                    Sort: Newest
                </option>

                <option
                    value="price_asc"
                    <?= $selectedSort === 'price_asc' ? 'selected' : '' ?>
                >
                    Sort: Price: Low to High
                </option>

                <option
                    value="price_desc"
                    <?= $selectedSort === 'price_desc' ? 'selected' : '' ?>
                >
                    Sort: Price: High to Low
                </option>

            </select>

        </form>

    </div>


    <!-- Categories -->

    <div class="category-list">

        <?php foreach ($availableCategories as $category): ?>

            <?php
                $categorySlug = (string) ($category['slug'] ?? 'all');
                $categoryName = (string) ($category['name'] ?? 'All');
            ?>

            <a
                href="<?= BASE_URL ?>shop.php?category=<?= urlencode($categorySlug) ?>&sort=<?= urlencode($selectedSort) ?>"
                class="category-link <?= $selectedCategory === $categorySlug ? 'active' : '' ?>"
            >
                <?= htmlspecialchars($categoryName) ?>
            </a>

        <?php endforeach; ?>

    </div>


    <!-- Products -->

    <?php if (!$products): ?>

        <div class="empty-products">
            No products are available right now.
        </div>

    <?php else: ?>

        <div class="products-grid">

            <?php foreach ($products as $product): ?>

                <?php

                    $displayPrice = (float) $product['price'];

                    $salePrice = $product['sale_price'] !== null
                        ? (float) $product['sale_price']
                        : null;

                    $productUrl =
                        BASE_URL .
                        'product.php?id=' .
                        (int) $product['id'];

                ?>


                <article class="product-card">


                    <!-- Product Image -->

                    <a
                        href="<?= htmlspecialchars($productUrl) ?>"
                        class="product-image-link"
                    >

                        <div class="product-image-container">

                            <img
                                src="<?= htmlspecialchars((string) $product['main_image']) ?>"
                                alt="<?= htmlspecialchars((string) $product['name']) ?>"
                                class="product-image"
                            >


                            <?php if ($salePrice !== null): ?>

                                <span class="sale-badge">
                                    Sale
                                </span>

                            <?php endif; ?>

                        </div>

                    </a>


                    <!-- Product Information -->

                    <div class="product-info">


                        <div class="product-top">

                            <div class="product-details">

                                <h2 class="product-name">
                                    <?= htmlspecialchars((string) $product['name']) ?>
                                </h2>

                                <p class="product-stock">
                                    In stock: <?= (int) $product['stock'] ?>
                                </p>

                            </div>


                            <!-- Wishlist -->

                            <form
                                method="post"
                                action="<?= BASE_URL ?>shop.php"
                                class="wishlist-form"
                            >

                                <input
                                    type="hidden"
                                    name="product_id"
                                    value="<?= (int) $product['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    name="add_to_wishlist"
                                    class="wishlist-button"
                                    aria-label="Add to wishlist"
                                >
                                    ♡
                                </button>

                            </form>

                        </div>


                        <!-- Price + Actions -->

                        <div class="product-bottom">


                            <div class="product-prices">

                                <?php if ($salePrice !== null): ?>

                                    <span class="product-old-price">
                                        PKR <?= number_format($displayPrice, 2) ?>
                                    </span>

                                    <span class="product-current-price">
                                        PKR <?= number_format($salePrice, 2) ?>
                                    </span>

                                <?php else: ?>

                                    <span class="product-current-price">
                                        PKR <?= number_format($displayPrice, 2) ?>
                                    </span>

                                <?php endif; ?>

                            </div>


                            <div class="product-actions">


                                <!-- Add To Cart -->

                                <form
                                    method="post"
                                    action="<?= BASE_URL ?>shop.php"
                                    class="cart-form"
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
                                        class="add-cart-button"
                                    >
                                        Add
                                    </button>

                                </form>


                                <!-- View Product -->

                                <a
                                    href="<?= htmlspecialchars($productUrl) ?>"
                                    class="view-button"
                                >
                                    View
                                </a>

                            </div>

                        </div>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>


<?php require_once __DIR__ . '/includes/footer.php'; ?>