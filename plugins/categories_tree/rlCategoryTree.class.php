<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : RLCATEGORYTREE.CLASS.PHP
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

class rlCategoryTree
{
    /**
     * Constructor
     *
     * @since 2.1.0
     */
    public function __construct()
    {
        global $rlSmarty;

        if (is_object($rlSmarty)) {
            $rlSmarty->register_function('buildCategoryUrl', array($this, 'buildCategoryUrl'));
        }
    }

    /**
     * Re-assign category blocks controller
     *
     * @access hook - specialBlock
     */
    public function blocks()
    {
        global $blocks, $rlListingTypes;

        foreach ($blocks as $key => &$block) {
            if (preg_match('#^ltcb\_#', $key)) {
                $apply = true;

                preg_match('/types\="([^"].*)"/', $block['Content'], $matches);
                if ($matches[1]) {
                    foreach (explode(',', $matches[1]) as $type) {
                        if (!$rlListingTypes->types[$type]['Ctree_module']) {
                            $apply = false;
                            break;
                        }
                    }

                    if ($apply) {
                        $block['Content'] = '{include file=$smarty.const.RL_PLUGINS|cat:"categories_tree"|cat:$smarty.const.RL_DS|cat:"block.tpl" types="' . $matches[1] . '"}';
                    }
                }
            }
        }
    }

    /**
     * Open category tree level
     *
     * @since   2.1.0 - Changed to simple ajax
     * @package xAjax
     *
     * @param  int     $id
     * @param  string  $type           - Current listing type
     * @param  int     $current_cat_id - ID of global category
     * @return string
     */
    public function ajaxOpen($id = 0, $type = '', $current_cat_id = 0)
    {
        global $reefless, $rlSmarty, $lang;

        $id   = (int) $id;
        $type = $GLOBALS['rlValid']->xSql($type);

        if (!$id || !$type) {
            return array();
        }

        $reefless->loadClass('Categories');
        $reefless->loadClass('Smarty');

        $rlSmarty->assign_by_ref('ctree_subcategories', $GLOBALS['rlCategories']->getCategories($id, $type));
        $rlSmarty->assign_by_ref('box_listing_type', $GLOBALS['rlListingTypes']->types[$type]);
        $rlSmarty->assign('rlTplBase', RL_URL_HOME . 'templates/' . $GLOBALS['config']['template'] . '/');
        $rlSmarty->assign_by_ref('lang', $lang);
        $rlSmarty->assign('category', array('ID' => $current_cat_id));

        $this->getCache();

        $out = $rlSmarty->fetch(RL_PLUGINS . 'categories_tree/level.tpl', null, null, false);

        return $out;
    }

