<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : EXPORT_IMPORT.INC.PHP
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

use Flynax\Plugins\ExportImport\Handlers\Escort;
use Flynax\Plugins\ExportImport\Handlers\File;
use Flynax\Plugins\ExportImport\Handlers\Jobs;
use Flynax\Plugins\ExportImport\Helpers;

$reefless->loadClass('ExportImport', null, 'export_import');
$reefless->loadClass('Account');
$reefless->loadClass('ListingsAdmin', 'admin');

// register ajax methods
$rlXajax->registerFunction(array('massActions', $rlListingsAdmin, 'ajaxMassActions'));
$rlXajax->registerFunction(array('deleteListing', $rlListingsAdmin, 'ajaxDeleteListingAdmin'));

/* multifield */
if (Helpers::isMultiFieldInstalled() && !Helpers::isNewMultifield()) {
	$reefless->loadClass('MultiField', null, 'multiField');
	$rlXajax->registerFunction(array('mfGetNext', $rlMultiField, 'ajaxGetNext'));
	$rlXajax->registerFunction(array('mfBuild', $rlMultiField, 'ajaxBuild'));

	$multi_formats = Helpers::getMultiFormats();
	$rlSmarty->assign('multi_formats', $multi_formats);
}
/* multifield end */

/* Membership plan */
if(file_exists(RL_CLASSES . "rlMembershipPlan.class.php") && $config['membership_module']) {
    $reefless->loadClass('MembershipPlan');
    $sql = "SELECT `ID` FROM `".RL_DBPREFIX."membership_plans`";
    $plans_id = $rlDb->getAll($sql);
    $membership_plans = array();
    foreach ($plans_id as $mpKey => $mpValue) {
        $mp_info =   $rlMembershipPlan->getPlan($mpValue['ID']);
        $mp_info['Type'] = "membership";
        $membership_plans[] = $mp_info;
    }
    $membership_module = true;
    $rlSmarty->assign('membership_module', true);
}
/* Membership plan end */

$max_file_size = str_replace('M', '', ini_get('upload_max_filesize'));
$rlSmarty->assign_by_ref( 'max_file_size', $max_file_size );

