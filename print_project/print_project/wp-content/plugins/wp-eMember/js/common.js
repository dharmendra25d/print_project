/*jQuery(document).ready(function(){

    jQuery('#member_since').datepicker({showOn: 'button', buttonImage: 'calendar.gif', buttonImageOnly: true})

    .dateEntry({spinnerImage: '', useMouseWheel:false});

    jQuery('#last_accessed').datepicker({showOn: 'button', buttonImage: 'calendar.gif', buttonImageOnly: true})

    .dateEntry({spinnerImage: '', useMouseWheel:false});



    jQuery('form').submit(function(){

       console.log('form submitted');

       return false;

    });

});*/
/*function checkError(at, rules)
{
   var testable = jQuery('#'+at);
   var ok       = true;

   for(var rule in rules)
   {
      switch(rule)
      {
         case 'required':
            if(jQuery.trim(testable.val())=='') ok = false;
            break;
         case 'email':
            var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            if(!filter.test(jQuery.trim(testable.val()))) ok = false;
            break;
         case '5_chars':
            var testcase = jQuery.trim(testable.val());
            if(testcase.length<5) ok = false;
            break;
         default:
            alert('undefined rule: ' +rule);
            return false;
            break;
      }

      if(!ok)
      {
          if(!jQuery("#"+at+"_msg").length)
          {
             testable.css("border", "1px solid red").
             focus(function(){jQuery(this).css("border","");jQuery("#"+at+"_msg").html("");}).
             after(jQuery('<span id= "'+at+'_msg"/>').
                   css("color", "red").html(rules[rule]));
          }
          else
          {
            testable.css("color", "1px solid red").
            focus(function(){jQuery(this).css("border","");jQuery("#"+at+"_msg").html("");});
            jQuery("#"+at+"_msg").html(rules[rule]);
          }
          return false;
      }
   }
   return true;
}
*/
jQuery(document).ready(function(){
  jQuery(".forgot_pass_link").click(function(){
    jQuery("#mailMsg").html('').hide();
    jQuery("#mailForm").show();
     jQuery.blockUI({ message: jQuery("#domMessage"),css: { width: "40%" }}); 
  });
/*  jQuery('.loginForm').submit(function(){
    var ok = true;
    ok = checkError("login_user_name", {"required":"This field is required"})&& ok;
    ok = checkError("login_pwd", {"required":"This field is required"})&& ok;
    return ok;
  });*/

  jQuery("#closeUI").click(function(){
    jQuery.unblockUI();
  });
  jQuery("#mailSendForm").submit(function(e){
       var $this = this;
       var divs = jQuery($this).parent().parent().find('div');
       var emailId = jQuery($this).find("input").eq(0).val();
       if(emailId=="")
          return;
       divs.eq(1).hide();
       divs.eq(0).html('<h3>Please Wait...</h3>').show();
       jQuery.get( WPURL+"/wp-admin/admin-ajax.php",{"event":"send_mail","email":emailId},
             function(data)
             {
                 divs.eq(0).html("<h3>" + data.msg + "</h3>")
                 setTimeout("jQuery.unblockUI()", 1000);
             },
       "json");
    e.preventDefault();
  });
 });