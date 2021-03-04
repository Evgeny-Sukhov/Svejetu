<!-- bumpup listings history tpl -->

<script>var listings_map = new Array();</script>
<section id="listings" class="list row monetize-block">
    {include file='blocks'|cat:$smarty.const.RL_DS|cat:'listing.tpl'}
</section>
<script class="fl-js-dynamic">
    // @todo 1.3.0 - Remove this when "compatible" will be more than 4.6.2
    rlConfig['hisrc'] = {if $config.rl_version|version_compare:'4.6.2' <= 0}true{else}false{/if};

    var is_escort = {if $smarty.const.IS_ESCORT === true} true {else} false {/if};

    {literal}
    $(document).ready(function () {
        $('.navigation-column').addClass('hide');
        monetizer.hideData('.monetize-block', is_escort);

        // @todo 1.3.0 - Remove this when "compatible" will be more than 4.6.2
        if (rlConfig['hisrc']) {
            flynaxTpl.hisrc();
        }
    });
    {/literal}
</script>


<!-- bumpup listings history tpl end -->
