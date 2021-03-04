<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : HIGHLIGHT_PAGE.INC.PHP
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
    $reefless->loadClass('Highlight', null, 'monetize');
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
            // get all highlight plans
            $highlight_plans = $rlHighlight->getPlans(false, false, 'active');
            //prepare highlight plans
            $sql = "SELECT SUM(`Highlights_available`) AS `sum`, `Days_highlight`  FROM `" . RL_DBPREFIX . "monetize_using`";
            $sql .= "WHERE `Account_ID` = {$account_info['ID']} AND `Plan_type` = 'highlight' AND `Is_unlim` = '0' ";
            $sql .= "GROUP BY `Days_highlight`";
            $h_credits = $rlDb->getAll($sql);
            if ($h_credits) {
                // creating credits plan
                $new_plan['ID'] = -1;
                $new_plan['Key'] = 'bump_up_credit';
                $new_plan['name'] = $lang['m_highlight_credits'];
                $new_plan['description'] = $lang['m_highlight_credits_description'];
                $new_plan['Price'] = -1;
                $new_plan['Type'] = 'highlight';
                
                // divide plans by Highlight Days
                if (count($h_credits) != 1) {
                    $total = 0;
                    $by_date = array();
                    foreach ($h_credits as $h_credit) {
                        $total += $h_credit['sum'];
                        $tmp['days'] = $h_credit['Days_highlight'];
                        $tmp['highlights'] = $h_credit['sum'];
                        $by_date[] = $tmp;
                    }
                    $new_plan['total'] = $total;
                    $new_plan['by_date'] = $by_date;
                } else {
                    $new_plan['Highlights'] = $h_credits[0]['sum'];
                }
                
                array_unshift($highlight_plans, $new_plan);
                $rlSmarty->assign('credits_exist', 'true');
            }
            
            // some unlimited plan was bought
            $where = "`Is_unlim` = '1' AND `Account_ID` = {$account_info['ID']} AND `Plan_type` = 'highlight'";
            $unlim_owned = $rlDb->getOne('Plan_ID', $where, 'monetize_using');
            if ($unlim_owned) {
                // if user bought unlim plan, need to remove all not bought unlim plans
                $highlight_plans = $rlMonetize->rebuildPlans($highlight_plans, $unlim_owned, 'highlights');
            }
            
            if ($highlight_plans && !$highlight_plans[0]['Price']) {
                $rlSmarty->assign('credits_exist', 'true');
            }
            
            $rlSmarty->assign('plans', $highlight_plans);
        }
    }
    
    if ($error_handler) {
        $sError = true;
        
        unset($blocks['monetize_listing_detail']);
        $rlCommon->defineBlocksExist($blocks);
    }
    
    if ($_POST && $_POST['buy_highlight']) {
        
        //redirect pages
        $cancel_url = SEO_BASE;
        $cancel_url .= $config['mod_rewrite']
            ? $page_info['Path'] . '.html?id=' . $_GET['id'] . '&canceled'
            : '?page=' . $page_info['Path'] . '&id=' . $_GET['id'] . '&canceled';
        $success_url = SEO_BASE;
        $success_url .= $config['mod_rewrite']
            ? $page_info['Path'] . '.html?id=' . $_GET['id'] . '&completed'
            : '?page=' . $page_info['Path'] . '&id=' . $_GET['id'] . '&completed';
        $plan_id = $rlValid->xSql($_POST['plan']);
        $plan_info = $rlHighlight->getPlanInfo($plan_id);
        $plan_using = $rlHighlight->getPlanUsing($plan_id);
        
        //deduct credits from the package
        if ($_POST['plan'] == -1 || $plan_using['Is_unlim'] || !$plan_info['Price']) {
            $days = $_POST['day-highlight'];
            $redirect_url = $rlHighlight->highlight($listing_info['ID'], $plan_id, $days) ? $success_url : $cancel_url;
            $reefless->redirect(null, $redirect_url);
            exit;
        } else {
            $reefless->loadClass('Payment');
            $rlPayment->clear();
            $rlPayment->setOption('service', 'highlight');
            $rlPayment->setOption('total', $plan_info['Price']);
            $rlPayment->setOption('plan_id', $plan_id);
            $rlPayment->setOption('item_id', $listing_info['ID']);
            $rlPayment->setOption('item_name', $plan_info['name'] . ' (#' . $plan_id . ')');
            $rlPayment->setOption('account_id', $account_info['ID']);
            $rlPayment->setOption('plugin', 'monetize');
            $rlPayment->setOption('params', 'monetize');
            $rlPayment->setOption('callback_class', 'rlHighlight');
            $rlPayment->setOption('callback_method', 'upgradeHighlight');
            $rlPayment->setOption('cancel_url', $cancel_url);
            $rlPayment->setOption('success_url', $success_url);
            
            $rlPayment->init($errors);
            $rlPayment->checkout($errors);
        }
    }
} else {
    $sError = true;
}

