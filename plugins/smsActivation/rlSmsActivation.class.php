<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : RLSMSACTIVATION.CLASS.PHP
 *
 *	This script is a commercial software and any kind of using it must be
 *	coordinate with Flynax Owners Team and be agree to Flynax License Agreement
 *
 *	This block may not be removed from this file or any other files with out
 *	permission of Flynax respective owners.
 *
 *	Copyrights Flynax Classifieds Software | 2020
 *	https://www.flynax.com
 *
 ******************************************************************************/

use Flynax\Utils\StringUtil;

class rlSmsActivation
{
    /**
     * @var bool access - is plugin access allowed
     **/
    public $access = false;

    /**
     * Count exists accounts
     *
     * @since 2.2.0
     *
     * @var int
     */
    public $count_exists_account = 0;

    /**
     * Mass send sms limit
     *
     * @since 2.2.0
     *
     * @var int
     */
    public static $mass_send_limit = 10;

    /**
     * Account verification details
     *
     * @since 2.2.0
     *
     * @var array
     */
    public $verification_details;

    /**
     * @hook specialBlock
     * @since 2.1.0
     */
    public function hookSpecialBlock()
    {
        global $rlSmarty, $page_info, $errors, $pages;

        if (defined('IS_LOGIN') && self::isConfigured()) {
            $avd = $this->getVerificationDetails($GLOBALS['account_info']['ID']);

            if (!$avd['smsActivation']) {
                $this->access = true;

                $allowed = array('my_profile', 'my_messages');

                /* restrict access in account area */
                $account_menu = $rlSmarty->get_template_vars('account_menu');
                foreach ($account_menu as $key => $account_menu_item) {
                    if ($account_menu_item['Key'] == 'my_profile') {
                        $account_menu[$key]['Get_vars'] = '#smsActivation_tab';
                    }
                    $account_meny_keys[] = $account_menu_item['Key'];
                    if (!in_array($account_menu_item['Key'], $allowed)) {
                        unset($account_menu[$key]);
                    }
                }
                $rlSmarty->assign_by_ref('account_menu', $account_menu);

                if ($_SESSION['smsActication_listing_id'] || $_SESSION['smsActivation_account_id']) {
                    $allowed[] = 'add_listing';
                }

                if (!in_array($page_info['Key'], $allowed)
                    && in_array($page_info['Key'], $account_meny_keys)
                ) {
                    $page_info['Controller'] = '404';
                    $activate_href = $GLOBALS['reefless']->getPageUrl('my_profile');
                    $errors[] = preg_replace(
                        '/\[(.*)\]/',
                        '<a href="' . $activate_href . '">$1</a>',
                        $GLOBALS['lang']['smsActivation_access_deny']
                    );
                }
            }
        }
    }

    /**
     * @hook profileController
     * @since 2.1.0
     */
    public function hookProfileController()
    {
        global $tabs, $lang, $rlSmarty, $account_info, $config, $errors;

        if ($this->access) {
            $tabs['smsActivation'] = array(
                'key' => 'smsActivation',
                'name' => $lang['smsActivation_tab_caption'],
                'active' => true,
            );

            $pfk = $config['sms_activation_phone_field'];
            $sms_phone_number = $account_info[$pfk];

            $message = StringUtil::replaceAssoc(
                $lang['smsActivation_profile_text'],
                array(
                    '{number}' => $config['sms_activation_code_length'],
                    '{phone}' => $sms_phone_number,
                )
            );
            $rlSmarty->assign('response_message', $message);
        }
        if ($_POST['info'] == 'account') {
            $new_phone = $_POST['account'][$pfk]['code']
                . $_POST['account'][$pfk]['area']
                . $_POST['account'][$pfk]['number']
                . $_POST['account'][$pfk]['ext'];

            $current_phone = $this->getPhone(0, $account_info);
            $current_phone = str_replace(array('+', '-', '(', ')', ' '), '', $current_phone);

            if ($current_phone != $new_phone) {
                $avd = $this->getVerificationDetails($account_info['ID']);

                if ($avd['smsActivation_count_attempts'] >= $config['sms_activation_count_attempts']) {
                    $errors[] = $lang['smsActivation_attempts_limit_exceeded'];
                    unset($_POST['info']); // temporary solution
                }
            }
        }
    }

    /**
     * @hook profileBlock
     * @since 2.1.0
     */
    public function hookProfileBlock()
    {
        global $account_info;

        if ($this->access) {
            $GLOBALS['rlSmarty']->display(RL_ROOT . 'plugins' . RL_DS . 'smsActivation' . RL_DS . 'tab.tpl');
        }

        $pfk = $GLOBALS['config']['sms_activation_phone_field'];

        $current_phone = $_POST['account'][$pfk]['code']
            . $_POST['account'][$pfk]['area']
            . $_POST['account'][$pfk]['number']
            . $_POST['account'][$pfk]['ext'];

        // load JS file
        $GLOBALS['rlStatic']->addJS(RL_TPL_BASE . 'components/popup/_popup.js', array('profile'));

        echo <<< FL
        <script class="fl-js-dynamic">
            var current_phone = '{$current_phone}';
            var is_popup = false;
            $(document).ready(function() {
                $('#area_account>form').submit(function() {
                    var code = '', area = '', number = '', ext = '';
                    var elCode = $('input[name="account[{$pfk}][code]"]');
                    var elArea = $('input[name="account[{$pfk}][area]"]');
                    var elAreaSelect = $('select[name="account[{$pfk}][area]"]');
                    var elNumber = $('input[name="account[{$pfk}][number]"]');
                    var elExt = $('input[name="account[{$pfk}][ext]"]');

                    if (elCode.length > 0) {
                        code = elCode.val();
                    }
                    if (elAreaSelect.length > 0) {
                        area = elAreaSelect.val();
                    } else {
                        area = elArea.val();
                    }
                    if (elNumber.length > 0) {
                        number = elNumber.val();
                    }
                    if (elExt.length > 0) {
                        ext = elExt.val();
                    }

                    var phone = code + area + number + ext;

                    if (current_phone != phone) {
                        if (is_popup) {
                            return true;
                        }
                        $(this).popup({
                            click: false,
                            content: '',
                            caption: '{$GLOBALS['lang']['smsActivation_confirm_change_number']}',
                            navigation: {
                                okButton: {
                                    text: '{$GLOBALS['lang']['yes']}',
                                    onClick: function(popup) {
                                        is_popup = true;
                                        $('#area_account>form').submit();
                                    }
                                },
                                cancelButton: {
                                    text: lang['cancel'],
                                    class: 'cancel'
                                }
                            }
                        });
                        return false;
                    }
                });
            });
        </script>
FL;
    }

    /**
     * @hook pageinfoArea
     * @since 2.1.0
     */
    public function hookPageinfoArea()
    {
        global $reg_steps, $lang, $page_info, $config, $account_info;

        if ($page_info['Key'] == 'registration' && self::isConfigured()) {
            if ($config['membership_module']) {
                foreach ($reg_steps as $key => $step) {
                    $steps[$key] = $step;
                    if ($key == 'plan') {
                        $steps['smsActivation'] = array(
                            'name' => $lang['smsActivation_tab_caption'],
                            'caption' => true,
                            'path' => 'sms-activation',
                        );
                    }
                }
            } else {
                foreach ($reg_steps as $key => $step) {
                    if ($key == 'done') {
                        $steps['smsActivation'] = array(
                            'name' => $lang['smsActivation_step'],
                            'caption' => true,
                            'path' => 'sms-activation',
                        );
                    }
                    $steps[$key] = $step;
                }
            }
            $reg_steps = $steps;
        }
    }

    /**
     * check settings of the plugin
     *
     * @return bool
     */
    public static function isConfigured()
    {
        if ($GLOBALS['config']['sms_activation_module'] &&
            $GLOBALS['config']['sms_activation_phone_field'] &&
            $GLOBALS['config']['sms_activation_api_key'] &&
            $GLOBALS['config']['sms_activation_method']) {
            return true;
        }

        return false;
    }

