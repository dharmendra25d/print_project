<?php
include_once('../../../../wp-load.php');

function eMember_handle_subsc_signup_stand_alone($ipn_data,$subsc_ref,$unique_ref,$eMember_id='')
{
    global $wpdb, $emember_config;
    $emember_config = Emember_Config::getInstance();    
    $members_table_name = $wpdb->prefix . "wp_eMember_members_tbl";
    	
    $email = $ipn_data['payer_email'];
    $query_db = $wpdb->get_row("SELECT * FROM $members_table_name WHERE email = '$email'", OBJECT);
    
    //TODO if nothing returned from the query then do another check against the "$unique_ref" to attempt to get another handle to this member profile
    if(!$query_db){//try to retrieve the member details based on the unique_ref
		eMember_debug_log_subsc("Could not find any record using the given email address (".$email."). Attempting to query database using the unique reference: ".$unique_ref,true);
    	if(!empty($unique_ref)){			
    		$query_db = $wpdb->get_row("SELECT * FROM $members_table_name WHERE subscr_id = '$unique_ref'", OBJECT);
    	}
    	else{
    		eMember_debug_log_subsc("Unique reference is missing in the notification so we have to assume that this is not a payment for an existing member.",true);
    	}
    }
    
	if ($query_db)//Upgrade the existing member account
	{		
		$eMember_id = $query_db->member_id;
		eMember_debug_log_subsc("Found a match in the member database. Modifying the existing membership profile... Member ID: ".$eMember_id,true);
		// upgrade the member account
		$account_state = 'active';
		$membership_level = $subsc_ref;
		$subscription_starts = (date ("Y-m-d"));
		$subscr_id = $unique_ref;
		$resultset = "";
		
		if($emember_config->getValue('eMember_enable_secondary_membership'))
		{
			eMember_debug_log_subsc("Using secondary membership level feature... adding additional levels to the existing profile",true);
			$resultset = $wpdb->get_row("SELECT * FROM $members_table_name where member_id='$eMember_id'", OBJECT);
			if($resultset)
			{
				$additional_levels = $resultset->more_membership_levels;
				if(is_null($additional_levels))
				{					
					$additional_levels = $resultset->membership_level;
					eMember_debug_log_subsc("Current additional levels for this profile is null. Adding level: ".$additional_levels,true);
				}
				else if(empty($additional_levels))
				{					
					$additional_levels = $resultset->membership_level;
					eMember_debug_log_subsc("Current additional levels for this profile is empty. Adding level: ".$additional_levels,true);					
				}
				else
				{
					$additional_levels = $additional_levels.",".$resultset->membership_level;
					$sec_levels = explode(',', $additional_levels);
					$additional_levels = implode(',', array_unique($sec_levels));//make sure there is not duplicate entry					
					eMember_debug_log_subsc("New additional level set: ".$additional_levels,true);
				}
				eMember_debug_log_subsc("Updating additional levels column for username: ".$resultset->user_name." with value: ".$additional_levels,true);							
				$updatedb = "UPDATE $members_table_name SET more_membership_levels='$additional_levels' WHERE member_id='$eMember_id'";    	    	
				$results = $wpdb->query($updatedb);		

				eMember_debug_log_subsc("Upgrading the primary membership level to the recently paid level. New primary membership level ID for this member is: ".$membership_level,true);
				$updatedb = "UPDATE $members_table_name SET account_state='$account_state',membership_level='$membership_level',subscription_starts='$subscription_starts',subscr_id='$subscr_id' WHERE member_id='$eMember_id'";    	    	
				$results = $wpdb->query($updatedb);								
			}
			else
			{
				eMember_debug_log_subsc("Could not find a member account for the given eMember ID",false);
			}
		}
		else
		{
			eMember_debug_log_subsc("Not using secondary membership level feature... upgrading the current membership level.",true);
			$updatedb = "UPDATE $members_table_name SET account_state='$account_state',membership_level='$membership_level',subscription_starts='$subscription_starts',subscr_id='$subscr_id' WHERE member_id='$eMember_id'";    	
	    	$results = $wpdb->query($updatedb);	    	     			
		}
		
    	//If using the WP user integration then update the role on WordPress too
    	$membership_level_table = $wpdb->prefix . "wp_eMember_membership_tbl";
    	if($emember_config->getValue('eMember_create_wp_user'))
    	{
			eMember_debug_log_subsc("Updating WordPress user role...",true);
			$resultset = $wpdb->get_row("SELECT * FROM $members_table_name where member_id='$eMember_id'", OBJECT);
    		$membership_level = $resultset->membership_level;
    		$username = $resultset->user_name;    		
	        $membership_level_resultset = $wpdb->get_row("SELECT * FROM $membership_level_table where id='$membership_level'", OBJECT);
	                                                                         
			$wp_user_id = $username;
	        $wp_user_info  = array();
	        $wp_user_info['user_nicename'] = implode('-', explode(' ', $username));
	        $wp_user_info['display_name']  = $username;
	        $wp_user_info['nickname']      = $username;
	        $wp_user_info['user_email']    = $resultset->email;
	        //$wp_user_info['user_pass']     = "We dont know the password";//we don't want to update the password anyway.                       
	        $wp_user_info['ID']            = $wp_user_id;
	        $wp_user_info['role']            = $membership_level_resultset->role;
	        $wp_user_info['user_registered'] = date('Y-m-d H:i:s');                                        
	        wp_update_user($wp_user_info);	        
	        $wp_user = get_userdatabylogin( $username ); 
	        $wp_user_id = $wp_user->ID;
	        eMember_debug_log_subsc("Current users username :".$username." ,Membership level is: ".$membership_level." WP User ID is: ".$wp_user_id,true);
            $user_info = get_userdata($wp_user_id);
            $user_cap = is_array($user_info->wp_capabilities)?array_keys($user_info->wp_capabilities):array();

            if(($resultset->account_state === 'active') && !in_array('administrator',$user_cap))            
    	        update_wp_user_Role($wp_user_id, $membership_level_resultset->role);  
	        do_action( 'set_user_role', $wp_user_id, $membership_level_resultset->role );
	        eMember_debug_log_subsc("Current WP users role updated to: ".$membership_level_resultset->role,true);
    	} 
    	
    	//Set Email details	for the account upgrade notification	
    	$email = $ipn_data['payer_email'];    	
    	$subject = $emember_config->getValue('eMember_account_upgrade_email_subject');
	    if (empty($subject))
	    {
	    	$subject = "Member Account Upgraded";
	    }    	
    	$body = $emember_config->getValue('eMember_account_upgrade_email_body');
    	if (empty($body))
    	{
    		$body = "Your account has been upgraded successfully";
    	}
		$from_address = get_option('senders_email_address');
		//$email_body = $body;
		$login_link = $emember_config->getValue('login_page_url');
		$tags1 = array("{first_name}","{last_name}","{user_name}","{login_link}");			
		$vals1 = array($resultset->first_name,$resultset->last_name,$resultset->user_name,$login_link);			
		$email_body = str_replace($tags1,$vals1,$body);				
	    $headers = 'From: '.$from_address . "\r\n";   	    					    	
	}// End of existing account upgrade
	else
	{
		// create new member account
		$user_name ='';
		$password = '';
	
		$first_name = $ipn_data['first_name'];
		$last_name = $ipn_data['last_name'];
		$email = $ipn_data['payer_email'];
		$membership_level = $subsc_ref;
		$subscr_id = $unique_ref;
		
		eMember_debug_log_subsc("Membership level ID: ".$membership_level,true);
		
	    $address_street = $ipn_data['address_street'];
	    $address_city = $ipn_data['address_city'];
	    $address_state = $ipn_data['address_state'];
	    $address_zipcode = $ipn_data['address_zip'];
	    $country = $ipn_data['address_country'];
	
		$date = (date ("Y-m-d"));
		$account_state = 'active';
		$reg_code = uniqid();//rand(10, 1000);
		$md5_code = md5($reg_code);
	
	    $updatedb = "INSERT INTO $members_table_name (user_name,first_name,last_name,password,member_since,membership_level,account_state,last_accessed,last_accessed_from_ip,email,address_street,address_city,address_state,address_zipcode,country,gender,referrer,extra_info,reg_code,subscription_starts,txn_id,subscr_id) VALUES ('$user_name','$first_name','$last_name','$password', '$date','$membership_level','$account_state','$date','IP','$email','$address_street','$address_city','$address_state','$address_zipcode','$country','','','','$reg_code','$date','','$subscr_id')";
	    $results = $wpdb->query($updatedb);
	
		$results = $wpdb->get_row("SELECT * FROM $members_table_name where subscr_id='$subscr_id' and reg_code='$reg_code'", OBJECT);
	
		$id = $results->member_id;
		
	    $separator='?';
		$url=get_option('eMember_registration_page');
		if(strpos($url,'?')!==false)
		{
			$separator='&';
		}
		$reg_url = $url.$separator.'member_id='.$id.'&code='.$md5_code;
		eMember_debug_log_subsc("Member signup URL :".$reg_url,true);
	
		$subject = get_option('eMember_email_subject');
		$body = get_option('eMember_email_body');
		$from_address = get_option('senders_email_address');
	
	    $tags = array("{first_name}","{last_name}","{reg_link}");
	    $vals = array($first_name,$last_name,$reg_url);
		$email_body    = str_replace($tags,$vals,$body);
	    $headers = 'From: '.$from_address . "\r\n";
	}

    wp_mail($email,$subject,$email_body,$headers);
    eMember_debug_log_subsc("Member signup/upgrade completion email successfully sent",true);
}

