<?php

function wp_eMember_general_settings()
{
	global $emember_config;
    $emember_config = Emember_Config::getInstance();    
    if (isset($_POST['info_update']))
    {
        $msg = '';
        if($_POST['eMember_enable_free_membership'])
        {
            if(empty($_POST["eMember_free_membership_level_id"]))
                $msg .= 'Please set free membership ID.<br />';
            else if($_POST["eMember_free_membership_level_id"]==1)
                $msg .= 'Membership level with the given ID is reserved ! <br / > Please use different one.<br />';                    
            else if(is_numeric($_POST["eMember_free_membership_level_id"]))
            {
                $l = dbAccess::find(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, " id='".$_POST["eMember_free_membership_level_id"]."' ");
                if($l){
            		$emember_config->setValue('eMember_enable_free_membership', 'checked="checked"');
            		$emember_config->setValue('eMember_free_membership_level_id', (string)$_POST["eMember_free_membership_level_id"]);                   
                }
                else
                    $msg .= 'Membership level with the given ID is not defined! <br / > Please correct it.<br />';         
            }
            else
                $msg .= 'Membership level with the given ID is not a number! <br / > Please correct it.<br />';
        }
        else
        {
            $emember_config->setValue('eMember_enable_free_membership', '');
            $emember_config->setValue('eMember_free_membership_level_id','');                               
        }
        
        if(!empty($_POST['eMember_login_limit']))
        {
        	if(is_numeric($_POST['eMember_login_limit']))
        		$emember_config->setValue('eMember_login_limit',(string)$_POST["eMember_login_limit"]);
            else
            { 
            	$msg .= 'Login Limit must be a number! <br / > Please correct it.<br />';
                $emember_config->setValue('eMember_login_limit','');
            }
        }
        else{
        	$emember_config->setValue('eMember_login_limit','');
        }
        $emember_config->setValue('wp_eMember_widget_title',(string)$_POST["wp_eMember_widget_title"]);
        $emember_config->setValue('eMember_language',(string)$_POST["eMember_language"]);
		$emember_config->setValue('non_members_error_page',(string)$_POST["non_members_error_page"]);
		$emember_config->setValue('wrong_membership_level', (string)$_POST["wrong_membership_level"]);
        $emember_config->setValue('eMember_rows_per_page', (string)$_POST["eMember_rows_per_page"]);
        $emember_config->setValue('eMember_enable_more_tag', isset($_POST["eMember_enable_more_tag"])?1:'');
        $emember_config->setValue('eMember_enable_redirection', isset($_POST["eMember_enable_redirection"])?1:'');
        $emember_config->setValue('eMember_create_wp_user', isset($_POST["eMember_create_wp_user"])?1:'');
        $emember_config->setValue('eMember_signin_wp_user', isset($_POST["eMember_signin_wp_user"])?1:'');
        $emember_config->setValue('eMember_enable_bookmark', isset($_POST["eMember_enable_bookmark"])?1:'');
        $emember_config->setValue('eMember_allow_expired_account', isset($_POST["eMember_allow_expired_account"])?1:'');        
        $emember_config->setValue('eMember_account_upgrade_url', isset($_POST["eMember_account_upgrade_url"])?$_POST["eMember_account_upgrade_url"]:'');
        $emember_config->setValue('eMember_signin_emem_user', isset($_POST["eMember_signin_emem_user"])?1:'');
        $emember_config->setValue('eMember_auto_affiliate_account', isset($_POST["eMember_auto_affiliate_account"])?1:'');
        $emember_config->setValue('eMember_auto_affiliate_account_login', isset($_POST["eMember_auto_affiliate_account_login"])?1:'');
        $emember_config->setValue('wp_eMember_affiliate_account_restriction_list',trim($_POST["wp_eMember_affiliate_account_restriction_list"]));        
        $emember_config->setValue('eMember_enable_debug', isset($_POST["eMember_enable_debug"])?1:'');
        $emember_config->setValue('eMember_enable_sandbox', isset($_POST["eMember_enable_sandbox"])?1:'');        
        $emember_config->setValue('eMember_enable_public_profile', isset($_POST["eMember_enable_public_profile"])?1:'');
        $emember_config->setValue('eMember_allow_account_removal', isset($_POST["eMember_allow_account_removal"])?1:'');
        $emember_config->setValue('eMember_allow_wp_account_removal', isset($_POST["eMember_allow_wp_account_removal"])?1:'');        
        $emember_config->setValue('eMember_secure_rss', isset($_POST["eMember_secure_rss"])?1:'');
        $emember_config->setValue('eMember_enable_fancy_login', isset($_POST["eMember_enable_fancy_login"])?$_POST["eMember_enable_fancy_login"]:'');
        $emember_config->setValue('eMember_enable_domain_lockdown', isset($_POST["eMember_enable_domain_lockdown"])?1:'');
        $emember_config->setValue('eMember_use_gravatar', isset($_POST["eMember_use_gravatar"])?"checked='checked'":'');
        $emember_config->setValue('eMember_custom_field', isset($_POST["eMember_custom_field"])?"checked='checked'":'');         
        $emember_config->setValue('eMember_email_notification', isset($_POST["eMember_email_notification"])?"checked='checked'":'');        
        $emember_config->setValue('eMember_protect_comments_separately', isset($_POST["eMember_protect_comments_separately"])?"checked='checked'":'');  
        $emember_config->setValue('eMember_enable_secondary_membership', isset($_POST["eMember_enable_secondary_membership"])?"checked='checked'":'');
        $emember_config->setValue('eMember_manually_approve_member_registration', isset($_POST["eMember_manually_approve_member_registration"])?1:''); 
        $emember_config->setValue('eMember_member_can_comment_only', isset($_POST["eMember_member_can_comment_only"])?1:'');     
        $emember_config->setValue('eMember_google_first_click_free', isset($_POST["eMember_google_first_click_free"])?1:'');     
        $emember_config->setValue('eMember_turn_off_protected_msg_formatting', isset($_POST["eMember_turn_off_protected_msg_formatting"])?1:'');
        
        //facebook feature     
        //$emember_config->setValue('eMember_enable_fb_reg', isset($_POST["eMember_enable_fb_reg"])?"checked='checked'":'');                
        $emember_config->setValue('eMember_secure_rss_seed', $_POST["eMember_secure_rss_seed"]?(string)$_POST["eMember_secure_rss_seed"]:"My Secret RSS Seed");

        $emember_config->setValue('wp_eMember_enable_remote_post', isset($_POST["wp_eMember_enable_remote_post"])?1:'');
        $emember_config->setValue('wp_eMember_secret_word_for_post',trim($_POST["wp_eMember_secret_word_for_post"]));        
        
        echo '<div id="message" class="updated fade"><p>';                
        echo ($msg)? '<strong style="color:red;">'.$msg : '<strong>Options Updated!';        
        echo '</strong></p></div>';  
        $emember_config->saveConfig();                        
    }
    if ($emember_config->getValue('eMember_enable_free_membership'))
    	$eMember_enable_free_membership = 'checked="checked"';
    else
    	$eMember_enable_free_membership = '';

    ?>
    <div class="eMember_grey_box">
    <p>For detailed documentation, information and updates, please visit the <a href="http://www.tipsandtricks-hq.com/wordpress-membership" target="_blank">WP eMember Documentation Page</a></p>
    <p>Like the plugin? Give us a <a href="http://www.tipsandtricks-hq.com/?p=1706#gfts_share" target="_blank">thumbs up here</a> by clicking on a share button or leave a comment to let us know.</p>
    </div>
    
    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <input type="hidden" name="info_update" id="info_update" value="true" />
	<div class="wrap"><h3>WP eMembers - General Settings</h3>
	<div id="poststuff">
	<div id="post-body">	
		<!-- <div class="basic" style="float:left;"  id="list1a"> -->
			<!-- <div class="title"><label for="title">General Settings</label></div> -->
    <div class="postbox">
         <h3><label for="title">General Settings</label></h3>
	    <div class="inside">
		    <table width="100%" border="0" cellspacing="0" cellpadding="6">
			     <tr valign="top">
				    <td width="25%" align="left">
				    	<strong>eMember Language:</strong>
			    	</td>
			    	<td align="left">
			    	    <?php $lang = $emember_config->getValue('eMember_language');?>
				    	<select name="eMember_language" >
				    		<option <?php echo ($lang==='eng')?'selected="selected"':'';?> value="eng">English</option>
				    		<option <?php echo ($lang==='fr')?'selected="selected"':'';?> value="fr">French</option>
				    		<option <?php echo ($lang==='ger')?'selected="selected"':'';?> value="ger">German</option>
				    		<option <?php echo ($lang==='heb')?'selected="selected"':'';?> value="heb">Hebrew</option>
				    		<option <?php echo ($lang==='ita')?'selected="selected"':'';?> value="ita">Italian</option>
				    		<option <?php echo ($lang==='spa')?'selected="selected"':'';?> value="spa">Spanish</option>
				    		<option <?php echo ($lang==='pl')?'selected="selected"':'';?> value="pl">Polish</option>
				    	</select><br/>
			    		<i>Select the language that you want your emember be displayed in.</i><br /><br />
			    	</td>
			    </tr>		    
			    <tr valign="top">
				    <td width="25%" align="left">
				    	<strong>Allow Free Membership:</strong>
			    	</td>
			    	<td align="left">
				    	<input type="checkbox" name="eMember_enable_free_membership" value="1" <?php echo $eMember_enable_free_membership; ?> /><br />
			    		<i>If you want to allow free membership on your site then check this box and specify the free Membership Level ID below. Visitors can register for a free membership by visiting the registration page.</i><br /><br />
			    	</td>
			    </tr>
			    <tr valign="top">
			    	<td width="25%" align="left">
			    		<strong>Free Membership Level ID:</strong>
			    	</td>
			    	<td align="left">
			    		<input name="eMember_free_membership_level_id" type="text" size="4" value="<?php echo $emember_config->getValue ('eMember_free_membership_level_id'); ?>"/><br /><i>If you want to allow free membership on your site then create a free membership level from the Manage Levels menu and specify the Level ID of that membership level here</i><br /><br />
			    	</td>
			    </tr>
			    <tr valign="top">
			    	<td width="25%" align="left">
			    		<strong>Entries Per Page:</strong>    
			    	</td>
			    	<td align="left">
			    		<input name="eMember_rows_per_page" type="text" size="4" value="<?php echo $emember_config->getValue('eMember_rows_per_page'); ?>"/><br />
			    		<i>Number of rows in each page for any list (e.g. the Manage Content Protection page). This value is used for pagination purpose.</i><br /><br />
			    	</td>
			    </tr>
			    <tr valign="top">
			    	<td width="25%" align="left">
			    		<strong>Enable More Tag Protection:</strong>    
			    	</td>
			    	<td align="left">
			    		<input name="eMember_enable_more_tag" type="checkbox"  <?php $enable_more_tag = $emember_config->getValue('eMember_enable_more_tag');echo ($enable_more_tag)?'checked="checked"':''?> value="1"/><br />
			    		<i>Enables or disables "more" tag protection in the posts and pages. Anything after the More tag is protected. Anything before the more tag is teaser content</i><br /><br />
			    	</td>
			    </tr>
			    <tr valign="top">
			    	<td width="25%" align="left">
			    		<strong>Enable After login Redirection:</strong>    
			    	</td>
			    	<td align="left">
			    		<input name="eMember_enable_redirection" type="checkbox"  <?php $enable_redirection = $emember_config->getValue('eMember_enable_redirection');echo ($enable_redirection)?'checked="checked"':''?> value="1"/><br />
			    		<i>Enables or disables redirection to a specified page after member login. You can specify the after login page in the membership level or in the pages settings menu.</i><br /><br />
			    	</td>
			    </tr>
			    <tr valign="top">
			    	<td width="25%" align="left">
			    		<strong>Enable Bookmarking Feature:</strong>    
			    	</td>
			    	<td align="left">
			    		<input name="eMember_enable_bookmark" type="checkbox"  <?php $enable_bookmark = $emember_config->getValue('eMember_enable_bookmark');echo ($enable_bookmark)?'checked="checked"':''?> value="1"/><br />
			    		<i>Allows your members to be able to bookmark your posts and pages for easy access later. Learn <a href="http://www.tipsandtricks-hq.com/wordpress-membership/?p=99" target="_blank">how to use the bookmarking feature</a>.</i><br /><br />
				    </td>
				</tr>
			    <tr valign="top">
			    	<td width="25%" align="left">
			    		<strong>Enable Public profile Listing:</strong>    
			    	</td>
			    	<td align="left">
			    		<input name="eMember_enable_public_profile" type="checkbox"  <?php $enable_public_profile = $emember_config->getValue('eMember_enable_public_profile');echo ($enable_public_profile)?'checked="checked"':''?> value="1"/><br />
			    		<i>This enables the member profiles to be publicly available for others to browse. Use the [wp_eMember_user_list] shortcode on a page to display all the member profiles.</i><br /><br />
				    </td>
				</tr>
			    <tr valign="top">
			    	<td width="25%" align="left">
			    		<strong>Login Restriction by IP Address:</strong>
			    	</td>
			    	<td align="left">
			    		<input name="eMember_login_limit" type="text" size="4" value="<?php echo $emember_config->getValue('eMember_login_limit'); ?>"/><i>IPs per day.<br />If the number of login attempts from different IP addresses exceed this limit then the member's account will be locked. Leave this field empty to disable this feature.</i><br /><br />
			    	</td>
			    </tr>				      				         
			    <tr valign="top">
			    	<td width="25%" align="left">
			    		<strong>Allow Account Deletion:</strong>
			    	</td>
			    	<td align="left">
			    		<input name="eMember_allow_account_removal" type="checkbox"  <?php $eMember_allow_account_removal = $emember_config->getValue('eMember_allow_account_removal');echo ($eMember_allow_account_removal)?'checked="checked"':''?> value="1"/><br />
			    		<i>Allows the member to delete their member account from the edit profile page.</i><br /><br />
			    	</td>
			    </tr>				   
			    <tr valign="top">
			    	<td width="25%" align="left">
			    		<strong>Allow Wordpress Account Deletion:</strong>
			    	</td>
			    	<td align="left">
			    		<input name="eMember_allow_wp_account_removal" type="checkbox"  <?php $eMember_allow_wp_account_removal = $emember_config->getValue('eMember_allow_wp_account_removal');echo ($eMember_allow_wp_account_removal)?'checked="checked"':''?> value="1"/><br />
			    		<i>If checked the corresponding WordPress user account will also be deleted when a member deletes his/her eMember account from the edit profile page</i><br /><br />
			    	</td>
			    </tr>				   
			    
			      <tr valign="top">
			    	<td width="25%" align="left">
			    		<strong>Display Secure RSS Feed:</strong>
			    	</td>
			    	<td align="left">
						<input name="eMember_secure_rss" type="checkbox"  <?php $eMember_secure_rss = $emember_config->getValue('eMember_secure_rss');echo ($eMember_secure_rss)?'checked="checked"':''?> value="1"/><br />
						<i>When checked the login widget will display a secure RSS feed link to the members.</i><br /><br />			    	
			    	</td>
			    </tr>
                <tr valign="top">
                    <td width="25%" align="left">
                        <strong>Enable Fancy Login Widget:</strong>
                    </td>
                    <td align="left">
                        <input id="eMember_enable_fancy_login" name="eMember_enable_fancy_login" type="checkbox"  <?php $eMember_enable_fancy_login = $emember_config->getValue('eMember_enable_fancy_login');echo ($eMember_enable_fancy_login)?'checked="checked"':''?> value="<?php echo empty($eMember_enable_fancy_login)? 1: $eMember_enable_fancy_login; ?>"/>
                        use 
                        <select id="emember_fancy_login_v">
                            <option value="1">Style 1</option>
                            <option value="2">Style 2</option>
                        </select>
                        <br />
                        <i>When checked, clicking the login link will make the login widget appear in a stylish way rather than redirecting to the login page.</i><br /><br />                    
                    </td>
                </tr>
                <tr valign="top">
                    <td width="25%" align="left">
                        <strong>Enable Domain Level Lockdown:</strong>
                    </td>
                    <td align="left">
                        <input name="eMember_enable_domain_lockdown" type="checkbox"  <?php $eMember_enable_domain_lockdown = $emember_config->getValue('eMember_enable_domain_lockdown');echo ($eMember_enable_domain_lockdown)?'checked="checked"':''?> value="1"/><br />
                        <i>When checked it will restrict anonymous visitor access to your site (the site won't even load unless the visitor logs in as a member). The only page the visitor will be able to access on the site when not logged in is the "Join Us" page.</i><br /><br />                    
                    </td>
                </tr>                                                                                                        
                <tr valign="top">
                    <td width="25%" align="left">
                        <strong>Protect Comments Separately:</strong>
                    </td>
                    <td align="left">
                        <input name="eMember_protect_comments_separately" type="checkbox"  <?php echo $emember_config->getValue('eMember_protect_comments_separately');?> value="1"/><br />
                        <i>By default all the comments on a post that is protected are also protected. If you want to individually protect each comment then check this option and protect the comments individually from the <a href="admin.php?page=eMember_membership_level_menu&level_action=2" target="_blank">Manage Content Protection</a> menu.</i><br /><br />                    
                    </td>
                </tr>

                <tr valign="top">
                    <td width="25%" align="left">
                        <strong>Enable Secondary Membership:</strong>
                    </td>
                    <td align="left">
                        <input name="eMember_enable_secondary_membership" type="checkbox"  <?php echo $emember_config->getValue('eMember_enable_secondary_membership');?> value="1"/><br />
                        <i>Enable/Disable the ability to assign multiple membership levels per user. <a href="http://www.tipsandtricks-hq.com/wordpress-membership/?p=291" target="_blank">Read More Here</a></i><br /><br />                    
                    </td>
                </tr>
                
				<tr valign="top">
                    <td width="25%" align="left">
                        <strong>Use Gravatar Image in Profile:</strong>
                    </td>
                    <td align="left">
                        <input name="eMember_use_gravatar" type="checkbox"  <?php echo $emember_config->getValue('eMember_use_gravatar');?> value="1"/><br />
                        <i>Enable/Disable usage of gravatar image in profile.</i><br /><br />                    
                    </td>
                </tr>                                                                                                                                                                    			    				      				         
				<tr valign="top">
                    <td width="25%" align="left">
                        <strong>Enable Custom Fields: </strong>
                    </td>
                    <td align="left">
                        <input name="eMember_custom_field" type="checkbox"  <?php echo $emember_config->getValue('eMember_custom_field');?> value="1"/><br />
                        <i>Enable/Disable the custom fields feature. You can configure custom fields from the "Custom Fields" settings tab.</i><br /><br />                    
                    </td>
                </tr>   
				<tr valign="top">
                    <td width="25%" align="left">
                        <strong>Enable Auto Expiry Email Notification: </strong>
                    </td>
                    <td align="left">
                        <input name="eMember_email_notification" type="checkbox"  <?php echo $emember_config->getValue('eMember_email_notification');?> value="1"/><br />
                        <i>Enable/Disable automatic membership expiry email notification. For example, the plugin will automatically send an email to the member after their membership expires. You can configure the email in the <a href="admin.php?page=eMember_settings_menu&tab=2" target="_blank">Email Settings page</a></i><br /><br />                    
                    </td>
                </tr>
		    	<tr valign="top">
		    		<td width="25%" align="left">
		    			<strong>Manually Approve Member Registration:</strong>
		    		</td>
		    		<td align="left">
		    			<input name="eMember_manually_approve_member_registration" type="checkbox"  <?php $eMember_manually_approve_member_registration = $emember_config->getValue('eMember_manually_approve_member_registration');echo ($eMember_manually_approve_member_registration)?'checked="checked"':''?> value="1"/><br />
		    			<i>If you want to manually approve the member after they register then check this option. A member's account will be "Inactive" after registration and the member will not be able to log in until you manually set the status to "Active".</i><br /><br />
			        </td>
                </tr>
		    	<tr valign="top">
		    		<td width="25%" align="left">
		    			<strong>Members Must be Logged in to Comment</strong>
		    		</td>
		    		<td align="left">
		    			<input name="eMember_member_can_comment_only" type="checkbox"  <?php $eMember_member_can_comment_only = $emember_config->getValue('eMember_member_can_comment_only');echo ($eMember_member_can_comment_only)?'checked="checked"':''?> value="1"/><br />
		    			<i>Check this option if you only want to allow the members of the site to be able to post a comment.</i><br /><br />
			        </td>
                </tr>                  
		    	<tr valign="top">
		    		<td width="25%" align="left">
		    			<strong>Enable Google First Click Free Feature</strong>
		    		</td>
		    		<td align="left">
		    			<input name="eMember_google_first_click_free" type="checkbox"  <?php $eMember_google_first_click_free = $emember_config->getValue('eMember_google_first_click_free');echo ($eMember_google_first_click_free)?'checked="checked"':''?> value="1"/><br />
		    			<i>Check this option if you want to enable the Google first click free feature. <a href="http://www.tipsandtricks-hq.com/wordpress-membership/?p=413" target="_blank">Read More Here</a></i><br /><br />
			        </td>
                </tr>   
		    	<tr valign="top">
		    		<td width="25%" align="left">
		    			<strong>Turn Off Protected Message Formatting</strong>
		    		</td>
		    		<td align="left">
		    			<input name="eMember_turn_off_protected_msg_formatting" type="checkbox"  <?php $eMember_turn_off_protected_msg_formatting = $emember_config->getValue('eMember_turn_off_protected_msg_formatting');echo ($eMember_turn_off_protected_msg_formatting)?'checked="checked"':''?> value="1"/><br />
		    			<i>By default eMember formats the protected messages to make it easily visible by placing it inside a warning box. Check this option if you do not want eMember to apply any formatting to messages (this will make eMember output the plain text only).</i><br /><br />
			        </td>
                </tr>                                                    
		    	<tr valign="top">
		    		<td width="25%" align="left">
		    			<strong>Login Widget Title:</strong>
		    		</td>
		    		<td align="left">
		    			<input name="wp_eMember_widget_title" type="text" size="100" value="<?php echo $emember_config->getValue ('wp_eMember_widget_title'); ?>"/><br/>
		    			<i>You can customize the login widget title here. This title is displayed when you use the eMember login widget from the widgets menu.</i><br /><br />
			        </td>
                </tr>                  
                
                <!--facebook feature-->     
                <!--<tr valign="top">
                    <td width="25%" align="left">
                        <strong>Enable Registration using Facebook: </strong>
                    </td>
                    <td align="left">
                        <input name="eMember_enable_fb_reg" type="checkbox"  <?php echo $emember_config->getValue('eMember_enable_fb_reg');?> value="1"/><br />
                        <i>eMember will use profile information from Facebook during registration if this feature is enabled.</i><br /><br />                    
                    </td>
                </tr>  -->   
                  <!--facebook feature-->                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                			    				      				                                                                                                                                                                                             			    				      				                                                                                                                                                                                          			    				      				                                                                                                                                                                                             			    				      				         
		    </table>
	    </div>
    </div>
   <!-- <div class="title"><label for="title">Account Upgrade and Renewal Settings</label></div> -->
    <div class="postbox">
    <h3><label for="title">Account Upgrade and Renewal Settings</label></h3>
	    <div class="inside">    
	    <strong><i>When a logged in member makes a new membership payment, his/her account is automatically upgraded/renewed to reflect the recent payment.</i></strong><br /><br />
		    <table width="100%" border="0" cellspacing="0" cellpadding="6">    
		    	<tr valign="top">
		    		<td width="25%" align="left">
		    			<strong>Allow Expired Account login:</strong>    
		    		</td>
		    		<td align="left">
		    			<input name="eMember_allow_expired_account" type="checkbox"  <?php $allow_expired = $emember_config->getValue('eMember_allow_expired_account');echo ($allow_expired)?'checked="checked"':''?> value="1"/><br />
		    			<i>When checked members whose account has expired will be able to log into the system but won't be able to view any protected content. This will allow them to easily renew their account by making another payment.</i><br /><br />
				    </td>
				</tr>        
		    	<tr valign="top">
		    		<td width="25%" align="left">
		    			<strong>Membership renewal Page:</strong>    
		    		</td>
		    		<td align="left">
		    			<input name="eMember_account_upgrade_url" type="text" size="100" value="<?php echo $emember_config->getValue('eMember_account_upgrade_url'); ?>"/><br />
		    			<i>The URL of the page where members can renew their membership. You can simply use the same page as the "Membership Payment/Join Page".</i><br /><br />
		    		</td>
		    	</tr>             
		    </table>
	    </div>
    </div>
   <!-- <div class="title"><label for="title">WordPress User Integration Settings (Only use this if you want to integrate the member's of WP eMember with your WordPress users)</label></div> -->               
		    <div class="postbox">
		    <h3><label for="title">WordPress User Integration Settings (Only use this if you want to integrate the member's of WP eMember with your WordPress users)</label></h3>
    	<div class="inside">
		    <table width="100%" border="0" cellspacing="0" cellpadding="6">		    
		    	<tr valign="top">
		    		<td width="25%" align="left">
		    			<strong>Automatically Create Wordpress User:</strong>    
		    		</td>
		    		<td align="left">
		    			<input name="eMember_create_wp_user" type="checkbox"  <?php $create_wp_user = $emember_config->getValue('eMember_create_wp_user');echo ($create_wp_user)?'checked="checked"':''?> value="1"/><br />
		    			<i>If checked it will automatically create a new wordpress user with the same credentials when a new member is registered with eMember.</i><br /><br />
				    </td>
				</tr>
		    	<tr valign="top">
		    		<td width="25%" align="left">
		    			<strong>Automatically log into Wordpress:</strong>    
		    		</td>
		    		<td align="left">
		    			<input name="eMember_signin_wp_user" type="checkbox"  <?php $create_signin_user = $emember_config->getValue('eMember_signin_wp_user');echo ($create_signin_user)?'checked="checked"':''?> value="1"/><br />
		    			<i>If checked members will be automatically logged into wordpress when they log in using the eMember login.</i><br /><br />
			    	</td>
			    </tr>
		    	<tr valign="top">
		    		<td width="25%" align="left">
		    			<strong>Automatically log into eMember:</strong>    
		    		</td>
		    		<td align="left">
		    			<input name="eMember_signin_emem_user" type="checkbox"  <?php $create_signin_user = $emember_config->getValue('eMember_signin_emem_user');echo ($create_signin_user)?'checked="checked"':''?> value="1"/><br />
		    			<i>If checked members will be automatically logged into eMember when they log in using the wordpress login system.</i><br /><br />
			    	</td>
			    </tr>    		        
			        		        
		    </table>
    	</div>
    </div> 
    <!-- <div class="title"><label for="title">WP Affiliate Platform Account Creation Settings</label></div> -->
    <div class="postbox">
        <h3><label for="title">WP Affiliate Platform Account Creation Settings</label></h3> <!-- added -->
	    <div class="inside">    
	    <strong><i>Only use this option if you are using the WP eMember plugin together with the <a href="http://www.tipsandtricks-hq.com/?p=1474" target="_blank">WP Affiliate Platform</a> plugin.</i></strong><br /><br />
		    <table width="100%" border="0" cellspacing="0" cellpadding="6">    
		    	<tr valign="top">
		    		<td width="25%" align="left">
		    			<strong>Automatically Create Affiliate Account:</strong>
		    		</td>
		    		<td align="left">
		    			<input name="eMember_auto_affiliate_account" type="checkbox"  <?php $eMember_auto_affiliate_account = $emember_config->getValue('eMember_auto_affiliate_account');echo ($eMember_auto_affiliate_account)?'checked="checked"':''?> value="1"/><br />
		    			<i>If checked an affiliate account will automatically be created with the same login credentials for each member when they register with the eMember plugin.</i><br /><br />
			        </td>
                </tr>
		    	<tr valign="top">
		    		<td width="25%" align="left">
		    			<strong>Automatically Log into Affiliate Account:</strong>
		    		</td>
		    		<td align="left">
		    			<input name="eMember_auto_affiliate_account_login" type="checkbox"  <?php $eMember_auto_affiliate_account_login = $emember_config->getValue('eMember_auto_affiliate_account_login');echo ($eMember_auto_affiliate_account_login)?'checked="checked"':''?> value="1"/><br />
		    			<i>If checked a member will automatically be logged into the affiliate account when logging into eMember.</i><br /><br />
			        </td>
                </tr>   
                
			    <tr valign="top"><td width="25%" align="left">
			    <strong>Limit Account Creation to Particular Levels Only</strong>
			    </td><td align="left">
			    <input name="wp_eMember_affiliate_account_restriction_list" type="text" size="50" value="<?php echo $emember_config->getValue('wp_eMember_affiliate_account_restriction_list'); ?>"/>
			    <br /><i>Only use this field if you want to restrict the affiliate account creation for particular membership levels only. Specify the membership level IDs separated by comma (,) in the above field (for example: 1,2,3).</i><br />
			    </td></tr>  
                          
		    </table>
	    </div>
    </div>    

	<div class="postbox">
	<h3><label for="title">Additional Integration Options</label></h3>
	<div class="inside">

	<br />
	<strong><i>(Only use this section if you have been instructed to do so from one of the <a href="http://www.tipsandtricks-hq.com/wordpress-membership/" target="_blank">documentation pages</a>)</i></strong>
	<br /><br />
		
    <table width="100%" border="0" cellspacing="0" cellpadding="6">    
    <tr valign="top"><td width="25%" align="left">
    <strong>Enable Remote POST:</strong>
    </td><td align="left">
    <input type="checkbox" name="wp_eMember_enable_remote_post" <?php $wp_eMember_enable_remote_post = $emember_config->getValue('wp_eMember_enable_remote_post');echo ($wp_eMember_enable_remote_post)?'checked="checked"':''?> value="1" />
    <br /><i>Check this box if you want to be able to create or cancel member account by sending a HTTP POST or GET request to a URL.</i><br /><br />
    </td></tr>
           
    <tr valign="top"><td width="25%" align="left">
    <strong>Secret Word:</strong>
    </td><td align="left">
    <input name="wp_eMember_secret_word_for_post" type="text" size="30" value="<?php echo $emember_config->getValue('wp_eMember_secret_word_for_post'); ?>"/>
    <br /><i>This secret word will be used to verify any request sent to the POST URL. You can change this code to something random.</i><br />
    </td></tr>  
    </table>
    </div></div>
        
    <div class="postbox">
        <h3><label for="title">Testing and Debugging Settings</label></h3>
	    <div class="inside">    
	    <strong><i>You do not need to use these options if you are using WP eStore plugin to process the membership payments</i></strong><br /><br />
		    <table width="100%" border="0" cellspacing="0" cellpadding="6">    
		    	<tr valign="top">
		    		<td width="25%" align="left">
		    			<strong>Enable Debug:</strong>
		    		</td>
		    		<td align="left">
		    			<input name="eMember_enable_debug" type="checkbox"  <?php $eMember_enable_debug = $emember_config->getValue('eMember_enable_debug');echo ($eMember_enable_debug)?'checked="checked"':''?> value="1"/><br />
		    			<i>If checked, debug output will be written to log files. This is useful for troubleshooting post payment failures (for example, if the members are not receiving the email after payment).</i><br /><br />
			        </td>
                </tr>
		    	<tr valign="top">
		    		<td width="25%" align="left">
		    			<strong>Enable Sandbox Testing:</strong>
		    		</td>
		    		<td align="left">
		    			<input name="eMember_enable_sandbox" type="checkbox"  <?php $eMember_enable_sandbox = $emember_config->getValue('eMember_enable_sandbox');echo ($eMember_enable_sandbox)?'checked="checked"':''?> value="1"/><br />
		    			<i>If checked the plugin will run in Sandbox/Testing mode (eg. <a href="http://www.tipsandtricks-hq.com/?p=2880" target="_blank">PayPal Sandbox</a>). Useful for testing.</i><br /><br />
			        </td>
                </tr>                
		    </table>
	    </div>
    </div>    
        
               <div class="submit">
                 <input type="submit" name="info_update" value="<?php _e('Update options'); ?> &raquo;" />	    
               </div>			
		<!-- </div> -->
		</div>
		</div>
		</div>
    </form>
<script type="text/javascript">
//jQuery(document).ready(function(){
//	jQuery('#list1a').accordion({autoHeight:false});
//	jQuery('#list1a a').click(function(e){ e.stopPropagation(); });
//	
//});
jQuery(document).ready(function($){
    $('#emember_fancy_login_v').change(function(){
        $('#eMember_enable_fancy_login').val($(this).val());
    }).val($('#eMember_enable_fancy_login').val());
});
</script>        
    <?php
}
?>