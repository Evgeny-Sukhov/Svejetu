<!-- listing categories tpl -->
<script type="text/javascript" src="{$smarty.const.RL_LIBS_URL}jquery/jquery.caret.js"></script>

<!-- navigation bar -->
<div id="nav_bar">
    {rlHook name='apTplCategoriesNavBar'}

    {if !isset($smarty.get.action)}
        <a onclick="show('search', '#action_blocks div');" href="javascript:void(0)" class="button_bar"><span class="left"></span><span class="center_search">{$lang.search}</span><span class="right"></span></a>
    {/if}
    
    {if $aRights.$cKey.add && !$smarty.get.action}
        <a href="{$rlBaseC}action=add" class="button_bar"><span class="left"></span><span class="center_add">{$lang.add_category}</span><span class="right"></span></a>
    {/if}
    
    {if $smarty.get.action == build}
        {assign var='find_cat' value=`$smarty.ldelim`category`$smarty.rdelim`}
        {if $smarty.get.form != 'submit_form'}
            <a title="{$lang.build_submit_form|replace:$find_cat:$category_info.name}" href="{$rlBase}index.php?controller=categories&amp;action=build&amp;form=submit_form&amp;key={$category_info.Key}" class="button_bar"><span class="left"></span><span class="center_build">{$lang.submit_form}</span><span class="right"></span></a>
        {/if}
        
        {if $smarty.get.form != 'short_form'}
            <a title="{$lang.build_short_form|replace:$find_cat:$category_info.name}" href="{$rlBase}index.php?controller=categories&amp;action=build&amp;form=short_form&amp;key={$category_info.Key}" class="button_bar"><span class="left"></span><span class="center_build">{$lang.short_form}</span><span class="right"></span></a>
        {/if}
        
        {if $smarty.get.form != 'listing_title'}
            <a title="{$lang.build_listing_title_form|replace:$find_cat:$category_info.name}" href="{$rlBase}index.php?controller=categories&amp;action=build&amp;form=listing_title&amp;key={$category_info.Key}" class="button_bar"><span class="left"></span><span class="center_build">{$lang.listing_title_form}</span><span class="right"></span></a>
        {/if}
        
        {if $smarty.get.form != 'featured_form'}
            <a title="{$lang.build_featured_form|replace:$find_cat:$category_info.name}" href="{$rlBase}index.php?controller=categories&amp;action=build&amp;form=featured_form&amp;key={$category_info.Key}" class="button_bar"><span class="left"></span><span class="center_build">{$lang.featured_form}</span><span class="right"></span></a>
        {/if}
    {/if}
    
    <a href="{$rlBase}index.php?controller={$smarty.get.controller}" class="button_bar"><span class="left"></span><span class="center_list">{$lang.categories_list}</span><span class="right"></span></a>
</div>
<!-- navigation bar end -->

