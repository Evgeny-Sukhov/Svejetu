<!-- sms activation tab -->
<div id="area_smsActivation" class="tab_area{if $smarty.request.info} hide{/if}">
    <div style="padding-bottom: 5px;">{$response_message}</div>

    <div id="smsActivation_box" style="padding: 10px 0px 50px 0px;">
        {if ($isLogin && $account_info.smsActivation) || $smarty.session.registration.smsActivation}
            <div class="info">{$lang.smsActivation_activated_aa}</div>
        {else}
            <div class="submit-cell">
                <div class="name">{$lang.smsActivation_code}</div>
                <div class="field single-field two-inline left smsActivation-box-confirm">
                    <div>
                        <input type="text" id="sms_code" name="sms_code" maxlength="{$config.sms_activation_code_length}" />
                    </div>
                    <div>
                        <input type="button" id="sms_check" value="{$lang.smsActivation_confirm}" />
                    </div>                                                     
                    <span style="padding: 0 8px;">{$lang.smsActivation_or}</span>
                    <div>
                        <span class="link" id="new_code">{$lang.smsActivation_get_code}</span>
                    <div>
                </div>
            </div>
        {/if}
    </div>
    
    <script class="fl-js-dynamic">
        {literal}
        $('#new_code').flModal({
            caption: '{/literal}{$lang.warning}{literal}',
            content: '{/literal}{$lang.smsActivation_get_code_confirm}{literal}',
            prompt: 'sendSMSCode()',
            width: 'auto',
            height: 'auto'
        });

        $(document).ready(function() {
            if ($('.content-padding').length > 0) {
                $('.content-padding').append($('#area_smsActivation'));
            }
            $('input#sms_check').click(function() {
                checkSMSCode($('#sms_code').val());
            });    
        });
        
        var sendSMSCode = function() {
            $('input#new_code').val(lang['loading']);
            $.getJSON(rlConfig['ajax_url'], {mode: 'smsActivationSend'}, function(response) {
                if (response) {
                    if (response.status == 'OK') {
                        printMessage('notice', response.message_text);
                    } else {
                        printMessage('error', response.message_text);
                    }
                }
                $('input#new_code').val('{/literal}{$lang.smsActivation_get_code}{literal}');
            });        
        }

        var checkSMSCode = function(code) {
            $('input#sms_check').val(lang['loading']);
            $.getJSON(rlConfig['ajax_url'], {mode: 'smsActivationCheck', item: code, profile: true, lang: rlLang}, function(response) {
                if (response) {
                    if (response.status == 'OK') {
                        location.href = response.url;
                        return;
                    } else {
                        printMessage('error', response.message_text);
                    }
                }
                $('input#sms_check').val('{/literal}{$lang.smsActivation_confirm}{literal}');
            });
        }
        {/literal}
    </script>
</div>
<!-- sms activation tab end -->