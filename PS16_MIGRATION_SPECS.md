# Guida di Migrazione e Blueprint Architetturale: Modulo `mpcolorproducts` per PrestaShop 1.6

Questo documento descrive dettagliatamente la struttura, le funzionalità ed il codice sviluppati nella versione 2.0 per **PrestaShop 8.2**, fornendo la guida di porting completa per implementare le medesime funzionalità sulla versione **PrestaShop 1.6.x**.

---

## 1. Panoramica delle Funzionalità da Portare su PS 1.6

### A. Configurazione Back-Office
1. **Selezione Multipla Gruppi Attributi Colore/Varianti (`MPCOLORPRODUCTS_ATTRIBUTE_GROUP_ID[]`)**:
   - Select multipla Chosen per selezionare i gruppi di attributi colore principali (es. *Colore Principale*, *Colore Secondario*, *Rifiniture*).
2. **Selezione Multipla Gruppi Attributi da Cercare e Nascondere (`MPCOLORPRODUCTS_HIDE_ATTR_GROUPS[]`)**:
   - Select multipla Chosen per selezionare i gruppi da scansionare ed oscurare automaticamente nel frontend (*Colore*, *Rifiniture*, *Fantasia*).
3. **Impostazioni Generali**:
   - Modalità visualizzazione swatch (`product_image` vs `color`).
   - Levetta switch per nascondere il prodotto corrente (`MPCOLORPRODUCTS_HIDE_CURRENT`).
   - Form inviato via AJAX `fetch()` in `application/x-www-form-urlencoded`.

### B. Algoritmo di Composizione Colore Multi-Attributo (`ColorLineHelper.php`)
- **Scansione Composita**: Quando viene caricato un prodotto, il modulo interroga le sue combinazioni cercando attributi in tutti i gruppi configurati (`MPCOLORPRODUCTS_ATTRIBUTE_GROUP_ID` e `MPCOLORPRODUCTS_HIDE_ATTR_GROUPS`).
- **Nomi Composti**:
  - 1 attributo trovato (es. *Fantasia: "Righe"*): restituisce **`Righe`**.
  - Più attributi trovati (es. *Fantasia: "Jeans"* + *Colore: "Monocolore"*): compone automaticamente **`Jeans / Monocolore`**.
- **Texture / Esadecimale**: Restituisce il colore o l'immagine di texture `img/co/{id_attribute}.jpg` ed il primo `color_hex` disponibile.

### C. Componente Frontend (`ColorSwatches.js` & `product_colors.tpl`)
1. **Dimensioni Swatch**: Cerchi di **56px × 56px** (bordo `2px`, active `3px solid #1e40af`, gap `12px`).
2. **Oscuramento Dinamico Varianti Nativi**:
   - Legge l'attributo HTML `data-hide-attr-groups="14,15,3"`.
   - Nasconde tutti i selettori nativi del tema (`group[14]`, `group[15]`, `group[3]`, `input.input-color`, `COLORE`, `Rifiniture`, `Fantasia`).
3. **Eliminazione Duplicati DOM**:
   - Mantiene solo il primo contenitore `#mpcolorproducts-block` inserendolo al posto dei selettori nativi.
   - Elimina immediatamente dal DOM tutti i contenitori duplicati generati da hook secondari (es. `displayFooterProduct`).

---

## 2. Differenze Specifiche per PrestaShop 1.6.x

| Componente | PrestaShop 8.2.x | PrestaShop 1.6.x |
| :--- | :--- | :--- |
| **Admin Template** | Twig 3 (`configure.html.twig`) | Smarty (`configure.tpl`) |
| **Admin Controller** | `AdminControllerCore` | `ModuleAdminController` |
| **Firma setMedia** | `setMedia($isNewTheme = false)` | `setMedia()` |
| **Bootstrap BO** | Bootstrap 4 / 5 | Bootstrap 3 (`.panel`, `.form-group`, `.row`) |
| **Chosen Plugin** | Nativamente presente | Nativamente presente (`$('.chosen').chosen()`) |
| **Frontend Selectors** | `.product-variants`, `.js-product-variants` | `#attributes`, `fieldset.attribute_fieldset`, `.product-variants` |

