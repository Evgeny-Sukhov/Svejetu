<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : RLBOOKMARKSADMIN.CLASS.PHP
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

/**
 * Bookmarks and share Admin Panel Class
 * 
 * @since 4.0.0
 */
class rlBookmarksAdmin
{
    /**
     * AddThis services list URL
     * @var string
     */
    private $servicesUrl = 'https://cache.addthiscdn.com/services/v1/sharing.en.json';

    /**
     * Local services json file path
     * @var string
     */
    private $localServicesFile = RL_PLUGINS . 'bookmarks' . RL_DS . 'services.json';

    /**
     * Services background colors
     * @var array
     */
    private $bgColors = array(
        '500px' => '222222',
        '100zakladok' => '6C8DBE',
        'aboutme' => '054A76',
        'adfty' => '9dcb43',
        'adifni' => '3888c8',
        'advqr' => 'EC5923',
        'aim' => '8db81d',
        'amazonsmile' => 'FF9900',
        'amazonwishlist' => 'FF9900',
        'amenme' => '0872d8',
        'aolmail' => '282828',
        'apsense' => 'd78818',
        'atavi' => 'F26747',
        'baidu' => '1d2fe3',
        'balatarin' => '019949',
        'bandcamp' => '60929C',
        'beat100' => '3399CA',
        'behance' => '176AFF',
        'bitbucket' => '215081',
        'bitly' => 'f26e2a',
        'bizsugar' => '1F72EA',
        'bland' => 'f07b16',
        'blogger' => 'F57D00',
        'blogkeen' => 'db69b6',
        'blogmarks' => 'A3DE38',
        'bobrdobr' => '2874C7',
        'bonzobox' => 'c83828',
        'bookmarkycz' => 'a81818',
        'bookmerkende' => '558c15',
        'box' => '3088b1',
        'buffer' => '000000',
        'camyoo' => 'ace8f7',
        'care2' => '6CB440',
        'cashme' => '28C101',
        'citeulike' => '0888c8',
        'cleanprint' => '97ba7a',
        'cleansave' => '5BA741',
        'cloob' => '3BB44B',
        'cosmiq' => '4ca8d8',
        'cssbased' => '394918',
        'delicious' => '3399FF',
        'deviantart' => '05CC47',
        'diary_ru' => '932C2E',
        'digg' => '221E1E',
        'diggita' => '88b748',
        'diigo' => '0888d8',
        'disqus' => '2E9FFF',
        'dribbble' => 'EA4C89',
        'domaintoolswhois' => '305891',
        'douban' => '0e7512',
        'draugiem' => 'f47312',
        'edcast' => 'E03E7C',
        'efactor' => '7797b7',
        'ello' => '000000',
        'email' => '848484',
        'mailto' => '4e4e4e',
        'etsy' => 'EA6D24',
        'evernote' => '7fce2c',
        'exchangle' => 'D3155A',
        'fabulously40' => '620e18',
        'facebook' => '3B5998',
        'facenama' => '00699D',
        'fashiolista' => '383838',
        'favable' => '009ce9',
        'faves' => '08aed9',
        'favorites' => 'f5ca59',
        'favoritus' => '97462e',
        'financialjuice' => '242D38',
        'flickr' => '282828',
        'flipboard' => 'E12828',
        'folkd' => '175ca6',
        'foodlve' => 'd51e48',
        'foursquare' => '2D5BE3',
        'gg' => 'D7232D',
        'github' => '171515',
        'gitlab' => 'E3421C',
        'gmail' => 'DB4437',
        'goodreads' => '39210D',
        'google' => '4285F4',
        'google_classroom' => '25A667',
        'google_follow' => 'CF4832',
        'google_plusone_share' => 'DC4E41',
        'googletranslate' => '2c72c8',
        'govn' => '0ca8ec',
        'hackernews' => 'FF6600',
        'hatena' => '08aed9',
        'hedgehogs' => '080808',
        'historious' => 'b84949',
        'hootsuite' => '000000',
        'hotmail' => 'f89839',
        'houzz' => '74B943',
        'indexor' => '8bd878',
        'informazione' => '104F6E',
        'instagram' => 'E03566',
        'instapaper' => '000000',
        'internetarchive' => '6e6e6e',
        'iorbix' => '384853',
        'jappy' => 'f59216',
        'jsfiddle' => '4478A6',
        'kakao' => 'FAB900',
        'kakaotalk' => 'FAB900',
        'kaixin' => 'dd394e',
        'ketnooi' => '1888b9',
        'kindleit' => '282828',
        'kledy' => '8db81d',
        'letterboxd' => '73D448',
        'lidar' => '2ca8d2',
        'lineme' => '00C300',
        'link' => '178BF4',
        'linkedin' => '0077B5',
        'linkuj' => '5898d9',
        'livejournal' => '0ca8ec',
        'margarin' => 'FD934A',
        'markme' => 'd80808',
        'medium' => '272727',
        'meetvibe' => 'EB304B',
        'meinvz' => 'FF781E',
        'memonic' => '083568',
        'memori' => 'ee2271',
        'meneame' => 'ff6400',
        'mendeley' => 'af122b',
        'messenger' => '0084FF',
        'mixcloud' => '314359',
        'mixi' => 'cfab59',
        'moemesto' => '3B5E80',
        'mrcnetworkit' => 'abd4ec',
        'mymailru' => '165496',
        'myspace' => '282828',
        'myvidster' => '93F217',
        'n4g' => 'd80808',
        'naszaklasa' => '4077a7',
        'netvibes' => '48d828',
        'netvouz' => '4EBD08',
        'newsmeback' => '316896',
        'newsvine' => '64a556',
        'nujij' => 'c8080a',
        'nurses_lounge' => '0971BA',
        'odnoklassniki_ru' => 'd57819',
        'oknotizie' => '8BC53E',
        'onenote' => '7321A6',
        'openthedoor' => '2277BB',
        'oyyla' => 'f6cf0e',
        'pafnetde' => 'f4080d',
        'patreon' => '232d32',
        'paypalme' => '0070ba',
        'pdfmyurl' => 'f89823',
        'periscope' => '3FA4C4',
        'pinboard' => '1111AA',
        'pinterest' => 'CB2027',
        'pinterest_share' => 'CB2027',
        'plurk' => 'd56a32',
        'pocket' => 'EE4056',
        'posteezy' => 'f8ce2c',
        'print' => '738a8d',
        'printfriendly' => '88b748',
        'pusha' => '0878ba',
        'quantcast' => '0878ba',
        'quora' => 'B92B27',
        'qrsrc' => '4A8BF6',
        'qzone' => '0985DD',
        'ravelry' => 'DD0F56',
        'reddit' => 'ff5700',
        'rediff' => 'd80808',
        'renren' => '0058AE',
        'researchgate' => '00CCBB',
        'retellity' => 'B70100',
        'rss' => 'EF8647',
        'safelinking' => '3888c8',
        'scoopit' => '9dcb43',
        'slashdot' => '78D4B6',
        'slideshare' => '00A7AA',
        'snapchat' => 'FFDD00',
        'sharer' => '0888C8',
        'sinaweibo' => 'E6162D',
        'skyrock' => '282828',
        'skype' => '00AFF0',
        'slack' => '78D4B6',
        'smiru' => 'af122b',
        'sms' => '1ECE8E',
        'sodahead' => 'ff8c00',
        'soundcloud' => 'FF7700',
        'spinsnap' => '9dcb43',
        'spotify' => '23CF5F',
        'stack_overflow' => 'EF8236',
        'stack_exchange' => '1E5296',
        'startaid' => '4498c8',
        'startlap' => '4891b7',
        'steam' => '010103',
        'studivz' => 'DA060D',
        'stuffpit' => '2c72c8',
        'stumbleupon' => 'EB4924',
        'stumpedia' => 'FC9707',
        'stylishhome' => 'bfd08d',
        'supbro' => '383838',
        'surfingbird' => '0ca8ec',
        'svejo' => 'f89823',
        'symbaloo' => '4077a7',
        'taringa' => '165496',
        'technerd' => '316896',
        'telegram' => '0088CC',
        'tencentqq' => '000000',
        'tencentweibo' => '319EDD',
        'thefancy' => '4ca8d8',
        'thefreedictionary' => '4891b7',
        'thisnext' => '282828',
        'trello' => '0079BF',
        'tuenti' => '5f729d',
        'tumblr' => '37455C',
        'twitch' => '6441A5',
        'twitter' => '1DA1F2',
        'typepad' => '080808',
        'untappd' => 'FFCD00',
        'urlaubswerkde' => 'f89823',
        'venmo' => '3D95CE',
        'viadeo' => 'f07355',
        'viber' => '7B519D',
        'vimeo' => '1AB7EA',
        'vine' => '01B488',
        'virb' => '08aed9',
        'visitezmonsite' => '7DD6EA',
        'vk' => '6383A8',
        'vkrugudruzei' => 'e65229',
        'voxopolis' => '1097eb',
        'vybralisme' => '318ef6',
        'w3validator' => '165496',
        'wanelo' => 'CCCCCC',
        'wechat' => '2DC100',
        'weheartit' => 'FF4477',
        'whatsapp' => '4DC247',
        'wishmindr' => 'EF474F',
        'wordpress' => '585858',
        'wykop' => 'FB803F',
        'xing' => '1a7576',
        'yahoomail' => '3a234f',
        'yammer' => '2ca8d2',
        'yelp' => 'C60D00',
        'yookos' => '0898d8',
        'yoolink' => 'A5C736',
        'yorumcuyum' => '597DA3',
        'youmob' => '191847',
        'youtube' => 'CD201F',
        'yummly' => 'E26221',
        'yuuby' => '290838',
        'zakladoknet' => '9CCC00',
        'ziczac' => 'FF891F',
        'zingme' => 'F02972',
        'addthis' => 'FF6550',
    );

