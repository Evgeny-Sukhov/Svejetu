{if $phone_fields}
    <div id="registration-phone-container" class="hide">
        {include file='blocks'|cat:$smarty.const.RL_DS|cat:'fields_search_box.tpl' fields=$phone_fields}
        <script class="fl-js-dynamic">
            var quick_types = [];
            {foreach from=$quick_types item='quick_type'}
                quick_types[{$quick_type.ID}] = '{$quick_type.phone_field}';
            {/foreach}
            {literal}
            $(document).ready(function() {
                var $rpcEl = $('#registration-phone-container');
                $rpcEl.find('input[type="text"]').each(function() {
                    $(this).attr('form', 'listing_form');
                });
                $('input[name="register[email]"]').after($rpcEl);
                $rpcEl.removeClass('hide');
                
                $('select[name="register[type]"]').change(function() {
                    if (quick_types[$(this).val()]) {
                        $rpcEl.removeClass('hide');
                    } else {
                        $rpcEl.addClass('hide');
                        $('.registration-phone input[form="listing_form"]').each(function() {
                            $(this).val('');
                        });
                    }
                });
            });
            {/literal}
        </script>
    </div>
{/if}
