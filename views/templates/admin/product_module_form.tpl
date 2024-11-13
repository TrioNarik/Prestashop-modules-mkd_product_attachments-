{**
 * Dane z ProductExtraAttachController && ExportProductDataController && ExtraFieldsProductController
 ****************************************************************************************************
 *
 * CSS + BLOKI:
 * - cms page
 * - extra fields
 * - export file
 * - attachments
 *
*}

<style type="text/css">
    @import url('{$modulePath}/views/css/admin_product_extra_form.css');
</style>


{************ CMS PAGE PRODUCT POSITION in CATEGORY ***********}
{block name='cms_page'}
    {include file='./_partials/cms_page_product_position.tpl'}
{/block}

{************ CMS PAGE OPTIONS ***********}
{block name='cms_page'}
    {include file='./_partials/cms_page_options.tpl'}
{/block}

{********** EXTRA FIELDS OPTIONS *********}

    {block name='extra_fields'}
        {include file='./_partials/extra_field_options.tpl'}
    {/block}


{********** EXPORT FILES OPTIONS *********}
{if $settings.export.product.EXPORT_SWITCH}
    {block name='export_file'}
        {include file='./_partials/export_file_options.tpl'}
    {/block}

{/if}

{********** PRODUCT ATTACHMENTS *********}
{block name='attachments'}
    {include file='./_partials/product_attachments.tpl'}
{/block}