---

## 3. Struttura del Codice per PrestaShop 1.6

### A. Template Smarty Back-Office (`configure.tpl`) - PS 1.6
```smarty
{* BO Configuration Template per PrestaShop 1.6 *}
<div class="panel">
    <div class="panel-heading">
        <i class="icon-cogs"></i> {l s='Configurazione MP Linee Colore' mod='mpcolorproducts'}
    </div>
    <div class="panel-body">
        <div id="config-alert-container"></div>
        <form id="mpcolorproducts-config-form" method="post" class="form-horizontal">
            
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Gruppi Attributi Colore Predefiniti' mod='mpcolorproducts'}</label>
                <div class="col-lg-7">
                    <select name="MPCOLORPRODUCTS_ATTRIBUTE_GROUP_ID[]" class="form-control chosen" multiple="multiple" style="min-height: 120px;">
                        {foreach from=$color_groups item=group}
                            <option value="{$group.id_attribute_group}" {if in_array($group.id_attribute_group, $selected_attr_groups)}selected="selected"{/if}>
                                {$group.name} (ID: {$group.id_attribute_group})
                            </option>
                        {/foreach}
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Gruppi Attributi da Cercare e Nascondere' mod='mpcolorproducts'}</label>
                <div class="col-lg-7">
                    <select name="MPCOLORPRODUCTS_HIDE_ATTR_GROUPS[]" class="form-control chosen" multiple="multiple" style="min-height: 120px;">
                        {foreach from=$all_attribute_groups item=group}
                            <option value="{$group.id_attribute_group}" {if in_array($group.id_attribute_group, $hidden_attr_groups)}selected="selected"{/if}>
                                {$group.name} (ID: {$group.id_attribute_group})
                            </option>
                        {/foreach}
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Nascondi Prodotto Corrente' mod='mpcolorproducts'}</label>
                <div class="col-lg-7">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="MPCOLORPRODUCTS_HIDE_CURRENT" id="MPCOLORPRODUCTS_HIDE_CURRENT_on" value="1" {if $hide_current == 1}checked="checked"{/if}>
                        <label for="MPCOLORPRODUCTS_HIDE_CURRENT_on">{l s='Sì' mod='mpcolorproducts'}</label>
                        <input type="radio" name="MPCOLORPRODUCTS_HIDE_CURRENT" id="MPCOLORPRODUCTS_HIDE_CURRENT_off" value="0" {if $hide_current == 0}checked="checked"{/if}>
                        <label for="MPCOLORPRODUCTS_HIDE_CURRENT_off">{l s='No' mod='mpcolorproducts'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>

            <div class="panel-footer">
                <button type="submit" id="btn-save-config" class="btn btn-default pull-right">
                    <i class="process-icon-save"></i> {l s='Salva Impostazioni' mod='mpcolorproducts'}
                </button>
            </div>
        </form>
    </div>
</div>
```

