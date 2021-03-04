{include file='head.tpl'}

<div class="main-wrapper d-flex flex-column">
    <header class="page-header{if $pageInfo.Key == 'search_on_map'} fixed-menu{/if}">
        <div class="clearfix">
            <div class="top-navigation">
                <div class="row point1 top-navigation-inside">
                    <div class="col-lg-6 col-md-12 header-row">
                        <a href="/" id="home-button">
                            <div id="home-link">
                               &nbsp;&nbsp;&nbsp;
                            </div>
                        </a>
                        {assign var='page_menu' value=','|explode:$pageInfo.Menus}
{*                        {if $pageInfo.Controller != 'search_map' && $pageInfo.Controller != 'listing_details' && !$pageInfo.Plugin && !'2'|in_array:$page_menu}*}
                        {include file='blocks'|cat:$smarty.const.RL_DS|cat:'home_content.tpl'}
{*                        {/if}*}
                        {*<div class="mr-2" id="logo">
                            <a href="{$rlBase}" title="{$config.site_name}">
                                <span id="sve"><b>Sve</b>je<b>tu</b></span>
                            </a>
                        </div>*}


                    </div>
                    <div class="col-lg-6 col-md-12">
                        <nav class="main-menu">

                            {include file='blocks'|cat:$smarty.const.RL_DS|cat:'lang_selector.tpl'}

                            {rlHook name='tplHeaderUserNav'}

                            {include file='blocks'|cat:$smarty.const.RL_DS|cat:'shopping_cart.tpl'}
                            {include file='blocks'|cat:$smarty.const.RL_DS|cat:'user_navbar.tpl'}
                            {include file='menus'|cat:$smarty.const.RL_DS|cat:'main_menu.tpl'}

                                {*include file='menus'|cat:$smarty.const.RL_DS|cat:'main_menu.tpl'*}
                        </nav>
                    </div>
                </div>
            </div>

            {*assign var='page_menu' value=','|explode:$pageInfo.Menus*}


            <section class="header-nav d-flex flex-column">
                <div class="point1 d-flex flex-fill flex-column">
                    <div class="row no-gutters flex-fill align-items-center">
                        <div class="col-xl-12 col-lg-12 order-1 order-xl-1">
                            {include file='header_cats.tpl'}
                        </div>
                    </div>
                </div>

            </section>


        </div>
    </header>
