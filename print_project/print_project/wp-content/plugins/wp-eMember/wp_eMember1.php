<?php
/********************************************
***      THIS IS NOT A FREE PLUGIN        ***
*** DO NOT COPY ANY CODE FROM THIS PLUGIN ***
*********************************************/
require_once ( ABSPATH . WPINC . '/pluggable.php' );
require_once ( ABSPATH . WPINC . '/registration.php' );

define('WP_EMEMBER_FOLDER', dirname(plugin_basename(__FILE__)));
define('WP_EMEMBER_URL', plugins_url('',__FILE__));
define('WP_EMEMBER_PATH',plugin_dir_path( __FILE__ ));
if ($_SERVER["HTTPS"] == "on"){$gravatar_url = "http://www.gravatar.com/avatar";}
else {$gravatar_url = "https://secure.gravatar.com/avatar";}
define('WP_EMEMBER_GRAVATAR_URL',$gravatar_url);
include_once('emember_config.php');
$emember_config = Emember_Config::getInstance();
$config = $emember_config;
$lang = $emember_config->getValue('eMember_language');
if (!empty($lang))$eMember_language_file = "lang/".$lang.".php";
else $eMember_language_file = "lang/eng.php";

include_once($eMember_language_file); 
include_once('eMember_db_access.php');
include_once('eMember_misc_functions.php');
include_once('emember_auth.php');
include_once('emember_ajax.php');
include_once('emember_access_checkers.php');
include_once('emember_custom_feed.php');
include_once('eMember_auto_responder_handler.php');
include_once('eMember_debug_handler.php');
include_once('wp_emember_fb_reg_handler.php');
include_once('emember_scheduled_membership_upgrade.php');
include_once('eMember_auth_utils.php');
include_once('eMember_bookmark_utils.php');
include_once ('eMember_registration_utils.php');
include_once('eMember_profile_utils.php');
if(isset($_REQUEST['member_logout']) && $_REQUEST['member_logout']=='1'){
	//Make sure the user is fully logged out
	$emember_auth = Emember_Auth::getInstance();
	$auth = $emember_auth;	
	$emember_auth->logout();
	$auth->logout();
}
else{
	//initialize the auth class
	$emember_auth = Emember_Auth::getInstance();
	$auth = $emember_auth;
}

if(isset($_GET['emember_feed_key'])){
	$emember_auth->login(array('md5ed_id'=>$_GET['emember_feed_key']));
}
define('WP_EMEMBER_ENABLE_AUTO_LOGIN_AFTER_REGO', false);

if(isset($_REQUEST['doLogin'])){
	
	if(isset($_REQUEST['emember_u_name']) && isset($_REQUEST['emember_pwd'])){
		$_POST['login_user_name'] = $_REQUEST['emember_u_name'];
		$_POST['login_pwd']= $_REQUEST['emember_pwd'];
	}
	emember_login();
}
function emember_login($redirect = true){
    global $wpdb;
    global $emember_config;
    global $emember_auth;
    $emember_auth = Emember_Auth::getInstance();
    $emember_config = Emember_Config::getInstance();    
    $credentials = array();
    $credentials['user']       = $_POST['login_user_name'];
    $credentials['pass']       = $_POST['login_pwd']; 
    $credentials['rememberme'] = (isset($_POST['rememberme'])?$_POST['rememberme'] : false);
    $emember_auth->login($credentials);
    if($emember_auth->isLoggedIn()){    	
        $user_id = username_exists( $_POST['login_user_name'] );        
        $after_login_page = $emember_config->getValue('after_login_page');
        $membership_level = $emember_auth->getUserInfo('membership_level');
        $membership_level_resultset = $emember_auth->userInfo->primary_membership_level;
        $_SESSION['membership_level_name'] = $membership_level_resultset->alias;
        
        //Log into the affiliate account if the option is set
        $eMember_auto_affiliate_account_login = $emember_config->getValue('eMember_auto_affiliate_account_login');
        if($eMember_auto_affiliate_account_login && function_exists('wp_aff_platform_install')){
            $_SESSION['user_id']= $_POST['login_user_name'];
    	    if(isset($_POST['rememberme'])){
    	    	setcookie("user_id", $_POST['login_user_name'], time()+60*60*24*7, "/");    	
    	    }
    	    else{
    	    	setcookie("user_id", $_POST['login_user_name'], time()+60*60*6, "/");
    	    }
        }
        
        $sign_in_wp = $emember_config->getValue('eMember_signin_wp_user');
        if($sign_in_wp){   
            if($user_id){
                $preserve_role = $emember_auth->getUserInfo('flags');
                if(($preserve_role & 1) != 1){ 
                	$user_info = get_userdata($user_id);
                	$user_cap = is_array($user_info->wp_capabilities)?array_keys($user_info->wp_capabilities):array();
                    $account_stat = $emember_auth->getUserInfo('account_state');
                    
                	if(($account_stat === 'active') && !in_array('administrator',$user_cap))
                		update_wp_user_Role($user_id, $membership_level_resultset->role);
                }
                update_account_status($_POST['login_user_name']);
                wp_signon(array(
                                'user_login'=>$_POST['login_user_name'],
                                'user_password'=>$_POST['login_pwd'],
                                'remember'=>isset($_POST['rememberme'])?$_POST['rememberme']:''
                                ),
                                is_ssl() ? true : false);
            }
        }  
        if($redirect){
            $enable_after_login_redirect = $emember_config->getValue('eMember_enable_redirection');                      
            if($enable_after_login_redirect){ 
                if(!empty($membership_level_resultset->loginredirect_page)){
                    header('Location: ' . $membership_level_resultset->loginredirect_page);exit;
                }
                else if(!empty($after_login_page)){
                    header('Location: ' . $after_login_page);exit;
                }
            }   
        }
    }    
}
if(isset($_POST['eMember_update_profile']))
{
	global $wpdb,$emember_config;
    $emember_config = Emember_Config::getInstance();    
        include_once(ABSPATH . WPINC . '/class-phpass.php');
        require_once ( ABSPATH . WPINC . '/registration.php' );
        	    
        $resultset  = dbAccess::find(WP_EMEMBER_MEMBERS_TABLE_NAME, ' member_id=' . 
                      $wpdb->escape($_POST['member_id'])); 
        $wp_user_id = username_exists($resultset->user_name);  
        $updatable = true;     
        if(isset($_POST['wp_emember_email'])){
        	$emmber_email_owner = emember_email_exists($_POST['wp_emember_email']);
        	$wp_email_owner = email_exists($_POST['wp_emember_email']);
	        if(!is_email($_POST['wp_emember_email'])){
	            $_POST['eMember_profile_update_result'] = EMEMBER_EMAIL_INVALID;
	            $updatable = false;
	        }
	        else if(($wp_email_owner&&($wp_email_owner!=$wp_user_id))||($emmber_email_owner &&($emmber_email_owner!=$_POST['member_id']))){
	            $_POST['eMember_profile_update_result']= '<span class="emember_error">'.EMEMBER_EMAIL_UNAVAIL.' </span>';
	            $updatable = false;
	        }
	} 
	if (($_POST['wp_emember_pwd'] != $_POST['wp_emember_pwd_r'])){
	     $_POST['eMember_profile_update_result']  = '<span class="emember_error">'.EMEMBER_PASSWORD_MISMATCH.'</span>';
	     $updatable = false;	    		    	
	} 

        if($updatable)
        {   	    
            $wp_hasher = new PasswordHash(8, TRUE);	    
            $fields = array();
            if(isset($_POST['wp_emember_firstname']))$fields['first_name']      = $_POST['wp_emember_firstname'];
            if(isset($_POST['wp_emember_lastname']))$fields['last_name']        = $_POST['wp_emember_lastname'];
            if(isset($_POST['wp_emember_email']))$fields['email']               = $_POST['wp_emember_email'];
            if(isset($_POST['wp_emember_phone']))$fields['phone']               = $_POST['wp_emember_phone'];
            if(isset($_POST['wp_emember_street']))$fields['address_street']     = $_POST['wp_emember_street'];
            if(isset($_POST['wp_emember_city']))$fields['address_city']         = $_POST['wp_emember_city'];
            if(isset($_POST['wp_emember_state']))$fields['address_state']       = $_POST['wp_emember_state'];
            if(isset($_POST['wp_emember_zipcode']))$fields['address_zipcode']   = $_POST['wp_emember_zipcode'];
            if(isset($_POST['wp_emember_country']))$fields['country']           = $_POST['wp_emember_country'];
            if(isset($_POST['wp_emember_gender']))$fields['gender']             = $_POST['wp_emember_gender'];  
            if(isset($_POST['wp_emember_company_name']))$fields['company_name'] = $_POST['wp_emember_company_name'];          
            if(!empty($_POST['wp_emember_pwd'])){
                $password = $wp_hasher->HashPassword($_POST['wp_emember_pwd']);
                $fields['password'] = $password;            
            }

            if($wp_user_id) {
                $wp_user_info  = array();
                $wp_user_info['first_name']    = $_POST['wp_emember_firstname'];
                $wp_user_info['last_name']     = $_POST['wp_emember_lastname'];
                $wp_user_info['user_email']    = $_POST['wp_emember_email'];
                $wp_user_info['ID']            = $wp_user_id;
                
                if(!empty($_POST['wp_emember_pwd'])) $wp_user_info['user_pass'] = $_POST['wp_emember_pwd'];                                            
                wp_update_user( $wp_user_info );
            }

            if(count($fields)>0){
	            $ret = dbAccess::update(WP_EMEMBER_MEMBERS_TABLE_NAME, ' member_id ='. $wpdb->escape($_POST['member_id']), $fields);
	            if(isset($_POST['emember_custom'])){
	            	$custom_fields = dbAccess::find(WP_EMEMBER_MEMBERS_META_TABLE, ' user_id=' . $_POST['member_id'] . ' AND meta_key=\'custom_field\'');
	            	if($custom_fields)
		                $wpdb->query('UPDATE ' . WP_EMEMBER_MEMBERS_META_TABLE . 
		                ' SET meta_value ='. '\''.serialize($_POST['emember_custom']). '\' WHERE meta_key = \'custom_field\' AND  user_id=' . $_POST['member_id']);
	                else 
		                $wpdb->query("INSERT INTO " . WP_EMEMBER_MEMBERS_META_TABLE . 
		                '( user_id, meta_key, meta_value ) VALUES(' . $_POST['member_id'] .',"custom_field",' . '\''.serialize($_POST['emember_custom']).'\')');
	            }
            else{
	            $wpdb->query('DELETE FROM ' . WP_EMEMBER_MEMBERS_META_TABLE . 
	            '  WHERE meta_key = \'custom_field\' AND  user_id=' . $wpdb->escape($_POST['member_id']));
            	
            }
	            if($ret === false){
	            	$_POST['eMember_profile_update_result'] = 'Failed';
	            }
	            else {
	            	$_POST['eMember_profile_update_result'] = EMEMBER_PROFILE_UPDATED;
	            	//Update the affiliate end if using the auto affiliate feature
	            	eMember_handle_affiliate_profile_update($_POST);
	            }
            }
    	}
}

