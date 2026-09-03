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
        $this->version = '2.2.7';
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
        Configuration::updateValue('MPCOLORPRODUCTS_HIDE_ATTR_GROUPS', '14,15');
        Configuration::updateValue('MPCOLORPRODUCTS_ENABLE_FEATURE_FILTER', 1);
        Configuration::updateValue('MPCOLORPRODUCTS_SHOW_ALL_COLORS', 0);
        Configuration::updateValue('MPCOLORPRODUCTS_LABEL_SAME_LINE', []);
        Configuration::updateValue('MPCOLORPRODUCTS_LABEL_OTHER_COLORS', []);

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
        Configuration::deleteByName('MPCOLORPRODUCTS_HIDE_FEATURE_COMBINATIONS');
        Configuration::deleteByName('MPCOLORPRODUCTS_AFTER_ADD_TO_CART');
        Configuration::deleteByName('MPCOLORPRODUCTS_IMAGE_TYPE');
        Configuration::deleteByName('MPCOLORPRODUCTS_HIDE_ATTR_GROUPS');
        Configuration::deleteByName('MPCOLORPRODUCTS_ENABLE_FEATURE_FILTER');
        Configuration::deleteByName('MPCOLORPRODUCTS_SHOW_ALL_COLORS');
        Configuration::deleteByName('MPCOLORPRODUCTS_LABEL_SAME_LINE');
        Configuration::deleteByName('MPCOLORPRODUCTS_LABEL_OTHER_COLORS');

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

        $data = \MpSoft\MpColorProducts\Helpers\ColorLineHelper::getProductFeaturesAndColors($idProduct, $idLang, $idShop);
        $features = $data['features'] ?? [];
        $colorItems = $data['color_line'] ?? [];

        if (empty($colorItems)) {
            return '';
        }

        $displayMode = Configuration::get('MPCOLORPRODUCTS_DISPLAY_MODE');
        if (empty($displayMode)) {
            $displayMode = 'product_image';
        }

        $hideCurrentVal = Configuration::get('MPCOLORPRODUCTS_HIDE_CURRENT');
        $hideCurrent = ($hideCurrentVal !== false && $hideCurrentVal !== '') ? (bool) (int) $hideCurrentVal : false;

        $enableFeatureVal = Configuration::get('MPCOLORPRODUCTS_ENABLE_FEATURE_FILTER');
        $enableFeatureFilter = ($enableFeatureVal !== false && $enableFeatureVal !== '') ? (bool) (int) $enableFeatureVal : true;

        $showAllVal = Configuration::get('MPCOLORPRODUCTS_SHOW_ALL_COLORS');
        $showAllColors = ($showAllVal !== false && $showAllVal !== '') ? (bool) (int) $showAllVal : false;

        // Se il filtro caratteristiche è attivo e "Mostra tutti i colori" non è attivo, filtriamo inizialmente i colori sulle caratteristiche del prodotto corrente
        if ($enableFeatureFilter && !$showAllColors && !empty($features)) {
            $activeFeatureValIds = [];
            foreach ($features as $feat) {
                foreach ($feat['values'] as $val) {
                    if (!empty($val['is_selected']) && (int) $val['id_feature_value'] > 0) {
                        $activeFeatureValIds[] = (int) $val['id_feature_value'];
                    }
                }
            }

            if (!empty($activeFeatureValIds)) {
                $colorItems = \MpSoft\MpColorProducts\Helpers\ColorLineHelper::filterColorLineByFeatureValues($colorItems, $features, $activeFeatureValIds);
            }
        } elseif ($showAllColors && !empty($features)) {
            // Quando mostra tutti i colori è attivo, selezioniamo inizialmente l'opzione "Tutti" nei pill
            foreach ($features as &$feat) {
                if (isset($feat['values']) && is_array($feat['values'])) {
                    foreach ($feat['values'] as &$val) {
                        $val['is_selected'] = ((int) $val['id_feature_value'] === 0);
                    }
                }
            }
        }

        if ($hideCurrent) {
            $colorItems = array_filter($colorItems, function ($item) {
                return !$item['is_current'];
            });
            $colorItems = array_values($colorItems);
        }

        if (empty($colorItems)) {
            return '';
        }

        $currentColorName = '';
        foreach ($colorItems as $item) {
            if ($item['is_current']) {
                $currentColorName = !empty($item['display_title']) ? $item['display_title'] : $item['color_name'];
                break;
            }
        }

        $sameLineColors = $data['same_line_colors'] ?? [];
        $otherLineColors = $data['other_line_colors'] ?? [];

        if (empty($sameLineColors) && empty($otherLineColors)) {
            foreach ($colorItems as $item) {
                if (!empty($item['same_features'])) {
                    $sameLineColors[] = $item;
                } else {
                    $otherLineColors[] = $item;
                }
            }
        }

        $labelSameLine = Configuration::get('MPCOLORPRODUCTS_LABEL_SAME_LINE', $idLang);
        if (empty($labelSameLine)) {
            $labelSameLine = $this->l('Stessa linea');
        }

        $labelOtherColors = Configuration::get('MPCOLORPRODUCTS_LABEL_OTHER_COLORS', $idLang);
        if (empty($labelOtherColors)) {
            $labelOtherColors = $this->l('Altri colori');
        }

        $hideAttrGroups = Configuration::get('MPCOLORPRODUCTS_HIDE_ATTR_GROUPS');
        $hideFeatureCombVal = Configuration::get('MPCOLORPRODUCTS_HIDE_FEATURE_COMBINATIONS');
        $hideFeatureCombinations = ($hideFeatureCombVal !== false && $hideFeatureCombVal !== '') ? (int) $hideFeatureCombVal : 0;
        $afterAtcVal = Configuration::get('MPCOLORPRODUCTS_AFTER_ADD_TO_CART');
        $afterAddToCart = ($afterAtcVal !== false && $afterAtcVal !== '') ? (int) $afterAtcVal : 0;
        $foAjaxUrl = $this->context->link->getModuleLink('mpcolorproducts', 'colors', ['ajax' => 1, 'id_product' => $idProduct]);

        $this->smarty->assign([
            'mp_colors_list' => $colorItems,
            'mp_same_line_colors' => $sameLineColors,
            'mp_other_line_colors' => $otherLineColors,
            'mp_label_same_line' => $labelSameLine,
            'mp_label_other_colors' => $labelOtherColors,
            'mp_features_list' => $features,
            'mp_enable_feature_filter' => $enableFeatureFilter,
            'mp_hide_feature_combinations' => $hideFeatureCombinations,
            'mp_after_add_to_cart' => $afterAddToCart,
            'mp_display_mode' => !empty($displayMode) ? $displayMode : 'product_image',
            'mp_current_color_name' => $currentColorName,
            'mp_hide_current' => $hideCurrent,
            'mp_hide_attr_groups' => !empty($hideAttrGroups) ? $hideAttrGroups : '',
            'mp_fo_ajax_url' => $foAjaxUrl,
            'id_product' => $idProduct,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/product_colors.tpl');
    }
}
