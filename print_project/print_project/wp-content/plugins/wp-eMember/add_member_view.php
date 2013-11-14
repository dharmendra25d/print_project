<style type="text/css">
        .emember_upload { padding: 0 20px; float: left; width: 230px; }
		.emember_upload_wrapper { width: 133px;  }		
		.emember_upload_div {
			height: 24px;	
			width: 133px;
            left:0%;
			background: #FFF 0 0;			
			font-size: 14px; color: #000; text-align: center; padding-top: 0px;
		}
		/* 
		We can't use ":hover" preudo-class because we have
		invisible file input above, so we have to simulate
		hover effect with JavaScript. 
		 */
		.emember_upload_div.hover {
			background: #6D6D6D 0 56px;
			color: #FFF;	
		}
		.emember_upload_div a.hover{
			color:#fff;
        }
}
</style>
    <script type="text/javascript">
/* <![CDATA[ */    
    jQuery(document).ready(function($){
        <?php echo include_once('emember_js_form_validation_rules.php');?>        
    	$.validationEngineLanguage.allRules['ajaxUserCall']['url']= '<?php echo admin_url('admin-ajax.php');?>';
    	$("#wp_emember_admin_regform").validationEngine('attach');     	  
    });    	
/* ]]> */      
    </script> 
<form method="post" id="wp_emember_admin_regform" action="admin.php?page=wp_eMember_manage&members_action=add_edit">
<!--<form method="post" action="javascript:void(0);">-->
<table class="form-table">
<tr valign="top">
<th scope="row"><?php _e('User Name', 'wp_eMember'); ?></th>
<td>
    <?php
    if ($_GET['editrecord']!=''):
    ?>
    <input name="editedrecord" id="editedrecord"  type="hidden" value="<?php echo $_GET['editrecord'];?>" />
    <input name="user_name" id="user_name"  type="hidden" value="<?php echo stripslashes($editingrecord['user_name']); ?>" />
    <b><?php echo stripslashes($editingrecord['user_name']); ?></b>
    <?php
    else:
    ?>
    <input name="user_name" class="validate[required,custom[onlyLetterNumberUnderscore],minSize[4],ajax[ajaxUserCall]]" type="text" id="user_name" value="<?php echo stripslashes($editingrecord['user_name']); ?>" size="30" />
    <br/><?php _e('User Name of the Member', 'wp_eMember'); ?>
    <?php
    endif;
    ?>
</td>
</tr>
<?php
if($_GET['editrecord']!=''): 
?>
<?php
global $emember_config;
$emember_config = Emember_Config::getInstance();    
if($emember_config->getValue('eMember_profile_thumbnail')):
?>
<tr>
	<th><?php  echo EMEMBER_PROFILE_IMAGE;?>:</th>
	<td>
		<img id="emem_profile_image" src="<?php echo $image_url; ?>"  width="100px" height="100px"/><br/>
		<a id="remove_button" href="<?php echo $image_path; ?>">Remove</a><span id="error_msg"></span>
		<div id="upload_button" class="emember_upload_div">Upload</div>
	</td>
</tr>
<?php 
endif;
endif;
?>
<tr valign="top">
    <th scope="row"><?php _e('First Name', 'wp_eMember'); ?></th>
    <td><input name="first_name" type="text" id="first_name" value="<?php echo stripslashes($editingrecord['first_name']); ?>" size="30" /></td>
</tr>
<tr valign="top">
    <th scope="row"><?php _e('Last Name', 'wp_eMember'); ?></th>
    <td><input name="last_name" type="text" id="last_name" value="<?php echo stripslashes($editingrecord['last_name']); ?>" size="30" /></td>
</tr>
<?php if ($_GET['editrecord']!=''):?>
<tr valign="top">
    <th scope="row"><?php _e('Password', 'wp_eMember'); ?></th>
    <td><input name="password" type="password" id="password"  value=""  size="30" /> <!--class="validate[equals[retype_password]]"-->
    Leave empty to keep current password.</td>
</tr>
<tr valign="top">
    <th scope="row"><?php _e('Retype Password', 'wp_eMember'); ?></th>
    <td><input name="retype_password" type="password" id="retype_password" class="validate[equals[password]]" value="" size="30" /></td>
