<!-- twitter box -->

{if $config.bookmarks_twitter_box_username}

{strip}
<a class="twitter-timeline" data-height="{$config.bookmarks_twitter_box_height}" href="https://twitter.com/{$config.bookmarks_twitter_box_username}">
    {$lang.bookmarks_twitter_tweets_by} @{$config.bookmarks_twitter_box_username}
</a>
{/strip}

<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>

{else}
    {$lang.bookmarks_twitter_box_deny}
{/if}

<!-- twitter box end -->