    /**
     * @hook phpRegistrationBottom
     * @since 2.1.0
     */
    public function hookPhpRegistrationBottom()
    {
        global $rlSmarty, $errors, $cur_step, $next_step, $page_info, $config, $lang, $reg_steps, $reefless;

        if ($cur_step == 'smsActivation') {
            $account_id = (int) $_SESSION['registration']['account_id'];
            $sms_account_info = $GLOBALS['rlAccount']->getProfile($account_id);
            $_SESSION['smsActication_username'] = $sms_account_info['Username'];
            $avd = $this->getVerificationDetails($account_id);

            if ($avd['smsActivation_code'] == 'done'
                || ($config['sms_activation_late_confirm'] && $_POST['confirm_late'])
            ) {
                // do redirect to next step
                $reefless->redirect(null, $reefless->getPageUrl('registration', array('step' => $next_step['path'])));
            }

            $code_exist = $avd['smsActivation_code'];
            $sms_code = $code_exist
            ? $code_exist
            : rand(
                str_repeat(1, $config['sms_activation_code_length']),
                str_repeat(9, $config['sms_activation_code_length'])
            );

            // set '0' as smsActivation for current user
            if (!$code_exist) {
                $this->updateVerificationDetails(
                    $account_id,
                    array(
                        'smsActivation' => '0',
                        'smsActivation_code' => $sms_code,
                        'smsActivation_count_attempts' => (int) $avd['smsActivation_count_attempts'] + 1,
                    )
                );
            }

            if ($avd['smsActivation_count_attempts'] >= $config['sms_activation_count_attempts']) {
                $errors[] = $lang['smsActivation_attempts_limit_exceeded'];
            }

            // check system phone fields
            if (empty($config['sms_activation_phone_field'])) {
                $errors[] = $lang['smsActivation_phone_fields_doesnot_exist'];
                $notice = preg_replace(
                    '/\[(.*)\]/',
                    '<a href="' . $reefless->getPageUrl('contact_us') . '">$1</a>',
                    $lang['smsActivation_account_approved']
                );
            }

            $sms_phone_number = $this->getPhone($account_id, $sms_account_info);

            if (!$sms_phone_number) {
                $errors[] = $lang['smsActivation_no_phone_error'];
                $notice = preg_replace(
                    '/(\[(.*)\])/',
                    '<b>$1</b>',
                    $lang['smsActivation_phone_value_doesnot_exist']
                );
            }
            $rlSmarty->assign_by_ref('notice', $notice);

            if (!$errors) {
                if (empty($avd['smsActivation_code'])) {
                    $response = $this->send($sms_code, $sms_phone_number, $lang['smsActivation_message_text']);
                    if ($response != 'OK') {
                        $errors[] = str_replace('{error}', $response, $lang['smsActivation_sending_fail']);
                        $notice = preg_replace(
                            '/\[(.*)\]/',
                            '<a href="' . $reefless->getPageUrl('contact_us') . '">$1</a>',
                            $lang['smsActivation_sending_fail_notice']
                        );
                        $isSent = false;
                        $_SESSION['registration']['smsActicationIsSent'] = false;
                    } else {
                        $reefless->loadClass('Notice');
                        $GLOBALS['rlNotice']->saveNotice($lang['smsActication_meesage_sent']);
                        // skip checkout step
                        if ($_SESSION['registration']['plan']['Price'] <= 0
                            && $next_step['path'] == $reg_steps['checkout']['path']
                        ) {
                            $GLOBALS['rlMembershipPlan']->skipRegistrationStep($next_step, 'checkout');
                        }
                        $isSent = true;
                        $_SESSION['registration']['smsActicationIsSent'] = true;
                    }
                } else {
                    if ($_SESSION['registration']['smsActicationIsSent']) {
                        $isSent = true;
                        $response = 'OK';
                    }
                }
            }

            $message = StringUtil::replaceAssoc(
                $lang[$response == 'OK' ? 'smsActication_meesage_sent_text' : 'smsActication_meesage_sent_text_fail'],
                array(
                    '{number}' => $config['sms_activation_code_length'],
                    '{phone}' => $sms_phone_number,
                )
            );

            $rlSmarty->assign('response_message', $message);
            $rlSmarty->assign('isSent', $isSent);
        }
    }

    /**
     * @hook ajaxRequest
     * @since 2.1.0
     */
    public function hookAjaxRequest(&$out, $request_mode, $request_item)
    {
        global $lang, $config, $account_info, $rlDb, $reefless;

        switch ($request_mode) {
            case 'smsActivationCheck':
                $code = $GLOBALS['rlValid']->xSql($request_item);
                $sms_username = $_SESSION['smsActication_username']
                ? $GLOBALS['rlValid']->xSql($_SESSION['smsActication_username'])
                : $_SESSION['account']['Username'];

                if (!$sms_username) {
                    $out = array(
                        'status' => 'failure',
                        'message_text' => $lang['smsActivation_sesseion_expired'],
                    );
                } else {
                    if (!$account_info && $_SESSION['registration']['account_id']) {
                        $where = array(
                            'ID' => (int) $_SESSION['registration']['account_id'],
                        );
                        $account_info = $rlDb->fetch('*', $where, null, 1, 'accounts', 'row');
                    }
                    $account_id = !$account_info && $_SESSION['registration']['account_id']
                    ? $_SESSION['registration']['account_id']
                    : $account_info['ID'];
                    $avd = $this->getVerificationDetails($account_id);

                    if ($account_info && $avd['smsActivation_code'] == $code && !empty($code)) {
                        if ($avd['smsActivation_listing_id']) {
                            $account_type = $GLOBALS['rlAccountTypes']->types[$account_info['Type']];

                            if ($config['listing_auto_approval']) {
                                $listing = $rlDb->fetch(
                                    array('ID', 'Pay_date', 'Plan_ID', 'Plan_type'),
                                    array('ID' => (int) $avd['smsActivation_listing_id']),
                                    null,
                                    1,
                                    'listings',
                                    'row'
                                );

                                $plan_table = $listing['Plan_type'] == 'account' ? 'membership_plans' : 'listing_plans';

                                $plan = $rlDb->fetch(
                                    array('ID', 'Price'),
                                    array('ID' => (int) $listing['Plan_ID']),
                                    null,
                                    1,
                                    $plan_table,
                                    'row'
                                );
                                
                                $status = $plan['Price'] <= 0 ? 'active' : 'pending';

                                $update = array(
                                    'fields' => array('Status' => $status),
                                    'where' => array('ID' => (int) $avd['smsActivation_listing_id']),
                                );
                                $rlDb->updateOne($update, 'listings');
                            }
                        }

                        $this->updateVerificationDetails(
                            $account_id,
                            array(
                                'smsActivation' => '1',
                                'smsActivation_code' => 'done',
                                'smsActivation_listing_id' => 0,
                            )
                        );

                        $is_login = $GLOBALS['rlAccount']->isLogin();
                        if (!$GLOBALS['config']['membership_module'] && !$is_login) {
                            $update = array(
                                'fields' => array(
                                    'Status' => $this->defineStatus($account_info),
                                ),
                                'where' => array('ID' => $account_id),
                            );
                            $rlDb->update($update, 'accounts');
                        }

                        if (!$is_login) {
                            $_SESSION['registration']['smsActivation'] = 1;
                        }
                        $out = array(
                            'status' => 'OK',
                            'message_text' => $lang['smsActivation_activated'],
                        );
                        if ($_REQUEST['profile']) {
                            $out['url'] = $reefless->getPageUrl('my_profile');
                            $reefless->loadClass('Notice');
                            $GLOBALS['rlNotice']->saveNotice($lang['smsActivation_activated']);
                        }
                    } else {
                        $out = array(
                            'status' => 'failure',
                            'message_text' => $lang['smsActivation_code_is_wrong'],
                        );
                    }
                }
                break;

            case 'smsActivationSend':
                $account_id = isset($account_info['ID'])
                ? (int) $account_info['ID']
                : (int) $_SESSION['registration']['account_id'];

                $avd = $this->getVerificationDetails($account_id);

                if ($avd['smsActivation_count_attempts'] >= $config['sms_activation_count_attempts']) {
                    $out = array(
                        'status' => 'failure',
                        'message_text' => $lang['smsActivation_attempts_limit_exceeded'],
                    );
                    $error = true;
                }

                $sms_phone_number = $this->getPhone($account_id, $account_info);

                if (!$sms_phone_number) {
                    $out = array(
                        'status' => 'failure',
                        'message_text' => $lang['smsActivation_no_phone_error'],
                    );
                    $error = true;
                }
                if (!$error) {
                    $sms_code = rand(
                        str_repeat(1, $config['sms_activation_code_length']),
                        str_repeat(9, $config['sms_activation_code_length'])
                    );
                    $response = $this->send($sms_code, $sms_phone_number, $lang['smsActivation_message_text']);

                    if ($response != 'OK') {
                        $mess = str_replace('{error}', $response, $lang['smsActivation_sending_fail']);
                        $out = array(
                            'status' => 'failure',
                            'message_text' => $mess,
                        );
                    } else {
                        $count_attempts = (int) $avd['smsActivation_count_attempts'] + 1;

                        if (isset($_SESSION['registration'])) {
                            $_SESSION['registration']['smsActicationIsSent'] = true;
                        }

                        $this->updateVerificationDetails(
                            $account_id,
                            array(
                                'smsActivation' => '0',
                                'smsActivation_code' => $sms_code,
                                'smsActivation_count_attempts' => $count_attempts,
                            )
                        );
                        $out = array(
                            'status' => 'OK',
                            'message_text' => str_replace(
                                '{number}',
                                $sms_phone_number,
                                $lang['smsActivation_regenerated']
                            ),
                        );
                    }
                }
                break;

            case 'smsActivationLateConfirm':
                if (!$account_info && $_SESSION['registration']['account_id']) {
                    $where = array(
                        'ID' => (int) $_SESSION['registration']['account_id'],
                    );
                    $account_info = $rlDb->fetch('*', $where, null, 1, 'accounts', 'row');
                }
                $update = array(
                    'fields' => array(
                        'status' => $this->defineStatus($account_info),
                    ),
                    'where' => array('ID' => $account_info['ID']),
                );
                if ($rlDb->update($update, 'accounts')) {
                    $out = array(
                        'status' => 'OK',
                        'url' => $reefless->getPageUrl('registration', array('step' => $_REQUEST['step'])),
                    );
                }
                break;

            case 'smsActivationCheckType':
                $type_id = (int) $request_item;
                $result = $this->isNoPhoneField($type_id);

                $out = array(
                    'no_phone' => $result ? true : false,
                );
                break;
        }
    }

