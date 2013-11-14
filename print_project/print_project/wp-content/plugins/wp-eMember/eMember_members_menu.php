<?php
if(!isset($_SESSION)) 
{
	@session_start();
}	
include_once('eMember_db_access.php');
include_once('eMember_misc_functions.php');
include_once('emember_auth.php');

function build_menu($current){
   ?>
   <ul class="eMemberSubMenu">
      <li <?php echo ($current==1) ? 'class="current"' : ''; ?> ><a href="admin.php?page=wp_eMember_manage">Manage Members</a></li>
      <li <?php echo ($current==2) ? 'class="current"' : ''; ?> ><a href="admin.php?page=wp_eMember_manage&members_action=add_edit">Add/Edit Member</a></li>
      <li <?php echo ($current==3) ? 'class="current"' : ''; ?> ><a href="admin.php?page=wp_eMember_manage&members_action=manage_list">Member Lists</a></li>
      <li <?php echo ($current==4) ? 'class="current"' : ''; ?> ><a href="admin.php?page=wp_eMember_manage&members_action=add_wp_members">Import WP Users</a></li>
      <li <?php echo ($current==5) ? 'class="current"' : ''; ?> ><a href="admin.php?page=wp_eMember_manage&members_action=manage_blacklist">Manage Blacklist</a></li>
      <li <?php echo ($current==6) ? 'class="current"' : ''; ?> ><a href="admin.php?page=wp_eMember_manage&members_action=manage_upgrade">Auto Upgrade</a></li>
   </ul>
   <?php
}

