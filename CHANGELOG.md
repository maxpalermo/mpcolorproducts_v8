# Changelog MP Linee Colore Prodotti

Tutte le modifiche rilevanti apportate a questo modulo saranno documentate in questo file.

## [2.0.0] - 2026-07-27
### Aggiunto
- Supporto nativo per PrestaShop 8.0.0 - 8.2.x+ e PHP 8.1+.
- Template Twig per il Back Office (`views/templates/admin/configure.html.twig`).
- Fogli di stile CSS separati per Back Office (`mpcolorproducts-admin.css`) e Frontend (`mpcolorproducts-frontend.css`).
- Integrazione con il design system di PrestaShop 8 (icone material, modale HTML5 `<dialog>` stilizzata e badge Bootstrap 5).

### Modificato
- Refactoring `AdminMpColorProductsController.php` per il rendering Twig e la gestione delle risposte AJAX.
- Standardizzazione delle query SQL in `ColorLineHelper.php` (formattazione a blocchi, varianti `$dbPrefix`, assenza di `LIMIT` con `getRow`/`getValue`).
- Aggiornato `composer.json` con versione `2.0.0` e requisito `php: ">=7.4 || >=8.1"`.

## [1.0.7] - 2026-07-27
### Aggiunto
- Documentazione completa nel `README.md`: sezioni "A cosa serve il modulo", "Come utilizzarlo" e la sezione speciale "PER UTENTI ESPERTI" per la configurazione pulita dell'hook personalizzato `{hook h='displayMpColorProducts'}` in `product.tpl`.

## [1.0.6] - 2026-07-27
### Aggiunto
- Spostamento automatico lato client via JS (`ColorSwatches.js`) del blocco colore subito sotto il selettore "Scegliere la taglia" / Guida Taglie.
- Hook personalizzato `displayMpColorProducts` per l'inserimento manuale in `product.tpl`.

## [1.0.5] - 2026-07-27
### Aggiunto
- Pulsante anteprima scheda prodotto frontend (icona occhio) affiancato sulla stessa riga al pulsante elimina nella colonna Azioni della tabella `selected-products-table`.

## [1.0.4] - 2026-07-27
### Aggiunto
- Colonna `ID` Prodotto visibile nella tabella dei prodotti inseriti nella linea (`selected-products-table`), subito dopo la colonna `Foto`.

## [1.0.3] - 2026-07-27
### Aggiunto
- Selezione multipla con checkbox nella ricerca prodotti nel Back Office per inserire più prodotti con un solo click.
- Controllo automatico anti-duplicato che disabilita i prodotti già associati alla linea corrente.

## [1.0.2] - 2026-07-27
### Corretto
- Risolto Fatal Error `syntax error, unexpected 'use' (T_USE)` in PrestaShop 1.6 sostituendo le istruzioni `use` di livello radice nel file del modulo e del controller con FQCN (`\MpSoft\MpColorProducts\...`).

## [1.0.1] - 2026-07-27
### Aggiunto
- Classi `ObjectModel` native senza namespace (`ModelMpColorProductsGroup` e `ModelMpColorProductsProduct`) nella cartella `classes/models/`.
- Autoloader dedicato `classes/models/autoload.php` incluso nei file principali dopo l'autoloader di Composer.

## [1.0.0] - 2026-07-27
### Aggiunto
- Struttura iniziale del modulo compatibile con PrestaShop 1.6.1.23.
- Autoloader PSR-4 via `composer.json` under namespace `MpSoft\MpColorProducts\`.
- Gestione tabelle database `ps_mpcolorproducts_group` e `ps_mpcolorproducts_product`.
- Controller Admin `AdminMpColorProductsController` visibile nel menu sotto **Catalogo**.
- Integrazione **Bootstrap Table** per il listato Back Office.
- Chiamate AJAX con Vanilla JS `fetch()` ed `application/x-www-form-urlencoded`.
- Modale nativa HTML5 `<dialog>` e `<template>` per aggiunta e modifica dinamica linee di prodotto.
- Template frontend Smarty `product_colors.tpl` con stile circolare, bordo attivo e hover.
- Supporto per immagini di copertina prodotto, colori esadecimali e texture in `img/co/`.
