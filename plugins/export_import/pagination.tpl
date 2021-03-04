<!-- Export/Import pagination -->

<ul class="pagination grid-pagination" data-type="{$type}" data-from="frontend">
    <li class="navigator ls">
        <a id="goto-previous" title="{$lang.prev_page}" class="button" href="javascript://">‹</a>
    </li>
    <li class="transit go-to-page">
        <span>{$lang.eil_page}</span>
        <input class="numeric" id="goto-page" type="text" />
        <span>{$lang.of} {$grid.total_pages}</span>
    </li>
    <li class="navigator rs">
        <a id="goto-next" title="{$lang.next_page}" class="button" href="javascript://">›</a>
    </li>
</ul>

<!-- Export/Import pagination end -->