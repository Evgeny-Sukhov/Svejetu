<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : RLEXPORTIMPORT.CLASS.PHP
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
use Flynax\Plugins\ExportImport\Handlers\ListingPackages;
use Flynax\Plugins\ExportImport\Helpers;

require_once __DIR__ . '/vendor/autoload.php';

class rlExportImport extends reefless
{
    /**
     * @var loop category id
     **/
    var $loop_category_id = false;
    
    /**
    * @var loop account id
    **/
    var $loop_account_id = false;

    /**
     * @since 3.6.0
     *
     * @var array - Pages of the Export Import plugin
     */
    protected $pluginPages = array('xls_export_import');

    /**
    * Fetch categories (as select option) by listing type key.
    *
    * @package xajax
    * @param   string $type      - Listing type key.
    * @param   string $el        - DOM element ID
    * @param   bool   $user_mode - Is request from front-end
    * @return  array  $out       - Array with built DOM
    **/
    function ajaxFetchOptions( $type = "", $el = 'import_category_id', $user_mode = false )
    {
        global $rlSmarty, $rlCommon, $lang;

        if (!$lang) {
            $lang = $GLOBALS['rlLang']->getLangBySide('frontEnd', RL_LANG_CODE);
        }

        $categories = $GLOBALS['rlCategories']->getCategories(0, $type);

        /* populate categories dropdown */
        $options = sprintf("<option value=''>%s</option>", $lang['eil_no_categories_available']);
        if ($categories) {
            $options = sprintf("<option value=''>%s</option>", $lang['select']);
            foreach ($categories as $category) {
                $margin = $category['margin'] ? 'margin-left: ' . $category['margin'] . 'px;' : '';
                $highlight = $category['Level'] == 0 ? 'class="highlight_opt highlight_option"' : '';
                if (defined('REALM') && REALM == 'admin') {
                    $count = $el == 'export_category_id' ? " ({$category['Count']})" : '';
                }

                $options .= sprintf(
                    "<option %s style='%s' value='%s'>%s %s</option>",
                    $highlight,
                    $margin,
                    $category['ID'],
                    $lang[$category['pName']],
                    $count
                );
            }
        }
        
        $out['status'] = 'ok';
        $out['html']['category'] = $options;

        function addJS() {}

        // Temporary solution, register anonymouse function to prevent smarty parse error
        $rlSmarty->register_function('addJS', 'addJS');

        /* build search form */
        if ($el == 'export_category_id') {
            $this->loadClass('Search');
            $fields = $GLOBALS['rlSearch']->buildSearch($type . '_quick', $type);
            $rlSmarty->assign_by_ref('fields', $fields);
            $rlSmarty->assign_by_ref('lang', $lang);
            $tpl = RL_PLUGINS . 'export_import/' . ($user_mode ? '' : 'admin/') . 'search.tpl';
            $form_html = $rlSmarty->fetch($tpl, null, null, false);
            $out['html']['form'] = $form_html;
        }
        $out['js'] = !$user_mode ? '' : "flynaxTpl.customInput();";

        if (Helpers::isMultiFieldInstalled()) {
            $multi_formats = Helpers::getMultiFormats();

            if ($multi_formats) {
                $js = '';
                foreach ($multi_formats as $multi_format) {
                    $sql = "SELECT * FROM `" . RL_DBPREFIX . "listing_fields` ";
                    $sql .= "WHERE `Condition` = '{$multi_format['Key']}'";
                    $related_fields = $this->getAll($sql);
                    foreach ($related_fields as $k => $field) {
                        $js .= <<< JAVASCRIPT
                        if (mfFields.indexOf('{$field['Key']}') < 0) {
                            mfFields.push('{$field['Key']}');
                        }
JAVASCRIPT;
                        $m_fields[] = $field;
                    }
                }

                $js .= <<< JAVASCRIPT
                var mfHandler = new mfHandlerClass();
                mfHandler.init('f', mfFields, []);
JAVASCRIPT;
                // if plugin version is less than 2.0.0
                if (!method_exists($GLOBALS['rlMultiField'], 'getPostPrefixByPage')) {
                    $rlSmarty->assign('fields', $m_fields);
                    $tpl2 = RL_PLUGINS . 'multiField' . RL_DS . 'mf_reg_js.tpl';
                    $js = $rlSmarty->fetch($tpl2, null, null, false);
                }

                $out['js'] .= $js;
            }
            
            $rlSmarty->assign('multi_formats', $multi_formats);
        }
        
        return $out;
    }
    
