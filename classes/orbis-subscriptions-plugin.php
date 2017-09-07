<?php

class Orbis_Subscriptions_Plugin extends Orbis_Plugin {
	public function __construct( $file ) {
		parent::__construct( $file );

		$this->set_name( 'orbis_subscriptions' );
		$this->set_db_version( '1.1.7' );

		// Includes
		$this->plugin_include( 'includes/post.php' );
		$this->plugin_include( 'includes/subscription.php' );
		$this->plugin_include( 'includes/template.php' );
		$this->plugin_include( 'includes/subscription-template.php' );

		// Tables
		orbis_register_table( 'orbis_subscriptions' );
		orbis_register_table( 'orbis_subscription_products', 'orbis_subscription_types' );
		orbis_register_table( 'orbis_subscriptions_invoices' );

		// Admin
		if ( is_admin() ) {
			$this->admin = new Orbis_Subscriptions_Admin( $this );
		}

		// Actions
		add_action( 'init', array( $this, 'init' ), 20 );
		add_action( 'p2p_init', array( $this, 'p2p_init' ) );
	}
	
	public function init() {
		register_taxonomy_for_object_type( 'orbis_payment_method', 'orbis_subscription' );
	}

	public function p2p_init() {
		p2p_register_connection_type( array(
			'name' => 'orbis_subscriptions_to_purchases',
			'from' => 'orbis_subscription',
			'to'   => 'orbis_subs_purchase',
		) );
	}

	public function loaded() {
		$this->load_textdomain( 'orbis_subscriptions', '/languages/' );
	}

	public function install() {
		orbis_install_table( 'orbis_subscriptions', '
			id BIGINT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
			company_id BIGINT(16) UNSIGNED DEFAULT NULL,
			type_id BIGINT(16) UNSIGNED DEFAULT NULL,
			domain_name_id BIGINT(32) UNSIGNED DEFAULT NULL,
			post_id BIGINT(20) UNSIGNED DEFAULT NULL,
			name VARCHAR(128) NOT NULL,
			activation_date DATETIME NOT NULL,
			expiration_date DATETIME NOT NULL,
			cancel_date DATETIME DEFAULT NULL,
			update_date DATETIME DEFAULT NULL,
			email VARCHAR(64) DEFAULT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY post_id (post_id),
			KEY company_id (company_id),
			KEY type_id (type_id),
			KEY domain_name_id (domain_name_id)
		' );

		orbis_install_table( 'orbis_subscription_products', '
			id BIGINT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id BIGINT(20) UNSIGNED DEFAULT NULL,
			name VARCHAR(64) NOT NULL,
			price FLOAT NOT NULL,
			cost_price FLOAT NULL,
			notes TEXT NULL,
			legacy_id BIGINT(16) UNSIGNED NULL,
			`type_default` BOOLEAN NOT NULL DEFAULT FALSE,
			twinfield_article VARCHAR(8) NOT NULL,
			auto_renew BOOLEAN NOT NULL DEFAULT TRUE,
			deprecated BOOLEAN NOT NULL DEFAULT FALSE,
			`interval` VARCHAR(2) NOT NULL DEFAULT "Y",
			PRIMARY KEY  (id)
		' );

		orbis_install_table( 'orbis_subscriptions_invoices', '
			id BIGINT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
			subscription_id BIGINT(16) UNSIGNED NOT NULL,
			invoice_number VARCHAR(8) NOT NULL,
			start_date DATETIME DEFAULT NULL,
			end_date DATETIME DEFAULT NULL,
			user_id BIGINT(20) UNSIGNED DEFAULT NULL,
			create_date DATETIME DEFAULT NULL,
			PRIMARY KEY  (id)
		' );

		parent::install();
	}
}
