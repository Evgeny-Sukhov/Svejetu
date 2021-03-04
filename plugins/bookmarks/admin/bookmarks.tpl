<!-- bookmarks tpl -->

<link type="text/css" rel="stylesheet" href="{$smarty.const.RL_PLUGINS_URL}bookmarks/static/style-admin.css" />
<style>
{literal}
ul.networks-list > li > span.nav-icon {
    background-image: url('{/literal}{$rlTplBase}img/form.png{literal}');
}
{/literal}
</style>

<!-- navigation bar -->
<div id="nav_bar">
    <a href="{$rlBaseC}action=add" class="button_bar"><span class="left"></span><span class="center_add">{$lang.bsh_add_block}</span><span class="right"></span></a>
    <a href="{$rlBaseC}" class="button_bar"><span class="left"></span><span class="center_list">{$lang.items_list}</span><span class="right"></span></a>
</div>
<!-- navigation bar end -->

{if $smarty.get.action}

    {assign var='sPost' value=$smarty.post}

    <!-- add new/edit block -->
    {include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl'}
    <form action="{$rlBaseC}action={if $smarty.get.action == 'add'}add{elseif $smarty.get.action == 'edit'}edit&item={$smarty.get.item}{/if}" method="post">
        <input type="hidden" name="submit" value="1" />
        {if $smarty.get.action == 'edit'}
            <input type="hidden" name="fromPost" value="1" />
        {/if}
        <table class="form">
        <tr>
            <td class="name">{$lang.bsh_bookmark_type}</td>
            <td class="field" style="padding-top: 10px">
                <div class="bookmarks-type{if $smarty.get.action == 'edit'} edit-mode{/if}">
                    <label{if $smarty.get.action == 'edit' && $sPost.type == 'inline'} class="hide"{/if}>
                        <div>
                            <input 
                                type="radio" 
                                name="type" 
                                value="floating_bar"
                                {if $sPost.type == 'floating_bar' || !$sPost.type}
                                checked="checked" 
                                {/if}
                                {if $smarty.get.action == 'edit'}
                                class="hide"
                                {/if}
                                />

                            {$lang.bsh_floating_bar}
                        </div>
                        <img src="{$smarty.const.RL_PLUGINS_URL}bookmarks/static/floating-bar.png" />
                    </label>

                    <label{if $smarty.get.action == 'edit' && $sPost.type == 'floating_bar'} class="hide"{/if}>
                        <div>
                            <input 
                                type="radio"
                                name="type"
                                value="inline"
                                {if $sPost.type == 'inline'}
                                checked="checked"
                                {/if}
                                {if $smarty.get.action == 'edit'}
                                class="hide"
                                {/if}
                                />
                            
                            {$lang.bsh_inline}
                        </div>
                        <img src="{$smarty.const.RL_PLUGINS_URL}bookmarks/static/inline.png" />
                    </label>
                </div>

                <script>
                {literal}

                $(function(){
                    "use strict";

                    var $type_input = $('input[name=type]');
                    var typeChangeHandler = function(){
                        var $input = $type_input.filter(':checked');
                        var val = $input.val();

                        $('#inline_settings')[val == 'inline'
                            ? 'slideDown'
                            : 'slideUp'
                        ]();

                        $('select[name=share_type] option[value=both]')[val == 'inline'
                            ? 'addClass'
                            : 'removeClass'
                        ]('hide');

                        $('.share-style')[val == 'inline'
                            ? 'slideDown'
                            : 'slideUp'
                        ]();
                        $('.share-theme')[val == 'floating_bar'
                            ? 'slideDown'
                            : 'slideUp'
                        ]();
                        $('#button_size_hint')[val == 'floating_bar'
                            ? 'show'
                            : 'hide'
                        ]();
                    }

                    typeChangeHandler();
                    $type_input.change(function(){
                        typeChangeHandler();
                    });
                });

                {/literal}
                </script>
            </td>
        </tr>
        </table>
        
        <table class="form">
        <tr>
            <td class="name">{$lang.bookmarks_social_networks}</td>
            <td class="field" style="padding: 9px 0 15px;">
                <input 
                    type="hidden"
                    name="services"
                    value="{if $sPost.services && $sPost.service_type == 'custom'}{$sPost.services}{else}facebook,twitter,pinterest_share,email,addthis{/if}"
                    />
                <label class="bookmarks-inline-label disabled">
                    <input
                        type="radio"
                        name="service_type"
                        value="automatic"
                        {if $sPost.service_type == 'automatic' || !$sPost.service_type}
                        checked="checked"
                        {/if}
                        /> {$lang.bookmarks_services_automatic}
                </label>
                <div class="bookmarks-type-container" id="service_automatic">
                    <select name="services_number" style="width: 50px;">
                        {section name='number' start=1 loop=11 step=1}
                            {assign var='num' value=$smarty.section.number.index}
                            <option
                                value="{$num}"
                                {if (!$sPost.services_number && $num == 5)
                                    || ($sPost.services_number && $sPost.services_number == $num)}
                                selected="selected"
                                {/if}

                            >{$num}</option>
                        {/section}
                    </select>
                    <span class="field_description">{$lang.bookmarks_services_automatic_number}</span>
                </div>

                <label class="bookmarks-inline-label disabled">
                    <input
                        type="radio"
                        name="service_type"
                        value="custom" 
                        {if $sPost.service_type == 'custom'}
                        checked="checked"
                        {/if}
                        /> {$lang.bookmarks_services_custom}
                </label>
                <div class="bookmarks-type-container" id="service_custom">
                    <div>
                        <b>{$lang.bookmarks_available_services}</b>
                        <div class="bookmarks-search">
                            <input type="text" name="search" autocomplete="off" placeholder="{$lang.search}" />
                        </div>
                        <ul class="networks-list" id="available-list">
                            {foreach from=$services item='service' name='services'}
                                <li 
                                    data-code="{$service.code}"
                                    data-name="{$service.name}"
                                    {if $service.original}
                                    data-original="true"
                                    {/if}
                                    >
                                    <span class="icon" style="background-color: #{$service.bg};">
                                        <img
                                            src="{$rlTplBase}img/blank.gif"
                                            data-src="{$smarty.const.RL_PLUGINS_URL}bookmarks/static/icons/{$service.code}.svg" />
                                    </span>
                                    {$service.name}
                                    <span class="nav-icon"></span>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                    <div>
                        <b>{$lang.bookmarks_selected_services}</b>
                        <ul class="networks-list" id="selected-list"></ul>
                    </div>
                </div>
            </td>
        </tr>
        </table>

        <script>
        {literal}

        $(function(){
            "use strict";

            var $networks = $('ul.networks-list');
            var $available_list = $('#available-list');
            var $selected_list = $('#selected-list');
            var $items = $available_list.find('> li');
            var $search_input = $('.bookmarks-search input');
            var $services_input = $('input[name=services]');

            // Search
            $search_input.on('keyup', function(char){
                var val = this.value;
                var pattern = new RegExp(val, 'gi');

                if (val == '') {
                    $items.removeClass('hide');
                } else {
                    $items
                        .addClass('hide')
                        .filter(function(index){
                            return $(this).data('name').match(pattern);
                        })
                        .removeClass('hide');
                }
            });

            // Management
            $networks.on('click', 'span.nav-icon', function(){
                var $item = $(this).closest('li');
                var $source_cont = $(this).closest('.networks-list');
                var code = $item.data('code');

                // Add
                if ($source_cont.attr('id') == 'available-list') {
                    $selected_list.append($item.clone());
                    $item.addClass('disabled');
                }
                // Remove
                else {
                    $item.remove();
                    $available_list.find('li[data-code=' + code + ']').removeClass('disabled');
                }

                // Update hidden input value
                var codes = $selected_list.find('> li')
                    .map(function(){
                        return $(this).data('code');
                    })
                    .get()
                    .join(',');

                $services_input.val(codes);
            });

            // Default selection
            if ($services_input.val()) {
                var default_services = $services_input.val().split(',');
                for (var i in default_services) {
                    $available_list.find('li[data-code="' + default_services[i] + '"] span.nav-icon')
                        .trigger('click');
                }
            }

            // Replace svg with it's content
            $networks.find('img').each(function(index, item){
                $.get($(item).data('src'), function(data) {
                    $(item).replaceWith($(data).find('svg'));
                });
            });

            // Service type handler
            var $service_type_input = $('input[name=service_type]');
            var serviceTypeChangeHandler = function(){
                var $input = $service_type_input.filter(':checked');
                var $label = $input.closest('label');

                $('.bookmarks-inline-label').addClass('disabled');
                $label.removeClass('disabled');
            }

            serviceTypeChangeHandler();
            $service_type_input.change(function(){
                serviceTypeChangeHandler();
            });
        });

        {/literal}
        </script>
        
        <div class="share-style hide">
            <table class="form">
            <tr>
                <td class="name">{$lang.bookmarks_share_style}</td>
                <td class="field">
                    <select name="share_style">
                        {foreach from=$share_styles item='share_style_name' key='share_style_key'}
                            <option 
                                {if $sPost.share_style == $share_style_key}
                                selected="selected"
                                {/if}
                                value="{$share_style_key}">
                                {$share_style_name}
                            </option>
                        {/foreach}
                    </select>
                </td>
            </tr>
            </table>

            <script>
            {literal}

            $(function(){
                "use strict";

                var $style_selector = $('select[name=share_style]');
                var $modern_settings = $('#modern_settings');
                var $labels = $('label.bookmarks-inline-label');
                var $services = $('ul.networks-list');
                var styleChangeHandler = function(){
                    var val = $style_selector.val();

                    if (val == 'original') {
                        $modern_settings.slideUp();
                        $labels.hide();
                        $services.find('li[data-original!=true]').addClass('unavailable');
                        $('input[name=service_type][value=custom]').trigger('click');
                        $('#selected-list > li[data-original!=true] span.nav-icon').trigger('click');
                    } else {
                        $modern_settings.slideDown();
                        $labels.show();
                        $services.find('.unavailable').removeClass('unavailable');
                    }
                }

                styleChangeHandler();
                $style_selector.change(function(){
                    styleChangeHandler();
                });
            });

            {/literal}
            </script>
        </div>

        <div class="share-theme hide">
            <table class="form">
            <tr>
                <td class="name">{$lang.bookmarks_theme}</td>
                <td class="field">
                    <select name="theme">
                        {foreach from=$themes item='theme_name' name='themes' key='theme_key'}
                            <option 
                                {if $sPost.theme == $theme_key
                                || ($sPost.theme && $smarty.foreach.themes.first)
                                }
                                selected="selected"
                                {/if}
                                value="{$theme_key}">
                                {$theme_name}
                            </option>
                        {/foreach}
                    </select>
                </td>
            </tr>
            </table>
        </div>

        <div id="modern_settings">
            <table class="form">
            <tr>
                <td class="name">{$lang.bookmarks_share_type}</td>
                <td class="field">
                    <select name="share_type">
                        {foreach from=$share_types item='share_type_name' key='share_type_key'}
                            <option {if $sPost.share_type == $share_type_key}selected="selected"{/if} value="{$share_type_key}">{$share_type_name}</option>
                        {/foreach}
                    </select>
                </td>
            </tr>

            <tr>
                <td class="name">{$lang.bookmarks_button_size}</td>
                <td class="field">
                    <select name="button_size">
                        {foreach from=$button_sizes item='size_name' key='size_key'}
                            <option {if $sPost.button_size == $size_key}selected="selected"{/if} value="{$size_key}">{$size_name}</option>
                        {/foreach}
                    </select>

                    <span id="button_size_hint" class="field_description hide">{$lang.bookmarks_button_size_hint}</span>
                </td>
            </tr>
            </table>
        </div>

        <table class="form">
        <tr>
            <td class="name">{$lang.show_on_pages}</td>
            <td class="field" id="pages_obj">
                <fieldset class="light">
                    {assign var='pages_phrase' value='admin_controllers+name+pages'}
                    <legend id="legend_pages" class="up">{$lang.$pages_phrase}</legend>
                    <div id="pages">
                        <div id="pages_cont" {if !empty($sPost.show_on_all)}style="display: none;"{/if}>
                            {assign var='bPages' value=$sPost.pages}
                            <table class="sTable" style="margin-bottom: 15px;">
                            <tr>
                                <td valign="top">
                                {foreach from=$pages item='page' name='pagesF'}
                                {assign var='pId' value=$page.ID}
                                <div style="padding: 2px 8px;">
                                    <input class="checkbox" {if $page.ID|in_array:$sPost.pages}checked="checked"{/if} id="page_{$page.ID}" type="checkbox" name="pages[{$page.ID}]" value="{$page.ID}" /> <label class="cLabel" for="page_{$page.ID}">{$page.name}</label>
                                </div>
                                {assign var='perCol' value=$smarty.foreach.pagesF.total/3|ceil}
            
                                {if $smarty.foreach.pagesF.iteration % $perCol == 0}
                                    </td>
                                    <td valign="top">
                                {/if}
                                {/foreach}
                                </td>
                            </tr>
                            </table>
                        </div>
                        
                        <div class="grey_area" style="margin: 0 0 5px;">
                            <label><input id="show_on_all" {if $sPost.show_on_all}checked="checked"{/if} type="checkbox" name="show_on_all" value="true" /> {$lang.sticky}</label>
                            <span id="pages_nav" {if $sPost.show_on_all}class="hide"{/if}>
                                <span onclick="$('#pages_cont input').attr('checked', true);" class="green_10">{$lang.check_all}</span>
                                <span class="divider"> | </span>
                                <span onclick="$('#pages_cont input').attr('checked', false);" class="green_10">{$lang.uncheck_all}</span>
                            </span>
                        </div>
                    </div>
                </fieldset>
                
                <script type="text/javascript">
                {literal}
                
                $(document).ready(function(){
                    $('#legend_pages').click(function(){
                        fieldset_action('pages');
                    });
                    
                    $('input#show_on_all').click(function(){
                        $('#pages_cont').slideToggle();
                        $('#pages_nav').fadeToggle();
                    });
                });
                
                {/literal}
                </script>
            </td>
        </tr>

        {if $smarty.get.action == 'edit'}
        <tr>
            <td class="name"><span class="red">*</span>{$lang.status}</td>
            <td class="field">
                <select name="status">
                    <option value="active" {if $sPost.status == 'active'}selected="selected"{/if}>{$lang.active}</option>
                    <option value="approval" {if $sPost.status == 'approval'}selected="selected"{/if}>{$lang.approval}</option>
                </select>
            </td>
        </tr>
        {/if}

        </table>

        <div id="inline_settings" class="hide">
            <table class="form">
            <tr>
                <td class="divider" colspan="3">
                    <div class="inner">{$lang.bookmarks_box_options}</div>
                </td>
            </tr>
            <tr>
                <td class="name">{$lang.use_block_header}</td>
                <td class="field">
                    {if $sPost.header == '1'}
                        {assign var='header_yes' value='checked="checked"'}
                    {elseif $sPost.header == '0'}
                        {assign var='header_no' value='checked="checked"'}
                    {else}
                        {assign var='header_no' value='checked="checked"'}
                    {/if}
                    <label><input {$header_yes} class="lang_add" type="radio" name="header" value="1" /> {$lang.yes}</label>
                    <label><input {$header_no} class="lang_add" type="radio" name="header" value="0" /> {$lang.no}</label>
                </td>
            </tr>
            <tr>
                <td class="name">{$lang.use_block_design}</td>
                <td class="field">
                    {if $sPost.tpl == '1'}
                        {assign var='tpl_yes' value='checked="checked"'}
                    {elseif $sPost.tpl == '0'}
                        {assign var='tpl_no' value='checked="checked"'}
                    {else}
                        {assign var='tpl_no' value='checked="checked"'}
                    {/if}
                    <label><input {$tpl_yes} type="radio" name="tpl" value="1" /> {$lang.yes}</label>
                    <label><input {$tpl_no} type="radio" name="tpl" value="0" /> {$lang.no}</label>
                </td>
            </tr>
            </table>

            <div id="box_name" class="hide">
                <table class="form">
                <tr>
                    <td class="name"><span class="red">*</span>{$lang.name}</td>
                    <td class="field">
                        {if $languages|@count > 1}
                            <ul class="tabs">
                                {foreach from=$languages item='language' name='langF'}
                                <li lang="{$language.Code}" {if $smarty.foreach.langF.first}class="active"{/if}>{$language.name}</li>
                                {/foreach}
                            </ul>
                        {/if}
                        
                        {foreach from=$languages item='language' name='langF'}
                            {if $languages|@count > 1}<div class="tab_area{if !$smarty.foreach.langF.first} hide{/if} {$language.Code}">{/if}
                            <input type="text" name="name[{$language.Code}]" value="{$sPost.name[$language.Code]}" maxlength="350" />
                            {if $languages|@count > 1}
                                    <span class="field_description_noicon">{$lang.name} (<b>{$language.name}</b>)</span>
                                </div>
                            {/if}
                        {/foreach}
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
                        <option value="{$sKey}" {if $sKey == $sPost.side}selected="selected"{/if}>{$block_side}</option>
                        {/foreach}
                    </select>
                </td>
            </tr>
            <tr>
                <td class="name"><span class="red">*</span>{$lang.bookmarks_align}</td>
                <td class="field">
                    <select name="align">
                        <option value="0">{$lang.select}</option>
                        {foreach from=$aligns item='align_name' key='align'}
                            <option {if $sPost.align == $align}selected="selected"{/if} value="{$align}">{$align_name}</option>
                        {/foreach}
                    </select>
                </td>
            </tr>
            </table>
        </div>

        <script>
        {literal}
                
        $(function(){
            "use strict";

            var $header = $('input[name=header]');
            var boxHeaderHandler = function(){
                $('#box_name')[$header.filter(':checked').val() == '1'
                    ? 'slideDown'
                    : 'slideUp'
                ]();
            }

            boxHeaderHandler();
            $header.change(function(){
                boxHeaderHandler();
            });
        });
        
        {/literal}
        </script>

        <table class="form">
        <tr>
            <td class="name" style="background: none;"></td>
            <td class="field">
                <input type="submit" value="{if $smarty.get.action == 'edit'}{$lang.edit}{else}{$lang.add}{/if}" />
            </td>
        </tr>
        </table>
    </form>
    {include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
    <!-- add new block end -->

{else}

    <!-- grid -->
    <div id="grid"></div>
    <script>
    lang['bookmarks_align'] = "{$lang.bookmarks_align}";
    lang['bookmark_left'] = "{$lang.bookmark_left}";
    lang['bookmark_center'] = "{$lang.bookmark_center}";
    lang['bookmark_right'] = "{$lang.bookmark_right}";
    lang['show_on_pages'] = "{$lang.show_on_pages}";
    {literal}

    var listingGroupsGrid;

    $(document).ready(function(){
        bookmarkGrid = new gridObj({
            key: 'bookmarks',
            id: 'grid',
            ajaxUrl: rlConfig['ajax_url'] + '?item=bookmarks_fetch',
            defaultSortField: 'Name',
            title: lang['bookmarks_ext_caption'],
            remoteSortable: false,
            fields: [
                {name: 'Name', mapping: 'Name', type: 'string'},
                {name: 'Key', mapping: 'Key', typr: 'string'},
                {name: 'Status', mapping: 'Status', type: 'string'},
                {name: 'ID', mapping: 'ID'},
                {name: 'Type', mapping: 'Type', type: 'string'},
                {name: 'Type_name', mapping: 'Type_name', type: 'string'},
                {name: 'Align', mapping: 'Align', type: 'string'},
                {name: 'Tpl', mapping: 'Tpl', type: 'string'},
                {name: 'Header', mapping: 'Header', type: 'string'},
                {name: 'Pages', mapping: 'Pages', type: 'string'},
            ],
            columns: [
                {
                    header: lang['ext_name'],
                    dataIndex: 'Name',
                    width: 20,
                    id: 'rlExt_item_bold'
                },{
                    header: lang['show_on_pages'],
                    dataIndex: 'Pages',
                    width: 20
                },{
                    header: lang['ext_type'],
                    dataIndex: 'Type_name',
                    width: 180,
                    fixed: true,
                    id: 'rlExt_item'
                },{
                    header: lang['bookmarks_align'],
                    dataIndex: 'Align',
                    width: 140,
                    fixed: true,
                    editor: new Ext.form.ComboBox({
                        store: [
                            ['left', lang['bookmark_left']],
                            ['center', lang['bookmark_center']],
                            ['right', lang['bookmark_right']]
                        ],
                        displayField: 'value',
                        valueField: 'key',
                        typeAhead: true,
                        mode: 'local',
                        triggerAction: 'all',
                        selectOnFocus: true
                    }),
                    renderer: function(val, data, row){
                        return row.data.Type == 'floating_bar' 
                            ? lang['ext_not_available']
                            : '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
                    }
                },{
                    header: lang['ext_block_header'],
                    dataIndex: 'Header',
                    width: 140,
                    fixed: true
                },{
                    header: lang['ext_block_style'],
                    dataIndex: 'Tpl',
                    width: 140,
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
                        selectOnFocus: true
                    }),
                    renderer: function(val, data, row){
                        return row.data.Type == 'floating_bar' 
                            ? lang['ext_not_available']
                            : '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
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
                        selectOnFocus: true
                    })
                },{
                    header: lang['ext_actions'],
                    width: 70,
                    fixed: true,
                    dataIndex: 'ID',
                    sortable: false,
                    renderer: function(id) {
                        var out = "<center>";
                        out += "<a href='" + rlUrlHome + "index.php?controller=" + controller + "&action=edit&item=" + id +"'>";
                        out += "<img class='edit' ext:qtip='" + lang['ext_edit'] + "' src='" + rlUrlHome + "img/blank.gif' />";
                        out += "</a>";
                        out += "<img class='remove' ext:qtip='" + lang['ext_delete'] + "' src='" + rlUrlHome + "img/blank.gif' data-id=" + id + " />";
                        out += "</center>";
                        
                        return out;
                    }
                }
            ]
        });

        bookmarkGrid.init();
        grid.push(bookmarkGrid.grid);

        bookmarkGrid.grid.addListener('beforeedit', function(editEvent){
            if (
                (['Align', 'Header', 'Tpl'].indexOf(editEvent.field) >= 0
                && editEvent.record.data.Type == 'floating_bar')
                || (editEvent.field == 'Header'
                && editEvent.record.data.Type == 'inline')
            ) {
                editEvent.cancel = true;
                bookmarkGrid.store.rejectChanges();
            }
        });

        // Remove handler
        $('#grid').on('click', 'center img.remove', function(){
            var id = $(this).data('id');

            Ext.MessageBox.confirm(lang['confirm'], lang['ext_notice_delete'], function(btn){
                if (btn == 'yes') {
                    var data = {
                        item: 'bookmarks_delete',
                        id: id
                    };
                    $.post(rlConfig['ajax_url'], data, function(response, status){
                        if (status == 'success' && response.status == 'OK') {
                            bookmarkGrid.reload();
                            printMessage('notice', response.message);
                        } else if (response.status == 'ERROR' && response.redirect) {
                            location.href = response.redirect;
                        } else {
                            printMessage('error', lang['system_error']);
                        }
                    }, 'json').fail(function(object, status){
                        if (status == 'abort') {
                            return;
                        }

                        printMessage('error', status);
                    }); 
                }
            });
        });
    });

    {/literal}
    </script>
    <!-- grid end -->
{/if}

<!-- bookmarks tpl end -->
