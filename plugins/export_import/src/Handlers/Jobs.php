<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : JOBS.PHP
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

use Flynax\Classes\ProfileThumbnailUpload;
use Flynax\Utils\Profile;
use Flynax\Plugins\ExportImport\Adapters\ProfileThumbnailUploadAdapter;

/**
 * Class Jobs
 *
 * @since   3.6.0
 * @package Flynax\Plugins\ExportImport\Handlers
 */
class Jobs
{
    /**
     * @var string - Account uploading directory
     */
    protected $mediaDirName = 'account-media';

    /**
     * Import Job resume image
     *
     * @param  int    $newListingID - To which listing will be attached uploaded images
     * @param  int    $accountID    - To which account will be attached uploaded images
     * @param  string $photo        - Original photo url
     * @return bool                 - Does import process successful
     */
    public function import($newListingID = 0, $accountID = 0, $photo = '')
    {
        if (!$newListingID || !$photo || !$accountID) {
            return false;
        }

        $photos = explode(',', $photo);
        $firstPhoto = $photos[key($photos)];
        $thumbnailUploader = new ProfileThumbnailUploadAdapter();

        $thumbnailUploader->setCurrentUploadingType(ProfileThumbnailUploadAdapter::TYPE_BEFORE_461);

        if (method_exists(ProfileThumbnailUpload::class, 'buildName')) {
            $thumbnailUploader->setCurrentUploadingType(ProfileThumbnailUploadAdapter::TYPE_BEFORE_470);

            if (method_exists(Profile::class, 'updateData')) {
                $thumbnailUploader->setCurrentUploadingType(ProfileThumbnailUploadAdapter::TYPE_FROM_470);
            }
        }

        return $thumbnailUploader->uploadImageToAccount($firstPhoto, $accountID, $newListingID);
    }

    /**
     * Prepare export info of the Job listing
     *
     * @param  array $listingInfo - Exporting listing info
     * @return string             - Full URL to the Main Image photo
     */
    public function export($listingInfo)
    {
        $accountInfo = $GLOBALS['rlAccount']->getProfile((int) $listingInfo['Account_ID']);
        $url = '';

        if ($accountInfo['Photo_original']) {
            $url = sprintf('%s%s', RL_FILES_URL, $accountInfo['Photo_original']);
        }

        return $url;
    }

    /**
     * Does provided listing is belongs to Job listing type
     *
     * @param  array $listingInfo - Listing info
     * @return bool
     */
    public static function isBelongsToJob($listingInfo = array())
    {
        if (!is_array($listingInfo) || empty($listingInfo)) {
            return false;
        }

        $mainPhoto = pathinfo($listingInfo['Main_photo']);
        $accountInfo = $GLOBALS['rlAccount']->getProfile((int) $listingInfo['Account_ID']);
        $accountPhoto = pathinfo($accountInfo['Photo']);

        return ($mainPhoto['basename'] == $accountPhoto['basename']) ? true : false;
    }

    /**
     * Does provided category is belong to the Listing Type
     *
     * @param int $categoryID
     * @return bool
     */
    public static function isBelongsToJobListingType($categoryID = 0)
    {
        if (!$categoryID) {
            return false;
        }

        global $rlCategories;

        $categoriesTree = $rlCategories->getParentIDs($categoryID);
        $categoriesTree[] = $categoryID;
        $isBelongsToJobListingType = false;

        foreach ($categoriesTree as $category) {
            $info = $rlCategories->getCategory($category);
            if ($info['Type'] == 'jobs') {
                $isBelongsToJobListingType = true;
                break;
            }
        }

        return $isBelongsToJobListingType;
    }
}
