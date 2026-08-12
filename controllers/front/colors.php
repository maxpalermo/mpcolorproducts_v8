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

use MpSoft\MpColorProducts\Helpers\ColorLineHelper;

class MpColorProductsColorsModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        if (!Tools::getValue('ajax')) {
            Tools::redirect('index.php');
        }

        $idProduct = (int) Tools::getValue('id_product');
        $featureValueId = (int) Tools::getValue('feature_value_id');
        $featureValueIdsParam = Tools::getValue('feature_value_ids');
        $idLang = (int) $this->context->language->id;
        $idShop = (int) $this->context->shop->id;

        if ($idProduct <= 0) {
            $this->ajaxResponse(false, 'ID Prodotto non valido.');
        }

        $selectedFeatureValueIds = [];
        if (!empty($featureValueIdsParam)) {
            if (is_array($featureValueIdsParam)) {
                $selectedFeatureValueIds = array_map('intval', $featureValueIdsParam);
            } else {
                $selectedFeatureValueIds = array_map('intval', explode(',', (string) $featureValueIdsParam));
            }
        } elseif ($featureValueId > 0) {
            $selectedFeatureValueIds = [$featureValueId];
        }
        $selectedFeatureValueIds = array_values(array_unique(array_filter($selectedFeatureValueIds)));

        $data = ColorLineHelper::getProductFeaturesAndColors($idProduct, $idLang, $idShop);
        $features = $data['features'] ?? [];
        $colorLine = $data['color_line'] ?? [];

        // Filtriamo i colori imponendo la combinazione esatta (INTERSEZIONE / AND) di tutte le caratteristiche selezionate
        $filteredColors = ColorLineHelper::filterColorLineByFeatureValues($colorLine, $features, $selectedFeatureValueIds);

        $displayMode = Configuration::get('MPCOLORPRODUCTS_DISPLAY_MODE', 'product_image');
        $hideCurrent = (bool) Configuration::get('MPCOLORPRODUCTS_HIDE_CURRENT', 0);
        $hideAttrGroups = Configuration::get('MPCOLORPRODUCTS_HIDE_ATTR_GROUPS', '');

        if ($hideCurrent) {
            $filteredColors = array_filter($filteredColors, function ($item) {
                return !$item['is_current'];
            });
            $filteredColors = array_values($filteredColors);
        }

        $currentColorName = '';
        foreach ($filteredColors as $item) {
            if ($item['is_current']) {
                $currentColorName = !empty($item['display_title']) ? $item['display_title'] : $item['color_name'];
                break;
            }
        }

        $sameLineColors = [];
        $otherLineColors = [];
        foreach ($filteredColors as $item) {
            if (!empty($item['same_features'])) {
                $sameLineColors[] = $item;
            } else {
                $otherLineColors[] = $item;
            }
        }

        $this->context->smarty->assign([
            'mp_colors_list' => $filteredColors,
            'mp_same_line_colors' => $sameLineColors,
            'mp_other_line_colors' => $otherLineColors,
            'mp_display_mode' => !empty($displayMode) ? $displayMode : 'product_image',
            'mp_current_color_name' => $currentColorName,
            'mp_hide_current' => $hideCurrent,
            'mp_hide_attr_groups' => !empty($hideAttrGroups) ? $hideAttrGroups : '',
        ]);

        $html = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'mpcolorproducts/views/templates/hook/_color_swatches_items.tpl');

        $this->ajaxResponse(true, '', [
            'html' => $html,
            'colors_count' => count($filteredColors),
            'colors' => $filteredColors,
        ]);
    }

    private function ajaxResponse(bool $success, string $message, array $extra = [])
    {
        header('Content-Type: application/json');
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message,
        ], $extra));
        exit;
    }
}
