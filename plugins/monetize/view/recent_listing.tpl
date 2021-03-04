{if $type === 'highilight'}
    {assign var='block_id' value='recently-highlighted'}
    {assign var='block_phrase' value=$lang.m_recently_highlighted}
{else}
    {assign var='block_id' value='recently-bumped-up'}
    {assign var='block_phrase' value=$lang.recently_bumped_up}
{/if}

<div id="{$block_id}">
    {include file='blocks'|cat:$smarty.const.RL_DS|cat:'divider.tpl' name=$block_phrase}

    <script>var listings_map = new Array();</script>
    <section id="listings" class="{if $smarty.const.IS_ESCORT === true}grid{else}list{/if} row monetize-recent-tab">
        {foreach from=$listings item='listing'}
            {if !defined('IS_ESCORT') && !$tpl_settings.name|strstr:'modern' }
                <div class="bump_date col-sm-4">
                    {include file='blocks'|cat:$smarty.const.RL_DS|cat:'divider.tpl' name=$listing.Date|date_format:$smarty.const.RL_DATE_FORMAT}
                </div>
            {/if}

            {include file='blocks'|cat:$smarty.const.RL_DS|cat:'listing.tpl'}
        {/foreach}
        <section>
</div>