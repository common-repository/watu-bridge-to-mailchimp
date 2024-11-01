<?php
// main model containing general config and UI functions
class WatuChimp {
   static function install() {
   	global $wpdb;	
   	$wpdb -> show_errors();
   	
   	self::init();
	   
    // relations bewteen completed exams and mailing lists. 
    // For now not depending on exam result but place the field for later use
    if($wpdb->get_var("SHOW TABLES LIKE '".WATUCHIMP_RELATIONS."'") != WATUCHIMP_RELATIONS) {  
        $sql = "CREATE TABLE `".WATUCHIMP_RELATIONS."` (
				id int(11) unsigned NOT NULL auto_increment PRIMARY KEY,
				exam_id int(11) unsigned NOT NULL default '0',
				list_id VARCHAR(100) NOT NULL DEFAULT '',
				grade_id int(11) unsigned NOT NULL default '0'
			) CHARACTER SET utf8;";
        $wpdb->query($sql);         
    	}
	}	   
   
   // main menu
   static function menu() {
   	$view_level = current_user_can('watu_manage') ? 'watu_manage' : 'manage_options';
   	add_submenu_page('watu_exams', __('Watu to MailChimp', 'wbbridge'), __('Watu to MailChimp', 'wbbridge'), $view_level, 
   		'watuchimp', array('WatuChimpBridge','main'));	
	}
	
	// CSS and JS
	static function scripts() {   
   	wp_enqueue_script('jquery');
	}
	
	// initialization
	static function init() {
		global $wpdb;
		load_plugin_textdomain( 'watuchimp' );
		define('WATUCHIMP_RELATIONS', $wpdb->prefix.'watuchimp_relations');
		
		add_action('watu_exam_submitted', array('WatuChimpBridge', 'complete_exam'));
	}	
}