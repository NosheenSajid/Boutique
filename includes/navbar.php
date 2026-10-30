<?php

$navCategories = [];

if (isset($pdo) && $pdo instanceof PDO) {

    try {

        $navCategoriesStmt = $pdo->prepare(
            'SELECT id, name, slug
             FROM categories
             WHERE status = 1
             ORDER BY name ASC
             LIMIT 6'
        );

        $navCategoriesStmt->execute();

        $navCategories = $navCategoriesStmt->fetchAll();

    } catch (Throwable $e) {

        $navCategories = [];

    }

}


$cartCount = 0;

if (isset($pdo) && $pdo instanceof PDO) {

    try {

        $userId = isLoggedIn()
            ? (int) $_SESSION['user']['id']
            : null;

        $sessionId = session_id();


        if ($userId !== null) {

            $countStmt = $pdo->prepare(
                'SELECT COALESCE(SUM(ci.quantity), 0) AS total
                 FROM cart_items ci
                 INNER JOIN cart c ON c.id = ci.cart_id
                 WHERE c.user_id = :user_id'
            );

            $countStmt->execute([
                'user_id' => $userId
            ]);

        } else {

            $countStmt = $pdo->prepare(
                'SELECT COALESCE(SUM(ci.quantity), 0) AS total
                 FROM cart_items ci
                 INNER JOIN cart c ON c.id = ci.cart_id
                 WHERE c.session_id = :session_id'
            );

            $countStmt->execute([
                'session_id' => $sessionId
            ]);

        }


        $cartCountRow = $countStmt->fetch();

        if ($cartCountRow) {

            $cartCount = (int) (
                $cartCountRow['total'] ?? 0
            );

        }

    } catch (Throwable $e) {

        $cartCount = 0;

    }

}

?>

<style>

/* =========================================
   NAVBAR
========================================= */

.store-navbar {
    position: sticky;
    top: 0;
    z-index: 50;
    width: 100%;
    border-bottom: 1px solid #e5e7eb;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}

.navbar-container {
    width: 100%;
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 16px;
    box-sizing: border-box;
}

.navbar-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    min-height: 80px;
}


/* =========================================
   BRAND + NAVIGATION
========================================= */

.navbar-left {
    display: flex;
    align-items: center;
    gap: 24px;
    min-width: 0;
}

.navbar-brand {
    color: #111827;
    font-size: 20px;
    font-weight: 900;
    line-height: 1;
    letter-spacing: 0.18em;
    text-decoration: none;
    text-transform: uppercase;
    white-space: nowrap;
}

.navbar-links {
    display: flex;
    align-items: center;
    gap: 24px;
}

.navbar-link {
    color: #374151;
    font-size: 14px;
    font-weight: 500;
    line-height: 1.5;
    text-decoration: none;
    white-space: nowrap;
    transition: color 0.2s ease;
}

.navbar-link:hover {
    color: #000000;
}


/* =========================================
   RIGHT SIDE ACTIONS
========================================= */

.navbar-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}


/* =========================================
   ICON BUTTONS
========================================= */

.navbar-icon-button {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    padding: 10px;
    box-sizing: border-box;
    border: 1px solid #e5e7eb;
    border-radius: 50%;
    background: #ffffff;
    color: #374151;
    font-size: 17px;
    line-height: 1;
    text-decoration: none;
    cursor: pointer;
    transition:
        background-color 0.2s ease,
        color 0.2s ease,
        border-color 0.2s ease;
}

.navbar-icon-button:hover {
    background: #f9fafb;
    color: #000000;
}


/* =========================================
   CART BADGE
========================================= */

.cart-count {
    position: absolute;
    top: -4px;
    right: -4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    min-height: 20px;
    padding: 2px 4px;
    border-radius: 50%;
    background: #000000;
    color: #ffffff;
    font-size: 10px;
    font-weight: 600;
    line-height: 1;
}


/* =========================================
   SEARCH
========================================= */

.search-wrapper {
    position: relative;
}

