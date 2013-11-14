<?php
include_once('admin_includes.php');
include_once('eMember_db_access.php');
include_once(ABSPATH.WPINC.'/pluggable.php');
function wp_eMember_membership_level()
{
    echo '<div class="wrap"><h2>WP eMembers - Manage Access Levels v'.WP_EMEMBER_VERSION.'</h2>';
    echo check_php_version();
    echo eMember_admin_submenu_css();
    include_once('menu_view.php');
    switch ($_GET['level_action'])
    {
        case '2': content_protection();break;
        default:   manage_access_levels();break;
    }
	echo '</div>';
}

function content_protection()
{
    if(isset($_POST['submit']))
    {
       $ids = array();
       $debookmarked = array();
       $fields = array();
       $page_ids = explode('-', $_POST['page_ids']);
       if(count($_POST['checked']))
       foreach($_POST['checked'] as $id=>$value)
       {
          array_push($ids, $id);
       }
       if(count($_POST['bookmark']))
       foreach($_POST['bookmark'] as $id=>$value)
       {
          array_push($debookmarked, $id);
       }
       
       $level = dbAccess::find(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, ' id = '.$_POST['wpm_content_level'], ' id DESC ');   
       switch($_POST['content_type'])
       {
           case 'Posts':
               $oldIds = unserialize( $level->post_list );
               if(!is_bool($oldIds))
               {
               	   $add = array_diff($ids, $oldIds);
               	   $sub = array_diff($page_ids, $ids);                                                                                                                  
               	   $rest = array_diff($oldIds, $sub);
               	   $ids = array_merge($add, $rest);
               	   $ids = array_unique($ids);
               }
               $ids = serialize($ids);
               $fields = array('post_list'=>$ids);
               
               $current_bookmarks = unserialize( $level->disable_bookmark_list );
               
               if(!is_bool($current_bookmarks))
               {   $oldmarks = empty($current_bookmarks['posts'])?array():$current_bookmarks['posts'];
               	   $add = array_diff($debookmarked, $oldmarks);
               	   $sub = array_diff($page_ids, $debookmarked);                                                                                                                  
               	   $rest = array_diff($oldmarks, $sub);
               	   $debookmarked = array_merge($add, $rest);
               	   $debookmarked = array_unique($debookmarked);
               }
               $current_bookmarks['posts'] = $debookmarked;
               $debookmarked = serialize($current_bookmarks);
               $fields['disable_bookmark_list']= $debookmarked;
               
               break;
           case 'Pages':
               $oldIds = unserialize( $level->page_list );
               if(!is_bool($oldIds))
               {
               	   $add = array_diff($ids, $oldIds);
               	   $sub = array_diff($page_ids, $ids);                                                                                                                  
               	   $rest = array_diff($oldIds, $sub);
               	   $ids = array_merge($add, $rest);
               	   $ids = array_unique($ids);
               }
               $ids = serialize($ids);
               $fields = array('page_list'=>$ids);
               
               $current_bookmarks = unserialize( $level->disable_bookmark_list );
               
               if(!is_bool($current_bookmarks))
               {   $oldmarks = empty($current_bookmarks['pages'])?array():$current_bookmarks['pages'];
               	   $add = array_diff($debookmarked, $oldmarks);
               	   $sub = array_diff($page_ids, $debookmarked);                                                                                                                  
               	   $rest = array_diff($oldmarks, $sub);
               	   $debookmarked = array_merge($add, $rest);
               	   $debookmarked = array_unique($debookmarked);
               }
               $current_bookmarks['pages'] = $debookmarked;
               $debookmarked = serialize($current_bookmarks);
               $fields['disable_bookmark_list']= $debookmarked;
               
               break;
           case 'Comments':
               $oldIds = unserialize( $level->comment_list );
               if(!is_bool($oldIds))
               {
               	   $add = array_diff($ids, $oldIds);
               	   $sub = array_diff($page_ids, $ids);                                                                                                                  
               	   $rest = array_diff($oldIds, $sub);
               	   $ids = array_merge($add, $rest);
               	   $ids = array_unique($ids);
               }
               $ids = serialize($ids);
               $fields = array('comment_list'=>$ids);
               break;
           case 'Categories':
               $oldIds = unserialize( $level->category_list );
               if(!is_bool($oldIds))
               {
               	   $add = array_diff($ids, $oldIds);
               	   $sub = array_diff($page_ids, $ids);                                                                                                                  
               	   $rest = array_diff($oldIds, $sub);
               	   $ids = array_merge($add, $rest);
               	   $ids = array_unique($ids);
               }
               $ids = serialize($ids);
               $fields = array('category_list'=>$ids);
               break;
           default :
               break;
       }
       $ret = dbAccess::update(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE,' id = ' . $_POST['wpm_content_level'], $fields);
       if($ret===false)
	       echo '<div id="message" style="color:red;" class="updated fade"><p>Failed to update.</p></div>';
	   else if ($ret === 0)
	       echo '<div id="message" style="color:red;" class="updated fade"><p>Nothing to update.</p></div>';
	   else
	       echo '<div id="message" class="updated fade"><p>Info Updated.</p></div>';	   
   }
   $levels = dbAccess::findAll(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, ' id != 1 ', ' id DESC ');   
   include_once('content_protection_view.php');
}
function manage_access_levels()
{
   global $wpdb;
   if(isset($_POST['add_new']))
   {
      $alias = $wpdb->escape($_POST['wpm_levels']['new_level']['name']);
      $role  = $_POST['wpm_levels']['new_level']['role'];
      $login_redirect = $wpdb->escape($_POST['wpm_levels']['new_level']['loginredirect']);
      $campaign_name = $wpdb->escape($_POST['wpm_levels']['new_level']['campaign_name']);
      if(isset($_POST['wpm_levels']['new_level']['noexpire'])&&($_POST['wpm_levels']['new_level']['noexpire']==1))
      {
         $subscription_period = 0;
         $subscription_unit   = null;
      }
      else
      {
         $subscription_period = $wpdb->escape($_POST['wpm_levels']['new_level']['expire']);
         $subscription_unit = $wpdb->escape($_POST['wpm_levels']['new_level']['calendar']);
      }
      $permissions = 0;
      $permissions += isset($_POST['wpm_levels']['new_level']['allpages'])? 8 : 0;
      $permissions += isset($_POST['wpm_levels']['new_level']['allposts'])? 4: 0;
      $permissions += isset($_POST['wpm_levels']['new_level']['allcomments'])? 2: 0;
      $permissions += isset($_POST['wpm_levels']['new_level']['allcategories'])? 1: 0;
      $fields['role'] = $role;
      $fields['alias'] = $alias;
      $fields['permissions'] = $permissions;
      $fields['loginredirect_page'] = $login_redirect;
      $fields['subscription_period'] = $subscription_period;
      $fields['subscription_unit'] = $subscription_unit;
      $fields['campaign_name '] = $campaign_name ;

      $ret = dbAccess::insert(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, $fields);
      if($ret===false)
      {
		  echo '<div id="message" style="color:red;" class="updated fade"><p>Membership Level &quot;'.$_POST['wpm_levels']['new_level']['name'].'&quot; couldn\'t be created due to error.</p></div>';
      }
      else
      {
		  echo '<div id="message" class="updated fade"><p>Membership Level &quot;'.$_POST['wpm_levels']['new_level']['name'].'&quot; created.</p></div>';      	
      }     
   }
   if(isset($_POST['update_info']))
   {
       foreach($_POST['wpm_levels'] as $id=>$wp_level)
       {
            $alias = $wpdb->escape($wp_level['name']);
            $role  = $wp_level['role']; 
            $login_redirect = $wpdb->escape($wp_level['loginredirect']);
            $campaign_name = $wpdb->escape($wp_level['campaign_name']);
            if(isset($wp_level['noexpire'])&&($wp_level['noexpire']==1))
            {
               $subscription_period = 0;
               $subscription_unit   = null;
            }
            else
            {
               $subscription_period = $wpdb->escape($wp_level['expire']);
               $subscription_unit = $wpdb->escape($wp_level['calendar']);
            }
            $permissions  = 0;
            $permissions += isset($wp_level['allpages'])? 8 : 0;
            $permissions += isset($wp_level['allposts'])? 4: 0;
            $permissions += isset($wp_level['allcomments'])? 2: 0;
            $permissions += isset($wp_level['allcategories'])? 1: 0;
            $fields['role'] = $role;
            $fields['alias'] = $alias;
            $fields['permissions'] = $permissions;
            $fields['loginredirect_page'] = $login_redirect;
            $fields['subscription_period'] = $subscription_period;
            $fields['subscription_unit'] = $subscription_unit;
            $fields['campaign_name'] = $campaign_name;
            /**
             * @todo update role based on flags.
             * */
            $ret = dbAccess::update(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, ' id = ' . $wp_level['id'], $fields);
            
            if($ret === false)
                echo '<div id="message" style="color:red;" class="updated fade"><p>Membership Level Update Failed..</p></div>';
            else
                echo '<div id="message" class="updated fade"><p>Membership Level Updated.</p></div>';                                    
       }       
   }

   if(isset($_GET['delete']))
   {
       $ret = dbAccess::delete(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, ' id=' . $_GET['delete']);
       if($ret===false)       
          echo '<div id="message" style="color:red;" class="updated fade"><p>Membership Level Couldn\'t be deleted due to error.</p></div>';
       else if($ret === 0)
          echo '<div id="message" style="color:red;" class="updated fade"><p>Nothing to delete.</p></div>';
       else
          echo '<div id="message" class="updated fade"><p>Membership Level Deleted.</p></div>';
   }

   $all_levels = dbAccess::findAll(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, ' id != 1 ', ' id DESC ');
   include_once('manage_access_levels_view.php');
}
?>