<?php
/*
Plugin Name: WCF Loyalty Points & Rewards for WooCommerce
Description:Our WordPress points & rewards plugin boosts your customer retention and gets you quality leads that further scale your sales by 79%..
Plugin URI: https://wecodefuture.com/wordpress-plugins/wcf-loyalty-points-and-rewards-plugin-for-woocommerce/
Version: 1.0.0
Author:            WecodeFuture
Author URI:        https://wecodefuture.com/
License:           GPL-2.0+
License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain:       wcf-woocommerce-loyalty-points-and-rewards
*/
define( 'WCF_REWARDS_VERSION', '1.0.0' );
define( 'WCF_REWARDS__MINIMUM_WP_VERSION', '5.5' );
define( 'WCF_REWARDS_PLUGIN_NAME', 'WCF Loyalty Points & Rewards Plugin');
define( 'WCF_REWARDS__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WCF_REWARDS_BASE_URL', plugin_dir_url( __FILE__ ) );
register_activation_hook( __FILE__,array( 'Wcf_Rewards_Activator', 'wcf_rewards_activate' )  );
register_deactivation_hook(__FILE__, 'wcf_rewards_deactivate');
require_once(WCF_REWARDS__PLUGIN_DIR . 'function.php' );
class Wcf_Rewards_Activator {
 static function wcf_rewards_activate() {

	 global $wpdb;
	 $rewards_table = $wpdb->prefix.'wcf_loyalty_points';  
     $charset_collate = $wpdb->get_charset_collate();
     $sql = "CREATE TABLE IF NOT EXISTS `$rewards_table` (
	 `id` int(11) NOT NULL AUTO_INCREMENT,
	 `user_id` varchar(100) NOT NULL,
	 `username` varchar(150) NOT NULL,
	 `email` varchar(220) NOT NULL,
	 `points` int(100) DEFAULT '0' NOT NULL,
	 PRIMARY KEY  (id)
	) $charset_collate;";
	 if($wpdb->query($sql)) {
		$wc_users = get_users(
		   array('role' => 'customer')
	   );
		foreach($wc_users as $user) {
		   $wpdb->insert(
			   $rewards_table,
			   array(
				   "user_id" => $user->ID,
				   "email" => $user->user_email,
				   "username" => $user->user_login,
			   )
		   );
	   }
	}
	//logs table
	global $wpdb;
	$logs_table = $wpdb->prefix.'wcf_loyalty_points_logs';  
	$charset_collate = $wpdb->get_charset_collate();
	$logs_sql = "CREATE TABLE IF NOT EXISTS `$logs_table` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	 `order_id` int(20) NOT NULL,
	 `cus_name` varchar(100) NOT NULL,
	 `email` varchar(220) NOT NULL,
	 `order_amt` varchar(150) NOT NULL,
	 `points_earned` int(100) DEFAULT '0' NOT NULL,
	 `datetime`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	 PRIMARY KEY  (id)
   ) $charset_collate;";
   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
   dbDelta( $logs_sql );
	}
}
//deactive plugin action 
function wcf_rewards_deactivate(){
   global $wpdb;
   global $table_prefix;
   $table_wcf_rewards = $wpdb->prefix . 'wcf_loyalty_points';
   $sql= "DROP TABLE $table_wcf_rewards";
   $wpdb->query($sql);
}

add_action('admin_menu', 'add_wcf_rewards_menu');
function add_wcf_rewards_menu() {
	add_submenu_page(
		'woocommerce',
		__('WCF Loyalty Points &#38; Rewards', 'wcf-woocommerce-loyalty-points-and-rewards'),
		__('WCF Loyalty Points &#38; Rewards', 'wcf-woocommerce-loyalty-points-and-rewards'),
		'manage_woocommerce',
		'wcf-loyalty-rewards',
		'wcf_rewards_admin_index'
	);
	add_submenu_page(
        'null'
        , __( 'WCF Loyalty Points Logs', 'wcf-woocommerce-loyalty-points-and-rewards' )
        , ''
        , 'manage_options'
        , 'wcf-loyalty-rewards-log'
        , 'wcf_loyalty_rewards_log'
    );
}
function wcf_rewards_admin_index(){
	include('wcf-loyalty-points-and-rewards-index.php');
}
function wcf_loyalty_rewards_log() {
	ob_start();
	//include('wcf_loyalty_points_log.php');
	include_once plugin_dir_path(__FILE__) . 'tamplate/wcf_loyalty_points_log.php';
	$wcf_logdata = ob_get_contents();
	ob_end_clean();
	echo $wcf_logdata ;
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'add_wcf_rewards_action_links' );
 
function add_wcf_rewards_action_links ( $wcf_actions ) {
   $wcf_settings = array(
      '<a href="' . admin_url( 'admin.php?page=wcf-loyalty-rewards' ) . '">Settings</a>',
   );
   $actions = array_merge( $wcf_actions, $wcf_settings );
   return $actions;
}  

?>