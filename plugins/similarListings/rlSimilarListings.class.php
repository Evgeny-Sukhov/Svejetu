<?php


/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : RLSIMILARLISTINGS.CLASS.PHP
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

class rlSimilarListings 
{
    /**
     * Plugin installer
     * @since 1.2.0
     **/
    public function install() 
    {
        global $rlDb;

        $raw_sql = "`ID` int(11) NOT NULL AUTO_INCREMENT,
             `Position` int(3) NOT NULL DEFAULT '0',
             `Category_ID` int(5) NOT NULL DEFAULT '0',
             `Field_ID` int(5) NOT NULL DEFAULT '0',
             PRIMARY KEY (`ID`),
             KEY `Kind_ID` (`Category_ID`)";

        $rlDb->createTable("similar_listings_form", $raw_sql, RL_DBPREFIX, "ENGINE=MYISAM CHARSET=utf8 COLLATE=utf8_general_ci");

        $sql = "UPDATE `{db_prefix}blocks` SET `Sticky` = 0, `Cat_sticky` = '1', ";
        $sql .= "`Page_ID` = (SELECT `ID` FROM `{db_prefix}pages` WHERE `Key` = 'view_details' LIMIT 1) WHERE `Key` = 'sl_similar_listings' ";
        $rlDb->query($sql);

        $GLOBALS['rlPlugin']->controller = '';
    }

    /**
     * Update plugin to version 1.2.0
     **/
    public function update120() 
    {
        // Update phrase
        $sql = "UPDATE `{db_prefix}lang_keys` SET `Value` = REPLACE(`Value`,'[category]','{category}') ";
        $sql .= "WHERE `Key` = 'sl_form_title'";
        $GLOBALS['rlDb']->query($sql);
    }

    /**
     * Plugin un-installer
     * @since 1.2.0
     **/
    public function uninstall() 
    {
        // DROP TABLE
        $GLOBALS['rlDb']->dropTable('similar_listings_form');

    }

    /** Hooks **/
    /**
     * @hook  apTplCategoriesNavBar
     * @since 1.2.0
     */
    public function hookApTplCategoriesNavBar()
    {
        global $lang;
        if ($_GET['form'] != 'similar_listings_form' && $_GET['action'] == 'build') {
            echo '<a title="' . str_replace('{category}', $GLOBALS['category_info']['name'], $lang['sl_form_title']) . '" href="';
            echo RL_URL_HOME . ADMIN;
            echo '/index.php?controller=similar_listings&action=build&form=similar_listings_form&key=' . $GLOBALS['category_info']['Key'];
            echo '" class="button_bar"><span class="left"></span><span class="center_build">';
            echo $lang['sl_form'] . '</span><span class="right"></span></a>';
        }
    }

    /**
     * @hook  apTplCategoriesBottom
     * @since 1.2.0
     */
    public function hookApTplCategoriesBottom()
    {
        echo '<script type="text/javascript">';
        echo 'var new_list_item = {';
        echo 'text: "' . $GLOBALS['lang']['sl_build_form'] . '",';
        echo 'href: rlUrlHome+"index.php?controller=similar_listings&amp;action=build&form=similar_listings_form&amp;key={key}"';
        echo '}; list.push(new_list_item);';
        echo '</script>';
    }

    /**
     * @hook  apTplFooter
     * @since 1.2.0
     */
    public function hookApTplFooter()
    {
        echo '<script type="text/javascript">';
        echo "$('#mPlugin_similarListings').remove();";
        echo '</script>';
    }

    /**
     * @hook  specialBlock
     * @since 1.2.0
     */
    public function hookSpecialBlock()
    {
        global $blocks, $page_info;

        if ($blocks['sl_similar_listings'] && $page_info['Key'] != 'view_details') {
            unset($blocks['sl_similar_listings']);
            $GLOBALS['rlCommon']->defineBlocksExist($blocks);
        }
    }

    /**
     * @hook  listingDetailsTop
     * @since 1.2.0
     */
    public function hookListingDetailsTop()
    {
        global $blocks, $page_info;

        if ($blocks['sl_similar_listings'] && $GLOBALS['listing_id'] && $page_info['Key'] == 'view_details') {
            $similar_listings = $this->getListings();
            if ($similar_listings) {
                $GLOBALS['rlSmarty']->assign_by_ref('similar_listings', $similar_listings);
            } else {
                unset($blocks['sl_similar_listings']);
                $GLOBALS['rlCommon']->defineBlocksExist($blocks);
            }
        } 
    }

    /**
     * Get listings
     *
     * @return array - listings information
     **/