    /**
     * get phone number
     *
     * @param  integer $account_id
     * @param  array $account_info
     * @return string
     */
    public function getPhone($account_id = 0, $account_info = array())
    {
        global $rlDb;

        $pfk = $GLOBALS['config']['sms_activation_phone_field'];

        if (!$account_info) {
            $account_info = $rlDb->fetch('*', array('ID' => (int) $account_id), null, 1, 'accounts', 'row');
        }
        $sms_phone_number = '';
        if ($pfk) {
            $sms_phone_number = $account_info[$pfk];

            // adapt account phone
            if (preg_match('/c?a?n\:/', $sms_phone_number)) {
                $sql = "SELECT * FROM `{db_prefix}account_fields` WHERE `Key` = '{$pfk}' LIMIT 1";
                $phone_field = $rlDb->getRow($sql);

                if ($phone_field) {
                    $sms_phone_number = $GLOBALS['reefless']->parsePhone($account_info[$pfk], $phone_field);
                }
            }
            if (is_array($sms_phone_number)) {
                if ($phone_field['Opt1'] && $sms_phone_number['code']) {
                    $phone = '+' . $sms_phone_number['code'] . ' ';
                }
                if ($sms_phone_number['area']) {
                    $phone .= "({$sms_phone_number['area']}) ";
                }
                if ($sms_phone_number['number']) {
                    $phone .= $GLOBALS['reefless']->flStrSplit($sms_phone_number['number'], 4, '-');
                }
                if ($field['Opt2'] && $sms_phone_number['ext']) {
                    $phone .= ' ' . $GLOBALS['lang']['phone_ext_out'] . $sms_phone_number['ext'];
                }
                $sms_phone_number = $phone;
            }
        }

        return $sms_phone_number;
    }

    /**
     * @hook ajaxRequest
     * @since 2.1.0
     */
    public function hookRegistrationStepActionsTpl()
    {
        global $cur_step;

        if ($cur_step == 'smsActivation') {
            $GLOBALS['rlSmarty']->display(RL_ROOT . 'plugins' . RL_DS . 'smsActivation' . RL_DS . 'step.tpl');
        }
    }

    /**
     * @hook begistrationBegin
     * @since 2.1.0
     */
    public function hookRegistrationBegin()
    {
        global $cur_step, $reg_steps, $reefless;

        $is_add = false;
        $steps = array();
        foreach ($reg_steps as $key => $step) {
            if ($key == 'smsActivation') {
                $is_add = true;
                continue;
            }
            if ($is_add) {
                $steps[] = $key;
            }
        }

        if (in_array($cur_step, $steps)) {
            $sms_account_info = $GLOBALS['rlAccount']->getProfile((int) $_SESSION['registration']['account_id']);

            if ($sms_account_info['smsActivation_code'] != 'done'
                && !$GLOBALS['config']['sms_activation_late_confirm']
            ) {
                $reefless->redirect(false, $reefless->getPageUrl('registration', array('step' => 'sms-activation')));
                exit;
            }
        }

    }

    /**
     * send request to sms server
     *
     * @param string $sms_code
     * @param mixed $phone
     * @param string $message
     *
     * @return mixed
     */
    public function send($sms_code = '', $phone = '', $message = '')
    {
        global $config;

        if (!$phone || !$message) {
            return false;
        }
        $phone = str_replace(array('+', '-', '(', ')', ' '), '', $phone);
        $sms_text = urlencode(str_replace('{code}', $sms_code, $message));

        if ($config['sms_activation_method'] == 'rest') {
            require_once __DIR__ . '/vendor/autoload.php';

            $clickatell = new \Clickatell\Rest($config['sms_activation_api_key']);

            try {
                $response = $clickatell->sendMessage(array('to' => array($phone), 'content' => $sms_text));
            } catch (\Clickatell\ClickatellException $e) {
                $result = $GLOBALS['lang']['smsActivation_rest_error'];
                $GLOBALS['rlDebug']->logger('SMS Activation Plugin, Clickatell Error: ' . $e->getMessage());
                return $result;
            }
        } else {
            $clickatell_url = 'https://platform.clickatell.com/messages/http/send?';
            $request = 'apiKey=' . $config['sms_activation_api_key'];
            $request .= '&to=' . $phone . '&content=' . $sms_text;
            $response = $GLOBALS['reefless']->getPageContent($clickatell_url . $request);
            $response = json_decode($response, true);
            $response = $response['messages'];
        }

        if (!empty($response)) {
            foreach ($response as $key => $item) {
                if ($item['accepted']) {
                    $result = 'OK';
                } else {
                    $result = $item['error'];
                }
            }
        } else {
            $result = $GLOBALS['lang']['smsActivation_rest_error'];
        }

        return $result;
    }

    /**
     * Define account status
     *
     * @since 2.2.0
     *
     * @param  array $account_info
     * @return string
     */
    public function defineStatus($account_info)
    {
        $GLOBALS['reefless']->loadClass('AccountTypes');
        $account_type = $GLOBALS['rlAccountTypes']->types[$account_info['Type']];

        if (!defined('REALM')) {
            if ($account_type['Admin_confirmation'] && !$account_type['Email_confirmation']) {
                $status = 'pending';
            } elseif ($account_type['Email_confirmation']) {
                $status = 'incomplete';
            } else {
                $status = 'active';
            }
        } else {
            $status = 'active';
        }

        return $status;
    }

