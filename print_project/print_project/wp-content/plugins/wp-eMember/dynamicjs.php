<?php //add the dynamic js stuff if specified in the settings ?>
<script type="text/javascript">
<?php $enable_bookmark = $emember_config->getValue('eMember_enable_bookmark');
if($enable_bookmark):?>    
jQuery(document).ready(function($){
	$(".ememberbookmarkbutton").find("a").each(function(i){
		$(this).click(function(e){
			e.preventDefault();
	    		  var id = jQuery(this).attr("href");			  
	    		  if(!id) return;
	    		  var $this = this;
	    	      $.get('<?php echo admin_url( "admin-ajax.php" ); ?>',
	    	         { event: "bookmark_ajax",action: "bookmark_ajax",id: id},
	    	            function(data){
	    	        	 $($this).parent().html(data.msg);
	    	         	},
	    	         "json"
	    	     );			
			});
		});     
});    
<?php endif; ?>
<?php
$password_reset_page = $emember_config->getValue('eMember_password_reset_page');
if(empty($password_reset_page)):  
?>  
jQuery(document).ready(function($){
    emember_forget_pass_trigger = '';    
    $forgot_pass_overlay = $(".forgot_pass_link").click(function(e){
        $("#wp_emember_email_mailMsg").html("").hide();
        $("#wp_emember_email_mailForm").show();   
        $('.eMember_text_input').val("");     
        }).overlay({
        mask: {
            color: '#ebecff'/*'darkred'*//*'#E7E7E7'*/,
            loadSpeed: 200,
            top: '30%',
            opacity: 0.9
        },
        api:true,
        onBeforeLoad:function(){
            emember_forget_pass_trigger = this.getTrigger();
        },    
        closeOnClick: false
    });
        	
  	$("#wp_emember_mailSendForm").live('submit',function(e){
       var $this = this;
       var divs = jQuery($this).parent().parent().find("div");
       var emailId = jQuery($this).find("input").eq(0).val();
       if(emailId=="")
          return;
       divs.eq(1).hide();
       divs.eq(0).html("").append(jQuery('<h3>Please Wait...</h3>')).show();
       jQuery.get( '<?php echo admin_url( "admin-ajax.php" );?>',{"event":"send_mail","action":"send_mail","email":emailId},    	       
             function(data){
                 divs.eq(0).html("").append(jQuery('<h3>'+data.msg+'</h3>'));                 
                 setTimeout("emember_forget_pass_trigger.overlay().close()", 1000);                                  
             },
       "json");
       e.preventDefault();
    });
});
<?php endif;?>    
<?php if($emember_config->getValue('eMember_enable_fancy_login') == '1'):?>    
//fancy login start//    
jQuery(document).ready(function($){
	$('#emem_ui_close').click(function(e){
		$(this).parent().hide('slow');
		$('#marker').html("");
	});
	$('.emember_fancy_login_link').click(
			function(e){
				var targetId = $(e.target).addClass('activeLink').attr('id');
				var alreadyOpened = $('#marker');
				var menu = $('#signin_menu'); 
                var offset = $(e.target).offset();
				if(!alreadyOpened.html()){
					alreadyOpened.html(targetId);
					menu.css({'left':offset.left +'px','top':(offset.top+20) +'px'}).show('slow');
				}
				else if (targetId !=alreadyOpened.html()){
					alreadyOpened.html(targetId);
					menu.hide().css({'left':offset.left +'px','top':(offset.top+20) +'px'}).show('slow');
				}else if(targetId ==alreadyOpened.html()){
                    $(e.target).removeClass('activeLink');
					alreadyOpened.html("");
					menu.hide('slow');
				}				
		    }
		);
     $('#emember_fancy_login_form').submit(function(){
         var msg = '<?php echo EMEMBER_PLEASE ?>'+' <?php echo EMEMBER_WAIT;?> ...';
         $('#emember_fancy_log_msg').css('color','black').html(msg+ '<br/>')
         $.post('<?php echo admin_url( "admin-ajax.php" );?>', 
                $(this).serialize(), function (result){
                    if(result.status)
                        window.location.reload();
                    else
                        $('#emember_fancy_log_msg').css('color', 'red').html(result.msg + '<br/>');
                }, 'json');
         return false;
     });       
});  
//fancy login end//
<?php elseif ($emember_config->getValue('eMember_enable_fancy_login') == '2'): ?>
<!--
jQuery(document).ready(function($) {
     $('#loginForm').live('submit',function(){
         var msg = '<?php echo EMEMBER_PLEASE ?>'+' <?php echo EMEMBER_WAIT;?> ...';
         $('#emember_fancy_log_msg').css('color','black').html(msg+ '<br/>')
         $.post('<?php echo admin_url( "admin-ajax.php" );?>', 
                $(this).serialize(), function (result){
                    if(result.status)
                        window.location.reload();
                    else
                        $('#emember_fancy_log_msg').css('color', 'red').html(result.msg + '<br/>');
                }, 'json');
         return false;
     });    
   $emember_fancy_login_v2 = $("#emember_fancy_login_v2").overlay({	   
	       top: '30%',
	       oneInstance: false,
	       api:true,
	       mask: {
	           color: '#ebecff',
	           loadSpeed: 200,
	           opacity: 0.9
	       },           
	       closeOnClick: true,
	       closeOnEsc:true,/*,
	       load: true	   */
           onLoad:function(){
               var $this = this;
               $('#emember_forgot_pass').click(function(){
                   emember_forget_pass_trigger = $emember_fancy_login_v2.getTrigger();
                   $this.close();                   
                   var $overlay = $this.getOverlay();
                    $("#wp_emember_email_mailMsg").html("").hide();
                    $("#wp_emember_email_mailForm").show();   
                    $('.eMember_text_input').val("");
                    $('#emember_forgot_pass_prompt_close_btn').live('click',function(){
                        $emember_fancy_login_v2.close();               
                    });              
                   $overlay.data('forgot_pass',$overlay.html());
                   $('#emember_forgot_pass_prompt').html();  
                   $overlay.html($('#emember_forgot_pass_prompt').html());                   
                   var timeid = setTimeout(function(){
                       $this.load();
                       clearTimeout(timeid);
                   }, 200)
                   
               });
           }
	   }); 
       /*$('#emember_forgot_pass1').live('click',function(){
           var $overlay = $emember_fancy_login_v2.getOverlay();
           //console.log($overlay);
           emember_forget_pass_trigger = $emember_fancy_login_v2.getTrigger();
            $("#wp_emember_email_mailMsg").html("").hide();
            $("#wp_emember_email_mailForm").show();   
            $('.eMember_text_input').val("");
            $('#emember_forgot_pass_prompt_close_btn').live('click',function(){
               // $emember_fancy_login_v2.close();               
            });
           $overlay.data('forgot_pass',$overlay.html());
           $('#emember_forgot_pass_prompt').html();
            
           $overlay.html('sssssssssssss');           
           $emember_fancy_login_v2.load();
       });*/
       
       $('.emember_fancy_login_link').click(function(){
           var $overlay = $emember_fancy_login_v2.getOverlay();
           var $prompt =$overlay.data('forgot_pass');
           if($prompt)$overlay.html($prompt);
           $emember_fancy_login_v2.load();
       }); 
	});
//-->
<?php endif;?>    
</script>