</tr>
<?php else:?>
<tr valign="top">
    <th scope="row"><?php _e('Password', 'wp_eMember'); ?></th>    
    <td><input name="password" type="password" id="password" value=""  size="30" /><!--class="validate[required,equals[retype_password]]"-->
</tr>
<tr valign="top">
    <th scope="row"><?php _e('Retype Password', 'wp_eMember'); ?></th>
    <td><input name="retype_password" type="password" id="retype_password" class="validate[required,equals[password]]" value="" size="30" /></td>
</tr>
<?php endif;?>
<tr valign="top">
    <th scope="row"><?php _e('Company', 'wp_eMember'); ?></th>
    <td><input name="company_name" type="text" id="company_name" value="<?php echo stripslashes($editingrecord['company_name']); ?>" size="30" /></td>
</tr>
<tr valign="top">
    <th scope="row"><?php _e('Member Since', 'wp_eMember'); ?></th>
    <td><input name="member_since" type="date" id="member_since" value="<?php echo empty($editingrecord['member_since']) ? date('Y-m-d') : $editingrecord['member_since']; ?>" size="20" /></td>
</tr>

<tr valign="top">
    <th scope="row"><?php _e('Membership Level', 'wp_eMember'); ?></th>
    <td>
          <select name="membership_level"  id="membership_level">
             <?php foreach($all_levels as $level){?>
             <option <?php echo ($editingrecord['membership_level'] ===$level->id)? "selected='selected'" : null;?> value="<?php echo $level->id?>"><?php echo $level->alias;?></option>
             <?php }?>
          </select>
    </td>
</tr>
<?php if($emember_config->getValue('eMember_enable_secondary_membership')):?>
<tr valign="top">
    <th scope="row"><?php _e('Additional Membership Levels', 'wp_eMember'); ?></th>
    <td>
          <table id="eMembership_level_container">
             <?php foreach($all_levels as $level){?>
             <tr>
                <td><input type="checkbox" class="eMembership_level" id="membership_level_<?php echo $level->id;?>" name=more_membership_levels[] value="<?php echo $level->id;?>" <?php echo in_array($level->id ,(array)$editingrecord['more_membership_levels'])? "checked='checked'" : ""?>  ></input></td>
                <td><?php echo $level->alias;?></td>
             </tr>
             <?php }?>
          </table>
    </td>
</tr>
<?php endif;?>
<tr valign="top">
    <th scope="row"><?php _e('Account State', 'wp_eMember'); ?></th>
    <td>   
        <select name="account_state"  id="account_state">
          <option <?php echo ($editingrecord['account_state'] ==='active')? "selected='selected'" : null;?> value="active">Active</option>
          <option <?php echo ($editingrecord['account_state'] ==='inactive')? "selected='selected'" : null;?> value="inactive">Inactive</option>
          <option <?php echo ($editingrecord['account_state'] ==='expired')? "selected='selected'" : null;?> value="expired">expired</option>
          <!--<option <?php echo ($editingrecord['account_state'] ==='pending')? "selected='selected'" : null;?> value="pending">Pending</option>-->
       </select>
    </td>
</tr>
<tr valign="top">
    <th scope="row"><?php _e('Email Address', 'wp_eMember'); ?></th>
    <td><input name="email" type="text" class="validate[required,custom[email]]" id="email" value="<?php echo stripslashes($editingrecord['email']); ?>" size="30" /></td>
</tr>
<tr valign="top">
    <th scope="row"><?php _e('Phone No.', 'wp_eMember'); ?></th>
    <td><input name="phone" type="text" id="phone" class="validate[custom[phone]]" value="<?php echo stripslashes($editingrecord['phone']); ?>" size="30" /></td>
</tr>
<tr valign="top">
    <th scope="row"><?php _e('Address Street', 'wp_eMember'); ?></th>
    <td><input name="address_street" type="text" id="address_street" value="<?php echo stripslashes($editingrecord['address_street']); ?>" size="30" /></td>
</tr>

<tr valign="top">
    <th scope="row"><?php _e('Address City', 'wp_eMember'); ?></th>
    <td><input name="address_city" type="text" id="address_city" value="<?php echo stripslashes($editingrecord['address_city']); ?>" size="30" /></td>