    /**
     * Mass send SMS to users
     *
     * @since 2.2.0
     *
     * @param int $start
     */
    public function deactivateExists($start = 0)
    {
        global $rlDb, $config, $rlMail;

        $status = 'OK';
        $GLOBALS['reefless']->loadClass('Mail');

        $sql = "SELECT `T1`.*, `T2`.`smsActivation`, `T2`.`smsActivation_code`, ";
        $sql .= "`T2`.`smsActivation_exists`, `T2`.`smsActivation_count_attempts` ";
        $sql .= "FROM `{db_prefix}accounts` AS `T1` ";
        $sql .= "LEFT JOIN `{db_prefix}sms_activation_details` AS `T2` ON `T1`.`ID` = `T2`.`Account_ID` ";
        $sql .= "WHERE `T2`.`smsActivation_exists` = '1' AND `T1`.`Status` <> 'trash' ";
        $sql .= " LIMIT " . self::$mass_send_limit;

        $accounts = $rlDb->getAll($sql);

        $page_info = $rlDb->fetch('*', array('Key' => 'my_profile'), null, 1, 'pages', 'row');

        $profile_url = RL_URL_HOME;
        $profile_url .= $config['mod_rewrite']
        ? '[lang]' . $page_info['Path'] . '.html'
        : 'index.php?page=' . $page_info['Path'] . '&language=[lang]';

        if ($accounts) {
            foreach ($accounts as $account) {
                if ($account['Mail']) {
                    $profile_url_tmp = str_replace(
                        '[lang]',
                        $config['lang'] != $account['Lang'] ? $account['Lang'] . '/' : '',
                        $profile_url
                    );
                    $phone = $this->getPhone(0, $account);
                    $sms_code = rand(
                        str_repeat(1, $config['sms_activation_code_length']),
                        str_repeat(9, $config['sms_activation_code_length'])
                    );

                    $mail_tpl = $rlMail->getEmailTemplate('smsActivation_deactivate_account');

                    $mail_tpl['body'] = StringUtil::replaceAssoc(
                        $mail_tpl['body'],
                        array(
                            '{name}' => trim($account['First_name'] || $account['Last_name']
                                ? $account['First_name'] . ' ' . $account['Last_name']
                                : $account['Username']),
                            '{profile}' => '<a href="' . $profile_url_tmp . '">' . $GLOBALS['lang']['smsActivation_profile'] . '</a>',
                        )
                    );

                    $rlMail->send($mail_tpl, $account['Mail']);

                    $this->updateVerificationDetails(
                        $account['ID'],
                        array(
                            'smsActivation' => '0',
                            'smsActivation_code' => $sms_code,
                            'smsActivation_exists' => '0',
                        )
                    );
                }
            }
            if (count($accounts) == self::$mass_send_limit) {
                if ($start == 0) {
                    $_SESSION['smsActivation_total'] = $this->getCountExistsAccounts();
                }
                $out['start'] = $start + self::$mass_send_limit;
                $out['total'] = (int) $_SESSION['smsActivation_total'];
            }
        }
        $out['status'] = $status;

        return $out;
    }

    /**
     * Get count exists accounts
     *
     * @since 2.2.0
     *
     * @return int
     */
    public function getCountExistsAccounts()
    {
        if (!$this->count_exists_account) {
            $sql = "SELECT COUNT(`T1`.`ID`) AS `count` ";
            $sql .= "FROM `{db_prefix}accounts` AS `T1` ";
            $sql .= "LEFT JOIN `{db_prefix}sms_activation_details` AS `T2` ON `T1`.`ID` = `T2`.`Account_ID` ";
            $sql .= "WHERE `T2`.`smsActivation_exists` = '1' AND `T1`.`Status` <> 'trash' ";

            $this->count_exists_account = (int) $GLOBALS['rlDb']->getRow($sql, 'count');
        }

        return $this->count_exists_account;
    }

    /**
     * @hook apTplAccountsGrid
     * @since 2.2.0
     */
    public function hookApTplAccountsGrid()
    {
        global $lang;

        $status = $lang['pending'] . ' (' . $lang['smsActivation_status'] . ')';

        echo <<< FL
    var gridInstance = accountsGrid.getInstance();
    var columns = [];
    var j = 0;
    for (var i = 0; i < gridInstance.columns.length; i++) {
        columns[j++] = gridInstance.columns[i];
        if (gridInstance.columns[i]['dataIndex'] == 'Status' && i > 0) {
            gridInstance.columns[i]['width'] = 140;
            gridInstance.columns[i]['editor'] = new Ext.form.ComboBox({
                store: [
                    ['active', lang['ext_active']],
                    ['approval', lang['ext_approval']]
                ],
                mode: 'local',
                typeAhead: true,
                triggerAction: 'all',
                selectOnFocus: true,
                listeners: {
                    beforeselect: function(combo, record){
                        var index = combo.gridEditor.row;
                        var row = accountsGrid.grid.store.data.items[index];
                        if (row.data.smsActivation == '0' && row.data.smsActivation_code != '') {
                            Ext.MessageBox.confirm(
                                lang['warning'],
                                '{$lang['smsActivation_notice_activate']}',
                                function(btn) {
                                if (btn == 'yes') {
                                    $.getJSON(
                                        rlConfig['ajax_url'],
                                        {item: 'activateAccount', id: row.data.ID},
                                        function(response) {
                                        if (response.status == 'OK') {
                                            printMessage('notice', response.message);
                                            accountsGrid.reload();
                                        } else {
                                            printMessage('error', response.message);
                                        }
                                    });
                                }
                            });

                            return false;
                        }
                    }
                }
            });
            gridInstance.columns[i]['renderer'] = function(val, obj, row){
                if (val == lang['ext_active'] && row.data.smsActivation == '1') {
                    obj.style += 'background: #d2e798;';
                } else if (val == lang['ext_approval']) {
                    obj.style += 'background: #ffe7ad;';
                } else if (val == lang['ext_expired']) {
                    obj.style += 'background: #fbc4c4;';
                } else if (val == lang['ext_new']) {
                    obj.style += 'background: #fbc4c4;';
                } else if (val == lang['ext_reviewed']) {
                    obj.style += 'background: #d2e798;';
                } else if (val == lang['ext_pending'] || val == lang['ext_replied']) {
                    obj.style += 'background: #c0ecee;';
                } else if (val == lang['ext_incomplete']
                    || val == lang['ext_canceled']
                    || val == lang['incompatible']
                ) {
                    obj.style += 'background: #e0e0e0;';
                } else if (val == 'not_installed' || val == 'pending') {
                    obj.style += 'background: #f9cece;';
                } else if (val == lang['ext_active']
                    && row.data.smsActivation == '0'
                    && row.data.smsActivation_code != ''
                ) {
                    obj.style += 'background: #c0ecee;';
                }
                if (row.data.smsActivation == '0' && row.data.smsActivation_code != '') {
                    return '<span ext:qtip="'+lang['ext_click_to_edit']+'">{$status}</span>';
                }
                return '<span ext:qtip="'+lang['ext_click_to_edit']+'">'+val+'</span>';
            };
        }
    }
    gridInstance.columns = columns;
    gridInstance.fields.push({name: 'smsActivation', mapping: 'smsActivation'});
    gridInstance.fields.push({name: 'smsActivation_code', mapping: 'smsActivation_code'});
    accountsGrid = new gridObj(gridInstance);
FL;
    }

    /**
     * @hook apExtAccountsSql
     * @since 2.2.0
     */
    public function hookApExtAccountsSql()
    {
        global $sql;

        $sql_replace = 'accounts` AS `T1` ';
        $sql_replace .= 'LEFT JOIN `{db_prefix}sms_activation_details` AS `T3` ON `T1`.`ID` = `T3`.`Account_ID` ';

        $sql = StringUtil::replaceAssoc($sql, array(
            'SQL_CALC_FOUND_ROWS' => 'SQL_CALC_FOUND_ROWS `T3`.`smsActivation`, `T3`.`smsActivation_code`,',
            'accounts` AS `T1`' => $sql_replace,
        ));
    }

