<?php
function emember_is_ajax(){
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH'])&&(strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=='xmlhttprequest'));
}
function emember_ajax_login(){    
    emember_login(false);
    global $emember_auth;
    $emember_auth = Emember_Auth::getInstance();
    $emember_config = Emember_Config::getInstance();    
    $msg = ($emember_auth->getCode()!=1)?  $emember_auth->getMsg(): '';
    echo json_encode(array('status'=>$emember_auth->isLoggedIn(), 'msg'=>$msg));
    exit(0);
}
function wp_emem_openid_login(){
        global $wpdb;
        global $emember_auth;
        $emember_auth = Emember_Auth::getInstance();
        $emember_config = Emember_Config::getInstance();        
        $query = "SELECT emember_id FROM ". WP_EMEMBER_OPENID_TABLE . " WHERE openuid= " . 
                 $_REQUEST['uid'] . ' AND type =\'' . $_REQUEST['type'] . '\'';
        $result = $wpdb->get_row($query);
        if($emember_auth->isLoggedIn()){
        	if($emember_auth->userInfo->member_id == $result->emember_id){
        		echo json_encode(array('status'=>2));    
        	}
        	else{
        		$emember_auth->logout();
        		$emember_auth->login(array('member_id'=>$result->emember_id));
        		echo json_encode(array('status'=>3));
        	}
        }
        else{        
            $emember_auth->login(array('member_id'=>$result->emember_id));
            echo json_encode(array('status'=>1));
         }

	exit(0);
}
function wp_emem_openid_logout(){
        global $emember_auth;
        $emember_auth = Emember_Auth::getInstance();
        $emember_config = Emember_Config::getInstance();        
        if($emember_auth->isLoggedIn()){
           $emember_auth->logout();
           echo json_encode(array('status'=>1));
        }
        else{
           echo json_encode(array('status'=>2));        
        }                 
	exit(0);
}
function wp_emem_delete_image(){	
	@unlink($_GET['path']);
	echo json_encode(array('status'=>"done",'payload'=>'deleted.'));
	exit(0);
}
function wp_emem_get_post_preview(){
    $post_content = get_post($_POST['id'],OBJECT);
    echo '<h2>' . $post_content->post_title . '</h2>';
    echo $post_content->post_content;
	exit(0);
}
function wp_emem_upload_file(){
     if(($_FILES["profile_image"]["size"] / 1024)>2048){
		echo json_encode(array('status'=>0));
		exit(0);
    }
    $upload_dir  = wp_upload_dir();
    $upload_path = $upload_dir['basedir'];
    $upload_url  = $upload_dir['baseurl'];
    $upload_path .= '/emember/';
    $upload_url  .= '/emember/';
    global $emember_auth;
    $emember_auth = Emember_Auth::getInstance();
    $emember_config = Emember_Config::getInstance();    
    $upload_name  = $_POST['image_id'];
    $old_exts = array('gif'=>'gif','jpg'=>'jpg','png'=>'png');    
    switch($_FILES['profile_image']['type']){
		case ("image/gif" ):
			$upload_name .= '.gif';
            unset($old_exts['gif']);
			break;
		case ("image/jpg" ):
			$upload_name .= '.jpg';
            unset($old_exts['jpg']);
			break;
		case ("image/jpeg"):
			$upload_name .= '.jpg';
            unset($old_exts['jpg']);
			break;
		case ("image/pjpeg"):
			$upload_name .= '.jpg';
            unset($old_exts['jpg']);
			break;			
		case ("image/png" ):
			$upload_name .= '.png';
            unset($old_exts['png']);
			break;
    }
    if(!file_exists($upload_path) || !is_dir($upload_path)) mkdir($upload_path);       
    if(move_uploaded_file($_FILES["profile_image"]["tmp_name"],$upload_path . $upload_name)){
        foreach($old_exts as $key=>$ext)@unlink($upload_path .$_POST['image_id'] . '.' . $ext);
    	echo json_encode(array('file'=>$upload_name, 'status'=>1));
    }
    else
        echo json_encode(array('status'=>0));
    exit(0);
}
function wp_emem_user_count_ajax(){
   global $wpdb;
   $member_table = WP_EMEMBER_MEMBERS_TABLE_NAME;
   $condition = "";
   if(!empty($_GET['u'])&&!empty($_GET['e']))
      $condition = 'user_name LIKE \'' . $_GET['u'] . '%\' ' . $_GET['o'] . ' email=\'' . $_GET['e'] . '%\'';
   else if(!empty($_GET['u']))
   	  $condition = 'user_name LIKE \'' . $_GET['u'] . '%\'';
   else if(!empty($_GET['e']))
      $condition = 'email LIKE \'' . $_GET['e'] . '%\'';
   if(empty($condition))
      $q = "SELECT COUNT(*) as count FROM " . $member_table . ' ORDER BY member_id';
   else
      $q = "SELECT COUNT(*) as count FROM " . $member_table . " WHERE $condition ORDER BY member_id";

   $emember_user_count = $wpdb->get_row($q);
   echo json_encode($emember_user_count);
   exit(0);
}
function wp_emem_wp_user_count_ajax(){
   global $wpdb;
   $condition = "";
   if(!empty($_GET['u'])&&!empty($_GET['e']))
      $condition = 'user_login LIKE \'' . $_GET['u'] . '%\' ' . $_GET['o'] . ' user_email=\'' . $_GET['e'] . '%\'';
   else if(!empty($_GET['u']))
   	  $condition = 'user_login LIKE \'' . $_GET['u'] . '%\'';
   else if(!empty($_GET['e']))
      $condition = 'user_email LIKE \'' . $_GET['e'] . '%\'';
   if(empty($condition))
      $q = "SELECT COUNT(*) as count FROM " . $wpdb->users . ' ORDER BY ID';
   else
      $q = "SELECT COUNT(*) as count FROM " . $wpdb->users . " WHERE $condition ORDER BY ID";

   $emember_user_count = $wpdb->get_row($q);
   echo json_encode($emember_user_count);    
    exit(0);
}
function wp_emem_user_list_ajax(){
   $member_table = WP_EMEMBER_MEMBERS_TABLE_NAME;
   $membership_table = WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE;
   $table_name = " $member_table LEFT JOIN $membership_table ON ".
                 " ($member_table.membership_level = $membership_table.id)";    
    global $wpdb;
   $condition = "";
   if(!empty($_GET['u'])&&!empty($_GET['e']))
      $condition = 'user_name LIKE \'' . $_GET['u'] . '%\' ' . $_GET['o'] . ' email=\'' . $_GET['e'] . '%\'';
   else if(!empty($_GET['u']))
   	  $condition = 'user_name LIKE \'' . $_GET['u'] . '%\'';
   else if(!empty($_GET['e']))
      $condition = 'email LIKE \'' . $_GET['e'] . '%\'';
    
	$orderby = $_GET['orderby'];
	$order = $_GET['order'];
	if(empty($orderby)){
		$orderby = "member_id";
		$order = "asc";
	}
		   
    if(empty($condition))
      $q = "SELECT member_id, user_name, membership_level,first_name,last_name,email,alias,last_accessed_from_ip,
            subscription_starts,account_state FROM $table_name ORDER BY $orderby $order LIMIT ";
    else 
      $q = "SELECT member_id, user_name, membership_level,first_name,last_name,email,alias,last_accessed_from_ip,
           subscription_starts,account_state FROM $table_name WHERE $condition ORDER BY $orderby $order LIMIT ";
    $wp_users = $wpdb->get_results( $q. $_GET['start'] . ',' . $_GET['limit']);
    //echo json_encode($wp_users);
    ?><tbody><?php
    if(count($wp_users)>0):            
        $count = 0;
        foreach($wp_users as $wp_user):
        ?>
        <tr valign="top" <?php echo ($count%2)?"class='alternate'":"";?>>
            <td><input type="checkbox" name="deleterecord[<?php echo $wp_user->member_id;?>]" class="emember_blk_op" value="<?php echo $wp_user->member_id;?>"></td>
            <td><?php echo $wp_user->member_id;?></td>
            <td><a href="#" class="emember_user_name" ><?php echo $wp_user->user_name;?></a></td>
            <td><?php echo $wp_user->first_name;?></td>
            <td><?php echo $wp_user->last_name;?></td>
            <td><?php echo $wp_user->email;?></td>
            <td><a href="#" class="emember_membership_level"><?php echo $wp_user->alias;?></a></td>
            <td><input type="text" size="12" readonly="readonly" value="<?php echo $wp_user->last_accessed_from_ip;?>" onfocus="this.select();" onclick="this.select();"></td>
            <td><?php echo $wp_user->subscription_starts;?></td>
            <td><?php echo $wp_user->account_state;?></td>
            <td><a href="admin.php?page=wp_eMember_manage&amp;members_action=edit_ip_lock&amp;editrecord=<?php echo $wp_user->member_id;?>" class="edit_ip_lock">Unlock</a></td>
            <td><a href="admin.php?page=wp_eMember_manage&amp;members_action=add_edit&amp;editrecord=<?php echo $wp_user->member_id;?>" class="edit">Edit</a></td>
            <td><a href="<?php echo $wp_user->member_id;?>" class="del">Delete</a></td>
        </tr>
        <?php
        $count++;
        endforeach;
    else:
    ?>
    <tr valign="top">
        <td colspan="13">No data found.</td>
    </tr>
    <?php    
    endif;
    ?>
    </tbody>
    <script type="text/javascript">
       $j('#member_list').find('a.del').each(function(e){
           $j(this).click(function(){
              var u = 'admin.php?page=wp_eMember_manage&members_action=delete&deleterecord='+$j(this).attr('href');
              top.document.location = u;
              return false;
           });
           $j(this).confirm({msg:"",timeout:5000});
        });
       //$j('.emember_user_name').tooltip();        
    </script>
    <?php
    exit(0);        
}
function wp_emem_public_user_list_ajax(){
    $member_table = WP_EMEMBER_MEMBERS_TABLE_NAME;
    $membership_table = WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE;
    $table_name = " $member_table LEFT JOIN $membership_table ON ".
                 " ($member_table.membership_level = $membership_table.id)";
    $no_email  = isset($_SESSION['emember_no_email_shortcode'])?$_SESSION['emember_no_email_shortcode']: ""; 
    global $wpdb;
    $condition = "";
    if(!empty($_GET['u'])&&!empty($_GET['e']))
       $condition = ' user_name LIKE \'' . $_GET['u'] . '%\' ' . $_GET['o'] . ' email=\'' . $_GET['e'] . '%\'';
    else if(!empty($_GET['u']))
   	   $condition = ' user_name LIKE \'' . $_GET['u'] . '%\'';
    else if(!empty($_GET['e']))
       $condition = ' email LIKE \'' . $_GET['e'] . '%\'';
    $order = ' ORDER BY member_id ';
    if(isset($_GET['ord'])&&isset($_GET['sort']))$order = ' ORDER BY ' . $_GET['sort'] . ' ' . $_GET['ord']; 
    
    $q  = "SELECT member_id, user_name, first_name,last_name";
    if(!$no_email)$q .= ",email ";
    
    $q .= " FROM $table_name";
    if(empty($condition))
      $q .= " $order LIMIT ";
    else 
      $q .= " WHERE $condition $order LIMIT ";

   $wp_users = $wpdb->get_results( $q. $_GET['start'] . ',' . $_GET['limit']);
   //wp_emember_printd($wpdb);
   //echo json_encode($wp_users);
   if(count($wp_users)):              
       $count = 0;
       ?><tbody><?php
       foreach($wp_users as $wp_user):
       ?>
        <tr valign="top" <?php echo ($count%2)?"class='alternate'":"";?>>
            <td><a uid="<?php echo $wp_user->member_id;?>" href="#" class="emember_post_preview"><?php echo $wp_user->user_name;?></a></td>
            <td><?php echo $wp_user->first_name;?></td>
            <td><?php echo $wp_user->last_name;?></td>
            <?php if(!$no_email):?>  <td><?php echo $wp_user->email;?></td><?php endif;?>
        </tr>    
       <?php
       $count++;
       endforeach;       
       ?>
        </tbody>
        <script type="text/javascript">
            jQuery(".emember_post_preview").overlay({
                mask: '#E6E6E6',
                effect: 'apple',
                target:jQuery('#emember_post_preview_overlay'),
                onBeforeLoad: function() {
                   var wrap = this.getOverlay().find(".emember_contentWrap");
                   wrap.html('Loading ...');
                   var params = {'event':'emember_public_user_profile_ajax',
                                 'action':'emember_public_user_profile_ajax',
                                 'id':this.getTrigger().attr("uid")
                             };
                   jQuery.get('<?php echo admin_url( "admin-ajax.php" ); ?>' ,params,function(data){wrap.html(data.content); },'json');
                }
            });
        </script>
       <?php
   else:
   ?>
    <tbody><tr><td>No data found.</td></tr></tbody>    
   <?php
   endif;
   exit(0);        
}
function wp_emem_wp_user_list_ajax(){
    global $wpdb;
   $condition = "";
   if(!empty($_GET['u'])&&!empty($_GET['e']))
      $condition = 'user_login LIKE \'' . $_GET['u'] . '%\' ' . $_GET['o'] . ' user_email=\'' . $_GET['e'] . '%\'';
   else if(!empty($_GET['u']))
   	  $condition = 'user_login LIKE \'' . $_GET['u'] . '%\'';
   else if(!empty($_GET['e']))
      $condition = 'user_email LIKE \'' . $_GET['e'] . '%\'';
    
   if($condition)
       $query = "SELECT ID, user_login, user_email FROM $wpdb->users WHERE " .
                $condition . "ORDER BY ID LIMIT " . $_GET['start'] . ',' . $_GET['limit'];       
   else
       $query = "SELECT ID, user_login, user_email FROM $wpdb->users ORDER BY ID LIMIT " . 
                   $_GET['start'] . ',' . $_GET['limit'];
   $wp_users = $wpdb->get_results($query);
   //echo json_encode($wp_users);
   $all_levels = dbAccess::findAll(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, ' id != 1 ', ' id DESC ');
   if(count($wp_users)>0):
   ?>
    <tbody>
   <?php
       $count = 0;
       foreach($wp_users as $wp_user):
   ?>
	<tr valign="top" <?php echo ($count%2)?"class='alternate'":"";?>>
		<td class="check-column" scope="row"><input type="checkbox" value="<?php echo $wp_user->ID;?>" name="selected_wp_users[<?php echo $count ?>][ID]"></td>
		<td><?php echo $wp_user->user_login; ?></td>
		<td><?php echo $wp_user->user_email; ?></td>
		<td><select name="selected_wp_users[<?php echo $count ?>][membership_level]">
                   <?php
                   foreach($all_levels as $l):
                   ?>					
                   <option value="<?php echo $l->id; ?>"><?php echo $l->alias; ?></option>
				   <?php endforeach;?>
			</select>
		</td>
		<td>
			<input type="date" value="<?php echo date('Y-m-d');?>" name="selected_wp_users[<?php echo $count ?>][subscription_starts]">	
		</td>
		<td><select name="selected_wp_users[<?php echo $count ?>][account_state]">
				<option value="active">Active</option>
				<option value="inactive">Inactive</option>
				<option value="blocked">Blocked</option>
			</select>
		</td>
		<td><input type="checkbox" value="1" name="selected_wp_users[<?php echo $count ?>][preserve_wp_role]" checked="checked"></td>
	</tr>
   <?php
       $count++;
       endforeach;
   else:
   ?>
   	<tr valign="top">
        <td colspan="7">No data found.</td>
    </tr>
   <?php
   endif;
   ?>    
    </tbody>
    <script type="text/javascript">
       jQuery('#wp_member_list').find('input[type^="date"]').each(function(e){
          jQuery(this).dateinput({'format':'yyyy-mm-dd',selectors: true,yearRange:[-100,100]});
        });        
    </script>    
    <?php
   exit(0);    
}
function wp_emem_public_user_profile_ajax(){
	global $emember_config;
    $emember_config = Emember_Config::getInstance();    
	$p  = $emember_config->getValue('eMember_enable_public_profile');	
	if(!$p) {
		echo json_encode(array('content'=>'Public profile Listing is disabled', 'status'=>0));
		exit(0);
	}
	$d = WP_EMEMBER_URL.'/images/default_image.gif';
	$no_email  = isset($_SESSION['emember_no_email_shortcode'])?$_SESSION['emember_no_email_shortcode']: "";
	global $wpdb;
    $resultset  = dbAccess::find(WP_EMEMBER_MEMBERS_TABLE_NAME, ' member_id=' . $wpdb->escape($_GET['id']));
	$upload_dir  = wp_upload_dir();
    $upload_url  = $upload_dir['baseurl'];
    $upload_path = $upload_dir['basedir'];
    $upload_url  .= '/emember/';
    $upload_path .= '/emember/';
    $upload_url  .= $resultset->member_id;
    $upload_path .= $resultset->member_id;
    if(file_exists($upload_path . '.jpg'))
    	$image_url = $upload_url . '.jpg';  
    else if(file_exists($upload_path . '.jpeg'))   
    	$image_url = $upload_url . '.jpeg';   
    else if(file_exists($upload_path . '.gif'))
    	$image_url = $upload_url . '.gif';
    else if(file_exists($upload_path . '.png'))
    	$image_url = $upload_url . '.png';
    else{
        $use_gravatar = $emember_config->getValue('eMember_use_gravatar');
        if($use_gravatar)
            $image_url = WP_EMEMBER_GRAVATAR_URL. "/" . md5(strtolower($resultset->email)) . "?d=" . urlencode($d) . "&s=" . 96;
        else
            $image_url = WP_EMEMBER_URL . '/images/default_image.gif';
    }
    
    ob_start();
    ?>
    <h2 class="emember_profile_head"><?php echo EMEMBER_USER_PROFILE; ?></h2>
    <table align="center" class="emember_profile">
    	<tbody align="center">
            <?php if($emember_config->getValue('eMember_profile_thumbnail')):?>
    		<tr>
    			<td colspan="2" align="center">
    				<img alt="" width="100px" height="100px" src="<?php echo $image_url;?>"/>    			
    			</td>
    		</tr>
            <?php endif; ?>
    		<tr class="emember_profile_cell">
    			<td colspan="2" align="center" class="emember_profile_user_name">
    			  <?php echo $resultset->user_name; ?>
    			</td>    		
    		</tr>
            <?php if($emember_config->getValue('eMember_edit_firstname')):?>            
    		<tr class="emember_profile_cell alternate">
    			<td>
    				<label><?php echo EMEMBER_FIRST_NAME;?>:</label>    			
    			</td>
    			<td>
    			  <?php echo $resultset->first_name; ?>
    			</td>
    		</tr>
            <?php endif;?>
            <?php if($emember_config->getValue('eMember_edit_lastname')):?>            
    		<tr class="emember_profile_cell">
    			<td>
    				<label><?php echo EMEMBER_LAST_NAME ?>:</label>    			
    			</td>
    			<td>
    			  <?php echo $resultset->last_name; ?>
    			</td>
    		</tr>
            <?php endif;?>
            <?php if($emember_config->getValue('eMember_edit_email')):?>            
            <?php if(!$no_email):?>
    		<tr class="emember_profile_cell alternate">
    			<td>
    				<label><?php echo EMEMBER_EMAIL; ?>:</label>    			
    			</td>
    			<td>
    			  <?php echo $resultset->email; ?>
    			</td>
    		</tr>
            <?php endif;?>
            <?php endif;?>
            <?php if($emember_config->getValue('eMember_edit_phone')):?>
    		<tr class="emember_profile_cell">
    			<td>
    				<label><?php echo EMEMBER_PHONE;?>:</label>    			
    			</td>
    			<td>
    			  <?php echo $resultset->phone; ?>
    			</td>
    		</tr>            
            <?php endif;?>
            <?php if($emember_config->getValue('eMember_edit_company')):?>                        
    		<tr class="emember_profile_cell alternate">
    			<td>
    				<label><?php echo EMEMBER_COMPANY;?>:</label>    			
    			</td>
    			<td>
    			  <?php echo $resultset->company_name; ?>
    			</td>
    		</tr>
            <?php endif;?>
            <?php if($emember_config->getValue('eMember_edit_street')):?>                
    		<tr class="emember_profile_cell">
    			<td>
    				<label><?php echo EMEMBER_ADDRESS_STREET;?>:</label>    			
    			</td>
    			<td>
    			  <?php echo $resultset->address_street; ?>
    			</td>
    		</tr>
            <?php endif;?>
            <?php if($emember_config->getValue('eMember_edit_city')):?>                
    		<tr class="emember_profile_cell alternate">
    			<td>
    				<label><?php echo EMEMBER_ADDRESS_CITY;?>:</label>    			
    			</td>
    			<td>
    			  <?php echo $resultset->address_city; ?>
    			</td>
    		</tr>    		
            <?php endif;?>
            <?php if($emember_config->getValue('eMember_edit_state')):?>                
    		<tr class="emember_profile_cell">
    			<td>
    				<label><?php echo EMEMBER_ADDRESS_STATE;?>:</label>    			
    			</td>
    			<td>
    			  <?php echo $resultset->address_state; ?>
    			</td>
    		</tr> 
            <?php endif;?>
            <?php if($emember_config->getValue('eMember_edit_zipcode')):?>                
    		<tr class="emember_profile_cell alternate">
    			<td>
    				<label><?php echo EMEMBER_ADDRESS_ZIP;?>:</label>    			
    			</td>
    			<td>
    			  <?php echo $resultset->address_zipcode; ?>
    			</td>
    		</tr> 
            <?php endif;?>
            <?php if($emember_config->getValue('eMember_edit_country')):?>            
    		<tr class="emember_profile_cell">
    			<td>
    				<label><?php echo EMEMBER_ADDRESS_COUNTRY?>:</label>    			
    			</td>
    			<td>
    			  <?php echo $resultset->country; ?>
    			</td>
    		</tr>
            <?php endif;?>
            <?php if($emember_config->getValue('eMember_edit_gender')):?>
            <tr class="emember_profile_cell alternate">
                <td>
                    <label><?php echo EMEMBER_GENDER;?>:</label>
                </td>
                <td>
                    <?php echo $resultset->gender;?>
                </td>
            </tr>
            <?php endif; ?>
    <?php
    if($emember_config->getValue('eMember_custom_field')):
    $edit_custom_fields = dbAccess::find(WP_EMEMBER_MEMBERS_META_TABLE, ' user_id=' . $wpdb->escape($_GET['id']) . ' AND meta_key=\'custom_field\'');
    $edit_custom_fields = unserialize($edit_custom_fields->meta_value);    
    $custom_fields = get_option('emember_custom_field_type');   
    $inversed_order = array();
        $revised_order = array();
        $num_field = count($custom_fields['emember_field_name']);
        for ($i = 1;$i<=$num_field;$i++) {
        $inversed_order[$i] = $i;
        }

    if(is_array($custom_fields['emember_field_order'] )){
        foreach($custom_fields['emember_field_order'] as $key=>$value){
            $inversed_order[$value] = $key;
        }
        $order_values = array_values($custom_fields['emember_field_order']);
        sort($order_values);
    
        foreach ($order_values as $key=>$value){
            $revised_order[] = $inversed_order[$value];
        }
    }
    else{
        $num_field = count($custom_fields['emember_field_name']);
        for ($i = 0;$i<=$num_field;$i++) {
        $revised_order[] = $i;
        }    
    }

    for($i=0; isset($custom_fields['emember_field_name'][$revised_order[$i]]); $i++){
       $emember_field_name = stripslashes($custom_fields['emember_field_name'][$revised_order[$i]]);             
       $emember_field_name_index = str_replace(array('\\','\'','(',')','[',']',' ', '"','%','<','>'), "_",$emember_field_name);             
    ?> 
        <tr <?php echo (($i%2) == 0 )? 'class="emember_profile_cell"': 'class="emember_profile_cell alternate"'?>>
           <td><label><?php echo  $emember_field_name;?>: </label></td>
           <td>
           <?php             
             $field_value = isset($edit_custom_fields[$emember_field_name_index])?$edit_custom_fields[$emember_field_name_index]:"";
             $field_value = isset($_POST['emember_custom'][$emember_field_name_index])?$_POST['emember_custom'][$emember_field_name_index]: $field_value;   
             $field_value = stripslashes($field_value);                                  
             switch($custom_fields['emember_field_type'][$revised_order[$i]]){
                case 'text': 
                echo $field_value; 
                break;
                case 'dropdown':
                $options = stripslashes($custom_fields['emember_field_extra'][$revised_order[$i]]);
                $options = explode(',',$options);               
                foreach($options as $option){
                    $option = explode("=>", $option);               
                    if(($field_value ===$option[0])) echo $option[1];
                }                             
                break; 
                case 'checkbox':
           ?>
           <?php echo $field_value? "&radic;": "&Chi;";?> 
           <?php                    
                break; 
                case 'textarea':
                echo $field_value;  
                break;                                                            
             } 
           ?>
           </td>
        </tr>       
    <?php
    }
    endif; 
    ?>    		    			
    	</tbody>
    </table>    
    <?php
    $content = ob_get_contents();
    ob_end_clean();
    echo json_encode(array('content'=>$content, 'status'=>1)); 
    exit(0);	
}
function wp_emem_add_bookmark(){
    if(emember_is_ajax()){
        $emember_auth = Emember_Auth::getInstance();
        $emember_config = Emember_Config::getInstance();        
        global $emember_auth;
        global $wpdb;
        $extr = $emember_auth->getUserInfo('extra_info');
        $member_id = $emember_auth->getUserInfo('member_id');
        $extr = unserialize($extr);
        $bookmarks = isset($extr['bookmarks'])?$extr['bookmarks'] : array();
        array_push($bookmarks, $_GET['id']);
        $bookmarks = array_unique($bookmarks);
        $extr['bookmarks'] = $bookmarks;
        $fields['extra_info'] = serialize($extr); 
        dbAccess::update(WP_EMEMBER_MEMBERS_TABLE_NAME,'member_id = ' . $member_id, $fields);
        $a1 = '<span title="Bookmarked"  class="count">
              <span class="c"><b>&radic;</b></span><br/>
              <span class="t">'.EMEMBER_FAVORITE.'</span></span>
              <span title="Bookmarked"class="emember">'.EMEMBER_ADDED.'</span>';        
        echo json_encode(array('status'=>1,'msg'=>$a1));
        exit(0);
    }
}
function item_list_ajax(){
    global $wpdb;
    if(emember_is_ajax()){
        switch($_GET['type']){
            case 'pages':    
                $args = array(
                    'child_of' => 0,
                    'sort_order' => 'ASC',
                    'sort_column' => 'post_title',
                    'hierarchical' => 0,
                    'parent' => -1,
                    'number' => $_GET['limit'],
                    'offset' => $_GET['start'] );                   
                $all_pages      = get_pages($args);                     
//                $all_pages      =  $wpdb->last_result;               
                $filtered_pages = array();
                foreach($all_pages as $page){
                      $page_summary = array();
                      $user_info    = get_userdata($page->post_author);
             
                      $page_summary['ID']     = $page->ID;
                      $page_summary['date']   = $page->post_date;
                      $page_summary['title']  = $page->post_title;
                      $page_summary['author'] = $user_info->user_nicename;
                      $page_summary['status'] = $page->post_status;
                      $filtered_pages[]       = $page_summary;
                }
               echo json_encode($filtered_pages);
                
                break;
            case 'posts':
            	$sql  = "SELECT ID,post_date,post_title,post_author, post_type, post_status FROM $wpdb->posts ";
            	$sql .= " WHERE post_type != 'page' AND post_status = 'publish' LIMIT " . $_GET['start'] . " , " . $_GET['limit'];
				$all_posts = $wpdb->get_results($sql);
				            	
//                $all_posts      = get_posts(array('numberposts'=>$_GET['limit'],
//                                                  'offset'=>$_GET['start'],
//                                                  'post_type'=>'any'));
                $filtered_posts = array();
                foreach($all_posts as $post){
                	//if($post->post_type=='page')continue;
                    $post_summary = array();
                    $user_info    = get_userdata($post->post_author);
                    $categories   = get_the_category($post->ID);
                    $cat          = array();
                    foreach($categories  as $category)
                       $cat[] = $category->category_nicename;
             
                    $post_summary['ID']         = $post->ID;
                    $post_summary['date']       = $post->post_date;
                    $post_summary['title']      = $post->post_title;
                    $post_summary['author']     = $user_info->user_nicename;
                    $post_summary['categories'] = implode(' ', $cat);
                    $post_summary['type']       = $post->post_type;                    
                    $post_summary['status']     = $post->post_status;
                    $filtered_posts[]           = $post_summary;
                }
                echo json_encode($filtered_posts);                       
                break;
            case 'comments':
                $all_comments      = get_comments(array('number'=>$_GET['limit'],'offset'=>$_GET['start'],'status'=>'approve'));
                $filtered_comments = array();
                foreach($all_comments as $comment){
                      $comment_summary            = array();
                      $comment_summary['ID']      = $comment->comment_ID;
                      $comment_summary['date']    = $comment->comment_date;
                      $comment_summary['author']  = $comment->comment_author;
                      $comment_summary['content'] = $comment->comment_content;
                      $filtered_comments[]        = $comment_summary;
                }               
                echo json_encode($filtered_comments);
                break;
            case 'categories':
                $all_categories = array();
                $all_cat_ids    = get_all_category_ids();
                for($i=$_GET['start'];$i<($_GET['start']+$_GET['limit'])&&!empty($all_cat_ids[$i]);$i++)
                    $all_categories[] = get_category($all_cat_ids[$i]);
                
                foreach($all_categories as $category){
                      $category_summary                = array();
                      $category_summary['ID']          = $category->term_id;
                      $category_summary['name']        = $category->name;
                      $category_summary['description'] = $category->description;
                      $category_summary['count']       = $category->count;
                      $filtered_categories[]           = $category_summary;
                }
                echo json_encode($filtered_categories);               
                break;    
        }
    }
    exit(0);
}
function wp_emem_send_mail(){
    if(emember_is_ajax())echo  json_encode(wp_emember_generate_and_mail_password($_GET['email']));
    exit(0);
}
function wp_emem_check_level_name(){
    if(emember_is_ajax()){        
        global $wpdb;
        $alias = $wpdb->escape(trim($_GET['alias']));
        $user = dbAccess::find(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, ' alias=\'' .$wpdb->escape($alias) .'\'');
        if($user) echo json_encode(array('status_code'=>false,'msg'=>'&chi;&nbsp;' . EMEMBER_ALREADY_TAKEN));
        else echo json_encode(array('status_code'=>true,'msg'=>'&radic;&nbsp;'.EMEMBER_STILL_AVAIL));
    }
    exit(0);
}

