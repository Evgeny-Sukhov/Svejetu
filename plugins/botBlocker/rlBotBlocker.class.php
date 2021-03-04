<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : RLBOTBLOCKER.CLASS.PHP
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

class rlBotBLocker
{
    /**
     * @var array
     */
    public $botsList = [];

    /**
     * @hook init
     */
    public function hookInit()
    {
        global $config;

        if (!$config['botB_module'] || (defined('IS_BOT') && IS_BOT === false)) {
            return false;
        }

        $botsList = explode(',', $this->getBotsList());

        if ((bool) preg_match('/' . implode('|', $botsList) . '/i', $_SERVER['HTTP_USER_AGENT'])) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }
    }

    /**
     * @hook apPhpConfigBeforeUpdate
     */
    public function hookApPhpConfigBeforeUpdate()
    {
        global $update;

        if (!$update) {
            return false;
        }

        foreach ((array) $update as $configData) {
            if ($configData['where']['Key'] !== 'botB_bots_list') {
                continue;
            }

            $updateData = [
                'fields' => [
                    'Code' => rtrim($configData['fields']['Default'], ','),
                ],
                'where' => [
                    'Name'   => 'boot',
                    'Plugin' => 'botBlocker',
                ],
            ];
            $GLOBALS['rlDb']->updateOne($updateData, 'hooks');
            break;
        }
    }

    /**
     * @hook apPhpConfigBottom
     */
    public function hookApPhpConfigBottom()
    {
        if (!empty($_POST)) {
            return false;
        }

        foreach ($GLOBALS['rlSmarty']->_tpl_vars['configs'] as &$data) {
            foreach ($data as &$configData) {
                if ($configData['Key'] !== 'botB_bots_list') {
                    continue;
                }

                $configData['Default'] = $this->getBotsList();
                break;
            }
        }
    }

    /**
     * @hook apTplContentBottom
     */
    public function hookApTplContentBottom()
    {
        global $cInfo;

        if ('settings' !== $cInfo['Controller']) {
            return false;
        }

        echo <<< HTML
            <script>                
                $(function(){
                    botBlockerModuleHandler();

                    $('[name="post_config[botB_module][value]"]').change(function(){
                        botBlockerModuleHandler(); 
                    });
                });
    
                var botBlockerModuleHandler = function() {
                    var pluginEnabled    = Number($('[name="post_config[botB_module][value]"]:checked').val()),
                        \$botsListOption = $('[name="post_config[botB_bots_list][value]"]');
    
                    if (pluginEnabled === 1) {
                       \$botsListOption.removeAttr('disabled').removeClass('disabled');
                    } else {
                       \$botsListOption.attr('disabled', true).addClass('disabled');
                    }
                }
            </script>
HTML;
    }

    /**
     * Get list of bots which must be blocked from config
     * @return string
     */
    public function getBotsList()
    {
        $this->botsList = $this->botsList ?: ($GLOBALS['rlDb']->getOne(
            'Code',
            "`Name` = 'boot' AND `Plugin` = 'botBlocker'",
            'hooks'
        ) ?: $GLOBALS['config']['botB_bots_list']);

        return $this->botsList;
    }

    /**
     * System use 'Code' column of this hook for cache
     * @hook boot
     */
    public function hookBoot()
    {
        return true;
    }
}
