<!-- bump up plans manager -->
{if $smarty.get.action}
    {assign var='sPost' value=$smarty.post}
    {include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.add_bump_up}
    <form action="{$rlBaseC}action={$smarty.get.action}" method="post">
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
                    <input type="text" name="bump_up_count" value="{$sPost.bump_up_count}" style="width: 50px; text-align: center;" class="numeric"/>
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
                ajaxUrl: rlPlugins + 'monetize/admin/bump_up_plans.inc.php?q=ext',
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
                            var out = "";
                            //edit
                            out += "<a href=\"" + rlUrlHome + "index.php?controller=bump_up_plans&action=edit&id=" + id + "\">";
                            out += "<img class='edit' ext:qtip='" + lang['ext_edit'] + "' src='" + rlUrlHome + "img/blank.gif' /></a>"
                            //delete
                            out += "<img class='remove' ext:qtip='" + lang['ext_delete'] + "'";
                            out += "src='" + rlUrlHome + "img/blank.gif' onclick='deleteBumpUpPlan(" + id + ")' />";
                            return out;
                        }
                    }
                ]
            });

            itemsGrid.init();
            grid.push(itemsGrid.grid);
        });
        function deleteBumpUpPlan(plan_id) {
            $.post(rlConfig["ajax_url"], {item: 'deleteBumpUpPlan', id: plan_id}, function (response) {
                var type = (response.status == 'ok') ? 'notice' : 'notice';
                printMessage(type,response.message);
                itemsGrid.init();
            }, 'json');
        }
        {/literal}
    </script>
{/if}

<!-- bump up plans manager end -->