    /**
     * import listings
     *
     * @param array  $data - listings data from file
     * @param array  $available_rows - available listing rows indexes in $data array
     * @param array  $columns - field columns availability
     * @param array  $fields - fields keys assigned by columns
     * @param int    $start - start index in $available_rows array
     * @param int    $limit - limit of listings per single import
     * @param string $listing_type - listing type key
     * @param int    $category_id - category id
     * @param int    $account_id - owner account id
     * @param int    $plan_id - plan id
     * @param bool   $paid - is imported listings should be paid by default
     * @param string $status - default listing status
     * @param bool   $user_mode - request from front-end
     **@return bool
     */
    function import( $data, $available_rows, $columns, $fields, $start, $limit = 5, $listing_type, $category_id, $account_id, $plan_id, $paid, $status, $user_mode = false)
    {
        global $rlListingTypes, $lang, $rlActions, $config, $rlCategories, $plan_info;

        if (!$data || !$available_rows || !$limit || !$listing_type || !$category_id || !$account_id) {
            return false;
        }

        if (Helpers::isMultiFieldInstalled()) {
            $multi_formats = Helpers::getMultiFormats();
        }

        /* check for "reference number" plugin */
        if ($reference_plugin = $this->getOne('ID', "`Key` = 'ref' AND `Status` = 'active'", 'plugins')) {
            $this->loadClass('Ref', null, 'ref');
        }

        /* get requested fields info */
        $add_sql_fields = 'AND (';
        foreach ($fields as $field_index => $field)
        {
            if ( $field && $columns[$field_index] )
            {
                $add_sql_fields .= "`Key` = '{$field}' OR ";
                $available_fields[$field] = $field_index;
            }
        }
        $add_sql_fields = rtrim($add_sql_fields, ' OR ');
        $add_sql_fields .= ')';
        $fields_info_tmp = $this -> fetch(array('Key', 'Type', 'Default', 'Values', 'Condition', 'Map'), null, "WHERE `Status` <> 'trash' ". $add_sql_fields, null, 'listing_fields');
        
        foreach ($fields_info_tmp as $field)
        {
            $fields_info[$field['Key']] = $field;
        }
        unset($fields_info_tmp);
        
        /* get requested plan info */
        
        if ($config['membership_module']) {
            $account_info = $this->fetch('*', array('ID' => $account_id), null, 1, 'accounts', 'row');
            $paid = $account_info['Pay_date'];
            $plan_info = $this->fetch('*', array('ID' => $plan_id), null, 1, 'membership_plans', 'row');
        } else {
            $plan_info = $this -> fetch(array('Featured', 'Image', 'Image_unlim', 'Price', 'Limit', 'Type'), array('ID' => $plan_id), null, 1, 'listing_plans', 'row');
        }
        
        $listing_number = 1;

        /* collect import listings array */
        for ($index = $start; $index < $start + $limit; $index++)
        {
            $row_data = $data[$available_rows[$index]];
            $coming_plan = $plan_id;
            $coming_status = $status;
            
            if ( !$row_data )
                continue;
            
            $listing_mode = 'standard';

            if ($plan_info['Featured_listing'] && $config['membership_module']) {
                $listing_mode = 'featured';
            }
            
            /* collect data by form fields */
            $category_levels = $this->getAllCategories($fields);
            $html_fields = array();
            $insert_fields = array();
            
            foreach ($fields as $field_index => $field)
            {
                $field_info = $this->getListingFieldInfo($field);
    
                if ($field_info && $field_info['Type'] == 'textarea' && $field_info['Condition'] == 'html') {
                    $html_fields[] = $field;
                }
                if ($field && $columns[$field_index]) {
                    if ($field == 'Category_level_1') {
                        $this->findCategory($category_levels, $row_data);
                    } else {
                        // find keys of multi fields
                        if ($GLOBALS['plugins']['multiField'] && $multi_formats[$field_info['Condition']]) {
                            $value = $this->getMultifieldValue(
                                $row_data[$field_index],
                                $field,
                                $row_data,
                                $fields,
                                $fields_info
                            );
                        } else {
                            $value = $this->adaptValue($row_data[$field_index], $field, $fields_info);
                        }

                        if ($field_info && $field_info['Type'] == 'textarea') {
                            $find = array('\\n', '\\n\\r');
                            $value = str_replace($find, '<br/>', $value);
                        }

                        if (in_array($field_info['Type'], array('textarea', 'text'))) {
                            $value = str_replace(array('\"', "\\'"), array('"', '\''), $value);

                            if ($field_info['Type'] == 'textarea') {
                                $fieldMaxChars = $field_info['Values'];

                                if (strlen($value) > $fieldMaxChars) {
                                    $value = substr($value, 0, $fieldMaxChars);
                                }
                            }
                        }

                        if ($field_info['Type'] == 'file') {
                            $value = File::importToListing($row_data[$field_index]);
                        }
    
                        if (Escort::isEscortInstallation() && $field == 'availability') {
                            Escort::importAvailability($row_data[$field_index], $insert_fields);
                        }

                        if (!empty($value)) {
                            if ($fields_info[$field]['Map']) {
                                // ignore multi fields
                                if ($fields_info[$field]['Condition'] && $GLOBALS['plugins']['multiField']) {
                                    $where = "`Key` = '{$fields_info[$field]['Condition']}' AND `Status` = 'active'";
                                    if (
                                        $fields_info[$field]['Condition']
                                        && $this->getOne('ID', $where, 'multi_formats')
                                    ) {
                                        $loc_request = $loc_request
                                            ? $loc_request . ', ' . $row_data[$field_index]
                                            : $row_data[$field_index];
                                    } else {
                                        $loc_request = $loc_request ? $loc_request . ', ' . $value : $value;
                                    }
                                } else {
                                    $loc_request = $loc_request ? $loc_request . ', ' . $value : $value;
                                }
                            }
                            $insert_fields[$field] = preg_replace('/[\r]+/', '<br />', $value);
                        }
                    }
                }
            }

            $set_category_id = $this -> loop_category_id ? $this -> loop_category_id : $category_id;
            $this -> loop_category_id = false;
            $set_account_id = $this -> loop_account_id ? $this -> loop_account_id : $account_id;
            $this -> loop_account_id = false;
            
            //membership
            $restricted_account = false;
            $should_make_featured = false;

            if ($config['membership_module']) {
                $mp_account_info = $this->fetch('*', array('ID' => $set_account_id), null, 1, 'accounts', 'row');
                $restricted_account = true;

                // change plan_id and plan_type
                $plan_id = $mp_account_info['Plan_ID'];
                $plan_info = $mp_plan_info = $GLOBALS['rlMembershipPlan']->getPlan($plan_id);

                $plan_type = 'account';
                $is_exceeded = $this->isLimitExceeded($account_id, $mp_plan_info);

                //check does user selected some membership plan
                if ($user_mode) {
                    if (!$config['allow_listing_plans']) {
                        if (!$mp_account_info['Plan_ID']) {
                            continue;
                        }
                    } else {
                        if ($is_exceeded) {
                            $plan_id = $coming_plan;
                            $fetch = array('Featured', 'Image', 'Image_unlim', 'Price', 'Limit');
                            $plan_info = $this->fetch($fetch, array('ID' => $plan_id), null, 1, 'listing_plans', 'row');
                            $restricted_account = false;
                            $plan_type = 'listing';
                        }
                    }
                }

                if ($restricted_account) {
                    $package_sql = "SELECT * FROM `" . RL_DBPREFIX . "listing_packages` ";
                    $package_sql .= "WHERE `Account_ID` = {$mp_account_info['ID']} AND `Plan_ID` = {$plan_id}";
                    $package_exist = $this->getRow($package_sql);

                    if (!$package_exist) {
                        $this->addMembershipPlanUsing($mp_plan_info, $mp_account_info['ID']);
                    }
        
                    // skip importing if limit of the membership plan is exceeded
                    if (!$config['allow_listing_plans'] && $is_exceeded) {
                        continue;
                    }
                    if ($mp_plan_info['Advanced_mode'] && !$is_exceeded) {
                        $status = 'approval';
                    }
                }
            }

            $user_status = $config['listing_auto_approval'] ? $status : 'pending';

            // listing package integration
            if (!$config['membership_module']) {
                if ($plan_info['Type'] == 'package' && $user_mode) {
                    try {
                        $plan_info['Featured'] = false;
                        $listingPackageManager = new ListingPackages($account_id, $plan_id);

                        $listingPackageManager->checkUsageRowExisting();

                        if ($listingPackageManager->isLimitExceeded()) {
                            continue;
                        }

                        $reducedType = $listingPackageManager->reduceListingUsage($account_id, $plan_id);

                        if ($reducedType['featured']) {
                            $should_make_featured = true;
                            $featuredPlanID = $plan_id;
                        }

                    } catch (\Exception $e) {
                        $GLOBALS['rlDebug']->logger($e->getMessage());
                    }
                }

                if ($plan_info['Type'] == 'listing') {
                    $statusDependPackage = $plan_info['Price'] > 0 ? 'expired' : 'active';

                    $status = $statusDependPackage;
                    if ($user_mode) {
                        $user_status = $statusDependPackage;
                    }
                }
            }

            /* collect data by system fields */
            $import = array(
                'Category_ID' => $set_category_id,
                'Account_ID' => $set_account_id,
                'Plan_ID' => $plan_id,
                'Pay_date' => $paid ? 'NOW()' : '',
                'Date' => 'NOW()',
                'Status' => $user_mode ? $user_status : $status,
                'Import_file' => $_SESSION['iel_data']['file_name'],
            );
            
            if ($restricted_account && $mp_plan_info['Advanced_mode']) {
                $should_make_featured = $this->makeFeatured($account_id, $mp_plan_info['Featured_listings']);
                $featuredPlanID = $mp_plan_info['ID'];
            }

            if ($should_make_featured) {
                $import['Featured_date'] = "NOW()";
                $import['Featured_ID'] = $featuredPlanID;
                $import['Plan_ID'] = $featuredPlanID;
            }
            
            if ($this -> getRow("SHOW FIELDS FROM  `".RL_DBPREFIX."listings` WHERE `Field` = 'cl_direct'")) {
                $import['cl_direct'] = '1';
            }

            if ($plan_info['Featured']) {
                $import['Featured_ID'] = $plan_id;
                $import['Featured_date'] = $paid ? 'NOW()' : '';
            }

            if ($user_mode && !$config['membership_module']) {
                $this -> planData($available_rows[$index], $import, $plan_id, $plan_info);
            }
    
            $import = array_merge($import, $insert_fields);
    
            /* coordinates fetching  */
            if ($loc_request) {
                $loc_request = rtrim($loc_request, ', ');
                $this->time_limit = 30;
        
                $GLOBALS['reefless']->geocodeLocation($loc_request, $import);
        
                unset($loc_request);
            }

            if (!empty($row_data[$available_fields['Loc_latitude']]) && !empty($row_data[$available_fields['Loc_longitude']])) {
                $import['Loc_latitude'] = $row_data[$available_fields['Loc_latitude']];
                $import['Loc_longitude'] = $row_data[$available_fields['Loc_longitude']];
            }

            /* beforeImport hook */
            $plugin = 'export_import';
            $action = 'insert';
            if (method_exists($rlListings, 'beforeImport')) {
                $rlListings->beforeImport($import, $plugin, $action);
            } else {
                // if method not exists (ver. < 4.6.1) try to load hook directly
                $GLOBALS['rlHook']->load('beforeImport', $import, $plugin, $action);
            }
            /* beforeImport hook end */
            
            /* insert new listing */
            if ($restricted_account) {
                $import['Plan_type'] = $plan_type;
            }

            $rlActions -> insertOne($import, 'listings', $html_fields);
            $imported_id = method_exists($this, 'insertID') ? $this->insertID() : mysql_insert_id();
            
            /* after end hook */
            if (method_exists($rlListings, 'afterImport')) {
                $rlListings->afterImport($import, $imported_id, $plugin, $action);
            } else {
                // if method not exists (ver. < 4.6.1) try to load hook directly
                $GLOBALS['rlHook']->load('afterImport', $import, $imported_id, $plugin, $action);
            }
            /* after end hook end */
    
            /* update membership plans of the user */
            if ($restricted_account) {
                $this->updateMembershipUsing($account_info);
            }
            
            /* set reference ID for the listing */
            if ( $reference_plugin ) {
                $ref = $GLOBALS['rlRef'] -> generate($imported_id, $config['ref_tpl']);
                $ref_update_sql = "UPDATE `". RL_DBPREFIX ."listings` SET `ref_number` = '{$ref}' WHERE `ID` = '{$imported_id}'";
                $this -> query($ref_update_sql);
            }

            /* update categories count information */
            if ($status == 'active' && $paid) {
                $rlCategories -> listingsIncrease($set_category_id, $listing_type);
                if(method_exists($rlCategories, 'accountListingsIncrease')) {
                    $rlCategories -> accountListingsIncrease($import['Account_ID']);
                }
            }
            
            
            /* pictures handler */
            if (array_key_exists('Main_photo_url', $available_fields)) {
                if (Jobs::isBelongsToJobListingType($import['Category_ID'])) {
                    if (array_key_exists('Main_photo_url', $available_fields)) {
                        $mainPhotoUrl = $row_data[$available_fields['Main_photo_url']];

                        $jobManager = new Jobs();
                        $jobManager->import($imported_id, $set_account_id, $mainPhotoUrl);
                    }
                } else {
                    $this->uploadPictures(
                        'url',
                        $imported_id,
                        $row_data[$available_fields['Main_photo_url']],
                        $plan_info
                    );
                }
            }
            if (array_key_exists('Main_photo_zip', $available_fields)) {
                $this->uploadPictures('zip', $imported_id, $row_data[$available_fields['Main_photo_zip']], $plan_info);
            }
            if (array_key_exists('Youtube_video', $available_fields)) {
                $this->parseYouTube($imported_id, $row_data[$available_fields['Youtube_video']]);
            }
            
            /* escort handlers */
            if(Escort::isEscortInstallation()) {
                if (array_key_exists('escort_rates', $available_fields)) {
                    Escort::importEscortRates($imported_id, $row_data[$available_fields['escort_rates']]);
                }
    
                if (array_key_exists('escort_tours', $available_fields)) {
                    Escort::importEscortTours($imported_id, $row_data[$available_fields['escort_tours']]);
                }
            }
            
            
            if ($config['membership_module']) {
                $this->updatePlanUsing($account_info, $listing_mode);
            }
    
            unset($insert_fields, $row_data, $imported_id, $import, $update);
            unset($_SESSION['iel_data']['post']['rows'][$available_rows[$index]]);
            $plan_id = $coming_plan;
            $status = $coming_status;
        }
    
        if ($user_mode && !$config['membership_module']) {
            $this->updatePlans($account_id);
        }
        
        return true;
    }
    
    /**
    * update listing data related selected plan
    *
    * @param int $index - current row index
    * @param array $import - referent to import array to modify necessary data in
    * @param int $plan_id - default plan ID
    * @param array $plan_info - default plan info
    *
    **/
    function planData( $index = false, &$import, $plan_id = false, $plan_info = false )
    {
        global $config;
        
        $status = $import['Status'];
        $plan_id = $import['Plan_ID'];
        $pay_date = $import['Pay_date'];
        $featured_id = $import['Featured_ID'];
        $featured_date = $import['Featured_date'];
        
        if ( $_SESSION['iel_data']['post']['plan'][$index] || $_SESSION['iel_data']['user_plans'][$plan_id] ) {
            $user_plan_id = $_SESSION['iel_data']['post']['plan'][$index] ? $_SESSION['iel_data']['post']['plan'][$index] : $plan_id;
            $user_plan = $_SESSION['iel_data']['user_plans'][$user_plan_id];
            
            if ( $_SESSION['iel_data']['user_plans'][$user_plan_id]['Listings_remains'] > 0 ) {
                $plan_id = $user_plan['ID'];
                $pay_date = 'NOW()';
                
                $type = ucfirst($_SESSION['iel_data']['post']['type'][$index]);
                
                if ( ($user_plan['Featured'] && !$user_plan['Advanced_mode']) || ($user_plan['Advanced_mode'] && $type == 'Featured' && $user_plan[$type.'_remains'] > 0) ) {
                    $featured_id = $user_plan['ID'];
                    $featured_date = 'NOW()';
                }
                
                /* decrease counters */
                $_SESSION['iel_data']['user_plans'][$user_plan_id]['Listings_remains'] -= 1;
                
                if ( $user_plan['Advanced_mode'] && $_SESSION['iel_data']['user_plans'][$user_plan_id][$type.'_remains'] > 0 ) {
                    $_SESSION['iel_data']['user_plans'][$user_plan_id][$type.'_remains'] -= 1;
                }
            }
        }
        else {
            if ( !$plan_info['Price'] && !$plan_info['Limit'] ) {
                $pay_date = 'NOW()';
                
                if ( $plan_info['Featured'] ) {
                    $featured_id = $plan_id;
                    $featured_date = 'NOW()';
                }
            }
        }
        
        $import['Status'] = $config['listing_auto_approval'] ? $status : 'pending';
        $import['Plan_ID'] = $plan_id;
        $import['Pay_date'] = $pay_date;
        $import['Featured_ID'] = $featured_id;
        $import['Featured_date'] = $featured_date;
    }
    
