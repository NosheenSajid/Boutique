<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';
ensurePasswordResetTable();

if (isLoggedIn()) {
    redirect('account.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'request';
    $token = (string) ($_POST['token'] ?? '');
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    $csrfToken = (string) ($_POST['csrf_token'] ?? '');

    if (!verifyCsrf($csrfToken)) {
        flash('error', 'Security token expired. Please try again.');
    } elseif ($action === 'request') {
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please enter a valid email address.');
        } else {
            $tokenValue = createPasswordResetToken($email);
            if ($tokenValue !== null) {
                flash('success', 'Password reset instructions have been sent to your email.');
            } else {
                flash('success', 'If the account exists, a reset link was generated for it.');
            }
            redirect('forgot-password.php');
        }
    } elseif ($action === 'reset') {
        if ($token === '') {
            flash('error', 'Invalid reset token.');
            redirect('forgot-password.php');
        }

        if ($password === '' || $confirmPassword === '') {
            flash('error', 'Please enter and confirm your new password.');
            redirect('forgot-password.php?token=' . urlencode($token));
        }

        if ($password !== $confirmPassword) {
            flash('error', 'Passwords do not match.');
            redirect('forgot-password.php?token=' . urlencode($token));
        }

        if (strlen($password) < 6) {
            flash('error', 'Password must be at least 6 characters long.');
            redirect('forgot-password.php?token=' . urlencode($token));
        }

        if (!resetPasswordWithToken($token, $password)) {
            flash('error', 'This reset link is invalid or expired.');
            redirect('forgot-password.php');
        }

        flash('success', 'Your password has been reset successfully.');
        redirect('login.php');
    }
}



$resetToken = trim((string) ($_GET['token'] ?? ''));
$resetData = $resetToken !== '' ? getPasswordResetToken($resetToken) : null;
$successMessage = flash('success');
$errorMessage = flash('error');
?>


<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/forgot-password.css">

<section class="forgot-section">
    <div class="forgot-card">
        <div class="forgot-grid">
            <div class="forgot-intro">
                <p class="forgot-label">Account</p>

                <h1 class="forgot-title">
                    <?= $resetData ? 'Reset your password' : 'Forgot your password?' ?>
                </h1>

                <p class="forgot-description">
                    <?= $resetData
                        ? 'Enter your new password below to complete the reset.'
                        : 'Enter the email address linked to your account and we will guide you through the reset process.' ?>
                </p>
            </div>

            <div class="forgot-form-container">
                <?php if ($errorMessage): ?>
                    <div class="forgot-error">
                        <?= htmlspecialchars($errorMessage) ?>
                    </div>
                <?php endif; ?>

                <?php if ($successMessage): ?>
                    <div class="forgot-success">
                        <?= htmlspecialchars($successMessage) ?>
                    </div>
                <?php endif; ?>

                <?php if ($resetData): ?>
                    <form method="post" class="forgot-form">
                        <?= csrfField() ?>

                        <input
                            type="hidden"
                            name="action"
                            value="reset"
                        >

                        <input
                            type="hidden"
                            name="token"
                            value="<?= htmlspecialchars($resetData['token'], ENT_QUOTES, 'UTF-8') ?>"
                        >

                        <div class="forgot-field">
                            <label class="forgot-field-label">
                                New password
                            </label>

                            <input
                                type="password"
                                name="password"
                                required
                                class="forgot-input"
                                placeholder="Enter new password"
                            >
                        </div>

                        <div class="forgot-field">
                            <label class="forgot-field-label">
                                Confirm password
                            </label>

                            <input
                                type="password"
                                name="confirm_password"
                                required
                                class="forgot-input"
                                placeholder="Repeat new password"
                            >
                        </div>

                        <button
                            type="submit"
                            class="forgot-button"
                        >
                            Reset password
                        </button>
                    </form>
                <?php else: ?>
                    <form method="post" class="forgot-form">
                        <?= csrfField() ?>

                        <input
                            type="hidden"
                            name="action"
                            value="request"
                        >

                        <div class="forgot-field">
                            <label class="forgot-field-label">
                                Email address
                            </label>

                            <input
                                type="email"
                                name="email"
                                required
                                class="forgot-input"
                                placeholder="you@example.com"
                            >
                        </div>

                        <button
                            type="submit"
                            class="forgot-button"
                        >
                            Send reset link
                        </button>
                    </form>
                <?php endif; ?>

                <div class="forgot-login-link">
                    <a href="<?= BASE_URL ?>login.php">
                        Back to login
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>