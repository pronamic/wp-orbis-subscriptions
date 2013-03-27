<?php

class Orbis_Subscriptions_Plugin extends Orbis_Plugin {
	public function __construct( $file ) {
		parent::__construct( $file );

		$this->plugin_include( 'includes/post.php' );
		$this->plugin_include( 'includes/api.php' );
		$this->plugin_include( 'includes/shortcodes.php' );
		$this->plugin_include( 'includes/template.php' );
	}

	public function loaded() {
		$this->load_textdomain( 'orbis_subscriptions', '/languages/' );
	}
}
