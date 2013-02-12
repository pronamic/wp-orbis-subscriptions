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

class Orbis_Subscriptions_Plugin {
	public $file;

	public function __construct( $file ) {
		$this->file    = $file;
		$this->dirname = dirname( $file );

		include $this->dirname . '/includes/post.php';
		include $this->dirname . '/includes/api.php';
	}
}

global $orbis_subscriptions_plugin;

$orbis_subscriptions_plugin = new Orbis_Subscriptions_Plugin( __FILE__ );
