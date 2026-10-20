</main>

<style>

/* =========================================
   FOOTER
========================================= */

.site-footer {
    margin-top: 80px;
    border-top: 1px solid #1f2937;
    background: #030712;
    color: #ffffff;
}

.footer-container {
    width: 100%;
    max-width: 1280px;
    margin: 0 auto;
    padding: 48px 16px;
    box-sizing: border-box;
}


/* =========================================
   FOOTER GRID
========================================= */

.footer-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 40px;
}


/* =========================================
   FOOTER BRAND
========================================= */

.footer-brand h2 {
    margin: 0;
    color: #ffffff;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.4;
}

.footer-brand p {
    max-width: 384px;
    margin: 12px 0 0;
    color: #9ca3af;
    font-size: 14px;
    line-height: 1.5;
}


/* =========================================
   FOOTER HEADINGS
========================================= */

.footer-column h3 {
    margin: 0;
    color: #ffffff;
    font-size: 14px;
    font-weight: 600;
    line-height: 1.5;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}


/* =========================================
   FOOTER LINKS
========================================= */

.footer-links {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 16px;
}

.footer-links a {
    display: block;
    color: #9ca3af;
    font-size: 14px;
    line-height: 1.5;
    text-decoration: none;
    transition: color 0.2s ease;
}

.footer-links a:hover {
    color: #ffffff;
}


/* =========================================
   FOOTER COPYRIGHT
========================================= */

.footer-bottom {
    margin-top: 40px;
    padding-top: 24px;
    border-top: 1px solid #1f2937;
}

.footer-bottom p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
    line-height: 1.5;
}


/* =========================================
   TABLET
========================================= */

@media (max-width: 768px) {

    .footer-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 32px;
    }

    .footer-brand {
        grid-column: span 2;
    }

}


/* =========================================
   MOBILE
========================================= */

@media (max-width: 640px) {

    .site-footer {
        margin-top: 48px;
    }

    .footer-container {
        padding: 40px 16px;
    }

    .footer-grid {
        grid-template-columns: 1fr;
        gap: 32px;
    }

    .footer-brand {
        grid-column: span 1;
    }

    .footer-brand p {
        max-width: 100%;
    }

    .footer-bottom {
        margin-top: 32px;
        padding-top: 20px;
    }

}


/* =========================================
   VERY SMALL MOBILE
========================================= */

@media (max-width: 400px) {

    .footer-container {
        padding-left: 12px;
        padding-right: 12px;
    }

}

</style>


<footer class="site-footer">

    <div class="footer-container">


        <!-- =========================================
             FOOTER GRID
        ========================================== -->

        <div class="footer-grid">


            <!-- =====================================
                 BRAND
            ====================================== -->

            <div class="footer-brand">

                <h2>
                    CLOTHING
                </h2>

                <p>
                    Modern fashion designed for everyday confidence.
                    Discover clothing you'll love to wear.
                </p>

            </div>


            <!-- =====================================
                 QUICK LINKS
            ====================================== -->

            <div class="footer-column">

                <h3>
                    Quick Links
                </h3>

                <div class="footer-links">

                    <a href="<?= BASE_URL ?>">
                        Home
                    </a>

                    <a href="<?= BASE_URL ?>shop.php">
                        Shop
                    </a>

                    <a href="<?= BASE_URL ?>about.php">
                        About
                    </a>

                    <a href="<?= BASE_URL ?>contact.php">
                        Contact
                    </a>

                </div>

            </div>


            <!-- =====================================
                 CUSTOMER LINKS
            ====================================== -->

            <div class="footer-column">

                <h3>
                    Customer
                </h3>

                <div class="footer-links">

                    <a href="<?= BASE_URL ?>login.php">
                        Login
                    </a>

                    <a href="<?= BASE_URL ?>register.php">
                        Register
                    </a>

                    <a href="<?= BASE_URL ?>cart.php">
                        Shopping Cart
                    </a>

                </div>

            </div>

        </div>


        <!-- =========================================
             COPYRIGHT
        ========================================== -->

        <div class="footer-bottom">

            <p>
                &copy; <?= date('Y') ?>
                <?= htmlspecialchars(APP_NAME) ?>.
                All rights reserved.
            </p>

        </div>

    </div>

</footer>


</body>
</html>