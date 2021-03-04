<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : MONETIZE.INC.PHP
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
    require_once '../../../includes/config.inc.php';
}

//Include modules
$module = isset($_GET['module']) ? $_GET['module'] : 'bump_up_plans';

if (is_file(RL_PLUGINS . 'monetize' . RL_DS . 'admin' . RL_DS . $module . '.inc.php')) {
    require_once(RL_PLUGINS . 'monetize' . RL_DS . 'admin' . RL_DS . $module . '.inc.php');
} else {
    $sError = true;
}

if ($sql_result) {
    $reefless->loadClass('Notice');
    $rlNotice->saveNotice($message);
    $reefless->redirect($redirect_url);
}