function wp_emem_check_user_name(){
    if(emember_is_ajax()){
        if (username_exists($_GET['fieldValue'])){
            //echo json_encode(array('status_code'=>false,'msg'=>'&chi;&nbsp;'.EMEMBER_ALREADY_TAKEN));
            echo '[ "' . $_GET['fieldId'] . '",false, "&chi;&nbsp;'.EMEMBER_ALREADY_TAKEN.'"]';
        }
        else{
            global $wpdb;
            $user_name = $wpdb->escape(trim($_GET['fieldValue']));
            $user = dbAccess::find(WP_EMEMBER_MEMBERS_TABLE_NAME, ' user_name=\'' .$wpdb->escape($user_name) .'\'');
            if($user) {
            	//
            	//echo json_encode(array('status_code'=>false,'msg'=>'&chi;&nbsp;' .EMEMBER_ALREADY_TAKEN));
            	echo '[ "' . $_GET['fieldId'] . '",false, "&chi;&nbsp;'.EMEMBER_ALREADY_TAKEN.'"]';
            }
            else{ 
            	//echo json_encode(array('status_code'=>true,'msg'=>'&radic;&nbsp;'.EMEMBER_STILL_AVAIL));
            	echo '[ "' . $_GET['fieldId'] . '",true, "&radic;&nbsp;'.EMEMBER_STILL_AVAIL.'"]';
            }
        }
    }
    exit(0);
}

