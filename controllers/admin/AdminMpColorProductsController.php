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

if (file_exists(_PS_MODULE_DIR_ . 'mpcolorproducts/vendor/autoload.php')) {
    require_once _PS_MODULE_DIR_ . 'mpcolorproducts/vendor/autoload.php';
}

class AdminMpColorProductsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->className = 'Configuration';
        $this->table = 'configuration';

        parent::__construct();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJqueryPlugin(['chosen']);

        // Fogli di stile e script isolati per Bootstrap Table e componente admin
        $this->addCSS([
            $this->module->getPathUri() . 'views/css/bstable.css',
            $this->module->getPathUri() . 'views/css/growl.css',
            $this->module->getPathUri() . 'views/css/mpcolorproducts-admin.css',
            'https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.1/dist/bootstrap-table.min.css',
        ]);

        $this->addJS([
            'https://cdn.jsdelivr.net/npm/bootstrap-table@1.22.1/dist/bootstrap-table.min.js',
            $this->module->getPathUri() . 'views/js/BootstrapTablePaginationShadcn.js',
            $this->module->getPathUri() . 'views/js/components/AdminMultiLangInput.js',
            $this->module->getPathUri() . 'views/js/components/AdminColorLines.js',
        ]);
    }

    public function initContent()
    {
        parent::initContent();

        if (Tools::isSubmit('submitMpColorProductsConfig')) {
            $this->postProcessConfig();
        }

        $idLang = (int) $this->context->language->id;

        $colorGroups = \MpSoft\MpColorProducts\Helpers\ColorLineHelper::getColorAttributeGroups($idLang);
        $allAttrGroups = \MpSoft\MpColorProducts\Helpers\ColorLineHelper::getAllAttributeGroups($idLang);
        $groups = \MpSoft\MpColorProducts\Helpers\ColorLineHelper::getAllGroups();
        $rawSelectedGroups = Configuration::get('MPCOLORPRODUCTS_ATTRIBUTE_GROUP_ID');
        $selectedAttrGroups = !empty($rawSelectedGroups) ? array_map('intval', explode(',', $rawSelectedGroups)) : [];

        $displayMode = Configuration::get('MPCOLORPRODUCTS_DISPLAY_MODE');
        $hideCurrentVal = Configuration::get('MPCOLORPRODUCTS_HIDE_CURRENT');
        $hideCurrent = ($hideCurrentVal !== false && $hideCurrentVal !== '') ? (int) $hideCurrentVal : 0;
        $hideFeatureCombVal = Configuration::get('MPCOLORPRODUCTS_HIDE_FEATURE_COMBINATIONS');
        $hideFeatureCombinations = ($hideFeatureCombVal !== false && $hideFeatureCombVal !== '') ? (int) $hideFeatureCombVal : 0;
        $afterAtcVal = Configuration::get('MPCOLORPRODUCTS_AFTER_ADD_TO_CART');
        $afterAddToCart = ($afterAtcVal !== false && $afterAtcVal !== '') ? (int) $afterAtcVal : 0;
        $enableFeatureVal = Configuration::get('MPCOLORPRODUCTS_ENABLE_FEATURE_FILTER');
        $enableFeatureFilter = ($enableFeatureVal !== false && $enableFeatureVal !== '') ? (int) $enableFeatureVal : 1;
        $showAllVal = Configuration::get('MPCOLORPRODUCTS_SHOW_ALL_COLORS');
        $showAllColors = ($showAllVal !== false && $showAllVal !== '') ? (int) $showAllVal : 0;
        $imageType = Configuration::get('MPCOLORPRODUCTS_IMAGE_TYPE');
        $imageTypes = ImageType::getImagesTypes('products');

        $rawHiddenGroups = Configuration::get('MPCOLORPRODUCTS_HIDE_ATTR_GROUPS');
        $hiddenAttrGroups = !empty($rawHiddenGroups) ? array_map('intval', explode(',', $rawHiddenGroups)) : [];

        $languages = Language::getLanguages(true);

        $labelSameLine = [];
        $labelOtherColors = [];
        foreach ($languages as $lang) {
            $idL = (int) $lang['id_lang'];
            $labelSameLine[$idL] = Configuration::get('MPCOLORPRODUCTS_LABEL_SAME_LINE', $idL);
            $labelOtherColors[$idL] = Configuration::get('MPCOLORPRODUCTS_LABEL_OTHER_COLORS', $idL);
        }

        $templateParams = [
            'languages' => $languages,
            'current_lang_id' => $idLang,
            'label_same_line' => $labelSameLine,
            'label_other_colors' => $labelOtherColors,
            'color_groups' => $colorGroups,
            'all_attribute_groups' => $allAttrGroups,
            'hidden_attr_groups' => $hiddenAttrGroups,
            'selected_attr_groups' => $selectedAttrGroups,
            'display_mode' => !empty($displayMode) ? $displayMode : 'product_image',
            'hide_current' => $hideCurrent,
            'hide_feature_combinations' => $hideFeatureCombinations,
            'after_add_to_cart' => $afterAddToCart,
            'enable_feature_filter' => $enableFeatureFilter,
            'show_all_colors' => $showAllColors,
            'image_type' => !empty($imageType) ? $imageType : 'small_default',
            'image_types' => $imageTypes,
            'color_line_groups' => $groups,
            'admin_ajax_url' => $this->context->link->getAdminLink('AdminMpColorProducts'),
        ];

        $content = $this->renderTwigTemplate(
            '@Modules/mpcolorproducts/views/templates/admin/configure.html.twig',
            $templateParams
        );

        $this->context->smarty->assign('content', $content);
    }

    /**
     * Esegue il rendering del template Twig in PrestaShop 8.2
     *
     * @param string $templatePath
     * @param array $params
     * @return string
     */
    protected function renderTwigTemplate($templatePath, array $params = [])
    {
        if (isset($this->container) && $this->container->has('twig')) {
            /** @var \Twig\Environment $twig */
            $twig = $this->get('twig');
            return $twig->render($templatePath, $params);
        }

        // Fallback tramite Twig Environment
        $adminTemplateDir = _PS_MODULE_DIR_ . 'mpcolorproducts/views/templates/admin/';
        $loader = new \Twig\Loader\FilesystemLoader($adminTemplateDir);
        $twig = new \Twig\Environment($loader);

        return $twig->render('configure.html.twig', $params);
    }

    protected function postProcessConfig()
    {
        $attrGroupIds = Tools::getValue('MPCOLORPRODUCTS_ATTRIBUTE_GROUP_ID');
        if (!is_array($attrGroupIds)) {
            $attrGroupIds = !empty($attrGroupIds) ? [(int) $attrGroupIds] : [];
        }
        $attrGroupStr = implode(',', array_map('intval', array_unique($attrGroupIds)));

        $displayMode = Tools::getValue('MPCOLORPRODUCTS_DISPLAY_MODE');
        $hideCurrent = (int) Tools::getValue('MPCOLORPRODUCTS_HIDE_CURRENT');
        $hideFeatureCombinations = (int) Tools::getValue('MPCOLORPRODUCTS_HIDE_FEATURE_COMBINATIONS', 0);
        $afterAddToCart = (int) Tools::getValue('MPCOLORPRODUCTS_AFTER_ADD_TO_CART', 0);
        $enableFeatureFilter = (int) Tools::getValue('MPCOLORPRODUCTS_ENABLE_FEATURE_FILTER', 1);
        $showAllColors = (int) Tools::getValue('MPCOLORPRODUCTS_SHOW_ALL_COLORS', 0);
        $imageType = Tools::getValue('MPCOLORPRODUCTS_IMAGE_TYPE');

        $hideGroups = Tools::getValue('MPCOLORPRODUCTS_HIDE_ATTR_GROUPS');
        if (!is_array($hideGroups)) {
            $hideGroups = [];
        }
        $hideGroupStr = implode(',', array_map('intval', array_unique($hideGroups)));

        $languages = Language::getLanguages(true);

        $sameLineRaw = Tools::getValue('MPCOLORPRODUCTS_LABEL_SAME_LINE');
        if (is_string($sameLineRaw) && !empty($sameLineRaw)) {
            $decoded = json_decode($sameLineRaw, true);
            if (is_array($decoded)) {
                $sameLineRaw = $decoded;
            }
        }
        if (!is_array($sameLineRaw)) {
            $sameLineRaw = [];
        }

        $otherColorsRaw = Tools::getValue('MPCOLORPRODUCTS_LABEL_OTHER_COLORS');
        if (is_string($otherColorsRaw) && !empty($otherColorsRaw)) {
            $decoded = json_decode($otherColorsRaw, true);
            if (is_array($decoded)) {
                $otherColorsRaw = $decoded;
            }
        }
        if (!is_array($otherColorsRaw)) {
            $otherColorsRaw = [];
        }

        $sameLineConfig = [];
        $otherColorsConfig = [];
        foreach ($languages as $lang) {
            $idL = (int) $lang['id_lang'];
            $sameLineConfig[$idL] = isset($sameLineRaw[$idL]) ? trim($sameLineRaw[$idL]) : '';
            $otherColorsConfig[$idL] = isset($otherColorsRaw[$idL]) ? trim($otherColorsRaw[$idL]) : '';
        }

        Configuration::updateValue('MPCOLORPRODUCTS_ATTRIBUTE_GROUP_ID', $attrGroupStr);
        Configuration::updateValue('MPCOLORPRODUCTS_DISPLAY_MODE', $displayMode);
        Configuration::updateValue('MPCOLORPRODUCTS_HIDE_CURRENT', $hideCurrent);
        Configuration::updateValue('MPCOLORPRODUCTS_HIDE_FEATURE_COMBINATIONS', $hideFeatureCombinations);
        Configuration::updateValue('MPCOLORPRODUCTS_AFTER_ADD_TO_CART', $afterAddToCart);
        Configuration::updateValue('MPCOLORPRODUCTS_ENABLE_FEATURE_FILTER', $enableFeatureFilter);
        Configuration::updateValue('MPCOLORPRODUCTS_SHOW_ALL_COLORS', $showAllColors);
        Configuration::updateValue('MPCOLORPRODUCTS_IMAGE_TYPE', $imageType);
        Configuration::updateValue('MPCOLORPRODUCTS_HIDE_ATTR_GROUPS', $hideGroupStr);
        Configuration::updateValue('MPCOLORPRODUCTS_LABEL_SAME_LINE', $sameLineConfig);
        Configuration::updateValue('MPCOLORPRODUCTS_LABEL_OTHER_COLORS', $otherColorsConfig);

        $this->confirmations[] = $this->l('Impostazioni aggiornate con successo.');
    }

    /**
     * Gestione delle richieste AJAX via fetch (application/x-www-form-urlencoded)
     */
    public function postProcess()
    {
        if (Tools::getValue('ajax') == '1') {
            $action = Tools::getValue('action');
            $idLang = (int) $this->context->language->id;

            switch ($action) {
                case 'saveConfig':
                    $this->postProcessConfig();
                    header('Content-Type: application/json');
                    die(json_encode([
                        'success' => true,
                        'message' => $this->l('Impostazioni aggiornate con successo.')
                    ]));

                case 'getGroupsList':
                    $groups = \MpSoft\MpColorProducts\Helpers\ColorLineHelper::getAllGroups();
                    header('Content-Type: application/json');
                    die(json_encode($groups));

                case 'searchProducts':
                    $query = Tools::getValue('query');
                    $products = \MpSoft\MpColorProducts\Helpers\ColorLineHelper::searchProducts($query, $idLang);
                    $link = $this->context->link;

                    $formatted = [];
                    foreach ($products as $p) {
                        $idP = (int) $p['id_product'];
                        $coverUrl = \MpSoft\MpColorProducts\Helpers\ColorLineHelper::getProductCoverUrl($idP, $link);
                        
                        $attrGroupId = (int) Configuration::get('MPCOLORPRODUCTS_ATTRIBUTE_GROUP_ID');
                        $detectedAttrId = \MpSoft\MpColorProducts\Helpers\ColorLineHelper::detectProductColorAttribute($idP, $attrGroupId);

                        $colorInfo = \MpSoft\MpColorProducts\Helpers\ColorLineHelper::getProductColorInfoExtended($idP, $detectedAttrId, $idLang);

                        $formatted[] = [
                            'id_product' => $idP,
                            'name' => $p['name'],
                            'reference' => $p['reference'],
                            'cover_url' => $coverUrl,
                            'product_url' => $link->getProductLink($idP),
                            'detected_attribute_id' => $detectedAttrId,
                            'color_name' => !empty($colorInfo['name']) ? $colorInfo['name'] : $p['name'],
                            'color_hex' => !empty($colorInfo['color']) ? $colorInfo['color'] : '#ffffff',
                            'texture_url' => !empty($colorInfo['texture_url']) ? $colorInfo['texture_url'] : '',
                            'available_features' => \MpSoft\MpColorProducts\Helpers\ColorLineHelper::getProductFeaturesList($idP, $idLang),
                        ];
                    }

                    header('Content-Type: application/json');
                    die(json_encode(['success' => true, 'products' => $formatted]));

                case 'getGroupDetails':
                    $idGroup = (int) Tools::getValue('id_group');
                    if ($idGroup <= 0) {
                        header('Content-Type: application/json');
                        die(json_encode(['success' => false, 'message' => 'ID gruppo non valido']));
                    }

                    $dbPrefix = _DB_PREFIX_;
                    $sqlGroup = "SELECT *
                                 FROM `{$dbPrefix}mpcolorproducts_group`
                                 WHERE `id_mpcolorproducts_group` = {$idGroup}";
                    $groupData = Db::getInstance()->getRow($sqlGroup);

                    $sqlProducts = "SELECT cp.*, p.`reference`, pl.`name` AS product_name
                                    FROM `{$dbPrefix}mpcolorproducts_product` cp
                                    INNER JOIN `{$dbPrefix}product` p
                                        ON (cp.`id_product` = p.`id_product`)
                                    INNER JOIN `{$dbPrefix}product_lang` pl
                                        ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = {$idLang})
                                    WHERE cp.`id_mpcolorproducts_group` = {$idGroup}
                                    ORDER BY cp.`position` ASC";
                    $productsData = Db::getInstance()->executeS($sqlProducts) ?: [];

                    $link = $this->context->link;
                    foreach ($productsData as &$p) {
                        $idP = (int) $p['id_product'];
                        $attrId = (int) $p['id_attribute'];
                        $colorInfo = \MpSoft\MpColorProducts\Helpers\ColorLineHelper::getProductColorInfoExtended($idP, $attrId, $idLang);

                        $p['cover_url'] = \MpSoft\MpColorProducts\Helpers\ColorLineHelper::getProductCoverUrl($idP, $link);
                        $p['product_url'] = $link->getProductLink($idP);
                        $p['color_name'] = !empty($colorInfo['name']) ? $colorInfo['name'] : $p['product_name'];
                        $p['color_hex'] = !empty($colorInfo['color']) ? $colorInfo['color'] : '#ffffff';
                        $p['texture_url'] = !empty($colorInfo['texture_url']) ? $colorInfo['texture_url'] : '';
                        $p['available_features'] = \MpSoft\MpColorProducts\Helpers\ColorLineHelper::getProductFeaturesList($idP, $idLang);
                        $p['features'] = !empty($p['features']) ? json_decode($p['features'], true) : [];
                    }

                    header('Content-Type: application/json');
                    die(json_encode([
                        'success' => true,
                        'group' => $groupData,
                        'products' => $productsData,
                    ]));

                case 'saveGroup':
                    $idGroup = (int) Tools::getValue('id_group');
                    $name = Tools::getValue('name');
                    $idAttributeGroup = (int) Tools::getValue('id_attribute_group');
                    $active = (int) Tools::getValue('active');
                    $productsJson = Tools::getValue('products_json');

                    $products = json_decode($productsJson, true);
                    if (!is_array($products)) {
                        $products = [];
                    }

                    $resultId = \MpSoft\MpColorProducts\Helpers\ColorLineHelper::saveGroup($idGroup, $name, $idAttributeGroup, $active, $products);

                    header('Content-Type: application/json');
                    if ($resultId > 0) {
                        die(json_encode(['success' => true, 'id_group' => $resultId]));
                    } else {
                        die(json_encode(['success' => false, 'message' => 'Errore durante il salvataggio della linea']));
                    }

                case 'deleteGroup':
                    $idGroup = (int) Tools::getValue('id_group');
                    $deleted = \MpSoft\MpColorProducts\Helpers\ColorLineHelper::deleteGroup($idGroup);

                    header('Content-Type: application/json');
                    die(json_encode(['success' => $deleted]));
            }
        }

        parent::postProcess();
    }
}
