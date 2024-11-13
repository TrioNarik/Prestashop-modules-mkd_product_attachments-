{**
 * Grupy użytkowników PS => $allUserGroups => $turnOffForGroups => wyłącznik poszczególnych Grup użytkowników
 * $firstActiveTabGroupClass => pierwsza aktywna Grupa w Tabs
 *
 * $groupedAttachment.group_info.hook
 *
*}


{*************** USTAWIENIA ***************}

{assign "turnOffForGroups" [0, 1]}

{*******************************************}

{assign var="firstActiveTabGroupClass" value=null}


{strip}
{if $attachments}
<div class="product-attachments">
    <h2>Do pobrania:</h2>
    <ul class="nav nav-tabs" role="tablist">

        {foreach from=$allUserGroups item=groupName key=groupKey}

            {*** DLA WYBRANYCH GRUP UŻYTKOWNIKÓW ***}
            {if $firstActiveTabGroupClass === null && !in_array($groupKey, $turnOffForGroups)}
                {$firstActiveTabGroupClass = $groupKey}
            {/if}

            {if !in_array($groupKey, $turnOffForGroups)}
                
                <li role="presentation">
                    <a href="#tab-{$hookName}-{$groupKey}" class="{if $groupKey == $firstActiveTabGroupClass}active{/if}" aria-controls="tab-{$groupKey}" role="tab" data-toggle="tab">{$groupName}</a>
                </li>
                
            {/if}

        {/foreach}

    </ul>

    <div class="tab-content">
        {foreach from=$allUserGroups item=groupName key=groupKey}

            {*** DLA WYBRANyYCH GRUP UŻYTKOWNIKÓW ***}
            {if $firstActiveTabGroupClass === null && !in_array($groupKey, $turnOffForGroups)}
                {$firstActiveTabGroupClass = $groupKey}
            {/if}

            {if !in_array($groupKey, $turnOffForGroups)}
                <div role="tabpanel" class="tab-pane {if $groupKey == $firstActiveTabGroupClass}active{/if}" id="tab-{$hookName}-{$groupKey}">
                    
                    <div id="attachment">
                        {foreach from=$attachments item=groupedAttachment}

                            {if in_array($groupName, $groupedAttachment.group_info.user_groups)}
                            <div class="group-info">
                                <h3>{$groupedAttachment.group_info.group_name}</h3>
                                
                                {if $groupedAttachment.group_info.group_description}
                                    <div>{$groupedAttachment.group_info.group_description|htmlspecialchars_decode nofilter}</div>
                                {/if}

                                {if $groupedAttachment.group_info.group_program && $groupedAttachment.group_info.program_active > 0}
                                    <div class="program-group">
                                        <h4>{$groupedAttachment.group_info.program_name}
                                            {if $groupedAttachment.group_info.program_version}
                                                <span class="version">{$groupedAttachment.group_info.program_version}</span>
                                            {/if}
                                            {if $groupedAttachment.group_info.program_expiration}
                                                <span class="date">[{$groupedAttachment.group_info.program_expiration}]</span>
                                            {/if}
                                        </h4>
                                        <p>{$groupedAttachment.group_info.program_comment}</p>
                                        
                                    </div>
                                {/if}
                            </div>
                                
                                
                                <ul class="attachment">
                                    {foreach from=$groupedAttachment.attachments item=attachment}
                                        <li>
                                            <h3>
                                                <a href="?fc=module&module=mkd_product_attachments&controller=Downloader&get={$attachment.id}" target="_blank">
                                                    {if $attachment.file_name}
                                                        {$attachment.file_name}
                                                    {else}
                                                        {if $attachment.file_url|strstr:'http'}
                                                            {$attachment.file_url|regex_replace:'/^https?:\/\/(www\.)?/':''}
                                                        {else}
                                                            {$attachment.file_url|regex_replace:'/^\d+-\d+-\d+_\d+_/':''}
                                                        {/if}
                                                    {/if}
                                                </a>
                                                {if $attachment.comment}
                                                    <small> | {$attachment.comment}</small>
                                                {/if}
                                            </h3>
                                        </li>
                                    {/foreach}
                                </ul>
                            {/if}

                        {/foreach}
                    </div>
                </div>
            {/if}
        {/foreach}
    </div>
</div>
{/if}
{/strip}



<style>
.product-attachments {
    margin: 1em 0;
    font-size: 1rem;
}
.nav-tabs {
    border-bottom: none;
}
.nav {
    display: flex;
    flex-wrap: wrap;
    padding-left: 0;
    margin-bottom: 0;
    list-style: none;
}
.nav li {
    position: relative;
    margin: 0 .25em;
}
.nav li:first-child {
    margin-left: 0;
}
.nav li:last-child {
    margin-right: 0;
}
.product-attachments *  {
    -webkit-box-sizing: content-box !important;
    -moz-box-sizing: content-box  !important;
    box-sizing: content-box  !important;
}
.nav li a {
    position: relative;
    bottom: -1px;
    z-index: 2;
    display: block;
    padding: 0.5em 1em;
    color: #05284e;
    background: #ededed;
    border-radius: 6px 6px 0 0;
    text-decoration: none;
    border: 1px solid transparent;
}
.nav li a.active {

    background: #fff;
    border: 1px solid #05284e;
    border-bottom: 1px solid #fff;
}

.tab-content {
    padding: 1em;
    border: 1px solid #05284e;
}
.group-info {
    margin: 2rem 0;
}
/* === Program === */
.program-group {
    margin: 2rem 0;
    padding: 2rem;
    border: 1px solid #F7F7F7;
    border-radius: 5px;
}
.program-group h4 {
    position: relative;
    padding: 0;
    padding-top: .5rem;
    margin: 0;
    display: flex;
    align-items: center;
    gap: .5em;
    color: #FF3D17;
}
.program-group h4::before {
    content: "";
    position: absolute;
    width: 3rem;
    height: 4px;
    background-color: #012d5a;
    top: 0;
    left: 0;
}
.program-group h4 span {
    display: inline-block;
    padding: .5em;
    font-size: 0.75rem;
    font-weight: 400;
}
.program-group h4 span.version {
    color: #333;
    background: #F7F7F7;
    border-radius: 10px;
}
.program-group h4 span.date {
    margin: 0 2rem;
    color: #FF3D17;
}
.program-group p {
    margin: 1rem 0;
}
/* === Linki === */
ul.attachment small {
    color: #A7A7A7;
}
</style>