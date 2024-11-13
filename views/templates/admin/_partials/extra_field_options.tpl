{***************************************** EXTRA FIELDS OPTIONS ****************************************}

<h3>{l s='Product Extra Fields data Management' mod='mkd_product_attachments'}:</h3>
<dl class="row" id="extra_fields">
  <dt class="col-sm-2">
    <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M0 96C0 60.7 28.7 32 64 32H448c35.3 0 64 28.7 64 64V416c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V96zm64 64V416H224V160H64zm384 0H288V416H448V160z" fill="#6c868e" /></svg>
    <small>{l s='Extra fields' mod='mkd_product_attachments'}</small><br />  
  </dt>
  <dd class="col-sm-10">
    <fieldset class="form-group">
      <div class="translations tabbable row">
        {foreach $activeFields as $active}
          <div class="translationsFields tab-content col-md-10 py-1">

            {foreach from=$languages item=language}
              {if $language.active}
                <div class="translationsFields-product_{$productId}_extra_field_{$active['id']}_lang_{$language.id_lang} tab-pane translation-field translation-label-{$language.iso_code} {if $id_lang == $language.id_lang}show active{/if}">
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">{$language.iso_code|strtoupper}</span>
                    </div>

                    <!-- --- Input or Textarea => .autoload_rte --- -->
                    <div class="edytorHTML">
                      <input type="text"
                        maxlength="200"
                        data-lang-id={$language.id_lang}
                        id="extra_field_{$active['id']}_value_lang_{$language.id_lang}"
                        placeholder="..."
                        class="form-control {if isset($active['html_content']) &&  $active['html_content'] == 1}autoload_rte{/if}"
                        value="{if isset($productValue[$active['id']][$language.id_lang]['value'])}{$productValue[$active['id']][$language.id_lang]['value']|escape:'html':'UTF-8'}{/if}"
                        {if trim(empty($fieldTitle[$active['id']][$language.id_lang]))} disabled{/if}>

                        <!-- ---- max.size ---- -->
                        <small class="form-text text-muted text-right maxLength maxType">
                          {if isset($active['html_content']) &&  $active['html_content'] == 1}
                            <em>{l s='Used' mod='mkd_product_attachments'} <span class="currentLength"></span> {l s='of' mod='mkd_product_attachments'} <span class="currentTotalMax">3200</span> {l s='chars available' mod='mkd_product_attachments'}</em>
                          {else}
                            <em><span class="currentTotalMax">200</span> {l s='chars available' mod='mkd_product_attachments'}</em>
                          {/if}
                        </small>
                    </div>
                    
                    <!-- --- Extra Field Name/Title --------- -->
                    <div class="input-group-append">
                      
                      {if trim(empty($fieldTitle[$active['id']][$language.id_lang]))}
                        <span class="input-group-text"><i class="material-icons">warning</i></span>
                        <span class="input-group-text" data-toggle="pstooltip" data-original-title="{l s='The Name for the Extra Field is missing. Fix it in the Module settings' mod='mkd_product_attachments'}">
                              &nbsp;<i class="material-icons action-disabled">error</i>&nbsp;
                        {else}
                          <span class="input-group-text"><i class="material-icons">mode_edit</i></span>
                          <span class="input-group-text">{$fieldTitle[$active['id']][$language.id_lang]|escape:'html':'UTF-8'}
                        {/if}

                    </div>

                    <!-- --- Change Status Button --- -->
                    <div class="px-2">
                      <button
                        data-toggle="pstooltip"
                        data-placement="top"
                        data-product-field-id="{if isset($productValue[$active['id']][$language.id_lang]['id'])}{$productValue[$active['id']][$language.id_lang]['id']}{/if}"

                        {if isset($productValue[$active['id']][$language.id_lang]['active']) && $productValue[$active['id']][$language.id_lang]['active'] == 1}

                          class="btn btn-sm btn-outline-success statusField"
                          data-confirm-message="{l s='Are you sure to Disable this extra field for this product?' mod='mkd_product_attachments'}"
                          data-original-title="{l s='Active in language' mod='mkd_product_attachments'} {$language.name}">
                            <i class="material-icons">done</i>
                        {else}
                          class="btn btn-sm btn-outline-danger statusField"
                          data-confirm-message="{l s='Are you sure to Enable this additional field for this product?' mod='mkd_product_attachments'}"
                          data-original-title="{l s='Inactive in language' mod='mkd_product_attachments'} {$language.name}">
                            <i class="material-icons">not_interested</i>
                        {/if}
                      </button>
                    </div>
                    <!-- ------------ -->

                  </div>
                </div>
              {/if}
            {/foreach}

          </div>
          <!-- ----- Save/Update Buttons ---------- -->
          <div class="col-md-1 py-1">
            {if isset($productValue[$active['id']][$language.id_lang]['value'])}
              <button name="updateField"
                class="btn btn-secondary updateField"
                data-action="updateExtraFieldValue"
                data-shop-id="{$shopId}"
                data-product-id="{$productId}"
                data-field-id="{$active['id']}"
                data-error-message="{l s='All language versions of the Extra Field are empty. Please enter your details before saving'  mod='mkd_product_attachments'}"
                data-toggle="pstooltip"
                data-original-title="{l s='Update' mod='mkd_product_attachments'}"
                >
                  <i class="material-icons">refresh</i>
              </button>

            {else}

              <button name="submitField"
                class="btn btn-primary submitField"
                data-action="saveExtraFieldValue"
                data-shop-id="{$shopId}"
                data-product-id="{$productId}"
                data-field-id="{$active['id']}"
                data-error-message="{l s='All language versions of the Extra Field are empty. Please enter your details before saving'  mod='mkd_product_attachments'}"
                data-toggle="pstooltip"
                data-original-title="{l s='Save' mod='mkd_product_attachments'}"
                >
                  <i class="material-icons">save</i>
              </button>
            {/if}
          </div>
          <!-- -------- -->
          
        {/foreach}

      </div>                
    </fieldset>
  </dd>
</dl>