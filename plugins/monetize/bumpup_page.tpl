{if $smarty.get.id && !isset($smarty.get.completed)}
     {if $plans}
        <form id="form-checkout" method="post" action="{$rlBase}{if $config.mod_rewrite}{$pageInfo.Path}.html?id={if $smarty.get.id}{$smarty.get.id}{else}{$account_info.ID}{/if}{else}?page={$pageInfo.Path}&amp;id={if $smarty.get.id}{$smarty.get.id}{else}{$account_info.ID}{/if}{/if}">
            <input type="hidden" name="buy_bumpup" value="true"/>
            <input type="hidden" name="from_post" value="1"/>
            <!-- select a bump up plan -->
            {if $plans|@count > 1}{assign var='fieldset_phrase' value=$lang.select_bump_up}{else}{assign var='fieldset_phrase' value=''}{/if}
            {include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_header.tpl' name=$fieldset_phrase}

            <div class="plans-container">
                {if $plans|@count > 1}
                    {foreach from=$plans item='plan'}{if $plan.Subscription && $plan.Price > 0 && !$plan.Listings_remains}{assign var=subscription_exists value=true}{elseif $plan.Featured && $plan.Price > 0 && !$plan.Listings_remains}{assign var=featured_exists value=true}{/if}{/foreach}
                    <ul class="plans{if $plans|@count > 5} more-5{/if}{if $subscription_exists} with-subscription{/if}{if $featured_exists} with-featured{/if}">
                        {foreach from=$plans item='plan' name='plansF'}{strip}
                            {include file=$mConfig.view|cat:'plan.tpl'}
                        {/strip}{/foreach}
                    </ul>
                {else}
                    <input type="hidden" name="plan" value="{$plans.0.ID}">
                    {assign var='package_name' value='<b>'|cat:$plans.0.name|cat:'</b>'}
                    <div class="table-cell  clearfix">
                        <div><div><span>{$lang.next_service_will_apply|replace:'[package_name]':$package_name}</span></div></div>
                    </div>
                    <div class="table-cell  clearfix">
                        <div class="name" title="{$lang.m_package_price}"><div><span>{$lang.m_package_price}</span></div></div>
                        <div class="value">
                            {if $plans.0.Price}
                                {if $plans.0.Price == -1}
                                    {$lang.bump_up_balance}
                                {else}
                                    {if $config.system_currency_position == 'before'}{$config.system_currency}{/if}
                                    {$plans.0.Price}
                                    {if $config.system_currency_position == 'after'}{$config.system_currency}{/if}
                                {/if}
                            {else}
                                {$lang.free}
                            {/if}</div>
                    </div>
                    <div class="table-cell  clearfix">
                        <div class="name" title="Transmission"><div><span>{$lang.bumpups_available}</span></div></div>
                        <div class="value">
                            {if $plans.0.Bump_ups > 0}
                                {$plans.0.Bump_ups}
                            {else}
                                {$lang.unlimited}
                            {/if}
                        </div>
                    </div>
                {/if}
            </div>

            {include file='blocks'|cat:$smarty.const.RL_DS|cat:'fieldset_footer.tpl'}

            <div id="gateways" {if !empty($credits_exist)}class="hide"{/if}>
                <script class="fl-js-dynamic">
                    var plans = plans || [];
                    {literal}
                    $(document).ready(function () {

                        var selected_plan_id = 0;
                        var last_plan_id = 0;
                        {/literal}
                        {foreach from=$plans item='plan'}
                            plans[{$plan.ID}] = [];
                            plans[{$plan.ID}]['Key'] = '{$plan.Key}';
                            plans[{$plan.ID}]['Price'] = {$plan.Price};
                        {/foreach}
                        {literal}
                        flynax.planClick();
                        flynax.qtip();
                        
                        if($("div.plans-container .plan").length > 0) {
                            monetizer.bp_plan_switcher();
                        }
                    });
                    {/literal}
                </script>
                {gateways}
            </div>

            <!-- select a bump up  plan end -->
            <div class="form-buttons">
                <input type="submit" value="{$lang.next}"/>
            </div>
        </form>
     {else}
         {$lang.no_bumpups_plans}
     {/if}
{/if}

{if $smarty.get.id && isset($smarty.get.completed)}
    {if $smarty.const.IS_ESCORT === true}{$lang.m_bumpup_success_escort}{else}{$lang.bumpup_success}{/if}
    <div class="form-buttons">
        <a href="{$back_url}">
            <input type="button" value="{if $smarty.const.IS_ESCORT === true}{$lang.m_back_to_my_profiles}{else}{$lang.bumpup_back}{/if}">
        </a>
    </div>
{/if}

{if $smarty.get.id && isset($smarty.get.canceled)}
    {$lang.bump_up_error}
    <div class="form-buttons">
        <a href="{$back_url}">
            <input type="button" value="{if $smarty.const.IS_ESCORT === true}{$lang.m_back_to_my_profiles}{else}{$lang.bumpup_back}{/if}">
        </a>
    </div>
{/if}