    /**
     * @hook apAjaxRequest
     * @since 2.2.0
     */
    public function hookApAjaxRequest(&$out, $item)
    {
        global $rlDb;

        switch ($item) {
            case 'activateAccount':
                $id = (int) $_REQUEST['id'];
                $this->updateVerificationDetails(
                    $id,
                    array(
                        'smsActivation' => '1',
                        'smsActivation_code' => 'done',
                    )
                );

                $update = array(
                    'fields' => array(
                        'Status' => 'active',
                    ),
                    'where' => array('ID' => $id),
                );
                $result = $rlDb->updateOne($update, 'accounts');

                $out = array(
                    'status' => $result ? 'OK' : 'ERROR',
                    'message' => $GLOBALS['lang']['smsActivation_' . ($result ? 'success' : 'error')],
                );
                break;

            case 'smsActivationHandleExists':
                $update = array(
                    'fields' => array(
                        'smsActivation_exists' => '0',
                    ),
                    'where' => array('smsActivation_exists' => '1'),
                );
                $result = $rlDb->updateOne($update, 'sms_activation_details');

                $out['status'] = $result ? 'OK' : 'ERROR';
                break;

            case 'smsActivationDeactivate':
                $out = $this->deactivateExists((int) $_REQUEST['start']);
                break;

            case 'smsActivationResetAttempts':
                $sql = "UPDATE `{db_prefix}sms_activation_details` SET `smsActivation_count_attempts` = 0";

                $out['status'] = $rlDb->query($sql) ? 'OK' : 'ERROR';
                break;
        }
    }

    /**
     * @hook  apMixConfigItem
     * @since 2.2.0
     */
    public function hookApMixConfigItem(&$value)
    {
        switch ($value['Key']) {
            case 'sms_activation_method':
                foreach ($value['Values'] as $key => $item) {
                    $value['Values'][$key] = array(
                        'ID' => $item,
                        'name' => $GLOBALS['lang']['smsActivation_' . $item],
                    );
                }
                break;

            case 'sms_activation_phone_field':
                $fields = $this->getPhoneFields('account');
                $value['Values'] = array();
                foreach ($fields as $key => $item) {
                    $value['Values'][$key] = array(
                        'ID' => $item['Key'],
                        'name' => $GLOBALS['lang']['account_fields+name+' . $item['Key']],
                    );
                }
                break;

            case 'sms_activation_phone_field_listing':
                $fields = $this->getPhoneFields('listing');
                $value['Values'] = array();
                foreach ($fields as $key => $item) {
                    $value['Values'][$key] = array(
                        'ID' => $item['Key'],
                        'name' => $GLOBALS['lang']['listing_fields+name+' . $item['Key']],
                    );
                }
                break;
        }
    }

    /**
     * Get list fields by type
     *
     * @since 2.2.0
     *
     * @param  string $type
     * @return array
     */
    public function getPhoneFields($type = '')
    {
        $sql = "SELECT * FROM `{db_prefix}{$type}_fields` WHERE `Type` = 'phone' AND `Status` = 'active'";
        $phone_fields = $GLOBALS['rlDb']->getAll($sql);

        return $phone_fields;
    }

    /**
     * Get phone field by key
     *
     * @since 2.2.0
     *
     * @param  string $key
     * @param  string $type
     * @return array
     */
    public function getPhoneField($key = '', $type = '')
    {
        return $GLOBALS['rlDb']->getRow("SELECT * FROM `{db_prefix}{$type}_fields` WHERE `Key` = '{$key}'");
    }

    /**
     * @hook  apNotifications
     * @since 2.2.0
     */
    public function hookApNotifications(&$notifications)
    {
        global $lang;

        if ($GLOBALS['config']['sms_activation_activate_exists']) {
            $url = RL_URL_HOME . ADMIN . "/index.php?controller=accounts";

            $notifications[] = str_replace(
                '{link}',
                '<a target="_blank" href="' . $url . '">' . $lang['accounts'] . '</a>',
                $lang['smsActivation_has_unverified_accounts']
            );
        }
    }

    /**
     * @hook  apTplAccountsBottom
     * @since 2.2.0
     */
    public function hookApTplAccountsBottom()
    {
        global $lang;

        $is_configured = self::isConfigured();
        echo <<< FL
        <script>
            var is_configured = '{$is_configured}';
        </script>
FL;
        if ($is_configured) {
            $phrase_popup = str_replace(
                '{count}',
                $this->getCountExistsAccounts(),
                $lang['smsActivation_activate_exists']
            );
        } else {
            $phrase_popup = $lang['smsActivation_need_settings'];
            $sgroup = $GLOBALS['rlDb']->getOne('ID', "`Key` = 'smsActivation_group'", 'config_groups');
            $url = RL_URL_HOME . ADMIN . "/index.php?controller=settings&group=" . $sgroup;

            echo <<< FL
            <script>
                var smsActivationPopupSettings = function() {
                    Ext.MessageBox.show({
                        title:'{$lang['smsActivation_popup_title']}',
                        msg: '{$phrase_popup}',
                        buttons: {ok: '{$lang['yes']}', cancel: '{$lang['cancel']}'},
                        icon: Ext.Msg.QUESTION,
                        width: 570,
                        fn: function(btn) {
                            if (btn == "ok") {
                                location.href = '{$url}';
                            }
                        }
                    });
                }
            </script>
FL;
        }

        if ($this->getCountExistsAccounts() > 0) {
            if ($GLOBALS['config']['sms_activation_activate_exists']) {
                echo <<< FL
                <script>
                    $(document).ready(function(){
                        if (is_configured) {
                            smsActivationPopup();
                        } else {
                            smsActivationPopupSettings();
                        }
                    });
                </script>
FL;
            }

            echo <<< FL
            <script>
                $(document).ready(function(){
                    $('.smsActivation-deactivate').click(function() {
                        if (is_configured) {
                            smsActivationPopup();
                        } else {
                            smsActivationPopupSettings();
                        }
                    });
                });

                var smsActivationPopup = function() {
                    Ext.MessageBox.show({
                        title:'{$lang['smsActivation_popup_title']}',
                        msg: '{$phrase_popup}',
                        buttons: {ok: '{$lang['smsActivation_keep_active']}', yes: '{$lang['smsActivation_deactivate']}'},
                        icon: Ext.Msg.QUESTION,
                        width: 570,
                        fn: function(btn) {
                            if (btn == "ok") {
                                smsActivationHandleExists();
                            }
                            if (btn == "yes") {
                                Ext.getBody().mask('{$lang['smsActivation_progress']} 0%');
                                smsActivationMassSend(0);
                            }
                        }
                    });
                }

                var smsActivationHandleExists = function() {
                    $.getJSON(rlConfig['ajax_url'], {item: 'smsActivationHandleExists'}, function(response) {
                        if (response && response.status) {
                            if (response.status == 'OK') {
                                $('.smsActivation-deactivate').remove();
                                printMessage('notice', '{$lang['smsActivation_activate_exists_success']}');
                            } else {
                                printMessage('error', '{$lang['smsActivation_activate_exists_fail']}');
                            }
                        }
                    });
                }

                var smsActivationMassSend = function(start) {
                    if (!start) {
                        start = 0;
                    }
                    $.getJSON(rlConfig['ajax_url'], {item: 'smsActivationDeactivate', start: start}, function(response) {
                        if (response && response.status) {
                            if (response.status == 'OK') {
                                if (response.start) {
                                    var percent = Math.ceil((response.start * 100) / response.total);
                                    percent = percent > 100 ? 100 : percent;
                                    $('.ext-el-mask-msg>div').html('{$lang['smsActivation_progress']} ' + percent + '%');
                                    smsActivationMassSend(response.start);
                                } else {
                                    $('.smsActivation-deactivate').remove();
                                    printMessage('notice', '{$lang['smsActivation_send_notify_success']}');
                                }
                            } else {
                                printMessage('error', '{$lang['smsActivation_send_notify_fail']}');
                            }
                            if (!response.start) {
                                Ext.getBody().unmask();
                                accountsGrid.reload();
                            }
                        }
                    });
                }
            </script>
FL;
            if ($GLOBALS['config']['sms_activation_activate_exists']) {
                $update = array(
                    'fields' => array(
                        'Default' => '0',
                    ),
                    'where' => array('Key' => 'sms_activation_activate_exists'),
                );
                $GLOBALS['rlDb']->updateOne($update, 'config');
            }
        }

        echo <<< FL
        <script>
            $(document).ready(function(){
                $('.smsActivation-reset-attempts').click(function() {
                    if (is_configured) {
                        Ext.MessageBox.confirm(
                            lang['warning'],
                            '{$lang['smsActivation_notice_reset_attempts']}',
                            function(btn) {
                            if (btn == 'yes') {
                                smsActivationResetAttempts();
                            }
                        });
                    } else {
                        smsActivationPopupSettings();
                    }
                });
            });

            var smsActivationResetAttempts = function() {
                $.getJSON(rlConfig['ajax_url'], {item: 'smsActivationResetAttempts'}, function(response) {
                    if (response && response.status) {
                        if (response.status == 'OK') {
                            printMessage('notice', '{$lang['smsActivation_reset_attempts_success']}');
                        } else {
                            printMessage('error', '{$lang['smsActivation_reset_attempts_fail']}');
                        }
                    }
                });
            }
        </script>
FL;

    }

