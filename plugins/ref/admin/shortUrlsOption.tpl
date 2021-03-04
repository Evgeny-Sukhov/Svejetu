<!-- Ref option in listing type tpl -->

<tr class="hide" id="ref_short_urls">
    <td class="name">{$lang.ref_short_urls}</td>
    <td class="field">
        {assign var='checkbox_field' value='ref_short_urls'}
        
        {if $sPost.$checkbox_field == '1'}
            {assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
        {elseif $sPost.$checkbox_field == '0'}
            {assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
        {else}
            {assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
        {/if}
        
        <table>
        <tr>
            <td>
                <label>
                    <input {$ref_short_urls_yes} type="radio" name="{$checkbox_field}" value="1" />
                    {$lang.yes}
                </label>
                <label>
                    <input {$ref_short_urls_no} type="radio" name="{$checkbox_field}" value="0" />
                    {$lang.no}
                </label>

                <span class="field_description">{$lang.ref_short_urls_desc}</span>
            </td>
        </tr>
        </table>
    </td>
</tr>

<script>{literal}
    $(function () {
        var $refShortUrlOption = $('#ref_short_urls'),
            $urlTypeOption     = $('select[name="links_type"]');

        $urlTypeOption.closest('td.field').parent('tr').after($refShortUrlOption);

        $urlTypeOption.change(function () {
           refShortUrlHandler();
        });

        refShortUrlHandler();

        function refShortUrlHandler() {
            if ($urlTypeOption.val() === 'short') {
                $refShortUrlOption.show();
            } else {
                $refShortUrlOption.hide();
                $refShortUrlOption.find('input:checked').removeAttr('checked');
                $refShortUrlOption.find('input[value="0"]').attr('checked', 'checked');
            }
        }
    });
{/literal}</script>

<!-- Ref option in listing type tpl end -->
