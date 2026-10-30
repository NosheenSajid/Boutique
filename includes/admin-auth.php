<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function ensureAdminAccountColumns(): void
{
    global $pdo;

    $columns = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admins' AND COLUMN_NAME IN ('role', 'created_by')")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('role', $columns, true)) {
        $pdo->exec("ALTER TABLE admins ADD COLUMN role ENUM('owner', 'assistant') NOT NULL DEFAULT 'owner' AFTER password");
    }

    if (!in_array('created_by', $columns, true)) {
        $pdo->exec('ALTER TABLE admins ADD COLUMN created_by INT UNSIGNED DEFAULT NULL AFTER role');
    }
}

function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin']['id']);
}

function isAdminOwner(): bool
{
    return isAdminLoggedIn() && ($_SESSION['admin']['role'] ?? 'owner') === 'owner';
}

function requireAdminLogin(): void
{
    if (!isAdminLoggedIn()) {
        redirect('admin/admin-login.php');
    }
}

function requireAdminOwner(): void
{
    requireAdminLogin();

    if (!isAdminOwner()) {
        flash('error', 'Only the store owner can manage assistant accounts.');
        redirect('admin/admin-dashboard.php');
    }
}

function logoutAdmin(): void
{
    $_SESSION['admin'] = [];
    session_destroy();
}
