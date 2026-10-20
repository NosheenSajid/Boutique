<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/admin-header.php';

ensureAdminPasswordResetTable();

if (isAdminLoggedIn()) {
    redirect('admin/admin-dashboard.php');
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

            $tokenValue = createAdminPasswordResetToken($email);

            if ($tokenValue !== null) {
                flash(
                    'success',
                    'Password reset instructions have been sent to your email.'
                );
            } else {
                flash(
                    'success',
                    'If the admin account exists, a reset link was generated for it.'
                );
            }

            redirect('admin/admin-forgot-password.php');
        }

    } elseif ($action === 'reset') {

        if ($token === '') {
            flash('error', 'Invalid reset token.');
            redirect('admin/admin-forgot-password.php');
        }

        if ($password === '' || $confirmPassword === '') {
            flash('error', 'Please enter and confirm your new password.');
            redirect('admin/admin-forgot-password.php?token=' . urlencode($token));
        }

        if ($password !== $confirmPassword) {
            flash('error', 'Passwords do not match.');
            redirect('admin/admin-forgot-password.php?token=' . urlencode($token));
        }

        if (strlen($password) < 6) {
            flash('error', 'Password must be at least 6 characters long.');
            redirect('admin/admin-forgot-password.php?token=' . urlencode($token));
        }

        if (!resetAdminPasswordWithToken($token, $password)) {
            flash('error', 'This reset link is invalid or expired.');
            redirect('admin/admin-forgot-password.php');
        }

        flash('success', 'Your admin password has been reset successfully.');
        redirect('admin/admin-login.php');
    }
}

$resetToken = trim((string) ($_GET['token'] ?? ''));
$resetData = $resetToken !== ''
    ? getAdminPasswordResetToken($resetToken)
    : null;

$successMessage = flash('success');
$errorMessage = flash('error');

?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin-forgot-password.css">

<section class="reset-page">

    <div class="reset-card">

        <div class="reset-info">

            <p class="reset-label">Admin portal</p>

            <h1>
                <?= $resetData
                    ? 'Reset admin password'
                    : 'Forgot admin password?' ?>
            </h1>

            <p>
                <?= $resetData
                    ? 'Enter your new password below to complete the reset.'
                    : 'Enter the email address linked to your admin account and follow the instructions to reset your password.' ?>
            </p>

        </div>

        <div class="reset-content">

            <?php if ($errorMessage): ?>
                <div class="reset-message reset-error">
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <?php if ($successMessage): ?>
                <div class="reset-message reset-success">
                    <?= htmlspecialchars($successMessage) ?>
                </div>
            <?php endif; ?>

            <?php if ($resetData): ?>

                <form method="post" class="reset-form">

                    <?= csrfField() ?>

                    <input type="hidden" name="action" value="reset">

                    <input
                        type="hidden"
                        name="token"
                        value="<?= htmlspecialchars(
                            (string) $resetData['token'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >

                    <div class="reset-field">

                        <label for="password">New password</label>

                        <div class="reset-password-wrapper">

                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                minlength="6"
                                autocomplete="new-password"
                                placeholder="Enter new password"
                            >

                            <button
                                type="button"
                                class="reset-toggle"
                                data-target="password"
                                aria-pressed="false"
                            >
                                Show
                            </button>

                        </div>

                    </div>

                    <div class="reset-field">

                        <label for="confirm_password">
                            Confirm password
                        </label>

                        <div class="reset-password-wrapper">

                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                required
                                minlength="6"
                                autocomplete="new-password"
                                placeholder="Repeat new password"
                            >

                            <button
                                type="button"
                                class="reset-toggle"
                                data-target="confirm_password"
                                aria-pressed="false"
                            >
                                Show
                            </button>

                        </div>

                    </div>

                    <button type="submit" class="reset-button">
                        Reset password
                    </button>

                </form>

            <?php else: ?>

                <form method="post" class="reset-form">

                    <?= csrfField() ?>

                    <input type="hidden" name="action" value="request">

                    <div class="reset-field">

                        <label for="email">Email address</label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            autocomplete="email"
                            placeholder="admin@example.com"
                        >

                    </div>

                    <button type="submit" class="reset-button">
                        Send reset link
                    </button>

                </form>

            <?php endif; ?>

            <div class="reset-footer">

                <a href="<?= BASE_URL ?>admin/admin-login.php">
                    &larr; Back to admin login
                </a>

            </div>

        </div>

    </div>

</section>

<script src="<?= BASE_URL ?>assets/javascript/admin-forgot-password.js"></script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>