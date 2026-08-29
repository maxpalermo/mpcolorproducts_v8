# MP Linee Colore Prodotti (mpcolorproducts)

Modulo PrestaShop 8.2.7 per raggruppare prodotti correlati in linee colore ed esporli con layout moderno e minimalista in stile Shadcn nella scheda prodotto front-end.

## Caratteristiche Principali

- **Gestione Gruppi Linea Colore**: Raggruppamento dinamico di prodotti indipendenti in linee colore visualizzate nella scheda del prodotto.
- **Inizializzazione & Filtraggio FO sulle Caratteristiche del Prodotto Corrente**: All'apertura della pagina vengono esposte solo le varianti che condividono le caratteristiche del prodotto visualizzato.
- **Selettore Caratteristiche FO con Combinazione Esatta (AND)**: Filtraggio dinamico via AJAX basato sull'intersezione esatta di tutte le caratteristiche selezionate dal cliente.
- **Select Chosen & Funzione "Applica a tutti" BO**: Gestione con la libreria Chosen native e funzione per copiare le macro-caratteristiche a tutti i prodotti della linea.

## Requisiti

- PrestaShop 8.0.0+ (testato su 8.2.7)
- PHP 8.1+

## Changelog

### v2.2.6 (2026-08-29)
- **Switch "Inserisci dopo Aggiungi al carrello"**: Inserito lo switch nelle impostazioni BO per consentire di riposizionare automaticamente il modulo subito dopo il blocco `div.product-add-to-cart` (.js-product-add-to-cart).

### v2.2.5 (2026-08-29)
- **Switch "Nascondi combinazioni caratteristiche"**: Inserito lo switch dedicato nelle impostazioni BO per permettere di nascondere (ON) o mostrare (OFF) il pannello delle caratteristiche nella scheda prodotto frontend.

### v2.2.4 (2026-08-29)
- **Fix Salvataggio Multilingua AJAX e Switch Caratteristiche**: Corretta la serializzazione JSON degli oggetti multilingua in `makeAjaxRequest` e la sintassi delle chiamate PHP `Configuration::get()` per salvare e leggere correttamente lo stato OFF dello switch caratteristiche.

### v2.2.3 (2026-08-29)
- **Restyling Componente Multilingua BO Shadcn**: Posizionato il pulsante dropdown della lingua con bandiera a sinistra ed il campo di testo per la lingua selezionata affiancato a destra sulla stessa riga, garantendo che le altre caselle rimangano rigorosamente nascoste.

### v2.2.2 (2026-08-29)
- **Componente Custom Multilingua BO con Selettore Bandiera**: Creato un componente custom riutilizzabile ed indipendente (`multilang_input.html.twig` + `AdminMultiLangInput.js`) con dropdown della lingua/bandiera che mostra solo l'input della lingua selezionata mantenendo tutti i campi attivi nel DOM.

### v2.2.1 (2026-08-29)
- **Campi Traduzione Multilingua etichette FO ("Stessa linea" / "Altri colori")**: Inseriti i campi di traduzione multilingua per ciascuna lingua abilitata nel negozio con fallback automatico.
- **Switch Selettore Caratteristiche in Scheda Prodotto**: Aggiornata l'etichetta dello switch per attivare o nascondere integralmente il pannello delle caratteristiche.

### v2.2.0 (2026-08-11)
- **Suddivisione in Sezioni "Stessa linea" ed "Altri colori"**: Organizzate le miniature colore in due sezioni distinte. "Stessa linea" raggruppa i prodotti con le stesse caratteristiche del prodotto visualizzato, "Altri colori" include le rimanenti combinazioni. Le sezioni vuote vengono automaticamente nascoste.

### v2.1.8 (2026-08-11)
- **Evidenziazione Bordo Swatch Prodotti Simili**: Applicato un bordo blu azzurro distintivo (`#0284c7`, classe `.same-features`) alle miniature/pallini colore dei prodotti aventi la stessa combinazione di caratteristiche del prodotto visualizzato.

### v2.1.7 (2026-08-11)
- **Combinazione Caratteristiche nel Titolo/Tooltip**: Inclusa la combinazione formattata delle caratteristiche nel titolo del colore (es. `Blu / Monocolore (100% Cotone)`), visibile nel tooltip hover dello swatch ed aggiornata dinamicamente nell'intestazione.

### v2.1.6 (2026-08-11)
- **Evidenziazione Caratteristica Prodotto Corrente**: Colorata in blu tenue (`#e0f2fe`) la pillola di caratteristica appartenente al prodotto visualizzato per indicare chiaramente all'utente la sua combinazione anche quando sono mostrati tutti i colori.

### v2.1.5 (2026-08-11)
- **Opzione Mostra Tutti i Colori**: Inserito uno switch nelle impostazioni BO ("Mostra tutti i colori"). Se attivo, la scheda prodotto mostra tutti i colori della linea anziché filtrare per le caratteristiche del prodotto corrente. Se disattivato, mantiene il comportamento precedente.

### v2.1.4 (2026-08-07)
- **Filtro Iniziale Scheda Prodotto**: All'apertura della pagina vengono mostrati esclusivamente i colori ed i prodotti aventi l'esatta combinazione delle caratteristiche attive del prodotto visualizzato.

### v2.1.3 (2026-08-07)
- **Filtraggio Front-Office per Combinazione Esatta (AND)**:
  - Invio di tutti gli ID di caratteristica attivi via `feature_value_ids`.
  - Calcolo dell'intersezione logica (`array_intersect`) nel FrontController `colors.php`.
