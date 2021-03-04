<!-- template of fields with AUTO mode -->

{if $cfInfo.Mode == 'category' && $category.ID}
    {assign var='itemsExist' value=0}
{/if}

{if $filter.Items || $filter.Key == 'Category_ID'}
    {if $cfActiveFilters}
        {assign var='backUrl' value=''}
        {assign var='aFiltersUrl' value=''}

        {foreach from=$cfActiveFilters item='aFilter' key='aBaseFilterKey'}
            {assign var='aFilterKey' value=$aBaseFilterKey|replace:'_':'-'}

            {if $cfCountActiveFilters == 1}
                {if $aBaseFilterKey == $filter.Key}
                    {assign var='aFilterData' value=$cfActiveFilters[$aBaseFilterKey]}

                    {if $filter.Type == 'checkbox'}
                        {assign var='backUrl' value=''}
                    {else}
                        {assign var='itemUrl' value=$cfBaseUrl}
                    {/if}
                {else}
                    {encodeFilter filter=$aFilter assign='aFilter' key=$aBaseFilterKey filters=$cfFields}

                    {if $config.mod_rewrite}
                        {assign var='aFiltersUrl' value=$aFiltersUrl|cat:$aFilterKey|cat:':'|cat:$aFilter|cat:'/'}
                    {else}
                        {assign var='aFiltersUrl' value=$aFiltersUrl|cat:'&cf-'|cat:$aFilterKey|cat:'='|cat:$aFilter}
                    {/if}
                {/if}
            {elseif $cfCountActiveFilters > 1}
                {if $aBaseFilterKey == $filter.Key}
                    {assign var='aFilterData' value=$cfActiveFilters[$aBaseFilterKey]}

                    {if $filter.Condition && $filter.Multifield_level == 1}
                        {foreach from=$cfActiveFilters item='backFilter' key='backFilterKey'}
                            {if $backFilterKey|regex_replace:"/[a-zA-Z|0-9]+\_level[0-9]/":"" === $backFilterKey
                                && $backFilterKey != $filter.Key
                            }
                                {assign var='backFilterKey' value=$backFilterKey|replace:'_':'-'}
                                {encodeFilter filter=$backFilter assign='backFilter' key=$aBaseFilterKey filters=$cfFields}

                                {if $config.mod_rewrite}
                                    {assign var='backUrl'
                                        value=$backUrl|cat:$backFilterKey|cat:':'|cat:$backFilter|cat:'/'}
                                {else}
                                    {assign var='backUrl'
                                        value=$backUrl|cat:'&cf-'|cat:$backFilterKey|cat:'='|cat:$backFilter}
                                {/if}
                            {/if}
                        {/foreach}

                        {if !$backUrl}
                            {assign var='backUrl' value=''}
                        {/if}
                    {elseif !$filter.Multifield_level && $filter.Condition}
                        {assign var='currentLevel' value=$filter.Key|substr:-1}

                        {foreach from=$cfActiveFilters item='backFilter' key='backFilterKey'}
                            {encodeFilter filter=$backFilter assign='backFilter'}

                            {if $backFilterKey|regex_replace:"/[a-zA-Z]+\_level[0-9]/":"" !== $backFilterKey}
                                {if $currentLevel|@intval >= 2}
                                    {encodeFilter
                                        filter=$backFilter
                                        assign='backFilter'
                                        key=$backFilterKey
                                        filters=$cfFields
                                    }
                                {/if}

                                {if $backFilterKey|substr:-1 < $currentLevel && $backFilterKey != $filter.Key}
                                    {assign var='backFilterKey' value=$backFilterKey|replace:'_':'-'}

                                    {if $config.mod_rewrite}
                                        {assign var='backUrl'
                                            value=$backUrl|cat:$backFilterKey|cat:':'|cat:$backFilter|cat:'/'}
                                    {else}
                                        {assign var='backUrl'
                                            value=$backUrl|cat:'&cf-'|cat:$backFilterKey|cat:'='|cat:$backFilter}
                                    {/if}
                                {/if}
                            {else}
                                {if $backFilterKey != $filter.Key}
                                    {encodeFilter
                                        filter=$backFilter
                                        assign='backFilter'
                                        key=$backFilterKey
                                        filters=$cfFields
                                    }

                                    {assign var='backFilterKey' value=$backFilterKey|replace:'_':'-'}

                                    {if $config.mod_rewrite}
                                        {assign var='backUrl'
                                            value=$backUrl|cat:$backFilterKey|cat:':'|cat:$backFilter|cat:'/'}
                                    {else}
                                        {assign var='backUrl'
                                            value=$backUrl|cat:'&cf-'|cat:$backFilterKey|cat:'='|cat:$backFilter}
                                    {/if}
                                {/if}
                            {/if}
                        {/foreach}
                    {else}
                        {foreach from=$cfActiveFilters item='backFilter' key='backFilterKey'}
                            {if $backFilterKey != $filter.Key}
                                {encodeFilter
                                    filter=$backFilter
                                    assign='backFilter'
                                    key=$backFilterKey
                                    filters=$cfFields
                                }

                                {assign var='backFilterKey' value=$backFilterKey|replace:'_':'-'}

                                {if $config.mod_rewrite}
                                    {assign var='backUrl'
                                        value=$backUrl|cat:$backFilterKey|cat:':'|cat:$backFilter|cat:'/'}
                                {else}
                                    {assign var='backUrl'
                                        value=$backUrl|cat:'&cf-'|cat:$backFilterKey|cat:'='|cat:$backFilter}
                                {/if}
                            {else}
                                {encodeFilter filter=$backFilter assign='backFilter'}
                            {/if}
                        {/foreach}
                    {/if}
                {else}
                    {encodeFilter filter=$aFilter assign='aFilter' key=$aBaseFilterKey filters=$cfFields}

                    {if $config.mod_rewrite}
                        {assign var='aFiltersUrl' value=$aFiltersUrl|cat:$aFilterKey|cat:':'|cat:$aFilter|cat:'/'}
                    {else}
                        {assign var='aFiltersUrl' value=$aFiltersUrl|cat:'&cf-'|cat:$aFilterKey|cat:'='|cat:$aFilter}
                    {/if}
                {/if}
            {/if}
        {/foreach}
    {/if}

    {assign var='aFilterKey' value=$filter.Key|replace:'_':'-'}

    {if $filter.Key == 'Category_ID'
        && $smarty.const.CRON_FILE !== true
        && $smarty.const.SITEMAP_BUILD !== true}
        {include file=$smarty.const.RL_PLUGINS|cat:'categoryFilter/fields/categories.tpl'}
    {elseif $filter.Type == 'checkbox' || $filter.Mode == 'checkboxes'}
        {if $aFilterData}
            {assign var='aValues' value=','|explode:$aFilterData}
        {/if}

        {if $filter.Values != ''}
            {strip}
            <ul>
                {foreach from=','|explode:$filter.Values item='item' key='key' name='cFilterCheckboxes'}
                    {assign var='itemPraseKey'
                        value='category_filter+name+'|cat:$cfInfo.ID|cat:'_'|cat:$filter.Field_ID|cat:'_'|cat:$item}

                    {if !$lang[$itemPraseKey]}
                        {if $filter.Condition}
                            {assign var='itemPraseKey' value='data_formats+name+'|cat:$item}
                        {else}
                            {assign var='itemPraseKey' value='listing_fields+name+'|cat:$filter.Key|cat:'_'|cat:$item}
                        {/if}
                    {/if}

                    {assign var='itemName' value=$lang[$itemPraseKey]}

                    {if $itemName}
                        <li>
                            <label title="{$itemName}">
                                <input type="checkbox"
                                    name="cf_checkbox_{$filter.Key}[]"
                                    value="{encodeFilter filter=$itemName}"
                                    {if $item|in_array:$aValues}checked="checked"{/if} />
                                    &nbsp;{$itemName}
                            </label>
                        </li>
                    {/if}
                {/foreach}
            </ul>
            {/strip}

            {if $aFiltersUrl}
                {if $config.mod_rewrite}
                    {assign var='itemUrl' value=$cfBaseUrl|cat:$aFiltersUrl|cat:$aFilterKey}
                {else}
                    {assign var='itemUrl' value=$cfBaseUrl|cat:$aFiltersUrl|cat:'&cf-'|cat:$aFilterKey}
                {/if}
            {else}
                {if $listing_type.Links_type == 'short'}
                    {if $config.mod_rewrite}
                        {assign var='itemUrl' value=$cfBaseUrl|substr:0:-1|cat:'.html'}
                    {else}
                        {assign var='itemUrl' value=$cfBaseUrl}
                    {/if}

                    {if $config.mod_rewrite}
                        {assign var='itemUrl' value=$itemUrl|cat:$aFilterKey}
                    {else}
                        {assign var='itemUrl' value=$itemUrl|cat:'&cf-'|cat:$aFilterKey}
                    {/if}
                {else}
                    {if $config.mod_rewrite}
                        {assign var='itemUrl' value=$cfBaseUrl|cat:$aFilterKey}
                    {else}
                        {assign var='itemUrl' value=$cfBaseUrl|cat:'&cf-'|cat:$aFilterKey}
                    {/if}
                {/if}
            {/if}

            <div class="cf-apply" id="cf_checkbox_{$filter.Key}">
                <a accesskey="{$itemUrl}:[replace]{if $config.mod_rewrite}/{/if}"
                    title="{$lang.category_filter_apply_filter}"
                    href="{$itemUrl}:1"
                    {if $filter.No_index || $cfPageNoindex} rel="nofollow"{/if}>
                    {$lang.category_filter_apply_filter}
                </a>

                {if $aValues}
                    <a class="cf-remove hide"
                        href="{if $backUrl}{$cfBaseUrl}{$backUrl}{else}{$cfCancelUrl}{/if}">
                        <span class="hide">{$lang.category_filter_remove_filter}</span>
                        <img title="{$lang.category_filter_remove_filter}"
                            alt=""
                            class="remove"
                            src="{$rlTplBase}img/blank.gif" />
                    </a>
                {else}
                    <span class="hide">{$lang.category_filter_apply_filter}</span>
                {/if}
            </div>

            <script class="fl-js-dynamic">
            lang.cf_apply_filter  = '{$lang.category_filter_apply_filter}';
            lang.cf_remove_filter = '{$lang.category_filter_remove_filter}';

            $(function() {literal}{{/literal}
                categoryFilter.checkbox(
                    $('#cf_checkbox_{$filter.Key}'),
                    {if $aValues}true{else}false{/if}
                );
            {literal}});{/literal}
            </script>

            {assign var='aValues' value=''}
        {/if}
    {else}
        {strip}
        <ul>
            {foreach from=$filter.Items item='item' key='key' name='cFilterItems'}
                {assign var='itemPraseKey'
                    value='category_filter+name+'|cat:$cfInfo.ID|cat:'_'|cat:$filter.Field_ID|cat:'_'|cat:$item[$filter.Key]}

                {if !$lang[$itemPraseKey]}
                    {if $filter.Condition}
                        {assign var='itemPraseKey' value='data_formats+name+'|cat:$item[$filter.Key]}
                    {else}
                        {if $filter.Key == 'posted_by'}
                            {assign var='itemPraseKey' value='account_types+name+'|cat:$item[$filter.Key]}
                        {elseif $filter.Type == 'bool'}
                            {if $item[$filter.Key] == 1}
                                {assign var='itemPraseKey' value='yes'}
                            {else}
                                {assign var='itemPraseKey' value='no'}
                            {/if}
                        {else}
                            {assign var='itemPraseKey'
                                value='listing_fields+name+'|cat:$filter.Key|cat:'_'|cat:$item[$filter.Key]}
                        {/if}
                    {/if}
                {/if}

                {if $lang[$itemPraseKey]}
                    {assign var='itemName' value=$lang[$itemPraseKey]}
                {else}
                    {assign var='itemName' value=$item[$filter.Key]}
                {/if}

                {encodeFilter filter=$item[$filter.Key] assign='itemValue' key=$filter.Key filters=$cfFields}

                {if !$cfActiveFilters}
                    {if $config.mod_rewrite}
                        {assign var='itemUrl' value=$cfBaseUrl|cat:$aFilterKey|cat:':'|cat:$itemValue|cat:'/'}
                    {else}
                        {assign var='itemUrl' value=$cfBaseUrl|cat:'&cf-'|cat:$aFilterKey|cat:'='|cat:$itemValue}
                    {/if}
                {else}
                    {if $aFiltersUrl}
                        {if $config.mod_rewrite}
                            {assign var='itemUrl'
                                value=$cfBaseUrl|cat:$aFiltersUrl|cat:$aFilterKey|cat:':'|cat:$itemValue|cat:'/'}
                        {else}
                            {assign var='itemUrl'
                                value=$cfBaseUrl|cat:$aFiltersUrl|cat:'&cf-'|cat:$aFilterKey|cat:'='|cat:$itemValue}
                        {/if}
                    {/if}

                    {if isset($backUrl) && isset($aFilterData)}
                        {assign var='itemUrl' value=$cfBaseUrl|cat:$backUrl}

                        {if !$backUrl && $listing_type.Links_type == 'short'}
                            {assign var='itemUrl' value=$cfBaseUrl|substr:0:-1|cat:'.html'}
                        {/if}
                    {/if}
                {/if}

                {if $category.ID != 0 && $cfInfo.Mode == 'category'}
                    {assign var='categoryCount' value='Category_count_'|cat:$category.ID}
                    {assign var='itemCounter' value=$item[$categoryCount]}
                {else}
                    {if $item.Number}
                        {assign var='itemCounter' value=$item.Number}
                    {/if}
                {/if}

                {if $itemCounter}
                    {if $aFilterData == $item[$filter.Key]}
                        <li>
                            <span>{$itemName}</span>
                            <a href="{$itemUrl}">
                                <img title="{$lang.category_filter_remove_filter}"
                                    alt=""
                                    class="remove"
                                    src="{$rlTplBase}img/blank.gif" />
                            </a>
                        </li>
                    {else}
                        <li {if $filter.Items_display_limit
                                && ($smarty.foreach.cFilterItems.iteration > $filter.Items_display_limit)}class="hide"{/if}>
                            <a href="{$itemUrl}"
                                title="{$itemName}"
                                {if $filter.No_index || $cfPageNoindex} rel="nofollow"{/if}>
                                {$itemName}
                            </a>

                            &nbsp;<span class="counter">({$itemCounter})</span>
                        </li>
                    {/if}

                    {if $cfInfo.Mode == 'category' && $category.ID}
                        {math equation='x + y' x=$itemsExist y=1 assign='itemsExist'}
                    {/if}
                {/if}
            {/foreach}
        </ul>
        {/strip}

        {if $smarty.foreach.cFilterItems.total > $filter.Items_display_limit}
            <a class="dark_12 more" href="javascript://" rel="nofollow">{$lang.category_filter_show_more}</a>
        {/if}

        {if !$itemsExist && ($cfInfo.Mode == 'category' && $category.ID)}
            <span>{$lang.category_filter_no_listings}</span>
        {/if}
    {/if}
{else}
    <span>{$lang.category_filter_no_listings}</span>
{/if}

<!-- template of fields with AUTO mode end -->
