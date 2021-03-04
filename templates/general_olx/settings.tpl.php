<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LICENSE: FL7YNR66E9FU - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN: svejetu.me
 *	FILE: SETTINGS.TPL.PHP
 *
 *	The software is a commercial product delivered under single, non-exclusive,
 *	non-transferable license for one domain or IP address. Therefore distribution,
 *	sale or transfer of the file in whole or in part without permission of Flynax
 *	respective owners is considered to be illegal and breach of Flynax License End
 *	User Agreement.
 *
 *	You are not allowed to remove this information from the file without permission
 *	of Flynax respective owners.
 *
 *	Flynax Classifieds Software 2020 |  All copyrights reserved.
 *
 *	http://www.flynax.com/
 *
 ******************************************************************************/

/* template settings */
$tpl_settings = array(
    'type' => 'responsive_42', // DO NOT CHANGE THIS SETTING
    'version' => 1.1,
    'name' => 'general_olx_nova_wide',
    'inventory_menu' => false,
    'category_menu' => true,
    'category_menu_listing_type' => true,
    'right_block' => false,
    'long_top_block' => false,
    'featured_price_tag' => true,
    'ffb_list' => false, //field bound boxes plugins list
    'fbb_custom_tpl' => true,
    'header_banner' => true,
    'header_banner_size_hint' => '728x90',
    'home_page_gallery' => false,
    'autocomplete_tags' => true,
    'category_banner' => true,
    'shopping_cart_use_sidebar' => true,
    'listing_details_anchor_tabs' => true,
    'search_on_map_page' => true,
    'home_page_map_search' => false,
    'browse_add_listing_icon' => false,
    'listing_grid_except_fields' => array('title', 'bedrooms', 'bathrooms', 'square_feet', 'time_frame', 'phone', 'pay_period'),
    'category_dropdown_search' => true,
    'sidebar_sticky_pages' => array('listing_details'),
    'sidebar_restricted_pages' => array('search_on_map'),
    'svg_icon_fill' => true,
    'qtip' => array(
        'background' => '1473cc',
        'b_color'    => '1473cc',
    ),
);

if ( is_object($rlSmarty) ) {
    $rlSmarty->assign_by_ref('tpl_settings', $tpl_settings);
}

