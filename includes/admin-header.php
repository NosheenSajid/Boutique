<?php

declare(strict_types=1);


require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/admin-auth.php';

ensureAdminAccountColumns();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_start();

$currentPage = basename($_SERVER['PHP_SELF']);
$adminLoggedIn = isAdminLoggedIn();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Admin | <?= htmlspecialchars(APP_NAME) ?>
    </title>

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>assets/css/store.css"
    >

    <style>

        /* =========================================
           RESET / BASE
        ========================================= */

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            background: #f6f6f7;
            color: #111827;
            font-family:
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                Helvetica,
                Arial,
                sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        a {
            color: inherit;
        }


        /* =========================================
           PAGE WRAPPER
        ========================================= */

        .admin-page {
            min-height: 100vh;
        }


        /* =========================================
           ADMIN HEADER
        ========================================= */

        .admin-header {
            position: sticky;
            top: 0;
            z-index: 50;
            width: 100%;
            height: 64px;
            border-bottom: 1px solid #e5e7eb;
            background: #ffffff;
        }

        .admin-header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            height: 64px;
            padding: 0 16px;
        }


        /* =========================================
           BRAND
        ========================================= */

        .admin-brand-area {
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 0;
        }

        .admin-brand {
            color: #030712;
            font-size: 18px;
            font-weight: 900;
            line-height: 1;
            letter-spacing: 0.16em;
            text-decoration: none;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .admin-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            background: #f3f4f6;
            color: #6b7280;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.4;
        }


        /* =========================================
           HEADER RIGHT SIDE
        ========================================= */

        .admin-header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #374151;
            font-size: 14px;
        }

        .view-store-link {
            color: #4b5563;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .view-store-link:hover {
            color: #030712;
        }

        .admin-user-name {
            color: #6b7280;
            font-size: 14px;
            white-space: nowrap;
        }


        /* =========================================
           HEADER BUTTONS
        ========================================= */

        .admin-header-button {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 8px;
            background: #111827;
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.5;
            text-decoration: none;
            transition:
                background-color 0.2s ease,
                color 0.2s ease;
        }

        .admin-header-button:hover {
            background: #374151;
        }


        /* =========================================
           DESKTOP SIDEBAR
        ========================================= */

        .admin-sidebar {
            position: fixed;
            top: 64px;
            bottom: 0;
            left: 0;
            z-index: 40;
            display: block;
            width: 256px;
            border-right: 1px solid #e5e7eb;
            background: #ffffff;
            overflow-y: auto;
        }

        .admin-sidebar-nav {
            padding: 16px;
            font-size: 14px;
            font-weight: 500;
        }


        /* =========================================
           SIDEBAR SECTION LABEL
        ========================================= */

        .admin-nav-label {
            margin: 0 0 12px;
            padding: 0 12px;
            color: #9ca3af;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.4;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .admin-nav-label.settings-label {
            margin-top: 32px;
        }


        /* =========================================
           SIDEBAR LINKS
        ========================================= */

        .admin-nav-link {
            display: block;
            margin: 0;
            padding: 10px 12px;
            border-radius: 8px;
            color: #4b5563;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.5;
            text-decoration: none;
            transition:
                background-color 0.2s ease,
                color 0.2s ease;
        }

        .admin-nav-link:hover {
            background: #f3f4f6;
            color: #030712;
        }

        .admin-nav-link.active {
            background: #111827;
            color: #ffffff;
        }

        .admin-nav-link.active:hover {
            background: #1f2937;
            color: #ffffff;
        }


        /* =========================================
           MOBILE NAVIGATION
        ========================================= */

        .admin-mobile-nav {
            display: none;
            gap: 4px;
            width: 100%;
            padding: 8px 16px;
            border-bottom: 1px solid #e5e7eb;
            background: #ffffff;
            overflow-x: auto;
            scrollbar-width: thin;
        }

        .admin-mobile-link {
            display: block;
            flex-shrink: 0;
            padding: 8px 12px;
            border-radius: 8px;
            color: #4b5563;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.5;
            text-decoration: none;
            white-space: nowrap;
        }

        .admin-mobile-link:hover {
            background: #f3f4f6;
            color: #111827;
        }

        .admin-mobile-link.active {
            background: #111827;
            color: #ffffff;
        }


        /* =========================================
           MAIN CONTENT
        ========================================= */

        .admin-main {
            min-height: calc(100vh - 64px);
            margin-left: 256px;
        }

        .admin-main-no-sidebar {
            min-height: calc(100vh - 64px);
            margin-left: 0;
        }


        /* =========================================
           RESPONSIVE - TABLET
        ========================================= */

        @media (max-width: 1023px) {

            .admin-sidebar {
                display: none;
            }

            .admin-mobile-nav {
                display: flex;
            }

            .admin-main {
                margin-left: 0;
            }

            .admin-main-no-sidebar {
                margin-left: 0;
            }
        }


        /* =========================================
           RESPONSIVE - MOBILE
        ========================================= */

        @media (max-width: 640px) {

            .admin-header,
            .admin-header-inner {
                height: 60px;
            }

            .admin-header-inner {
                padding: 0 16px;
            }

            .admin-brand-area {
                gap: 10px;
            }

            .admin-brand {
                font-size: 16px;
                letter-spacing: 0.12em;
            }

            .admin-badge {
                display: none;
            }

            .view-store-link {
                display: none;
            }

            .admin-user-name {
                display: none;
            }

            .admin-header-actions {
                gap: 8px;
            }

            .admin-header-button {
                padding: 7px 10px;
                font-size: 13px;
            }

            .admin-mobile-nav {
                padding-left: 12px;
                padding-right: 12px;
            }

            .admin-mobile-link {
                padding: 8px 10px;
                font-size: 13px;
            }

            .admin-main,
            .admin-main-no-sidebar {
                min-height: calc(100vh - 60px);
            }
        }


        /* =========================================
           VERY SMALL MOBILE
        ========================================= */

        @media (max-width: 400px) {

            .admin-header-inner {
                padding: 0 12px;
            }

            .admin-brand {
                font-size: 14px;
            }

            .admin-header-button {
                padding: 6px 9px;
                font-size: 12px;
            }

            .admin-mobile-nav {
                padding-left: 8px;
                padding-right: 8px;
            }

        }

    </style>

