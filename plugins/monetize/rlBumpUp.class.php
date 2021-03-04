<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : RLBUMPUP.CLASS.PHP
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

class rlBumpUp
{
    /**
     * @var string - plan type
     */
    private $plan_type = 'bumpup';

    /**
     * Adding new BumpUp plan from Admin Panel
     *
     * @param $data - Data for adding to the bump up table
     * @return string|bool - Key of the added Bump up plan, if it was added | False if something goes wrong
     */
    public function addPlan($data)
    {
        global $rlActions;

        $data['Type'] = $this->plan_type;
        if ($rlActions->insertOne($data, 'monetize_plans')) {
            $last_id = $GLOBALS['rlDb']->insertID();
            $bp_key = 'bumpup_' . $last_id;
            $update_data = array(
                'fields' => array(
                    'Key' => $bp_key,
                ),
                'where' => array(
                    'ID' => $last_id,
                ),
            );
            $rlActions->updateOne($update_data, 'monetize_plans');
            $result = $bp_key;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Return bump up plans by limit or all
     *
     * @param  int $start - Start from
     * @param  int $limit - Limit plans
     * @return array $data  - An array of the bump up plans
     */
    public function getPlans($start = false, $limit = false, $status = false)
    {
        global $rlLang, $lang;

        $sql = "SELECT * FROM `" . RL_DBPREFIX . "monetize_plans` ";
        $sql .= "WHERE `Type` = '{$this->plan_type}' ";
        $sql .= ($status) ? "AND `Status` = '{$status}' " : '';
        $sql .= ($start && $limit) ? "LIMIT {$start}, {$limit}" : '';
        $data = $GLOBALS['rlDb']->getAll($sql);
        foreach ($data as $key => $item) {
            $data[$key] = $rlLang->replaceLangKeys($item, 'bump_up_plan', array('name', 'description'), RL_LANG_CODE);
            $data[$key]['plan_type'] = $lang['bump_up_plan'];
            $data[$key]['Status'] = $lang[$data[$key]['Status']];
        }

        return $data;
    }

    /**
     * Getting bump up plan info
     *
     * @param  $plan_id - ID of the plan
     * @return data|bool   - Return plan info if plan exist | false if plan doesn't exist
     */
    public function getPlanInfo($plan_id)
    {
        global $rlLang;

        $sql = "SELECT * FROM `" . RL_DBPREFIX . "monetize_plans` WHERE `ID` = {$plan_id}";
        $data = $GLOBALS['rlDb']->getRow($sql);
        if ($data) {
            $data = $rlLang->replaceLangKeys($data, 'bump_up_plan', array('name', 'description'),
                RL_LANG_CODE);

            return $data;
        } else {
            return false;
        }
    }

    /**
     * Delete bump up plan and all associated phrases of the plan
     *
     * @param  int $plan_id - ID of the plan
     * @return array $out     - Response for ajax
     */
    public function deletePlan($plan_id)
    {
        global $lang, $rlDb;

        $plan_info = $this->getPlanInfo($plan_id);
        //delete plan
        $sql = "DELETE FROM `" . RL_DBPREFIX . "monetize_plans` WHERE  `ID` = {$plan_id}";
        $rlDb->query($sql);

        //delete lang keys
        $sql = "DELETE FROM `" . RL_DBPREFIX . "lang_keys` WHERE `Key` = 'bump_up_plan+name+{$plan_info['Key']}'";
        $sql .= "OR `Key` = 'bump_up_plan+description+{$plan_info['Key']}'";

        if ($rlDb->query($sql)) {
            $out['status'] = 'ok';
            $out['message'] = $lang['bump_up_deleted'];
        } else {
            $out['status'] = 'error';
            $out['message'] = $lang['bump_up_delete_error'];
        }

        return $out;
    }

    /**
     * This method run after payment successfully passed
     *
     * @param int   $item_id    - Listing ID
     * @param int   $plan_id    - Bump up ID
     * @param int   $account_id - ID of the account who bought bump ups
     * @param array $params     -  Additional parameters of the payment system
     */
    public function upgradeBumpUp($item_id = 0, $plan_id = 0, $account_id = 0, $params = array())
    {
        $GLOBALS['reefless']->loadClass('Account');
        $GLOBALS['account_info'] = $GLOBALS['rlAccount']->getProfile($account_id);
        $plan = $this->getPlanInfo($plan_id);
        $this->addPlanUsing($plan, 'bumpup');
        $this->bumpUp($item_id, $plan_id);
    }

    /**
     * Add bump up 'plan using' information
     *
     * @since  1.3.0   Added - $from, $accountID, $addBumpUps
     *
     * @param  array   $plan       - Information about plan. It can be listing or bumpup plan info
     * @param  string  $type       - Type of the plan: {bumpup/listing}
     * @param  string  $from       - From what part was assigned monetize using row: {plugin, flynax}
     * @param  int     $accountID  - Account to which you want to assign credits
     * @param  int     $addBumpUps - Number of bump up credits which you want to assign
     *
     * @return bool    $result - True if row was added, false if not.
     */
    public function addPlanUsing($plan = array(), $type = 'bumpup', $from = 'plugin', $accountID = 0, $addBumpUps = 0)
    {
        if (!is_array($plan)) {
            return false;
        }

        $plan_id = $plan['ID'];

        if ($type == 'bumpup') {
            $bumpups = $plan['Bump_ups'] ?: 0;
        } else {
            $bumpups = $plan['Bumpups'] > 0 ? $plan['Bumpups'] : 0;
        }
        $is_unlim = $bumpups ? 0 : 1;

        if ($addBumpUps) {
            $bumpups = $addBumpUps;
        }

        $data = array(
            'Account_ID' => $accountID ?: $GLOBALS['account_info']['ID'],
            'Plan_ID' => $plan_id,
            'Plan_type' => 'bumpup',
            'Date' => 'NOW()',
            'Bumpups_available' => $bumpups,
            'Is_unlim' => $is_unlim,
            'Credit_from' => $from,
        );

        $result = $GLOBALS['rlActions']->insertOne($data, 'monetize_using');

        return $result;
    }

    /**
     * Bump up listing ID
     *
     * @param  int   $listing_id   - Listing ID which user want to update
     * @param  int   $plan_id      - ID of the bump up plan
     * @return bool  $out          - Status of the bump up processing
     */
    public function bumpUp($listing_id = 0, $plan_id = 0)
    {
        global $rlDb;
        if (!$listing_id || !$plan_id) {
            return false;
        }

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

        $account_id = $GLOBALS['account_info']['ID'];
        $c_sql = "SELECT `ID`,`Bumpups_available` FROM  `" . RL_DBPREFIX . "monetize_using` ";
        $c_sql .= "WHERE `Account_ID` = {$account_id} AND `Plan_type` = 'bumpup' ";
        $account_plans = $rlDb->getAll($c_sql);

        $sql = "UPDATE `" . RL_DBPREFIX . "listings` SET `Date` = NOW(), `Bumped` = '1' ";
        $sql .= "WHERE `ID` = {$listing_id};";
        if ($rlDb->query($sql)) {
            if (!$plan_using['Is_unlim']) {
                $first_plan = array_shift($account_plans);
                $credits = $first_plan['Bumpups_available'];

                if ($credits == 1) {
                    $GLOBALS['rlMonetize']->removePlanUsingRow($first_plan['ID']);
                } else {
                    $updateData = array(
                        'fields' => array(
                            'Bumpups_available' => $credits - 1,
                        ),
                        'where' => array(
                            'ID' => $first_plan['ID'],
                        ),
                    );
                    $GLOBALS['rlActions']->updateOne($updateData, 'monetize_using');
                }
            }
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Getting plan usin row by plan_id and account
     *
     * @since 1.3.0
     *
     * @param int $plan_id   - Monetize plan ID
     * @param int $accountID
     *
     * @return bool|mixed  - $plan_using
     */
    public function getPlanUsing($plan_id = 0, $accountID = 0)
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
     * Return available credits by specified account.
     *
     * @since 1.3.0 Added $planID
     *
     * @param  int $account_id - ID of needed account
     * @param  int $planID     - Monetize plan ID
     * @return int
     */
    public function getCredits($account_id, $planID = 0)
    {
        $account_id = (int) $account_id;
        $planID = (int) $planID;

        if (!$account_id) {
            return false;
        }

        $sql = "SELECT SUM(`Bumpups_available`) AS `sum` FROM `" . RL_DBPREFIX . "monetize_using` ";
        $sql .= "WHERE `Account_ID` = {$account_id} ";

        if ($planID) {
            $sql .= "AND `Plan_ID` = {$planID} ";
        }

        $row = $GLOBALS['rlDb']->getRow($sql);

        return $row['sum'] ?: 0;
    }

    /**
     * Prepare BumpUp tab in the 'My profile' page
     */
    public function prepareTab()
    {
        global $account_info;

        if (!$account_info['ID']) {
            return false;
        }

        $GLOBALS['tabs']['bump_up_credits'] = array(
            'key' => 'bump_up',
            'name' => $GLOBALS['lang']['bumpups_credits'],
        );
        $info['bump_ups'] = $this->getCredits($account_info['ID']);

        $accountID = (int) $account_info['ID'];
        $sql = "SELECT `Date` FROM `" . RL_DBPREFIX . "monetize_using` AS `T1` ";
        $sql .= "LEFT JOIN `" . RL_DBPREFIX . "monetize_plans` AS `T2` ON `T1`.`Plan_ID` = `T2`.`ID` ";
        $sql .= "WHERE `T2`.`Type` = 'bumpup' AND `T1`.`Account_ID` = {$accountID} ";
        $sql .= "ORDER BY `T1`.`Date` DESC LIMIT 1";
        $row = $GLOBALS['rlDb']->getRow($sql);
        if ($row) {
            $info['last_purchased'] = $row['Date'];
        }

        $GLOBALS['modify_where'] = true;

        $info['bumpedUpListings'] = $GLOBALS['rlListings']->getMyListings(
            'monetizeAll',
            'Date',
            'desc',
            0,
            $GLOBALS['config']['listings_per_page']
        );

        $link = $GLOBALS['rlMonetize']->getNotEmptyListingType() ?: 'my_listings';
        $info['link'] = $GLOBALS['reefless']->getPageUrl($link);

        $GLOBALS['rlSmarty']->assign_by_ref('buInfo', $info);
        unset($GLOBALS['modify_where']);
    }

}
