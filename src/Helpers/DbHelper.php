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

namespace MpSoft\MpColorProducts\Helpers;

use pSQL;

class DbHelper
{
    /**
     * Restituisce il nome completo della tabella con il prefisso _DB_PREFIX_
     * Se il prefisso è già presente, evita la duplicazione.
     *
     * @param string $tableName Nome della tabella con o senza prefisso
     * @return string
     */
    public static function getFullTableName(string $tableName): string
    {
        $prefix = _DB_PREFIX_;

        if (strpos($tableName, $prefix) === 0) {
            return $tableName;
        }

        return $prefix . $tableName;
    }

    /**
     * Costruisce una query SQL parametrica sanificando i parametri in base al tipo.
     *
     * Struttura di ciascun elemento in $params:
     * [
     *     'name'  => 'nome_parametro', // corrisponde a :nome_parametro nella query SQL
     *     'value' => $valore,
     *     'type'  => 'int|float|bool|date|string|int_array|raw' // opzionale, default 'string'
     * ]
     *
     * @param string $sql Query SQL con i segnaposto nel formato :nome_parametro
     * @param array $params Array associativo dei parametri da sostituire
     * @return string Query SQL finale sanificata
     */
    public static function buildQuery(string $sql, array $params = []): string
    {
        if (empty($params)) {
            return $sql;
        }

        foreach ($params as $param) {
            if (!isset($param['name']) || !array_key_exists('value', $param)) {
                continue;
            }

            $placeholder = ':' . $param['name'];
            $type = isset($param['type']) ? strtolower($param['type']) : 'string';
            $val = $param['value'];

            $escapedValue = self::escapeValue($val, $type);

            $sql = str_replace($placeholder, $escapedValue, $sql);
        }

        return $sql;
    }

    /**
     * Esegue l'escaping di un singolo valore in base al tipo specificato
     *
     * @param mixed $val Valore da sanificare
     * @param string $type Tipo di dato (int, float, bool, date, string, int_array, raw)
     * @return string Valore sanificato pronto per la query SQL
     */
    private static function escapeValue($val, string $type): string
    {
        switch ($type) {
            case 'int':
            case 'integer':
                return (string) (int) $val;

            case 'float':
            case 'double':
            case 'decimal':
                return (string) (float) $val;

            case 'bool':
            case 'boolean':
                return $val ? '1' : '0';

            case 'date':
            case 'datetime':
                if (empty($val)) {
                    return 'NULL';
                }
                return "'" . pSQL($val) . "'";

            case 'int_array':
                if (!is_array($val) || empty($val)) {
                    return '0';
                }
                $cleanArray = array_map('intval', $val);
                return implode(',', $cleanArray);

            case 'raw':
                return (string) $val;

            case 'string':
            default:
                if ($val === null) {
                    return 'NULL';
                }
                return "'" . pSQL($val) . "'";
        }
    }
}