</tr>

<tr valign="top">
    <th scope="row"><?php _e('Address State', 'wp_eMember'); ?></th>
    <td><input name="address_state" type="text" id="address_state" value="<?php echo stripslashes($editingrecord['address_state']); ?>" size="30" /></td>
</tr>

<tr valign="top">
    <th scope="row"><?php _e('Address Zipcode', 'wp_eMember'); ?></th>
    <td><input name="address_zipcode" type="text" id="address_zipcode" value="<?php echo stripslashes($editingrecord['address_zipcode']); ?>" size="30" /></td>
</tr>
<tr valign="top">
    <th scope="row"><?php _e('Country', 'wp_eMember'); ?></th>
    <td><input name="country" type="text" id="country" value="<?php echo stripslashes($editingrecord['country']); ?>" size="30" /></td>
</tr>

<tr valign="top">
    <th scope="row"><?php _e('Gender', 'wp_eMember'); ?></th>
    <td>   
       <select name="gender" id="gender">
          <option <?php echo ($editingrecord['gender'] ==='male')? "selected='selected'" : null;?> value="male">Male</option>
          <option <?php echo ($editingrecord['gender'] ==='female')? "selected='selected'" : null;?> value="female">Female</option>
          <option <?php echo ($editingrecord['gender'] ==='not specified')? "selected='selected'" : null;?> value="not specified">Not Specified</option>
       </select>
    </td>
</tr>
<tr valign="top">
    <th scope="row"><?php _e('Referrer', 'wp_eMember'); ?></th>
    <td><input name="referrer" type="text" id="referrer" value="<?php echo stripslashes($editingrecord['referrer']); ?>" size="30" /></td>
</tr>
<tr valign="top">
    <th scope="row"><?php _e('Subscription Starts', 'wp_eMember'); ?></th>
    <td><input name="subscription_starts" type="date" id="subscription_starts" value="<?php echo empty($editingrecord['subscription_starts'])? date('Y-m-d'):$editingrecord['subscription_starts']; ?>" size="20" /></td>
</tr>
<tr valign="top">
    <th scope="row"><?php _e('Unique Reference/Subscriber ID', 'wp_eMember'); ?></th>
    <td><input name="subscr_id" type="text" id="subscr_id" value="<?php echo stripslashes($editingrecord['subscr_id']); ?>" size="30" />
    <br /><i>This reference value is used to recognize future membership payments from this user. You do not need to change the value of this field.</i>
    </td>    
