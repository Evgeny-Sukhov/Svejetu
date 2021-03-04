<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : HIGHLIGHT_PLANS.INC.PHP
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
    $reefless->loadClass('Highlight', null, 'monetize');

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
        $sql = "SELECT COUNT(`ID`) AS `count` FROM `" . RL_DBPREFIX . "monetize_plans` WHERE `Type` = 'highlight'";
        $count = $rlDb->getRow($sql);

        $reefless->loadClass('Json');
        $output['total'] = $count['count'];
        $data = $rlHighlight->getPlans($start, $limit);
        foreach ($data as $key => $plan) {
            $data[$key]['Highlights'] =  $plan['Highlights'] ?: $lang['unlimited'];
            $data[$key]['Price'] = $plan['Price'] ?: $lang['free'];
        }
        $output['data'] = $data;

        echo $rlJson->encode($output);
        exit;
    }
} else {
    $reefless->loadClass('Valid');
    $reefless->loadClass('Monetize', null, 'monetize');
    $reefless->loadClass('Highlight', null, 'monetize');
    $allLangs = $GLOBALS['languages'];
    $rlSmarty->assign_by_ref('allLangs', $allLangs);

    // breadcrumbs
    if ($_GET['action']) {
        $bcAStep = $_GET['action'] == 'add' ? $lang['m_add_highlight'] : $lang['m_edit_highlight'];
    }

    // simulate forms on "Edit" action
    if ($_GET['action'] == 'edit' && !$_POST['edit']) {
        $id = $rlValid->xSql($_GET['id']);
        $h_info = $rlHighlight->getPlanInfo($id);
        $_POST['id'] = $h_info['ID'];
        $_POST['key'] = $h_info['Key'];
        $_POST['name'] = $h_info['Name'];
        $_POST['highlight_count'] = $h_info['Highlights'];
        $_POST['highlight_count_unlimited'] = !(bool) $h_info['Highlights'];
        $_POST['price'] = $h_info['Price'];
        $_POST['color'] = $h_info['Color'];
        $_POST['highlight_days'] = $h_info['Days'];
        $_POST['status'] = $h_info['Status'];
        
        // get name
        $where = array('Key' => 'highlight_plan+name+' . $h_info['Key']);
        $names = $rlDb->fetch(array('Code', 'Value'), $where, null, null, 'lang_keys');
        foreach ($names as $pKey => $pVal) {
            $_POST['name'][$names[$pKey]['Code']] = $names[$pKey]['Value'];
        }

        // get description
        $where = array('Key' => 'highlight_plan+description+' . $h_info['Key']);
        $descriptions = $rlDb->fetch(array('Code', 'Value'), $where, "AND `Status` <> 'trash'", null, 'lang_keys');
        foreach ($descriptions as $pKey => $pVal) {
            $_POST['description'][$descriptions[$pKey]['Code']] = $descriptions[$pKey]['Value'];
        }
    }

    // form submit handlers
    if ($_POST) {
        loadUTF8functions('ascii', 'utf8_to_ascii', 'unicode');

        if ($_POST['add']) {
            
            $plan_name = $rlValid->xSql($_POST['name']);
            $plan_description = $rlValid->xSql($_POST['description']);

            foreach ($allLangs as $lkey => $lval) {
                if (empty($_POST['name'][$lkey])) {
                    $find = "<b>" . $lang['name'] . "({$allLangs[$lkey]['name']})</b>";
                    $errors[] = str_replace('{field}', $find, $lang['notice_field_empty']);
                    $error_fields[] = "name[{$lval['name']}]";
                }
            }

            if ($_POST['highlight_count'] < 0) {
                $replace = "<b>" . $lang['m_highlight_available'] . "</b>";
                $errors[] = str_replace('{h_field}', $replace, $lang['m_field_negative']);
                $error_fields[] = "name[highlight_count]";
            }

            if (empty($_POST['highlight_days'])) {
                $find = "<b>" . $lang['m_days_highlight'] . "</b>";
                $errors[] = str_replace('{h_field}', $find, $lang['m_field_empty']);
                $error_fields[] = "name[{$lval['name']}]";
            }

            if (!empty($errors)) {
                $rlSmarty->assign_by_ref('errors', $errors);
            } else {
                $hp_description = $rlValid->xSql($_POST['description']);
                $plan_name = $rlValid->xSql($_POST['name']);
                $highlights = $_POST['highlight_count_unlimited'] ? 0 : $_POST['highlight_count'];
    
                $data['plan_info'] = array(
                    'Highlights' => $highlights,
                    'Price' => (float)$_POST['price'],
                    'Color' => $_POST['color'],
                    'Days' => (int)$_POST['highlight_days'],
                    'Status' => $_POST['status'],
                );
                $h_key = $sql_result = $rlHighlight->addPlan($data['plan_info']);

                if ($h_key) {
                    foreach ($allLangs as $key => $lang) {
                        $lang_keys[] = array(
                            'Code' => $allLangs[$key]['Code'],
                            'Module' => 'common',
                            'Status' => 'active',
                            'Key' => 'highlight_plan+name+' . $h_key,
                            'Value' => $plan_name[$allLangs[$key]['Code']],
                            'Plugin' => 'monetize',
                        );

                        $lang_keys[] = array(
                            'Code' => $allLangs[$key]['Code'],
                            'Module' => 'common',
                            'Status' => 'active',
                            'Key' => 'highlight_plan+description+' . $h_key,
                            'Value' => $hp_description[$allLangs[$key]['Code']],
                            'Plugin' => 'monetize',
                        );
                    }
                    $result = $rlActions->insert($lang_keys, 'lang_keys');
                    $message = $lang['m_highlight_plan_added'];
                } else {
                    $message = $lang['m_highlight_save_error'];
                }
                $redirect_url = array("controller" => $controller);
            }
        }

        if ($_POST['edit']) {
            $plan_id = $_POST['plan_id'];
            $bp_key = 'highlight_' . $plan_id;

            $upd_data = array(
                'fields' => array(
                    'Highlights' => (int)$_POST['highlight_count'],
                    'Price' => (float)$_POST['price'],
                    'Color' => $_POST['color'],
                    'Days' => (int)$_POST['highlight_days'],
                    'Status' => $_POST['status'],
                ),
                'where' => array(
                    'ID' => $plan_id,
                ),
            );
            
            $sql_result = $rlActions->updateOne($upd_data, 'monetize_plans');

            foreach ($allLangs as $key => $value) {
                $where = "`Key` = 'highlight_plan+name+{$bp_key}' AND `Code` = '{$allLangs[$key]['Code']}'";
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

                $where = "`Key` = 'highlight_plan+description+{$bp_key}' AND `Code` = '{$allLangs[$key]['Code']}'";
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
        }
        $redirect_url = array("controller" => $controller, 'module' => 'highlight_plans');
    }
}
