<?php


/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : RLREF.CLASS.PHP
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

use \Flynax\Utils\Valid;

class rlRef
{
    /**
     * @var rlLang - language class object
     **/
    protected $rlLang;
    
    /**
     * @var rlValid - validator class object
     **/
    protected $rlValid;
    
    /**
     * @var array - Internal configuration of the plugin
     */
    protected $config;
    
    /**
     * class constructor
     **/
    function __construct()
    {
        $this->config['view']['path'] = RL_PLUGINS . 'ref/';
        $this->config['static']['url'] = RL_PLUGINS_URL . 'ref/static/';
        
        $this->rlLang = $GLOBALS['rlLang'];
        $this->rlValid = $GLOBALS['rlValid'];
    }
    
    /**
     * Generate unique reference number
     *
     * @param int    $listing_id
     * @param string $ref_tpl    - Reference string template
     * @return mixed $ref        - Return
     */
    function generate($listing_id = 0, $ref_tpl = 'RF******')
    {
        $rlength = substr_count($ref_tpl, '*');
        
        
        if (!$rlength && !is_numeric(strpos($ref_tpl, '#ID#'))) {
            $GLOBALS['rlDebug']->logger("REF NUMBER: ref tpl configured wrong");
            return;
        }
        
        $rand = $this->randomNumber($rlength);
        $ref = str_replace(str_repeat('*', $rlength), $rand, $ref_tpl);

        $ref = str_replace('#ID#', $listing_id, $ref);
        
        if ($GLOBALS['rlDb']->getOne("ID", "`ref_number` = '{$ref}' AND `ID` != '" . $listing_id . "'", 'listings')) {
            return $this->generate($listing_id, $ref_tpl);
        } else {
            return $ref;
        }
    }
    
    /**
     * Ajax Ref Search
     *
     * @param $ref
     * @return xajaxResponse
     */
    function ajaxRefSearch($ref)
    {
        global  $lang;
        
        $ref = $GLOBALS['rlValid']->xSql($ref);
        $GLOBALS['reefless']->loadClass('Listings');
        
        $listing = $GLOBALS['rlDb']->fetch('*', array('ref_number' => $ref), null, null, 'listings', 'row');
        
        if ($listing) {
            $out['status'] = 'OK';
            $link = $GLOBALS['reefless']->getListingUrl($listing);
            $out['redirect'] = $GLOBALS['config']['mod_rewrite'] ? $link : str_replace('amp;', '', $link);
        } else {
            $out['status'] = 'ERROR';
            $out['message'] = $lang['ref_not_found'];
        }
        
        return $out;
    }
    
    /**
     * @hook staticDataRegister
     * @since 2.1.0
     */
    public function hookStaticDataRegister()
    {
        $GLOBALS['rlStatic']->addBoxJS($this->config['static']['url'] . 'lib.js', 'ref_search');
    }
    
    /**
     * @hook ajaxRequest
     * @since 2.1.0
     */
    function hookAjaxRequest(&$out, $request_mode, $request_item, $request_lang)
    {
        switch ($request_item) {
            case 'refSearch':
                $ref = $GLOBALS['rlValid']->xSql($_POST['ref']);
                $out = $this->ajaxRefSearch($ref);
                break;
        }
    }
    
    /**
     * @hook afterImport
     * @since 2.1.0
     * @param array $import    - Imported data
     * @param int   $import_id - New listing ID
     */
    public function hookAfterImport($import, $import_id)
    {
        if ($import_id) {
            $this->updateRefOfTheListing($import_id);
        }
    }
    
    /**
     * Refresh Ref numbers of all listings
     *
     * @package AJAX
     *
     * @param  int   $start - Starting pointer
     * @return mixed $out   - AJAX prepared answer
     */
    public function rebuildRefs($start = 0)
    {
        global $lang;
        
        $limit = 500;
        $listings = $GLOBALS['rlDb']->fetch(array('ID'), null, null, array($start, $limit), 'listings');
        
        foreach ($listings as $listing) {
            $this->updateRefOfTheListing($listing['ID']);
        }
    
        $out['status'] = 'OK';
        if (count($listings) == $limit) {
            $out['next_limit'] = $start + $limit;
            $out['message'] = $lang['ref_processing'];
            return $out;
        }
        
        $out['message'] = $lang['ref_rebuilt'];
        return $out;
    }
    
