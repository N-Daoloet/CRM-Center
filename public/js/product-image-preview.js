document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-image-preview-input]').forEach((input) => {
        const preview = input.closest('form')?.querySelector('[data-image-preview]');
        if (!preview) return;

        let objectUrls = [];

        input.addEventListener('change', () => {
            objectUrls.forEach((url) => URL.revokeObjectURL(url));
            objectUrls = [];
            preview.replaceChildren();

            const files = Array.from(input.files || []).filter((file) => file.type.startsWith('image/'));
            preview.hidden = files.length === 0;

            files.forEach((file) => {
                const url = URL.createObjectURL(file);
                objectUrls.push(url);

                const item = document.createElement('div');
                item.className = 'product-selected-preview';

                const image = document.createElement('img');
                image.src = url;
                image.alt = file.name;
                image.title = file.name;

                const name = document.createElement('span');
                name.textContent = file.name;
                name.title = file.name;

                item.append(image, name);
                preview.append(item);
            });
        });

        input.form?.addEventListener('reset', () => {
            objectUrls.forEach((url) => URL.revokeObjectURL(url));
            objectUrls = [];
            preview.replaceChildren();
            preview.hidden = true;
        });
    });
});