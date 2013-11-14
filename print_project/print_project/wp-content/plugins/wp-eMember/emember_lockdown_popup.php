<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($) {
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
   $lockdown_overlay = $("#lockdownbox").overlay({	   
	       top: '30%',
	       oneInstance: false,
	       api:true,
	       mask: {
	           color: '#ebecff',
	           loadSpeed: 200,
	           opacity: 0.9
	       },
	       closeOnClick: false,
	       closeOnEsc:false,
	       load: true	    
	   }); 
        $forgot_pass_overlay.onClose(function(){
            $lockdown_overlay.load();
        });
});
/* ]]> */
</script>
<div id="lockdownbox" class="emember_modal"> 
 
    <div> 
        <h2>
            <?php echo EMEMBER_AUTH_REQUIRED; ?> <br/>
            <span><?php echo EMEMBER_PROTECTED_BY;?> <a target="_blank" href="http://www.tipsandtricks-hq.com/wordpress-emember-easy-to-use-wordpress-membership-plugin-1706">WP eMember &copy;</a></span>
        </h2>  
	    <form action="<?php echo $login_url?>" id="loginForm" name="loginForm" method="post">
	        <p class="textbox">
	            <label for="login_user_name" class="eMember_label"><?php echo EMEMBER_USER_NAME;?></label>
	            <input type="text" tabindex="4" title="username" value="" name="login_user_name" id="login_user_name" />
	        </p>
	        <p class="textbox">
	            <label for="login_pwd" class="eMember_label"><?php echo EMEMBER_PASSWORD;?></label>
	            <input type="password" tabindex="5" title="password" value="" name="login_pwd" id="login_pwd" />
	        </p>
	        
	        <p class="rememberme">
	            <input type="submit" tabindex="7" value="<?php echo EMEMBER_SIGNIN;?>" name="doLogin" class="emember_button" id="doLogin" />
	            <input type="checkbox" tabindex="6" value="forever" name="remember_me" id="rememberme" />
	            <input type="hidden" value="1" name="testcookie" />
	            <label for="rememberme"><?php echo EMEMBER_REMEMBER_ME;?></label>
	        </p>
                <p style="color:red;"><?php echo ($emember_auth->getCode()!=1)?  $emember_auth->getMsg(): '';?></p>
	        <p class="forgot">
	            <a id="forgot_pass" rel="#emember_forgot_pass_prompt" class="forgot_pass_link" href="javascript:void(0);"><?php echo EMEMBER_FORGOT_PASS;?></a>
	        </p>        
	        <p class="forgot-username">
	            <a title="Join us" id="join_us" href="<?php echo $join_url; ?>"><?php echo EMEMBER_JOIN_US;?></a>
	        </p>
	    </form>  
    </div>  
</div> 
 
</body>
</html>