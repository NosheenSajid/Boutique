<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$dbHost = '127.0.0.1';
$dbName = 'clothing_store';
$dbUser = 'root';
$dbPassword = '';
$dbCharset = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $adminPdo = new PDO(
        "mysql:host={$dbHost};charset={$dbCharset}",
        $dbUser,
        $dbPassword,
        $options
    );

    $adminPdo->exec(
        "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    );

    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}",
        $dbUser,
        $dbPassword,
        $options
    );
} catch (PDOException $e) {
    die('Database connection failed.');
}