<?php
/**
 * @@name Auth
 * @description User Authentication API for eMember plugin.
 * @@access public
 * @@author nur hasan <nur858@gmail.com>
 */
class Emember_Auth{
   //authentication
   var $hasmore;
   var $loggedIn;
   var $userInfo;
   var $errorMsg;
   var $sessionName;
   var $errorCode;
   
   //permission
    var $protection;
    var $user_membership_level;
    var $user_membership_level_name;
    var $protected_posts;
    var $protected_pages;
    var $protected_comments;
    var $protected_categories;
    var $loggedin_for_feed;
    var $config;
    static  $_this;
    static function getInstance(){
    	if(empty(self::$_this)){
    		self::$_this = new Emember_Auth();
    		return self::$_this;
    	}
    	return self::$_this;
    }
   function  __construct(){
   	   //permission
        $this->protection = dbAccess::find(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, " id='1' ");
        $this->user_membership_level = null;
        
        $protected = unserialize($this->protection->post_list);
        $this->protected_posts = is_bool($protected)? array(): (array)$protected;

        $protected = unserialize($this->protection->page_list);
        $this->protected_pages = is_bool($protected)? array(): (array)$protected;

        $protected = unserialize($this->protection->comment_list);
        $this->protected_comments = is_bool($protected)? array(): (array)$protected;
                
        $protected = unserialize($this->protection->category_list);
        $this->protected_categories = is_bool($protected)? array(): (array)$protected;   
        $this->loggedin_for_feed = false;   	
   	   //authentication
		include_once('emember_config.php');
		$this->config = Emember_Config::getInstance();   	      	  
   	    $this->hasmore     = false;
        $this->loggedIn    = false;
        $this->password_reminder_block_added = 'no';
        $this->sessionName = 'wordpress.AUTH.eMember';
        $this->errorMsg    = '';
        $sess_id = session_id();
	    if(empty($sess_id)){ 
			@session_start();
	    }

       dbAccess::delete(WP_EMEMBER_AUTH_SESSION_TABLE, ' (UNIX_TIMESTAMP( \''.date('Y-m-d H:i:s').'\' ) - UNIX_TIMESTAMP(last_impression)) >1800 ');//remove invalid sessions.
       $condition   = 'session_id = \'' . session_id() . '\'';
       
       $sessionInfo = dbAccess::find(WP_EMEMBER_AUTH_SESSION_TABLE, $condition);
       $user_id   = null;
       if(!$sessionInfo){
       	  if(isset($_COOKIE['e_m_bazooka'])){

       	     $result = dbAccess::find(WP_EMEMBER_MEMBERS_TABLE_NAME, 'md5(member_id)=\'' . $_COOKIE['e_m_bazooka'] . '\''); 
       	     if(empty($result))
       	         $not_logged_in = null;
       	     else{
		         $_SESSION[$this->sessionName] = session_id();           
		         $sessionInfo = array('session_id'=>session_id(),
		                              'user_id'=>$result->member_id,
		                              'logged_in_from_ip'=>get_real_ip_addr(),
		                              'last_impression'=>current_time('mysql',1));
		         dbAccess::insert(WP_EMEMBER_AUTH_SESSION_TABLE,$sessionInfo);
		         dbAccess::update(WP_EMEMBER_MEMBERS_TABLE_NAME,'member_id='.$result->member_id, array('last_accessed_from_ip'=>get_real_ip_addr(),'last_accessed'=>current_time('mysql',1)));       	     	
       	         $user_id  = $result->member_id;
       	     }    	         	
       	  }
       	  else
       	  	  $user_id = null;       	  	  
       }
       else
          $user_id = $sessionInfo->user_id;
       if(empty($user_id)){
           $this->loggedIn = false;
           $this->userInfo = null;
           $this->errorMsg = EMEMBER_NOT_LOGGED_IN;
           $this->errorCode= 1;
           return;       	
       }   
       
       $userInfo = dbAccess::find(WP_EMEMBER_MEMBERS_TABLE_NAME,'member_id='.$user_id);
       $this->userInfo = $userInfo;
                    
       if($userInfo){
       	//var_dump($userInfo->membership_level);
       	   $this->setPermissions(); 
           if($userInfo->account_state==='inactive'){
               $this->loggedIn = false;
               $this->userInfo = null;
               $this->errorMsg = EMEMBER_ACCOUNT_INACTIVE;
               $this->errorCode= 3;
               return;
           }
           
           $allow_expired_account = $this->config->configs['eMember_allow_expired_account'];
           $account_upgrade_url = $this->config->configs['eMember_account_upgrade_url'];       
           
           if($userInfo->account_state=='unsubscribed'){
           	// Nothing to do.
           }		
                                   
           if($this->is_subscription_expired()){
               dbAccess::update(WP_EMEMBER_MEMBERS_TABLE_NAME,'member_id='.$userInfo->member_id, array('account_state'=>'expired'));               
               if(!$allow_expired_account){
                    $this->loggedIn = false;
                    $this->userInfo = null;
                    $this->errorCode= 8;
                    $this->errorMsg = EMEMBER_SUBSCRIPTION_EXPIRED_MESSAGE;
                    return;                  
               }                              
           }           
           $this->loggedIn = true;
           $this->userInfo = $userInfo;           
           $this->errorMsg = EMEMBER_LOGGED_IN_AS . $this->userInfo->user_name;
           $this->errorCode= 4;
           return;
       }
       $this->errorMsg = EMEMBER_NOT_LOGGED_IN;
       $this->errorCode= 1;       
       $this->loggedIn = false;
       $this->userInfo = null;
   }
   function login($credentials/*$user, $pass,$remember*/){   
       global $wpdb;
	   if(isset($credentials['user'])&&isset($credentials['pass'])){
	       include_once(ABSPATH.WPINC.'/class-phpass.php');
           $wp_hasher = new PasswordHash(8, TRUE);
           $password  = $wp_hasher->HashPassword($credentials['pass']);       
           $user      = $wpdb->escape( trim($credentials['user']));
           $condition = "user_name= '$user' ";
	   }
	   else if (isset($credentials['md5ed_id'])){
	       $condition = 'md5(member_id)=\'' . $credentials['md5ed_id'] . '\'';
	   }
	   else if (isset($credentials['member_id'])){
	       $condition = ' member_id=\'' . $credentials['member_id'] . '\' ';
	   }

	   $userInfo = array();	   
       if($this->loggedIn){
       	   if(isset($credentials['md5ed_id'])){
       	       $userInfo  = dbAccess::find(WP_EMEMBER_MEMBERS_TABLE_NAME, $condition);
       	       if($userInfo->member_id === $this->userInfo->member_id)
       	           return;
       	       else
       	           die("Invalid Key.");       	      
       	   }
       	   else
       	      return;
       }
       else{
       	   $userInfo  = dbAccess::find(WP_EMEMBER_MEMBERS_TABLE_NAME, $condition);
       }
	   
       dbAccess::delete(WP_EMEMBER_AUTH_SESSION_TABLE,'session_id="' . session_id() . '"');
       
       $this->userInfo = $userInfo;       
       if($userInfo){
       	   //$this->setPermissions();
		   if(isset($credentials['pass'])){
	           if(!$wp_hasher->CheckPassword(trim($credentials['pass']),$userInfo->password)){
	               $this->loggedIn = false;
	               $this->userInfo = null;
	               $this->errorCode= 7;
	               $this->errorMsg = EMEMBER_WRONG_PASS;               
	               return;
	           }
		   }
           if($userInfo->account_state=='inactive'){
               $this->loggedIn = false;
               $this->userInfo = null;
               $this->errorCode= 3;
               $this->errorMsg = EMEMBER_ACCOUNT_INACTIVE;
               return;
           }
           $login_limit = $this->config->getValue('eMember_login_limit');
           if(!empty($login_limit)){           	
               $query = "SELECT meta_value FROM " . WP_EMEMBER_MEMBERS_META_TABLE . 
                        " WHERE user_id = " . $userInfo->member_id . " AND meta_key = 'login_count'";
               $login_count = $wpdb->get_row($query);
               $login_count = unserialize($login_count->meta_value);
               if($login_count === false) $insert = true;
               if(isset($login_count[date('y-m-d')])) {
               	   $current_ip = get_real_ip_addr();
               	   if(!in_array($current_ip, $login_count[date('y-m-d')])){
	               	   if(count($login_count[date('y-m-d')])>=intval($login_limit)){
			               $this->loggedIn = false;
			               $this->userInfo = null;
			               $this->errorCode= 10;
			               $this->errorMsg = EMEMBER_LOGIN_LIMIT_ERROR;
			               return;	               	   	
	               	   }
	               	   array_push($login_count[date('y-m-d')], $current_ip);	               	   
	               	   $login_count[date('y-m-d')] = array_unique($login_count[date('y-m-d')]);
               	   }               	   
               }
               else{
               	   $login_count = array( date('y-m-d')=>array( get_real_ip_addr()));
               }   
               if($insert)
               	   $query =  "INSERT INTO " . WP_EMEMBER_MEMBERS_META_TABLE . "(user_id,meta_key,meta_value)".
               	             "VALUES(".$userInfo->member_id.", 'login_count', '".serialize($login_count)."')";
               else 
                   $query =  "UPDATE " . WP_EMEMBER_MEMBERS_META_TABLE . " SET meta_value = '" . serialize($login_count) . "'".
                             " WHERE user_id= " . $userInfo->member_id . " AND meta_key = 'login_count'";
               $wpdb->query($query);            
           }      
           
           $allow_expired_account = $this->config->getValue('eMember_allow_expired_account');
           $account_upgrade_url = $this->config->getValue('eMember_account_upgrade_url');
       
		   $this->setPermissions();
           if($userInfo->account_state=='unsubscribed'){
           	// Nothing to do.
           }		   
		   
           if($this->is_subscription_expired()){
               dbAccess::update(WP_EMEMBER_MEMBERS_TABLE_NAME,'member_id='.$userInfo->member_id, array('account_state'=>'expired'));
                              
               if(!$allow_expired_account){
                    $this->loggedIn = false;
                    $this->userInfo = null;
                    $this->errorCode= 8;
                    $this->errorMsg = EMEMBER_SUBSCRIPTION_EXPIRED_MESSAGE;
                    return;                  
               }               
           }
           
	       if($credentials['rememberme']){
	       	
		       	if ( version_compare(phpversion(), '5.2.0', 'ge') ) {
			         setcookie('e_m_bazooka', md5($userInfo->member_id), time()+3600*24*2, "/", COOKIE_DOMAIN, is_ssl() ? true : false, true);
		       	}
		       	else{
				    $cookie_domain = COOKIE_DOMAIN;
				    if ( !empty($cookie_domain) )
						$cookie_domain .= '; HttpOnly';
			         setcookie('e_m_bazooka', md5($userInfo->member_id), time()+3600*24*2, "/", $cookie_domain, is_ssl() ? true : false);
	           }
	       }
	       else{
	       		setcookie('e_m_bazooka',md5($userInfo->member_id),time()+3600*6,"/");
	       }
           setcookie('eMember_in_use',true,time()+3600*24*2,"/");
           
           $_SESSION[$this->sessionName] = session_id();           
           $sessionInfo = array('session_id'=>session_id(),
                                'user_id'=>$userInfo->member_id,
                                'logged_in_from_ip'=>get_real_ip_addr(),
                                'last_impression'=>current_time('mysql',1));
           dbAccess::insert(WP_EMEMBER_AUTH_SESSION_TABLE,$sessionInfo);
           dbAccess::update(WP_EMEMBER_MEMBERS_TABLE_NAME,'member_id='.$userInfo->member_id, array('last_accessed_from_ip'=>get_real_ip_addr(),'last_accessed'=>current_time('mysql',1)));
           $this->userInfo = $userInfo;
           $this->loggedIn = true;
           if (isset($credentials['md5ed_id']))$this->loggedin_for_feed = true;    
           //notify any other plugin listening for the member login event via WordPress action
		   do_action('eMember_login_complete');                 
       }
       else if(isset($credentials['md5ed_id'])){

       	   die(EMEMBER_NO_USER_KEY);
       }
       else{
           $this->errorMsg = EMEMBER_WRONG_USER_PASS;
           $this->errorCode= 5;
           $this->loggedIn = false;
           $this->userInfo = null;
       }
   }
   function logout(){
   	   if(!$this->loggedin_for_feed){
           setcookie("e_m_bazooka", '', time()-60*60*24*2, "/");
           setcookie('eMember_in_use','',time()-3600*24*2, "/");
   	   }       
       dbAccess::delete(WP_EMEMBER_AUTH_SESSION_TABLE,'session_id="' . session_id() . '"');
       $this->loggedIn = false;
       $this->userInfo = null;
       $this->errorMsg = EMEMBER_LOGOUT_SUCCESS;
       $this->errorCode= 6;
       nocache_headers();
       unset($_SESSION[$this->sessionName]);
   }
   function getUserInfo($key){
       if(!$this->loggedIn) return false;
       return $this->userInfo->$key;
   }
   function isLoggedIn(){
       return $this->loggedIn;
   }
   function getMsg(){
       return $this->errorMsg;
   }
   function getCode(){
       return $this->errorCode;
   }
   
