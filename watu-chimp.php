<?php
/*
Plugin Name: Watu to MailChimp Bridge 
Plugin URI: 
Description: Automatically subscribe users who take Watu quizzes to your MailChimp mailing lists
Author: Kiboko Labs
Version: 1.1
Author URI: http://calendarscripts.info/
License: GPLv2 or later
Text-domain: watuchimp
*/

define( 'WATUCHIMP_PATH', dirname( __FILE__ ) );
define( 'WATUCHIMP_RELATIVE_PATH', dirname( plugin_basename( __FILE__ )));
define( 'WATUCHIMP_URL', plugin_dir_url( __FILE__ ));

// require controllers and models
require_once(WATUCHIMP_PATH.'/models/basic.php');
require_once(WATUCHIMP_PATH.'/controllers/bridge.php');

add_action('init', array("WatuChimp", "init"));

register_activation_hook(__FILE__, array("WatuChimp", "install"));
add_action('watu_admin_menu', array("WatuChimp", "menu"));