<?php
//***** Installer *****
include_once('class.emember_meta.php');

//***Installer***
function wp_emember_activate(){	
	global $wpdb;
    if (function_exists('is_multisite') && is_multisite()) {
    	// check if it is a network activation - if so, run the activation function for each blog id
    	if (isset($_GET['networkwide']) && ($_GET['networkwide'] == 1)) {
                    $old_blog = $wpdb->blogid;
    		// Get all blog ids
    		$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
    		foreach ($blogids as $blog_id) {
    			switch_to_blog($blog_id);
    			wp_emember_installer();
    			wp_emember_upgrader();
    			wp_emember_initialize_db();
    		}
    		switch_to_blog($old_blog);
    		return;
    	}	
    } 
    wp_emember_installer();
    wp_emember_upgrader();
    wp_emember_initialize_db();    
}
function wp_emember_installer(){
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
	global $wpdb;
	$wpememmeta = new WPEmemberMeta(); 
	if($wpdb->get_var("SHOW TABLES LIKE '" . $wpememmeta->get_table('member') ."'") != $wpememmeta->get_table('member')){
	   $sql = "CREATE TABLE " . $wpememmeta->get_table('member') . " (
	          `member_id` int(12) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	          `user_name` varchar(32) NOT NULL,
	          `first_name` varchar(32) DEFAULT '',
	          `last_name` varchar(32) DEFAULT '',
	          `password` varchar(64) NOT NULL,
	          `member_since` date NOT NULL DEFAULT '0000-00-00',
	          `membership_level` smallint(6) NOT NULL,
	          `more_membership_levels` VARCHAR(100) DEFAULT NULL,
	          `account_state` enum('active','inactive','expired','pending','unsubscribed') DEFAULT 'pending',
	          `last_accessed` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	          `last_accessed_from_ip` varchar(15) NOT NULL,
	          `email` varchar(64) DEFAULT NULL,
	          `phone` varchar(64) DEFAULT NULL,
	          `address_street` varchar(255) DEFAULT NULL,
	          `address_city` varchar(255) DEFAULT NULL,
	          `address_state` varchar(255) DEFAULT NULL,
	          `address_zipcode` varchar(255) DEFAULT NULL,
	          `country` varchar(255) DEFAULT NULL,
	          `gender` enum('male','female','not specified') DEFAULT 'not specified',
	          `referrer` varchar(255) DEFAULT NULL,
	          `extra_info` text,
	          `reg_code` varchar(255) DEFAULT NULL,
	          `subscription_starts` date DEFAULT NULL,
	          `initial_membership_level` smallint(6) DEFAULT NULL,
	          `txn_id` varchar(64) DEFAULT '',
	          `subscr_id` varchar(32) DEFAULT '',
	          `company_name` varchar(100) DEFAULT '',
	          `flags` int(11) DEFAULT '0'
	      );";
	   dbDelta($sql);
	
	   // Add default options	
	   include_once('emember_config.php');
	   $emember_config = Emember_Config::getInstance();
	   //$emember_config->loadConfig();
	   $emember_config->setValue('eMember_reg_firstname' ,"checked='checked'");
	   $emember_config->setValue('eMember_reg_lastname'  ,"checked='checked'");
	   $emember_config->setValue('eMember_edit_firstname',"checked='checked'"); 
	   $emember_config->setValue('eMember_edit_lastname' ,"checked='checked'");
	   $emember_config->setValue('eMember_edit_company'  ,"checked='checked'");
	   $emember_config->setValue('eMember_edit_email'    ,"checked='checked'");
	   $emember_config->setValue('eMember_edit_phone'    ,"checked='checked'");
	   $emember_config->setValue('eMember_edit_street'   ,"checked='checked'");
	   $emember_config->setValue('eMember_edit_city'     ,"checked='checked'");
	   $emember_config->setValue('eMember_edit_state'    ,"checked='checked'");
	   $emember_config->setValue('eMember_edit_zipcode'  ,"checked='checked'");
	   $emember_config->setValue('eMember_edit_country'  ,"checked='checked'");
	   $emember_config->saveConfig();
	}
	if($wpdb->get_var("SHOW TABLES LIKE '" . $wpememmeta->get_table('member_meta') ."'") != $wpememmeta->get_table('member_meta')){
	   $sql = "CREATE TABLE IF NOT EXISTS " .$wpememmeta->get_table('member_meta'). " (
	          umeta_id bigint(20) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
	  		  user_id bigint(20) unsigned NOT NULL DEFAULT '0',
	          meta_key varchar(255) DEFAULT NULL,
	          meta_value longtext,
	          KEY user_id (user_id)
	      ) ;";
	   dbDelta($sql);
	}
	
	if($wpdb->get_var("SHOW TABLES LIKE '" . $wpememmeta->get_table('session') . "'") != $wpememmeta->get_table('session')){
	   $sql = "CREATE TABLE " .$wpememmeta->get_table('session') . " (
	         id INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	         session_id VARCHAR( 100 ) NOT NULL ,
	         user_id INT NOT NULL ,
	         last_impression TIMESTAMP NOT NULL ,
	         logged_in_from_ip varchar(15) NOT NULL,
	         UNIQUE (session_id)
	      ) ENGINE = MYISAM ";
	   dbDelta($sql);	
	}
	
	if($wpdb->get_var("SHOW TABLES LIKE '" . $wpememmeta->get_table('membership_level') . "'") != $wpememmeta->get_table('membership_level')){
	   $sql = "CREATE TABLE ".$wpememmeta->get_table('membership_level')." (
	         id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	         alias varchar(127) NOT NULL,
	         role varchar(255) NOT NULL DEFAULT 'subscriber',
	         permissions tinyint(4) NOT NULL DEFAULT '0',
	         subscription_period int(11) NOT NULL DEFAULT '-1',
	         subscription_unit   VARCHAR(15)        NULL,
	         loginredirect_page  text NULL,
	         category_list longtext,
	         page_list longtext,
	         post_list longtext,
	         comment_list longtext,
	         disable_bookmark_list longtext,
	         options longtext,
	         campaign_name varchar(60) NOT NULL DEFAULT ''         
	      ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
	   dbDelta($sql);
	   //file_put_contents(WP_PLUGIN_DIR .'/' . WP_EMEMBER_FOLDER .  '/temp1.txt', serialize($wpdb));   
	   $sql = "SELECT * FROM " . $wpememmeta->get_table('membership_level') . " WHERE id = 1";
	   $results = $wpdb->get_row($sql);
	   if(is_null($results)){
	      $sql = "INSERT INTO  ".$wpememmeta->get_table('membership_level')."  (
	            id ,
	            alias ,
	            role ,
	            permissions ,
	            subscription_period ,
	            subscription_unit,
	            loginredirect_page,
	            category_list ,
	            page_list ,
	            post_list ,
	            comment_list,
	            disable_bookmark_list,
	            options,
	            campaign_name
	         )VALUES (NULL , 'Content Protection', 'administrator', '15', '0',NULL,NULL, NULL , NULL , NULL , NULL,NULL,NULL,''
	         );";
	      $wpdb->query($sql);
	   }	   
	   if($wpdb->get_var("SHOW TABLES LIKE '" . $wpememmeta->get_table('openid') ."'") != $wpememmeta->get_table('openid')){
	       $sql = "CREATE TABLE " .$wpememmeta->get_table('openid') . " (
	              `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	              `emember_id` INT NOT NULL ,
	              `openuid` INT NOT NULL ,
	              `type` VARCHAR( 20 ) NOT NULL
	               );";
	       dbDelta($sql); 
	   }
	   
	   // Add default options
	   add_option("wp_eMember_db_version", $wpememmeta->get_db_version());
	}
}
//***Upgrader***
function wp_emember_upgrader(){
	global $wpdb;
	$wpememmeta = new WPEmemberMeta();
	$installed_ver = get_option( "wp_eMember_db_version" );
	
	if( $installed_ver != $wpememmeta->get_db_version() )
	{
	   $sql = "CREATE TABLE " . $wpememmeta->get_table('member') . " (
	          `member_id` int(12) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	          `user_name` varchar(32) NOT NULL,
	          `first_name` varchar(32) DEFAULT '',
	          `last_name` varchar(32) DEFAULT '',
	          `password` varchar(64) NOT NULL,
	          `member_since` date NOT NULL DEFAULT '0000-00-00',
	          `membership_level` smallint(6) NOT NULL,
	          `more_membership_levels` VARCHAR(100) DEFAULT NULL,
	          `account_state` enum('active','inactive','expired','pending','unsubscribed') DEFAULT 'pending',
	          `last_accessed` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	          `last_accessed_from_ip` varchar(15) NOT NULL,
	          `email` varchar(64) DEFAULT NULL,
	          `phone` varchar(64) DEFAULT NULL,
	          `address_street` varchar(255) DEFAULT NULL,
	          `address_city` varchar(255) DEFAULT NULL,
	          `address_state` varchar(255) DEFAULT NULL,
	          `address_zipcode` varchar(255) DEFAULT NULL,
	          `country` varchar(255) DEFAULT NULL,
	          `gender` enum('male','female','not specified') DEFAULT 'not specified',
	          `referrer` varchar(255) DEFAULT NULL,
	          `extra_info` text,
	          `reg_code` varchar(255) DEFAULT NULL,
	          `subscription_starts` date DEFAULT NULL,
	          `initial_membership_level` smallint(6) DEFAULT NULL,
	          `txn_id` varchar(64) DEFAULT '',
	          `subscr_id` varchar(32) DEFAULT '',
	          `company_name` varchar(100) DEFAULT '',
	          `flags` int(11) DEFAULT '0'
	      );";
	      dbDelta($sql);
	    $sql = "CREATE TABLE " . $wpememmeta->get_table('session') . " (
	         id INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	         session_id VARCHAR( 100 ) NOT NULL ,
	         user_id INT NOT NULL ,
	         logged_in_from_ip varchar(15) NOT NULL,
	         last_impression TIMESTAMP NOT NULL ,
	         UNIQUE (session_id)
	      ) ENGINE = MYISAM ";
	      dbDelta($sql);
	   $sql = "CREATE TABLE IF NOT EXISTS " .$wpememmeta->get_table('member_meta'). " (
	         umeta_id  bigint(20) unsigned NOT NULL  PRIMARY KEY AUTO_INCREMENT,
	  		 user_id  bigint(20) unsigned NOT NULL DEFAULT '0',
	         meta_key  varchar(255) DEFAULT NULL,
	         meta_value longtext,
	         KEY user_id (user_id),
	         KEY meta_key (meta_key)
	      ) ;";
	      dbDelta($sql);
	   $sql = "CREATE TABLE ".$wpememmeta->get_table('membership_level')." (
	         id int(11) NOT NULL  PRIMARY KEY AUTO_INCREMENT,
	         alias varchar(127) NOT NULL,
	         role varchar(255) NOT NULL DEFAULT 'subscriber',
	         permissions tinyint(4) NOT NULL DEFAULT '0',
	         subscription_period int(11) NOT NULL DEFAULT '-1',
	         subscription_unit   VARCHAR(15)        NULL,
	         loginredirect_page  text NULL,
	         category_list longtext,
	         page_list longtext,
	         post_list longtext,
	         comment_list longtext,
	         disable_bookmark_list longtext,
	         options longtext,
	         campaign_name varchar(60) NOT NULL DEFAULT ''
	      ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
	   dbDelta($sql);
       $sql = "CREATE TABLE " .$wpememmeta->get_table('openid') . " (
              `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
              `emember_id` INT NOT NULL ,
              `openuid` INT NOT NULL ,
              `type` VARCHAR( 20 ) NOT NULL
              ) ;";
           dbDelta($sql); 
	   
	   //file_put_contents(WP_PLUGIN_DIR .'/' . WP_EMEMBER_FOLDER .  '/temp.txt', serialize($wpdb));
	   
	   $sql = "SELECT * FROM " . $wpememmeta->get_table('membership_level') . " WHERE id = 1";
	   $results = $wpdb->get_row($sql);
	   if(is_null($results))
	   {
	      $sql = "INSERT INTO  ".$wpememmeta->get_table('membership_level')."  (
	            id ,
	            alias ,
	            role ,
	            permissions ,
	            subscription_period ,
	            subscription_unit,
	            category_list ,
	            page_list ,
	            post_list ,
	            comment_list,
	            disable_bookmark_list,
	            options
	         )VALUES (NULL , 'Content Protection', '1', '0', '0', NULL,NULL , NULL , NULL , NULL,NULL,NULL
	         );";
	      $wpdb->query($sql);
	   }
	   // Add default options
	   update_option("wp_eMember_db_version", $wpememmeta->get_db_version());
	}
}
/******************************************************************/
/*** === Other upgrade/default value setting realated tasks === ***/
/******************************************************************/
function wp_emember_initialize_db(){
	include_once('emember_config.php');
	$emember_config = Emember_Config::getInstance();
	   	
	$wp_eMember_rego_field_default_settings_ver = 1;	
	$currently_installed_ver = get_option( "wp_eMember_rego_field_default_settings_ver" );
	if($currently_installed_ver < $wp_eMember_rego_field_default_settings_ver)
	{
	   //$emember_config->loadConfig();
	   $emember_config->setValue('eMember_reg_firstname' ,"checked='checked'");
	   $emember_config->setValue('eMember_reg_lastname'  ,"checked='checked'");
	   $emember_config->setValue('eMember_edit_firstname',"checked='checked'"); 
	   $emember_config->setValue('eMember_edit_lastname' ,"checked='checked'");
	   $emember_config->setValue('eMember_edit_company'  ,"checked='checked'");
	   $emember_config->setValue('eMember_edit_email'    ,"checked='checked'");
	   $emember_config->setValue('eMember_edit_phone'    ,"checked='checked'");
	   $emember_config->setValue('eMember_edit_street'   ,"checked='checked'");
	   $emember_config->setValue('eMember_edit_city'     ,"checked='checked'");
	   $emember_config->setValue('eMember_edit_state'    ,"checked='checked'");
	   $emember_config->setValue('eMember_edit_zipcode'  ,"checked='checked'");
	   $emember_config->setValue('eMember_edit_country'  ,"checked='checked'");
	   $emember_config->saveConfig();	
	   add_option("wp_eMember_rego_field_default_settings_ver", $wp_eMember_rego_field_default_settings_ver);
	}
	
	/*** Settings default values at activation time ***/
	$senders_email_address = get_bloginfo('name')." <".get_bloginfo('admin_email').">";
	$eMember_email_subject = "Complete your registration";
	$eMember_email_body = "Dear {first_name} {last_name}".
				  "\n\nThank you for joining us!".
				  "\n\nPlease complete your registration by visiting the following link:".
				  "\n\n{reg_link}".
				  "\n\nThank You";        
	
	add_option('senders_email_address', stripslashes($senders_email_address));
	add_option('eMember_email_subject', stripslashes($eMember_email_subject));
	add_option('eMember_email_body', stripslashes($eMember_email_body));
	
	$admin_email = get_option('admin_email');
	$emember_config->addValue('eMember_admin_notification_email_address',$admin_email);
	
	$emember_config->addValue('eMember_account_upgrade_email_subject',"Member Account Upgraded");
	$emember_config->addValue('eMember_account_upgrade_email_body',"Your account has been upgraded successfully");
	
	/*** Setting default values at upgrade time ***/
	$emember_config->addValue('eMember_profile_thumbnail',"checked='checked'");
	$secrete_code = uniqid();
	$emember_config->addValue('wp_eMember_secret_word_for_post',$secrete_code);	
	
	$emember_config->setValue('wp_eMember_plugin_activation_check_flag','1');	
	
	/*** Create the mandatory configuration pages ***/
	// Check and create the member login page
	$create_login_page = '';
	if(!array_key_exists('login_page_url', $emember_config->configs))
	{
		$create_login_page = '1';
	}
	else if(array_key_exists('login_page_url', $emember_config->configs))
	{
		if($emember_config->getValue('login_page_url') == "")
		{
			$create_login_page = '1';
		}
	}
	if($create_login_page == '1')
	{
		// Create a new page object for Collect-Details page
		$eMember_login_page = array(
			'post_type' => 'page',
		    'post_title' => 'Member Login',
		    'post_content' => '[wp_eMember_login]',
		    'post_status' => 'publish'
		);	
		$page_id = wp_insert_post($eMember_login_page);
		$eMember_login_page_permalink = get_permalink($page_id);
		$emember_config->setValue('login_page_url',$eMember_login_page_permalink);
	}	
	// Check and create the member join us page
	$create_join_page = '';
	if(!array_key_exists('eMember_payments_page', $emember_config->configs))
	{
		$create_join_page = '1';
	}
	else if(array_key_exists('eMember_payments_page', $emember_config->configs))
	{
		if($emember_config->getValue('eMember_payments_page') == "")
		{
			$create_join_page = '1';
		}
	}
	if($create_join_page == '1')
	{
		// Create a new page object for Collect-Details page
		$eMember_join_page_content = '<p style="color:red;font-weight:bold;">This page and the content has been automatically generated for you to give you a basic idea of how a Join Us page should look like. You can customize this page however you like it by editing this page from your WordPress page editor.</p>';
		$eMember_join_page_content = '<p style="font-weight:bold;">If you end up changing the URL of this page then make sure to update the URL value in the pages/forms settings menu of the plugin.</p>';
		$eMember_join_page_content .= '<p style="border-top:1px solid #ccc;padding-top:10px;margin-top:10px;"></p>
			<strong>Free Membership</strong>
			<br />
			You get unlimited access to free membership content
			<br />
			<em><strong>Price: Free!</strong></em>
			<br /><br />Link the following image to go to the Registration Page if you want your visitors to be able to create a free membership account<br /><br />
			<img title="Join Now" src="'.WP_EMEMBER_URL.'/images/join-now-button-image.gif" alt="Join Now Button" width="277" height="82" />
			<p style="border-bottom:1px solid #ccc;padding-bottom:10px;margin-bottom:10px;"></p>';		
		$eMember_join_page_content .= '<p><strong>You can register for a Free Membership or pay for one of the following membership options</strong></p>';
		$eMember_join_page_content .= '<p style="border-top:1px solid #ccc;padding-top:10px;margin-top:10px;"></p>
			[ ==> Insert Payment Button For Your Paid Membership Levels Here <== ]
			<p style="border-bottom:1px solid #ccc;padding-bottom:10px;margin-bottom:10px;"></p>';
		
		$eMember_join_page = array(
			'post_type' => 'page',
		    'post_title' => 'Join Us',
		    'post_content' => $eMember_join_page_content,
		    'post_status' => 'publish'
		);	
		$join_page_id = wp_insert_post($eMember_join_page);
		$eMember_join_page_permalink = get_permalink($join_page_id);
		$emember_config->setValue('eMember_payments_page',$eMember_join_page_permalink);
		// Create registration page
		$eMember_rego_page = array(
			'post_type' => 'page',
		    'post_title' => 'Registration',
		    'post_content' => '[wp_eMember_registration]',
			'post_parent' => $join_page_id,
		    'post_status' => 'publish'
		);	
		$rego_page_id = wp_insert_post($eMember_rego_page);
		$eMember_rego_page_permalink = get_permalink($rego_page_id);
		$emember_config->setValue('eMember_registration_page',$eMember_rego_page_permalink);		
	}		
	/*** end of mandatory configuration page creation ***/
	
	//Save the data
	$emember_config->saveConfig();			
}
//***** End Installer *****
?>