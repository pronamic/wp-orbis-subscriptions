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

function orbis_subscriptions_bootstrap() {
	include 'classes/orbis-subscriptions-plugin.php';

	global $orbis_subscriptions_plugin;
	
	$orbis_subscriptions_plugin = new Orbis_Subscriptions_Plugin( __FILE__ );
	
	include 'classes/orbis-subscription.php';
	
	include 'includes/functions-subscription.php';
}

add_action( 'orbis_bootstrap', 'orbis_subscriptions_bootstrap' );
