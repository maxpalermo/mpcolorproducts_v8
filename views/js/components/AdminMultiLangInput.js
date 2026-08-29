/**
 * AdminMultiLangInput.js
 * Componente Vanilla JS per la gestione autonoma ed indipendente dei campi input multilingua (Shadcn style)
 */
class AdminMultiLangInput {
    constructor(containerSelector = '.mp-multilang-wrapper') {
        this.containerSelector = containerSelector;
        this.init();
    }

    init() {
        document.addEventListener('click', (e) => {
            const selectItem = e.target.closest('.mp-lang-select-item');
            if (!selectItem) return;

            e.preventDefault();
            const targetLangId = selectItem.getAttribute('data-lang-id');
            const targetIso = selectItem.getAttribute('data-lang-iso');
            const targetFlagUrl = selectItem.getAttribute('data-flag-url');

            this.switchAllLanguages(targetLangId, targetIso, targetFlagUrl);
        });
    }

    switchAllLanguages(langId, isoCode, flagUrl) {
        const containers = document.querySelectorAll(this.containerSelector);

        containers.forEach(container => {
            // Nascondi tutti i campi e mostra solo quello della lingua selezionata
            const fields = container.querySelectorAll('.mp-multilang-field-item');
            fields.forEach(field => {
                if (field.getAttribute('data-lang-id') === String(langId)) {
                    field.classList.remove('mp-lang-hidden');
                } else {
                    field.classList.add('mp-lang-hidden');
                }
            });

            // Aggiorna la voce attiva nel dropdown
            const items = container.querySelectorAll('.mp-lang-select-item');
            items.forEach(item => {
                if (item.getAttribute('data-lang-id') === String(langId)) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });

            // Aggiorna l'etichetta ed il flag del pulsante dropdown
            const btn = container.querySelector('.mp-multilang-toggle-btn');
            if (btn) {
                const flagImg = btn.querySelector('.mp-lang-flag');
                if (flagImg && flagUrl) {
                    flagImg.src = flagUrl;
                    flagImg.alt = isoCode;
                    flagImg.style.display = 'inline-block';
                }
                const codeSpan = btn.querySelector('.mp-lang-code');
                if (codeSpan) {
                    codeSpan.textContent = isoCode;
                }

                // Chiudi il menu dropdown Bootstrap se aperto
                const dropdownMenu = container.querySelector('.dropdown-menu');
                if (dropdownMenu) {
                    dropdownMenu.classList.remove('show');
                }
                const dropdownParent = btn.closest('.dropdown');
                if (dropdownParent) {
                    dropdownParent.classList.remove('show');
                }
            }
        });
    }
}

// Inizializzazione automatica al caricamento della pagina DOM
document.addEventListener('DOMContentLoaded', () => {
    window.adminMultiLangInputApp = new AdminMultiLangInput();
});