// Insert configs and hooks
if (!isset($config['nova_support'])) {
    // set phrases
    $reefless->loadClass('Lang');
    $languages = $rlLang->getLanguagesList();
    $tpl_phrases = array(
        array('admin', 'nova_category_menu', 'Category menu'),
        array('admin', 'nova_category_icon', 'Category Icon'),
        array('admin', 'nova_load_more', 'Load More'),
        array('frontEnd', 'nova_mobile_apps', 'Mobile Apps'),
        array('frontEnd', 'footer_menu_1', 'About Classifieds'),
        array('frontEnd', 'footer_menu_2', 'Help & Contact'),
        array('frontEnd', 'footer_menu_3', 'More Helpful Links'),
        array('frontEnd', 'nova_load_more_listings', 'Load More Listings'),
        array('frontEnd', 'contact_email', 'sales@flynax.com'),
        array('frontEnd', 'phone_number', '+1 (994) 546-1212'),
        array('admin', 'config+name+ios_app_url', 'iOS app url'),
        array('admin', 'config+name+android_app_url', 'Android app url'),
        array('frontEnd', 'nova_newsletter_text', 'Subscribe for our newsletters and stay updated about the latest news and special offers.'),
    );

    // insert template phrases
    foreach ($languages as $language) {
        foreach ($tpl_phrases as $tpl_phrase) {
            if (!$rlDb->getOne('ID', "`Code` = '{$language['Code']}' AND `Key` = '{$tpl_phrase[1]}'", 'lang_keys')) {
                $sql = "INSERT IGNORE INTO `". RL_DBPREFIX ."lang_keys` (`Code`, `Module`, `Key`, `Value`, `Plugin`) VALUES ";
                $sql .= "('{$language['Code']}', '{$tpl_phrase[0]}', '{$tpl_phrase[1]}', '". $rlValid->xSql($tpl_phrase[2])."', 'nova_template');";
                $rlDb->query($sql);
            }
        }
    }

    // Insert configs
    $insert_setting = array(
        array(
            'Group_ID' => 0,
            'Key' => 'nova_support',
            'Default' => 1,
            'Type' => 'text',
            'Plugin' => 'nova_template'
        ),
        array(
            'Group_ID' => 1,
            'Position' => 36,
            'Key' => 'ios_app_url',
            'Default' => 'https://itunes.apple.com/us/app/iflynax/id424570449?mt=8',
            'Type' => 'text',
            'Plugin' => 'nova_template'
        ),
        array(
            'Group_ID' => 1,
            'Position' => 36,
            'Key' => 'android_app_url',
            'Default' => 'https://play.google.com/store/apps/details?id=com.flynax.flydroid&hl=en_US',
            'Type' => 'text',
            'Plugin' => 'nova_template'
        )
    );
    $rlDb->insert($insert_setting, 'config');

    // Create fields
    $rlDb->addColumnToTable('Menu', "ENUM('1','0') NOT NULL DEFAULT '0' AFTER `Status`", 'categories');
    $rlDb->addColumnToTable('Menu_icon', "VARCHAR(100) NOT NULL AFTER `Menu`", 'categories');

    $mapping = array(
        'houses' => 'real-estate-action-house-pin.svg',
        'cars' => 'car-dashboard-steering.svg',
        'electronics' => 'responsive-design-1.svg',
        'vacancies' => 'job-search.svg',
        'resumes' => 'job-seach-profile.svg',
        'boats' => 'sea-transport-sailing-boat.svg',
        'books' => 'book-close-bookmark-1.svg',
        'cameras' => 'camera-1.svg',
        'clothing_and_shoes' => 'dress-2.svg',
        'computers' => 'mouse-smart.svg',
        'home_and_garden' => 'outdoors-tree-gate.svg',
        'car_service_repair' => 'car-repair-engine.svg',
    );

    foreach ($mapping as $category_key => $map_icon) {
        $icon_update = array(
            'fields' => array('Menu' => '1', 'Menu_icon' => $map_icon),
            'where'  => array('Key' => $category_key)
        );
        $rlDb->update($icon_update, 'categories');
    }

    // insert hooks
    $sql = <<< MYSQL
INSERT INTO `{db_prefix}hooks` (`Name`, `Plugin`, `Class`, `Code`, `Status`) VALUES
('apExtCategoriesData', 'nova_template', '', 'if (!strpos(\$GLOBALS[''config''][''template''], ''_nova'') || !\$GLOBALS[''tpl_settings''][''category_menu'']) {\r\n    return;\r\n}\r\n\r\nglobal \$lang;\r\n\r\nforeach (\$GLOBALS[''data''] as &\$item) {\r\n    \$item[''Menu''] = \$item[''Menu''] ? \$lang[''yes''] : \$lang[''no''];\r\n}', 'active'),
('apTplCategoriesGrid', 'nova_template', '', 'if (!strpos(\$GLOBALS[''tpl_settings''][''name''], ''_nova'') || !\$GLOBALS[''tpl_settings''][''category_menu'']) {\r\n    return;\r\n}\r\n\r\necho <<< JAVASCRIPT\r\nlang[''category_menu''] = \"{\$GLOBALS[''lang''][''nova_category_menu'']}\";\r\nvar instance = categoriesGrid.getInstance();\r\n\r\ninstance.fields.push({name: ''Menu'', mapping: ''Menu''});\r\ninstance.columns.splice(4, 0, {\r\n    header: lang[''category_menu''],\r\n    dataIndex: ''Menu'',\r\n    width: 8,\r\n    editor: new Ext.form.ComboBox({\r\n        store: [\r\n            [''1'', lang[''ext_yes'']],\r\n            [''0'', lang[''ext_no'']]\r\n        ],\r\n        displayField: ''value'',\r\n        valueField: ''key'',\r\n        emptyText: lang[''ext_not_available''],\r\n        typeAhead: true,\r\n        mode: ''local'',\r\n        triggerAction: ''all'',\r\n        selectOnFocus: true\r\n    })\r\n});\r\nJAVASCRIPT;', 'active'),
('apTplCategoriesForm', 'nova_template', '', 'if (!strpos(\$GLOBALS[''tpl_settings''][''name''], ''_nova'') || !\$GLOBALS[''tpl_settings''][''category_menu'']) {\r\n    return;\r\n}\r\n\r\n\$GLOBALS[''rlSmarty'']->display(RL_ROOT . ''templates'' . RL_DS . \$GLOBALS[''config''][''template''] . RL_DS . ''icon-manager.tpl'');', 'active'),
('apPhpCategoriesPost', 'nova_template', '', 'if (!strpos(\$GLOBALS[''tpl_settings''][''name''], ''_nova'') || !\$GLOBALS[''tpl_settings''][''category_menu'']) {\r\n    return;\r\n}\r\n\r\n\$_POST[''category_menu''] = \$GLOBALS[''category_info''][''Menu''];\r\n\$_POST[''category_menu_icon''] = \$GLOBALS[''category_info''][''Menu_icon''];', 'active'),
('apAjaxRequest', 'nova_template', '', 'if (\$GLOBALS[''item''] != ''novaGetIcons'') {\r\n    return;\r\n}\r\n\r\n\$limit = 55;\r\n\$start = \$_REQUEST[''start''] ? \$_REQUEST[''start''] * \$limit : 0;\r\n\$dir = RL_ROOT . ''templates/'' . \$GLOBALS[''config''][''template''] . ''/img/icons/'';\r\n\$icons = \$GLOBALS[''reefless'']->scanDir(\$dir);\r\n\r\nif (\$q = \$_REQUEST[''q'']) {\r\n    foreach (\$icons as \$index => \$name) {\r\n        if (!is_numeric(strpos(\$name, \$q))) {\r\n            unset(\$icons[\$index]);\r\n        }\r\n    }\r\n}\r\n\r\n\$total = count(\$icons);\r\n\r\n\$GLOBALS[''out''] = array(\r\n    ''status''  => ''OK'',\r\n    ''results'' => array_slice(\$icons, \$start, \$limit),\r\n    ''total''   => \$total,\r\n    ''next''    => \$total > \$start + \$limit \r\n);', 'active'),
('apPhpCategoriesBeforeEdit', 'nova_template', '', 'if (!strpos(\$GLOBALS[''tpl_settings''][''name''], ''_nova'') || !\$GLOBALS[''tpl_settings''][''category_menu'']) {\r\n    return;\r\n}\r\n\r\nglobal \$update_data;\r\n\r\n\$update_data[''fields''][''Menu''] = \$_POST[''category_menu''];\r\n\$update_data[''fields''][''Menu_icon''] = \$_POST[''category_menu_icon''];', 'active'),
('apPhpCategoriesBeforeAdd', 'nova_template', '', 'if (!strpos(\$GLOBALS[''tpl_settings''][''name''], ''_nova'') || !\$GLOBALS[''tpl_settings''][''category_menu'']) {\r\n    return;\r\n}\r\n\r\nglobal \$data;\r\n\r\n\$data[''Menu''] = \$_POST[''category_menu''];\r\n\$data[''Menu_icon''] = \$_POST[''category_menu_icon''];', 'active'),
('specialBlock', 'nova_template', '', 'if (!\$GLOBALS[''tpl_settings''][''category_menu''] || \$GLOBALS[''page_info''][''Key''] == ''search_on_map'') {\r\n    return;\r\n}\r\n\r\nglobal \$pages, \$rlListingTypes;\r\n\r\n\$category_menu = \$GLOBALS[''rlDb'']->fetch(\r\n    array(''ID'', ''Type'', ''Path'', ''Key'', ''Menu_icon''),\r\n    array(\r\n        ''Menu'' => 1,\r\n        ''Status'' => ''active''\r\n    ),\r\n    \"ORDER BY `Position`\",\r\n    null, ''categories''\r\n);\r\n\$GLOBALS[''rlSmarty'']->assign_by_ref(''category_menu'', \$category_menu);\r\n\r\nforeach (\$category_menu as &\$item) {\r\n    \$href = SEO_BASE;\r\n    \$type = \$rlListingTypes->types[\$item[''Type'']];\r\n\r\n    if (\$GLOBALS[''config''][''mod_rewrite'']) {\r\n        \$href .= \$pages[\$type[''Page_key'']];\r\n        \$href .= ''/'' . \$item[''Path''];\r\n        \$href .= \$type[''Cat_postfix''] ? ''.html'' : ''/'';\r\n    } else {\r\n        \$href .= ''index.php?page='' . \$pages[\$type[''Page_key'']];\r\n        \$href .= ''&category='' . \$item[''ID''];\r\n    }\r\n\r\n    \$item[''href''] = \$href;\r\n    \$item[''icon''] = FL_TPL_ROOT . ''img/icons/'' . \$item[''Menu_icon''];\r\n}\r\n\r\n\$GLOBALS[''rlLang'']->replaceLangKeys(\$category_menu, ''categories'', [''name'', ''title'']);', 'active'),
('ajaxRequest', 'nova_template', '', 'if (\$param2 != ''novaLoadMoreListings'') {\r\n    return;\r\n}\r\n\r\nglobal \$rlSmarty, \$config, \$reefless, \$rlDb, \$rlListings, \$lang, \$request_lang;\r\n\r\n\$ts_path = RL_ROOT . ''templates'' . RL_DS . \$config[''template''] . RL_DS . ''settings.tpl.php'';\r\nif (is_readable(\$ts_path)) {\r\n   require_once(\$ts_path);\r\n}\r\n\r\n\$type  = \$_REQUEST[''type''];\r\n\$key   = \$_REQUEST[''key''];\r\n\$total = \$_REQUEST[''total''];\r\n\$ids   = explode('','', \$_REQUEST[''ids'']);\r\n\r\n\$results   = array();\r\n\$page_info = array(\r\n    ''Controller'' => ''home'',\r\n    ''Key'' => ''home'',\r\n);\r\n\r\n\$reefless->loadClass(''Listings'');\r\n\r\n\$rlSmarty->assign(''side_bar_exists'', \$_REQUEST[''side_bar_exists'']);\r\n\$rlSmarty->assign(''block'', array(''Side'' => \$_REQUEST[''block_side'']));\r\n\r\n\$rlListings->selectedIDs = \$ids;\r\n\r\n\$lang = \$GLOBALS[''rlLang'']->getLangBySide(''frontEnd'', \$request_lang);\r\n\r\nif (\$type == ''featured'') {\r\n    \$limit      = \$config[''featured_per_page''];\r\n    \$next_limit = \$limit < 10 ? 10 : \$limit;\r\n    \$tpl        = ''blocks'' . RL_DS . ''featured.tpl'';\r\n    \$listings   = \$rlListings->getFeatured(\$key, \$next_limit);\r\n    \$count      = count(\$listings);\r\n    \$next       = \$total + \$count < \$rlListings->calc;\r\n\r\n    \$rlSmarty->assign_by_ref(''listings'', \$listings);\r\n} else {\r\n    \$reefless->loadClass(''ListingsBox'', null, ''listings_box'');\r\n\r\n    \$box_id     = str_replace(''listing_box_'', '''', \$key);\r\n    \$box_info   = \$rlDb->fetch(\r\n        ''*'',\r\n        array(''ID'' => \$box_id),\r\n        null, 1, ''listing_box'', ''row''\r\n    );\r\n    \$limit      = \$box_info[''Count''];\r\n    \$next_limit = \$limit < 10 ? 10 : \$limit;\r\n    \$tpl        = RL_PLUGINS . ''listings_box'' . RL_DS . ''listings_box.block.tpl'';\r\n    \$listings   = \$GLOBALS[''rlListingsBox'']->getListings(\r\n        \$box_info[''Type''],\r\n        \$box_info[''Box_type''],\r\n        \$next_limit,\r\n        1,\r\n        \$box_info[''By_category'']\r\n    );\r\n    \$count      = count(\$listings);\r\n    \$next       = true;\r\n\r\n    \$box_option = array(\r\n        ''display_mode'' => \$box_info[''Display_mode'']\r\n    );\r\n\r\n    \$rlSmarty->assign(''box_option'', \$box_option); \r\n    \$rlSmarty->assign_by_ref(''listings_box'', \$listings);\r\n}\r\n\r\nif (\$listings) {\r\n    \$rlSmarty->preAjaxSupport();\r\n\r\n    \$results = array(\r\n        ''next''  => \$next,\r\n        ''count'' => \$count,\r\n        ''ids''   => \$rlListings->selectedIDs,\r\n        ''html''  => \$rlSmarty->fetch(\$tpl, null, null, false)\r\n    );\r\n\r\n    \$rlSmarty->postAjaxSupport(\$results, \$page_info, \$tpl);\r\n}\r\n\r\n\$param1 = array(\r\n    ''status'' => ''OK'',\r\n    ''results'' => \$results\r\n);', 'active'),
('listingsModifyPreSelectFeatured', 'nova_template', '', 'if (\$_REQUEST[''mode''] == ''novaLoadMoreListings'') {\r\n    \$param1 = true;\r\n}', 'active');
MYSQL;
    $rlDb->query($sql);

    // Refresh the page to apply new hooks
    if (defined('REALM') && REALM == 'admin') {
        $reefless->referer();
        exit;
    }
}

// Insert Thumbnails Preview support hook and set config flag
if (!$config['nova_thumbnails_preview']) {
    // Insert configs
    $insert_setting = array(
        'Group_ID' => 0,
        'Key' => 'nova_thumbnails_preview',
        'Default' => 1,
        'Type' => 'text',
        'Plugin' => 'nova_template'
    );
    $rlDb->insertOne($insert_setting, 'config');

    $config['nova_thumbnails_preview'] = 1;

    // Delete specialBlock hook, new version of the hook will be installed below
    $rlDb->delete(array('Name' => 'specialBlock', 'Plugin' => 'nova_template'), 'hooks');

    $sql = <<< MYSQL
    INSERT INTO `{db_prefix}hooks` (`Name`, `Plugin`, `Class`, `Code`, `Status`) VALUES
    ('ajaxRequest', 'nova_template', '', 'if (\$param2 == ''getListingPhotos'') {\r\n    \$listing_id = (int) \$_REQUEST[''id''];\r\n\r\n    if (\$listing_id) {\r\n        \$fields = [''Thumbnail''];\r\n\r\n        if (\$GLOBALS[''config''][''thumbnails_x2'']) {\r\n            \$fields[] = ''Thumbnail_x2'';\r\n        }\r\n\r\n        \$photos = \$GLOBALS[''rlDb'']->fetch(\r\n            \$fields,\r\n            [''Type'' => ''picture'', ''Status'' => ''active'', ''Listing_ID'' => \$listing_id],\r\n            \"ORDER BY `Position`\",\r\n            5, ''listing_photos''\r\n        );\r\n\r\n        if (\$photos) {\r\n            \$param1 = array(\r\n                ''status'' => ''OK'',\r\n                ''data'' => \$photos\r\n            );\r\n        } else {\r\n            \$param1 = array(\r\n                ''status'' => ''ERROR''\r\n            );\r\n        }\r\n    }\r\n} elseif (\$param2 == ''novaGetCategories'') {\r\n    \$listing_type = \$GLOBALS[''rlListingTypes'']->types[\$_REQUEST[''type'']];\r\n    \$categories = \\\Flynax\\\Utils\\\Category::getCategories(\$_REQUEST[''type''], \$_REQUEST[''parent_id''], \$listing_type[''Ablock_show_subcats''] ? 2 : 0);\r\n\r\n    /**\r\n     * @todo Remove this code once the `geo_filter_data[''location_url_pages'']` is available in `hookPhpUrlBottom` multiField plugin hook\r\n     */\r\n    if (\$GLOBALS[''plugins''][''multiField'']) {\r\n        \$GLOBALS[''reefless'']->loadClass(''GeoFilter'', null, ''multiField'');\r\n\r\n        if (\$GLOBALS[''rlGeoFilter'']->geo_format && !\$GLOBALS[''rlGeoFilter'']->geo_filter_data[''location_url_pages'']) {\r\n            \$GLOBALS[''rlGeoFilter'']->init();\r\n        }\r\n    }\r\n\r\n    foreach (\$categories as \$key => &\$category) {\r\n        if (\$listing_type[''Cat_hide_empty''] && \$category[''Count''] <= 0) {\r\n            unset(\$categories[\$key]);\r\n            continue;\r\n        }\r\n\r\n        if (\$listing_type[''Cat_listing_counter'']) {\r\n            \$category[''show_count''] = 1;\r\n        }\r\n\r\n        \$category[''link''] = \$GLOBALS[''reefless'']->url(''category'', \$category);\r\n\r\n        if (!\$category[''sub_categories'']) {\r\n            continue;\r\n        }\r\n\r\n        if (\$listing_type[''Ablock_show_subcats''] && \$listing_type[''Ablock_subcat_number''] > 0) {\r\n            array_splice(\$category[''sub_categories''], \$listing_type[''Ablock_subcat_number'']);\r\n        }\r\n\r\n        \$reset_index = false;\r\n\r\n        foreach (\$category[''sub_categories''] as \$sub_key => &\$sub_category) {\r\n            if (\$listing_type[''Cat_hide_empty''] && \$sub_category[''Count''] <= 0) {\r\n                unset(\$categories[\$key][''sub_categories''][\$sub_key], \$sub_category);\r\n                \$reset_index = true;\r\n                continue;\r\n            }\r\n\r\n            \$sub_category[''Type''] = \$category[''Type''];\r\n            \$sub_category[''name''] = \$GLOBALS[''rlLang'']->getPhrase(\$sub_category[''pName'']);\r\n            \$sub_category[''link''] = \$GLOBALS[''reefless'']->url(''category'', \$sub_category);\r\n        }\r\n\r\n        if (\$reset_index) {\r\n            \$category[''sub_categories''] = array_values(\$category[''sub_categories'']);\r\n        }\r\n    }\r\n\r\n    \$param1 = array(\r\n        ''status''  => ''OK'',\r\n        ''results'' => \$categories\r\n    );\r\n}', 'active'),
    ('specialBlock', 'nova_template', '', 'if (!\$GLOBALS[''tpl_settings''][''category_menu''] || \$GLOBALS[''page_info''][''Key''] != ''home'') {\r\n    return;\r\n}\r\n\r\nglobal \$pages, \$rlListingTypes;\r\n\r\n\$category_menu = [];\r\n\r\nif (\$GLOBALS[''tpl_settings''][''category_menu_listing_type'']) {\r\n    foreach (\$rlListingTypes->types as \$type) {\r\n        if (!\$type[''Menu'']) {\r\n            continue;\r\n        }\r\n\r\n        \$category_menu[] = array(\r\n            ''ID'' => \$type[''ID''],\r\n            ''Type'' => \$type[''Key''],\r\n            ''Name'' => \$type[''name''],\r\n            ''Menu_icon'' => \$type[''Menu_icon''],\r\n            ''isListingType'' => true\r\n        );\r\n    }\r\n}\r\n\r\n\$category_icons = \$GLOBALS[''rlDb'']->fetch(\r\n    array(''ID'', ''Type'', ''Path'', ''Key'', ''Menu_icon''),\r\n    array(\r\n        ''Menu'' => 1,\r\n        ''Status'' => ''active''\r\n    ),\r\n    \"ORDER BY `Position`\",\r\n    null, ''categories''\r\n);\r\n\r\n\r\nif (\$category_icons) {\r\n    \$category_menu = array_merge(\$category_menu, \$category_icons);\r\n    \$category_ids = [];\r\n\r\n    foreach (\$category_icons as \$icon) {\r\n        \$category_ids[] = \$icon[''ID''];\r\n    }\r\n\r\n    \$sql = \"\r\n        SELECT `Parent_ID` FROM `fl_categories`\r\n        WHERE `Parent_ID` IN (''\" . implode(\"'',''\", \$category_ids) . \"'')\r\n        GROUP BY `Parent_ID`\r\n    \";\r\n    \$parents = \$GLOBALS[''rlDb'']->getAll(\$sql, [false, ''Parent_ID'']);\r\n    \$GLOBALS[''rlSmarty'']->assign_by_ref(''category_menu_parents'', \$parents);\r\n}\r\n\r\n\$GLOBALS[''rlSmarty'']->assign_by_ref(''category_menu'', \$category_menu);\r\n\r\nforeach (\$category_menu as &\$item) {\r\n    \$href = SEO_BASE;\r\n    \$type = \$rlListingTypes->types[\$item[''Type'']];\r\n\r\n    if (\$GLOBALS[''config''][''mod_rewrite'']) {\r\n        \$href .= \$pages[\$type[''Page_key'']];\r\n        if (\$item[''Path'']) {\r\n            \$href .= ''/'' . \$item[''Path''];\r\n            \$href .= \$type[''Cat_postfix''] ? ''.html'' : ''/'';\r\n        } else {\r\n            \$href .= ''.html'';\r\n        }\r\n    } else {\r\n        \$href .= ''index.php?page='' . \$pages[\$type[''Page_key'']];\r\n        if (\$item[''Path'']) {\r\n            \$href .= ''&category='' . \$item[''ID''];\r\n        }\r\n    }\r\n\r\n    \$item[''href''] = \$href;\r\n    \$item[''icon''] = FL_TPL_ROOT . ''img/icons/'' . \$item[''Menu_icon''];\r\n    if (!\$item[''Name'']) {\r\n        \$item[''Name''] = \$GLOBALS[''rlLang'']->getPhrase(''categories+name+'' . \$item[''Key''], null, null, true);\r\n    }\r\n}\r\n\r\n\$GLOBALS[''rlLang'']->replaceLangKeys(\$category_menu, ''categories'', [''name'', ''title'']);', 'active'),
    ('apTplListingTypesForm', 'nova_template', '', 'if (!strpos(\$GLOBALS[''tpl_settings''][''name''], ''_olx_nova'')) {\r\n    return;\r\n}\r\n\r\n\$GLOBALS[''rlSmarty'']->display(RL_ROOT . ''templates'' . RL_DS . \$GLOBALS[''config''][''template''] . RL_DS . ''icon-manager.tpl'');', 'active'),
    ('apPhpListingTypesPost', 'nova_template', '', 'if (!strpos(\$GLOBALS[''tpl_settings''][''name''], ''_olx_nova'')) {\r\n    return;\r\n}\r\n\r\n\$_POST[''category_menu''] = \$GLOBALS[''type_info''][''Menu''];\r\n\$_POST[''category_menu_icon''] = \$GLOBALS[''type_info''][''Menu_icon''];', 'active'),
    ('apPhpListingTypesBeforeEdit', 'nova_template', '', 'if (!strpos(\$GLOBALS[''tpl_settings''][''name''], ''_olx_nova'')) {\r\n    return;\r\n}\r\n\r\nglobal \$update_date;\r\n\r\n\$update_date[''fields''][''Menu''] = \$_POST[''category_menu''];\r\n\$update_date[''fields''][''Menu_icon''] = \$_POST[''category_menu_icon''];', 'active'),
    ('apPhpListingTypesBeforeAdd', 'nova_template', '', 'if (!strpos(\$GLOBALS[''tpl_settings''][''name''], ''_olx_nova'')) {\r\n    return;\r\n}\r\n\r\nglobal \$data;\r\n\r\n\$data[''Menu''] = \$_POST[''category_menu''];\r\n\$data[''Menu_icon''] = \$_POST[''category_menu_icon''];', 'active');
MYSQL;
    $rlDb->query($sql);

    $rlDb->outputRowsMap = [false, 'ID'];
    $page_ids = $rlDb->fetch(['ID'], ['Controller' => 'listing_type'], null, null, 'pages');

    // Create fields
    $rlDb->addColumnToTable('Menu', "ENUM('1','0') NOT NULL DEFAULT '0' AFTER `Status`", 'listing_types');
    $rlDb->addColumnToTable('Menu_icon', "VARCHAR(100) NOT NULL AFTER `Menu`", 'listing_types');

    // Insert "Search on Map" box
    $box = [
        'Page_ID' => implode(',', $page_ids),
        'Category_ID' => '',
        'Sticky' => '0',
        'Cat_sticky' => '1',
        'Key' => 'search_on_map',
        'Position' => '1',
        'Side' => 'left',
        'Type' => 'smarty',
        'Content' => '{include file=\'blocks\'|cat:$smarty.const.RL_DS|cat:\'search_on_map.tpl\'}',
        'Tpl' => '0',
        'Header' => '0'
    ];
    $rlDb->insertOne($box, 'blocks');

    // Insert phrases
    $reefless->loadClass('Lang');
    $languages = $rlLang->getLanguagesList();
    $tpl_phrases = array(
        array('box', 'search_on_map', 'blocks+name+search_on_map', 'Search Listings on Map'),
        array('frontEnd', 'home', 'view_all_listings_in_category', 'View all listings in category {name}')
    );

    foreach ($languages as $language) {
        foreach ($tpl_phrases as $tpl_phrase) {
            if (!$rlDb->getOne('ID', "`Code` = '{$language['Code']}' AND `Key` = '{$tpl_phrase[1]}'", 'lang_keys')) {
                $insert = array(
                    'Code' => $language['Code'],
                    'Module' => $tpl_phrase[0],
                    'Target_key' => $tpl_phrase[1],
                    'Key' => $tpl_phrase[2],
                    'Value' => $rlValid->xSql($tpl_phrase[3]),
                    'Plugin' => 'nova_template'
                );

                if (version_compare($config['rl_version'], '4.8.1', '<')) {
                    unset($insert['Target_key']);
                    $insert['Module'] = 'frontEnd';
                }

                $rlDb->insertOne($insert, 'lang_keys');
            }
        }
    }

    // Refresh the page to apply new hooks
    if (defined('REALM') && REALM == 'admin') {
        $reefless->referer();
        exit;
    }
}