add_shortcode("wp_eMember_total_members","emember_total_members_handler");
add_shortcode("wp_eMember_first_name","emember_first_name_handler");
add_shortcode("wp_eMember_last_name","emember_last_name_handler");

add_shortcode("free_rego_with_email_confirmation","free_rego_with_email_confirmation_handler");
add_shortcode("emember_protected","emember_protected_handler"); // validation error
add_shortcode('wp_eMember_registration_form_for','wp_eMember_registration_form_handler');
add_shortcode('wp_eMember_compact_login','eMember_compact_login_widget');
add_shortcode('wp_eMember_renew_membership_for_free', 'wp_eMember_renew_membership_for_free_handler');
add_shortcode('wp_eMember_upgrade_membership_level_to', 'wp_eMember_upgrade_membership_level_to_handler');
add_shortcode('wp_eMember_login','eMember_login_widget');
add_shortcode('wp_eMember_registration','show_registration_form');
add_shortcode('wp_eMember_edit_profile','show_edit_profile_form');
add_shortcode('wp_eMember_user_list','show_eMember_public_user_list');
add_shortcode('wp_eMember_user_bookmarks','print_eMember_bookmark_list');
add_shortcode('wp_eMember_password_reset','print_password_reset_form');
//facebook feature
/*$enable_fb = $emember_config->getValue('eMember_enable_fb_reg');
if($enable_fb){
    add_shortcode('wp_emember_fb_reg','wp_emember_fb_reg_handler');
    add_filter('language_attributes','add_fb_namespace');
}*/
 //facebook feature
//$first_click_enabled = $emember_config->getValue('eMember_google_first_click_free'); 

if(!emember_is_first_click()){
    add_filter('the_content','secure_content', 3000);
    add_action('comment_text','comment_text_action');
}
if(function_exists('wp_footer')){
    add_action('wp_footer', 'wp_emember_password_reminder_at_footer');
}
 else {
    add_filter('the_content', 'wp_emember_password_reminder_filter',3001); /// validation error
}
add_filter('the_content', 'do_shortcode',11);
add_filter('the_content', 'filter_eMember_registration_form');
add_filter('the_content', 'filter_eMember_public_user_list');
add_filter('the_content', 'filter_eMember_login_form');
add_filter('the_content', 'filter_eMember_edit_profile_form');
add_filter('the_content', 'filter_eMember_bookmark_list');
add_filter('do_enclose', 'eMember_delete_enclosure' );
add_filter('rss_enclosure', 'eMember_delete_enclosure' );
add_filter('atom_enclosure', 'eMember_delete_enclosure' );
$enable_more_tag = $emember_config->getValue('eMember_enable_more_tag');
if($enable_more_tag) add_filter( 'the_content_more_link', 'eMember_my_more_link', 10, 2 );
$enable_bookmark = $emember_config->getValue('eMember_enable_bookmark');
if($enable_bookmark) add_filter('the_content', 'bookmark_handler');

$lockdown_domain = $emember_config->getValue('eMember_enable_domain_lockdown');
if($lockdown_domain)add_action('wp_head', 'lockdown_widget');

