<?php
function update_wp_user_Role($wp_user_id, $role){
    	update_user_meta($wp_user_id,'wp_capabilities', array($role=>true));
         $roles = new WP_Roles();
         $level = $roles->roles[$role]['capabilities'];
         if(isset($level['level_10']) &&$level['level_10']){
         	update_user_meta($wp_user_id,'wp_user_level', 10);
         	return;
         }
         if(isset($level['level_9']) &&$level['level_9']){
         	update_user_meta($wp_user_id,'wp_user_level', 9);
         	return;
         }
         if(isset($level['level_8']) &&$level['level_8']){
         	update_user_meta($wp_user_id,'wp_user_level', 8);
         	return;
         }
         if(isset($level['level_7']) &&$level['level_7']){
         	update_user_meta($wp_user_id,'wp_user_level', 7);
         	return;
         }
         if(isset($level['level_6']) &&$level['level_6']){
         	update_user_meta($wp_user_id,'wp_user_level', 6);
         	return;
         }
         if(isset($level['level_5']) &&$level['level_5']){
         	update_user_meta($wp_user_id,'wp_user_level', 5);
         	return;
         }
         if(isset($level['level_4']) &&$level['level_4']){         
         	update_user_meta($wp_user_id,'wp_user_level', 4);
         	return;
         }
         if(isset($level['level_3']) &&$level['level_3']){
         	update_user_meta($wp_user_id,'wp_user_level', 3);
         	return;
         }                                                
         if(isset($level['level_2']) &&$level['level_2']){
         	update_user_meta($wp_user_id,'wp_user_level', 2);
         	return;
         }
         if(isset($level['level_1']) &&$level['level_1']){
         	update_user_meta($wp_user_id,'wp_user_level', 1);
         	return;
         }
         if(isset($level['level_0']) &&$level['level_0']){
         	update_user_meta($wp_user_id,'wp_user_level', 0);
         	return;
         }
}

function emember_registered_email_exists($email){
    global $wpdb;
    $member_table = WP_EMEMBER_MEMBERS_TABLE_NAME;
    $resultset = dbAccess::find($member_table,' email=\''. $wpdb->escape($email) . '\' AND user_name != ""');
    if(empty($resultset)) return false;
    return $resultset->member_id;        
}
function emember_email_exists($email){
    global $wpdb;
    $member_table = WP_EMEMBER_MEMBERS_TABLE_NAME;
    $resultset = dbAccess::find($member_table,' email=\''. $wpdb->escape($email) . '\'');
    if(empty($resultset)) return false;
    return $resultset->member_id;        
}
function emember_username_exists($user_name){
    global $wpdb;
    $member_table = WP_EMEMBER_MEMBERS_TABLE_NAME;
    $resultset = dbAccess::find($member_table,' user_name=\''. $wpdb->escape($user_name) . '\'');
    if(empty($resultset)) return false;
    return $resultset->member_id;    
}

if (!function_exists('json_decode')) {
    function json_decode($content, $assoc=false) {
        require_once WP_PLUGIN_DIR. '/' . WP_EMEMBER_FOLDER.'/JSON.php';
        if ($assoc) $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        else $json = new Services_JSON;
        return $json->decode($content);
    }
}

if (!function_exists('json_encode')) {
    function json_encode($content) {
        require_once WP_PLUGIN_DIR. '/' . WP_EMEMBER_FOLDER.'/JSON.php';
        $json = new Services_JSON;
        return $json->encode($content);
    }
}

function eMember_send_aweber_mail($list_name,$from_address,$cust_name,$cust_email){
    $subject = "Aweber Automatic Sign up email";
    $body    = "\n\nThis is an automatic email that is sent to AWeber for member signup purpose\n".
               "\nEmail: ".$cust_email.
               "\nName: ".$cust_name;

	$headers = 'From: '.$from_address . "\r\n";
    wp_mail($list_name, $subject, $body, $headers);
}
function get_real_ip_addr(){
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   
        $ip=$_SERVER['HTTP_CLIENT_IP'];
    else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   
        $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    else
        $ip=$_SERVER['REMOTE_ADDR'];
        
    return $ip;
}
function eMember_get_string_between($string, $start, $end){
	$string = " ". $string;
	$ini = strpos($string,$start);
	if ($ini == 0) return "";
	$ini += strlen($start);
	$len = strpos($string, $end, $ini) - $ini;
	return substr($string, $ini, $len);
}

