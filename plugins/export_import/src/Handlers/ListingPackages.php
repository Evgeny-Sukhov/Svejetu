<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : LISTINGPACKAGES.PHP
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
 * Class ListingPackages
 *
 * @since   3.6.0
 * @package Flynax\Plugins\ExportImport\Handlers
 */
class ListingPackages
{
    /**
     * Class is working with this table
     */
    const WORKING_TABLE = 'listing_packages';

    /**
     * @var int - Account_ID in the table
     */
    private $userID;

    /**
     * @var int - Plan_ID in the table
     */
    private $plandID;

    /**
     * @var array - Actual data for user and plan from the Listing Packages table
     */
    private $packageUsingRow;

    /**
     * ListingPackages constructor.
     *
     * @param int $userID
     * @param int $planID
     *
     * @throws \Exception
     */
    public function __construct($userID, $planID)
    {
        if (!$userID || !$planID) {
            throw new \Exception("[Export/Import]: Package ID or user doesn't provided");
        }

        $this->userID = $userID;
        $this->plandID = $planID;

        $this->packageUsingRow = $this->getListingPackageRow();
    }

    /**
     * Method is checking does user has been ever used this package before
     *      if not, plugin is creating new Listing package row to allow import process
     */
    public function checkUsageRowExisting()
    {
        if (!$this->isUsageRowExistInDb($this->userID, $this->plandID)) {
            $this->createUsageRow($this->userID, $this->plandID);
        }
    }


    /**
     * Reduce of the listing package using
     *
     * @param  int $userID
     * @param  int $planID
     *
     * @return array       - What listing type counter were reduced (featured or not)
     */
    public function reduceListingUsage($userID, $planID)
    {
        $reduced = array();
        if ($this->isFeaturedListingsRemains($userID, $planID)) {
            $reduced['featured'] = true;
            $this->reduceFeaturedListingUsage();

            return $reduced;
        }

        $reduced['featured'] = false;
        $this->reduceStandardListingUsage();

        return $reduced;
    }

    /**
     * Does featured listings is remained for active user and active plan
     *
     * @param int $userID
     * @param int $planID
     *
     * @return bool
     */
    public function isFeaturedListingsRemains($userID, $planID)
    {
        $fieldToGet = 'Featured_remains';

        if (!empty($this->packageUsingRow)) {
            return (bool) $this->packageUsingRow[$fieldToGet];
        }

        return (bool) $this->getOneFromListingPackagesTable('Featured_remains', $userID, $planID);
    }

    /**
     * Getting column value from working table by user and plan
     *
     * @param string $rowName
     * @param int    $userID
     * @param int    $planID
     *
     * @return bool|string
     */
    public function getOneFromListingPackagesTable($rowName, $userID, $planID)
    {
        if (!$userID || !$planID) {
            return false;
        }

        $where = "`Account_ID` = {$userID} AND `Plan_ID` = {$planID} ";

        return $GLOBALS['rlDb']->getOne($rowName, $where, 'listing_packages');
    }

    /**
     * Reduce default listings usage for the user
     *
     * @return bool
     */
    public function reduceStandardListingUsage()
    {
        $defaultListingsRemains = (int) $this->packageUsingRow['Standard_remains'];
        $totalListingsRemains = (int) $this->packageUsingRow['Listings_remains'];

        $newData = array(
            'Standard_remains' => ($defaultListingsRemains > 0) ? $defaultListingsRemains - 1 : 0,
            'Listings_remains' => ($totalListingsRemains > 0) ? $totalListingsRemains - 1 : 0,
        );

        return $this->updateListingPackageRow($newData);
    }

    /**
     * Reduce featured listings usage for user by plan
     *
     * @return bool
     */
    public function reduceFeaturedListingUsage()
    {
        $featuredListingsRemains = (int) $this->packageUsingRow['Featured_remains'];
        $totalListingsRemains = (int) $this->packageUsingRow['Listings_remains'];

        $newData = array(
            'Featured_remains' => ($featuredListingsRemains > 0) ? $featuredListingsRemains - 1 : 0,
            'Listings_remains' => ($totalListingsRemains > 0) ? $totalListingsRemains - 1 : 0,
        );

        return $this->updateListingPackageRow($newData);
    }