function wp_eMember_members(){
    echo '<div class="wrap"><h2>WP eMembers - Members v'.WP_EMEMBER_VERSION.'</h2>';
    echo '<div id="poststuff"><div id="post-body">';
    echo check_php_version();
    echo eMember_admin_submenu_css();
   switch ($_GET['members_action']){
       case 'add_edit':
              build_menu(2);
           wp_eMember_add_memebers();
           break;
       case 'manage_list':
              build_menu(3);
           wp_eMember_manage_memebers_lists();
           break;
       case 'delete':
           build_menu(1);
           wp_eMember_delete_member();
           break;
       case 'edit_ip_lock':
              build_menu(1);
              wp_eMember_edit_ip_lock();
              break;
       case 'add_wp_members':
              build_menu(4);
           wp_eMember_add_wp_members();
           break;
       case 'manage_blacklist':
              build_menu(5);
           wp_eMember_manage_blackList();
           break;
       case 'manage_upgrade':
              build_menu(6);
           wp_eMember_manage_upgrade();
           break;           
       case 'manage':
       default:
              build_menu(1);
           wp_eMember_manage_members();
           break;
   }
    echo '</div></div>';
    echo '</div>';
}
function wp_eMember_manage_upgrade(){
	echo '<div class="eMember_yellow_box"><p><strong>Please read the <a href="http://www.tipsandtricks-hq.com/wordpress-membership/?p=194" target="_blank">Auto Upgrade Setup Documentation</a> before attempting to setup auto upgrade for you members.</strong></p></div>';
	if(isset($_POST['submit'])){
		foreach($_POST['data'] as $key=>$data){
			if($key==1) continue;
			$fields = array();
			$fields['options'] = serialize($data);
			dbAccess::update(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE,'id = ' . $key, $fields);
		}
		echo wp_eMember_message('Updated!');
	}
    global $emember_config;
    $emember_config = Emember_Config::getInstance();    
   $levels = dbAccess::findAll(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, ' id != 1 ', ' id DESC ');
   ob_start(); 
    ?>
     <form method="post" id="emember_auto_upgrade">
	    <div id="Pagination" class="pagination"></div>
	    <table id="membership_level_list" class="widefat"><thead><tr>
	    <th scope="col"><?php echo __('Membership Level', 'wp_eMember');?></th>
	    <th scope="col"><?php echo __('Promote to', 'wp_eMember');?></th>
	    <th scope="col"><?php echo __('After #of Days From the Subscription Start Date', 'wp_eMember');?></th>
	    </tr></thead>
	    <tbody>    
	     <?php
	    $count = 0;
	    foreach($levels as $level){   
	    	$em_options = unserialize($level->options);  	
	    	?>
	    	<tr <?php echo ($count%2)? 'class="alternate"': '';?>>
	    		<td>
	    			<?php echo $level->alias;?>
	    		</td>
	    		<td>
	    			<select name="data[<?php echo $level->id;?>][promoted_level_id]">
	    			    <option value="-1">No Auto Promote</option>	    			
	    			    <?php	    			     
	    			     foreach($levels as $l){
	    		       	    ?>	    			    
	    				<option <?php echo ($l->id===$em_options['promoted_level_id'])? 'selected="selected"':'';?> value="<?php echo $l->id;?>">
	    					<?php echo $l->alias;?>
	    				</option>
	    				<?php
	    			     } 
	    				?>
	    			</select>
	    		</td>
	    		<td>
	    			<input name="data[<?php echo $level->id;?>][days_after]" type="text" size="6" value="<?php echo $em_options['days_after'];?>" ></input> Day(s) After the Subscription Start Date
	    		</td>
	    	</tr>
	    	<?php     	
	    	$count++;
	    }
	    ?>    
	    </tbody>
	    </table>
	    <p class="submit">
	    <input type="submit" name="submit" value="Update" class="button-secondary"/>
	    </p>
    </form>
<script type="text/javascript">
    jQuery(document).ready(function($){
        $('#emember_auto_upgrade input[type=text]').focus(function(){
            $(this).css('border','');
        });
        $('#emember_auto_upgrade').submit(function(){
            var ok = true;
            $(this).find('input[type=text]').each(function(){
                var $this = $(this);
                if($this.val() ==""){
                    var $selected = $this.parents('tr:first').find('select').val();
                    if($selected != -1){
                        ok = false;
                        $this.css("border","1px solid red");                        
                    }

                }
            });
            if(!ok) alert('Fields cannot be empty.');
            return ok;
        });
    });
</script>
    <?php 	
    $content = ob_get_contents();
    ob_end_clean();
    echo $content;
}
function wp_eMember_edit_ip_lock(){
    echo '<h2>Manage IP Lock</h2>';
    if(isset($_GET['editrecord'])){
        global $wpdb;
        $query = "SELECT meta_value FROM " . WP_EMEMBER_MEMBERS_META_TABLE .
                 " WHERE user_id = " . $_GET['editrecord'] . " AND meta_key = 'login_count'";
        $login_count = $wpdb->get_row($query);        
        $login_count = unserialize($login_count->meta_value);

        $used_ips = '';
        if(isset($login_count[date('y-m-d')]))
            $used_ips = implode(';',$login_count[date('y-m-d')]);

        if(isset($_POST['submit'])){
            $used_ips = $_POST['locked_ips'];
            if(empty($used_ips))
                $ips = array();
            else
                $ips = explode(';',$_POST['locked_ips']);

            $ips = array(date('y-m-d')=>array_unique($ips));
            if($login_count === false)
                   $query =  "INSERT INTO " . WP_EMEMBER_MEMBERS_META_TABLE . "(user_id,meta_key,meta_value)".
                             "VALUES(".$_GET['editrecord'].", 'login_count', '".serialize($ips)."')";
            else
                $query =  "UPDATE " . WP_EMEMBER_MEMBERS_META_TABLE . " SET meta_value = '" . serialize($ips) . "'".
                          " WHERE user_id= " . $_GET['editrecord'] . " AND meta_key = 'login_count'";
            $wpdb->query($query);
            echo wp_eMember_message('Updated!');
        }

    ?>
    <form method="post">
        <table class="widefat">
          <tr>
            <th>IP Addresses</th>
          </tr>
          <tr>
            <td><p>Following list provides all the IP addresses used by this user to log into this site.you can modify it but make sure that IP addresses are semi-colon separated.</p></td>
          </tr>
          <tr>
            <td><br/><textarea name="locked_ips" rows="15" cols="35"><?php echo $used_ips; ?></textarea> </td>
          </tr>
          <tr >
              <td colspan="2" ><p class="submit"><input type="submit" name="submit" value="Update" /> </p></td>
          </tr>
        </table>
    </form>
    <?php
    }
    else{
        die('<span style="color:red;">Oops!</span>');
    }
}
function wp_eMember_manage_blackList(){
    global $emember_config;
    $emember_config = Emember_Config::getInstance();    
    ?>
    <h2>Registration BlackList</h2>
    <?php
    if(isset($_POST['submit'])){
        $options = array('blacklisted_ips'=>$_POST['blacklisted_ips'],
                         'blacklisted_emails'=>$_POST['blacklisted_emails']
                        );
        $emember_config->setValue('blacklisted_ips', $_POST['blacklisted_ips']);
        $emember_config->setValue('blacklisted_emails',$_POST['blacklisted_emails']);
        $emember_config->saveConfig();
    }

     $blacklisted_ips    = $emember_config->getValue('blacklisted_ips');
     $blacklisted_emails = $emember_config->getValue('blacklisted_emails');
    ?>
    <form method="post">
        <table class="widefat">
          <tr>
            <th>IP Black List</th>
            <th>Email Black List</th>
          </tr>
          <tr>
            <td><p>Following list provides a list (semi-colon separated) of blacklisted IP addresses. You may modify the list as needed.</p></td>
            <td><p>Following list provides a list (semi-colon separated) of blacklisted email addresses. You may modify the list as needed.</p></td>
          </tr>
          <tr>
            <td><br/><textarea name="blacklisted_ips" rows="15" cols="35"><?php echo $blacklisted_ips; ?></textarea> </td>
            <td><br/><textarea name="blacklisted_emails" rows="15" cols="35"><?php echo $blacklisted_emails; ?></textarea> </td>
          </tr>
          <tr >
              <td colspan="2" ><p class="submit"><input type="submit" name="submit" value="Update" /> </p></td>
          </tr>
        </table>
    </form>
    <?php
}
function __wp_eMember_add($row){
   $user_info = get_userdata($row['ID']);
   $user_cap = array_keys($user_info->wp_capabilities);
   $fields = array();
   $fields['user_name'] = $user_info->user_login;
   $fields['first_name'] = $user_info->user_firstname;
   $fields['last_name'] = $user_info->user_lastname;
   $fields['password'] = $user_info->user_pass;
   $fields['member_since'] = date('Y-m-d H:i:s');                
   $fields['membership_level'] = $row['membership_level'];
   //$fields['initial_membership_level'] = $row['membership_level'];
   $fields['account_state'] = $row['account_state'];
   $fields['email'] = $user_info->user_email;
   $fields['address_street'] = '';
   $fields['address_city'] = '';
   $fields['address_state'] = '';
   $fields['address_zipcode'] ='';
   $fields['country'] = '';
   $fields['gender'] = '';
   $fields['referrer'] = '';
   $fields['subscription_starts'] = $row['subscription_starts'];
   $fields['extra_info'] = '';
   if(isset($row['preserve_wp_role'])){
       $fields['flags'] = 1;
   }
   else{
       $fields['flags'] = 0;
       if(($row['account_state'] === 'active') && !in_array('administrator',$user_cap))
       	update_wp_user_Role($row['ID'], $row['membership_level']);
   }
   $user_exists = emember_username_exists($fields['user_name']);
   
   if($user_exists){
       return dbAccess::update(WP_EMEMBER_MEMBERS_TABLE_NAME,'member_id = ' . $user_exists, $fields);
   }
   else{
       return dbAccess::insert(WP_EMEMBER_MEMBERS_TABLE_NAME, $fields);
   }
	
}
function wp_eMember_add_wp_members(){
    global $emember_config;
    $emember_config = Emember_Config::getInstance();    
    global $wpdb;
    if(isset($_POST['add_to_wp'])&&isset($_POST['wp_add_wp_member_to_emember'])){
    	$query = "SELECT ID,user_login FROM $wpdb->users";
    	$result = $wpdb->get_results($query, ARRAY_A);
    	$wp_user_data = array();
    	$wp_user_data['membership_level'] = $_POST['wp_users_membership_level'];
    	$wp_user_data['account_state'] = $_POST['wp_users_account_state'];
    	$wp_user_data['subscription_starts'] = $_POST['wp_users_subscription_starts'];
    	$wp_user_data['preserve_wp_role'] = $_POST['wp_users_preserve_wp_role'];
    	foreach($result as $row){
    		$wp_user_data['ID'] = $row['ID'];
    		$updated = __wp_eMember_add($wp_user_data);
    	    if($updated === false){
            	$_SESSION['flash_message'] = '<div id="message" style= "color:red;" class="updated fade"><p>'.__('Failed to update "' . $row['user_login'] . '"', 'wp_eMember').__('Member Info.', 'wp_eMember').'</p></div>';
            	break;
        	}

    	}
        $_SESSION['flash_message'] = '<div id="message" class="updated fade"><p>'.__('Member Info ', 'wp_eMember').__('updated.', 'wp_eMember').'</p></div>';
        echo '<script type="text/javascript">window.location = "admin.php?page=wp_eMember_manage";</script>';
        return ;   	
    }
    else if(isset($_POST['submit'])){
        $updated = false;
        foreach($_POST['selected_wp_users'] as $row){
            if(isset($row['ID'])){
            	$updated = __wp_eMember_add($row);
            }
        }
        if($updated === false){
            $_SESSION['flash_message'] = '<div id="message" style= "color:red;" class="updated fade"><p>'.__('Failed to update ', 'wp_eMember').__('Member Info.', 'wp_eMember').'</p></div>';
        }
        else{
            $_SESSION['flash_message'] = '<div id="message" class="updated fade"><p>'.__('Member Info ', 'wp_eMember').__('updated.', 'wp_eMember').'</p></div>';
            echo '<script type="text/javascript">window.location = "admin.php?page=wp_eMember_manage";</script>';
            return ;
        }
    }
    global $wpdb;
    $wp_member_count = $wpdb->get_row("SELECT count(*) as count FROM $wpdb->users ORDER BY ID");
    $all_levels = dbAccess::findAll(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, ' id != 1 ', ' id DESC ');
    ?>    
<div class="wrap">
	<h2>Import Wordpress Users</h2>  
	<p><strong><i>You can either import all of your WordPress users to eMember in one go or selectively import users from this interface.</i></strong></p>
	<form method="post" action="">
    <table class="widefat" >
        <thead>
            <tr>
                <th scope="col" colspan="3">Import All Users to eMember</th>
                <th scope="col">Membership Level</th>
                <th scope="col">Subscription Starts From</th>
                <th scope="col">Account State</th>
                <th scope="col">Preserve Role</th>
            </tr>
        </thead>
        <tbody>
			<tr valign="top">
				<td class="check-column" colspan="3" scope="row">
					<input type="checkbox" value="1" name="wp_add_wp_member_to_emember">
				</td>
				<td>
					<select name="wp_users_membership_level">
			           <?php
			           foreach($all_levels as $l):
			           ?>					
						<option value="<?php echo $l->id; ?>"><?php echo $l->alias; ?></option>
						<?php endforeach;?>
					</select>
				</td>
				<td>
					<input type="date" value="<?php echo date('Y-m-d');?>" name="wp_users_subscription_starts" id="wp_users_subscription_starts" >
				</td>
				<td>
					<select name="wp_users_account_state">
						<option value="active">Active</option>
						<option value="inactive">Inactive</option>
						<option value="blocked">Blocked</option>
					</select>
				</td>
				<td>
					<input type="checkbox" value="1" checked="checked" name="wp_users_preserve_wp_role">
				</td>
			</tr>        
        </tbody>
    </table>
    <p class="submit">
        <input name="add_to_wp" type="submit" value="Submit" />
    </p>
    </form>
    <hr>    
    <form action="javascript:void(0);" id="emember_user_search">
        <p class="search-box">
            <label for="post-search-input" class="screen-reader-text">Search Users:</label>
            <input type="text" value="" name="e" title="Email" id="post-search-email" size="30" />
            <select id="post_search_operator">
                <option value="and"> And </option>
                <option value="or"> Or </option>
            </select>
            <input type="text" value="" name="u" title="User Name" id="post-search-user" size="20" />
            <input type="submit" class="button" value="Search Users"/>
        </p>
    </form>    
    <div id="Pagination" class="pagination"></div>
    <form method="post">
    <table class="widefat" id="wp_member_list">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">User Name</th>
                <th scope="col">Email</th>
                <th scope="col">Membership Level</th>
                <th scope="col">Subscription Starts From</th>
                <th scope="col">Account State</th>
                <th scope="col">Preserve Role</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <p class="submit">
        <input name="submit" type="submit" value="Submit" />
    </p>
    </form>
    <script type="text/javascript">
    /* <![CDATA[ */
    var $j = jQuery.noConflict();
    function drawContent(count, params){
      var counter = 0;
      var itms_per_pg = parseInt(<?php 
      $items_per_page = $emember_config->getValue('eMember_rows_per_page');
      $items_per_page = trim($items_per_page);
      echo (!empty($items_per_page)&& is_numeric($items_per_page))? $items_per_page:30;
      ?>);
      var $tbody = $j('#wp_member_list tbody');
      $j("#Pagination").pagination(count, {
         callback: function(i,container){
             var preloader = '<?php echo emember_preloader(7); ?>';
           $tbody.html(preloader);
           var paramss = {}; 
           if(params)
               paramss = { 
                   action: "wp_user_list_ajax",
                   event: "wp_user_list_ajax",
                   start:i*itms_per_pg,
                   limit:itms_per_pg,
                   u:params.u,
                   e:params.e,
                   o:params.o
               };
           else 
               paramss = { 
                   action: "wp_user_list_ajax",
                   event: "wp_user_list_ajax",
                   start:i*itms_per_pg,
                   limit:itms_per_pg
               };
           var maxIndex = Math.min((i+1)*itms_per_pg, count);
           var target_url = '<?php echo admin_url( "admin-ajax.php" ); ?>';
           $j.get(target_url,
               paramss,
                 function(data){
                     data = $j(data);
                     $tbody.html(data.filter('tbody').html());
                     window.eval(data.filter('script').html());
                 },
                 'html'
             );
         },
         num_edge_entries: 2,
         num_display_entries: 10,
         items_per_page: itms_per_pg
      });        
    }
    $j(document).ready(function(){
//           imgPath = '<?php echo WP_EMEMBER_URL . '/images/';?>';
//           var $dp ={dateFormat: 'yy-mm-dd' ,
//                     showOn: 'button', 
//                     buttonImage: imgPath+'calendar.gif', 
//                     buttonImageOnly: true};
//           var $de = {dateFormat: 'ymd-',spinnerImage: '', useMouseWheel:false};
//           $j('#wp_users_subscription_starts').datepicker($dp).dateEntry($de);
           $j("#wp_users_subscription_starts").dateinput({'format':'yyyy-mm-dd',selectors: true,yearRange:[-100,100]});
           var count = <?php echo $wp_member_count->count; ?>;
           $j('input[title!=""]').hint();
           drawContent(count);
           $j('#emember_user_search').submit(function(e){
                e.prevenDefault;
                var params = {action: "emember_wp_user_count_ajax",
                              event: "emember_wp_user_count_ajax",
                              u:user, 
                              e:email,
                              o:op
                          };
                var user  = $j('#post-search-user').val();
                var email = $j('#post-search-email').val();
                var op    = $j('#post_search_operator').val();
                var q     = "u="+ user+ "&e="+email + "&o="+op;
                if(user !="" ||email !=""){
                    var target_url = '<?php echo admin_url( "admin-ajax.php" ); ?>';
                    $j.get(target_url ,params,
                           function(data){
                              drawContent(data.count, {u:user, e:email,o:op});
                           },
                           'json'
                        );
                }
                  return false;
          });
 /*****************/
          });
   /*]]>*/
    </script>
</div>
<?php
}
function wp_eMember_manage_memebers_lists(){
     $active = (isset($_POST['wp_eMember_display_active_member_email']))? $_POST['wp_eMember_display_active_member_email']: 0;    	
    ?>
    <div class="postbox">
       <h3><label for="title">Member Email Options</label></h3>
       <div class="inside">
          <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
             <input type="hidden" name="wp_eMember_display_email_list_for_id" id="wp_eMember_display_email_list_for_id" value="true" />
             <table width="100%" border="0" cellspacing="0" cellpadding="6">
                <tr valign="top">
                   <td width="25%" align="left">
                      <strong>1)</strong> Display Member Email List for a Particular Membership Level:
                   </td>
                   <td align="left">
                      <input name="wp_eMember_mem_level_id" type="text" size="10" value="<?php echo $wp_eMember_mem_level_id; ?>" />
                      <input type="submit" name="wp_eMember_display_email_list_for_id" value="<?php _e('Display List'); ?> &raquo;" />
                      <br /><i>Enter the ID of the membership level that you want to display the member email list for (comma separated) and hit Display List.</i><br />
                    </td>
                </tr>
             </table>
          </form>

          <br />
          <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
             <input type="hidden" name="wp_eMember_display_all_member_email" id="wp_eMember_display_all_member_email" value="true" />
             <table width="100%" border="0" cellspacing="0" cellpadding="6">
                <tr valign="top">
                    <td width="25%" align="left">
                        <strong>2)</strong> Display Email List of All Members:
                    </td>
                    <td align="left">
                        <input type="checkbox" <?php if($active) echo 'checked="checked"';?> name="wp_eMember_display_active_member_email" value="1" /> Active Members Only 
                        <input type="submit" name="wp_eMember_display_all_member_email" value="<?php _e('Display All Members Email List'); ?> &raquo;" />                        
                        <br /><i>Use this to display a list of emails (comma separated) of all the members for bulk emailing purpuse.</i><br />
                    </td>
                </tr>
             </table>
          </form>
          <br/>
          <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
             <table width="100%" border="0" cellspacing="0" cellpadding="6">
                <tr valign="top">
                    <td width="25%" align="left">
                        <strong>3)</strong> Export All Member info:
                    </td>
                    <td align="left">
                        <input type="submit" name="wp_emember_export" value="<?php _e('Export'); ?> &raquo;" />
                        <br /><i>Use this to export all member info in CSV format.</i><br />
                    </td>
                </tr>
             </table>
          </form>          
       </div>
    </div>
    <?php
    global $wpdb;	
    if ($_POST['wp_eMember_display_email_list_for_id']){
        $selected_level_id = (string)$_POST["wp_eMember_mem_level_id"];
        $member_table = WP_EMEMBER_MEMBERS_TABLE_NAME;
        $ret_member_db = $wpdb->get_results("SELECT * FROM $member_table WHERE membership_level = '$selected_level_id'", OBJECT);
        echo wp_eMember_display_member_email_list($ret_member_db);
    }
    if ($_POST['wp_eMember_display_all_member_email']){
        $member_table = WP_EMEMBER_MEMBERS_TABLE_NAME;
        $query = "SELECT * FROM $member_table ";
        if(isset($_POST['wp_eMember_display_active_member_email'])&&( 1==$_POST['wp_eMember_display_active_member_email']))
            $query .= " WHERE account_state ='active' ";
            
        $query .= " ORDER BY member_id DESC";  
        $ret_member_db = $wpdb->get_results($query, OBJECT);
        echo wp_eMember_display_member_email_list($ret_member_db);
    }
}
function wp_eMember_display_member_email_list($ret_member_db){
    if ($ret_member_db){
        foreach ($ret_member_db as $ret_member_db){
            $output .= $ret_member_db->email;
            $output .= ', ';
        }
    }
    else{
        $output .= 'No Members found.';
    }
    return $output;
}

