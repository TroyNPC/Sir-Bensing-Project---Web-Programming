// assets/new.js

document.addEventListener('DOMContentLoaded', function () {
    bindPcProductPreview();
});

document.addEventListener('turbo:load', function () {
    bindPcProductPreview();
});

function bindPcProductPreview() {
    const preview = document.getElementById('imagePreview');

    if (!preview) {
        console.warn('[new.js] #imagePreview not found');
        return;
    }

    // Listen globally for ANY file input change
    document.addEventListener('change', function (e) {

        // Detect file input
        if (e.target.type !== 'file') return;

        console.log('[new.js] File input changed:', e.target);

        const file = e.target.files && e.target.files[0];

        if (!file) return;

        if (!file.type.startsWith('image/')) {
            alert('Please select a valid image.');
            return;
        }

        // Show immediately
        const url = URL.createObjectURL(file);
        preview.src = url;

        console.log('[new.js] Preview updated ✅');
    });
}



