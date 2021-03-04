<div id="{$type}-statistic" class="col-sm-12 m-statistic ">
    <div class="bu-info">
    {if $type === 'highilight'}
        <span class="credits-count">{$hInfo.highlights}</span>
            <span class="credits-text small">
                {$lang.bp_credits_available} <br>
                {if $hInfo.highlights > 0}
                    {$lang.bp_last_purchase}: <span class="date">{$hInfo.last_purchased|date_format:$smarty.const.RL_DATE_FORMAT}</span>
                {/if}
            </span>
        </div>

    {if $hInfo.link}
        <a href="{$hInfo.link}" class="button">{if $smarty.const.IS_ESCORT === true}{$lang.m_do_highlight_escort}{else}{$lang.m_highlight_something}{/if}</a>
    {/if}

    {elseif $type === 'bumpup'}
        <span class="credits-count">{$buInfo.bump_ups}</span>
            <span class="credits-text small">
                {$lang.bp_credits_available} <br>

                {if $buInfo.bump_ups > 0}
                    {$lang.bp_last_purchase}: <span class="date">{$buInfo.last_purchased|date_format:$smarty.const.RL_DATE_FORMAT}</span>
                {/if}
            </span>
        </div>
        {if $buInfo.link}
            <a href="{$buInfo.link}" class="button">{if $smarty.const.IS_ESCORT === true}{$lang.m_do_bump_up_escort}{else}{$lang.do_bump_up}{/if}</a>
        {/if}
    {/if}
</div>
