<?php
function filter_eMember_registration_form($content){  
    $pattern = '#\[wp_eMember_registration_form:end]#';
    preg_match_all ($pattern, $content, $matches);

    foreach ($matches[0] as $match){
        $replacement = print_eMember_registration_form();
        $content = str_replace ($match, $replacement, $content);
    }
    return $content;
}

function print_eMember_registration_form(){
    return show_registration_form();
}

function free_rego_with_email_confirmation_handler(){
	global $emember_config,$wpdb;
    $emember_config = Emember_Config::getInstance();    
	$error_message = "";
	$enable_recaptcha = $emember_config->getValue('emember_enable_recaptcha');
	$publickey = $emember_config->getValue('emember_recaptcha_public');
	if(!function_exists('recaptcha_get_html')){
        //require_once(WP_EMEMBER_URL.'/recaptchalib.php');
        require_once(WP_PLUGIN_DIR.'/'.WP_EMEMBER_FOLDER.'/recaptchalib.php');      
	}    	
	if(isset($_POST['eMember_Register_with_confirmation']))
	{
		if(!empty($_POST['wp_emember_aemail']) && !empty($_POST['wp_emember_afirstname']) && !empty($_POST['wp_emember_alastname']))
		{
			if($enable_recaptcha){
		    	if(isset($_POST["recaptcha_response_field"])){
		        	$recaptcha_private_key = $emember_config->getValue('emember_recaptcha_private');            
		            $resp = recaptcha_check_answer ($recaptcha_private_key,
		                                                $_SERVER["REMOTE_ADDR"],
		                                                $_POST["recaptcha_challenge_field"],
		                                                $_POST["recaptcha_response_field"]);
		            if(!$resp->is_valid){
		            	$recaptcha_error = $resp->error;
		                $error_message = "<p class='emember_error'><strong>Image Verification failed!</strong></p>";                
		            }            
		        }
		        else
		        	$output .= '<span class="emember_error">reCAPTCHA&trade; service encountered error. please Contact Admin. </span>';                            
		    }			
			
		    if(!$enable_recaptcha || $resp->is_valid)
		    {
			    // create new member account and send the registration completion email			    
				if(emember_email_exists($_POST['wp_emember_aemail'])){
					$error_message = "<p class='emember_error'><strong>".EMEMBER_EMAIL_TAKEN."</strong></p>";
				}
				else{
					$fields = array();
					$fields['user_name'] = '';
					$fields['password'] = '';
					$fields['first_name'] = $_POST['wp_emember_afirstname'];
					$fields['last_name'] = $_POST['wp_emember_alastname'];
					$fields['email'] = $_POST['wp_emember_aemail'];
					
					$fields['member_since'] = (date ("Y-m-d"));
					$fields['subscription_starts'] = date("Y-m-d");
					
					$fields['membership_level'] = $emember_config->getValue('eMember_free_membership_level_id');
					//$fields['initial_membership_level'] = $emember_config->getValue('eMember_free_membership_level_id');
					$eMember_manually_approve_member_registration = $emember_config->getValue('eMember_manually_approve_member_registration');
		            if($eMember_manually_approve_member_registration){
		            	$fields['account_state'] = 'inactive';
		            }
		            else{
		            	$fields['account_state'] = 'active';
		            }					
					
					$reg_code = uniqid(); //rand(10, 1000);
					$md5_code = md5($reg_code);
					$fields['reg_code'] = $reg_code;
					
					$ret = dbAccess::insert(WP_EMEMBER_MEMBERS_TABLE_NAME, $fields);
					
					$resultset = dbAccess::find(WP_EMEMBER_MEMBERS_TABLE_NAME,' reg_code=\''.$reg_code.'\'');
					$id = $resultset->member_id;
						
				    $separator='?';
					$url = $emember_config->getValue('eMember_registration_page');
					if(strpos($url,'?')!==false){
						$separator='&';
					}
					$reg_url = $url.$separator.'member_id='.$id.'&code='.$md5_code;
				
					$subject = $emember_config->getValue('eMember_email_subject');
					$body = $emember_config->getValue('eMember_email_body');
					$from_address = $emember_config->getValue('senders_email_address');
				
				    $tags = array("{first_name}","{last_name}","{reg_link}");
				    $vals = array($_POST['wp_emember_afirstname'],$_POST['wp_emember_alastname'],$reg_url);
					$email_body = str_replace($tags,$vals,$body);
				    $headers = 'From: '.$from_address . "\r\n";
					
			        wp_mail($_POST['wp_emember_aemail'],$subject,$email_body,$headers);
			        $output = "<br /><strong>".EMEMBER_PLEASE_CHECK_YOUR_INBOX."</strong><br /><br />";
			        return $output;
				}
		    }
		}
		else{
			$error_message = "<p class='emember_error'><strong>".EMEMBER_YOU_MUST_FILL_IN_ALL_THE_FIELDS."</strong></p>";
		}
    }
    	
	ob_start();
	echo $error_message;
    ?>
    <form action="" method="post" name="wp_emember_regoFormWithConfirmation" id="wp_emember_regoFormWithConfirmation" >	
    <table width="95%" border="0" cellpadding="3" cellspacing="3" class="forms">
    <tr>
       <td><label for="wp_emember_afirstname" class="eMember_label"><?php echo EMEMBER_FIRST_NAME;?>: </label></td>
       <td>
         <input type="text" id="wp_emember_afirstname" name="wp_emember_afirstname" size="20" value="<?php echo $_POST['wp_emember_afirstname'];?>" class="eMember_text_input" />
       </td>
    </tr>
    <tr>
       <td><label for="wp_emember_alastname" class="eMember_label"><?php echo EMEMBER_LAST_NAME ?>: </label></td>
       <td><input type="text" id="wp_emember_alastname" name="wp_emember_alastname" size="20" value="<?php echo $_POST['wp_emember_alastname'];?>" class="eMember_text_input" /></td>
    </tr>
    <tr>
       <td><label for="wp_emember_aemail" class="eMember_label"><?php echo EMEMBER_EMAIL;?>: </label></td>
       <td><input type="text" id="wp_emember_aemail" name="wp_emember_aemail" size="20" value="<?php echo $_POST['wp_emember_aemail'];?>" class="eMember_text_input" /></td>
    </tr>
    <tr>
       <td colspan="2" align="center">
       <?php 
       if($enable_recaptcha)
       {
           if ($_SERVER["HTTPS"] == "on"){
               echo recaptcha_get_html($publickey , $recaptcha_error, true);
           }
           else{
               echo recaptcha_get_html($publickey , $recaptcha_error); 
           }
       } 
       ?>
       </td>
    </tr>    
    <tr>
    	<td></td>
    	<td><input class="eMember_button" name="eMember_Register_with_confirmation" type="submit" id="eMember_Register_with_confirmation" value="<?php echo EMEMBER_REGISTRATION;?>" /></td>
    </tr>                
    </table>
    </form>
    <?php
    $output = ob_get_contents();
    ob_end_clean();    
    return $output;    
}

