
document.querySelectorAll('.password-toggle').forEach(function (button) {

    button.addEventListener('click', function () {

        const input = document.getElementById(this.dataset.target);

        if (!input) {
            return;
        }

        const showPassword = input.type === 'password';

        input.type = showPassword ? 'text' : 'password';
        this.textContent = showPassword ? 'Hide' : 'Show';

        this.setAttribute(
            'aria-pressed',
            showPassword ? 'true' : 'false'
        );

        this.setAttribute(
            'aria-label',
            showPassword ? 'Hide password' : 'Show password'
        );
    });

});
