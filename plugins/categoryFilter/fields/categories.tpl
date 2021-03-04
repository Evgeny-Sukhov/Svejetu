<!-- categories block tpl -->

{if $category.ID > 0 && $smarty.const.REALM != 'admin'}
    {foreach from=$categories item='baseCategory' name='fCats'}
        {if $cfCategoryCounts}
            {foreach from=$cfCategoryCounts item='cfCategory' key='cf_category_key'}
                {if $cfCategory.Category_ID == $baseCategory.ID && $cfCategory.Number}
                    {assign var='cfCategoryCount' value=$cfCategory.Number}
                    {break}
                {else}
                    {assign var='cfCategoryCount' value=false}
                {/if}
            {/foreach}
        {else}
            {assign var='cfCategoryCount' value=$baseCategory.Count}
        {/if}

        {assign var='countExist' value=false}
        {if $filter.Items}
            {if $cfCategoryCount}
                {assign var='countExist' value=true}
                {break}
            {/if}
        {else}
            {assign var='countExist' value=true}
        {/if}
    {/foreach}

    {math assign='bcCount' equation='count-2' count=$bread_crumbs|@count}

    <div{if !empty($categories) && $countExist} style="padding: 0 0 15px 0;"{/if}>
        {$category.name}

        {if ($cfInfo.Mode == 'search_results' || $cfInfo.Mode == 'field_bound_boxes')
            && $cfActiveFilters.category_id}
            {if $cfCountActiveFilters > 1}
                {assign var='aFiltersUrl' value=false}

                {foreach from=$cfActiveFilters item='aFilter' key='aFilterKey'}
                    {assign var='aFilterKey' value=$aFilterKey|replace:'_':'-'}

                    {if $aFilterKey != 'category_id'}
                        {encodeFilter filter=$aFilter assign='aFilter'}

                        {if $config.mod_rewrite}
                            {assign var='aFiltersUrl'
                                value=$aFiltersUrl|cat:$aFilterKey|cat:':'|cat:$aFilter|cat:'/'}
                        {else}
                            {assign var='aFiltersUrl'
                                value=$aFiltersUrl|cat:'&cf-'|cat:$aFilterKey|cat:'='|cat:$aFilter}
                        {/if}
                    {/if}
                {/foreach}

                {assign var='cfBackCategoryUrl' value=$cfBaseUrl|cat:$aFiltersUrl}
            {else}
                {if $category.Parent_ID}
                    {assign var='cfBackCategoryUrl' value=$cfBaseUrl}
                {else}
                    {assign var='cfBackCategoryUrl' value=$cfCancelUrl}
                {/if}
            {/if}

            {if $category.Parent_ID}
                {if $config.mod_rewrite}
                    {assign var='cfBackCategoryUrl'
                        value=$cfBackCategoryUrl|cat:'category-id:'|cat:$category.Parent_ID|cat:'/'}
                {else}
                    {assign var='cfBackCategoryUrl'
                        value=$cfBackCategoryUrl|cat:'&cf-category-id='|cat:$category.Parent_ID}
                {/if}
            {/if}

            <a href="{$cfBackCategoryUrl}">
                <img title="{$lang.category_filter_remove_filter}"
                    alt=""
                    class="remove"
                    src="{$rlTplBase}img/blank.gif" />
            </a>
        {else}
            <a href="{strip}{$rlBase}
                {if $config.mod_rewrite}
                    {$pageInfo.Path}
                    {if $bread_crumbs[$bcCount].Path}/{$bread_crumbs[$bcCount].Path}
                        {if $listing_type.Cat_postfix}.html{else}/{/if}
                    {else}
                        .html
                    {/if}
                {else}
                    ?page={$pageInfo.Path}
                    {if $bread_crumbs[$bcCount].ID}
                        &category={$bread_crumbs[$bcCount].ID}
                    {/if}
                {/if}{/strip}">
                <img title="{$lang.category_filter_remove_filter}"
                    alt=""
                    class="remove"
                    src="{$rlTplBase}img/blank.gif" />
            </a>
        {/if}
    </div>
{/if}