    /**
    * update used plans options
    *
    * @param int $account_id - account ID
    *
    **/
    function updatePlans( $account_id = false )
    {
        global $rlActions;
        
        if ( !$account_id || empty($_SESSION['iel_data']['user_plans']) )
            return;
            
        foreach ($_SESSION['iel_data']['user_plans'] as $plan) {
            if ( $plan['Package_ID'] || $plan['Limit'] > 0 ) {
                /* update */
                if ( $plan['Package_ID'] ) {
                    $update = array(
                        'fields' => array(
                            'Listings_remains' => $plan['Listings_remains'],
                            'Standard_remains' => $plan['Standard_remains'],
                            'Featured_remains' => $plan['Featured_remains'],
                        ),
                        'where' => array(
                            'ID' => $plan['Package_ID']
                        )
                    );
                    
                    $rlActions -> updateOne($update, 'listing_packages');
                }
                /* insert */
                else {
                    $insert = array(
                        'Account_ID' => $account_id,
                        'Plan_ID' => $plan['ID'],
                        'Listings_remains' => $plan['Listings_remains'],
                        'Standard_remains' => $plan['Standard_remains'],
                        'Featured_remains' => $plan['Featured_remains'],
                        'Type' => $plan['Package'] ? $plan['Package'] : 'limited',
                        'Date' => 'NOW()',
                        'IP' => $this->getClientIpAddress()
                    );
                    
                    $rlActions -> insertOne($insert, 'listing_packages');
                    
                    $_SESSION['iel_data']['user_plans'][$plan['ID']]['Package_ID'] = method_exists($this, 'insertID') ? $this->insertID() : mysql_insert_id();
                }
            }
        }
    }
    
    /**
    * adapt category/subcategory value
    *
    * @param array $row - row data (all fields)
    * @param int $category_index - category field index
    * @param int $subcategory_index - subcategory field index (if exists)
    *
    **/
    function handleCategory( &$row, $category_index = false, $subcategory_index = false )
    {
        $value = $row[$category_index];
        
        if (is_numeric($value))    {
            if ($category_id = $this->getOne('ID', "`ID` = '{$value}' AND `Status` = 'active'", 'categories')) {
                $this->loop_category_id = $category_id;
            }
        } elseif ($value) {
            /* category + subcategory way */
            if ($row[$subcategory_index] && $subcategory_index) {
                $subcategory_value = $row[$subcategory_index];
                
                $compare_sign = strlen($value) > 5 ? '?' : '';
                $compare_sign_sub = strlen($subcategory_value) > 5 ? '?' : '';
                $sql = "SELECT `T1`.`ID` FROM `". RL_DBPREFIX ."categories` AS `T1` ";
                $sql .= "LEFT JOIN `". RL_DBPREFIX ."lang_keys` AS `T2` ON CONCAT('categories+name+', `T1`.`Key`) = `T2`.`Key` ";
                $sql .= "LEFT JOIN `". RL_DBPREFIX ."categories` AS `T3` ON `T3`.`ID` = `T1`.`Parent_ID` ";
                $sql .= "LEFT JOIN `". RL_DBPREFIX ."lang_keys` AS `T4` ON CONCAT('categories+name+', `T3`.`Key`) = `T4`.`Key` ";
                $sql .= "WHERE ";
                $sql .= "`T4`.`Value` RLIKE '^{$value}{$compare_sign}' AND `T4`.`Key` LIKE 'categories+name+%' AND ";
                $sql .= "`T2`.`Value` RLIKE '^{$subcategory_value}{$compare_sign_sub}' AND `T2`.`Key` LIKE 'categories+name+%' ";
                $sql .= "AND `T1`.`Status` <> 'trash' ";
                $sql .= "LIMIT 1";
                
                if ($category = $this->getRow($sql)) {
                    $this->loop_category_id = $category['ID'];
                    return;
                }
            }

            /* category way */
            $compare_sign = strlen($value) > 5 ? '?' : '';
            $sql = "SELECT `Key` FROM `". RL_DBPREFIX ."lang_keys` ";
            $sql .= "WHERE `Value` RLIKE '^{$value}{$compare_sign}' AND `Key` LIKE 'categories+name+%' AND `Status` <> 'trash' LIMIT 1";

            if ($phrase_key = $this->getRow($sql)) {
                $phrase_key_exp = array_reverse(explode('+', $phrase_key['Key']));

                if ($category_id = $this->getOne('ID', "`Key` = '{$phrase_key_exp[0]}' AND `Status` = 'active'", 'categories')) {
                    $this -> loop_category_id = $category_id;
                }
            }
        }
    }

    /**
     * Adapt Listing value
     *
     * @param string $value - field value
     * @param string $key   - field key
     * @param array  $info  - field information
     * @return bool|mixed
     */
    function adaptValue( $value, $key = '', &$info )
    {
        global $rlValid;
        
        if ( !$key )
            return $value;
        
        $value = $rlValid -> xSql(trim($value));

        switch ($key){
            case 'Account_ID':
                if ( is_numeric($value) )
                {
                    if ( $account_id = $this -> getOne('ID', "`ID` = '{$value}' AND `Status` = 'active'", 'accounts') )
                    {
                        $this -> loop_account_id = $account_id;
                    }
                }
                elseif ( $value )
                {
                    if ( $account_id = $this -> getOne('ID', "`Username` = '{$value}' AND `Status` = 'active'", 'accounts') )
                    {
                        $this -> loop_account_id = $account_id;
                    }
                }
                break;
                
            default:
                switch ($info[$key]['Type']){
                    case 'text':
                    case 'textarea':
                    case 'number':
                    case 'date':
                        
                        if ( $value != '' )
                        {
                            $out = $value;
                            
                        }
                        elseif ( $info[$key]['Default'] )
                        {
                            $out = $info[$key]['Default'];
                        }
                        break;
                        
                    case 'bool':
                        if ( is_numeric($value) )
                        {
                            $out = $value ? 1 : 0;
                        }
                        elseif ( $value == '' && $info[$key]['Default'] )
                        {
                            $out = $info[$key]['Default'];
                        }
                        else
                        {
                            $out = in_array(strtolower($value), array('no', 'n')) ? 0 : 1;
                        }
                        break;
                        
                    case 'phone':
                        $area_length = $info[$key]['Default'] ? $info[$key]['Default'] : 0;
                        $code_length = $info[$key]['Opt1'] ? $info[$key]['Opt1'] : 0;
                        $number_length = $info[$key]['Values'] ? $info[$key]['Values']  : 0;

                        preg_match('/(\+\s?[0-9]{0,'. $code_length .'})?\s*(\(?[0-9]{0,'. $area_length .'}\)?)?\s*([\s\-0-9]{0,'. $number_length .'})?\s*(\/.+)?/', $value, $matches);

                        if ($matches) {
                            /* code */
                            if ($matches[1]) {
                                $out = 'c:'. (int) preg_replace('/\D/', '', $matches[1]) .'|';
                            }
                            
                            /* area */
                            $out .= 'a:'. preg_replace('/\D/', '', $matches[2]) . '|';
                            
                            /* number */
                            $out .= 'n:'. preg_replace('/[\s\-]/', '', $matches[3]);
                            
                            /* extension */
                            if ($matches[4]) {
                                $out .= '|e:'. preg_replace('/\D/', '', $matches[4]);
                            }
                        }
                        
                        break;
                        
                    case 'mixed':
                        preg_match('/([^\s]+)?\s*([\/,%,\w\d\,\.\s]+)?\s*([^\s]+)?/', $value, $matches);

                        if ( $matches[1] )
                        {
                            $out = (float) str_replace(',', '', $matches[1]);
                            if ( $unit = $matches[2] )
                            {
                                $unit = trim($unit);
                                $compare_sign = strlen($unit) > 5 ? '?' : '';
                                
                                if ( $info[$key]['Condition'] )
                                {
                                    $sql = "SELECT `Key` FROM `". RL_DBPREFIX ."lang_keys` ";
                                    //$sql .= "WHERE `Value` RLIKE '^{$unit}{$compare_sign}' AND `Key` LIKE 'data_formats+name+{$info[$key]['Condition']}_%' AND `Status` <> 'trash' LIMIT 1";
                                    $sql .= "WHERE `Value` = '{$unit}' AND `Key` REGEXP 'data_formats\\\+name\\\+({$info[$key]['Condition']}\_)?' AND `Status` <> 'trash' LIMIT 1";//var_dump($sql);
                                    if ( $phrase_key = $this -> getRow($sql) )
                                    {
                                        $phrase_key_exp = str_replace('data_formats+name+', '', $phrase_key['Key']);
                                        //$phrase_key_exp = str_replace($info[$key]['Condition'] .'_', '', $phrase_key_exp);
                                        $out .= '|'. $phrase_key_exp;
                                    }
                                }
                                else
                                {
                                    $sql = "SELECT `Key` FROM `". RL_DBPREFIX ."lang_keys` ";
                                    //$sql .= "WHERE `Value` RLIKE '^{$unit}{$compare_sign}' AND `Key` REGEXP 'listing_fields\\+name\\+{$key}_[0-9]+' AND `Status` <> 'trash' LIMIT 1";
                                    $sql .= "WHERE `Value` = '{$unit}' AND `Key` REGEXP 'listing_fields\\\+name\\\+{$key}_[0-9]+' AND `Status` <> 'trash' LIMIT 1";
                                    //var_dump($sql);
                                    if ( $phrase_key = $this -> getRow($sql) )
                                    {
//                                        $phrase_key_exp = array_reverse(explode('_', $phrase_key['Key']));
//                                        $out .= '|'. $phrase_key_exp[0];
                                        $phrase_key_exp = str_replace('listing_fields+name+', '', $phrase_key['Key']);
                                        $out .= '|'. $phrase_key_exp;
                                    }
                                }
                            }
                        }
                        break;
                        
                    case 'price':
                        if ($price = $this->parsePrice($value)) {
                            $out = $price['price'];

                            if ($price['currency']) {
                                $sql = "
                                    SELECT `Key` FROM `{db_prefix}lang_keys`
                                    WHERE `Value` = '{$price['currency']}' AND `Key` LIKE 'data_formats+name+%' AND `Status` <> 'trash'
                                    LIMIT 1
                                ";
                                
                                if ($phrase_key = $this->getRow($sql)) {
                                    $phrase_key_exp = array_reverse(explode('+', $phrase_key['Key']));
                                    $out .= '|'. $phrase_key_exp[0];
                                }
                            }
                        }
                        break;
                        
                    case 'select':
                    case 'radio':
                    case 'checkbox':
                        if ( $value != '' )
                        {
                            if ( $info[$key]['Condition'] == 'years' )
                            {
                                $out = $value;
                            }
                            elseif ( $info[$key]['Condition'] )
                            {
                                if ( $value_exp = explode(',', $value) )
                                {
                                    foreach ($value_exp as $sub_value)
                                    {
                                        $sub_value = preg_replace('/([\"\\\'\{\}\(\)\[\]\*\.\-\^\$\+\?\\\|]+)/', '$1', trim($sub_value));
                                        $compare_sign = strlen($sub_value) > 5 ? '?' : '';
                                        
                                        $sql = "SELECT `Key` FROM `". RL_DBPREFIX ."lang_keys` ";
                                        //$sql .= "WHERE `Value` RLIKE '^{$sub_value}{$compare_sign}' AND `Key` RLIKE 'data\_formats\\\+name\\\+(". str_replace('_', '_\\', $info[$key]['Condition']) ."\_)?' AND `Status` <> 'trash' LIMIT 1";
                                        $sql .= "WHERE `Value` = '{$sub_value}' AND `Key` RLIKE 'data_formats\\\+name\\\+({$info[$key]['Condition']}_)?' AND `Status` <> 'trash' LIMIT 1";
                                        //var_dump($sql);
                                        if ( $phrase_key = $this -> getRow($sql) )
                                        {
                                            $phrase_key_exp = str_replace('data_formats+name+', '', $phrase_key['Key']);
                                            //$phrase_key_exp = str_replace($info[$key]['Condition'] .'_', '', $phrase_key_exp);
                                            $out .= $phrase_key_exp .',';
                                        }
                                    }
                                }
                            }
                            else
                            {
                                if ( $value_exp = explode(',', $value) )
                                {
                                    foreach ($value_exp as $sub_value)
                                    {
                                        $sub_value = preg_replace('/([\"\\\'\{\}\(\)\[\]\*\.\-\^\$\+\?\\\|]+)/', '$1', trim($sub_value));
                                        $compare_sign = strlen($sub_value) > 5 ? '?' : '';
                                        
                                        $sql = "SELECT `Key` FROM `". RL_DBPREFIX ."lang_keys` ";
                                        $sql .= "WHERE `Value` RLIKE '^{$sub_value}{$compare_sign}' AND `Key` REGEXP 'listing_fields\\\+name\\\+{$key}_[0-9]+' AND `Status` <> 'trash' LIMIT 1";
                                        //var_dump($sql);
                                        if ( $phrase_key = $this -> getRow($sql) )
                                        {
                                            $phrase_key_exp = array_reverse(explode('_', $phrase_key['Key']));
                                            $out .= $phrase_key_exp[0] .',';
//                                            $phrase_key_exp = str_replace('listing_fields+name+', '', $phrase_key['Key']);
//                                            $out .= $phrase_key_exp .',';
                                        }
                                    }
                                }
                            }
                        }
                        elseif ( $info[$key]['Default'] )
                        {
                            $out = $info[$key]['Default'];
                        }
                        
                        $out = rtrim($out, ',');
                        
                        break;
                }
            
                break;
        }
        
        return $out ? $out : false;
    }
    
