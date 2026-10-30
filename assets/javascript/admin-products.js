
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('product-form-modal');
    const openButton = document.getElementById('show-product-form');
    const closeButton = document.getElementById('close-product-form');
    const cancelButton = document.getElementById('cancel-product-form');

    const imageUrlInput = document.getElementById('main-image-input');
    const fileInput = document.getElementById('product-image-input');
    const preview = document.getElementById('main-image-preview');

    let previousFocus = null;
    let previewObjectUrl = null;

    function openModal() {
        if (!modal) return;

        previousFocus = document.activeElement;
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        const firstInput = modal.querySelector(
            'input[name="name"]'
        );

        if (firstInput) {
            firstInput.focus();
        }
    }

    function closeModal() {
        if (!modal) return;

        modal.classList.add('hidden');
        document.body.style.overflow = '';

        if (previousFocus && typeof previousFocus.focus === 'function') {
            previousFocus.focus();
        }
    }

    function showPreview(source, altText) {
        if (!preview) return;

        preview.replaceChildren();

        if (!source) {
            preview.textContent = 'Image Preview';
            return;
        }

        const image = document.createElement('img');
        image.alt = altText;
        image.src = source;

        image.addEventListener('error', function () {
            preview.replaceChildren();
            preview.textContent = 'Image unavailable';
        }, { once: true });

        preview.appendChild(image);
    }

    if (openButton) {
        openButton.addEventListener('click', openModal);
    }

    if (closeButton) {
        closeButton.addEventListener('click', closeModal);
    }

    if (cancelButton) {
        cancelButton.addEventListener('click', closeModal);
    }

    if (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (
            event.key === 'Escape' &&
            modal &&
            !modal.classList.contains('hidden')
        ) {
            closeModal();
        }
    });

    if (imageUrlInput) {
        imageUrlInput.addEventListener('input', function () {
            if (fileInput && fileInput.files.length > 0) {
                return;
            }

            const url = imageUrlInput.value.trim();

            if (!url) {
                showPreview('', '');
                return;
            }

            try {
                const parsedUrl = new URL(url);

                if (
                    parsedUrl.protocol !== 'https:' &&
                    parsedUrl.protocol !== 'http:'
                ) {
                    showPreview('', '');
                    return;
                }

                showPreview(parsedUrl.href, 'Product preview');
            } catch (error) {
                showPreview('', '');
            }
        });
    }

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            const file = fileInput.files[0];

            if (previewObjectUrl) {
                URL.revokeObjectURL(previewObjectUrl);
                previewObjectUrl = null;
            }

            if (!file) {
                showPreview(
                    imageUrlInput ? imageUrlInput.value.trim() : '',
                    'Product preview'
                );
                return;
            }

            const allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif'
            ];

            if (!allowedTypes.includes(file.type)) {
                fileInput.value = '';
                showPreview('', '');
                alert('Please select a JPG, PNG, WEBP, or GIF image.');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                fileInput.value = '';
                showPreview('', '');
                alert('The image must be 5 MB or smaller.');
                return;
            }

            previewObjectUrl = URL.createObjectURL(file);

            showPreview(
                previewObjectUrl,
                'Uploaded product preview'
            );
        });
    }

    document.querySelectorAll('.delete-product-form').forEach(
        function (form) {
            form.addEventListener('submit', function (event) {
                const confirmed = confirm(
                    'Are you sure you want to remove this product?'
                );

                if (!confirmed) {
                    event.preventDefault();
                }
            });
        }
    );

    window.addEventListener('pagehide', function () {
        if (previewObjectUrl) {
            URL.revokeObjectURL(previewObjectUrl);
        }
    });

    // Keep the background from scrolling while the modal is open.
    if (modal && !modal.classList.contains('hidden')) {
        document.body.style.overflow = 'hidden';
    }
});