    /**
     * Add cache data
     *
     * @access hook - specialBlock
     */
    public function setCache()
    {
        global $config;

        $sql = "SELECT `T1`.`ID`, `T1`.`Count`, IF(`T2`.`ID`, 1, 0) AS `Sub_cat` ";
        $sql .= "FROM `" . RL_DBPREFIX . "categories` AS `T1` ";
        $sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T2` ON `T1`.`ID` = `T2`.`Parent_ID` AND `T2`.`Status` = 'active' ";
        $sql .= "WHERE `T1`.`Level` > 0 AND `T1`.`Status` = 'active' ";
        $categories = $GLOBALS['rlDb']->getAll($sql, 'ID');

        reset($categories);
        if (key($categories) <= 0) {
            foreach ($categories as $category) {
                $data[$category['ID']] = $category;
            }
            unset($categories);
        }

        if ($data || $categories) {
            $GLOBALS['rlCache']->set('cache_ctree_data', $data ?: $categories);
        }
    }

    /**
     * Get cache data
     *
     * @access hook - specialBlock
     */
    public function getCache()
    {
        $GLOBALS['reefless']->loadClass('Cache');

        if (!$GLOBALS['cache_ctree_data']) {
            $GLOBALS['cache_ctree_data'] = $GLOBALS['rlCache']->get('cache_ctree_data');
        }

        $GLOBALS['rlSmarty']->assign_by_ref('cache_ctree_data', $GLOBALS['cache_ctree_data']);
    }

    /**
     * Build category url
     *
     * @since 2.1.0
     *
     * @param  array  $params
     * @return string
     */
    public function buildCategoryUrl($params)
    {
        global $config, $pages;

        $category    = $params['category'];
        $page_key    = $params['page_key'];
        $cat_postfix = $params['cat_postfix'];

        if ($category && $page_key) {
            $lt_urls_type = $GLOBALS['rlListingTypes']->types[str_replace('lt_', '', $params['page_key'])]['Links_type'];
            $lt_urls_type = $lt_urls_type ?: 'full';

            $url = RL_URL_HOME;
            $url .= $config['lang'] != RL_LANG_CODE && $config['mod_rewrite'] ? (RL_LANG_CODE . '/') : '';
            $url .= !$config['mod_rewrite'] ? 'index.php' : '';

            if ($config['mod_rewrite']) {
                $url .= ($lt_urls_type == 'full' ? $pages[$page_key] . '/' : '') . $category['Path'];

                if ($lt_urls_type == 'subdomain') {
                    $url = preg_replace('#http(s)?://(www.)?#', 'http$1://' . $pages[$page_key] . '.', $url);
                }

                $url .= ($cat_postfix ? '.html' : '/');
            } else {
                $url .= '?page=' . $pages[$page_key] . '&category=' . $category['ID'];
            }

            return $url;
        }
    }

    /**
     * Update status of system hook of AllInOne package
     *
     * @since 2.1.2
     *
     * @param  boolean $active - Activate or Deactivate system hook
     */
    public function updateCombinedCategoriesBox($active)
    {
        $status = $active ? 'active' : 'approval';

        $GLOBALS['rlDb']->query(
            "UPDATE `" . RL_DBPREFIX . "hooks` SET `Status` = '{$status}'
            WHERE `Name` = 'simulateCatBlocks' AND `Class` = 'AllInOne'"
        );
    }

    /**
     * Install process
     *
     * @since 2.1.0
     */
    public function install()
    {
        global $rlDb;

        $rlDb->query("
            ALTER TABLE `" . RL_DBPREFIX . "listing_types`
            ADD `Ctree_module` ENUM('0', '1') NOT NULL DEFAULT '1' AFTER `Cat_hide_empty`,
            ADD `Ctree_subcat_counter` ENUM('0', '1') NOT NULL DEFAULT '0' AFTER `Ctree_module`,
            ADD `Ctree_open_subcat` ENUM('0', '1') NOT NULL DEFAULT '0' AFTER `Ctree_subcat_counter`,
            ADD `Ctree_child_only` ENUM('0', '1') NOT NULL DEFAULT '0' AFTER `Ctree_open_subcat`
        ");

        $rlDb->query("
            INSERT INTO `" . RL_DBPREFIX . "config` (`Key`, `Default`, `Type`, `Data_type`, `Plugin`)
            VALUES ('cache_ctree_data', '', 'text', 'int', 'categories_tree')
        ");

        $GLOBALS['config']['cache_ctree_data'] = '';

        $rlDb->query("
            UPDATE `" . RL_DBPREFIX . "listing_types` SET
            `Ablock_visible_number` = '0',
            `Ablock_show_subcats` = '1',
            `Ablock_subcat_number` = '0',
            `Ablock_scrolling` = '1'
        ");

        $this->updateCombinedCategoriesBox(false);
        $this->setCache();
    }

    /**
     * Uninstall process
     *
     * @since 2.1.0
     */
    public function uninstall()
    {
        global $rlDb;

        $rlDb->query("
            ALTER TABLE `" . RL_DBPREFIX . "listing_types`
            DROP `Ctree_module`,
            DROP `Ctree_subcat_counter`,
            DROP `Ctree_open_subcat`,
            DROP `Ctree_child_only`
        ");

        $this->updateCombinedCategoriesBox(true);
    }

    /**
     * @hook  specialBlock
     * @since 2.1.0
     */
    public function hookSpecialBlock()
    {
        $this->getCache();

        if (!$_POST['xjxfun']) {
            $GLOBALS['rlCategoryTree']->blocks();
        }
    }

    /**
     * @hook  browseBCArea
     * @since 2.1.0
     */
    public function hookBrowseBCArea()
    {
        global $category;

        $ctree_bc = [];

        if ($category['ID']) {
            // collect categories which must be opened in current level of categories
            foreach ($GLOBALS['bread_crumbs'] as $bc_item) {
                if (isset($bc_item['category']) && isset($bc_item['Level'])) {
                    $ctree_bc[] = $bc_item['ID'];
                }
            }

            $ctree_bc[] = $category['ID'];
        }

        $GLOBALS['rlSmarty']->assign('ctree_bc', $ctree_bc);
    }

    /**
     * @hook  apPhpListingTypesPost
     * @since 2.1.0
     */
    public function hookApPhpListingTypesPost()
    {
        global $type_info;

        $_POST['ctree_module']         = $type_info['Ctree_module'];
        $_POST['ctree_subcat_counter'] = $type_info['Ctree_subcat_counter'];
        $_POST['ctree_open_subcat']    = $type_info['Ctree_open_subcat'];
        $_POST['ctree_child_only']     = $type_info['Ctree_child_only'];
    }

    /**
     * @hook  apPhpListingTypesBeforeAdd
     * @since 2.1.0
     */
    public function hookApPhpListingTypesBeforeAdd()
    {
        global $data;

        $data['Ctree_module']         = $_POST['ctree_module'];
        $data['Ctree_subcat_counter'] = $_POST['ctree_subcat_counter'];
        $data['Ctree_open_subcat']    = $_POST['ctree_open_subcat'];
        $data['Ctree_child_only']     = $_POST['ctree_child_only'];

        if ((bool) $_POST['ctree_module']) {
            $this->updateCombinedCategoriesBox(false);
        } else {
            $this->updateCombinedCategoriesBox(true);
        }
    }

    /**
     * @hook  apPhpListingTypesBeforeEdit
     * @since 2.1.0
     */
    public function hookApPhpListingTypesBeforeEdit()
    {
        global $update_date;

        $update_date['fields']['Ctree_module']         = $_POST['ctree_module'];
        $update_date['fields']['Ctree_subcat_counter'] = $_POST['ctree_subcat_counter'];
        $update_date['fields']['Ctree_open_subcat']    = $_POST['ctree_open_subcat'];
        $update_date['fields']['Ctree_child_only']     = $_POST['ctree_child_only'];

        if ((bool) $_POST['ctree_module']) {
            $this->updateCombinedCategoriesBox(false);
        } else {
            $this->updateCombinedCategoriesBox(true);
        }
    }

    /**
     * @hook  apTplListingTypesFormCategoryAddBlock
     * @since 2.1.0
     */
    public function hookApTplListingTypesFormCategoryAddBlock()
    {
        $GLOBALS['rlSmarty']->display(RL_PLUGINS . 'categories_tree/admin/settings.tpl');
    }

    /**
     * @hook  tplHeader
     * @since 2.1.0
     */
    public function hookTplHeader()
    {
        foreach ($GLOBALS['blocks'] as $block_key => $block) {
            if (false !== strpos($block_key, 'ltcb_')) {
                $GLOBALS['rlSmarty']->display(RL_PLUGINS . 'categories_tree' . RL_DS . 'header.tpl');
                break;
            }
        }
    }

    /**
     * @hook  browseMiddle
     * @since 2.1.0
     */
    public function hookBrowseMiddle()
    {
        global $category, $listing_type_key, $rlSmarty;

        if (!$GLOBALS['rlListingTypes']->types[$listing_type_key]['Ctree_child_only'] || !$category['ID']) {
            return;
        }

        $box_categories                                         = $rlSmarty->get_template_vars('box_categories');
        $box_categories                                         = $box_categories ?: $rlSmarty->get_template_vars('categories');
        $box_categories[$listing_type_key]                      = array();
        $box_categories[$listing_type_key][0]                   = $category;
        $box_categories[$listing_type_key][0]['pName']          = 'categories+name+' . $category['Key'];
        $box_categories[$listing_type_key][0]['pTitle']         = 'categories+title+' . $category['Key'];
        $box_categories[$listing_type_key][0]['sub_categories'] = $GLOBALS['categories'] ?: $GLOBALS['rlCategories']->getCategories(
            $category['ID'],
            $listing_type_key
        );

        $rlSmarty->assign('box_categories', $box_categories);
    }

    /**
     * @hook  categoriesListingsIncrease
     * @since 2.1.0
     */
    public function hookCategoriesListingsIncrease()
    {
        $this->setCache();
    }

    /**
     * @hook  categoriesListingsDecrease
     * @since 2.1.0
     */
    public function hookCategoriesListingsDecrease()
    {
        $this->setCache();
    }

    /**
     * @hook  apPhpCategoriesAfterAdd
     * @since 2.1.0
     */
    public function hookApPhpCategoriesAfterAdd()
    {
        $this->setCache();
    }

    /**
     * @hook  apPhpCategoriesAfterEdit
     * @since 2.1.0
     */
    public function hookApPhpCategoriesAfterEdit()
    {
        $this->setCache();
    }

    /**
     * @hook  staticDataRegister
     * @since 2.1.0
     */
    public function hookStaticDataRegister()
    {
        $GLOBALS['rlStatic']->addFooterCSS(RL_PLUGINS_URL . 'categories_tree/static/style.css');
    }

    /**
     * @hook  ajaxRequest
     * @since 2.1.0
     */
    public function hookAjaxRequest(&$out, $mode = '', $item = '', $request_lang = '')
    {
        global $lang;

        if ($mode == 'ctreeOpen') {
            $lang = $GLOBALS['rlLang']->getLangBySide('frontEnd', $request_lang ?: RL_LANG_CODE);

            if ($data = $this->ajaxOpen((int) $item['id'], $item['type'], (int) $item['cat_id'])) {
                $out = array('status' => 'OK', 'data' => $data);
            } else {
                $out = array('status'  => 'ERROR', 'message' => $lang['category_tree_get_cats_notify_fail']);
            }
        }
    }

    /**
     * @hook  apExtPluginsUpdate
     * @since 2.1.2
     */
    public function hookApExtPluginsUpdate()
    {
        global $updateData, $rlDb;

        $status   = $updateData['fields']['Status'];
        $pluginID = (int) $rlDb->getOne('ID', "`Key` = 'categories_tree'", 'plugins');

        if ($status && $pluginID !== (int) $updateData['where']['ID']) {
            return;
        }

        // Stay the hook is active to have ability update hook of AllInOne package
        $rlDb->query(
            "UPDATE `" . RL_DBPREFIX . "hooks` SET `Status` = 'active'
            WHERE `Name` = 'apExtPluginsUpdate' AND `Plugin` = 'categories_tree'"
        );

        if ($status == 'approval') {
            $this->updateCombinedCategoriesBox(true);
        } else {
            $this->updateCombinedCategoriesBox(false);
        }
    }
}
