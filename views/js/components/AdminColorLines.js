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

            const attrGroupId = configForm.querySelector('[name="MPCOLORPRODUCTS_ATTRIBUTE_GROUP_ID"]').value;
            const displayMode = configForm.querySelector('[name="MPCOLORPRODUCTS_DISPLAY_MODE"]').value;
            const hideCurrentRadio = configForm.querySelector('[name="MPCOLORPRODUCTS_HIDE_CURRENT"]:checked');
            const hideCurrent = hideCurrentRadio ? parseInt(hideCurrentRadio.value, 10) : 0;
            const imageType = configForm.querySelector('[name="MPCOLORPRODUCTS_IMAGE_TYPE"]').value;

            const btnSave = document.getElementById('btn-save-config');
            if (btnSave) btnSave.disabled = true;

            const res = await this.makeAjaxRequest({
                action: 'saveConfig',
                MPCOLORPRODUCTS_ATTRIBUTE_GROUP_ID: attrGroupId,
                MPCOLORPRODUCTS_DISPLAY_MODE: displayMode,
                MPCOLORPRODUCTS_HIDE_CURRENT: hideCurrent,
                MPCOLORPRODUCTS_IMAGE_TYPE: imageType
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

        // Delega eventi di eliminazione riga prodotto
        if (this.productsTbody) {
            this.productsTbody.addEventListener('click', (e) => {
                const btnRemove = e.target.closest('.btn-remove-product');
                if (btnRemove) {
                    const tr = btnRemove.closest('tr');
                    if (tr) tr.remove();
                }
            });
        }
    }

    /**
     * Esegue chiamate AJAX usando fetch() esclusivamente in application/x-www-form-urlencoded
     */
    async makeAjaxRequest(params) {
        const bodyParams = new URLSearchParams();
        bodyParams.append('ajax', '1');
        
        for (const [key, value] of Object.entries(params)) {
            bodyParams.append(key, value);
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
                        this.addProductRow(p.id_product, p.product_name, p.reference, p.cover_url, p.id_attribute, false, p.product_url);
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
                const isAlreadyAdded = !!this.productsTbody.querySelector(`tr[data-product-id="${p.id_product}"]`);
                
                tr.innerHTML = `
                    <td class="text-center">
                        <input type="checkbox" class="search-product-cb" 
                               data-id="${p.id_product}" 
                               data-name="${p.name.replace(/"/g, '&quot;')}" 
                               data-ref="${(p.reference || '').replace(/"/g, '&quot;')}" 
                               data-cover="${p.cover_url || ''}" 
                               data-url="${p.product_url || ''}" 
                               data-attr="${p.detected_attribute_id || 0}"
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
                `;
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
                    const cover = cb.getAttribute('data-cover');
                    const url = cb.getAttribute('data-url');
                    const attr = parseInt(cb.getAttribute('data-attr'), 10);

                    const added = this.addProductRow(idP, name, ref, cover, attr, false, url);
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
     * Aggiunge una riga prodotto alla linea (evitando duplicati)
     */
    addProductRow(idProduct, name, ref, coverUrl, attrId = 0, showAlertOnDuplicate = true, productUrl = '') {
        // Evitiamo duplicati nella stessa linea
        const existing = this.productsTbody.querySelector(`tr[data-product-id="${idProduct}"]`);
        if (existing) {
            if (showAlertOnDuplicate) {
                alert('Il prodotto è già stato inserito in questa linea.');
            }
            return false;
        }

        const clone = this.rowTemplate.content.cloneNode(true);
        const tr = clone.querySelector('tr');
        tr.setAttribute('data-product-id', idProduct);

        const img = tr.querySelector('.product-thumb-preview');
        img.src = coverUrl || '';

        const idTd = tr.querySelector('.product-id-td');
        if (idTd) idTd.textContent = '#' + idProduct;

        tr.querySelector('.product-name-td').textContent = name;
        tr.querySelector('.product-ref-td').textContent = ref || '-';

        const btnPreview = tr.querySelector('.btn-preview-product');
        if (btnPreview) {
            if (productUrl) {
                btnPreview.href = productUrl;
            } else {
                btnPreview.style.display = 'none';
            }
        }

        const inputAttr = tr.querySelector('.product-attr-input');
        inputAttr.value = attrId || 0;

        this.productsTbody.appendChild(tr);
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
            productsList.push({
                id_product: parseInt(idP, 10),
                id_attribute: parseInt(attrId, 10) || 0
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
