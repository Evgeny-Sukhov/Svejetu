{if $multi_format_keys}
    <script class="fl-js-dynamic">
    var mf_prefix = '{$mf_form_prefix}';
    {literal}
    $(function(){
        for (var i in mfFields) {
            (function(fields, values, index){
                var $form = null;

                if (index.indexOf('|') >= 0) {
                    var form_key = index.split('|')[1];
                    $form = $('#area_' + form_key).find('form');
                    $form = $form.length ? $form : null;
                }

                var mfHandler = new mfHandlerClass();
                mfHandler.init(mf_prefix, fields, values, $form);
            })(mfFields[i], mfFieldVals[i], i);
        }
    });
    {/literal}
    </script>
{/if}
