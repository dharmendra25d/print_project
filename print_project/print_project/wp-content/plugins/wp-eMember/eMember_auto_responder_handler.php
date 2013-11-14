<?php
//global $emember_config;
//if($emember_config->getValue('eMember_use_mailchimp'))
{
	$eMember_mailchimp_lib_file = "lib/auto-responder/MCAPI.class.php";
	if(!class_exists('MCAPI'))
	{
		include_once($eMember_mailchimp_lib_file);
	}	
}
//if($emember_config->getValue('eMember_use_getresponse'))
{
	$eMember_get_response_lib_file = "lib/auto-responder/jsonRPCClient.php";
	if(!class_exists('jsonRPCClient'))
	{
		include_once($eMember_get_response_lib_file);
	}	
}

function eMember_get_chimp_api()
{
	global $emember_config;
    $emember_config = Emember_Config::getInstance();    
    $api_key = $emember_config->getValue('eMember_chimp_api_key');
    if(!empty($api_key))
    {
    	eMember_log_debug("Creating a new API object using the API Key specified in the settings: ".$api_key,true);
        $api = new MCAPI($api_key);
    }   
    else
    { 
    	$api = new MCAPI($emember_config->getValue('eMember_chimp_user_name'), $emember_config->getValue('eMember_chimp_pass'));
    }
    return $api;
}

function eMember_mailchimp_subscribe($api,$target_list_name,$fname,$lname,$email_to_subscribe)
{
    $lists = $api->lists();
    foreach ($lists AS $list) 
    {
        if ($list['name'] == $target_list_name)
        {
            $list_id = $list['id'];
            eMember_log_debug("Found a match for the list name on MailChimp. List ID :".$list_id,true);
        }
    }   
    global $emember_config;
    $emember_config = Emember_Config::getInstance();    
    $signup_date_field_name = $emember_config->getValue('eMember_mailchimp_signup_date_field_name');
    if(empty($signup_date_field_name)){
    	$merge_vars = array('FNAME'=>$fname, 'LNAME'=>$lname, 'INTERESTS'=>'');
    }
    else{
    	eMember_log_debug("Signup date field name: ".$signup_date_field_name,true); 
    	$todays_date = date ("Y-m-d");
    	$merge_vars = array('FNAME'=>$fname, 'LNAME'=>$lname, 'INTERESTS'=>'', $signup_date_field_name => $todays_date);
    }    
    //$merge_vars = array('FNAME'=>$fname, 'LNAME'=>$lname,'INTERESTS'=>'');

    if($emember_config->getValue('eMember_mailchimp_disable_double_optin')!='')
    {
    	eMember_log_debug("Subscribing to MailChimp without double opt-in... Name: ".$fname." ".$lname." Email: ".$email_to_subscribe,true); 
    	//listSubscribe doc at http://apidocs.mailchimp.com/1.2/listsubscribe.func.php
    	$retval = $api->listSubscribe($list_id, $email_to_subscribe, $merge_vars, "html", false, false, true, true);
    }   
    else{
	    eMember_log_debug("Subscribing to MailChimp... Name: ".$fname." ".$lname." Email: ".$email_to_subscribe,true); 
	    $retval = $api->listSubscribe( $list_id, $email_to_subscribe, $merge_vars );
    }
    
	if ($api->errorCode){
		eMember_log_debug ("Unable to load listSubscribe()!",false);
		eMember_log_debug ("\tError Code=".$api->errorCode,false);
		eMember_log_debug ("\tError Msg=".$api->errorMessage,false);
	} 
	else
	{
		eMember_log_debug("MailChimp Signup was successful.",true);
	}    
    return $retval;
}

function eMember_getResponse_subscribe($campaign_name,$fname,$lname,$email_to_subscribe)
{
	eMember_log_debug('Attempting to call GetResponse API for list signup...',true);	 
	// your API key
	// available at http://www.getresponse.com/my_api_key.html
	global $emember_config;
    $emember_config = Emember_Config::getInstance();    
	$api_key = $emember_config->getValue('eMember_getResponse_api_key');
	
	// API 2.x URL
	$api_url = 'http://api2.getresponse.com';
	
	$customer_name = $fname." ".$lname;
	
	eMember_log_debug('API Key:'.$api_key.', Customer name:'.$customer_name,true);	
	// initialize JSON-RPC client
	$client = new jsonRPCClient($api_url);
	
	$result = NULL;

	eMember_log_debug('Attempting to retrieve campaigns for '.$campaign_name,true);
    $result = $client->get_campaigns(
        $api_key,
        array (
            # find by name literally
            'name' => array ( 'EQUALS' => $campaign_name )
        )
    );

	# uncomment this line to preview data structure
	# print_r($result);
	
	# since there can be only one campaign of this name
	# first key is the CAMPAIGN_ID you need
	$CAMPAIGN_ID = array_pop(array_keys($result));	
	eMember_log_debug("Attempting GetResponse add contact operation for campaign ID: ".$CAMPAIGN_ID." Name: ".$customer_name." Email: ".$email_to_subscribe,false);
	
	if(empty($CAMPAIGN_ID))
	{
		eMember_log_debug("Could not retrieve campaign ID. Please double check your GetResponse Campaign Name:".$campaign_name,false);
	}
	else
	{
	# add contact to 'sample_marketing' campaign
	    $result = $client->add_contact(
	        $api_key,
	        array (
	            'campaign'  => $CAMPAIGN_ID,
	            'name'      => $customer_name,
	            'email'     => $email_to_subscribe,
	        	'cycle_day' => '0'
	        )
	    );
	}
	# uncomment this line to preview data structure
	# print_r($result);	
	eMember_log_debug("GetResponse contact added... result:".$result,true);
	return true;
}

