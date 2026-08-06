/**
 * ColorSwatches.js
 * Componente Vanilla JS (ES6+) per la scheda prodotto frontend
 * Intercetta .product-variants, nasconde i gruppi attributo configurati nel BO (group[<id>])
 * posiziona il blocco delle linee colore al loro posto e rimuove eventuali duplicati dal DOM.
 */
class ColorSwatches {
    constructor() {
        this.init();
    }

    init() {
        this.replaceColorAttributeGroup();
        this.bindEvents();
        this.listenPrestaShopEvents();
    }

    bindEvents() {
        const container = document.querySelector('.mpcolorproducts-container');
        if (!container) return;

        const nameLabel = container.querySelector('#mpcolorproducts-current-name');
        const swatches = container.querySelectorAll('.mpcolorproducts-swatch-item');
        const initialColorName = nameLabel ? nameLabel.textContent : '';

        if (swatches && swatches.length > 0) {
            swatches.forEach(swatch => {
                swatch.addEventListener('mouseenter', () => {
                    const colorName = swatch.getAttribute('data-color-name');
                    if (colorName && nameLabel) {
                        nameLabel.textContent = colorName;
                    }
                });

                swatch.addEventListener('mouseleave', () => {
                    if (nameLabel) {
                        nameLabel.textContent = initialColorName;
                    }
                });
            });
        }
    }

    /**
     * Intercetta div.product-variants, nasconde i gruppi attributi configurati (group[<id>])
     * ed inserisce il blocco del modulo al loro posto.
     */
    replaceColorAttributeGroup() {
        const containers = document.querySelectorAll('.mpcolorproducts-container');
        if (!containers || containers.length === 0) return;

        // Il primo contenitore è quello principale che verrà posizionato vicino alle varianti
        const primaryContainer = containers[0];

        // Rimuoviamo dal DOM tutti i contenitori duplicati (es. generati da displayFooterProduct)
        for (let i = 1; i < containers.length; i++) {
            containers[i].remove();
        }

        // Recuperiamo gli ID dei gruppi attributo da nascondere dall'attributo data-hide-attr-groups
        const hideAttrGroupsStr = primaryContainer.getAttribute('data-hide-attr-groups') || '';
        const configuredHideGroupIds = hideAttrGroupsStr
            .split(',')
            .map(id => id.trim())
            .filter(id => id.length > 0);

        const productVariants = document.querySelector('.product-variants, .js-product-variants');
        
        // Se non troviamo il contenitore varianti principale, utilizziamo il fallback generale
        if (!productVariants) {
            this.fallbackRelocate(primaryContainer);
            return;
        }

        const variantItems = productVariants.querySelectorAll('.product-variants-item');
        let firstHiddenItem = null;

        variantItems.forEach(item => {
            // Ignoriamo il nostro stesso blocco se già inserito
            if (item.classList.contains('mpcolorproducts-container') || item.id === 'mpcolorproducts-block') {
                return;
            }

            let isConfiguredGroup = false;

            // 1. Verifica dinamica degli ID attributi configurati nel Back-Office (group[<id>])
            if (configuredHideGroupIds.length > 0) {
                for (const groupId of configuredHideGroupIds) {
                    const groupInput = item.querySelector(
                        `input[name="group[${groupId}]"], select[name="group[${groupId}]"], input[data-product-attribute="${groupId}"]`
                    );
                    if (groupInput) {
                        isConfiguredGroup = true;
                        break;
                    }
                }
            }

            // 2. Fallback di ricerca per etichetta o input.input-color se non sono stati trovati match sugli ID
            if (!isConfiguredGroup) {
                const hasColorInput = item.querySelector('input.input-color');
                const labelEl = item.querySelector('.control-label, .attribute_label, label');
                const labelText = labelEl ? labelEl.textContent.trim().toLowerCase() : '';

                if (hasColorInput || labelText.includes('colore') || labelText.includes('color') || labelText.includes('rifiniture')) {
                    isConfiguredGroup = true;
                }
            }

            if (isConfiguredGroup) {
                item.style.display = 'none';
                if (!firstHiddenItem) {
                    firstHiddenItem = item;
                }
            }
        });

        if (firstHiddenItem && firstHiddenItem.parentNode) {
            firstHiddenItem.parentNode.insertBefore(primaryContainer, firstHiddenItem);
        } else {
            productVariants.appendChild(primaryContainer);
        }
    }

    /**
     * Fallback per temi personalizzati privi di .product-variants
     */
    fallbackRelocate(primaryContainer) {
        const targetElements = [
            document.querySelector('#attributes .attribute_fieldset'),
            document.querySelector('#attributes'),
            document.querySelector('.product-actions')
        ];

        let target = null;
        for (const el of targetElements) {
            if (el) {
                target = el;
                break;
            }
        }

        if (target && target.parentNode) {
            target.parentNode.insertBefore(primaryContainer, target.nextSibling);
        }
    }

    /**
     * Ascolta gli eventi AJAX di PrestaShop 8 per mantenere nascosti i gruppi nativi dopo i cambi variante
     */
    listenPrestaShopEvents() {
        if (typeof prestashop !== 'undefined' && prestashop.on) {
            prestashop.on('updatedProduct', () => {
                setTimeout(() => this.replaceColorAttributeGroup(), 80);
            });
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new ColorSwatches();
});
