<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$stmt = $pdo->prepare('SELECT id, name, slug, price, sale_price, main_image, stock, status FROM products WHERE status = 1 ORDER BY created_at DESC, id DESC LIMIT 4');
$stmt->execute();
$featuredProducts = $stmt->fetchAll();
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/index.css">
<section class="hero-section">
    <div class="container hero-container">

        <?php if (!empty($featuredProducts)): ?>

            <div class="carousel">

                <div class="carousel-slides">

                    <?php foreach ($featuredProducts as $index => $slide): ?>

                        <?php
                            $slidePrice = (float) $slide['price'];
                            $slideSalePrice = $slide['sale_price'] !== null ? (float) $slide['sale_price'] : null;
                            $slideUrl = BASE_URL . 'product.php?id=' . (int) $slide['id'];
                        ?>

                        <div
                            class="slide <?= $index === 0 ? 'active' : '' ?>"
                            <?= $index === 0 ? 'style="z-index: 1;"' : 'style="z-index: 0;"' ?>
                        >

                            <div class="hero-grid">

                                <div class="hero-content">

                                    <p class="hero-label">
                                        New Collection
                                    </p>

                                    <h1 class="hero-title">
                                        <?= htmlspecialchars((string) $slide['name']) ?>
                                    </h1>

                                    <p class="hero-description">
                                        Discover fresh essentials and elevated everyday staples designed for confident living.
                                    </p>

                                    <div class="hero-actions">

                                        <a
                                            href="<?= htmlspecialchars($slideUrl) ?>"
                                            class="btn btn-primary"
                                        >
                                            Shop now
                                        </a>

                                        <a
                                            href="<?= BASE_URL ?>shop.php"
                                            class="btn btn-secondary"
                                        >
                                            View collection
                                        </a>

                                    </div>

                                    <div class="hero-features">

                                        <span class="hero-feature">
                                            Free shipping
                                        </span>

                                        <span class="hero-feature">
                                            Easy returns
                                        </span>

                                        <span class="hero-feature">
                                            Secure checkout
                                        </span>

                                    </div>

                                </div>


                                <div class="hero-image-wrapper">

                                    <div class="hero-image-container">

                                        <img
                                            src="<?= htmlspecialchars((string) $slide['main_image']) ?>"
                                            alt="<?= htmlspecialchars((string) $slide['name']) ?>"
                                            class="hero-image"
                                        >

                                        <span class="fresh-badge">
                                            Fresh
                                        </span>

                                    </div>


                                    <div class="price-box">

                                        <?php if ($slideSalePrice !== null): ?>

                                            <div class="old-price">
                                                PKR <?= number_format($slidePrice, 2) ?>
                                            </div>

                                            <div class="current-price">
                                                PKR <?= number_format($slideSalePrice, 2) ?>
                                            </div>

                                        <?php else: ?>

                                            <div class="current-price">
                                                PKR <?= number_format($slidePrice, 2) ?>
                                            </div>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>


                <div class="carousel-dots">

                    <?php foreach ($featuredProducts as $index => $slide): ?>

                        <button
                            type="button"
                            class="carousel-dot <?= $index === 0 ? 'active' : '' ?>"
                            data-slide="<?= (int) $index ?>"
                            aria-label="Show slide <?= (int) ($index + 1) ?>"
                        ></button>

                    <?php endforeach; ?>

                </div>


                <button
                    type="button"
                    class="carousel-prev"
                    aria-label="Previous slide"
                >
                    ‹
                </button>

                <button
                    type="button"
                    class="carousel-next"
                    aria-label="Next slide"
                >
                    ›
                </button>

            </div>

        <?php else: ?>

            <div class="empty-hero">

                <p class="hero-label">
                    New Collection
                </p>

                <h1 class="empty-hero-title">
                    Fresh arrivals,
                    <span>made to stand out.</span>
                </h1>

                <p class="empty-hero-description">
                    Discover modern essentials and statement pieces for every day.
                </p>

                <div class="empty-hero-actions">

                    <a
                        href="<?= BASE_URL ?>shop.php"
                        class="btn btn-primary"
                    >
                        Shop Collection
                    </a>

                    <a
                        href="<?= BASE_URL ?>about.php"
                        class="btn btn-secondary"
                    >
                        Learn More
                    </a>

                </div>

            </div>

        <?php endif; ?>

    </div>
