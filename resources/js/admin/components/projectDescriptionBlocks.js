import Sortable from 'sortablejs';
import { fields } from '../fields/fields.js';
import { wysiwyg } from './wysiwyg.js';

export function projectDescriptionBlocks() {
    const builders = document.querySelectorAll('[data-project-description-builder]');

    builders.forEach((builder) => {
        if (builder.dataset.inited) return;

        initBuilder(builder);
        builder.dataset.inited = 'true';
    });
}

function initBuilder(builder) {
    const blocksWrapper = builder.querySelector('[data-blocks-wrapper]');

    if (!blocksWrapper) return;

    Sortable.create(blocksWrapper, {
        draggable: '[data-block]',
        handle: '[data-block-move]',
        onEnd: () => reindexBuilder(builder),
    });

    builder.addEventListener('click', (event) => {
        const addBlockButton = event.target.closest('[data-block-add]');
        const addAfterButton = event.target.closest('[data-block-add-after]');
        const removeBlockButton = event.target.closest('[data-block-remove]');

        const addGalleryItemButton = event.target.closest('[data-gallery-item-add]');
        const addGalleryItemAfterButton = event.target.closest('[data-gallery-item-add-after]');
        const removeGalleryItemButton = event.target.closest('[data-gallery-item-remove]');

        const addTcItemButton = event.target.closest('[data-tc-item-add]');
        const addTcItemAfterButton = event.target.closest('[data-tc-item-add-after]');
        const removeTcItemButton = event.target.closest('[data-tc-item-remove]');

        const toggleBlockButton = event.target.closest('[data-block-toggle]');

        if (toggleBlockButton) {
            const block = toggleBlockButton.closest('[data-block]');
            const isExpanded = toggleBlockButton.getAttribute('aria-expanded') === 'true';

            setBlockCollapsed(block, isExpanded);
            if (isExpanded === false) {
                ensureWysiwygForBlock(block);
            }
            return;
        }

        if (addBlockButton) {
            const newBlock = createBlock(builder, 'text');
            blocksWrapper.appendChild(newBlock);
            reindexBuilder(builder);
            fields();
            ensureWysiwygForBlock(newBlock);
            return;
        }

        if (addAfterButton) {
            const currentBlock = addAfterButton.closest('[data-block]');
            if (!currentBlock) return;

            const newBlock = createBlock(builder, 'text');
            currentBlock.insertAdjacentElement('afterend', newBlock);
            reindexBuilder(builder);
            fields();
            ensureWysiwygForBlock(newBlock);
            return;
        }

        if (removeBlockButton) {
            const currentBlock = removeBlockButton.closest('[data-block]');
            if (!currentBlock) return;

            const allBlocks = blocksWrapper.querySelectorAll('[data-block]');
            if (allBlocks.length <= 1) return;

            currentBlock.remove();
            reindexBuilder(builder);
            return;
        }

        if (addGalleryItemButton) {
            const currentBlock = addGalleryItemButton.closest('[data-block]');
            if (!currentBlock) return;

            const itemsWrapper = currentBlock.querySelector('[data-gallery-items-wrapper]');
            if (!itemsWrapper) return;

            itemsWrapper.appendChild(createGalleryItem(builder));
            reindexBuilder(builder);
            fields();
            return;
        }

        if (addGalleryItemAfterButton) {
            const currentItem = addGalleryItemAfterButton.closest('[data-gallery-item]');
            if (!currentItem) return;

            currentItem.insertAdjacentElement('afterend', createGalleryItem(builder));
            reindexBuilder(builder);
            fields();
            return;
        }

        if (removeGalleryItemButton) {
            const currentItem = removeGalleryItemButton.closest('[data-gallery-item]');
            const itemsWrapper = removeGalleryItemButton.closest('[data-gallery-items-wrapper]');
            if (!currentItem || !itemsWrapper) return;

            const allItems = itemsWrapper.querySelectorAll('[data-gallery-item]');
            if (allItems.length <= 1) return;

            currentItem.remove();
            reindexBuilder(builder);
            return;
        }

        if (addTcItemButton) {
            const currentBlock = addTcItemButton.closest('[data-block]');
            if (!currentBlock) return;

            const itemsWrapper = currentBlock.querySelector('[data-tc-items-wrapper]');
            if (!itemsWrapper) return;

            itemsWrapper.appendChild(createTcItem(builder));
            reindexBuilder(builder);
            fields();
            wysiwyg();
            return;
        }

        if (addTcItemAfterButton) {
            const currentItem = addTcItemAfterButton.closest('[data-tc-item]');
            if (!currentItem) return;

            currentItem.insertAdjacentElement('afterend', createTcItem(builder));
            reindexBuilder(builder);
            fields();
            wysiwyg();
            return;
        }

        if (removeTcItemButton) {
            const currentItem = removeTcItemButton.closest('[data-tc-item]');
            const itemsWrapper = removeTcItemButton.closest('[data-tc-items-wrapper]');
            if (!currentItem || !itemsWrapper) return;

            const allItems = itemsWrapper.querySelectorAll('[data-tc-item]');
            if (allItems.length <= 1) return;

            currentItem.remove();
            reindexBuilder(builder);
            return;
        }
    });

    builder.addEventListener('change', (event) => {
        const typeSelect = event.target.closest('[data-block-type-select]');
        if (!typeSelect) return;

        const block = typeSelect.closest('[data-block]');
        if (!block) return;

        setBlockType(block, typeSelect.value);

        if (typeSelect.value === 'text') {
            ensureWysiwygForBlock(block);
        }

        if (typeSelect.value === 'floating_gallery') {
            const itemsWrapper = block.querySelector('[data-gallery-items-wrapper]');

            if (itemsWrapper && itemsWrapper.querySelectorAll('[data-gallery-item]').length === 0) {
                itemsWrapper.appendChild(createGalleryItem(builder));
                fields();
            }
        }

        if (typeSelect.value === 'text_column_row') {
            const itemsWrapper = block.querySelector('[data-tc-items-wrapper]');

            if (itemsWrapper && itemsWrapper.querySelectorAll('[data-tc-item]').length === 0) {
                itemsWrapper.appendChild(createTcItem(builder));
                fields();
            }
        }

        reindexBuilder(builder);
    });

    builder.querySelectorAll('[data-block]').forEach((block) => {
        const typeInput = block.querySelector('[data-block-type-input]');
        const type = typeInput?.value || 'text';
        setBlockType(block, type);
        setBlockCollapsed(block, true);

        initGalleryItemsSortable(block, builder);
        initTcItemsSortable(block, builder);
    });

    reindexBuilder(builder);
}

