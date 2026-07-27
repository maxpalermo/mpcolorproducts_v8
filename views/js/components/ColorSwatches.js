/**
 * ColorSwatches.js
 * Componente Vanilla JS (ES6+) per la scheda prodotto frontend
 * Intercetta .product-variants, nasconde i gruppi colore nativi (es. group[14], group[15])
 * ed inserisce il blocco delle linee colore del modulo al loro posto.
 */
class ColorSwatches {
    constructor() {
        this.container = document.getElementById('mpcolorproducts-block');
        if (!this.container) return;

        this.nameLabel = document.getElementById('mpcolorproducts-current-name');
        this.swatches = this.container.querySelectorAll('.mpcolorproducts-swatch-item');
        this.initialColorName = this.nameLabel ? this.nameLabel.textContent : '';

        this.init();
    }

    init() {
        this.replaceColorAttributeGroup();
        this.bindEvents();
        this.listenPrestaShopEvents();
    }

    bindEvents() {
        if (this.swatches && this.swatches.length > 0) {
            this.swatches.forEach(swatch => {
                swatch.addEventListener('mouseenter', () => {
                    const colorName = swatch.getAttribute('data-color-name');
                    if (colorName && this.nameLabel) {
                        this.nameLabel.textContent = colorName;
                    }
                });

                swatch.addEventListener('mouseleave', () => {
                    if (this.nameLabel) {
                        this.nameLabel.textContent = this.initialColorName;
                    }
                });
            });
        }
    }

    /**
     * Intercetta div.product-variants, nasconde i gruppi colore nativi (group[14], group[15] ecc.)
     * ed inserisce il blocco del modulo al loro posto.
     */
    replaceColorAttributeGroup() {
        const productVariants = document.querySelector('.product-variants, .js-product-variants');
        
        // Se non troviamo il contenitore varianti principale, utilizziamo il fallback generale
        if (!productVariants) {
            this.fallbackRelocate();
            return;
        }

        const variantItems = productVariants.querySelectorAll('.product-variants-item');
        let firstHiddenItem = null;

        variantItems.forEach(item => {
            // Verifica se l'item contiene input di colore o attributi group[14], group[15] o etichette COLORE / Rifiniture
            const hasColorInput = item.querySelector('input.input-color, input[name="group[14]"], input[name="group[15]"]');
            const labelEl = item.querySelector('.control-label, .attribute_label, label');
            const labelText = labelEl ? labelEl.textContent.trim().toLowerCase() : '';

            const isColorOrFinishGroup = hasColorInput ||
                labelText.includes('colore') ||
                labelText.includes('color') ||
                labelText.includes('rifiniture');

            if (isColorOrFinishGroup) {
                item.style.display = 'none';
                if (!firstHiddenItem) {
                    firstHiddenItem = item;
                }
            }
        });

        if (firstHiddenItem && firstHiddenItem.parentNode) {
            firstHiddenItem.parentNode.insertBefore(this.container, firstHiddenItem);
        } else {
            productVariants.appendChild(this.container);
        }
    }

    /**
     * Fallback per temi personalizzati privi di .product-variants
     */
    fallbackRelocate() {
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
            target.parentNode.insertBefore(this.container, target.nextSibling);
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