    /**
     * @hook  apTplAccountsNavBar
     * @since 2.2.0
     */
    public function hookApTplAccountsNavBar()
    {
        if (!isset($_GET['action'])) {
            if ($this->getCountExistsAccounts() > 0) {
                $btn = '<a href="javascript:void(0)" class="button_bar smsActivation-deactivate">';
                $btn .= '<span class="left"></span>';
                $btn .= '<span class="center_lock">' . $GLOBALS['lang']['smsActivation_deactivate'] . '</span>';
                $btn .= '<span class="right"></span>';
                $btn .= '</a>' . PHP_EOL;

                echo $btn;
            }

            $btn = '<a href="javascript:void(0)" class="button_bar smsActivation-reset-attempts">';
            $btn .= '<span class="left"></span>';
            $btn .= '<span class="center_unlock">' . $GLOBALS['lang']['smsActivation_reset_attempts'] . '</span>';
            $btn .= '<span class="right"></span>';
            $btn .= '</a>';

            echo $btn;
        }
    }

    /**
     * @hook  profileEditProfileDone
     * @since 2.2.0
     */
    public function hookProfileEditProfileDone()
    {
        global $reefless;

        $avd = $this->getVerificationDetails($GLOBALS['account_info']['ID']);

        if (self::isConfigured()
            && !$avd['smsActivation']
            && $avd['smsActivation_code'] != 'done'
        ) {
            $reefless->loadClass('Notice');
            $GLOBALS['rlNotice']->saveNotice($GLOBALS['lang']['notice_profile_edited']);

            $aUrl = array('info' => 'profile');
            $reefless->redirect($aUrl);
        }
    }

    /**
     * @hook  staticDataRegister
     * @since 2.2.0
     */
    public function hookStaticDataRegister()
    {
        global $rlStatic;

        // load CSS file
        $rlStatic->addHeaderCSS(
            RL_PLUGINS_URL . 'smsActivation/static/style.css',
            array('profile', 'registration', 'add_listing')
        );
    }

    /**
     * Adapt phone number to DB
     *
     * @since 2.2.0
     *
     * @param  array $field
     * @param  array $data
     * @return string
     */
    public function adaptPhone($field = array(), $data = array())
    {
        global $rlValid;

        $out = '';

        /* code */
        if ($field['Opt1']) {
            $code = $rlValid->xSql(substr($data['code'], 0, $field['Default']));
            $out = 'c:' . $code . '|';
        }

        /* area */
        $area = $rlValid->xSql($data['area']);
        $out .= 'a:' . $area . '|';

        /* number */
        $number = $rlValid->xSql(substr($data['number'], 0, $field['Values']));
        $out .= 'n:' . $number;

        /* extension */
        if ($field['Opt2']) {
            $ext = $rlValid->xSql($data['ext']);
            $out .= '|e:' . $ext;
        }

        return $out;
    }

    /**
     * @hook  beforeRegister
     * @since 2.2.0
     */
    public function hookBeforeRegister()
    {
        global $account_data, $fields, $errors, $account_id;

        $sms_phone_field = $GLOBALS['config']['sms_activation_phone_field'];

        if ($sms_phone_field) {
            foreach ($fields as $fKey => $fVal) {
                if ($fVal['Key'] == $sms_phone_field) {
                    $field_phone = $fVal;
                }
            }
            $phone_adapted = $this->adaptPhone($field_phone, $account_data[$sms_phone_field]);
            $exist = $GLOBALS['rlDb']->getOne('ID', "`{$sms_phone_field}` = '{$phone_adapted}'", 'accounts');
            if ($exist && (empty($account_id) || $exist != $account_id)) {
                $errors[] = str_replace(
                    '{phone}',
                    $this->getPhone(0, $account_data),
                    $GLOBALS['lang']['smsActivation_account_phone_exist']
                );
            }
        }
    }

    /**
     * @hook  profileEditAccountValidate
     * @since 2.2.0
     */
    public function hookProfileEditAccountValidate()
    {
        global $account_info, $config, $lang;

        $pfk = $config['sms_activation_phone_field'];

        $new_phone = $_POST['account'][$pfk]['code']
            . $_POST['account'][$pfk]['area']
            . $_POST['account'][$pfk]['number']
            . $_POST['account'][$pfk]['ext'];

        $current_phone = $this->getPhone(0, $account_info);
        $current_phone = str_replace(array('+', '-', '(', ')', ' '), '', $current_phone);

        if ($current_phone != $new_phone) {
            $avd = $this->getVerificationDetails($account_info['ID']);
            $sms_code = rand(
                str_repeat(1, $config['sms_activation_code_length']),
                str_repeat(9, $config['sms_activation_code_length'])
            );

            $count_attempts = (int) $avd['smsActivation_count_attempts'] + 1;

            $this->updateVerificationDetails(
                $account_info['ID'],
                array(
                    'smsActivation' => '0',
                    'smsActivation_code' => $sms_code,
                    'smsActivation_count_attempts' => $count_attempts,
                )
            );

            $response = $this->send($sms_code, $new_phone, $lang['smsActivation_message_text']);

            if ($response != 'OK') {
                $GLOBALS['rlDebug']->logger("smsActivation plugin: {$response}");
            }
        }
    }

    /**
     * @hook  phpRegistrationTop
     * @since 2.2.0
     */
    public function hookPhpRegistrationTop()
    {
        global $reg_steps, $rlDb;

        $request = explode('/', $_GET['rlVareables']);
        $request_step = array_pop($request);
        $get_step = $request_step ? $request_step : $_GET['step'];

        $cur_step = $GLOBALS['rlAccount']->stepByPath($reg_steps, $get_step);
        if ($cur_step == 'account') {
            $account_type_id = (int) $_SESSION['registration']['profile']['type'];

            if ($this->isNoPhoneField($account_type_id)) {
                unset($reg_steps['smsActivation']);
                $_SESSION['registration']['no_smsActivation_step'] = true;
            }
        }
    }

    /**
     * Check if no phone number on registration form
     *
     * @since 2.2.0
     *
     * @param  int $type_id
     * @return bool
     */
    public function isNoPhoneField($type_id = 0)
    {
        $field = $this->getPhoneField($GLOBALS['config']['sms_activation_phone_field'], 'account');
        $type_id = (int) $type_id;
        $field_id = (int) $field['ID'];

        $sql = "SELECT * FROM `{db_prefix}account_submit_form` ";
        $sql .= "WHERE `Category_ID` = {$type_id} AND `Field_ID` = {$field_id}";
        $form = $GLOBALS['rlDb']->getAll($sql);

        if (count($form) <= 0) {
            return true;
        }

        return false;
    }

    /**
     * @hook  registerSuccess
     * @since 2.2.0
     */
    public function hookRegisterSuccess()
    {
        if ($_SESSION['registration']['no_smsActivation_step']) {
            $account_id = (int) $_SESSION['registration']['account_id'];
            if ($this->verifyAccount($account_id)) {
                unset($_SESSION['registration']['no_smsActivation_step']);
            }
        }
    }

    /**
     * @hook  apPhpAccountsAfterAdd
     * @since 2.2.0
     */
    public function hookApPhpAccountsAfterAdd()
    {
        $account_id = (int) $_SESSION['registration']['account_id'];
        $this->verifyAccount($account_id);
    }