function wp_eMember_delete_member(){
    global $wpdb;
    wp_eMember_sub_header('<div class="wrap"><h2>WP eMembers - Manage Members</h2>');             
    $timestamp = isset($_GET['confirm'])? trim($_GET['confirm']):"0";
    if(isset($_SESSION['emember_deleterecord'][$timestamp])){
        $privileged_profiles = $_SESSION['emember_deleterecord'][$timestamp];
        foreach($privileged_profiles as $profile){
           if($profile['wp_user_id']) wp_delete_user( $profile['wp_user_id'], 1 ); //assigns all related to this user to admin.
           $query = "DELETE FROM " . WP_EMEMBER_MEMBERS_TABLE_NAME . " WHERE member_id = " . $profile['member_id'];
           $wpdb->query($query);                    
        }
        unset($_SESSION['emember_deleterecord'][$timestamp]);
        wp_eMember_message('Privileged Member(s) Deleted.');
        wp_eMember_list();                                  
    }        
    else{    
        if ($_REQUEST['deleterecord']!=''){
            $therecord=$_REQUEST['deleterecord'];
            if(!is_array($therecord)) $therecord = array($therecord);               
            $query = "SELECT user_name, member_id FROM " . WP_EMEMBER_MEMBERS_TABLE_NAME . 
                     " WHERE member_id IN (" . implode(',', $therecord ) . ')';
            $profiles = $wpdb->get_results($query, ARRAY_A);
            $privileged_profiles = array();
            foreach($profiles as $profile){
                $wp_user_id = username_exists($profile['user_name']);
                $ud = get_userdata($wp_user_id); 
                if(isset($ud->wp_capabilities['administrator'])||$ud->wp_user_level == 10){
                    $profile['wp_user_id'] = $wp_user_id;
                    $privileged_profiles[] = $profile; 
                }
                else{
                    if($wp_user_id) wp_delete_user( $wp_user_id, 1 ); //assigns all related to this user to admin.                   
                    $query = "DELETE FROM " . WP_EMEMBER_MEMBERS_TABLE_NAME . " WHERE member_id = " . $profile['member_id'];
                    $wpdb->query($query);
                }
            }
            if(count($privileged_profiles)>0){
                $curtimestamp = time();
                unset($_SESSION['emember_deleterecord']);
                $_SESSION['emember_deleterecord'][$curtimestamp] = $privileged_profiles;
                $u = "admin.php";
                $_GET['confirm'] = $curtimestamp;
                $u .= '?' . http_build_query($_GET);
                $warning = "<div id='message' style=\"color:red;\" ><p>You are about to delete an account that has admin privilege.<br/>
                If you are using WordPress user integration then this will delete the corresponding user <br/>
                account from WordPress and you may not be able to log in as admin with this account.<br/>";
                $warning .= "Continue? <a href='". $u. "'>yes</a>/<a href='javascript:void(0);' onclick='jQuery(\"#message\").remove();top.document.location=\"admin.php?page=wp_eMember_manage\";' >no</a></p></div>";
                $warning .='Following User(s) have administrative privileges:<br/>';
                $warning .= '<table class="wp-list-table widefat fixed users" style="width:400px;"><tr><th>User</th><th>WP profile</th><th>eMember profile</th></tr>';
                foreach($privileged_profiles as $profile){
                    $warning .= "<tr><td>".$profile['user_name']. "</td>";
                    $warning .= "<td><a target='_blank' href='user-edit.php?user_id=".$profile['wp_user_id']."'>View</a></td>";
                    $warning .= "<td><a target='_blank' href='admin.php?page=wp_eMember_manage&members_action=add_edit&editrecord=".$profile['wp_user_id']."'>View</a></td>";
                    $warning .="</tr>";                                 
                }
                $warning .="</table>";
                echo $warning;                  
            }
            else{
                wp_eMember_message('Member(s) Deleted.');
                wp_eMember_list();              
            }               
        }
        else{
            wp_eMember_list();
        }             
    }
}
function wp_eMember_sub_header($sub_header){
   echo $sub_header;
}
function wp_eMember_message($msg){
   echo '<div id="message" class="updated fade"><p>'.__($msg, 'wp_eMember').'</p></div>';
}
function wp_eMember_list(){
    global $emember_config;
    $emember_config = Emember_Config::getInstance();    
    echo '<div id="Pagination" class="pagination"></div>';
    echo '<form method="post" action="admin.php?page=wp_eMember_manage&members_action=delete">
    <table id="member_list" class="widefat"><thead><tr>
    <th scope="col"><input type="checkbox" name="emember_blk_all" id="emember_blk_all" value=1 /></th>
    <th scope="col">'.__('ID', 'wp_eMember').'</th>';
    
    echo '<th scope="col">';
	if(isset($_GET['order']) && $_GET['order'] == 'asc'){
		echo 'User Name <a href="admin.php?page=wp_eMember_manage&orderby=user_name&order=desc"><img src="'.WP_EMEMBER_URL.'/images/sort-desc-icon.gif" title="Sort by username"></a>';
	}	
	else{
		echo 'User Name <a href="admin.php?page=wp_eMember_manage&orderby=user_name&order=asc"><img src="'.WP_EMEMBER_URL.'/images/sort-asc-icon.gif" title="Sort by username"></a>';		
	}
	echo '</th>';	

    echo '<th scope="col">';
	if(isset($_GET['order']) && $_GET['order'] == 'asc'){
		echo 'First Namee <a href="admin.php?page=wp_eMember_manage&orderby=first_name&order=desc"><img src="'.WP_EMEMBER_URL.'/images/sort-desc-icon.gif" title="Sort by first name"></a>';
	}	
	else{
		echo 'First Name <a href="admin.php?page=wp_eMember_manage&orderby=first_name&order=asc"><img src="'.WP_EMEMBER_URL.'/images/sort-asc-icon.gif" title="Sort by first name"></a>';		
	}
	echo '</th>';	

    echo '<th scope="col">';
	if(isset($_GET['order']) && $_GET['order'] == 'asc'){
		echo 'Last Name <a href="admin.php?page=wp_eMember_manage&orderby=last_name&order=desc"><img src="'.WP_EMEMBER_URL.'/images/sort-desc-icon.gif" title="Sort by last name"></a>';
	}	
	else{
		echo 'Last Name <a href="admin.php?page=wp_eMember_manage&orderby=last_name&order=asc"><img src="'.WP_EMEMBER_URL.'/images/sort-asc-icon.gif" title="Sort by last name"></a>';		
	}
	echo '</th>';	

    echo '<th scope="col">';
	if(isset($_GET['order']) && $_GET['order'] == 'asc'){
		echo 'Email <a href="admin.php?page=wp_eMember_manage&orderby=email&order=desc"><img src="'.WP_EMEMBER_URL.'/images/sort-desc-icon.gif" title="Sort by email address"></a>';
	}	
	else{
		echo 'Email <a href="admin.php?page=wp_eMember_manage&orderby=email&order=asc"><img src="'.WP_EMEMBER_URL.'/images/sort-asc-icon.gif" title="Sort by email address"></a>';		
	}
	echo '</th>';	
		
    echo '<th scope="col">';
	if(isset($_GET['order']) && $_GET['order'] == 'asc'){
		echo 'Membership Level <a href="admin.php?page=wp_eMember_manage&orderby=membership_level&order=desc"><img src="'.WP_EMEMBER_URL.'/images/sort-desc-icon.gif" title="Sort by membership level"></a>';
	}	
	else{
		echo 'Membership Level <a href="admin.php?page=wp_eMember_manage&orderby=membership_level&order=asc"><img src="'.WP_EMEMBER_URL.'/images/sort-asc-icon.gif" title="Sort by membership level"></a>';		
	}
	echo '</th>';			    
    echo '<th scope="col">'.__('Last Access from IP', 'wp_eMember').'</th>
    <th scope="col">'.__('Subscription Starts', 'wp_eMember').'</th>
    <th scope="col">';
	if(isset($_GET['order']) && $_GET['order'] == 'asc'){
		echo 'Account State <a href="admin.php?page=wp_eMember_manage&orderby=account_state&order=desc"><img src="'.WP_EMEMBER_URL.'/images/sort-desc-icon.gif" title="Sort by membership level"></a>';
	}	
	else{
		echo 'Account State <a href="admin.php?page=wp_eMember_manage&orderby=account_state&order=asc"><img src="'.WP_EMEMBER_URL.'/images/sort-asc-icon.gif" title="Sort by membership level"></a>';		
	}    
    echo '</th>';
    echo '<th scope="col" colspan="3">'.__('Actions', 'wp_eMember').'</th>
    </tr></thead>
    <tbody>';

   global $wpdb;
   $emember_user_count = $wpdb->get_row("SELECT COUNT(*) as count FROM " . WP_EMEMBER_MEMBERS_TABLE_NAME . ' ORDER BY member_id');
   
    echo '</tbody>
    </table>
    <div class="tablenav bottom">
    <div class="alignleft actions">
        <select name="action2">
            <option value="trash">delete</option>
        </select>
        <input type="submit" value="Apply" class="button-secondary action" id="doaction2" name="">
    </div>
    <div class="alignleft actions"></div>
    <br class="clear">
    </div> </form>';

	if(isset($_GET['order']))
	{		
		$orderby = $_GET['orderby'];
		$order = $_GET['order'];
	}
	else
	{    
	    $orderby = 'member_id';
	    $order = 'asc';
	}
    
    ?>
   <script type="text/javascript">
   /* <![CDATA[ */
   $j = jQuery.noConflict();
   function drawContent(count, params){
	   var orderbyVal = "<?php echo $orderby; ?>";
	   var orderVal = "<?php echo $order; ?>";
       var counter = 0;
       var itms_per_pg = parseInt(<?php $items_per_page = $emember_config->getValue ('eMember_rows_per_page');
       $items_per_page = trim($items_per_page);
       echo (!empty($items_per_page)&& is_numeric($items_per_page))? $items_per_page:30;?>);
       var $tbody = $j('#member_list tbody');
       jQuery("#Pagination").pagination(count, {
          callback: function(i,container){
             $tbody.html('<?php echo emember_preloader(13)?>');
            if(params)
                paramss = { action: "emember_user_list_ajax",
                            event: "emember_user_list_ajax",
                            start:i*itms_per_pg,
                            limit:itms_per_pg,
                            u:params.u, 
                            e:params.e,
                            o:params.o,
                            orderby:orderbyVal,
                            order:orderVal};
            else
                paramss = { action: "emember_user_list_ajax",
                            event: "emember_user_list_ajax",
                            start:i*itms_per_pg,
                            limit:itms_per_pg,
                            orderby:orderbyVal,
                            order:orderVal};
            var maxIndex = Math.min((i+1)*itms_per_pg, count);
            var target_url = '<?php echo admin_url( "admin-ajax.php" ); ?>';
            $j.get(target_url ,
                paramss,
                  function(data){
                       $tbody.html('');
                       data =$j(data);
                       $tbody.html(data.filter('tbody').html());
                       window.eval(data.filter('script').html());                       
                  },
                  'html'
              );
          },
          num_edge_entries: 2,
          num_display_entries: 10,
          items_per_page: itms_per_pg
       });
   }

      $j(document).ready(function(){
          $j('#emember_blk_all').click(function(){
                if(this.checked)
                   $j('.emember_blk_op').attr("checked","checked");
                else
                    $j('.emember_blk_op').removeAttr("checked");
          });
          $j('input[title!=""]').hint();
          var count = <?php echo $emember_user_count->count; ?>;
          drawContent(count);
          $j('#emember_user_search').submit(function(e){
                e.prevenDefault;
                var user  = $j('#post-search-user').val();
                var email = $j('#post-search-email').val();
                var op    = $j('#post_search_operator').val();
                var q     = "u="+ user+ "&e="+email + "&o="+op;
                if(user !="" ||email !=""){
                    var target_url = '<?php echo admin_url( "admin-ajax.php" ); ?>';
                    $j.get(target_url ,
                        {  action: "emember_user_count_ajax",event: "emember_user_count_ajax",u:user, e:email,o:op},
                           function(data){
                              drawContent(data.count, {u:user, e:email,o:op});
                           },
                           'json'
                        );
                }
                  return false;
          });
      });
   /*]]>*/
   </script>
   <?php
}
function wp_eMember_manage_members(){
    wp_eMember_sub_header('<div class="wrap"><h2>WP eMembers - Manage Members</h2>');
    echo '<form action="javascript:void(0);" id="emember_user_search"><p class="search-box">
    <label for="post-search-input" class="screen-reader-text">Search Users:</label>
    <input type="text" value="" name="e" title="Email" id="post-search-email" size="30" />
    <select id="post_search_operator">
    <option value="and"> And </option>
    <option value="or"> Or </option>
    </select>
    <input type="text" value="" name="u" title="User Name" id="post-search-user" size="20" />
    <input type="submit" class="button" value="Search Users"/>
    </p></form>';
    if(isset($_SESSION['flash_message'])){echo $_SESSION['flash_message']; unset($_SESSION['flash_message']);}
    wp_eMember_list();
}

