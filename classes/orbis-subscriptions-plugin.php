<?php

class Orbis_Subscriptions_Plugin extends Orbis_Plugin {
	public function __construct( $file ) {
		parent::__construct( $file );

		$this->set_name( 'orbis_subscriptions' );
		$this->set_db_version( '1.1.6' );

		$this->plugin_include( 'includes/post.php' );
		$this->plugin_include( 'includes/subscription.php' );
		$this->plugin_include( 'includes/api.php' );
		$this->plugin_include( 'includes/shortcodes.php' );
		$this->plugin_include( 'includes/template.php' );
		$this->plugin_include( 'includes/subscription-template.php' );

		orbis_register_table( 'orbis_subscriptions' );
		orbis_register_table( 'orbis_subscription_products', 'orbis_subscription_types' );
		orbis_register_table( 'orbis_subscriptions_invoices' );

		if ( is_admin() ) {
			$this->admin = new Orbis_Subscriptions_Admin( $this );
		}
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
			license_key VARCHAR(32) DEFAULT NULL,
			license_key_md5 VARCHAR(32) DEFAULT NULL,
			email VARCHAR(64) DEFAULT NULL,
			sent_notifications TINYINT(2) DEFAULT 0,
			PRIMARY KEY  (id),
			UNIQUE KEY license_key (license_key),
			UNIQUE KEY license_key_md5 (license_key_md5),
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
			`interval` VARCHAR(1) NOT NULL DEFAULT "Y",
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
