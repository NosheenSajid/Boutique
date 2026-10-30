<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

requireLogin();
ensureProductReviewsTable();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {

    $productId = (int) ($_POST['product_id'] ?? 0);
    $rating = (int) ($_POST['rating'] ?? 0);
    $title = trim((string) ($_POST['review_title'] ?? ''));
    $comment = trim((string) ($_POST['review_comment'] ?? ''));

    if (!verifyCsrf((string) ($_POST['csrf_token'] ?? ''))) {

        flash(
            'error',
            'Security token expired. Please try again.'
        );

    } elseif ($rating < 1 || $rating > 5 || $comment === '') {

        flash(
            'error',
            'Please choose a rating and write a review.'
        );

    } else {

        $purchaseStmt = $pdo->prepare(
            "SELECT oi.product_id
             FROM order_items oi
             INNER JOIN orders o ON o.id = oi.order_id
             WHERE o.user_id = :user_id
             AND oi.product_id = :product_id
             AND o.status <> 'cancelled'
             LIMIT 1"
        );

        $purchaseStmt->execute([
            'user_id' => (int) $_SESSION['user']['id'],
            'product_id' => $productId
        ]);

        if (!$purchaseStmt->fetch()) {

            flash(
                'error',
                'You can only review products you purchased.'
            );

        } else {

            try {

                $reviewStmt = $pdo->prepare(
                    'INSERT INTO product_reviews
                    (product_id, user_id, rating, title, comment)
                    VALUES
                    (:product_id, :user_id, :rating, :title, :comment)'
                );

                $reviewStmt->execute([
                    'product_id' => $productId,
                    'user_id' => (int) $_SESSION['user']['id'],
                    'rating' => $rating,
                    'title' => $title !== '' ? $title : null,
                    'comment' => $comment
                ]);

                flash(
                    'success',
                    'Your review was added.'
                );

            } catch (PDOException $exception) {

                flash(
                    'error',
                    $exception->getCode() === '23000'
                        ? 'You have already reviewed this product.'
                        : 'Unable to save your review.'
                );
            }
        }
    }

    redirect('orders.php');
}


/* =========================================================
   ORDERS
   ========================================================= */

$ordersStmt = $pdo->prepare(
    'SELECT
        o.id,
        o.order_number,
        o.created_at,
        o.status,
        o.total,
        COUNT(oi.id) AS item_count,

        (
            SELECT p.main_image
            FROM order_items oi2
            LEFT JOIN products p ON p.id = oi2.product_id
            WHERE oi2.order_id = o.id
            ORDER BY oi2.id ASC
            LIMIT 1
        ) AS thumbnail

     FROM orders o

     LEFT JOIN order_items oi
        ON oi.order_id = o.id

     WHERE o.user_id = :user_id

     GROUP BY
        o.id,
        o.order_number,
        o.created_at,
        o.status,
        o.total

     ORDER BY o.created_at DESC'
);

$ordersStmt->execute([
    'user_id' => (int) $_SESSION['user']['id']
]);

$orders = $ordersStmt->fetchAll();


/* =========================================================
   PURCHASED PRODUCTS
   ========================================================= */

$purchasedStmt = $pdo->prepare(
    "SELECT
        oi.product_id,
        oi.product_name,
        p.main_image,
        pr.id AS review_id

     FROM order_items oi

     INNER JOIN orders o
        ON o.id = oi.order_id

     LEFT JOIN products p
        ON p.id = oi.product_id

     LEFT JOIN product_reviews pr
        ON pr.product_id = oi.product_id
        AND pr.user_id = :user_id

     WHERE o.user_id = :owner_id
       AND o.status <> 'cancelled'

     GROUP BY
        oi.product_id,
        oi.product_name,
        p.main_image,
        pr.id

     ORDER BY MAX(o.created_at) DESC"
);

$purchasedStmt->execute([
    'user_id' => (int) $_SESSION['user']['id'],
    'owner_id' => (int) $_SESSION['user']['id']
]);

$purchasedProducts = $purchasedStmt->fetchAll();


$error = flash('error');
$success = flash('success');

?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/orders.css">


