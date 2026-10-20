<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function db(): PDO
{
    global $pdo;

    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new RuntimeException('Database connection is not available.');
    }

    return $pdo;
}

function redirect(string $path): void
{
    if (preg_match('/^https?:\/\//', $path) === 1) {
        header('Location: ' . $path);
        exit;
    }

    $base = rtrim(BASE_URL, '/');
    $target = '/' . ltrim($path, '/');

    header('Location: ' . $base . $target);
    exit;
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['user']['id']);
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
}

function requireGuest(): void
{
    if (isLoggedIn()) {
        redirect('account.php');
    }
}

function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['flash'][$key] = $value;
        return $value;
    }

    $message = $_SESSION['flash'][$key] ?? null;

    if (isset($_SESSION['flash'][$key])) {
        unset($_SESSION['flash'][$key]);
    }

    return $message;
}

function ensurePasswordResetTable(): void
{
    global $pdo;

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS password_reset_tokens (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            token VARCHAR(255) NOT NULL UNIQUE,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_password_reset_tokens_user
                FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE
                ON UPDATE CASCADE,
            INDEX idx_password_reset_tokens_user (user_id),
            INDEX idx_password_reset_tokens_token (token),
            INDEX idx_password_reset_tokens_expires_at (expires_at)
        ) ENGINE=InnoDB'
    );
}

function ensureAdminPasswordResetTable(): void
{
    global $pdo;

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS admin_password_reset_tokens (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            admin_id INT UNSIGNED NOT NULL,
            token VARCHAR(255) NOT NULL UNIQUE,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_admin_password_reset_tokens_admin
                FOREIGN KEY (admin_id)
                REFERENCES admins(id)
                ON DELETE CASCADE
                ON UPDATE CASCADE,
            INDEX idx_admin_password_reset_tokens_admin (admin_id),
            INDEX idx_admin_password_reset_tokens_token (token),
            INDEX idx_admin_password_reset_tokens_expires_at (expires_at)
        ) ENGINE=InnoDB'
    );
}

function ensureProductReviewsTable(): void
{
    global $pdo;

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS product_reviews (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            product_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NOT NULL,
            rating TINYINT UNSIGNED NOT NULL,
            title VARCHAR(150) DEFAULT NULL,
            comment TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_product_reviews_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT fk_product_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
            UNIQUE KEY unique_product_review_user (product_id, user_id),
            INDEX idx_product_reviews_product (product_id)
        ) ENGINE=InnoDB'
    );
}

