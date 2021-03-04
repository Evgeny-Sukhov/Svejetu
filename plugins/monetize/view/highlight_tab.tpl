<!-- highlight tab tpl -->

<div id="area_highlight" class="tab_area {if $smarty.request.info != 'highlight'}hide{/if}">

    {include file=$mConfig.view|cat:$smarty.const.RL_DS|cat:'monetize_stat_tab.tpl' type='highilight'}

    {if $hInfo.highlightListings}
        {include file=$mConfig.view|cat:$smarty.const.RL_DS|cat:'recent_listing.tpl' listings=$hInfo.highlightListings type='highilight'}
    {else}
        {$lang.m_no_highlighted}
    {/if}
</div>

<!-- highlight tab tpl end -->