    /**
     * Verify account without confirmation
     *
     * @since 2.2.0
     *
     * @param  int $account_id
     * @return bool
     */
    public function verifyAccount($account_id = 0)
    {
        if ($account_id) {
            return $this->updateVerificationDetails(
                $account_id,
                array(
                    'smsActivation' => '1',
                    'smsActivation_code' => 'done',
                )
            );
        }
        return false;
    }

    /**
     * @hook  addListingTop
     * @since 2.2.0
     */
    public function hookAddListingTop(&$steps)
    {
        $is_phone_exists = true;

        /**
         * Temporary solution when value of type on quick registration form empty
         * @todo - Remove this code when compatibility will be >= 4.7.0
         */
        if ($_POST['register'] && empty($_POST['register']['type'])) {
            $types = $GLOBALS['rlAccountTypes']->types;

            if ($types) {
                foreach ($types as $type) {
                    if ($type['Quick_registration']) {
                        $_POST['register']['type'] = $type['ID'];
                        break;
                    }
                }
            }
        }

        if ($_POST['register'] && $this->isNoPhoneField((int) $_POST['register']['type'])) {
            $is_phone_exists = false;
        }

        if (defined('IS_LOGIN')) {
            $avd = $this->getVerificationDetails($GLOBALS['account_info']['ID']);
        }

        if (self::isConfigured()
            && !$_SESSION['no_smsActivation_step']
            && $is_phone_exists
            && (!$avd['smsActivation'] || ($avd['smsActivation'] && $_POST['step'] == 'smsActivation'))
        ) {
            $sms_activation_step = array(
                'name' => $GLOBALS['lang']['smsActivation_tab_caption'],
                'path' => 'sms-activation',
                'caption' => true,
                'plugin' => 'smsActivation',
                'class' => 'rlSmsActivation',
                'method' => 'step',
                'tpl' => 'step_add_listing',
            );

            // Find checkout step index
            $index = array_search('checkout', array_keys($steps));

            if (!$index) {
                $index = count($steps);
            }

            // Add step
            $steps = array_merge(
                array_slice($steps, 0, $index, true),
                array('sms-activation' => $sms_activation_step),
                array_slice($steps, $index, null, true)
            );
        }
    }

    /**
     * @hook  addListingPreFields
     * @since 2.2.0
     */
    public function hookAddListingPreFields()
    {
        global $rlSmarty;

        if (!$GLOBALS['rlAccount']->isLogin() && self::isConfigured()) {
            $phone_field_listing = false;
            $form = $rlSmarty->_tpl_vars['form'];

            if ($form) {
                foreach ($form as $fKey => $fVal) {
                    if ($fVal['Fields']) {
                        foreach ($fVal['Fields'] as $ffKey => $ffVal) {
                            if ($ffKey == $GLOBALS['config']['sms_activation_phone_field_listing']) {
                                $phone_field_listing = true;
                                break 2;
                            }
                        }
                    }
                }
            }

            $_SESSION['smsActivation_phone_field_listing'] = $phone_field_listing ? true : false;

            if (!$phone_field_listing) {
                $phone_field = $this->getPhoneField($GLOBALS['config']['sms_activation_phone_field'], 'account');

                $sql = "SELECT `T1`.`ID`, `T1`.`Key`, `T2`.`Field_ID` AS `phone_field` ";
                $sql .= "FROM `{db_prefix}account_types` AS `T1` ";
                $sql .= "LEFT JOIN `{db_prefix}account_submit_form` AS `T2` ";
                $sql .= "ON `T2`.`Category_ID` = `T1`.`ID` AND `T2`.`Field_ID` = '{$phone_field['ID']}' ";
                $sql .= "WHERE `T1`.`Status` = 'active' AND `T1`.`Quick_registration` = '1' AND `T2`.`Field_ID` > 0";

                $quick_types = $GLOBALS['rlDb']->getAll($sql, 'ID');

                if (count($quick_types) > 0) {
                    $phone_field['pName'] = 'account_fields+name+' . $phone_field['Key'];
                    $phone_field = array($phone_field);

                    $rlSmarty->assign_by_ref('phone_fields', $phone_field);
                    $rlSmarty->assign_by_ref('quick_types', $quick_types);
                    $rlSmarty->display(RL_ROOT . 'plugins' . RL_DS . 'smsActivation' . RL_DS . 'phone.tpl');
                }
            }
        }
    }

    /**
     * SMS activation step
     *
     * @since 2.2.0
     *
     * @param object $instance
     */
    public function step($instance)
    {
        global $errors, $config, $lang, $reefless, $rlSmarty, $account_info;

        $_SESSION['smsActication_username'] = $account_info['Username'];
        $avd = $this->getVerificationDetails($account_info['ID']);

        if ($avd['smsActivation_code'] == 'done'
            || ($config['sms_activation_late_confirm'] && $_POST['confirm_late'])
        ) {
            $instance->redirectToNextStep();
            exit;
        }

        $code_exist = $avd['smsActivation_code'];
        $sms_code = $code_exist
        ? $code_exist
        : rand(
            str_repeat(1, $config['sms_activation_code_length']),
            str_repeat(9, $config['sms_activation_code_length'])
        );

        // set '0' as smsActivation for current user
        $this->updateVerificationDetails(
            $account_info['ID'],
            array(
                'smsActivation' => '0',
                'smsActivation_code' => $sms_code,
                'smsActivation_count_attempts' => (int) $avd['smsActivation_count_attempts'] + 1,
                'smsActivation_listing_id' => $instance->listingID,
            )
        );

        if ($avd['smsActivation_count_attempts'] >= $config['sms_activation_count_attempts']) {
            $errors[] = $lang['smsActivation_attempts_limit_exceeded'];
        }

        // check system phone fields
        if (empty($config['sms_activation_phone_field'])) {
            $errors[] = $lang['smsActivation_phone_fields_doesnot_exist'];
            $notice = preg_replace(
                '/\[(.*)\]/',
                '<a href="' . $reefless->getPageUrl('contact_us') . '">$1</a>',
                $lang['smsActivation_account_approved']
            );
        }

        $sms_phone_number = $this->getPhone(0, $account_info);

        if (!$sms_phone_number) {
            $errors[] = $lang['smsActivation_no_phone_error'];
            $notice = preg_replace(
                '/(\[(.*)\])/',
                '<b>$1</b>',
                $lang['smsActivation_phone_value_doesnot_exist']
            );
        }
        $rlSmarty->assign_by_ref('notice', $notice);

        if (!$errors) {
            if (empty($account_info['smsActivation_code'])) {
                $response = $this->send($sms_code, $sms_phone_number, $lang['smsActivation_message_text']);
                if ($response != 'OK') {
                    $errors[] = str_replace('{error}', $response, $lang['smsActivation_sending_fail']);
                    $notice = preg_replace(
                        '/\[(.*)\]/',
                        '<a href="' . $reefless->getPageUrl('contact_us') . '">$1</a>',
                        $lang['smsActivation_sending_fail_notice']
                    );
                    $isSent = false;
                } else {
                    $reefless->loadClass('Notice');
                    $GLOBALS['rlNotice']->saveNotice($lang['smsActication_meesage_sent']);

                    $isSent = true;
                }
            }
        }

        $message = StringUtil::replaceAssoc(
            $lang[$response == 'OK' ? 'smsActication_meesage_sent_text' : 'smsActication_meesage_sent_text_fail'],
            array(
                '{number}' => $config['sms_activation_code_length'],
                '{phone}' => $sms_phone_number,
            )
        );

        $rlSmarty->assign('response_message', $message);
        $rlSmarty->assign('isSent', $isSent);
        $rlSmarty->assign('isVerified', false);
    }

    /**
     * Get account verification details
     *
     * @since 2.2.0
     *
     * @param  int $account_id
     * @return array
     */
    public function getVerificationDetails($account_id = 0)
    {
        if ($this->verification_details) {
            return $this->verification_details;
        }

        $sql = "SELECT * FROM `{db_prefix}sms_activation_details` WHERE `Account_ID` = {$account_id} LIMIT 1";
        $this->verification_details = $GLOBALS['rlDb']->getRow($sql);

        return $this->verification_details;
    }

