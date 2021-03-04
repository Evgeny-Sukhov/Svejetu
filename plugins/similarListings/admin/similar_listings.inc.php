<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : SIMILAR_LISTINGS.INC.PHP
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

$reefless->loadClass('Categories');

if ($_GET['action'] == 'build') {
    $category_key = $rlValid->xSql($_GET['key']);

    /* get current category info */
    $category_info = $rlDb->fetch(array('ID', 'Key'), array('Key' => $category_key), "AND `Status` <> 'trash'", null, 'categories', 'row');
    $category_info = $rlLang->replaceLangKeys($category_info, 'categories', array('name'), RL_LANG_CODE, 'admin');
    $rlSmarty->assign_by_ref('category_info', $category_info);

    if (!$category_info) {
        $sError = true;
    } else {
        $rlSmarty->assign('cpTitle', $category_info['name']);

        $reefless->loadClass('Builder', 'admin');

        $rlSmarty->assign('no_groups', true);

        switch ($_GET['form']) {
            case 'similar_listings_form':
                $rlBuilder->rlBuildTable = 'similar_listings_form';
                $rlBuilder->rlBuildField = 'Field_ID';

                /* additional bread crumb step */
                $bcAStep = $lang['sl_form'];
                break;
        }

        /* get available fields for current category */
        $a_fields = $rlBuilder->getAvailableFields($category_info['ID']);
        $relations = $rlBuilder->getFormRelations($category_info['ID']);

        $rlSmarty->assign_by_ref('relations', $relations);

        foreach ($relations as $rKey => $rValue) {
            $no_groups[] = $relations[$rKey]['Key'];

            $f_fields = $relations[$rKey]['Fields'];

            if ($relations[$rKey]['Group_ID']) {
                foreach ($f_fields as $fKey => $fValue) {
                    $no_fields[] = $f_fields[$fKey]['Key'];
                }
            } else {
                $no_fields[] = $relations[$rKey]['Fields']['Key'];
            }
        }

        /* get listing fields */
        if (!empty($a_fields)) {
            $a_fields[] = 88;
            $add_cond = "AND(`ID` = '" . implode("' OR `ID` = '", $a_fields) . "') ";
            $add_cond .= "AND (`Type` != 'checkboxes' AND `Type` != 'textarea' AND `Type` != 'price' ) ";
            $add_cond .= "AND (`Key` != 'account_address_on_map') ";

            $fields = $rlDb->fetch(array('ID', 'Key', 'Type', 'Status'), null, "WHERE `Status` <> 'trash' {$add_cond}", null, 'listing_fields');
            $fields = $rlLang->replaceLangKeys($fields, 'listing_fields', array('name'), RL_LANG_CODE, 'admin');

            // hide already using fields
            if (!empty($no_fields)) {
                foreach ($fields as $fKey => $fVal) {
                    if (false !== array_search($fields[$fKey]['Key'], $no_fields)) {
                        $fields[$fKey]['hidden'] = true;
                    }
                }
            }

            $rlSmarty->assign_by_ref('fields', $fields);
        }
        /* register ajax methods */
        $rlXajax->registerFunction(array('buildForm', $rlBuilder, 'ajaxBuildForm'));
    }
} else {
    $aUrl['controller'] = 'categories';
    $reefless->redirect($aUrl);
}
