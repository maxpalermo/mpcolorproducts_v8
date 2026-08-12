/**
 * ColorSwatches.js
 * Componente Vanilla JS (ES6+) per la scheda prodotto frontend
 * Intercetta .product-variants, nasconde i gruppi attributo configurati nel BO (group[<id>])
 * posiziona il blocco delle linee colore al loro posto, gestisce il selettore Caratteristiche AJAX e rimuove duplicati.
 */
class ColorSwatches {
    constructor() {
        this.init();
    }

    init() {
        this.replaceColorAttributeGroup();
        this.bindEvents();
        this.bindFeaturePillEvents();
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
     * Gestisce i click sui pill delle Caratteristiche ed invia le chiamate AJAX per aggiornare i colori
     */
    bindFeaturePillEvents() {
        const container = document.querySelector('.mpcolorproducts-container');
        if (!container) return;

        const featurePills = container.querySelectorAll('.mp-feature-pill');
        if (!featurePills || featurePills.length === 0) return;

        featurePills.forEach(pill => {
            pill.addEventListener('click', (e) => {
                e.preventDefault();
                const parentGroup = pill.closest('.mpcolorproducts-feature-group');
                if (parentGroup) {
                    const siblingPills = parentGroup.querySelectorAll('.mp-feature-pill');
                    siblingPills.forEach(p => p.classList.remove('active'));
                }
                pill.classList.add('active');

                const activePills = container.querySelectorAll('.mp-feature-pill.active');
                const activeFeatureValueIds = [];
                activePills.forEach(p => {
                    const valId = parseInt(p.getAttribute('data-feature-value-id'), 10);
                    if (valId > 0) {
                        activeFeatureValueIds.push(valId);
                    }
                });

                const ajaxUrl = container.getAttribute('data-ajax-url');
                if (!ajaxUrl) return;

                const swatchesList = container.querySelector('#mpcolorproducts-swatches-list');
                if (swatchesList) {
                    swatchesList.style.opacity = '0.5';
                }

                const reqUrl = ajaxUrl + '&feature_value_ids=' + activeFeatureValueIds.join(',');

                fetch(reqUrl)
                    .then(res => res.json())
                    .then(data => {
                        if (swatchesList) {
                            swatchesList.style.opacity = '1';
                        }
                        if (data.success && data.html) {
                            if (swatchesList) {
                                swatchesList.innerHTML = data.html;
                            }
                            this.bindEvents();
                        }
                    })
                    .catch(err => {
                        console.error('Errore durante l\'aggiornamento delle varianti colore:', err);
                        if (swatchesList) {
                            swatchesList.style.opacity = '1';
                        }
                    });
            });
        });
    }

    /**
     * Intercetta div.product-variants, nasconde i gruppi attributi configurati (group[<id>])
     * ed inserisce il blocco del modulo al loro posto.
     */
    replaceColorAttributeGroup() {
        const containers = document.querySelectorAll('.mpcolorproducts-container');
        if (!containers || containers.length === 0) return;

        const primaryContainer = containers[0];

        for (let i = 1; i < containers.length; i++) {
            containers[i].remove();
        }

        const hideAttrGroupsStr = primaryContainer.getAttribute('data-hide-attr-groups') || '';
        const configuredHideGroupIds = hideAttrGroupsStr
            .split(',')
            .map(id => id.trim())
            .filter(id => id.length > 0);

        const productVariants = document.querySelector('.product-variants, .js-product-variants');
        
        if (!productVariants) {
            this.fallbackRelocate(primaryContainer);
            return;
        }

        const variantItems = productVariants.querySelectorAll('.product-variants-item');
        let firstHiddenItem = null;

        variantItems.forEach(item => {
            if (item.classList.contains('mpcolorproducts-container') || item.id === 'mpcolorproducts-block') {
                return;
            }

            let isConfiguredGroup = false;

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