    /**
     * Update account verification details
     *
     * @since 2.2.0
     *
     * @param int $account_id
     * @param array $data
     */
    public function updateVerificationDetails($account_id = 0, $data = array())
    {
        global $rlDb;

        if ($rlDb->getOne('ID', "`Account_ID` = {$account_id}", 'sms_activation_details')) {
            $update = array(
                'fields' => $data,
                'where' => array(
                    'Account_ID' => $account_id,
                ),
            );
            if ($this->verification_details) {
                foreach ($this->verification_details as $key => $val) {
                    if (isset($data[$key])) {
                        $this->verification_details[$key] = $data[$key];
                    }
                }
            }
            $result = $rlDb->updateOne($update, 'sms_activation_details');
        } else {
            $data['Account_ID'] = $account_id;
            $result = $rlDb->insertOne($data, 'sms_activation_details');
        }

        return $result;
    }

    /**
     * @hook  phpAddListingQuickRegistrationValidate
     * @since 2.2.0
     */
    public function hookPhpAddListingQuickRegistrationValidate(&$register_data, &$errors, &$error_fields)
    {
        global $config, $lang;

        if (self::isConfigured()) {
            if ($this->isNoPhoneField((int) $register_data['type'])) {
                $_SESSION['no_smsActivation_step'] = true;
                return;
            }

            unset($_SESSION['no_smsActivation_step']);

            if ($_SESSION['smsActivation_phone_field_listing']) {
                $phone = $_POST['f'][$config['sms_activation_phone_field_listing']];
                $field = $this->getPhoneField($config['sms_activation_phone_field_listing'], 'listing');
                $register_data[$config['sms_activation_phone_field_listing']] = $phone;
            } else {
                $phone = $_POST['f'][$config['sms_activation_phone_field']];
                $field = $this->getPhoneField($config['sms_activation_phone_field'], 'account');
                $register_data[$config['sms_activation_phone_field']] = $phone;
            }

            $phone_name = $lang['account_fields+name+' . $config['sms_activation_phone_field']];

            if ($field['Required']
                && ((empty($phone['code']) && $field['Opt1']) || empty($phone['area']) || empty($phone['number']))
            ) {
                $errors[] = str_replace(
                    '{field}',
                    '<span class="field_error">"' . $phone_name . '"</span>',
                    $lang['notice_phone_field_error']
                );

                if (empty($phone['code']) && $field['Opt1']) {
                    $error_fields .= "f[{$field['Key']}][code],";
                }
                if (empty($phone['area'])) {
                    $error_fields .= "f[{$field['Key']}][area],";
                }
                if (empty($phone['number'])) {
                    $error_fields .= "f[{$field['Key']}][number],";
                }
            } elseif (!$field['Required']
                && (((!empty($phone['area']) && !$field['Condition'])
                    || !empty($phone['number'])
                    || (!empty($phone['code']) && $field['Opt1'])
                )
                    && (empty($phone['area']) || empty($phone['number']) || (empty($phone['code']) && $field['Opt1']))
                )
            ) {
                $errors[] = str_replace(
                    '{field}',
                    '<span class="field_error">"' . $phone_name . '"</span>',
                    $lang['notice_phone_field_error']
                );
            }

            if ($field && $phone) {
                $phone_adapted = $this->adaptPhone($field, $phone);
                $exist = $GLOBALS['rlDb']->getOne('ID', "`{$field['Key']}` = '{$phone_adapted}'", 'accounts');
                if ($exist) {
                    $errors[] = str_replace(
                        '{phone}',
                        $this->getPhone(0, $register_data),
                        $lang['smsActivation_account_phone_exist']
                    );
                }
            }
        }
    }

    /**
     * @hook  phpAddListingAfterQuickRegistration
     * @since 2.2.0
     */
    public function hookPhpAddListingAfterQuickRegistration($new_account, $register_data)
    {
        if (self::isConfigured()) {
            if (!$_SESSION['no_smsActivation_step']) {
                $fk = $GLOBALS['config']['sms_activation_phone_field'];
                $field = $this->getPhoneField($fk, 'account');

                if ($field && isset($register_data[$fk])) {
                    $phone_adapted = $this->adaptPhone($field, $register_data[$fk]);

                    if ($_SESSION['account']) {
                        $_SESSION['account'][$fk] = $phone_adapted;
                    }
                    $update = array(
                        'fields' => array($fk => $phone_adapted),
                        'where' => array('ID' => (int) $new_account[2]),
                    );
                    $GLOBALS['rlDb']->updateOne($update, 'accounts');

                    $_SESSION['smsActivation_account_id'] = (int) $new_account[2];
                }
            } else {
                $this->verifyAccount((int) $new_account[2]);
            }
        }
    }

    /**
     * @hook  afterListingCreate
     * @since 2.2.0
     */
    public function hookAfterListingCreate($addListing)
    {
        $avd = $this->getVerificationDetails($GLOBALS['account_info']['ID']);

        if (self::isConfigured() && !$_SESSION['no_smsActivation_step'] && !$avd['smsActivation']) {
            $_SESSION['smsActication_listing_id'] = $addListing->listingID;
            unset($_SESSION['smsActivation_account_id']);
        } else {
            unset($_SESSION['smsActication_listing_id']);
        }
    }

    /**
     * @hook  afterListingEdit
     * @since 2.2.0
     */
    public function hookAfterListingEdit($addListing)
    {
        $avd = $this->getVerificationDetails($GLOBALS['account_info']['ID']);

        if (self::isConfigured()
            && $GLOBALS['page_info']['Controller'] == 'add_listing'
            && !$_SESSION['no_smsActivation_step']
            && !$avd['smsActivation']
        ) {
            $_SESSION['smsActication_listing_id'] = $addListing->listingID;
            unset($_SESSION['smsActivation_account_id']);
        } else {
            unset($_SESSION['smsActication_listing_id']);
        }
    }

    /**
     * @hook  addListingBottom
     * @since 2.2.0
     */
    public function hookAddListingBottom($addListing)
    {
        global $rlDb;

        if (self::isConfigured() && $addListing->step == 'done') {
            if ($_SESSION['smsActication_listing_id']) {
                $avd = $this->getVerificationDetails($GLOBALS['account_info']['ID']);
                
                $listing = $rlDb->fetch(
                    array('ID', 'Pay_date'),
                    array('ID' => (int) $_SESSION['smsActication_listing_id']),
                    null,
                    1,
                    'listings',
                    'row'
                );

                $status = $avd['smsActivation'] && strtotime($listing['Pay_date']) > 0
                ? 'active'
                : 'pending';

                $update = array(
                    'fields' => array('Status' => $status),
                    'where' => array('ID' => (int) $_SESSION['smsActication_listing_id']),
                );
                $rlDb->updateOne($update, 'listings');
            }
            unset($_SESSION['no_smsActivation_step'], $_SESSION['smsActication_listing_id']);
        }
    }

    /**
     * @hook  deleteAccountSetItems
     * @since 2.2.0
     */
    public function hookDeleteAccountSetItems($id)
    {
        if (self::isConfigured()) {
            $GLOBALS['rlDb']->query("DELETE FROM `{db_prefix}sms_activation_details` WHERE `Account_ID` = '{$id}'");
        }
    }

    /**
     * @hook  addListingFormDataChecking
     * @since 2.2.0
     */
    public function hookAddListingFormDataChecking()
    {
        global $config;

        if (self::isConfigured()
            && !defined('IS_LOGIN')
            && !isset($_POST[$config['sms_activation_phone_field']])
        ) {
            $_POST[$config['sms_activation_phone_field']] = $_POST['f'][$config['sms_activation_phone_field']];
        }
    }

    /**
     * @deprecated 2.2.0
     */
    public function profileTab()
    {}

    /**
     * @deprecated 2.2.0
     *
     * Check code
     *
     * @package XAJAX
     * @param int $code - sms code
     **/
    public function ajax_check($code = false)
    {}

    /**
     * @deprecated 2.2.0
     *
     * Check code on profile page
     *
     * @package XAJAX
     * @param int $code - sms code
     **/
    public function ajax_checkp($code = false)
    {}

    /**
     * @deprecated 2.2.0
     *
     * Send new code
     *
     * @package XAJAX
     * @param int $code - sms code
     **/
    public function ajax_sendCode($code = false)
    {}
}