function eMember_handle_subsc_cancel_stand_alone($ipn_data)
{
    $subscr_id = $ipn_data['subscr_id'];    

    global $wpdb;
    $members_table_name = $wpdb->prefix . "wp_eMember_members_tbl";
    $membership_level_table   = $wpdb->prefix . "wp_eMember_membership_tbl";
    
    eMember_debug_log_subsc("Retrieving member account from the database...",true);
    $resultset = $wpdb->get_row("SELECT * FROM $members_table_name where subscr_id='$subscr_id'", OBJECT);
    if($resultset)
    {
    	$membership_level = $resultset->membership_level;
    	$level_query = $wpdb->get_row("SELECT * FROM $membership_level_table where id='$membership_level'", OBJECT);
    	if ($level_query->subscription_period == 0)
    	{
    		//subscription duration is set to no expiry or until canceled so deactivate the account now
    		$account_state = 'inactive';
		    $updatedb = "UPDATE $members_table_name SET account_state='$account_state' WHERE subscr_id='$subscr_id'";
		    $results = $wpdb->query($updatedb);    		
		    eMember_debug_log_subsc("Subscription cancellation received! Member account deactivated.",true);
    	}
    	else
    	{
    		//Set the account to unsubscribed and it will be set to inactive when the "Subscription duration" is over	
    		$account_state = 'unsubscribed';    
		    $updatedb = "UPDATE $members_table_name SET account_state='$account_state' WHERE subscr_id='$subscr_id'";
		    $results = $wpdb->query($updatedb);    		
		    eMember_debug_log_subsc("Subscription cancellation received! Member account set to unsubscribed.",true);
    	}
    }
    else
    {
    	eMember_debug_log_subsc("No member found for the given subscriber ID:".$subscr_id,false);
    	return;
    }      	
}

function eMember_debug_log_subsc($message,$success,$end=false)
{
    // Timestamp
    $text = '['.date('m/d/Y g:i A').'] - '.(($success)?'SUCCESS :':'FAILURE :').$message. "\n";
    if ($end) {
    	$text .= "\n------------------------------------------------------------------\n\n";
    }
    // Write to log
    $fp=fopen("subscription_handle_debug.log",'a');
    fwrite($fp, $text );
    fclose($fp);  // close file
}
?>