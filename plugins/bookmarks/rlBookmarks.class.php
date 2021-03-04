<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : RLBOOKMARKS.CLASS.PHP
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

class rlBookmarks
{
    /**
    * @var services     - Available social networks by AddThis 
    * @deprecated 4.0.0 - Generates in rlBookmarksAdmin::getServices()
    **/
    public $services = array();

    /**
    * @var bookmarks    - Bookmark types
    * @deprecated 4.0.0 - Only two share button types are avilable now, "floating" and "inline"
    **/
    public $bookmarks = array();
    
    /**
     * Plugin installer
     */
    public function install()
    {
        global $rlDb;

        $sql = "
            CREATE TABLE IF NOT EXISTS `" . RL_DBPREFIX . "bookmarks` (
            `ID` INT NOT NULL AUTO_INCREMENT,
            `Key` VARCHAR(30) NOT NULL,
            `Type` enum('floating_bar','inline') NOT NULL DEFAULT 'inline',
            `Service_type` enum('automatic','custom') NOT NULL DEFAULT 'automatic',
            `Services` MEDIUMTEXT NOT NULL,
            `Align` ENUM('left', 'center', 'right') DEFAULT 'center' NOT NULL,
            `Theme` ENUM('light','dark','gray','transparent') DEFAULT 'transparent' NOT NULL,
            `View_mode` ENUM('large','medium','small') NOT NULL DEFAULT 'medium',
            `Share_type` ENUM('none','each','one','both') NOT NULL DEFAULT 'none',
            `Share_style` ENUM('responsive','fixed','original') NOT NULL DEFAULT 'responsive',
            PRIMARY KEY (`ID`)
        ) CHARSET=utf8;
        ";
        $rlDb->query($sql);

        $sql = "
            INSERT INTO `" . RL_DBPREFIX . "bookmarks` 
            (`Key`, `Type`, `Service_type`, `Services`, `Align`, `Theme`, `View_mode`, `Share_type`) VALUES 
            ('bookmark_floating_bar_1', 'floating_bar', 'automatic', '5', '', 'transparent', 'medium', 'one')
        ";
        $rlDb->query($sql);

        $rlDb->query("DELETE FROM `" . RL_DBPREFIX . "lang_keys` WHERE `Key` = 'blocks+name+bookmark_floating_bar_1'");
    }

    /**
     * Plugin un-installer
     */
    public function uninstall()
    {
        $GLOBALS['rlDb']->query("DROP TABLE IF EXISTS `" . RL_DBPREFIX . "bookmarks`");
    }

    /**
     * Modify plugin boxes
     * 
     * @since 4.0.0
     * @hook specialBlock
     */
    public function hookSpecialBlock()
    {
        global $blocks, $config;

        $register = false;

        foreach ($blocks as $block) {
            // Floating bar handler
            if (strpos($block['Key'], 'bookmarks_floating_bar') === 0) {
                $block['Side'] = 'hidden';
                $GLOBALS['rlCommon']->defineBlocksExist($blocks);
                $register = true;
                break;
            }

            if ($block['Plugin'] == 'bookmarks') {
                $register = true;
            }
        }

        if ($register) {
            $url = 'https://s7.addthis.com/js/300/addthis_widget.js';
            if ($config['bookmarks_addthis_id']) {
                $url .= '#pubid=' . $config['bookmarks_addthis_id'];
            }
            $GLOBALS['rlStatic']->addJS($url);
        }
    }

    /**
     * Display floating bar and add prefered dns
     * 
     * @since 4.0.0
     * @hook tplHeader
     */
    public function hookTplHeader()
    {
        global $blocks, $rlSmarty;

        // Add prefered dns
        echo PHP_EOL . '<link rel="dns-prefetch" href="//s7.addthis.com" />';

        // Floating bar handler
        foreach ($blocks as $key => $block) {
            if (0 === strpos($block['Key'], 'bookmark_floating_bar_')) {
                $rlSmarty->_compile_source(
                    'evaluated template',
                    $block['Content'],
                    $_var_compiled
                );

                ob_start();
                $rlSmarty->_eval('?>' . $_var_compiled);
                echo ob_get_clean();

                unset($blocks[$key]);
                break;
            }
        }

        // Add custom floating bar styles
        if ($_var_compiled) {
            $styles = <<< CSS
            <style>
            .at-custom-sidebar-counter {
                padding-bottom: 4px;
            }
            body.bookmarks-theme-light .at-custom-sidebar-counter {
                background: white;
            }
            body.bookmarks-theme-gray .at-custom-sidebar-counter {
                background: #f2f2f2;
            }
            body.bookmarks-theme-dark .at-custom-sidebar-counter {
                background: #262b30;
            }
            body.bookmarks-theme-dark .at4-share .at-custom-sidebar-count {
                color: #eeeeee;
            }
            </style>
CSS;
            echo $styles;
        }
    }

    /**
     * @deprecated 4.0.0 - Method moved to rlBookmarksAdmin class
     */
    public function ajaxDeleteBookmark()
    {}
}
