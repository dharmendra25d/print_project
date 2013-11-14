<?php
class WPEmemberMeta{
	var $db_version = "3.0.3";
	var $tables = array('member'=>'wp_eMember_members_tbl',
	                    'membership_level'=>'wp_eMember_membership_tbl',
	                    'session'=>'wp_auth_session_tbl',
	                    'member_meta'=>'wp_members_meta_tbl',
	                    'openid'=>'emember_openid_lookup'
	                   );
	function get_table($index){
		global $wpdb;
		return $wpdb->prefix . $this->tables[$index];
	}
	function get_db_version(){
		return $this->db_version;
	}
}
