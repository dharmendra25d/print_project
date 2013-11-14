<form id="emember_form" method="post">
<?php include_once('membership_level_list_view.php');?>
</form>
<div id="hidden_content" style="display:none" >
<?php include_once('add_membership_level_view.php');?>
</div>
<script type="text/javascript">
   function checkExpiry(form)
   {
      jQuery(form).find('input[id^=noexpire_button_]').
         each(function(i){
            jQuery(this).click(function(){
            var parent = jQuery(this).parent().parent();
            jQuery(this).attr('checked') ?
               parent.find('input:text').attr('disabled', 'disabled') :
               parent.find('input').removeAttr('disabled');
            jQuery(this).attr('checked') ?
               parent.find('select').attr('disabled', 'disabled') :
               parent.find('select').removeAttr('disabled');
            })
         });
   }
   function showForm()
   {
      jQuery('#add_update').attr('name','add_new');
      jQuery('#add_update').val('Submit');
      var levelForm = jQuery(jQuery('#hidden_content').html()).clone();
      jQuery('#hidden_content').html('');
      var levelList = jQuery(jQuery('#emember_form').slideUp().html()).clone();
      jQuery('#emember_form').html('');
      jQuery(levelList).appendTo('#hidden_content');
      jQuery('#emember_form').append(jQuery(levelForm)).slideDown();
      jQuery('#cancel_button').click(showList);
      jQuery('#level_name_msg').html('');
      jQuery("#level_name_new_allpages").attr("checked", "checked");
      jQuery("#level_name_new_allcategories").attr("checked", "checked");
      jQuery("#level_name_new_allposts").attr("checked", "checked");
      jQuery("#level_name_new_allcomments").attr("checked", "checked");
      checkExpiry('#emember_form');
      jQuery("#level_name_new_level").keyup(function(){
        var $_this = this;
        var user_name = jQuery(this).val();
        if(user_name!="" &&(user_name.length>4))
        {
           available = false;
           var target_url = '<?php echo admin_url( "admin-ajax.php" ); ?>';
           jQuery.get(target_url ,{"event":"check_level_name","action":"check_level_name","alias":jQuery(this).val()},
                 function(data)
                 {
                     var msg_obj = jQuery("#level_name_msg");
                     if(!msg_obj.length)
                     {
                        msg_obj = jQuery('<span id= "level_name_msg"/>');
                        jQuery($_this).after(msg_obj);
                     }
                     available = data.status_code;
                     if(data.status_code)
                     {
                        msg_obj.css("color", "green").html(data.msg);
                     }
                     else
                     {
                         msg_obj.css("color", "red").html(data.msg);
                     }
                  },
            "json");
         }
      });
   }
   function bindActions()
   {
      jQuery('#e_membership_levels').find('a').each(function(i){
         var label = jQuery(this).html();
         if(label == 'Edit')
         {
            jQuery(this).click(function(){
               showForm();
               jQuery('<input name="wpm_levels[new_level][id]" id="hidden_id" type="text" />').
               val(jQuery(this).attr('id')).hide().appendTo(jQuery('#emember_form'));
               jQuery('#add_update').attr('name','update_info');
               jQuery('#add_update').val('Update Info');
               var id = jQuery(this).attr('id');
               jQuery('#level_name_new_level').val(jQuery('#alias_'+id).html());
               var role = jQuery.trim(jQuery('#role_'+id).html());
               var roleId = new Array();
               <?php
                   $roles = new WP_Roles();
                   $f = true;
                   foreach($roles->role_names as $key =>$value)
                   {                   	 
                   	  echo 'roleId["' . $value . '"] = "' .$key . '"' . "\n"; 
                   } 
               ?>;
               jQuery('#level_name_new_role').val(roleId[role]);
               jQuery('#level1_name_new_loginredirect').val(jQuery.trim(jQuery('#redirect_'+id).html()));
               jQuery('#level_name_new_campaign_name').val(jQuery.trim(jQuery('#campaign_name_'+id).html()));               
               if(jQuery('#post_'+id).html()=='X') jQuery('#level_name_new_allposts').removeAttr('checked'); else jQuery('#level_name_new_allposts').attr('checked','checked');
               if(jQuery('#cat_'+id).html()=='X') jQuery('#level_name_new_allcategories').removeAttr('checked'); else jQuery('#level_name_new_allcategories').attr('checked','checked');
               if(jQuery('#page_'+id).html()=='X') jQuery('#level_name_new_allpages').removeAttr('checked'); else jQuery('#level_name_new_allpages').attr('checked','checked');
               if(jQuery('#comment_'+id).html()=='X') jQuery('#level_name_new_allcomments').removeAttr('checked'); else jQuery('#level_name_new_allcomments').attr('checked','checked');
               var subscription = jQuery.trim(jQuery('#expiry_'+id).html());
               if(subscription=='No Expiry or Until Cancelled')
               {
                  jQuery('#noexpire_button_new_level').attr('checked', 'checked');
                  jQuery('#level_name_new_calendar').attr('disabled','disabled');
                  jQuery('#level_name_new_expire').attr('disabled','disabled');
               }
               else
               {
                  subscription = subscription.split(' ');
                  jQuery('#noexpire_button_new_level').removeAttr('checked');
                  jQuery('#level_name_new_calendar').removeAttr('disabled').val(subscription[1]);
                  jQuery('#level_name_new_expire').removeAttr('disabled').val(subscription[0]);
               }
            });
         }
         else if (label=='Delete')
         {
            jQuery(this).click(function(){
               top.document.location = 'admin.php?page=eMember_membership_level_menu&delete='+jQuery(this).attr('id');
               return false;
            });
            jQuery(this).confirm({msg:"",timeout:5000});
         }
      });
   }
   function showList()
   {
      jQuery('#hidden_id').remove();
      var levelList = jQuery(jQuery('#hidden_content').html()).clone();
      jQuery('#hidden_content').html('');
      var levelForm = jQuery(jQuery('#emember_form').slideUp().html()).clone();
      jQuery('#emember_form').html('');
      jQuery(levelForm).appendTo('#hidden_content');
      jQuery('#emember_form').append(jQuery(levelList)).slideDown();
      jQuery('#add_new').click(showForm);
      checkExpiry('#emember_form');
      bindActions();
   }
   jQuery(document).ready(function(){
      available = true;
      bindActions();
      jQuery('#e_membership_levels').show('slow');
      jQuery('#add_new').click(showForm);
      checkExpiry('#emember_form');
      jQuery('#emember_form').submit(function(){
         var ok = true;
         jQuery(this).find('input[id^=noexpire_button_]').
            each(function(i){
               if(!jQuery(this).attr('checked'))
               {

                  var el = jQuery(this).parent().parent().find('input:text');
                  if(el.val()==''||isNaN(el.val()))
                  {
                     el.css("background", "red").
                        focus(function(){jQuery(this).css('background','')});
                     ok = false;
                  }
               }
            });

            jQuery(this).find('input[id^="level_name_"]').
               each(function(i){
                  if(jQuery(this).attr('id') == 'level_name_new_campaign_name'){
                      
                  }
                  else if((jQuery(this).val()=='')&&(!jQuery(this).attr('disabled')))
                  {
                     jQuery(this).css('background', 'red');
                     ok = false;
                  }
               });
         return ok&&available;
      });
   });
</script>