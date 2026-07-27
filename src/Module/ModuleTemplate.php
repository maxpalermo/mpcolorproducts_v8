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

namespace MpSoft\MpColorProducts\Module;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ModuleTemplate extends \Module
{
    /**
     * Installa un nuovo Tab (Menu) nel Back Office
     *
     * @param string $name Nome del tab
     * @param string $module_name Nome del modulo
     * @param string $parent Nome della tab padre (es. AdminCatalog)
     * @param string $controller Nome della classe controller (es. AdminMpColorProducts)
     * @param bool $active Se visualizzabile nel menu
     * @return bool
     */
    public function installModuleTab(
        $name,
        $module_name,
        $parent,
        $controller,
        $active = true
    ) {
        $tab = new \Tab();

        if ($parent != -1) {
            $id_parent = (int) \Tab::getIdFromClassName($parent);
            $tab->id_parent = $id_parent;
        } else {
            $tab->id_parent = -1;
        }
        
        $tab->name = [];
        if (!is_array($name)) {
            foreach (\Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = $name;
            }
        } else {
            foreach ($name as $name_lang) {
                $tab->name[$name_lang['id_lang']] = $name_lang['name'];
            }
        }

        $tab->class_name = $controller;
        $tab->module = $module_name;
        $tab->active = $active;

        return (bool) $tab->add();
    }

    /**
     * Disinstalla il Tab del modulo
     *
     * @param string $className
     * @return bool
     */
    public function uninstallModuleTab($className)
    {
        $id_tab = (int) \Tab::getIdFromClassName($className);
        if ($id_tab) {
            $tab = new \Tab($id_tab);
            return (bool) $tab->delete();
        }

        return true;
    }

    /**
     * Registra un array di hooks
     *
     * @param \Module $module
     * @param array $hooks
     * @return bool
     */
    public function registerHooks(\Module $module, array $hooks)
    {
        foreach ($hooks as $hook) {
            if (!$module->registerHook($hook)) {
                return false;
            }
        }

        return true;
    }
}