<section class="orders-section">


    <!-- Page Header -->

    <div class="orders-header">

        <p class="orders-label">
            Orders
        </p>

        <h1 class="orders-title">
            My purchases
        </h1>

    </div>


    <!-- Flash Messages -->

    <?php if ($error): ?>

        <div class="orders-alert orders-alert-error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <?php if ($success): ?>

        <div class="orders-alert orders-alert-success">
            <?= htmlspecialchars($success) ?>
        </div>

    <?php endif; ?>


    <!-- Orders Table -->

    <div class="orders-table-card">

        <div class="orders-table-wrapper">

            <table class="orders-table">

                <thead>

                    <tr>

                        <th>
                            Order
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Total
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($orders as $order): ?>

                        <?php

                            $orderStatus = (string) $order['status'];

                            if ($orderStatus === 'cancelled') {
                                $statusClass = 'order-status-cancelled';
                            } elseif ($orderStatus === 'delivered') {
                                $statusClass = 'order-status-delivered';
                            } else {
                                $statusClass = 'order-status-default';
                            }

                        ?>

                        <tr>


                            <!-- Order -->

                            <td class="orders-table-order">

                                <div class="order-item-info">


                                    <?php if (!empty($order['thumbnail'])): ?>

                                        <img
                                            src="<?= htmlspecialchars((string) $order['thumbnail']) ?>"
                                            alt="Order item"
                                            class="order-thumbnail"
                                        >

                                    <?php else: ?>

                                        <div class="order-no-image">
                                            No image
                                        </div>

                                    <?php endif; ?>


                                    <div>

                                        <div class="order-number">

                                            #<?= htmlspecialchars(
                                                (string) $order['order_number']
                                            ) ?>

                                        </div>

                                        <div class="order-item-count">

                                            <?= (int) $order['item_count'] ?>

                                            item<?= (int) $order['item_count'] === 1 ? '' : 's' ?>

                                        </div>

                                    </div>

                                </div>

                            </td>


                            <!-- Date -->

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


                            <!-- Status -->

                            <td>

                                <span
                                    class="order-status <?= $statusClass ?>"
                                >
                                    <?= htmlspecialchars(
                                        $orderStatus
                                    ) ?>
                                </span>

                            </td>


                            <!-- Total -->

                            <td>

                                PKR
                                <?= number_format(
                                    (float) $order['total'],
                                    2
                                ) ?>

                            </td>


                            <!-- Action -->

                            <td>

                                <a
                                    href="<?= BASE_URL ?>order-details.php?id=<?= (int) $order['id'] ?>"
                                    class="order-view-link"
                                >
                                    View
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>


                    <?php if (!$orders): ?>

                        <tr>

                            <td
                                colspan="5"
                                class="orders-empty"
                            >

                                You have not placed any orders yet.

                                <a
                                    href="<?= BASE_URL ?>shop.php"
                                    class="orders-shop-link"
                                >
                                    Start shopping
                                </a>

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>


    <!-- Review Purchases -->

    <?php if ($purchasedProducts): ?>

        <section class="review-purchases">

            <h2 class="review-purchases-title">
                Review your purchases
            </h2>


            <div class="review-products-grid">

                <?php foreach ($purchasedProducts as $purchasedProduct): ?>

                    <article class="review-product-card">


                        <div class="review-product-info">


                            <?php if (!empty($purchasedProduct['main_image'])): ?>

                                <img
                                    src="<?= htmlspecialchars((string) $purchasedProduct['main_image']) ?>"
                                    alt="<?= htmlspecialchars((string) $purchasedProduct['product_name']) ?>"
                                    class="review-product-image"
                                >

                            <?php else: ?>

                                <div class="review-product-no-image"></div>

                            <?php endif; ?>


                            <h3 class="review-product-name">

                                <?= htmlspecialchars(
                                    (string) $purchasedProduct['product_name']
                                ) ?>

                            </h3>

                        </div>


                        <?php if ($purchasedProduct['review_id']): ?>

                            <p class="reviewed-text">
                                Reviewed
                            </p>

                        <?php else: ?>

                            <button
                                type="button"
                                class="review-product-button"
                                data-product-id="<?= (int) $purchasedProduct['product_id'] ?>"
                                data-product-name="<?= htmlspecialchars(
                                    (string) $purchasedProduct['product_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                            >
                                Review
                            </button>

                        <?php endif; ?>

                    </article>

                <?php endforeach; ?>

            </div>

        </section>

    <?php endif; ?>

</section>


<!-- Review Modal -->

<div
    id="review-modal"
    class="review-modal"
>

    <div class="review-modal-content">


        <!-- Modal Header -->

        <div class="review-modal-header">

            <div>

                <p class="review-modal-label">
                    Purchased product
                </p>

                <h2
                    id="review-product-name"
                    class="review-modal-title"
                ></h2>

            </div>


            <button
                type="button"
                id="close-review-modal"
                class="close-review-modal"
            >
                Close
            </button>

        </div>


        <!-- Review Form -->

        <form
            method="post"
            class="review-form"
        >

            <?= csrfField() ?>


            <input
                id="review-product-id"
                type="hidden"
                name="product_id"
            >


            <!-- Rating -->

            <div class="review-field">

                <label class="review-field-label">
                    Rating
                </label>

                <select
                    name="rating"
                    required
                    class="review-select"
                >

                    <option value="">
                        Choose rating
                    </option>

                    <?php for ($rating = 5; $rating >= 1; $rating--): ?>

                        <option value="<?= $rating ?>">

                            <?= $rating ?>
                            star<?= $rating === 1 ? '' : 's' ?>

                        </option>

                    <?php endfor; ?>

                </select>

            </div>


            <!-- Review Title -->

            <div class="review-field">

                <label class="review-field-label">
                    Title
                </label>

                <input
                    name="review_title"
                    maxlength="150"
                    class="review-input"
                >

            </div>


            <!-- Review Comment -->

            <div class="review-field">

                <label class="review-field-label">
                    Review
                </label>

                <textarea
                    name="review_comment"
                    required
                    rows="4"
                    class="review-textarea"
                ></textarea>

            </div>


            <!-- Submit -->

            <button
                type="submit"
                name="submit_review"
                class="review-submit-button"
            >
                Submit review
            </button>

        </form>

    </div>

</div>


<script>

    const reviewModal =
        document.getElementById('review-modal');

    const reviewProductId =
        document.getElementById('review-product-id');

    const reviewProductName =
        document.getElementById('review-product-name');


    document
        .querySelectorAll('.review-product-button')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                reviewProductId.value =
                    this.dataset.productId;

                reviewProductName.textContent =
                    this.dataset.productName;

                reviewModal.classList.add('is-open');

            });

        });


    function closeReviewModal() {

        reviewModal.classList.remove('is-open');

    }


    document
        .getElementById('close-review-modal')
        .addEventListener(
            'click',
            closeReviewModal
        );


    reviewModal.addEventListener(
        'click',
        function (event) {

            if (event.target === reviewModal) {
                closeReviewModal();
            }

        }
    );

</script>


<?php require_once __DIR__ . '/includes/footer.php'; ?>