    /**
     * Get keys of fields from Multifield plugin
     *
     * @param  string $value       - Value of field
     * @param  string $field_key   - Key of field
     * @param  array  $row_data    - Data of current listing
     * @param  array  $fields      - Data of import fields
     * @param  array  $fields_info - System info of fields
     * @return string              - Matching field key
     */
    function getMultifieldValue($value = false, $field_key = false, $row_data = [], $fields = [], $fields_info = [])
    {
        global $rlValid, $rlDb;

        $value = $rlValid->xSql(trim($value));

        if (!$value) {
            return false;
        }

        preg_match('/_level([0-9]+)/', $field_key, $matches);
        $level = (int) $matches[1];
        $condition = $fields_info[$field_key]['Condition'];

        $related = [];

        // Build related fields array
        foreach ($fields_info as $field) {
            if ($field['Condition'] && $field['Condition'] == $condition) {
                if (strpos($field['Key'], '_level')) {
                    preg_match('/^([^\)]+)_level([0-9]+)/', $field['Key'], $matches);

                    if (strpos($field['Key'], $matches[1]) === 0 && intval($matches[2]) <= $level) {
                        $index = array_search($field['Key'], $fields);
                        $related[$field['Key']] = $rlValid->xSql(trim($row_data[$index]));
                    }
                } elseif (strpos($field_key, $field['Key']) === 0) {
                    $index = array_search($field['Key'], $fields);
                    $related[$field['Key']] = $rlValid->xSql(trim($row_data[$index]));
                }
            }
        }

        ksort($related);

        if (Helpers::isNewMultifield()) {
            return $this->getMultifieldValueNew($related, $condition);
        } else {
            return $this->getMultifieldValueOld($related, $condition);
        }
    }

    /**
     * Get data key from the new Multifield plugin structure (from 2.2.0 version)
     *
     * @since 3.7.1
     *
     * @param  array $related   - Related/parent fields data, field key => field value
     * @param  array $condition - Field condition
     * @return string           - Matching data key
     */
    public function getMultifieldValueNew($related, $condition)
    {
        global $rlDb;

        $languages = $GLOBALS['languages'] ?: $GLOBALS['rlLang']->getLanguagesList('all');
        $found = null;

        foreach ($related as $field_key => $field_value) {
            $check_key = $found ?: $condition;

            foreach ($languages as $language) {
                if ($found = $rlDb->getOne('Key', "`Key` LIKE '{$check_key}_%' AND `Value` LIKE '{$field_value}%'", 'multi_formats_lang_' . $language['Code'])) {
                    break;
                }
            }

            if (!$found) {
                break;
            }
        }

        return $found;
    }

    /**
     * Get data key from the old Multifield plugin structure (before 2.2.0 version)
     *
     * @since 3.7.1
     *
     * @param  array $related   - Related/parent fields data, field key => field value
     * @param  array $condition - Field condition
     * @return string           - Matching data key
     */
    public function getMultifieldValueOld($related, $condition)
    {
        global $rlDb;

        $found = null;

        foreach ($related as $field_key => $field_value) {
            $check_key = $found ?: 'data_formats+name+' . $condition;
            if (!$found = $rlDb->getOne('Key', "`Key` LIKE '{$check_key}_%' AND `Value` LIKE '{$field_value}%' AND `Status` = 'active'", 'lang_keys')) {
                break;
            }
        }

        return $found ? str_replace('data_formats+name+', '', $found) : null;
    }
    
    /**
     * upload listing pictures
     *
     * @param string $mode - pictures upload mode, url or zip
     * @param int    $id - listing ID
     * @param string $value - value from import form
     * @param array  $plan_info - plain details
     * @return bool
     */
    function uploadPictures($mode = 'url', $id = 0, &$value, $plan_info = array())
    {
        global $config, $rlActions, $plan_info, $reefless;
        
        if ( !$id || !$value || (!$plan_info['Image'] && !$plan_info['Image_unlim']) )
            return false;

        $this -> loadClass('Crop');
        $this -> loadClass('Resize');

        $ext_regexp = "/(jpg|jpeg|jpe|gif|png|bmp)$/i";

        $dir = RL_FILES . date('m-Y') . RL_DS .'ad'. $id . RL_DS;
        $dir_name = date('m-Y') .'/ad'. $id .'/';

        $url = RL_FILES_URL . $dir_name;
        $this -> rlMkdir($dir);

        switch ($mode){
            case 'url':
                $picture_urls = explode(',', $value);
                $iteration = 1;

                foreach ($picture_urls as $url)
                {

                    $url = trim($url);
                    $url = (bool) preg_match('/^http/', $url) ? $url : 'http://' . $url;
                    $ext = pathinfo(strtok($url, '?'), PATHINFO_EXTENSION);

                    if (!$ext) {
                        $imageInfoByUrl = getimagesize($url);
                        if (isset($imageInfoByUrl[2])) {
                            $ext = image_type_to_extension($imageInfoByUrl[2], false);
                        }
                    }

                    if (!(bool) preg_match($ext_regexp, strtolower($ext))) {
                        continue;
                    }

                    $orig_name = 'orig_' . time() . mt_rand() . '.' . $ext;
                    $orig_path = $dir . $orig_name;
    
                    $thumbnail_name = 'thumb_' . time() . mt_rand() . '.' . $ext;
                    $thumbnail_path = $dir . $thumbnail_name;
    
                    $large_name = 'large_' . time() . mt_rand() . '.' . $ext;
                    $large_path = $dir . $large_name;
    
                    if (!$reefless->copyRemoteFile($url, $orig_path)) {
                        var_dump(0);
                        exit;
                        continue;
                    }

                    //chmod($orig_path, 0644);
    
                    if (!is_readable($orig_path)) {
                        var_dump(1);
                        exit;
                        continue;
                    }

                    /* crop/resize to thumbnail */
                    $willCropImage = $orig_path;
                    if ($config['img_crop_module']) {
                        $GLOBALS['rlCrop']->loadImage($orig_path);
                        $GLOBALS['rlCrop']->cropBySize(
                            $config['pg_upload_thumbnail_width'],
                            $config['pg_upload_thumbnail_height'],
                            ccCENTER
                        );

                        $willCropImage = $thumbnail_path;
                        $GLOBALS['rlCrop']->saveImage($thumbnail_path, $config['img_quality']);
                        $GLOBALS['rlCrop']->flushImages();
                    }

                    $GLOBALS['rlResize']->resize(
                        $willCropImage,
                        $thumbnail_path,
                        'C',
                        array($config['pg_upload_thumbnail_width'], $config['pg_upload_thumbnail_height']),
                        true,
                        false
                    );

                    /* crop/resize to large image */
                    if ($config['img_crop_module']) {
                        $GLOBALS['rlCrop']->loadImage($orig_path);

                        $GLOBALS['rlCrop']->cropBySize(
                            $config['pg_upload_large_width'],
                            $config['pg_upload_large_height'],
                            ccCENTER
                        );

                        $GLOBALS['rlCrop']->saveImage($large_path, $config['img_quality']);
                        $GLOBALS['rlCrop']->flushImages();
                    }

                    $GLOBALS['rlResize']->resize(
                        $config['img_crop_module'] ? $large_path : $orig_path, $large_path,
                        'C',
                        array($config['pg_upload_large_width'], $config['pg_upload_large_height']),
                        false,
                        $config['watermark_using']
                    );

                    /* collect picture entry */
                    $pictures[] = array(
                        'Listing_ID' => $id,
                        'Position' => $iteration,
                        'Photo' => $dir_name . $large_name,
                        'Thumbnail' => $dir_name . $thumbnail_name,
                        'Original' => $orig_name ? $dir_name . $orig_name : '',
                    );
    
                    if ($iteration == 1) {
                        $main_photo = $dir_name . $thumbnail_name;
                    }
                    
                    // break foreach if we exided limit
                    if (!$plan_info['Image_unlim'] && $plan_info['Image'] == $iteration) {
                        break;
                    }

                    $iteration++;
                }
            
                break;
                
            case 'zip':
                $picture_names = explode(',', $value);
    
                $iteration = 1;
                foreach ($picture_names as $name) {
                    $name = trim($name);
    
                    if (!(bool) preg_match($ext_regexp, strtolower($name))) {
                        continue;
                    }
    
                    $cdir = scandir($_SESSION['iel_data']['archive_dir']);
    
                    $checker = array_filter($cdir, function ($e) {
                        if ($e != '.' && $e != '..') {
                            return true;
                        }
                    });
                    
                    if(
                        count($checker) == 1
                        && is_file($_SESSION['iel_data']['archive_dir'] . RL_DS . $checker[2] . RL_DS . $name)
                        ) {
                        $picture_dir = $_SESSION['iel_data']['archive_dir'] . RL_DS . $checker[2] . RL_DS . $name;
                    } else {
                        $picture_dir = $_SESSION['iel_data']['archive_dir'] . RL_DS . $name;
                    }

                    if (!file_exists($picture_dir)) {
                        continue;
                    }
    
                    $ext = pathinfo($picture_dir, PATHINFO_EXTENSION);
                    $orig_name = 'orig_' . time() . mt_rand() . '.' . $ext;
                    $orig_path = $dir . $orig_name;
    
                    $thumbnail_name = 'thumb_' . time() . mt_rand() . '.' . $ext;
                    $thumbnail_path = $dir . $thumbnail_name;
    
                    $large_name = 'large_' . time() . mt_rand() . '.' . $ext;
                    $large_path = $dir . $large_name;
    
                    if (!copy($picture_dir, $orig_path)) {
                        // alternative by stream to stream copy
                        $source = file_get_contents($picture_dir);
        
                        $handle = fopen($orig_path, "w");
                        fwrite($handle, $source);
                        fclose($handle);
                    }
    
                    chmod($orig_path, 0644);
    
                    if (!is_readable($orig_path)) {
                        continue;
                    }
                    
                    /* crop/resize to thumbnail */
                    $GLOBALS['rlCrop']->loadImage($orig_path);
                    $GLOBALS['rlCrop']->cropBySize(
                        $config['pg_upload_thumbnail_width'],
                        $config['pg_upload_thumbnail_height'],
                        ccCENTER
                    );
                    $GLOBALS['rlCrop']->saveImage($thumbnail_path, $config['img_quality']);
                    $GLOBALS['rlCrop']->flushImages();
    
                    $GLOBALS['rlResize']->resize(
                        $thumbnail_path,
                        $thumbnail_path,
                        'C',
                        array($config['pg_upload_thumbnail_width'], $config['pg_upload_thumbnail_height']), true,
                        false
                    );
                    
                    /* crop/resize to large image */
                    if ($config['img_crop_module']) {
                        $GLOBALS['rlCrop']->loadImage($orig_path);
                        $GLOBALS['rlCrop']->cropBySize(
                            $config['pg_upload_large_width'],
                            $config['pg_upload_large_height'],
                            ccCENTER
                        );
                        $GLOBALS['rlCrop']->saveImage($large_path, $config['img_quality']);
                        $GLOBALS['rlCrop']->flushImages();
                    }
    
                    $GLOBALS['rlResize']->resize(
                        $config['img_crop_module'] ? $large_path : $orig_path, $large_path,
                        'C',
                        array($config['pg_upload_large_width'], $config['pg_upload_large_height']),
                        false,
                        $config['watermark_using']
                    );
                    
                    /* collect picture entry */
                    $pictures[] = array(
                        'Listing_ID' => $id,
                        'Position' => $iteration,
                        'Photo' => $dir_name . $large_name,
                        'Thumbnail' => $dir_name . $thumbnail_name,
                        'Original' => $orig_name ? $dir_name . $orig_name : '',
                    );
    
                    if ($iteration == 1) {
                        $main_photo = $dir_name . $thumbnail_name;
                    }

                    // break foreach if we exided limit
                    if (!$plan_info['Image_unlim'] && $plan_info['Image'] == $iteration) {
                        break;
                    }
                    
                    $iteration++;
                }
                
                break;
        }
    
        $photos_count = !$plan_info['Image_unlim'] && count($pictures) > $plan_info['Image']
            ? $plan_info['Image']
            : count($pictures);
    
        /* insert listing pictures data */
        $rlActions->insert($pictures, 'listing_photos');
        
        /* update listing picture data */
        if ($photos_count && version_compare($config['rl_version'], '4.1.0') >= 0) {
            $update = array(
                'fields' => array(
                    'Main_photo' => $main_photo,
                    'Photos_count' => $photos_count,
                ),
                'where' => array(
                    'ID' => $id,
                ),
            );
    
            $rlActions->updateOne($update, 'listings');
        }
    }

