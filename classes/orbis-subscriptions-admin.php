<?php

class Orbis_Subscriptions_Admin {
	private $plugin;

	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}
	
	public function admin_menu() {
		add_submenu_page(
			'edit.php?post_type=orbis_subscription',
			__( 'Orbis Subscriptions Statistics', 'orbis_subscriptions' ),
			__( 'Statistics', 'orbis_subscriptions' ),
			'manage_options',
			'orbis_subscription_statistics',
			array( $this, 'page_statistics' )
		);
	}

	public function page_statistics() {
		$this->plugin->plugin_include( 'admin/page-statistics.php' );
	}
}
