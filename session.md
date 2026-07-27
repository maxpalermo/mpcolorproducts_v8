# Note di Sessione e Direttive Moduli PrestaShop 8

Documento di riferimento per lo sviluppo e la conversione dei moduli PrestaShop 8.2.x, con regole di codice, architettura e guida all'implementazione dei componenti UI.

---

## 1. Implementazione dello Switch PrestaShop 8 (`ps-switch`)

Lo switch nativo di PrestaShop 8 utilizza due campi `<input type="radio">` per i valori `0` (Off) e `1` (On) affiancati da uno `<span class="slide-button"></span>` per l'effetto levetta scorrevole.

### Struttura Twig (`.html.twig`) - Back Office
```html
<div class="ps-switch ps-switch-sm ps-switch-nolabel ps-switch-center">
    <input type="radio" name="NOME_CONFIGURAZIONE" id="NOME_CONFIGURAZIONE_off" value="0" {% if valore == 0 %}checked="checked"{% endif %}>
    <label for="NOME_CONFIGURAZIONE_off">Off</label>
    <input type="radio" name="NOME_CONFIGURAZIONE" id="NOME_CONFIGURAZIONE_on" value="1" {% if valore == 1 %}checked="checked"{% endif %}>
    <label for="NOME_CONFIGURAZIONE_on">On</label>
    <span class="slide-button"></span>
</div>
```

### Struttura Smarty (`.tpl`) - Front Office o Legacy BO
```smarty
<div class="ps-switch ps-switch-sm ps-switch-nolabel ps-switch-center">
    <input type="radio" name="NOME_CONFIGURAZIONE" id="NOME_CONFIGURAZIONE_off" value="0" {if $valore == 0}checked="checked"{/if}>
    <label for="NOME_CONFIGURAZIONE_off">Off</label>
    <input type="radio" name="NOME_CONFIGURAZIONE" id="NOME_CONFIGURAZIONE_on" value="1" {if $valore == 1}checked="checked"{/if}>
    <label for="NOME_CONFIGURAZIONE_on">On</label>
    <span class="slide-button"></span>
</div>
```

### Lettura del Valore in JavaScript (ES6+)
```javascript
// Recupera il valore dal radio selezionato (0 oppure 1)
const selectedRadio = form.querySelector('[name="NOME_CONFIGURAZIONE"]:checked');
const optionValue = selectedRadio ? parseInt(selectedRadio.value, 10) : 0;
```

### CSS di Fallback per `ps-switch`
```css
.ps-switch {
    position: relative;
    display: inline-block;
    height: 32px;
    width: 90px;
    background-color: #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    vertical-align: middle;
    user-select: none;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.12);
}

.ps-switch input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 50%;
    height: 100%;
    margin: 0;
    cursor: pointer;
    z-index: 2;
}

.ps-switch input[value="0"] { left: 0; }
.ps-switch input[value="1"] { right: 0; }

.ps-switch label {
    position: relative;
    display: inline-block;
    float: left;
    width: 50%;
    height: 100%;
    line-height: 32px;
    text-align: center;
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    margin: 0;
    z-index: 1;
    cursor: pointer;
    transition: color 0.2s ease;
    text-transform: uppercase;
}

.ps-switch input[value="1"]:checked ~ label[for*="_on"] { color: #ffffff; }
.ps-switch input[value="0"]:checked ~ label[for*="_off"] { color: #ffffff; }

.ps-switch .slide-button {
    position: absolute;
    top: 3px;
    left: 3px;
    height: 26px;
    width: 40px;
    background-color: #ef4444;
    border-radius: 13px;
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.25s ease;
    z-index: 0;
}

.ps-switch input[value="1"]:checked ~ .slide-button {
    transform: translateX(44px);
    background-color: #10b981;
}
```

---

## 2. Direttive di Sviluppo Moduli PrestaShop 8

### Template Engine
- **Back Office (Admin)**: Utilizzare esclusivamente template **Twig** (`.html.twig`).
- **Front Office (Frontend)**: Utilizzare template **Smarty** (`.tpl`).
- Mantenere la separazione tassativa tra file HTML/Twig, CSS (`mpcolorproducts-admin.css`, `mpcolorproducts-frontend.css`) e JS (`AdminColorLines.js`, `ColorSwatches.js`).

### Controller Admin & AJAX
- **Comunicazione AJAX**: Usare `fetch()` in modalità `application/x-www-form-urlencoded` (`URLSearchParams`) per tutte le chiamate senza invio di file binari.
- **Dati Binari**: Utilizzare `fetch()` con `Multipart/Form-Data` e `FormData`.
- **Formattazione Listati**: Utilizzare esclusivamente **Bootstrap Table**.
- **Firma setMedia**: In PrestaShop 8, definire `public function setMedia($isNewTheme = false)` ed invocare `parent::setMedia($isNewTheme);`.

### Gestione Database
- Utilizzare esclusivamente la classe `Db` di PrestaShop.
- **MAI utilizzare `LIMIT`** nelle query eseguite con `getRow()` e `getValue()`.
- Dichiarare prima della query la variabile d'appoggio per il prefisso: `$dbPrefix = _DB_PREFIX_;`.
- Incapsulare le query tra virgolette doppie `""` e racchiudere le variabili tra graffe: `"SELECT...FROM \`{$dbPrefix}nome_tabella\`..."`.
- Formattare le query a blocchi indentati (`SELECT...FROM...JOIN...WHERE...ORDER BY...`).

### Frontend & Intercettazione Varianti Prodotto
- Intercettare il div `.product-variants.js-product-variants`.
- Individuare e **nascondere** i gruppi colore nativi (es. `name="group[14]"`, `name="group[15]"`, `input.input-color`, `COLORE`, `Rifiniture`).
- Inserire il blocco del modulo `#mpcolorproducts-block` (`.product-variants-item`) al loro posto.
- Gestire il riposizionamento dinamico riascoltando gli eventi AJAX di PrestaShop 8 (`prestashop.on('updatedProduct', ...)`).
