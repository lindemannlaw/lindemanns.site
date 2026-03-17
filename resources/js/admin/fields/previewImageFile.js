export function previewImageFile() {
    const fields = document.querySelectorAll('[data-pif-field]');

    fields.forEach(field => {
        if (field?.previewImageFileInit) return;

        const parent = field.closest('[data-preview-image-file]');
        const picture = parent?.querySelector('[data-pif-picture]');
        const dropZone = parent?.querySelector('[data-pif-dropzone]');
        const image = parent?.querySelector('[data-pif-image]');

        if (!parent || !image || image?.tagName !== 'IMG') return;

        if (field.isFieldClearable) {
            const pictureWidth = picture.offsetWidth;
            const pictureHeight = picture.offsetHeight;

            image.src = '/img/default' + (pictureWidth < pictureHeight ? '-vertical' : '') + '.svg';

            field.isFieldClearable = false;
        }

        field.previewImageFileCurrentData = {
            url: image.src,
            class: image.getAttribute('class'),
            hasImage: field.hasAttribute('data-pif-has-image'),
        };

        if (dropZone) {
            dropZone.addEventListener('dragover', (event) => {
                event.preventDefault();
                toggleDropZoneClasses('replace');
            });

            dropZone.addEventListener('dragleave', () => {
                toggleDropZoneClasses();
            });

            dropZone.addEventListener('drop', (event) => {
                event.preventDefault();
                toggleDropZoneClasses();

                const files = event.dataTransfer.files;

                if (files.length > 0) {
                    field.files = files;
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });

            function toggleDropZoneClasses(action) {
                const baseClasses = ['bg-white', 'border-opacity-25'];
                const newClasses = ['bg-primary', 'bg-opacity-10', 'border-opacity-50'];

                if (action === 'replace') {
                    dropZone.classList.remove(...baseClasses);
                    dropZone.classList.add(...newClasses);
                    return;
                }

                dropZone.classList.add(...baseClasses);
                dropZone.classList.remove(...newClasses);
            }
        }

        const isCompact = picture?.hasAttribute('data-pif-compact');

        field.addEventListener('change', () => {
            const file = field?.files[0];
            const url = getImageUrl(file);
            const defaultProps = field.previewImageFileCurrentData;

            if (isCompact) {
                const placeholder = parent.querySelector('[data-pif-compact-placeholder]');
                const changeLabel = parent.querySelector('[data-pif-compact-change]');

                if (url) {
                    image.src = url;
                    image.classList.remove('d-none');
                    placeholder?.classList.add('d-none');
                    if (changeLabel) {
                        changeLabel.classList.remove('d-none');
                    } else if (placeholder) {
                        const span = document.createElement('span');
                        span.className = 'small text-muted';
                        span.setAttribute('data-pif-compact-change', '');
                        span.textContent = 'Ändern';
                        placeholder.insertAdjacentElement('afterend', span);
                    }
                    picture.classList.remove('border-dashed', 'bg-light');
                    picture.classList.add('border-solid', 'bg-white');
                } else {
                    image.src = '';
                    image.classList.add('d-none');
                    placeholder?.classList.remove('d-none');
                    changeLabel?.classList.add('d-none');
                    picture.classList.add('border-dashed', 'bg-light');
                    picture.classList.remove('border-solid', 'bg-white');
                }
                return;
            }

            const newClasses = image.dataset.pifViewClasses;

            image.src = url ? url : defaultProps.url;
            image.setAttribute('class', url && newClasses ? newClasses : defaultProps.class);

            if (defaultProps.hasImage) return;

            const pictureBaseClasses = ['border-dashed', 'p-4'];
            const pictureNewClasses = ['border-solid', 'p-3'];

            if (url) {
                picture.classList.remove(...pictureBaseClasses);
                picture.classList.add(...pictureNewClasses);
            } else {
                picture.classList.add(...pictureBaseClasses);
                picture.classList.remove(...pictureNewClasses);
            }
        });

        field.previewImageFileInit = true;
    });
}

function getImageUrl(file){
    if (!file?.type?.includes('image/')) return false;

    let url = URL.createObjectURL(file);
    const img = document.createElement('img');

    img.src = url;
    img.onload = function() {
        URL.revokeObjectURL(this.src);
    }

    return url;
}
