
document.querySelectorAll('.order-status').forEach(function (select) {
    const form = select.closest('form');
    const saveButton = form.querySelector('.order-save-btn');

    function updateSaveButton() {
        const originalStatus = select.dataset.originalStatus;
        const changed = select.value !== originalStatus;

        saveButton.hidden = !changed;
    }

    select.addEventListener('change', updateSaveButton);

    form.addEventListener('submit', function (event) {
        if (select.value === select.dataset.originalStatus) {
            event.preventDefault();
            return;
        }

        if (!confirm('Update this order status to "' + select.value + '"?')) {
            event.preventDefault();
        }
    });
});
