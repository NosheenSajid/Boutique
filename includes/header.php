<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/admin-auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_start();

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
        <?= htmlspecialchars(APP_NAME) ?>
    </title>


    <style>

        /* =========================================
           BASE STYLES
        ========================================= */

        * {
            box-sizing: border-box;
        }

        html {
            margin: 0;
            padding: 0;
        }

        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
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
           ANNOUNCEMENT BAR
        ========================================= */

        .announcement-bar {
            width: 100%;
            border-bottom: 1px solid #374151;
            background: #111827;
            color: #ffffff;
            text-align: center;
            font-size: 12px;
            font-weight: 500;
            line-height: 1.5;
            letter-spacing: 0.2em;
            text-transform: uppercase;
        }

        .announcement-container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 12px 16px;
            box-sizing: border-box;
        }


        /* =========================================
           MAIN CONTENT
        ========================================= */

        main {
            width: 100%;
        }


        /* =========================================
           RESPONSIVE - TABLET
        ========================================= */

        @media (max-width: 1024px) {

            .announcement-container {
                padding-left: 24px;
                padding-right: 24px;
            }

        }


        /* =========================================
           RESPONSIVE - MOBILE
        ========================================= */

        @media (max-width: 640px) {

            .announcement-bar {
                font-size: 10px;
                letter-spacing: 0.12em;
            }

            .announcement-container {
                padding: 10px 16px;
            }

        }


        /* =========================================
           VERY SMALL MOBILE
        ========================================= */

        @media (max-width: 400px) {

            .announcement-bar {
                font-size: 9px;
                letter-spacing: 0.08em;
            }

            .announcement-container {
                padding-left: 12px;
                padding-right: 12px;
            }

        }

    </style>

</head>


<body>

<?php

$announcementText =
    defined('STORE_ANNOUNCEMENT') && STORE_ANNOUNCEMENT !== ''
        ? STORE_ANNOUNCEMENT
        : 'Free delivery on eligible orders';

?>


<!-- =========================================
     ANNOUNCEMENT BAR
========================================== -->

<div class="announcement-bar">

    <div class="announcement-container">

        <?= htmlspecialchars(
            $announcementText,
            ENT_QUOTES,
            'UTF-8'
        ) ?>

    </div>

</div>


<?php require_once __DIR__ . '/navbar.php'; ?>


<main>