### B. Metodo `getProductColorInfoExtended` (PHP 1.6 / 8.x Universale)
```php
public static function getProductColorInfoExtended($idProduct, $idAttribute, $idLang, $configuredAttrGroup = 0)
{
    $idProduct = (int) $idProduct;
    $idAttribute = (int) $idAttribute;
    $idLang = (int) $idLang;
    $dbPrefix = _DB_PREFIX_;

    $rawColorGroups = \Configuration::get('MPCOLORPRODUCTS_ATTRIBUTE_GROUP_ID');
    $rawHideGroups = \Configuration::get('MPCOLORPRODUCTS_HIDE_ATTR_GROUPS');

    $colorGroupIds = !empty($rawColorGroups) ? array_map('intval', explode(',', $rawColorGroups)) : [];
    $hideGroupIds = !empty($rawHideGroups) ? array_map('intval', explode(',', $rawHideGroups)) : [];

    $configuredGroups = array_unique(array_merge($colorGroupIds, $hideGroupIds));
    if ($configuredAttrGroup > 0) {
        $configuredGroups[] = (int) $configuredAttrGroup;
    }
    $configuredGroups = array_values(array_unique(array_filter($configuredGroups, function($id) { return $id > 0; })));

    if (!empty($configuredGroups)) {
        $idsList = implode(',', $configuredGroups);
        $sqlCombo = "SELECT DISTINCT al.`name`, a.`color`, a.`id_attribute`, a.`id_attribute_group`
                     FROM `{$dbPrefix}product_attribute` pa
                     INNER JOIN `{$dbPrefix}product_attribute_combination` pac
                         ON (pa.`id_product_attribute` = pac.`id_product_attribute`)
                     INNER JOIN `{$dbPrefix}attribute` a
                         ON (pac.`id_attribute` = a.`id_attribute`)
                     INNER JOIN `{$dbPrefix}attribute_lang` al
                         ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = {$idLang})
                     WHERE pa.`id_product` = {$idProduct}
                       AND a.`id_attribute_group` IN ({$idsList})
                     ORDER BY FIELD(a.`id_attribute_group`, {$idsList}), a.`id_attribute` ASC";

        $rows = \Db::getInstance()->executeS($sqlCombo);
        if (!empty($rows)) {
            $names = [];
            $firstColor = '';
            $firstAttrId = 0;
            $firstTextureUrl = '';

            foreach ($rows as $r) {
                if (!empty($r['name']) && !in_array($r['name'], $names)) {
                    $names[] = $r['name'];
                }
                if (empty($firstColor) && !empty($r['color'])) {
                    $firstColor = $r['color'];
                }
                if ($firstAttrId <= 0) {
                    $firstAttrId = (int) $r['id_attribute'];
                }
                if (empty($firstTextureUrl)) {
                    $tex = self::getAttributeTextureUrl((int) $r['id_attribute']);
                    if (!empty($tex)) {
                        $firstTextureUrl = $tex;
                    }
                }
            }

            if (!empty($names)) {
                return [
                    'id_attribute' => ($idAttribute > 0) ? $idAttribute : $firstAttrId,
                    'name' => implode(' / ', $names),
                    'color' => !empty($firstColor) ? $firstColor : '#ffffff',
                    'texture_url' => !empty($firstTextureUrl) ? $firstTextureUrl : self::getAttributeTextureUrl($firstAttrId),
                ];
            }
        }
    }

    if ($idAttribute > 0) {
        $colorInfo = self::getAttributeColorInfo($idAttribute, $idLang);
        if (!empty($colorInfo['name'])) {
            return [
                'id_attribute' => $idAttribute,
                'name' => $colorInfo['name'],
                'color' => !empty($colorInfo['color']) ? $colorInfo['color'] : '#ffffff',
                'texture_url' => self::getAttributeTextureUrl($idAttribute),
            ];
        }
    }

    $detectedAttrId = self::detectProductColorAttribute($idProduct, $configuredAttrGroup);
    $colorInfo = self::getAttributeColorInfo($detectedAttrId, $idLang);

    return [
        'id_attribute' => $detectedAttrId,
        'name' => !empty($colorInfo['name']) ? $colorInfo['name'] : '',
        'color' => !empty($colorInfo['color']) ? $colorInfo['color'] : '#ffffff',
        'texture_url' => self::getAttributeTextureUrl($detectedAttrId),
    ];
}
```

---

## 4. Come Utilizzare questo Documento nella Nuova Sessione

Quando aprirai il nuovo workspace per PrestaShop 1.6:
1. Digita semplicemente: **"Applichiamo le modifiche di mpcolorproducts per PrestaShop 1.6 seguendo le specifiche di c20f534a-f3a4-434b-ab46-b32000269c98 o leggendo PS16_MIGRATION_SPECS.md"**.
2. Il sistema riconoscerà istantaneamente tutte le regole architetturali, i metodi PHP, gli attributi frontend e la struttura delle query.