    /**
     * @hook apAjaxRequest
     * @since 2.1.0
     * @param array  $out  - Prepared AJAX response
     * @param string $item - Request item
     */
    public function hookApAjaxRequest(&$out, $item)
    {
        switch ($item) {
            case 'refRefresh':
                $start = (int)$_POST['start'];
                $out = $this->rebuildRefs($start);
                break;
        }
    }

    /**
     * Plugin installation method
     *
     * @since 2.1.0
     */
    public function install()
    {
        global $rlDb, $rlLang, $rlCache;
    
        $rlDb->addColumnToTable(
            'ref_number',
            'VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL',
            'listings'
        );
        
        $sql = "INSERT INTO `{db_prefix}listing_fields` ";
        $sql .= "( `Key`, `Type`, `Required`, `Map`, `Status`, `Add_page`, `Details_page`, `Values`, `Readonly` ) ";
        $sql .= "VALUES ( 'ref_number', 'text', '0', '0', 'active', '0', '1', '255', '1' ) ";
        $rlDb->query($sql);
        $id = $rlDb->insertID();
        
        $allLangs = $rlLang->getLanguagesList('all');
        
        foreach ($allLangs as $key => $value) {
            $lkeys[] = array(
                'Code' => $allLangs[$key]['Code'],
                'Module' => 'common',
                'Status' => 'active',
                'Key' => 'listing_fields+name+ref_number',
                'Value' => 'Reference Number',
                'Plugin' => 'ref',
            );
        }
        $rlDb->insert($lkeys, 'lang_keys');
        
        foreach ($GLOBALS['rlListingTypes']->types as $type_key => $type) {
            if ($type['Cat_general_cat']) {
                $sql = "INSERT INTO `{db_prefix}listing_relations` (`Position`, `Category_ID`, `Fields`) ";
                $sql .= "VALUES ('0', '" . $type['Cat_general_cat'] . "', '" . $id . "')";
                $rlDb->query($sql);
            }
            
            $sql = "SELECT `T1`.`ID` FROM `{db_prefix}categories` AS `T1` ";
            $sql .= "JOIN `{db_prefix}listing_relations` AS `T2` ON `T1`.`ID` = `T2`.`Category_ID` ";
            $sql .= "WHERE `T2`.`ID` AND `T1`.`Type` = '" . $type_key . "' ";
            $sql .= "AND `T1`.`ID` != {$type['Cat_general_cat']} GROUP BY `T1`.`ID` ";
            $cats = $rlDb->getAll($sql);
            
            foreach ($cats as $key => $cat) {
                $sql = "INSERT INTO `{db_prefix}listing_relations` (`Position`, `Category_ID`, `Fields`) ";
                $sql .= "VALUES ('0', '" . $cat['ID'] . "', '" . $id . "')";
                $rlDb->query($sql);
            }
        }
        
        $rlCache->updateSubmitForms();

        /**
         * @since 2.2.0
         */
        $rlDb->addColumnToTable(
            'ref_short_urls',
            "ENUM('0','1') NOT NULL DEFAULT '0'",
            'listing_types'
        );

        /**
         * Temp hook for fix problem with updating cache after plugin installation/updating
         * @todo - Remove when problem will be fixed in core
         */
        $rlDb->query(
            "INSERT INTO `{db_prefix}config` (`Key`,`Group_ID`,`Default`,`Plugin`)
            VALUES ('ref_update_cache_tmp', '0', '1', 'ref')"
        );
    }
    
    /**
     * @hook afterListingDone
     * @since 2.1.0
     *
     * @param \Flynax\Classes\AddListing $addListing - Instance of the AddListing class
     */
    public function hookAfterListingDone($addListing)
    {
        $this->updateRefOfTheListing($addListing->listingID);
    }
    
