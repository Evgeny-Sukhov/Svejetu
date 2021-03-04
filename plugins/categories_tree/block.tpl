<!-- category tree -->

{assign var='types' value=','|explode:$types}

{if !$box_categories}
    {assign var='box_categories' value=$categories}
{/if}

{foreach from=$types item='type'}
    {assign var='box_listing_type' value=$listing_types.$type}

    {if $types|@count > 1}
        {include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' 
            name=$box_listing_type.name 
            id='addcatblock'|cat:$box_listing_type.Key}
    {/if}

    {if $box_categories.$type}
        {if $category.ID > 0 && $box_listing_type.Ctree_child_only}
            {math assign='ctree_cbc_count' equation='count-2' count=$bread_crumbs|@count}
            {assign var='ctree_cbc' value=$bread_crumbs[$ctree_cbc_count]}
            <div class="ctree-move-top">
                <a title="{$lang.category_tree_go_back}" 
                    href="{strip}{$rlBase}
                        {if $config.mod_rewrite}
                            {$ctree_cbc.path}

                            {if $ctree_cbc.category}
                                {if $box_listing_type.Cat_postfix}.html{else}/{/if}
                            {else}
                                .html
                            {/if}
                        {else}
                            ?page={$ctree_cbc.path}
                        {/if}{/strip}">
                    {$lang.category_tree_go_back}
                </a>
            </div>
        {/if}
    
        <ul class="ctree-container {if $box_listing_type.Ablock_show_subcats}ctree-allow-sc{/if}" 
            id="ctree-container-{$type}">
        {foreach from=$box_categories.$type item='cat' name='fCats'}
            <li id="ctree-catid-{$cat.ID}" 
                class="loaded 
                {if ($category.ID == $cat.ID && $box_listing_type.Ctree_child_only && $cat.sub_categories) 
                    || ($cat.sub_categories && $box_listing_type.Ctree_open_subcat)}
                    opened
                {/if}&nbsp;
                {if !empty($cat.sub_categories) && $box_listing_type.Ablock_show_subcats}ctree-sc{/if}">
                <img title="{$lang.category_tree_show_subcategories}" 
                    class="plus-icon" 
                    alt="{$lang.category_tree_show_subcategories}" 
                    src="{$rlTplBase}img/blank.gif" />

                {rlHook name='tplPreCategory'}

                {if $category.ID == $cat.ID && $box_listing_type.Ctree_child_only}
                    <span class="current">{$lang[$cat.pName]}</span>
                {else}
                    <a {if $category.ID == $cat.ID}class="current"{/if} 
                        title="{if $lang[$cat.pTitle]}{$lang[$cat.pTitle]}{else}{$lang[$cat.pName]}{/if}" 
                        href="{strip}
                            {$rlBase}

                            {if $config.mod_rewrite}
                                {$pages[$box_listing_type.Page_key]}/{$cat.Path}
                                {if $box_listing_type.Cat_postfix}.html{else}/{/if}
                            {else}
                                ?page={$pages[$box_listing_type.Page_key]}&category={$cat.ID}
                            {/if}{/strip}">
                        {$lang[$cat.pName]}
                    </a>
                {/if}

                {if $box_listing_type.Cat_listing_counter}
                    <span class="count hlight">{$cat.Count|number_format}</span>
                {/if}

                {rlHook name='tplPostCategory'}

                {assign var='direct' value=false}
                {if $cat.ID|in_array:$ctree_bc 
                    || $box_listing_type.Ctree_open_subcat 
                    || ($category.ID == $cat.ID && $box_listing_type.Ctree_child_only)}
                    {assign var='direct' value=true}
                {/if}

                {include file=$smarty.const.RL_PLUGINS|cat:'categories_tree'|cat:$smarty.const.RL_DS|cat:'level.tpl' 
                    ctree_subcategories=$cat.sub_categories 
                    direct=$direct}
            </li>
        {/foreach}
        </ul>

        <script class="fl-js-dynamic">
        var ctree_bc = new Array();
        {if $ctree_bc && !$box_listing_type.Ctree_child_only}
            {foreach from=$ctree_bc item='cb_item'}
                ctree_bc.push({$cb_item});
            {/foreach}
        {/if}
        var ctree_progress = false;
        {literal}
        
        $(document).ready(function(){
            $('#ctree-container-{/literal}{$type}{literal}').parent().attr('style', false);
            $('#ctree-container-{/literal}{$type}{literal}').closest('div.fieldset').parent().attr('style', false);

            ctreeOpen{/literal}{$type}{literal}();

            if (ctree_bc.length > 0) {
                $('#ctree-catid-'+ctree_bc[0]).addClass('opened');
                if ( ctree_bc[1] ) {
                    $('#ctree-catid-'+ctree_bc[1]).find('img.plus-icon:first').trigger('click');
                }
            }

            // Adapt box for customScrollbar function
            var $box = $('#ctree-container-{/literal}{$type}{literal}');

            if ($box.closest('.mCustomScrollbar').length) {
                $box.closest('.side_block.categories-box-nav').addClass('tree');
            }
        });
        
        var ctreeOpen{/literal}{$type}{literal} = function(){
            $('#ctree-container-{/literal}{$type}{literal} li.ctree-sc > img.plus-icon')
                .off('click')
                .on('click', function(){
                var self = this;
                if ($(this).parent().hasClass('opened')) {
                    $(this).parent().find('ul').fadeOut(function(){
                        $(self).parent().removeClass('opened');
                    });
                } else {
                    if ($(this).parent().hasClass('loaded')) {
                        $(this).parent().find('ul').fadeIn(function(){
                            $(self).parent().addClass('opened');
                        });
                    } else {
                        if (!ctree_progress) {
                            var id = $(this).parent().attr('id').split('-')[2];
                            ctree_progress = true;

                            $.post(
                                rlConfig['ajax_url'],
                                {
                                    mode: 'ctreeOpen',
                                    item: {
                                        id    : id,
                                        type  : '{/literal}{$type}{literal}',
                                        cat_id: {/literal}{if $category.ID}{$category.ID}{else}0{/if}{literal}
                                    },
                                    lang: rlLang
                                },
                                function(response){
                                    if (response && (response.status || response.message)) {
                                        if (response.status == 'OK' && response.data) {
                                            ctree_progress = false;

                                            var $catContainer = $('#ctree-catid-' + id);
                                            $catContainer.append(response.data);
                                            $catContainer.addClass('opened loaded');
                                            $catContainer.find('ul').removeClass('hide');

                                            ctreeOpen{/literal}{$type}{literal}();

                                            // open last selected category
                                            if (ctree_bc.length > 0) {
                                                for (var i in ctree_bc) {
                                                    var $catContainer = $('#ctree-catid-' + ctree_bc[i]);

                                                    if (!$catContainer.hasClass('opened')) {
                                                        $catContainer.find('img.plus-icon').trigger('click');
                                                    }
                                                }
                                            }
                                        } else {
                                            printMessage('error', response.message);
                                        }
                                    }
                                },
                                'json'
                            );
                        }
                    }
                }
            });
        }
        {/literal}
        </script>
    {else}
        <div class="text-notice">{$lang.listing_type_no_categories}</div>
    {/if}
    
    {if $types|@count > 1}
        {include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}
    {/if}
    
{/foreach}

<!-- category tree end -->
