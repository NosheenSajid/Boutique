<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

if (isLoggedIn()) {
    redirect('account.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $token = (string) ($_POST['csrf_token'] ?? '');

    if (!verifyCsrf($token)) {
        flash('error', 'Security token expired. Please try again.');
    } else {
        $stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id' => (int) $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
            ];

            redirect('account.php');
        }

        flash('error', 'Invalid email or password.');
    }
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/login.css">

<section class="login-section">
    <div class="login-card">
        <div class="login-intro">
            <p class="login-label">Welcome back</p>

            <h1 class="login-title">
                Sign in to your account
            </h1>

            <p class="login-description">
                Track orders, save favorites, and manage your preferences in one place.
            </p>

            <div class="login-register-box">
                <p class="login-register-text">
                    New to our collection?
                </p>

                <a
                    href="<?= BASE_URL ?>register.php"
                    class="login-register-link"
                >
                    Create a new account
                </a>
            </div>
        </div>

        <div class="login-form-container">
            <?php $error = flash('error'); if ($error): ?>
                <div class="login-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" class="login-form">
                <?= csrfField() ?>

                <div class="login-field">
                    <label class="login-label-field">
                        Email address
                    </label>

                    <input
                        type="email"
                        name="email"
                        required
                        class="login-input"
                        placeholder="you@example.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    >
                </div>

                <div class="login-field">
                    <label class="login-label-field">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        required
                        class="login-input"
                        placeholder="••••••••"
                    >
                </div>

                <div class="login-options">
                    <label class="remember-label">
                        <input type="checkbox">
                        Remember me
                    </label>

                    <a
                        href="<?= BASE_URL ?>forgot-password.php"
                        class="forgot-link"
                    >
                        Forgot password?
                    </a>
                </div>

                <button
                    type="submit"
                    class="login-button"
                >
                    Login
                </button>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>