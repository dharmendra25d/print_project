$.validationEngineLanguage = {
    newLang: function(){
        $.validationEngineLanguage.allRules = {
            "required": { // Add your regex rules here, you can take telephone as an example
                "regex": "none",
                "alertText": "* " + '<?php echo EMEMBER_REQUIRED;?> ',
                "alertTextCheckboxe": "* " + '<?php echo EMEMBER_REQUIRED;?> '
            },
            "minSize": {
                "regex": "none",
                "alertText": "* " +'<?php echo EMEMBER_MIN;?> ',
                "alertText2": " "+'<?php echo EMEMBER_ALLOWED_CHAR_TEXT;?> '
            },
            "equals": {
                "regex": "none",
                "alertText": "* "+'<?php echo EMEMBER_FIELD_MISMATCH;?> '
            },
            "phone": {
                // credit: jquery.h5validate.js / orefalo
                "regex": /^([\+][0-9]{1,3}[ \.\-])?([\(]{1}[0-9]{2,6}[\)])?([0-9 \.\-\/]{3,20})((x|ext|extension)[ ]?[0-9]{1,4})?$/,
                "alertText": "* Invalid phone number"
            },
            "email": {
                // Simplified, was not working in the Iphone browser
                "regex": /^([A-Za-z0-9_\-\.\'\+])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,6})$/,
                "alertText": "* " + '<?php echo EMEMBER_INVALID_EMAIL?>'
            },
            "onlyLetterNumberUnderscore": {
                "regex": /^[0-9a-zA-Z_]+$/,
                "alertText": "* "+'<?php echo EMEMBER_ALPHA_NUMERIC_UNDERSCORE;?>'
            },

            // --- CUSTOM RULES -- Those are specific to the demos, they can be removed or changed to your likings
            "ajaxUserCall": {
                "url": "ajaxurl",
                // you may want to pass extra data on the ajax call
                "extraData": "&event=check_name&action=check_name",
                "alertText": "* "+'<?php echo EMEMBER_USERNAME_TAKEN;?>',
                "alertTextOk": "* "+'<?php echo EMEMBER_USERNAME_AVAIL;?>',
                "alertTextLoad": "* "+'<?php echo EMEMBERR_WAIT;?>'
            }
        };            
    }
};
$.validationEngineLanguage.newLang();