if ( !$_REQUEST['xjxfun'] )
{
	$allowed_types = array('csv', 'xls', 'xlsx');
	
	/* reset import file request */
	if ( isset($_GET['reset']) )
	{
		unlink($_SESSION['iel_data']['file']);
		$reefless -> deleteDirectory($_SESSION['iel_data']['archive_dir']);
		unset($_SESSION['iel_data'], $_SESSION['eil_data']);
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

        if (!$errors) {
			/* upload main listings file */
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $dynamic = time() . mt_rand();
            $file_name = $_SESSION['sessAdmin']['user'] . '_import_' . $dynamic . '.' . $ext;
            $file = RL_UPLOAD . $file_name;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
                $_SESSION['iel_data'] = array(
                    'file' => $file,
                    'file_name' => $file_name
                );

                /* upload pictures archive */
                $ext = pathinfo($_FILES['archive']['name'], PATHINFO_EXTENSION);
                $archive_name = $_SESSION['sessAdmin']['user'] . '_import_zip_' . $dynamic . '.' . $ext;
                $archive = RL_UPLOAD . $archive_name;
                $archive_dir = RL_UPLOAD . $_SESSION['sessAdmin']['user'] . '_import_zip_' . $dynamic;

                if (move_uploaded_file($_FILES['archive']['tmp_name'], $archive)) {
                    $_SESSION['iel_data']['archive_dir'] = $archive_dir;
                    \Flynax\Plugins\ExportImport\Adapters\ZipArchiveAdapter::extract($archive, $archive_dir);
                }

                $reefless->redirect(array('controller' => $controller, 'action' => 'import'));
            } else {
                $errors[] = $lang['eil_import_unable_to_upload'];
            }
		}
	}
	
	if ( in_array($_GET['action'], array('import', 'importing')) )
	{
        $bcAStep = $lang['eil_preview'];

		/* get requested listing type categoies */
		if (version_compare($config['rl_version'], '4.4.0') < 0 && ($_POST['import_listing_type'] || $_SESSION['iel_data']['post']['import_listing_type']))	{
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
		
		/* get available plans */
		$reefless -> loadClass('Plan');
		$plans_tmp = $rlPlan -> getPlans(array('listing', 'package'));
		foreach ($plans_tmp as $plan)
		{
			$plans[$plan['ID']] = $plan;
		}
	
        unset($plans_tmp);
		$rlSmarty -> assign_by_ref('plans', $plans);
		
		$per_run = array(2, 5, 10, 20, 40, 50, 100);
		$rlSmarty -> assign_by_ref('per_run', $per_run);
        
        if (!isset($_GET['edit'])) {
            unset($_SESSION['eil_data']);
        }
	}
	
	/* parse uploaded file */
	if ( $_SESSION['iel_data']['file'] && is_readable($_SESSION['iel_data']['file']) && $_GET['action'] == 'import' )
	{
		$ei_mode = 'import_form';
		$bcAStep = $lang['eil_import_form'];
		
		/* get listing fields */
		$rlDb -> setTable('listing_fields');
        $where = "WHERE (`Readonly` = '0' OR `Key` LIKE '%level%') AND `Type` <> 'image' ";
        $where .= "AND `Type` <> 'accept' AND `Status` = 'active'";
        
        if (Escort::isEscortInstallation()) {
            $where .= "OR (`Key` = 'availability' OR `Key` = 'escort_rates' OR `Key` = 'escort_tours')";
        }
        
		$listing_fields_tmp = $rlDb -> fetch(array('Key', 'Type', 'Default'), null, $where);
		$listing_fields_tmp = $rlLang -> replaceLangKeys($listing_fields_tmp, 'listing_fields', array('name'), RL_LANG_CODE, 'admin');
        foreach ($listing_fields_tmp as $tmp_listign_field) {
            $listing_fields[$tmp_listign_field['Key']] = $tmp_listign_field;
        }
        unset($listing_fields_tmp);
        $rlSmarty->assign_by_ref('listing_fields', $listing_fields);
        
        //Get maximum category child
        $levels = $rlExportImport->getMaxCategoryLevelArray(false);
        $system_fields[] = array(
            'Account_ID' => array(
                'Key' => 'Account_ID',
                'name' => $lang['eil_owner_account'],
            ),
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
            )
        );
        $system_fields = array_merge($levels, $system_fields[0]);
        /* system fields */
		$rlSmarty -> assign_by_ref('system_fields', $system_fields);
		
		if ( $_POST['from_post'] && $_POST['action'] == 'import_form' )
		{
            $username = (string) $_POST['import_account_id_tmp'];
            $accountID = 0;

            if ($username) {
                $where = "`Username` = '{$username}'";
                $accountID = (int) $rlDb->getOne('ID', $where, 'accounts');
            }

            if($_SESSION['import_data']) {
                $_POST['data'] = $_SESSION['import_data'];
                $rows_array = array();
                for ($i = 1; $i < count($_POST['data']); $i++) { 
                    $rows_array[$i] = 1;
                }
                $_POST['rows'] = $rows_array;
            }

            /* Pagination */
			$rlExportImport->prepareImportPreviewGrid($_POST['data'], true);
			if ( !$_POST['import_listing_type'] )
			{
				$errors[] = str_replace( '{field}', "<b>\"".$lang['listing_type']."\"</b>", $lang['notice_select_empty']);
				$error_fields[] = 'import_listing_type';
			}
			if ( !$_POST['import_category_id'] )
			{
				$errors[] = str_replace( '{field}', "<b>\"".$lang['eil_default_category']."\"</b>", $lang['notice_select_empty']);
				$error_fields[] = 'import_category_id';
			}
			if ( !$_POST['import_status'] )
			{
				$errors[] = str_replace( '{field}', "<b>\"".$lang['eil_default_status']."\"</b>", $lang['notice_select_empty']);
				$error_fields[] = 'import_status';
			}

            if (!$accountID) {
                $errors[] = str_replace(
                    '{field}',
                    sprintf('<b>"%s"</b>', $lang['eil_default_owner']),
                    $lang['notice_select_empty']
                );
                $error_fields[] = 'import_account_id';
                $error_fields[] = 'import_account_id_tmp';
            }

            if (!$membership_module && !$_POST['import_plan_id'])
			{
				$errors[] = str_replace( '{field}', "<b>\"".$lang['eil_default_plan']."\"</b>", $lang['notice_select_empty']);
				$error_fields[] = 'import_plan_id';
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
                unset($_SESSION['import_data']);
				$_SESSION['iel_data']['post'] = $_POST;
				$_SESSION['iel_data']['post']['rows_tmp'] = $_POST['rows'];
				$reefless -> redirect(array('controller' => $controller, 'action' => 'importing'));
			}
		}
		else
		{
            if ( $_SESSION['iel_data']['post']  )
			{
                if (isset($_GET['edit'])) {
                    $_POST = $_SESSION['iel_data']['post'];
                    $rlExportImport->prepareImportPreviewGrid($_POST['data'], true);
                    $_SESSION['import_data'] = $_POST['data'];
                } else {
                    unset($_SESSION['iel_data']['post']);
                }
            }
			else
			{
				/* get from file */
				switch (pathinfo($_SESSION['iel_data']['file'], PATHINFO_EXTENSION)){
					case 'csv':
						$handle = fopen($_SESSION['iel_data']['file'], 'r');
                        while (($data = fgetcsv($handle)) !== false) {
                            if (!array_filter($data)) {
                                continue;
                            }
                            
                            $source[] = $data;
                        }

						break;

                    case 'xls':
                    case 'xlsx':
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

                if ($source) {
                    foreach ($source[0] as $key => $col) {
                        $_POST['cols'][$key] = true;
                    }

                    for ($i = 0; $i < count($source); $i++) {
                        foreach ($source[$i] as $index => $cel) {
                            $_POST['data'][$i][$index] = $cel;
                        }
                    }

                    /* Pagination */
                    $rlExportImport->prepareImportPreviewGrid($_POST['data']);

                    $_SESSION['import_data'] = $_POST['data'];
                } else {
                    unset($ei_mode);
                    $errors[] = $lang['eil_import_no_content'];
                }
			}
		}
	}
	elseif ( $_SESSION['iel_data']['post'] && $_GET['action'] == 'importing' )
	{
		$ei_mode = 'import_importing';
		$bcAStep = $lang['eil_preview'];
		$category_key = $rlDb->getOne('Key', "`ID` = '{$_SESSION['iel_data']['post']['import_category_id']}'", 'categories');

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
				'value' => $rlLang->getPhrase('categories+name+' . $category_key, null, null, true)
			),
			array(
				'name' => $lang['eil_default_status'],
				'value' => $lang[$_SESSION['iel_data']['post']['import_status']]
			),
			array(
				'name' => $lang['eil_default_owner'],
				'value' => '<a title="'. $lang['view_account'] .'" target="_blank" href="'. RL_URL_HOME . ADMIN .'/index.php?controller=accounts&amp;action=view&amp;userid='. $_SESSION['iel_data']['post']['import_account_id'] .'">'. $_SESSION['iel_data']['post']['import_account_id_tmp'] .'</a>'
			),
			array(
				'name' => $lang['eil_default_plan'],
				'value' => $plans[$_SESSION['iel_data']['post']['import_plan_id']]['name']
			),
			array(
				'name' => $lang['eil_paid'],
				'value' => $_SESSION['iel_data']['post']['import_paid'] ? $lang['yes'] : $lang['no']
			)
		);
		$rlSmarty -> assign_by_ref('import_details', $import_details);
	}
	
	/* export */
    if ($_GET['action'] == 'export') {
        $reefless->loadClass('Search');

        if (Helpers::isMultiFieldInstalled()) {
            $rlSmarty->assign('multiFieldExist', true);
        }

		if ( $_SESSION['eil_data']['post'] && !$_POST['from_post'] )
		{
			$_POST = $_SESSION['eil_data']['post'];
		}
		
		$lt = $_POST['export_listing_type'];
		
		if ($lt) {
			if (version_compare($config['rl_version'], '4.4.0') < 0) {
				
				$sql ="SELECT *, CONCAT('categories+name+', `Key`) as `pName` FROM `".RL_DBPREFIX."categories` WHERE `Type` = '{$lt}' AND `Parent_ID` = 0";
				$categories_tmp = $rlDb->getAll($sql);
				foreach ($categories_tmp as $category)
				{
					$categories[$category['ID']] = $category;
				}
				unset($categories_tmp, $category);
				$rlSmarty -> assign_by_ref('categories', $categories);
			}

			$reefless -> loadClass('Categories');
			
			$fields = $rlSearch -> buildSearch($lt. '_quick', $lt);
			$rlSearch -> getFields($lt. '_quick', $lt);
			$rlSmarty -> assign_by_ref('fields', $fields);
		}

		if ( $_POST['from_post'] && $_POST['action'] == 'export_condition' )
		{
			if ( !$_POST['export_format'] )
			{
				$errors[] = str_replace( '{field}', "<b>\"".$lang['eil_file_format']."\"</b>", $lang['notice_select_empty']);
				$error_fields[] = 'export_format';
			}
			if ( !$lt )
			{
				$errors[] = str_replace( '{field}', "<b>\"".$lang['listing_type']."\"</b>", $lang['notice_select_empty']);
				$error_fields[] = 'export_listing_type';
			}
            
            if ($lt) {
                $form_id = $_POST['export_category_id']
                    ? $_POST['export_category_id']
                    : $rlListingTypes->types[$lt]['Cat_general_cat'];

                $form = method_exists(\Flynax\Utils\Category::class, 'buildForm')
                    ? \Flynax\Utils\Category::buildForm($form_id, $rlListingTypes->types[$lt])
                    : $rlCategories->buildListingForm($form_id, $rlListingTypes->types[$lt]);
                if (!$form) {
                    $errors[] = $lang['eil_no_form'];
                }
                $category_id = $_POST['export_category_id'];
                if (!$rlSearch->search($_POST['f'], $lt, 0, 1)) {
                    $errors[] = $lang['eil_no_listings_found'];
                }
                
            }
			
			if ( !$errors )
			{
				unset($_POST['action'], $_POST['from_post']);
				$_SESSION['eil_data']['post'] = $_POST;
				$reefless -> redirect(array('controller' => $controller, 'action' => 'export_table'));
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
	}
	elseif ( $_GET['action'] == 'export_table' )
	{

		define('EIL_EXPORT_TABLE', true);
		$reefless -> loadClass('Search');
		
		$ei_mode = 'export_table';
		$bcAStep = $lang['eil_export'];
		
		$lt = $_SESSION['eil_data']['post']['export_listing_type'];
		$category_id = $_SESSION['eil_data']['post']['export_category_id'];
		$rlSearch -> getFields($lt. '_quick', $lt);
		
		$form_id = $category_id ? $category_id : $rlListingTypes -> types[$lt]['Cat_general_cat'];
        $form = method_exists(\Flynax\Utils\Category::class, 'buildForm')
            ? \Flynax\Utils\Category::buildForm($form_id, $rlListingTypes->types[$lt])
            : $rlCategories->buildListingForm($form_id, $rlListingTypes->types[$lt]);
		$fields = array();

        // get the maximum depth of all categories
        $levels =  $rlExportImport->getMaxCategoryLevelArray();

		$sys_fields = array(
			'Account_username' => array(
				'pName' => 'username',
				'Key' => 'Account_username'
			),
			'Picture_URLs' => array(
				'pName' => 'eil_pictures_urls',
				'Key' => 'Picture_URLs'
			),
			'Loc_latitude' => array(
				'pName' => 'eil_loc_latitude',
				'Key' => 'Loc_latitude'
			),
			'Loc_longitude' => array(
				'pName' => 'eil_loc_longitude',
				'Key' => 'Loc_longitude'
			),
			'Account_email' => array(
				'pName' => 'mail',
				'Key' => 'Account_email'
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
			if ( !$group['Fields'] )
				continue;
			
			$fields = array_merge($fields, $group['Fields']);
		}
		$_SESSION['fields'] = $fields;
		$rlSmarty -> assign('fields', $fields);
		$listings = $rlSearch -> search($_SESSION['eil_data']['post']['f'], $lt, 0, 5000);
		$listings_count = count($listings);
        
		$rlSmarty -> assign('cpTitle', $lang['eil_export_listings'] ." ({$listings_count})");
		foreach ($listings as &$listing)
		{
            $listing = $rlExportImport->prepareMultiCategory($listing, $levels);

			foreach ($listing as $field_key => &$field)
			{
				if ( array_key_exists($field_key, $sys_fields) )
				{
					switch ($field_key){
						case 'Cat_key':
						case 'Subcat_key':
							$field = $lang['categories+name+'. $field];
							break;

						case 'Picture_URLs':
                            if ($listing['Photos_count']) {
                                $field = '';
                                $picture = $pictures = $rlDb->fetch(
                                    array('Photo'),
                                    array('Listing_ID' => $listing['ID'], 'Status' => 'active'),
                                    "ORDER BY `Type` DESC, `Position` ASC", null, 'listing_photos'
                                );

                                if ($picture) {
                                    foreach ($pictures as $picture) {
                                        $field .= RL_FILES_URL . $picture['Photo'] . ', ';
                                    }
                                    $field = trim($field, ', ');
                                }

                            }

                            if (Jobs::isBelongsToJob($listing)) {
                                $jobsManager = new Jobs();
                                $field = $jobsManager->export($listing) ?: $field;
                            }
                    break;
					}
				}
				else
				{
                    if ($fields[$field_key]) {
                        switch ($fields[$field_key]['Key']) {
                            case 'availability':
                                $field = Escort::exportAvailability($listing);
                                break;
                            case 'escort_rates':
                                $field = Escort::exportEscortRates($listing['ID']);
                                break;
                            case 'escort_tours':
                                $field = Escort::exportTours($listing['ID']);
                                break;
                            default:
                                if ($fields[$field_key]['Type'] == 'file') {
                                    $field = File::prepareFileUrl($listing[$field_key]);
                                } else {
                                    $field = $rlCommon->adaptValue($fields[$field_key], $field);
                                }

                                break;
                        }
                    }

				}
			}
		}

        unset($listing);
        unset($field);
        $_SESSION['export_data'] = $listings;
        $rlExportImport->prepareExportPreviewGrid($listings);
        
		$rlSmarty -> assign_by_ref('listings', $listings);
		
		if ( $_POST['action'] == 'export_table' )
		{
		    
            if (!$_POST['cols']) {
                $errors[] = $lang['eil_no_fields_checked'];
            }
			
			if ( !$errors )
			{
				$cat_key = $category_id ? str_replace('_', '-', $rlDb -> getOne('Key', "`ID` = '{$category_id}'", 'categories')) : $rlListingTypes -> types[$lt]['name'];
				$file_name = 'export-'. $cat_key .'-'. date('M\.j\-Y');
				$fileFormat = $_SESSION['eil_data']['post']['export_format'];
                switch ($fileFormat) {
                    case 'xls':
                    case 'xlsx':
                        /* load the utf8 lib */
                        loadUTF8functions('ascii', 'utf8_to_ascii');

                        /* create a phpExcel object */
                        $objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                        try {
                            /* set document properties */
                            $objPHPExcel
                                ->getProperties()
                                ->setCreator($config['owner_name'])
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
                                        'argb' => 'FFd6d6d6',
                                    ),
                                ),
                            );

                            $last_col = $objPHPExcel->getActiveSheet()->getHighestColumn();
                            $objPHPExcel->getActiveSheet()->getStyle('A1:' . $last_col . '1')->applyFromArray($styleArray);

                            /* write listings */
                            $exclude_listings = array_filter(explode(',', $_POST['exclude_from_list']));

                            foreach ($_SESSION['export_data'] as $l_index => $export_listing) {
                                if ($exclude_listings && in_array($export_listing['ID'], $exclude_listings)) {
                                    continue;
                                }

                                $col = $index = 0;

                                foreach ($fields as $field) {
                                    if ($_POST['cols'][$index]) {
                                        switch ($field['Type']) {
                                            case 'file':
                                                $set_value = File::export($field['Key'], $export_listing['ID']);
                                                break;
                                            default:

                                                $set_value = strip_tags($export_listing[$field['Key']]);
                                                break;
                                        }


                                        /* new line trick */
                                        if ((bool) preg_match("/" . PHP_EOL . "/", $set_value)) {
                                            $set_value = preg_replace("/" . PHP_EOL . "/", "\n", $set_value);
                                            $set_value = str_replace(array("<br>", "<br />", "<br/>"), "\n", $set_value);
                                            $objPHPExcel
                                                ->getActiveSheet()
                                                ->getStyleByColumnAndRow($col, $row)
                                                ->getAlignment()
                                                ->setWrapText(true);
                                        }

                                        /* add value to cell */
                                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $set_value);
                                        $col++;
                                    }

                                    $index++;
                                }

                                $row++;
                            }

                            // clear data
                            unset($_SESSION['export_data']);
                            unset($_SESSION['fields']);

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
                            foreach ($fields as $field) {
                                if ($_POST['cols'][$index]) {
                                    $set_value = strip_tags($listing[$field['Key']]);
                                    $set_value = '"' . str_replace('"', '""', $set_value) . '"';
                                    $csv .= $set_value . ',';
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
	
	/* define default mode */
	if ( !$ei_mode )
	{
		$rlSmarty -> assign('cpTitle', $lang['eil_import']);
		$ei_mode = 'import_upload';
	}
	
	$rlSmarty -> assign_by_ref('ei_mode', $ei_mode);
}