<div id="action_blocks">

    {if !isset($smarty.get.action)}
        <!-- search -->
        <div id="search" class="hide">
        {include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_start.tpl' block_caption=$lang.search}
        
        <form method="post" onsubmit="return false;" id="search_form" action="">
            <table class="form">
            <tr>
                <td class="name">{$lang.name}</td>
                <td class="field">
                    <input type="text" id="search_name" />
                </td>
            </tr>
            
            <tr>
                <td class="name">{$lang.listing_type}</td>
                <td class="field">
                    <select id="search_type" style="width: 200px;">
                    <option value="">- {$lang.all} -</option>
                    {foreach from=$listing_types item='l_type'}
                        <option value="{$l_type.Key}">{$l_type.name}</option>
                    {/foreach}
                    </select>
                    
                    <script type="text/javascript">
                    {literal}
                    
                    $(document).ready(function(){
                        $('select#search_type').change(function(){
                            var type = $(this).val();
                            
                            if ( !type )
                            {
                                $('select#search_parent option').show();
                            }
                            else
                            {
                                $('select#search_parent option:not(:first)').hide();
                                $('select#search_parent option:first').attr('selected', true);
                                $('select#search_parent option.type_'+type).show();
                            }
                        });
                    });
                    
                    {/literal}
                    </script>
                </td>
            </tr>
            
            <tr>
                <td class="name">{$lang.parent}</td>
                <td class="field">
                    <select id="search_parent" style="width: 200px;">
                    <option value="">- {$lang.all} -</option>
                    {foreach from=$parent_cats_list item='item' key='key'}
                        <option {if $item.margin && $item.margin != 5}style="margin-left: {$item.margin}px;"{/if} class="type_{$item.Type}{if $item.Level == 0} highlight_opt{/if}" value="{$item.ID}">{$lang[$item.pName]}</option>
                    {/foreach}
                    </select>
                </td>
            </tr>
            
            <tr>
                <td class="name">{$lang.locked}</td>
                <td class="field" id="search_locked_td">
                    <label title="{$lang.unmark}"><input title="{$lang.unmark}" type="radio" id="locked_uncheck" value="" /> ...</label>
                    <label><input type="radio" name="search_locked" value="yes" /> {$lang.yes}</label>
                    <label><input type="radio" name="search_locked" value="no" /> {$lang.no}</label>
                    
                    <script type="text/javascript">
                    {literal}
                    $('#locked_uncheck').click(function(){
                        $('#search_locked_td input').attr('checked', false);
                    });
                    {/literal}
                    </script>
                </td>
            </tr>
            
            {rlHook name='apTplCategoriesSearch'}
            
            <tr>
                <td class="name">{$lang.status}</td>
                <td class="field">
                    <select id="search_status" style="width: 200px;">
                        <option value="">- {$lang.all} -</option>
                        <option value="active">{$lang.active}</option>
                        <option value="approval">{$lang.approval}</option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <td></td>
                <td class="field">
                    <input type="submit" class="button" value="{$lang.search}" id="search_button" />
                    <input type="button" class="button" value="{$lang.reset}" id="reset_search_button" />
                
                    <a class="cancel" href="javascript:void(0)" onclick="show('search')">{$lang.cancel}</a>
                </td>
            </tr>
            
            </table>
        </form>
        
        {include file='blocks'|cat:$smarty.const.RL_DS|cat:'m_block_end.tpl'}
        </div>
        
        <script type="text/javascript">
        var remote_filters = new Array();
        {if $smarty.get.type}
            remote_filters.push( 'action||search' );
            remote_filters.push( 'Type||{$smarty.get.type}' );
        {/if}
        
        {literal}
        
        var search = new Array();
        var cookie_filters = new Array();

        $(document).ready(function(){
            
            if ( readCookie('categories_sc') || remote_filters.length > 0 )
            {
                $('#search').show();
                cookie_filters = remote_filters.length > 0 ? remote_filters : readCookie('categories_sc').split(',');

                for (var i in cookie_filters)
                {
                    if ( typeof(cookie_filters[i]) == 'string' )
                    {
                        var item = cookie_filters[i].split('||');
                        if ( item[0] != 'undefined' && item[0] != '' )
                        {
                            if ( item[0] == 'Lock' )
                            {
                                $('#search input').each(function(){
                                    var val = item[1] == 1 ? 'yes' : 'no';
                                    if ( $(this).attr('name') == 'search_locked' && $(this).val() == val )
                                    {
                                        $(this).attr('checked', true);
                                    }
                                });
                            }
                            else
                            {
                                if ( item[0] == 'Parent_ID' )
                                {
                                    item[0] = 'parent';
                                }
                                
                                $('#search_'+item[0].toLowerCase()).selectOptions(item[1]);
                            }
                        }
                    }
                }
            }
            
            $('#search_form').submit(function(){
                
                createCookie('categories_pn', 0, 1);
                search = new Array();
                search.push( new Array('action', 'search') );
                search.push( new Array('Name', $('#search_name').val()) );
                search.push( new Array('Type', $('#search_type').val()) );              
                search.push( new Array('Parent_ID', $('#search_parent').val()) );
                
                {/literal}{rlHook name='apTplCategoriesSearchJS'}{literal}
                
                if ( $('input[name=search_locked]:checked').length > 0 )
                {
                    search.push( new Array('Lock', $('input[name=search_locked]:checked').val() == 'yes'? 1 : 0) );
                }
                search.push( new Array('Status', $('#search_status').val()) );

                // save search criteria
                var save_search = new Array();
                for(var i in search)
                {
                    if ( search[i][1] != '' && typeof(search[i][1]) != 'undefined'  )
                    {
                        save_search.push(search[i][0]+'||'+search[i][1]);
                    }
                }
                createCookie('categories_sc', save_search, 1);
                
                categoriesGrid.filters = search;
                categoriesGrid.reload();
            });
            
            $('#reset_search_button').click(function(){
                eraseCookie('categories_sc');
                categoriesGrid.reset();
                
                $("#search select option[value='']").attr('selected', true);
                $("#search input[type=text]").val('');
                $("#search input").each(function(){
                    if ( $(this).attr('type') == 'radio' )
                    {
                        $(this).attr('checked', false);
                    }
                });
            });
            
        });
        
        {/literal}
        </script>
        <!-- search end -->
    {/if}
    
</div>

{include file='blocks'|cat:$smarty.const.RL_DS|cat:'builder'|cat:$smarty.const.RL_DS|cat:'builder.tpl' no_groups=$no_groups}
