<!-- tree level tpl -->

<ul class="ctree-sc-container{if !$direct} hide{/if}">
{foreach from=$ctree_subcategories item='sub_cat'}
    <li id="ctree-catid-{$sub_cat.ID}" 
        class="{if $direct && $sub_cat.sub_categories}
                loaded&nbsp;
                {if $box_listing_type.Ctree_open_subcat}opened{/if}
            {/if} {if !empty($cache_ctree_data[$sub_cat.ID].Sub_cat)}ctree-sc{/if}">
        <img title="{$lang.category_tree_show_subcategories}" 
            class="plus-icon" 
            alt="{if $lang[$sub_cat.pName]}{$lang[$sub_cat.pName]}{else}{$sub_cat.name}{/if}" 
            src="{$rlTplBase}img/blank.gif" />
        <a {if $category.ID == $sub_cat.ID}class="current"{/if} 
            title="{if $lang[$sub_cat.pName]}{$lang[$sub_cat.pName]}{else}{$sub_cat.name}{/if}" 
            href="{buildCategoryUrl 
                    category=$sub_cat 
                    page_key=$box_listing_type.Page_key 
                    cat_postfix=$box_listing_type.Cat_postfix}">
                {$sub_cat.name}
        </a>
        {if $box_listing_type.Ctree_subcat_counter}
            <span class="count hlight">{$cache_ctree_data[$sub_cat.ID].Count|number_format}</span>
        {/if}

        {if $direct && $sub_cat.sub_categories}
            {if !$box_listing_type.Ctree_open_subcat}
                {assign var='direct' value=false}
            {/if}
            {include file=$smarty.const.RL_PLUGINS|cat:'categories_tree'|cat:$smarty.const.RL_DS|cat:'level.tpl' 
                ctree_subcategories=$sub_cat.sub_categories 
                direct=$direct}
        {/if}
    </li>
{/foreach}
</ul>

<!-- tree level tpl end -->