function initGalleryItemsSortable(block, builder) {
    const itemsWrapper = block.querySelector('[data-gallery-items-wrapper]');

    if (itemsWrapper && !itemsWrapper.dataset.sortableInited) {
        Sortable.create(itemsWrapper, {
            draggable: '[data-gallery-item]',
            handle: '[data-gallery-item-move]',
            onEnd: () => reindexBuilder(builder),
        });

        itemsWrapper.dataset.sortableInited = 'true';
    }
}

function initTcItemsSortable(block, builder) {
    const itemsWrapper = block.querySelector('[data-tc-items-wrapper]');

    if (itemsWrapper && !itemsWrapper.dataset.sortableInited) {
        Sortable.create(itemsWrapper, {
            draggable: '[data-tc-item]',
            handle: '[data-tc-item-move]',
            onEnd: () => reindexBuilder(builder),
        });

        itemsWrapper.dataset.sortableInited = 'true';
    }
}

function createBlock(builder, type = 'text') {
    const blockTemplate = getTemplateFromPane(builder, '[data-block-template="text"]');

    if (!blockTemplate) return document.createElement('div');

    const block = blockTemplate.content.firstElementChild.cloneNode(true);
    const typeSelect = block.querySelector('[data-block-type-select]');

    if (typeSelect) {
        typeSelect.value = type;
    }

    setBlockType(block, type);
    setBlockCollapsed(block, true);

    initGalleryItemsSortable(block, builder);
    initTcItemsSortable(block, builder);

    return block;
}

function createGalleryItem(builder) {
    const itemTemplate = getTemplateFromPane(builder, '[data-gallery-item-template]');

    if (!itemTemplate) return document.createElement('div');

    return itemTemplate.content.firstElementChild.cloneNode(true);
}

function createTcItem(builder) {
    const itemTemplate = getTemplateFromPane(builder, '[data-tc-item-template]');

    if (!itemTemplate) return document.createElement('div');

    return itemTemplate.content.firstElementChild.cloneNode(true);
}