function print_password_reset_form(){
	ob_start();
	if(isset($_POST['wp_emember_email_password_doSend'])){
	    $status = wp_emember_generate_and_mail_password($_POST['wp_emember_reset_password_email']);
	    if($status['status_code'])
	         echo '<span style="color:green;">' . $status['msg'] . '</span>'; 	
	    else 
	         echo '<span style="color:red;">' . $status['msg'] . '</span>';
	}
	?>
    <script type="text/javascript"> 
	/* <![CDATA[ */   
    jQuery(document).ready(function($){
        <?php echo include_once('emember_js_form_validation_rules.php');?>
    	$("#wp_emember_mailSendForm").validationEngine('attach');     	  
    });    	
	/*]]>*/      
    </script>        
        <div id="wp_emember_email_mailForm">        
            <?php echo EMEMBER_PASS_RESET_MSG; ?>
            <form action="" name="wp_emember_mailSendForm" id="wp_emember_mailSendForm" method="post"  >
			    <table width="95%" border="0" cellpadding="3" cellspacing="3" class="forms">
				    <tr>
				       <td><label for="wp_emember_reset_password_email" class="eMember_label"><?php echo EMEMBER_EMAIL;?>: </label></td>
				       <td><input class="validate[required,custom[email]] eMember_text_input" type="text" id="wp_emember_reset_password_email" name="wp_emember_reset_password_email" size="20" value="" /></td>
				    </tr>
				    <tr>
				       <td></td>
				       <td><input name="wp_emember_email_password_doSend" type="submit" id="wp_emember_email_password_doSend" class="emember_button"  value="<?php echo EMEMBER_RESET;?>" /></td>
				    </tr>    
                </table>
           </form>
       </div>
	<?php 
    $output = ob_get_contents();
    ob_end_clean();	
    return $output;
}

function wp_emember_generate_and_mail_password($email){
       global $wpdb;
       global $emember_config;
       $emember_config = Emember_Config::getInstance();    
       $emailId= $wpdb->escape(trim($email));       
       $user = dbAccess::find(WP_EMEMBER_MEMBERS_TABLE_NAME, 'email=\'' . $emailId.'\'');
       if($user)
       {
           require_once('rand_pass.php');
           include_once(ABSPATH . WPINC . '/class-phpass.php');
           $wp_hasher = new PasswordHash(8, TRUE);        
           
           $reset_pass = utility::generate_password();
           //send mail from here with user name & password
           $wp_user_id = username_exists($user->user_name);
           if ($wp_user_id)
           {
               $wp_user_info              = array();
               $wp_user_info['user_pass'] = $reset_pass;
               $wp_user_info['ID']        = $wp_user_id;
               wp_update_user( $wp_user_info );               
           }      
           $fields   = array();     
           $password = $wp_hasher->HashPassword($reset_pass);
           $fields['password'] = $wpdb->escape($password);
           dbAccess::update(WP_EMEMBER_MEMBERS_TABLE_NAME,'member_id = ' . $user->member_id, $fields);
           $email_body = $emember_config->getValue('eMember_fogot_pass_email_body');
           $email_subject = $emember_config->getValue('eMember_fogot_pass_email_subject');
           //wp_mail($emailId,$email_subject,$email_body);
           $tags1                 = array("{first_name}","{last_name}","{user_name}","{password}");			
           $vals1                 = array($user->first_name,$user->last_name,$user->user_name,$reset_pass);			
           $email_body           = str_replace($tags1,$vals1,$email_body);
		   $from_address = $emember_config->getValue('eMember_fogot_pass_senders_email_address');
		   $headers = 'From: '.$from_address . "\r\n";
		   wp_mail($emailId,$email_subject,$email_body,$headers);           
           return array('status_code'=>true,'msg'=>EMEMBER_PASS_EMAILED_MSG);
       }
       else
           return array('status_code'=>false,'msg'=>EMEMBER_EMAIL_NOT_EXIST);	
}

function wp_emember_get_profile_image_url_by_id($id){  
	global $emember_config;
    $emember_config = Emember_Config::getInstance();    
    $image_url   = null;
    $image_path  = null;
	$upload_dir  = wp_upload_dir();
    $upload_url  = $upload_dir['baseurl'];
    $upload_path = $upload_dir['basedir'];
    $upload_url  .= '/emember/';
    $upload_path .= '/emember/';
    $upload_url  .= $id;
    $upload_path .= $id;
    if(file_exists($upload_path . '.jpg')){
	    $image_url = $upload_url . '.jpg?'. time();
	    $image_path = $upload_path . '.jpg';
    }
    else if(file_exists($upload_path . '.jpeg')){
	    $image_url = $upload_url . '.jpeg?'. time();
	    $image_path = $upload_path . '.jpeg';
    }
    else if(file_exists($upload_path . '.gif')){
	    $image_url = $upload_url . '.gif?'. time();
	    $image_path = $upload_path . '.gif';
    }
    else if(file_exists($upload_path . '.png')){
	    $image_url = $upload_url . '.png?'. time();
	    $image_path = $upload_path . '.png';
    }
    else{
	    $use_gravatar = $emember_config->getValue('eMember_use_gravatar');
	    if($use_gravatar)
	    	$image_url = WP_EMEMBER_GRAVATAR_URL . "/" . md5(strtolower($email)) . "?d=" . urlencode(404) . "&s=" . 96;
	    else
	    	$image_url = WP_EMEMBER_URL . '/images/default_image.gif';
    }
	return $image_url;
}
/**
 * adapted from Linux Journal(linuxjournal)
 * courtesy Douglas Lovell
 */
