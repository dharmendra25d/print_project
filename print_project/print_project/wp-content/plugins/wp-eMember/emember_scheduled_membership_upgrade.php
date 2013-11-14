<?php
include_once('class.emember_meta.php');
function wp_eMember_scheduled_membership_upgrade(){
    global $wpdb;
    global $emember_config;
    $emember_config = Emember_Config::getInstance();    
    $wpememmeta = new WPEmemberMeta(); 
    $membership_tbl = $wpememmeta->get_table('membership_level');  
    $members_tbl    = $wpememmeta->get_table('member');
    $membership_levels = $wpdb->get_results("SELECT id,subscription_period,subscription_unit,role,options FROM $membership_tbl WHERE id!=1", OBJECT );
    
    $levels_indexed_by_pk = array();
    foreach($membership_levels as $level)
        $levels_indexed_by_pk[$level->id] = $level;   
    unset($membership_levels);    
    $query_start  = 0;
    $query_limit  = 500;
    $iterations = 0;
    while(1){
        $query  = 'SELECT member_id,membership_level, subscription_starts, more_membership_levels FROM ' 
                    . $members_tbl . ' WHERE account_state="active"  LIMIT ' . $query_start . ', ' .$query_limit;         
        $members = $wpdb->get_results($query, OBJECT);
        if(count($members)<1) break;
        foreach($members as $member){
            $my_level = $levels_indexed_by_pk[$member->membership_level];
            $options  =  unserialize($my_level->options);          
            if(isset($options['promoted_level_id'])&&(!empty($options['promoted_level_id']))&&($options['promoted_level_id']!=-1)){       	          	  
                $current_subscription_starts = strtotime($member->subscription_starts);	
                $current_level = $member->membership_level;
                $more_levels = $member->more_membership_levels;	
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
                    if(!isset($options['promoted_level_id'])||empty($options['promoted_level_id'])||($options['promoted_level_id']==-1)) break;
                    $sec_levels[] = $current_level;
                    $current_level = $options['promoted_level_id'];        	   		
                    $my_level = $levels_indexed_by_pk[$current_level];
                    $options  =  unserialize($my_level->options); 		               	   		       	   	           	   	
                }
                if(($current_level!=-1)&& (!empty($current_level))){        
                    $level_info ['membership_level'] = $current_level;
                    if($emember_config->getValue('eMember_enable_secondary_membership')){
                        $level_info['more_membership_levels'] = implode(',', array_unique($sec_levels));
                    }                  

                    dbAccess::update(WP_EMEMBER_MEMBERS_TABLE_NAME,'member_id='.$member->member_id, $level_info);
                }       	          	   
            }            
        }
        $query_start = $query_limit*(++$iterations)+1;
    }
}
