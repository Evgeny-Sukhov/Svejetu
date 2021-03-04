<!-- sms activation tab -->
<div class="area_smsActivation step_area content-padding sms-activation-step">
    <div class="sms-activation-step-message">{$response_message}</div>

    <div id="area_smsActivation" class="sms-activation-step-area">
        <div class="submit-cell">
            <div class="name">{$lang.smsActivation_code}</div>
            <div class="field single-field">
                <div id="smsActivation_confirm" class="sms-activation-step-confirm{if !$isSent} hide{/if}">
                    <input class="w120" type="text" id="sms_code" name="sms_code" maxlength="{$config.sms_activation_code_length}" />
                    <input type="button" id="sms_check" value="{$lang.smsActivation_confirm}" />
                    <span class="sms-activation-step-or">{$lang.smsActivation_or}</span>
                </div>
                <input id="new_code" name="new_code" type="button" value="{$lang.smsActivation_get_code}"  />
            </div>
        </div>
    </div>
    <form id="form-smsActivation" method="post" action="{buildFormAction}">
        <input type="hidden" name="step" value="smsActivation" />
        <input type="hidden" name="confirm_late" value="{if $config.sms_activation_late_confirm}1{else}0{/if}" />

        <div class="form-buttons no-top-padding">
            <a href="{buildPrevStepURL}">{$lang.perv_step}</a>
            <input type="submit" {if !$config.sms_activation_late_confirm}disabled="disabled"{/if} value="{if !$isVerified && $config.sms_activation_late_confirm}{$lang.smsActivation_late}{else}{$lang.next_step}{/if}" class="registration-next-step" />
        </div>
    </form>
    
    <script class="fl-js-dynamic">
        {literal}
        $('input[name=new_code]').flModal({
            caption: '{/literal}{$lang.warning}{literal}',
            content: '{/literal}{$lang.smsActivation_get_code_confirm}{literal}',
            prompt: 'sendSMSCode()',
            width: 'auto',
            height: 'auto'
        });

        $(document).ready(function(){
            $('input#sms_check').click(function() {
                checkSMSCode($('#sms_code').val());
            });    
        });
        
        var sendSMSCode = function() {
            $('input#new_code').val(lang['loading']);
            $.getJSON(rlConfig['ajax_url'], {mode: 'smsActivationSend', page: 'add_listing'}, function(response) {
                if (response) {
                    if (response.status == 'OK') {
                        printMessage('notice', response.message_text);
                        $('#smsActivation_confirm').removeClass('hide');
                    } else {
                        printMessage('error', response.message_text);
                    }
                }
                $('input#new_code').val('{/literal}{$lang.smsActivation_get_code}{literal}');
            });        
        }

        var checkSMSCode = function(code) {
            $('input#sms_check').val(lang['loading']);
            $.getJSON(rlConfig['ajax_url'], {mode: 'smsActivationCheck', item: code, page: 'add_listing'}, function(response) {
                if (response) {
                    if (response.status == 'OK') {
                        printMessage('notice', response.message_text);
                        var $rnsEl = $('.registration-next-step');
                        $rnsEl.attr('disabled', false);
                        $rnsEl.val('{/literal}{$lang.next_step}{literal}');
                        $('#area_smsActivation').html('<div class="info">{/literal}{$lang.smsActivation_activated_aa}{literal}</div>');
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
