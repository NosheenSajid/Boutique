<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/contact.css">


<section class="contact-section">
    <div class="contact-grid">
        <div class="contact-info">
            <p class="contact-label">Contact us</p>

            <h1 class="contact-title">
                We’d love to hear from you.
            </h1>

            <p class="contact-description">
                Whether you need help with an order or want styling advice, our team is here to support you.
            </p>

            <div class="contact-details">
                <div>
                    <p class="contact-detail-title">Email</p>
                    <p class="contact-detail-text">hello@clothingstore.com</p>
                </div>

                <div>
                    <p class="contact-detail-title">Phone</p>
                    <p class="contact-detail-text">+92 300 1234567</p>
                </div>

                <div>
                    <p class="contact-detail-title">Address</p>
                    <p class="contact-detail-text">Main Boulevard, Gulshan, Karachi</p>
                </div>
            </div>
        </div>

        <div class="contact-form-container">
            <form class="contact-form">
                <div class="contact-field">
                    <label>Name</label>

                    <input
                        type="text"
                        class="contact-input"
                        placeholder="Your full name"
                    >
                </div>

                <div class="contact-field">
                    <label>Email</label>

                    <input
                        type="email"
                        class="contact-input"
                        placeholder="you@example.com"
                    >
                </div>

                <div class="contact-field">
                    <label>Subject</label>

                    <input
                        type="text"
                        class="contact-input"
                        placeholder="Order support"
                    >
                </div>

                <div class="contact-field">
                    <label>Message</label>

                    <textarea
                        rows="5"
                        class="contact-textarea"
                        placeholder="Tell us how we can help"
                    ></textarea>
                </div>

                <button
                    type="submit"
                    class="contact-button"
                >
                    Send message
                </button>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>