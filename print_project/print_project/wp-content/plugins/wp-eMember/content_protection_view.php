<div class="wrap">
      <form id="list_form" method="post">
    <div class="eMember_yellow_box"> 
    <p>Select a membership level to manage content protection for that level or select "General Protection" to manage general content protection (content that can be viewed by non-members)<br/></p>
    <p>You can also set the protection settings of a post or page when you edit it in the WordPress editor (look for the "eMember Protection Options" section)</p>
    </div>
    
    <blockquote>
         <select id="wpm_content_level"  name="wpm_content_level">
            <option value="-1" selected="selected">Select...</option>
            <option value="1">General Protection</option>
           <?php foreach ($levels as $level)
           {
           ?>
           <option  value="<?php echo $level->id; ?>"><?php echo $level->alias; ?></option>
           <?php } ?>
         </select>
      </blockquote>
   <!-- </form>-->
   <div id="secondary_nav" style="display:none;">
	 <h2 style="font-size: 18px;"><a name="specific"></a><span id="label_str">Manage Content Protection</span> &raquo; <span id="head">Posts</span></h2>
		<ul class="eMemberSubMenu">
			<li><a href="#">Comments</a></li>
			<li><a href="#">Categories</a></li>
			<li><a href="#">Pages</a></li>
			<li class="current"><a href="#">Posts</a></li>
		</ul>
    <div id="submit_button" style="display:none;">
      <div class="tablenav">
         <span id="button_legend">Select which content to protect:</span> 
         <input type="submit" name="submit" value="Set Protection" class="button-secondary"/> <span id="ajax_msg"></span>
         </div>
        <div id="Pagination" class="pagination">        </div>
    </div>
         <table id="wpm_post_page_table" class="widefat">
         </table>
   </div>
         </form>
      </div>
<div class="emember_apple_overlay" id="emember_post_preview_overlay">

	<!-- the external content is loaded inside this tag -->
	<div class="emember_contentWrap"></div>

