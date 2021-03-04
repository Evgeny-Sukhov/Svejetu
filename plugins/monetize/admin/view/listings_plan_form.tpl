<!-- Bump up option -->
<div id="bumpups">
    <table class="form">
        <tbody>
            <tr>
                <td class="name">{$lang.bumpups}</td>
                <td class="field">
                    <table class="infinity monetize">
                        <tbody>
                        <tr>
                            <td>
                                <input accesskey="{if $smarty.post.bumpups > 0}{$smarty.post.bumpups}{else}0{/if}" type="text"  name="bumpups" class="numeric" value="{if $smarty.post.bumpups > 0}{$smarty.post.bumpups}{else}0{/if}" style="width: 50px; text-align: center;" />
                            </td>
                            <td>
                                <span title="{if $smarty.post.bump_up_count_unlimited}{$lang.unset_unlimited}{else}{$lang.set_unlimited}{/if}" class="{if $smarty.post.bump_up_count_unlimited}active{else}inactive{/if}"></span>
                                <input name="bump_up_count_unlimited" type="hidden" value="{if $smarty.post.bump_up_count_unlimited}1{else}0{/if}">
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="name">{$lang.m_highlights}</td>
                <td class="field">
                    <table class="infinity monetize">
                        <tbody>
                        <tr>
                            <td>
                                <input accesskey="{if $smarty.post.highlights > 0}{$smarty.post.highlights}{else}0{/if}" type="text"  name="highlights" class="numeric" value="{if $smarty.post.highlights > 0}{$smarty.post.highlights}{else}0{/if}" class="numeric"/>
                            </td>
                            <td>
                                <span title="{if $smarty.post.highlights_unlimited}{$lang.unset_unlimited}{else}{$lang.set_unlimited}{/if}" class="{if $smarty.post.highlights_unlimited}active{else}inactive{/if}"></span>
                                <input name="highlights_unlimited" type="hidden" value="{if $smarty.post.highlights_unlimited}1{else}0{/if}">
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="name"><span class="red">*</span>{$lang.m_highlight_for}</td>
                <td class="field">
                    <input type="text" name="days_highlight" class="numeric" value="{if $smarty.post.days_highlight}{$smarty.post.days_highlight}{/if}">
                    <span class="field_description_noicon">&nbsp; {$lang.m_days}</span>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<!-- Bump up option end  -->
