<!-- template of fields with MULTI (auto, ranges, slider, text) mode -->

{if $filter.Items || $filter.Mode == 'text'}
    {if $cfActiveFilters}
        {assign var='backUrl' value=''}
        {assign var='aFiltersUrl' value=''}

        {foreach from=$cfActiveFilters item='aFilter' key='aBaseFilterKey'}
            {assign var='aFilterKey' value=$aBaseFilterKey|replace:'_':'-'}

            {if $cfCountActiveFilters == 1}
                {if $aBaseFilterKey == $filter.Key}
                    {assign var='aFilterData' value=$cfActiveFilters[$aBaseFilterKey]}
                    {assign var='itemUrl' value=$cfBaseUrl}
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

                    {foreach from=$cfActiveFilters item='backFilter' key='backFilterKey'}
                        {if $backFilterKey != $filter.Key}
                            {assign var='backFilterKey' value=$backFilterKey|replace:'_':'-'}
                            {encodeFilter filter=$backFilter assign='backFilter' key=$backFilterKey filters=$cfFields}

                            {if $config.mod_rewrite}
                                {assign var='backUrl' value=$backUrl|cat:$backFilterKey|cat:':'|cat:$backFilter|cat:'/'}
                            {else}
                                {assign var='backUrl' value=$backUrl|cat:'&cf-'|cat:$backFilterKey|cat:'='|cat:$backFilter}
                            {/if}
                        {/if}
                    {/foreach}
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

    {if $filter.Mode != 'slider'}
        {if $filter.Mode == 'text'}
            <div class="submit-cell">
                {if $aFiltersUrl}
                    {if $config.mod_rewrite}
                        {assign var='itemUrl' value=$cfBaseUrl|cat:$aFiltersUrl|cat:$aFilterKey}
                    {else}
                        {assign var='itemUrl' value=$cfBaseUrl|cat:$aFiltersUrl|cat:'&cf-'|cat:$aFilterKey}
                    {/if}
                {else}
                    {if $listing_type.Links_type == 'short'}
                        {assign var='itemUrl' value=$cfBaseUrl|substr:0:-1|cat:'.html'}

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

                <div class="field search-item three-field">{strip}
                    {if $aFilterData}
                        {assign var='filterValue' value='-'|explode:$aFilterData}
                    {/if}

                    <input placeholder="{$lang.from}"
                        class="numeric"
                        type="text"
                        name="from"
                        size="9"
                        maxlength="12"
                        value="{$filterValue.0}">
                    <input placeholder="{$lang.to}"
                        class="numeric"
                        type="text"
                        name="to"
                        size="9"
                        maxlength="12"
                        value="{$filterValue.1}">

                    {if $filter.Key == $config.price_tag_field}
                        {if $plugins.currencyConverter}
                            {if $curConv_code
                                && ($curConv_rates.$curConv_code.Symbol || $curConv_rates.$curConv_code.Code)
                            }
                                <span class="currency wauto">
                                    {if $curConv_rates.$curConv_code.Symbol}
                                        {$curConv_rates.$curConv_code.Symbol}
                                    {else}
                                        {$curConv_rates.$curConv_code.Code}
                                    {/if}
                                </span>
                            {/if}
                        {else}
                            {assign var='currencySource' value='currency'|df}
                            {if $currencySource|@count == 1}
                                <span class="wauto">
                                    {foreach from=$currencySource item='currency'}
                                        {$lang[$currency.pName]}
                                        {break}
                                    {/foreach}
                                </span>
                            {elseif $currencySource|@count > 1}
                                <select title="{$lang.currency}" name="currency" style="width: 62px">
                                    <option value="0">{$lang.any|replace:'-':''}</option>
                                    {foreach from=$currencySource item='currency'}
                                        <option value="{$currency.Key}"
                                            {if $cfActiveFilters.currency == $currency.Key}selected="selected"{/if}>
                                            {$lang[$currency.pName]}
                                        </option>
                                    {/foreach}
                                </select>
                            {/if}
                        {/if}
                    {/if}
                {/strip}</div>

                <div class="cf-apply" id="cf_checkbox_{$filter.Key}">
                    {if $cfCountActiveFilters >= 2 && $cfActiveFilters.currency}
                        {assign var='backUrl' value=$backUrl|regex_replace:'/currency:[a-zA-Z_]+\/?/':''}
                        {assign var='itemUrl' value=$itemUrl|regex_replace:'/currency:[a-zA-Z_]+\/?/':''}
                    {/if}

                    <a accesskey="{$itemUrl}:[replace]"
                        title="{$lang.category_filter_apply_filter}"
                        href="javascript://"
                        {if $filter.No_index || $cfPageNoindex} rel="nofollow"{/if}>
                        {$lang.category_filter_apply_filter}
                    </a>

                    {if $aFilterData}
                        <a class="cf-remove hide" href="{if $backUrl}{$cfBaseUrl}{$backUrl}{else}{$cfCancelUrl}{/if}">
                            <span>{$lang.category_filter_remove_filter}</span>
                            <img title="{$lang.category_filter_remove_filter}"
                                alt=""
                                class="remove"
                                src="{$rlTplBase}img/blank.gif" />
                        </a>
                    {else}
                        <span class="hide">{$lang.category_filter_apply_filter}</span>
                    {/if}
                </div>

                <script class="fl-js-dynamic">{literal}
                $(function() {
                    categoryFilter.textFields(
                        $('#cf_checkbox_{/literal}{$filter.Key}{literal}'),
                        {/literal}{if $aFilterData}true{else}false{/if}{literal}
                    );

                    var $currency = $('#cf_checkbox_{/literal}{$filter.Key}{literal}')
                                        .closest('.submit-cell')
                                        .find('span.currency');
                    $('#currency_selector > span > span').on('DOMSubtreeModified', function(){
                        if ($(this).text() != $currency.text()) {
                            $currency.text($(this).text());
                        }
                    });
                });
                {/literal}</script>
            </div>
        {else}
            <ul>
                {foreach from=$filter.Items item='item' key='key' name='cFilterItems'}
                    {assign var='itemPraseKey'
                        value='category_filter+name+'|cat:$cfInfo.ID|cat:'_'|cat:$filter.Field_ID|cat:'_'|cat:$key}

                    {if $lang[$itemPraseKey]}
                        {assign var='itemName' value=$lang[$itemPraseKey]}
                    {else}
                        {assign var='itemName' value=$key}
                    {/if}

                    {encodeFilter filter=$key assign='itemValue'}

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

                    {assign var='countPrefix' value='-1'}

                    {if $category.ID != 0 && $cfInfo.Mode == 'category'}
                        {assign var='countPrefix' value='Category_count_'|cat:$category.ID}
                    {/if}

                    {assign var='itemCounter' value=$item[$countPrefix]}

                    {if $itemCounter}
                        {if $aFilterData}
                            {if $aFilterData == $key}
                                <li>
                                    <span>{$itemName}</span>
                                    <a href="{$itemUrl}">
                                        <img title="{$lang.category_filter_remove_filter}"
                                            alt=""
                                            class="remove"
                                            src="{$rlTplBase}img/blank.gif" />
                                    </a>
                                </li>
                            {/if}
                        {else}
                            <li {if $filter.Items_display_limit
                                    && ($smarty.foreach.cFilterItems.iteration > $filter.Items_display_limit)}class="hide"{/if}>
                                <a href="{$itemUrl}"
                                    title="{$itemName}"
                                    {if $filter.No_index || $cfPageNoindex} rel="nofollow"{/if}>
                                    {$itemName}
                                </a>

                                <span class="counter">({$itemCounter})</span>
                            </li>
                        {/if}
                    {/if}
                {/foreach}
            </ul>
        {/if}
    {else}
        {if $category.ID == 0}
            {assign var='cfCategoryID' value=-1}
        {else}
            {if $cfInfo.Mode == 'category'}
                {assign var='cfCategoryID' value='Category_count_'|cat:$category.ID}
            {else}
                {assign var='cfCategoryID' value=-1}
            {/if}
        {/if}

        {assign var='cF' value=$filter.Key}
        {assign var='itemMin' value=$filter.Minimum}
        {assign var='itemMax' value=$filter.Maximum}

        {if !$cfActiveFilters || ($cfActiveFilters && !$aFiltersUrl)}
            {if $config.mod_rewrite}
                {assign var='itemUrl'
                    value=$cfBaseUrl|cat:$aFilterKey|cat:':'|cat:$itemMin|cat:'-'|cat:$itemMax|cat:'/'}
            {else}
                {assign var='itemUrl'
                    value=$cfBaseUrl|cat:'&cf-'|cat:$aFilterKey|cat:'='|cat:$itemMin|cat:'-'|cat:$itemMax}
            {/if}
        {else}
           {if $aFiltersUrl}
                {if $config.mod_rewrite}
                    {assign var='itemUrl'
                        value=$cfBaseUrl|cat:$aFiltersUrl|cat:$aFilterKey|cat:':'|cat:$itemMin|cat:'-'|cat:$itemMax|cat:'/'}
                {else}
                    {assign var='itemUrl'
                        value=$cfBaseUrl|cat:$aFiltersUrl|cat:'&cf-'|cat:$aFilterKey|cat:'='|cat:$itemMin|cat:'-'|cat:$itemMax}
                {/if}
            {/if}
        {/if}

        <div class="cf-slider">
            <input type="hidden" value="{$filter.Slider_minimum};{$filter.Slider_maximum}" name="slider_{$filter.Key}" />
        </div>

        <div class="cf-apply" id="cf_link_{$filter.Key}">
            <a title="{$lang.category_filter_apply_filter}"
                href="{$itemUrl}"
                {if $filter.No_index || $cfPageNoindex} rel="nofollow"{/if}>
                {$lang.category_filter_apply_filter}
            </a>

            <span class="empty"></span>
            <span class="counter"></span>
        </div>

        <script class="fl-js-dynamic">
        $(function() {literal}{{/literal}
            var countsData = [], dimension = '', format = {literal}{}{/literal}, minExist, maxExist;
            {foreach from=$filter.Items item='number' key='counts'}{strip}
                {if $number[$cfCategoryID]}countsData.push([{$number[$cfCategoryID]}, {$counts|round}]);{/if}
            {/strip}{/foreach}

            {if $filter.Type == 'price'
                && $plugins.currencyConverter
                && ($smarty.session.curConv_code || $smarty.cookies.curConv_code)
            }
                dimension = '&nbsp;' + currencyConverter.rates[currencyConverter.config['currency']][1][0];
            {/if}

            {if $filter.Condition == 'years'}
                format = {literal}{format: '', locale: 'us'}{/literal};
            {/if}

            minExist = {if $filter.Second_condition && $cfCountActiveFilters > 1}{$filter.Slider_minimum}{else}-1{/if};
            maxExist = {if $filter.Second_condition && $cfCountActiveFilters > 1}{$filter.Slider_maximum}{else}-1{/if};

            categoryFilter.slider({literal}{{/literal}
                minExist  : minExist,
                maxExist  : maxExist,
                countsData: countsData,
                key       : '{$filter.Key}'.replace('-', '\-'),
                dimension : dimension,
                from      : {$itemMin},
                to        : {$itemMax},
                step      : {$filter.Step},
                format    : format
            {literal}});
        }{/literal});</script>
    {/if}
{else}
    <span>{$lang.category_filter_no_listings}</span>
{/if}

<!-- template of fields with MULTI (auto, ranges, slider) mode end -->
