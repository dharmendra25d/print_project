<?php
function filter_eMember_edit_profile_form($content){
    global $auth; 

    $pattern = '#\[wp_eMember_profile_edit_form:end]#';
    preg_match_all ($pattern, $content, $matches);
    if((count($matches[0])>0)&&!$auth->isLoggedIn()){
        return EMEMBER_PROFILE_MESSAGE;
    }

    foreach ($matches[0] as $match){
        $replacement = print_eMember_edit_profile_form();
        $content = str_replace ($match, $replacement, $content);
    }

    return $content;
}

function print_eMember_edit_profile_form(){
    return show_edit_profile_form();
}

function show_edit_profile_form()
{
	if(isset($_POST['eMember_update_profile']) && isset($_POST['eMember_profile_update_result']))
	{
		$output = $_POST['eMember_profile_update_result'];
		if(!empty($_POST['wp_emember_pwd'])){//Password has been changed
			$output .= '<div class="emember_warning">'.EMEMBER_PASSWORD_CHANGED_RELOG_RECOMMENDED.'</div>';
		}		
		return $output;
	}
    global $wpdb;
    global $emember_config;	    
    global $emember_auth;
    $emember_auth = Emember_Auth::getInstance();
    $emember_config = Emember_Config::getInstance();    
	$d = WP_EMEMBER_URL.'/images/default_image.gif';
    $member_id  = $emember_auth->getUserInfo('member_id');
    $resultset  = dbAccess::find(WP_EMEMBER_MEMBERS_TABLE_NAME, ' member_id=' . $wpdb->escape($member_id));
    $edit_custom_fields = dbAccess::find(WP_EMEMBER_MEMBERS_META_TABLE, ' user_id=' . $wpdb->escape($member_id) . ' AND meta_key=\'custom_field\'');
    $edit_custom_fields = unserialize($edit_custom_fields->meta_value);
    $username   = $resultset->user_name;
    $first_name = $resultset->first_name;
    $last_name  = $resultset->last_name;
    $phone      = $resultset->phone;
    $email      = $resultset->email;
    $password   = $resultset->password;
    $address_street  = $resultset->address_street;
    $address_city    = $resultset->address_city;
    $address_state   = $resultset->address_state;
    $address_zipcode = $resultset->address_zipcode;
    $country         = $resultset->country;
    $gender          = $resultset->gender;
    $company         = $resultset->company_name;    
    $image_url       = null;
    $image_path  = null;
	$upload_dir  = wp_upload_dir();
    $upload_url  = $upload_dir['baseurl'];
    $upload_path = $upload_dir['basedir'];
    $upload_url  .= '/emember/';
    $upload_path .= '/emember/';
    $upload_url  .= $emember_auth->getUserInfo('member_id');
    $upload_path .= $emember_auth->getUserInfo('member_id');
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
	    	$image_url = WP_EMEMBER_GRAVATAR_URL . "/" . md5(strtolower($email)) . "?d=" . urlencode($d) . "&s=" . 96;
    	else
    		$image_url = WP_EMEMBER_URL . '/images/default_image.gif';
    }
    
    $f = $emember_config->getValue('eMember_allow_account_removal');
    $delete_button = empty($f)? '': '<a id="delete_account_btn" href="'.get_bloginfo('wpurl').
                     '?event=delete_account" >Delete Account</a> ';
    ob_start();
    echo isset($msg)?'<span class="emember_error">'.$msg . '</span>': '';    
	?>
    <script type="text/javascript"> 
/* <![CDATA[ */   
    jQuery(document).ready(function($){
        <?php echo include_once('emember_js_form_validation_rules.php');?>        
    	$("#wp_emember_profileUpdateForm").validationEngine('attach');     	  
    });    	
