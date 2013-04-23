<?php
/*
Plugin Name: Orbis Subscriptions
Plugin URI: http://orbiswp.com/
Description: 

Version: 0.1
Requires at least: 3.5

Author: Pronamic
Author URI: http://pronamic.eu/

Text Domain: orbis
Domain Path: /languages/

License: GPL

GitHub URI: https://github.com/pronamic/wp-orbis-subscriptions
*/

DEFINE( 'ORBIS_SUBSCRIPTIONS_FILE', __FILE__ );
DEFINE( 'ORBIS_SUBSCRIPTIONS_FOLDER', dirname( ORBIS_SUBSCRIPTIONS_FILE ) );

function orbis_subscriptions_bootstrap() {
	include 'classes/orbis-subscriptions-plugin.php';

	global $orbis_subscriptions_plugin;
	
	$orbis_subscriptions_plugin = new Orbis_Subscriptions_Plugin( __FILE__ );
	
	// Classes
	include 'classes/orbis-subscription.php';
	include 'classes/orbis-subscriptions-expiration-factory.php';
	include 'classes/orbis-subscriptions-expiration.php';
	include 'classes/orbis-subscriptions-settings.php';
	
	// Functions
	include 'includes/functions.php';
	
	// Load settings
	$expiration = new Orbis_Subscriptions_Expiration();
	$settings = new Orbis_Subscriptions_Settings();
	
}

add_action( 'orbis_bootstrap', 'orbis_subscriptions_bootstrap' );
