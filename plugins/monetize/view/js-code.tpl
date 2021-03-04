<script class="fl-js-dynamic">
    var is_escort = {if $smarty.const.IS_ESCORT === true} true {else} false {/if};
    var is_profile = {if $pageInfo.Controller === 'profile'} true {else} false {/if};
    var template = '{$tpl_settings.name}';
    {literal}
        $(document).ready(function () {

            monetizer.hideData('#recently-bumped-up,#recently-highlighted', is_escort);
            flynaxTpl.hisrc();
            if(is_profile && template.indexOf('modern') > 0 ) {
                monetizer.moveTabs();
            }
        });
{/literal}</script>