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

if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

if (file_exists(__DIR__ . '/classes/models/autoload.php')) {
    require_once __DIR__ . '/classes/models/autoload.php';
}

class MpColorProducts extends \MpSoft\MpColorProducts\Module\ModuleTemplate
{
    protected $adminClassName;

    public function __construct()
    {
        $this->name = 'mpcolorproducts';
        $this->tab = 'administration';
        $this->version = '2.0.0';
        $this->author = 'Massimiliano Palermo';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('MP Linee Colore Prodotti');
        $this->description = $this->l('Raggruppa prodotti indipendenti in linee colore visibili nella scheda prodotto frontend');
        $this->adminClassName = 'AdminMpColorProducts';
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        if (!\MpSoft\MpColorProducts\Helpers\ColorLineHelper::createTables()) {
            return false;
        }

        // Impostazioni predefinite
        Configuration::updateValue('MPCOLORPRODUCTS_DISPLAY_MODE', 'product_image');
        Configuration::updateValue('MPCOLORPRODUCTS_HIDE_CURRENT', 0);
        Configuration::updateValue('MPCOLORPRODUCTS_IMAGE_TYPE', 'small_default');

        $hooks = [
            'displayProductButtons',
            'displayFooterProduct',
            'displayHeader',
            'actionAdminControllerSetMedia',
            'displayMpColorProducts',
        ];

        return $this->registerHooks($this, $hooks)
            && $this->installModuleTab(
                $this->l('MP Linee Colore'),
                $this->name,
                'AdminCatalog',
                $this->adminClassName
            );
    }

    public function uninstall()
    {
        \MpSoft\MpColorProducts\Helpers\ColorLineHelper::dropTables();
        Configuration::deleteByName('MPCOLORPRODUCTS_ATTRIBUTE_GROUP_ID');
        Configuration::deleteByName('MPCOLORPRODUCTS_DISPLAY_MODE');
        Configuration::deleteByName('MPCOLORPRODUCTS_HIDE_CURRENT');
        Configuration::deleteByName('MPCOLORPRODUCTS_IMAGE_TYPE');

        return parent::uninstall() && $this->uninstallModuleTab($this->adminClassName);
    }

    /**
     * Reindirizza la pagina di configurazione al controller Admin dedicato
     */
    public function getContent()
    {
        Tools::redirectAdmin(
            $this->context->link->getAdminLink($this->adminClassName)
        );
    }

    public function hookDisplayHeader($params)
    {
        $this->context->controller->addCSS($this->_path . 'views/css/mpcolorproducts-frontend.css');
        $this->context->controller->addJS($this->_path . 'views/js/components/ColorSwatches.js');
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        if (Tools::getValue('controller') === $this->adminClassName) {
            $this->context->controller->addCSS($this->_path . 'views/css/mpcolorproducts-admin.css');
            $this->context->controller->addCSS($this->_path . 'views/css/icon-menu.css');
        }
    }

    public function hookDisplayMpColorProducts($params)
    {
        return $this->renderColorSwatches($params);
    }

    public function hookDisplayProductButtons($params)
    {
        return $this->renderColorSwatches($params);
    }

    public function hookDisplayFooterProduct($params)
    {
        // Consentiamo la resa anche se l'hook primario non è presente nel tema
        return $this->renderColorSwatches($params);
    }

    /**
     * Resa del componente visivo per la scheda prodotto
     */
    protected function renderColorSwatches($params)
    {
        $idProduct = 0;
        if (isset($params['product']) && is_object($params['product'])) {
            $idProduct = (int) $params['product']->id;
        } elseif (isset($params['product']) && is_array($params['product'])) {
            $idProduct = (int) $params['product']['id_product'];
        } else {
            $idProduct = (int) Tools::getValue('id_product');
        }

        if ($idProduct <= 0) {
            return '';
        }

        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;

        $colorItems = \MpSoft\MpColorProducts\Helpers\ColorLineHelper::getProductColorLine($idProduct, $idLang, $idShop);
        if (empty($colorItems)) {
            return '';
        }

        $displayMode = Configuration::get('MPCOLORPRODUCTS_DISPLAY_MODE');
        $hideCurrent = (bool) Configuration::get('MPCOLORPRODUCTS_HIDE_CURRENT');

        // Se l'opzione hideCurrent è attiva, filtriamo il prodotto corrente
        if ($hideCurrent) {
            $colorItems = array_filter($colorItems, function ($item) {
                return !$item['is_current'];
            });
        }

        if (empty($colorItems)) {
            return '';
        }

        // Troviamo il nome del colore del prodotto corrente per il titolo (es. "Colore: Jeans")
        $currentColorName = '';
        foreach ($colorItems as $item) {
            if ($item['is_current']) {
                $currentColorName = $item['color_name'];
                break;
            }
        }

        $this->smarty->assign([
            'mp_colors_list' => $colorItems,
            'mp_display_mode' => !empty($displayMode) ? $displayMode : 'product_image',
            'mp_current_color_name' => $currentColorName,
            'mp_hide_current' => $hideCurrent,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/product_colors.tpl');
    }
}
