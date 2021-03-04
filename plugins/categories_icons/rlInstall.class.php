<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : RLINSTALL.CLASS.PHP
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

class rlInstall extends reefless
{    
	/**
	* Install plugin
	*/
	public function install()
	{
		$this->query("ALTER TABLE `" . RL_DBPREFIX . "categories` ADD `Icon` varchar(255) NOT NULL;");
		$this->query("ALTER TABLE `" . RL_DBPREFIX . "listing_types` ADD `Icon` varchar(255) NOT NULL;");

		$GLOBALS['rlCache']->updateCategories();
	}
	
	/**
	* Uninstall plugin
	*/
	public function uninstall()
	{
		// remove category icons
		$cats = $this->fetch(array('Icon'), null, "WHERE `Icon` <> ''", null, 'categories');

		foreach($cats as $key => $value) {
			unlink(RL_FILES . $value['Icon']);
			unlink(RL_FILES . str_replace("icon", "icon_original", $value['Icon']));
		}

		$this->query("ALTER TABLE `" . RL_DBPREFIX . "categories` DROP `Icon`");
		$GLOBALS['rlCache']->updateCategories();
		
		// remove listing type icons
		if ($this->isExsistField('listing_types')) {
			$listing_types = $this->fetch(array('Icon'), null, "WHERE `Icon` <> ''", null, 'listing_types');

			foreach($listing_types as $key => $value) {
				unlink(RL_FILES . $value['Icon']);
				unlink(RL_FILES . str_replace("icon", "icon_original", $value['Icon']));
			}
			$this->query("ALTER TABLE `" . RL_DBPREFIX . "listing_types` DROP `Icon`");
		}
	}
	
	/**
	* Check if icon field exist in table
	* @param string $table
	*/
	public function isExsistField($table)
	{
		$sql = "SHOW COLUMNS FROM `" . RL_DBPREFIX . $table . "`";
		$list_fields = $this->getAll($sql);
		
		foreach ($list_fields as $lfKey => $lfValue) {
			$fields[] = $lfValue['Field'];
		}
		if (in_array('Icon', $fields)) {
			return true;
		}
		return false;
	}
}

