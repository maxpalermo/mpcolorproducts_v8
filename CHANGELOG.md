# Changelog MP Linee Colore Prodotti

Tutte le modifiche rilevanti apportate a questo modulo saranno documentate in questo file.

## [2.2.0] - 2026-08-11
### Aggiunto
- **Sezioni Distinte "Stessa linea" ed "Altri colori" nel Frontend**:
  - Calcolati e separati in PHP gli array `same_line_colors` e `other_line_colors`.
  - In `_color_swatches_items.tpl`, la lista è divisa nelle due sezioni con i rispettivi titoli formattati in stile Shadcn.
  - Se una delle due sezioni non contiene prodotti (es. `other_line_colors` o `same_line_colors` è vuota), la relativa sezione viene automaticamente nascosta.

## [2.1.8] - 2026-08-11
### Aggiunto
- **Evidenziazione Bordo Swatch Prodotti Simili (`.same-features`)**:
  - Calcolato il confronto degli ID delle caratteristiche di ciascun prodotto rispetto al prodotto corrente (`same_features`).
  - Applicata la classe CSS `.same-features` con un bordo blu azzurro vivace (`#0284c7`) alle miniature colore dei prodotti che condividono le stesse caratteristiche.
  - In questo modo l'utente riconosce all'istante quali varianti colore sono identiche per composizione/caratteristiche al prodotto visualizzato.

## [2.1.7] - 2026-08-11
### Aggiunto
- **Combinazione Caratteristiche Formattata nel Titolo e Tooltip (`display_title`)**:
  - Calcolata la stringa delle caratteristiche formattate per ciascun prodotto della linea (es. `100% Cotone`).
  - Formattata la proprietà `display_title` nel formato `NomeColore (Caratteristiche)` (es. `Blu / Monocolore (100% Cotone)`).
  - Esposta sia nell'attributo `title` (tooltip al passaggio del mouse nello swatch) sia in `data-color-name` per l'aggiornamento dell'etichetta `Colore:`.

## [2.1.6] - 2026-08-11
### Aggiunto
- **Evidenziazione della Caratteristica del Prodotto Visualizzato (Blu tenue `#e0f2fe`)**:
  - Aggiunto il flag `is_current` per identificare la pillola di caratteristica appartenente al prodotto visualizzato.
  - Applicata la classe CSS `.is-current-feature` per colorare il pulsante con un bluetto tenue ed elegante (`#e0f2fe`, testo `#0369a1`, bordo `#7dd3fc`).
  - In questo modo l'utente riconosce immediatamente la composizione/caratteristica del prodotto visualizzato, anche se la selezione è impostata su "Tutti".

## [2.1.5] - 2026-08-11
### Aggiunto
- **Switch "Mostra tutti i colori" nelle impostazioni BO (`MPCOLORPRODUCTS_SHOW_ALL_COLORS`)**:
  - Inserita nuova impostazione switch nel pannello di configurazione del modulo.
  - Quando attiva (`1`), al caricamento della scheda prodotto vengono mostrati tutti i colori della linea ed il selettore delle caratteristiche seleziona l'opzione "Tutti".
  - Quando disattivata (`0`), viene mantenuto il comportamento stabilito nella v2.1.4 (filtraggio iniziale basato sulle caratteristiche del prodotto corrente).

## [2.1.4] - 2026-08-07
### Aggiunto
- **Inizializzazione dei Colori sulle Caratteristiche del Prodotto Corrente**:
  - All'apertura della scheda prodotto, la lista dei colori visualizzati viene filtrata in automatico in base alle caratteristiche possedute dal **prodotto corrente**.
  - Vengono esposte unicamente le varianti colore dei prodotti che condividono l'esatta combinazione delle caratteristiche attive del prodotto visualizzato.

## [2.1.3] - 2026-08-07
### Aggiunto
- **Filtraggio Front-Office per Combinazione Esatta (INTERSEZIONE / AND)**:
  - Aggiornato `ColorSwatches.js` per raccogliere l'insieme di tutti i pill attivi ed inviarli via `feature_value_ids`.
  - Aggiornato `colors.php` per eseguire l'intersezione logica (`array_intersect` / AND) fra tutti i gruppi di caratteristiche selezionati.

## [2.1.2] - 2026-08-07
### Aggiunto
- **Select Chosen Multiple & Colonna Colore nel Modal BO**:
  - Inserito il plugin nativo PrestaShop `chosen`.
  - Sostituito l'input di testo `ID Attr Colore` con la colonna **Colore**.
- **Funzionalità "Applica a tutti" per le Caratteristiche**:
  - Pulsante `Applica 1° a tutti` e pulsante di copia rapida per ciascuna riga prodotto.

## [2.1.1] - 2026-08-07
### Aggiunto
- Script di aggiornamento `upgrade/upgrade-2.1.1.php` per la colonna `features`.
- Opzione **"TUTTI"** (ID `0`) in ciascun gruppo di caratteristiche nel Front-Office.
