<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/admin-header.php';

requireAdminLogin();

$adminId = (int) $_SESSION['admin']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if (!verifyCsrf((string) ($_POST['csrf_token'] ?? ''))) {

        flash('error', 'Security token expired. Please try again.');

    } elseif ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {

        flash('error', 'Enter a name and a valid email address.');

    } elseif (
        $password !== '' &&
        (strlen($password) < 8 || $password !== $confirmPassword)
    ) {

        flash(
            'error',
            'A new password must be at least 8 characters and match its confirmation.'
        );

    } else {

        $emailStmt = $pdo->prepare(
            'SELECT id FROM admins
             WHERE email = :email AND id <> :id
             LIMIT 1'
        );

        $emailStmt->execute([
            'email' => $email,
            'id' => $adminId
        ]);

        if ($emailStmt->fetch()) {

            flash('error', 'That email address is already used by another admin.');

        } else {

            if ($password !== '') {

                $stmt = $pdo->prepare(
                    'UPDATE admins
                     SET name = :name,
                         email = :email,
                         password = :password,
                         updated_at = NOW()
                     WHERE id = :id'
                );

                $stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'id' => $adminId
                ]);

            } else {

                $stmt = $pdo->prepare(
                    'UPDATE admins
                     SET name = :name,
                         email = :email,
                         updated_at = NOW()
                     WHERE id = :id'
                );

                $stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'id' => $adminId
                ]);
            }

            $_SESSION['admin']['name'] = $name;
            $_SESSION['admin']['email'] = $email;

            flash('success', 'Account information updated.');
        }
    }
}

$stmt = $pdo->prepare(
    'SELECT name, email, created_at
     FROM admins
     WHERE id = :id
     LIMIT 1'
);

$stmt->execute(['id' => $adminId]);

$admin = $stmt->fetch();

$error = flash('error');
$success = flash('success');

?>

<link
    rel="stylesheet"
    href="<?= BASE_URL ?>assets/css/admin-account.css">

<section class="account-page">

    <div class="account-header">

        <div>
            <h1>Account settings</h1>
        </div>

    </div>

    <?php if ($error): ?>
        <div class="account-message account-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="account-message account-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="account-card">

        <div class="account-profile">

            <div class="account-avatar">
                <?= htmlspecialchars(
                    strtoupper(
                        substr((string) ($admin['name'] ?? 'A'), 0, 2)
                    )
                ) ?>
            </div>

            <div>
                <h2>
                    <?= htmlspecialchars((string) ($admin['name'] ?? 'Admin')) ?>
                </h2>

                <p>
                    Administrator since
                    <?= htmlspecialchars(
                        date(
                            'M j, Y',
                            strtotime((string) ($admin['created_at'] ?? 'now'))
                        )
                    ) ?>
                </p>
            </div>

        </div>

        <form method="post" class="account-form">

            <?= csrfField() ?>

            <div class="account-field">

                <label for="name">Full name</label>

                <input
                    id="name"
                    type="text"
                    name="name"
                    required
                    autocomplete="name"
                    value="<?= htmlspecialchars(
                        (string) ($admin['name'] ?? '')
                    ) ?>"
                >

            </div>

            <div class="account-field">

                <label for="email">Email address</label>

                <input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autocomplete="email"
                    value="<?= htmlspecialchars(
                        (string) ($admin['email'] ?? '')
                    ) ?>"
                >

            </div>

            <div class="account-field">

                <label for="password">New password</label>

                <div class="password-wrapper">

                    <input
                        id="password"
                        type="password"
                        name="password"
                        minlength="8"
                        autocomplete="new-password"
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        data-target="password"
                        aria-label="Show new password"
                        aria-pressed="false"
                    >
                        Show
                    </button>

                </div>

                <p class="account-hint">
                    Leave blank to keep your current password.
                </p>

            </div>

            <div class="account-field">

                <label for="confirm_password">
                    Confirm new password
                </label>

                <div class="password-wrapper">

                    <input
                        id="confirm_password"
                        type="password"
                        name="confirm_password"
                        minlength="8"
                        autocomplete="new-password"
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        data-target="confirm_password"
                        aria-label="Show password confirmation"
                        aria-pressed="false"
                    >
                        Show
                    </button>

                </div>

            </div>

            <div class="account-submit">

                <button
                    type="submit"
                    class="account-btn account-btn-primary"
                >
                    Save account changes
                </button>

            </div>

        </form>

    </div>

</section>

<script src="<?= BASE_URL ?>assets/javascript/admin-account.js"></script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>