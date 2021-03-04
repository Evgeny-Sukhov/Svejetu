<!-- bumpup tab tpl -->

<div id="area_bump_up" class="tab_area {if $smarty.request.info != 'bump_up'}hide{/if}">
    {include file=$mConfig.view|cat:$smarty.const.RL_DS|cat:'monetize_stat_tab.tpl' type='bumpup'}

    {if $buInfo.bumpedUpListings}
        {include file=$mConfig.view|cat:$smarty.const.RL_DS|cat:'recent_listing.tpl' listings=$buInfo.bumpedUpListings type='bumpup'}
    {else}
        {$lang.no_bumped_up}
    {/if}
</div>

<!-- bumpup tab tpl end -->