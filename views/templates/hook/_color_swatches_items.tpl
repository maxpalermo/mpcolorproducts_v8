{*
 * PrestaShop Module: mpcolorproducts
 * Partial template for color swatches items
 *}

{function name="render_swatch_item" item=null}
    <li class="float-xs-left input-container">
        <a href="{$item.product_url|escape:'html':'UTF-8'}"
           class="mpcolorproducts-swatch-item {if $item.is_current}active{/if} {if isset($item.same_features) && $item.same_features}same-features{/if}"
           title="{if !empty($item.display_title)}{$item.display_title|escape:'html':'UTF-8'}{else}{$item.color_name|escape:'html':'UTF-8'}{/if}"
           data-color-name="{if !empty($item.display_title)}{$item.display_title|escape:'html':'UTF-8'}{else}{$item.color_name|escape:'html':'UTF-8'}{/if}"
           data-id-product="{$item.id_product}">
            
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
{/function}

{if !empty($mp_same_line_colors) || !empty($mp_other_line_colors)}
    {if !empty($mp_same_line_colors)}
        <div class="mpcolorproducts-swatch-group mb-3">
            <div class="mpcolorproducts-group-title">
                {if !empty($mp_label_same_line)}{$mp_label_same_line|escape:'html':'UTF-8'}{else}{l s='Stessa linea' d='Modules.Mpcolorproducts.Shop'}{/if}
            </div>
            <ul class="mpcolorproducts-swatches-list clearfix m-0 p-0">
                {foreach from=$mp_same_line_colors item=item}
                    {render_swatch_item item=$item}
                {/foreach}
            </ul>
        </div>
    {/if}

    {if !empty($mp_other_line_colors)}
        <div class="mpcolorproducts-swatch-group mb-3">
            <div class="mpcolorproducts-group-title">
                {if !empty($mp_label_other_colors)}{$mp_label_other_colors|escape:'html':'UTF-8'}{else}{l s='Altri colori' d='Modules.Mpcolorproducts.Shop'}{/if}
            </div>
            <ul class="mpcolorproducts-swatches-list clearfix m-0 p-0">
                {foreach from=$mp_other_line_colors item=item}
                    {render_swatch_item item=$item}
                {/foreach}
            </ul>
        </div>
    {/if}
{elseif !empty($mp_colors_list)}
    <ul class="mpcolorproducts-swatches-list clearfix m-0 p-0">
        {foreach from=$mp_colors_list item=item}
            {render_swatch_item item=$item}
        {/foreach}
    </ul>
{else}
    <div class="mpcolorproducts-no-colors text-muted small py-2 px-1" style="font-size: 12.5px;">
        {l s='Nessuna variante colore disponibile per l\'esatta combinazione selezionata.' d='Modules.Mpcolorproducts.Shop'}
    </div>
{/if}
