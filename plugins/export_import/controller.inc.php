<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : CONTROLLER.INC.PHP
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

use Flynax\Plugins\ExportImport\Handlers\ListingPackages;

$max_file_size = str_replace('M', '', ini_get('upload_max_filesize'));
$rlSmarty -> assign_by_ref( 'max_file_size', $max_file_size );

$action = $config['mod_rewrite'] ? $_GET['nvar_1'] : $_GET['action'];

$rlSmarty -> assign_by_ref('iel_action', $action);
$rlSmarty->assign('allow_import', true);

/* re-assign config */
$config['search_fields_position'] = 2;

if ( $action )
{
    $bread_crumbs[count($bread_crumbs)-1]['vars'] = 'reset=true';

    switch ( $action ) {
        case 'import-table':
            /* add bread crumbs step */
            $bread_crumbs[] = array(
                'name' => $lang['eil_import_table']
            );
            $page_info['name'] = $lang['eil_import_table'];

            /* add "view mode" icon */
            $navIcons[] = '<a class="button eil_fullscreen" title="'. $lang['eil_fullscreen'] .'" href="javascript:void(0)"><span>'.$lang['eil_fullscreen'].'</span></a>';
            $rlSmarty -> assign_by_ref('navIcons', $navIcons);

            break;

        case 'import-preview':
            $bread_crumbs[] = array(
                'name' => $lang['eil_import_table'],
                'path' => $page_info['Path'] .'/import-table'
            );
            $bread_crumbs[] = array(
                'name' => $lang['eil_preview']
            );
            $page_info['name'] = $lang['eil_preview'];

            break;

        case 'export-table':
            /* add "view mode" icon */
            $navIcons[] = '<a class="button eil_fullscreen" title="'. $lang['eil_fullscreen'] .'" href="javascript:void(0)"><span>'.$lang['eil_fullscreen'].'</span></a>';
            $rlSmarty -> assign_by_ref('navIcons', $navIcons);

            break;
    }
}

if (!$action) {
    unset($_SESSION['will_be_imported']);
    unset($_SESSION['eil_import_grid_message']);
}

/* populate tabs */
$tabs = array(
    'import' => array(
        'key' => 'import',
        'name' => $lang['eil_import']
    ),
    'export' => array(
        'key' => 'export',
        'name' => $lang['eil_export']
    )
);

// prevent using plugin for people who can't add listings
$abilities = array_filter($account_info['Abilities'], function ($item) {
    return $item == 'export_import' ? false : true;
});
if (empty($abilities)) {
    $rlSmarty->assign('prevent_import', 'true');
    $rlSmarty->assign('prevent_export', 'true');
}

$rlSmarty -> assign_by_ref('tabs', $tabs);

$reefless -> loadClass('ExportImport', null, 'export_import');

// todo: should I remove?
$rlXajax -> registerFunction( array( 'mfBuild', $GLOBALS['rlMultiField'], 'ajaxBuild' ) );

if (\Flynax\Plugins\ExportImport\Helpers::isMultiFieldInstalled()) {
    $rlSmarty->assign('multiFieldExist', true);
}


