<!-- statistics block -->

{if $statistics_block}
	{foreach from=$statistics_block item='stat_item' key='lt_key'}
        {assign var='values' value=','|explode:$listing_types.$lt_key.Arrange_values}

		<table class="stats">
		<tr>
			<td class="stat-caption text-overflow"{if !$listing_types.$lt_key.Arrange_field} colspan="2"{/if}>{$listing_types.$lt_key.name}</td>
			{if $listing_types.$lt_key.Arrange_field}
				{foreach from=$values item='s_column' name='scolsF'}
				{assign var='column' value='stats+name+'|cat:$lt_key|cat:'_column'|cat:$s_column}
				<td class="column text-overflow">{$lang.$column}</td>
				{/foreach}
			{/if}
		</tr>
		<tr>
			<td class="dotted"><a class="block_bg" href="{pageUrl key='listings'}#{$lt_key}_tab">{$lang.total}</a></td>
			{if $listing_types.$lt_key.Arrange_field}
				{foreach from=$values item='s_column' name='scolsF'}
					<td class="counter">{$stat_item.total.$s_column}</td>
				{/foreach}
			{else}
				<td class="counter">{$stat_item.total}</td>
			{/if}
		</tr>
		{if ($stat_item.today && !$listing_types.$lt_key.Arrange_field) || ($listing_types.$lt_key.Arrange_field && $stat_item.today.total)}
		<tr>
			<td class="dotted"><a class="block_bg" href="{pageUrl key='listings'}#{$lt_key}_tab">{$lang.today}</a></td>
			{if $listing_types.$lt_key.Arrange_field}
				{foreach from=$values item='s_column' name='scolsF'}
					<td class="counter">{$stat_item.today.$s_column}</td>
				{/foreach}
			{else}
				<td class="counter">{$stat_item.today}</td>
			{/if}
		</tr>
		{/if}
		</table>
	{/foreach}
{else}
	{$lang.statistics_isnot_available}
{/if}

<!-- statistics block -->
