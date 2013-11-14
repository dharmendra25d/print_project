<?php
include_once('admin_includes.php');
include_once('eMember_db_access.php');
include_once('eMember_email_settings_menu.php');
include_once('recaptcha_settings.php');
include_once('emember_pages_settings.php');
include_once('emember_general_settings.php');
include_once('eMember_auto_responder_settings.php');
include_once('eMember_custom_field_settings.php');

function wp_eMember_settings()
{
	echo '<div class="wrap"><h2>WP eMembers - Settings v'.WP_EMEMBER_VERSION.'</h2>';
	echo check_php_version();
	echo eMember_admin_submenu_css();
   $current = (isset($_GET['tab']))? $_GET['tab']: 1;
   ?>
   <ul class="eMemberSubMenu">
   <li <?php echo ($current==1) ? 'class="current"' : ''; ?> ><a href="admin.php?page=eMember_settings_menu">General Settings</a></li>
   <li <?php echo ($current==4) ? 'class="current"' : ''; ?> ><a href="admin.php?page=eMember_settings_menu&tab=4">Pages/Forms Settings</a></li>   
   <li <?php echo ($current==2) ? 'class="current"' : ''; ?> ><a href="admin.php?page=eMember_settings_menu&tab=2">Email Settings</a></li>
   <li <?php echo ($current==3) ? 'class="current"' : ''; ?> ><a href="admin.php?page=eMember_settings_menu&tab=3">reCAPTCHA&trade; Settings</a></li>
   <li <?php echo ($current==5) ? 'class="current"' : ''; ?> ><a href="admin.php?page=eMember_settings_menu&tab=5">Autoresponder Settings</a></li>    
   <li <?php echo ($current==6) ? 'class="current"' : ''; ?> ><a href="admin.php?page=eMember_settings_menu&tab=6">Custom Field Settings</a></li>
   </ul>
   <?php

   switch ($_GET['tab'])
   {
       case '2':
           wp_eMember_email_settings();
           break;
       case '3':
           recaptcha_settings();
           break;
       case '4':
       	   emember_pages_settings();
       	   break;       
       case '5':
       	   emember_auto_responder_settings();
       	   break;          	       
       case '6':
       	   emember_custom_field_settings();
       	   break;          	              	   
       default:
           wp_eMember_general_settings();
           break;
   }
	echo '</div>';
}
?>