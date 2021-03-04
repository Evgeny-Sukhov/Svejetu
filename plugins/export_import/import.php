<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : IMPORT.PHP
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

set_time_limit(0);

/* system config */
require_once('../../includes/config.inc.php');
require_once(RL_INC . 'control.inc.php');

$reefless->loadClass('Actions');
$reefless->loadClass('ListingTypes', null, false, true);
$reefless->loadClass('Json');

$limit = $_SESSION['iel_data']['post']['import_per_run'];
$start = (int)$_GET['index'];

$account_info = $_SESSION['account'];

/* exit if user not logged in */
$reefless -> loadClass( 'Account' );

if ($config['membership_module']) {
    $reefless ->  loadClass('MembershipPlan');
}
if ( !$rlAccount -> isLogin() )
	exit;

/* prepare available listings */
foreach ($_SESSION['iel_data']['post']['rows_tmp'] as $index => $val)
{
	$available_rows[] = $index;
}

$reefless -> loadClass('ExportImport', null, 'export_import');
$rlExportImport -> import(
	$_SESSION['iel_data']['post']['data'],
	$available_rows,
	$_SESSION['iel_data']['post']['cols'],
	$_SESSION['iel_data']['post']['field'],
	$start,
	$limit,
	$_SESSION['iel_data']['post']['import_listing_type'],
	$_SESSION['iel_data']['post']['import_category_id'],
	$account_info['ID'],
	$_SESSION['iel_data']['post']['import_plan_id'],
	false,
	$_SESSION['iel_data']['post']['import_status'],
	true
);

$items['from'] = $start + $limit;
$items['to'] = $start + ($limit * 2) - 1;
$items['count'] = count($available_rows);

echo $rlJson ->  encode($items);
