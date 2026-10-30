
document.querySelectorAll('.reset-toggle').forEach(function (button) {

    button.addEventListener('click', function () {

        const input = document.getElementById(this.dataset.target);

        if (!input) {
            return;
        }

        const shouldShow = input.type === 'password';

        input.type = shouldShow ? 'text' : 'password';
        this.textContent = shouldShow ? 'Hide' : 'Show';

        this.setAttribute(
            'aria-pressed',
            shouldShow ? 'true' : 'false'
        );

        this.setAttribute(
            'aria-label',
            shouldShow ? 'Hide password' : 'Show password'
        );
    });

});
