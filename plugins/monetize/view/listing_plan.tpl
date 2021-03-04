<!-- bumpup listing plan tpl -->

{if $plan.Bumpups}
    <span title="{$lang.bumpups}" class="count">
        {if $plan.Bumpups > 0}{$plan.Bumpups} {else}{$lang.unlimited} {/if}
        {$lang.bumpups}
    </span>
{/if}
{if $plan.Highlight}
    <span title="{$lang.m_highlights}" class="count">
        {if $plan.Highlight > 0}{$plan.Highlight} {else}{$lang.unlimited} {/if}
        {$lang.m_highlights}
    </span>
    <span title="{$lang.m_highlighted_for}" class="count">
        {$lang.m_highlighted_for} {$plan.Days_highlight} {$lang.days}
    </span>
{/if}

<!-- bumpup listing plan tpl end -->