/*]]>*/      
    </script>        
	
    <form action="" method="post" name="wp_emember_profileUpdateForm" id="wp_emember_profileUpdateForm" >
    <input type="hidden" name="member_id" id="member_id" value ="<?php echo $member_id;?>" />
    <table width="95%" border="0" cellpadding="3" cellspacing="3" class="forms">	
	<tr>
		<td><label class="eMember_label"> <?php echo EMEMBER_USERNAME;?>: </label></td>
		<td><label class="eMember_highlight"><?php echo $username;?></label></td>
	</tr>
	<?php if($emember_config->getValue('eMember_profile_thumbnail')):?>
	<tr>
		<td><label class="eMember_label"><?php echo EMEMBER_PROFILE_IMAGE;?>: </label></td>
		<td>
			<img id="emem_profile_image" src="<?php echo $image_url;?> "  width="100px" height="100px"/>
			<a id="remove_button" href="<?php echo $image_path; ?>">Remove</a>
			<div id="upload_button" class="emember_upload_div">Upload</div><br/><span id="error_msg"></span>
		</td>
    </tr>
    <?php endif;?>
    <?php if($emember_config->getValue('eMember_edit_firstname')):?>
    <tr>
       <td><label for="wp_emember_firstname" class="eMember_label"><?php echo EMEMBER_FIRST_NAME;?>: </label></td>
       <td><input type="text" id="wp_emember_firstname" name="wp_emember_firstname" size="20" value="<?php echo $first_name;?>" class="<?php echo $emember_config->getValue('eMember_edit_firstname_required')? 'validate[required] ': "";?>eMember_text_input" /></td>
    </tr>
    <?php endif;?>
    <?php if($emember_config->getValue('eMember_edit_lastname')):?>
    <tr>
       <td><label for="wp_emember_lastname" class="eMember_label"><?php echo EMEMBER_LAST_NAME;?>: </label></td>
       <td><input type="text" id="wp_emember_lastname"  name="wp_emember_lastname" size="20" value="<?php echo $last_name;?>" class="<?php echo $emember_config->getValue('eMember_edit_lastname_required')? 'validate[required] ': "";?>eMember_text_input" /></td>
    </tr>
    <?php endif;?>
    <?php if($emember_config->getValue('eMember_edit_company')):?>
    <tr>
       <td><label for="wp_emember_company_name" class="eMember_label"><?php echo EMEMBER_COMPANY ?>: </label></td>
       <td><input type="text" id="wp_emember_company_name"  name="wp_emember_company_name" size="20" value="<?php echo $company ?>" class="<?php echo $emember_config->getValue('eMember_edit_company_required')? 'validate[required] ': "";?>eMember_text_input" /></td>
    </tr>
    <?php endif;?>    
    <?php if($emember_config->getValue('eMember_edit_email')):?>
    <tr>
       <td><label for="wp_emember_email" class="eMember_label"><?php echo EMEMBER_EMAIL;?>: </label></td>
       <td><input type="text" id="wp_emember_email" name="wp_emember_email" size="20" value="<?php echo $email;?>" class="<?php echo $emember_config->getValue('eMember_edit_email_required')? 'validate[required] ': "";?>eMember_text_input" /></td>
    </tr>
    <?php endif;?>
    <?php if($emember_config->getValue('eMember_edit_phone')):?>
    <tr>
       <td><label for="wp_emember_phone" class="eMember_label"><?php echo EMEMBER_PHONE?>: </label></td>
       <td><input type="text" id="wp_emember_phone" name="wp_emember_phone" size="20" value="<?php echo $phone ?>" class="<?php echo $emember_config->getValue('eMember_edit_phone_required')? 'validate[required] ': "";?>eMember_text_input" /></td>
    </tr>    
    <?php endif;?>
    <tr>
       <td><label for="wp_emember_pwd" class="eMember_label"><?php echo EMEMBER_PASSWORD ?>: </label></td>
       <td><input type="password" id="wp_emember_pwd" name="wp_emember_pwd" size="20" value="" class="eMember_text_input" /><br/></td>
    </tr>
    <tr>
       <td><label for="wp_emember_pwd_r" class="eMember_label"><?php echo EMEMBER_PASSWORD_REPEAT ?>: </label></td>
       <td><input type="password" id="wp_emember_pwd_r" name="wp_emember_pwd_r" size="20" value="" class="validate[equals[wp_emember_pwd]] eMember_text_input" /><br/></td>
    </tr>    
    <?php if($emember_config->getValue('eMember_edit_street')):?>    
    <tr>
       <td><label for="wp_emember_street" class="eMember_label"><?php echo EMEMBER_ADDRESS_STREET?>: </label></td>
       <td><input type="text" id="wp_emember_street" name="wp_emember_street" size="20" value="<?php echo $address_street ?>" class="<?php echo $emember_config->getValue('eMember_edit_street_required')? 'validate[required] ': "";?>eMember_text_input" /></td>
    </tr>
    <?php endif;?>
    <?php if($emember_config->getValue('eMember_edit_city')):?>    
    <tr>    
       <td><label for="wp_emember_city" class="eMember_label"><?php echo EMEMBER_ADDRESS_CITY ?>: </label></td>
       <td><input type="text" id="wp_emember_city" name="wp_emember_city" size="20" value="<?php echo $address_city?>" class="<?php echo $emember_config->getValue('eMember_edit_city_required')? 'validate[required] ': "";?>eMember_text_input" /></td>
    </tr>
    <?php endif;?>
    <?php if($emember_config->getValue('eMember_edit_state')):?>    
    <tr>
       <td><label for="wp_emember_state" class="eMember_label"><?php echo EMEMBER_ADDRESS_STATE ?>: </label></td>
       <td><input type="text"  id="wp_emember_status" name="wp_emember_state" size="20" value="<?php echo $address_state ?>" class="<?php echo $emember_config->getValue('eMember_edit_state_required')? 'validate[required] ': "";?>eMember_text_input" /></td>
    </tr>
    <?php endif;?>
    <?php if($emember_config->getValue('eMember_edit_zipcode')):?>    
    <tr>
       <td><label for="wp_emember_zipcode" class="eMember_label"><?php echo EMEMBER_ADDRESS_ZIP ?>: </label></td>
       <td><input type="text"  id="wp_emember_zipcode" name="wp_emember_zipcode" size="20" value="<?php echo $address_zipcode ?>" class="<?php echo $emember_config->getValue('eMember_edit_zipcode_required')? 'validate[required] ': "";?>eMember_text_input" /></td>
    </tr>
    <?php endif;?>
    <?php if($emember_config->getValue('eMember_edit_country')):?>
    
    <tr>
       <td><label for="wp_emember_country" class="eMember_label"><?php echo EMEMBER_ADDRESS_COUNTRY ?>: </label></td>
       <td><input type="text"  id="wp_emember_country" name="wp_emember_country" size="20" value="<?php echo $country ?>" class="<?php echo $emember_config->getValue('eMember_edit_country_required')? 'validate[required] ': "";?>eMember_text_input" /></td>
    </tr>
    <?php endif;?>
    <?php if($emember_config->getValue('eMember_edit_gender')):?>    
	<tr >
		<td > <label for="wp_emember_gender" class="eMember_label"><?php echo EMEMBER_GENDER ?>: </label></td>
		<td>
	   <select name="wp_emember_gender" id="wp_emember_gender">
	      <option  <?php echo (($gender ==='male') ? 'selected=\'selected\'' : '' ) ?> value="male"><?php echo EMEMBER_GENDER_MALE ?></option>
	      <option  <?php echo (($gender ==='female') ? 'selected=\'selected\'' : '' ) ?> value="female"><?php echo EMEMBER_GENDER_FEMALE ?></option>      
	      <option  <?php echo (($gender ==='not specified') ? 'selected=\'selected\'' : '' ) ?> value="not specified"><?php echo EMEMBER_GENDER_UNSPECIFIED ?></option>      
	   </select>
		</td>
	</tr>        
	<?php 
	endif;
	include ('custom_field_template.php');	
	?> 
    <tr>
    <td >
      <?php echo $delete_button ?>
    </td>
    <td>
       <input class="eMember_button" name="eMember_update_profile" type="submit" id="eMember_update_profile" class="button" value="<?php echo EMEMBER_UPDATE ?>" />
    </td>
    </tr>
    </table>
    </form><br />
    <script type="text/javascript">
