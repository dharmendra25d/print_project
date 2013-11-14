<?php
function print_eMember_public_user_list($no_email=''){	
    $_SESSION['emember_no_email_shortcode'] = $no_email;
	global $emember_config;
    $emember_config = Emember_Config::getInstance();    
	$p  = $emember_config->getValue('eMember_enable_public_profile');
	if(!$p) return 'Public profile Listing is disabled';
   $member_table = WP_EMEMBER_MEMBERS_TABLE_NAME;
   $membership_table = WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE;
   $table_name = " $member_table LEFT JOIN $membership_table ON ".
                 " ($member_table.membership_level = $membership_table.id)";
   global $wpdb;
   $emember_user_count = $wpdb->get_row("SELECT COUNT(*) as count FROM" . $table_name . ' ORDER BY member_id');    
	ob_start();
    ?>
	<br/><br/><br/><div id="Pagination" class="pagination"></div>	
    <form action="javascript:void(0);" id="emember_user_search">
        <p>
            <input type="text" value="" name="e" title="Email" id="post-search-email"/>
            <select id="post_search_operator">
                <option value="and"> And </option>
                <option value="or"> Or </option>
            </select>
            <input type="text" value="" name="u" title="User Name" id="post-search-user"/>
            <input type="submit"  value="Search"/>
        </p>
    </form> 	    
	<table id="member_list" class="widefat">
        <thead>
            <tr>
                <th scope="col"><?php echo EMEMBER_USERNAME; ?>&nbsp;
                    <img sort="user_name" order="asc" class="wp-emember-sort-order" src="<?php echo WP_EMEMBER_URL?>/images/sort-asc-icon.gif" title="Sort by username"/>
                </th>
                <th scope="col"><?php echo EMEMBER_FIRST_NAME; ?></th>
                <th scope="col"><?php echo EMEMBER_LAST_NAME ?>&nbsp;
                    <img sort="last_name" order="asc" class="wp-emember-sort-order" src="<?php echo WP_EMEMBER_URL?>/images/sort-asc-icon.gif" title="Sort by last name"/>
                </th>
                <?php if(!$no_email): ?>
                    <th scope="col"><?php echo EMEMBER_EMAIL; ?>&nbsp;
                        <img sort="email" order="asc" class="wp-emember-sort-order" src="<?php echo WP_EMEMBER_URL?>/images/sort-asc-icon.gif" title="Sort by email"/>
                    </th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
	<div class="emember_apple_overlay" id="emember_post_preview_overlay">
		  <!-- the external content is loaded inside this tag -->
        <div class="emember_contentWrap"></div>
	</div>   
   <script type="text/javascript">
   /*<![CDATA[ */
   function drawContent(count, params){
       var counter = 0;
       if(params == undefined) params = {};       
       var itms_per_pg = parseInt(<?php $items_per_page = $emember_config->getValue('eMember_rows_per_page');
       $items_per_page = trim($items_per_page);
       echo (!empty($items_per_page)&& is_numeric($items_per_page))? $items_per_page:30;?>);         
       var $tbody = jQuery('#member_list tbody');              
       jQuery("#Pagination").pagination(count, {
          callback: function(i,container){
              $tbody.html('<?php echo emember_preloader(3); ?>');
             params['event']  = "emember_public_user_list_ajax";
             params['action'] = "emember_public_user_list_ajax";
             params['start']  = i*itms_per_pg;
             params['limit']  = itms_per_pg;
            var maxIndex = Math.min((i+1)*itms_per_pg, count);
            var target_url = '<?php echo admin_url( "admin-ajax.php" ); ?>';
            jQuery.get(target_url ,
                params,
                  function(data){
                      $tbody.html('');
                      data = jQuery(data);                      
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
   
      jQuery(document).ready(function(){
    	  jQuery('input[title!=""]').hint();
          var count = <?php echo $emember_user_count->count; ?>; 
          drawContent(count);
          var asc_img  = '<?php echo WP_EMEMBER_URL;?>'+ '/images/sort-asc-icon.gif';
          var desc_img = '<?php echo WP_EMEMBER_URL;?>'+ '/images/sort-desc-icon.gif';
          jQuery('.wp-emember-sort-order').click(function(){
              var target_url = '<?php echo admin_url( "admin-ajax.php" ); ?>';
              var $this = jQuery(this);
              var order = $this.attr('order');
              var sort  = $this.attr('sort');
              var user  = jQuery('#post-search-user').val();
              var email = jQuery('#post-search-email').val(); 
              var op    = jQuery('#post_search_operator').val();
              var params = {ord:order, sort:sort,o:op};
              if(user!=jQuery('#post-search-user').attr('title'))  params['u'] = user; 
              if(email!=jQuery('#post-search-email').attr('title'))params['e'] = email;
              var paramss = params;
              paramss['event']  = "emember_user_count_ajax";
              paramss['action'] = "emember_user_count_ajax";
                          
              jQuery.get(target_url ,paramss,
                     function(data){                    
                        drawContent(data.count, params);
                        if(order=="asc"){
                            $this.attr('order','desc');
                            $this.attr('src',desc_img);
                        }
                        else{
                            $this.attr('order','asc');
                            $this.attr('src',asc_img);                            
                        }                       
                     },
                     'json'
                  );               
          }); 
          jQuery('#emember_user_search').submit(function(e){              
	            e.prevenDefault;
	            var user  = jQuery('#post-search-user').val();
	            var email = jQuery('#post-search-email').val(); 
	            var op    = jQuery('#post_search_operator').val();
	            var q     = "u="+ user+ "&e="+email + "&o="+op;
	            if(user !=jQuery('#post-search-user').attr('title') ||email !=jQuery('#post-search-email').attr('title'))
	            {
	            	var target_url = '<?php echo admin_url( "admin-ajax.php" ); ?>';
	                jQuery.get(target_url ,
                        {  event: "emember_user_count_ajax",action: "emember_user_count_ajax",u:user, e:email,o:op},
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
   $content = ob_get_contents();
   ob_end_clean();
   return $content;
}
?>