<!-- categories_icons plugin -->

{if $cat.Icon && ($config.categories_icons_position == 'left' || $config.categories_icons_position == 'top')}
    {if $config.rl_version|version_compare:"4.4.0" >= 0}
        {assign var='lt_tmp' value=$listing_types[$cat.Type]}
    {else}
        {if isset($listing_types.$type)}
            {assign var='lt_tmp' value=$listing_types.$type}
        {else}
            {assign var='lt_tmp' value=$listing_type}
        {/if}
    {/if}
    {assign var='lt_page_path' value='lt_'|cat:$lt_tmp.Key}
	<div style="{if $config.categories_icons_position == 'left'}display: inline;{else}display: block;{/if}">
		<a class="category cat_icon" title="{$cat.name}" href="{$rlBase}{if $config.mod_rewrite}{$pages[$lt_page_path]}/{$cat.Path}{if $lt_tmp.Cat_postfix}.html{else}/{/if}{else}?page={$pages[$lt_page_path]}&category={$cat.ID}{/if}">
			<img src="{$smarty.const.RL_URL_HOME}files/{$cat.Icon}" title="{$cat.name}" alt="{$cat.name}" />
		</a>
	</div>
{/if}

<!-- end categories_icons plugin -->