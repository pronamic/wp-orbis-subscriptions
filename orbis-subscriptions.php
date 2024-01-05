<?php
/*
Plugin Name: Orbis Subscriptions
Plugin URI: http://www.pronamic.eu/plugins/orbis-subscriptions/
Description: The Orbis Subscriptions plugin extends your Orbis environment with the option to add subscription products and subscriptions.

Version: 1.2.0
Requires at least: 3.5

Author: Pronamic
Author URI: http://www.pronamic.eu/

Text Domain: orbis-subscriptions
Domain Path: /languages/

License: Copyright (c) Pronamic

GitHub URI: https://github.com/wp-orbis/wp-orbis-subscriptions
*/

/**
 * Autoload.
 */
require_once __DIR__ . '/vendor/autoload_packages.php';

/**
 * Bootstrap.
 */
add_action(
	'plugins_loaded',
	function() {
		load_plugin_textdomain( 'orbis-subscriptions', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
	}
);

function orbis_subscriptions_bootstrap() {
	// Classes
	require_once 'classes/orbis-subscriptions-plugin.php';
	require_once 'classes/orbis-subscriptions-admin.php';
	require_once 'classes/orbis-subscription.php';

	// Functions
	require_once 'includes/functions.php';

	// Initialize
	global $orbis_subscriptions_plugin;

	$orbis_subscriptions_plugin = new Orbis_Subscriptions_Plugin( __FILE__ );
}

add_action( 'orbis_bootstrap', 'orbis_subscriptions_bootstrap' );
