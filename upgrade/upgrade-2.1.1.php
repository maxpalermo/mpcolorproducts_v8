<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Aggiorna il modulo mpcolorproducts alla versione 2.1.1
 * Aggiunge il campo `features` (TEXT JSON) alla tabella ps_mpcolorproducts_product
 *
 * @param MpColorProducts $module
 * @return bool
 */
function upgrade_module_2_1_1($module)
{
    $dbPrefix = _DB_PREFIX_;
    $tableName = $dbPrefix . 'mpcolorproducts_product';

    $sqlCheckColumn = "SHOW COLUMNS FROM `{$tableName}` LIKE 'features'";
    $columns = Db::getInstance()->executeS($sqlCheckColumn);
    $columnExists = !empty($columns);

    if (!$columnExists) {
        $sqlAddColumn = "ALTER TABLE `{$tableName}` ADD COLUMN `features` TEXT NULL AFTER `position`";
        if (!Db::getInstance()->execute($sqlAddColumn)) {
            return false;
        }
    }

    return true;
}
