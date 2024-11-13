{***************************************** CMS PAGE OPTIONS ****************************************}

<h3>{l s='Master CMS Page data Management' mod='mkd_product_attachments'}:</h3>
<dl class="row" id="access">
    <dt class="col-sm-2">
        <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 640 512"><path d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM609.3 512H471.4c5.4-9.4 8.6-20.3 8.6-32v-8c0-60.7-27.1-115.2-69.8-151.8c2.4-.1 4.7-.2 7.1-.2h61.4C567.8 320 640 392.2 640 481.3c0 17-13.8 30.7-30.7 30.7zM432 256c-31 0-59-12.6-79.3-32.9C372.4 196.5 384 163.6 384 128c0-26.8-6.6-52.1-18.3-74.3C384.3 40.1 407.2 32 432 32c61.9 0 112 50.1 112 112s-50.1 112-112 112z" fill="#6c868e" /></svg>
        <small>{l s='Access' mod='mkd_product_attachments'}:</small>
    </dt>
    <dd class="col-sm-10">
        <div class="flex">
            {if $settings.service.premium.CMS_PAGE_ACCESS_LOCK}
                <span data-toggle="pstooltip" data-original-title="{l s='The CMS Page Content is limited to Authorized Users' mod='mkd_product_attachments'}">
                    <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z" fill="#e08f95" /></svg>
                </span>
                <small>{l s='Login required' mod='mkd_product_attachments'}</small>
            {else}
                <span data-toggle="pstooltip" data-original-title="{l s='The CMS Page Content is publicly available' mod='mkd_product_attachments'}">
                    <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 576 512"><path d="M352 144c0-44.2 35.8-80 80-80s80 35.8 80 80v48c0 17.7 14.3 32 32 32s32-14.3 32-32V144C576 64.5 511.5 0 432 0S288 64.5 288 144v48H64c-35.3 0-64 28.7-64 64V448c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V256c0-35.3-28.7-64-64-64H352V144z" fill="#72c279" /></svg>
                </span>
                <small>{l s='Public' mod='mkd_product_attachments'}</small>
            {/if}
        </div>
    </dd>
</dl>
