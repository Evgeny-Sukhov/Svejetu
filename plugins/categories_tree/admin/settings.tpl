<!-- category tree / category block settings -->

<tr>
    <td class="name">{$lang.category_tree_tree_options}</td>
    <td class="field" style="vertical-align: top;padding-top: 10px;" id="ctree_block_settings">
        {assign var='checkbox_field' value='ctree_module'}
            
        {if $sPost.$checkbox_field == '1'}
            {assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
        {elseif $sPost.$checkbox_field == '0'}
            {assign var=$checkbox_field|cat:'_no' value='checked="checked"'}
        {else}
            {assign var=$checkbox_field|cat:'_yes' value='checked="checked"'}
        {/if}
        
        <input {$ctree_module_yes} type="radio" id="{$checkbox_field}_yes" name="{$checkbox_field}" value="1" /> <label for="{$checkbox_field}_yes">{$lang.yes}</label>
        <input {$ctree_module_no} type="radio" id="{$checkbox_field}_no" name="{$checkbox_field}" value="0" /> <label for="{$checkbox_field}_no">{$lang.no}</label>
        
        <div class="ctree-cont">
            <div style="padding: 14px 0 0;"><label><input value="1" {if $sPost.ctree_subcat_counter}checked="checked"{/if} type="checkbox" name="ctree_subcat_counter" /> {$lang.category_tree_show_subcat_counter}</label></div>
            <div style="padding: 5px 0 0;"><label><input value="1" {if $sPost.ctree_open_subcat}checked="checked"{/if} type="checkbox" name="ctree_open_subcat" /> {$lang.category_tree_open_subcat}</label></div>
            <div style="padding: 5px 0 0;"><label><input value="1" {if $sPost.ctree_child_only}checked="checked"{/if} type="checkbox" name="ctree_child_only" /> {$lang.category_tree_show_child_only}</label></div>
        </div>
        
        <script type="text/javascript">
        {literal}
        
        var ctree_settings = function(){
            var obj = $('#ctree_block_settings input[name=ctree_module]:checked');
            if ( parseInt(obj.val()) ) {
                obj.parent().find('.ctree-cont').slideDown('fast');
                $('input[name=ablock_display_subcategories], \
                input[name=ablock_subcategories_number], \
                input[name=ablock_scrolling_in_box], \
                input[name=ablock_visible_number], \
                input[name=ablock_columns_number]').attr('disabled', true).addClass('disabled').each(function(){
                    if (($(this).attr('type') == 'radio' && $(this).attr('checked')) || $(this).attr('type') == 'text') {
                        var value = $(this).val();
                        var name  = $(this).attr('name');

                        if (name == 'ablock_visible_number' || name == 'ablock_subcategories_number') {
                            value = '0';
                            $(this).val(value);
                        } else if (name == 'ablock_scrolling_in_box' || name == 'ablock_display_subcategories') {
                            value = '1';
                            $('#'+ name + '_yes').prop('checked', true)
                        }

                        $(this).after('<input name="' + name + '" type="hidden" value="' + value + '" />');
                    }
                });
            }
            else {
                obj.parent().find('.ctree-cont').slideUp();
                $('input[name=ablock_display_subcategories], \
                input[name=ablock_subcategories_number], \
                input[name=ablock_scrolling_in_box], \
                input[name=ablock_visible_number], \
                input[name=ablock_columns_number]').attr('disabled', false).removeClass('disabled').each(function(){
                    if ( ($(this).attr('type') == 'radio' && $(this).attr('checked')) || $(this).attr('type') == 'text' ) {
                        if ( $(this).next().attr('type') == 'hidden' ) {
                            $(this).next().remove();
                        }
                    }
                });
            }
        }
        
        $(document).ready(function(){
            ctree_settings();
            
            $('#ctree_block_settings input[name=ctree_module]').change(function(){
                ctree_settings();
            });
        });
        
        {/literal}
        </script>
    </td>
</tr>

<!-- category tree / category block settings end -->