function show_registration_form($level=0){
	global $emember_config;
	$emember_config = Emember_Config::getInstance();    
	$blacklisted_ips    = $emember_config->getValue('blacklisted_ips');
	$blacklisted_ips = empty($blacklisted_ips)? array(): explode(';',$blacklisted_ips);
	
	$current_ip = get_real_ip_addr();
	if(in_array($current_ip, $blacklisted_ips))
		return '<span class="emember_error">' .EMEMBER_IP_BLACKLISTED. ' </span>'; 
	$enable_fb = $emember_config->getValue('eMember_enable_fb_reg');
	//facebook feature
	/*if($enable_fb){
		return wp_emember_fb_reg_handler();
	}
	else{*/	 
		if(!function_exists('recaptcha_check_answer'))
		require_once(WP_PLUGIN_DIR.'/'.WP_EMEMBER_FOLDER.'/recaptchalib.php');         
		
		$output     = '';
		$eMember_id = $_GET["member_id"];
		$code       = $_GET["code"];
		$recaptcha_error = null;
		$resp = null;    
		global $wpdb;
		$is_reg_successfull = false;
		if(isset($_POST['eMember_Register']))    {
		$dc = $_POST['wp_emember_email'].$_POST['wp_emember_user_name'].$_POST['wp_emember_pwd'];
		if(isset($_SESSION['emember_dc'])){
			if($_SESSION['emember_dc']==$dc&&((time()-$_SESSION['time'])<10)){
			    if(isset($_SESSION['msg'])&&$_SESSION['msg'])
			        $output .= '<br/>'.EMEMBER_REG_COMPLETE;
			    else {
		                $output .= eMember_reg_form();
		                $output .= '<span class="emember_error">Please wait for 10 seconds before submiting the form again.</span>';    		    	
			    } 
			    return $output;
			}
			else{
			   $_SESSION['time'] = time();
			   $_SESSION['emember_dc'] = $dc;
			}
		}
		else {
			$_SESSION['time'] = time();
		    $_SESSION['emember_dc'] = $dc;
		}
		
		$blacklisted_emails = $emember_config->getValue('blacklisted_emails');	  
		$blacklisted_emails = empty($blacklisted_emails)? array(): explode(';',$blacklisted_emails);
		foreach($blacklisted_emails as $email){
			if((!empty($email))&&stristr($_POST['wp_emember_email'],$email))
				return '<span class="emember_error"> '. EMEMBER_EMAIL_BLACKLISTED.' </span>';
		}
		
		$enable_recaptcha = $emember_config->getValue('emember_enable_recaptcha');
		if($enable_recaptcha){
		    if(isset($_POST["recaptcha_response_field"])){
		        $recaptcha_private_key = $emember_config->getValue('emember_recaptcha_private');            
		        $resp = recaptcha_check_answer ($recaptcha_private_key,
		                                        $_SERVER["REMOTE_ADDR"],
		                                        $_POST["recaptcha_challenge_field"],
		                                        $_POST["recaptcha_response_field"]);
		        if(!$resp->is_valid){
		            $recaptcha_error = $resp->error;
		            $output .= eMember_reg_form($recaptcha_error);                
		        }            
		    }
		    else
		        $output .= '<span class="emember_error">reCAPTCHA&trade; service encountered error. please Contact Admin. </span>';                            
		}
		if(!$enable_recaptcha||($resp && $resp->is_valid)){
		    include_once(ABSPATH . WPINC . '/class-phpass.php'); 
		    require_once( ABSPATH . WPINC . '/registration.php' );
		    $wp_hasher = new PasswordHash(8, TRUE);
		    $password = $wp_hasher->HashPassword($_POST['wp_emember_pwd']);
            include_once ('emember_validator.php');
            $validator  = new Emember_Validator();            
            $validator->add(array('value'=>$_POST['wp_emember_user_name'],'label'=>EMEMBER_USERNAME,'rules'=>array('user_required','user_minlength','alphanumericunderscore','user_unavail')));
            $validator->add(array('value'=>$_POST['wp_emember_email'],'label'=>EMEMBER_EMAIL,'rules'=>array('email_required','email','email_unavail')));
            $validator->add(array('value'=>$_POST['wp_emember_pwd'],'label'=>EMEMBER_PASSWORD,'rules'=>array('pass_required')));
            $messages = $validator->validate();
            if(count($messages)>0){                
                $output .= '<span class="emember_error">' . implode('<br/>', $messages) . '</span>';
                $output .= eMember_reg_form();
            }            
		    else
		    {  
		    	$fields = array();
		    	$custom_fields = array();
		    	if (!empty($_SESSION['eMember_id']) && !empty($_SESSION['reg_code']))
		    	{
		    		$mresultset = $wpdb->get_row("SELECT reg_code,membership_level FROM " . WP_EMEMBER_MEMBERS_TABLE_NAME . " where member_id='$eMember_id'", ARRAY_A);
		    		//Update the membership data with the registration complete details (this path is exercised when the unique link is clicked from the email to do the registration complete action)		    		
						/*************************/		            
		            $fields['user_name'] = $_POST['wp_emember_user_name'];
		            $fields['password']  = $password;
		            $fields['membership_level'] = $mresultset['membership_level'];
		            $fields['reg_code']  = '';
		            if(isset($_POST['wp_emember_firstname']))$fields['first_name'] 		     = $_POST['wp_emember_firstname'];
		            if(isset($_POST['wp_emember_lastname']))$fields['last_name']   		     = $_POST['wp_emember_lastname'];
                    if(isset($_POST['wp_emember_phone']))$fields['phone']                    = $_POST['wp_emember_phone'];
                    if(isset($_POST['wp_emember_street']))$fields['address_street']          = $_POST['wp_emember_street'];
                    if(isset($_POST['wp_emember_city']))$fields['address_city']              = $_POST['wp_emember_city'];
                    if(isset($_POST['wp_emember_state']))$fields['address_state']            = $_POST['wp_emember_state'];
                    if(isset($_POST['wp_emember_zipcode']))$fields['address_zipcode']        = $_POST['wp_emember_zipcode'];
                    if(isset($_POST['wp_emember_country']))$fields['country']                = $_POST['wp_emember_country'];
                    if(isset($_POST['wp_emember_gender']))$fields['gender']                  = $_POST['wp_emember_gender'];  
                    if(isset($_POST['wp_emember_company_name']))$fields['company_name']      = $_POST['wp_emember_company_name'];          
		            
		            $fields['member_since']        = (date ("Y-m-d"));
		            $fields['subscription_starts'] = date("Y-m-d");
		            
		            //No need to update the membership level as it has already been set for this member when the unique rego complete link was sent out
		            
		    		$eMember_manually_approve_member_registration = $emember_config->getValue('eMember_manually_approve_member_registration');
		            if($eMember_manually_approve_member_registration){
		            	$fields['account_state'] = 'inactive';
		            }
		            else{
		            	$fields['account_state'] = 'active';
		            }
		            $fields['email']               = $_POST['wp_emember_email'];
		            $fields['last_accessed_from_ip']= get_real_ip_addr();
		            
		            if(md5($mresultset['reg_code'])==$_SESSION['reg_code'])
		            {
			            $ret = dbAccess::update(WP_EMEMBER_MEMBERS_TABLE_NAME, ' member_id='. $wpdb->escape($eMember_id), $fields);
			            /*************************/
			    		$lastid = $eMember_id;
			            if(isset($_POST['emember_custom']))
			            {
			            	foreach($_POST['emember_custom'] as $key=>$value){
			            		$custom_fields[$key] = $value;
			            	}				            	
			            	$wpdb->query("INSERT INTO " . WP_EMEMBER_MEMBERS_META_TABLE . 
			            	'( user_id, meta_key, meta_value ) VALUES(' . $lastid .',\'custom_field\',' . '\''.addslashes(serialize($_POST['emember_custom'])).'\')');
			            }
			            
			            if($ret === false){
			                $output .= '<br />' .'Failed.';
			                $is_reg_successfull = false;                    	
			            }
			            else{   
			            	$_SESSION['msg'] = true;                                     
			                $output .= '<br />' .EMEMBER_REG_COMPLETE;
			                $is_reg_successfull = true;
				            unset($_SESSION['eMember_id']);
				            unset($_SESSION['reg_code']);
				            unset($_SESSION['eMember_resultset']);		                    
			            }
		            }
		            else
		            {
		            	$output .= '<span class="emember_error">Error! Unique registration code do not match!</span>';
		            }
		    	}
		    	else
		    	{         		
		    		//Create a new account for a free member or the level specified in the shortcode. This path is exercised when someone directly goes to the registration page and submits the details.
		            $fields['user_name']           = $_POST['wp_emember_user_name'];
		            $fields['password']            = $password;
		            if(isset($_POST['wp_emember_firstname']))$fields['first_name'] 		     = $_POST['wp_emember_firstname'];
		            if(isset($_POST['wp_emember_lastname']))$fields['last_name']   		     = $_POST['wp_emember_lastname'];
                    if(isset($_POST['wp_emember_phone']))$fields['phone']                    = $_POST['wp_emember_phone'];
                    if(isset($_POST['wp_emember_street']))$fields['address_street']          = $_POST['wp_emember_street'];
                    if(isset($_POST['wp_emember_city']))$fields['address_city']              = $_POST['wp_emember_city'];
                    if(isset($_POST['wp_emember_state']))$fields['address_state']            = $_POST['wp_emember_state'];
                    if(isset($_POST['wp_emember_zipcode']))$fields['address_zipcode']        = $_POST['wp_emember_zipcode'];
                    if(isset($_POST['wp_emember_country']))$fields['country']                = $_POST['wp_emember_country'];
                    if(isset($_POST['wp_emember_gender']))$fields['gender']                  = $_POST['wp_emember_gender'];  
                    if(isset($_POST['wp_emember_company_name']))$fields['company_name']      = $_POST['wp_emember_company_name'];          
		            
		            $fields['member_since']        = (date ("Y-m-d"));
		            $fields['subscription_starts'] = date("Y-m-d");
		
		            if(isset($_POST['custom_member_level_shortcode'])){
		            	$fields['membership_level']    = $_POST['custom_member_level_shortcode'];
		            	//$fields['initial_membership_level']    = $_POST['custom_member_level_shortcode'];
		            }
		            else{
		            	$fields['membership_level']    = $emember_config->getValue('eMember_free_membership_level_id');
		            	//$fields['initial_membership_level']    = $emember_config->getValue('eMember_free_membership_level_id');
		            }
		            $eMember_manually_approve_member_registration = $emember_config->getValue('eMember_manually_approve_member_registration');
		            if($eMember_manually_approve_member_registration){
		            	$fields['account_state'] = 'inactive';
		            }
		            else{
		            	$fields['account_state'] = 'active';
		            }
		            $fields['email']               = $_POST['wp_emember_email'];
		            $fields['last_accessed_from_ip']= get_real_ip_addr();
		            
		            $ret = dbAccess::insert(WP_EMEMBER_MEMBERS_TABLE_NAME, $fields);
		            $lastid = $wpdb->insert_id;
		            if(isset($_POST['emember_custom']))
		            {
		            	foreach($_POST['emember_custom'] as $key=>$value){
		            		$custom_fields[$key] = $value;
		            	}		            	
		            	$wpdb->query("INSERT INTO " . WP_EMEMBER_MEMBERS_META_TABLE . 
		            	'( user_id, meta_key, meta_value ) VALUES(' . $lastid .',\'custom_field\',' . '\''.addslashes(serialize($_POST['emember_custom'])).'\')');
		            }
		            if($ret === false){
		                    $output .= '<br />' .'Failed.';
		                    $is_reg_successfull = false;                    	
		            }
		            else{   
		            	$_SESSION['msg'] = true;                                     
		                    $output .= '<br />' .EMEMBER_REG_COMPLETE;
		                    $is_reg_successfull = true;
		            }
		        }
		        if($is_reg_successfull){   
		        	//Send notification to any other plugin listening for the eMember registration complete event.
		        	do_action('eMember_registration_complete',$fields,$custom_fields); 
		        	
		        	//Query the membership level table to get a handle for the level
		        	$membership_level_resultset = dbAccess::find(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, " id='" .$fields['membership_level'] . "'" );              	
		        	
		        	// Create the corresponding wordpress user
		            $should_create_wp_user = $emember_config->getValue('eMember_create_wp_user');
		            if($should_create_wp_user) 
		            {
		               	$role_names = array(1=>'Administrator',2=>'Editor',3=>'Author',4=>'Contributor',5=>'Subscriber');                														                	                    
		                $wp_user_info  = array();
		                $wp_user_info['user_nicename'] = implode('-', explode(' ', $_POST['wp_emember_user_name']));
		                $wp_user_info['display_name']  = $_POST['wp_emember_user_name'];
		                $wp_user_info['nickname']      = $_POST['wp_emember_user_name'];
		                $wp_user_info['first_name']    = $_POST['wp_emember_firstname'];
		                $wp_user_info['last_name']     = $_POST['wp_emember_lastname'];
		        		$wp_user_info['role']            = $membership_level_resultset->role;
		    			$wp_user_info['user_registered'] = date('Y-m-d H:i:s');                	
		                
		                $wp_user_id = wp_create_user($_POST['wp_emember_user_name'], $_POST['wp_emember_pwd'], $_POST['wp_emember_email']);                        
		                $wp_user_info['ID'] = $wp_user_id;
		                wp_update_user( $wp_user_info );
		                update_wp_user_Role($wp_user_id, $membership_level_resultset->role);
		                do_action( 'set_user_role', $wp_user_id, $membership_level_resultset->role );                        
		            }
		            //-----------------            	
		            $subject_rego_complete = $emember_config->getValue('eMember_email_subject_rego_complete');			
		            $body_rego_complete    = $emember_config->getValue('eMember_email_body_rego_complete');			
		            $from_address          = $emember_config->getValue('senders_email_address');			
		            $login_link            = $emember_config->getValue('login_page_url');			
		            $tags1                 = array("{first_name}","{last_name}","{user_name}","{password}","{login_link}","{email}","{membership_level}","{phone}");			
		            $vals1                 = array($_POST['wp_emember_firstname'],$_POST['wp_emember_lastname'],$_POST['wp_emember_user_name'],$_POST['wp_emember_pwd'],$login_link,$_POST['wp_emember_email'],$membership_level_resultset->alias,$_POST['wp_emember_phone']);			
		            $email_body1           = str_replace($tags1,$vals1,$body_rego_complete);			
		            $headers               = 'From: '.$from_address . "\r\n";			
		            wp_mail($_POST['wp_emember_email'],$subject_rego_complete,$email_body1,$headers); 
		            if ($emember_config->getValue('eMember_admin_notification_after_registration')){
		            	$admin_email = $emember_config->getValue('eMember_admin_notification_email_address');//get_option('admin_email');
		            	$admin_notification_subject = EMEMBER_NEW_ACCOUNT_MAIL_HEAD;
		            	$admin_email_body = EMEMBER_NEW_ACCOUNT_MAIL_BODY.
		            						"\n\n-------Member Email----------\n".
		            						$email_body1.
		            						"\n\n------End------\n";
		            	wp_mail($admin_email,$admin_notification_subject,$admin_email_body,$headers);
		            }
		            //Create the corresponding affliate account
		            if($emember_config->getValue('eMember_auto_affiliate_account')){
		                eMember_handle_affiliate_signup($_POST['wp_emember_user_name'],$_POST['wp_emember_pwd'],$_POST['wp_emember_firstname'],$_POST['wp_emember_lastname'],$_POST['wp_emember_email'],eMember_get_aff_referrer());
		            }
		            
		            /*** Signup the member to Autoresponder List (Autoresponder integration) ***/
		            $membership_level_id = $fields['membership_level'];
		            $firstname = $_POST['wp_emember_firstname'];
		            $lastname = $_POST['wp_emember_lastname'];
		            $emailaddress = $_POST['wp_emember_email'];
		            eMember_level_specific_autoresponder_signup($membership_level_id,$firstname,$lastname,$emailaddress);
		            eMember_global_autoresponder_signup($firstname,$lastname,$emailaddress);  
					/*** end of autoresponder integration ***/                
		
		            /*** check redirection options and redirect accordingly ***/
					$after_rego_page = $emember_config->getValue('eMember_after_registration_page');
					$redirect_page = $emember_config->getValue('login_page_url');		            		            
	            	if(WP_EMEMBER_ENABLE_AUTO_LOGIN_AFTER_REGO)
	            	{	            		
	            		if (!empty($redirect_page)){
							$redirect_page = $redirect_page."?doLogin=1&emember_u_name=".$_POST['wp_emember_user_name']."&emember_pwd=".$_POST['wp_emember_pwd'];
							wp_emember_redirect_to_url($redirect_page);
	            		}                   		
	            	}
	            	else if(!empty($after_rego_page))
	            	{
	            		wp_emember_redirect_to_url($after_rego_page);
	            	}
	            	else
	            	{
	            		$output .= EMEMBER_PLEASE.' <a href="'.$redirect_page.'">'.EMEMBER_LOGIN.'</a>';
	            	} 
	            	/*** End of redirection stuff ***/                   	    	                                                                    
		            
		        }
		        else
		            $output .= "<b><br/>Something went wrong. Please Contact <a href='mailto:".get_bloginfo('admin_email')."'>Admin.</a></b>";                 
		    }
		}
		}
		else{
			if (!empty($eMember_id) && !empty($code)){
		    $resultset = dbAccess::find(WP_EMEMBER_MEMBERS_TABLE_NAME,' member_id='. $wpdb->escape($eMember_id));
			    $md5code = md5($resultset->reg_code);
			    if ($code == $md5code){
		        $free_member_level = $resultset->membership_level;
		        $level_resultset   = dbAccess::find(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE,' id='.$wpdb->escape($free_member_level));
		        $_POST['wp_emember_member_level']        = $level_resultset->alias;
			    	$_POST['wp_emember_firstname']           = $resultset->first_name;
			    	$_POST['wp_emember_lastname']            = $resultset->last_name;
			    	$_POST['wp_emember_email']               = $resultset->email;
					$_SESSION['eMember_id']        = $eMember_id;
					$_SESSION['reg_code']          = $code;
			    	$_SESSION['eMember_resultset'] = $resultset;
			    	$output .= "<br />" . EMEMBER_USER_PASS_MSG;
			    	$output .= eMember_reg_form($recaptcha_error);
			    }
			}
			else if($emember_config->getValue('eMember_enable_free_membership') || !empty($level))
			{
				if(isset($_POST['custom_member_level_shortcode']))
					$level = $_POST['custom_member_level_shortcode'];
				if(!empty($level)){
					$level_resultset   = dbAccess::find(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE,' id='.$wpdb->escape($level));
					if(!$level_resultset){
						$output .= '<p style="color:red;">You seem to have specified a membership level ID that does not exist. Please correct the membership level ID in the shortcode.</p>';
						return $output;
					}
				}
				else{
		            $free_member_level = $emember_config->getValue('eMember_free_membership_level_id');
		            if(empty($free_member_level)) return "<b>Free Membership Level ID has not been specified. Site Admin needs to correct the settings in the settings menu of eMember.</b>";
		            if(!is_numeric($free_member_level)) return "<b>Free Membership Level should be numeric. Site Admin needs to correct the settings in the settings menu of eMember.</b>";
		            $level_resultset   = dbAccess::find(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE,' id='.$wpdb->escape($free_member_level));
				}
		    $_POST['wp_emember_member_level'] = $level_resultset->alias;
				$output .= eMember_reg_form($recaptcha_error,$level);					
			}
			else
			{
				//Free membership is disabled
				$output .= EMEMBER_FREE_MEMBER_DISABLED;
				$payment_page = $emember_config->getValue('eMember_payments_page');
				if (!empty($payment_page)){
					$output .= '<br />'.EMEMBER_VISIT_PAYMENT_PAGE.'.'.EMEMBER_CLICK.' <a href="'.$payment_page.'">'.EMEMBER_HERE.'</a>';
				}
			}
		}
//facebook feature		
//	}
	return $output;
}
function eMember_reg_form($error = null,$level=0){	
	global $emember_config;
    $emember_config = Emember_Config::getInstance();    
    $publickey = $emember_config->getValue('emember_recaptcha_public');
    $emember_enable_recaptcha = $emember_config->getValue('emember_enable_recaptcha');
    if(!function_exists('recaptcha_get_html'))
        require_once(WP_PLUGIN_DIR.'/'.WP_EMEMBER_FOLDER.'/recaptchalib.php');         
    ob_start();
    $letter_number_underscore = $emember_config->getValue('eMember_auto_affiliate_account')? ',custom[onlyLetterNumberUnderscore]':'';
    ?>
    <script type="text/javascript">
/* <![CDATA[ */    
    jQuery(document).ready(function($){
        <?php echo include_once('emember_js_form_validation_rules.php');?>        
    	$.validationEngineLanguage.allRules['ajaxUserCall']['url']= '<?php echo admin_url('admin-ajax.php');?>';
    	$("#wp_emember_regoForm").validationEngine('attach');     	  
    });    	
/* ]]> */      
    </script>        
    <form action="" method="post" name="wp_emember_regoForm" id="wp_emember_regoForm" >    
    <?php if($level!=0){ ?>
    	<input type="hidden" name="custom_member_level_shortcode" value="<?php echo $level; ?>" />
    <?php } ?>
    <table width="95%" border="0" cellpadding="3" cellspacing="3" class="forms">
    <tr>
       <td><label for="wp_emember_user_name"  class="eMember_label"><?php echo EMEMBER_USERNAME;?>: </label></td>
       <td><input type="text" id="wp_emember_user_name" name="wp_emember_user_name" size="20" value="<?php echo $_POST['wp_emember_user_name'];?>" class="validate[required,custom[onlyLetterNumberUnderscore],minSize[4]<?php echo $letter_number_underscore?>,ajax[ajaxUserCall]] eMember_text_input" /></td>
    </tr>
    <tr>
       <td><label for="wp_emember_pwd" class="eMember_label"><?php echo EMEMBER_PASSWORD;?>: </label></td>
       <td><input type="password" id="wp_emember_pwd" name="wp_emember_pwd" size="20" value="<?php echo $_POST['wp_emember_pwd'];?>" class="validate[required,minSize[4]] eMember_text_input" /></td>
    </tr>    
    <tr>
       <td><label for="wp_emember_email" class="eMember_label"><?php echo EMEMBER_EMAIL;?>: </label></td>
       <td><input type="text" id="wp_emember_email" name="wp_emember_email" size="20" value="<?php echo $_POST['wp_emember_email'];?>" class="validate[required,custom[email]] eMember_text_input" /></td>
    </tr>
    <tr>
       <td><label for="wp_emember_member_level" class="eMember_label"> <?php echo EMEMBER_MEMBERSHIP_LEVEL;?>: </label></td>
       <td><input type="text" id="wp_emember_member_level" name="wp_emember_member_level" size="20" value="<?php echo $_POST['wp_emember_member_level'];?>" class="validate[required] eMember_text_input" readonly /></td>
    </tr>    
    <?php if($emember_config->getValue('eMember_reg_title')):?>
    <tr>
       <td width="30%"><label for="atitle" class="eMember_label"><?php echo EMEMBER_TITLE;?>: </label></td>
       <td>
       	   <select name="wp_emember_title">
               <option value="Mr">Mr</option>
               <option value="Mrs">Mrs</option>
               <option value="Miss">Miss</option>
               <option value="Ms">Ms</option>
               <option value="Dr">Dr</option>
           </select>
       </td>
     </tr>
     <?php endif;?>
     <?php if($emember_config->getValue('eMember_reg_firstname')):?>
    <tr>
       <td><label for="wp_emember_firstname" class="eMember_label"><?php echo EMEMBER_FIRST_NAME;?>: </label></td>
       <td>
         <input type="text" id="wp_emember_firstname" name="wp_emember_firstname" size="20" value="<?php echo $_POST['wp_emember_firstname'];?>" class="<?php echo $emember_config->getValue('eMember_reg_firstname_required')? 'validate[required] ': "";?>eMember_text_input" />
       </td>
    </tr>
    <?php endif;?>
    <?php if($emember_config->getValue('eMember_reg_lastname')):?>
    <tr>
       <td><label for="wp_emember_lastname" class="eMember_label"><?php echo EMEMBER_LAST_NAME ?>: </label></td>
       <td><input type="text" id="wp_emember_lastname" name="wp_emember_lastname" size="20" value="<?php echo $_POST['wp_emember_lastname'];?>" class="<?php echo $emember_config->getValue('eMember_reg_lastname_required')? 'validate[required] ': "";?>eMember_text_input" /></td>
    </tr>
    <?php endif;?>
    <?php if($emember_config->getValue('eMember_reg_phone')):?>
    <tr>
       <td><label for="wp_emember_email" class="eMember_label"><?php echo EMEMBER_PHONE?>: </label></td>
       <td><input type="text" id="wp_emember_phone" name="wp_emember_phone" size="20" value="<?php echo $_POST['wp_emember_phone']; ?>" class="<?php echo $emember_config->getValue('eMember_reg_phone_required')? 'validate[required,custom[phone]] ': "";?>eMember_text_input" /></td>
    </tr>    
    <?php endif;?>
    <?php if($emember_config->getValue('eMember_reg_company')):?>
    <tr>
       <td><label for="wp_emember_company_name" class="eMember_label"><?php echo EMEMBER_COMPANY ?>: </label></td>
       <td><input type="text" id="wp_emember_company_name" name="wp_emember_company_name" size="20" value="<?php echo $_POST['wp_emember_company_name']; ?>" class="<?php echo $emember_config->getValue('eMember_reg_company_required')? 'validate[required] ': "";?>eMember_text_input" /></td>
    </tr>
    <?php endif;?>
    <?php if($emember_config->getValue('eMember_reg_street')):?>    
    <tr>
       <td><label for="emember_street" class="eMember_label"><?php echo EMEMBER_ADDRESS_STREET?>: </label></td>
       <td><input type="text" id="wp_emember_street" name="wp_emember_street" size="20" value="<?php echo $_POST['wp_emember_street']; ?>" class="<?php echo $emember_config->getValue('eMember_reg_street_required')? 'validate[required] ': "";?>eMember_text_input" /></td>
    </tr>
    <?php endif;?>
    <?php if($emember_config->getValue('eMember_reg_city')):?>    
    <tr>    
       <td><label for="wp_emember_city" class="eMember_label"><?php echo EMEMBER_ADDRESS_CITY ?>: </label></td>
       <td><input type="text" id="wp_emember_city" name="wp_emember_city" size="20" value="<?php echo $_POST['wp_emember_city'];?>" class="<?php echo $emember_config->getValue('eMember_reg_city_required')? 'validate[required] ': "";?>eMember_text_input" /></td>
    </tr>
    <?php endif;?>
    <?php if($emember_config->getValue('eMember_reg_state')):?>    
    <tr>
       <td><label for="wp_emember_state" class="eMember_label"><?php echo EMEMBER_ADDRESS_STATE ?>: </label></td>
       <td><input type="text" id="wp_emember_state" name="wp_emember_state" size="20" value="<?php echo $_POST['wp_emember_state']; ?>" class="<?php echo $emember_config->getValue('eMember_reg_state_required')? 'validate[required] ': "";?>eMember_text_input" /></td>
    </tr>
    <?php endif;?>
    <?php if($emember_config->getValue('eMember_reg_zipcode')):?>    
    <tr>
       <td><label for="wp_emember_zipcode" class="eMember_label"><?php echo EMEMBER_ADDRESS_ZIP ?>: </label></td>
       <td><input type="text" id="wp_emember_zipcode" name="wp_emember_zipcode" size="20" value="<?php echo $_POST['wp_emember_zipcode']; ?>" class="<?php echo $emember_config->getValue('eMember_reg_zipcode_required')? 'validate[required] ': "";?>eMember_text_input" /></td>
    </tr>
    <?php endif;?>
    <?php if($emember_config->getValue('eMember_reg_country')):?>
    
    <tr>
       <td><label for="wp_emember_country" class="eMember_label"><?php echo EMEMBER_ADDRESS_COUNTRY ?>: </label></td>
       <td><input type="text" name="wp_emember_country" id="wp_emember_country" size="20" value="<?php echo $_POST['wp_emember_country']; ?>" class="<?php echo $emember_config->getValue('eMember_reg_country_required')? 'validate[required] ': "";?>eMember_text_input" /></td>
    </tr>
    <?php endif;?>
    <?php if($emember_config->getValue('eMember_reg_gender')):?>    
	<tr >
		<td > <label for="wp_emember_gender" class="eMember_label"><?php echo EMEMBER_GENDER ?>: </label></td>
		<td>
	   <select name="wp_emember_gender" id="wp_emember_gender">
	      <option  <?php echo (($_POST['wp_emember_gender'] ==='male') ? 'selected=\'selected\'' : '' ) ?> value="male"><?php echo EMEMBER_GENDER_MALE ?></option>
	      <option  <?php echo (($_POST['wp_emember_gender'] ==='female') ? 'selected=\'selected\'' : '' ) ?> value="female"><?php echo EMEMBER_GENDER_FEMALE ?></option>      
	      <option  <?php echo (($_POST['wp_emember_gender'] ==='not specified') ? 'selected=\'selected\'' : '' ) ?> value="not specified"><?php echo EMEMBER_GENDER_UNSPECIFIED ?></option>      
	   </select>
		</td>
	</tr>        
	<?php 
	endif;
	include ('custom_field_template.php');
	$use_ssl = false;
    if ($_SERVER["HTTPS"] == "on"){
        $use_ssl = true;
    }	
	?>   
    
    <tr><td colspan="2" align="center"> <?php echo (($emember_enable_recaptcha)? recaptcha_get_html($publickey , $error, $use_ssl): '' );?>  </td></tr>
    <tr>
    	<td></td>
    	<td><input class="eMember_button submit" name="eMember_Register" type="submit" id="eMember_Register" value="<?php echo EMEMBER_REGISTRATION;?>" /></td>
    </tr>
    </table>    
    </form><br />
    <?php 
    $output = ob_get_contents();
    ob_end_clean();    
    return $output;
}