   function setPermissions($level = null){
   	   $level_info = array();	
   	   $current_level = isset($this->userInfo->membership_level)? $this->userInfo->membership_level : $level;
   	   //if(empty($current_level)) die("No Membership Level found for the user.");
       $my_level = dbAccess::find(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, ' id=\'' .$current_level . '\' ');
       $this->userInfo->primary_membership_level = $my_level;
       $options  =  unserialize($my_level->options);          
       if(isset($options['promoted_level_id'])&&($options['promoted_level_id']!=-1)){       	          	  
       	   $current_subscription_starts = strtotime($this->userInfo->subscription_starts);	
       	   $more_levels = $this->userInfo->more_membership_levels;	
       	   $more_levels = is_array($more_levels)?array_filter($more_levels): $more_levels;				         	   								  									
       	   $sec_levels = explode(',', $more_levels);

       	   $current_time = time();
       	   while(1){
		        if($current_level === $options['promoted_level_id']) break;
 		        $promoted_after = trim($options['days_after']); 	
                if(empty($promoted_after)) break;

       	   		$d = ($promoted_after==1)? ' day':' days';
       	   		$expires = strtotime(" + " . abs($promoted_after) .$d,  $current_subscription_starts);  	   		       	   		       	  
       	   	    if($expires>$current_time) break;
       	   	    if(!isset($options['promoted_level_id'])||($options['promoted_level_id']==-1)) break;
       	   	    //$current_subscription_starts = $expires;
       	   		$sec_levels[] = $current_level;
       	   		$current_level = $options['promoted_level_id'];        	   		
		        $my_level = dbAccess::find(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, ' id=\'' .$current_level . '\' ');
  		        $this->userInfo->primary_membership_level = $my_level;
 		        $options  =  unserialize($my_level->options); 		               	   		       	   	           	   	
       	   }
    	    if(($current_level!=-1)){        
    	    	$level_info ['membership_level'] =$current_level;
    	    	//$level_info ['current_subscription_starts'] = date('y-m-d', $current_subscription_starts);

    	    	if($this->config->getValue('eMember_enable_secondary_membership')){
    	    		$level_info['more_membership_levels'] = implode(',', array_unique($sec_levels));
    	    	}
    	    	$this->userInfo->membership_level = $current_level; 
    	    	
    	    	dbAccess::update(WP_EMEMBER_MEMBERS_TABLE_NAME,'member_id='.$this->userInfo->member_id, $level_info);
    	    	$this->userInfo->primary_membership_level = $my_level;
    	    }       	          	   
       }
       
       $this->my_options = unserialize($my_level->options);       
       $this->user_membership_level_name = $my_level->alias;
       $my_contents = unserialize($my_level->post_list);
       $this->my_posts = (is_bool($my_contents))? array() : (array)$my_contents;
       
       $my_contents = unserialize($my_level->page_list);
       $this->my_pages = (is_bool($my_contents))? array() : (array)$my_contents;
                
       $my_contents = unserialize($my_level->comment_list);
       $this->my_comments = (is_bool($my_contents))? array() : (array)$my_contents;
                
       $my_contents = unserialize($my_level->category_list);
       $this->my_categories = (is_bool($my_contents))? array() : (array)$my_contents;   
       $my_subcript_period = (int)$my_level->subscription_period;
       $my_subscript_unit  = $my_level->subscription_unit;
	   if($this->config->getValue('eMember_enable_secondary_membership')){
	       if(!empty($this->userInfo->more_membership_levels)){
	       	   $my_secondary_levels = dbAccess::findAll(WP_EMEMBER_MEMBERSHIP_LEVEL_TABLE, ' id IN ( ' . $this->userInfo->more_membership_levels . ' ) ');
	       	   $this->userInfo->secondary_membership_levels = $my_secondary_levels ;
	       	   
	       	   foreach($my_secondary_levels as $my_secondary_level){
	       		  $my_contents = unserialize($my_secondary_level->post_list);
	              $this->my_posts = (is_bool($my_contents))? $this->my_posts : array_unique(array_merge($this->my_posts,(array)$my_contents));
	       	   	
	       		  $my_contents = unserialize($my_secondary_level->page_list);
	              $this->my_pages = (is_bool($my_contents))? $this->my_pages : array_unique(array_merge($this->my_pages,(array)$my_contents));
	
	       		  $my_contents = unserialize($my_secondary_level->comment_list);
	              $this->my_comments = (is_bool($my_contents))? $this->my_comments : array_unique(array_merge($this->my_comments,(array)$my_contents));
	
	       		  $my_contents = unserialize($my_secondary_level->category_list);
	              $this->my_categories = (is_bool($my_contents))? $this->my_categories : array_unique(array_merge($this->my_categories,(array)$my_contents));              
	       	   }       	           	          	  
	       }
	       else 
	       	   $this->userInfo->secondary_membership_levels = NULL;
	   }
       
       switch($my_subscript_unit){
          case 'Days':
             break;
          case 'Weeks':
             $my_subcript_period = $my_subcript_period*7;
             break;
          case 'Months':
             $my_subcript_period = $my_subcript_period*30;
             break;
          case 'Years':
             $my_subcript_period = $my_subcript_period*365;
             break;
       }
       $this->my_subscription_duration = $my_subcript_period;
       $this->permissions = $my_level->permissions;   
       //echo 'ddd';   
   } 
    function is_subscription_expired(){    	
        if(isset($this->my_subscription_duration)){
            if($this->my_subscription_duration===0)
               return false;
            /**alternative***/
            $d = ($this->my_subscription_duration==1)? ' day':' days';
            $sDate = date('Y-m-d', strtotime(" - " . abs($this->my_subscription_duration) .$d ));
            if((strtotime($sDate)-strtotime($this->userInfo->subscription_starts))>=0){
            	return true;
            }
            return false;
            /**/
            /********* initial implementation********
            $time_diff = time() - strtotime($this->userInfo->subscription_starts);
            
            $time_diff = (int)(($time_diff)/(24*3600));
            if($time_diff<$this->my_subscription_duration)
               return false;
            return true;
            ***/    
        }
        die('Something is wrong:expire');
    }
    function is_protected_post($id){
        return in_array($id, $this->protected_posts);
    }
    function is_protected_page($id){
        return in_array($id, $this->protected_pages);
    }
    function is_protected_comment($id){
        return in_array($id, $this->protected_comments);
    }
    function is_protected_category($id){
        return in_category($this->protected_categories, $id);
    }
   
    function is_permitted_category($id){
        if(isset($this->my_categories))
            return (($this->permissions&1)===1) && in_category($this->my_categories, $id);
        die('something is wrong#cat');
    }
    function is_permitted_post($id){
        if(isset($this->my_posts))
            return (($this->permissions&4)===4) && in_array($id, $this->my_posts );
        die('Something is wrong#post');
    }
    function is_permitted_page($id){
        if(isset($this->my_pages))
            return (($this->permissions&8)===8) && in_array( $id, $this->my_pages);
        die('Something is wrong#page');
    }
    function is_permitted_comment($id){
        if(isset($this->my_comments))
            return (($this->permissions&2)===2) && in_array($id,$this->my_comments);
        die('Something is wrong#comment');               
    }     
}
?>