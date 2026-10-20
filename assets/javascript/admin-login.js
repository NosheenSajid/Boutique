
document.addEventListener('DOMContentLoaded', function () {
    const passwordInput = document.getElementById('password');
    const toggleButton = document.getElementById('togglePassword');

    if (passwordInput && toggleButton) {
        toggleButton.addEventListener('click', function () {
            const showPassword = passwordInput.type === 'password';

            passwordInput.type = showPassword ? 'text' : 'password';
            toggleButton.textContent = showPassword ? 'Hide' : 'Show';
            toggleButton.setAttribute(
                'aria-pressed',
                String(showPassword)
            );
        });
    }
});
