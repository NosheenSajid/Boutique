<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function csrf_token(): string
{
    return csrfToken();
}

function csrf_field(): string
{
    return csrfField();
}

function verify_csrf(?string $token): bool
{
    return verifyCsrf($token);
}
