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

function orbis_subscriptions_init() {
	include 'classes/orbis-subscriptions-plugin.php';

	global $orbis_subscriptions_plugin;
	
	$orbis_subscriptions_plugin = new Orbis_Subscriptions_Plugin( __FILE__ );
}

add_action( 'orbis_init', 'orbis_subscriptions_init' );