    /**
     * @hook afterListingEdit
     * @since 2.2.0
     *
     * @param \Flynax\Classes\EditListing $editListing - Instance of the EditListing class
     */
    public function hookAfterListingEdit($editListing)
    {
        if ($editListing->listingData['ref_number']) {
            return;
        }

        $this->updateRefOfTheListing($editListing->listingID);
    }
    
    /**
     * @hook apPhpListingsAfterAdd
     * @since 2.1.0
     */
    public function hookApPhpListingsAfterAdd()
    {
        $this->updateRefOfTheListing($GLOBALS['listing_id']);
    }
    
    /**
     * @hook apPhpListingsAfterEdit
     * @since 2.1.1
     */
    public function hookApPhpListingsAfterEdit()
    {
        global $listing_id;

        if (!$this->isRefExist($listing_id)) {
            $this->updateRefOfTheListing($GLOBALS['listing_id']);
        }
    }

    /**
     * @hook apExtListingsAfterUpdate
     * @since 2.1.1
     */
    public function hookApExtListingsAfterUpdate()
    {
        global $updateData;

        $id     = isset($updateData['where']['ID']) ? (int) $updateData['where']['ID'] : 0;
        $status = isset($updateData['fields']['Status']) ? (string) $updateData['fields']['Status'] : '';

        if ($id && $status === 'active' && !$this->isRefExist($id)) {
            $this->updateRefOfTheListing($id);
        }
    }

    /**
     * Updating the Ref number of the provided Listing
     *
     * @param  int $listing_id - Listing ID
     * @return bool            - Is update process has been processed successfully
     */
    public function updateRefOfTheListing($listing_id = 0)
    {
        if (!$listing_id) {
            return false;
        }

        $ref_pattern = $GLOBALS['config']['ref_tpl'] ?: 'RF******';

        $ref = $this->generate($listing_id, $ref_pattern);
        $sql = "UPDATE `{db_prefix}listings` SET `ref_number` = '" . $ref . "' ";
        $sql .= "WHERE `ID` = " . $listing_id;
        
        return $GLOBALS['rlDb']->query($sql);
    }
    
    /**
     * @deprecated 2.2.0
     */
    public function hookSpecialBlock() {}
    
    /**
     * @deprecated 2.2.0
     */
    public function hookApPhpControlsBottom() {}
    
    /**
     * @hook apTplControlsForm
     * @since 2.1.0
     */
    public function hookApTplControlsForm()
    {
        $GLOBALS['rlSmarty']->display($this->config['view']['path'] . 'apTplControlsForm.tpl');
    }
    
    /**
     * Update to 2.0.1
     */
    public function update201()
    {
        global $rlDb;
    
        $field_id = $rlDb->getOne('ID', "`Key` ='ref_number'", 'listing_fields');
    
        $sql = "DELETE FROM `{db_prefix}listing_relations` WHERE `Position` = '0' ";
        $sql .= "AND `Fields` = '" . $field_id . "'";
        $rlDb->query($sql);
    
        foreach ($GLOBALS['rlListingTypes']->types as $type_key => $type) {
            if ($type['Cat_general_cat']) {
                $sql = "INSERT INTO `{db_prefix}listing_relations` ";
                $sql .= "(`Position`, `Category_ID`, `Fields`) ";
                $sql .= "VALUES ('0', '" . $type['Cat_general_cat'] . "', '" . $field_id . "')";
                $rlDb->query($sql);
            }
        
            $sql = "SELECT `T1`.`ID` FROM `{db_prefix}categories` AS `T1` ";
            $sql .= "JOIN `{db_prefix}listing_relations` AS `T2` ON `T1`.`ID` = `T2`.`Category_ID` ";
            $sql .= "WHERE `T2`.`ID` AND `T1`.`Type` = '" . $type_key . "' ";
            $sql .= "AND `T1`.`ID` != '{$type['Cat_general_cat']}' GROUP BY `T1`.`ID`";
            $cats = $rlDb->getAll($sql);
        
            foreach ($cats as $key => $cat) {
                $sql = "INSERT INTO `{db_prefix}listing_relations` ";
                $sql .= "(`Position`, `Category_ID`, `Fields`) VALUES ('0', '" . $cat['ID'] . "', '" . $field_id . "')";
                $rlDb->query($sql);
            }
        }
        $GLOBALS['rlCache']->updateSubmitForms();
    }
    
