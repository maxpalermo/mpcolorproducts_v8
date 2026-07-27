# MP Linee Colore Prodotti (`mpcolorproducts`)

**Versione:** 2.0.0  
**Autore:** Massimiliano Palermo  
**Compatibilità PrestaShop:** 8.0.0 - 8.2.x+  
**Requisito PHP:** >=7.4 || >=8.1  
**Licenza:** AFL 3.0 Academic Free License  

---

## A Cosa Serve il Modulo

Nel catalogo di molti e-commerce PrestaShop, i prodotti che appartengono alla stessa linea ma con **colori diversi** vengono spesso inseriti come **prodotti completamente indipendenti** (ciascuno con la propria scheda prodotto, il proprio ID e le proprie combinazioni di taglia).

Senza un modulo dedicato, il cliente che visita la scheda di un prodotto blu non sa che esiste lo stesso identico articolo in colore nero o jeans, a meno di non effettuare una nuova ricerca nel catalogo.

**MP Linee Colore Prodotti** risolve questo problema raggruppando i prodotti indipendenti in una **Linea Colore**. Nella pagina del prodotto frontend mostra un elegante selettore di colori/miniature circolari. Quando il cliente fa click su uno dei colori disponibili, viene reindirizzato all'altra scheda prodotto, rendendo la navigazione fluida ed incrementando le conversioni.

---

## Come Utilizzarlo

### 1. Configurazione Iniziale
Accedi al Back Office di PrestaShop 8 e naviga in **Catalogo -> MP Linee Colore**.

Nella sezione superiore **Configurazione MP Linee Colore**:
1. **Gruppo Attributi Colore Predefinito**: Seleziona il gruppo attributi colore usato nel tuo negozio (es. *Colore*).
2. **Modalità di Visualizzazione Swatch**:
   - *Immagine Copertina del Prodotto*: Mostra le miniature delle foto di copertina dei prodotti correlati (pallini circolari).
   - *Colore Esadecimale / Texture Attributo*: Mostra il colore esadecimale (o la texture in `img/co/{id_attribute}.jpg` se presente).
3. **Nascondi Prodotto Corrente**: Scegli se nascondere lo swatch del prodotto attualmente visualizzato dal cliente.
4. Clicca su **Salva Impostazioni**.

### 2. Creazione e Gestione delle Linee Colore
1. Nella tabella **Gestione Linee Colore Prodotti**, clicca sul pulsante **Nuova Linea Colore**.
2. Assegna un nome alla linea (es. *Linea Grembiuli Workwear*).
3. Utilizza la barra di ricerca **"Cerca prodotto per nome, ID o riferimento..."**:
   - Digita il nome, riferimento o ID del prodotto e premi Cerca.
   - Verrà mostrata una tabella di risultati con **checkbox di selezione multipla**.
   - Spunta i prodotti che fanno parte della stessa linea (oppure usa *Seleziona Tutti*) e clicca su **Aggiungi Selezionati**.
4. I prodotti verranno inseriti nella tabella sottostante:
   - Viene mostrata la foto, l'**ID Prodotto**, il nome, il riferimento, l'**ID Attributo Colore** e la colonna **Azioni**.
   - Nella colonna Azioni puoi usare il pulsante **Anteprima** (icona occhio) per aprire direttamente la scheda frontend in una nuova scheda.
   - I prodotti già presenti vengono disabilitati automaticamente per evitare duplicati.
5. Clicca su **Salva Linea**.

---

## PER UTENTI ESPERTI

### Inserimento Pulito dell'Hook Personalizzato nel Tema (`product.tpl`)

Di default, il modulo riposiziona automaticamente il blocco colore via JavaScript subito sotto la sezione della taglia o della guida taglie.

Per un'integrazione ancora più performante e pulita a livello di rendering server-side (senza attendere l'esecuzione dello script JS lato client), puoi inserire l'hook personalizzato direttamente nel file `product.tpl` del tuo tema Smarty.

#### Istruzioni di Integrazione:

1. Apri il file del tuo tema: `themes/[nome-tuo-tema]/templates/catalog/product.tpl` (o `product.tpl`).
2. Individua la sezione dove vengono renderizzati gli attributi e le taglie.
3. Inserisci il seguente codice Smarty nel punto esatto in cui desideri mostrare il selettore di colori:

```smarty
{* --- MP Linee Colore Prodotti --- *}
{hook h='displayMpColorProducts'}
```

---

## Struttura del Modulo

```
mpcolorproducts/
├── classes/
│   └── models/
│       ├── ModelMpColorProductsGroup.php
│       ├── ModelMpColorProductsProduct.php
│       └── autoload.php
├── controllers/
│   └── admin/
│       └── AdminMpColorProductsController.php
├── src/
│   ├── Helpers/
│   │   └── ColorLineHelper.php
│   └── Module/
│       └── ModuleTemplate.php
├── views/
│   ├── css/
│   │   ├── mpcolorproducts-admin.css
│   │   └── mpcolorproducts-frontend.css
│   ├── js/
│   │   └── components/
│   │       ├── AdminColorLines.js
│   │       └── ColorSwatches.js
│   └── templates/
│       ├── admin/
│       │   └── configure.html.twig
│       └── hook/
│           └── product_colors.tpl
├── composer.json
├── mpcolorproducts.php
├── CHANGELOG.md
└── README.md
```

---

## Changelog Dettagliato

### Versione 2.0.0 (2026-07-27)
- **Conversione PrestaShop 8.2.x**: Aggiornamento completo del modulo per la compatibilità con PrestaShop 8.0.0 - 8.2.x+ e PHP 8.1+.
- **Template Engine Twig in Back Office**: Conversione del template di configurazione Admin da Smarty (`configure.tpl`) a Twig (`configure.html.twig`).
- **Separazione Fogli di Stile**: Creazione di `mpcolorproducts-admin.css` e `mpcolorproducts-frontend.css` per un isolamento completo delle risorse.
- **Integrazione UI PrestaShop 8**: Modale HTML5 `<dialog>` integrata con il design system di PrestaShop 8, icone material ed elementi UI modernizzati.
- **Ottimizzazione Database**: Uniformate le query SQL in `ColorLineHelper.php` e `AdminMpColorProductsController.php` seguendo le regole di formattazione a blocchi, varianti d'appoggio `$dbPrefix` e rimozione di clausole `LIMIT` superflue con `getRow()` e `getValue()`.
- **Comunicazione AJAX**: Chiamate `fetch()` in modalità `application/x-www-form-urlencoded` con Vanilla JS ES6+.

### Versione 1.0.7 (2026-07-27)
- **Documentazione e Guida Avanzata**: Aggiunte nel `README.md` le sezioni "A cosa serve il modulo", "Come utilizzarlo" ed la guida avanzata "PER UTENTI ESPERTI".
