<?php
/*
Plugin Name: Orbis Subscriptions
Plugin URI: http://www.orbiswp.com/
Description: 

Version: 0.1
Requires at least: 3.5

Author: Pronamic
Author URI: http://www.pronamic.eu/

Text Domain: orbis_subscriptions
Domain Path: /languages/

License: GPL

GitHub URI: https://github.com/pronamic/wp-orbis-subscriptions
*/

function orbis_subscriptions_bootstrap() {
	// Classes
	require_once 'classes/orbis-subscriptions-plugin.php';
	require_once 'classes/orbis-subscription.php';
	require_once 'classes/orbis-subscriptions-expiration-factory.php';
	require_once 'classes/orbis-subscriptions-expiration.php';
	require_once 'classes/orbis-subscriptions-settings.php';
	
	// Functions
	require_once 'includes/functions.php';

	// Initialize
	global $orbis_subscriptions_plugin;
	
	$orbis_subscriptions_plugin = new Orbis_Subscriptions_Plugin( __FILE__ );
	
	// Load settings
	$expiration = new Orbis_Subscriptions_Expiration();
	$settings   = new Orbis_Subscriptions_Settings();
}

add_action( 'orbis_bootstrap', 'orbis_subscriptions_bootstrap' );
