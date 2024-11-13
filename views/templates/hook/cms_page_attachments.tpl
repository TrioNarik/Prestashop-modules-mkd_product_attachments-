{**
 * Blok z komunikatami => notifications
 *
*}

{******************** LINKI ************************}
{assign var='link' value=Context::getContext()->link}


{**************** PAGINATION ***********************}
{assign var='totalPages' value=ceil(count($products) / $productPerPage)}
{assign var='currentPage' value=Tools::getValue('page', 1)}
{assign var='startIndex' value=($currentPage - 1) * $productPerPage + 1}
{assign var='endIndex' value=$currentPage * $productPerPage}


{********** NOTIFICATIONS ************************}
{block name='customer_notifications'}
    {include file='_partials/notifications.tpl'}
{/block}

{strip}
<section id="cmspage_products">

    <h4>{l s='Select the Category of Attachment' mod='mkd_product_attachments'}</h4>
    {if $activeGroupsAttachment}
        <ul>
            <li class="root{if $currentGroup_ID == 0} current{/if}">
                <a href="?group=0&category={$currentCategory_ID}">{l s='All categories' mod='mkd_product_attachments'}</a>
            </li>
            <li>&#8759;</li>
            {foreach $activeGroupsAttachment as $group}
                <li {if $group.group_id == $currentGroup_ID}class="current"{/if}>
                    <a href="?group={$group.group_id}&category={$currentCategory_ID}">{$group.group_name}</a>
                </li>
            {/foreach}
        </ul>
    {else}
        <p>{l s='No active attachment groups found' mod='mkd_product_attachments'}</p>
    {/if}

    <h4>{l s='Select the Category of Product' mod='mkd_product_attachments'}</h4>


    {************************
    {if $categories}
        <ul>
            {foreach $categories item=category}
                {if $category.id_category != 1} <!-- Exclude Base Category -->
                    {if isset($category) && $category.id_category == $rootCategory_ID}
                        <li class="root{if $currentCategory_ID == 0} current{/if}">
                            <a href="?group={$currentGroup_ID}&category=0">{l s='All categories' mod='mkd_product_attachments'}</a>
                        </li>
                        <li>&#8759;</li>
                    {else}
                        <li {if $category.id_category == $currentCategory_ID}class="current"{/if}>
                            <a href="?group={$currentGroup_ID}&category={$category.id_category}-{$category.link_rewrite|urlencode}">{$category.name}</a>
                        </li>
                    {/if}
                {/if}
            {/foreach}
        </ul>
    {/if}
    **********************}

    {if $allCategories}
    <ul>
        {foreach $allCategories as $category}
            {if $category.id_category != 1}
                {if $category.id_category == 3}
                    <li class="root{if $currentCategory_ID == 3} current{/if}">
                        <a href="?group={$currentGroup_ID}&category=3">{$category.name}</a>
                    </li>
                    <li>&#8759;</li>
                {else}
                    <li {if $category.id_category == $currentCategory_ID}class="current"{/if}>
                        <a href="?group={$currentGroup_ID}&category={$category.id_category}-{$category.link_rewrite|urlencode}">{$category.name}</a>
                    </li>
                {/if}
            {/if}
        {/foreach}
    </ul>
{/if}



    <div class="row cmsPageProducts">

    {foreach from=$products item=product key=num }

        {*** if $num >= $startIndex - 1 && $num <= $endIndex - 1 ***}
        {assign var='productID' value=$product.id_product}

            <div class="col-xs-12 {if $productViewMode == 'grid'}grid col-sm-6 col-md-4{elseif $productViewMode == 'catalog'}catalog{/if}">
                <article data-id-product="{$product.id_product}" data-id-brand="{$product.id_manufacturer}">
                    <div class="product-container">
                    
                        <div class="thumbnail">

                            {if isset($coverImageID_{$product.id_product})}
                                <img src="{$link->getImageLink($product.link_rewrite, $coverImageID_{$product.id_product}, 'cart_default')}" alt="{$product.name|escape:'html':'UTF-8'}">
                            {else}
                                <p>{l s='No image available' mod='mkd_product_attachments'}</p>
                            {/if}

                        </div>

                        <div class="product">
                            <div class="title">
                                <h5><a href="{$link->getProductLink($product)}">{$product.name|strip_tags|truncate:35:"..."}</a></h5>
                                
                                {******* Pobierz wszystkie załączniki ********}
                                {if $activeAttachments[$productID]}
                                    <div class="download_zip">
                                        <a href="?fc=module&module=mkd_product_attachments&controller=ZipMaker&product={$productID}&limit=0&group=0" class="zipper" title="{l s='Download all attachmenst as ZIP File' mod='mkd_product_attachments'}">
                                            <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="square" stroke-linejoin="arcs"><path d="M3 15v4c0 1.1.9 2 2 2h14a2 2 0 0 0 2-2v-4M17 9l-5 5-5-5M12 12.8V2.5"></path></svg>
                                        </a>
                                    </div>
                                {/if}
                                {*********************************************}
                            </div>

                            {if isset($activeAttachments[$productID]) && $activeAttachments[$productID]}
                                {foreach from=$activeAttachments[$productID] item=attachment}
                                    <dl class="row">
                                        <dt class="col-sm-7">
                                            <ol>
                                            <li>
                                                {if $attachment.file_name}
                                                    {$attachment.file_name}
                                                {else}
                                                    {if $attachment.file_url|strstr:'http'}
                                                        {$attachment.file_url|regex_replace:'/^https?:\/\/(www\.)?/':''}
                                                    {else}
                                                        {$attachment.file_url|regex_replace:'/^\d+-\d+-\d+_\d+_/':''}
                                                    {/if}
                                                {/if}
                                            </li>
                                            {if $attachment.comment}
                                                <li><small class="text-muted comment">{$attachment.comment|strip_tags|truncate:35:"..."}</small></li>
                                            {/if}

                                            <li><small class="text-muted">{l s='Added' mod='mkd_product_attachments'}: {$attachment.data_upd|date_format:'%d-%m-%Y'}</small></li>

                                            {if $attachDownloadCounter}
                                                <li><small class="text-muted">{l s='Downloads' mod='mkd_product_attachments'}: {$attachment.download_count}</small></li>
                                            {/if}
                                            </ol>
                                        </dt>

                                        <dd class="col-sm-5 text-sm-right">
                                            <a href="?fc=module&module=mkd_product_attachments&controller=Downloader&get={$attachment.id}">{l s='Download' mod='mkd_product_attachments'}</a>
                                        </dd>

                                    </dl>
                                {/foreach}
                            {/if}

                            {foreach from=$exportFile[$productID] item=export}
                                {if $export}
                                <dl class="row export">
                                    <dt class="col-sm-7">
                                        <ol>
                                            <li>{l s='Export product' mod='mkd_product_attachments'}</li>
                                            <li><small class="text-muted">{l s='Format' mod='mkd_product_attachments'}: <strong>{$exportFormat}</strong></small></li>
                                            {if $exportFileCounterSwitch && $export}
                                                <li><small class="text-muted">{l s='Downloads' mod='mkd_product_attachments'}: {$exportFileCounter[$productID]}</small></li>
                                            {/if}

                                        </ol>
                                    </dt>
                                    <dd class="col-sm-5 export text-sm-right">
                                        <a href="?fc=module&module=mkd_product_attachments&controller=Exporter&get={$product.id_product}" target="_blank">{l s='Download' mod='mkd_product_attachments'}</a>
                                    </dd>
                                </dl>
                                {/if}
                            {/foreach}

                        </div>
                    </div>
                </article>
            </div>

        {*** /if ***}
    {/foreach}
    </div>

    {***
    <!-- --------------- PAGINATION ------------------ -->
    <div class="product_pagination">
        {if $endIndex > count($products)}
            {assign var='endIndex' value=count($products)}
        {/if}
        <p class="text-sm-center text-md-right">
            <small class="text-muted">{l s='Showing' mod='mkd_product_attachments'} {$startIndex} - {$endIndex} {l s='of'  mod='mkd_product_attachments'} {count($products)} {l s='products'  mod='mkd_product_attachments'}</small>
        </p>
        <ul class="my-1">
            {for $i=1 to $totalPages}
                <li {if $i == $currentPage}class="current"{/if}>
                    <a href="?group={$currentGroup_ID}&category={$currentCategory_ID}&page={$i}">{$i}</a>
                </li>
            {/for}
        </ul>

    </div>
    ***}

</section>

{/strip}