    /**
     * Update to 2.1.0
     */
    public function update210()
    {
        $sql = "DELETE FROM `{db_prefix}hooks` WHERE `Plugin` = 'ref' ";
        $sql .= "AND `Name` = 'afterListingCreate'";
        $GLOBALS['rlDb']->query($sql);
    }
    
    /**
     * Update to 2.1.1
     */
    public function update211()
    {
        $sql = "DELETE FROM `{db_prefix}hooks` WHERE `Plugin` = 'ref' ";
        $sql .= "AND `Name` = 'pageinfoArea'";
        $GLOBALS['rlDb']->query($sql);
    }

    /**
     * Update to 2.2.0
     */
    public function update220()
    {
        global $rlDb;

        $rlDb->addColumnToTable(
            'ref_short_urls',
            "ENUM('0','1') NOT NULL DEFAULT '0'",
            'listing_types'
        );

        $rlDb->query(
            "DELETE FROM `{db_prefix}hooks`
             WHERE `Plugin` = 'ref'
             AND `Name` IN ('specialBlock','apPhpControlsBottom')"
        );
    }

    /**
     * Checking does reference number is exist in provided Listing
     *
     * @since 2.1.1
     * @param  int $listing_id
     * @return bool
     */
    public function isRefExist($listing_id = 0)
    {
        $listing_id = (int)$listing_id;
        
        if (!$listing_id) {
            return false;
        }
        
        $res = $GLOBALS['rlDb']->getOne('ref_number', "`ID` = {$listing_id}", 'listings');
        return !empty($res);
    }
    
    /**
     * @hook apTplFooter
     * @since 2.1.0
     */
    public function hookApTplFooter()
    {
        echo sprintf("<script type='text/javascript' src='%s'></script>", $this->config['static']['url'] . 'lib.js');
    }

    /**
     * @hook editListingAdditionalInfo
     * @since 2.1.2
     */
    public function hookEditListingAdditionalInfo($editInstance, &$data)
    {
        if ($data['ref_number']) {
            return false;
        }

        $data['ref_number'] = $editInstance->listingData['ref_number'];
    }
    /**
     * @hook  phpListingUrl
     * @since 2.2.0
     */
    public function hookPhpListingUrl($basePartUrl, &$additionalPartUrl, $listing)
    {
        if ($GLOBALS['ref_url']
            || !$listing['ref_number']
            || !$GLOBALS['config']['mod_rewrite']
            || !(bool) $GLOBALS['rlListingTypes']->types[$listing['Listing_type']]['ref_short_urls']
        ) {
            return false;
        }

        $additionalPartUrl = $listing['ref_number'] . '/';
    }

    /**
     * @hook  init
     * @since 2.2.0
     */
    public function hookInit()
    {
        global $rlDb;

        $refNumber   = Valid::escape($_GET['page']);
        $langFromGet = Valid::escape($_GET['lang']);
        $defaultLang = true;

        // URL with non default language
        if (strlen($refNumber) === 2
            && in_array($refNumber, array_keys($GLOBALS['rlLang']->getLanguagesList()))
        ) {
            $langFromGet = $refNumber;
            $refNumber   = Valid::escape($_GET['rlVareables']);
            $defaultLang = false;
        }

        if (!$refNumber || !$GLOBALS['config']['mod_rewrite']) {
            return;
        }

        $rlDb->outputRowsMap = [false, 'Key'];
        $refTypes = (array) $rlDb->fetch(['Key'], ['ref_short_urls' => '1'], null, null, 'listing_types');

        if (!$refTypes) {
            return;
        }

        $listingID = (int) $rlDb->getOne('ID', "`ref_number` = '{$refNumber}'", 'listings');
        $langCode  = $langFromGet ?: $GLOBALS['config']['lang'];

        if (!$listingID) {
            return;
        }

        $GLOBALS['ref_url'] = true;
        $listing            = $GLOBALS['rlListings']->getListing($listingID);
        $originalListingUrl = $GLOBALS['reefless']->getListingUrl($listing, $langCode);
        unset($GLOBALS['ref_url']);

        if (!in_array($listing['Listing_type'], $refTypes)) {
            return;
        }

        $baseUrl         = RL_URL_HOME . ($langFromGet ? $langFromGet . '/' : '');
        $originalUrlPart = str_replace($baseUrl, '', $originalListingUrl);
        $urlParts        = explode('/', $originalUrlPart);
        $categoryPath    = $urlParts[0];

        // Emulate data of standard listing details page
        $_GET['listing_id'] = $listingID;

        if ($defaultLang) {
            $_GET['page'] = $categoryPath;
            array_shift($urlParts);
        }

        $urlParts = implode('/', $urlParts);
        $urlParts = str_replace('-' . $listingID . '.html', '', $urlParts);
        $_GET['rlVareables'] = $urlParts;
    }