add_action('shutdown', 'wp_emember_shutdown_chores');
add_action('init', 'export_members_to_csv');
add_action('init', 'load_library');
add_action('init', 'wp_eMember_widget_init');
add_action('init', 'emember_menu');
add_action('profile_update','sync_emember_profile', $id);
add_action('wp_logout', 'logout_handler');
add_action('init', 'del_bookmark');
//add_action('do_feed_ememberfeed', 'emember_create_custom_feed', 10, 1);
add_action('wp_ajax_emember_upload_ajax', 'wp_emem_upload_file');
add_action('wp_ajax_nopriv_check_name', 'wp_emem_check_user_name');
add_action('wp_ajax_item_list_ajax', 'item_list_ajax');
add_action('wp_ajax_access_list_ajax', 'access_list_ajax');
add_action('wp_ajax_send_mail', 'wp_emem_send_mail');
add_action('wp_ajax_nopriv_send_mail', 'wp_emem_send_mail');
add_action('wp_ajax_check_level_name', 'wp_emem_check_level_name');
add_action('wp_ajax_add_bookmark', 'wp_emem_add_bookmark');
add_action('wp_ajax_wp_user_list_ajax', 'wp_emem_wp_user_list_ajax');
add_action('wp_ajax_emember_user_list_ajax', 'wp_emem_user_list_ajax');
add_action('wp_ajax_emember_user_count_ajax', 'wp_emem_user_count_ajax');
add_action('wp_ajax_emember_wp_user_count_ajax','wp_emem_wp_user_count_ajax');
add_action('wp_ajax_nopriv_emember_public_user_list_ajax', 'wp_emem_public_user_list_ajax');
add_action('wp_ajax_nopriv_emember_public_user_profile_ajax', 'wp_emem_public_user_profile_ajax');
add_action('wp_ajax_nopriv_delete_profile_picture', 'wp_emem_delete_image');
add_action('wp_ajax_delete_profile_picture', 'wp_emem_delete_image');
add_action('wp_ajax_get_post_preview', 'wp_emem_get_post_preview');
add_action('wp_ajax_nopriv_openid_login','wp_emem_openid_login');
add_action('wp_ajax_nopriv_openid_logout','wp_emem_openid_logout');
add_action('wp_ajax_nopriv_emember_ajax_login', 'emember_ajax_login');
add_action('wp_ajax_emember_ajax_login', 'emember_ajax_login');
add_action('wp_eMember_email_notifier_event', 'eMember_email_notifier');
add_action('wp_eMember_scheduled_membership_upgrade_event', 'wp_eMember_scheduled_membership_upgrade');
add_action('admin_menu', 'eMember_add_custom_box');
add_action('save_post', 'eMember_save_postdata');
add_action('wp_authenticate', 'wp_login_callback', 1, 2 );
add_filter('wp_head','add_dynamicjs');
if (is_admin())add_action('admin_menu','wp_eMember_add_admin_menu');
if (is_admin())add_action('admin_notices', 'wp_eMember_plugin_conflict_check');
$emember_allow_comment = $emember_config->getValue('eMember_member_can_comment_only');
if($emember_allow_comment){
    add_action ('init','emember_check_comment');
    add_filter('wp_head','emember_customise_comment_form');
    add_filter('comment_form_defaults','emember_change_comment_field');
    function emember_change_comment_field($fields){
        global $emember_auth; 
        $emember_auth = Emember_Auth::getInstance();
        $emember_config = Emember_Config::getInstance();        
        if(!$emember_auth->isLoggedIn()){
            $fields = array();
            $login_link = EMEMBER_PLEASE ." ". eMember_get_login_link_only_based_on_settings_condition('1'). EMEMBER_TO_COMMENT;
            $fields['comment_field']= $login_link;
        }
        return $fields;
    }    
    function emember_customise_comment_form($c){
        global $emember_auth;
        $emember_auth = Emember_Auth::getInstance();
        $emember_config = Emember_Config::getInstance();        
        if(!$emember_auth->isLoggedIn()){
            $login_link  = EMEMBER_PLEASE ." ". eMember_get_login_link_only_based_on_settings_condition('1'). EMEMBER_TO_COMMENT;
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($){
                $('#respond').html('<?php echo $login_link;?>');
            });
        </script>
        <?php
        }
    }
    function emember_check_comment(){
        global $emember_auth;
        $emember_auth = Emember_Auth::getInstance();
        $emember_config = Emember_Config::getInstance();        
        if(!$emember_auth->isLoggedIn()){
            if(isset($_POST['comment_post_ID'])){
                $_POST = array();
                wp_die('Comments not allowed.');
            }
        }
    }
}
function add_dynamicjs(){
    global $emember_config;
    $emember_config = Emember_Config::getInstance();    
    include_once('dynamicjs.php');
}
function wp_emember_password_reminder_at_footer(){
    wp_emember_hook_password_reminder();
}
function wp_emember_password_reminder_filter($content){
    global $emember_auth;
    $emember_auth = Emember_Auth::getInstance();
    $emember_config = Emember_Config::getInstance();    
    if($emember_auth->password_reminder_block_added == 'no'){
        $emember_auth->password_reminder_block_added = 'yes';
        ob_start();
        wp_emember_hook_password_reminder();
        $output = ob_get_contents();
        ob_end_clean();
        return $output . $content;
    }
    return $content;
}
/* used to check various common conflicts and report to the user */
function wp_eMember_plugin_conflict_check()
{
	global $emember_config;
    $emember_config = Emember_Config::getInstance();    
	$msg = "";    
	//Check schemea
	$installed_schema_version = get_option("wp_eMember_db_version");
	if($installed_schema_version != WP_EMEMBER_DB_VERSION)
	{
		$msg .= '<div class="error"><br />It looks like you did not follow the <a href="http://www.tipsandtricks-hq.com/wordpress-membership/?p=3" target="_blank">WP eMember upgrade instruction</a> to update the plugin. The database schema is out of sync and need to be updated. Please deactivate the plugin and follow the <a href="http://www.tipsandtricks-hq.com/wordpress-membership/?p=3" target="_blank">upgrade instruction from here</a> to upgrade the plugin and correct this.<br /><br /></div>';
	}
	    	
	$activation_flag_value = $emember_config->getValue('wp_eMember_plugin_activation_check_flag');
    if($activation_flag_value != '1' && empty($msg))
    {
        //no need check for conflict
        return;
    }
    		
	if(function_exists('bb2_install'))
	{
		$msg .= '<div class="updated fade">You have the Bad Behavior plugin active! This plugin is known to block PayPal\'s payment notification (IPN). Please see <a href="http://www.tipsandtricks-hq.com/forum/topic/list-of-plugins-that-dont-play-nice-conflicting-plugins" target="_blank">this post</a> for more details.</div>';
	}
	if (function_exists('wp_cache_serve_cache_file'))
	{
		// WP Supercache is active 
		if(!defined('TIPS_AND_TRICKS_SUPER_CACHE_OVERRIDE')){
			$msg .= '<div class="updated fade">You have the WP Super Cache plugin active. Please make sure to follow <a href="http://www.tipsandtricks-hq.com/forum/topic/using-the-plugins-together-with-wp-super-cache-plugin" target="_blank">these instructions</a> to make it work with the WP eMember plugin. You can ignore this message if you have already applied the recommended changes.</div>';
		}	
	}	
	if (function_exists('w3tc_pgcache_flush'))
	{				
		$integration_in_place = false;
		$w3_pgcache = & W3_PgCache::instance();
	    foreach ($w3_pgcache->_config->get_array('pgcache.reject.cookie') as $reject_cookie) {
	    	if (strstr($reject_cookie,"eMember_in_use") !== false){
	    		$integration_in_place = true;
	    	}   	
        }	
        if(!$integration_in_place){
        	$msg .= '<div class="updated fade">You have the W3 Total Cache plugin active. Please make sure to follow <a href="http://www.tipsandtricks-hq.com/forum/topic/using-the-plugins-with-w3-total-cache-plugin" target="_blank">these instructions</a> to make it work with the WP eMember plugin.</div>';
        }	
	}
	
	//Check for duplicate copies of the eMember plugin
	$plugins_list = get_plugins();
	$plugin_names_arrray = array();
	foreach ($plugins_list as $plugin)
	{
		$plugin_names_arrray[] = $plugin['Name'];
	}
	$plugin_unqiue_count = array_count_values($plugin_names_arrray);
	if($plugin_unqiue_count['WP eMember']>1)
	{
		$msg .= '<div class="error"><br />It looks like you have two copies (potentially different versions) of the WP eMember plugin in your plugins directory. This can be the source of many problems. Please delete every copy of the eMember plugin from your plugins directory to clean it out then upload one fresh copy. <a href="http://www.tipsandtricks-hq.com/wordpress-membership/?p=3" target="_blank">More Info</a><br /><br /></div>';
	}
		
	if(!empty($msg))
	{
		echo $msg;	
	}
	else
	{
		//Set this flag so it does not do the conflict check on every page load
		$emember_config->setValue('wp_eMember_plugin_activation_check_flag','');	
		$emember_config->saveConfig();	
	}
}

