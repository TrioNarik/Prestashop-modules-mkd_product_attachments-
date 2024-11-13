{***************************************** PRODUCT ATTACHMENTS ****************************************}

<h3>{l s='Product Attachments Management' mod='mkd_product_attachments'}:</h3>
{if empty($availableGroups)}
    <p class="alert alert-warning">{l s='No active Attachment Groups' mod='mkd_product_attachments'}</p>
    <a href="{$linkToAddNewGroup}" class="btn btn-default">{l s='Manage Groups settings' mod='mkd_product_attachments'}</a> 
    <a href="{$linkToAddNewGroup}&add_group_type" class="btn btn-primary">{l s='Add new Group' mod='mkd_product_attachments'}</a>
{else}
    <dl><i class="icon-file-text"></i> {l s='List of active product attachment groups' mod='mkd_product_attachments'}:</dl>
{/if}
{foreach $availableGroups as $groupId => $groupData}
    {assign var='groupHasFiles' value=false}
    <h4><span>ID {$groupId}:</span> {$groupData.title}</h4>
    <h5 class="action-enabled">&#9724;
        {if $groupData.url}
            {l s='External URL' mod='mkd_product_attachments'}
        {else}
            {$groupData.format}
        {/if}
    </h5>
    
    <div class="small">

        <p>{$groupData.description|strip_tags|truncate:250:" [...]"}</p>
        
        {if $groupData.users_name}
        <div class="flex">
            <ul class="list-flex">
                {foreach from=$groupData.users_name item=userGroupName}
                    <li>&#10004; {$userGroupName}</li>
                {/foreach}
            </ul>
        </div>
        {/if}
        
        
    </div>
    
    <table class="table grid-table js-grid-table table  ">
        <thead class="thead-default">
            <tr>
                <th class="text-center">LP</th>
                <th class="text-center">{l s='Language' mod='mkd_product_attachments'}</th>
                <th>{l s='Title' mod='mkd_product_attachments'}</th>
                <th>{l s='Comment' mod='mkd_product_attachments'}</th>
                <th class="data-type text-center">{l s='Added' mod='mkd_product_attachments'}</th>
                <th class="data-type text-center">{l s='Update' mod='mkd_product_attachments'}</th>
                {if $settings.service.premium.ATTACHMENT_DOWNLOAD_COUNTER}
                    <th class="data-type text-center">{l s='Downloads' mod='mkd_product_attachments'}</th>
                {/if}
                <th class="data-type text-center">{l s='Status' mod='mkd_product_attachments'}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            {assign var='fileNumber' value=1}
            {foreach $productAttachments as $attachment}
                {if $attachment.type_id == $groupId}
                    {assign var='groupHasFiles' value=true}
                    <tr>
                      <td class="data-type text-center">{$fileNumber}</td>
                      <td class="data-type text-center">{$langISO[$attachment.id_lang]}</td>
                      <td class="attachmentName">
                        {if $attachment.file_name}
                            {$attachment.file_name}
                        {else}
                            {$attachment.file_url|regex_replace:'/^\d+-\d+-\d+_\d+_/':''}
                        {/if}
                        <p><small>{$attachment.file_url}</small></p>
                      </td>
                      <td class="html-type column-description">{$attachment.comment|default:'---'}</td>
                      <td class="data-type text-center">{$attachment.data_add|date_format:'%Y-%m-%d'}</td>
                      <td class="data-type text-center">{if $attachment.data_add != $attachment.data_upd}{$attachment.data_upd|date_format:'%Y-%m-%d'}{else}---{/if}</td>
                      {if $settings.service.premium.ATTACHMENT_DOWNLOAD_COUNTER}
                        <td class="data-type text-center">{$attachment.download_count}</td>
                      {/if}
                      <td class="data-type text-center">
                        {if $attachment.active}
                            <i class="material-icons action-enabled">done</i>
                        {else}
                            <i class="material-icons action-disabled">clear</i></span>
                        {/if}
                      </td>

                      <td>
                        <div class="btn-group-action text-right">
                          <div class="btn-group">

                            {if $groupData.url == 1}
                                <a class="btn tooltip-link js-link-row-action dropdown-item" target="_blank" href="{$attachment.file_url}" data-confirm-message="" data-toggle="pstooltip" data-placement="top" data-original-title="{l s='View URL' mod='mkd_product_attachments'}" data-clickable-row="1"><i class="material-icons">home</i></a>
                            {else}
                                <a class="btn tooltip-link js-link-row-action dropdown-item" target="_blank" href="{$uploadPath}/{$groupData.value}/{$attachment.file_url}" data-confirm-message="" data-toggle="pstooltip" data-placement="top" data-original-title="{l s='View File' mod='mkd_product_attachments'}" data-clickable-row="1"><i class="material-icons">remove_red_eye</i></a>
                            {/if}

                            <a class="btn btn-link dropdown-toggle dropdown-toggle-dots dropdown-toggle-split no-rotate" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="btn tooltip-link js-link-row-action dropdown-item edit-attachment"
                                href="#" data-confirm-message="" data-clickable-row="1"
                                data-group-id="{$groupId}"
                                data-attachment-id="{$attachment.id}"
                                data-attachment-lang-id="{$attachment.id_lang}"
                                data-group-url="{$groupData.url}"
                                data-attachment-name="{$attachment.file_name}"
                                data-attachment-file="{$attachment.file_url}"
                                data-attachment-comment="{$attachment.comment}"
                                data-attachment-status="{$attachment.active}"
                                data-attachment-button="update">
                                <i class="material-icons">mode_edit</i>
                                    {l s='Edit' mod='mkd_product_attachments'}
                                </a>
                                <a class="btn tooltip-link js-delete-category-row-action dropdown-item deleteAttachment" href="#" 
                                data-confirm-message="{l s='Are you sure you want to delete this attachment?' mod='mkd_product_attachments'}"
                                data-shop-id="{$shopId}"
                                data-group-id="{$groupId}"
                                data-product-id"="{$productId}"
                                data-attachment-id="{$attachment.id}">
                                    <i class="material-icons">delete</i>
                                    {l s='Delete' mod='mkd_product_attachments'}
                                </a>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                    </tr>
                    {assign var='fileNumber' value=$fileNumber+1}
                {/if}
            {/foreach}
        </tbody>
    </table>
    {if !$groupHasFiles}
        <p class="alert alert-warning">{l s='No attachments in the group' mod='mkd_product_attachments'}</p>
    {/if}
    
    <!-- Przycisk "Dodaj załącznik" -->
    <a href="#" class="add-attachment btn btn-secondary" data-group-id="{$groupId}" data-group-url="{$groupData.url}">
    <i class="material-icons">add_circle</i>
        {if $groupData.url == 1}
            {l s='Add URL' mod='mkd_product_attachments'}
        {else}
            {l s='Add file' mod='mkd_product_attachments'}
        {/if}
    </a>
    <!------->

    <!-- Modal z formularzem -->
    <div class="modal fade" id="attachmentModal" tabindex="-1" role="dialog" aria-labelledby="attachmentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h4  class="modal-title">{l s='Group ID'  mod='mkd_product_attachments'}: <span id="modalGroupId">{$groupId}</span></h4>
                
                <button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close' mod='mkd_product_attachments'}">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                    <h4>{l s='Complete the form'  mod='mkd_product_attachments'}:</h4>
                    
                    <div class="form-group">
                        <label for="language" data-toggle="pstooltip" data-placement="top" data-original-title="{l s='Select Language' mod='mkd_product_attachments'}">{l s='Language' mod='mkd_product_attachments'}:</label>
                        <select class="form-control" id="attachmentLang" name="attachmentLang">
                            {foreach from=$languages item=language}
                                <option value="{$language.id_lang}">{$language.name}</option>
                            {/foreach}
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="attachmentName" data-toggle="pstooltip" data-placement="top" data-original-title="{l s='Optionally' mod='mkd_product_attachments'}">{l s='Title' mod='mkd_product_attachments'}:
                        </label>
                        <input type="text" maxlength="50" placeholder="{l s='Title...' mod='mkd_product_attachments'}" class="form-control" id="attachmentName" name="attachmentName">
                        <small>[{l s='max. 50 characters' mod='mkd_product_attachments'}]</small>
                    </div>
                    <div class="form-group">
                        <label for="attachmentComment" data-toggle="pstooltip" data-placement="top" data-original-title="{l s='Optionally' mod='mkd_product_attachments'}">{l s='Short description' mod='mkd_product_attachments'}:
                        </label>
                        <input type="text" maxlength="250" placeholder="{l s='Comment...' mod='mkd_product_attachments'}" class="form-control" id="attachmentComment" name="attachmentComment">
                        <small>[{l s='max. 250 characters' mod='mkd_product_attachments'}]</small>
                    </div>
                    <div class="form-group">
                        <sup id="required">* {l s='Required field' mod='mkd_product_attachments'}:
                            <span class="help-box" data-toggle="popover" data-content="{l s='The field is required to correctly save the data as an attachment' mod='mkd_product_attachments'}"></span>
                        </sup>
                        
                        <input id="inputFiedType" class="form-control">
                        <small>[{l s='max. 250 characters' mod='mkd_product_attachments'}]</small>

                        <div id="attachmentFileURL"></div>
                        
                    </div>
                    <div class="form-group">
                        <label for="active" data-toggle="pstooltip" data-placement="top" data-original-title="{l s='Active or Inactive status' mod='mkd_product_attachments'}">
                         <input type="checkbox" id="active" name="active" checked>
                         {l s='Available' mod='mkd_product_attachments'}                         
                         </label>    
                    </div>
                    <div class="form-group">
                        <input type="hidden" id="shopId" name="shopId" value="{$shopId}">
                        <input type="hidden" id="productId" name="productId" value="{$productId}">
                        <input type="hidden" id="groupId" name="groupId" value="{$groupId}">
                    </div>
                    <div class="form-group">
                        <button type="submit" name="submitAttachment" class="btn btn-primary submitAttachment">{l s='Save settings' mod='mkd_product_attachments'}</button>
                    </div>
                
            </div>
            </div>
        </div>
    </div>
  
    <hr />
{/foreach}

{if !empty($availableGroups)}
    <div class="module-name-grid">
    <hr />
    <h4>{l s='Managment Attachment Groups in the Module' mod='mkd_product_attachments'}:</h4>
    <a href="{$linkToAddNewGroup}&add_group_type" class="btn btn-primary">{l s='Add new Group' mod='mkd_product_attachments'}</a>
    <a href="{$linkToAddNewGroup}" class="btn btn-default">{l s='View all groups' mod='mkd_product_attachments'}</a>
    </div>
{/if}
