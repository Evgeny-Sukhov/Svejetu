{if $plugins.massmailer_newsletter && $pageInfo.Controller != 'search_map'}
    <div class="newsletter">
        <div class="point1 clearfix mx-auto">
            <div class="content-padding">
                <div class="row mb-0">
                    <p class="newsletter__text col-lg-6 col-md-5 col-sm-12 align-self-center">{$lang.nova_newsletter_text}</p>
                    <div class="col-lg-6 col-md-7 col-sm-12" id="nova-newsletter-cont">

                    </div>
                </div>
            </div>
        </div>
    </div>
{/if}
<footer class="page-footer content-padding">
    <div class="point1 clearfix">
        <div class="row">
            <nav class="footer-menu col-12 col-xl-12">
                <div class="row">
                    {include file='menus'|cat:$smarty.const.RL_DS|cat:'footer_menu.tpl'}

{*                    <div class="mobile-apps col-sm-6 col-md-3 d-block d-md-flex flex-column">*}
{*                        <h4 class="footer__menu-title">{$lang.nova_mobile_apps}</h4>*}
{*                        <a class="d-inline-block pt-0 mt-sm-2" target="_blank" href="{$config.ios_app_url}">*}
{*                            <img src="{$rlTplBase}img/app-store-icon.svg" alt="App store icon" />*}
{*                        </a>*}
{*                        <a class="d-inline-block mt-0 mt-sm-3" target="_blank" href="{$config.android_app_url}">*}
{*                            <img src="{$rlTplBase}img/play-market-icon.svg" alt="Play market icon" />*}
{*                        </a>*}
{*                    </div>*}
                </div>
            </nav>
        </div>

        {include file='footer_data.tpl'}
    </div>
</footer>

{include file='../img/gallery.svg'}

{rlHook name='tplFooter'}
</div>

{if !$isLogin}
    <div id="login_modal_source" class="hide">
        <div class="tmp-dom">
            <div class="caption_padding">{$lang.login}</div>

            {if $loginAttemptsLeft > 0 && $config.security_login_attempt_user_module}
                <div class="attention">{$loginAttemptsMess}</div>
            {elseif $loginAttemptsLeft <= 0 && $config.security_login_attempt_user_module}
                <div class="attention">
                    {assign var='periodVar' value=`$smarty.ldelim`period`$smarty.rdelim`}
                    {assign var='replace' value='<b>'|cat:$config.security_login_attempt_user_period|cat:'</b>'}
                    {assign var='regReplace' value='<span class="red">$1</span>'}
                    {$lang.login_attempt_error|replace:$periodVar:$replace|regex_replace:'/\[(.*)\]/':$regReplace}
                </div>
            {/if}

            <form {if $loginAttemptsLeft <= 0 && $config.security_login_attempt_user_module}onsubmit="return false;"{/if} action="{$rlBase}{if $config.mod_rewrite}{$pages.login}.html{else}?page={$pages.login}{/if}" method="post">
                <input type="hidden" name="action" value="login" />

                <div class="submit-cell">
                    <div class="name">{$lang.username}</div>
                    <div class="field">
                        <input {if $loginAttemptsLeft <= 0 && $config.security_login_attempt_user_module}disabled="disabled" class="disabled"{/if} type="text" name="username" maxlength="35" value="{$smarty.post.username}" />
                    </div>
                </div>
                <div class="submit-cell">
                    <div class="name">{$lang.password}</div>
                    <div class="field">
                        <input {if $loginAttemptsLeft <= 0 && $config.security_login_attempt_user_module}disabled="disabled" class="disabled"{/if} type="password" name="password" maxlength="35" />
                    </div>
                </div>

                <div class="submit-cell buttons">
                    <div class="name"></div>
                    <div class="field">
                        <input {if $loginAttemptsLeft <= 0 && $config.security_login_attempt_user_module}disabled="disabled" class="disabled"{/if} type="submit" value="{$lang.login}" />

                        <div style="padding: 10px 0 0 0;">{$lang.forgot_pass} <a title="{$lang.remind_pass}" class="brown_12" href="{$rlBase}{if $config.mod_rewrite}{$pages.remind}.html{else}?page={$pages.remind}{/if}">{$lang.remind}</a></div>
                    </div>
                </div>
            </form>
        </div>
    </div>
{/if}

{displayCSS mode='footer'}

{displayJS}

<script>
{literal}

(function(){
    $('#main_container').on('mouseover', '.listing-picture-slider', function(){
        if (!this.sliderPicturesLoaded) {
            var id = $(this).data('id');
            var item = this;
            var counter = 0;

            var data = {
                mode: 'getListingPhotos',
                id: id
            };
            flUtil.ajax(data, function(response, status){
                if (status == 'success') {
                    if (response.status == 'OK') {
                        for (var i in response.data) {
                            if (i === '0') {
                                continue;
                            }

                            var index = parseInt(i) + 1;
                            var src = rlConfig['files_url'] + response.data[i].Thumbnail;

                            $(item).find('.pic-empty-' + index).attr('src', src);
                        }

                        $(item).find('img').one('load', function(){
                            counter++;

                            if (counter == (response.data.length - 1)) {
                                $(item).addClass('listing-picture-slider_loaded');
                            }
                        });
                    }
                } else {
                    printMessage('error', lang['system_error']);
                }
            }, true);

            item.sliderPicturesLoaded = true;
        }
    });
})();

{/literal}
</script>

<script>
    {literal}

    (function(){
        $('#nova-newsletter-cont').append($('#tmp-newsletter > div'));
        $('.newsletter #newsletter_name').val('Guest');
    })();

    {/literal}
</script>
</body>
</html>