</head>


<body>

    <div class="admin-page">


        <!-- =========================================
             ADMIN HEADER
        ========================================== -->

        <header class="admin-header">

            <div class="admin-header-inner">


                <!-- =================================
                     BRAND
                ================================== -->

                <div class="admin-brand-area">

                    <a
                        href="<?= BASE_URL ?>admin/admin-dashboard.php"
                        class="admin-brand"
                    >
                        Clothing
                    </a>


                    <?php if ($adminLoggedIn): ?>

                        <span class="admin-badge">
                            Admin
                        </span>

                    <?php endif; ?>

                </div>


                <!-- =================================
                     HEADER ACTIONS
                ================================== -->

                <div class="admin-header-actions">


                    <!-- View Store -->

                    <a
                        href="<?= BASE_URL ?>"
                        class="view-store-link"
                    >
                        View store
                    </a>


                    <?php if ($adminLoggedIn): ?>

                        <!-- Admin Name -->

                        <span class="admin-user-name">
                            <?= htmlspecialchars(
                                (string) (
                                    $_SESSION['admin']['name'] ?? 'Admin'
                                )
                            ) ?>
                        </span>


                        <!-- Logout -->

                        <a
                            href="<?= BASE_URL ?>logout.php?type=admin"
                            class="admin-header-button"
                        >
                            Log out
                        </a>


                    <?php else: ?>


                        <!-- Admin Login -->

                        <a
                            href="<?= BASE_URL ?>admin/admin-login.php"
                            class="admin-header-button"
                        >
                            Admin login
                        </a>


                    <?php endif; ?>

                </div>

            </div>

        </header>


        <?php if ($adminLoggedIn): ?>


            <!-- =========================================
                 DESKTOP SIDEBAR
            ========================================== -->

            <aside class="admin-sidebar">

                <nav class="admin-sidebar-nav">


                    <!-- Manage Store -->

                    <p class="admin-nav-label">
                        Manage store
                    </p>


                    <!-- Dashboard -->

                    <a
                        href="<?= BASE_URL ?>admin/admin-dashboard.php"
                        class="admin-nav-link <?= $currentPage === 'admin-dashboard.php' ? 'active' : '' ?>"
                    >
                        Overview
                    </a>


                    <!-- Products -->

                    <a
                        href="<?= BASE_URL ?>admin/admin-products.php"
                        class="admin-nav-link <?= $currentPage === 'admin-products.php' ? 'active' : '' ?>"
                    >
                        Products
                    </a>


                    <!-- Orders -->

                    <a
                        href="<?= BASE_URL ?>admin/admin-orders.php"
                        class="admin-nav-link <?= $currentPage === 'admin-orders.php' ? 'active' : '' ?>"
                    >
                        Orders
                    </a>


                    <!-- Settings -->

                    <p class="admin-nav-label settings-label">
                        Settings
                    </p>


                    <!-- Account -->

                    <a
                        href="<?= BASE_URL ?>admin/admin-account.php"
                        class="admin-nav-link <?= $currentPage === 'admin-account.php' ? 'active' : '' ?>"
                    >
                        Account
                    </a>


                    <?php if (isAdminOwner()): ?>

                        <!-- Add Assistant -->

                        <a
                            href="<?= BASE_URL ?>admin/admin-register.php"
                            class="admin-nav-link"
                        >
                            Add assistant
                        </a>

                    <?php endif; ?>

                </nav>

            </aside>


            <!-- =========================================
                 MOBILE NAVIGATION
            ========================================== -->

            <nav class="admin-mobile-nav">


                <!-- Overview -->

                <a
                    href="<?= BASE_URL ?>admin/admin-dashboard.php"
                    class="admin-mobile-link <?= $currentPage === 'admin-dashboard.php' ? 'active' : '' ?>"
                >
                    Overview
                </a>


                <!-- Products -->

                <a
                    href="<?= BASE_URL ?>admin/admin-products.php"
                    class="admin-mobile-link <?= $currentPage === 'admin-products.php' ? 'active' : '' ?>"
                >
                    Products
                </a>


                <!-- Orders -->

                <a
                    href="<?= BASE_URL ?>admin/admin-orders.php"
                    class="admin-mobile-link <?= $currentPage === 'admin-orders.php' ? 'active' : '' ?>"
                >
                    Orders
                </a>


                <!-- Account -->

                <a
                    href="<?= BASE_URL ?>admin/admin-account.php"
                    class="admin-mobile-link <?= $currentPage === 'admin-account.php' ? 'active' : '' ?>"
                >
                    Account
                </a>

            </nav>


            <!-- =========================================
                 MAIN CONTENT - LOGGED IN
            ========================================== -->

            <main class="admin-main">


        <?php else: ?>


            <!-- =========================================
                 MAIN CONTENT - NOT LOGGED IN
            ========================================== -->

            <main class="admin-main-no-sidebar">


        <?php endif; ?>