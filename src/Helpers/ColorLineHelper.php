<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace MpSoft\MpColorProducts\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ColorLineHelper
{
    /**
     * Crea le tabelle del database all'installazione del modulo
     *
     * @return bool
     */
    public static function createTables()
    {
        $dbPrefix = _DB_PREFIX_;
        $engine = _MYSQL_ENGINE_;

        $sqlGroup = "CREATE TABLE IF NOT EXISTS `{$dbPrefix}mpcolorproducts_group` (
            `id_mpcolorproducts_group` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `id_attribute_group` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_mpcolorproducts_group`)
        ) ENGINE={$engine} DEFAULT CHARSET=utf8;";

        $sqlProduct = "CREATE TABLE IF NOT EXISTS `{$dbPrefix}mpcolorproducts_product` (
            `id_mpcolorproducts_group` INT(11) UNSIGNED NOT NULL,
            `id_product` INT(11) UNSIGNED NOT NULL,
            `id_attribute` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `position` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (`id_mpcolorproducts_group`, `id_product`),
            KEY `id_product` (`id_product`)
        ) ENGINE={$engine} DEFAULT CHARSET=utf8;";

        return \Db::getInstance()->execute($sqlGroup) && \Db::getInstance()->execute($sqlProduct);
    }

    /**
     * Elimina le tabelle alla disinstallazione
     *
     * @return bool
     */
    public static function dropTables()
    {
        $dbPrefix = _DB_PREFIX_;
        $sqlGroup = "DROP TABLE IF EXISTS `{$dbPrefix}mpcolorproducts_group`;";
        $sqlProduct = "DROP TABLE IF EXISTS `{$dbPrefix}mpcolorproducts_product`;";

        return \Db::getInstance()->execute($sqlGroup) && \Db::getInstance()->execute($sqlProduct);
    }

    /**
     * Rileva tutti i gruppi attributo che hanno is_color_group = 1
     *
     * @param int $idLang
     * @return array
     */
    public static function getColorAttributeGroups($idLang)
    {
        $dbPrefix = _DB_PREFIX_;
        $idLang = (int) $idLang;

        $sql = "SELECT ag.`id_attribute_group`, agl.`name`
                FROM `{$dbPrefix}attribute_group` ag
                INNER JOIN `{$dbPrefix}attribute_group_lang` agl
                    ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = {$idLang})
                WHERE ag.`is_color_group` = 1
                ORDER BY agl.`name` ASC";

        return \Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Rileva tutti i gruppi attributo del negozio per la selezione multipla BO
     *
     * @param int $idLang
     * @return array
     */
    public static function getAllAttributeGroups($idLang)
    {
        $dbPrefix = _DB_PREFIX_;
        $idLang = (int) $idLang;

        $sql = "SELECT ag.`id_attribute_group`, agl.`name`, ag.`is_color_group`
                FROM `{$dbPrefix}attribute_group` ag
                INNER JOIN `{$dbPrefix}attribute_group_lang` agl
                    ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = {$idLang})
                ORDER BY agl.`name` ASC";

        return \Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Recupera le varianti di colore della linea a cui appartiene un dato prodotto
     *
     * @param int $idProduct
     * @param int $idLang
     * @param int $idShop
     * @return array
     */
    public static function getProductColorLine($idProduct, $idLang, $idShop)
    {
        $dbPrefix = _DB_PREFIX_;
        $idProduct = (int) $idProduct;
        $idLang = (int) $idLang;
        $idShop = (int) $idShop;

        // 1. Troviamo prima l'id del gruppo linea di cui fa parte il prodotto (MAI usare LIMIT con getRow)
        $sqlGroupSearch = "SELECT cp.`id_mpcolorproducts_group`, cg.`id_attribute_group`
                           FROM `{$dbPrefix}mpcolorproducts_product` cp
                           INNER JOIN `{$dbPrefix}mpcolorproducts_group` cg
                               ON (cp.`id_mpcolorproducts_group` = cg.`id_mpcolorproducts_group`)
                           WHERE cp.`id_product` = {$idProduct}
                             AND cg.`active` = 1";

        $groupRow = \Db::getInstance()->getRow($sqlGroupSearch);
        if (!$groupRow || empty($groupRow['id_mpcolorproducts_group'])) {
            return [];
        }

        $idGroup = (int) $groupRow['id_mpcolorproducts_group'];
        $configuredAttrGroup = (int) $groupRow['id_attribute_group'];
        if ($configuredAttrGroup <= 0) {
            $configuredAttrGroup = (int) \Configuration::get('MPCOLORPRODUCTS_ATTRIBUTE_GROUP_ID');
        }

        // 2. Troviamo tutti i prodotti appartenenti alla linea
        $sqlLineProducts = "SELECT cp.`id_product`, cp.`id_attribute`, cp.`position`,
                                   pl.`name` AS product_name, p.`reference`
                            FROM `{$dbPrefix}mpcolorproducts_product` cp
                            INNER JOIN `{$dbPrefix}product` p
                                ON (cp.`id_product` = p.`id_product`)
                            INNER JOIN `{$dbPrefix}product_lang` pl
                                ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = {$idLang} AND pl.`id_shop` = {$idShop})
                            WHERE cp.`id_mpcolorproducts_group` = {$idGroup}
                              AND p.`active` = 1
                            ORDER BY cp.`position` ASC, cp.`id_product` ASC";

        $products = \Db::getInstance()->executeS($sqlLineProducts);
        if (empty($products)) {
            return [];
        }

        $link = \Context::getContext()->link;
        $result = [];

        foreach ($products as $item) {
            $itemIdProduct = (int) $item['id_product'];
            $itemIdAttribute = (int) $item['id_attribute'];

            // Otteniamo informazioni estese sull'attributo (colore, fantasia, rifiniture o combinati)
            $colorInfo = self::getProductColorInfoExtended($itemIdProduct, $itemIdAttribute, $idLang, $configuredAttrGroup);

            // Otteniamo l'immagine di copertina del prodotto
            $coverImageUrl = self::getProductCoverUrl($itemIdProduct, $link);

            // URL del prodotto
            $productUrl = $link->getProductLink($itemIdProduct);

            $result[] = [
                'id_product' => $itemIdProduct,
                'id_attribute' => $colorInfo['id_attribute'],
                'product_name' => $item['product_name'],
                'color_name' => !empty($colorInfo['name']) ? $colorInfo['name'] : $item['product_name'],
                'color_hex' => !empty($colorInfo['color']) ? $colorInfo['color'] : '#ffffff',
                'texture_url' => $colorInfo['texture_url'],
                'cover_image_url' => $coverImageUrl,
                'product_url' => $productUrl,
                'is_current' => ($itemIdProduct === $idProduct),
            ];
        }

        return $result;
    }

    /**
     * Rileva le informazioni estese sul colore/fantasia/rifiniture di un prodotto
     *
     * @param int $idProduct
     * @param int $idAttribute
     * @param int $idLang
     * @param int $configuredAttrGroup
     * @return array
     */
    public static function getProductColorInfoExtended($idProduct, $idAttribute, $idLang, $configuredAttrGroup = 0)
    {
        $idProduct = (int) $idProduct;
        $idAttribute = (int) $idAttribute;
        $idLang = (int) $idLang;

        $dbPrefix = _DB_PREFIX_;

        // 1. Raccogliamo tutti gli ID dei gruppi attributo configurati sia in MPCOLORPRODUCTS_ATTRIBUTE_GROUP_ID che in MPCOLORPRODUCTS_HIDE_ATTR_GROUPS
        $rawColorGroups = \Configuration::get('MPCOLORPRODUCTS_ATTRIBUTE_GROUP_ID');
        $rawHideGroups = \Configuration::get('MPCOLORPRODUCTS_HIDE_ATTR_GROUPS');

        $colorGroupIds = !empty($rawColorGroups) ? array_map('intval', explode(',', $rawColorGroups)) : [];
        $hideGroupIds = !empty($rawHideGroups) ? array_map('intval', explode(',', $rawHideGroups)) : [];

        $configuredGroups = array_unique(array_merge($colorGroupIds, $hideGroupIds));
        if ($configuredAttrGroup > 0) {
            $configuredGroups[] = (int) $configuredAttrGroup;
        }
        $configuredGroups = array_values(array_unique(array_filter($configuredGroups, function($id) { return $id > 0; })));

        // 2. Scansioniamo le combinazioni del prodotto per tutti i gruppi configurati ed uniamo tutti i nomi (es. "Jeans / Monocolore")
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

        // 3. Fallback: se è stato specificato un id_attribute manuale
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

        // 4. Fallback automatico
        $detectedAttrId = self::detectProductColorAttribute($idProduct, $configuredAttrGroup);
        $colorInfo = self::getAttributeColorInfo($detectedAttrId, $idLang);

        return [
            'id_attribute' => $detectedAttrId,
            'name' => !empty($colorInfo['name']) ? $colorInfo['name'] : '',
            'color' => !empty($colorInfo['color']) ? $colorInfo['color'] : '#ffffff',
            'texture_url' => self::getAttributeTextureUrl($detectedAttrId),
        ];
    }

    /**
     * Rileva l'id_attribute colore o variante alternativa di un prodotto provando su più livelli
     *
     * @param int $idProduct
     * @param int $idAttributeGroup
     * @return int
     */
    public static function detectProductColorAttribute($idProduct, $idAttributeGroup)
    {
        $dbPrefix = _DB_PREFIX_;
        $idProduct = (int) $idProduct;
        $idAttributeGroup = (int) $idAttributeGroup;

        // 1. Gruppo attributo predefinito
        if ($idAttributeGroup > 0) {
            $sql = "SELECT pac.`id_attribute`
                    FROM `{$dbPrefix}product_attribute` pa
                    INNER JOIN `{$dbPrefix}product_attribute_combination` pac
                        ON (pa.`id_product_attribute` = pac.`id_product_attribute`)
                    INNER JOIN `{$dbPrefix}attribute` a
                        ON (pac.`id_attribute` = a.`id_attribute`)
                    WHERE pa.`id_product` = {$idProduct}
                      AND a.`id_attribute_group` = {$idAttributeGroup}";

            $val = \Db::getInstance()->getValue($sql);
            if ($val) {
                return (int) $val;
            }
        }

        // 2. Gruppi attributi configurati per l'oscuramento (es. Fantasia, Rifiniture, ecc.)
        $rawHideGroups = \Configuration::get('MPCOLORPRODUCTS_HIDE_ATTR_GROUPS');
        if (!empty($rawHideGroups)) {
            $hideGroupIds = array_map('intval', explode(',', $rawHideGroups));
            $hideGroupIds = array_filter($hideGroupIds, function($id) { return $id > 0; });

            if (!empty($hideGroupIds)) {
                $idsList = implode(',', $hideGroupIds);
                $sqlHide = "SELECT pac.`id_attribute`
                            FROM `{$dbPrefix}product_attribute` pa
                            INNER JOIN `{$dbPrefix}product_attribute_combination` pac
                                ON (pa.`id_product_attribute` = pac.`id_product_attribute`)
                            INNER JOIN `{$dbPrefix}attribute` a
                                ON (pac.`id_attribute` = a.`id_attribute`)
                            WHERE pa.`id_product` = {$idProduct}
                              AND a.`id_attribute_group` IN ({$idsList})";

                $valHide = \Db::getInstance()->getValue($sqlHide);
                if ($valHide) {
                    return (int) $valHide;
                }
            }
        }

        // 3. Gruppi marcati come is_color_group = 1
        $sqlColorGroup = "SELECT pac.`id_attribute`
                          FROM `{$dbPrefix}product_attribute` pa
                          INNER JOIN `{$dbPrefix}product_attribute_combination` pac
                              ON (pa.`id_product_attribute` = pac.`id_product_attribute`)
                          INNER JOIN `{$dbPrefix}attribute` a
                              ON (pac.`id_attribute` = a.`id_attribute`)
                          INNER JOIN `{$dbPrefix}attribute_group` ag
                              ON (a.`id_attribute_group` = ag.`id_attribute_group`)
                          WHERE pa.`id_product` = {$idProduct}
                            AND ag.`is_color_group` = 1";

        $valColorGroup = \Db::getInstance()->getValue($sqlColorGroup);
        if ($valColorGroup) {
            return (int) $valColorGroup;
        }

        // 4. Primo attributo di qualsiasi combinazione del prodotto
        $sqlAny = "SELECT pac.`id_attribute`
                   FROM `{$dbPrefix}product_attribute` pa
                   INNER JOIN `{$dbPrefix}product_attribute_combination` pac
                       ON (pa.`id_product_attribute` = pac.`id_product_attribute`)
                   WHERE pa.`id_product` = {$idProduct}";

        $valAny = \Db::getInstance()->getValue($sqlAny);
        return $valAny ? (int) $valAny : 0;
    }

    /**
     * Recupera le info del colore (nome e valore esadecimale) (MAI usare LIMIT con getRow)
     *
     * @param int $idAttribute
     * @param int $idLang
     * @return array
     */
    public static function getAttributeColorInfo($idAttribute, $idLang)
    {
        if ($idAttribute <= 0) {
            return ['name' => '', 'color' => ''];
        }

        $dbPrefix = _DB_PREFIX_;
        $idAttribute = (int) $idAttribute;
        $idLang = (int) $idLang;

        $sql = "SELECT a.`color`, al.`name`
                FROM `{$dbPrefix}attribute` a
                INNER JOIN `{$dbPrefix}attribute_lang` al
                    ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = {$idLang})
                WHERE a.`id_attribute` = {$idAttribute}";

        $row = \Db::getInstance()->getRow($sql);
        return $row ?: ['name' => '', 'color' => ''];
    }

    /**
     * Verifica l'esistenza del file texture img/co/{id_attribute}.jpg e ne restituisce l'URL se presente
     *
     * @param int $idAttribute
     * @return string
     */
    public static function getAttributeTextureUrl($idAttribute)
    {
        if ($idAttribute <= 0) {
            return '';
        }

        $idAttribute = (int) $idAttribute;
        $textureFile = _PS_COL_IMG_DIR_ . $idAttribute . '.jpg';

        if (file_exists($textureFile)) {
            return _THEME_COL_DIR_ . $idAttribute . '.jpg';
        }

        return '';
    }

    /**
     * Ottiene l'URL dell'immagine di copertina del prodotto
     *
     * @param int $idProduct
     * @param \Link $link
     * @return string
     */
    public static function getProductCoverUrl($idProduct, $link)
    {
        $idProduct = (int) $idProduct;
        $cover = \Product::getCover($idProduct);

        if ($cover && !empty($cover['id_image'])) {
            $imageType = \Configuration::get('MPCOLORPRODUCTS_IMAGE_TYPE');
            if (empty($imageType)) {
                $imageType = 'small_default';
            }
            return $link->getImageLink('product', $idProduct . '-' . $cover['id_image'], $imageType);
        }

        return '';
    }

    /**
     * Ottiene l'elenco delle linee di colore per la gestione in Back Office
     *
     * @return array
     */
    public static function getAllGroups()
    {
        $dbPrefix = _DB_PREFIX_;

        $sql = "SELECT cg.`id_mpcolorproducts_group`, cg.`name`, cg.`id_attribute_group`, cg.`active`,
                       cg.`date_add`, cg.`date_upd`,
                       COUNT(cp.`id_product`) AS total_products
                FROM `{$dbPrefix}mpcolorproducts_group` cg
                LEFT JOIN `{$dbPrefix}mpcolorproducts_product` cp
                    ON (cg.`id_mpcolorproducts_group` = cp.`id_mpcolorproducts_group`)
                GROUP BY cg.`id_mpcolorproducts_group`
                ORDER BY cg.`id_mpcolorproducts_group` DESC";

        return \Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Cerca prodotti per nome o riferimento per il selettore AJAX nel BO
     *
     * @param string $query
     * @param int $idLang
     * @return array
     */
    public static function searchProducts($query, $idLang)
    {
        $dbPrefix = _DB_PREFIX_;
        $queryEsc = \pSQL($query);
        $idLang = (int) $idLang;
        $idProd = (int) $query;

        $sql = "SELECT p.`id_product`, p.`reference`, pl.`name`
                FROM `{$dbPrefix}product` p
                INNER JOIN `{$dbPrefix}product_lang` pl
                    ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = {$idLang})
                WHERE p.`active` = 1
                  AND (pl.`name` LIKE '%{$queryEsc}%' OR p.`reference` LIKE '%{$queryEsc}%' OR p.`id_product` = {$idProd})
                ORDER BY pl.`name` ASC";

        return \Db::getInstance()->executeS($sql) ?: [];
    }

    /**
     * Salva o aggiorna un gruppo linea prodotti
     *
     * @param int $idGroup
     * @param string $name
     * @param int $idAttributeGroup
     * @param int $active
     * @param array $products Array di item ['id_product' => int, 'id_attribute' => int]
     * @return int ID del gruppo salvato o 0 in caso di errore
     */
    public static function saveGroup($idGroup, $name, $idAttributeGroup, $active, array $products)
    {
        $dbPrefix = _DB_PREFIX_;
        $idGroup = (int) $idGroup;
        $name = \pSQL($name);
        $idAttributeGroup = (int) $idAttributeGroup;
        $active = (int) $active;
        $now = date('Y-m-d H:i:s');

        if ($idGroup > 0) {
            $sqlUpdate = "UPDATE `{$dbPrefix}mpcolorproducts_group`
                          SET `name` = '{$name}',
                              `id_attribute_group` = {$idAttributeGroup},
                              `active` = {$active},
                              `date_upd` = '{$now}'
                          WHERE `id_mpcolorproducts_group` = {$idGroup}";
            \Db::getInstance()->execute($sqlUpdate);
        } else {
            $sqlInsert = "INSERT INTO `{$dbPrefix}mpcolorproducts_group`
                          (`name`, `id_attribute_group`, `active`, `date_add`, `date_upd`)
                          VALUES ('{$name}', {$idAttributeGroup}, {$active}, '{$now}', '{$now}')";
            \Db::getInstance()->execute($sqlInsert);
            $idGroup = (int) \Db::getInstance()->Insert_ID();
        }

        if ($idGroup <= 0) {
            return 0;
        }

        // Sincronizziamo i prodotti associati
        $sqlDeleteProducts = "DELETE FROM `{$dbPrefix}mpcolorproducts_product`
                              WHERE `id_mpcolorproducts_group` = {$idGroup}";
        \Db::getInstance()->execute($sqlDeleteProducts);

        $position = 0;
        foreach ($products as $item) {
            $idProduct = (int) $item['id_product'];
            $idAttribute = isset($item['id_attribute']) ? (int) $item['id_attribute'] : 0;

            if ($idProduct > 0) {
                $sqlInsertProd = "INSERT INTO `{$dbPrefix}mpcolorproducts_product`
                                  (`id_mpcolorproducts_group`, `id_product`, `id_attribute`, `position`)
                                  VALUES ({$idGroup}, {$idProduct}, {$idAttribute}, {$position})";
                \Db::getInstance()->execute($sqlInsertProd);
                $position++;
            }
        }

        return $idGroup;
    }

    /**
     * Elimina un gruppo di linee colore
     *
     * @param int $idGroup
     * @return bool
     */
    public static function deleteGroup($idGroup)
    {
        $dbPrefix = _DB_PREFIX_;
        $idGroup = (int) $idGroup;

        $sqlDelProducts = "DELETE FROM `{$dbPrefix}mpcolorproducts_product`
                           WHERE `id_mpcolorproducts_group` = {$idGroup}";
        $sqlDelGroup = "DELETE FROM `{$dbPrefix}mpcolorproducts_group`
                        WHERE `id_mpcolorproducts_group` = {$idGroup}";

        return \Db::getInstance()->execute($sqlDelProducts) && \Db::getInstance()->execute($sqlDelGroup);
    }
}
