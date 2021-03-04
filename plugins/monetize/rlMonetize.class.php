<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : RLMONETIZE.CLASS.PHP
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

include 'bootstrap.php';

class rlMonetize
{
    /**
     * @var string - Path to the front-end view folder
     */
    private $view;

    /**
     * @var string - Path to the admin view folder
     */
    private $a_view;

    /**
     * @var \rlBumpUp - Bump Up class instance
     */
    private $bumpUp;

    /**
     * @var \rlHighlight - Highlight class instance
     */
    private $highlight;

    /**
     * @since 1.3.0
     *
     * @var array - Controllers of the pages in which will be included css and js files
     */
    private $includeFilesInPages;

    /**
     * rlMonetize constructor.
     */
    public function __construct()
    {
        $m_time_format = $GLOBALS['config']['is_european_time_format'] ? ' %H:%M:%S' : ' %I:%M %p';
        define('BUMPUP_TIME_FORMAT', RL_DATE_FORMAT . $m_time_format);

        $this->view = RL_PLUGINS . 'monetize' . RL_DS . 'view' . RL_DS;
        $this->a_view = RL_PLUGINS . 'monetize' . RL_DS . 'admin' . RL_DS . 'view' . RL_DS;
        if ($GLOBALS['rlSmarty']) {
            $config['view'] = $this->view;
            $config['a_view'] = $this->a_view;
            $config['a_path'] = RL_PLUGINS . 'monetize' . RL_DS . 'admin' . RL_DS;
            $GLOBALS['rlSmarty']->assign('mConfig', $config);
        }

        //Load BumpUp and High
        $GLOBALS['reefless']->loadClass('BumpUp', null, 'monetize');
        $GLOBALS['reefless']->loadClass('Highlight', null, 'monetize');
        $this->bumpUp = $GLOBALS['rlBumpUp'];
        $this->highlight = $GLOBALS['rlHighlight'];

    }

    /**
     * Getting allowed pages controllers
     *
     * @since 1.3.0
     *
     * @return array - Listing of allowed pages controllers where lib.js and style.css will be included
     */
    public function getAllowPagesControllers()
    {
        if ($this->getIncludeFilesInPages()) {
            return $this->getIncludeFilesInPages();
        }

        $allowedPages = array(
            'my_listings',
            'bumpup_page',
            'profile',
            'highlight_page',
            'listing_type',
            'recently_added',
            'search',
            'account_type',
        );
        $this->setIncludeFilesInPages($allowedPages);

        $addPageMethod = 'addIncludePagesController';

        /**
         * Use first parameter as an instance of the current class and the second one as callable function
         * Example:
         *          $rlMonetize->$addPageMethod('page_controller');
         */
        $GLOBALS['rlHook']->load('phpMonetizeAssignAllowedPages', $this, $addPageMethod);

        return $this->getIncludeFilesInPages();
    }

