<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : ESCORT.PHP
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

namespace Flynax\Plugins\ExportImport\Handlers;

/**
 * Class Escort
 *
 * @since 3.6.0
 * @package Flynax\Plugins\ExportImport\Handlers
 */
class Escort
{
    /**
     * Export escort availability
     *
     * @param  array        $listingData - Listing info array
     * @return bool|string  $fields      - Prepared to export string | False if something went wrong
     */
    public static function exportAvailability($listingData = array())
    {
        if (empty($listingData)) {
            return false;
        }
        
        $fields = array();
        foreach ($listingData as $key => $data) {
            if (strpos($key, 'availability') !== false) {
                $fields[$key] = $data;
            }
        }
    
        return !empty($fields) ? implode(',', $fields) : '';
    }
    
    /**
     * Prepare export value of the Escort Rates
     *
     * @param  int $listingID
     * @return string          - Prepared to export string
     */
    public static function exportEscortRates($listingID = 0)
    {
        $ratesJson = '';
        if (!$listingID) {
            return $ratesJson;
        }
    
        $sql = "SELECT * FROM `" . RL_DBPREFIX . "escort_rates` WHERE `Listing_ID` = {$listingID} ";
        $rates = $GLOBALS['rlDb']->getAll($sql);
    
        if (!empty($rates)) {
            $ratesJson = json_encode($rates);
        }
    
        return $ratesJson;
    }
    
    /**
     * Prepare export string of the Escort Tours
     * @param  int $listingID
     * @return string|bool          - Prepared to export string | False if something went wrong
     */
    public static function exportTours($listingID = 0)
    {
        if (!$listingID) {
            return false;
        }
        
        $toursJson = '';
        
        $sql = "SELECT * FROM `" . RL_DBPREFIX . "escort_tours` ";
        $sql .= "WHERE `Listing_ID` = " . $listingID;
        $tours = $GLOBALS['rlDb']->getAll($sql);
        
        if (!empty($tours)) {
            $toursJson = json_encode($tours);
        }
        
        return $toursJson;
    }
    
    /**
     * Import prepared availability string to new listing
     *
     * @param string $availabilityString
     * @param array  $insertData         - Inserting listing data
     */
    public static function importAvailability($availabilityString, &$insertData)
    {
        $availabilities = explode(',', $availabilityString);
        foreach ($availabilities as $arrayKey => $availability) {
            $dataKey = 'availability';
            if ($arrayKey) {
                $dataKey .= '_' . ($arrayKey - 1);
            }
            $insertData[$dataKey] = $availability;
        }
    }
    
    /**
     * Decode prepared rates json and import it to the Escort Rates table
     *
     * @param int $importListingID
     * @param string $jsonString - Prepared to import json (using exportEscortRates method)
     */
    public static function importEscortRates($importListingID, $jsonString)
    {
        $data = json_decode($jsonString, true);
        
        foreach ($data as $key => $value) {
            $newData = array(
                'Listing_ID' => $importListingID,
                'Rate' => $value['Rate'],
                'Custom' => $value['Custom'],
                'Price' => $value['Price'],
            );
            $GLOBALS['rlActions']->insertOne($newData, 'escort_rates');
        }
    }
    
    /**
     * Decode prepared tours json and import it to the Escort Tours table
     *
     * @param int $importListingID
     * @param string $jsonString - Prepared to import json (using exportEscortTours method)
     */
    public static function importEscortTours($importListingID, $jsonString)
    {
        $data = json_decode($jsonString, true);
        
        foreach ($data as $key => $value) {
            $newData = array(
                'Listing_ID' => $importListingID,
                'Location' => $value['Location'],
                'Latitude' => $value['Latitude'],
                'Longitude' => $value['Longitude'],
                'Place_ID' => $value['Place_ID'],
                'From' => $value['From'],
                'To' => $value['To'],
            );
            $GLOBALS['rlActions']->insertOne($newData, 'escort_tours');
        }
    }
    
    /**
     * Checking is current installation is escort*
     * @return bool
     */
    public static function isEscortInstallation()
    {
        return file_exists(RL_CLASSES . 'rlEscort.class.php');
    }
}
