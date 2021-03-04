<!-- highlight plans manager -->

{if $smarty.get.action}
    {assign var='sPost' value=$smarty.post}
    <script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/colorpicker/js/colorpicker.js"></script>

    {if $smarty.get.module}
        {assign var='module' value=$smarty.get.module}
    {/if}

    <!-- Add/Edit highlight plan -->
    {include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.m_add_highlight}
    <form id="add-highlight-plan" action="{$rlBaseC}module={$module}&action={$smarty.get.action}" method="post">
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
                <td class="name">{$lang.m_highlight_available}</td>
                <td class="field">
                    <table class="infinity monetize">
                        <tbody>
                        <tr>
                            <td>
                                <input accesskey="{$sPost.highlight_count}" type="text"  name="highlight_count" class="numeric" value="{$sPost.highlight_count}" style="width: 50px; text-align: center;" class="numeric"/>
                            </td>
                            <td>
                                <span title="{if $sPost.highlight_count_unlimited}{$lang.unset_unlimited}{else}{$lang.set_unlimited}{/if}" class="{if $sPost.highlight_count_unlimited}active{else}inactive{/if}"></span>
                                <input name="highlight_count_unlimited" type="hidden" value="{if $sPost.highlight_count_unlimited}1{else}0{/if}">
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="name"><span class="red">*</span>{$lang.m_highlight_for}</td>
                <td class="field">
                    <input type="text" name="highlight_days" value="{$sPost.highlight_days}" style="width: 50px; text-align: center;" class="numeric"/>
                    <span class="field_description_noicon">&nbsp; {$lang.m_days}</span>
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
                ajaxUrl: rlPlugins + 'monetize/admin/highlight_plans.inc.php?q=ext',
                defaultSortField: 'name',
                remoteSortable: true,
                title: {/literal}"{$lang.m_highlight_plans}"{literal},
                fields: [
                    {name: 'id', mapping: 'ID', type: 'int'},
                    {name: 'name', mapping: 'name', type: 'string'},
                    {name: 'description', mapping: 'description', type: 'string'},
                    {name: 'highlights', mapping: 'Highlights'},
                    {name: 'Days', mapping: 'Days'},
                    {name: 'price', mapping: 'Price'},
                    {name: 'Status', mapping: 'Status'}
                ],
                columns: [
                    {
                        header: {/literal}"{$lang.id}"{literal},
                        dataIndex: 'id',
                        id: 'highlight_id',
                        width: 40,
                        fixed: true
                    },{
                        header: {/literal}"{$lang.name}"{literal},
                        dataIndex: 'name',
                        id: 'rlExt_item_bold'
                    },{
                        header: {/literal}"{$lang.days}"{literal},
                        dataIndex: 'Days',
                        id: 'days_highlight',
                        width: 150,
                        fixed: true,
                        editor: new Ext.form.NumberField({
                            allowBlank: false,
                            allowDecimals: false
                        }),
                        renderer: function(val){
                            return '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
                        }
                    },{
                        header: {/literal}"{$lang.m_highlights}"{literal},
                        dataIndex: 'highlights',
                        id: 'highlights_count',
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
                        id: 'highlight_price',
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
                            out += "<a href=\"" + rlUrlHome + "index.php?controller=monetize&module=highlight_plans&action=edit&id=" + id + "\">";
                            out += "<img class='edit' ext:qtip='" + lang['ext_edit'] + "' src='" + rlUrlHome + "img/blank.gif' /></a>"
                            //delete
                            out += "<img class='remove' ext:qtip='" + lang['ext_delete'] + "'";
                            out += "src='" + rlUrlHome + "img/blank.gif' onclick='deleteHighlightPlan(" + id + ",\"" + name + "\")' />";
                            return out;
                        }
                    }
                ]
            });

            itemsGrid.init();
            grid.push(itemsGrid.grid);
        });

        /**
         * @since 1.3.0 Added - planName
         *
         * @param {number} planID
         * @param {string} planName
         */
        function deleteHighlightPlan(planID, planName) {
            monetizer.handlePlanDelete(planID, planName, 'highlight', function (response) {
                var type = (response.status == 'ok') ? 'notice' : 'error';
                printMessage(type, response.message);
                itemsGrid.init();
            });
        }
        {/literal}
    </script>
{/if}

<!-- highlight plans manager end -->
