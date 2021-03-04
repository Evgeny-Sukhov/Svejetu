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
require_once('../../../includes/config.inc.php');
require_once(RL_ADMIN_CONTROL . 'ext_header.inc.php');
require_once(RL_LIBS . 'system.lib.php');

$reefless->loadClass('Json');
$reefless->loadClass('Actions');
$reefless->loadClass('Cache');
$reefless->loadClass('Categories');
$reefless->loadClass('Listings');

if ($config['membership_module']) { 
    $reefless ->  loadClass('Account');
    $reefless ->  loadClass('MembershipPlan');
}

$limit = $_SESSION['iel_data']['post']['import_per_run'];
$start = (int)$_GET['index'];

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
	$_SESSION['iel_data']['post']['import_account_id'],
	$_SESSION['iel_data']['post']['import_plan_id'],
	$_SESSION['iel_data']['post']['import_paid'],
	$_SESSION['iel_data']['post']['import_status']
);

$items['from'] = $start + $limit;
$items['to'] = $start + ($limit * 2) - 1;
$items['count'] = count($available_rows);

echo $rlJson ->  encode($items);
