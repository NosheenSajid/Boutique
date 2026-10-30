<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

if (isLoggedIn()) {
    redirect('account.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    $token = (string) ($_POST['csrf_token'] ?? '');

    if (!verifyCsrf($token)) {

        flash(
            'error',
            'Security token expired. Please try again.'
        );

    } elseif ($name === '' || $email === '' || $password === '') {

        flash(
            'error',
            'Please fill in all required fields.'
        );

    } elseif ($password !== $confirmPassword) {

        flash(
            'error',
            'Passwords do not match.'
        );

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        flash(
            'error',
            'Please enter a valid email address.'
        );

    } else {

        $stmt = $pdo->prepare(
            'SELECT id FROM users WHERE email = :email LIMIT 1'
        );

        $stmt->execute([
            'email' => $email
        ]);

        if ($stmt->fetch()) {

            flash(
                'error',
                'An account with this email already exists.'
            );

        } else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $insert = $pdo->prepare(
                'INSERT INTO users (name, email, password, created_at, updated_at)
                 VALUES (:name, :email, :password, NOW(), NOW())'
            );

            $insert->execute([
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword,
            ]);

            flash(
                'success',
                'Registration successful. Please log in.'
            );

            redirect('login.php');
        }
    }
}

?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/register.css">

<section class="register-section">

    <div class="register-card">


        <!-- Introduction Panel -->

        <div class="register-intro">

            <p class="register-label">
                Join us
            </p>

            <h1 class="register-title">
                Create your account
            </h1>

            <p class="register-description">
                Enjoy exclusive updates, faster checkout, and a personalized
                shopping experience.
            </p>


            <div class="register-login-box">

                <p class="register-login-text">
                    Already have an account?
                </p>

                <a
                    href="<?= BASE_URL ?>login.php"
                    class="register-login-link"
                >
                    Log in here
                </a>

            </div>

        </div>


        <!-- Registration Form -->

        <div class="register-form-panel">

            <?php
                $error = flash('error');

                if ($error):
            ?>

                <div class="register-error">
                    <?= htmlspecialchars($error) ?>
                </div>

            <?php endif; ?>


            <form
                method="post"
                class="register-form"
            >

                <?= csrfField() ?>


                <!-- Full Name -->

                <div class="register-field">

                    <label class="register-label-text">
                        Full name
                    </label>

                    <input
                        type="text"
                        name="name"
                        required
                        class="register-input"
                        placeholder="Your full name"
                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                    >

                </div>


                <!-- Email -->

                <div class="register-field">

                    <label class="register-label-text">
                        Email address
                    </label>

                    <input
                        type="email"
                        name="email"
                        required
                        class="register-input"
                        placeholder="you@example.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    >

                </div>


                <!-- Password -->

                <div class="register-field">

                    <label class="register-label-text">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        required
                        class="register-input"
                        placeholder="Create a password"
                    >

                </div>


                <!-- Confirm Password -->

                <div class="register-field">

                    <label class="register-label-text">
                        Confirm password
                    </label>

                    <input
                        type="password"
                        name="confirm_password"
                        required
                        class="register-input"
                        placeholder="Repeat password"
                    >

                </div>


                <!-- Submit -->

                <button
                    type="submit"
                    class="register-button"
                >
                    Register
                </button>

            </form>

        </div>

    </div>

</section>


<?php require_once __DIR__ . '/includes/footer.php'; ?>