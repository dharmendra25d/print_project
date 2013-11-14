<?php

//**** This file needs to be included from a file that has access to "wp-load.php" ****
function eMember_log_debug($message,$success,$end=false)
{
	global $emember_config;
    $emember_config = Emember_Config::getInstance();    
	$debug_enabled = false;
	if($emember_config->getValue('eMember_enable_debug') == 1){
		$debug_enabled = true;
	}
	$debug_log_file_name = dirname(__FILE__).'/eMember_debug.log';

	//global $debug_enabled,$debug_log_file_name;
    if (!$debug_enabled) return;
    // Timestamp
    $text = '['.date('m/d/Y g:i A').'] - '.(($success)?'SUCCESS :':'FAILURE :').$message. "\n";
    if ($end) {
    	$text .= "\n------------------------------------------------------------------\n\n";
    }
    // Write to log
    $fp=fopen($debug_log_file_name,'a');
    fwrite($fp, $text );
    fclose($fp);  // close file
}

function eMember_log_debug_array($array_to_write,$success,$end=false,$debug_log_file_name='')
{
	$debug_enabled = false;
	global $wp_pg_bundle_config;	
	if($wp_pg_bundle_config->getValue('wp_pg_enable_debug')=='1'){	
		$debug_enabled = true;
	}
    if (!$debug_enabled) return;
    // Timestamp
    $text = '['.date('m/d/Y g:i A').'] - '.(($success)?'SUCCESS :':'FAILURE :'). "\n";
	ob_start(); 
	print_r($array_to_write); 
	$var = ob_get_contents(); 
	ob_end_clean();     
    $text .= $var;
    
    if ($end) 
    {
    	$text .= "\n------------------------------------------------------------------\n\n";
    }

	if(empty($debug_log_file_name)){
    	$debug_log_file_name = dirname(__FILE__).'/eMember_debug.log';
	}    
    // Write to log
    $fp=fopen($debug_log_file_name,'a');
    fwrite($fp, $text );
    fclose($fp);  // close file
}
?>