/* Adds a custom section to the "advanced" Post and Page edit screens */
function eMember_add_custom_box() {    
    if( function_exists( 'add_meta_box' )) {
//	    add_meta_box( 'eMember_sectionid', __( 'eMember Protection options', 'eMember_textdomain' ), 
//	                'eMember_inner_custom_box', 'post', 'advanced' );
        $post_types = get_post_types();
        foreach($post_types as $post_type=>$post_type)
	    add_meta_box( 'eMember_sectionid', __( 'eMember Protection options', 
                'eMember_textdomain' ), 'eMember_inner_custom_box', $post_type, 'advanced' );
	} 
	else {//older version doesn't have custom post type so modification isn't needed.
	    add_action('dbx_post_advanced', 'eMember_old_custom_box' );
	    add_action('dbx_page_advanced', 'eMember_old_custom_box' );
	}
}
function wp_emember_shutdown_chores(){	
	global $emember_auth;
    $emember_auth = Emember_Auth::getInstance();
    $emember_config = Emember_Config::getInstance();    
	if($emember_auth->loggedin_for_feed)$emember_auth->logout();
}  
//eMember_email_notifier();
function eMember_email_notifier(){
	global $wpdb;
	global $emember_config;
    $emember_config = Emember_Config::getInstance();    
	$s = $emember_config->getValue('eMember_email_notification');
	if(empty($s)) return;
	
	$query = "SELECT id, subscription_period,subscription_unit FROM " . 
	         WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE . " WHERE id !=1 and subscription_unit !=''";
	$levels = $wpdb->get_results($query);
	if(is_array($levels)){
		foreach($levels as $level){
			$interval = "";			
			switch ($level->subscription_unit){
				case 'Years':
					$interval = $level->subscription_period *365;
					break;
				case 'Months':
					$interval = $level->subscription_period * 30;
					break;
				case 'weeks':
					$interval = $level->subscription_period * 7;
					break;					
				case 'Days':
					$interval = $level->subscription_period; 	
			}
			if(!empty($interval)){
				$query = "SELECT email FROM ".WP_EMEMBER_MEMBERS_TABLE_NAME.			          
				         " WHERE date_add( `subscription_starts` , INTERVAL " . ($interval+1) . " DAY ) = CURDATE( )" .
				         " AND membership_level = " . $level->id;
				$result = $wpdb->get_results($query);
				if(!empty($result)){
					$subject = $emember_config->getValue('eMember_after_expiry_email_subject');
					$body    =  $emember_config->getValue('eMember_after_expiry_email_body');
					$headers = 'From: '.$emember_config->getValue('eMember_after_expiry_senders_email_address') . "\r\n";
					$email_list = array();					
					foreach($result as $row) $email_list[] = $row->email;
					wp_mail($email_list, $subject, $body, $headers);	
				}
				$alert_before = $emember_config->getValue('eMember_before_expiry_num_days');
				if(($interval - $alert_before)>0){				
					$query = "SELECT email FROM ".WP_EMEMBER_MEMBERS_TABLE_NAME.			          
					         " WHERE date_add( `subscription_starts` , INTERVAL " . ($interval - $alert_before) . " DAY ) = CURDATE( )" .
					         " AND membership_level = " . $level->id;
					$result = $wpdb->get_results($query);
					if(!empty($result)){
						$subject = $emember_config->getValue('eMember_before_expiry_email_subject');
						$body    =  $emember_config->getValue('eMember_before_expiry_email_body');
						$headers = 'From: '.$emember_config->getValue('eMember_before_expiry_senders_email_address') . "\r\n";
						$email_list = array();					
						foreach($result as $row) $email_list[] = $row->email;
						wp_mail($email_list, $subject, $body, $headers);							
					}
				}			
			}
		}
	}
} 

function eMember_delete_enclosure($enclosure){   
    global $post;
    global $emember_auth;
    $emember_auth = Emember_Auth::getInstance();
    $emember_config = Emember_Config::getInstance();    
    $is_protected = false;
    if($post->post_type =='post'){
        if($emember_auth->is_protected_post($post->ID))
            $is_protected = true;      
        }
    
    if($post->post_type =='page'){
        if($emember_auth->is_protected_page($post_ID)){
            $is_protected = true;
        }
    }
    if($is_protected){
            if($emember_auth->isLoggedIn())
                    return $enclosure;                
            return '';
        }
    return $enclosure;
}

