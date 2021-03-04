<!-- My package item listing info (monetize) -->

{if $package.Bumpups}
    <span title="{$lang.bumpups}" class="count">
        {$lang.bumpups}: <span class="highlight"> {if $package.Bumpups > 0}{$package.Bumpups}{else}{$lang.unlimited}{/if}</span>
    </span>
{/if}

{if $package.Highlight}
    <span title="{$lang.m_highlights}" class="count">
        {$lang.m_highlights}: <span class="highlight">{if $package.Highlight > 0}{$package.Highlight}{else}{$lang.unlimited}{/if}</span>
    </span>
    <span title="{$lang.m_highlighted_for}" class="count">
        {$lang.m_highlighted_for}: <span class="highlight"> {$package.Days_highlight} {$lang.days}</span>
    </span>
{/if}

<!-- My package item listing info (monetize) end -->
