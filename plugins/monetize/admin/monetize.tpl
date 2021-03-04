<!-- Monetize manager -->

<!-- Navigation bar -->
<div id="nav_bar">
    {strip}
        <a id="assign-block-toggle-btn" href="javascript:void(0)" onclick="show('credits-assign-block', '#action-block div');" class="button_bar">
            <span class="left"></span>
            <span class="center_build">{$lang.m_assign_credits}</span>
            <span class="right"></span>
        </a>
    {/strip}
    {if $smarty.get.module != 'highlight_plans'}
        <a href="{$rlBaseC}module=highlight_plans" class="button_bar"><span class="left"></span><span class="center_list">{$lang.m_highlight_plans}</span><span class="right"></span></a>
        <a href="{$rlBaseC}action=add" class="button_bar"><span class="left"></span><span class="center_add">{$lang.add_bump_up}</span><span class="right"></span></a>
    {else}
        <a href="{$rlBaseC}" class="button_bar"><span class="left"></span><span class="center_list">{$lang.bumpup_plans}</span><span class="right"></span></a>
        <a href="{$rlBaseC}module=highlight_plans&action=add" class="button_bar"><span class="left"></span><span class="center_add">{$lang.m_add_highlight}</span><span class="right"></span></a>
    {/if}
</div>