    /**
     * Update working table with provided data
     *
     * @param array $newData - New data which you want to apply
     * @param int   $id      - In which row do you want to change provided data
     *
     * @return bool          - Changing process result
     */
    public function updateListingPackageRow($newData, $id = 0)
    {
        $id = $id ?: $this->packageUsingRow['ID'];

        if (empty($newData) || !$id) {
            return false;
        }

        $update = array(
            'fields' => $newData,
            'where' => array(
                'ID' => $id,
            ),
        );

        if ($GLOBALS['rlActions']->updateOne($update, self::WORKING_TABLE)) {
            return $this->getListingPackageRow();
        }
    }

    /**
     * Does user used provided package before
     *
     * @param int $userID
     * @param int $planID
     * @return bool
     */
    public function isUsageRowExistInDb($userID = 0, $planID = 0)
    {
        $userID = $userID ?: $this->userID;
        $planID = $planID ?: $this->plandID;

        if (!$userID || !$planID) {
            return false;
        }

        return (bool) $this->getListingPackageRow();
    }

    /**
     * Getting data from the working table by user and plan info
     *
     * @return mixed
     */
    public function getListingPackageRow()
    {
        $sql = "SELECT * FROM `" . RL_DBPREFIX . "listing_packages` ";
        $sql .= "WHERE `Account_ID` = {$this->userID} AND `Plan_ID` = {$this->plandID} ";

        $planUsage = $GLOBALS['rlDb']->getAll($sql);
        if (count($planUsage) == 1) {
            return reset($planUsage);
        }

        $sql .= " AND `Listings_remains` <> 0 ";

        return $GLOBALS['rlDb']->getRow($sql);
    }

    /**
     * Getting summ of Package using of the user
     *  Listings_remains: Total listings remains by provided package
     *  Standard_remains: Standart listings remains by provided package
     *  Featured_remains: Featured listings remains by provided package
     *
     * @return array - Package using information
     */
    public function getPackageUsageInfo()
    {
        $sql = "SELECT SUM(`Listings_remains`) as `Listings_remains`, SUM(`Standard_remains`) as `Standard_remains`, ";
        $sql .= "SUM(`Featured_remains`) as `Featured_remains` FROM `" . RL_DBPREFIX . "listing_packages` ";
        $sql .= "WHERE `Account_ID` = {$this->userID} AND `Plan_ID` = {$this->plandID} GROUP BY `Plan_ID` ";

        return $GLOBALS['rlDb']->getRow($sql);
    }


    /**
     * Create new Listing Package row for user and plan if data doesn't exist in the table.
     *
     * @param int $userID
     * @param int $planID
     *
     * @return bool
     */
    public function createUsageRow($userID, $planID)
    {
        if (!$userID || !$userID) {
            return false;
        }

        $GLOBALS['reefless']->loadClass('Plan');
        $packageInfo = $GLOBALS['rlPlan']->getPlan($planID);

        $newRow = array(
            'Listings_remains' => $packageInfo['Listing_number'],
            'Standard_remains' => $packageInfo['Standard_listings'],
            'Featured_remains' => $packageInfo['Featured_listings'],
            'Plan_ID' => $packageInfo['ID'],
            'Account_ID' => $userID,
            'Type' => 'package',
            'IP' => $GLOBALS['reefless']->getClientIpAddress(),
            'Count_used' => 0,
        );

        $this->packageUsingRow = $newRow;

        return (bool) $GLOBALS['rlActions']->insertOne($newRow, 'listing_packages');
    }

    /**
     * Can Plugin import more listing to the active plan for active user
     *
     * @return bool
     */
    public function isLimitExceeded()
    {
        if ($this->packageUsingRow) {
            return (bool) !$this->packageUsingRow['Listings_remains'];
        }

        return (bool) !$this->getOneFromListingPackagesTable('Listings_remains', $this->userID, $this->plandID);
    }

    /**
     * Does provided plan is a single plan
     *
     * @param  int $planID
     * @return bool
     */
    public static function isSingleListingPackage($planID)
    {
        if (!$planID) {
            return false;
        }

        $where = sprintf('`ID` = %s', $planID);
        $planType = $GLOBALS['rlDb']->getOne('Type', $where, 'listing_plans');

        return $planType == 'listing';
    }
}