function check_email_address($email) {
    // First, we check that there's one @ symbol, 
    // and that the lengths are right.
    if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
        // Email invalid because wrong number of characters 
        // in one section or wrong number of @ symbols.
        return false;
    }
    // Split it into sections to make life easier
    $email_array = explode("@", $email);
    $local_array = explode(".", $email_array[0]);
    for ($i = 0; $i < sizeof($local_array); $i++) {
        $regexp = "^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&
        ?'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$";
        if(!ereg($regexp,$local_array[$i])) {
            return false;
        }
    }
    // Check if domain is IP. If not, 
    // it should be valid domain name
    if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
        $domain_array = explode(".", $email_array[1]);
        if (sizeof($domain_array) < 2) {
            return false; // Not enough parts to domain
        }
        for ($i = 0; $i < sizeof($domain_array); $i++) {
            $regexp = "^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$"; 
            if(!ereg($regexp,$domain_array[$i])) {
                return false;
            }
        }
    }
    return true;
}
function wp_emember_format_message($msg){
	global $emember_config;
    $emember_config = Emember_Config::getInstance();    
	if($emember_config->getValue('eMember_turn_off_protected_msg_formatting')=='1'){//do not apply formatting
		return $msg;
	}		
	$output .= '<span class="wp-emember-warning-msgbox">';
	$output .= '<span class="wp-emember-warning-msgbox-image"><img width="40" height="40" src="'.WP_EMEMBER_URL.'/images/warn_msg.png" alt=""/></span>';
	$output .= '<span class="wp-emember-warning-msgbox-text">'.$msg.'</span>';
	$output .= '</span>';
	$output .= '<span class="eMember-clear-float"></span>';
	return $output;
}

function wp_emember_redirect_to_url($url){
	if (!headers_sent()) {
		header('Location: ' . $url);
	}
	else{
		echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
	}
	exit;
}
function wp_emember_add_name_value_pair_to_url($url,$nvp_string){
	$separator='?';
	if(strpos($url,'?')!==false) {
		$separator='&';
	}	
	return $url.$separator.$nvp_string;
}
function wp_emember_printd($ar){
	echo '<pre>';
	print_r($ar);
	echo '</pre>';
}
function wp_eMember_registration_form_handler($atts){
    extract(shortcode_atts(array(
        'level' => '',
    ), $atts));	
    return show_registration_form($level);
}

function emember_total_members_handler($attrs, $contents, $codes=''){
    return emember_get_total_members();  
}
function emember_first_name_handler(){
    global $emember_auth;
    $emember_auth = Emember_Auth::getInstance();
    $emember_config = Emember_Config::getInstance();    
    $first_name = "";
    if($emember_auth->isLoggedIn()){
        $first_name = $emember_auth->getUserInfo('first_name');	
    }
	return $first_name;
}
function emember_last_name_handler(){
    global $emember_auth;
    $emember_auth = Emember_Auth::getInstance();
    $emember_config = Emember_Config::getInstance();    
    $last_name = "";
    if($emember_auth->isLoggedIn()){
        $last_name = $emember_auth->getUserInfo('last_name');	
    }
	return $last_name;
}

function emember_get_total_members(){
    global $wpdb;
    $emember_user_count = $wpdb->get_row("SELECT COUNT(*) as count FROM " . WP_EMEMBER_MEMBERS_TABLE_NAME . ' ORDER BY member_id');
    return $emember_user_count->count;		
}

function emember_preloader($colspan){
    $imgpath =  WP_EMEMBER_URL . '/images/loading.gif';
    $preloader = '<img src="'.$imgpath.'" />';
    $preloader = '<tr valign="top"><td align="center" colspan="'.$colspan.'">'.$preloader.'</td></tr>';
    return $preloader;
}
function emember_is_first_click(){
    $emember_config = Emember_Config::getInstance();
    $enabled = $emember_config->getValue('eMember_google_first_click_free'); 
    if(!$enabled) return false;    
    $agent = false;
    if(stripos($_SERVER['HTTP_USER_AGENT'],"Googlebot") != false){
        $agent = 'Googlebot';
        $ip = $_SERVER['REMOTE_ADDR'];
        $name = gethostbyaddr($ip);
        if(stripos($name,$agent) != false){
            //list of IP's
            $hosts = gethostbynamel($name);
            foreach($hosts as $host){
                if ($host == $ip)return true;            
            }
        }                
    }
    else{        
        $google = '{^[a-z]+://[^.]*\.google\.}i';
        $found_google = stripos($_SERVER['HTTP_REFERER'], '.google.');
        if(($found_google != false) && preg_match($google, $_SERVER['HTTP_REFERER'])){
            $agent = 'google';
            $name = $_SERVER['HTTP_REFERER'];
            return true;
        }
    }
    return false;    
}