    /**
     * @hook  apTplListingTypesForm
     * @since 2.2.0
     */
    public function hookApTplListingTypesForm()
    {
        $GLOBALS['rlSmarty']->display(RL_PLUGINS . 'ref/admin/shortUrlsOption.tpl');
    }

    /**
     * @since 2.2.0
     * @hook  apPhpListingTypesPost
     */
    public function hookApPhpListingTypesPost()
    {
        $_POST['ref_short_urls'] = $GLOBALS['type_info']['ref_short_urls'];
    }

    /**
     * @since 2.2.0
     * @hook  apPhpListingTypesBeforeAdd
     */
    public function hookApPhpListingTypesBeforeAdd()
    {
        $GLOBALS['data']['ref_short_urls'] = (int) $_POST['ref_short_urls'];
    }

    /**
     * @since 2.2.0
     * @hook  apPhpListingTypesBeforeEdit
     */
    public function hookApPhpListingTypesBeforeEdit()
    {
        $GLOBALS['update_date']['fields']['ref_short_urls'] = (int) $_POST['ref_short_urls'];
    }

    /**
     * @hook  apExtPluginsData
     * @since 2.2.0
     */
    public function hookApExtPluginsData()
    {
        global $config;

        /** Update cache of system forms
         *  In install process updating of cache works without any plugin integrations in core > 4.8.0
         * @todo - Remove it when problem will be fixed in core
         */
        if ($config['ref_update_cache_tmp']) {
            $GLOBALS['rlCache']->updateForms();
            $GLOBALS['rlDb']->query(
                "DELETE FROM `{db_prefix}config`
                 WHERE `Key` = 'ref_update_cache_tmp'"
            );
            unset($config['ref_update_cache_tmp']);
        }
    }

    /**
     * Plugin uninstalling method
     */
    public function uninstall()
    {
        global $rlDb;
    
        $field_id = $rlDb->getOne('ID', "`Key` ='ref_number'", 'listing_fields');
        
        $rlDb->dropColumnFromTable('ref_number', 'listings');
        
        $sql = "DELETE FROM `{db_prefix}listing_fields` WHERE `Key` = 'ref_number'";
        $rlDb->query($sql);
    
        $sql = "DELETE FROM `{db_prefix}listing_relations` WHERE `Position` = '0' ";
        $sql .= "AND `Fields` = '" . $field_id . "'";
        $rlDb->query($sql);
    
        $GLOBALS['rlCache']->updateSubmitForms();

        $rlDb->dropColumnFromTable('ref_short_urls', 'listing_types');
    }
    
    /**
     * Generating random n-digit number
     *
     * @param  int    $length - String length
     * @return string $result - Generated string
     */
    public function randomNumber($length)
    {
        /* use default rand generator */
        if ($length <= 10) {
            return substr(mt_rand(), 0, $length);
        }
        
        $result = '';
        if (!$length) {
            return $result;
        }
        
        for ($i = 0; $i < $length; $i++) {
            $result .= mt_rand(0, 9);
        }
        
        return $result;
    }
}
