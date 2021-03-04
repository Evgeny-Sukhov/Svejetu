<!-- home page content tpl -->
    {assign var='cdt_count' value=$category_dropdown_types|@count}

    {if $cdt_count == 1}
        {assign var='spage_key' value=$listing_types[$category_dropdown_types.0].Page_key}
        {assign var='spage_path' value=$pages[$spage_key]}
    {elseif $cdt_count > 1}
        {assign var='spage_path' value=`$smarty.ldelim`type`$smarty.rdelim`}
    {/if}

    <form id=top-search class="{$spage_path.type}" accesskey="{pageUrl key='search'}#keyword_tab" method="post" action="{if $cdt_count == 0}{pageUrl key='search'}{else}{$rlBase}{if $config.mod_rewrite}{$spage_path}/{$search_results_url}.html{else}?page={$spage_path}&{$search_results_url}{/if}{/if}">
        <input type="hidden" name="action" value="search" />
        <input type="hidden" name="form" value="keyword_search" />
        <input type="hidden" name="post_form_key" value="{if $cdt_count == 1}{$category_dropdown_types.0}_{if $listing_types[$category_dropdown_types.0].Advanced_search}advanced{else}quick{/if}{/if}" />


            {assign var='any_replace' value=`$smarty.ldelim`field`$smarty.rdelim`}

            <input class="tags-autocomplete flex-fill" type="text" placeholder="{$lang.keyword_search_hint}" name="f[keyword_search]" value="{if $smarty.post.keyword_search}{$smarty.post.keyword_search}{/if}" />
            {* disable categories dropdown *}
            {*if $cdt_count > 0*}
            {if $cdt_count == 0}
                <select name="f[Category_ID]" class="cd-dropdown">
                    <option value="">{$lang.any_field_value|replace:$any_replace:$lang.category}</option>
                </select>

                <script class="fl-js-dynamic">
                var categoryDropdownTypes = {if $cdt_count == 1}'{$category_dropdown_types.0}'{else}Array('{$category_dropdown_types|@implode:"', '"}'){/if};
                var categoryDropdownData = null;
                var currentListingTypeKey = {if $smarty.request.f.type_parent_key}'{$smarty.request.f.type_parent_key}'{elseif $listing_type && $pageInfo.Controller == 'listing_type'}'{$listing_type.Key}'{else}false{/if};
                var currentCategoryParentIDs = {if $smarty.request.f.category_parent_ids}{assign var='parent_ids_reversed' value=','|explode:$smarty.request.f.category_parent_ids|@array_reverse}'{','|implode:$parent_ids_reversed}'{elseif $category.Parent_IDs}'{$category.ID},{$category.Parent_IDs}'{else}false{/if};

                {if $cdt_count > 1}
                    categoryDropdownData = new Array();
                    {foreach from=$category_dropdown_types item='dropdown_type' name='fSearchForms'}
                        {assign var='type_page_key' value=$listing_types[$dropdown_type].Page_key}

                        categoryDropdownData.push({literal} { {/literal}
                            ID: '{$dropdown_type}',
                            Key: '{$dropdown_type}',
                            Link_type: '{$listing_types[$dropdown_type].Links_type}',
                            Path: '{$pages.$type_page_key}',
                            name: '{phrase key='pages+name+lt_'|cat:$dropdown_type}',
                            Sub_cat: {$smarty.foreach.fSearchForms.iteration},
                            Advanced_search: {$listing_types[$dropdown_type].Advanced_search}
                        {literal} } {/literal});
                    {/foreach}
                {/if}

                {literal}

                flUtil.loadScript(rlConfig['tpl_base']  + 'js/categoryDropdown.js', function(){
                    $('section.horizontal-search select[name="f[Category_ID]"]').categoryDropdown({
                        listingTypeKey: categoryDropdownTypes,
                        typesData: categoryDropdownData,
                        default_listing_type_key: currentListingTypeKey,
                        default_selection_parents: currentCategoryParentIDs,
                        phrases: { {/literal}
                            no_categories_available: "{$lang.no_categories_available}",
                            select: "{$lang.any_field_value|replace:$any_replace:$lang.category}",
                            select_category: "{$lang.any_field_value|replace:$any_replace:$lang.category}"
                        {literal} }
                    });
                });

                {/literal}
                </script>
            {/if}

            {if $aHooks.search_by_distance}
                {addJS file=$smarty.const.RL_PLUGINS_URL|cat:'search_by_distance/static/lib.js'}
                {include file=$smarty.const.RL_PLUGINS|cat:'search_by_distance'|cat:$smarty.const.RL_DS|cat:'config.js.tpl'}

                <script class="fl-js-dynamic">
                var sbd_zip_field = '{$config.sbd_zip_field}';

                {literal}
                    $(function(){
                        if (typeof sbdLocationAutocomplete != 'undefined') {
                            sbdLocationAutocomplete('.horizontal-search.sbd-exists input#location_header_search', sbd_zip_field);
                        }
                    });
                    {/literal}
                    </script>

                    <input class="flex-fill"
                           type="text"
                           placeholder="{if $config.sbd_search_mode == 'mixed'}{$lang.sbd_location_search_hint}{else}{$lang.sbd_zipcode}{/if}"
                           name="f[{$config.sbd_zip_field}][zip]"
                           id="location_header_search"
                           value="{if $smarty.post.f[$config.sbd_zip_field].zip}{$smarty.post.f[$config.sbd_zip_field].zip}{elseif $geo_filter_data.applied_location}{$geo_filter_data.applied_location.name}{/if}" />
                    <input type="hidden"
                           name="f[{$config.sbd_zip_field}][distance]"
                           value="{assign var='sbd_distance' value=','|explode:$config.sbd_distance_items}{$sbd_distance.1}" />
                    <select name="f[{$config.sbd_zip_field}][distance]">
                        {foreach from=','|explode:$config.sbd_distance_items item='distance'}
                            <option {if $smarty.post.block_distance == $distance}selected="selected"{elseif $distance == $config.sbd_default_distance}selected="selected"{/if} value="{$distance}">{$distance} {if $config.sbd_units == 'miles'}{$lang.sbd_mi}{else}{$lang.sbd_km}{/if}</option>
                        {/foreach}
                    </select>

                    <input type="hidden" name="f[{$config.sbd_zip_field}][lat]" value="{if $smarty.post.f[$config.sbd_zip_field].lat}{$smarty.post.f[$config.sbd_zip_field].lat}{/if}" />
                    <input type="hidden" name="f[{$config.sbd_zip_field}][lng]" value="{if $smarty.post.f[$config.sbd_zip_field].lng}{$smarty.post.f[$config.sbd_zip_field].lng}{/if}" />
            {/if}
            {*<input class="d-block" type="submit" value="{$lang.search}" />*}
            <input class="d-block" id="top-search-bar" type="submit" value="SVE JE TU" />

    </form>


<!-- home page content tpl end -->