</section>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const carousel = document.querySelector('.carousel');

    if (!carousel) {
        return;
    }

    const slides = Array.from(
        carousel.querySelectorAll('.slide')
    );

    const dots = Array.from(
        carousel.querySelectorAll('.carousel-dot')
    );

    const prevButton = carousel.querySelector('.carousel-prev');
    const nextButton = carousel.querySelector('.carousel-next');

    if (!slides.length) {
        return;
    }

    let currentIndex = 0;

    function renderSlide(index) {

        slides.forEach((slide, slideIndex) => {

            const isActive = slideIndex === index;

            slide.classList.toggle(
                'active',
                isActive
            );

            slide.style.zIndex = isActive ? '1' : '0';

        });


        dots.forEach((dot, dotIndex) => {

            const isActive = dotIndex === index;

            dot.classList.toggle(
                'active',
                isActive
            );

        });

    }


    function showNext() {

        currentIndex =
            (currentIndex + 1) % slides.length;

        renderSlide(currentIndex);

    }


    function showPrev() {

        currentIndex =
            (currentIndex - 1 + slides.length) % slides.length;

        renderSlide(currentIndex);

    }


    prevButton?.addEventListener(
        'click',
        showPrev
    );

    nextButton?.addEventListener(
        'click',
        showNext
    );


    dots.forEach((dot) => {

        dot.addEventListener(
            'click',
            function () {

                currentIndex =
                    Number(this.dataset.slide || 0);

                renderSlide(currentIndex);

            }
        );

    });


    setInterval(
        showNext,
        5000
    );

});
</script>


<!-- Features -->

<section class="features-section">

    <div class="container">

        <div class="features-grid">

            <div class="feature-card">

                <div class="feature-icon">
                    ✦
                </div>

                <h3 class="feature-title">
                    Quality First
                </h3>

                <p class="feature-description">
                    Carefully selected materials and thoughtful designs.
                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">
                    🚚
                </div>

                <h3 class="feature-title">
                    Fast Delivery
                </h3>

                <p class="feature-description">
                    Get your favorite pieces delivered to your doorstep.
                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">
                    ♡
                </div>

                <h3 class="feature-title">
                    Made With Care
                </h3>

                <p class="feature-description">
                    Fashion created with attention to every detail.
                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">
                    ↻
                </div>

                <h3 class="feature-title">
                    Easy Shopping
                </h3>

                <p class="feature-description">
                    A simple and convenient online shopping experience.
                </p>

            </div>

        </div>

    </div>

</section>


<!-- Featured Products -->

<section class="featured-section">

    <div class="container featured-container">

        <div class="featured-header">

            <div>

                <p class="featured-label">
                    Explore
                </p>

                <h2 class="featured-title">
                    Featured picks.
                </h2>

            </div>

            <a
                href="<?= BASE_URL ?>shop.php"
                class="view-all-link"
            >
                View all products →
            </a>

        </div>


        <?php if (empty($featuredProducts)): ?>

            <div class="empty-products">
                No featured products are available right now.
            </div>

        <?php else: ?>

            <div class="products-grid">

                <?php foreach ($featuredProducts as $product): ?>

                    <?php
                        $displayPrice = (float) $product['price'];
                        $salePrice = $product['sale_price'] !== null ? (float) $product['sale_price'] : null;
                        $productUrl = BASE_URL . 'product.php?id=' . (int) $product['id'];
                    ?>

                    <article class="product-card">

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


                        <div class="product-info">

                            <p class="product-category">
                                Collection
                            </p>

                            <h3 class="product-name">
                                <?= htmlspecialchars((string) $product['name']) ?>
                            </h3>

                            <p class="product-stock">
                                In stock: <?= (int) $product['stock'] ?>
                            </p>


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


                                <a
                                    href="<?= htmlspecialchars($productUrl) ?>"
                                    class="product-view-button"
                                >
                                    View
                                </a>

                            </div>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

</section>


<?php

require_once __DIR__ . '/includes/footer.php';

?>