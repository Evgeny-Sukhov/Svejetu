<!-- category filter tpl -->

{assign var='langsCount' value=$allLangs|@count}

<div id="nav_bar">{strip}
    {if !$smarty.get.action}
        <a href="{$rlBaseC}action=add" class="button_bar">
            <span class="left"></span>
            <span class="center_add">{$lang.category_filter_add_filter_box}</span>
            <span class="right"></span>
        </a>
    {/if}

    {if $smarty.get.action != 'config'}
        <a href="{$rlBase}index.php?controller={$smarty.get.controller}" class="button_bar">
            <span class="left"></span>
            <span class="center_list">{$lang.category_filter_filter_boxes_list}</span>
            <span class="right"></span>
        </a>
    {/if}
{/strip}</div>

{if $smarty.get.action == 'add' || $smarty.get.action == 'edit'}
    {assign var='sPost' value=$smarty.post}

    {include file='blocks/m_block_start.tpl'}
    <form action="{$rlBaseC}action={if $smarty.get.action == 'add'}add{elseif $smarty.get.action == 'edit'}edit&item={$smarty.get.item}{/if}"
        method="post"
        enctype="multipart/form-data"
    >
        <input type="hidden" name="submit" value="1" />

        {if $smarty.get.action == 'edit'}
            <input type="hidden" name="fromPost" value="1" />
        {/if}

        <table class="form">
        <tr>
            <td class="name"><span class="red">*</span>{$lang.category_filter_box_name}</td>
            <td>
                {if $langsCount > 1}
                    <ul class="tabs">
                        {foreach from=$allLangs item='language' name='langF'}
                            <li lang="{$language.Code}" {if $smarty.foreach.langF.first}class="active"{/if}>
                                {$language.name}
                            </li>
                        {/foreach}
                    </ul>
                {/if}

                {foreach from=$allLangs item='language' name='langF'}
                    {if $langsCount > 1}
                        <div class="tab_area{if !$smarty.foreach.langF.first} hide{/if} {$language.Code}">
                    {/if}

                    <input type="text"
                        name="name[{$language.Code}]"
                        value="{$sPost.name[$language.Code]}"
                        maxlength="350" />

                    {if $langsCount > 1}
                            <span class="field_description_noicon">{$lang.name} (<b>{$language.name}</b>)</span>
                        </div>
                    {/if}
                {/foreach}
            </td>
        </tr>

        <tr>
            <td class="name"><span class="red">*</span>{$lang.category_filter_filter_for}</td>
            <td class="field">
                {if $smarty.get.action == 'edit'}
                    <input type="hidden" name="mode" value="{$sPost.mode}" />
                {/if}

                <select name="mode" {if $smarty.get.action == 'edit'}disabled class="disabled"{/if}>
                    <option value="">{$lang.select}</option>
                    <option {if $sPost.mode == 'type'}selected="selected"{/if} value="type">
                        {$lang.category_filter_filter_for_type}
                    </option>
                    <option {if $sPost.mode == 'category'}selected="selected"{/if} value="category">
                        {$lang.category_filter_filter_for_category}
                    </option>
                    <option {if $sPost.mode == 'search_results'}selected="selected"{/if} value="search_results">
                        {$lang.category_filter_filter_for_search_results}
                    </option>

                    {if $fieldBoundBoxes}
                        <option {if $sPost.mode == 'field_bound_boxes'}selected="selected"{/if}
                            value="field_bound_boxes">
                            {$lang.category_filter_filter_for_field_bound_boxes}
                        </option>
                    {/if}
                </select>

                <script>{literal}
                $(function() {
                    $('select[name=mode]').change(function() { cfMode(); });
                    cfMode();
                });

                var cfMode = function() {
                    var mode = $('select[name=mode]').val();

                    if (!mode) {
                        $('div.filter_mode').slideUp('fast');
                    } else if (mode == 'type') {
                        $('div#for_category, div#for_search_results, div#for_field_bound_boxes').slideUp('fast');
                        $('div#for_type').slideDown();
                    } else if (mode == 'category') {
                        $('div#for_type, div#for_search_results, div#for_field_bound_boxes').slideUp('fast');
                        $('div#for_category').slideDown();
                    } else if (mode == 'search_results') {
                        $('div#for_type, div#for_category, div#for_field_bound_boxes').slideUp('fast');
                        $('div#for_search_results').slideDown();
                    } else if (mode == 'field_bound_boxes') {
                        $('div#for_type, div#for_category, div#for_search_results').slideUp('fast');
                        $('div#for_field_bound_boxes').slideDown();
                    }
                }
                {/literal}</script>
            </td>
        </tr>
        </table>

        <div class="hide filter_mode" id="for_type">
            <table class="form">
            <tr>
                <td class="name"><span class="red">*</span>{$lang.listing_type}</td>
                <td class="field">
                    {if $smarty.get.action == 'edit'}
                        <input type="hidden" name="type" value="{$sPost.type}" />
                    {/if}

                    <select name="type" {if $smarty.get.action == 'edit'}disabled class="disabled"{/if}>
                    <option value="">- {$lang.all} -</option>
                    {foreach from=$listing_types item='l_type'}
                        <option value="{$l_type.Key}" {if $sPost.type == $l_type.Key}selected="selected"{/if}>
                            {$l_type.name}
                        </option>
                    {/foreach}
                    </select>
                </td>
            </tr>
            </table>
        </div>

        <div class="hide filter_mode" id="for_search_results">
            <table class="form">
            <tr>
                <td class="name"><span class="red">*</span>{$lang.listing_type}</td>
                <td class="field">
                    {if $smarty.get.action == 'edit'}
                        <input type="hidden" name="type_for_search" value="{$sPost.type}" />
                    {/if}

                    <select name="type_for_search" {if $smarty.get.action == 'edit'}disabled class="disabled"{/if}>
                    <option value="">- {$lang.all} -</option>
                    {foreach from=$listing_types item='l_type'}
                        <option value="{$l_type.Key}" {if $sPost.type == $l_type.Key}selected="selected"{/if}>
                            {$l_type.name}
                        </option>
                    {/foreach}
                    </select>
                </td>
            </tr>
            </table>
        </div>

        <div class="hide filter_mode" id="for_category">
            <table class="form">
            <tr>
                <td class="name"><span class="red">*</span>{$lang.category_filter_filter_mode_category}</td>
                <td class="field">
                    <fieldset class="light">
                        <legend id="legend_cats" class="up" onclick="fieldset_action('cats');">
                            {$lang.categories}
                        </legend>

                        <div id="cats">
                            <div id="cat_checkboxed" style="margin: 0 0 8px;{if $sPost.cat_sticky}display: none{/if}">
                                <div class="tree">
                                    {foreach from=$sections item='section'}
                                        <fieldset class="light">
                                            <legend id="legend_section_{$section.ID}"
                                                class="up"
                                                onclick="fieldset_action('section_{$section.ID}');">
                                                {$section.name}
                                            </legend>

                                            <div id="section_{$section.ID}">
                                                {if !empty($section.Categories)}
                                                    {include file='blocks/category_level_checkbox.tpl'
                                                        categories=$section.Categories
                                                        first=true}
                                                {else}
                                                    <div style="padding: 0 0 8px 10px;">
                                                        {$lang.no_items_in_sections}
                                                    </div>
                                                {/if}
                                            </div>
                                        </fieldset>
                                    {/foreach}
                                </div>
                            </div>

                            <script>
                            var tree_selected = false, tree_parentPoints = false;

                            {if $smarty.post.categories}
                                tree_selected = [], tree_parentPoints = [];

                                {foreach from=$smarty.post.categories item='postCategory'}
                                    tree_selected.push(['{$postCategory}']);
                                {/foreach}

                                {foreach from=$parentPoints item='parentPoint'}
                                    tree_parentPoints.push(['{$parentPoint}']);
                                {/foreach}
                            {/if}

                            {literal}
                            $(function() {
                                flynax.treeLoadLevel(
                                    'checkbox',
                                    'flynax.openTree(tree_selected, tree_parentPoints)',
                                    'div#cat_checkboxed'
                                );
                                flynax.openTree(tree_selected, tree_parentPoints);

                                $('input[name=cat_sticky]').click(function(){
                                    $('#cat_checkboxed').slideToggle();
                                    $('#cats_nav').fadeToggle();
                                });
                            });
                            {/literal}</script>

                            <div class="grey_area">
                                <label>
                                    <input class="checkbox"
                                        {if $sPost.cat_sticky}checked="checked"{/if}
                                        type="checkbox"
                                        name="cat_sticky"
                                        value="true" />
                                    {$lang.sticky}
                                </label>

                                <span id="cats_nav" {if $sPost.cat_sticky}class="hide"{/if}>
                                    <span onclick="$('#cat_checkboxed div.tree input').attr('checked', true);"
                                        class="green_10">
                                        {$lang.check_all}
                                    </span>
                                    <span class="divider"> | </span>
                                    <span onclick="$('#cat_checkboxed div.tree input').attr('checked', false);"
                                        class="green_10">
                                        {$lang.uncheck_all}
                                    </span>
                                </span>
                            </div>

                        </div>
                    </fieldset>
                </td>
            </tr>
            </table>
        </div>

        <div class="hide filter_mode" id="for_field_bound_boxes">
            <table class="form">
            <tr>
                <td class="name"><span class="red">*</span>{$lang.fb_boxes_list}</td>
                <td class="field">
                    {if $smarty.get.action == 'edit'}
                        <input type="hidden" name="field_bound_boxes" value="{$sPost.type}" />
                    {/if}

                    <select name="field_bound_boxes" {if $smarty.get.action == 'edit'}disabled class="disabled"{/if}>
                        <option value="">- {$lang.all} -</option>

                        {foreach from=$fieldBoundBoxes item='field_bound_box'}
                            <option value="{$field_bound_box.Key}"
                                {if $sPost.type == $field_bound_box.Key}selected="selected"{/if}>
                                {$field_bound_box.Box_name}
                            </option>
                        {/foreach}
                    </select>
                </td>
            </tr>
            </table>
        </div>

        <table class="form">
        <tr>
            <td class="name"><span class="red">*</span>{$lang.block_side}</td>
            <td class="field">
                <select name="side">
                    <option value="">{$lang.select}</option>
                    {foreach from=$l_block_sides item='block_side' name='sides_f' key='sKey'}
                    {if $sKey != 'integrated_banner' && $sKey != 'header_banner'}
                        <option {if $sKey != 'left' && $sKey != 'right'}class="dynamic"{/if}
                            value="{$sKey}" {if $sKey == $sPost.side}selected="selected"{/if}>
                            {$block_side}
                        </option>
                    {/if}
                    {/foreach}
                </select>
            </td>
        </tr>

        <tr>
            <td class="name"><span class="red">*</span>{$lang.use_block_design}</td>
            <td class="field">
                {if $sPost.tpl == '1'}
                    {assign var='tpl_yes' value='checked="checked"'}
                {elseif $sPost.tpl == '0'}
                    {assign var='tpl_no' value='checked="checked"'}
                {else}
                    {assign var='tpl_yes' value='checked="checked"'}
                {/if}
                <label><input {$tpl_yes} type="radio" name="tpl" value="1" /> {$lang.yes}</label>
                <label><input {$tpl_no} type="radio" name="tpl" value="0" /> {$lang.no}</label>
            </td>
        </tr>

        <tr>
            <td class="name"><span class="red">*</span>{$lang.status}</td>
            <td class="field">
                <select name="status">
                    <option value="active" {if $sPost.status == 'active'}selected="selected"{/if}>
                        {$lang.active}
                    </option>
                    <option value="approval" {if $sPost.status == 'approval'}selected="selected"{/if}>
                        {$lang.approval}
                    </option>
                </select>
            </td>
        </tr>

        <tr>
            <td></td>
            <td class="field">
                <input type="submit" value="{if $smarty.get.action == 'edit'}{$lang.edit}{else}{$lang.add}{/if}" />
            </td>
        </tr>
        </table>
    </form>
    {include file='blocks/m_block_end.tpl'}

    <script>{literal}
    function cat_chooser(cat_id){
        return true;
    }
    {/literal}

    {if $smarty.post.parent_id}
        cat_chooser('{$smarty.post.parent_id}');
    {elseif $smarty.get.parent_id}
        cat_chooser('{$smarty.get.parent_id}');
    {/if}

    {if $exp_cats}
        xajax_openTree('{$exp_cats}', 'category_level_checkbox.tpl');
    {/if}
    </script>
{elseif $smarty.get.action == 'build'}
    <div id="filter_fields">{include file='blocks/builder/builder.tpl' no_groups=true}</div>
    <script>{literal}
    $(function() {
        // change caption
        $('div#filter_fields legend:first').html("{/literal}{$lang.category_filter_filters_form}{literal}");

        // add config icon
        $('div#filter_fields div.field_obj').each(function() {
            $(this).find('span.b_field_type')
                .addClass('categoryFilterFieldType')
                .after(
                    $('<a>')
                        .addClass('categoryFilterFieldConfig')
                        .attr('title', '{/literal}{$lang.category_filter_configure_filter}{literal}')
                );

            var href = location.href;
            var replace = new RegExp('(\&form$)', 'gi');
            href = href.replace(replace, '');
            replace = new RegExp('\&action\=([^\&]+)', 'gi');
            href = href.replace(replace, '&action=config');
            var id = $(this).attr('id').split('_')[1];
            href += '&field='+ id;
            $(this).find('a.categoryFilterFieldConfig').attr('href', href);

            if ($(this).closest('div#fields_container').length) {
                $(this).find('a.categoryFilterFieldConfig').addClass('suspended');
            }
        });
    });
    {/literal}</script>
{elseif $smarty.get.action == 'config'}
    {assign var='sPost' value=$smarty.post}

    {include file='blocks/m_block_start.tpl'}
    <form action="{$rlBaseC}action=config&item={$smarty.get.item}&field={$smarty.get.field}"
        method="post"
        enctype="multipart/form-data"
        class="cf_filterConfiguration">
        <input type="hidden" name="action" value="config" />

        <table class="form">
        <tr>
            <td class="name">{$lang.category_filter_filter_field}</td>
            <td class="field"><b>{$lang[$fieldInfo.pName]} ({$lang[$fieldInfo.Type_pName]})</b></td>
        </tr>
        <tr {if $fieldInfo.Type != 'checkbox' && $fieldInfo.Mode != 'checkboxes'}style="display:none"{/if}>
            <td class="name"><span class="red">*</span>{$lang.category_filter_visible_items_limit}</td>
            <td class="field">
                <input type="text"
                    class="w60"
                    style="text-align: center;"
                    name="items_display_limit"
                    value="{$sPost.items_display_limit}" />
            </td>
        </tr>

        {if $fieldInfo.Key == 'Category_ID'}
            <tr>
                <td class="name"><span class="red">*</span>{$lang.category_filter_hide_empty_categories}</td>
                <td class="field">
                    {assign var='checkbox_field' value='hide_empty'}

                    {if $sPost.$checkbox_field == '1'}
                        {assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
                    {elseif $sPost.$checkbox_field == '0'}
                        {assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
                    {else}
                        {assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
                    {/if}

                    <input {$hide_empty_yes}
                        type="radio"
                        id="{$checkbox_field}_yes"
                        name="{$checkbox_field}"
                        value="1" />
                    <label for="{$checkbox_field}_yes">{$lang.yes}</label>
                    <input {$hide_empty_no}
                        type="radio"
                        id="{$checkbox_field}_no"
                        name="{$checkbox_field}"
                        value="0" />
                    <label for="{$checkbox_field}_no">{$lang.no}</label>
                </td>
            </tr>
        {/if}
        <tr>
            <td class="name"><span class="red">*</span>{$lang.category_filter_view_mode}</td>
            <td class="field">
                {assign var='cfMultiModeTypes' value="number|mixed|price"}

                {if $cfMultiModeTypes|strpos:$fieldInfo.Type === false && $fieldInfo.Condition != 'years'}
                    <select name="mode"
                        {if $fieldInfo.Type != 'select'
                            || $fieldInfo.multiField
                            || $fieldInfo.Key == 'Category_ID'}disabled="disabled" class="disabled"{/if}>
                        {foreach from=$modes item='mode_name' key='mode_key'}
                            {if $fieldInfo.Type == 'select'}
                                {if $mode_key == 'auto' || ($mode_key == 'checkboxes' && !$fieldInfo.multiField)}
                                    <option value="{$mode_key}" {if $sPost.mode == $mode_key}selected="selected"{/if}>
                                        {$mode_name}
                                    </option>
                                {/if}
                            {else}
                                {if $mode_key != 'checkboxes'}
                                    <option value="{$mode_key}" {if $sPost.mode == $mode_key}selected="selected"{/if}>
                                        {$mode_name}
                                    </option>
                                {/if}
                            {/if}
                        {/foreach}
                    </select>
                    <span class="field_description">{$lang.category_filter_mode_limit_notice}</span>
                {else}
                    <select name="mode">
                        {foreach from=$modes item='mode_name' key='mode_key'}
                            {if $fieldInfo.Condition == 'years'}
                                {if $mode_key != 'group' && $mode_key != 'checkboxes'}
                                    <option value="{$mode_key}"
                                        {if $sPost.mode == $mode_key}selected="selected"
                                        {elseif !$sPost.mode && $mode_key == 'slider'}selected="selected"{/if}>
                                        {$mode_name}
                                    </option>
                                {/if}
                            {else}
                                {if $mode_key != 'auto' && $mode_key != 'checkboxes'}
                                    <option value="{$mode_key}"
                                        {if $sPost.mode != 'auto' && $sPost.mode == $mode_key}selected="selected"
                                        {elseif $sPost.mode == 'auto' && $mode_key == 'slider'}selected="selected"{/if}>
                                        {$mode_name}
                                    </option>
                                {/if}
                            {/if}
                        {/foreach}
                    </select>
                {/if}
            </td>
        </tr>
        </table>

        {if $fieldInfo.Type != 'text' && $fieldInfo.Key != 'Category_ID' && !$fieldInfo.multiField}
            <div id="items_area">
                <table class="form">
                <tr>
                    <td class="name"><span class="red">*</span>{$lang.category_filter_filter_items}</td>
                    <td class="field">
                        {if $sPost.items}
                            {if $fieldInfo.Mode == 'group' || $sPost.mode == 'group'}
                                <div>
                                    {$min_max_stat}
                                    <div id="append_items" style="padding-top: 8px;">
                            {/if}

                            {foreach from=$sPost.items item='item_use' key='item_key'}
                                <div class="item {if $langsCount == 1}padding{/if}">
                                    {if $fieldInfo.Mode == 'group' || $sPost.mode == 'group'}
                                        <div class="cf-nav">
                                            <input type="text"
                                                style="width: 35px"
                                                name="from[{$item_key}]"
                                                value="{if $sPost.sign[$item_key] == 'less'}min{else}{$sPost.from[$item_key]}{/if}"
                                                {if $sPost.sign[$item_key] == 'less'}class="disabled" disabled{/if} />
                                            <span title="{if $sPost.sign[$item_key] == 'less'
                                                || $sPost.sign[$item_key] == 'greater'}{$lang.category_filter_change_sign}{/if}"
                                                class="{$sPost.sign[$item_key]}"></span>
                                            <input type="text"
                                                style="width: 35px"
                                                name="to[{$item_key}]"
                                                value="{if $sPost.sign[$item_key] == 'greater'}max{else}{$sPost.to[$item_key]}{/if}"
                                                {if $sPost.sign[$item_key] == 'greater'}class="disabled" disabled{/if}/>
                                            <input type="hidden" name="sign[{$item_key}]" value="{$sPost.sign[$item_key]}" />
                                            <input type="hidden" name="exist[{$item_key}]" value="1" />
                                        </div>
                                        <div class="cf-names">
                                    {/if}

                                    {if $langsCount > 1}
                                        <ul class="tabs">
                                            {foreach from=$allLangs item='language' name='langF'}
                                                <li lang="{$language.Code}"
                                                    {if $smarty.foreach.langF.first}class="active"{/if}>
                                                    {$language.name}
                                                </li>
                                            {/foreach}
                                        </ul>
                                    {/if}

                                    {foreach from=$allLangs item='language' name='langF'}
                                        {if $langsCount > 1}
                                            <div class="tab_area{if !$smarty.foreach.langF.first} hide{/if} {$language.Code}">
                                        {/if}

                                        <input type="text"
                                            name="items[{$item_key}][{$language.Code}]"
                                            value="{$item_use[$language.Code]}"
                                            maxlength="350" />

                                        {if $langsCount > 1}
                                                <span class="field_description_noicon">
                                                    {$lang.name} (<b>{$language.name}</b>)
                                                </span>
                                            </div>
                                        {/if}
                                    {/foreach}

                                    {if $fieldInfo.Mode == 'group' || $sPost.mode == 'group'}
                                        </div>

                                        <a href="javascript:void(0);"
                                            style="margin-left: 10px;"
                                            class="delete_item delete_item">
                                            {$lang.remove}
                                        </a>
                                    {/if}
                                </div>
                            {/foreach}

                            {if $fieldInfo.Mode == 'group' || $sPost.mode == 'group'}
                                    </div>
                                    <div class="add_item">
                                        <a onclick="cfAddItem();" href="javascript:void(0)">
                                            {$lang.add_item}
                                        </a>
                                    </div>
                                </div>
                            {/if}
                        {else}
                            <div>
                                {$min_max_stat}
                                <div id="append_items" style="padding-top: 8px;"></div>
                                <div class="add_item">
                                    <a onclick="cfAddItem();" href="javascript:void(0)">
                                        {$lang.add_item}
                                    </a>
                                </div>
                            </div>
                        {/if}
                    </td>
                </tr>
                </table>
            </div>
        {/if}

        <table class="form">
        <tr>
            <td class="name">{$lang.category_filter_no_index}</td>
            <td class="field">
                {if $sPost.no_index == '1'}
                    {assign var='no_index_yes' value='checked="checked"'}
                {elseif $sPost.no_index == '0'}
                    {assign var='no_index_no' value='checked="checked"'}
                {else}
                    {assign var='no_index_no' value='checked="checked"'}
                {/if}

                <label><input {$no_index_yes} type="radio" name="no_index" value="1" /> {$lang.yes}</label>
                <label><input {$no_index_no} type="radio" name="no_index" value="0" /> {$lang.no}</label>

                <span class="field_description_noicon">{$lang.category_filter_no_index_desc}</span>
            </td>
        </tr>
        <tr class="data_settings">
            <td class="name">{$lang.cf_data_in_page}</td>
            <td class="field">
                <fieldset class="light">
                    <legend id="legend_data_settings"></legend>
                    <div id="data_settings">
                        <table class="form">
                            <tbody>
                                {if $fieldInfo.Key == 'Category_ID'}
                                    {$lang.not_available}

                                    <span class="field_description"><span>{$lang.cf_data_in_H1_category}</span></span>
                                {else}
                                    <tr>
                                        <td class="name">{$lang.cf_data_in_title}</td>
                                        <td class="field">
                                            {if $sPost.data_in_title == '1'}
                                                {assign var='data_in_title_yes' value='checked="checked"'}
                                            {elseif $sPost.data_in_title == '0'}
                                                {assign var='data_in_title_no' value='checked="checked"'}
                                            {else}
                                                {assign var='data_in_title_yes' value='checked="checked"'}
                                            {/if}

                                            <label>
                                                <input {$data_in_title_yes} type="radio" name="data_in_title" value="1" />
                                                {$lang.yes}
                                            </label>
                                            <label>
                                                <input {$data_in_title_no} type="radio" name="data_in_title" value="0" />
                                                {$lang.no}
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="name">{$lang.cf_data_in_description}</td>
                                        <td class="field">
                                            {if $sPost.data_in_description == '1'}
                                                {assign var='data_in_description_yes' value='checked="checked"'}
                                            {elseif $sPost.data_in_description == '0'}
                                                {assign var='data_in_description_no' value='checked="checked"'}
                                            {else}
                                                {assign var='data_in_description_yes' value='checked="checked"'}
                                            {/if}

                                            <label>
                                                <input {$data_in_description_yes}
                                                    type="radio"
                                                    name="data_in_description"
                                                    value="1" />
                                                {$lang.yes}
                                            </label>
                                            <label>
                                                <input {$data_in_description_no}
                                                    type="radio"
                                                    name="data_in_description"
                                                    value="0" />
                                                {$lang.no}
                                            </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="name">{$lang.cf_data_in_H1}</td>
                                        <td class="field">
                                            {if $sPost.data_in_H1 == '1'}
                                                {assign var='data_in_H1_yes' value='checked="checked"'}
                                            {elseif $sPost.data_in_H1 == '0'}
                                                {assign var='data_in_H1_no' value='checked="checked"'}
                                            {else}
                                                {if $fieldInfo.Key == 'Category_ID'}
                                                    {assign var='data_in_H1_yes' value='checked="checked"'}
                                                {else}
                                                    {assign var='data_in_H1_no' value='checked="checked"'}
                                                {/if}
                                            {/if}

                                            <label>
                                                <input {$data_in_H1_yes} type="radio" name="data_in_H1" value="1" />
                                                {$lang.yes}
                                            </label>
                                            <label>
                                                <input {$data_in_H1_no} type="radio" name="data_in_H1" value="0" />
                                                {$lang.no}
                                            </label>
                                        </td>
                                    </tr>
                                {/if}
                            </tbody>
                        </table>
                    </div>
                </fieldset>
            </td>
        </tr>
        <tr>
            <td class="name"><span class="red">*</span>{$lang.status}</td>
            <td class="field">
                <select name="status">
                    <option value="active" {if $sPost.status == 'active'}selected="selected"{/if}>
                        {$lang.active}
                    </option>
                    <option value="approval" {if $sPost.status == 'approval'}selected="selected"{/if}>
                        {$lang.approval}
                    </option>
                </select>
            </td>
        </tr>
        <tr>
            <td></td>
            <td class="field">
                <input type="submit" value="{$lang.edit}" />
                <a href="{$rlBaseC}action=build&item={$smarty.get.item}&form" class="cancel">{$lang.cancel}</a>
            </td>
        </tr>
        </table>
    </form>

    <div class="hide item_source">
        <div {if $langsCount == 1}style="padding: 0 0 5px 0;"{/if}>
            <div class="cf-nav">
                <input class="numeric" type="text" style="width: 35px" name="from[[key]]" value="" />
                <span title="" class="between"></span>
                <input class="numeric" type="text" style="width: 35px" name="to[[key]]" value="" />
                <input type="hidden" name="sign[[key]]" value="between" />
            </div>

            <div class="cf-names">
                {if $langsCount > 1}
                    <ul class="tabs">
                        {foreach from=$allLangs item='language' name='langF'}
                            <li lang="{$language.Code}"
                                {if $smarty.foreach.langF.first}class="active"{/if}>
                                {$language.name}
                            </li>
                        {/foreach}
                    </ul>
                {/if}

                {foreach from=$allLangs item='language' name='langF'}
                    {if $langsCount > 1}
                        <div class="tab_area{if !$smarty.foreach.langF.first} hide{/if} {$language.Code}">
                    {/if}

                    <input type="text" name="items[[key]][{$language.Code}]" maxlength="350" />

                    {if $langsCount > 1}
                            <span class="field_description_noicon">{$lang.name} (<b>{$language.name}</b>)</span>
                        </div>
                    {/if}
                {/foreach}
            </div>

            <a href="javascript:void(0);" style="margin-left: 10px;" class="delete_item delete_row">
                {$lang.remove}
            </a>
        </div>
    </div>
    {include file='blocks/m_block_end.tpl'}

    <script>
    var cfCondition   = '{$fieldInfo.Condition}';
    var cfMode        = '{$fieldInfo.Mode}';
    var cfType        = '{$fieldInfo.Type}';
    var cfCurrentItem = {if $sPost.items}{$sPost.items|@count}+1{else}1{/if};
    var cfBoxID       = '{$smarty.get.item}';
    var cfFieldID     = '{$smarty.get.field}';
    {literal}

    $(function() {
        $('select[name=mode]').change(function(){ cfModeHandler(); });

        cfModeHandler();
        cfRemoveHandler();
        cfDeleteHandler();
        cfControlSigns();

        $('a#build_items').click(function() {
            $(this).parent().next().show();
            $(this).parent().remove();
        });

        if (cfMode == 'group') {
            flynax.copyPhrase = function(){};
        }

        $('input.numeric').numeric();
    });

    var cfModeHandler = function() {
        var val = $('select[name=mode]').val();

        if (val == 'slider' || val == 'checkboxes' || val == 'text'
            || (val == 'auto'
                && (cfType == 'price' || cfType == 'mixed' || cfType == 'number' || cfCondition == 'years')
            )
        ) {
            if (val != 'checkboxes') {
                $('#items_area').slideUp('fast');
            }

            $('[name="items_display_limit"]').closest('tr').hide();
        } else {
            $('#items_area').slideDown();

            if (cfCurrentItem == 1) {
                cfAddItem();
            }

            $('[name="items_display_limit"]').closest('tr').show();
        }
    }

    var cfAddItem = function() {
        var source = $('div.item_source').html();
        $('div#append_items').append(source.replace(/\[key\]/gi, cfCurrentItem));

        cfRemoveHandler();
        cfCurrentItem++;
        flynax.tabs();
        cfControlSigns();

        $('input.numeric').numeric();

        if ($('div#append_items > div').length == 6) {
            $('#items_area div.add_item').addClass('hide');
        }
    }

    var cfRemoveHandler = function() {
        $('a.delete_row').off('click').click(function() {
            $(this).parent().remove();
            $('#items_area div.add_item').removeClass('hide');
            cfControlSigns();
        });
    }

    var cfDeleteHandler = function() {
        $('a.delete_item').off('click').click(function() {
            var itemID        = $(this).parent().find('div.cf-nav input[name^=sign]').attr('name');
            var pattern       = new RegExp('\\[([^\\]]+)\\]');
            itemID            = itemID.match(pattern);
            var $link         = $(this);
            var $itemsSection = $('div#append_items');

            $link
                .html(lang['loading'])
                .css('text-decoration', 'none')
                .css('cursor', 'default');

            $.post(
                rlConfig['ajax_url'],
                {item: 'cfAjaxRemoveRow', id: itemID[1], box_id: cfBoxID, field_id: cfFieldID},
                function(response) {
                    if (response && (response.status || response.message)) {
                        if (response.status == 'OK') {
                            $('[name="sign[' + itemID[1] + ']"]').closest('.item').remove();

                            if ($itemsSection.find('.item').length == 1) {
                                $itemsSection.find('a.delete_row,a.delete_item').addClass('hide');
                            }
                        } else {
                            $link
                                .html(lang['ext_remove'])
                                .css('text-decoration', 'underline')
                                .css('cursor', 'pointer');
                            printMessage('error', response.message);
                        }
                    }
                },
                'json'
            );

            cfControlSigns();
        });
    }

    var cfSignHandler = function() {
        $('div#append_items div.cf-nav span').off('click');

        // update functional of first and last elements
        $('div#append_items div.cf-nav span').click(function() {
            // get indexes of first and last elements
            var first = $('div#append_items > div:first').index();
            var last = $('div#append_items > div:last').index();

            if (first != last && $('div#append_items > div').length > 1) {
                // first element
                if ($(this).parent().parent().index() == first) {
                    if ($(this).hasClass('between')) {
                        $(this).removeClass('between').addClass('less');
                        $(this).prev().val('min').addClass('disabled').attr('disabled', true);
                        $(this).next().next().val('less');
                    } else {
                        $(this).removeClass('less').addClass('between');
                        $(this).prev().val('').removeClass('disabled').attr('disabled', false);
                        $(this).next().next().val('between');
                    }
                }

                // last element
                if ($(this).parent().parent().index() == last) {
                    if ($(this).hasClass('between')) {
                        $(this).removeClass('between').addClass('greater');
                        $(this).next().val('max').addClass('disabled').attr('disabled', true);
                        $(this).next().next().val('greater');
                    } else {
                        $(this).removeClass('greater').addClass('between');
                        $(this).next().val('').removeClass('disabled').attr('disabled', false);
                        $(this).next().next().val('between');
                    }
                }
            }
        });
    }

    var cfControlSigns = function() {
        // count exist elements
        var count_items = $('div#append_items > div').length;
        var first = '', last = '';

        // case with two or more elements
        if (count_items > 1) {
            // update all exist elements
            $('div#append_items > div').find('div.cf-nav > span').attr('title', '').css('cursor', 'auto');

            // first element will be less always and last element will be greater always
            first = $('div#append_items > div:first');
            last = $('div#append_items > div:last');

            $(first).find('div.cf-nav > span')
                .attr('title', '{/literal}{$lang.category_filter_change_sign}{literal}')
                .css('cursor', 'pointer');
            $(last).find('div.cf-nav > span')
                .attr('title', '{/literal}{$lang.category_filter_change_sign}{literal}')
                .css('cursor', 'pointer');

            $('div#append_items div.cf-nav > span').each(function() {
                if ($(this).next().val() == 'max'
                    && $(this).parent().parent().index() != $('div#append_items > div:last').index()
                ) {
                    $(this).removeClass('greater').addClass('between');
                    $(this).next().val('').removeClass('disabled').attr('disabled', false);
                    $(this).next().next().val('between');
                }
            });

            cfSignHandler();

            // show remove icon
            $('div#append_items > div a.delete_row,a.delete_item').removeClass('hide');
        }
        // case with one element only
        else if (count_items == 1) {
            var parent = $('div#append_items > div');

            // update fields if items not exist
            if (cfCurrentItem == 1) {
                $(parent).find('div.cf-nav input[name^=from]')
                    .val('')
                    .removeClass('disabled')
                    .attr('disabled', false);
                $(parent).find('div.cf-nav input[name^=to]')
                    .val('')
                    .removeClass('disabled')
                    .attr('disabled', false);
                $(parent).find('div.cf-nav span').attr('class', '').addClass('between');
                $(parent).find('div.cf-nav input[name^=sign]').val('between');
            }

            // hide remove icon
            $('div#append_items > div a.delete_row,a.delete_item').addClass('hide');
        }
    }
    {/literal}
    </script>
{else}
    <!-- grid -->
    <div id="grid"></div>

    <script>
    var blocksSides = [], categoryFilterGrid;

    // blocks sides list
    {foreach from=$l_block_sides key='sideKey' item='blockSide'}
        blocksSides.push(['{$sideKey}', '{$blockSide}']);
    {/foreach}

    {literal}
    $(function() {
        categoryFilterGrid = new gridObj({
            key: 'categoryFilter',
            id: 'grid',
            ajaxUrl: rlPlugins + 'categoryFilter/admin/categoryFilter.inc.php?q=ext',
            defaultSortField: 'ID',
            title: lang['category_filter_ext_caption'],
            remoteSortable: false,
            fields: [
                {name: 'Name', mapping: 'Name', type: 'string'},
                {name: 'Mode', mapping: 'Mode', type: 'string'},
                {name: 'Status', mapping: 'Status', type: 'string'},
                {name: 'ID', mapping: 'ID'},
                {name: 'Categories', mapping: 'Categories', type: 'string'},
                {name: 'Side', mapping: 'Side', type: 'string'},
                {name: 'Tpl', mapping: 'Tpl', type: 'string'}
            ],
            columns: [
                {
                    header: lang['ext_name'],
                    dataIndex: 'Name',
                    width: 15,
                    id: 'rlExt_item_bold'
                },{
                    header: lang['category_filter_related_categories'],
                    dataIndex: 'Categories',
                    width: 20,
                    id: 'rlExt_item'
                },{
                    header: "{/literal}{$lang.category_filter_filter_for}{literal}",
                    dataIndex: 'Mode',
                    width: 120,
                    fixed: true
                },{
                    header: lang['ext_block_side'],
                    dataIndex: 'Side',
                    width: 100,
                    fixed: true,
                    editor: new Ext.form.ComboBox({
                        store: blocksSides,
                        displayField: 'value',
                        valueField: 'key',
                        typeAhead: true,
                        mode: 'local',
                        triggerAction: 'all',
                        selectOnFocus:true
                    }),
                    renderer: function(val){
                        return '<span ext:qtip="' + lang['ext_click_to_edit'] + '">' + val + '</span>';
                    }
                },{
                    header: lang['ext_block_style'],
                    dataIndex: 'Tpl',
                    width: 100,
                    fixed: true,
                    editor: new Ext.form.ComboBox({
                        store: [
                            ['1', lang['ext_yes']],
                            ['0', lang['ext_no']]
                        ],
                        displayField: 'value',
                        valueField: 'key',
                        typeAhead: true,
                        mode: 'local',
                        triggerAction: 'all',
                        selectOnFocus:true
                    }),
                    renderer: function(val){
                        return '<span ext:qtip="' + lang['ext_click_to_edit'] + '">' + val + '</span>';
                    }
                },{
                    header: lang['ext_status'],
                    dataIndex: 'Status',
                    fixed: true,
                    width: 100,
                    editor: new Ext.form.ComboBox({
                        store: [
                            ['active', lang['ext_active']],
                            ['approval', lang['ext_approval']]
                        ],
                        displayField: 'value',
                        valueField: 'key',
                        typeAhead: true,
                        mode: 'local',
                        triggerAction: 'all',
                        selectOnFocus:true
                    })
                },{
                    header: lang['ext_actions'],
                    width: 100,
                    fixed: true,
                    dataIndex: 'ID',
                    sortable: false,
                    renderer: function(data) {
                        var out = "<center>";
                        out += "<a href='" + rlUrlHome + "index.php?controller=" + controller;
                        out += "&action=build&item=" + data + "&form'><img class='build' ext:qtip='";
                        out += lang['ext_build'] + "' src='" + rlUrlHome + "img/blank.gif' /></a>";
                        out += "<a href='" + rlUrlHome + "index.php?controller=" + controller;
                        out += "&action=edit&item=" + data + "'><img class='edit' ext:qtip='";
                        out += lang['ext_edit'] + "' src='" + rlUrlHome + "img/blank.gif' /></a>";
                        out += "<img class='remove' ext:qtip='" + lang['ext_delete'] + "' src='";
                        out += rlUrlHome + "img/blank.gif' onClick='rlConfirm(\"";
                        out += lang['ext_notice_' + delete_mod] + "\", \"cfAjaxDeleteBox\", \"";
                        out += Array(data) + "\" )' />";
                        out += "</center>";
                        return out;
                    }
                }
            ]
        });

        categoryFilterGrid.init();
        grid.push(categoryFilterGrid.grid);
    });

    var cfAjaxDeleteBox = function(data) {
        if (!data) {
            return;
        }

        $.post(
            rlConfig['ajax_url'],
            {item: 'cfAjaxDeleteBox', id: data},
            function(response){
                if (response && response.status && response.message) {
                    if (response.status == 'OK') {
                        printMessage('notice', response.message);
                        categoryFilterGrid.reload();
                    } else {
                        printMessage('error', response.message);
                    }
                }
            },
            'json'
        );
    }
    {/literal}</script>
    <!-- grid end -->
{/if}

<!-- category filter tpl end -->