    /**
    * parse youtube video
    *
    * @param int $id - listing ID
    * @param string $value - value from import form
    *
    **/
    function parseYouTube( $id = false, &$value ) {
        global $rlActions;
        
        $video_items = explode(',', $value);
        $iteration = 1;
        
        foreach ($video_items as $item) {
            /* parse video key from url */
            if ( 0 === strpos($item, 'http') )
            {
                /* parse from short link */
                if ( false !== strpos($item, 'youtu.be') )
                {
                    $matches[1] = array_pop(explode('/', $item));
                }
                else
                {
                    preg_match('/v=([\w-_]*)/', $item, $matches);
                }
            }
            /* parse video key from tags */
            else
            {
                preg_match('/(.{5,15})/', $item, $matches);
            }
            
            if ( $matches[1] ) {
                $insert = array(
                    'Listing_ID' => $id,
                    'Preview' => $matches[1],
                    'Position' => $possition,
                    'Type' => 'youtube'
                );
                
                $rlActions -> insertOne($insert, 'listing_video');
                $iteration++;
            }
        }
    }
    
    /**
    * get available plans for the given user + free plans
    *
    * @param array $account_id - account info
    *
    * @return array - plans
    *
    **/
    function getUserPlans( $account_info = false ) {
        if ( !$account_info )
            return false;
            
        global $rlLang;
        
        $sql = "SELECT `T1`.`ID`, `T1`.`Key`, `T1`.`Type`, `T1`.`Featured`, `T1`.`Advanced_mode`, `T1`.`Limit`, `T1`.`Standard_listings`, ";
        $sql .= "`T1`.`Featured_listings`, `T1`.`Listing_number`, ";
        $sql .= "`T2`.`Type` AS `Package`, `T2`.`Listings_remains`, `T2`.`Standard_remains`, `T2`.`Featured_remains`, `T2`.`ID` AS `Package_ID` ";
        $sql .= "FROM `". RL_DBPREFIX ."listing_plans` AS `T1` ";
        $sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_packages` AS `T2` ON `T1`.`ID` = `T2`.`Plan_ID` AND `T2`.`Account_ID` = {$account_info['ID']} ";
        $sql .= "WHERE `T1`.`Status` = 'active' AND ";
        $sql .= "(FIND_IN_SET('{$account_info['Type']}', `T1`.`Allow_for`) > 0 OR `T1`.`Allow_for` = '') AND";
        $sql .= "(";
        $sql .= " (`T2`.`Type` = 'package' AND ";
        $sql .= "  (`T1`.`Listing_number` = 0 OR ";
        $sql .= "   ( ";
        $sql .="      `T1`.`Listing_number` > 0 AND `T2`.`Listings_remains` > 0 AND `T1`.`Plan_period` > 0 AND ";
        $sql .= "     DATE_ADD(`T2`.`Date`, INTERVAL `T1`.`Plan_period` DAY) <= NOW() ";
        $sql .= "   )";
        $sql .= "  ) ";
        $sql .= " ) OR ";
        $sql .= " ( ";
        $sql .= "  (`T1`.`Type` = 'listing' AND `T1`.`Price` = 0) OR ";
        $sql .= "  (`T2`.`Type` = 'limited' AND `T2`.`Listings_remains` > 0) ";
        $sql .= " ) ";
        $sql .= ") ";
        $sql .= "GROUP BY `T1`.`ID` ";
        $sql .= "ORDER BY `T1`.`Position` ";
        
        $plans_tmp = $rlLang -> replaceLangKeys($this -> getAll($sql), 'listing_plans', array('name'));
        
        foreach ( $plans_tmp as $plan ) {
            if ( $plan['Limit'] > 0 && !$plan['Package'] ) {
                $plan['Listings_remains'] = $plan['Limit'];
            }
            
            $plans[$plan['ID']] = $plan;
        }
        unset($plans_tmp);
        
        return $plans;
    }
    
    /**
    * update plan using details
    *
    * @param mixed $data - account ID or account details
    * @param string $listing_type - listing type option (standard or featured)
    * @return boolean
    */
    public function updatePlanUsing($data, $listing_type = 'standard')
    {
        if (!$data) {
            return false;
        }

        if (!is_array($data)) {
            $account_info = $this->fetch('*', array('ID' => (int)$data), null, null, 'accounts', 'row');
        } else {
            $account_info = $data;
        }

        $this->loadClass('Account');
        $this->loadClass('MembershipPlan');

        $result = false;
        $plan_id = $account_info['Plan_ID'];
        $account_info['plan'] = $GLOBALS['rlMembershipPlan']->getPlan($plan_id, true, $account_info);

        $sql = "SELECT * FROM `".RL_DBPREFIX."listing_packages` WHERE `Plan_ID` = '{$plan_id}' AND `Type` = 'account' AND `Account_ID` = '{$account_info['ID']}' LIMIT 1";
        $plan_using = $this->getRow($sql);
        
        if ($plan_using['ID']) {
            if ($account_info['plan']['Listing_number'] == 0 || $plan_using['Listings_remains'] > 0)  {
                $plan_using_update = array(
                    'fields' => array(
                        'Account_ID' => $account_info['ID'],
                        'Listings_remains' => $plan_using['Listings_remains'] > 0 ? $plan_using['Listings_remains'] - 1 : 0,
                        'Date' => 'NOW()',
                        'IP' => $this->getClientIpAddress()
                    ),
                    'where' => array(
                        'ID' => $plan_using['ID']
                    )
                );

                if ($account_info['plan']['Advanced_mode']) {
                    if ($listing_type == 'standard') {
                        $plan_using_update['fields']['Standard_remains'] = $plan_using['Standard_remains'] > 0 ? ($plan_using['Standard_remains'] - 1) : 0;
                    }
                    if ($listing_type == 'featured') {
                        $plan_using_update['fields']['Featured_remains'] = $plan_using['Featured_remains'] > 0 ?  ($plan_using['Featured_remains'] - 1) : 0;
                    }
                }
                
                $result = $GLOBALS['rlActions']->updateOne($plan_using_update, 'listing_packages');
            } else {
                $result = false;
            }
        } else {
            $plan_using_insert = array(
                'Account_ID' => (int)$account_info['ID'],
                'Plan_ID' => (int)$account_info['Plan_ID'],
                'Listings_remains' => $account_info['plan']['Listing_number'] > 0 ? $account_info['plan']['Listing_number'] - 1 : 0,
                'Standard_remains' => (int)$account_info['plan']['Standard_listings'],
                'Featured_remains' => (int)$account_info['plan']['Featured_listings'],
                'Type' => 'account',
                'Date' => 'NOW()',
                'IP' => $this->getClientIpAddress()
            );
    
            if ($account_info['plan']['Advanced_mode'])    {
                if ($listing_type == 'standard') {
                    $plan_using_insert['Standard_remains'] = $plan_using_insert['Standard_remains'] > 0 ? ($plan_using_insert['Standard_remains'] - 1) : 0;
                }
                if ($listing_type == 'featured') {
                    $plan_using_insert['Featured_remains'] = $plan_using_insert['Featured_remains'] > 0 ? ($plan_using_insert['Featured_remains'] - 1) : 0;
                }
            }
            
            $result = $GLOBALS['rlActions']->insertOne($plan_using_insert, 'listing_packages');
        }
        return $result;
    }

    /**
     * @hook  ajaxRequest
     * @since 3.5.0
     * @throws \Exception
     */
    function hookAjaxRequest(&$out, $request_mode, $request_item, $request_lang)
    {
        global $rlSmarty, $rlValid, $config, $lang;

        if (!$this->isValidRequest($request_mode)) {
            return false;
        }

        $this->loadClass('Actions');

        switch ($request_mode) {
            case 'eil_fetchOptions':
                $ts_path = RL_ROOT . 'templates' . RL_DS . $config['template'] . RL_DS . 'settings.tpl.php';
                if (is_readable($ts_path)) {
                    require_once($ts_path);
                }
                $GLOBALS['tpl_settings'] = $tpl_settings;
                $rlSmarty->register_function('rlHook', array('rlHook', 'load'));
                $element = $rlValid->xSql($_REQUEST['element']);
                $key = $rlValid->xSql($_REQUEST['key']);
                $out = $this->ajaxFetchOptions($key, $element, 1);
                break;
            case 'eil_ajaxGetPaginationInfo':
                $type = $rlValid->xSql($_REQUEST['type']);
                $out = $this->getPaginationData($type);
                break;
            case 'eil_ajaxImportExportPagination':
                $page = $rlValid->xSql($_REQUEST['page']);
                $type = $rlValid->xSql($_REQUEST['type']);
                $out = $this->ajaxPagination($page, $type);
                break;
            case 'eil_checkListingPackage':
                $userID = (int) $_SESSION['account']['ID'];
                $packageID = (int) $_REQUEST['package_id'];
                unset($_SESSION['eil_import_grid_message']);

                try {
                    $GLOBALS['reefless']->loadClass('Plan');
                    $planInfo = $GLOBALS['rlPlan']->getPlan($packageID);
                    $isFreePlan = !$planInfo['Price'];
                    $eiListingPackages = new ListingPackages($userID, $packageID);
                    $messageToShow = !$isFreePlan ? $lang['eil_you_didnt_bought_listing_package'] : '';
                    $isBought = false;

                    $out = array(
                        'status' => 'OK',
                        'type' => $planInfo['Type'],
                        'plan_id' => $packageID,
                        'is_bought' => $isBought,
                        'can_import' => $planInfo['Type'] == 'listing',
                        'is_free' => $isFreePlan,
                        'message' => $messageToShow,
                    );

                    $packageUsingInfo = $planInfo;
                    $listingRemains = $planInfo['Listing_number'];

                    if ($eiListingPackages->isUsageRowExistInDb()) {
                        $messageToShow = $lang['eil_cant_import_more_listings_to_package'];
                        $packageUsingInfo = $eiListingPackages->getPackageUsageInfo();
                        $listingRemains = (int) $packageUsingInfo['Listings_remains'];
                        $isBought = true;
                    }

                    if ($listingRemains && $planInfo['Type'] == 'package') {
                        $messageToShow = str_replace(
                            '{number}',
                            sprintf('<b>%s</b>', $listingRemains),
                            $lang['eil_you_can_import_only_for_package']
                        );
                    }

                    if ($planInfo['Type'] == 'listing' && $isFreePlan) {
                        $messageToShow = '';
                    }

                    $out['is_bought'] = $isBought;
                    $out['can_import'] = $listingRemains ?: false;
                    $out['message'] = $messageToShow;

                    if ($out['message']) {
                        $_SESSION['eil_import_grid_message'] = $out['message'];
                    }
                } catch (Exception $e) {
                    $out['status'] = 'ERROR';
                }
                break;
        }
    }

    /**
    * @hook apAjaxRequest
    * @since 3.5.0
    */
    function hookApAjaxRequest()
    {
        global $item, $out, $rlValid, $rlLang;

        if (!$this->isValidRequest($item)) {
            return false;
        }

        /* load smarty library */
        require_once( RL_LIBS . 'smarty' . RL_DS . 'Smarty.class.php' );
        $this -> loadClass( 'Smarty' );

        switch ($item) {
            case 'eil_fetchOptions':
                $GLOBALS['rlSmarty'] -> register_function('rlHook', array( 'rlHook', 'load' ));
                $element = $rlValid->xSql($_REQUEST['element']);
                $key = $rlValid->xSql($_REQUEST['key']);
                $out = $this->ajaxFetchOptions($key, $element);
            break;
            case 'eil_ajaxImportExportPagination':
                $this->loadClass('Actions');
                $page = $rlValid->xSql($_REQUEST['page']);
                $type = $rlValid->xSql($_REQUEST['type']);
                
                $out = $this->ajaxPagination($page, $type);
                break;
            case 'eil_ajaxGetPaginationInfo':
                $this->loadClass('Actions');
                $type = $rlValid->xSql($_REQUEST['type']);
                $out = $this->getPaginationData($type);
                break;
        }
    }
    
    /**
     * @hook apTplAccountTypesForm
     * @since 3.5.0
     */
    public function hookApTplAccountTypesForm()
    {
        $script = <<<HTML
        <script>
            $(document).ready(function() {
                document.querySelector(".option_padding input[value=export_import]")
                    .closest('label')
                    .lastChild
                    .nodeValue = ' {$GLOBALS['lang']['eil_option_name']}.';
            });
        </script>
HTML;
        echo $script;
    }
    /**
    * recount listings number for each membership plan
    *
    */
    public function ajaxUpdateMembershipPlans($start = 0, $account_id  = false,  $direct = false)
    {
        global $lang, $rlCache, $rlHook, $config, $rlActions;
        
        $start = (int)$start;
        $limit = 100;
    
        $sql = "SELECT `T1`.`ID`, `T1`.`Featured`, `T1`.`Pay_date`, ";
        $sql .= "`T2`.`Advanced_mode`, `T2`.`Listing_number`, `T2`.`Standard_listings`, `T2`.`Featured_listings`, `T2`.`Plan_period`, ";
        $sql .= "`T3`.`Listings_remains`, `T3`.`Standard_remains`, `T3`.`Featured_remains`, `T3`.`ID` AS `lpID`, ";
        $sql .= "(SELECT COUNT(`TL`.`ID`) FROM `". RL_DBPREFIX ."listings` AS `TL`
                WHERE `TL`.`Account_ID` = `T1`.`ID` AND `TL`.`Status` <> 'pending' AND `TL`.`Status` <> 'trash' AND `TL`.`Plan_type` = 'account' LIMIT 1) AS `ltotal`, ";
        $sql .= "(SELECT COUNT(`TLS`.`ID`) FROM `". RL_DBPREFIX ."listings` AS `TLS`
                WHERE `TLS`.`Account_ID` = `T1`.`ID` AND `TLS`.`Status` <> 'pending' AND `TLS`.`Status` <> 'trash' AND `TLS`.`Plan_type` = 'account' 
                AND (`TLS`.`Featured_ID` <= 0 OR `TLS`.`Featured_ID` = '') AND `TLS`.`Featured_date` IS NULL LIMIT 1) AS `standard_total`, ";
        $sql .= "(SELECT COUNT(`TLF`.`ID`) FROM `". RL_DBPREFIX ."listings` AS `TLF`
                WHERE `TLF`.`Plan_ID` = `T1`.`Plan_ID` AND `TLF`.`Status` <> 'pending' AND `TLF`.`Status` <> 'trash' AND `TLF`.`Plan_type` = 'account' 
                AND `TLF`.`Featured_ID` > 0 AND `TLF`.`Featured_date` IS NOT NULL LIMIT 1) AS `featured_total` ";
        $sql .= "FROM `". RL_DBPREFIX ."accounts` AS `T1` ";
        $sql .= "LEFT JOIN `". RL_DBPREFIX ."membership_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
        $sql .= "LEFT JOIN `". RL_DBPREFIX ."listing_packages` AS `T3` ON `T1`.`Plan_ID` = `T3`.`Plan_ID` AND `T3`.`Account_ID` = `T1`.`ID` AND `T3`.`Type` = 'account' ";
        $sql .= "WHERE `T1`.`Status` <> 'pending' AND `T1`.`Status` <> 'trash' ";
        $sql .= $account_id ? "AND `T1`.`ID` = {$account_id} " : '';
        $sql .= "GROUP BY `T1`.`ID` ";
        $sql .= !$direct ? "LIMIT {$start},{$limit}"  : '';
        $accounts = $this->getAll($sql);
    
        if ($accounts) {
            foreach ($accounts as $account) {
                $listings_by_plan = $account['Listing_number'];
                $used_listings = $account['ltotal'];
                if(!$account['Advanced_mode']) {
                    if($used_listings > $listings_by_plan) {
                        $update['fields']['Listings_remains'] = 0;
                        
                        $sql  = "UPDATE `". RL_DBPREFIX."listings` SET `Status` = 'approval' ";
                        $sql .= "WHERE `Account_ID` = {$account['ID']}";
                        $this->query($sql);
                        
                        //activate listings depending on the membership plan restriction
                        $sql  = "UPDATE `". RL_DBPREFIX."listings` SET `Status` = 'active' ";
                        $sql .= "WHERE `Account_ID` = {$account['ID']} LIMIT {$listings_by_plan}";
                        $this->query($sql);
                    } else {
                        $update['fields']['Listings_remains'] = $listings_by_plan - $used_listings;
                    }
                } else {
                
                }
            
                if ($update) {
                    $update['where'] = array('ID' => $account['lpID']);
                    $rlActions->updateOne($update, 'listing_packages');
                }
            }
        }
    
        if(!$direct) {
            if (count($accounts) == $limit) {
                $start += $limit;
                $out['status'] = 'updating';
                $out['start'] = $start;
            } else {
                $out['status'] = 'ok';
            }
        }
    
        return $out;
    }
    
    
    