</div>
<script type="text/javascript">
   Array.prototype.in_array = function(p_val) {
      for(var i = 0, l = this.length; i < l; i++) {
         if(this[i] == p_val) {
            return true;
         }
      }
      return false;
   }
   var $j = jQuery.noConflict();
      
   function bindCheckBoxes()
   {
	$j('#wpm_post_page_table thead .emember_select').click(function(){
	    var checked = this.checked;
	     $j('#wpm_post_page_table tbody .emember_select').each(function(){
		     this.checked = checked;
	  });
       });
   
    }
   function processRequest(parent, target){
      if(target.nodeName!='A') return;
      var postHeadings    = ['Disable Bookmark', 'Date', 'Title','Author','Categories','Type', 'Status'];
      var categoryHeadings= ['Name', 'Description','Posts'];
      var pageHeadings    = ['Disable Bookmark','Date','Title', 'Author','Status'];
      var commentHeadings    = ['Date', 'Author', 'Contents'];
      $j('#submit_button').show('slide');
      var clicked  = $j(target).html();
      $j('#head').html(clicked);
      $j(parent).find('li').each(function(i){$j(this).removeClass('current');});
      var $thead = $j('<thead />');
      var $tbody = $j('<tbody />');
      var $tr    = $j('<tr />').appendTo($thead);
      $j('<th scope="row" class="check-column" />').
         append('<input class="emember_select" type="checkbox" />').removeAttr('checked').appendTo($tr);
//      $j('<th scope="row" class="check-column" />').
//         append('<input class="emember_bookmark" type="checkbox" />').removeAttr('checked').appendTo($tr);
      $j.cookie('selected_tab', $j(target).html());
      switch($j(target).html()){
         case 'Comments':
            loadContent('Comments',commentHeadings, $tr,$tbody);
            break;
         case 'Categories':
            loadContent('Categories',categoryHeadings, $tr,$tbody);
            break;
         case 'Pages':
            loadContent('Pages',pageHeadings, $tr,$tbody);
            break;
         case 'Posts':
            loadContent('Posts',postHeadings, $tr,$tbody);
            break;
         default:
            break;
      }
      $j('#wpm_post_page_table').html('');
      $thead.appendTo($j('#wpm_post_page_table'));
      $tbody.appendTo($j('#wpm_post_page_table'));
      $j(target).parent().addClass('current');
   }
   function loadContent(tab,headings, trh,tbody){
      $j('#ajax_msg').css({'background': 'red','color':'white'}).html('Loading....');
      var target_url = '<?php echo admin_url( "admin-ajax.php" ); ?>';
      $j.get(target_url,
         { action: "access_list_ajax",event: "access_list_ajax",level_content:tab,
           level_type: $j('#wpm_content_level').val()},
            function(data){
               $j('#ajax_msg').html('');
               buildHead(headings, trh);
               switch(tab)
               {
                  case 'Comments':
                     buildContent( tbody, data.comment_list,data.count, 'comments');
                     break;
                  case 'Posts':
                     buildContent( tbody, data.post_list,data.count, 'posts', data.disable_bookmark);
                     break;
                  case 'Pages':
                     buildContent( tbody, data.page_list,data.count, 'pages', data.disable_bookmark);
                     break;
                  case 'Categories':
                     buildContent(tbody, data.category_list,data.count, 'categories');
                     break;
               }
         },
         'json'
     );
   }
   function buildHead(titles, $tr){
      for ( k in titles){
	if(typeof titles[k] === "string")
           $j('<th scope="row" />').html(titles[k]).appendTo($tr);
      }
   }
   function buildContent($tbody, $permissions,count, type, $disable_bookmark){
      var counter = 0;
      var itms_per_pg = parseInt(<?php global $emember_config; $items_per_page = $emember_config->getValue ('eMember_rows_per_page');
                                   $items_per_page = trim($items_per_page);
                                   echo (!empty($items_per_page)&& is_numeric($items_per_page))? $items_per_page:30;?>);      
      $j("#Pagination").pagination(count, {
         callback: function(i,container){
            $tbody.html('Loading...........');
            var maxIndex = Math.min((i+1)*itms_per_pg, count);
            //
           var target_url = '<?php echo admin_url( "admin-ajax.php" ); ?>';
           $j.get(target_url,
               { action: "item_list_ajax",event: "item_list_ajax",type:type,start:i*itms_per_pg,limit:itms_per_pg},
                 function(data){
	                  $tbody.html('');
	                  var page_ids = '';
	                  for( var k in data){	                  	
		                  if($j.isFunction(data[k])) continue;
                       var cls = (counter%2)? 'class="alternate"' : '';
                       var $tr2    = $j('<tr  '+cls+' />').appendTo($tbody);
                       for (l in data[k]){
                          if(data[k][l] == 'array') continue;
                          if(l=='ID'){                                                  	     
                             $j('<th scope="row" class="check-column" />').
                                append('<input class="emember_select" type="checkbox" '+(($j.inArray(parseInt(data[k][l]),$permissions)>-1)?
                                'checked="checked"':'') +' name="checked['+data[k][l]+']" />').removeAttr('checked').
                                appendTo($tr2);
                                page_ids += data[k][l] + '-';
                                if(type=='posts'||type=='pages')
                                {
                                    $j('<th scope="row" class="check-column" />').
                                    append('<input class="emember_bookmark" type="checkbox" '+(($j.inArray(parseInt(data[k][l]),$disable_bookmark)>-1)?
                                    'checked="checked"':'') +' name="bookmark['+data[k][l]+']" />').removeAttr('checked').
                                    appendTo($tr2);                           
                                }                                                       
                          }
                          else if (l=='title'){
                              $j('<td />').
                              html('<a class="emember_post_preview" href="'+data[k]['ID']+'">' + data[k][l] + '</a>').
                              appendTo($tr2);                              
                          }

                          else{
                             $j('<td />').
                                html(data[k][l]).
                                appendTo($tr2);
                          }
                       }
                       counter++;
                    }
	                $j(".emember_post_preview").overlay({
	            		mask: '#E6E6E6',
	            		effect: 'apple',
	            		target:$j('#emember_post_preview_overlay'),
	            		onBeforeLoad: function() {
	            			var wrap = this.getOverlay().find(".emember_contentWrap");
	            			var params = {'action':'get_post_preview','id':this.getTrigger().attr("href")};
	            			wrap.load(target_url,params);
	            		}

	            	});
 
                    var p = $j('#page_ids');
                    if(p.length<1) p = $j('<input type = "hidden" name="page_ids" id="page_ids" />');                       
                    p.val(page_ids.substr(0, page_ids.length-1)). appendTo($j('#list_form'));
                    bindCheckBoxes();
                 },
                 'json'
             );            
         },
         num_edge_entries: 2,
         num_display_entries: 10,
         items_per_page: itms_per_pg
      });      
   }   
   function updateLevel(target){
      var buttonLabel  = '';
      var contentLabel = '';
      if(target==1){
         buttonLabel = 'Set Protection';
         contentLabel = 'Manage Content Protection';
         legendLabel  = 'Select which content to protect:'
      }
      else{
         buttonLabel = 'Grant Access';
         contentLabel = 'Manage Content Access';
         legendLabel  = 'Select which content to grant access to for the selected level:';
      }
      $j('#list_form .button-secondary').each(function(){
         $j(this).val(buttonLabel);
      });
      $j('#button_legend').html(legendLabel);
      $j('#label_str').html(contentLabel);
   }
   $j(document).ready(function(){
         var selectedLevel = $j.cookie('selected_level')?$j.cookie('selected_level'):-1;
         var selectedTab  = $j.cookie('selected_tab');
         $j('#wpm_content_level').val(selectedLevel);
         if(selectedLevel!=-1){
            updateLevel(selectedLevel);
            $j('#secondary_nav').show('slide');
            var parent = $j('#list_form .eMemberSubMenu');
            var target = '';
            if(selectedTab)
               parent.find('li a').each(function(i){if($j(this).html()==selectedTab)target = this;});
            else
               target = parent.find('li.current').children('a').get(0);
            processRequest(parent.get(0), target);
         }
         $j('#list_form .eMemberSubMenu').click(function(e){
            processRequest(this,e.target);
      });
      $j('#list_form').submit(function(){
         $j('<input type="text" name="content_type" />').hide().
            val($j('#list_form .eMemberSubMenu').find('li.current').children('a').html()).
            appendTo($j(this));
         return true;
      });//
      $j("#wpm_content_level").change(function(e){
         updateLevel($j(this).val());
      });

      $j('#wpm_content_level').change(function(e){
         $j.cookie('selected_level', $j(this).val());
         if($j(this).val()!=-1)
         {
            $j('#secondary_nav').show('slide');
            var parent = $j('#list_form .eMemberSubMenu');
            var target = parent.find('li.current').children('a').get(0);
            processRequest(parent.get(0), target);
         }
         else
            $j('#secondary_nav').hide('slide');
      });
   });
</script>