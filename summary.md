# Summary di Sviluppo - MP Linee Colore Prodotti (mpcolorproducts)

**Versione Attuale**: 2.2.7

## 1. Obiettivi del Progetto
Il modulo permette di raggruppare prodotti indipendenti in linee colore ed esporli con layout moderno e minimalista in stile Shadcn nella scheda prodotto del negozio PrestaShop 8.2.7. Con la versione 2.2.7, l'etichetta "Colore:" e la descrizione estesa del prodotto/caratteristica sono state separate su due righe distinte per eliminare i problemi di tremore dello schermo (flickering layout shift) quando la descrizione si estende su più righe.

## 2. Scelte Architetturali e Tecnologiche
- **PrestaShop Version**: 8.2.7 (PHP 8.1+).
- **Layout Stabile Etichetta/Nome Colore (`product_colors.tpl` & `mpcolorproducts-frontend.css`)**: Separazione visiva con l'etichetta "Colore:" come blocco dedicato in alto e la descrizione del colore/caratteristica sottostante in un contenitore `.mpcolorproducts-selected-name-wrapper` con `min-height` e `display: block`. Elimina i glitch di ridimensionamento dinamico al passaggio del mouse sugli swatch.
- **Switch "Inserisci dopo Aggiungi al carrello" (`MPCOLORPRODUCTS_AFTER_ADD_TO_CART`)**: Switch BO ed attributo `data-after-add-to-cart`. In `ColorSwatches.js`, se l'opzione è attiva (ON), il contenitore del modulo viene riposizionato automaticamente subito dopo il blocco nativo `.product-add-to-cart` (`.js-product-add-to-cart`).
- **Switch "Nascondi combinazioni caratteristiche" (`MPCOLORPRODUCTS_HIDE_FEATURE_COMBINATIONS`)**: Switch BO per disattivare e nascondere visivamente il pannello caratteristiche in scheda prodotto frontend (`product_colors.tpl`).

## 3. Cronologia Versioni e Modifiche
- **2.2.7 (2026-09-03)**: Separata l'etichetta "Colore:" sulla prima riga e posizionata la descrizione del colore/caratteristica sulla riga sottostante per evitare il glitch visivo del layout.
- **2.2.6 (2026-08-29)**: Inserito nelle impostazioni lo switch "Inserisci dopo Aggiungi al carrello" (`MPCOLORPRODUCTS_AFTER_ADD_TO_CART`) per riposizionare dinamicamente il modulo subito dopo il blocco `.product-add-to-cart`.
- **2.2.5 (2026-08-29)**: Inserito nella pagina impostazioni lo switch "Nascondi combinazioni caratteristiche" (`MPCOLORPRODUCTS_HIDE_FEATURE_COMBINATIONS`) per nascondere o mostrare il pannello delle caratteristiche nella scheda prodotto frontend.
- **2.2.4 (2026-08-29)**: Risolto il salvataggio dei campi multilingua AJAX (serializzazione JSON) e corretta la lettura delle configurazioni booleane in PHP (`Configuration::get`).
- **2.2.3 (2026-08-29)**: Restyling del componente custom multilingua BO in stile Shadcn: pulsante dropdown con bandiera integrato sulla sinistra ed unica casella di testo affiancata sulla stessa riga.
- **2.2.2 (2026-08-29)**: Creato componente custom riutilizzabile multilingua BO (`multilang_input.html.twig` e `AdminMultiLangInput.js`) con selettore dropdown di lingua e bandiera.
- **2.2.1 (2026-08-29)**: Aggiunte due caselle di traduzione multilingua per le intestazioni "Stessa linea" ed "Altri colori" nel pannello di configurazione BO con fallback automatico. Aggiornata l'etichetta dello switch per mostrare o nascondere il pannello del selettore caratteristiche.
- **2.2.0 (2026-08-11)**: Suddivisione visiva delle varianti colore in due sezioni distinte **"Stessa linea"** ed **"Altri colori"**, con rendering condizionale ed occultamento delle sezioni prive di prodotti.
- **2.1.8 (2026-08-11)**: Evidenziazione visiva con bordo blu oceano (`#0284c7`, `.same-features`) sui pallini colore dei prodotti aventi la stessa combinazione di caratteristiche del prodotto corrente.
- **2.1.7 (2026-08-11)**: Inclusa la combinazione delle caratteristiche formattata nel titolo del colore (es. `Blu / Monocolore (100% Cotone)`), visibile nel tooltip al passaggio del mouse e nell'etichetta dell'intestazione.
- **2.1.6 (2026-08-11)**: Evidenziazione visiva in blu tenue (`#e0f2fe`) della pillola corrispondente alle caratteristiche del prodotto attualmente visualizzato (`.is-current-feature`).
- **2.1.5 (2026-08-11)**: Inserita opzione di configurazione "Mostra tutti i colori" (`MPCOLORPRODUCTS_SHOW_ALL_COLORS`). Quando attiva, mostra tutti i colori della linea al caricamento del prodotto; quando disattivata, mantiene il filtraggio iniziale basato sulle caratteristiche del prodotto corrente.
- **2.1.4 (2026-08-07)**: Filtraggio iniziale della lista colori sulle caratteristiche del prodotto corrente all'apertura della pagina.
- **2.1.3 (2026-08-07)**: Filtraggio Front-Office ad intersezione esatta (AND logic) per combinare più caratteristiche selezionate.
- **2.1.2 (2026-08-07)**: Select Chosen per caratteristiche, colonna visiva Colore con swatch e nome descrittivo, funzionalità "Applica a tutti" BO.
- **2.1.1 (2026-08-07)**: Campo DB `features` in `ps_mpcolorproducts_product`, correzione upgrade `executeS()`, ed opzione "TUTTI" nel FO.
- **2.1.0 (2026-08-07)**: Selettore caratteristiche ed aggiornamento colori AJAX FO.
- **2.0.0 (2026-07-27)**: Inizializzazione e refactoring per PrestaShop 8.2.x.
