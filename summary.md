# Summary di Sviluppo - MP Linee Colore Prodotti (mpcolorproducts)

**Versione Attuale**: 2.2.0

## 1. Obiettivi del Progetto
Il modulo permette di raggruppare prodotti indipendenti in linee colore ed esporli con layout moderno e minimalista in stile Shadcn nella scheda prodotto del negozio PrestaShop 8.2.7. Con la versione 2.2.0, le varianti colore sono suddivise visivamente in due sezioni separate: **"Stessa linea"** (prodotti aventi le stesse caratteristiche del prodotto visualizzato) e **"Altri colori"** (rimanenti combinazioni), con occultamento automatico delle sezioni vuote.

## 2. Scelte Architetturali e Tecnologiche
- **PrestaShop Version**: 8.2.7 (PHP 8.1+).
- **Suddivisione in Sezioni FO ("Stessa linea" & "Altri colori")**: Array `same_line_colors` e `other_line_colors` separati in PHP ed inviati sia a Smarty per la scheda prodotto iniziale sia al controller AJAX `colors.php`. In `_color_swatches_items.tpl` ciascuna sezione viene mostrata solo se contiene prodotti.
- **Evidenziazione Bordo Swatch Prodotti Simili**: Flag `same_features` calcolato in `ColorLineHelper::getProductFeaturesAndColors` confrontando gli ID caratteristica con quelli del prodotto corrente, ed applicazione della classe CSS `.same-features` con bordo blu oceano (`#0284c7`).
- **Titolo Formattato con Caratteristiche**: Mappatura `display_title` in `ColorLineHelper::getProductFeaturesAndColors` nel formato `NomeColore (Caratteristica1 - Caratteristica2)` esposta nel tooltip `title`, in `data-color-name` e nell'intestazione `#mpcolorproducts-current-name`.
- **Evidenziazione Caratteristica Prodotto Corrente**: Flag `is_current` nei dati delle caratteristiche e classe CSS `.is-current-feature` per applicare uno sfondo bluetto tenue (`#e0f2fe`) alla pillola corrispondente al prodotto visualizzato.
- **Opzione Configurazione "Mostra tutti i colori"**: Se l'opzione `MPCOLORPRODUCTS_SHOW_ALL_COLORS` è attiva (1), la scheda prodotto espone tutti i colori della linea ed imposta come predefinita l'opzione "Tutti" nei pill delle caratteristiche. Se disattivata (0), il modulo si comporta come nella v2.1.4 filtrando inizialmente i colori per le caratteristiche del prodotto corrente.
- **Inizializzazione & Filtraggio Iniziale**: Metodo statico `ColorLineHelper::filterColorLineByFeatureValues` richiamato sia da `renderColorSwatches` all'apertura della pagina (se `MPCOLORPRODUCTS_SHOW_ALL_COLORS` è 0), sia da `colors.php` durante i cambi AJAX.
- **FO AND Intersection Filtering**: Componente `ColorSwatches.js` raccoglie tutti i pill attivi non-zero in `feature_value_ids`.
- **Select Chosen & Funzione Applica a tutti BO**: Plugin nativo Chosen ed algoritmo JS `applyRowFeaturesToAll` per copiare le macro-caratteristiche a tutti i prodotti della linea.

## 3. Cronologia Versioni e Modifiche
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
