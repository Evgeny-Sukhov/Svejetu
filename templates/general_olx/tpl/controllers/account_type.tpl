<!-- accounts tpl -->

{if $account_type}
	<!-- account details -->
	{if $account}
		<!-- account listings -->
		{if !empty($listings)}
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'grid_navbar.tpl'}
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'grid.tpl'}

			<!-- paging block -->
            {if $config.mod_rewrite}
                {paging calc=$pInfo.calc total=$listings|@count current=$pInfo.current perPage=$config.listings_per_page custom=$account.Own_address customSubdomain=$config.account_wildcard}
            {else}
                {paging calc=$pInfo.calc total=$listings|@count current=$pInfo.current perPage=$config.listings_per_page var="id" url=$account.ID full=true}
            {/if}
			<!-- paging block end -->
		
		{else}
			<div class="info">{$lang.no_dealer_listings}</div>
		{/if}
		<!-- account listings end -->
	{else}
		{if $alphabet_dealers}
			{assign var='dealers' value=$alphabet_dealers}
		{/if}

        {if $config.map_module}
        <script>var accounts_map_data = new Array();</script>
        {/if}

		<!-- dealers list -->
		{if $dealers}
			{include file='blocks'|cat:$smarty.const.RL_DS|cat:'grid_navbar_account.tpl'}

			<script>var accounts_map = new Array();</script>
			<section id="accounts" class="grid row">
				{foreach from=$dealers item='dealer' key='key' name='dealersF'}
					{include file='blocks'|cat:$smarty.const.RL_DS|cat:'dealer.tpl'}

                    {if $dealer.Loc_latitude && $dealer.Loc_longitude && $config.map_module}
                    <script class="fl-js-dynamic">
                    accounts_map_data.push({$smarty.ldelim}
                        latLng: [{$dealer.Loc_latitude}, {$dealer.Loc_longitude}],
                        preview: {$smarty.ldelim}
                            component: 'account',
                            id: {$dealer.ID}
                        {$smarty.rdelim}
                    {$smarty.rdelim});
                    </script>
                {/if}
				{/foreach}
			</section>

            {if $config.map_module}
                <section id="accounts_map" class="hide"></section>

                {mapsAPI assign='mapAPI'}

                <script>
                rlConfig['map_api_css'] = {$mapAPI.css|@json_encode};
                rlConfig['map_api_js'] = {$mapAPI.js|@json_encode};
                </script>
            {/if}

			{if $alphabet_dealers}
				{paging calc=$pInfo.calc_alphabet total=$dealers|@count current=$pInfo.current per_page=$config.dealers_per_page url=$char var='character'}
			{else}
				{paging calc=$pInfo.calc total=$dealers|@count current=$pInfo.current per_page=$config.dealers_per_page url=$search_results_url}
			{/if}
		{else}
			<div class="info">{$lang.no_dealers}</div>
		{/if}
		<!-- dealers list end -->
	{/if}
{/if}

<!-- accounts tpl end -->
