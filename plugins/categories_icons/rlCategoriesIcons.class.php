<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : RLCATEGORIESICONS.CLASS.PHP
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

class rlCategoriesIcons extends reefless 
{
	/**
    * Delete category icon
    * @param string $category_key
    * @param string $object
    */
	public function ajaxDeleteIcon($key = '', $object = 'category')
	{
		global $_response;

		$GLOBALS['rlValid']->sql($key);
		$_response->setCharacterEncoding('UTF-8');
        $table = $object == 'category' ? 'categories' : 'listing_types';
		$icon = $this->getOne('Icon', "`Key` = '{$key}'", $table);

		$update_info = array(
			'fields' => array('Icon' => ''),
			'where' => array('Key' => $key)
		);

		$this->loadClass('Actions');
		$GLOBALS['rlActions']->updateOne($update_info, $table);

		if (!empty($icon)) {
			@unlink(RL_FILES . $icon);
			@unlink(RL_FILES . str_replace('icon', 'icon_original', $icon));
	    }
        if ($object == 'category') {
		    $GLOBALS['rlCache']->updateCategories();
        }
		$_response->script("$('#gallery').slideUp('normal');");
		$_response->script("$('#fileupload').html(null);");
		$_response->script("printMessage('notice','{$GLOBALS['lang']['category_icon_icon_deleted']}');");

	    return $_response;
	}

	/**
    * Update icons after change icon sizes
    * @param int $width
    * @param int $height
    */
	public function updateIcons($width = 0, $height = 0)
	{
		if ($width > 0 && $height > 0) {
            $this->loadClass('Resize');
            $this->loadClass('Crop');

            // get categories
            $sql = "SELECT `ID`, `Icon` FROM `". RL_DBPREFIX ."categories` ";
            $sql .= "WHERE `Icon` <> '' AND `Status` <> 'trash'";
	        $categories = $this->getAll($sql);
			if (!empty($categories)) {   
				foreach($categories as $key => $category) {
					$this->resizeIcon($category, $width, $height);
				}
			}
            // get listing types
            if (version_compare($GLOBALS['config']['rl_version'], '4.5.1') >= 0) {
                $sql = "SELECT `ID`, `Icon` FROM `". RL_DBPREFIX ."listing_types` ";
                $sql .= "WHERE `Icon` <> '' AND `Status` <> 'trash'";
                $listing_types = $this->getAll($sql);
                if (!empty($listing_types)) {
                    foreach($listing_types as $type) {
                        $this->resizeIcon($type, $width, $height);
                    }
                }
            }
            unset($categories, $listing_types);
		}
	}
    
    /**
    * Resize icon
    * @param array $item
    * @param int $width
    * @param int $height
    */
    public function resizeIcon($item = array(), $width = 0, $height = 0)
    {
        global $rlCrop, $rlResize, $config;

        if (!empty($item['Icon'])) {
            $original = RL_FILES . str_replace("icon", "icon_original", $item['Icon']);
            $icon_name = $item['Icon'];
            $icon_file = RL_FILES . $icon_name;

            if ($config['icon_crop_module']) {
                $rlCrop->loadImage($original);
                $rlCrop->cropBySize($width, $height, ccCENTER);
                $rlCrop->saveImage($icon_file, $config['img_quality']);
                $rlCrop->flushImages();

                $rlResize->resize($icon_file, $icon_file, 'C', array($width, $height));
            } else {
                $rlResize->resize($original, $icon_file, 'C', array($width, $height), null, false);
            }

            if (is_readable($icon_file)) {
                chmod($icon_file, 0644);
            }
        }    
    }

	/**
    * Check if uploaded file is image type
    * @param mixed $image
    */
	public function isImage($image = false)
	{
		if (!$image) {
			return false;
		}
		$allowed_types = array(
			'image/gif',
			'image/jpeg',
			'image/jpg',
			'image/png'
		);

		$img_details = getimagesize($image);
		if (in_array($img_details['mime'], $allowed_types)) {
			return true;
		}
		return false;
	}
    
