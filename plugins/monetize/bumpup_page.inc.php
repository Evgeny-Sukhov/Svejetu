<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : BUMPUP_PAGE.INC.PHP
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

if (defined('IS_LOGIN')) {
    $reefless->loadClass('Monetize', null, 'monetize');
    $reefless->loadClass('BumpUp', null, 'monetize');
    $rlSmarty->assign('back_url', $_SESSION['m_back_to']);
    $error_handler = false;
    if (isset($_GET['id']) || isset($_GET['completed']) || isset($_GET['canceled'])) {
        $reefless->loadClass('Valid');
        $id = (int)$_GET['id'];
        $error_handler = true;
        $reefless->loadClass('Listings');
        $listing_info = $rlListings->getListing($id);
        if ($id && $listing_info['Account_ID'] == $account_info['ID']) {
            $rlSmarty->assign('listing_info', $listing_info);
            $error_handler = false;
            //get all plans
            $bumpup_plans = $rlBumpUp->getPlans(false, false, 'active');
            
            //prepare bump up credits plan
            $sql = "SELECT SUM(`Bumpups_available`) AS `sum` FROM `" . RL_DBPREFIX . "monetize_using`";
            $sql .= "WHERE `Account_ID` = {$account_info['ID']} AND `Plan_type` = 'bumpup'";
            $bumpup_credits = $rlDb->getRow($sql);
            if ($bumpup_credits['sum']) {
                $new_plan['ID'] = -1;
                $new_plan['Key'] = 'bump_up_credit';
                $new_plan['name'] = $lang['bumpups_credits'];
                $new_plan['description'] = $lang['bumpup_creditplan_des'];
                $new_plan['Bump_ups'] = $bumpup_credits['sum'];
                $new_plan['Price'] = -1;
                array_unshift($bumpup_plans, $new_plan);
                $rlSmarty->assign('credits_exist', 'true');
            }
            
            //some unlimited plan was bought
            $where = "`Is_unlim` = '1' AND `Account_ID` = {$account_info['ID']} AND `Plan_type` = 'bumpup'";
            $unlim_owned = $rlDb->getOne('Plan_ID', $where, 'monetize_using');
            if ($unlim_owned) {
                //if user bought unlim plan, need to remove all not bought unlim plans
                $bumpup_plans = $rlMonetize->rebuildPlans($bumpup_plans, $unlim_owned);
            }
            
            if ($bumpup_plans && !$bumpup_plans[0]['Price']) {
                $rlSmarty->assign('credits_exist', 'true');
            }
            
            $rlSmarty->assign('plans', $bumpup_plans);
        }
    }
    
    if ($error_handler) {
        $sError = true;
        
        unset($blocks['monetize_listing_detail']);
        $rlCommon->defineBlocksExist($blocks);
    }
    
    if ($_POST && $_POST['buy_bumpup']) {
        // redirect pages
        $cancel_url = SEO_BASE;
        $cancel_url .= $config['mod_rewrite']
            ? $page_info['Path'] . '.html?id=' . $_GET['id'] . '&canceled'
            : '?page=' . $page_info['Path'] . '&id=' . $_GET['id'] . '&canceled';
        $success_url = SEO_BASE;
        $success_url .= $config['mod_rewrite']
            ? $page_info['Path'] . '.html?id=' . $_GET['id'] . '&completed'
            : '?page=' . $page_info['Path'] . '&id=' . $_GET['id'] . '&completed';
        $plan_id = $rlValid->xSql($_POST['plan']);
        $plan_info = $rlBumpUp->getPlanInfo($plan_id);
        $plan_using = $rlBumpUp->getPlanUsing($plan_id);
        
        // deduct credits from the package
        if ($_POST['plan'] == -1 || $plan_using['Is_unlim'] || !$plan_info['Price']) {
            $redirect_url = $rlBumpUp->bumpUp($listing_info['ID'], $plan_id) ? $success_url : $cancel_url;
            $reefless->redirect(null, $redirect_url);
            exit;
        } else {
            $reefless->loadClass('Payment');
            $rlPayment->clear();
            $rlPayment->setOption('service', 'bump_up');
            $rlPayment->setOption('total', $plan_info['Price']);
            $rlPayment->setOption('plan_id', $plan_id);
            $rlPayment->setOption('item_id', $listing_info['ID']);
            $rlPayment->setOption('item_name', $plan_info['name'] . ' (#' . $plan_id . ')');
            $rlPayment->setOption('account_id', $account_info['ID']);
            $rlPayment->setOption('plugin', 'monetize');
            $rlPayment->setOption('params', 'monetize');
            $rlPayment->setOption('callback_class', 'rlBumpUp');
            $rlPayment->setOption('callback_method', 'upgradeBumpUp');
            $rlPayment->setOption('cancel_url', $cancel_url);
            $rlPayment->setOption('success_url', $success_url);
            
            $rlPayment->init($errors);
            $rlPayment->checkout($errors);
        }
    }
} else {
    $sError = true;
}
