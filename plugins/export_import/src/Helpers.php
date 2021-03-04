<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : HELPERS.PHP
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

namespace Flynax\Plugins\ExportImport;

/**
 * Class Helpers
 * @since 3.7.0
 */
class Helpers
{
    /**
     * Checking does multifield plugin has been installed and activated
     *
     * @return bool
     */
    public static function isMultiFieldInstalled()
    {
        $GLOBALS['reefless']->loadClass('MultiField', null, 'multiField');

        if (is_object($GLOBALS['rlSmarty']) && self::isNewMultifield()) {
            $GLOBALS['rlSmarty']->assign('multi_format_keys', $GLOBALS['rlMultiField']->formatKeys);
        }

        return isset($GLOBALS['plugins']['multiField']);
    }

    /**
     * Defines is the Multifield plugin has new data structure (v2.2.0)
     *
     * @since 3.7.1
     *
     * @return boolean
     */
    public static function isNewMultifield()
    {
        return version_compare($GLOBALS['plugins']['multiField'], '2.2.0', '>=');
    }

    /**
     * Get multiformat data
     *
     * @since 3.7.1
     *
     * @return array - Multiformat data
     */
    public static function getMultiFormats()
    {
        global $rlDb, $reefless;

        $multi_formats = [];

        $rlDb->setTable('multi_formats');
        $rlDb->outputRowsMap = 'Key';

        if (self::isNewMultifield()) {
            $multi_formats = $rlDb->fetch('*', ['Parent_ID' => '0']);
        } else {
            $multi_formats = $rlDb->fetch();
        }

        return $multi_formats;
    }
}
