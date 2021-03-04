<!-- custom field bound boxes -->

{if !empty($options)}
    {if !$icons}
        <div class="categories">
            <li>
    {/if}

    {php}
    $this->_tpl_vars['content_positions'] = array('top', 'middle', 'bottom');
    {/php}

    <ul class="row {if $icons}special-bound-box{else}categoty-column{/if}">
        {foreach from=$options item='option' name='fCats'}{strip}
            {if $icons}
                <li class="col-4 col-sm-3{if $block.Side|strpos:'middle_' !== false} col-xl-3 col-md-4{elseif $block.Side|in_array:$content_positions} col-xl-1 col-md-2{else} col-lg-6{/if} col-sm-2 {if $show_count && !$option.Count} empty{/if} mb-4">
                    <a class="text-center"
                       title="{$lang[$option.pName]}"
                       href="{$rlBase}{if $config.mod_rewrite}{$path}/{$option.Key}{if $html_postfix}.html{else}/{/if}{else}?page={$pages.listings_by_field}&{$path}={$option.Key}{/if}">
                        {if $option.Icon}
                            <div class="mb-3">
                                <img style="{if $icons_width}width: {$icons_width}px;{/if}" src="{$smarty.const.RL_FILES_URL}{$option.Icon}" alt="{$lang[$option.pName]}" />
                            </div>
                        {/if}
                        <div class="font-size-sm fbb-greyscale">{$lang[$option.pName]}</div>
                        {if $show_count}<div class="font-size-xs text-info mt-1 font-weight-bold fbb-greyscale">{$option.Count}</div>{/if}
                    </a>
                </li>
            {else}
                <div class="item {if $block.Side == 'middle_left' || $block.Side == 'middle_right'}{if $side_bar_exists}col-md-6{else}col-lg-4 col-md-6{/if}{elseif $block.Side == 'left'}col-md-12{else}col-md-4 col-lg-2{/if} col-sm-6{if $show_count && !$option.Count} empty-category{/if}">
                    <div class="parent-cateory d-flex">
                        <div>
                            <a class="font-size-sm"
                               title="{$lang[$option.pName]}"
                               href="{$rlBase}{if $config.mod_rewrite}{$path}/{$option.Key}{if $html_postfix}.html{else}/{/if}{else}?page={$pages.listings_by_field}&{$path}={$option.Key}{/if}">{$lang[$option.pName]}</a>
                        </div>
                        {if $show_count}
                            <div class="ml-2 font-size-xs text-info font-weight-bold category-counter">{$option.Count}</div>
                        {/if}
                    </div>
                </div>
            {/if}
        {/strip}{/foreach}
    </ul>
    
    {if !$icons}
            </li>
        </div>
    {/if}
{/if}

<!-- custom field bound boxes end -->
