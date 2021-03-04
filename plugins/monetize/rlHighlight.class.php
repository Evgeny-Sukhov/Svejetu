<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : RLHIGHLIGHT.CLASS.PHP
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

class rlHighlight
{
    /**
     * @since 1.4.0
     */
    const HIGLIGHT_HTML_CLASS = 'highlight';
    /**
     * @var string - plan type
     */
    private $plan_type = 'highlight';
    
    /**
     * @var string - default ordering field
     */
    private $orderBy = 'HighlightDate';

    /**
     * @since 1.3.0
     *
     * @var \rlActions
     */
    private $rlActions;

    /**
     * rlHighlight constructor.
     */
    public function __construct()
    {
        if (!$GLOBALS['rlActions']) {
            $GLOBALS['reefless']->loadClass('Actions');
        }

        $this->rlActions = $GLOBALS['rlActions'];
    }

    public function addPlan($data)
    {
        global $rlActions, $rlDb;
        $data['Type'] = $this->plan_type;
        if ($rlActions->insertOne($data, 'monetize_plans')) {
            $last_id = $rlDb->insertID();
            $h_key = 'highlight_' . $last_id;
            $update_data = array(
                'fields' => array(
                    'Key' => $h_key,
                ),
                'where' => array(
                    'ID' => $last_id,
                ),
            );
            $rlActions->updateOne($update_data, 'monetize_plans');
            $result = $h_key;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Return all highlight plans by limit or all
     *
     * @param  int    $start  - Start from
     * @param  int    $limit  - Limit plans
     * @param  string $status - Status of the plans
     * @return array  $data   - An array of the highlight plans
     */
    public function getPlans($start = 0, $limit = 0, $status = '')
    {
        global $rlLang, $lang;

        $sql = "SELECT * FROM `" . RL_DBPREFIX . "monetize_plans` ";
        $sql .= "WHERE `Type` = '{$this->plan_type}' ";
        $sql .= ($status) ? "AND `Status` = '{$status}' " : '';
        $sql .= ($start && $limit) ? "LIMIT {$start}, {$limit}" : '';
        $data = $GLOBALS['rlDb']->getAll($sql);

        foreach ($data as $key => $item) {
            $data[$key] = $rlLang->replaceLangKeys($item, 'highlight_plan', array('name', 'description'), RL_LANG_CODE);
            $data[$key]['plan_type'] = $lang['bump_up_plan'];
            $data[$key]['Status'] = $lang[$data[$key]['Status']];
        }

        return $data;
    }

    /**
     * Delete highlight plan.
     *
     * @since 1.3.0 Added - $withPlanUsing
     *
     * @param  int  $plan_id       - Highlight Plan ID
     * @param  bool $withPlanUsing - Delete plan with all related plan using
     *
     * @return array $out     - Answer for Ajax
     */
    public function deletePlan($plan_id, $withPlanUsing = false)
    {
        global $lang, $rlDb;

        $plan_info = $this->getPlanInfo($plan_id);

        // delete plan
        $sql = "DELETE FROM `" . RL_DBPREFIX . "monetize_plans` WHERE  `ID` = {$plan_id}";
        $rlDb->query($sql);

        // delete lang keys
        $sql = "DELETE FROM `" . RL_DBPREFIX . "lang_keys` WHERE `Key` = 'highlight_plan+name+{$plan_info['Key']}'";
        $sql .= "OR `Key` = 'highlight_plan+description+{$plan_info['Key']}'";

        if ($rlDb->query($sql)) {
            $out['status'] = 'ok';
            $out['message'] = $lang['m_highlight_plan_removed'];

            if ($withPlanUsing) {
                $sql = "DELETE FROM `" . RL_DBPREFIX . "monetize_using` WHERE  `Plan_ID` = {$plan_id}";
                $rlDb->query($sql);
            }
        } else {
            $out['status'] = 'error';
            $out['message'] = $lang['m_highlight_plan_remove_error'];
        }

        return $out;

    }

    /**
     * Getting highlight plan info
     *
     * @since 1.3.0 Added - $by
     *
     * @param  int        $plan_id - ID of the plan
     * @param  string     $by      - Getting plan of monetize of flynax
     * @return array|bool          - Return plan info if plan exist | false if plan doesn't exist
     */
    public function getPlanInfo($plan_id, $by = 'plugin')
    {
        global $rlLang;
        if (!$plan_id) {
            return false;
        }

        $planInfo = array();

        if ($by == 'plugin') {
            $sql = "SELECT * FROM `" . RL_DBPREFIX . "monetize_plans` WHERE `ID` = {$plan_id}";
            $data = $GLOBALS['rlDb']->getRow($sql);
            $planInfo = $rlLang->replaceLangKeys($data, 'highlight_plan', array('name', 'description'), RL_LANG_CODE);
        }

        if ($by == 'flynax') {
            if (!$GLOBALS['rlPlan']) {
                $GLOBALS['reefless']->loadClass('Plan');
            }

            $planInfo = $GLOBALS['rlPlan']->getPlan($plan_id);
            $planInfo['Days'] = $planInfo['Days_highlight'];
            unset($planInfo['Days_highlight']);
        }

        return $planInfo;
    }

    /**
     * Highlight listing.
     *
     * @param  int  $listing_id - ID of the Listing
     * @param  int  $plan_id    - ID of the Highlight Plan
     * @param  int  $days       - Highlight days
     * @return bool $result     - Status of the listing higlighting
     */
    public function highlight($listing_id, $plan_id, $days = 0)
    {
        global $rlDb;

        if ($plan_id != -1) {
            $plan_info = $this->getPlanInfo($plan_id);
            if ($plan_info['Price']) {
                $plan_using = $this->getPlanUsing($plan_id);
            } else {
                $plan_using['Is_unlim'] = true;
            }
        } else {
            $plan_using['Is_unlim'] = false;
        }
        $result = false;

        $h_sql = "SELECT `ID`,`Highlights_available`, `Plan_ID`, `Credit_from` FROM  `" . RL_DBPREFIX . "monetize_using` ";
        $h_sql .= "WHERE `Account_ID` = {$GLOBALS['account_info']['ID']} AND `Plan_type` = 'highlight' ";
        if ($days) {
            $h_sql .= "AND `Days_highlight` = $days";
        }
        $account_plans = $rlDb->getAll($h_sql);
        $monetize_plan_using_row = array_shift($account_plans);
        
        // check should I just increase days
        if ($highlight_info = $this->alreadyHighlighted($listing_id)) {
            $by_plan_id = $plan_id == -1 ? $monetize_plan_using_row['Plan_ID'] : $plan_id;
            $when_was_highlighted = new DateTime($highlight_info['HighlightDate']);
        } else {
            $by_plan_id = $monetize_plan_using_row['Plan_ID'] ?: $plan_id;
            $when_was_highlighted = new DateTime();
        }

        $highlight_plan = $this->getPlanInfo($by_plan_id, $monetize_plan_using_row['Credit_from']);
        $new_highlightDate = $when_was_highlighted->add(new DateInterval(sprintf('P%dD', $highlight_plan['Days'])));
        $highlight_date = $new_highlightDate->format('Y-m-d H:i:s');
        $sql = "UPDATE `" . RL_DBPREFIX . "listings` SET `HighlightDate` = '{$highlight_date}' ";
        if ($monetize_plan_using_row) {
            $sql .= ",`Highlight_Plan` = {$monetize_plan_using_row['Plan_ID']} ";
            $sql .= ",`Monetize_using_id` = {$monetize_plan_using_row['ID']} ";
        }
        $sql .= "WHERE `ID` = {$listing_id};";
        
        if ($rlDb->query($sql)) {
            if (!$plan_using['Is_unlim']) {
                $credits = $monetize_plan_using_row['Highlights_available'];
                if ($credits == 1) {
                    if (!$GLOBALS['rlMonetize']) {
                        $GLOBALS['reefless']->loadClass('Monetize', null, 'monetize');
                    }

                    $GLOBALS['rlMonetize']->removePlanUsingRow($monetize_plan_using_row['ID']);
                } else {
                    $updateData = array(
                        'fields' => array(
                            'Highlights_available' => $credits - 1,
                        ),
                        'where' => array(
                            'ID' => $monetize_plan_using_row['ID'],
                        ),
                    );
                    $GLOBALS['rlActions']->updateOne($updateData, 'monetize_using');
                }
            }
            $result = true;
        }

        return $result;
    }

    /**
     * Get plan using.
     *
     * @since 1.3.0 Added -  parameter $accountID
     *
     * @param  bool $plan_id   - ID of the plan
     * @param  int  $accountID
     * @return mixed|bool $plan_using - Plan using row | False if it doesn't exist
     */
    public function getPlanUsing($plan_id = false, $accountID = 0)
    {
        if (!$plan_id) {
            return false;
        }

        $account_id = $accountID ?: $GLOBALS['account_info']['ID'];
        $sql = "SELECT * FROM `" . RL_DBPREFIX . "monetize_using` ";
        $sql .= "WHERE `Account_ID` = {$account_id} AND `Plan_ID` = {$plan_id}";
        $plan_using = $GLOBALS['rlDb']->getRow($sql);
        $result = $plan_using ?: false;

        return $result;
    }

    /**
     * This method run after payment successfully passed
     *
     * @param int   $item_id    - Listing ID
     * @param int   $plan_id    - Bump up ID
     * @param int   $account_id - ID of the account who bought bump ups
     * @param array $params     - Additional parameters of the payment system
     */
    public function upgradeHighlight($item_id = 0, $plan_id = 0, $account_id = 0, $params = array())
    {
        $GLOBALS['reefless']->loadClass('Account');
        $GLOBALS['account_info'] = $GLOBALS['rlAccount']->getProfile($account_id);
        $plan = $this->getPlanInfo($plan_id);
        $this->addPlanUsing($plan);
        $this->highlight($item_id, $plan_id);
    }


    /**
     * Add plan using depending on the package. It can be Listing plan or Highlight plan.
     *
     * @since  1.3.0   Added - $from, $accountID, $addHighlights
     *
     * @param  array  $plan           - Plan info.
     * @param  string $type           - Plan type: {highlight/listing}
     * @param  string $from           - From what part was assigned monetize using row: {plugin, flynax}
     * @param  int     $accountID     - Account ID on which you want to assign credits
     * @param  int     $addHighlights - Highlight numbers
     *
     * @return bool                   - Is successfully added
     */
    public function addPlanUsing($plan = array(), $type = 'highlight', $from = 'plugin', $accountID = 0, $addHighlights = 0)
    {
        if (!is_array($plan)) {
            return false;
        }

        $plan_id = $plan['ID'];

        if ($type == 'highlight') {
            $highlights = $plan['Highlights'] ?: 0;
            $days_highlight = $plan['Days'] ?: 0;
        } else {
            $highlights = $plan['Highlight'] > 0 ? $plan['Highlight'] : 0;
            $days_highlight = $plan['Days_highlight'] ?: 0;
        }
        $is_unlim = $highlights ? 0 : 1;

        if ($addHighlights) {
            $highlights = $addHighlights;
        }

        $data = array(
            'Account_ID' => $accountID ?: $GLOBALS['account_info']['ID'],
            'Plan_ID' => $plan_id,
            'Plan_type' => 'highlight',
            'Date' => 'NOW()',
            'Highlights_available' => $highlights,
            'Days_highlight' => $days_highlight,
            'Is_unlim' => $is_unlim,
            'Credit_from' => $from,
        );

        return $this->rlActions->insertOne($data, 'monetize_using');
    }

    /**
     * Getting highlight information by Listing plan / package.
     *
     * @param int $plan_id - Listing plan package ID
     * @return array $plan - Highlight information depending on plan
     */
    public function getHighlightByPlan($plan_id)
    {
        $sql = "SELECT `Highlight`, `Days_highlight`  FROM `" . RL_DBPREFIX . "listing_plans` WHERE `ID` = {$plan_id} ";
        $plan = $GLOBALS['rlDb']->getRow($sql);

        return $plan;
    }

    /**
     * Return available credits by specified account.
     *
     * @param  int  $account_id - ID of needed account.
     * @param  int  $planID     - Highlight plan ID
     *
     * @return int  $row        - Highlight credits.
     */
    public function getCredits($account_id, $planID = 0)
    {
        $account_id = (int) $account_id;
        $planID = (int) $planID;

        if (!$account_id) {
            return false;
        }

        $sql = "SELECT SUM(`Highlights_available`) AS `sum` FROM `" . RL_DBPREFIX . "monetize_using` ";
        $sql .= "WHERE `Account_ID` = {$account_id} ";

        if ($planID) {
            $sql .= "AND `Plan_ID` = {$planID} ";
        }

        $row = $GLOBALS['rlDb']->getRow($sql);

        return $row['sum'] ?: 0;
    }

    /**
     * Prepare highlight tab data
     */
    public function prepareTab()
    {
        global $lang, $account_info;

        if (!$account_info['ID']) {
            return false;
        }

        $GLOBALS['tabs']['highlight_credits'] = array(
            'key' => 'highlight',
            'name' => $lang['m_highlight_credits'],
        );

        $info['highlights'] = $this->getCredits($account_info['ID']);

        $accountID = (int) $account_info['ID'];
        $sql = "SELECT `Date` FROM `" . RL_DBPREFIX . "monetize_using` AS `T1` ";
        $sql .= "LEFT JOIN `" . RL_DBPREFIX . "monetize_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
        $sql .= "WHERE `T2`.`Type` = 'highlight' AND `T1`.`Account_ID` = {$accountID} ";
        $sql .= "ORDER BY `T1`.`Date` DESC LIMIT 1";
        $row = $GLOBALS['rlDb']->getRow($sql);

        if ($row) {
            $info['last_purchased'] = $row['Date'];
        }

        // get last highlighted listings
        $GLOBALS['modify_highlight_where'] = true;
        $listings = $GLOBALS['rlListings']->getMyListings(
            'monetizeAll',
            $this->orderBy,
            'asc',
            0,
            $GLOBALS['config']['listings_per_page']
        );
        foreach ($listings as $key => $listing) {
            $plan_info = $this->getPlanInfo($listing['Highlight_Plan']);
            $now = new DateTime();
            $highlight_end = new DateTime($listing['HighlightDate']);
            $time_left = $now->diff($highlight_end);
            $listings[$key]['expiring_status'] = $lang['m_unhighlighted'];
            $left_phrase_array = array();
            
            if($time_left->d > 0 && !$time_left->invert) {
                $left_phrase_array[] = str_replace('{days}', $time_left->d, $lang['m_highlight_days']);
            }
            if($time_left->h > 0 && !$time_left->invert) {
                $left_phrase_array[] = str_replace('{hours}', $time_left->h, $lang['m_highlight_hours']);
            }
            
            if($left_phrase_array) {
                $listings[$key]['expiring_status'] = implode(',', $left_phrase_array) . $lang['m_highlight_left'];
            }
        }
        $info['highlightListings'] = $listings;
        unset($GLOBALS['modify_highlight_where']);

        $link = $GLOBALS['rlMonetize']->getNotEmptyListingType() ?: 'my_listings';
        $info['link'] = $GLOBALS['reefless']->getPageUrl($link);

        $GLOBALS['rlSmarty']->assign_by_ref('hInfo', $info);
    }

    /**
     * Modify <article> tags.
     *
     * @param  string $html - HTML container where all <articles> is located (<section id="listings"> in our case).
     * @return string $html - Modified tags.
     */
    function modifyArticles($html)
    {
        preg_match_all("/<article[^\>]*>[\s\S]*?<\/article>/", $html, $output_array);
        $articles = $output_array[0];

        foreach ($articles as $article) {
            if ($this->hasElement($article)) {
                $modified_article = $this->addClass('highlight', $article);
                $html = str_replace($article, $modified_article, $html);
            }
        }

        return $html;
    }

    /**
     * Checking if child exist in the element.
     *
     * @param  string $html - Element, where child nodes will be search.
     * @return bool         - Did element found? True or false
     */
    function hasElement($html)
    {
        $re = '/<i .*class="highlight .*"[^\>]*>[\s\S]*?<\/i>/m';
        preg_match_all($re, $html, $is_exist);
        $is_exist = array_filter($is_exist);
        if (!empty($is_exist)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add class to the element (article in our case).
     *
     * @param  string $class       - Class which you wan't to add
     * @param  string $element     - Element to which class will be added
     * @return string $new_element - Changed element
     */
    function addClass($class, $element)
    {
        $old_classes = $this->getClasses($element);
        $new_classes = $old_classes . " " . $class;
        $new_element = str_replace($old_classes, $new_classes, $element);

        return $new_element;
    }

    /**
     * Get class attribute of the "<article>" HTML element.
     *
     * @param  string $element - HTML element.
     * @return string $class   - Value of the class attribute
     */
    function getClasses($element)
    {
        $re = '/<article\s*class="([^"]*)"/m';
        preg_match($re, $element, $matches);
        $class = '';
        if ($matches[1]) {
            $class = $matches[1];
        }
        
        return $class;
    }

    /**
     * Rebuild all plans depending bought unlimited plan.
     *
     * @param  array  $plans    - Highlight plans
     * @param  int    $unlim_id - Bought unlimited plan ID
     * @param  string $type     - Type of the plan
     * @return array  $plans    - Modified plans
     */
    public function rebuildPlans($plans, $unlim_id, $type = 'highlight')
    {
        //getting all not bought unlim plans ID's
        $sql = "SELECT `ID` FROM `" . RL_DBPREFIX . "monetize_plans` ";
        $sql .= "WHERE `Highlights` = 0 AND `ID` != {$unlim_id} AND `Status` = 'active'";
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
     * Method is checking, does listings was highlighted before
     *
     * @param  int        $listing_id   - Listing ID
     * @return array|bool $listing_info - Highlight data of the listing in success case, or false.
     */
    public function alreadyHighlighted($listing_id)
    {
        $sql = "SELECT `HighlightDate`, `Highlight_Plan` FROM `" . RL_DBPREFIX . "listings` ";
        $sql .= "WHERE `ID` = {$listing_id}";
        $listing_info = $GLOBALS['rlDb']->getRow($sql);
        
        if ($listing_info['Highlight_Plan']) {
            return $listing_info;
        }
    
        return false;
    }
}
