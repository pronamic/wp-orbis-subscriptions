<?php
/**
 * Plugin
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Subscriptions
 */

namespace Pronamic\Orbis\Subscriptions;

class Plugin {
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
 
		return self::$instance;
	}

	private function __construct() {
		include __DIR__ . '/../includes/post.php';
		include __DIR__ . '/../includes/subscription.php';
		include __DIR__ . '/../includes/template.php';
		include __DIR__ . '/../includes/subscription-template.php';

		if ( \is_admin() ) {
			$this->admin = new AdminController( $this );
		}

		add_action( 'init', [ $this, 'init' ] );
		add_action( 'p2p_init', [ $this, 'p2p_init' ] );

		add_shortcode( 'orbis_subscriptions_without_agreement', [ $this, 'shortcode_subscriptions_without_agreement' ] );
	}

	public function init() {
		global $wpdb;

		$wpdb->orbis_subscriptions          = $wpdb->prefix . 'orbis_subscriptions';
		$wpdb->orbis_subscription_products  = $wpdb->prefix . 'orbis_subscription_products';
		$wpdb->orbis_subscriptions_invoices = $wpdb->prefix . 'orbis_subscriptions_invoices';

		$version = '1.1.10';

		if ( \get_option( 'orbis_subscriptions_db_version' ) !== $version ) {
			$this->install();

			\update_option( 'orbis_subscriptions_db_version', $version );
		}

		\register_taxonomy_for_object_type( 'orbis_payment_method', 'orbis_subscription' );
	}

	public function p2p_init() {
		p2p_register_connection_type(
			[
				'name' => 'orbis_subscriptions_to_persons',
				'from' => 'orbis_subscription',
				'to'   => 'orbis_person',
			] 
		);
	}

	/**
	 * Install.
	 * 
	 * @link https://codex.wordpress.org/Creating_Tables_with_Plugins
	 * @return void
	 */
	public function install() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "
			CREATE TABLE $wpdb->orbis_subscriptions (
				id BIGINT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
				company_id BIGINT(16) UNSIGNED DEFAULT NULL,
				type_id BIGINT(16) UNSIGNED DEFAULT NULL,
				domain_name_id BIGINT(32) UNSIGNED DEFAULT NULL,
				post_id BIGINT(20) UNSIGNED DEFAULT NULL,
				name VARCHAR(128) NOT NULL,
				activation_date DATE NOT NULL,
				expiration_date DATE NOT NULL,
				cancel_date DATE DEFAULT NULL,
				update_date DATETIME DEFAULT NULL,
				end_date DATE DEFAULT NULL,
				billed_to DATE DEFAULT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY post_id (post_id),
				KEY company_id (company_id),
				KEY type_id (type_id),
				KEY domain_name_id (domain_name_id)
			) $charset_collate;

			CREATE TABLE $wpdb->orbis_subscription_products (
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
				`interval` VARCHAR(2) NOT NULL DEFAULT 'Y',
				time_per_year INT(16) UNSIGNED DEFAULT NULL,
				PRIMARY KEY  (id)
			) $charset_collate;

			CREATE TABLE $wpdb->orbis_subscriptions_invoices (
				id BIGINT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
				created_at DATETIME NOT NULL,
				subscription_id BIGINT(16) UNSIGNED NOT NULL,
				invoice_number VARCHAR(128) NOT NULL,
				invoice_data TEXT,
				start_date DATE NULL,
				end_date DATE NULL,
				user_id BIGINT(20) UNSIGNED DEFAULT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY subscription_invoice_start (subscription_id, invoice_number, start_date),
				KEY invoice_number (invoice_number)
			) $charset_collate;
		";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $sql );

		\maybe_convert_table_to_utf8mb4( $wpdb->orbis_subscriptions );
		\maybe_convert_table_to_utf8mb4( $wpdb->orbis_subscription_products );
		\maybe_convert_table_to_utf8mb4( $wpdb->orbis_subscriptions_invoices );
	}

	public function shortcode_subscriptions_without_agreement() {
		$return = '';

		ob_start();

		$this->plugin_include( 'templates/subscriptions-without-agreement.php' );

		$return = ob_get_contents();

		ob_end_clean();

		return $return;
	}
}
