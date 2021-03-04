<!-- bumped up date tpl -->
<li id="bu_{$listing.ID}" class="bump-date bumped-up-full">
    {$listing.Date|date_format:$smarty.const.BUMPUP_TIME_FORMAT}
</li>

<li id="hi_{$listing.ID}" class="highlight-date bumped-up-full">
    {$listing.expiring_status}
</li>
<!-- bumped up date tpl end -->