    /**
    * @hook getCategoriesModifySelect
    * @since 2.2.0
    */
    public function hookGetCategoriesModifySelect()
    {
        $GLOBALS['select'][] = 'Icon';    
    }
    
    /**
    * @hook tplPreCategory
    * @since 2.2.0
    */
    public function hookTplPreCategory()
    {
        $GLOBALS['rlSmarty']->display(RL_PLUGINS . 'categories_icons' . RL_DS . 'icons_pre_category.tpl');    
    }
    
    /**
    * @hook tplPostCategory
    * @since 2.2.0
    */
    public function hookTplPostCategory()
    {
        $GLOBALS['rlSmarty']->display(RL_PLUGINS . 'categories_icons' . RL_DS . 'icons_post_category.tpl');
    }
    
    /**
    * @hook tplPreSubCategory
    * @since 2.2.0
    */
    public function hookTplPreSubCategory()
    {
        $GLOBALS['rlSmarty']->display(RL_PLUGINS . 'categories_icons' . RL_DS . 'subcat_icons.tpl');
    }
    
    /**
    * @hook apTplCategoriesForm
    * @since 2.2.0
    */
    public function hookApTplCategoriesForm()
    {
        $GLOBALS['rlSmarty']->display(RL_PLUGINS . 'categories_icons' . RL_DS .  'admin' . RL_DS . 'add_category_block.tpl');
    }
    
    /**
    * @hook apPhpCategoriesBeforeAdd
    * @since 2.2.0
    */
    public function hookApPhpCategoriesBeforeAdd()
    {
        $this->uploadIcon($_FILES['icon']);
    }
    
    /**
    * @hook apPhpCategoriesBeforeEdit
    * @since 2.2.0
    */
    public function hookApPhpCategoriesBeforeEdit()
    {
        global $category_info;

        $this->uploadIcon($_FILES['icon'], $category_info);
    }
    
    /**
    * @hook apPhpCategoriesBottom
    * @since 2.2.0
    */
    public function hookApPhpCategoriesBottom()
    {
        $GLOBALS['reefless']->loadClass('CategoriesIcons' , null, 'categories_icons'); 
        $GLOBALS['rlXajax']->registerFunction(array('deleteIcon', $GLOBALS['rlCategoriesIcons'], 'ajaxDeleteIcon'));
    }
    
    /**
    * @hook apPhpCategoriesPost
    * @since 2.2.0
    */
    public function hookApPhpCategoriesPost()
    {
        global $category_info;

        $_POST['icon'] = $category_info['Icon'];
    }
    
    /**
    * @hook apPhpConfigAfterUpdate
    * @since 2.2.0
    */
    public function hookApPhpConfigAfterUpdate()
    {
        global $dConfig;

        if(!empty($dConfig['categories_icons_width']['value']) && !empty($dConfig['categories_icons_height']['value'])) {
            $this->updateIcons((int)$dConfig['categories_icons_width']['value'], (int)$dConfig['categories_icons_height']['value']);
        }
    }
    
    /**
    * @hook apPhpConfigAfterUpdate
    * @since 2.2.0
    */
    public function hookApTplListingTypesForm()
    {
        if (version_compare($GLOBALS['config']['rl_version'], '4.5.1') < 0) {
            return;
        }
        $GLOBALS['rlSmarty']->display(RL_PLUGINS . 'categories_icons' . RL_DS . 'admin' . RL_DS . 'add_category_block.tpl');
    }
    
    /**
    * @hook apPhpListingTypesPost
    * @since 2.2.0
    */
    public function hookApPhpListingTypesPost()
    {
        if (version_compare($GLOBALS['config']['rl_version'], '4.5.1') < 0) {
            return;
        }
        global $type_info;
        $_POST['icon'] = $type_info['Icon'];
    }
    