<div id="action-block">
    <div id="credits-assign-block" class="hide">
        {include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.m_assign_credits}
            <div class="block-message hide">
                {$lang.loading}
            </div>
            <div class="block-body">
                <table>
                    <tr>
                        <td valign="top">
                            <table class="form">
                                <tr>
                                    <td class="name">{$lang.username}</td>
                                    <td class="field">
                                        <input id="account" type="text">
                                    </td>
                                </tr>
                                <tr class="higlight-input hide">
                                    <td class="name">{$lang.m_highlights}</td>
                                    <td class="field">
                                        <div class="two-fields">
                                            <input class="assign-input numeric" type="text">
                                            <span class="assign-label"> {$lang.m_select_highlight_plan} </span>
                                            <select id="highlight-plans" class="monetize-plan" name="highlight-plan"></select>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="bumpup-input hide">
                                    <td class="name">{$lang.bumpups}</td>
                                    <td class="field">
                                        <input class="assign-input numeric"  type="text">
                                        <span class="assign-label"> {$lang.select_bump_up} </span>
                                        <select id="bumpup-plans" class="monetize-plan" name="bumpup-plan"></select>
                                    </td>
                                </tr>

                                <tr>
                                    <td></td>
                                    <td class="field nowrap">
                                        <input type="button" disabled="disabled" class="button" value="{$lang.m_assign}" id="assign-btn" />
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        {include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
    </div>
    <div id="plans-reassign-block" class="hide">
        {include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.m_assign_block_title}
        <div class="assign-info-block"></div>

        <div class="assign-main-block">
            <span>{$lang.m_remove_method_notice}</span>
            <ul class="assign-options">
                <li class="monetize-delete-row">
                    <label>
                        <input checked="checked" id="monetize-delete-plan" type="radio" name="monetize-reassing" value="0">
                        {$lang.m_direct_remove}
                    </label>
                </li>
                <li class="monetize-reassign-row hide">
                    <label>
                        <input  type="radio" name="monetize-reassing" value="1">
                        {$lang.m_remove_with_assign}
                    </label>
                </li>

                <li id="reassign-to" class="hide">
                    {$lang.m_highlight_plan}
                    <select></select>
                </li>
            </ul>

            <div id="bottom_buttons">
                <input class="simple reassign-credits-button" data-type="fetchPlans" type="button" value="{$lang.go}">
                <a class="cancel" href="javascript:void(0)" onclick="show('plans-reassign-block', '#action-block div');">{$lang.cancel}</a>
            </div>
        </div>
        {include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
    </div>
</div>
<!-- Navigation bar end-->
{if !$smarty.get.module}
    <!-- Bump up -->
    {if $smarty.get.action}
        {assign var='sPost' value=$smarty.post}
        <script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/colorpicker/js/colorpicker.js"></script>

        {include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.add_bump_up}
        <form id="create-monetize-plan" action="{$rlBaseC}action={$smarty.get.action}" method="post">
            {if $smarty.get.action == 'edit'}
                <input type="hidden" value="{$sPost.id}" name="plan_id">
            {/if}
            <table class="form">
                <tr>
                    <td class="name"><span class="red">*</span>{$lang.name}</td>
                    <td class="field">
                        {if $allLangs|@count > 1}
                            <ul class="tabs">
                                {foreach from=$allLangs item='language' name='langF'}
                                    <li lang="{$language.Code}" {if $smarty.foreach.langF.first}class="active"{/if}>{$language.name}</li>
                                {/foreach}
                            </ul>
                        {/if}
                        {foreach from=$allLangs item='language' name='langF'}
                            {if $allLangs|@count > 1}<div class="tab_area{if !$smarty.foreach.langF.first} hide{/if} {$language.Code}">{/if}
                            <input type="text" name="name[{$language.Code}]" value="{$sPost.name[$language.Code]}" maxlength="350"/>
                            {if $allLangs|@count > 1}
                                <span class="field_description_noicon">{$lang.name} (<b>{$language.name}</b>)</span>
                                </div>
                            {/if}
                        {/foreach}
                    </td>
                </tr>
                <tr>
                    <td class="name">{$lang.description}</td>
                    <td class="field">
                        {if $allLangs|@count > 1}
                            <ul class="tabs">
                                {foreach from=$allLangs item='language' name='langF'}
                                    <li lang="{$language.Code}"
                                        {if $smarty.foreach.langF.first}class="active"{/if}>{$language.name}</li>
                                {/foreach}
                            </ul>
                        {/if}
                        {foreach from=$allLangs item='language' name='langF'}
                            {if $allLangs|@count > 1}<div class="tab_area{if !$smarty.foreach.langF.first} hide{/if} {$language.Code}">{/if}
                            <textarea name="description[{$language.Code}]">{$sPost.description[$language.Code]}</textarea>
                            {if $allLangs|@count > 1}
                                </div>
                            {/if}
                        {/foreach}
                        {*<input type="text" id="description" name="description[en]" value="{$sPost.description.en}">*}
                    </td>
                </tr>
                <tr>
                    <td class="name">{$lang.label_bg_color}</td>
                    <td class="field">
                        <div style="padding: 0 0 5px 0;">
                            <input type="hidden" name="color" value="{$sPost.color}" />
                            <div id="colorSelector" class="colorSelector"><div style="background-color: #{if $sPost.color}{$sPost.color}{else}d8cfc4{/if}"></div></div>
                        </div>

                        <script type="text/javascript">
                            var bg_color = '{if $sPost.color}{$sPost.color}{else}d8cfc4{/if}';
                            {literal}

                            $(document).ready(function(){

                                $('#colorSelector').ColorPicker({
                                    color: '#'+bg_color,
                                    onShow: function (colpkr) {
                                        $(colpkr).fadeIn(500);
                                        return false;
                                    },
                                    onHide: function (colpkr) {
                                        $(colpkr).fadeOut(500);
                                        return false;
                                    },
                                    onChange: function (hsb, hex, rgb) {
                                        $('#colorSelector div').css('backgroundColor', '#' + hex);
                                        $('input[name=color]').val(hex);
                                    }
                                });

                            });

                            {/literal}
                        </script>
                    </td>
                </tr>
                <tr>
                    <td class="name">{$lang.price}</td>
                    <td class="field">
                        <input type="text" name="price" value="{$sPost.price}" class="numeric" style="width: 50px; text-align: center;"/> <span class="field_description_noicon">&nbsp;{$config.system_currency}</span>
                    </td>
                </tr>
                <tr>
                    <td class="name">{$lang.bumpups_available}</td>
                    <td class="field">
                        <table class="infinity monetize">
                            <tbody>
                            <tr>
                                <td>
                                    <input accesskey="{$sPost.bump_up_count}" type="text"  name="bump_up_count" class="numeric" value="{$sPost.bump_up_count}" style="width: 50px; text-align: center;" class="numeric"/>
                                </td>
                                <td>
                                    <span title="{if $sPost.bump_up_count_unlimited}{$lang.unset_unlimited}{else}{$lang.set_unlimited}{/if}" class="{if $sPost.bump_up_count_unlimited}active{else}inactive{/if}"></span>
                                    <input name="bump_up_count_unlimited" type="hidden" value="{if $sPost.bump_up_count_unlimited}1{else}0{/if}">
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="name">{$lang.status}</td>
                    <td class="value">
                        <select id="badword_status" name="status" class="login_input_select lang_add">
                            <option value="active" {if $sPost.status == 'active'}selected="selected"{/if}>{$lang.active}</option>
                            <option value="approval" {if $sPost.status == 'approval'}selected="selected"{/if}>{$lang.approval}</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td class="value">
                        <input id="add_badword_button" class="button" type="submit" name="{$smarty.get.action}"
                               value="{if $smarty.get.action == 'add'}{$lang.add}{else}{$lang.edit}{/if}"/>
                        <a class="cancel" href="{$rlBaseC}">{$lang.bw_cancel}</a>
                    </td>
                </tr>
            </table>
        </form>
        {include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}

    {else}
        <div id="grid"></div>
        <script type="text/javascript">

            {literal}
            $(document).ready(function () {
                itemsGrid = new gridObj({
                    key: 'data_items',
                    id: 'grid',
                    ajaxUrl: rlPlugins + 'monetize/admin/monetize.inc.php?q=ext',
                    defaultSortField: 'name',
                    remoteSortable: true,
                    title: {/literal}"{$lang.bumpup_plans}"{literal},
                    fields: [
                        {name: 'id', mapping: 'ID', type: 'int'},
                        {name: 'name', mapping: 'name', type: 'string'},
                        {name: 'description', mapping: 'description', type: 'string'},
                        {name: 'bump_ups', mapping: 'Bump_ups'},
                        {name: 'price', mapping: 'Price'},
                        {name: 'Status', mapping: 'Status'}
                    ],
                    columns: [
                        {
                            header: {/literal}"{$lang.ID} ID"{literal},
                            dataIndex: 'id',
                            id: 'bump_up_id',
                            width: 40,
                            fixed: true
                        },{
                            header: {/literal}"{$lang.name}"{literal},
                            dataIndex: 'name',
                            id: 'rlExt_item_bold'
                        }, {
                            header: {/literal}"{$lang.bumpups}"{literal},
                            dataIndex: 'bump_ups',
                            id: 'bump_up_count',
                            width: 150,
                            fixed: true,
                            editor: new Ext.form.NumberField({
                                allowBlank: false,
                                allowDecimals: false
                            }),
                            renderer: function(val){
                                return '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
                            }
                        }, {
                            header:{/literal}"{$lang.price}"{literal},
                            dataIndex: 'price',
                            id: 'bump_up_price',
                            css: 'font-weight: bold;',
                            width: 150,
                            fixed: true,
                            editor: new Ext.form.NumberField({
                                allowBlank: false,
                                allowDecimals: true
                            }),
                            renderer: function(val){
                                return '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
                            }
                        },{
                            header: lang['ext_status'],
                            dataIndex: 'Status',
                            width: 100,
                            fixed: true,
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
                            width: 70,
                            fixed: true,
                            dataIndex: 'Key',
                            sortable: false,
                            renderer: function (val, obj, row) {
                                var id = row.data.id;
                                var name = row.data.name;

                                var out = "";
                                //edit
                                out += "<a href=\"" + rlUrlHome + "index.php?controller=monetize&action=edit&id=" + id + "\">";
                                out += "<img class='edit' ext:qtip='" + lang['ext_edit'] + "' src='" + rlUrlHome + "img/blank.gif' /></a>"
                                //delete
                                out += "<img class='remove' ext:qtip='" + lang['ext_delete'] + "'";
                                out += "src='" + rlUrlHome + "img/blank.gif' onclick='deleteBumpUpPlan(" + id + ",\"" + name + "\")' />";
                                return out;
                            }
                        }
                    ]
                });

                itemsGrid.init();
                grid.push(itemsGrid.grid);
            });

            /**
             * Delete bump up plan
             *
             * @since 1.3.0
             *
             * @param {number} planID   - Monetizer plan ID
             * @param {string} planName - Removing monetizer plan name
             */
            function deleteBumpUpPlan(planID, planName)
            {
                monetizer.deletePlan(planID, 'bumpup', function (response) {
                    var type = (response.status == 'ok') ? 'notice' : 'error';
                    printMessage(type, response.message);
                    itemsGrid.init();
                });
            }
            {/literal}
        </script>
    {/if}

{else}

    {include file=$mConfig.a_path|cat:$smarty.const.RL_DS|cat:$smarty.get.module|cat:'.tpl'}
{/if}
<script class="fl-js-dynamic">
    var phrase_set_unlimited = "{$lang.set_unlimited}";
    var phrase_unset_unlimited = "{$lang.unset_unlimited}";

    var monetizeLang = [];
    monetizeLang['m_something_went_wrong'] = '{$lang.bump_up_error}';
    monetizeLang['m_credits_assigned'] = '{$lang.m_credits_assigned}';
    monetizeLang['m_cant_assign_highlight_credits'] = '{$lang.m_cant_assign_highlight_credits}';
    monetizeLang['m_cant_assign_bumpup_credits'] = '{$lang.m_cant_assign_bumpup_credits}';
    monetizeLang['select_bump_up'] = '{$lang.select_bump_up}';
    monetizeLang['m_select_highlight_plan'] = '{$lang.m_select_highlight_plan}';
    monetizeLang['m_plan_has_assigned_to'] = '{$lang.m_plan_has_assigned_to}';

    var monetizeConfig = [];
    monetizeConfig['less_than_460'] = {if $config.rl_version|version_compare:"4.6.0 < 0" < 0}true{else}false{/if};

    var creditAssign = new monetizeCreditAssignClass();
    creditAssign.init();

    {literal}
    $(document).ready(function () {
        var $addHighlightForm = $('#add-highlight-plan');
        $addHighlightForm.submit(function () {
            $addHighlightForm.submit(function () {
                return false;
            });
        });

        var $monetizePlanForm = $('#create-monetize-plan');
        $monetizePlanForm.submit(function () {
            $monetizePlanForm.submit(function () {
                return false;
            });
        });
    });
    {/literal}
</script>
<!-- Monetize -->