/* <![CDATA[ */    
    jQuery(document).ready(function(){
        jQuery("#delete_account_btn").click(function(){
            top.document.location = jQuery(this).attr("href");
        }).confirm({timeout:5000});
       <?php if($emember_config->getValue('eMember_profile_thumbnail')):?>
	    var upload_button = jQuery("#upload_button");
	    var target_url = "<?php echo  get_bloginfo('wpurl'); ?>";
	    interval = "";
	    jQuery('#remove_button').click(function(e){
	        var imagepath = jQuery(this).attr('href');
	        if(imagepath){
	        jQuery.get( '<?php echo admin_url('admin-ajax.php');?>',{"action":"delete_profile_picture","path":imagepath},
	                function(data){
	     	              jQuery("#emem_profile_image").attr("src",   "<?php echo WP_EMEMBER_URL; ?>/images/default_image.gif?" + (new Date()).getTime());  
	      	             jQuery('#remove_button').attr('href','');         
	                },
	          "json");
	        }
	        e.preventDefault();
	    }); 	    
	    new AjaxUpload(upload_button, 
	    {
	        action:  "<?php echo admin_url('admin-ajax.php');?>?event=emember_upload_ajax&action=emember_upload_ajax",
	        name: "profile_image",
	        data :{image_id: <?php echo $emember_auth->getUserInfo('member_id');?>} ,
	        onSubmit : function(file , ext){    
	            if (ext && /^(jpg|png|jpeg|gif)$/.test(ext)){
	                upload_button.text("Uploading");
	                this.disable();
	                interval = window.setInterval(function(){
	                    var text = upload_button.text();
	                    if (text.length < 13){
	                        upload_button.text(text + ".");         
	                    } else {
	                        upload_button.text("Uploading");        
	                    }
	                }, 200);
	                jQuery("#error_msg").css("color","").text("Uploading ");  
	            } else {          
	                jQuery("#error_msg").css("color","red").text("Error: only images are allowed");
	                return false;       
	            }   
	        },    
	        onComplete: function(file, response){
	            upload_button.text("Upload");           
	            window.clearInterval(interval);
	            this.enable();      
	            response = eval( "("+response+")");   
	            if(response.status==1){         
		            var $url = "<?php echo $upload_dir['baseurl'] ;?>/emember/" +response.file +"?" + (new Date()).getTime();		            
	                jQuery("#emem_profile_image").attr("src", $url);
	    			jQuery("#error_msg").css("color","").text("Uploaded");
	    		}	
	    		else{
	    			jQuery("#error_msg").css("color","").text("Error Occurred.Check file size.");
	    		}						
	        }
	    });
	    <?php endif;?> 	    
	});
/*]]>*/
		</script>	
	<?php 
    $output = ob_get_contents();
    ob_end_clean();	
    return $output;
}
