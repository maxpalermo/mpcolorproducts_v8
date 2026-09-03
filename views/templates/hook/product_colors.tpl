{*
 * PrestaShop Module: mpcolorproducts
 * Frontend Product Color Line & Feature Selector View (PrestaShop 8.0+)
 *}

{if !empty($mp_colors_list)}
<div class="clearfix product-variants-item mpcolorproducts-container" id="mpcolorproducts-block" data-hide-attr-groups="{$mp_hide_attr_groups|escape:'html':'UTF-8'}" data-after-add-to-cart="{$mp_after_add_to_cart|escape:'html':'UTF-8'}" data-ajax-url="{$mp_fo_ajax_url|escape:'html':'UTF-8'}" data-id-product="{$id_product|escape:'html':'UTF-8'}">

    {if empty($mp_hide_feature_combinations) && isset($mp_enable_feature_filter) && $mp_enable_feature_filter && !empty($mp_features_list)}
        <div class="mpcolorproducts-features-wrapper mb-3">
            {foreach from=$mp_features_list item=feature}
                <div class="mpcolorproducts-feature-group mb-2">
                    <span class="control-label d-block font-weight-bold mb-1" style="font-size: 13px; color: #475569;">
                        {$feature.name|escape:'html':'UTF-8'}:
                    </span>
                    <div class="mpcolorproducts-feature-pills d-flex flex-wrap gap-2" style="gap: 8px;">
                        {foreach from=$feature.values item=fval}
                            <button type="button"
                                    class="btn btn-sm mp-feature-pill {if $fval.is_selected}active{/if} {if isset($fval.is_current) && $fval.is_current}is-current-feature{/if}"
                                    data-feature-value-id="{$fval.id_feature_value}"
                                    data-product-ids="{$fval.product_ids|json_encode|escape:'html':'UTF-8'}"
                                    {if isset($fval.is_current) && $fval.is_current}title="{l s='Caratteristica del prodotto in visione' d='Modules.Mpcolorproducts.Shop'}"{/if}
                                    style="border-radius: 20px; font-size: 12.5px; font-weight: 500; padding: 5px 14px; transition: all 0.15s ease;">
                                {$fval.value|escape:'html':'UTF-8'}
                            </button>
                        {/foreach}
                    </div>
                </div>
            {/foreach}
        </div>
    {/if}

    <div class="mpcolorproducts-swatches-section">
        <span class="control-label d-block mb-1">{l s='Colore' d='Modules.Mpcolorproducts.Shop'}:</span>
        <div class="mpcolorproducts-selected-name-wrapper mb-2">
            <span class="mpcolorproducts-selected-name" id="mpcolorproducts-current-name">{$mp_current_color_name|escape:'html':'UTF-8'}</span>
        </div>

        <ul class="mpcolorproducts-swatches-list clearfix" id="mpcolorproducts-swatches-list">
            {include file='./_color_swatches_items.tpl'}
        </ul>
    </div>
</div>
{/if}
