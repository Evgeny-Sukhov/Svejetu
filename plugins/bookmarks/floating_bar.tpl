<!-- bookmarks floating bar -->

<script class="fl-js-dynamic">
{literal}

(function(){
    "use strict";

    {/literal}
    var service_type = '{$service_type}';
    var services     = '{$services}';
    var button_size  = '{$button_size}';
    var share_type   = '{$share_type}';
    var theme        = '{$theme}';
    {literal}

    var share = {
        'position': rlLangDir == 'ltr' ? 'left' : 'right',
        'desktop' : true,
        'mobile'  : false,
        'theme'   : theme,
        'counts'   : share_type,
    };
    share[service_type == 'automatic'
    ? 'numPreferredServices'
    : 'services'] = services;

    var sharedock = {
        'counts'          : share_type,
        'mobileButtonSize': button_size,
        'position'        : 'bottom',
        'theme'           : theme,
    };
    sharedock[service_type == 'automatic'
    ? 'numPreferredServices'
    : 'services'] = services;

    var settings = {
        'share': share,
        'sharedock': sharedock
    };

    addthis.layers(settings);

    $('body').addClass('bookmarks-theme-' + theme);
})();

{/literal}
</script>

<!-- bookmarks floating bar end -->