if ( !$_REQUEST['xjxfun'] )
{
    // Membership module  
    $restricted_account = false; 
    if(file_exists(RL_CLASSES . "rlMembershipPlan.class.php") && $config['membership_module']) {
        $reefless->loadClass('MembershipPlan');
        $restricted_account = true;
        if($account_info['Plan_ID']) {
            $mp_plan_info = $rlMembershipPlan->getPlan($account_info['Plan_ID']);
            $is_exceeded = $rlExportImport->isLimitExceeded($account_info['ID'], $mp_plan_info);
            $mp_plan_using = $rlMembershipPlan->getUsingPlan($account_info['Plan_ID'], $account_info['ID']);
            if(!$mp_plan_using) {
                $rlExportImport->addMembershipPlanUsing($mp_plan_info, $account_info['ID']);
                $mp_plan_using = $rlMembershipPlan->getUsingPlan($account_info['Plan_ID'], $account_info['ID']);
                
            }
            if($is_exceeded && !$config['allow_listing_plans']) {
                $prevent_import_export = true;
            }
        } else {
            $prevent_import_export = !$config['allow_listing_plans'] ? true : false ;
        }
        
        $block_plan_import = $config['allow_listing_plans'] ? false : true ;
        $rlSmarty->assign('block_plan_import', $block_plan_import);
        $rlSmarty->assign('prevent_import_export', $prevent_import_export);
    }
    $rlSmarty->assign('restricted_account', $restricted_account);

    $allowed_types = array('csv', 'xls');

    /* reset import file request */
    if ( isset($_GET['reset']) )
    {
        // update membeship plan using
        if ($restricted_account) {
            $rlExportImport->updateMembershipUsing($account_info);
        }
        
        unlink($_SESSION['iel_data']['file']);

        $reefless -> deleteDirectory($_SESSION['iel_data']['archive_dir']);
        unset($_SESSION['iel_data'], $_SESSION['eil_data'], $_SESSION['import_data']);
        unset($_SESSION['will_be_imported']);
        unset($_SESSION['eil_import_grid_message']);
    }

    /* post actions */
    if ( $_POST['action'] == 'import_file' && $_FILES['file']['name'] )
    {
        if ( !in_array(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION), $allowed_types) )
        {
            $errors[] = $lang['eil_import_wrong_file_format'];
        }

        if ( $_FILES['archive']['tmp_name'] && !(bool)preg_match('/\.zip$/', $_FILES['archive']['name']) )
        {
            $errors[] = $lang['eil_import_wrong_archive_format'];
        }

        if ( !$errors )
        {
            /* upload main listings file */
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $dynamic = time() . mt_rand();
            $file_name = $account_info['Username'] .'_import_'. $dynamic .'.'. $ext;
            $file = RL_UPLOAD . $file_name;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
                $_SESSION['iel_data'] = array(
                    'file' => $file,
                    'file_name' => $file_name
                );
                chmod($file, 0644);

                /* upload pictures archive */
                $ext = pathinfo($_FILES['archive']['name'], PATHINFO_EXTENSION);
                $archive_name = $account_info['Username'] . '_import_zip_' . $dynamic . '.' . $ext;
                $archive = RL_UPLOAD . $archive_name;
                $archive_dir = RL_UPLOAD . $account_info['Username'] . '_import_zip_' . $dynamic;

                if (move_uploaded_file($_FILES['archive']['tmp_name'], $archive)) {
                    $_SESSION['iel_data']['archive_dir'] = $archive_dir;
                    \Flynax\Plugins\ExportImport\Adapters\ZipArchiveAdapter::extract($archive, $archive_dir);
                }

                /* redirect */
                $stringPattern = $config['mod_rewrite'] ? '%s/import-table.html' : '?page=%s&action=import-table';

                $url = SEO_BASE;
                $url .= sprintf($stringPattern, $page_info['Path']);

                $reefless->redirect(null, $url);
            } else {
                $errors[] = $lang['eil_import_unable_to_upload'];
            }
        }
    }

    /* fetch necessary data for import/preview tables */
    if ( in_array($action, array('import-table', 'import-preview')) )
    {
        if ( !is_readable($_SESSION['iel_data']['file']) ) {
            /* redirect */
            $url = SEO_BASE;
            if ( $config['mod_rewrite'] ) {
                $url .= $page_info['Path'] .'.html?reset=true';
            }
            else {
                $url .= '?page='. $page_info['Path'] .'&reset=true';
            }
            $reefless -> redirect(null, $url);
            exit;
        }

        /* get requested listing type categoies */
        if ( $_POST['import_listing_type'] || $_SESSION['iel_data']['post']['import_listing_type'] )
        {
            $lt = $_POST['import_listing_type'] ? $_POST['import_listing_type'] : $_SESSION['iel_data']['post']['import_listing_type'];

            $sql ="SELECT *, CONCAT('categories+name+', `Key`) as `pName` FROM `".RL_DBPREFIX."categories` WHERE `Type` = '{$lt}' AND `Parent_ID` = 0";
            $categories_tmp = $rlDb->getAll($sql);
            
            foreach ($categories_tmp as $category)
            {
                $categories[$category['ID']] = $category;
            }
            unset($categories_tmp, $category);
            $rlSmarty -> assign_by_ref('categories', $categories);
        }

        /* get default plans */
        $reefless->loadClass('Plan');
        $plans_tmp = $rlPlan->getPlans(array('listing', 'package'), true);

        foreach ($plans_tmp as $key => $plan) {
            if ($plan['Price'] > 0 && (!$rlExportImport->isBoughtPlan($plan, $account_info['ID']) && $plan['Type'] == 'package')) {
                continue;
            }

            $plans[$plan['ID']] = $plan;
            $plans[$plan['ID']]['is_bought'] = $plan['Type'] == 'package'
                ? $rlExportImport->isBoughtPlan($plan, $account_info['ID'])
                : false;
        }
        unset($plans_tmp);
        $rlSmarty->assign_by_ref('plans', $plans);

        /* get available plans */
        $user_plans = $rlExportImport -> getUserPlans($account_info);
        $rlSmarty -> assign_by_ref('user_plans', $user_plans);

        $per_run = array(2, 5, 10, 20, 50);
        $rlSmarty -> assign_by_ref('per_run', $per_run);
        $rlSmarty -> assign('restricted_account', $restricted_account);
        
        if (!isset($_GET['edit'])) {
            unset($_SESSION['eil_data']);
        }
    }

    /* parse uploaded file */
    if ( $_SESSION['iel_data']['file'] && is_readable($_SESSION['iel_data']['file']) && $action == 'import-table' )
    {
        /* get listing fields */
        $rlDb -> setTable('listing_fields');
        $listing_fields_tmp = $rlDb -> fetch(array('Key', 'Type', 'Default'), null, "WHERE `Status` <> 'trash' AND (`Readonly` = '0' OR `Key` LIKE '%level%') AND `Type` <> 'image' AND `Type` <> 'accept' AND `Type` <> 'file' AND `Status` = 'active'");
        $listing_fields_tmp = $rlLang -> replaceLangKeys($listing_fields_tmp, 'listing_fields', array('name'), RL_LANG_CODE, 'admin');
        foreach ($listing_fields_tmp as $tmp_listign_field)
        {
            $listing_fields[$tmp_listign_field['Key']] = $tmp_listign_field;
        }
        unset($listing_fields_tmp);
        $rlSmarty -> assign_by_ref('listing_fields', $listing_fields);

        /* system fields */

        //Get maximum category child
        $levels = $rlExportImport->getMaxCategoryLevelArray(false);
        $system_fields = array(
            'Main_photo_url' => array(
                'Key' => 'Main_photo_url',
                'name' => $lang['eil_pictures_by_url'],
            ),
            'Main_photo_zip' => array(
                'Key' => 'Main_photo_zip',
                'name' => $lang['eil_pictures_from_zip'],
            ),
            'Youtube_video' => array(
                'Key' => 'Youtube_video',
                'name' => $lang['eil_youtube_video_field'],
            ),
            'Loc_latitude' => array(
                'Key' => 'Loc_latitude',
                'name' => $lang['eil_loc_latitude'],
            ),
            'Loc_longitude' => array(
                'Key' => 'Loc_longitude',
                'name' => $lang['eil_loc_longitude'],
            ),
        );
        $system_fields = array_merge($levels, $system_fields);
        $rlSmarty -> assign_by_ref('system_fields', $system_fields);

        if ( $_POST['from_post'] && $action == 'import-table' )
        {
            if ($_SESSION['import_data']) {
                $_POST['data'] = $_SESSION['import_data'];
                $rows_array = array();
                for ($i = 1; $i < count($_POST['data']); $i++) { 
                    $rows_array[$i] = 1;
                }
                $_POST['rows'] = $rows_array;
            }
            
            /* Pagination */
            $rlExportImport->prepareImportPreviewGrid($_POST['data'], true);
            if (isset($_POST['max-import'])
                && ($_POST['import_plan_id']
                    && !ListingPackages::isSingleListingPackage($_POST['import_plan_id'])
                )
            ) {
                $maxImport = isset($_POST['max-import']) ? (int) $_POST['max-import'] : null;
            }

            if (isset($maxImport) && !$maxImport) {
                $errors[] = $lang['eil_cant_import_more_listings_to_package'];
                $error_fields .= 'import_plan_id,';
            }

            if ( !$_POST['import_listing_type'] )
            {
                $errors[] = str_replace( '{field}', "<b>\"".$lang['listing_type']."\"</b>", $lang['notice_select_empty']);
                $error_fields .= 'import_listing_type,';
            }
            if ( !$_POST['import_category_id'] )
            {
                $errors[] = str_replace( '{field}', "<b>\"".$lang['eil_default_category']."\"</b>", $lang['notice_select_empty']);
                $error_fields .= 'import_category_id,';
            }
            if ( !$_POST['import_status'] )
            {
                $errors[] = str_replace( '{field}', "<b>\"".$lang['eil_default_status']."\"</b>", $lang['notice_select_empty']);
                $error_fields .= 'import_status,';
            }
            if ( !$_POST['import_plan_id'] && !$block_plan_import)
            {
                $errors[] = str_replace( '{field}', "<b>\"".$lang['eil_default_plan']."\"</b>", $lang['notice_select_empty']);
                $error_fields .= 'import_plan_id,';
            }
            if ( !$_POST['rows'] )
            {
                $errors[] = $lang['eil_no_listings'];
            }
            if ( !$_POST['cols'] )
            {
                $errors[] = $lang['eil_no_fields_checked'];
            }

            $selected_fields = 0;
            foreach ($_POST['field'] as $post_field)
            {
                if ( !empty($post_field) )
                {
                    $selected_fields++;
                }
            }
            if ( $selected_fields < 2 )
            {
                $errors[] = $lang['eil_no_fields_selected'];
            }

            $choosen_fields = array_filter($_POST['field']);
            $unique = array_unique($choosen_fields);
            if (count($unique) != count($choosen_fields)) {
                foreach (array_diff_assoc($choosen_fields, $unique) as $duplicate) {
                    $from_lang = $system_fields[$duplicate]['name'] ?: $lang['eil_' . strtolower($duplicate)];
                    $phrase =  $from_lang ?: $listing_fields[$duplicate]['name'];
                    $duplicate_fields .= $phrase . ', ';
                }
                $duplicate_fields = rtrim($duplicate_fields, ', ');
                
                $errors[] = $lang['eil_duplicate_fields_selected'] . ' (<b>' . $duplicate_fields . '</b>)';
            }

            if ( !$errors )
            {
                unset($_POST['action']);
                $_SESSION['iel_data']['post'] = $_POST;

                if (isset($_POST['max-import'])) {
                    $maxImport = isset($_POST['max-import']) ? (int) $_POST['max-import'] : 0;
                    if (count($_SESSION['import_data']) - 1 > $maxImport) {
                        $_SESSION['will_be_imported'] = $maxImport;
                        $_SESSION['import_data'] = array_slice($_SESSION['import_data'], 0, $maxImport + 1);
                    }
                }

                // membership module
                if ($restricted_account && !$config['allow_listing_plans']) {
                    $_POST['rows'] = array_slice($_POST['rows'], 0, $mp_plan_using['Listings_remains']);
                }

                $_SESSION['iel_data']['post']['rows_tmp'] = $_POST['rows'];
                $_SESSION['iel_data']['user_plans'] = $user_plans;

                /* redirect */
                $url = SEO_BASE;
                if ( $config['mod_rewrite'] ) {
                    $url .= $page_info['Path'] .'/import-preview.html';
                }
                else {
                    $url .= '?page='. $page_info['Path'] .'&action=import-preview';
                }
                $reefless -> redirect(null, $url);
            }
        }
        else
        {
            if ( $_SESSION['iel_data']['post'] )
            {
                $_POST = $_SESSION['iel_data']['post'];
                $_SESSION['import_data'] = $_POST['data'];
                $rlExportImport->prepareImportPreviewGrid($_POST['data']);
                unset($_SESSION['iel_data']['post']);
            }
            else
            {
                /* get from file */
                switch (pathinfo($_SESSION['iel_data']['file'], PATHINFO_EXTENSION)){
                    case 'csv':
                        $handle = fopen($_SESSION['iel_data']['file'], 'r');
                        while ( ($data = fgetcsv($handle)) !== false )
                        {
                            $source[] = $data;
                        }
                        break;

                    case 'xls':
                        try {
                            $objPHPExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load($_SESSION['iel_data']['file']);
                            $source = $objPHPExcel->getActiveSheet()->toArray();
                        } catch (\Exception $e) {
                            $GLOBALS['rlDebug']->logger("Export/Import reading xls(x) problem: {$e->getMessage()}");
                        }

                        foreach ($source as $rowIndex => $row) {
                            if (!array_filter($row)) {
                                unset($source[$rowIndex]);
                            }
                        }
                        break;
                }

                // membership module
                if ($restricted_account && !$config['allow_listing_plans'] ) {
                    $mb_error = str_replace('{listting_count}', $mp_plan_using['Listings_remains'], $lang['eil_mp_restriction']);
                    $reefless->loadClass('Notice');
                    $rlNotice -> saveNotice($mb_error, 'alert');
                }

                if ( $source)
                {
                    if(!$restricted_account || ($mp_plan_using['Listings_remains'] || $config['allow_listing_plans']) ) {
                        foreach ($source[0] as $key => $col)
                        {
                            $_POST['cols'][$key] = true;
                        }

                        foreach ($source as $key => $row)
                        {
                            foreach ($row as $index => $cel)
                            {
                                $_POST['data'][$key][$index] = $cel;
                            }
                        }

                        /* Pagination */
                        $rlExportImport->prepareImportPreviewGrid($_POST['data'], true);

                        $_SESSION['import_data'] = $_POST['data'];
                    } else {
                        $errors[] = $lang['eil_mp_cant_import'];
                        $rlSmarty->assign('allow_import', false);
                    }
                } else {
                    $errors[] = $lang['eil_import_no_content'];
                }
            }
        }
    }
    elseif ( $_SESSION['iel_data']['post'] && $action == 'import-preview' )
    {
        $import_details = array(
            array(
                'name' => $lang['eil_total_listings'],
                'value' => count($_SESSION['iel_data']['post']['rows_tmp'])
            ),
            array(
                'name' => $lang['eil_per_run'],
                'value' => $_SESSION['iel_data']['post']['import_per_run']
            ),
            array(
                'name' => $lang['listing_type'],
                'value' => $rlListingTypes -> types[$_SESSION['iel_data']['post']['import_listing_type']]['name']
            ),
            array(
                'name' => $lang['eil_default_category'],
                'value' => $lang[$categories[$_SESSION['iel_data']['post']['import_category_id']]['pName']]
            ),
            array(
                'name' => $lang['eil_default_status'],
                'value' => $lang[$_SESSION['iel_data']['post']['import_status']]
            )
        );
        if($config['membership_module']  && !$config['allow_listing_plans']) {
            $_SESSION['iel_data']['post']['import_plan_id'] = $mp_plan_using['Plan_ID'];
            $import_details[] = array(
                    'name' => $lang['eil_default_plan'],
                    'value' => $mp_plan_info['name']
            );
        } else {
            $import_details[] = array(
                    'name' => $lang['eil_default_plan'],
                    'value' => $plans[$_SESSION['iel_data']['post']['import_plan_id']]['name']
            );
        }

        $rlSmarty -> assign_by_ref('import_details', $import_details);
    }
    elseif ( $_POST['action'] == 'export_condition' || isset($_GET['refine']) )
    {
        $reefless -> loadClass('Search');

        if ( $_SESSION['eil_data']['post'] && !$_POST['from_post'] )
        {
            $_POST = $_SESSION['eil_data']['post'];
        }
        
        $lt = $_POST['export_listing_type'];
        if ($lt) {
            $sql = "SELECT *, CONCAT('categories+name+', `Key`) as `pName` FROM `" . RL_DBPREFIX . "categories` ";
            $sql .= "WHERE `Type` = '{$lt}' AND `Parent_ID` = 0";
            $categories = $rlDb->getAll($sql);
            $rlSmarty->assign_by_ref('categories', $categories);
            
            if ($_POST['f'] && !$_REQUEST['f']) {
                $_REQUEST['f'] = $_POST['f'];
            }
            
            $fields = $rlSearch->buildSearch($lt . '_quick', $lt);
            $rlSearch->getFields($lt . '_quick', $lt);
            $rlSmarty->assign_by_ref('fields', $fields);
        }

        if ( $_POST['from_post'] && $_POST['action'] == 'export_condition' )
        {
            if ( !$_POST['export_format'] )
            {
                $errors[] = str_replace( '{field}', "<b>\"".$lang['eil_file_format']."\"</b>", $lang['notice_select_empty']);
                $error_fields .= 'export_format,';
            }
            if ( !$lt )
            {
                $errors[] = str_replace( '{field}', "<b>\"".$lang['listing_type']."\"</b>", $lang['notice_select_empty']);
                $error_fields .= 'export_listing_type,';
            }
    
            if ($lt) {
                $form_id = $_POST['export_category_id']
                    ? $_POST['export_category_id']
                    : $rlListingTypes->types[$lt]['Cat_general_cat'];

                $form = method_exists(\Flynax\Utils\Category::class, 'buildForm')
                    ? \Flynax\Utils\Category::buildForm($form_id, $rlListingTypes->types[$lt])
                    : $GLOBALS['rlCategories']->buildListingForm($form_id, $rlListingTypes->types[$lt]);
                if (!$form) {
                    $errors[] = $lang['eil_no_form'];
                }
                $GLOBALS['geo_filter_data'] = array();
                if (!$rlSearch->search($_POST['f'], $lt, 0, 1)) {
                    $errors[] = $lang['eil_no_listings_found'];
                }
            }
            if ( !$errors )
            {
                unset($_POST['action'], $_POST['from_post']);
                $_SESSION['eil_data']['post'] = $_POST;

                /* redirect */
                $url = SEO_BASE;
                if ( $config['mod_rewrite'] ) {
                    $url .= $page_info['Path'] .'/export-table.html';
                }
                else {
                    $url .= '?page='. $page_info['Path'] .'&action=export-table';
                }
                $reefless -> redirect(null, $url);
            }

            /* poor thing :) */
            if ( $_POST['f'] )
            {
                $_POST = $_POST + $_POST['f'];
                unset($_POST['f']);
            }
        }
        else
        {
            unset($_SESSION['eil_data']);
        }

        $ei_mode = 'export';
        $bcAStep = $lang['eil_export'];
        $rlSmarty -> assign('cpTitle', $lang['eil_export_criteria']);

        /* clear last import attempt data */
        unlink($_SESSION['iel_data']['file']);
        $reefless -> deleteDirectory($_SESSION['iel_data']['archive_dir']);

        unset($_SESSION['iel_data']);
        unset($_SESSION['eil_import_grid_message']);
    }
    elseif ( $action == 'export-table' )
    {
        $reefless -> loadClass('Search');

        $lt = $_SESSION['eil_data']['post']['export_listing_type'];
        $category_id = $_SESSION['eil_data']['post']['export_category_id'];
        $rlSearch -> getFields($lt. '_quick', $lt);
        
        $form_id = $category_id ? $category_id : $rlListingTypes -> types[$lt]['Cat_general_cat'];
        $form = method_exists(\Flynax\Utils\Category::class, 'buildForm')
            ? \Flynax\Utils\Category::buildForm($form_id, $rlListingTypes->types[$lt])
            : $GLOBALS['rlCategories']->buildListingForm($form_id, $rlListingTypes->types[$lt]);
        $fields = array();

        // get the maximum depth of all categories
        $levels =  $rlExportImport->getMaxCategoryLevelArray();

        $sys_fields = array(
            'Cat_key' => array(
                'pName' => 'category',
                'Key' => 'Cat_key'
            ),
            'Account_username' => array(
                'pName' => 'username',
                'Key' => 'Account_username'
            ),
            'Picture_URLs' => array(
                'pName' => 'eil_pictures_urls',
                'Key' => 'Picture_URLs'
            ),
            'Account_email' => array(
                'pName' => 'mail',
                'Key' => 'Account_email',
            ),
            'Date' => array(
                'pName' => 'date',
                'Key' => 'Date',
            ),
        );
        $sys_fields = array_merge($levels, $sys_fields);
        $fields = array_merge($sys_fields, $fields);
        foreach ($form as $group)
        {
            $fields = array_merge($fields, $group['Fields']);
        }
        $_SESSION['fields'] = $fields;
        $rlSmarty -> assign('fields', $fields);
        
        $GLOBALS['geo_filter_data'] = array();
        $listings = $rlSearch -> search($_SESSION['eil_data']['post']['f'], $lt, 0, 1000);
        $listings_count = count($listings);

        /* add bread crumbs step */
        $bread_crumbs[] = array(
            'name' => $lang['eil_export_listings'] ." ({$listings_count})"
        );
        $page_info['name'] = $lang['eil_export_listings'] ." ({$listings_count})";

        foreach ($listings as &$listing)
        {
            $listing = $rlExportImport->prepareMultiCategory($listing, $levels);
            foreach ($listing as $field_key => &$field)
            {
                if ( array_key_exists($field_key, $sys_fields) )
                {
                    switch ($field_key){
                        case 'Cat_key':
                            $field = $lang['categories+name+'. $field];

                            break;

                        case 'Picture_URLs':
                            if ( $listing['Photos_count'] )
                            {
                                $field = '';
                                if ( $pictures = $rlDb -> fetch(array('Photo'), array('Listing_ID' => $listing['ID'], 'Status' => 'active'), "ORDER BY `Type` DESC, `Position` ASC", null, 'listing_photos') )
                                {
                                    foreach ($pictures as $picture)
                                    {
                                        $field .= RL_FILES_URL . $picture['Photo'] .', ';
                                    }
                                    $field = trim($field, ', ');
                                }
                            }
                            break;
                    }
                }
                else
                {
                    if ( $fields[$field_key] )
                    {
                        $field = $rlCommon -> adaptValue($fields[$field_key], $field);
                    }
                }
            }
        }
        $_SESSION['export_data'] = $listings;
        $rlExportImport->prepareExportPreviewGrid($listings);
        
        $rlSmarty -> assign_by_ref('listings', $listings);

        if ( $_POST['action'] == 'export_table' )
        {
            if ( !$_POST['cols'] )
            {
                $errors[] = $lang['eil_no_fields_checked'];
            }

            if ( !$errors )
            {
                $cat_key = $category_id ? str_replace('_', '-', $rlDb -> getOne('Key', "`ID` = '{$category_id}'", 'categories')) : $rlListingTypes -> types[$lt]['name'];
                $file_name = 'export-'. $cat_key .'-'. date('M\.j\-Y');
                $fileFormat = $_SESSION['eil_data']['post']['export_format'];

                switch ($fileFormat){
                    case 'xls':
                    case 'xlsx':
                        /* load the utf8 lib */
                        loadUTF8functions('ascii', 'utf8_to_ascii');

                        /* create a phpExcel object */
                        $objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

                        try {
                            /* set document properties */
                            $objPHPExcel->getProperties()->setCreator($config['owner_name'])
                                ->setLastModifiedBy($config['owner_name'])
                                ->setTitle($cat_key)
                                ->setSubject($cat_key);

                            $objPHPExcel->setActiveSheetIndex(0);

                            $row = 1;
                            $col = $index = 0;

                            /* write header */
                            foreach ($fields as $field) {
                                if ($_POST['cols'][$index]) {
                                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row,
                                        $lang[$field['pName']]);
                                    $col++;
                                }
                                $index++;
                            }
                            $row++;

                            /* highlight the title row */
                            $styleArray = array(
                                'fill' => array(
                                    'type' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'startcolor' => array(
                                        'argb' => 'FFd6d6d6'
                                    )
                                )
                            );

                            $last_col = $objPHPExcel->getActiveSheet()->getHighestColumn();
                            $objPHPExcel
                                ->getActiveSheet()
                                ->getStyle('A1:' . $last_col . '1')
                                ->applyFromArray($styleArray);

                            /* write listings */
                            $exclude_listings = array_filter(explode(',', $_POST['exclude_from_list']));
                            foreach ($listings as $l_index => &$listing) {
                                if ($exclude_listings && in_array($listing['ID'], $exclude_listings)) {
                                    continue;
                                }

                                $col = $index = 0;
                                foreach ($fields as $field) {
                                    if ($_POST['cols'][$index]) {
                                        $set_value = strip_tags($listing[$field['Key']]);

                                        /* new line trick */
                                        if ((bool) preg_match("/" . PHP_EOL . "/", $set_value)) {
                                            $set_value = preg_replace("/" . PHP_EOL . "/", "\n", $set_value);
                                            $what = array("<br>", "<br />", "<br/>");
                                            $set_value = str_replace($what, "\n", $set_value);
                                            $objPHPExcel
                                                ->getActiveSheet()
                                                ->getStyleByColumnAndRow($col, $row)
                                                ->getAlignment()
                                                ->setWrapText(true);
                                        }

                                        /* add value to cell */
                                        $objPHPExcel
                                            ->getActiveSheet()
                                            ->setCellValueByColumnAndRow($col, $row, $set_value);

                                        $col++;
                                    }
                                    $index++;
                                }
                                $row++;
                            }

                            /* send necessary headers */
                            header("Content-Disposition: attachment; filename={$file_name}.{$fileFormat}");
                            header("Content-Type: application/vnd.ms-excel;");
                            header("Cache-Control: max-age=0");

                            /* force download */
                            $objWriter = $fileFormat == 'xlsx'
                                ? new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objPHPExcel)
                                : new \PhpOffice\PhpSpreadsheet\Writer\Xls($objPHPExcel);
                            $objWriter->save('php://output');
                            exit;
                        } catch (Exception $e) {
                            $message = "Export/Import plugin couldn't save to Excel {$e->getMessage()}";
                            $GLOBALS['rlDebug']->logger($message);
                        }
                        break;

                    case 'csv':
                        header("Content-Disposition: attachment; filename={$file_name}.csv; charset=utf-8");
                        header("Content-Type: application/vnd.ms-excel; charset=utf-8");

                        $index = 0;

                        /* write header */
                        foreach ($fields as $field)
                        {
                            if ( $_POST['cols'][$index] )
                            {
                                $csv .= $lang[$field['pName']] .',';
                            }
                            $index++;
                        }

                        $csv = rtrim($csv, ',') . PHP_EOL;

                        /* write listings */
                        $exclude_listings = array_filter(explode(',',$_POST['exclude_from_list']));
                        foreach ($listings as $l_index => &$listing)
                        {
                            if ($exclude_listings && in_array($listing['ID'], $exclude_listings)) {
                                continue;
                            }
                            
                            $index = 0;
                            foreach ($fields as $field)
                            {
                                if ( $_POST['cols'][$index] )
                                {
                                    $set_value = strip_tags($listing[$field['Key']]);
                                    $set_value = '"'. str_replace('"', '""', $set_value) .'"';
                                    $csv .= $set_value .',';
                                }
                                $index++;
                            }
                            $csv = rtrim($csv, ',') . PHP_EOL;
                        }

                        echo $csv;
                        exit;

                        break;
                }
            }
        }
    }
}
