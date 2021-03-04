<!-- category filter box -->

<span class="expander"></span>
<div class="filter-area">
    {if $cfFields}
        {if $cfClearFiltersLink}
            <div class="clear-filters">
                <a href="{$cfCancelUrl}">
                    {$lang.category_filter_remove_filters}
                    <img style="margin: 0 7px;"
                        title="{$lang.category_filter_remove_filter}"
                        alt=""
                        class="remove"
                        src="{$rlTplBase}img/blank.gif" />
                </a>
            </div>
        {/if}

        {assign var='cfMultiModeTypes' value='number|mixed|price'}

        {foreach from=$cfFields item='filter'}
            {include file='blocks/fieldset_header.tpl' id='cf_'|cat:$filter.Key name=$lang[$filter.pName]}

            {if $cfMultiModeTypes|strpos:$filter.Type !== false
                || ($filter.Condition == 'years' && $filter.Mode == 'slider' || $filter.Mode == 'text')
            }
                {include file=$smarty.const.RL_PLUGINS|cat:'categoryFilter/fields/multi_mode.tpl'}
            {else}
                {include file=$smarty.const.RL_PLUGINS|cat:'categoryFilter/fields/auto_mode.tpl'}
            {/if}

            {include file='blocks/fieldset_footer.tpl'}
        {/foreach}

        <script class="fl-js-dynamic">
        rlConfig.links_type   = '{$listing_type.Links_type}';
        lang.cf_apply_filter  = '{$lang.category_filter_apply_filter}';
        lang.cf_remove_filter = '{$lang.category_filter_remove_filter}';

        {literal}
        $(function() {
            // restore correct url
            if (rlConfig.links_type == 'short') {
                $('.cf-apply[id*="cf_checkbox_"]').each(function () {
                    var $filterLink = $(this).find('a:not(.cf-remove)');

                    $filterLink.attr('accesskey', $filterLink.attr('accesskey').replace('.html', '/'));
                    $filterLink.attr('href', $filterLink.attr('href').replace('.html', '/'));
                });
            }

            categoryFilter.moreFilters();
        });
        {/literal}</script>
    {else}
        {$lang.category_filter_no_fields_added}
    {/if}
</div>

<!-- category filter box end -->