    public function getListings()
    {
        global $sql, $config, $rlListings, $rlValid, $rlHook, $rlDb, $listing_id, $listing_data;
       
        if (!$listing_data) {
            return;
        }

        if ($config['cache']) {
            $config['cache'] = 0;
            $restore_cache = true;
        }

        $similar_form_fields = $rlListings->getFormFields(
                $listing_data['Category_ID'], 
                'similar_listings_form', 
                $listing_data['Listing_type']
            );

        if ($restore_cache) {
            $config['cache'] = 1;
        }

        // if there is not form field, add category field
        if (!$similar_form_fields) {
            $similar_form_fields['Category_ID'] = array(
                'Key'  => 'Category_ID',
                'Type' => 'select',
            );
        }

        $sql = "SELECT DISTINCT {hook} ";
        $sql .= "`T1`.*, `T3`.`Path` AS `Path`, `T3`.`Key` AS `Key`, `T3`.`Type` AS `Listing_type`, ";

        $rlHook->load('listingsModifyField');

        $sql .= "IF(`T1`.`Featured_date`, '1', '0') `Featured` ";
        
        $sql .= "FROM `{db_prefix}listings` AS `T1` ";
        $sql .= "LEFT JOIN `{db_prefix}categories` AS `T3` ON `T1`.`Category_ID` = `T3`.`ID` ";

        $rlHook->load('listingsModifyJoin');

        $sql .= "WHERE `T1`.`Status` = 'active' ";
        $sql .= "AND `T1`.`ID` != '{$listing_id}' ";

        foreach ($similar_form_fields as $key => $field) {
            if ($listing_data[$field['Key']]) {
                $relevance_built = true;
                break;
            }
        }

        if ($config['sl_relevance_mode'] && $relevance_built) {
            $hook = "( ";
        }

        foreach ($similar_form_fields as $key => $field) {
            if ($listing_data[$field['Key']]) {
                switch ($field['Type']) {
                    case "select":
                        if ($field['Key'] == 'Category_ID') {
                            if ($config['sl_relevance_mode']) {
                                $hook .= "IF(`T1`.`Category_ID` = '{$listing_data[$field['Key']]}', 3, 0) + ";
                                $hook .= "IF( FIND_IN_SET('{$listing_data['Category_ID']}', `T1`.`Crossed`) > 0, 2, 0 ) + ";
                                $hook .= "IF( FIND_IN_SET('{$listing_data['Category_ID']}', `T3`.`Parent_IDs`) > 0, 1, 0 ) + ";
                                $hook .= "IF( FIND_IN_SET('{$listing_data['Parent_ID']}', `T3`.`Parent_IDs`) > 0, 1, 0 ) + ";
                            }

                            if ($config['sl_category_exact_match'] || !$config['sl_relevance_mode']) {
                                $sql .= "AND (`T1`.`Category_ID` = '{$listing_data['Category_ID']}' ";

                                if (!$config['sl_category_exact_match'] || $config['sl_relevance_mode']) {
                                    $sql .= "OR (FIND_IN_SET('{$listing_data['Category_ID']}', `T1`.`Crossed`) > 0 ) ";
                                    $sql .= "OR FIND_IN_SET('{$listing_data['Category_ID']}', `T3`.`Parent_IDs`) > 0 ";
                                    $sql .= "OR FIND_IN_SET('{$listing_data['Parent_ID']}', `T3`.`Parent_IDs`) > 0 ";
                                }

                                $sql .= " ) ";
                            }

                        } else {
                            if ($config['sl_relevance_mode']) {
                                $hook .= "IF(`T1`.`{$field['Key']}` = '" . $rlValid->xSql($listing_data[$field['Key']]) . "', 1, 0) + ";
                            } else {
                                $sql .= " AND `T1`.`{$field['Key']}` = '" . $rlValid->xSql($listing_data[$field['Key']]) . "' ";
                            }
                        }

                        break;
                    case "text":
                        if ($config['sl_relevance_mode']) {
                            $keywords = preg_split("/[\s,]+/", $listing_data[$field['Key']]);
                            if ($keywords) {
                                $sql .= " AND (";
                                foreach ($keywords as $kwKey => $keyword) {
                                    $hook .= "IF(`T1`.`{$field['Key']}` LIKE '%" . $rlValid->xSql($keyword) . "%', 1, 0) + ";
                                    $sql .= "`T1`.`{$field['Key']}` LIKE '%" . $rlValid->xSql($keyword) . "%' OR ";
                                }
                                $sql = substr($sql, 0, -3);
                                $sql .= ") ";
                            }
                            break;
                        }
                    default:
                        if ($config['sl_relevance_mode']) {
                            $hook .= "IF(`T1`.`{$field['Key']}` = '" . $rlValid->xSql($listing_data[$field['Key']]) . "', 1, 0) + ";
                        } else {
                            $sql .= " AND `T1`.`{$field['Key']}` = '" . $rlValid->xSql($listing_data[$field['Key']]) . "' ";
                        }
                        break;
                }
            }
        }

        if ($config['sl_relevance_mode'] && $relevance_built) {
            $hook = substr($hook, 0, -3);
            $hook .= " ) as `relevance`, ";
        }

        $plugin_name = "similar_listings";
        $rlHook->load('listingsModifyWhere', $sql, $plugin_name);
        $rlHook->load('listingsModifyGroup');

        if ($config['sl_relevance_mode'] && $relevance_built) {
            $sql .= "ORDER BY `relevance` DESC ";
        } else {
            $sql .= "ORDER BY RAND() ";
        }

        if ($config['sl_listings_in_box']) {
            $sql .= "LIMIT " . intval($config['sl_listings_in_box']);
        }

        $sql = str_replace('{hook}', $hook, $sql);

        $listings = $rlDb->getAll($sql);
        $listings = $GLOBALS['rlLang']->replaceLangKeys($listings, 'categories', 'name');

        if (empty($listings)) {
            return false;
        }

        foreach ($listings as $key => $value) {
            /* populate fields */
            $fields = $rlListings->getFormFields($value['Category_ID'], 'featured_form', $value['Listing_type']);

            foreach ($fields as $fKey => $fValue) {
                $fields[$fKey]['value'] = $GLOBALS['rlCommon']->adaptValue($fValue, $value[$fKey], 'listing', $value['ID']);
            }

            $listings[$key]['fields'] = $fields;
            $listings[$key]['listing_title'] = $rlListings->getListingTitle($value['Category_ID'], $value, $value['Listing_type']);
            $listings[$key]['url'] = $GLOBALS['reefless']->getListingUrl($listings[$key]);
        }

        return $listings;
    }
}