    /**
     * Prepare data for import preview grid and pagination
     * @param array $data - importing data
     * @param bool  $mode -
     */
    public function prepareImportPreviewGrid($data, $user_mode = false)
    {
        $mode = $user_mode ? 'pg' : 'page';
        $page = $_GET[$mode] ? $_GET[$mode] : 1 ;
        $limit = $GLOBALS['config']['listings_per_page'];
        $total_listing = count($data);
        $total_pages = ceil($total_listing / $limit);

        // data for mapping grid on the Export/Import page. Including Pagination
        $grid['start'] = ($page - 1 ) * $limit;
        $grid['end'] = $grid['start'] + $limit;
        // first column is not import data
        $grid['total'] = $total_listing - 1;
        $grid['current'] = $page;
        $grid['limit'] = $limit;
        $grid['total_pages'] = $total_pages != 1 ? $total_pages : null ;

        if($user_mode) {
            //TODO: Check on non mod_rewrite domains
            $grid['paging_url'] = $GLOBALS['pages']['xls_export_import'] . '/import-table';
        }
        $GLOBALS['rlSmarty'] -> assign_by_ref('grid', $grid);
    }
    
    public function prepareExportPreviewGrid($data, $user_mode = false)
    {
        $pagination = $this->getPaginationData('export');
        $listings = array_slice($data, 0, $pagination['limit']);
        $GLOBALS['rlSmarty']->assign('export_listings', $listings);
        if (count($data) > $pagination['limit']) {
            $GLOBALS['rlSmarty']->assign('grid', $pagination);
        }
    }
    
