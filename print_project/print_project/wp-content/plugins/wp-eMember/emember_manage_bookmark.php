<div class="wrap">
      <form id="list_form" method="post">
    Select a membership level to manage content protection for that level or select "General Protection" to manage general content protection (content that can be viewed by non-members)<br/>
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

