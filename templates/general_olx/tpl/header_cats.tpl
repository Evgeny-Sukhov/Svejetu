<ul id="header-cats" class="row cat-tree">{strip}
        {foreach from=$root_categories item='cat' name='fCats'}
            <li class="header-cats cats-item">
                {*rlHook name='tplPreCategory'*}
                <a class="cat-item-link {$cat|@print_r}" title="{if $lang[$cat.pTitle]}{$lang[$cat.pTitle]}{else}{$cat.name}{/if}" href="{$cat.Key}">
                    {if isset($cat.Icon) && $cat.Icon|count_characters > 0}
                        <img class="parent-cat" src="/files/{$cat.Icon}">
                    {/if}
                    {$cat.name}
                </a>
                {rlHook name='tplPostCategory'}
                {if $cat.Categories|@count > 0}
                    <ul class="header-subcats-list">
                    {foreach from=$cat.Categories item='subcat' name='subCats'}
                        {if $subcat.Count > 1}
                            <li class="header-subcats-item {$subcat|@print_r}">
                                <a href="/{$subcat.Type}/{$subcat.Key}">{$lang[$subcat.pName]}</a>
                            </li>
                        {/if}
                    {/foreach}
                    </ul>
                {/if}
            </li>
        {/foreach}
{/strip}</ul>
<div id="subcats-container"></div>
<script src="{$rlTplBase}/js/header-cats-menu.js"></script>