function wp_eMember_add_memebers(){
    global $emember_config;
    $emember_config = Emember_Config::getInstance();    
    global $wpdb;
    $d = WP_EMEMBER_URL.'/images/default_image.gif';
    wp_eMember_sub_header('<div class="wrap"><h2>WP eMember - Add/Edit Members</h2>');
    //If being edited, grab current info
    if ($_GET['editrecord']!=''){
        $theid = $_GET['editrecord'];
        $editingrecord = dbAccess::find(WP_EMEMBER_MEMBERS_TABLE_NAME, ' member_id=' . $theid);
        $edit_custom_fields = dbAccess::find(WP_EMEMBER_MEMBERS_META_TABLE, ' user_id=' . $theid . ' AND meta_key="custom_field"');
        $edit_custom_fields = unserialize($edit_custom_fields->meta_value);
        $editingrecord->more_membership_levels = explode(',', $editingrecord->more_membership_levels);
        $editingrecord = (array)$editingrecord;        
	    $image_url       = null;
	    $image_path  = null;
		$upload_dir  = wp_upload_dir();
	    $upload_url  = $upload_dir['baseurl'];
	    $upload_path = $upload_dir['basedir'];
	    $upload_url  .= '/emember/';
	    $upload_path .= '/emember/';
	    $upload_url  .= $theid;
	    $upload_path .= $theid;
	    if(file_exists($upload_path . '.jpg')){
	    	$image_url = $upload_url . '.jpg';
	    	$image_path = $upload_path . '.jpg';
	    }
	    else if(file_exists($upload_path . '.jpeg')){
	    	$image_url = $upload_url . '.jpeg';
	    	$image_path = $upload_path . '.jpeg';
	    }
	    else if(file_exists($upload_path . '.gif')){
	    	$image_url = $upload_url . '.gif';
	    	$image_path = $upload_path . '.gif';
	    }
	    else if(file_exists($upload_path . '.png')){
	    	$image_url = $upload_url . '.png';
	    	$image_path = $upload_path . '.png';
	    }
	    else{
	    	$use_gravatar = $emember_config->getValue('eMember_use_gravatar');
	    	if($use_gravatar)	    	
		    	$image_url = WP_EMEMBER_GRAVATAR_URL. "/" . md5(strtolower($editingrecord['email'])) . "?d=" . urlencode($d) . "&s=" . 96;
	         else
		   		$image_url = WP_EMEMBER_URL . '/images/default_image.gif';
	    }        
    }
    if ($_POST['Submit']){
        global $wpdb;
        include_once(ABSPATH . WPINC . '/class-phpass.php');
        $wp_hasher = new PasswordHash(8, TRUE);
        $post_editedrecord = $wpdb->escape($_POST['editedrecord']);
        $fields = array();
        $fields['flags'] = 0;
        if($emember_config->getValue('eMember_enable_secondary_membership'))
        $fields['more_membership_levels'] = implode(',',empty($_POST['more_membership_levels'])?array():$_POST['more_membership_levels']);
		$fields["user_name"] =          $_POST["user_name"];         
		$fields["first_name"]=          $_POST["first_name"];        
		$fields["last_name"]=           $_POST["last_name"];                     
		$fields["company_name"]=        $_POST["company_name"];      
		$fields["member_since"]=        $_POST["member_since"];      
		$fields["membership_level"]=    $_POST["membership_level"]; 
		//$fields["initial_membership_level"]=    $_POST["membership_level"];		
		$fields["account_state"]=       $_POST["account_state"];     
		$fields["email"]=               $_POST["email"];  
		$fields["phone"]=               $_POST["phone"];           
		$fields["address_street"]=      $_POST["address_street"];    
		$fields["address_city"]=        $_POST["address_city"];      
		$fields["address_state"]=       $_POST["address_state"];     
		$fields["address_zipcode"]=     $_POST["address_zipcode"];   
		$fields["country"]=             $_POST["country"];           
		$fields["gender"]=              $_POST["gender"];            
		$fields["referrer"]=            $_POST["referrer"];          
		$fields["subscription_starts"]= $_POST["subscription_starts"];  
		$fields['last_accessed_from_ip']= get_real_ip_addr();             
        $wp_user_info  = array();
        $wp_user_info['user_nicename'] = implode('-', explode(' ', $_POST['user_name']));
        $wp_user_info['display_name']  = $_POST['user_name'];
        $wp_user_info['user_email']    = $_POST['email'];
        $wp_user_info['nickname']      = $_POST['user_name'];
        $wp_user_info['first_name']    = $_POST['first_name'];
        $wp_user_info['last_name']     = $_POST['last_name'];
        
        if ($post_editedrecord==''){
            $fields['user_name']        = $wpdb->escape($_POST['user_name']);
            $wp_user_info['user_login'] = $_POST['user_name'];
            // Add the record to the DB
            include_once ('emember_validator.php');
            $validator  = new Emember_Validator();            
            $validator->add(array('value'=>$fields['user_name'],'label'=>'User Name','rules'=>array('user_required','alphanumericunderscore','user_unavail','user_minlength')));
            $validator->add(array('value'=>$_POST['password'],'repeat'=>$_POST['retype_password'],'label'=>'Password','rules'=>array('pass_required','pass_mismatch')));
            $validator->add(array('value'=>$fields['email'],'label'=>'Email','rules'=>array('email_required','email_unavail')));
            $messages = $validator->validate();
            if(count($messages)>0){
                echo '<span class="emember_error">' . implode('<br/>', $messages) . '</span>';
                $editingrecord = $_POST;
            }                
            else{
                $password           = $wp_hasher->HashPassword($_POST['password']);
                $fields['password'] = $wpdb->escape($password);
                $should_create_wp_user = $emember_config->getValue('eMember_create_wp_user');
                if($should_create_wp_user){
                    $role_names = array(1=>'Administrator',2=>'Editor',3=>'Author',4=>'Contributor',5=>'Subscriber');
                    $membership_level_resultset = dbAccess::find(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, " id='" .$fields['membership_level'] . "'" );
                    $wp_user_info['role']            = $membership_level_resultset->role;
                    $wp_user_info['user_registered'] = date('Y-m-d H:i:s');
                    $wp_user_id = wp_create_user($_POST['user_name'], $_POST['password'], $_POST['email']);
                    $wp_user_info['ID'] = $wp_user_id;
                    wp_update_user( $wp_user_info );
                	$user_info = get_userdata($wp_user_id);
                	$user_cap = is_array($user_info->wp_capabilities)?array_keys($user_info->wp_capabilities):array();

                	if(($fields["account_state"] === 'active') && !in_array('administrator',$user_cap))                    
                        update_wp_user_Role($wp_user_id, $membership_level_resultset->role);
                    do_action( 'set_user_role', $wp_user_id, $membership_level_resultset->role );                    
                }

                $ret = dbAccess::insert(WP_EMEMBER_MEMBERS_TABLE_NAME, $fields);
                $lastid = $wpdb->insert_id;
                ///custom field insert
                if(isset($_POST['emember_custom']))
                $wpdb->query("INSERT INTO " . WP_EMEMBER_MEMBERS_META_TABLE . 
                '( user_id, meta_key, meta_value ) VALUES(' . $lastid .',"custom_field",' . '\''.addslashes (serialize($_POST['emember_custom'])).'\')');
                if($ret === false)
                    $_SESSION['flash_message'] = '<div id="message" style = "color:red;" class="updated fade"><p>Couldn\'t create new member.</p></div>';
                else{
                    if(isset($_POST['uploaded_profile_img'])){
                        $upload_dir  = wp_upload_dir();
                        $upload_path = $upload_dir['basedir'];
                        $upload_path .= '/emember/';

                        $ext = explode('.',$_POST['uploaded_profile_img']);
                        rename($upload_path . $_POST['uploaded_profile_img'], $upload_path . $lastid . '.' . $ext[1]);
                    }
                    $_SESSION['flash_message'] = '<div id="message" class="updated fade"><p>Member &quot;'.
                    $fields['user_name'].'&quot; created.</p></div>';

                     //Notify the newly created member if specified in the settings
                    if ($emember_config->getValue('eMember_email_notification_for_manual_member_add')){
                        $login_link            = $emember_config->getValue('login_page_url');
                        $member_email_address  = $_POST['email'];

                        $subject_rego_complete = $emember_config->getValue('eMember_email_subject_rego_complete');

                        $body_rego_complete    = $emember_config->getValue('eMember_email_body_rego_complete');
                        $tags1                 = array("{first_name}","{last_name}","{user_name}","{password}","{login_link}");
                        $vals1                 = array($_POST['first_name'],$_POST['last_name'],$_POST['user_name'],$_POST['password'],$login_link);
                        $email_body1           = str_replace($tags1,$vals1,$body_rego_complete);

                        $from_address          = $emember_config->getValue('senders_email_address');
                        $headers               = 'From: '.$from_address . "\r\n";

                        wp_mail($member_email_address,$subject_rego_complete,$email_body1,$headers);
                    }
                    
                    //Create the corresponding affliate account if specified in the settings
                    if($emember_config->getValue('eMember_auto_affiliate_account')){
                        eMember_handle_affiliate_signup($_POST['user_name'],$_POST['password'],$_POST['first_name'],$_POST['last_name'],$_POST['email'],'');
                    }                    

                    echo '<script type="text/javascript">window.location = "admin.php?page=wp_eMember_manage";</script>';
                }
            }
        }
        else{
            if(isset($_POST['emember_custom'])){
            	$custom_fields = dbAccess::find(WP_EMEMBER_MEMBERS_META_TABLE, ' user_id=' . $post_editedrecord . ' AND meta_key=\'custom_field\'');
            	if($custom_fields)
	                $wpdb->query('UPDATE ' . WP_EMEMBER_MEMBERS_META_TABLE . 
	                ' SET meta_value ='. '\''.addslashes (serialize($_POST['emember_custom'])). '\' WHERE meta_key = \'custom_field\' AND  user_id=' . $post_editedrecord);
                else 
	                $wpdb->query("INSERT INTO " . WP_EMEMBER_MEMBERS_META_TABLE . 
	                '( user_id, meta_key, meta_value ) VALUES(' . $post_editedrecord .',"custom_field",' . '\''.addslashes (serialize($_POST['emember_custom'])).'\')');            	
            }else{
	            $wpdb->query('DELETE FROM ' . WP_EMEMBER_MEMBERS_META_TABLE . 
	            '  WHERE meta_key = \'custom_field\' AND  user_id=' . $post_editedrecord);
            	
            }
                             
            $editingrecord = dbAccess::find(WP_EMEMBER_MEMBERS_TABLE_NAME, ' member_id=' . $post_editedrecord);
            // Update the member info
            $member_id = $wpdb->escape($_POST['editedrecord']);
            $wp_user_id = username_exists($fields['user_name']);
            $wp_email_owner = email_exists($fields['email']);
            $emember_email_owner = emember_email_exists($fields['email']);
            if(($wp_email_owner&&($wp_user_id!=$wp_email_owner))||($emember_email_owner&&($member_id!=$emember_email_owner))){
                echo '<div id="message" class="updated fade"><p>Email ID &quot;'.
                $fields['email'].'&quot; is already registered to a user!</p></div>';
            }
            else{
                $user_info = get_userdata($wp_user_id);
                $user_cap = array_keys((array)($user_info->wp_capabilities));
                                	
                if(!empty($_POST['password'])){
                    if($_POST['password']===$_POST['retype_password']){
                        $password           = $wp_hasher->HashPassword($_POST['password']);
                        $fields['password'] = $wpdb->escape($password);
                        $wp_user_info['user_pass']=$_POST['password'];
                        if($wp_user_id){
                            $wp_user_info['ID'] = $wp_user_id;
                            wp_update_user( $wp_user_info );
                            if(($editingrecord->flags&1)!=1){
                                $membership_level_resultset = dbAccess::find(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, " id='" .$fields['membership_level'] . "'" );
                                if(($fields["account_state"] === 'active')&&!in_array('administrator',$user_cap)){                               
                                	update_wp_user_Role($wp_user_id, $membership_level_resultset->role);
                                    do_action( 'set_user_role', $wp_user_id, $membership_level_resultset->role );                                	
                                }
                            }
                        }
                        $ret = dbAccess::update(WP_EMEMBER_MEMBERS_TABLE_NAME,'member_id = ' . $member_id, $fields);
                        if($ret === false){
                            $_SESSION['flash_message'] = '<div id="message" style="color:red;" class="updated fade"><p>'.__('Member', 'wp_eMember').' &quot;'.
                            $fields['user_name'].'&quot; '.__('Update Failed.', 'wp_eMember').'</p></div>';
                        }
                        else{                        	
                            $_SESSION['flash_message'] = '<div id="message" class="updated fade"><p>'.__('Member', 'wp_eMember').' &quot;'.
                            $fields['user_name'].'&quot; '.__('updated.', 'wp_eMember').'</p></div>';
                            echo '<script type="text/javascript">window.location = "admin.php?page=wp_eMember_manage";</script>';
                        }
                    }
                    else{
                        echo '<div id="message" class="updated fade"><p>Password does\'t match!</p></div>';
                    }
                }
                else{
                    if($wp_user_id){
                        $wp_user_info['ID'] = $wp_user_id;
                        wp_update_user( $wp_user_info );
                        if(($editingrecord->flags&1)!=1){
                            $membership_level_resultset = dbAccess::find(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, " id='" .$fields['membership_level'] . "'" );
                			if(($fields["account_state"] === 'active')&&!in_array('administrator',$user_cap)){                                            				
                            	update_wp_user_Role($wp_user_id, $membership_level_resultset->role);
                            	do_action( 'set_user_role', $wp_user_id, $membership_level_resultset->role );
                			}
                        }
                    }
                    $ret = dbAccess::update(WP_EMEMBER_MEMBERS_TABLE_NAME,'member_id = ' . $member_id, $fields);                    
                     if($ret === false){
                        $_SESSION['flash_message'] = '<div id="message" class="updated fade"><p>'.__('Member', 'wp_eMember').' &quot;'.
                        $fields['user_name'].'&quot; '.__('Update Failed.', 'wp_eMember').'</p></div>';
                     }
                     else{                     	
                        $_SESSION['flash_message'] = '<div id="message" class="updated fade"><p>'.__('Member', 'wp_eMember').' &quot;'.
                        $fields['user_name'].'&quot; '.__('updated.', 'wp_eMember').'</p></div>';
                        echo '<script type="text/javascript">window.location = "admin.php?page=wp_eMember_manage";</script>';
                     }
                }
            }
            $editingrecord = (array)$editingrecord;
        }
    }

   $all_levels = dbAccess::findAll(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, ' id != 1 ', ' id DESC ');   
   include_once ('add_member_view.php');
}
?>