    /**
     * Plugin install function
     */
    public function install()
    {
        global $rlDb;

        $rlDb->query("
           CREATE TABLE IF NOT EXISTS `" . RL_DBPREFIX . "monetize_plans` (
              `ID` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `Key` VARCHAR(200) NOT NULL,
              `Bump_ups` INT(4) NOT NULL,
              `Days` INT(3) NOT NULL,
              `Highlights` INT(4) NOT NULL,
              `Price` VARCHAR(50) NOT NULL,
              `Color` VARCHAR(6) NOT NULL,
              `Status` ENUM('active', 'approval') DEFAULT 'active',
              `Type` ENUM('highlight', 'bumpup')
            ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;
        ");

        $rlDb->query("
           CREATE TABLE IF NOT EXISTS `" . RL_DBPREFIX . "monetize_using` (
              `ID` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `Account_ID` INT(11) NOT NULL,
              `Plan_ID` INT(4) NOT NULL,
              `Plan_type` ENUM('bumpup', 'highlight') DEFAULT 'bumpup',
              `Credit_from` ENUM('flynax', 'plugin') DEFAULT 'plugin',
              `Date` DATETIME NOT NULL,
              `Bumpups_available` INT(4) NOT NULL,
              `Highlights_available` INT(4) NOT NULL,
              `Days_highlight` INT(3) NOT NULL,
              `Is_unlim` ENUM('0', '1') DEFAULT '0'
            ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;
        ");

        $rlDb->addColumnsToTable(array(
            'Bumped' => "ENUM('0', '1') DEFAULT '0'",
            'HighlightDate' => "DATETIME NOT NULL",
            'Highlight_Plan' => "INT(11) NOT NULL",
            'Monetize_using_id' => "INT(11) NOT NULL",
        ), 'listings');

        $rlDb->addColumnsToTable(array(
            'Bumpups' => "INT(4) NOT NULL",
            'Highlight' => "INT(4) NOT NULL",
            'Days_highlight' => "INT(3) NOT NULL",
        ), 'listing_plans');

        // set default pages to the Monetize block
        $sql = "SELECT `ID` FROM `" . RL_DBPREFIX . "pages` WHERE `Key`='bumpup_page' OR  `Key`='highlight_page'";
        $result = $rlDb->getAll($sql);
        $ids = array_map(function ($element) {
            return $element['ID'];
        }, $result);
        $ids = implode(',', $ids);
        $updatePage = array(
            'fields' => array(
                'Page_ID' => $ids,
                'Header' => 0,
                'Sticky' => 0,
            ),
            'where' => array(
                'Key' => 'monetize_listing_detail',
            ),
        );
        $GLOBALS['rlActions']->updateOne($updatePage, 'blocks');
    }

    /**
     * @hook myListingsIcon
     */
    public function hookMyListingsIcon()
    {
        $listing = $GLOBALS['rlSmarty']->_tpl_vars['listing'];
        if (!$listing || $listing['Status'] !== 'active') {
            return false;
        }

        $back_to = $GLOBALS['reefless']->url('page', $GLOBALS['page_info']['Key']);
        $_SESSION['m_back_to'] = $back_to;
        $GLOBALS['rlSmarty']->display($this->view . 'monetize_icons.tpl');
    }

    /**
     * Getting bump ups count from Listings plan/packages
     *
     * @param  int $plan_id - ID of the listing plan/package
     * @return string           - Bump ups count of the listing package
     */
    public function getBumpUpFromPlan($plan_id)
    {
        return $GLOBALS['rlDb']->getOne('Bumpups', "`ID` = {$plan_id}", 'listing_plans');
    }

    /**
     * @hook apAjaxRequest
     */
    public function hookApAjaxRequest()
    {
        $item = $_REQUEST['item'];
        if (!$this->isCorrectApAjaxRequest($item)) {
            return false;
        }

        switch ($item) {
            case 'deleteBumpUpPlan':
                $GLOBALS['reefless']->loadClass('BumpUp', null, 'monetize');
                $id = $GLOBALS['rlValid']->xSql($_POST['id']);
                $GLOBALS['out'] = $GLOBALS['rlBumpUp']->deletePlan($id);
                break;
            case 'deleteHighlightPlan':
                $GLOBALS['reefless']->loadClass('Highlight', null, 'monetize');
                $id = $GLOBALS['rlValid']->xSql($_POST['id']);
                $GLOBALS['out'] = $GLOBALS['rlHighlight']->deletePlan($id, true);
                break;
            case 'monetize_getMonetizePlanUsingInfo':
                $username = $GLOBALS['rlValid']->xSql($_REQUEST['username']);
                $creditInfo = $this->getCreditsInfoByUser($username);

                $GLOBALS['out'] = array(
                    'status' => 'OK',
                    'credits_info' => $creditInfo,
                );
                break;
            case 'monetize_ajaxAssignCredits':
                $GLOBALS['reefless']->loadClass('Actions');
                $username = $GLOBALS['rlValid']->xSql($_POST['username']);
                $userID = $GLOBALS['rlDb']->getOne('ID', "`Username` = '{$username}'", 'accounts');
                $status = 'ERROR';
                if ($userID) {
                    if (isset($_POST['bumpup_plan'])) {
                        $bumpupPlan = (int) $_POST['bumpup_plan'];
                        $bumpupCredits = (int) $_POST['bumpup_credits'];
                        $this->addCustomPlanUsing($bumpupPlan, $bumpupCredits, $userID, 'bumpup');
                    }

                    if (isset($_POST['highlight_plan'])) {
                        $highlightPlan = (int) $_POST['highlight_plan'];
                        $highlightCredits = (int) $_POST['highlight_credits'];
                        $this->addCustomPlanUsing($highlightPlan, $highlightCredits, $userID, 'highlight');
                    }
                    $status = 'OK';
                }

                $GLOBALS['out'] = array('status' => $status);
                break;
            case 'monetize_getHighlightCredits':
                $username = $GLOBALS['rlValid']->xSql($_POST['username']);
                $userID = (int) $GLOBALS['rlDb']->getOne('ID', "`Username` = '{$username}'", 'accounts');
                $planID = (int) $_POST['plan'];

                $out = array(
                    'status' => 'ERROR',
                );

                if ($userID && $planID) {
                    $credits = $this->highlight->getCredits($userID, $planID);
                    $out = array(
                        'status' => 'OK',
                        'credits' => $credits,
                    );
                }

                $GLOBALS['out'] = $out;
                break;
            case 'monetize_getBumpUpCredits':
                $username = $GLOBALS['rlValid']->xSql($_POST['username']);
                $userID = (int) $GLOBALS['rlDb']->getOne('ID', "`Username` = '{$username}'", 'accounts');
                $planID = (int) $_POST['plan'];

                $out = array(
                    'status' => 'ERROR',
                );

                if ($userID && $planID) {
                    $credits = $this->bumpUp->getCredits($userID, $planID);
                    $out = array(
                        'status' => 'OK',
                        'credits' => $credits,
                    );
                }

                $GLOBALS['out'] = $out;
                break;
            case 'monetize_checkMonetizePlanUsage':
                $planID = (int) $_POST['plan_id'];
                $users = $this->getPlanUsingUsers($planID);
                $GLOBALS['out'] = array(
                    'status' => 'OK',
                    'users' => $users,
                );
                break;
            case 'monetize_getPlans':
                $type = $GLOBALS['rlValid']->xSql($_POST['type']);
                $exclude = (int) $_POST['exclude'];

                $out = array(
                    'status' => 'ERROR',
                );

                if ($type == 'highlight') {
                    $plans = $GLOBALS['rlHighlight']->getPlans();
                    $pos = array_search($exclude, array_column($plans, 'ID'));
                    if (is_int($pos)) {
                        unset($plans[$pos]);
                    }
                    $plans = array_values(array_filter($plans));

                    $out = array(
                        'status' => 'OK',
                        'plans' => $plans,
                    );
                }

                $GLOBALS['out'] = $out;
                break;
            case 'monetize_reassignPlan':
                $from = (int) $_POST['from'];
                $to = (int) $_POST['to'];
                $out = array(
                    'status' => 'ERROR',
                );

                if ($this->reassignMonetizePlanToAnother($from, $to)) {
                    $out['status'] = 'OK';
                }

                $GLOBALS['out'] = $out;
                break;
        }
    }

    /**
     * Getting credits info by user
     *
     * @since 1.3.0
     * @param string $username
     *
     * @return array
     */
    public function getCreditsInfoByUser($username)
    {
        if (!$username) {
            return array();
        }

        $accountID = $GLOBALS['rlDb']->getOne('ID', "`Username` = '{$username}'", 'accounts');

        $highlightPlans = $this->highlight->getPlans();
        foreach ($highlightPlans as $key => $plan) {
            if (!(int) $plan['Highlights']) {
                unset($highlightPlans[$key]);
                continue;
            }

            $highlightPlans[$key]['credits'] = $this->highlight->getCredits($accountID, $plan['ID']);
        }
        $highlightPlans = array_values(array_filter($highlightPlans));

        $bumpupPlans = $this->bumpUp->getPlans();
        foreach ($bumpupPlans as $key => $plan) {
            if (!(int) $plan['Bump_ups']) {
                unset($bumpupPlans[$key]);
                continue;
            }
            $bumpupPlans[$key]['credits'] = $this->bumpUp->getCredits($accountID, $plan['ID']);
        }
        $bumpupPlans = array_values(array_filter($bumpupPlans));

        return array(
            'bump_up' => array(
                'total_credits' => $this->bumpUp->getCredits($accountID),
                'plans' => $bumpupPlans,
            ),
            'highlight' => array(
                'total_credits' => $this->highlight->getCredits($accountID),
                'plans' => $highlightPlans,
            ),
        );
    }

    /**
     * Does incoming ajax request item is related to the monetize plugin
     *
     * @since 1.3.0
     * @param  string $item - Request item
     * @return bool
     */
    public function isCorrectApAjaxRequest($item)
    {
        $availableRequests = array(
            'deleteBumpUpPlan',
            'deleteHighlightPlan',
            'monetize_getMonetizePlanUsingInfo',
            'monetize_ajaxAssignCredits',
            'monetize_getHighlightCredits',
            'monetize_getBumpUpCredits',
            'monetize_checkMonetizePlanUsage',
            'monetize_getPlans',
            'monetize_reassignPlan',
        );

        return in_array($item, $availableRequests);
    }

    /**
     * @hook staticDataRegister
     */
    public function hookStaticDataRegister()
    {
        global $rlStatic;

        $chartUrl = '';

        $rlStatic->addFooterCSS(RL_PLUGINS_URL . 'monetize/static/style.css', $this->getAllowPagesControllers());
        $rlStatic->addJS(RL_PLUGINS_URL . 'monetize/static/lib.js', $this->getAllowPagesControllers());

        $templateRoot = RL_ROOT . 'templates/' . $GLOBALS['config']['template'] . '/';
        $chartLocationIn = array(
            'css' => 'css/plans-chart.css',
            'components' => 'components/plans-chart/plans-chart.css',
        );

        if (file_exists($templateRoot . $chartLocationIn['components'])) {
            $chartUrl = RL_TPL_BASE . $chartLocationIn['components'];
        }

        // todo: remove, when plugin compatible will be >= 4.7.0
        if (file_exists($templateRoot . $chartLocationIn['css'])) {
            $chartUrl = RL_TPL_BASE . $chartLocationIn['css'];
        }

        if ($chartUrl) {
            $rlStatic->addFooterCSS($chartUrl, array('bumpup_page', 'highlight_page'));
        }
    }

    /**
     * @hook tplFooter
     */
    public function hookTplFooter()
    {
        if (class_exists('rlStatic')) {
            return;
        }

        if (in_array($GLOBALS['page_info']['Controller'], $this->getAllowPagesControllers())) {
            echo '<script src="' . RL_PLUGINS_URL . 'monetize/static/style.css"></script>';
            echo '<script src="' . RL_PLUGINS_URL . 'monetize/static/lib.js"></script>';
        }
    }

    /**
     * @hook ListingAfterFields
     */
    public function hookListingAfterFields()
    {
        $GLOBALS['rlSmarty']->display($this->view . 'highlight_hook.tpl');
    }

    /**
     * @hook apTplListingPlansForm
     */
    public function hookApTplListingPlansForm()
    {
        $GLOBALS['rlSmarty']->display($this->a_view . 'listings_plan_form.tpl');
    }

    /**
     * @hook apPhpListingPlansBeforeAdd
     */
    public function hookApPhpListingPlansBeforeAdd()
    {
        global $data;

        $data['Bumpups'] = $_POST['bump_up_count_unlimited'] ? -1 : $_POST['bumpups'];
        $data['Highlight'] = $_POST['highlights_unlimited'] ? -1 : $_POST['highlights'];
        $data['Days_highlight'] = $_POST['days_highlight'];
    }

    /**
     * @hook apPhpListingPlansBeforeEdit
     */
    public function hookApPhpListingPlansBeforeEdit()
    {
        global $update_date;

        $update_date['fields']['Bumpups'] = $_POST['bump_up_count_unlimited'] ? -1 : $_POST['bumpups'];
        $update_date['fields']['Highlight'] = $_POST['highlights_unlimited'] ? -1 : $_POST['highlights'];
        $update_date['fields']['Days_highlight'] = $_POST['days_highlight'];
    }

    /**
     * @hook addListingBeforeSteps
     */
    public function hookAddListingBeforeSteps()
    {
        global $plans, $lang;

        if (version_compare($GLOBALS['config']['rl_version'], '4.5.2') < 0) {
            foreach ($plans as $key => $value) {
                $new_text = '';
                $plan_type_title = $GLOBALS['lang'][$plans[$key]['Type'] . '_plan_short'];

                // bump ups
                $bumpups = $this->getBumpUpFromPlan($key);
                if ($bumpups) {
                    $new_text .= $plan_type_title . '</span>';
                    $plans[$key]['Type'] = 'bump_up' . $key;
                    $new_text .= "</span><span class='count'>";
                    if ($bumpups > 0) {
                        $new_text .= $bumpups . ' ' . $lang['bumpups'];
                    } else {
                        $new_text .= $lang['unlimited'];
                    }
                }

                // highlights
                $highlights = $this->highlight->getHighlightByPlan($key);
                if ($highlights['Highlight']) {
                    $new_text .= "</span></span><span class='count'>";
                    if ($highlights['Highlight'] > 0) {
                        $new_text .= $highlights['Highlight'] . ' ' . $lang['m_highlights'];
                    } else {
                        $new_text .= $lang['unlimited'];
                    }
                    $new_text .= "</span>";
                    $new_text .= "</span></span><span class='count'>" . $lang['m_highlighted_for'] . " ";
                    $new_text .= $highlights['Days_highlight'] . ' ' . $lang['days'] . " </span>";
                }

                $lang[$plans[$key]['Type'] . '_plan_short'] = $new_text;
            }
        }
    }

    /**
     * @hook apPhpListingPlansValidate
     */
    public function hookApPhpListingPlansValidate()
    {
        global $lang, $errors, $error_fields;
        if (!empty($_POST['highlights']) || $_POST['highlights_unlimited']) {
            if ($_POST['highlights'] < 0) {
                $replace = "<b>" . $lang['m_highlight_available'] . "</b>";
                $errors[] = str_replace('{h_field}', $replace, $lang['m_field_negative']);
                $error_fields[] = "name[highlights]";
            }

            if (empty($_POST['days_highlight'])) {
                $find = "<b>" . $lang['m_days_highlight'] . "</b>";
                $errors[] = str_replace('{h_field}', $find, $lang['m_field_empty']);
                $error_fields[] = "name[days_highlight]";
            }
        }

        if ($_POST['bumpups'] < 0) {
            $replace = "<b>" . $lang['bumpups_available'] . "</b>";
            $errors[] = str_replace('{h_field}', $replace, $lang['m_field_negative']);
            $error_fields[] = "name[bumpups]";
        }
    }

    /**
     * @hook apPhpListingPlansPost
     */
    public function hookApPhpListingPlansPost()
    {
        $bumpups = $GLOBALS['plan_info']['Bumpups'];
        $highlights = $GLOBALS['plan_info']['Highlight'];

        $_POST['bumpups'] = $bumpups;
        $_POST['bump_up_count_unlimited'] = $bumpups < 0 ? 1 : 0;
        $_POST['highlights'] = $highlights;
        $_POST['highlights_unlimited'] = $highlights < 0 ? 1 : 0;
        $_POST['days_highlight'] = $GLOBALS['plan_info']['Days_highlight'];
    }

    /**
     * @hook  afterListingDone
     *
     * @since 1.3.0 $addListing, $updateData, $isFree added
     *
     * @param  \Flynax\Classes\AddListing $addListing
     * @param  array                      $updateData
     * @param  bool                       $isFree
     *
     * @return bool
     */
    public function hookAfterListingDone($addListing, &$updateData, $isFree)
    {
        $listingData = !is_null($addListing) ? $addListing->listingData : $GLOBALS['listing_data'];
        $planInfo = !is_null($addListing) && $addListing->plans[$listingData['Plan_ID']]
        ? $addListing->plans[$listingData['Plan_ID']]
        : $GLOBALS['plan_info'];
        $isFree = !is_null($isFree) ? $isFree : $planInfo['Price'] <= 0;

        if ($isFree) {
            $this->addMonetizeUsingDependingOnPlan($listingData['Plan_ID']);
        }

        return true;
    }

    /**
     * @hook  phpListingsUpgradeListing
     * @since 1.2.0
     *
     * @param array $plan_info - Information regarding plan
     * @param int   $plan_id
     * @param int   $listing_id
     */
    public function hookPhpListingsUpgradeListing($plan_info = array(), $plan_id = 0, $listing_id = 0)
    {
        return false;

        global $rlPayment, $account_info;
        $GLOBALS['reefless']->loadClass('Account');

        if (!$plan_id) {
            $plan_id = $GLOBALS['plan_id'] ?: $rlPayment->getOption('plan_id');
        }

        $account_info = $account_info ?: $GLOBALS['rlAccount']->getProfile((int) $rlPayment->getOption('account_id'));
        $this->addMonetizeUsingDependingOnPlan($plan_id);
    }

    /**
     * Adding Highlight/BumpUp credits to the user depending on the plan.
     *
     * @param  int $plan_id
     * @return bool
     */
    public function addMonetizeUsingDependingOnPlan($plan_id = 0)
    {
        if (!$plan_id) {
            return false;
        }

        $sql = "SELECT * FROM `" . RL_DBPREFIX . "listing_plans` WHERE `ID` = {$plan_id}";
        $plan = $GLOBALS['rlDb']->getRow($sql);

        // add highlights
        if ($plan['Highlight']) {
            $this->highlight->addPlanUsing($plan, 'listing', 'flynax');
        }

        // add bumpups
        if ($plan['Bumpups']) {
            $this->bumpUp->addPlanUsing($plan, 'listing', 'flynax');
        }
    }

    /**
     * @hook phpListingsUpgradePlanInfo
     */
    public function hookPhpListingsUpgradePlanInfo()
    {
        if ($GLOBALS['plan_info']['Price'] === 0) {
            $this->addMonetizeUsingDependingOnPlan($GLOBALS['plan_info']['ID']);
        }
    }

    /**
     * Monetize block
     */
    public function blockMonetizeListingDetail()
    {
        global $rlSmarty;

        $listing_id = $GLOBALS['rlValid']->xSql($_GET['id']);
        if ($listing_id) {
            $listing_data = $GLOBALS['rlListings']->getListing($listing_id, true, true);
            $listing_data['url'] = $listing_data['listing_link'];
            $rlSmarty->assign('listing', $listing_data);
            $rlSmarty->display($this->view . 'monetize_block.tpl');
        }
    }

    /**
     * @hook apTplBlocksBottom
     */
    public function hookApTplFooter()
    {
        if ($_GET['controller'] == 'blocks' && $_GET['block'] == 'monetize_listing_detail') {
            echo "<script type='text/javascript'>$(\"#pages_obj\").parent().hide();</script>";
        }

        if ($_GET['controller'] == 'listing_plans' || $_GET['controller'] == 'monetize') {
            $adminStyle = "<link href='" . RL_PLUGINS_URL . "monetize/static/admin_style.css' ";
            $adminStyle .= "type='text/css' rel='stylesheet' />";
            echo $adminStyle;

            echo "<script src='" . RL_PLUGINS_URL . "monetize/static/lib.js'></script>";
        }
    }

    /**
     * @since 1.3.0
     */
    public function hookApTplHeader()
    {
        if ($_GET['controller'] == 'monetize') {
            echo sprintf("<script type='text/javascript' src='%smonetize/static/lib.js'></script>", RL_PLUGINS_URL);
        }
    }

    /**
     * @hook profileController
     */
    public function hookProfileController()
    {

        if (!$this->cantAddListing($GLOBALS['account_info']['Type'])) {
            return false;
        }

        // load BumpUp Tab
        $this->bumpUp->prepareTab();

        // load Highlight Tab
        $this->highlight->prepareTab();
    }

    /**
     * @hook profileBlock
     */
    public function hookProfileBlock()
    {
        $GLOBALS['rlSmarty']->display($this->view . 'bump_up_tab.tpl');
        $GLOBALS['rlSmarty']->display($this->view . 'highlight_tab.tpl');
        $GLOBALS['rlSmarty']->display($this->view . 'js-code.tpl');
    }

    /**
     * Rebuild plans depending on bought unlim plan ID. All remain not bought plans should be removed from the list.
     *
     * @param  array  $plans    - Bump up plans
     * @param  int    $unlim_id - Bought unlim plan ID
     * @param  string $type     - Type of the Monetize Plan
     * @return array            - Modified plans
     */
    public function rebuildPlans($plans, $unlim_id, $type = 'bumpups')
    {
        //getting all not bought unlim plans ID's
        $sql = "SELECT `ID` FROM `" . RL_DBPREFIX . "monetize_plans` ";
        if ($type == 'bumpups') {
            $sql .= "WHERE `Bump_ups` = 0 AND `ID` != {$unlim_id} AND `Status` = 'active'";
        } else {
            $sql .= "WHERE `Highlights` = 0 AND `ID` != {$unlim_id} AND `Status` = 'active'";
        }
        $query_result = $GLOBALS['rlDb']->getAll($sql);
        foreach ($query_result as $id) {
            $unlim_ids[] = $id['ID'];
        }

        //removing all not bought unlim plans from the array
        foreach ($plans as $key => $plan) {
            if ($plan['ID'] == $unlim_id) {
                $plans[$key]['Price'] = 0;
            }
            if (in_array($plan['ID'], $unlim_ids)) {
                unset($plans[$key]);
            }
        }

        return $plans;
    }

    /**
     * Return first listing type with listings.
     *
     * @return string|bool $my_key - Listing type key or false if user doesn't added any listing yet
     */
    public function getNotEmptyListingType()
    {
        if ($GLOBALS['config']['one_my_listings_page']) {
            return 'my_all_ads';
        }

        $listing_types = $GLOBALS['rlListingTypes']->types;
        $my_key = false;
        $GLOBALS['modify_where'] = false;
        $GLOBALS['modify_highlight_where'] = false;

        foreach ($listing_types as $listing_type) {
            $listing_exist = $GLOBALS['rlListings']->getMyListings($listing_type['Key'], 'ID', 'asc', 0, 10);
            if (!empty($listing_exist)) {
                $my_key = $listing_type['My_key'];
                break;
            }
        }

        $GLOBALS['modify_where'] = true;
        $GLOBALS['modify_highlight_where'] = true;

        return $my_key;
    }

    /**
     * Remove monetize 'plan using' row
     *
     * @param int $id - ID of the row
     *
     * @return bool $result - true if row was removed successfully, false if not.
     */
    public function removePlanUsingRow($id)
    {
        $sql = "DELETE FROM `" . RL_DBPREFIX . "monetize_using` WHERE `ID` = {$id}";
        $result = $GLOBALS['rlDb']->query($sql);

        return $result;
    }

    /**
     * @hook smartyFetchHook
     */
    public function hookSmartyFetchHook(&$compiled_content, &$resource_name)
    {
        if (in_array($GLOBALS['page_info']['Controller'], $this->getAllowPagesControllers())) {
            $file_name = basename($resource_name);

            if ($file_name == 'content.tpl') {
                $html = Pharse::str_get_dom($compiled_content);

                foreach ($html('#listings article.item') as $listing) {
                    if ((bool) count($listing('i.highlight'))) {
                        $classList = explode(' ', $listing->getAttribute('class'));
                        $classList[] = 'highlight';
                        $listing->setAttribute('class', implode(' ', $classList));
                    }
                }

                $compiled_content = (string) $html;
            }
        }
    }

    /**
     * @hook myListingsSqlWhere.
     * @param string $sql - SQL query of the getMyListings method.
     */
    public function hookMyListingsSqlWhere(&$sql, $type)
    {
        if ($type == 'monetizeAll') {
            $find = "AND `T4`.`Type` = 'monetizeAll'";
            $sql = str_replace($find, '', $sql);
        }

        if ($GLOBALS['modify_where']) {
            $sql .= "AND `T1`.`Bumped` = '1' ";
        }

        if ($GLOBALS['modify_highlight_where']) {
            $sql .= "AND `T1`.`Date` != `T1`.`HighlightDate` AND `T1`.`HighlightDate` != '0000-00-00 00:00:00' ";
        }
    }

    /**
     * @hook listingNavIcons
     */
    public function hookListingNavIcons()
    {
        if ($GLOBALS['page_info']['Controller'] == 'profile') {
            $GLOBALS['rlSmarty']->display($this->view . 'bumped_up_date.tpl');
        }
    }

    /**
     * @hook TplListingPlanService
     */
    public function hookTplListingPlanService()
    {
        $GLOBALS['rlSmarty']->display($this->view . 'listing_plan.tpl');
    }

    /**
     * @hook  tplMyPackagesPlanService
     * @since 1.3.0
     */
    public function hookTplMyPackagesPlanService()
    {
        $GLOBALS['rlSmarty']->display($this->view . 'listing_plan.tpl');
    }

    /**
     * @hook  tplMyPackageItemListingInfo
     *
     * @since 1.4.0
     */
    public function hookTplMyPackageItemListingInfo()
    {
        $GLOBALS['rlSmarty']->display($this->view . 'my_package_item_listing_info.tpl');
    }

    /**
     * @hook  phpMyPackagesTop
     *
     * @since 1.4.0
     */
    public function hookPhpMyPackagesTop()
    {
        $GLOBALS['reefless']->loadClass('Plan');

        foreach ($GLOBALS['packages'] as &$package) {
            $planID = (int) $package['Plan_ID'];
            $planInfo = $GLOBALS['rlPlan']->getPlan($planID);

            $package += array(
                'Bumpups' => (int) $planInfo['Bumpups'],
                'Highlight' => (int) $planInfo['Highlight'],
                'Days_highlight' => (int) $planInfo['Days_highlight'],
            );
        }
    }

    /**
     * @hook apPhpListingsMassActions
     *
     * @since 1.4.0
     *
     * @param $ids
     * @param $action
     */
    public function hookApPhpListingsMassActions($ids, $action)
    {
        if ($action !== 'renew') {
            return;
        }

        $ids = explode('|', $ids);

        $sql = "UPDATE `" . RL_DBPREFIX . "listings` SET `Date` = NOW(), `Bumped` = '1' ";
        $sql .= "WHERE FIND_IN_SET(`ID`, '" . implode(',', $ids) . "')";
        $GLOBALS['rlDb']->query($sql);
    }

    /**
     * @hook PhpGetPlanByCategoryModifyField
     * @param string $sql - SQL query of the getPlan method
     * @param        $id  - ID of the plan
     */
    public function hookPhpGetPlanByCategoryModifyField(&$sql, $id)
    {
        $sql .= ' `T1`.`Days_highlight`, `T1`.`Bumpups`, `T1`.`Highlight`, ';
    }

    /**
     * @hook ListingsModifyField
     */
    public function hookListingsModifyField()
    {
        $GLOBALS['sql'] .= "IF(`T1`.`Date` != `T1`.`HighlightDate` ";
        $GLOBALS['sql'] .= "AND `T1`.`HighlightDate` != '0000-00-00 00:00:00', '1', '0') as 'is_highlighted', ";
    }

    /**
     * @hook  myListingsSqlFields
     *
     * @since 1.1.0
     */
    public function hookMyListingsSqlFields(&$sql)
    {
        $sql .= ", IF(`T1`.`Date` != `T1`.`HighlightDate` ";
        $sql .= "AND `T1`.`HighlightDate` != '0000-00-00 00:00:00', '1', '0') as 'is_highlighted' ";
    }

    /**
     * @hook  listingsModifyFieldSearch
     *
     * @since 1.1.0
     */
    public function hookListingsModifyFieldSearch(&$sql)
    {
        $sql .= "IF(`T1`.`Date` != `T1`.`HighlightDate` ";
        $sql .= "AND `T1`.`HighlightDate` != '0000-00-00 00:00:00', '1', '0') as 'is_highlighted', ";
    }

    /**
     * @hook  myListingsafterStatFields
     *
     * @since 1.1.0
     */
    public function hookMyListingsafterStatFields()
    {
        $GLOBALS['rlSmarty']->display($this->view . 'highlight_hook.tpl');
    }

    /**
     * @hook listingsModifyFieldByPeriod
     * @param $sql - SQL string of the getRecentlyAdded method
     */
    public function hookListingsModifyFieldByPeriod(&$sql)
    {
        $sql .= "IF(`T1`.`Date` != `T1`.`HighlightDate` ";
        $sql .= "AND `T1`.`HighlightDate` != '0000-00-00 00:00:00', '1', '0') as 'is_highlighted', ";
    }

    /**
     * @since 1.3.0
     *
     * @hook  listingsModifyFieldByAccount
     */
    public function hookListingsModifyFieldByAccount()
    {
        $GLOBALS['sql'] .= "IF(`T1`.`Date` != `T1`.`HighlightDate` ";
        $GLOBALS['sql'] .= "AND `T1`.`HighlightDate` != '0000-00-00 00:00:00', '1', '0') as 'is_highlighted', ";
    }

    /**
     * @hook  cronAdditional
     *
     * @since 1.1.0
     */
    public function hookCronAdditional()
    {
        global $rlDb;

        $sql = "SELECT `ID` FROM `" . RL_DBPREFIX . "listings` ";
        $sql .= "WHERE `HighlightDate` != '0000-00-00 00:00:00'  && `HighlightDate` < NOW()";
        $listings = $rlDb->getAll($sql);

        if (!empty($listings)) {
            $ids = array_map(
                function ($element) {
                    return $element['ID'];
                }, $listings
            );
            $ids = implode(',', $ids);

            $sql = "UPDATE  `" . RL_DBPREFIX . "listings` SET `HighlightDate` = '0000-00-00 00:00:00', ";
            $sql .= "`Highlight_Plan` = 0 WHERE `ID` IN ({$ids})";

            $rlDb->query($sql);
        }
    }

    /**
     * Working only for users who upgrade only free packages.
     *
     * @hook  phpMyPackagesRenewValidate
     * @since 1.1.0
     */
    public function hookPhpMyPackagesRenewValidate()
    {
        global $pack_info;

        if ($pack_info['Price'] <= 0) {
            $this->addMonetizeUsingDependingOnPlan($pack_info['Plan_ID']);
        }

    }

    /**
     * @hook  postPaymentComplete
     *
     * @since 1.1.0
     * @param array $data - Payment options
     */
    public function hookPostPaymentComplete($data)
    {
        $service = $GLOBALS['rlPayment']->getOption('service');

        if (in_array($service, array('listing', 'package'))) {
            if (!$GLOBALS['rlAccount']) {
                $GLOBALS['reefless']->loadClass('Account');
            }

            $accountID = (int) $GLOBALS['rlPayment']->getOption('account_id');
            $GLOBALS['account_info'] = $GLOBALS['account_info'] ?: $GLOBALS['rlAccount']->getProfile($accountID);
            $this->addMonetizeUsingDependingOnPlan($data['plan_id']);
        }
    }

    /**
     * @hook  phpMyPackagesRenewPreAction
     *
     * @since 1.1.0
     */
    public function hookPhpMyPackagesRenewPreAction()
    {
        $GLOBALS['rlPayment']->setOption('params', 'monetize');
    }

    /**
     * @hook  apPhpListingsAfterAdd
     * @since 1.2.1
     */
    public function hookApPhpListingsAfterAdd()
    {
        global $plan_info, $listing_id;

        if ($plan_info['Bumpups'] || $plan_info['Highlight']) {
            $this->addMonetizeUsingDependingOnPlan($plan_info['ID']);
        }
    }

    /**
     * @hook  apPhpPlansUsingAfterGrant
     * @since 1.2.1
     */
    public function hookApPhpPlansUsingAfterGrant()
    {
        $GLOBALS['account_info'] = $GLOBALS['account_data'];
        $packageInfo = array();

        foreach ($GLOBALS['plans'] as $package) {
            if ($package['ID'] == $GLOBALS['package_id']) {
                $packageInfo = $package;
                break;
            }
        }

        if ($packageInfo['Bumpups'] || $packageInfo['Highlight']) {
            $this->addMonetizeUsingDependingOnPlan($packageInfo['ID']);
        }
    }

    /**
     * @hook  apExtTransactionsService
     * @since 1.3.0
     *
     * @param array $paymentServicesMultilang - Multilanguage services
     */
    public function hookApExtTransactionsService(&$paymentServicesMultilang)
    {
        $paymentServicesMultilang = array_filter($paymentServicesMultilang, function ($item) {
            return ($item !== 'bump_up');
        });
    }

    /**
     * @hook  couponAttachCouponBox
     * @since 1.3.0
     *
     * @param string $service - Coupon code service name
     * @param int    $item_id - Payed service ID
     */
    public function hookCouponAttachCouponBox(&$service, &$item_id)
    {
        global $page_info;

        $monetizePlans = ($page_info['Controller'] == 'bumpup_page')
        ? $GLOBALS['bumpup_plans']
        : $GLOBALS['highlight_plans'];

        if (count($monetizePlans) == 1) {
            $firstPlan = reset($monetizePlans);
            $item_id = $firstPlan['ID'];
        }

        if (in_array($page_info['Controller'], array('bumpup_page', 'highlight_page'))) {
            $service = str_replace('_page', '', $page_info['Controller']);
        }
    }

    /**
     * @since 1.1.0
     */
    public function update_110()
    {
        global $rlDb;

        $sql = "SELECT `ID`,`HighlightDate`, `Highlight_Plan`  FROM `" . RL_DBPREFIX . "listings` ";
        $sql .= "WHERE `HighlightDate` != '0000-00-00 00:00:00'";
        $listings = $rlDb->getAll($sql);

        foreach ($listings as $listing) {
            $days = null;
            $h_plan = $this->highlight->getPlanInfo($listing['Highlight_Plan']);
            $days = $h_plan['Days'];

            if (!$h_plan) {
                $days = $rlDb->getOne(
                    'Days_highlight',
                    "ID = {$listing['Highlight_Plan']}",
                    'listing_plans'
                );
            }

            if ($days) {
                $sql = "UPDATE `" . RL_DBPREFIX . "listings` ";
                $sql .= "SET `HighlightDate` = DATE_ADD(`HighlightDate`, INTERVAL {$days} DAY) ";
                $sql .= "WHERE `ID` = {$listing['ID']}";
                $rlDb->query($sql);
            }
        }
    }

    /**
     *
     */
    public function update141()
    {
        global $rlDb;

        $rlDb->dropColumnFromTable('BumpDate', 'listings');
        $rlDb->addColumnToTable('Bumped', "ENUM('0', '1') DEFAULT '0'", 'listings');

        // delete hooks
        $sql = "DELETE FROM `" . RL_DBPREFIX . "hooks` ";
        $sql .= "WHERE `Plugin` = 'monetize' AND (`Name` = 'listingsModifyGroup' ";
        $sql .= "OR `Name` = 'listingsModifyGroupSearch' OR `Name` = 'phpListingTypeTop' OR `Name` = 'beforeImport')";
        $rlDb->query($sql);
    }

    /**
     * Plugin uninstall function
     */
    public function uninstall()
    {
        global $rlDb;

        $rlDb->dropColumnsFromTable(
            array(
                'Bumped',
                'HighlightDate',
                'Highlight_Plan',
                'Monetize_using_id',
            ),
            'listings'
        );

        $rlDb->dropColumnsFromTable(
            array(
                'Bumpups',
                'Highlight',
                'Days_highlight',
            ),
            'listing_plans'
        );

        if (method_exists($rlDb, 'dropTable')) {
            $rlDb->dropTable('monetize_plans');
            $rlDb->dropTable('monetize_using');
            return true;
        }

        $rlDb->query("DROP TABLE IF EXISTS `" . RL_DBPREFIX . "monetize_plans`");
        $rlDb->query("DROP TABLE IF EXISTS `" . RL_DBPREFIX . "monetize_using`");

        return true;
    }

    /**
     * Can user with provided listing type adding listings
     *
     * @param  string $account_type - Account type key
     * @return bool                 - Is user is available to add listings
     */
    public function cantAddListing($account_type)
    {
        $row = $GLOBALS['rlDb']->getOne('Abilities', "`Key` = '{$account_type}'", 'account_types');
        $abilities = explode(',', $row);

        // if export import plugin is active
        $plugins = $GLOBALS['plugins'] ?: $GLOBALS['aHooks'];
        if (key_exists('export_import', $plugins) && ($key = array_search('export_import', $abilities))) {
            unset($abilities[$key]);
        }

        $abilities = array_filter($abilities);
        if (empty($abilities)) {
            return false;
        }

        return true;
    }

    /**
     * Assign custom number of credits to account
     *
     * @since 1.3.0
     *
     * @param        $planID
     * @param        $value
     * @param        $accountID
     * @param string $type
     *
     * @return bool
     */
    public function addCustomPlanUsing($planID, $value, $accountID, $type = 'highlight')
    {
        $value = (int) $value;

        if (!$planID || $value < 0 || !$accountID || !in_array($type, array('highlight', 'bumpup'))) {
            return false;
        }

        $class = $type == 'highlight' ? 'highlight' : 'bumpUp';
        $planUsing = $this->{$class}->getPlanUsing($planID, $accountID);
        $planInfo = $this->{$class}->getPlanInfo($planID);

        if (!$planUsing && $value > 0) {
            $this->{$class}->addPlanUsing($planInfo, $type, 'plugin', $accountID, $value);
            $planUsing = $this->{$class}->getPlanUsing($planID, $accountID);
        }

        $planUsingRowID = $planUsing['ID'];

        if ($value == 0 && $planUsingRowID) {
            return $this->removePlanUsingRow($planUsingRowID);
        }

        $updateField = $type == 'highlight' ? 'Highlights_available' : 'Bumpups_available';

        $fields = array(
            $updateField => $value,
        );

        if ($type == 'highlight') {
            $fields['Days_highlight'] = $planUsing['Days_highlight'];
        }

        $update = array(
            'fields' => $fields,
            'where' => array(
                'ID' => $planUsingRowID,
            ),
        );

        $GLOBALS['rlActions']->updateOne($update, 'monetize_using');
    }

    /**
     * IncludeFilesInPages array getter
     *
     * @since 1.3.0
     *
     * @return array
     */
    public function getIncludeFilesInPages()
    {
        return $this->includeFilesInPages;
    }

    /**
     * IncludeFiltersInPages property setter
     *
     * @since 1.3.0
     *
     * @param array $includeFilesInPages
     */
    public function setIncludeFilesInPages($includeFilesInPages)
    {
        $this->includeFilesInPages = $includeFilesInPages;
    }

    /**
     * Add new page controller to the list
     *
     * @since 1.3.0
     *
     * @param string $controller - Page controller, which you want to add to the IncludingFilesInPages
     */
    public function addIncludePagesController($controller)
    {
        $listOfNewControllers = $this->getIncludeFilesInPages();
        $listOfNewControllers[] = $controller;

        $this->setIncludeFilesInPages($listOfNewControllers);
    }

    /**
     * Remove page controller from the controllers list
     *
     * @since 1.3.0
     *
     * @param string $controller - Page controller, which you want to remove from the IncludingFilesInPages
     */
    public function removePageController($controller)
    {
        $existingControllers = $this->getAllowPagesControllers();

        if (($key = array_search($controller, $existingControllers)) !== false) {
            unset($existingControllers[$key]);
            $this->setIncludeFilesInPages($existingControllers);
        }
    }

    /**
     * @since 1.3.0
     * @hook  ajaxRecentlyAddedLoadPost
     */
    public function hookAjaxRecentlyAddedLoadPost()
    {
        $GLOBALS['_response']->script('monetizer.highlightListings();');
    }

    /**
     * Getting users which are using provided monetize plan
     *
     * @since  1.3.0
     *
     * @param int $planID - Monetize plan ID
     *
     * @return array
     */
    public function getPlanUsingUsers($planID)
    {
        $planID = (int) $planID;

        if (!$planID) {
            return array();
        }

        $sql = "SELECT `Account_ID` FROM `" . RL_DBPREFIX . "monetize_using` WHERE `Plan_ID` = {$planID} ";

        return $GLOBALS['rlDb']->getAll($sql);
    }

    /**
     * Reassign highlight monetize plan to another
     *
     * @since  1.3.0
     *
     * @param int $from - Monetize plan id which you want to reassign
     * @param int $to   - Monetize plan id on which you want to assign
     *
     * @return bool
     */
    public function reassignMonetizePlanToAnother($from, $to)
    {
        $assignToPlanInfo = $this->highlight->getPlanInfo($to);
        $update = array(
            'fields' => array(
                'Plan_ID' => $to,
                'Days_highlight' => $assignToPlanInfo['Days'],
            ),
            'where' => array(
                'Plan_ID' => $from,
            ),
        );

        return (bool) $GLOBALS['rlActions']->update($update, 'monetize_using');
    }

    /**
     * Update to 1.3.0
     */
    public function update130()
    {
        global $rlDb;

        // copy vendor folder using component
        require_once RL_UPLOAD . 'monetize/vendor/autoload.php';
        $filesystem = new \Flynax\Component\Filesystem();
        $filesystem->copy(RL_UPLOAD . 'monetize/vendor', RL_PLUGINS . 'monetize/vendor');

        // change structure of database
        $rlDb->addColumnsToTable(array(
            'Credit_from' => "ENUM('flynax', 'plugin') DEFAULT 'plugin'",
        ), 'monetize_using');

        $rlDb->addColumnsToTable(array(
            'Monetize_using_id' => "INT(11) NOT NULL",
        ), 'listings');
    }

    /**
     * @since 1.4.0
     */
    public function hookTplListingItemClass()
    {
        $listing = $GLOBALS['rlSmarty']->_tpl_vars['listing'];
        if ($this->shouldHighlight($listing)) {
            echo rlHighlight::HIGLIGHT_HTML_CLASS . ' ';
        }
    }

    /**
     * @since 1.4.0
     */
    public function hookTplMyListingItemClass()
    {
        $listing = $GLOBALS['rlSmarty']->_tpl_vars['listing'];
        if ($this->shouldHighlight($listing)) {
            echo rlHighlight::HIGLIGHT_HTML_CLASS . ' ';
        }
    }

    /**
     * Check does listing should be highlighted by provided listing information
     *
     * @since 1.4.0
     *
     * @param array $listingInfo
     * @return bool
     * @throws \Exception
     */
    public function shouldHighlight($listingInfo)
    {
        if ($listingInfo['HighlightDate'] == '0000-00-00 00:00:00' || !$listingInfo) {
            return false;
        }

        try {
            $highlightData = new DateTime($listingInfo['HighlightDate']);
            $now = new DateTime();

            return $highlightData > $now;
        } catch (\Exception $e) {
            // todo: Think about debugging of this place. Should I write something to log, or just skip it as it is.
            return false;
        }
    }

    /**
     * @deprecated 1.4.1
     *
     * @hook listingsModifyGroup
     */
    public function hookListingsModifyGroup()
    {}

    /**
     * @deprecated 1.4.1
     *
     * @hook listingsModifyGroupSearch
     */
    public function hookListingsModifyGroupSearch()
    {}

    /**
     * @deprecated 1.4.1
     *
     * @hook phpListingTypeTop
     */
    public function hookPhpListingTypeTop()
    {}

    /**
     * @deprecated 1.4.1
     *
     * @hook  beforeImport
     *
     * @since 1.1.0
     *
     * @param array  $data
     * @param string $plugin
     * @param string $action
     */
    public function hookBeforeImport(&$data, $plugin, $action)
    {}

    /**
     * @deprecated 1.4.1
     *
     * Copy `Date` of the creation listing to the `BumpDate` field
     *
     * @since  1.3.0
     * @param  mixed $listingData - Listing ID or Listing Info array which you want to modify
     * @return bool                   - Does updating process was successful
     */
    public function updateBumpDateOfTheListing($listingData)
    {}
}