/* Prints the inner fields for the custom post/page section */
function eMember_inner_custom_box() {
    global $post;
    global $emember_auth;
    $emember_auth = Emember_Auth::getInstance();
    $emember_config = Emember_Config::getInstance();    
    $id = $post->ID;
	$protection = $emember_auth->protection;
	$all_levels = dbAccess::findAll(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, ' id != 1 ', ' id DESC ');
    // Use nonce for verification
    $is_protected = false;
    $is_in = array();
    if($post->post_type === 'page'){
    	$p_pages = unserialize( $protection->page_list );
        $p_pages = is_bool($p_pages)? array() : $p_pages;
    	$is_protected = in_array($id, $p_pages)? true:false;
    	foreach($all_levels as $level){
    		$l_pages = unserialize( $level->page_list );
    		$l_pages = is_bool($l_pages)? array() : $l_pages;
    		$is_in[$level->id] = in_array($id, $l_pages)? "checked='checked'":"";
    	}
    }
//    if($post->post_type ==='post'){
//    	$p_posts = unserialize( $protection->post_list ); 
//    	$p_posts = is_bool($p_posts)? array() : $p_posts;
//    	$is_protected = in_array($id, $p_posts)? true: false;
//    	
//    	foreach($all_levels as $level){
//    		$l_posts = unserialize( $level->post_list );
//    		$l_posts = is_bool($l_posts)? array() : $l_posts;
//    		$is_in[$level->id] = in_array($id, $l_posts)? "checked='checked'":"";
//    	}
//    }  
    else { 
    	$p_posts = unserialize( $protection->post_list ); 
    	$p_posts = is_bool($p_posts)? array() : $p_posts;
    	$is_protected = in_array($id, $p_posts)? true: false;
    	
    	foreach($all_levels as $level){
    		$l_posts = unserialize( $level->post_list );
    		$l_posts = is_bool($l_posts)? array() : $l_posts;
    		$is_in[$level->id] = in_array($id, $l_posts)? "checked='checked'":"";
    	}        
    }
    echo '<input type="hidden" name="eMember_noncename" id="eMember_noncename" value="' . 
    wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
    // The actual fields for data entry
	echo '<h4>'.  __("Do you want to protect this content?", 'eMember_textdomain' ) . '</h4>' ;
	echo '  <input type="radio" ' . ((!$is_protected)? 'checked': "") . '  name="eMember_protect_post" value="1" /> No, Do not protect this content. <br/>';
	echo '  <input type="radio" ' . (($is_protected)? 'checked': "") . '  name="eMember_protect_post" value="2" /> Yes, Protect this content.<br/>';  
    echo  '<h4>'.__("Select the membership level that can access this content:", 'eMember_textdomain' )  ."</h4>";  
    foreach ($all_levels as $level)
       echo '<input type="checkbox" ' . (isset($is_in[$level->id])? $is_in[$level->id]:""). ' name="eMember_protection_level['.$level->id .']" value="' . $level->id . '" /> ' .$level->alias  . "<br/>";        
}

/* Prints the edit form for pre-WordPress 2.5 post/page */
function eMember_old_custom_box() {
  echo '<div class="dbx-b-ox-wrapper">' . "\n";
  echo '<fieldset id="eMember_fieldsetid" class="dbx-box">' . "\n";
  echo '<div class="dbx-h-andle-wrapper"><h3 class="dbx-handle">' . 
        __( 'eMember Protection options', 'eMember_textdomain' ) . "</h3></div>";     
  echo '<div class="dbx-c-ontent-wrapper"><div class="dbx-content">';
  // output editing form
  eMember_inner_custom_box();
  // end wrapper
  echo "</div></div></fieldset></div>\n";
}

/* When the post is saved, saves our custom data */
function eMember_save_postdata( $post_id ) {
  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times

  if ( !wp_verify_nonce( $_POST['eMember_noncename'], plugin_basename(__FILE__) )) {
    return $post_id;
  }

  // verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
  // to do anything
  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
    return $post_id;

  
  // Check permissions
  if ( 'page' == $_POST['post_type'] ) {
    if ( !current_user_can( 'edit_page', $post_id ) )
      return $post_id;
  } else {
    if ( !current_user_can( 'edit_post', $post_id ) )
      return $post_id;
  }

  // OK, we're authenticated: we need to find and save the data
  $enable_protection = array();
  global $emember_auth;
  $emember_auth = Emember_Auth::getInstance();
  $emember_config = Emember_Config::getInstance();
  $enable_protection['protect'] = $_POST['eMember_protect_post'];
  $enable_protection['level']   = $_POST['eMember_protection_level'];
  $protection = $emember_auth->protection;  
	  if('page' == $_POST['post_type']){
	      $p_pages = unserialize( $protection->page_list );
	      $p_pages = is_bool($p_pages)? array() : $p_pages;
	      if($_POST['eMember_protect_post']==2)$p_pages[] = $post_id;	     
	      else foreach($p_pages as $k=>$v)if($v===$post_id) unset($p_pages[$k]);	
	  	  $p_pages = array_unique($p_pages);
          $p_pages = serialize($p_pages);
          $protection = (array)($protection);
          $protection['page_list']=$p_pages;	  	  	      
	  }
	  else /*if('post' == $_POST['post_type'])*/{
	  	  $p_posts = unserialize( $protection->post_list );
	  	  $p_posts = is_bool($p_posts)? array() : $p_posts;
	  	  if($_POST['eMember_protect_post']==2)$p_posts[] = $post_id;	     
	      else foreach($p_posts as $k=>$v)if($v===$post_id) unset($p_posts[$k]);	  	  
	  	  $p_posts = array_unique($p_posts);
          $p_posts = serialize($p_posts);
          $protection = (array)($protection);
          $protection['post_list']=$p_posts;	            	 
	  }
      dbAccess::update(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE,' id = 1' , $protection);
      
      $all_levels = dbAccess::findAll(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, ' id != 1 ', ' id DESC ');
      
	  foreach($all_levels as $level){
    	  if('page' == $_POST['post_type']){
    	      $l_pages = unserialize( $level->page_list );
    	      $l_pages = is_bool($l_pages)? array() : $l_pages;
    	      if(isset($_POST['eMember_protection_level'][$level->id]))
	          	 $l_pages[] = $post_id;
	          else 
	             foreach($l_pages as $k=>$v)if($v===$post_id) unset($l_pages[$k]);
	  	      $l_pages = array_unique($l_pages);
              $l_pages = serialize($l_pages);
              $level = (array)($level);
              $level['page_list']=$l_pages;	  	  	          	      
    	  }
    	  else /*if('post' == $_POST['post_type'])*/{
    	  	  $l_posts = unserialize( $level->post_list );
    	  	  $l_posts = is_bool($l_posts)? array() : $l_posts;
    	  	  if(isset($_POST['eMember_protection_level'][$level->id]))
	  	          $l_posts[] = $post_id;
	  	      else 
	  	          foreach($l_posts as $k=>$v)if($v===$post_id) unset($l_posts[$k]);
	  	      $l_posts = array_unique($l_posts);
              $l_posts = serialize($l_posts);
              $level = (array)($level);
              $level['post_list']=$l_posts;	  	      	  	      	  	  
    	  }
    	  dbAccess::update(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE,' id = '.$level['id'],  $level);	      
	  }

  // Do something with $mydata 
  // probably using add_post_meta(), update_post_meta(), or 
  // a custom table (see Further Reading section below)
   
   return $enable_protection;
}
////////////////////////////////////////////

function lockdown_widget(){
	global $emember_auth;
	global $emember_config;
    $emember_auth = Emember_Auth::getInstance();
    $emember_config = Emember_Config::getInstance();   
	$join_url = $emember_config->getValue('eMember_payments_page');
    //$first_click_enabled = $emember_config->getValue('eMember_google_first_click_free'); 
	if(emember_is_first_click()) return ;
	$url_components = parse_url($join_url);
	$join_path  = (isset($url_components['path'])?$url_components['path']:"");
	$join_query = (isset($url_components['query'])? '?' . $url_components['query']:"");
	$join_uri = $join_path . $join_query;
	$join_uri = empty($join_uri)? '#':$join_uri;
	if(strpos($_SERVER['REQUEST_URI'],$join_uri)!==false) return;	
	$reg_url = $emember_config->getValue('eMember_registration_page');
	$url_components = parse_url($reg_url);
	$reg_path  = (isset($url_components['path'])?$url_components['path']:"");
	$reg_query = (isset($url_components['query'])? '?' . $url_components['query']:"");
	$reg_uri = $reg_path . $reg_query;
    $reg_uri = empty($reg_uri)? '#':$reg_uri;
	if(strpos($_SERVER['REQUEST_URI'],$reg_uri)!==false) return;	
	if(!$emember_auth->isLoggedIn()){
        echo '</head><body>';
       wp_emember_hook_password_reminder();	           
	   include_once('emember_lockdown_popup.php');
	   exit;
	}
}