    /**
     * Exluded services which we not going to use
     * @var array
     */
    private $excludeServices = array(
        'facebook_like',
        'google_plusone'
    );

    /**
     * Services which might be used with original share buttons
     * @var array
     */
    private $originalServices = array(
        'addthis',
        'facebook',
        'foursquare',
        'linkedin',
        'stumbleupon',
        'twitter',
        'pinterest_share',
        'google_plusone_share',
    );

    /**
     * Get AddThis services list
     * @return array
     */
    public function getServices()
    {
        // Check/upload file
        $this->checkServices();
        $services = json_decode(file_get_contents($this->localServicesFile), true);

        // Add 'addthis' service
        array_unshift($services['data'], array(
            'code' => 'addthis',
            'name' => 'AddThis',
        ));

        // Exclude services
        foreach ($services['data'] as $key => &$service) {
            if (in_array($service['code'], $this->excludeServices)
                || $service['script_only'] == 'true')
            {
                unset($services['data'][$key]);
                continue;
            }

            unset(
                $service['script_only'],
                $service['prompt'],
                $service['icon32'],
                $service['endpoint'],
                $service['icon']
            );

            $service['bg'] = $this->bgColors[$service['code']];

            if (in_array($service['code'], $this->originalServices)) {
                $service['original'] = true;
            }
        }

        return $services['data'];
    }

