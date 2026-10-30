<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/admin-header.php';

$creatingAssistant = isAdminLoggedIn();

if ($creatingAssistant) {
    requireAdminOwner();
} elseif ((int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0) {
    flash('error', 'Only the store owner can add assistant accounts.');
    redirect('admin/admin-login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    $token = (string) ($_POST['csrf_token'] ?? '');

    if (!verifyCsrf($token)) {

        flash('error', 'Security token expired. Please try again.');

    } elseif ($name === '' || $email === '' || $password === '') {

        flash('error', 'Please fill in all required fields.');

    } elseif ($password !== $confirmPassword) {

        flash('error', 'Passwords do not match.');

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        flash('error', 'Please enter a valid email address.');

    } else {

        $stmt = $pdo->prepare(
            'SELECT id FROM admins WHERE email = :email LIMIT 1'
        );

        $stmt->execute(['email' => $email]);

        if ($stmt->fetch()) {

            flash('error', 'An admin account with this email already exists.');

        } else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $insert = $pdo->prepare(
                'INSERT INTO admins
                (name, email, password, role, created_by, created_at, updated_at)
                VALUES
                (:name, :email, :password, :role, :created_by, NOW(), NOW())'
            );

            $insert->execute([
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword,
                'role' => $creatingAssistant ? 'assistant' : 'owner',
                'created_by' => $creatingAssistant
                    ? (int) $_SESSION['admin']['id']
                    : null,
            ]);

            flash(
                'success',
                $creatingAssistant
                    ? 'Assistant account created successfully.'
                    : 'Store owner account created successfully. Please log in.'
            );

            redirect(
                $creatingAssistant
                    ? 'admin/admin-dashboard.php'
                    : 'admin/admin-login.php'
            );
        }
    }
}

$error = flash('error');

?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin-register.css">

<section class="register-page">

    <div class="register-card">

        <div class="register-info">

            <p class="register-label">
                <?= $creatingAssistant ? 'Team access' : 'Store setup' ?>
            </p>

            <h1>
                <?= $creatingAssistant
                    ? 'Add store assistant'
                    : 'Create store owner account' ?>
            </h1>

            <p class="register-description">
                <?= $creatingAssistant
                    ? 'Create a separate account for a trusted team member to help manage products and orders.'
                    : 'Create the owner account for your store and manage your products, orders, and team.' ?>
            </p>

        </div>

        <div class="register-content">

            <?php if ($error): ?>
                <div class="register-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" class="register-form">

                <?= csrfField() ?>

                <div class="register-field">

                    <label for="name">Full name</label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        required
                        autocomplete="name"
                        placeholder="<?= $creatingAssistant
                            ? 'Assistant full name'
                            : 'Owner full name' ?>"
                        value="<?= htmlspecialchars(
                            (string) ($_POST['name'] ?? '')
                        ) ?>"
                    >

                </div>

                <div class="register-field">

                    <label for="email">Email address</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        autocomplete="email"
                        placeholder="<?= $creatingAssistant
                            ? 'assistant@example.com'
                            : 'owner@example.com' ?>"
                        value="<?= htmlspecialchars(
                            (string) ($_POST['email'] ?? '')
                        ) ?>"
                    >

                </div>

                <div class="register-field">

                    <label for="password">Password</label>

                    <div class="register-password">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="Create a password"
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            data-target="password"
                            aria-pressed="false"
                        >
                            Show
                        </button>

                    </div>

                </div>

                <div class="register-field">

                    <label for="confirm_password">
                        Confirm password
                    </label>

                    <div class="register-password">

                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            required
                            autocomplete="new-password"
                            placeholder="Repeat password"
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            data-target="confirm_password"
                            aria-pressed="false"
                        >
                            Show
                        </button>

                    </div>

                </div>

                <button type="submit" class="register-button">
                    <?= $creatingAssistant
                        ? 'Create assistant account'
                        : 'Create owner account' ?>
                </button>

            </form>

            <?php if (!$creatingAssistant): ?>

                <div class="register-login">
                    Already have an owner account?

                    <a href="<?= BASE_URL ?>admin-login.php">
                        Login here
                    </a>
                </div>

            <?php endif; ?>

        </div>

    </div>

</section>

<script src="<?= BASE_URL ?>assets/javascript/admin-register.js"></script>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>