function escape_csv_value($value) {
	$value = str_replace('"', '""', $value); // First off escape all " and make them ""
	$value = trim($value, ",");
	if(preg_match('/,/', $value) or preg_match("/\n/", $value) or preg_match('/"/', $value)) { // Check if I have any commas or new lines
		return '"'.$value.'"'; // If I have new lines or commas escape them
	} else {
		return $value; // If no new lines or commas just return the value
	}
}

function export_members_to_csv(){
    global $wpdb;
    if(isset($_POST['wp_emember_export'])){
        $member_table = WP_EMEMBER_MEMBERS_TABLE_NAME;
        $ret_member_db = $wpdb->get_results("SELECT * FROM $member_table ORDER BY member_id DESC", OBJECT);
        $csv_output = "User name, First Name, Last Name, Street, City,State,ZIP,Country, Email, Phone,Membership Start,\n";
        
        foreach($ret_member_db as $result){
            $csv_output .= escape_csv_value(stripslashes($result->user_name )). ','.
                           escape_csv_value(stripslashes($result->first_name)). ', '. 
                           escape_csv_value(stripslashes($result->last_name)) .  ','.
                           escape_csv_value(stripslashes($result->address_street)). ', ' . 
                           escape_csv_value(stripslashes($result->address_city)). ', ' . 
                           escape_csv_value(stripslashes($result->address_state)).', ' . 
                           escape_csv_value(stripslashes($result->address_zipcode)) . ', ' .
                           escape_csv_value(stripslashes($result->country)) . ',' .
                           escape_csv_value(stripslashes($result->email)) . ','.
                           escape_csv_value(stripslashes($result->phone)) .','. 
                           escape_csv_value(stripslashes($result->subscription_starts)). ','."\n"; 
        }
        $filename = "member_list_".date("Y-m-d_H-i",time());
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");  
        header("Content-Description: File Transfer");  
        header("Content-Length: " . strlen($csv_output));   
        header("Content-type: text/x-csv");     
        header( "Content-disposition: attachment; filename=".$filename.".csv");
        print $csv_output;
        exit;   
    }
}
function wp_emember_hook_password_reminder(){
    include_once('emember_password_sender_box.php');
}

function sync_emember_profile($wp_user_id){
    $wp_user_data = get_userdata($wp_user_id);
    $profile = dbAccess::find(WP_EMEMBER_MEMBERS_TABLE_NAME, ' user_name=\'' . $wp_user_data->user_login . '\'');
    $profile = (array)$profile;
    $profile['user_name'] = $wp_user_data->user_login; 
    $profile['email']     = $wp_user_data->user_email;
    $profile['password']  = $wp_user_data->user_pass;
    $profile['first_name']= $wp_user_data->user_firstname;
    $profile['last_name'] = $wp_user_data->user_lastname;
    dbAccess::update(WP_EMEMBER_MEMBERS_TABLE_NAME,'member_id = ' . $profile['member_id'], $profile);   
}
function load_library(){
	  global $emember_config;
      $emember_config = Emember_Config::getInstance();    
      wp_enqueue_script('jquery');
      
      if(is_admin() && in_array($_GET['page'],$emember_config->pages))
      {
	      //Load only on WP eMember admin pages
      	  wp_enqueue_style('eMember.adminstyle', WP_EMEMBER_URL.'/css/eMember_admin_style.css');
          wp_enqueue_style('eMember.style',WP_EMEMBER_URL.'/css/eMember_style.css');
          wp_enqueue_style('jquery.tools.dateinput',WP_EMEMBER_URL.'/css/jquery.tools.dateinput.css');          
          wp_enqueue_style('validationEngine.jquery',WP_EMEMBER_URL.'/css/validationEngine.jquery.css');
          wp_enqueue_style('eMember.style.custom',WP_EMEMBER_URL.'/css/eMember_custom_style.css');          
          wp_enqueue_script('jquery.dynamicField',WP_EMEMBER_URL.'/js/jquery.dynamicField-1.0.js');                                
	      wp_enqueue_script('jquery.validationEngine',WP_EMEMBER_URL.'/js/jquery.validationEngine.js');
	      wp_enqueue_script('jquery.hint',WP_EMEMBER_URL.'/js/jquery.hint.js');     
	      wp_enqueue_script('ajaxupload',WP_EMEMBER_URL.'/js/ajaxupload.js');
	      wp_enqueue_script('jquery.tools',WP_EMEMBER_URL.'/js/jquery.tools.min.js');
	      wp_enqueue_script('jquery.libs',WP_EMEMBER_URL.'/js/jquery.libs.js');     
	                
          if(get_bloginfo('version')<'3.0'){
              wp_enqueue_script('jquery.pagination',WP_EMEMBER_URL.'/js/jquery.pagination-1.2.js');	     
              wp_enqueue_script('jquery.confirm',WP_EMEMBER_URL.'/js/jquery.confirm-1.2.js');      	
          }
          else {
              wp_enqueue_script('jquery.pagination',WP_EMEMBER_URL.'/js/jquery.pagination-2.0rc.js');
              wp_enqueue_script('jquery.confirm',WP_EMEMBER_URL.'/js/jquery.confirm-1.3.js');      	
          }          
      }            
      if(!is_admin()){
      	  //Load on all front pages of the site
          wp_enqueue_style('eMember.style',WP_EMEMBER_URL.'/css/eMember_style.css');        
          wp_enqueue_style('eMember.style.custom',WP_EMEMBER_URL.'/css/eMember_custom_style.css');
          wp_enqueue_style('validationEngine.jquery',WP_EMEMBER_URL.'/css/validationEngine.jquery.css');
	      wp_enqueue_script('jquery.validationEngine',WP_EMEMBER_URL.'/js/jquery.validationEngine.js');
	      wp_enqueue_script('jquery.hint',WP_EMEMBER_URL.'/js/jquery.hint.js');     
	      wp_enqueue_script('ajaxupload',WP_EMEMBER_URL.'/js/ajaxupload.js');
	      wp_enqueue_script('jquery.tools',WP_EMEMBER_URL.'/js/jquery.tools.min.js');
	      wp_enqueue_script('jquery.libs',WP_EMEMBER_URL.'/js/jquery.libs.js');
          if(get_bloginfo('version')<'3.0'){
              wp_enqueue_script('jquery.pagination',WP_EMEMBER_URL.'/js/jquery.pagination-1.2.js');	     
              wp_enqueue_script('jquery.confirm',WP_EMEMBER_URL.'/js/jquery.confirm-1.2.js');      	
          }
          else {
              wp_enqueue_script('jquery.pagination',WP_EMEMBER_URL.'/js/jquery.pagination-2.0rc.js');
              wp_enqueue_script('jquery.confirm',WP_EMEMBER_URL.'/js/jquery.confirm-1.3.js');      	
          }                    
      }                        
}

