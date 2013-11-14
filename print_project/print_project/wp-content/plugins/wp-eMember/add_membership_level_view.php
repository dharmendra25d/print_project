<h3 style="margin-bottom: 5pt;">Add/Edit Membership Level</h3>
<table id="new_level" class="widefat wpm_nowrap">
   <tbody>
      <tr id="wpm_new_row" >
          <th  style="padding:5px 4px 8px;" scope="row">Membership Level Name</th>
         <td colspan="4"><input type="text" size="25" id="level_name_new_level" name="wpm_levels[new_level][name]"/>
         <br /><i> Name of the Membership Level (eg. Silver, Gold, Platinum)</i>
         </td>
      </tr>
      <tr  class="alternate">
         <th style="padding:5px 4px 8px;" scope="row">Default WordPress Role</th>
         <td colspan="4">
         	<select id="level_name_new_role" name="wpm_levels[new_level][role]">
         <?php
		 $roles = new WP_Roles();         
		 foreach($roles->role_names as $key =>$value)
		 { 
         ?>
         	
               <option <?php echo ($key=='subscriber')? "selected='selected'": ""; ?> value="<?php echo $key; ?>"><?php echo $value; ?></option>
               <?php }?>
            </select>            
            <br /><i> This option is only used if you are using the WordPress user integration feature. When used, the members signing up to this membership level will have the specified WordPress role.</i>
            </td>
      </tr>
      <tr >
         <th style="padding:5px 4px 8px;" scope="row">Redirect After Login</th>
         <td colspan="4"><input id="level1_name_new_loginredirect" type="text" size="70" value="" name="wpm_levels[new_level][loginredirect]"/>
         <br /><i>The URL of the main page for this membership level. Members who belong to this level will be redirected to this page after they login. Leave empty if you do not have a main page for this level.</i>
         </td>
      </tr>
      <tr class="alternate">
        <th style="padding:5px 4px 8px;" scope="row" >Global Access To</th>
         <td colspan="4"><label><input id="level_name_new_allpages" type="checkbox" name="wpm_levels[new_level][allpages]"/>Pages</label>
         <label><input id="level_name_new_allcategories" type="checkbox" name="wpm_levels[new_level][allcategories]"/>Categories</label>
         <label><input id="level_name_new_allposts" type="checkbox" name="wpm_levels[new_level][allposts]"/>Posts</label>
         <label><input id="level_name_new_allcomments" type="checkbox" name="wpm_levels[new_level][allcomments]"/>Comments</label>
         <br /><i>Globally turn on or off access to content (eg. posts, pages, comments) for this membership level here. Checking these checkboxes do not give the member access to all content. It simply allows you to customize the content protection (eg. access to certain posts or pages) via the <a href="admin.php?page=eMember_membership_level_menu&level_action=2" target="_blank">Manage Content Protection</a> menu</i>
         </td>
      </tr>
      <tr >
          <th style="padding:5px 4px 8px;" scope="row">Subscription Duration</th>
          <td colspan="4">
            <input type="text" size="3" id="level_name_new_expire" name="wpm_levels[new_level][expire]"/>
            <select id="level_name_new_calendar" name="wpm_levels[new_level][calendar]">
               <option value="Days">Days</option>
               <option value="Weeks">Weeks</option>
               <option value="Months">Months</option>
               <option value="Years">Years</option>
            </select>
            <label>
               <input type="checkbox" id="noexpire_button_new_level"  value="1" name="wpm_levels[new_level][noexpire]"/>
               No Expiry or Until Cancelled
            </label>
            <br /><i>When members sign up for this membership level their membership will be active for the duration of the subscription period unless they renew it (eg. by making another payment)</i>
         </td>
      </tr>

      <tr class="alternate">
         <th style="padding:5px 4px 8px;" scope="row">Autoresponder List/Campaign Name (optional)</th>
         <td colspan="4"><input id="level_name_new_campaign_name" type="text" size="70" value="" name="wpm_levels[new_level][campaign_name]"/>
         <br /><i>The name of the list/campaign where the members of this membership level will be signed up to (example: "listname@aweber.com" if you are using AWeber or "sample_marketing" if you are using GetResponse or "My Members" if you are using MailChimp). You can find the list/campaign name inside your autoresponder account. Only use this field if you want the members of this level to be signed up to a specific autoresponder list.</i>
         <br /><i>Make sure you enable your preferred autoresponder from the <a href="admin.php?page=eMember_settings_menu&tab=5" target="_blank">Autoresponder Settings</a> menu if you want to use this field.</i>
         </td>         
      </tr>  
          
   </tbody>
   </table>
<p class="submit">
   <input type="submit" id="add_update" name="add_new" value="Submit"/>
   <input type="button" value="Cancel" id="cancel_button" name="cancel" />
</p>