function createPasswordResetToken(string $email): ?string
{
    global $pdo;

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        return null;
    }

    $token = bin2hex(random_bytes(32));
    $expiresAt = (new DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s');

    $pdo->prepare('DELETE FROM password_reset_tokens WHERE user_id = :user_id')
        ->execute(['user_id' => (int) $user['id']]);

    $pdo->prepare('INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)')
        ->execute([
            'user_id' => (int) $user['id'],
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);

    return $token;
}

function getPasswordResetToken(string $token): ?array
{
    global $pdo;

    $token = trim($token);
    if ($token === '') {
        return null;
    }

    $stmt = $pdo->prepare(
        'SELECT prt.user_id, prt.expires_at, u.email
         FROM password_reset_tokens prt
         INNER JOIN users u ON u.id = prt.user_id
         WHERE prt.token = :token
         LIMIT 1'
    );
    $stmt->execute(['token' => $token]);
    $row = $stmt->fetch();

    if (!$row) {
        return null;
    }

    $expiresAt = new DateTimeImmutable((string) $row['expires_at']);
    $now = new DateTimeImmutable('now');

    if ($expiresAt < $now) {
        $pdo->prepare('DELETE FROM password_reset_tokens WHERE token = :token')->execute(['token' => $token]);
        return null;
    }

    return [
        'user_id' => (int) $row['user_id'],
        'email' => (string) $row['email'],
        'token' => $token,
    ];
}

function createAdminPasswordResetToken(string $email): ?string
{
    global $pdo;

    $stmt = $pdo->prepare('SELECT id FROM admins WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $admin = $stmt->fetch();

    if (!$admin) {
        return null;
    }

    $token = bin2hex(random_bytes(32));
    $expiresAt = (new DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s');

    $pdo->prepare('DELETE FROM admin_password_reset_tokens WHERE admin_id = :admin_id')
        ->execute(['admin_id' => (int) $admin['id']]);

    $pdo->prepare('INSERT INTO admin_password_reset_tokens (admin_id, token, expires_at) VALUES (:admin_id, :token, :expires_at)')
        ->execute([
            'admin_id' => (int) $admin['id'],
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);

    return $token;
}

function getAdminPasswordResetToken(string $token): ?array
{
    global $pdo;

    $token = trim($token);
    if ($token === '') {
        return null;
    }

    $stmt = $pdo->prepare(
        'SELECT aprt.admin_id, aprt.expires_at, a.email
         FROM admin_password_reset_tokens aprt
         INNER JOIN admins a ON a.id = aprt.admin_id
         WHERE aprt.token = :token
         LIMIT 1'
    );
    $stmt->execute(['token' => $token]);
    $row = $stmt->fetch();

    if (!$row) {
        return null;
    }

    $expiresAt = new DateTimeImmutable((string) $row['expires_at']);
    $now = new DateTimeImmutable('now');

    if ($expiresAt < $now) {
        $pdo->prepare('DELETE FROM admin_password_reset_tokens WHERE token = :token')->execute(['token' => $token]);
        return null;
    }

    return [
        'admin_id' => (int) $row['admin_id'],
        'email' => (string) $row['email'],
        'token' => $token,
    ];
}

function resetPasswordWithToken(string $token, string $newPassword): bool
{
    global $pdo;

    $tokenData = getPasswordResetToken($token);
    if (!$tokenData) {
        return false;
    }

    $newPassword = trim($newPassword);
    if ($newPassword === '') {
        return false;
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $pdo->beginTransaction();
    try {
        $pdo->prepare('UPDATE users SET password = :password WHERE id = :id')
            ->execute([
                'password' => $hashedPassword,
                'id' => $tokenData['user_id'],
            ]);

        $pdo->prepare('DELETE FROM password_reset_tokens WHERE token = :token')
            ->execute(['token' => $token]);

        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        $pdo->rollBack();
        return false;
    }
}

function resetAdminPasswordWithToken(string $token, string $newPassword): bool
{
    global $pdo;

    $tokenData = getAdminPasswordResetToken($token);
    if (!$tokenData) {
        return false;
    }

    $newPassword = trim($newPassword);
    if ($newPassword === '') {
        return false;
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $pdo->beginTransaction();
    try {
        $pdo->prepare('UPDATE admins SET password = :password WHERE id = :id')
            ->execute([
                'password' => $hashedPassword,
                'id' => $tokenData['admin_id'],
            ]);

        $pdo->prepare('DELETE FROM admin_password_reset_tokens WHERE token = :token')
            ->execute(['token' => $token]);

        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        $pdo->rollBack();
        return false;
    }
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

function verifyCsrf(?string $token): bool
{
    $expected = $_SESSION['csrf_token'] ?? null;

    return is_string($expected) && is_string($token) && hash_equals($expected, $token);
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/i', '-', $value) ?? $value;
    $value = trim((string) $value, '-');

    return $value !== '' ? $value : 'item';
}

function formatMoney($amount): string
{
    return 'PKR ' . number_format((float) $amount, 2);
}

function getCurrentCartId(): int
{
    global $pdo;

    $userId = isLoggedIn() ? (int) $_SESSION['user']['id'] : null;
    $sessionId = session_id();

    if ($userId !== null) {
        $stmt = $pdo->prepare('SELECT id FROM cart WHERE user_id = :user_id LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();

        if ($row) {
            return (int) $row['id'];
        }

        $stmt = $pdo->prepare('INSERT INTO cart (user_id, session_id) VALUES (:user_id, :session_id)');
        $stmt->execute([
            'user_id' => $userId,
            'session_id' => $sessionId,
        ]);

        return (int) $pdo->lastInsertId();
    }

    $stmt = $pdo->prepare('SELECT id FROM cart WHERE session_id = :session_id LIMIT 1');
    $stmt->execute(['session_id' => $sessionId]);
    $row = $stmt->fetch();

    if ($row) {
        return (int) $row['id'];
    }

    $stmt = $pdo->prepare('INSERT INTO cart (session_id) VALUES (:session_id)');
    $stmt->execute(['session_id' => $sessionId]);

    return (int) $pdo->lastInsertId();
}

function addToCart(int $productId, int $quantity = 1): bool
{
    global $pdo;

    if ($productId <= 0 || $quantity <= 0) {
        return false;
    }

    $cartId = getCurrentCartId();

    $stmt = $pdo->prepare('SELECT id, quantity FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id LIMIT 1');
    $stmt->execute([
        'cart_id' => $cartId,
        'product_id' => $productId,
    ]);
    $item = $stmt->fetch();

    if ($item) {
        $newQty = (int) $item['quantity'] + $quantity;
        $stmt = $pdo->prepare('UPDATE cart_items SET quantity = :quantity, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            'quantity' => $newQty,
            'id' => (int) $item['id'],
        ]);
        return true;
    }

    $stmt = $pdo->prepare('INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (:cart_id, :product_id, :quantity)');
    $stmt->execute([
        'cart_id' => $cartId,
        'product_id' => $productId,
        'quantity' => $quantity,
    ]);

    return true;
}

function setCartQuantity(int $productId, int $quantity): bool
{
    global $pdo;

    if ($productId <= 0 || $quantity <= 0) {
        removeFromCart($productId);
        return false;
    }

    $cartId = getCurrentCartId();

    $stmt = $pdo->prepare('SELECT id FROM cart_items WHERE cart_id = :cart_id AND product_id = :product_id LIMIT 1');
    $stmt->execute([
        'cart_id' => $cartId,
        'product_id' => $productId,
    ]);
    $item = $stmt->fetch();

    if (!$item) {
        return addToCart($productId, $quantity);
    }

    $stmt = $pdo->prepare('UPDATE cart_items SET quantity = :quantity, updated_at = NOW() WHERE id = :id');
    $stmt->execute([
        'quantity' => $quantity,
        'id' => (int) $item['id'],
    ]);

    return true;
}

function removeFromCart(int $productId): void
{
    global $pdo;

    $stmt = $pdo->prepare('DELETE ci FROM cart_items ci INNER JOIN cart c ON c.id = ci.cart_id WHERE c.id = :cart_id AND ci.product_id = :product_id');
    $stmt->execute([
        'cart_id' => getCurrentCartId(),
        'product_id' => $productId,
    ]);
}

function getCartItems(): array
{
    global $pdo;

    $cartId = getCurrentCartId();

    $stmt = $pdo->prepare(
        'SELECT ci.id AS cart_item_id, ci.product_id, ci.quantity, p.name, p.price, p.sale_price, p.main_image
         FROM cart_items ci
         INNER JOIN products p ON p.id = ci.product_id
         WHERE ci.cart_id = :cart_id
         ORDER BY ci.id DESC'
    );
    $stmt->execute(['cart_id' => $cartId]);

    return $stmt->fetchAll();
}

function getWishlistItems(): array
{
    global $pdo;

    if (!isLoggedIn()) {
        return [];
    }

    $userId = (int) $_SESSION['user']['id'];

    $stmt = $pdo->prepare(
        'SELECT wi.id, wi.product_id, p.name, p.price, p.sale_price, p.main_image
         FROM wishlist_items wi
         INNER JOIN wishlists w ON w.id = wi.wishlist_id
         INNER JOIN products p ON p.id = wi.product_id
         WHERE w.user_id = :user_id
         ORDER BY wi.id DESC'
    );
    $stmt->execute(['user_id' => $userId]);

    return $stmt->fetchAll();
}

function ensureWishlistForUser(): int
{
    global $pdo;

    if (!isLoggedIn()) {
        throw new RuntimeException('User must be logged in to manage wishlist.');
    }

    $userId = (int) $_SESSION['user']['id'];
    $stmt = $pdo->prepare('SELECT id FROM wishlists WHERE user_id = :user_id LIMIT 1');
    $stmt->execute(['user_id' => $userId]);
    $row = $stmt->fetch();

    if ($row) {
        return (int) $row['id'];
    }

    $stmt = $pdo->prepare('INSERT INTO wishlists (user_id) VALUES (:user_id)');
    $stmt->execute(['user_id' => $userId]);

    return (int) $pdo->lastInsertId();
}

function addToWishlist(int $productId): bool
{
    global $pdo;

    if (!isLoggedIn() || $productId <= 0) {
        return false;
    }

    $wishlistId = ensureWishlistForUser();

    $stmt = $pdo->prepare('SELECT id FROM wishlist_items WHERE wishlist_id = :wishlist_id AND product_id = :product_id LIMIT 1');
    $stmt->execute([
        'wishlist_id' => $wishlistId,
        'product_id' => $productId,
    ]);

    if ($stmt->fetch()) {
        return true;
    }

    $stmt = $pdo->prepare('INSERT INTO wishlist_items (wishlist_id, product_id) VALUES (:wishlist_id, :product_id)');
    $stmt->execute([
        'wishlist_id' => $wishlistId,
        'product_id' => $productId,
    ]);

    return true;
}

function removeFromWishlist(int $productId): void
{
    global $pdo;

    if (!isLoggedIn()) {
        return;
    }

    $wishlistId = ensureWishlistForUser();
    $stmt = $pdo->prepare('DELETE FROM wishlist_items WHERE wishlist_id = :wishlist_id AND product_id = :product_id');
    $stmt->execute([
        'wishlist_id' => $wishlistId,
        'product_id' => $productId,
    ]);
}