function getTemplateFromPane(builder, selector) {
    const pane = builder.closest('.tab-pane');

    if (!pane) {
        return document.querySelector(selector);
    }

    return pane.querySelector(selector);
}

function setBlockType(block, type) {
    const typeInput = block.querySelector('[data-block-type-input]');
    const textPanel = block.querySelector('[data-block-type-panel="text"]');
    const galleryPanel = block.querySelector('[data-block-type-panel="floating_gallery"]');
    const tcRowPanel = block.querySelector('[data-block-type-panel="text_column_row"]');

    if (typeInput) {
        typeInput.value = type;
    }

    textPanel?.classList.toggle('d-none', type !== 'text');
    galleryPanel?.classList.toggle('d-none', type !== 'floating_gallery');
    tcRowPanel?.classList.toggle('d-none', type !== 'text_column_row');

    togglePanelRequired(textPanel, type === 'text');
    togglePanelRequired(galleryPanel, type === 'floating_gallery');
    togglePanelRequired(tcRowPanel, type === 'text_column_row');
}

function ensureWysiwygForBlock(block) {
    const body = block.querySelector('[data-block-body]');
    if (body?.classList.contains('d-none')) return;

    wysiwyg();
}

function setBlockCollapsed(block, collapsed) {
    if (!block) return;

    const toggleButton = block.querySelector('[data-block-toggle]');
    const icon = block.querySelector('[data-block-toggle-icon]');
    const body = block.querySelector('[data-block-body]');

    if (!toggleButton || !body) return;

    toggleButton.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    body.classList.toggle('d-none', collapsed);

    if (icon) {
        icon.textContent = collapsed ? '>' : 'v';
    }
}

function togglePanelRequired(panel, shouldBeRequired) {
    if (!panel) return;

    const fields = panel.querySelectorAll('input, textarea, select');

    fields.forEach((field) => {
        if (field.dataset.requiredOriginal === undefined) {
            field.dataset.requiredOriginal = field.hasAttribute('required') ? '1' : '0';
        }

        if (shouldBeRequired && field.dataset.requiredOriginal === '1') {
            field.setAttribute('required', 'required');
            return;
        }

        field.removeAttribute('required');
    });
}

function reindexBuilder(builder) {
    const locale = builder.dataset.locale;
    const blocks = builder.querySelectorAll('[data-blocks-wrapper] > [data-block]');

    blocks.forEach((block, blockIndex) => {
        updateBlockLabel(block, blockIndex);

        const blockFields = block.querySelectorAll('[name]');

        blockFields.forEach((field) => {
            const originalName = field.getAttribute('name');
            if (!originalName) return;

            const blockName = originalName.replace(
                new RegExp(`description_blocks\\[${locale}\\]\\[(?:\\d+|__block__)\\]`, 'g'),
                `description_blocks[${locale}][${blockIndex}]`
            );

            field.setAttribute('name', blockName);
        });

        // Reindex gallery items
        const galleryItems = block.querySelectorAll('[data-gallery-items-wrapper] > [data-gallery-item]');

        galleryItems.forEach((item, itemIndex) => {
            const itemFields = item.querySelectorAll('[name]');

            itemFields.forEach((field) => {
                const itemName = field.getAttribute('name');
                if (!itemName) return;

                field.setAttribute(
                    'name',
                    itemName.replace(/\[items\]\[(?:\d+|__item__)\]/g, `[items][${itemIndex}]`)
                );
            });
        });

        // Reindex text_column_row items
        const tcItems = block.querySelectorAll('[data-tc-items-wrapper] > [data-tc-item]');

        tcItems.forEach((item, itemIndex) => {
            const itemFields = item.querySelectorAll('[name]');

            itemFields.forEach((field) => {
                const itemName = field.getAttribute('name');
                if (!itemName) return;

                field.setAttribute(
                    'name',
                    itemName.replace(/\[items\]\[(?:\d+|__item__)\]/g, `[items][${itemIndex}]`)
                );
            });
        });
    });
}

function updateBlockLabel(block, blockIndex) {
    const labelEl = block.querySelector('[data-block-label]');
    if (!labelEl) return;

    const type = block.querySelector('[data-block-type-input]')?.value || 'text';
    const typeLabel = type === 'floating_gallery' ? 'Floating Gallery'
        : type === 'text_column_row' ? 'Text Column Row'
        : 'Content';

    labelEl.textContent = `Block ${blockIndex + 1} - ${typeLabel}`;
}
