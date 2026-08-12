/**
 * AdminColorLines.js
 * Componente Vanilla JS (ES6+) per la gestione Back-Office delle linee di colore prodotti
 * Utilizza fetch() con application/x-www-form-urlencoded e la dialog HTML5 nativa
 */
class AdminColorLines {
    constructor() {
        this.ajaxUrl = typeof adminAjaxUrl !== 'undefined' ? adminAjaxUrl : '';
        this.tableEl = document.getElementById('mpcolorproducts-table');
        this.dialogEl = document.getElementById('color-line-dialog');
        this.productsTbody = document.getElementById('selected-products-tbody');
        this.rowTemplate = document.getElementById('product-row-template');
        this.searchInput = document.getElementById('product-search-input');
        this.searchResultsDropdown = document.getElementById('search-results-dropdown');

        this.init();
    }

    init() {
        this.bindEvents();
        this.bindConfigFormSubmit();
        this.loadTableData();
    }

    bindConfigFormSubmit() {
        const configForm = document.getElementById('mpcolorproducts-config-form');
        if (!configForm) return;

        configForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const attrGroupSelect = configForm.querySelector('[name="MPCOLORPRODUCTS_ATTRIBUTE_GROUP_ID[]"]');
            const selectedAttrGroups = attrGroupSelect ? Array.from(attrGroupSelect.selectedOptions).map(opt => opt.value) : [];
            const displayMode = configForm.querySelector('[name="MPCOLORPRODUCTS_DISPLAY_MODE"]').value;
            const hideCurrentRadio = configForm.querySelector('[name="MPCOLORPRODUCTS_HIDE_CURRENT"]:checked');
            const hideCurrent = hideCurrentRadio ? parseInt(hideCurrentRadio.value, 10) : 0;
            const enableFeatureFilterRadio = configForm.querySelector('[name="MPCOLORPRODUCTS_ENABLE_FEATURE_FILTER"]:checked');
            const enableFeatureFilter = enableFeatureFilterRadio ? parseInt(enableFeatureFilterRadio.value, 10) : 1;
            const showAllColorsRadio = configForm.querySelector('[name="MPCOLORPRODUCTS_SHOW_ALL_COLORS"]:checked');
            const showAllColors = showAllColorsRadio ? parseInt(showAllColorsRadio.value, 10) : 0;
            const imageType = configForm.querySelector('[name="MPCOLORPRODUCTS_IMAGE_TYPE"]').value;

            const hideGroupsSelect = configForm.querySelector('[name="MPCOLORPRODUCTS_HIDE_ATTR_GROUPS[]"]');
            const selectedHideGroups = hideGroupsSelect ? Array.from(hideGroupsSelect.selectedOptions).map(opt => opt.value) : [];

            const btnSave = document.getElementById('btn-save-config');
            if (btnSave) btnSave.disabled = true;

            const res = await this.makeAjaxRequest({
                action: 'saveConfig',
                'MPCOLORPRODUCTS_ATTRIBUTE_GROUP_ID[]': selectedAttrGroups,
                MPCOLORPRODUCTS_DISPLAY_MODE: displayMode,
                MPCOLORPRODUCTS_HIDE_CURRENT: hideCurrent,
                MPCOLORPRODUCTS_ENABLE_FEATURE_FILTER: enableFeatureFilter,
                MPCOLORPRODUCTS_SHOW_ALL_COLORS: showAllColors,
                MPCOLORPRODUCTS_IMAGE_TYPE: imageType,
                'MPCOLORPRODUCTS_HIDE_ATTR_GROUPS[]': selectedHideGroups
            });

            if (btnSave) btnSave.disabled = false;

            const alertContainer = document.getElementById('config-alert-container');
            if (res && res.success) {
                if (alertContainer) {
                    alertContainer.innerHTML = `
                        <div class="alert alert-success d-flex align-items-center mb-4 p-3" style="border-radius: 8px;" role="alert">
                            <i class="material-icons me-2 text-success">check_circle</i>
                            <strong>${res.message || 'Impostazioni aggiornate con successo.'}</strong>
                        </div>
                    `;
                    setTimeout(() => {
                        alertContainer.innerHTML = '';
                    }, 4000);
                }
            } else {
                if (alertContainer) {
                    alertContainer.innerHTML = `
                        <div class="alert alert-danger d-flex align-items-center mb-4 p-3" style="border-radius: 8px;" role="alert">
                            <i class="material-icons me-2 text-danger">error</i>
                            <strong>Errore nel salvataggio delle impostazioni.</strong>
                        </div>
                    `;
                }
            }
        });
    }

    bindEvents() {
        // Apri dialog per nuova linea
        const btnNew = document.getElementById('btn-open-new-line-modal');
        if (btnNew) {
            btnNew.addEventListener('click', () => this.openDialog(0));
        }

        // Chiudi dialog
        const btnCloseTop = document.getElementById('dialog-close-top');
        const btnCancel = document.getElementById('btn-dialog-cancel');
        if (btnCloseTop) btnCloseTop.addEventListener('click', () => this.closeDialog());
        if (btnCancel) btnCancel.addEventListener('click', () => this.closeDialog());

        // Salva dialog
        const btnSave = document.getElementById('btn-dialog-save');
        if (btnSave) {
            btnSave.addEventListener('click', () => this.saveLineGroup());
        }

        // Ricerca prodotto
        const btnSearch = document.getElementById('btn-search-products');
        if (btnSearch) {
            btnSearch.addEventListener('click', () => this.searchProducts());
        }
        if (this.searchInput) {
            this.searchInput.addEventListener('keyup', (e) => {
                if (e.key === 'Enter') {
                    this.searchProducts();
                }
            });
        }

        // Delega eventi di eliminazione riga ed applicazione caratteristiche a tutti
        if (this.productsTbody) {
            this.productsTbody.addEventListener('click', (e) => {
                const btnCopy = e.target.closest('.btn-copy-features-row');
                if (btnCopy) {
                    const currentTr = btnCopy.closest('tr');
                    if (currentTr) {
                        this.applyRowFeaturesToAll(currentTr);
                    }
                }
                const btnRemove = e.target.closest('.btn-remove-product');
                if (btnRemove) {
                    const tr = btnRemove.closest('tr');
                    if (tr) tr.remove();
                }
            });
        }

        const btnApplyFirstAll = document.getElementById('btn-apply-first-features-all');
        if (btnApplyFirstAll) {
            btnApplyFirstAll.addEventListener('click', () => {
                const firstTr = this.productsTbody.querySelector('tr[data-product-id]');
                if (firstTr) {
                    this.applyRowFeaturesToAll(firstTr);
                } else {
                    alert('Nessun prodotto presente nella linea.');
                }
            });
        }

        // Chiudi il menu a discesa dei risultati di ricerca al click esterno
        document.addEventListener('click', (e) => {
            if (this.searchResultsDropdown && 
                !this.searchResultsDropdown.contains(e.target) && 
                e.target !== this.searchInput && 
                e.target !== document.getElementById('btn-search-products')) {
                this.searchResultsDropdown.innerHTML = '';
            }
        });
    }

    /**
     * Applica le TIPOLOGIE di caratteristiche selezionate in una riga sorgente a tutte le altre righe della linea
     */
    applyRowFeaturesToAll(sourceTr) {
        const sourceSelect = sourceTr.querySelector('.product-features-select');
        if (!sourceSelect) return;

        const selectedOptions = Array.from(sourceSelect.selectedOptions);
        if (selectedOptions.length === 0) {
            alert('Seleziona almeno una caratteristica in questa riga prima di applicare a tutti.');
            return;
        }

        // Raccogliamo gli ID caratteristica e i Nomi caratteristica selezionati
        const targetFeatureIds = new Set();
        const targetFeatureNames = new Set();

        selectedOptions.forEach(opt => {
            if (opt.dataset.idFeature) {
                targetFeatureIds.add(String(opt.dataset.idFeature));
            }
            if (opt.dataset.featureName) {
                targetFeatureNames.add(opt.dataset.featureName.trim().toLowerCase());
            }
        });

        const allRows = this.productsTbody.querySelectorAll('tr[data-product-id]');
        let countUpdated = 0;

        allRows.forEach(tr => {
            const select = tr.querySelector('.product-features-select');
            if (!select) return;

            let changed = false;

            Array.from(select.options).forEach(opt => {
                const optFeatId = String(opt.dataset.idFeature || '');
                const optFeatName = (opt.dataset.featureName || '').trim().toLowerCase();

                // Verifica corrispondenza per id_feature o per nome caratteristica (es. "Composizione Tessuto:")
                const isMatch = (optFeatId && targetFeatureIds.has(optFeatId)) ||
                                (optFeatName && targetFeatureNames.has(optFeatName)) ||
                                Array.from(targetFeatureNames).some(name => opt.textContent.toLowerCase().startsWith(name + ':'));

                if (isMatch) {
                    if (!opt.selected) {
                        opt.selected = true;
                        changed = true;
                    }
                }
            });

            if (changed || tr !== sourceTr) {
                countUpdated++;
                if (typeof $ !== 'undefined' && $.fn.chosen) {
                    $(select).trigger('chosen:updated');
                }
            }
        });

        const alertContainer = document.getElementById('config-alert-container');
        if (alertContainer) {
            alertContainer.innerHTML = `
                <div class="alert alert-info d-flex align-items-center mb-3 p-2" style="border-radius: 6px; font-size: 13px;" role="alert">
                    <i class="material-icons me-2 text-info fs-5">info</i>
                    <span>Caratteristiche applicate a <strong>${countUpdated}</strong> prodotti della linea.</span>
                </div>
            `;
            setTimeout(() => { alertContainer.innerHTML = ''; }, 4000);
        }
    }

    /**
     * Esegue chiamate AJAX usando fetch() esclusivamente in application/x-www-form-urlencoded
     */
    async makeAjaxRequest(params) {
        const bodyParams = new URLSearchParams();
        bodyParams.append('ajax', '1');
        
        for (const [key, value] of Object.entries(params)) {
            if (Array.isArray(value)) {
                value.forEach(val => bodyParams.append(key, val));
            } else {
                bodyParams.append(key, value);
            }
        }

        try {
            const response = await fetch(this.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: bodyParams.toString()
            });

            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('AJAX Request Error:', error);
            alert('Si è verificato un errore di comunicazione con il server.');
            return null;
        }
    }

    async loadTableData() {
        if (!this.tableEl) return;

        const data = await this.makeAjaxRequest({ action: 'getGroupsList' });
        if (data && Array.isArray(data)) {
            if (typeof $ !== 'undefined' && $.fn.bootstrapTable) {
                $(this.tableEl).bootstrapTable('load', data);
            }
        }
    }

    async openDialog(idGroup = 0) {
        document.getElementById('line-id-group').value = idGroup;
        document.getElementById('line-name').value = '';
        document.getElementById('line-active').value = '1';
        this.productsTbody.innerHTML = '';
        if (this.searchResultsDropdown) this.searchResultsDropdown.innerHTML = '';

        if (idGroup > 0) {
            document.getElementById('dialog-title').textContent = 'Modifica Linea Colore #' + idGroup;
            const res = await this.makeAjaxRequest({
                action: 'getGroupDetails',
                id_group: idGroup
            });

            if (res && res.success) {
                document.getElementById('line-name').value = res.group.name || '';
                document.getElementById('line-active').value = res.group.active || '1';

                if (Array.isArray(res.products)) {
                    res.products.forEach(p => {
                        this.addProductRow(
                            p.id_product,
                            p.product_name,
                            p.reference,
                            p.ean13 || '',
                            p.cover_url,
                            p.id_attribute,
                            false,
                            p.product_url,
                            p.available_features || [],
                            p.features || [],
                            p.color_name || '',
                            p.color_hex || '#ffffff',
                            p.texture_url || ''
                        );
                    });
                }
            }
        } else {
            document.getElementById('dialog-title').textContent = 'Nuova Linea Colore';
        }

        if (this.dialogEl.showModal) {
            this.dialogEl.showModal();
        } else {
            this.dialogEl.setAttribute('open', 'true');
        }
    }

    closeDialog() {
        if (this.dialogEl.close) {
            this.dialogEl.close();
        } else {
            this.dialogEl.removeAttribute('open');
        }
    }

    async searchProducts() {
        const query = this.searchInput.value.trim();
        if (!query) return;

        this.searchResultsDropdown.innerHTML = '<div class="p-3 text-center"><i class="icon-spinner icon-spin"></i> Ricerca in corso...</div>';

        const res = await this.makeAjaxRequest({
            action: 'searchProducts',
            query: query
        });

        this.searchResultsDropdown.innerHTML = '';

        if (res && res.success && Array.isArray(res.products) && res.products.length > 0) {
            // Tabella per la selezione multipla con checkbox
            const tableHtml = document.createElement('div');
            tableHtml.className = 'search-batch-wrapper';
            tableHtml.innerHTML = `
                <div class="search-batch-toolbar">
                    <label class="checkbox-inline">
                        <input type="checkbox" id="select-all-search-products"> <strong>Seleziona Tutti (${res.products.length})</strong>
                    </label>
                    <button type="button" id="btn-add-selected-products" class="btn btn-success btn-xs pull-right" disabled>
                        <i class="icon-plus-sign"></i> Aggiungi Selezionati (<span id="selected-count">0</span>)
                    </button>
                </div>
                <table class="table table-condensed table-hover search-batch-table">
                    <thead>
                        <tr>
                            <th style="width: 30px;" class="text-center">#</th>
                            <th style="width: 40px;">Foto</th>
                            <th>ID & Prodotto</th>
                            <th>Riferimento</th>
                            <th>EAN13</th>
                        </tr>
                    </thead>
                    <tbody id="search-products-list-tbody">
                    </tbody>
                </table>
            `;

            this.searchResultsDropdown.appendChild(tableHtml);

            const tbody = tableHtml.querySelector('#search-products-list-tbody');
            const selectAllCb = tableHtml.querySelector('#select-all-search-products');
            const btnAddSelected = tableHtml.querySelector('#btn-add-selected-products');
            const selectedCountSpan = tableHtml.querySelector('#selected-count');

            res.products.forEach(p => {
                const tr = document.createElement('tr');
                const eanVal = (p.ean13 || '').trim();
                const attrIdVal = p.detected_attribute_id || 0;

                // Verifica duplicati
                const isAlreadyAdded = Array.from(this.productsTbody.querySelectorAll('tr[data-product-id]')).some(existingTr => {
                    const trEan = (existingTr.getAttribute('data-ean13') || '').trim();
                    const trIdP = parseInt(existingTr.getAttribute('data-product-id'), 10);
                    const trAttrId = parseInt(existingTr.querySelector('.product-attr-input').value, 10) || 0;

                    if (eanVal !== '' && trEan !== '' && eanVal === trEan) {
                        return true;
                    }
                    return (trIdP === p.id_product && trAttrId === attrIdVal);
                });
                
                tr.innerHTML = `
                    <td class="text-center">
                        <input type="checkbox" class="search-product-cb" 
                               data-id="${p.id_product}" 
                               data-name="${p.name.replace(/"/g, '&quot;')}" 
                               data-ref="${(p.reference || '').replace(/"/g, '&quot;')}" 
                               data-ean="${(p.ean13 || '').replace(/"/g, '&quot;')}" 
                               data-cover="${p.cover_url || ''}" 
                               data-url="${p.product_url || ''}" 
                               data-attr="${attrIdVal}"
                               data-color-name="${(p.color_name || '').replace(/"/g, '&quot;')}"
                               data-color-hex="${p.color_hex || '#ffffff'}"
                               data-texture-url="${p.texture_url || ''}"
                               ${isAlreadyAdded ? 'disabled title="Già presente nella linea"' : ''}>
                    </td>
                    <td>
                        <img src="${p.cover_url || ''}" width="28" height="28" style="border-radius:50%; object-fit:cover;">
                    </td>
                    <td>
                        <strong>[#${p.id_product}]</strong> ${p.name}
                        ${isAlreadyAdded ? '<span class="label label-warning" style="margin-left: 5px;">Già presente</span>' : ''}
                    </td>
                    <td>${p.reference || '-'}</td>
                    <td>${p.ean13 || '-'}</td>
                `;

                const cb = tr.querySelector('.search-product-cb');
                if (cb) {
                    cb._availableFeatures = p.available_features || [];
                }

                tbody.appendChild(tr);
            });

            // Gestione Checkbox Seleziona Tutti e contatore
            const updateCount = () => {
                const checkedCbs = tbody.querySelectorAll('.search-product-cb:checked');
                const count = checkedCbs.length;
                selectedCountSpan.textContent = count;
                btnAddSelected.disabled = (count === 0);
            };

            if (selectAllCb) {
                selectAllCb.addEventListener('change', (e) => {
                    const cbs = tbody.querySelectorAll('.search-product-cb:not([disabled])');
                    cbs.forEach(cb => cb.checked = e.target.checked);
                    updateCount();
                });
            }

            tbody.addEventListener('change', (e) => {
                if (e.target.classList.contains('search-product-cb')) {
                    updateCount();
                }
            });

            // Aggiungi tutti i prodotti selezionati in un unico click
            btnAddSelected.addEventListener('click', () => {
                const checkedCbs = tbody.querySelectorAll('.search-product-cb:checked');
                let addedCount = 0;
                checkedCbs.forEach(cb => {
                    const idP = parseInt(cb.getAttribute('data-id'), 10);
                    const name = cb.getAttribute('data-name');
                    const ref = cb.getAttribute('data-ref');
                    const ean = cb.getAttribute('data-ean');
                    const cover = cb.getAttribute('data-cover');
                    const url = cb.getAttribute('data-url');
                    const attr = parseInt(cb.getAttribute('data-attr'), 10);
                    const availFeats = cb._availableFeatures || [];
                    const cName = cb.getAttribute('data-color-name') || '';
                    const cHex = cb.getAttribute('data-color-hex') || '#ffffff';
                    const tUrl = cb.getAttribute('data-texture-url') || '';

                    const added = this.addProductRow(idP, name, ref, ean, cover, attr, false, url, availFeats, [], cName, cHex, tUrl);
                    if (added) addedCount++;
                });

                this.searchResultsDropdown.innerHTML = '';
                this.searchInput.value = '';
            });

        } else {
            this.searchResultsDropdown.innerHTML = '<div class="p-3 text-danger text-center">Nessun prodotto trovato.</div>';
        }
    }

    /**
     * Aggiunge una riga prodotto alla linea (discernendo per EAN13 ed id_product + id_attribute)
     */
    addProductRow(idProduct, name, ref, ean13 = '', coverUrl = '', attrId = 0, showAlertOnDuplicate = true, productUrl = '', availableFeatures = [], savedFeatures = [], colorName = '', colorHex = '#ffffff', textureUrl = '') {
        const targetEan = (ean13 || '').trim();
        const trs = Array.from(this.productsTbody.querySelectorAll('tr[data-product-id]'));

        // Discerni per EAN13 se presente, altrimenti per id_product + id_attribute. MAI per reference.
        const existing = trs.find(tr => {
            const trEan = (tr.getAttribute('data-ean13') || '').trim();
            const trIdP = parseInt(tr.getAttribute('data-product-id'), 10);
            const trAttrId = parseInt(tr.querySelector('.product-attr-input').value, 10) || 0;

            if (targetEan !== '' && trEan !== '' && targetEan === trEan) {
                return true;
            }
            return (trIdP === idProduct && trAttrId === attrId);
        });

        if (existing) {
            if (showAlertOnDuplicate) {
                alert('Il prodotto/combinazione è già stato inserito in questa linea.');
            }
            return false;
        }

        const clone = this.rowTemplate.content.cloneNode(true);
        const tr = clone.querySelector('tr');
        tr.setAttribute('data-product-id', idProduct);
        tr.setAttribute('data-ean13', targetEan);

        const img = tr.querySelector('.product-thumb-preview');
        img.src = coverUrl || '';

        const idTd = tr.querySelector('.product-id-td');
        if (idTd) idTd.textContent = '#' + idProduct;

        tr.querySelector('.product-name-td').textContent = name;
        tr.querySelector('.product-ref-td').textContent = ref || '-';

        const eanTd = tr.querySelector('.product-ean-td');
        if (eanTd) eanTd.textContent = targetEan || '-';

        const btnPreview = tr.querySelector('.btn-preview-product');
        if (btnPreview) {
            if (productUrl) {
                btnPreview.href = productUrl;
            } else {
                btnPreview.style.display = 'none';
            }
        }

        const nameText = tr.querySelector('.product-color-name-text');
        if (nameText) {
            nameText.textContent = colorName || ('#' + attrId);
        }

        const circle = tr.querySelector('.product-color-swatch-circle');
        if (circle) {
            if (textureUrl) {
                circle.style.backgroundImage = 'url(' + textureUrl + ')';
                circle.style.backgroundColor = 'transparent';
            } else {
                circle.style.backgroundColor = colorHex || '#ffffff';
                circle.style.backgroundImage = 'none';
            }
        }

        const inputAttr = tr.querySelector('.product-attr-input');
        inputAttr.value = attrId || 0;

        const featuresSelect = tr.querySelector('.product-features-select');
        if (featuresSelect && Array.isArray(availableFeatures)) {
            featuresSelect.innerHTML = '';
            const savedSet = new Set(Array.isArray(savedFeatures) ? savedFeatures.map(Number) : []);

            availableFeatures.forEach(f => {
                const opt = document.createElement('option');
                const valId = parseInt(f.id_feature_value, 10);
                opt.value = valId;
                opt.dataset.idFeature = f.id_feature || 0;
                opt.dataset.featureName = f.feature_name || '';
                opt.textContent = `${f.feature_name}: ${f.feature_value}`;
                if (savedSet.has(valId)) {
                    opt.selected = true;
                }
                featuresSelect.appendChild(opt);
            });
        }

        this.productsTbody.appendChild(tr);

        if (featuresSelect && typeof $ !== 'undefined' && $.fn.chosen) {
            $(featuresSelect).chosen({
                width: '100%',
                placeholder_text_multiple: 'Seleziona caratteristiche...'
            });
        }

        return true;
    }

    async saveLineGroup() {
        const idGroup = document.getElementById('line-id-group').value;
        const name = document.getElementById('line-name').value.trim();
        const active = document.getElementById('line-active').value;

        if (!name) {
            alert('Inserisci il nome della linea.');
            return;
        }

        const rows = this.productsTbody.querySelectorAll('tr[data-product-id]');
        const productsList = [];
        rows.forEach(r => {
            const idP = r.getAttribute('data-product-id');
            const attrId = r.querySelector('.product-attr-input').value;
            const featSelect = r.querySelector('.product-features-select');
            const selectedFeatures = featSelect ? Array.from(featSelect.selectedOptions).map(opt => parseInt(opt.value, 10)) : [];

            productsList.push({
                id_product: parseInt(idP, 10),
                id_attribute: parseInt(attrId, 10) || 0,
                features: selectedFeatures
            });
        });

        if (productsList.length === 0) {
            alert('Aggiungi almeno un prodotto alla linea prima di salvare.');
            return;
        }

        const res = await this.makeAjaxRequest({
            action: 'saveGroup',
            id_group: idGroup,
            name: name,
            active: active,
            products_json: JSON.stringify(productsList)
        });

        if (res && res.success) {
            this.closeDialog();
            this.loadTableData();
        } else {
            alert((res && res.message) ? res.message : 'Errore nel salvataggio della linea.');
        }
    }

    async deleteGroup(idGroup) {
        if (!confirm('Sei sicuro di voler eliminare questa linea di colore?')) {
            return;
        }

        const res = await this.makeAjaxRequest({
            action: 'deleteGroup',
            id_group: idGroup
        });

        if (res && res.success) {
            this.loadTableData();
        } else {
            alert('Errore durante l\'eliminazione della linea.');
        }
    }
}

// Formattatori globali per Bootstrap Table
function activeFormatter(value) {
    if (parseInt(value, 10) === 1) {
        return '<span class="badge bg-success badge-success">Attivo</span>';
    }
    return '<span class="badge bg-danger badge-danger">Disattivato</span>';
}

function actionsFormatter(value, row) {
    return `
        <button type="button" class="btn btn-outline-secondary btn-sm me-1" onclick="window.adminColorLinesApp.openDialog(${row.id_mpcolorproducts_group})">
            <i class="material-icons">edit</i> Modifica
        </button>
        <button type="button" class="btn btn-outline-danger btn-sm" onclick="window.adminColorLinesApp.deleteGroup(${row.id_mpcolorproducts_group})">
            <i class="material-icons">delete</i> Elimina
        </button>
    `;
}

// Inizializzazione al caricamento della pagina DOM
document.addEventListener('DOMContentLoaded', () => {
    window.adminColorLinesApp = new AdminColorLines();
});