function access_list_ajax(){
    if(emember_is_ajax()){
        global $wpdb;
        $levelId = $_GET['level_type'];
        $level = dbAccess::find(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE," id = '" . $wpdb->escape($levelId) . " ' ");
        switch($_GET['level_content']){
           case 'Comments':
              $content = unserialize($level->comment_list);
              $num_comm  = get_comment_count();            
              $content = is_bool($content)? array(): $content;
              echo json_encode(array('comment_list'=>$content,'count'=>$num_comm['approved']));
              break;
           case 'Posts':
              $content = unserialize($level->post_list);
              $bookmark = unserialize($level->disable_bookmark_list);
              #$num_posts = wp_count_posts( 'post' );             
              #$num_posts->publish
              $num_posts = $wpdb->get_var("SELECT count(*) from $wpdb->posts WHERE post_status='publish' AND post_type!='page'");
              $content = is_bool($content)? array(): $content;
              $bookmark = empty($bookmark['posts'])? array(): $bookmark['posts'];
              echo json_encode(array('post_list'=>$content,'disable_bookmark'=>$bookmark,'count'=>$num_posts));
              break;
           case 'Pages':
           	  $bookmark = unserialize($level->disable_bookmark_list);
              $content = unserialize($level->page_list);
              $bookmark = empty($bookmark['pages'])? array(): $bookmark['pages'];
              $content = is_bool($content)? array(): $content;
              $num_pages = wp_count_posts( 'page' );
              echo json_encode(array('page_list'=>$content,'disable_bookmark'=>$bookmark,'count'=>$num_pages->publish));
              break;
           case 'Categories':
              $content = unserialize($level->category_list);
              $content = is_bool($content)? array(): $content;
              $num_cats  = wp_count_terms('category');
              echo json_encode(array('category_list'=>$content,'count'=>$num_cats));
              break;
        }
    }
    exit(0);
}
