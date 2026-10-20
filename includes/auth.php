<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function loginUser(array $user): void
{
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'name' => (string) $user['name'],
        'email' => (string) $user['email'],
    ];
}

function logoutCurrentUser(): void
{
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
}

function isUserLoggedIn(): bool
{
    return isLoggedIn();
}

function currentLoggedUser(): ?array
{
    return currentUser();
}
