<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : TESTIMONIALS.INC.PHP
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

/* ext js action */
if ($_GET['q'] == 'ext')
{
	/* system config */
	require_once( '../../../includes/config.inc.php' );
	require_once( RL_ADMIN_CONTROL . 'ext_header.inc.php' );
	require_once( RL_LIBS . 'system.lib.php' );
	
	/* date update */
	if ( $_GET['action'] == 'update' )
	{
		$reefless -> loadClass( 'Actions' );
		
		$type = $rlValid -> xSql( $_GET['type'] );
		$field = $rlValid -> xSql( $_GET['field'] );
		$value = $rlValid -> xSql( nl2br($_GET['value']) );
		$id = $rlValid -> xSql( $_GET['id'] );
		$key = $rlValid -> xSql( $_GET['key'] );

		$updateData = array(
			'fields' => array(
				$field => $value
			),
			'where' => array(
				'ID' => $id
			)
		);
		
		$rlActions -> updateOne( $updateData, 'testimonials');
		exit;
	}
	
	/* data read */
	$limit = (int)$_GET['limit'];
	$start = (int)$_GET['start'];
	
	$sql = "SELECT SQL_CALC_FOUND_ROWS `T1`.* ";
	$sql .= "FROM `". RL_DBPREFIX ."testimonials` AS `T1` ";
	$sql .= "LEFT JOIN `". RL_DBPREFIX ."accounts` AS `T2` ON `T1`.`Account_ID` = `T2`.`ID` ";
	$sql .= "ORDER BY `T1`.`ID` DESC ";
	$sql .= "LIMIT {$start}, {$limit}";
	$data = $rlDb -> getAll($sql);
	
	$count = $rlDb -> getRow("SELECT FOUND_ROWS() AS `testimonials`");
	
	foreach ($data as $key => $value)
	{
		$data[$key]['Status'] = $lang[$data[$key]['Status']];
	}

	$reefless -> loadClass('Json');
	
	$output['total'] = $count['count'];
	$output['data'] = $data;

	echo $rlJson -> encode( $output );
}
else
{
	$reefless -> loadClass('Testimonials', null, 'testimonials');
	$rlXajax -> registerFunction(array('deleteTestimonial', $rlTestimonials, 'ajaxDelete'));
}
/* ext js action end */