<?php
function emember_auto_responder_settings()
{
	global $emember_config;
    $emember_config = Emember_Config::getInstance();    
	echo '<div class="wrap"><h3>WP eMember - Autoresponder Settings</h3>';
	echo '<div id="poststuff"><div id="post-body">';
    if (isset($_POST['info_update']))
    {       
        $emember_config->setValue('eMember_enable_aweber_int',($_POST['eMember_enable_aweber_int']=='1') ? '1':'');
        $emember_config->setValue('eMember_aweber_list_name',(string)$_POST["eMember_aweber_list_name"]);
        
        $emember_config->setValue('eMember_use_mailchimp', ($_POST['eMember_use_mailchimp']=='1') ? '1':'' );
        //$emember_config->setValue('eMember_enable_global_chimp_int', ($_POST['eMember_enable_global_chimp_int']=='1') ? '1':'' );
        $emember_config->setValue('eMember_chimp_list_name', trim($_POST["eMember_chimp_list_name"]));
        
        $emember_config->setValue('eMember_chimp_api_key', trim($_POST["eMember_chimp_api_key"]));
        $emember_config->setValue('eMember_chimp_user_name', (string)$_POST["eMember_chimp_user_name"]);
        $emember_config->setValue('eMember_chimp_pass', (string)$_POST["eMember_chimp_pass"]);
        $emember_config->setValue('eMember_mailchimp_disable_double_optin', ($_POST['eMember_mailchimp_disable_double_optin']=='1') ? '1':'' );
        $emember_config->setValue('eMember_mailchimp_signup_date_field_name', trim($_POST["eMember_mailchimp_signup_date_field_name"]));

        $emember_config->setValue('eMember_use_getresponse', ($_POST['eMember_use_getresponse']=='1') ? '1':'' );
        $emember_config->setValue('eMember_getResponse_campaign_name', (string)$_POST["eMember_getResponse_campaign_name"]);
        $emember_config->setValue('eMember_getResponse_api_key', (string)$_POST["eMember_getResponse_api_key"]);
                
        $emember_config->saveConfig();
        echo '<div id="message" class="updated fade"><p>';                
        echo '<strong>Options Updated!';        
        echo '</strong></p></div>';        
    }
?>  

    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <input type="hidden" name="info_update" id="info_update" value="true" />

	<div class="postbox">
	<h3><label for="title">AWeber Settings (<a href="http://www.tipsandtricks-hq.com/wordpress-membership/?p=151" target="_blank">AWeber Integration Instructions</a>)</label></h3>
	<div class="inside">

    <table width="100%" border="0" cellspacing="0" cellpadding="6">

    <tr valign="top"><td width="25%" align="left">
    <strong>Enable AWeber Signup:</strong>
    </td><td align="left">
    <input name="eMember_enable_aweber_int" type="checkbox"<?php if($emember_config->getValue('eMember_enable_aweber_int')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><i>When checked the plugin will automatically sign up the members at registration time to your AWeber List specified below.</i><br /><br />
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    <strong>AWeber List Name:</strong>
    </td><td align="left">
    <input name="eMember_aweber_list_name" type="text" size="30" value="<?php echo $emember_config->getValue('eMember_aweber_list_name'); ?>"/>
    <br /><i>The name of the AWeber list where the members will be signed up to (eg. listname@aweber.com)</i><br /><br />
    </td></tr>

    </table>
    </div>
    </div>  
    
	<div class="postbox">
	<h3><label for="title">MailChimp Settings (<a href="http://www.tipsandtricks-hq.com/wordpress-membership/?p=236" target="_blank">MailChimp Integration Instructions</a>)</label></h3>
	<div class="inside">

    <table width="100%" border="0" cellspacing="0" cellpadding="6">

    <tr valign="top"><td width="25%" align="left">
    <strong>Use MailChimp AutoResponder:</strong>
    </td><td align="left">
    <input name="eMember_use_mailchimp" type="checkbox"<?php if($emember_config->getValue('eMember_use_mailchimp')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><i>Check this if you want to use MailChimp Autoresponder service.</i><br /><br />
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    <strong>MailChimp List Name:</strong>
    </td><td align="left">
    <input name="eMember_chimp_list_name" type="text" size="30" value="<?php echo $emember_config->getValue('eMember_chimp_list_name'); ?>"/>
    <br /><i>The name of the MailChimp list where the customers will be signed up to (e.g. Customer List)</i><br /><br />
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    <strong>MailChimp API Key:</strong>
    </td><td align="left">
    <input name="eMember_chimp_api_key" type="text" size="50" value="<?php echo $emember_config->getValue('eMember_chimp_api_key'); ?>"/>
    <br /><i>The API Key of your MailChimp account (can be found under the "Account" tab). By default the API Key is not active so make sure you activate it in your Mailchimp account. If you do not have the API Key then you can use the username and password option below but it is better to use the API key.</i>
    </td></tr>
    
    <tr valign="top"><td width="25%" align="left">
    Or <br /><br />
    </td></tr>
    
    <tr valign="top"><td width="25%" align="left">
    <strong>MailChimp Username:</strong>
    </td><td align="left">
    <input name="eMember_chimp_user_name" type="text" size="30" value="<?php echo $emember_config->getValue('eMember_chimp_user_name'); ?>"/>
    <br /><i>The username of your MailChimp account</i><br /><br />
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    <strong>MailChimp Password:</strong>
    </td><td align="left">
    <input name="eMember_chimp_pass" type="password" size="30" value="<?php echo $emember_config->getValue('eMember_chimp_pass'); ?>"/>
    <br /><i>The password of your MailChimp account</i><br /><br />
    </td></tr>
    
    <tr valign="top"><td width="25%" align="left">
    <strong>Disable Double Opt-In:</strong>
    </td><td align="left">
    <input name="eMember_mailchimp_disable_double_optin" type="checkbox"<?php if($emember_config->getValue('eMember_mailchimp_disable_double_optin')!='') echo ' checked="checked"'; ?> value="1"/>
    Do not send double opt-in confirmation email  
    <br /><i>Use this checkbox if you do not wish to use the double opt-in option. Please note that abusing this option may cause your MailChimp account to be suspended.</i><br /><br />
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    <strong>Signup Date Field Name (optional):</strong>
    </td><td align="left">
    <input name="eMember_mailchimp_signup_date_field_name" type="text" size="30" value="<?php echo $emember_config->getValue('eMember_mailchimp_signup_date_field_name'); ?>"/>
    <br /><i>If you have configured a signup date field for your mailchimp list then specify the name of the field here (example: SIGNUPDATE). <a href="http://kb.mailchimp.com/article/how-do-i-create-a-date-field-in-my-signup-form" target="_blank">More Info</a></i><br /><br />
    </td></tr>

    </table>
    </div></div>
          
	<div class="postbox">
	<h3><label for="title">GetResponse Settings (<a href="http://www.tipsandtricks-hq.com/wordpress-membership/?p=283" target="_blank">GetResponse Integration Instructions</a>)</label></h3>
	<div class="inside">

    <table width="100%" border="0" cellspacing="0" cellpadding="6">

    <tr valign="top"><td width="25%" align="left">
    <strong>Use GetResponse AutoResponder:</strong>
    </td><td align="left">
    <input name="eMember_use_getresponse" type="checkbox"<?php if($emember_config->getValue('eMember_use_getresponse')!='') echo ' checked="checked"'; ?> value="1"/>
    <br /><i>Check this if you want to use GetResponse Autoresponder service.</i><br /><br />
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    <strong>GetResponse Campaign Name:</strong>
    </td><td align="left">
    <input name="eMember_getResponse_campaign_name" type="text" size="30" value="<?php echo $emember_config->getValue('eMember_getResponse_campaign_name'); ?>"/>
    <br /><i>The name of the GetResponse campaign where the customers will be signed up to (e.g. marketing)</i><br /><br />
    </td></tr>

    <tr valign="top"><td width="25%" align="left">
    <strong>GetResponse API Key:</strong>
    </td><td align="left">
    <input name="eMember_getResponse_api_key" type="text" size="50" value="<?php echo $emember_config->getValue('eMember_getResponse_api_key'); ?>"/>
    <br /><i>The API Key of your GetResponse account (can be found inside your GetResponse Account).</i><br /><br />
    </td></tr>

    </table>
    </div></div>
              
    <div class="submit">
    <input type="submit" name="info_update" value="<?php _e('Update options'); ?> &raquo;" />
    </div>    
    </form>
    
<?php
    echo '</div></div>';
    echo '</div>';	
}
?>