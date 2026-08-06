{*
 * PrestaShop Module: mpcolorproducts
 * Frontend Product Color Line Swatches View (PrestaShop 8.0+)
 *}

{if !empty($mp_colors_list)}
<div class="clearfix product-variants-item mpcolorproducts-container" id="mpcolorproducts-block" data-hide-attr-groups="{$mp_hide_attr_groups|escape:'html':'UTF-8'}">
    <span class="control-label">{l s='Colore' d='Modules.Mpcolorproducts.Shop'}:
        <span class="mpcolorproducts-selected-name" id="mpcolorproducts-current-name">{$mp_current_color_name|escape:'html':'UTF-8'}</span>
    </span>

    <ul class="mpcolorproducts-swatches-list clearfix">
        {foreach from=$mp_colors_list item=item}
            <li class="float-xs-left input-container">
                <a href="{$item.product_url|escape:'html':'UTF-8'}"
                   class="mpcolorproducts-swatch-item {if $item.is_current}active{/if}"
                   title="{$item.color_name|escape:'html':'UTF-8'}"
                   data-color-name="{$item.color_name|escape:'html':'UTF-8'}">
                    
                    {if $mp_display_mode == 'product_image' && !empty($item.cover_image_url)}
                        <span class="mpcolorproducts-swatch-img-wrap">
                            <img src="{$item.cover_image_url|escape:'html':'UTF-8'}"
                                 alt="{$item.color_name|escape:'html':'UTF-8'}"
                                 class="mpcolorproducts-swatch-img"
                                 loading="lazy">
                        </span>
                    {else}
                        {if !empty($item.texture_url)}
                            <span class="mpcolorproducts-swatch-color"
                                  style="background-image: url('{$item.texture_url|escape:'html':'UTF-8'}');">
                            </span>
                        {else}
                            <span class="mpcolorproducts-swatch-color"
                                  style="background-color: {$item.color_hex|escape:'html':'UTF-8'};">
                            </span>
                        {/if}
                    {/if}
                </a>
            </li>
        {/foreach}
    </ul>
</div>
{/if}
