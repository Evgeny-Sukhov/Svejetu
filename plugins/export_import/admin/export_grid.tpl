{strip}
    {foreach from=$export_listings item='listing' name='rowF'}
        {assign var='iter_row' value=$smarty.foreach.rowF.iteration-1}
        {if $smarty.const.REALM == 'admin'}
                <tr class="body{if $smarty.foreach.rowF.iteration%2 == 0 && !$smarty.foreach.rowF.first} highlight{/if}">
                    <td class="row-checkbox">
                        <div><input {if isset($sPost.from_post) && $sPost.rows[$iter_row]}checked="checked"{elseif !isset($sPost.from_post)}checked="checked"{/if} type="checkbox" name="rows[{$iter_row}]" data-id="{$listing.ID}" value="1" /><div>
                    </td>
                    {foreach from=$fields item='field'}
                        <td><div>{$listing[$field.Key]}</div></td>
                    {/foreach}
                </tr>
        {else}
                <tr class="body">
                    <td class="row-checkbox">
                        <label><input {if isset($sPost.from_post) && $sPost.rows[$iter_row]}checked="checked"{elseif !isset($sPost.from_post)}checked="checked"{/if} type="checkbox" name="rows[{$iter_row}]" data-id="{$listing.ID}" value="1" /></label>
                    </td>
                    {foreach from=$fields item='field' name='fieldF'}
                        <td class="eil-field{if $listing[$field.Key]|@strlen > 65 || $listing[$field.Key]|@strlen != $listing[$field.Key]|regex_replace:'/[\r\t\n]/':''|@strlen} scroll{elseif $listing[$field.Key]|regex_replace:'/[\r\t\n]/':''|@strlen < 20} inline-nowrap{/if}">
                            <div class="hborder">
                                {$listing[$field.Key]}
                            </div>
                        </td>
                        {if !$smarty.foreach.fieldF.last}
                            <td class="divider"></td>
                        {/if}
                    {/foreach}
                </tr>
        {/if}
    {/foreach}
{/strip}