    /**
     * Ajax pagination handler
     *
     * @param  string $page - Current page
     * @return array  $out  - Current pagination data
     */
    public function ajaxPagination($page, $type)
    {
        $data = ($type == 'export') ? $_SESSION['export_data'] : $_SESSION['import_data'];
        $pagination_data = $this->getPaginationData();
        $start = ($page - 1) * $pagination_data['limit'];
        $listings = array_slice($data, $start, $pagination_data['limit']);
        $tpl_var = ($type == 'export') ? 'export_listings' : 'import_data';
        $GLOBALS['rlSmarty']->assign($tpl_var, $listings);
    
        if ($type == 'export') {
            $GLOBALS['rlSmarty']->assign('fields', $_SESSION['fields']);
        }
    
        $out['status'] = 'OK';
        $file = ($type == 'export')
            ? RL_PLUGINS . 'export_import/admin/export_grid.tpl'
            : RL_PLUGINS . 'export_import/admin/grid.tpl';
        $out['html'] = $GLOBALS['rlSmarty']->fetch($file, null, null, false);
    
        return $out;
    }
    
    /**
     * @param array $data
     * @return mixed
     */
    public function getPaginationData($type = 'import')
    {
        $data = ($type == 'import') ? $_SESSION['import_data'] : $_SESSION['export_data'];
        $out['limit'] = $GLOBALS['config']['listings_per_page'];
        $out['total_listing'] = count($data);
        $out['total_pages'] = ceil($out['total_listing'] / $out['limit']);
        $out['status'] = 'OK';
        
        return $out;
    }
    
    /**
     * Find ID of the category to which listing will be assign
     *
     * @param array $levels - Array of the categories, mapped to the importing row.
     * @param array $mapping - Importing row.
     * @return bool|int - ID of the system category, to which will be listing assigned.
     */
    public function findCategory($levels, $mapping)
    {
        asort($levels);
        if (!$levels) {
            return false;
        }
        //Final category ID
        $category_id = 0;
        foreach ($levels as $key => $level) {
            $cat_value = $GLOBALS['rlValid']->xSql($mapping[$key]);
            $sql  = "SELECT `T1`.`ID`, `T2`.`Value`, `T1`.`Key` FROM `" . RL_DBPREFIX . "categories` AS `T1` ";
            $sql .= "LEFT JOIN `" . RL_DBPREFIX . "lang_keys` AS `T2` ";
            $sql .= "ON `T2`.`Key` = CONCAT('categories+name+', `T1`.`Key`) ";
            $sql .= "WHERE (`T2`.`Value` = '{$cat_value}' OR `T1`.`Key` = '{$cat_value}') ";
            if ($category_id) {
                $sql .= "AND `T1`.`Parent_ID` = {$category_id}";
            }
            $category = $GLOBALS['rlDb']->getRow($sql);

            //Move forward if category is exist
            if (!$category) {
                break;
            }
            $category_id = $category['ID'];
        }
        $this->loop_category_id = $category_id;

        return $category_id;
    }

    /**
     * Filtering Row data. Leave only values of the Multilevel categories.
     *
     * @param array $rows - Importing row data
     * @return array $categories - Array of the categories
     */
    public function getAllCategories($rows)
    {
        $categories = array();
        foreach ($rows as $key => $row) {
            //Check Category_level_ fields
            if (strpos($row, 'Category_level_') !== false) {
                $categories[$key] = $row;
            }
        }

        return $categories;
    }

    /**
     * Adding all categories assigned to the ticket.
     *
     * @param array $listing  - Listing information.
     * @param array $levels   - Categories.
     * @return array $listing - Modified listing info.
     */
    public function prepareMultiCategory($listing, $levels)
    {
        foreach ($levels as $level) {
            $depth = str_replace('Category_level_', '', $level['Key']);
            $listing[$level['Key']] = $this->getCatNameByLevel($listing, $depth);
        }

        return $listing;
    }

    /**
     * Return a category name by level.
     *
     * @param array   $listing - Importing listing Information.
     * @param array   $level   - Level array.
     * @return string $name    - Category name.
     */
    public function getCatNameByLevel($listing, $level)
    {
        global $rlCategories;

        $name = '';
        $parentIds = $rlCategories->getParentIDs($listing['Category_ID']);

        if(is_array($parentIds)) {
            array_unshift($parentIds, $listing['Category_ID']);
            $catIds = array_reverse($parentIds);
        } else {
            $catIds[] = $listing['Category_ID'];
        }
        $catInfo = $rlCategories->getCategory($catIds[$level-1]);

        if($catInfo) {
            $name = $catInfo['name'];
        }

        return $name;
    }

    /**
     * Return an array of the Maximum category level.
     *
     * @param  bool  $is_export - Should method prepare array to use array in the export or import process (import is default)
     * @return array $levels    - Array of the categories
     */
    public function getMaxCategoryLevelArray($is_export = true)
    {
        $max_level = $GLOBALS['rlDb']->getOne("Level", "`Status` = 'active' ORDER BY `Level` DESC", 'categories');
        $levels = array();
        for ($i = 0; $i <= $max_level; $i++) {
            $cur_level = $i + 1;
            $key = 'Category_level_' . $cur_level;
            $phrase_key = 'eil_category_level_' . $cur_level;

            if($is_export) {
                $levels[$key] = array(
                    'Key' => $key,
                    'pName' => $phrase_key,
                );
            } else {
                $levels[]= array(
                    'Key' => $key,
                    'name' => $GLOBALS['lang'][$phrase_key],
                );
            }
        }

        return $levels;
    }
    
    /**
     * Is user exceeded Membership limit
     *
     * @param  int   $account_id - Account ID
     * @param  array $plan_info  - Membership plan info
     * @return bool              - Is limit is exceeded
     */
    public function isLimitExceeded($account_id, $plan_info)
    {
        $sql = "SELECT COUNT('ID') as `count` FROM `" . RL_DBPREFIX . "listings` WHERE `Account_ID` = {$account_id} ";
        $sql .= "AND `Plan_type` = 'account'";
        $count = $GLOBALS['rlDb']->getRow($sql);

        if ($count['count'] >= $plan_info['Listing_number']) {
            return true;
        }

        return false;
    }
    /*
     * @hook apTplHeader
     * @since 3.5.0
     */
    public function hookApTplHeader()
    {
        if ($GLOBALS['controller'] == 'export_import') {
            $link = "<link href='" . RL_PLUGINS_URL . "";
            $link .= "export_import/static/style.css' type='text/css' rel='stylesheet' />";
            echo $link;
        }
    }
    
    /**
     * @hook apPhpIndexBottom
     * @since 3.5.0
     */
    public function hookApPhpIndexBottom()
    {
        if ($GLOBALS['controller'] == 'export_import' && $GLOBALS['breadCrumbs'][0]['Controller']) {
            $action = '';
            switch ($_GET['action']) {
                case 'import':
                    $action = 'reset';
                    break;
                
                case 'importing':
                    $action = 'action=import';
                    break;
                
                case 'export_table':
                    $action = 'action=export';
                    break;
            }
            $GLOBALS['breadCrumbs'][0]['Controller'] .= '&' . $action;
        }
    }
    
    /**
     * @hook apExtListingsFilters
     * @since 3.5.0
     */
    public function hookApExtListingsFilters()
    {
        if ($_GET['f_Import_file']) {
            $GLOBALS['filters']['f_Import_file'] = true;
        
            unlink($_SESSION['iel_data']['file']);
            $GLOBALS['reefless']->deleteDirectory($_SESSION['iel_data']['archive_dir']);
            unset($_SESSION['iel_data']);
        }
    }
    
    /**
     * @hook listingsModifyFieldSearch
     * @since 3.5.0
     */
    public function  hookListingsModifyFieldSearch (&$sql)
    {
        if (defined('EIL_EXPORT_TABLE') || $GLOBALS['page_info']['Key'] == 'xls_export_import') {
            $sql .= "`T7`.`Username` AS `Account_username`, `T7`.`Mail` AS `Account_email`, `T1`.`Main_photo` AS `Picture_URLs`, ";
        }
    
        if (defined('EIL_EXPORT_TABLE')) {
            $sql .= "`PC`.`Key` AS `Parent_cat_key`, ";
        }
    }
    
    /**
     * @hook listingsModifyWhereSearch
     * @since 3.5.0
     */
    public function hookListingsModifyWhereSearch(&$sql)
    {
        global $page_info, $account_info;
        
        if ((defined('REALM') && REALM == 'admin' && $_GET['controller'] == 'export_import')
            || ($page_info['Key'] == 'xls_export_import')
        ) {
            // remove active status of the listings
            $sql = str_replace("`T1`.`Status` = 'active' AND", ' ', $sql);
    
            $category_id = $GLOBALS['category_id'] ?: $_SESSION['eil_data']['post']['export_category_id'];
            if ($category_id) {
                $sql .= "AND ((`T1`.`Category_ID` = {$category_id} OR FIND_IN_SET({$category_id}, `T3`.`Parent_IDs`) > 0) OR (FIND_IN_SET({$category_id}, `T1`.`Crossed`) > 0)) ";
            }
        
            $from = $_SESSION['eil_data']['post']['export_date_from'];
            if ($from) {
                $sql .= "AND UNIX_TIMESTAMP(DATE(`T1`.`Pay_date`)) >= UNIX_TIMESTAMP('{$from}') ";
            }
        
            $to = $_SESSION['eil_data']['post']['export_date_to'];
            if ($to) {
                $sql .= "AND UNIX_TIMESTAMP(DATE(`T1`.`Pay_date`)) <= UNIX_TIMESTAMP('{$to}') ";
            }
        
            if ($page_info['Key'] == 'xls_export_import' && $account_info['ID']) {
                $sql .= "AND `T1`.`Account_ID` = {$account_info['ID']} ";
            }
        }
    }
    
    /**
     * @hook tplHeader
     *
     * @since 3.7.0 added new MultiField support
     * @since 3.5.0
     */
    public function hookTplHeader()
    {
        global $rlSmarty, $lang;

        if ($GLOBALS['page_info']['Key'] != 'xls_export_import') {
            return;
        }

        $frontCss = '<link href="' . RL_PLUGINS_URL . 'export_import/static/front-end.css" ';
        $frontCss .= 'type="text/css" rel="stylesheet" />';
        echo $frontCss;

        if (Helpers::isMultiFieldInstalled()) {
            printf(
                "<script>
                    lang['any'] = '%s';
                    var mfFields = new Array();
                    var mfFieldVals = new Array();
                    lang['select'] = '%s';
                    lang['not_available'] = '%s';
                    </script>",
                $lang['any'],
                $lang['select'],
                $lang['not_available']
            );

            $rlSmarty->assign('mf_old_style', true);
            $rlSmarty->display(RL_PLUGINS . "multiField" . RL_DS . "tplHeader.tpl");
        }
    }
    
    /**
     * @hook apPhpAccountTypesTop
     * @since 3.5.0
     */
    public function hookApPhpAccountTypesTop()
    {
        $GLOBALS['rlListingTypes']->types['export_import'] = array(
            'Key' => 'export_import',
            'name' => '',
        );
    }
    
