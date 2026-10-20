<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/admin-header.php';

if (isAdminLoggedIn()) {
    redirect('admin/admin-dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $token = (string) ($_POST['csrf_token'] ?? '');

    if (!verifyCsrf($token)) {
        flash('error', 'Security token expired. Please try again.');
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, name, email, password, role, created_by
             FROM admins
             WHERE email = :email
             LIMIT 1'
        );

        $stmt->execute(['email' => $email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);

            $_SESSION['admin'] = [
                'id' => (int) $admin['id'],
                'name' => $admin['name'],
                'email' => $admin['email'],
                'role' => $admin['role'] ?? 'owner',
                'created_by' => $admin['created_by'] !== null
                    ? (int) $admin['created_by']
                    : null,
            ];

            redirect('admin/admin-dashboard.php');
        }

        flash('error', 'Invalid admin email or password.');
    }
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin-login.css">

<section class="admin-login-page">
    <div class="admin-login-container">

        <!-- Left information panel -->
        <div class="admin-login-info">

            <span class="admin-login-label">Admin Portal</span>

            <h1>Admin Login</h1>

            <p>
                Sign in to manage your store, products,
                orders, and customers.
            </p>

            <div class="admin-register-box">
                <p>Need an admin account?</p>

                <a href="<?= htmlspecialchars(BASE_URL) ?>admin/admin-register.php">
                    Create admin account
                </a>
            </div>

        </div>

        <!-- Login form -->
        <div class="admin-login-form">

            <h2>Welcome Back</h2>

            <p class="admin-form-description">
                Enter your credentials to access your account.
            </p>

            <?php
            $error = flash('error');

            if ($error):
            ?>
                <div class="admin-login-error" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="post">

                <?= csrfField() ?>

                <div class="admin-form-group">
                    <label for="email">Email Address</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="admin@example.com"
                        value="<?= htmlspecialchars(
                            (string) ($_POST['email'] ?? ''),
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="admin-form-group">
                    <label for="password">Password</label>

                    <div class="admin-password-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="admin-show-password"
                            id="togglePassword"
                            aria-controls="password"
                            aria-pressed="false"
                        >
                            Show
                        </button>
                    </div>
                </div>

                <div class="admin-forgot-password">
                    <a href="<?= htmlspecialchars(BASE_URL) ?>admin/admin-forgot-password.php">
                        Forgot password?
                    </a>
                </div>

                <button type="submit" class="admin-login-button">
                    Login
                </button>

            </form>

        </div>

    </div>
</section>

<script src="<?= BASE_URL ?>assets/javascript/admin-login.js"></script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>