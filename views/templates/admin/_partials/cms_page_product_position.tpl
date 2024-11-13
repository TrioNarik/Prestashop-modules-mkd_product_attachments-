{******************************** CMS PAGE => PRODUCT POSITION OPTIONS *******************************}

<h3>{l s='Product position on CMS Page in category' mod='mkd_product_attachments'}</h3>
<dl class="row" id="position">

    <dt class="col-sm-2">
        <p>{$shop_name}</p>
    </dt>

    <dd class="col-sm-10">
        <p>{l s='Total active products in the category' mod='mkd_product_attachments'}: <strong>{$total_product}</strong></p>    
    </dd>

    <dt class="col-sm-2">
        {if $product_active}
            <small class="action-enabled">{l s='Product enabled' mod='mkd_product_attachments'}</small>
        {else}
            <small class="action-disabled">{l s='Product disabled' mod='mkd_product_attachments'}</small>
        {/if}
    </dt>
    <dd class="col-sm-10">
        <label class="switcher" data-toggle="pstooltip"  data-original-title="{l s='Active or Inactive product on CMS Page'  mod='mkd_product_attachments'}">
            <input type="checkbox" id="product_active" name="product_active" {if $product_active}checked{/if}>
            <span class="mkd_slider round"></span>
        </label>
    </dd>

    <dt class="col-sm-2">
        <small>{l s='Current position' mod='mkd_product_attachments'}:
        {if $current_position > 0 }
            <span class="action-enabled">{$current_position}</span>
        {else}
            <span class="action-disabled">---</span>
        {/if}
        </small>
    </dt>
    <dd class="col-sm-10">
        <select name="position" id="product_position">
            <option value="0">[--- {l s='Select position' mod='mkd_product_attachments'} ---]</option>
            <optgroup label="{$category_name}">
                {for $i=1 to $total_product}
                    <option value="{$i}" {if $i == $current_position}selected{/if}>
                        {$i} - 
                        {* Szukamy produktu przypisanego do tej pozycji *}
                        {assign var="foundProduct" value=false}
                        {foreach from=$products item=product}
                            {if $product.position == $i}
                                {$product.name}
                                {assign var="foundProduct" value=true}
                            {/if}
                        {/foreach}
                        {* Jeśli nie znaleziono produktu, wyświetl "Available" *}
                        {if !$foundProduct}
                            ----- {l s='empty' mod='mkd_product_attachments'} -----
                        {/if}
                    </option>
                {/for}
            </optgroup>
        </select>

    </dd>
    <dt class="col-sm-2">
        <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 100 30">
            <rect x="0" y="5" width="20" height="20" fill="#6c868e" />
            <rect x="30" y="5" width="20" height="20" fill="none" stroke="#6c868e" stroke-width="2" />
            <rect x="60" y="5" width="20" height="20" fill="#6c868e" />
        </svg>
    </dt>
    <dd class="col-sm-10">
        <button name="submitPosition"
            class="btn btn-primary submitPosition"
            data-shop-id="{$shop_id}"
            data-lang-id="{$lang_id}"
            data-product-id="{$product_id}"
            data-category-id="{$category_id}"
            data-error-message="{l s='Please select product position before saving'  mod='mkd_product_attachments'}"
            data-toggle="pstooltip"
            data-original-title="{l s='Update settings for product' mod='mkd_product_attachments'}"
            >{l s='Save' mod='mkd_product_attachments'}
        </button>
    </dd>
</dl>