.search-form {
    position: absolute;
    top: 56px;
    right: 0;
    z-index: 50;
    width: 288px;
    padding: 12px;
    box-sizing: border-box;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
}

.search-form.hidden {
    display: none;
}

.search-row {
    display: flex;
    gap: 8px;
}

.search-input {
    min-width: 0;
    flex: 1;
    width: 100%;
    padding: 8px 12px;
    box-sizing: border-box;
    border: 1px solid #d1d5db;
    border-radius: 12px;
    background: #ffffff;
    color: #111827;
    font-size: 14px;
    line-height: 1.5;
    outline: none;
}

.search-input:focus {
    border-color: #111827;
}

.search-input::placeholder {
    color: #9ca3af;
}

.search-button {
    flex-shrink: 0;
    padding: 8px 16px;
    border: 0;
    border-radius: 12px;
    background: #000000;
    color: #ffffff;
    font-size: 14px;
    font-weight: 600;
    line-height: 1.5;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.search-button:hover {
    background: #1f2937;
}


/* =========================================
   ACCOUNT BUTTONS
========================================= */

.navbar-account-button,
.navbar-login-button,
.navbar-logout-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 16px;
    border-radius: 999px;
    font-size: 14px;
    font-weight: 500;
    line-height: 1.5;
    text-decoration: none;
    white-space: nowrap;
    transition:
        background-color 0.2s ease,
        color 0.2s ease,
        border-color 0.2s ease;
}

.navbar-account-button {
    border: 1px solid #d1d5db;
    background: #ffffff;
    color: #111827;
}

.navbar-account-button:hover {
    background: #f9fafb;
}

.navbar-login-button,
.navbar-logout-button {
    border: 1px solid #000000;
    background: #000000;
    color: #ffffff;
}

.navbar-login-button:hover,
.navbar-logout-button:hover {
    background: #1f2937;
    border-color: #1f2937;
}


/* =========================================
   ACCESSIBILITY
========================================= */

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}


/* =========================================
   TABLET
========================================= */

@media (max-width: 1100px) {

    .navbar-left {
        gap: 18px;
    }

    .navbar-links {
        gap: 16px;
    }

    .navbar-link {
        font-size: 13px;
    }

}


/* =========================================
   SMALL TABLET
========================================= */

@media (max-width: 900px) {

    .navbar-links {
        display: none;
    }

}


/* =========================================
   MOBILE
========================================= */

@media (max-width: 640px) {

    .navbar-container {
        padding-left: 12px;
        padding-right: 12px;
    }

    .navbar-inner {
        min-height: 68px;
        gap: 8px;
    }

    .navbar-left {
        gap: 0;
    }

    .navbar-brand {
        font-size: 17px;
        letter-spacing: 0.13em;
    }

    .navbar-actions {
        gap: 6px;
    }

    .navbar-icon-button {
        width: 38px;
        height: 38px;
        padding: 9px;
        font-size: 15px;
    }

    .navbar-account-button,
    .navbar-login-button,
    .navbar-logout-button {
        display: none;
    }

    .search-form {
        top: 50px;
        right: -5px;
        width: calc(100vw - 24px);
        max-width: 320px;
    }

}


/* =========================================
   VERY SMALL MOBILE
========================================= */

@media (max-width: 400px) {

    .navbar-brand {
        font-size: 15px;
        letter-spacing: 0.1em;
    }

    .navbar-icon-button {
        width: 36px;
        height: 36px;
    }

}

</style>


<!-- =========================================
     NAVIGATION
========================================== -->

