<?php
class Emember_Config
{
	var $configs;
	static $_this;
	var $pages = array('eMember_admin_functions_menu',
					   'eMember_membership_level_menu',
	                   'wp_eMember_manage',
	                   'eMember_settings_menu'
	                  );
	function loadConfig(){	
		//$this->configs = unserialize(get_option('eMember_configs')) ;
		$eMember_raw_configs = get_option('eMember_configs');
		if(is_string($eMember_raw_configs)){
			$this->configs = unserialize($eMember_raw_configs);
		}
		else
		{
			$this->configs = unserialize((string)$eMember_raw_configs);
		}		
	}
	
    function getValue($key){
    	return isset($this->configs[$key])?$this->configs[$key] : '';    	
    }
    
    function setValue($key, $value){
    	$this->configs[$key] = $value;
    }
    function addValue($key, $value){
    	if (array_key_exists($key, $this->configs))
    	{
    		//Don't update the value for this key
    	}
    	else
    	{
    		//It is save to update the value for this key
    		$this->configs[$key] = $value;
    	}    	
    }    
    function saveConfig(){
    	update_option('eMember_configs', serialize($this->configs) );
    }
    
    static function getInstance(){
    	if(empty(self::$_this)){
    		self::$_this = new Emember_Config();
    		self::$_this->loadConfig();
    		return self::$_this;
    	}
    	return self::$_this;
    }
     
}
?>