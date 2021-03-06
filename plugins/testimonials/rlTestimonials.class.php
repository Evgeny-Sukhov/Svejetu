<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LISENSE: FL7YNR66E9FU - https://www.flynax.com/flynax-software-eula.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN : svejetu.me
 *	FILE   : RLTESTIMONIALS.CLASS.PHP
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

class rlTestimonials extends reefless {
	
	/**
	* get single testimonial
	* 
	* @todo get single testimonial data and assign it to the smarty
	**/
	function getOne() {
		global $rlSmarty;
		
		$testimonial = $this -> fetch(array('Author', 'Testimonial', 'Date'), array('Status' => 'active'), "ORDER BY Rand()", 1, 'testimonials', 'row');
		$rlSmarty -> assign_by_ref('testimonial_box', $testimonial);
	}
	
	/**
	* get all active testimonials
	* 
	* @todo get all testimonials data and assign them to the smarty
	**/
	function get() {
		global $rlSmarty;
		
		$testimonials = $this -> fetch(array('Author', 'Testimonial', 'Date'), array('Status' => 'active'), "ORDER BY `Date` DESC", null, 'testimonials');
		$rlSmarty -> assign_by_ref('testimonials', $testimonials);
		$rlSmarty -> assign('total', count($testimonials));
	}
	
	/**
	* add testimonial
	*
	* @package AJAX
	*
	* @param text $name - 
	* @param text $listing_id - listing_id
	*
	**/
	function ajaxAdd( $name = false, $email = false, $testimonial = false, $code = false )
	{
		global $_response, $rlValid, $rlSmarty, $lang, $account_info, $config;
		
		$rlValid -> sql($name);
		$rlValid -> sql($email);
		$rlValid -> sql($testimonial);
		$rlValid -> sql($code);
		
		/* check required fields */
		if ( empty($name) ) {
			$errors[] = str_replace('{field}', '<span class="field_error">'. $lang['your_name'] .'</span>', $lang['notice_field_empty']);
			$error_fields .= '#t-name,';
		}
		
		if ( !empty($email) && !$rlValid -> isEmail($email) ) {
			$errors[] = $lang['notice_bad_email'];
			$error_fields .= '#t-email,';
		}

		if ( empty($testimonial) || strlen($testimonial) < 20 ) {
			$errors[] = $lang['testimonial_not_valid_content'];
			$error_fields .= '#t-testimonial,';
		}

		if ( $code != $_SESSION['ses_security_code'] || empty($_SESSION['ses_security_code']) ) {
			$errors[] = $lang['security_code_incorrect'];
			$error_fields .= '#security_code,';
		}
		
		if ( $errors ) {
			$error_content = '<ul>';
			foreach ($errors as $error) {
				$error_content .= "<li>" . $error . "</li>";
			}
			$error_content .= '</ul>';
			
			$error_fields = $error_fields ? substr($error_fields, 0, -1) : '';
			$_response -> script("printMessage('error', '{$error_content}', '{$error_fields}')");
		}
		else {
			$this -> loadClass('Actions');
			
			$insert = array(
				'Author' => $name,
				'Account_ID' => $account_info['ID'],
				'Testimonial' => $testimonial,
				'Date' => 'NOW()',
				'Email' => $email,
				'IP' => $_SERVER['REMOTE_ADDR'],
				'Status' => $config['testimonials_moderate'] ? 'pending' : 'active'
			);
			
			$GLOBALS['rlActions'] -> insertOne($insert, 'testimonials');
			
			$message = $config['testimonials_moderate'] ? $lang['testimonials_accepted_to_moderation'] : $lang['testimonials_posted'];
			
			$_response -> script("
				$('form[name=testimonial-form]').find('input,textarea').val('');
				$('#security_img').trigger('click');
				printMessage('notice', '{$message}');
			");
			
			if ( !$config['testimonials_moderate'] ) {
				$_response -> script("
					$('#testimonials_area').html('');
					flynax.slideTo('body');
				");
				
				$this -> get();
				
				$tpl = RL_ROOT .'plugins'. RL_DS .'testimonials'. RL_DS .'dom.tpl';
				$_response -> assign('testimonials_area', 'innerHTML', $rlSmarty -> fetch($tpl, null, null, false));
				
				$set_dir = RL_LANG_DIR == 'rtl' ? 'top' : 'right';
				$_response -> script("
					var color = $('.testimonials div.hlight').css('background-color');
					$('.testimonials div.triangle').css('border-{$set_dir}-color', color);
				");
			}
		}
		
		$_response -> script("$('form[name=testimonial-form] input[type=submit]').val('{$lang['send']}')");
		
		return $_response;
	}
	
	/**
	* delete testimonial
	*
	* @package ajax
	*
	* @param int $id - testimonial ID
	*
	**/
	function ajaxDelete( $id = false )
	{
		global $_response, $lang;

		// check admin session expire
		if ( $this -> checkSessionExpire() === false )
		{
			$redirect_url = RL_URL_HOME . ADMIN ."/index.php";
			$redirect_url .= empty($_SERVER['QUERY_STRING']) ? '?session_expired' : '?'. $_SERVER['QUERY_STRING'] .'&session_expired';
			$_response -> redirect( $redirect_url );
		}
		
		if ( !$id )
			return $_response;
		
		$id = (int)$id;
		
		/* remove category filter box entry */
		$sql = "DELETE FROM `". RL_DBPREFIX ."testimonials` WHERE `ID` = '{$id}' LIMIT 1";
		$this -> query($sql);
		
		$_response -> script("
			testimonialsGrid.reload();
			printMessage('notice', '{$lang['item_deleted']}');
		");

		return $_response;
	}
	
	/**
	* build admin panel statistics section
	**/
	function apStatistics()
	{
		global $plugin_statistics, $lang;
		
		$total = $this -> getRow("SELECT COUNT(`ID`) AS `Count` FROM `". RL_DBPREFIX ."testimonials`");
		$total = $total['Count'];
		
		$pending = $this -> getRow("SELECT COUNT(`ID`) AS `Count` FROM `". RL_DBPREFIX ."testimonials` WHERE `Status` = 'pending'");
		$pending = $pending['Count'];
		
		$link = RL_URL_HOME . ADMIN . '/index.php?controller=testimonials';
		
		$plugin_statistics[] = array(
			'name' => $lang['testimonials_testimonials'],
			'items' => array(
				array(
					'name' => $lang['total'],
					'link' => $link,
					'count' => $total
				),
				array(
					'name' => $lang['pending'] .' / '. $lang['new'],
					'link' => $link .'&amp;status=pending',
					'count' => $pending
				)
			)
		);
	}
}