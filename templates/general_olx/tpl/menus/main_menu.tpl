<!-- main menu block -->


    <div class="d-none h-100 align-items-center flex-fill shrink-fix justify-content-end d-sm-down-block">
        <span class="mobile-menu-header d-none align-items-center">
            <span class="mr-auto">{$lang.menu}</span>
            <svg viewBox="0 0 12 12">
                <use xlink:href="#close-icon"></use>
            </svg>
        </span>

	{foreach name='mMenu' from=$main_menu item='mainMenu'}
		{if $mainMenu.Key == 'add_listing'}{assign var='add_listing_button' value=$mainMenu}{continue}{/if}
		<a title="{$mainMenu.title}"
           class="h-100{if $pageInfo.Key == $mainMenu.Key} active{/if}"
           {if $mainMenu.No_follow || $mainMenu.Login}menu-button
           rel="nofollow"
           {/if}
           href="{strip}
           {if $mainMenu.Page_type == 'external'}
               {$mainMenu.Controller}
           {else}
                {pageUrl key=$mainMenu.Key vars=$mainMenu.Get_vars}
           {/if}
           {/strip}">{$mainMenu.name}</a>
	{/foreach}
    </div>

    <a class="button add-property"
        {if $mainMenu.No_follow || $mainMenu.Login}
        rel="nofollow"
        {/if}
        title="{$mainMenu.title}"
        href="{strip}
            {if $pageInfo.Controller != 'add_listing'
                && !empty($category.Path)
                && !$category.Lock
            }
                {$rlBase}
                {if $config.mod_rewrite}
                    {$add_listing_button.Path}/{$category.Path}/{$steps.plan.path}.html
                {else}
                    ?page={$add_listing_button.Path}&step={$steps.plan.path}&id={$category.ID}
                {/if}
            {else}
                {pageUrl key=$add_listing_button.Key}
            {/if}
        {/strip}">
        {$add_listing_button.name}</a>

	{*<div class="more" style="display: none;"><span><span></span><span></span><span></span></span></div>*}




{*<ul id="main_menu_more"></ul>*}

<!-- main menu block end -->
