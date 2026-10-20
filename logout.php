<?php

declare(strict_types=1);

require_once __DIR__ . '/config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

$destination = isset($_GET['type']) && $_GET['type'] === 'admin' ? 'admin/admin-login.php' : 'login.php';
header('Location: ' . BASE_URL . $destination);
exit;
