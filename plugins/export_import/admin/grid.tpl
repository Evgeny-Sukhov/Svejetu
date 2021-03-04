{strip}
    {if $smarty.const.REALM == 'admin'}
        {section name=rowF loop=$import_data start=0}
            {assign var='iter_row' value=$smarty.foreach.rowF.iteration-1}
            <tr class="body{if $smarty.foreach.rowF.iteration%2 == 0 && !$smarty.foreach.rowF.first} highlight{/if}">
                <td class="row-checkbox"></td>
                {foreach from=$import_data[rowF] item='col' name='colF'}
                    <td>
                        <div>
                            {if $col|@strlen > 200 || $col|@strlen != $col|regex_replace:'/[\r\t\n]/':''|@strlen}
                                <span style="display:block;max-height: 200px;">{$col|replace:'"':'&quot;'}</span>
                            {else}
                                <span style="display:block;">{$col|escape}</span>
                            {/if}
                        </div>
                    </td>
                {/foreach}
            </tr>
        {/section}
    {else}
        {section name=rowF loop=$import_data start=0}
            {assign var='iter_row' value=$smarty.foreach.rowF.iteration-1}
            <tr class="body">
                <td class="row-checkbox">
                </td>
                {foreach from=$import_data[rowF] item='col' name='colF'}
                    <td class="eil-field{if $col|@strlen > 94 || $col|@strlen != $col|regex_replace:'/[\r\t\n]/':''|@strlen} scroll{/if}">
                        <div class="hborder">
                            {$col|escape:'quotes'}
                        </div>
                    </td>
                    {if !$smarty.foreach.colF.last}<td class="divider"></td>{/if}
                {/foreach}
            </tr>
        {/section}
    {/if}
{/strip}