    /**
     * @hook specialBlock
     * @since 3.5.0
     */
    public function hookSpecialBlock()
    {
        global $rlSmarty, $account_menu;
    
        if (!in_array('export_import', $GLOBALS['account_info']['Abilities'])) {
            $account_menu = $rlSmarty->get_template_vars('account_menu');
            foreach ($account_menu as $key => $item) {
                if ($item['Key'] == 'xls_export_import') {
                    unset($account_menu[$key]);
                }
            }
        
            $rlSmarty->assign_by_ref('account_menu', $account_menu);
        }
    }

    /**
     * @hook listingsModifyJoinSearch
     * @since 3.5.0
     */
    public function hookListingsModifyJoinSearch(&$sql)
    {
        if (defined('EIL_EXPORT_TABLE')) {
            $sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `PC` ON `T3`.`Parent_ID` = `PC`.`ID` ";
        }
    }
    
    /**
     * Installation method
     * @since 3.5.0
     */
    public function install()
    {
        global $rlDb;
    
        $sql = "ALTER TABLE `" . RL_DBPREFIX . "listings` ADD `Import_file` VARCHAR( 100 ) NOT NULL AFTER `Date`";
        $rlDb->query($sql);
    
        $current_version = $rlDb->getOne('Version', "`Key` = 'export_import'", 'plugins');

        if (version_compare($current_version, "3.0.0") < 0) {
            $sql = "UPDATE `" . RL_DBPREFIX . "lang_keys` SET `Module` = 'common' ";
            $sql .= "WHERE `Plugin` = 'export_import' AND `Module` = 'admin'";
            $rlDb->query($sql);
        }
        
        $GLOBALS['rlActions']->enumAdd('account_types', 'Abilities', 'export_import');
    
        $sql = "UPDATE `" . RL_DBPREFIX . "account_types` SET `Abilities` = CONCAT(`Abilities`, ',export_import') ";
        $sql .= "WHERE `Key` <> 'visitor' AND `Abilities` <> ''";
        $rlDb->query($sql);
    
        $sql = "UPDATE `" . RL_DBPREFIX . "account_types` SET `Abilities` = 'export_import' ";
        $sql .= "WHERE `Key` <> 'visitor' AND `Abilities` = ''";
        $rlDb->query($sql);

        $this->makePagesNoFollow();
    }
    
    /**
     * Plugin uninstall
     * @since 3.5.0
     */
    public function uninstall()
    {
        $sql = "ALTER TABLE `" . RL_DBPREFIX . "listings` DROP `Import_file`";
        $GLOBALS['rlDb']->query($sql);
    
        $GLOBALS['rlActions']->enumRemove('account_types', 'Abilities', 'export_import');
    }

    /**
     * @hook staticDataRegister
     *
     * @since 3.7.0 - Added $rlStatic
     * @since 3.5.0
     *
     * @param rlStatic $rlStatic
     */
    public function hookStaticDataRegister($rlStatic = null)
    {
        $rlStatic = $rlStatic !== null ? $rlStatic : $GLOBALS['rlStatic'];

        $rlStatic->addJS(RL_LIBS_URL . 'jquery/jquery.categoryDropdown.js', 'controller');
        $rlStatic->addJS(RL_PLUGINS_URL . 'export_import/static/pagination.js', 'controller');
        $rlStatic->addJS(RL_PLUGINS_URL . 'export_import/static/lib.js', 'controller');

        if (Helpers::isMultiFieldInstalled()) {
            $rlStatic->addJS(RL_PLUGINS_URL . 'multiField/static/lib.js');
        }
    }
    
    /**
     * @hook apTplFooter
     * @since 3.5.0
     */
    public function hookApTplFooter()
    {
        if($_GET['controller'] == 'export_import') {
            $this->addJs(RL_LIBS_URL . 'jquery/jquery.categoryDropdown.js');
            $this->addJs(RL_PLUGINS_URL . 'export_import/static/lib_admin.js');
            $this->addJs(RL_PLUGINS_URL . 'export_import/static/pagination.js');
        }
    }

    /**
     * @hook sitemapExcludedPages
     *
     * @since 3.6.0
     */
    public function hookSitemapExcludedPages(&$pages)
    {
        $pages = array_merge($pages, $this->pluginPages);
    }
    
    /**
     * Echo out script tag
     *
     * @param string $url - Url of the including js script
     */
    public function addJs($url) {
        echo sprintf("<script type='text/javascript' src='%s'></script>", $url);
    }
    
    public function addMembershipPlanUsing($membership_plan, $account_id)
    {
        $insert_data = array();
        $insert_data['Account_ID'] = $account_id;
        $insert_data['Plan_ID'] = $membership_plan['ID'];
    
        if (!$membership_plan['Featured_listing']) {
            $insert_data['Listings_remains'] = $membership_plan['Listing_number'];
            $insert_data['Standard_remains'] = $membership_plan['Listing_number'];
        } else {
            $insert_data['Listings_remains'] = $membership_plan['Listing_number'];
            $insert_data['Standard_remains'] = $membership_plan['Standard_listings'];
            $insert_data['Featured_remains'] = $membership_plan['Featured_listings'];
        }
        $insert_data['Type'] = 'account';
        $insert_data['Date'] = "NOW()";
        $insert_data['IP'] = $this->getClientIpAddress();
        $GLOBALS['rlActions']->insertOne($insert_data, 'listing_packages');
    }
    
    /**
     * Updating membership plan using of the provided account
     *
     * @param array $account_info - Account info
     * @return bool|void          - Return false, if provided wrong argument
     */
    public function updateMembershipUsing($account_info)
    {
        if (!is_array($account_info)) {
            return false;
        }
        $this->loadClass('MembershipPlan');
        
        $plan_ID = $account_info['Plan_ID'];
        $fields = array();
        $under_mp = 0;
        
        $mp_info = $GLOBALS['rlMembershipPlan']->getPlan($plan_ID);
        $sql = "SELECT * FROM `" . RL_DBPREFIX . "listings` WHERE `Status` != 'trash' ";
        $sql .= "AND `Account_ID` = {$account_info['ID']}";
        $all_user_listings = $GLOBALS['rlDb']->getAll($sql);
        foreach ($all_user_listings as $listing) {
            if ($listing['Plan_type'] == 'account') {
                $under_mp++;
            }
        }
        
        $fields['Listings_remains'] = $under_mp > $mp_info['Listing_number']
            ? 0
            : $mp_info['Listing_number'] - $under_mp;
        
        if ($mp_info['Advanced_mode']) {
            $featured_remains = $mp_info['Featured_listings'];
            $listings_remains = $mp_info['Standard_listings'];
            if ($under_mp >= $listings_remains) {
                $under_mp -= $listings_remains;
                $listings_remains = 0;
            } else {
                $listings_remains = $listings_remains - $under_mp;
                $under_mp = 0;
            }
    
            if ($under_mp >= $featured_remains) {
                $under_mp -= $featured_remains;
                $featured_remains = 0;
            } else {
                $featured_remains = $featured_remains - $under_mp;
                $under_mp = 0;
            }
            $fields['Standard_remains'] = $listings_remains;
            $fields['Featured_remains'] = $featured_remains;
            unset($under_mp);
        } else {
            $listings_remains = $under_mp > $mp_info['Listing_number']
                ? 0
                : $mp_info['Listing_number'] - $under_mp;
            $fields['Standard_remains'] = $listings_remains;
        }
        
        
        if (!empty($fields)) {
            $update = array(
                'fields' => $fields,
                'where' => array('Account_ID' => $account_info['ID']),
            );
            
            $GLOBALS['rlActions']->updateOne($update, 'listing_packages');
        }
        
    }
    
    /**
     * Should I make listing featured depending on the membership plan
     *
     * @param  int  $account_id
     * @param  int  $featured_count - Featured listings count in the advanced Membership plan
     * @return bool                 - Should script make this listing featured
     */
    public function makeFeatured($account_id, $featured_count)
    {
        $sql = "SELECT COUNT(`ID`) as `count` FROM `" . RL_DBPREFIX . "listings` ";
        $sql .= "WHERE `Account_ID` = {$account_id} AND `Featured_date` != '0000-00-00 00:00:00'";
        $my_featured_count = $GLOBALS['rlDb']->getRow($sql, 'count');
    
        if ($my_featured_count >= $featured_count) {
            return false;
        }
    
        return true;
    }

    /**
     * Does plan is bought by provided user
     *
     * @param  array $plan       - Checking plan information
     * @param  int   $account_id - Account ID
     *
     * @throws \Exception
     *
     * @return bool              - Does transaction is exist for this plan
     */
    public function isBoughtPlan($plan, $account_id)
    {
        if (!$plan || !$account_id) {
            return false;
        }

        $listingPackages = new ListingPackages($account_id, $plan['ID']);
        return (bool) $listingPackages->isUsageRowExistInDb();
    }
    
    /**
     * Getting information regarding listing field
     * 
     * @since 3.6.0
     *
     * @param  string $field_key - Listing field key
     * @return array|bool        - Listing field info | False if passed wrong argument
     */
    public function getListingFieldInfo($field_key = '')
    {
        if (!$field_key) {
            return false;
        }
    
        $sql = "SELECT * FROM `" . RL_DBPREFIX . "listing_fields` WHERE `Key` = '{$field_key}'";
        $res = $GLOBALS['rlDb']->getRow($sql);
    
        return $res;
    }

    /**
     * Make all pages of the plugin with nofollow tag
     * 
     * @since 3.6.0
     */
    public function makePagesNoFollow()
    {
        $sql = sprintf(
            "UPDATE `%spages` SET `No_follow` = '1' WHERE `Key` IN('%s') AND `Plugin` = 'export_import'",
            RL_DBPREFIX,
            join("','", $this->pluginPages)
        );

        return $GLOBALS['rlDb']->query($sql);
    }

    /**
     * Checking does provided ajax item is belongs to the plugin
     *
     * @since 3.7.0
     * @param string $item
     *
     * @return bool
     */
    public function isValidRequest($item)
    {
        $apRequests = array(
            'eil_fetchOptions',
            'eil_ajaxImportExportPagination',
            'eil_ajaxGetPaginationInfo',
            'eil_checkListingPackage',
        );

        return in_array($item, $apRequests);
    }

    /**
     * Parse price string, get price and currency code
     *
     * @since 3.7.1
     *
     * @param  string $str - Price string
     * @return array       - Parsed data [price => float(), currency => string()]
     */
    public function parsePrice($str)
    {
        preg_match('/([\D]+)?\s*([\d\,\.\s]+)?\s*([\D]+)?/', $str, $matches);

        $out = [];

        if ($matches[2]) {
            $price = trim($matches[2]);
            $cents = '';
            $currency = '';

            preg_match('/([\.\,]+([0-9]{2}))$/', $price, $new_price);

            if ($new_price[2]) {
                $cents = '.' . $new_price[2];
                $price = substr($price, 0, -3);
                $price = str_replace([' ', ',', '.'], '', $price) . $cents;
            }

            if ($matches[1] || $matches[3]) {
                $currency = $matches[1] ?: $matches[3];
                $currency = str_replace(array('\\'), '', $currency);
                $currency = trim($currency);
            }

            $out = array(
                'price' => $price,
                'currency' => $currency
            );
        }

        return $out;
    }
}
