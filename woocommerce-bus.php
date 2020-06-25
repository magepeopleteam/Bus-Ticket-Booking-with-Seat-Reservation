<?php
/**
* Plugin Name: Bus Ticket Booking with Seat Reservation
* Plugin URI: http://mage-people.com
* Description: A Complete Bus Ticketig System for WordPress & WooCommerce
* Version: 2.0
* Author: MagePeople Team
* Author URI: http://www.mage-people.com/
* Text Domain: bus-ticket-booking-with-seat-reservation
* Domain Path: /languages/
*/ 

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-mage-plugin-activator.php
 */
function wbtm_activate_wbtm_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-activator.php';
	// WBTM_Plugin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-mage-plugin-deactivator.php
 */
function wbtm_deactivate_wbtm_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-plugin-deactivator.php';
	// wbtm_Plugin_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'wbtm_activate_wbtm_plugin' );
register_deactivation_hook( __FILE__, 'wbtm_deactivate_wbtm_plugin' );


class Wbtm_Base{
	
	public function __construct(){
		$this->define_constants();
		$this->load_main_class();
		$this->run_wbtm_plugin();
	}

	public function define_constants() {
		define( 'WBTM_PLUGIN_URL', WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__ ) ) . '/' );
		define( 'WBTM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		define( 'WBTM_PLUGIN_FILE', plugin_basename( __FILE__ ) );
		define( 'WBTM_TEXTDOMAIN', 'mage-plugin' );
	}

	public function load_main_class(){
		require WBTM_PLUGIN_DIR . 'includes/class-plugin.php';
	}

	public function run_wbtm_plugin() {
		$plugin = new Wbtm_Plugin();
		$plugin->run();
	}
}
new Wbtm_Base();