function eMember_level_specific_autoresponder_signup($membership_level_id,$firstname,$lastname,$emailaddress)
{
	eMember_log_debug('Performing membership level specific autoresponder signup if specified.',true);
	$membership_level_resultset = dbAccess::find(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, " id='" . $membership_level_id . "'" );
	
	// Autoresponder Sign up
    if (!empty($membership_level_resultset->campaign_name))
    {
    	global $emember_config;
        $emember_config = Emember_Config::getInstance();    
    	eMember_log_debug('List name specified for this membership level is: '.$membership_level_resultset->campaign_name,true);
	    if($emember_config->getValue('eMember_enable_aweber_int') == 1)
	    {
		    $list_name = $membership_level_resultset->campaign_name;
		    $from_address = $emember_config->getValue('senders_email_address');
	    	$senders_email = eMember_get_string_between($from_address, "<", ">");
	        if(empty($senders_email))
	        {
	        	$senders_email = $from_address;
	        }	    
		    $cust_name = $firstname .' '. $lastname;
		    eMember_send_aweber_mail($list_name,$senders_email,$cust_name,$emailaddress);
	        eMember_log_debug('AWeber signup from email address used:'.$senders_email,true);	            
	        eMember_log_debug('AWeber list to signup to:'.$list_name,true);
	        eMember_log_debug('AWeber signup operation performed for:'.$emailaddress,true);	    
	    }	
	    if ($emember_config->getValue('eMember_use_mailchimp') == 1)
	    {
	        $api = eMember_get_chimp_api();
	        $target_list_name = $membership_level_resultset->campaign_name;
	        $retval = eMember_mailchimp_subscribe($api,$target_list_name,$firstname,$lastname,$emailaddress);
	        eMember_log_debug('Mailchimp email address to signup:'.$emailaddress,true);	            
	        eMember_log_debug('Mailchimp list to signup to:'.$target_list_name,true);
	        eMember_log_debug('Mailchimp signup operation performed. returned value:'.$retval,true);	          
	    }	
	    if ($emember_config->getValue('eMember_use_getresponse') == 1)
	    {
		    $campaign_name = $membership_level_resultset->campaign_name;
		    $retval = eMember_getResponse_subscribe($campaign_name,$firstname,$lastname,$emailaddress);    	
	        eMember_log_debug('GetResponse email address to signup:'.$emailaddress,true);	            
	        eMember_log_debug('GetResponse campaign to signup to:'.$campaign_name,true);
	        eMember_log_debug('GetResponse signup operation performed. returned value:'.$retval,true);	  	    
	    }   
    }	
    eMember_log_debug('End of membership level specific autoresponder signup.',true);
}

function eMember_global_autoresponder_signup($firstname,$lastname,$emailaddress)
{
	eMember_log_debug('Performing global autoresponder signup if specified.',true);
	global $emember_config;
    $emember_config = Emember_Config::getInstance();    
    if($emember_config->getValue('eMember_enable_aweber_int') == 1)
    {
	    $list_name = $emember_config->getValue('eMember_aweber_list_name');
	    $from_address = $emember_config->getValue('senders_email_address');
    	$senders_email = eMember_get_string_between($from_address, "<", ">");
        if(empty($senders_email))
        {
        	$senders_email = $from_address;
        }	    
	    $cust_name = $firstname .' '. $lastname;
	    eMember_send_aweber_mail($list_name,$senders_email,$cust_name,$emailaddress);
        eMember_log_debug('AWeber signup from email address:'.$senders_email,true);	            
        eMember_log_debug('AWeber list to signup to:'.$list_name,true);
        eMember_log_debug('AWeber signup operation performed for:'.$emailaddress,true);	    
    }	
    if ($emember_config->getValue('eMember_use_mailchimp') == 1)
    {
        $api = eMember_get_chimp_api();
        $target_list_name = $emember_config->getValue('eMember_chimp_list_name');
        $retval = eMember_mailchimp_subscribe($api,$target_list_name,$firstname,$lastname,$emailaddress);
        eMember_log_debug('Mailchimp email address to signup:'.$emailaddress,true);	            
        eMember_log_debug('Mailchimp list to signup to:'.$target_list_name,true);
        eMember_log_debug('Mailchimp signup operation performed. returned value:'.$retval,true);	          
    }	
    if ($emember_config->getValue('eMember_use_getresponse') == 1)
    {
	    $campaign_name = $emember_config->getValue('eMember_getResponse_campaign_name');
	    $retval = eMember_getResponse_subscribe($campaign_name,$firstname,$lastname,$emailaddress);    	
        eMember_log_debug('GetResponse email address to signup:'.$emailaddress,true);	            
        eMember_log_debug('GetResponse campaign to signup to:'.$campaign_name,true);
        eMember_log_debug('GetResponse signup operation performed. returned value:'.$retval,true);	  	    
    }    
    eMember_log_debug('End of global autoresponder signup.',true);
}
?>