</tr>
 	<?php
	if($emember_config->getValue('eMember_custom_field')):	
	$custom_fields = get_option('emember_custom_field_type');
        if(!isset($custom_fields['emember_field_flag'] ))
	for($i=0; isset($custom_fields['emember_field_name'][$i]); $i++){
	    $emember_field_name = $custom_fields['emember_field_name'][$i];
	    $emember_field_name = stripslashes($emember_field_name);	         
	    $emember_field_name_index = str_replace(array('\\','\'','(',')','[',']',' ','"', '%','<','>'), "_",$emember_field_name);     
	?> 
	    <tr>
	       <th><label for="<?php echo  $emember_field_name_index?>" class=""><?php echo   $emember_field_name?>: </label></th>
	       <td>
	       <?php
	         $field_value = isset($edit_custom_fields[$emember_field_name_index])?$edit_custom_fields[$emember_field_name_index]:"";
	         $field_value = isset($_POST['emember_custom'][$emember_field_name_index])?$_POST['emember_custom'][$emember_field_name_index]: $field_value;
                 $field_value = stripslashes($field_value);
	         switch($custom_fields['emember_field_type'][$i]){
	         	case 'text':	       
	       ?>
	       <input type="text" size="30" id="wp_emember_<?php echo $emember_field_name_index;?>" value="<?php echo $field_value; ?>" name="emember_custom[<?php echo $emember_field_name_index;?>]" size="20"  class="<?php echo in_array($i,$custom_fields['emember_field_requred'])? ' validate[required] ': "";?>" />
	       <?php
	            break;
	            case 'dropdown':	            	
	       ?>	               
	       <select name="emember_custom[<?php echo $emember_field_name_index;?>]" id="wp_emember_<?php echo $emember_field_name_index;?>" >
	       	   <?php
	       	    $options = stripslashes($custom_fields['emember_field_extra'][$i]);
				$options = explode(',',$options);				
				
				foreach($options as $option){
					$option = explode("=>", $option);       	    
	       	   ?>   
	       	   <option <?php echo ($field_value ===$option[0])? "selected='selected'": "";?> value="<?php echo $option[0];?>"><?php echo $option[1];?></option>
	       	   <?php
				} 
	       	   ?>  
	       </select>
	       <?php	            	
	            break; 
	            case 'checkbox':
	       ?>
	       <input <?php echo $field_value? "checked='checked'": "";?> type="checkbox" value="checked" id="wp_emember_<?php echo $emember_field_name_index;?>" name="emember_custom[<?php echo $emember_field_name_index;?>]" class="<?php echo in_array($i,$custom_fields['emember_field_requred'])? ' validate[required] ': "";?>" />
	       <?php	            	
	            break; 
	            case 'textarea':
	       ?>
	       <textarea name="emember_custom[<?php echo $emember_field_name_index;?>]" id="wp_emember_<?php echo $emember_field_name_index;?>" class="<?php echo in_array($i, $custom_fields['emember_field_requred'])? 'validate[required] ': "";?>" ><?php echo $field_value; ?></textarea>
	       <?php	            	
	            break; 	            	            
	       ?>
	       <?php 	            	            
	         } 
	       ?>
	       </td>
	    </tr>
		
	<?php
	} 
	endif;
	?>
</table>
<p class="submit">
   <input type="submit" name="Submit" value="<?php _e('Save Member Info', 'wp_eMember'); ?>" /> &nbsp; <?php if ($_GET['editrecord']!='') { ?>
   <input type="button" secret="<?php echo $_GET['editrecord']; ?>" name="deleterecord" id="deleterecord" value="<?php _e('Delete Member', 'wp_eMember'); ?>" /><?php } ?>
</p>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function(){
    var upload_button = jQuery("#upload_button");
    interval = ""; 
    jQuery('#membership_level').change(function(){
        jQuery('.eMembership_level').removeAttr('disabled');
        jQuery('#membership_level_'+jQuery(this).val()).attr('disabled', 'disabled').attr('checked','checked');
    }).change();
    <?php if($_GET['editrecord']!='') 
    {
    ?>
    <?php
    global $emember_config; 
    if($emember_config->getValue('eMember_profile_thumbnail')):
    ?>
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
    if(upload_button.length){          	    
        new AjaxUpload(upload_button, 
    	    {
	    		action:  "<?php echo admin_url('admin-ajax.php');?>?event=emember_upload_ajax&action=emember_upload_ajax",
				name: "profile_image",
                data :{image_id: '<?php echo $_GET['editrecord'];?>'},
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
                    } 
                    else {					
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
                        jQuery('#remove_button').attr('href','<?php echo addslashes($upload_dir['basedir']) . '/emember/';?>'+response.file);		    
                        jQuery("#emem_profile_image").attr("src",   "<?php echo $upload_dir['baseurl']; ?>"+ "/emember/" +response.file +"?" + (new Date()).getTime());
                        jQuery("#error_msg").css("color","").text("Uploaded");
                    }	
                    else{
                        jQuery("#error_msg").css("color","").text("Error Occurred.Check file size.");
                    }						
                }
            }); 
    } 
    <?php endif;?> 	
    <?php }?>
   jQuery('#deleterecord').click(function(){
      var u = 'admin.php?page=wp_eMember_manage&members_action=delete&deleterecord='+
              jQuery(this).attr('secret')+'&confirm=1';
      top.document.location = u;
      return false;
   });
   jQuery('#deleterecord').confirm({timeout:5000});
   jQuery("#subscription_starts").dateinput({'format':'yyyy-mm-dd',selectors: true,yearRange:[-100,100]});
   jQuery("#member_since").dateinput({'format':'yyyy-mm-dd',selectors: true,yearRange:[-100,100]});
});
/*]]>*/
</script>