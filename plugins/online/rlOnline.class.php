<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : RLONLINE.CLASS.PHP
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

class rlOnline
{
    /**
     * Install the plugin
     *
     * @since 2.1.0
     */
    public function install()
    {
        $GLOBALS['rlDb']->query("
            CREATE TABLE IF NOT EXISTS `" . RL_DBPREFIX . "online` (
              `ID` int(11) NOT NULL AUTO_INCREMENT,
              `sess_id` varchar(32) NOT NULL DEFAULT '',
              `ip` varchar(15) NOT NULL,
              `last_online` int(10) NOT NULL DEFAULT '0',
              `visibility` enum('0','1') NOT NULL DEFAULT '1',
              `is_login` enum('0','1') NOT NULL DEFAULT '0',
              PRIMARY KEY (`ID`),
              KEY `last_online` (`last_online`),
              KEY `sess_id` (`sess_id`)
            ) DEFAULT CHARSET=utf8;
        ");

        $GLOBALS['rlDb']->query("
            UPDATE `" . RL_DBPREFIX . "blocks` SET `Page_ID` = '1', `Sticky` = '0' WHERE `Key` = 'online_block'
        ");
    }

    /**
     * Uninstall the plugin
     *
     * @since 2.1.0
     */
    public function uninstall()
    {
        $GLOBALS['rlDb']->query("DROP TABLE IF EXISTS `" . RL_DBPREFIX . "online`");
    }

    /**
     * @hook specialBlock
     *
     * @since 2.1.0
     */
    public function hookSpecialBlock()
    {
        $this->updateStatistics();

        if (array_key_exists('online_block', $GLOBALS['block_keys'])) {
            $statistics = $this->fetchStatisticsInfo();
            $GLOBALS['rlSmarty']->assign('onlineStatistics', $statistics);
            unset($statistics);
        }
    }

    /**
     * @hook cronAdditional
     *
     * @since 2.1.0
     */
    public function hookCronAdditional()
    {
        $day = $GLOBALS['config']['online_last_day'] * 60 * 60;
        $sql = "DELETE FROM `" . RL_DBPREFIX . "online` WHERE `last_online` < UNIX_TIMESTAMP() - {$day}";
        $GLOBALS['rlDb']->query($sql);
    }

    /**
     * @hook apPhpHome
     *
     * @since 2.1.0
     */
    public function hookApPhpHome()
    {
        $GLOBALS['rlXajax']->registerFunction(array('adminStatistics', $this, 'ajaxAdminStatistics'));
    }

    /**
     * Show online statistics on admin panel
     *
     * @since 2.1.0
     * 
     * @return object
     */
    public function ajaxAdminStatistics()
    {
        global $_response, $rlSmarty;

        $statistics = $this->fetchStatisticsInfo();
        $rlSmarty->assign('onlineStatistics', $statistics);

        $tpl = RL_PLUGINS . 'online' . RL_DS . 'admin' . RL_DS . 'statistics_dom.tpl';
        $_response->assign('online_block_container', 'innerHTML', $rlSmarty->fetch($tpl, null, null, false));

        $_response->script(
            "$('#online_block_container').fadeIn('normal', function() {
                $(this).parent().removeClass('block_loading');
            });"
        );

        return $_response;
    }

    /**
     * Update online statistics
     *
     * @since 2.1.0
     */
    public function updateStatistics()
    {
        global $reefless, $rlDb, $config;

        $userIP = method_exists($reefless, 'getClientIpAddress')
        ? $reefless->getClientIpAddress()
        : $_SERVER['REMOTE_ADDR'];

        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $sessionHash = md5($userIP . $userAgent);
        $isUser = defined('IS_LOGIN') ? 1 : 0;
        $onlineDowntime = time() - ($config['online_downtime'] * 60);

        // collect statistics only for people
        if (false === $this->isBot($userAgent)) {
            $online = $rlDb->getOne('sess_id', "`sess_id` = '{$sessionHash}'", 'online');

            if (!empty($online)) {
                $this->shutdownQuery("
                    UPDATE `" . RL_DBPREFIX . "online`
                    SET `last_online` = UNIX_TIMESTAMP() , `visibility` = '1', `is_login` = '{$isUser}'
                    WHERE `sess_id` = '{$sessionHash}' LIMIT 1
                ");
            } else {
                $this->shutdownQuery("
                    INSERT INTO `" . RL_DBPREFIX . "online` (`sess_id`, `ip`, `last_online`, `visibility`, `is_login`)
                    VALUES ('{$sessionHash}', '{$userIP}', '{$nowDate}', '1', '{$isUser}')
                ");
            }
        }

        $this->shutdownQuery("
            UPDATE `" . RL_DBPREFIX . "online` SET `visibility` = '0'
            WHERE `last_online` < '{$onlineDowntime}'
        ");
    }

    /**
     * Register an SQL query for execution on shutdown;
     * Or execute the query immediately if rlDb::shutdownQuery method not exists.
     * 
     * @since 2.1.0
     * 
     * @param string $sql - SQL query string
     */
    private function shutdownQuery($sql)
    {
        global $rlDb;

        if (method_exists($rlDb, 'shutdownQuery')) {
            $rlDb->shutdownQuery($sql);
        } else {
            $rlDb->query($sql);
        }
    }

    /**
     * Fetch statistics from DB
     *
     * @return array - assoc data [total/users/guests/lastHour/lastDay]
     */
    public function fetchStatisticsInfo()
    {
        global $config;

        $onlineLastHour = time() - (intval($config['online_last_hour']) * 3600);
        $onlineLastDay = time() - (intval($config['online_last_day']) * 3600);
        $sub_sql_prefix = "SELECT COUNT(`ID`) FROM `" . RL_DBPREFIX . "online` WHERE";

        $sql = "SELECT COUNT(`ID`) AS `total`, ";
        $sql .= "({$sub_sql_prefix} `is_login` = '1' AND `visibility` = '1') AS `users`, ";
        $sql .= "({$sub_sql_prefix} `is_login` = '0' AND `visibility` = '1') AS `guests`, ";
        $sql .= "({$sub_sql_prefix} `last_online` > '{$onlineLastHour}') AS `lastHour`, ";
        $sql .= "({$sub_sql_prefix} `last_online` > '{$onlineLastDay}') AS `lastDay` ";
        $sql .= "FROM `" . RL_DBPREFIX . "online` WHERE `visibility` = '1'";

        return $GLOBALS['rlDb']->getRow($sql);
    }

    /**
     * @deprecated 2.1.0
     * @see rlOnline::updateStatistics
     *
     * Show online statistics
     */
    public function statistics()
    {
        $this->updateStatistics();
    }

    /**
     * @deprecated 2.1.0
     * @see reefless::isBot
     *
     * isBot - detect bots
     *
     * @param string $userAgent - User agent
     * @return bool - true/false
     */
    public function isBot($userAgent = null)
    {
        if (method_exists($GLOBALS['reefless'], 'isBot')) {
            return $GLOBALS['reefless']->isBot();
        }

        // if no user agent is supplied then assume it's a bot
        if (empty($userAgent)) {
            return true;
        }

        // array of bots
        $bots = array(
            "google", "bot", "radian",
            "yahoo", "spider", "crawl",
            "archiver", "curl", "yandex",
            "python", "nambu", "eventbox",
            "twitt", "perl", "monitor",
            "sphere", "PEAR", "mechanize",
            "java", "wordpress", "facebookexternal",
        );

        foreach ($bots as $bot) {
            if (false !== strpos($userAgent, $bot)) {
                return true;
            }
        }
        return false;
    }
}
