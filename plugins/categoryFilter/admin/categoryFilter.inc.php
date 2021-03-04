<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : CATEGORYFILTER.INC.PHP
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

use Flynax\Utils\Valid;

if ($_GET['q'] == 'ext') {
    require '../../../includes/config.inc.php';
    require RL_ADMIN_CONTROL . 'ext_header.inc.php';
    require RL_LIBS . 'system.lib.php';

    if ($_GET['action'] == 'update') {
        $type  = Valid::escape($_GET['type']);
        $field = Valid::escape($_GET['field']);
        $value = Valid::escape(nl2br($_GET['value']));
        $id    = Valid::escape($_GET['id']);
        $key   = Valid::escape($_GET['key']);

        $rlDb->updateOne(
            ['fields' => [$field => $value], 'where'  => ['Key' => 'categoryFilter_' . $id]],
            'blocks'
        );
        exit;
    }

    $limit = (int) $_GET['limit'];
    $start = (int) $_GET['start'];

    $sql = "SELECT SQL_CALC_FOUND_ROWS `T1`.`ID`, `T1`.`Category_IDs`, `T1`.`Mode`, `T1`.`Type`, ";
    $sql .= "`T2`.`Status`, `T2`.`Tpl`, `T2`.`Side` ";
    $sql .= "FROM `{db_prefix}category_filter` AS `T1` ";
    $sql .= "LEFT JOIN `{db_prefix}blocks` AS `T2` ON CONCAT('categoryFilter_', `T1`.`ID`) = `T2`.`Key` ";
    $sql .= "ORDER BY `T1`.`ID` ASC ";
    $sql .= "LIMIT {$start}, {$limit}";
    $data  = $rlDb->getAll($sql);
    $count = $rlDb->getRow('SELECT FOUND_ROWS() AS `count`', 'count');

    foreach ($data as $key => $value) {
        $data[$key]['Status'] = $lang[$data[$key]['Status']];
        $data[$key]['Tpl']    = $data[$key]['Tpl'] ? $lang['yes'] : $lang['no'];
        $data[$key]['Side']   = $lang[$data[$key]['Side']];
        $data[$key]['Name']   = $rlLang->getPhrase(
            'blocks+name+categoryFilter_' . $value['ID'],
            RL_LANG_CODE,
            false,
            true
        );

        if ($value['Mode'] == 'type') {
            $data[$key]['Mode'] = $lang['listing_type'];
        } elseif ($value['Mode'] == 'search_results') {
            $data[$key]['Mode'] = $lang['category_filter_filter_for_search_results'];
        } elseif ($value['Mode'] == 'field_bound_boxes') {
            $data[$key]['Mode'] = $lang['category_filter_filter_for_field_bound_boxes'];
        } else {
            $data[$key]['Mode'] = $lang['category_filter_filter_mode_category'];
        }

        if ($value['Mode'] == 'category') {
            if ($value['Category_IDs']) {
                $sql = "SELECT SQL_CALC_FOUND_ROWS `Key` FROM `{db_prefix}categories` ";
                $sql .= "WHERE FIND_IN_SET(`ID`, '{$value['Category_IDs']}') > 0 LIMIT 5";
                $categoriesKeys = $rlDb->getAll($sql);
                $categoriesCount = $rlDb->getRow('SELECT FOUND_ROWS() AS `count`', 'count');

                if ($categoriesKeys) {
                    foreach ($categoriesKeys as $categoryKey) {
                        $name = $rlLang->getPhrase("categories+name+{$categoryKey['Key']}", RL_LANG_CODE, false, true);
                        $data[$key]['Categories'] .= $name;
                        $data[$key]['Categories'] .= ', ';
                    }

                    $data[$key]['Categories'] = rtrim($data[$key]['Categories'], ', ');

                    if ($categoriesCount > 5) {
                        $data[$key]['Categories'] = $data[$key]['Categories'] . '...';
                    }
                }
            } else {
                $data[$key]['Categories'] = $lang['not_available'];
            }
        } elseif ($value['Mode'] == 'type' || $value['Mode'] == 'search_results') {
            $data[$key]['Categories'] = $rlListingTypes->types[$value['Type']]['name'];
        } elseif ($value['Mode'] == 'field_bound_boxes') {
            $data[$key]['Categories'] = $lang['blocks+name+' . $value['Type']];
        }
    }

    $output['total'] = $count;
    $output['data']  = $data;

    echo json_encode($output);
} else {
    $reefless->loadClass('CategoryFilter', null, 'categoryFilter');

    // Additional bread crumb step
    if ($_GET['action']) {
        switch ($_GET['action']) {
            case 'add':
                $bcAStep = $lang['category_filter_add_filter_box'];
                break;

            case 'edit':
                $bcAStep = $lang['category_filter_edit_filter_box'];
                break;

            case 'build':
                $bcAStep = $lang['category_filter_build_filter_box'];
                break;

            case 'config':
                $bcAStep[] = [
                    'name'       => $lang['category_filter_build_filter_box'],
                    'Controller' => 'categoryFilter&action=build&item=' . $_GET['item'] . '&form',
                    'Plugin'     => 'categoryFilter',
                ];
                $bcAStep[] = ['name' => $lang['category_filter_configure_filter']];
                break;
        }
    }

    if ($_GET['action'] == 'add' || $_GET['action'] == 'edit') {
        $sections = $GLOBALS['rlCategories']->getCatTree(0, false, true);
        $rlSmarty->assign_by_ref('sections', $sections);

        $allLangs = $GLOBALS['languages'];
        $rlSmarty->assign_by_ref('allLangs', $allLangs);

        // Check existing of the Field Bound Box plugin
        if ($rlDb->getOne('ID', "`Key` = 'fieldBoundBoxes' AND `Status` = 'active'", 'plugins')) {
            if ($fieldBoundBoxes = $rlDb->fetch(['Key'], null, null, null, 'field_bound_boxes')) {
                foreach ($fieldBoundBoxes as $fb_key => $fb_box) {
                    $fieldBoundBoxes[$fb_key]['Box_name'] = $lang['blocks+name+' . $fb_box['Key']];
                }

                $rlSmarty->assign_by_ref('fieldBoundBoxes', $fieldBoundBoxes);
            }
        }

        $itemID = (int) $_GET['item'];

        if ($_GET['action'] == 'edit' && !$_POST['fromPost']) {
            $sql = "SELECT `T1`.`ID`, `T1`.`Category_IDs`, `T1`.`Mode`, `T1`.`Type`, `T2`.`Status`, ";
            $sql .= "`T2`.`Tpl`, `T2`.`Side` FROM `{db_prefix}category_filter` AS `T1` ";
            $sql .= "LEFT JOIN `{db_prefix}blocks` AS `T2` ON CONCAT('categoryFilter_', `T1`.`ID`) = `T2`.`Key` ";
            $sql .= "WHERE `T1`.`ID` = {$itemID}";
            $itemInfo = $rlDb->getRow($sql);

            $_POST['status']     = $itemInfo['Status'];
            $_POST['mode']       = $itemInfo['Mode'];
            $_POST['type']       = $itemInfo['Type'];
            $_POST['categories'] = explode(',', $itemInfo['Category_IDs']);
            $_POST['side']       = $itemInfo['Side'];
            $_POST['tpl']        = $itemInfo['Tpl'];

            $names = $rlDb->fetch(
                ['Code', 'Value'],
                ['Key' => "blocks+name+categoryFilter_{$itemInfo['ID']}"],
                "AND `Status` <> 'trash'",
                null,
                'lang_keys'
            );

            foreach ($names as $phraseName) {
                $_POST['name'][$phraseName['Code']] = $phraseName['Value'];
            }
        }

        // Get parent points
        if ($_POST['categories']) {
            $GLOBALS['rlCategories']->parentPoints($_POST['categories']);
        }

        // Creating new filter box
        if (isset($_POST['submit'])) {
            $boxMode = $_POST['mode'];

            if (!$boxMode) {
                $errors[] = str_replace(
                    '{field}',
                    "<b>{$lang['category_filter_filter_for']}</b>",
                    $lang['notice_select_empty']
                );
                $error_fields[] = 'mode';
            }

            // Check category filters exist (in add mode only)
            if ($boxMode == 'category') {
                $categories = $_POST['categories'];

                if (!$categories) {
                    $errors[] = $lang['category_filter_no_category_selected'];
                } else {
                    $additionalWhere = $itemID ? "AND `T1`.`ID` <> {$itemID}" : '';

                    foreach ($categories as $category) {
                        $sql = "SELECT `T2`.`Key` FROM `{db_prefix}category_filter` AS `T1` ";
                        $sql .= "LEFT JOIN `{db_prefix}categories` AS `T2` ON `T2`.`ID` = {$category} ";
                        $sql .= "WHERE FIND_IN_SET('{$category}', `Category_IDs`) > 0 {$additionalWhere}";
                        $exists = $rlDb->getRow($sql);

                        if ($exists) {
                            $categoriesExistsNames[] = $lang['categories+name+' . $exists['Key']];
                        }
                    }

                    if ($categoriesExistsNames) {
                        $errors[] = str_replace(
                            '{categories}',
                            '<b>' . implode(', ', $categoriesExistsNames) . '</b>',
                            $lang['category_filter_filter_exists_for']
                        );
                    }
                }
            }
            // Checking listing type exist  (in add mode only)
            elseif ($boxMode == 'type' || $boxMode == 'search_results') {
                $boxListingTypeKey = $boxMode == 'type' ? $_POST['type'] : $_POST['type_for_search'];

                if (!$boxListingTypeKey) {
                    $errors[] = str_replace(
                        '{field}',
                        "<b>{$lang['listing_type']}</b>",
                        $lang['notice_select_empty']
                    );
                    $error_fields[] = $boxMode == 'type' ? 'type' : 'type_for_search';
                } else {
                    $sql = "SELECT `ID` FROM `{db_prefix}category_filter` ";
                    $sql .= "WHERE `Mode` = " . ($boxMode == 'type' ? "'type'" : "'search_results'");
                    $sql .= " AND `Type` = '{$boxListingTypeKey}'";

                    if ($itemID) {
                        $sql .= "AND `ID` <> {$itemID}";
                    }

                    $exists = $rlDb->getRow($sql);

                    if ($exists) {
                        $errors[] = str_replace(
                            '{type}',
                            '<b>' . implode(', ', $rlListingTypes->types[$boxListingTypeKey]['name']) . '</b>',
                            $lang['category_filter_filter_exists_for_type']
                        );
                    }
                }
            }
            // Checking field bound selected box
            elseif ($boxMode == 'field_bound_boxes') {
                if (empty($_POST['field_bound_boxes'])) {
                    $errors[]       = str_replace(
                        '{field}',
                        "<b>{$lang['fb_block_name']}</b>",
                        $lang['notice_select_empty']
                    );
                    $error_fields[] = 'field_bound_boxes';
                } else {
                    $boxListingTypeKey = $_POST['field_bound_boxes'];
                }
            }

            // Checking of filled name
            $namesInPost = $_POST['name'];
            $defaultName = $namesInPost[$config['lang']];

            foreach ($allLangs as $langItem) {
                if (empty($namesInPost[$langItem['Code']]) && !$defaultName) {
                    $errors[] = str_replace(
                        '{field}',
                        "<b>{$lang['name']} ({$langItem['name']})</b>",
                        $lang['notice_field_empty']
                    );
                    $error_fields[] = "name[{$langItem['Code']}]";
                }

                $names[$langItem['Code']] = $namesInPost[$langItem['Code']] ?: $defaultName;
            }

            // Checking of selected side
            $boxSide = $_POST['side'];

            if (empty($boxSide)) {
                $errors[]       = str_replace(
                    '{field}',
                    "<b>{$lang['block_side']}</b>",
                    $lang['notice_select_empty']
                );
                $error_fields[] = 'side';
            }

            if (!empty($errors)) {
                $rlSmarty->assign_by_ref('errors', $errors);
            } else {
                if ($boxMode == 'category') {
                    // Get category type keys by selected categories
                    $sql = "SELECT `T3`.`ID` FROM `{db_prefix}categories` AS `T1` ";
                    $sql .= "LEFT JOIN `{db_prefix}listing_types` AS `T2` ON `T1`.`Type` = `T2`.`Key` ";
                    $sql .= "LEFT JOIN `{db_prefix}pages` AS `T3` ON CONCAT('lt_', `T2`.`Key`) = `T3`.`Key` ";
                    $sql .= "WHERE FIND_IN_SET(`T1`.`ID`, '" . implode(',', $categories) . "') > 0 ";
                    $sql .= "GROUP BY `T1`.`Type` ";
                    $typeKeys = $rlDb->getAll($sql);

                    foreach ($typeKeys as $lt_key) {
                        $pageIDs[] = $lt_key['ID'];
                    }
                } else {
                    $pageIDs[] = $boxMode == 'field_bound_boxes'
                    ? $rlDb->getOne('ID', "`Key` = 'listings_by_field' AND `Plugin` = 'fieldBoundBoxes'", 'pages')
                    : $rlDb->getOne('ID', "`Key` = 'lt_{$boxListingTypeKey}'", 'pages');
                }

                if ($_GET['action'] == 'add') {
                    if ($action = $GLOBALS['rlCategoryFilter']->createFilter([
                        'Mode'         => $boxMode,
                        'Side'         => $boxSide,
                        'Listing_type' => $boxListingTypeKey,
                        'Categories'   => $categories,
                        'Page_ids'     => $pageIDs,
                    ])) {
                        $message = $lang['category_filter_filter_box_added'];
                        $aUrl    = ['controller' => "{$controller}&action=build&item={$action['filterID']}&form"];
                    }
                } elseif ($_GET['action'] == 'edit') {
                    if ($action = $GLOBALS['rlCategoryFilter']->editFilter([
                        'ID'           => $itemID,
                        'Mode'         => $boxMode,
                        'Side'         => $boxSide,
                        'Listing_type' => $boxListingTypeKey,
                        'Categories'   => $categories,
                        'Page_ids'     => $pageIDs,
                    ])) {
                        $message = $lang['category_filter_filter_box_edited'];
                        $aUrl    = ['controller' => $controller];
                    }
                }

                if ($action['action']) {
                    $reefless->loadClass('Notice');
                    $GLOBALS['rlNotice']->saveNotice($message);
                    $reefless->redirect($aUrl);
                }
            }
        }
    } elseif ($_GET['action'] == 'build') {
        $itemID = (int) $_GET['item'];

        $sql = "SELECT `T1`.`ID`, CONCAT('categoryFilter_', `T1`.`ID`) AS `Key`, `T1`.`Category_IDs`, ";
        $sql .= "`T1`.`Mode`, `T1`.`Type`, `T2`.`Status`, `T2`.`Tpl`, `T2`.`Side` ";
        $sql .= "FROM `{db_prefix}category_filter` AS `T1` ";
        $sql .= "LEFT JOIN `{db_prefix}blocks` AS `T2` ON CONCAT('categoryFilter_', `T1`.`ID`) = `T2`.`Key` ";
        $sql .= "WHERE `T1`.`ID` = {$itemID} ";
        $boxInfo = $rlDb->getRow($sql);

        // Emulate data for Builder
        $rlSmarty->assign_by_ref('category_info', $boxInfo);

        $filterName = $rlLang->getPhrase('blocks+name+categoryFilter_' . $itemID, RL_LANG_CODE, false, true);

        // Get related filter categories
        if ($boxInfo['Mode'] == 'category') {
            $sql = "SELECT SQL_CALC_FOUND_ROWS `Key` FROM `{db_prefix}categories` ";
            $sql .= "WHERE FIND_IN_SET(`ID`, '{$boxInfo['Category_IDs']}') > 0 LIMIT 5";
            $categoriesKeys    = $rlDb->getAll($sql);
            $categoriesCount   = $rlDb->getRow("SELECT FOUND_ROWS() AS `count`", 'count');
            $captionCategories = '';

            if ($categoriesKeys) {
                foreach ($categoriesKeys as $categoryKey) {
                    $name = $rlLang->getPhrase("categories+name+{$categoryKey['Key']}", RL_LANG_CODE, false, true);
                    $captionCategories .= $name . ', ';
                }

                $captionCategories = rtrim($captionCategories, ', ');

                if ($categoriesCount > 5) {
                    $captionCategories .= ', ...';
                }
            }

            $rlSmarty->assign('cpTitle', $filterName . ' (' . $captionCategories . ')');
        }
        // Get related filter type
        else {
            $rlSmarty->assign(
                'cpTitle',
                $filterName
                    . ($boxInfo['Mode'] != 'field_bound_boxes'
                        ? ' (' . $rlListingTypes->types[$boxInfo['Type']]['name'] . ')'
                        : ''
                    )
            );
        }

        // Get listing fields
        $denyFieldsKeys  = ['keyword_search', 'text_search'];
        $denyFieldsTypes = ['textarea', 'phone', 'date', 'image', 'file', 'accept'];

        // Remove denied field types
        foreach ($l_types as $listingTypeKey => $listingTypeName) {
            if (in_array($listingTypeKey, $denyFieldsTypes)) {
                unset($l_types[$listingTypeKey]);
            }
        }

        // Deny Category_ID field if Field-Bound Box doesn't have listing type
        if ($boxInfo['Mode'] == 'field_bound_boxes') {
            $fbbBoxInfo = $rlDb->fetch(
                '*',
                null,
                "WHERE `Key` = '{$boxInfo['Type']}' AND `Status` = 'active'",
                null,
                'field_bound_boxes',
                'row'
            );

            $denyFieldsKeys[] = $fbbBoxInfo['Field_key'];

            if (empty($fbbBoxInfo['Listing_type'])) {
                $denyFieldsKeys[] = 'Category_ID';
            }
        }

        $select = ['ID', 'Key', 'Type', 'Status'];
        $where  = "WHERE `Status` <> 'trash' AND `Details_page` = '1' AND `Multilingual` = '0' AND `Key` <> '";
        $where .= implode("' AND `Key` <> '", $denyFieldsKeys);
        $where .= "' AND `Type` <> '" . implode("' AND `Type` <> '", $denyFieldsTypes) . "'";

        // Get available fields
        $fields = $rlDb->fetch($select, null, $where, null, 'listing_fields');
        $fields = $rlLang->replaceLangKeys($fields, 'listing_fields', ['name'], RL_LANG_CODE, 'admin');
        $rlSmarty->assign_by_ref('fields', $fields);

        $reefless->loadClass('Builder', 'admin');
        $rlBuilder->rlBuildTable = 'category_filter_relation';
        $rlBuilder->rlBuildField = 'Fields';

        $relations = $rlBuilder->getRelations($boxInfo['ID']);
        $rlSmarty->assign_by_ref('relations', $relations);

        foreach ($relations as $rKey => $rValue) {
            $filterFields = $relations[$rKey]['Fields'];

            if ($relations[$rKey]['Group_ID']) {
                foreach ($filterFields as $fKey => $fValue) {
                    $noFields[] = $filterFields[$fKey]['Key'];
                }
            } else {
                $noFields[] = $relations[$rKey]['Fields']['Key'];
            }
        }

        // Hide already using fields
        if (!empty($noFields)) {
            foreach ($fields as $fKey => $fVal) {
                if (false !== array_search($fields[$fKey]['Key'], $noFields)) {
                    $fields[$fKey]['hidden'] = true;
                }
            }
        }

        $rlXajax->registerFunction(['buildForm', $rlBuilder, 'ajaxBuildForm']);
    } elseif ($_GET['action'] == 'config') {
        $reefless->loadClass('Categories');

        $allLangs = $GLOBALS['languages'];
        $rlSmarty->assign_by_ref('allLangs', $allLangs);

        $boxID    = (int) $_GET['item'];
        $field_id = (int) $_GET['field'];

        $sql = "SELECT `T1`.`Items`, `T1`.`Item_names`, `T1`.`Items_display_limit`, `T1`.`Mode`, `T1`.`Status`, ";
        $sql .= "`T2`.`Key`, `T2`.`Type`, `T2`.`Values`, `T2`.`Condition`, `T1`.`No_index`, ";
        $sql .= "`T1`.`Data_in_title`, `T1`.`Data_in_description`, `T1`.`Data_in_H1` ";
        $sql .= "FROM `{db_prefix}category_filter_field` AS `T1` ";
        $sql .= "LEFT JOIN `{db_prefix}listing_fields` AS `T2` ON `T1`.`Field_ID` = `T2`.`ID` ";
        $sql .= "WHERE `T1`.`Box_ID` = {$boxID} AND `T1`.`Field_ID` = '{$field_id}' ";
        $fieldInfo = $rlDb->getRow($sql);

        $fieldInfo['pName']      = 'listing_fields+name+' . $fieldInfo['Key'];
        $fieldInfo['Type_pName'] = 'type_' . $fieldInfo['Type'];

        if (!$fieldInfo) {
            $sError = true;
        }

        // Detect fields created via Multifield/Location Filter plugin
        if ($fieldInfo['Condition'] && $GLOBALS['plugins']['multiField']) {
            $formatKeys = $config['mf_format_keys'];

            if ($formatKeys) {
                if (in_array($fieldInfo['Condition'], explode('|', $formatKeys))) {
                    $fieldInfo['multiField'] = true;
                }
            } else {
                if ($rlDb->getOne(
                    'Levels',
                    "`Key` = '{$fieldInfo['Condition']}' AND `Status` = 'active'",
                    'multi_formats') > 1
                ) {
                    $fieldInfo['multiField'] = true;
                }
            }
        }

        $rlSmarty->assign_by_ref('fieldInfo', $fieldInfo);
        $rlSmarty->assign(
            'cpTitle',
            str_replace('{field}', $lang[$fieldInfo['pName']], $lang['category_filter_title_field'])
        );

        // Get MIN/MAX values by field
        if (in_array($fieldInfo['Type'], ['number', 'price', 'mixed']) || $fieldInfo['Condition'] == 'years') {
            $sql = "SELECT MIN(ROUND(`T1`.`{$fieldInfo['Key']}`)) AS `min`, ";
            $sql .= "MAX(ROUND(`T1`.`{$fieldInfo['Key']}`)) AS `max` ";
            $sql .= "FROM `{db_prefix}listings` AS `T1` ";
            $sql .= "LEFT JOIN `{db_prefix}listing_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
            $sql .= "LEFT JOIN `{db_prefix}categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";
            $sql .= "LEFT JOIN `{db_prefix}accounts` AS `T7` ON `T1`.`Account_ID` = `T7`.`ID` ";
            $sql .= "WHERE (UNIX_TIMESTAMP(DATE_ADD(`T1`.`Pay_date`, INTERVAL `T2`.`Listing_period` DAY)) ";
            $sql .= "> UNIX_TIMESTAMP(NOW()) OR `T2`.`Listing_period` = 0) ";
            $sql .= "AND `T1`.`{$fieldInfo['Key']}` <> '' AND `T1`.`{$fieldInfo['Key']}` NOT LIKE '%+%' ";
            $sql .= "AND `T1`.`Status` = 'active' AND `T3`.`Status` = 'active' AND `T7`.`Status` = 'active' ";
            $fieldStatistics = $rlDb->getRow($sql);

            $fieldInfo['min'] = $fieldStatistics['min'];
            $fieldInfo['max'] = $fieldStatistics['max'];

            $rlSmarty->assign(
                'min_max_stat',
                str_replace(
                    ['{min}', '{max}'],
                    [$fieldInfo['min'], $fieldInfo['max']],
                    $lang['category_filter_min_max_stat']
                )
            );
        }

        // Collect items
        if (($fieldInfo['Items']
                || $fieldInfo['Values']
                || $fieldInfo['Condition']
                || in_array($fieldInfo['Type'], ['bool', 'price'])
            ) && !$_POST['action']
        ) {
            $pattern = '/category\_filter\+name\+[0-9]+\_[0-9]+\_([0-9|min]+)?([\<\-\>]+)?([0-9|max]+)?/';

            switch ($fieldInfo['Type']) {
                case 'number':
                case 'mixed':
                case 'price':
                    if ($fieldInfo['Item_names']) {
                        $items = unserialize(base64_decode($fieldInfo['Item_names']));

                        foreach ($items as $item) {
                            preg_match($pattern, $item, $matches);
                            $itemKey = $matches[1] . $matches[2] . $matches[3];

                            if ($matches[1] == 'min') {
                                $matches[2] = '<';
                            } elseif ($matches[3] == 'max') {
                                $matches[2] = '>';
                            }

                            switch ($matches[2]) {
                                case '-':
                                    $_POST['sign'][$itemKey] = 'between';
                                    $_POST['from'][$itemKey] = $matches[1];
                                    $_POST['to'][$itemKey]   = $matches[3];
                                    break;

                                case '<':
                                    $_POST['sign'][$itemKey] = 'less';
                                    $_POST['to'][$itemKey]   = $matches[3];
                                    break;

                                case '>':
                                    $_POST['sign'][$itemKey] = 'greater';
                                    $_POST['from'][$itemKey] = $matches[1];
                                    break;
                            }

                            foreach ($allLangs as $langItem) {
                                if ($value = $rlDb->getOne(
                                        'Value',
                                        "`Key` = '{$item}' AND `Code` = '{$langItem['Code']}'",
                                        'lang_keys')
                                ) {
                                    $_POST['items'][$itemKey][$langItem['Code']] = $value;
                                }
                            }
                        }
                    }
                    break;

                case 'bool':
                    foreach ([1, 0] as $item) {
                        $custom_key = 'category_filter+name+' . $boxID . '_' . $field_id . '_' . $item;
                        $real_key   = $item ? 'yes' : 'no';

                        foreach ($allLangs as $langItem) {
                            if ($value = $rlDb->getOne(
                                    'Value',
                                    "`Key` = '{$custom_key}' AND `Code` = '{$langItem['Code']}'",
                                    'lang_keys')
                            ) {
                                $_POST['items'][$item][$langItem['Code']] = $value;
                            } else {
                                $_POST['items'][$item][$langItem['Code']] = $rlDb->getOne(
                                    'Value',
                                    "`Key` = '{$real_key}' AND `Code` = '{$langItem['Code']}'",
                                    'lang_keys'
                                );
                            }
                        }
                    }
                    break;

                case 'radio':
                case 'select':
                case 'checkbox':
                    if ($fieldInfo['Values'] && !$fieldInfo['Condition']) {
                        foreach (explode(',', $fieldInfo['Values']) as $item) {
                            $custom_key = 'category_filter+name+' . $boxID . '_' . $field_id . '_' . $item;
                            $real_key   = 'listing_fields+name+' . $fieldInfo['Key'] . '_' . $item;

                            foreach ($allLangs as $langItem) {
                                if ($value = $rlDb->getOne(
                                        'Value',
                                        "`Key` = '{$custom_key}' AND `Code` = '{$langItem['Code']}'",
                                        'lang_keys')
                                ) {
                                    $_POST['items'][$item][$langItem['Code']] = $value;
                                } else {
                                    $_POST['items'][$item][$langItem['Code']] = $rlDb->getOne(
                                        'Value',
                                        "`Key` = '{$real_key}' AND `Code` = '{$langItem['Code']}'",
                                        'lang_keys'
                                    );
                                }
                            }
                        }
                    } elseif ($fieldInfo['Condition']) {
                        foreach ($rlCategories->getDF($fieldInfo['Condition']) as $item) {
                            $custom_key   = 'category_filter+name+' . $boxID . '_' . $field_id . '_' . $item['Key'];
                            $real_key     = 'data_formats+name+' . $fieldInfo['Condition'] . '_' . $item['Key'];
                            $real_alt_key = 'data_formats+name+' . $item['Key'];

                            foreach ($allLangs as $langItem) {
                                if ($value = $rlDb->getOne(
                                    'Value',
                                    "`Key` = '{$custom_key}' AND `Code` = '{$langItem['Code']}'",
                                    'lang_keys')
                                ) {
                                    $_POST['items'][$item['Key']][$langItem['Code']] = $value;
                                } else {
                                    $where = "(`Key` = '{$real_key}' OR `Key` = '{$real_alt_key}') ";
                                    $where .= "AND `Code` = '{$langItem['Code']}'";

                                    $_POST['items'][$item['Key']][$langItem['Code']] = $rlDb->getOne(
                                        'Value',
                                        $where,
                                        'lang_keys'
                                    );
                                }
                            }
                        }
                    }
                    break;
            }
        }

        // View mode list
        $modes = [
            'auto'       => $lang['category_filter_mode_auto'],
            'group'      => $lang['category_filter_mode_group'],
            'slider'     => $lang['category_filter_mode_slider'],
            'checkboxes' => $lang['category_filter_mode_checkboxes'],
            'text'       => $lang['cf_mode_text_fields'],
        ];
        $rlSmarty->assign_by_ref('modes', $modes);

        if (!$_POST['action']) {
            $_POST['mode']                = $fieldInfo['Mode'];
            $_POST['items_display_limit'] = $fieldInfo['Items_display_limit'];
            $_POST['no_index']            = $fieldInfo['No_index'];
            $_POST['data_in_title']       = $fieldInfo['Data_in_title'];
            $_POST['data_in_description'] = $fieldInfo['Data_in_description'];
            $_POST['data_in_H1']          = $fieldInfo['Data_in_H1'];
            $_POST['status']              = $fieldInfo['Status'];

            if ($fieldInfo['Key'] == 'Category_ID') {
                $_POST['hide_empty'] = $fieldInfo['Items'] ? 1 : 0;
            }
        }

        if ($_POST['action'] == 'config') {
            if (!$_POST['items_display_limit'] && $fieldInfo['Type'] != 'checkbox') {
                $errors[] = str_replace(
                    '{field}',
                    "<b>{$lang['category_filter_visible_items_limit']}</b>",
                    $lang['notice_select_empty']
                );
                $error_fields[] = 'items_display_limit';
            }

            // Validation of data
            if ($_POST['mode'] == 'group' && $_POST['items']) {
                $existErrors = false;

                foreach ($_POST['items'] as $itemKey => $itemName) {
                    if ($_POST['sign'][$itemKey]) {
                        // Checking of exist phrases
                        $emptyPhrases = false;
                        $countPhrases = 1;
                        $phraseCode   = '';

                        foreach ($allLangs as $langItem) {
                            if ($countPhrases == 1) {
                                $phraseCode = $langItem['Code'];
                            }

                            $countPhrases++;

                            if ($_POST['items'][$itemKey][$langItem['Code']]) {
                                $emptyPhrases = true;
                                break;
                            }
                        }

                        if (!$emptyPhrases && $phraseCode) {
                            $existErrors    = true;
                            $error_fields[] = "items[{$itemKey}][{$phraseCode}]";
                        }

                        switch ($_POST['sign'][$itemKey]) {
                            case 'less':
                                if (!is_numeric($_POST['to'][$itemKey])) {
                                    $existErrors    = true;
                                    $error_fields[] = "to[{$itemKey}]";
                                }
                                break;

                            case 'greater':
                                if (!is_numeric($_POST['from'][$itemKey])) {
                                    $existErrors    = true;
                                    $error_fields[] = "from[{$itemKey}]";
                                }
                                break;

                            case 'between':
                                if (!is_numeric($_POST['from'][$itemKey])
                                    || !is_numeric($_POST['to'][$itemKey])
                                ) {
                                    $existErrors = true;

                                    if (!is_numeric($_POST['from'][$itemKey])) {
                                        $error_fields[] = "from[{$itemKey}]";
                                    }

                                    if (!is_numeric($_POST['to'][$itemKey])) {
                                        $error_fields[] = "to[{$itemKey}]";
                                    }
                                }
                                break;
                        }
                    }
                }

                if ($existErrors) {
                    $errors[] = str_replace(
                        '{field}',
                        "<b>{$lang['category_filter_filter_items']}</b>",
                        $lang['notice_field_empty']
                    );
                }
            }

            if (!$errors) {
                $update = [
                    'fields' => [
                        'Items_display_limit' => (int) $_POST['items_display_limit'],
                        'No_index'            => (int) $_POST['no_index'],
                        'Data_in_title'       => (int) $_POST['data_in_title'],
                        'Data_in_description' => (int) $_POST['data_in_description'],
                        'Data_in_H1'          => (int) $_POST['data_in_H1'],
                        'Status'              => $_POST['status'],
                    ],
                    'where'  => [
                        'Box_ID'   => $boxID,
                        'Field_ID' => $field_id,
                    ],
                ];

                if (in_array($fieldInfo['Type'], ['number', 'price', 'mixed'])
                    || $fieldInfo['Condition'] == 'years'
                ) {
                    $update['fields']['Mode'] = $_POST['mode'];
                } else {
                    $update['fields']['Mode'] = $_POST['mode'] == 'checkboxes' ? $_POST['mode'] : 'auto';
                }

                if ($fieldInfo['Key'] == 'Category_ID') {
                    $update['fields']['Items'] = $_POST['hide_empty'] ? 1 : 0;
                }

                $itemNames = [];

                foreach ($_POST['items'] as $itemKey => $itemName) {
                    if ($_POST['sign']) {
                        switch ($_POST['sign'][$itemKey]) {
                            case 'less':
                                $newItemKey = 'min' . '-' . (int) $_POST['to'][$itemKey];
                                break;

                            case 'greater':
                                $newItemKey = (int) $_POST['from'][$itemKey] . '-' . 'max';
                                break;

                            case 'between':
                                $newItemKey = (int) $_POST['from'][$itemKey] . '-' . (int) $_POST['to'][$itemKey];
                                break;
                        }
                    }

                    if ($newItemKey && !$_POST['exist'][$itemKey]) {
                        $itemKey = $newItemKey;
                    }

                    $phraseKey = $orig_key = 'category_filter+name+' . $boxID . '_' . $field_id . '_' . $itemKey;

                    if ($newItemKey != $itemKey && $_POST['exist'][$itemKey]) {
                        $phraseKey = 'category_filter+name+' . $boxID . '_' . $field_id . '_' . $newItemKey;
                        $itemKey   = $newItemKey;
                    }

                    foreach ($allLangs as $langItem) {
                        if (!$itemName[$config['lang']]) {
                            continue;
                        }

                        if ($rlDb->getOne(
                            'ID',
                            "`Key` = '{$orig_key}' AND `Code` = '{$langItem['Code']}'",
                            'lang_keys')
                        ) {
                            $rlDb->updateOne(
                                [
                                    'fields' => [
                                        'Value' => $itemName[$langItem['Code']] ?: $itemName[$config['lang']],
                                        'Key'   => $phraseKey,
                                    ],
                                    'where'  => [
                                        'Code' => $langItem['Code'],
                                        'Key'  => $orig_key,
                                    ],
                                ],
                                'lang_keys'
                            );
                        } else {
                            $rlDb->insertOne(
                                [
                                    'Code'   => $langItem['Code'],
                                    'Module' => 'common',
                                    'Key'    => $phraseKey,
                                    'Plugin' => 'categoryFilter',
                                    'Value'  => $itemName[$langItem['Code']] ?: $itemName[$config['lang']],
                                ],
                                'lang_keys'
                            );
                        }
                    }

                    $itemNames[$itemKey] = $phraseKey;
                }

                if ($_POST['mode'] != 'group'
                    && (in_array($fieldInfo['Type'], ['number', 'mixed', 'price'])
                        || $fieldInfo['Condition'] == 'years'
                    )
                ) {
                    $update['fields']['Item_names'] = '';
                } else {
                    $update['fields']['Item_names'] = $itemNames ? base64_encode(serialize($itemNames)) : '';
                }

                $rlDb->updateOne($update, 'category_filter_field');

                $rlCategoryFilter->recountFilters($boxID);

                $reefless->loadClass('Notice');
                $rlNotice->saveNotice($lang['category_filter_changes_saved']);

                $reefless->redirect(
                    false,
                    RL_URL_HOME . ADMIN . "/index.php?controller=categoryFilter&action=build&item={$boxID}&form"
                );
            }
        }
    }

    $rlXajax->registerFunction(['getCatLevel', $rlCategories, 'ajaxGetCatLevel']);
    $rlXajax->registerFunction(['openTree', $rlCategories, 'ajaxOpenTree']);
}