    /**
     * Check and download new file with services every month
     */
    public function checkServices()
    {
        $file_date = filemtime($this->localServicesFile);
        $month = 60 * 60 * 24 * 31;

        if (!$file_date || $file_date + $month < time()) {
            $GLOBALS['reefless']->copyRemoteFile($this->servicesUrl, $this->localServicesFile);
        }
    }

    /**
     * Generate box SMARTY content
     * 
     * @param  string $type         - box type: "floating_bar" or "inline"
     * @param  string $service_type - service type: "custom" or "automatic"
     * @param  string $services     - services to use separated by comma, 
     *                                number is possible in case if $service_type equals "automatic"
     * @param  string $button_size  - button size: "small", "middle" or "large"
     * @param  string $share_type   - share count type: "none", "each", "one" or "both"
     * @param  string $share_style  - share buttons style: "responsive", "fixed" or "original"
     * @param  string $theme        - floating bar theme: "light", "dark", "gray" or "transparent"
     * @param  string $align        - inline box items align: "left", "center" or "right"
     * @return string               - SMARTY content
     */
    public function generateContent($type, $service_type, $services, $button_size, $share_type, $share_style, $theme, $align = 'left')
    {
        return '{include 
            file=$smarty.const.RL_PLUGINS|cat:$smarty.const.RL_DS|cat:"bookmarks"|cat:$smarty.const.RL_DS|cat:"'. $type . '.tpl"
            service_type="' . $service_type . '"
            services="' . $services . '"
            button_size="' . $button_size . '"
            share_type="' . $share_type . '"
            share_style="' . $share_style . '"
            theme="' . $theme . '"
            align="' . $align . '"
        }';
    }

    /**
     * Delete bookmarks box by entry ID
     *
     * @package ajax
     * @param  string $id - box entry ID
     * @return array      - query response
     */
    public function delete($id)
    {
        // Check admin session expire
        if ($GLOBALS['reefless']->checkSessionExpire() === false) {
            return $this->getFailResponse();
        }

        global $rlDb;

        $id = (int) $id;

        if (!$id) {
            $msg = 'Unable to delete selected sharing box, no ID parameter specified.';

            $GLOBALS['rlDebug']->logger('Bookmarks plugins: ' . $msg);

            return array(
                'status'  => 'ERROR',
                'message' => $msg,
            );
        }

        $key = $rlDb->getOne('Key', "`ID` = {$id}", 'bookmarks');

        // Remove bookmark entry
        $sql = "DELETE FROM `" . RL_DBPREFIX . "bookmarks` WHERE `ID` = {$id} LIMIT 1";
        $rlDb->query($sql);

        // Remove box entry
        $sql = "DELETE FROM `" . RL_DBPREFIX . "blocks` WHERE `Key` = '{$key}' LIMIT 1";
        $rlDb->query($sql);

        // Remove box related phrases
        $sql = "DELETE FROM `" . RL_DBPREFIX . "lang_keys` WHERE `Key` = 'blocks+name+{$key}'";
        $rlDb->query($sql);

        return array(
            'status'  => 'OK',
            'message' => $GLOBALS['lang']['block_deleted'],
        );
    }

    /**
     * Fetch data for grid
     *
     * @package ajax
     * @return array - response data with bookmarks details
     */
    public function fetch()
    {
        global $rlDb, $lang;

        $limit = (int) $_GET['limit'] ?: 20;
        $start = (int) $_GET['start'] ?: 0;

        $sql = "
            SELECT SQL_CALC_FOUND_ROWS `T2`.`Key`, `T2`.`Status`, `T2`.`Tpl`,
            `T2`.`Header`, `T2`.`Page_ID`, `T2`.`Sticky`, `T1`.`Type`, `T1`.`Align`,
            `T1`.`ID`
            FROM `" . RL_DBPREFIX . "bookmarks` AS `T1`
            LEFT JOIN `" . RL_DBPREFIX . "blocks` AS `T2` ON `T1`.`Key` = `T2`.`Key`
            ORDER BY `T2`.`ID` ASC
            LIMIT {$start}, {$limit}
        ";
        $data = $rlDb->getAll($sql);

        foreach ($data as &$item) {
            if ($item['Type'] == 'inline') {
                if ($item['Header']) {
                    $item['Name'] = $lang['blocks+name+' . $item['Key']];
                } else {
                    $item['Name'] = $lang['bsh_inline'] . ' ' . $item['ID'];
                }
            } else {
                $item['Name'] = $lang['bsh_floating_bar'] . ' ' . $item['ID'];
            }
            $item['Status'] = $lang[$item['Status']];
            $item['Type_name'] = $lang['bsh_' . $item['Type']];
            $item['Align'] = $lang['bookmark_' . $item['Align']];
            $item['Tpl'] = $item['Tpl'] ? $lang['yes'] : $lang['no'];
            $item['Header'] = $item['Header'] ? $lang['yes'] : $lang['no'];

            $page_names = array();
            if (!$item['Sticky'] && $item['Page_ID']) {
                $sql = "
                    SELECT `Key` FROM `" . RL_DBPREFIX . "pages`
                    WHERE FIND_IN_SET(`ID`, '{$item['Page_ID']}') > 0
                ";
                foreach ($rlDb->getAll($sql) as $page) {
                    $page_names[] = $lang['pages+name+' . $page['Key']];
                }
            }
            $item['Pages'] = $item['Sticky']
            ? $lang['sticky']
            : implode(', ', $page_names);
        }

        return array(
            'total' => $rlDb->getRow("SELECT FOUND_ROWS() AS `count`", 'count'),
            'data' => $data,
        );
    }

    /**
     * Update entry through the grid
     *
     * @package ajax
     * @param  string $id    - entry id
     * @param  string $field - field to edit
     * @param  string $value - new value
     * @return array         - query response
     */
    public function update($id, $field, $value)
    {
        global $rlActions, $reefless, $rlDb, $rlValid;

        $field = $rlValid->xSql($field);
        $value = $rlValid->xSql($value);
        $id    = (int) $id;
        
        if (!$id) {
            $msg = 'Unable to update selected sharing box, no ID parameter specified.';

            $GLOBALS['rlDebug']->logger('Bookmarks plugins: ' . $msg);

            return array(
                'status'  => 'ERROR',
                'message' => $msg,
            );
        }

        // Update block content
        $bookmark = $rlDb->fetch('*', array('ID' => $id), null, 1, 'bookmarks', 'row');

        switch ($field){
            case 'Align':
                // Update related box
                $updateBlock = array(
                    'fields' => array(
                        'Content' => $this->generateContent(
                            $bookmark['Type'],
                            $bookmark['Service_type'],
                            $bookmark['Services'],
                            $bookmark['View_mode'],
                            $bookmark['Share_type'],
                            $bookmark['Share_style'],
                            $bookmark['Theme'],
                            $value
                        )
                    ),
                    'where' => array(
                        'Key' => $bookmark['Key']
                    )
                );
                $rlActions->updateOne($updateBlock, 'blocks');

                // Update bookmark entry
                $updateData = array(
                    'fields' => array(
                        $field => $value
                    ),
                    'where' => array(
                        'ID' => $id
                    )
                );
                $rlActions->updateOne($updateData, 'bookmarks');
                break;

            case 'Status':
            case 'Tpl':
                $updateBlock = array(
                    'fields' => array(
                        $field => $value
                    ),
                    'where' => array(
                        'Key' => $bookmark['Key']
                    )
                );
                $rlActions->updateOne($updateBlock, 'blocks');
                break;
        }

        return array('status' => 'OK');
    }

    /**
     * Get expired session response data
     * 
     * @return array - response data
     */
    public function getFailResponse()
    {
        $redirect = RL_URL_HOME . ADMIN . '/index.php';
        $redirect .= empty($_SERVER['QUERY_STRING']) 
                ? '?session_expired'
                :'?' . $_SERVER['QUERY_STRING'] . '&session_expired';

        return array(
            'status'   => 'ERROR',
            'redirect' => $redirect
        );
    }

    /**
     * Handle admin panel ajax queries
     * 
     * @hook apAjaxRequest
     */
    public function hookApAjaxRequest(&$out, $item)
    {
        // Support for version less then 4.6.0
        if (!func_get_arg(1)) {
            global $out, $item;
        }

        if (!in_array($item, array('bookmarks_delete', 'bookmarks_fetch'))) {
            return;
        }

        switch ($item) {
            case 'bookmarks_delete':
                $out = $this->delete($_POST['id']);
                break;
            
            case 'bookmarks_fetch':
                if ($_GET['action'] == 'update') {
                    $out = $this->update($_GET['id'], $_GET['field'], $_GET['value']);
                } else {
                    $out = $this->fetch();
                }
                break;
        }
    }

    /**
     * Hide floating bar boxes from the grid of blocks
     *
     * @hook apExtBlocksSql
     */
    public function hookApExtBlocksSql()
    {
        global $sql;

        $sql = preg_replace(
            '/(LIMIT\s[0-9]+)/',
            "AND (`T1`.`Key` NOT LIKE 'bookmark_floating_bar%') $1",
            $sql
        );
    }

    /**
     * Simulate inline box name in grid
     *
     * @hook apExtBlocksData
     */
    public function hookApExtBlocksData()
    {
        global $data, $lang, $rlDb;

        foreach ($data as $index => $block) {
            if (strpos($block['Key'], 'bookmark_inline_') === 0 
                && $block['Header'] == $lang['no']
            ) {
                $data[$index]['name'] = $lang['bsh_inline'] . ' ' . str_replace('bookmark_inline_', '', $block['Key']);
            }
        }
    }

    /**
     * Change controller for bookmarks blocks
     *
     * @hook apTplBlocksGrid
     */
    public function hookApTplBlocksGrid()
    {
        $out = <<< JAVASCRIPT
            var instance = blocksGrid.getInstance();
            var index = instance.columns.length-1;
            var renderer = instance.columns[index].renderer;
            instance.columns[index].renderer = function(data, ext, row){
                if (row.data.Key.indexOf('bookmark_inline_') === 0) {
                    var original_controller = controller;
                    controller = 'bookmarks';
                }
                data = renderer.call(this, data);
                if (row.data.Key.indexOf('bookmark_inline_') === 0) {
                    controller = original_controller;
                }

                return data;
            }
JAVASCRIPT;

        echo $out;
    }

    /**
     * Remove "integrated_banner" and "header_banner" box positions from the 
     * grid cell for plugin rows
     *
     * @hook apTplBlocksBottom
     */
    public function hookApTplBlocksBottom()
    {
        $out = <<< JAVASCRIPT
        $(function(){
            blocksGrid.grid.addListener('beforeedit', function(editEvent){
                if (editEvent.field == 'Header') {
                    if (editEvent.record.data.Key.indexOf('bookmark_inline_') === 0) {
                        editEvent.cancel = true;
                        blocksGrid.store.rejectChanges();
                    }
                } else if (editEvent.field == 'Side') {
                    var column = editEvent.grid.colModel.columns[2];
                    var removed = false;

                    if (editEvent.record.data.Key.indexOf('bookmark_inline_') === 0) {
                        var items = column.editor.getStore().data.items;
                        var items_ids = [];
                        for (var i = 0; i < items.length; i++) {
                            if (['integrated_banner', 'header_banner'].indexOf(items[i].data.field1) >= 0) {
                                items_ids.push(i);
                            }
                        }

                        if (items_ids.length) {
                            for (var i in items_ids.reverse()) {
                                column.editor.getStore().removeAt(items_ids[i])
                            }

                            removed = true;
                        }
                    } else {
                        if (removed) {
                            column.editor = new Ext.form.ComboBox({
                                store: block_sides,
                                displayField: 'value',
                                valueField: 'key',
                                typeAhead: true,
                                mode: 'local',
                                triggerAction: 'all',
                                selectOnFocus: true
                            });
                            removed = false;
                        }
                    }
                }
            });
        });
JAVASCRIPT;

        echo "<script>{$out}</script>";
    }
}
