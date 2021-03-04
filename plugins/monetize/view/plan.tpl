{strip}
    <!-- bump up plan tpl -->
    {assign var='item_disabled' value=false}
    {if $plan.Limit > 0 && $plan.Using == 0 && $plan.Using != ''}
        {assign var='item_disabled' value=true}
    {/if}

    <li id="plan_{$plan.ID}" class="plan" data-isfree="{if $plan.Price}false{else}true{/if}">
        <div class="frame{if $plan.Color} colored{/if}{if $item_disabled} disabled{/if}" {if $plan.Color}style="background-color: #{$plan.Color};border-color: #{$plan.Color};"{/if}>
            <span class="name">{$plan.name}</span>
        <span class="price">
            {if !$plan.Price}
                {$lang.free}
            {else}
                    {if $plan.Price > 0}
                        {if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
                        {$plan.Price}
                        {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
                    {elseif $plan.Price == '-1'}
                        {$lang.bump_up_balance}
                    {/if}
            {/if}
        </span>
        <span title="{$lang.plan_type}" class="type">
            {assign var='l_type' value=$plan.Type|cat:'_plan_short'}
        </span>
                {if $plan.Type != 'highlight'}
                    <span title="{$lang.listing_live}" class="count">
                        {if $plan.Price}
                            {if $plan.Bump_ups}{$plan.Bump_ups} {else}{$lang.unlimited} {/if}
                            {$lang.bumpups}
                        {/if}
                    </span>
                {else}
                    {if $plan.by_date}
                        <span class="count">{$lang.total}: {$plan.total} {$lang.m_highlights}</span>
                    {else}
                        <span title="{$lang.m_highlights}" class="count">
                            {if $plan.Price}
                                {if $plan.Highlights}{$plan.Highlights} {else}{$lang.unlimited} {/if}
                                {$lang.m_highlights}
                            {/if}
                        </span>
                            <span title="{$lang.m_days_active}" class="count">
                            {if $plan.Days}{$lang.m_highlighted_for} {$plan.Days} {$lang.days}{else}{$lang.ulimited}{/if}
                        </span>
                    {/if}

                {/if}

            {if $plan.description}
                <span class="description">
                <img class="qtip middle-bottom" alt="" title="{$plan.description}" id="fd_{$field.Key}" src="{$rlTplBase}img/blank.gif" />
            </span>
            {/if}

            <div class="selector">
                <label {if $item_disabled}class="hint" title="{$lang.plan_limit_using_deny}"{/if}><input class="multiline" {if $item_disabled}disabled="disabled" {/if} type="radio" name="plan" value="{$plan.ID}" {if $plan.ID == $smarty.post.plan && !$item_disabled}checked="checked"{/if} /></label>
            </div>
        </div>
    </li>
    <!-- bump up plan tpl end -->
{/strip}
