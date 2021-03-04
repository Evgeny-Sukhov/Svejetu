<!-- Export/Import pagination -->

<ul class="grid-pagination" data-type="{$type}">
    <li>
        <input id="goto-first" type="button" value="&laquo;" />
    </li>
    <li>
        <input id="goto-previous" type="button" value="&lsaquo;" />
    </li>
    <li class="go-to-page">
        <span>{$lang.eil_page}</span>
        <input class="numeric" id="goto-page" type="text" />
        <span>{$lang.of} {$grid.total_pages}</span>
    </li>
    <li>
        <input id="goto-next" type="button" value="&rsaquo;" />
    </li>
    <li>
        <input id="goto-last" type="button" value="&raquo;" />
    </li>
</ul>

<!-- Export/Import pagination end -->