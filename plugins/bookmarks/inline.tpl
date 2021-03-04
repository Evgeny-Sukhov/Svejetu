<!-- bookmarks inline bar -->

<div 
    class="sharetool_{$block.Key}"
    {if $align == 'right'}
    style="text-align: {if $smarty.const.RL_LANG_DIR == 'ltr'}right{else}left{/if};"
    {elseif $align == 'center'}
    style="text-align: center;"
    {/if}
    ></div>

<script class="fl-js-dynamic">
{literal}

(function(){
    "use strict";

    {/literal}
    var service_type = '{$service_type}';
    var services     = '{$services}';
    var button_size  = '{$button_size}';
    var share_type   = '{$share_type}';
    var share_style  = '{$share_style}';
    {literal}

    var size = '32px';
    if (button_size == 'medium') {
        size = '20px';
    } else if (button_size == 'small') {
        size = '16px';
    }

    if (share_style == 'original') {
        // Convert to original
        services = services.split(',');

        var mapping = {
            'facebook': 'facebook_like',
            'twitter': 'tweet',
            'pinterest_share': 'pinterest_pinit',
            'linkedin': 'linkedin_counter',
            'google_plusone_share': 'google_plusone',
            'stumbleupon': 'stumbleupon_badge',
            'addthis': 'counter',
        };
        for (var i in services) {
            if (typeof mapping[services[i]] != 'undefined') {
                services[i] = mapping[services[i]];
            }
        }

        // Build share
        var share = {
            thirdPartyButtons: true,
            numPreferredServices: services.length,
            services: services.join(',')
        };
    } else {
        // Build share
        var share = {
            counts: share_type,
            size: size,
            style: share_style,
            shareCountThreshold: 0,
        };

        share[service_type == 'automatic'
        ? 'numPreferredServices'
        : 'services'] = services;
    }

    share.elements = ".sharetool_{/literal}{$block.Key}{literal}";

    var widget = {sharetoolbox: share};

    if (share_style == 'responsive') {
        widget = {responsiveshare: share};
    }

    addthis.layers(widget);
})();

{/literal}
</script>

<!-- bookmarks inline bar end -->
