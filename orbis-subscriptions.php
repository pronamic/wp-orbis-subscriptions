<?php
/*
Plugin Name: Orbis Subscriptions
Plugin URI: http://www.orbiswp.com/
Description: The Orbis Subscriptions extends your Orbis environment with the option to add subscription types and subscriptions.

Version: 1.2.0
Requires at least: 3.5

Author: Pronamic
Author URI: http://www.pronamic.eu/

Text Domain: orbis_subscriptions
Domain Path: /languages/

License: Copyright (c) Pronamic

GitHub URI: https://github.com/pronamic/wp-orbis-subscriptions
*/

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
