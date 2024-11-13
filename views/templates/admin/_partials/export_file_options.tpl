{***************************************** EXPORT FILES OPTIONS ****************************************}

<h3>{l s='Files export product Management' mod='mkd_product_attachments'}:</h3>
<dl class="row" id="export">
    <dt class="col-sm-2">
        <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 576 512"><path d="M264.5 5.2c14.9-6.9 32.1-6.9 47 0l218.6 101c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8l-218.6 101c-14.9 6.9-32.1 6.9-47 0L45.9 149.8C37.4 145.8 32 137.3 32 128s5.4-17.9 13.9-21.8L264.5 5.2zM476.9 209.6l53.2 24.6c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8l-218.6 101c-14.9 6.9-32.1 6.9-47 0L45.9 277.8C37.4 273.8 32 265.3 32 256s5.4-17.9 13.9-21.8l53.2-24.6 152 70.2c23.4 10.8 50.4 10.8 73.8 0l152-70.2zm-152 198.2l152-70.2 53.2 24.6c8.5 3.9 13.9 12.4 13.9 21.8s-5.4 17.9-13.9 21.8l-218.6 101c-14.9 6.9-32.1 6.9-47 0L45.9 405.8C37.4 401.8 32 393.3 32 384s5.4-17.9 13.9-21.8l53.2-24.6 152 70.2c23.4 10.8 50.4 10.8 73.8 0z" fill="#6c868e" /></svg>
        <small>{l s='Data range' mod='mkd_product_attachments'}:</small>
    </dt>
    <dd class="col-sm-10 flex">
        {if isset($settings.export.product.EXPORT_COLUMNS)}
            {assign var="columns" value=$settings.export.product.EXPORT_COLUMNS|unserialize}
            {if is_array($columns)}
                <ul class="list-flex">
                    {foreach $columns as $columnName}
                        <li>{$columnName|strtoupper}</li>
                    {/foreach}
                </ul>
            {/if}
        {/if}

        <!-- ----------- EXTRA FIELDS EXPORT ------- -->
        {if $activeFields}
            <div class="translations tabbable list-flex my-1">
            {foreach $activeFields as $active}
                <div class="translationsFields tab-content">

                {foreach from=$languages item=language}

                {if $language.active && $fieldTitle[$active['id']][$language.id_lang]}
                    <div class="exta-field translationsFields-product_{$productId}_extra_field_{$active['id']}_lang_{$language.id_lang} tab-pane translation-field translation-label-{$language.iso_code} {if $id_lang == $language.id_lang}show active{/if}">
                            
                        {if isset($productValue[$active['id']][$language.id_lang]['value']) && !empty(trim($productValue[$active['id']][$language.id_lang]['value'])) && $productValue[$active['id']][$language.id_lang]['active'] == 1}
                            <span 
                                data-toggle="pstooltip"
                                data-placement="top"
                                data-original-title="{l s='This Extra Field contains Content and will be exported in language' mod='mkd_product_attachments'} {$language.name}">
                                {$fieldTitle[$active['id']][$language.id_lang]|escape:'html':'UTF-8'}
                            </span>
                        {else}
                            <span><i class="material-icons action-disabled">not_interested</i></span>
                            <span 
                                class="action-disabled"
                                data-toggle="pstooltip"
                                data-placement="top"
                                data-original-title="{l s='This Extra Field does not contain any active Content and will not be exported in language' mod='mkd_product_attachments'} {$language.name}">
                                {$fieldTitle[$active['id']][$language.id_lang]|escape:'html':'UTF-8'}
                            </span>
                        {/if}
                            
                    </div>
                {/if}

                {/foreach}

                </div>
            {/foreach}
            </div>
        {/if}
        <!-- --------- -->

    </dd>

    <dt class="col-sm-2">
        <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M0 64C0 28.7 28.7 0 64 0H224V128c0 17.7 14.3 32 32 32H384V304H176c-35.3 0-64 28.7-64 64V512H64c-35.3 0-64-28.7-64-64V64zm384 64H256V0L384 128zM200 352h16c22.1 0 40 17.9 40 40v8c0 8.8-7.2 16-16 16s-16-7.2-16-16v-8c0-4.4-3.6-8-8-8H200c-4.4 0-8 3.6-8 8v80c0 4.4 3.6 8 8 8h16c4.4 0 8-3.6 8-8v-8c0-8.8 7.2-16 16-16s16 7.2 16 16v8c0 22.1-17.9 40-40 40H200c-22.1 0-40-17.9-40-40V392c0-22.1 17.9-40 40-40zm133.1 0H368c8.8 0 16 7.2 16 16s-7.2 16-16 16H333.1c-7.2 0-13.1 5.9-13.1 13.1c0 5.2 3 9.9 7.8 12l37.4 16.6c16.3 7.2 26.8 23.4 26.8 41.2c0 24.9-20.2 45.1-45.1 45.1H304c-8.8 0-16-7.2-16-16s7.2-16 16-16h42.9c7.2 0 13.1-5.9 13.1-13.1c0-5.2-3-9.9-7.8-12l-37.4-16.6c-16.3-7.2-26.8-23.4-26.8-41.2c0-24.9 20.2-45.1 45.1-45.1zm98.9 0c8.8 0 16 7.2 16 16v31.6c0 23 5.5 45.6 16 66c10.5-20.3 16-42.9 16-66V368c0-8.8 7.2-16 16-16s16 7.2 16 16v31.6c0 34.7-10.3 68.7-29.6 97.6l-5.1 7.7c-3 4.5-8 7.1-13.3 7.1s-10.3-2.7-13.3-7.1l-5.1-7.7c-19.3-28.9-29.6-62.9-29.6-97.6V368c0-8.8 7.2-16 16-16z" fill="#6c868e" /></svg>
        <small>{l s='Format' mod='mkd_product_attachments'}:</small>
    </dt>
    <dd class="col-sm-10"><small>{$settings.export.product.EXPORT_FORMAT|strtoupper}</small></dd>

    <dt class="col-sm-2">
        <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 576 512"><path d="M384 480h48c11.4 0 21.9-6 27.6-15.9l112-192c5.8-9.9 5.8-22.1 .1-32.1S555.5 224 544 224H144c-11.4 0-21.9 6-27.6 15.9L48 357.1V96c0-8.8 7.2-16 16-16H181.5c4.2 0 8.3 1.7 11.3 4.7l26.5 26.5c21 21 49.5 32.8 79.2 32.8H416c8.8 0 16 7.2 16 16v32h48V160c0-35.3-28.7-64-64-64H298.5c-17 0-33.3-6.7-45.3-18.7L226.7 50.7c-12-12-28.3-18.7-45.3-18.7H64C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H87.7 384z" fill="#6c868e" /></svg>
        <small>{l s='Folder' mod='mkd_product_attachments'}:</small>
    </dt>
    <dd class="col-sm-10">{$downloadPath}/</dd>

    <dt class="col-sm-2">
        <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 384 512"><path d="M64 464c-8.8 0-16-7.2-16-16V64c0-8.8 7.2-16 16-16H224v80c0 17.7 14.3 32 32 32h80V448c0 8.8-7.2 16-16 16H64zM64 0C28.7 0 0 28.7 0 64V448c0 35.3 28.7 64 64 64H320c35.3 0 64-28.7 64-64V154.5c0-17-6.7-33.3-18.7-45.3L274.7 18.7C262.7 6.7 246.5 0 229.5 0H64zm97 289c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0L79 303c-9.4 9.4-9.4 24.6 0 33.9l48 48c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-31-31 31-31zM257 255c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l31 31-31 31c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l48-48c9.4-9.4 9.4-24.6 0-33.9l-48-48z" fill="#6c868e" /></svg>
        <small>{l s='File' mod='mkd_product_attachments'}:</small>
    </dt>
    <dd class="col-sm-10 flex">
        {if $fileName}
            
                <a target="_blank" href="{$downloadPath}/{$fileName}">{$fileName}</a> <small>[{$fileSize} KB {if $exportFileCounters} | {l s='Downloads' mod='mkd_product_attachments'}: <strong>{$exportFileCounters}</strong>{/if}]</small>
                
                <a href="#" class="tooltip-link deleteExportFile" data-toggle="pstooltip" title="" data-placement="top" data-original-title="{l s='Delete all files' mod='mkd_product_attachments'} *.{$settings.export.product.EXPORT_FORMAT}" data-shop-id="{$shopId}" data-product-id="{$productId}" data-confirm-message="{l s='Are you sure you want to delete %format% files for this product?' sprintf=['%format%' => strtoupper($settings.export.product.EXPORT_FORMAT)] mod='mkd_product_attachments'}"><i class="material-icons">delete</i></a>
            
        {else}
            <button class="btn btn-secondary generateExportFile" data-shop-id="{$shopId}" data-product-id="{$productId}" data-toggle="pstooltip" data-original-title="{l s='Create new %format% file' sprintf=['%format%' => strtoupper($settings.export.product.EXPORT_FORMAT)] mod='mkd_product_attachments'}">
                <i class="material-icons">add_circle</i>
                {l s='Generate file' mod='mkd_product_attachments'}
            </button>
        {/if}
    </dd>

    {if $fileTime}
        <dt class="col-sm-2 flex"></dt>
       
        {if $fileTime < max($settings.export.product.EXPORT_TIMEMARKER, $attachTime, $extraFieldTime, $productTime)}
            <dd class="col-sm-10 flex">
                <span class="alert alert-warning" role="alert">
                    <code>{l s='Created' mod='mkd_product_attachments'}:
                        {$fileTime|date_format:"%d.%m.%Y"}
                        <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M464 256A208 208 0 1 1 48 256a208 208 0 1 1 416 0zM0 256a256 256 0 1 0 512 0A256 256 0 1 0 0 256zM232 120V256c0 8 4 15.5 10.7 20l96 64c11 7.4 25.9 4.4 33.3-6.7s4.4-25.9-6.7-33.3L280 243.2V120c0-13.3-10.7-24-24-24s-24 10.7-24 24z" fill="#fab000" /></svg>
                        {$fileTime|date_format:"%H:%M:%S"}
                    </code>                    
                </span>
                <p><small class="action-disabled">
                    <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><path d="M224 0c-17.7 0-32 14.3-32 32V51.2C119 66 64 130.6 64 208v18.8c0 47-17.3 92.4-48.5 127.6l-7.4 8.3c-8.4 9.4-10.4 22.9-5.3 34.4S19.4 416 32 416H416c12.6 0 24-7.4 29.2-18.9s3.1-25-5.3-34.4l-7.4-8.3C401.3 319.2 384 273.9 384 226.8V208c0-77.4-55-142-128-156.8V32c0-17.7-14.3-32-32-32zm45.3 493.3c12-12 18.7-28.3 18.7-45.3H224 160c0 17 6.7 33.3 18.7 45.3s28.3 18.7 45.3 18.7s33.3-6.7 45.3-18.7z" fill="#e08f95" /></svg>
                    {l s='Requires updating' mod='mkd_product_attachments'}
                    {if $fileTime < $settings.export.product.EXPORT_TIMEMARKER}
                        <span class="help-box" data-toggle="popover" data-content="{l s='The Module Settings has been updated: %date% at %time%' sprintf=['%date%' => date('d.m.Y', $settings.export.product.EXPORT_TIMEMARKER), '%time%' => date('H:i:s', $settings.export.product.EXPORT_TIMEMARKER)] mod='mkd_product_attachments'}" title="{l s='The file does not include changes in Module Settings' mod='mkd_product_attachments'}"></span>
                    {elseif $fileTime < $attachTime}
                        <span class="help-box" data-toggle="popover" data-content="{l s='The attachments has been updated: %date% at %time%' sprintf=['%date%' => date('d.m.Y', $attachTime), '%time%' => date('H:i:s', $attachTime)] mod='mkd_product_attachments'}" title="{l s='The file does not include changes in the Attachments' mod='mkd_product_attachments'}"></span>
                    {elseif $fileTime < $extraFieldTime}
                        <span class="help-box" data-toggle="popover" data-content="{l s='The Value has been updated: %date% at %time%' sprintf=['%date%' => date('d.m.Y', $extraFieldTime), '%time%' => date('H:i:s', $extraFieldTime)] mod='mkd_product_attachments'}" title="{l s='The file does not include changes in the Extra Fields' mod='mkd_product_attachments'}"></span>
                    {elseif $fileTime < $productTime}
                        <span class="help-box" data-toggle="popover" data-content="{l s='The product has been updated: %date% at %time%' sprintf=['%date%' => date('d.m.Y', $productTime), '%time%' => date('H:i:s', $productTime)] mod='mkd_product_attachments'}" title="{l s='The file does not include changes in the Product' mod='mkd_product_attachments'}"></span>
                    {/if}

                </small></p>
            </dd>
            <dt class="col-sm-2"></dt>
            <dd class="col-sm-10"><button class="btn btn-success generateExportFile" data-shop-id="{$shopId}" data-product-id="{$productId}"><i class="material-icons">refresh</i> {l s='Update now' mod='mkd_product_attachments'}</button></dd> 
        {else}
            <dd class="col-sm-10 flex">
            </dd>
            <dt class="col-sm-2"></dt>
            <dd class="col-sm-10 flex">
                <button class="btn btn-outline-primary generateExportFile" data-shop-id="{$shopId}" data-product-id="{$productId}"  data-toggle="pstooltip" title="" data-placement="top" data-original-title="{l s='Create the file again' mod='mkd_product_attachments'}">{l s='Generate again' mod='mkd_product_attachments'}</button>
            </dd>
        {/if}
    {/if}

</dl>