    /**
    * @hook apPhpListingTypesBeforeAdd
    * @since 2.2.0
    */
    public function hookApPhpListingTypesBeforeAdd()
    {
        if (version_compare($GLOBALS['config']['rl_version'], '4.5.1') < 0) {
            return;
        }
        $this->uploadIcon($_FILES['icon'], false, 'type');
    }
    
    /**
    * @hook apPhpListingTypesBeforeEdit
    * @since 2.2.0
    */
    public function hookApPhpListingTypesBeforeEdit()
    {
        if (version_compare($GLOBALS['config']['rl_version'], '4.5.1') < 0) {
            return;
        }
        global $type_info;

        $this->uploadIcon($_FILES['icon'], $type_info, 'type');
    }
    
    /**
    * @hook apTplListingTypesAction
    * @since 2.2.0
    */
    public function hookApTplListingTypesAction()
    {
        if (version_compare($GLOBALS['config']['rl_version'], '4.5.1') < 0 
            || version_compare($GLOBALS['config']['rl_version'], '4.5.1') > 0) {
            return;
        }
        echo <<< FL
<script>
    $(document).ready(function(){
        $('form').each(function() {
            if (!$(this).attr('enctype')) {
                $(this).attr('enctype', 'multipart/form-data');
            }
        });
    });
</script>
FL;
    }
    
    /**
    * @hook apPhpListingTypesBottom
    * @since 2.2.0
    */
    public function hookApPhpListingTypesBottom()
    {
        $GLOBALS['reefless']->loadClass('CategoriesIcons' , null, 'categories_icons'); 
        $GLOBALS['rlXajax']->registerFunction(array('deleteIcon', $GLOBALS['rlCategoriesIcons'], 'ajaxDeleteIcon'));
    }
    
    /**
    * Upload icon to server
    * @param array $icon
    * @param array $item_info
    * @param string $type
    */
    public function uploadIcon($icon, $item_info = array(), $type = 'category')
    {
        global $data, $update_date, $update_data, $config, $rlCrop, $rlResize;

        if (!empty($icon['tmp_name']) && $this->isImage($icon['tmp_name'])) {
            $this->loadClass('Resize');
            $this->loadClass('Crop');
            
            if ($item_info['Icon']) {
                unlink(RL_FILES . $item_info['Icon']);
                unlink(RL_FILES . str_replace("icon", "icon_original", $item_info['Icon']));
            }

            $file_ext = explode('.', $icon['name']);
            $file_ext = array_reverse($file_ext);
            $file_ext = '.' . $file_ext[0];

            $tmp_location = RL_UPLOAD.'tmp_listing' . mt_rand() . time() . $file_ext;

            if(move_uploaded_file($icon['tmp_name'], $tmp_location)) {
                chmod($tmp_location, 0777);

                $icon_name = $type . '_icon_' . mt_rand() . time() . $file_ext;

                $icon_original = str_replace("icon", "icon_original", $icon_name);
                copy($tmp_location, RL_FILES . $icon_original);

                $icon_file = RL_FILES . $icon_name;

                if($config['icon_crop_module']) {
                    $rlCrop->loadImage($tmp_location);
                    $rlCrop->cropBySize($config['categories_icons_width'], $config['categories_icons_height'], ccCENTER);
                    $rlCrop->saveImage($icon_file, $config['img_quality']);
                    $rlCrop->flushImages();

                    $rlResize->resize($icon_file, $icon_file, 'C', array($config['categories_icons_width'], $config['categories_icons_height']));
                } else {
                    $rlResize->resize($tmp_location, $icon_file, 'C', array($config['categories_icons_width'], $config['categories_icons_height']), null, false);   
                }

                unlink($tmp_location);

                if (is_readable($icon_file)) {
                    chmod($icon_file, 0644);
                    if (isset($update_date['fields'])) {
                        $update_date['fields']['Icon'] = $icon_name;
                    } elseif ($update_data['fields']) {
                        $update_data['fields']['Icon'] = $icon_name;
                    } else {
                        $data['Icon'] = $icon_name;
                    }
                }
            }
        }
    }
}