<nav class="store-navbar">

    <div class="navbar-container">

        <div class="navbar-inner">


            <!-- =================================
                 LEFT SIDE
            ================================== -->

            <div class="navbar-left">


                <!-- Brand -->

                <a
                    href="<?= BASE_URL ?>"
                    class="navbar-brand"
                >
                    Clothing
                </a>


                <!-- Desktop Navigation -->

                <div class="navbar-links">


                    <a
                        href="<?= BASE_URL ?>"
                        class="navbar-link"
                    >
                        Home
                    </a>


                    <a
                        href="<?= BASE_URL ?>shop.php"
                        class="navbar-link"
                    >
                        Shop
                    </a>


                    <?php foreach ($navCategories as $category): ?>

                        <a
                            href="<?= BASE_URL ?>shop.php?category=<?= urlencode((string) ($category['slug'] ?? '')) ?>"
                            class="navbar-link"
                        >
                            <?= htmlspecialchars(
                                (string) (
                                    $category['name'] ?? 'Shop'
                                )
                            ) ?>
                        </a>

                    <?php endforeach; ?>


                    <a
                        href="<?= BASE_URL ?>about.php"
                        class="navbar-link"
                    >
                        About
                    </a>


                    <a
                        href="<?= BASE_URL ?>contact.php"
                        class="navbar-link"
                    >
                        Contact
                    </a>

                </div>

            </div>


            <!-- =================================
                 RIGHT SIDE
            ================================== -->

            <div class="navbar-actions">


                <!-- =================================
                     SEARCH
                ================================== -->

                <div class="search-wrapper">

                    <button
                        type="button"
                        id="search-toggle"
                        class="navbar-icon-button"
                        aria-label="Search"
                        aria-expanded="false"
                    >
                        🔍
                    </button>


                    <form
                        id="search-form"
                        method="get"
                        action="<?= BASE_URL ?>shop.php"
                        class="search-form hidden"
                    >

                        <label
                            for="site-search"
                            class="sr-only"
                        >
                            Search products
                        </label>


                        <div class="search-row">

                            <input
                                id="site-search"
                                type="search"
                                name="q"
                                class="search-input"
                                placeholder="Search products..."
                                autocomplete="off"
                            >


                            <button
                                type="submit"
                                class="search-button"
                            >
                                Search
                            </button>

                        </div>

                    </form>

                </div>


                <!-- =================================
                     WISHLIST
                ================================== -->

                <a
                    href="<?= BASE_URL ?>wishlist.php"
                    class="navbar-icon-button"
                    aria-label="Wishlist"
                >
                    ♡
                </a>


                <!-- =================================
                     CART
                ================================== -->

                <a
                    href="<?= BASE_URL ?>cart.php"
                    class="navbar-icon-button"
                    aria-label="Cart"
                >

                    🛒

                    <?php if ($cartCount > 0): ?>

                        <span class="cart-count">
                            <?= (int) $cartCount ?>
                        </span>

                    <?php endif; ?>

                </a>


                <!-- =================================
                     USER AUTHENTICATION
                ================================== -->

                <?php if (isLoggedIn()): ?>


                    <!-- Account -->

                    <a
                        href="<?= BASE_URL ?>account.php"
                        class="navbar-account-button"
                    >
                        Account
                    </a>


                    <!-- Logout -->

                    <a
                        href="<?= BASE_URL ?>logout.php"
                        class="navbar-logout-button"
                    >
                        Logout
                    </a>


                <?php else: ?>


                    <!-- Login -->

                    <a
                        href="<?= BASE_URL ?>login.php"
                        class="navbar-login-button"
                    >
                        Login
                    </a>


                <?php endif; ?>

            </div>

        </div>

    </div>

</nav>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const searchToggle =
            document.getElementById('search-toggle');

        const searchForm =
            document.getElementById('search-form');

        const searchInput =
            document.getElementById('site-search');


        if (!searchToggle || !searchForm) {
            return;
        }


        searchToggle.addEventListener(
            'click',
            function () {

                const isHidden =
                    searchForm.classList.toggle('hidden');

                searchToggle.setAttribute(
                    'aria-expanded',
                    String(!isHidden)
                );


                if (!isHidden && searchInput) {
                    searchInput.focus();
                }

            }
        );


        document.addEventListener(
            'click',
            function (event) {

                if (
                    !searchForm.contains(event.target) &&
                    !searchToggle.contains(event.target)
                ) {

                    searchForm.classList.add('hidden');

                    searchToggle.setAttribute(
                        'aria-expanded',
                        'false'
                    );

                }

            }
        );

    }
);

</script>