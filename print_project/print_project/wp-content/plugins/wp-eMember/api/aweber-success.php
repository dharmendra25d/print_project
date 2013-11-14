<?php
include_once('../../../../wp-load.php');

//Massage the data
$email = $_REQUEST['email'];
$name = $_REQUEST['name'];
list($first_name,$last_name) = explode(' ',$name);
$listname = $_REQUEST['unit'];
$username = $email;

// 2011-05-17 by DKO
$referrer_mail = $_REQUEST['referrer'];

//POST the data to the eMember API
$postURL = WP_EMEMBER_URL."/api/create.php";
global $emember_config;
$emember_config = Emember_Config::getInstance();    
$secretKey = $emember_config->getValue('wp_eMember_secret_word_for_post');

    $data = array ();
    $data['secret_key'] = $secretKey;
    //$data['requested_domain'] = $domainURL;
    $data['email'] = $email;
    $data['first_name'] = $first_name;
    $data['last_name'] = $last_name;
    $data['username'] = $username;
    $data['membership_level_name'] = $listname;

    // send data to post URL
    $ch = curl_init ($postURL);
    curl_setopt ($ch, CURLOPT_POST, true);
    curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
    $returnValue = curl_exec ($ch);
    curl_close($ch);    
    
    //print_r($returnValue);

    list ($result, $msg, $additionalMsg) = explode ("\n", $returnValue);
    
    if ($result == 'Success!')
    {
    	$login_data = $additionalMsg;
    	
//		// 2011-05-17 by DKO - POST data to the eMember API to modify membership
//		$modify_postURL = WP_EMEMBER_URL."/api/modify.php";
//		
//    		$data['ref_email'] = $referrer_mail;
//    		 
//    		// send data to post URL
//    		$m_ch = curl_init ($modify_postURL);
//    		curl_setopt ($m_ch, CURLOPT_POST, true);
//    		curl_setopt ($m_ch, CURLOPT_POSTFIELDS, $data);
//    		curl_setopt ($m_ch, CURLOPT_RETURNTRANSFER, false);
//    		$m_returnValue = curl_exec ($m_ch);
//    		curl_close($m_ch);    
//    
//    		// print_r($m_returnValue);
//    		
//    		list ($result, $msg, $additionalMsg) = explode ("\n", $m_returnValue);
//   		// End DKO
   
		//Redirect to login page
		list($username,$password) = explode("|",$login_data); 
		$redirect_page = $emember_config->getValue('login_page_url');
		$redirect_page = $redirect_page."?doLogin=1&emember_u_name=".$username."&emember_pwd=".$password;								    
		header("Location: ".$redirect_page);
		exit;
       
        echo "<br />".$msg;
        echo "<br />".$additionalMsg;
        
    }
    else
    {
        //Something failed.. do not create.
        echo "<br />Error!";
        echo "<br />".$msg;
    } 

?>