{if !empty($categories)}
    {rlHook name='browsePreCategories'}

    {if $cfActiveFilters}
        {assign var='aFiltersUrl' value=false}

        {foreach from=$cfActiveFilters item='aFilter' key='aFilterKey'}
            {assign var='aFilterKey' value=$aFilterKey|replace:'_':'-'}

            {if $aFilterKey != 'category_id'}
                {encodeFilter filter=$aFilter assign='aFilter' key=$aFilterKey filters=$cfFields}

                {if $config.mod_rewrite}
                    {assign var='aFiltersUrl' value=$aFiltersUrl|cat:$aFilterKey|cat:':'|cat:$aFilter|cat:'/'}
                {else}
                    {assign var='aFiltersUrl' value=$aFiltersUrl|cat:'&cf-'|cat:$aFilterKey|cat:'='|cat:$aFilter}
                {/if}
            {/if}
        {/foreach}
    {/if}

    <div class="cat-tree-cont limit-height{if $category.ID > 0} subcat-cont{/if}">
        <ul class="cat-tree">{strip}
        {foreach from=$categories item='baseCategory' name='fCats'}
            {if $cfCategoryCounts}
                {foreach from=$cfCategoryCounts item='cfCategory' key='cf_category_key'}
                    {if $cfCategory.Category_ID == $baseCategory.ID && $cfCategory.Number}
                        {assign var='cfCategoryCount' value=$cfCategory.Number}
                        {break}
                    {else}
                        {assign var='cfCategoryCount' value=false}
                    {/if}
                {/foreach}
            {else}
                {assign var='cfCategoryCount' value=$baseCategory.Count}
            {/if}

            {assign var='countExist' value=false}
            {if $filter.Items}
                {if $cfCategoryCount}
                    {assign var='countExist' value=true}
                {/if}
            {else}
                {assign var='countExist' value=true}
            {/if}

            {if $countExist}
                <li>
                    {rlHook name='tplPreCategory'}

                    {if $listing_type.Cat_show_subcats}
                        <span class="toggle">
                            {if !empty($baseCategory.sub_categories)}+{/if}
                        </span>
                    {/if}

                    {if $cfInfo.Mode == 'search_results' || $cfInfo.Mode == 'field_bound_boxes'}
                        {if $aFiltersUrl}
                            {if $config.mod_rewrite}
                                {assign var='cfCategoryUrl'
                                    value=$cfBaseUrl|cat:'category-id:'|cat:$baseCategory.ID|cat:'/'|cat:$aFiltersUrl}
                            {else}
                                {assign var='cfCategoryUrl'
                                    value=$cfBaseUrl|cat:'&cf-category-id='|cat:$baseCategory.ID|cat:$aFiltersUrl}
                            {/if}
                        {else}
                            {if $config.mod_rewrite}
                                {assign var='cfCategoryUrl'
                                    value=$cfBaseUrl|cat:'category-id:'|cat:$baseCategory.ID|cat:'/'}
                            {else}
                                {assign var='cfCategoryUrl'
                                    value=$cfBaseUrl|cat:'&cf-category-id='|cat:$baseCategory.ID}
                            {/if}
                        {/if}
                    {else}
                        {categoryUrl id=$baseCategory.ID assign='cfCategoryUrl'}

                        {if $aFiltersUrl}
                            {if $config.mod_rewrite}
                                {if $listing_types[$baseCategory.Type].Cat_postfix}
                                    {assign var='cfCategoryUrl' value=$cfCategoryUrl|replace:'.html':''|cat:'/'|cat:$aFiltersUrl}
                                {else}
                                    {assign var='cfCategoryUrl' value=$cfCategoryUrl|cat:$aFiltersUrl}
                                {/if}
                            {else}
                                {assign var='cfCategoryUrl' value=$cfCategoryUrl|cat:$aFiltersUrl}
                            {/if}
                        {/if}
                    {/if}

                    <a title="{if $lang[$baseCategory.pTitle]}{$lang[$baseCategory.pTitle]}{else}{$baseCategory.name}{/if}"
                        href="{$cfCategoryUrl}"
                        {if $filter.No_index || $cfPageNoindex} rel="nofollow"{/if}>
                        {$baseCategory.name}
                    </a>

                    {if $listing_type.Cat_listing_counter}
                        &nbsp;<span class="counter">({if $cfCategoryCount}{$cfCategoryCount}{else}0{/if})</span>
                    {/if}

                    {rlHook name='tplPostCategory'}

                    {if !empty($baseCategory.sub_categories) && $listing_type.Cat_show_subcats}
                        <ul class="sub-cats">
                            {foreach from=$baseCategory.sub_categories item='baseSubCategory' name='subCatF'}
                                {if $listing_type.Cat_listing_counter}
                                    {if $cfCategoryCounts}
                                        {foreach from=$cfCategoryCounts item='cf_subcategory'}
                                            {if $cf_subcategory.Category_ID == $baseSubCategory.ID
                                                && $cf_subcategory.Number
                                            }
                                                {assign var='cfSubCategoryCount' value=$cf_subcategory.Number}
                                                {break}
                                            {else}
                                                {assign var='cfSubCategoryCount' value=false}
                                            {/if}
                                        {/foreach}
                                    {else}
                                        {assign var='cfSubCategoryCount' value=$baseSubCategory.Count}
                                    {/if}
                                {/if}

                                {if !$filter.Items || ($filter.Items && $cfSubCategoryCount)}
                                    <li>
                                        {rlHook name='tplPreSubCategory'}

                                        {if $cfInfo.Mode == 'search_results' || $cfInfo.Mode == 'field_bound_boxes'}
                                            {if $aFiltersUrl}
                                                {if $config.mod_rewrite}
                                                    {assign var='cfSubCategoryUrl'
                                                        value=$cfBaseUrl|cat:'category-id:'|cat:$baseSubCategory.ID|cat:'/'|cat:$aFiltersUrl}
                                                {else}
                                                    {assign var='cfSubCategoryUrl'
                                                        value=$cfBaseUrl|cat:'&cf-category-id='|cat:$baseSubCategory.ID|cat:'&'|cat:$aFiltersUrl}
                                                {/if}
                                            {else}
                                                {if $config.mod_rewrite}
                                                    {assign var='cfSubCategoryUrl'
                                                        value=$cfBaseUrl|cat:'category-id:'|cat:$baseSubCategory.ID|cat:'/'}
                                                {else}
                                                    {assign var='cfSubCategoryUrl'
                                                        value=$cfBaseUrl|cat:'&cf-category-id='|cat:$baseSubCategory.ID}
                                                {/if}
                                            {/if}
                                        {else}
                                            {categoryUrl id=$baseSubCategory.ID assign='cfSubCategoryUrl'}

                                            {if $aFiltersUrl}
                                                {if $config.mod_rewrite}
                                                    {if $listing_types[$baseCategory.Type].Cat_postfix}
                                                        {assign var='cfSubCategoryUrl' value=$cfSubCategoryUrl|replace:'.html':''|cat:'/'|cat:$aFiltersUrl}
                                                    {else}
                                                        {assign var='cfSubCategoryUrl' value=$cfSubCategoryUrl|cat:$aFiltersUrl}
                                                    {/if}
                                                {else}
                                                    {assign var='cfSubCategoryUrl' value=$cfSubCategoryUrl|cat:$aFiltersUrl}
                                                {/if}
                                            {/if}
                                        {/if}

                                        <a title="{if $lang[$baseSubCategory.pTitle]}{$lang[$baseSubCategory.pTitle]}{else}{$baseSubCategory.name}{/if}"
                                            href="{$cfSubCategoryUrl}"
                                            {if $filter.No_index || $cfPageNoindex} rel="nofollow"{/if}>
                                            {$baseSubCategory.name}
                                        </a>

                                        {if $listing_type.Cat_listing_counter}
                                            &nbsp;<span class="counter">({if $cfSubCategoryCount}{$cfSubCategoryCount}{else}0{/if})</span>
                                        {/if}
                                    </li>
                                {/if}
                            {/foreach}
                        </ul>
                    {/if}
                </li>
            {/if}
        {/foreach}
        {/strip}</ul>

        <div class="cat-toggle hide" accesskey="{$filter.Items_display_limit}">...</div>
    </div>

    {rlHook name='browsePostCategories'}
{/if}

<!-- categories block tpl -->
