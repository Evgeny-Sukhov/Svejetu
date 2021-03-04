<tr class="body">
    <td class="list_td">{$lang.ref_rebuild}</td>
    <td class="list_td" align="center" style="width: 200px;">
        <input id="ref_rebuild" type="button" value="{$lang.rebuild}" style="margin: 0;width: 100px;">
    </td>
</tr>
<script>
    var refLang = [];
    refLang['rebuild'] = '{$lang.rebuild}';

    {literal}
        $(document).ready(function(){
            var refNumber = new refNumberClass();
            refNumber.init();
        });
    {/literal}
</script>
