<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : BUMP_UP_PLANS.INC.PHP
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

if ($_GET['q'] == 'ext') {
    // system config
    require_once '../../../includes/config.inc.php';
    require_once RL_ADMIN_CONTROL . 'ext_header.inc.php';
    require_once RL_LIBS . 'system.lib.php';

    $reefless->loadClass('Actions');
    $reefless->loadClass('Lang');
    $reefless->loadClass('Monetize', null, 'monetize');
    $reefless->loadClass('BumpUp', null, 'monetize');

    // data read
    $limit = intval($_GET['limit']);
    if ($_GET['action'] == 'update') {
        $update_data = array(
            'fields' => array(
                $_GET['field'] => $_GET['value'],
            ),
            'where' => array(
                'ID' => intval($_GET['id']),
            ),
        );

        $rlActions->updateOne($update_data, 'monetize_plans');
    } else {
        $start = intval($_GET['start']);
        $sql = "SELECT COUNT(`ID`) AS `count` FROM `" . RL_DBPREFIX . "monetize_plans` WHERE `Type` = 'bumpup'";
        $count = $rlDb->getRow($sql);

        $reefless->loadClass('Json');
        $output['total'] = $count['count'];
        $data = $rlBumpUp->getPlans($start, $limit);
        foreach ($data as $key => $plan) {
             $data[$key]['Bump_ups'] =  $plan['Bump_ups'] ?: $lang['unlimited'];
             $data[$key]['Price'] = $plan['Price'] ?: $lang['free'];
        }
        $output['data'] = $data;

        echo $rlJson->encode($output);
        exit;
    }
} else {
    $reefless->loadClass('Valid');
    $allLangs = $GLOBALS['languages'];
    $rlSmarty->assign_by_ref('allLangs', $allLangs);
    $reefless->loadClass('BumpUp', null, 'monetize');

    //breadcrumbs
    if ($_GET['action']) {
        $bcAStep = $_GET['action'] == 'add' ? $lang['add_bump_up'] : $lang['edit_bump_up'];
    }

    if ($_GET['action'] == 'edit' && !$_POST['edit']) {
        $id = $rlValid->xSql($_GET['id']);
        $bp_info = $rlBumpUp->getPlanInfo($id);

        $_POST['id'] = $bp_info['ID'];
        $_POST['key'] = $bp_info['Key'];
        $_POST['name'] = $bp_info['Name'];
        $_POST['bump_up_count'] = $bp_info['Bump_ups'];
        $_POST['bump_up_count_unlimited'] = !(bool) $bp_info['Bump_ups'];
        $_POST['price'] = $bp_info['Price'];
        $_POST['color'] = $bp_info['Color'];
        $_POST['status'] = $bp_info['Status'];
        
        // get name
        $where = array('Key' => 'bump_up_plan+name+' . $bp_info['Key']);
        $names = $rlDb->fetch(array('Code', 'Value'), $where, null, null, 'lang_keys');

        foreach ($names as $pKey => $pVal) {
            $_POST['name'][$names[$pKey]['Code']] = $names[$pKey]['Value'];
        }

        // get description
        $where = array('Key' => 'bump_up_plan+description+' . $bp_info['Key']);
        $descriptions = $rlDb->fetch(array('Code', 'Value'), $where, "AND `Status` <> 'trash'", null, 'lang_keys');
        foreach ($descriptions as $pKey => $pVal) {
            $_POST['description'][$descriptions[$pKey]['Code']] = $descriptions[$pKey]['Value'];
        }
    }

    // post request handler
    if ($_POST) {
        loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');

        if ($_POST['add']) {
            $plan_name = $rlValid->xSql($_POST['name']);

            foreach ($allLangs as $lkey => $lval) {
                if (empty($_POST['name'][$lkey])) {
                    $find = "<b>" . $lang['name'] . "({$allLangs[$lkey]['name']})</b>";
                    $errors[] = str_replace('{field}', $find, $lang['notice_field_empty']);
                    $error_fields[] = "name[{$lval['name']}]";
                }
            }

            if ($_POST['bump_up_count'] < 0) {
                $replace = "<b>" . $lang['bumpups_available'] . "</b>";
                $errors[] = str_replace('{h_field}', $replace, $lang['m_field_negative']);
                $error_fields[] = "name[{$lval['name']}]";
            }

            if (!empty($errors)) {
                $rlSmarty->assign_by_ref('errors', $errors);
            } else {
                $bp_description = $rlValid->xSql($_POST['description']);
                $plan_name = $rlValid->xSql($_POST['name']);
                $bump_ups = $_POST['bump_up_count_unlimited'] ? 0 : $_POST['bump_up_count'];
    
                $data['plan_info'] = array(
                    'Bump_ups' => $bump_ups,
                    'Price' => (float)$_POST['price'],
                    'Color' => $_POST['color'],
                    'Status' => $_POST['status'],
                );
                $bp_key = $sql_result = $rlBumpUp->addPlan($data['plan_info']);

                if ($bp_key) {
                    foreach ($allLangs as $key => $lang) {
                        $data['lang_keys'][] = array(
                            'Code' => $allLangs[$key]['Code'],
                            'Module' => 'common',
                            'Status' => 'active',
                            'Key' => 'bump_up_plan+name+' . $bp_key,
                            'Value' => $plan_name[$allLangs[$key]['Code']],
                            'Plugin' => 'monetize',
                        );

                        $data['lang_keys'][] = array(
                            'Code' => $allLangs[$key]['Code'],
                            'Module' => 'common',
                            'Status' => 'active',
                            'Key' => 'bump_up_plan+description+' . $bp_key,
                            'Value' => $bp_description[$allLangs[$key]['Code']],
                            'Plugin' => 'monetize',
                        );
                    }
                    $result = $rlActions->insert($data['lang_keys'], 'lang_keys');
                    $message = $lang['bumpup_plan_added'];
                } else {
                    $message = $lang['bump_up_adding_error'];
                }

                $redirect_url = array("controller" => $controller);
            }
        }

        if ($_POST['edit']) {
            $plan_id = $_POST['plan_id'];
            $bp_key = 'bumpup_' . $plan_id;
            $upd_data = array(
                'fields' => array(
                    'Bump_ups' => $_POST['bump_up_count'],
                    'Price' => $_POST['price'],
                    'Color' => $_POST['color'],
                    'Status' => $_POST['status'],
                ),
                'where' => array(
                    'ID' => $plan_id,
                ),
            );

            $sql_result = $rlActions->updateOne($upd_data, 'monetize_plans');

            foreach ($allLangs as $key => $value) {
                $where = "`Key` = 'bump_up_plan+name+{$bp_key}' AND `Code` = '{$allLangs[$key]['Code']}'";
                if ($phrase_id = $rlDb->getOne('ID', $where, 'lang_keys')) {
                    //update name
                    $upd_name = array(
                        'fields' => array(
                            'Value' => $_POST['name'][$value['Code']],
                        ),
                        'where' => array(
                            'ID' => $phrase_id,
                        ),
                    );
                    $rlActions->updateOne($upd_name, 'lang_keys');
                }

                $where = "`Key` = 'bump_up_plan+description+{$bp_key}' AND `Code` = '{$allLangs[$key]['Code']}'";
                if ($phrase_id = $rlDb->getOne('ID', $where, 'lang_keys')) {
                    //update description
                    $upd_description = array(
                        'fields' => array(
                            'Value' => $_POST['description'][$value['Code']],
                        ),
                        'where' => array(
                            'ID' => $phrase_id,
                        ),
                    );
                    $rlActions->updateOne($upd_description, 'lang_keys');
                }
            }
            $message = $lang['bumpup_plan_edited'];
            $redirect_url = array("controller" => $controller);
        }
    }
}
