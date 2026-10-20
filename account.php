<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$userId = (int) $_SESSION['user']['id'];

$stmt = $pdo->prepare(
    'SELECT name, email, phone, address, city, postal_code
     FROM users
     WHERE id = :id
     LIMIT 1'
);

$stmt->execute([
    'id' => $userId
]);

$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    redirect('login.php');
}

?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/account.css">

<section class="account-page">

    <div class="account-container">


        <!-- =========================================
             PAGE HEADER
        ========================================== -->

        <div class="account-header">

            <p class="account-eyebrow">
                My profile
            </p>

            <h1>
                Account overview
            </h1>

        </div>


        <!-- =========================================
             ACCOUNT LAYOUT
        ========================================== -->

        <div class="account-layout">


            <!-- =====================================
                 SIDEBAR
            ====================================== -->

            <aside class="account-sidebar">

                <!-- User Information -->

                <div class="account-user">

                    <div class="account-avatar">
                        <?= htmlspecialchars(
                            strtoupper(
                                substr(
                                    (string) $user['name'],
                                    0,
                                    2
                                )
                            )
                        ) ?>
                    </div>

                    <div class="account-user-info">

                        <h2>
                            <?= htmlspecialchars((string) $user['name']) ?>
                        </h2>

                        <p>
                            <?= htmlspecialchars((string) $user['email']) ?>
                        </p>

                    </div>

                </div>


                <!-- Account Navigation -->

                <nav class="account-nav">

                    <a
                        href="<?= BASE_URL ?>account.php"
                        class="active"
                    >
                        Profile
                    </a>

                    <a
                        href="<?= BASE_URL ?>orders.php"
                    >
                        Orders
                    </a>

                    <a
                        href="<?= BASE_URL ?>wishlist.php"
                    >
                        Wishlist
                    </a>

                    <a
                        href="<?= BASE_URL ?>logout.php"
                    >
                        Logout
                    </a>

                </nav>

            </aside>


            <!-- =====================================
                 MAIN CONTENT
            ====================================== -->

            <div class="account-content">


                <!-- =================================
                     PERSONAL INFORMATION
                ================================== -->

                <div class="account-card">

                    <h2>
                        Personal information
                    </h2>


                    <div class="personal-info-grid">


                        <!-- Full Name -->

                        <div class="personal-info-item">

                            <p class="info-label">
                                Full name
                            </p>

                            <p class="info-value">
                                <?= htmlspecialchars((string) $user['name']) ?>
                            </p>

                        </div>


                        <!-- Email -->

                        <div class="personal-info-item">

                            <p class="info-label">
                                Email
                            </p>

                            <p class="info-value">
                                <?= htmlspecialchars((string) $user['email']) ?>
                            </p>

                        </div>


                        <!-- Phone -->

                        <div class="personal-info-item">

                            <p class="info-label">
                                Phone
                            </p>

                            <p class="info-value">
                                <?= htmlspecialchars(
                                    (string) (
                                        $user['phone'] ?? 'Not provided'
                                    )
                                ) ?>
                            </p>

                        </div>


                        <!-- City -->

                        <div class="personal-info-item">

                            <p class="info-label">
                                City
                            </p>

                            <p class="info-value">
                                <?= htmlspecialchars(
                                    (string) (
                                        $user['city'] ?? 'Not provided'
                                    )
                                ) ?>
                            </p>

                        </div>


                        <!-- Address -->

                        <div class="personal-info-item full-width">

                            <p class="info-label">
                                Address
                            </p>

                            <p class="info-value">
                                <?= htmlspecialchars(
                                    (string) (
                                        $user['address'] ?? 'Not provided'
                                    )
                                ) ?>
                            </p>

                        </div>

                    </div>

                </div>


                <!-- =================================
                     ORDER SUMMARY
                ================================== -->

                <div class="account-card">

                    <h2>
                        Order summary
                    </h2>


                    <div class="order-summary-box">

                        <div class="order-summary-row">

                            <span class="order-summary-label">
                                Active orders
                            </span>

                            <span class="order-summary-number">
                                0
                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


<?php require_once __DIR__ . '/includes/footer.php'; ?>