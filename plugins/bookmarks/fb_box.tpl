<!-- facebook funs box tpl -->

{if $config.bookmarks_fb_box_appid && $config.bookmarks_fb_box_url}
    {assign var='allow_fb_init' value=true}
    {if $aHooks.facebookConnect}
        {if $config.facebookConnect_module
            && $config.facebookConnect_appid
            && $config.facebookConnect_secret
            && $config.facebookConnect_account_type}
                {assign var='allow_fb_init' value=false}
        {/if}
    {/if}

    <div id="fl-facebook-funs"></div>
    <div id="fb-root"></div>
    <script class="fl-js-dynamic">
    var allow_fb_init = {if $allow_fb_init}true{else}false{/if};
    {literal}
    $(document).ready(function(){
        var width = $('#fl-facebook-funs').width();
        $('.fb-page').attr('data-width', width);

        if ( allow_fb_init ) {
            window.fbAsyncInit = function() {
                // init the FB JS SDK
                FB.init({
                    appId      : '{/literal}{$config.bookmarks_fb_box_appid}{literal}', // App ID from the app dashboard
                    channelUrl : '{/literal}{$config.bookmarks_fb_box_url}{literal}',   // Channel file for x-domain comms
                    status     : true,                                                  // Check Facebook Login status
                    xfbml      : true,                                                  // Look for social plugins on the page
                    version    : 'v2.2'
                });

                FB.Event.subscribe('xfbml.render',
                    function(response) {
                        $('.fb_iframe_widget iframe, .fb_iframe_widget > span').width(width);
                    }
                );
            };
        }

        // Load the SDK asynchronously
        (function(d, s, id){
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {return;}
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/en_US/all.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    });

    {/literal}
    </script>

    <div
        class="fb-page" 
        data-href="{$config.bookmarks_fb_box_url}"
        data-show-facepile="{if $config.bookmarks_fb_box_faces}true{else}false{/if}"
        data-tabs="{if $config.bookmarks_fb_box_stream}timeline{else}false{/if}"
        data-small-header="{if $config.bookmarks_fb_box_header}false{else}true{/if}"
        {if $config.bookmarks_fb_box_height}
        data-height="{$config.bookmarks_fb_box_height}"
        {/if}></div>
{else}
    {$lang.bookmarks_fb_box_deny}
{/if}

<!-- facebook funs box tpl end -->