function wp_eMember_widget_init(){
    $widget_options = array('classname' => 'wp_eMember_widget', 'description' => __( "Display WP eMember Login.") );
    wp_register_sidebar_widget('wp_eMember_widget', __('WP eMember Login'), 'show_wp_eMember_login_widget', $widget_options);
}

//Add the Admin Menus
if (is_admin()){
	if (get_bloginfo('version') >= 3.0) {
	     define("EMEMBER_MANAGEMENT_PERMISSION", "add_users");
	}
	else{
		define("EMEMBER_MANAGEMENT_PERMISSION", "edit_themes");
	}	
   function wp_eMember_add_admin_menu(){
      add_menu_page(__("WP eMember", 'wp_eMember'), __("WP eMember", 'wp_eMember'), EMEMBER_MANAGEMENT_PERMISSION, __FILE__, "wp_eMember_dashboard");
      add_submenu_page(__FILE__, __("Dashboard WP eMember", 'wp_eMember'), __('Dashboard', 'wp_eMember'), EMEMBER_MANAGEMENT_PERMISSION, __FILE__, "wp_eMember_dashboard");
	  add_submenu_page(__FILE__, __("Settings WP eMember", 'wp_eMember'), __("Settings", 'wp_eMember'), EMEMBER_MANAGEMENT_PERMISSION, 'eMember_settings_menu', "wp_eMember_settings");      
      add_submenu_page(__FILE__, __("Members WP eMember", 'wp_eMember'), __("Members", 'wp_eMember'), EMEMBER_MANAGEMENT_PERMISSION, 'wp_eMember_manage', "wp_eMember_members");      
      add_submenu_page(__FILE__, __("Membership Level WP eMember", 'wp_eMember'), __("Membership Level", 'wp_eMember'), EMEMBER_MANAGEMENT_PERMISSION, 'eMember_membership_level_menu', "wp_eMember_membership_level");      
      add_submenu_page(__FILE__, __("Admin Functions", 'wp_eMember'), __("Admin Functions", 'wp_eMember'), EMEMBER_MANAGEMENT_PERMISSION, 'eMember_admin_functions_menu', "wp_eMember_admin_functions_menu");
   }

   //Include menus
   require_once(dirname(__FILE__).'/eMember_members_menu.php');
   require_once(dirname(__FILE__).'/eMember_dashboard_menu.php');
   require_once(dirname(__FILE__).'/eMember_membership_level_menu.php');
   require_once(dirname(__FILE__).'/eMember_settings_menu.php');
   require_once(dirname(__FILE__).'/eMember_admin_functions_menu.php');   
}
// Insert the options page to the admin menu

function emember_menu(){
    global $wpemem_evt;
    $wpemem_evt = trim($_REQUEST['event']);
    switch($wpemem_evt){
       case 'logout':       
          wp_emem_logout();
          break;
       case 'delete_account':
          delete_account();
           break;
       case 'check_name':
           do_action( 'wp_ajax_nopriv_check_name');
           die('0');break;
       case 'access_list_ajax':
          do_action( 'wp_ajax_access_list_ajax');
           die('0');break;
       case 'item_list_ajax':
           do_action( 'wp_ajax_item_list_ajax');
           die('0');break;
       case 'check_level_name':
          do_action( 'wp_ajax_check_level_name');
          die('0');break;
       case 'send_mail':
          do_action( 'wp_ajax_send_mail');
          die('0');break;
       case 'bookmark_ajax':
           do_action('wp_ajax_add_bookmark');
           die('0');break;
       case 'wp_user_list_ajax':
           do_action('wp_ajax_wp_user_list_ajax');
           die('0');break;           
       case 'emember_user_list_ajax':
           do_action('wp_ajax_emember_user_list_ajax');
           die('0');break;           
       case 'emember_user_count_ajax':
           do_action('wp_ajax_emember_user_count_ajax');
           die('0');break;           
       case 'emember_upload_ajax':
           do_action('wp_ajax_emember_upload_ajax');
           die('0');break;           
       case 'emember_public_user_list_ajax':
           do_action('wp_ajax_nopriv_emember_public_user_list_ajax');
           die('0');break;           
       case 'emember_public_user_profile_ajax':
           do_action('wp_ajax_nopriv_emember_public_user_profile_ajax');
           die('0');break;            
    }
}

function comment_text_action($content){
    return auth_check_comment($content);
}

function secure_content($content){
	global $post; 
    if(is_category()) {return auth_check_category($content); }
    if($post->post_type === 'page') {return auth_check_page($content);}    
    return auth_check_post($content);
}

function filter_eMember_public_user_list($content){
    include_once('public_user_directory.php');
    $pattern = '#\[wp_eMember_public_user_list:end]#';
    preg_match_all ($pattern, $content, $matches);
    foreach ($matches[0] as $match){
        $replacement = print_eMember_public_user_list();
        $content = str_replace ($match, $replacement, $content);
    }	    
    return $content;    	
}

function show_eMember_public_user_list($atts)
{
	extract(shortcode_atts(array(
		'no_email' => '',
	), $atts));		
	include_once('public_user_directory.php');
	return print_eMember_public_user_list($no_email);
}


function delete_account(){        
    global $emember_config;
    global $emember_auth;
    $emember_auth = Emember_Auth::getInstance();
    $emember_config = Emember_Config::getInstance();    
    if(!$emember_auth->isLoggedIn()) return;
    $f = $emember_config->getValue('eMember_allow_account_removal');
    if($f){
        $f = $emember_config->getValue('eMember_allow_wp_account_removal');
        if($f){			
            $wp_user_id = username_exists($emember_auth->getUserInfo('user_name'));
            $ud = get_userdata($wp_user_id);
            if(isset($ud->wp_capabilities['administrator'])||$ud->wp_user_level == 10){
              if($_GET['confirm']!=2){
                  $u = get_bloginfo('wpurl');
                  $_GET['confirm'] = 2;
                  $u .= '?' . http_build_query($_GET);
                  $warning = "<html><body><div id='message' style=\"color:red;\" ><p>You are about to delete an account that has admin privilege.
                  If you are using WordPress user integration then this will delete the corresponding user
                  account from WordPress and you may not be able to log in as admin with this account.
                  Continue? <a href='". $u. "'>yes</a>/<a href='javascript:void(0);' onclick='top.document.location=\"". get_bloginfo('wpurl') . "\";' >no</a></p></div></body></html>";
                  echo $warning;
                  exit;
              }	
            }
            wp_clear_auth_cookie();
            if($wp_user_id){
            	include_once(ABSPATH.'wp-admin/includes/user.php');
            	wp_delete_user( $wp_user_id, 1 ); //assigns all related to this user to admin.
            }
        }
        $ret = dbAccess::delete(WP_EMEMBER_MEMBERS_TABLE_NAME, 'member_id=' . $emember_auth->getUserInfo('member_id'));
        $ret = dbAccess::delete(WP_EMEMBER_MEMBERS_META_TABLE, 'user_id='.$emember_auth->getUserInfo('member_id'));	
        $emember_auth = Emember_Auth::getInstance();
        $emember_config = Emember_Config::getInstance();        
        global $emember_auth;
        $emember_auth->logout();
        header('location:' . get_bloginfo('wpurl'));exit;
    }	
}

function wp_eMember_renew_membership_for_free_handler($atts){
	extract(shortcode_atts(array(
		'level' => '',
	), $atts));	
	//TODO - If level parameter is not empty then also offer to upgrade to this level?
	
	global $auth;
	$user_id = $auth->getUserInfo('member_id');
	if (!empty($user_id)){		
		$output = "";
		$output .= '<div class="free_eMember_renewal_form">';
	    if(isset($_POST['eMember_free_renewal'])){
			$member_id = $_POST['eMember_free_renewal'];
			$curr_date = (date ("Y-m-d"));
			dbAccess::update(WP_EMEMBER_MEMBERS_TABLE_NAME,'member_id='.$member_id, array('subscription_starts'=>$curr_date,'account_state'=>'active'));		    	
			$output .= "Membership Renewed!";
	    }
	    else{
			$output .= '<form name="free_eMember_renewal" method="post" action="">';
			$output .= '<input type="hidden" name="eMember_free_renewal" value="'.$user_id.'" />';
			$output .= '<input type="submit" name="eMember_free_renew_submit" value="Renew" />';
			$output .= '</form>';			
	    }
	    $output .= '</div>';
		return $output;
	}
	else
		return "You must be logged in to renew a membership!";
}

function wp_eMember_upgrade_membership_level_to_handler($atts){
	extract(shortcode_atts(array(
		'level' => '',
	), $atts));	
	if(empty($level)){
		return '<div class="emember_error">Error! You must specify a membership level in the level parameter.</div>';
	}
	
	global $emember_auth,$emember_config,$wpdb;
    $emember_auth = Emember_Auth::getInstance();
    $emember_config = Emember_Config::getInstance();    
	$user_id = $emember_auth->getUserInfo('member_id');
	if (!empty($user_id)){		
		$output = "";
		$output .= '<div class="eMember_level_upgrade_form">';
	    if(isset($_POST['eMember_level_upgrade_submit'])){
			$member_id = $_POST['eMember_level_upgrade'];
			$curr_date = (date ("Y-m-d"));
			$resultset  = dbAccess::find(WP_EMEMBER_MEMBERS_TABLE_NAME, ' member_id=' . $wpdb->escape($member_id));
			if($emember_config->getValue('eMember_enable_secondary_membership')){
				$additional_levels = $resultset->more_membership_levels;
				$active_membership_level = $resultset->membership_level;				
				$additional_levels = $additional_levels.",".$active_membership_level;
				$level_info['membership_level'] = trim($level);
				$level_info['more_membership_levels'] = $additional_levels;				
			}
			else{
				$level_info['membership_level'] = trim($level);				
			}	
			dbAccess::update(WP_EMEMBER_MEMBERS_TABLE_NAME,'member_id='.$member_id, $level_info);							    
			$output .= "Membership Level Upgraded!";
	    }
	    else{
			$output .= '<form name="eMember_level_upgrade_form" method="post" action="">';
			$output .= '<input type="hidden" name="eMember_level_upgrade" value="'.$user_id.'" />';
			$output .= '<input type="submit" name="eMember_level_upgrade_submit" class="eMember_level_upgrade_submit" value="Upgrade" />';
			$output .= '</form>';			
	    }
	    $output .= '</div>';
		return $output;
	}
	else{
		return "You must be logged in to upgrade a membership!";
	}
}

function eMember_handle_affiliate_signup($user_name,$pwd,$afirstname,$alastname,$aemail,$referrer)
{    	
	global $wpdb,$emember_config;
    $emember_config = Emember_Config::getInstance();    
	if (function_exists('wp_aff_platform_install'))
	{		
		$members_table_name = $wpdb->prefix . "wp_eMember_members_tbl";
		$query_db = $wpdb->get_row("SELECT * FROM $members_table_name WHERE user_name = '$user_name'", OBJECT);
	    if($query_db)
	    {
	    	$eMember_id = $query_db->member_id;
	    	$membership_level = $query_db->membership_level;
	    	$allowed_levels = $emember_config->getValue('wp_eMember_affiliate_account_restriction_list');//example value "1,2,3";
	    	if(!empty($allowed_levels))//check if this level should be allowed to have an affiliate account
	    	{
		    	$pieces = explode(",", $allowed_levels);
		    	if(!in_array($membership_level,$pieces))//no permission for affilaite account creation
		    	{	    		
		    		return;
		    	}
	    	}
			$commission_level = get_option('wp_aff_commission_level');//This must use the get_option and not getValue
			$date = (date ("Y-m-d"));
			wp_aff_create_affilate($user_name,$pwd,'','',$afirstname,$alastname,'',$aemail,'','','','','','','','',$date,'',$commission_level,$referrer);
		    wp_aff_send_sign_up_email($user_name,$pwd,$aemail);
	    	
	    }
	    else
	    {
	    	echo "<br />Error! This username does not exist in the member database!";
	    }
	}
}

function eMember_handle_affiliate_profile_update($_POST){
	global $emember_config;
    $emember_config = Emember_Config::getInstance();    
	$eMember_auto_affiliate_account_login = $emember_config->getValue('eMember_auto_affiliate_account_login');
	if (function_exists('wp_aff_platform_install') && $eMember_auto_affiliate_account_login){
	    //update the affiliate account profile
		global $wpdb;
		$affiliates_table_name = WP_AFF_AFFILIATES_TBL_NAME;	  
		if(!empty($_POST['wp_emember_pwd'])){  
	    	$updatedb = "UPDATE $affiliates_table_name SET pass = '".$_POST['wp_emember_pwd']."', firstname = '".$_POST['wp_emember_firstname']."', lastname = '".$_POST['wp_emember_lastname']."', email = '".$_POST['wp_emember_email']."' WHERE refid = '".$_COOKIE['user_id']."'";
		}
		else{
			$updatedb = "UPDATE $affiliates_table_name SET firstname = '".$_POST['wp_emember_firstname']."', lastname = '".$_POST['wp_emember_lastname']."', email = '".$_POST['wp_emember_email']."' WHERE refid = '".$_COOKIE['user_id']."'";
		}
	    $results = $wpdb->query($updatedb);				
	}
}
function eMember_get_aff_referrer(){
        $referrer = "";
        if (!empty($_SESSION['ap_id']))
            $referrer = $_SESSION['ap_id'];
        else if (isset($_COOKIE['ap_id']))
            $referrer = $_COOKIE['ap_id'];

        return $referrer;
}
function eMember_is_post_protected ($post_id){
    global $wpdb;
    $wpdb->prefix . "wp_eMember_membership_tbl";
    $query = "SELECT post_list FROM " . $wpdb->prefix . "wp_eMember_membership_tbl WHERE id = 1;";
    $post_list = unserialize($wpdb->get_var($wpdb->prepare($query)));
    if(!$post_list) return false;
    return in_array($post_id, $post_list);
}
