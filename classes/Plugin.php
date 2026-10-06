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

	/**
	 * Admin controller.
	 *
	 * @var AdminController|null
	 */
	private $admin = null;

	public static function instance() {
		self::$instance ??= new self();
 
		return self::$instance;
	}

	private function __construct() {
		include __DIR__ . '/../includes/post.php';

		if ( \is_admin() ) {
			$this->admin = new AdminController( $this );
		}

		( new RenewController() )->setup();
		( new SubscribersExportController() )->setup();
		( new TemplateController() )->setup();
		( new QueryController() )->setup();
		( new AbilitiesController() )->setup();

		add_action( 'init', $this->init( ... ) );

		add_shortcode( 'orbis_subscriptions_without_agreement', $this->shortcode_subscriptions_without_agreement( ... ) );
	}

	public function init() {
		global $wpdb;

		$wpdb->orbis_subscriptions  = $wpdb->prefix . 'orbis_subscriptions';
		$wpdb->orbis_invoices       = $wpdb->prefix . 'orbis_invoices';
		$wpdb->orbis_invoices_lines = $wpdb->prefix . 'orbis_invoices_lines';

		$version = '1.2.0';

		if ( \get_option( 'orbis_subscriptions_db_version' ) !== $version ) {
			$this->install();

			\update_option( 'orbis_subscriptions_db_version', $version );
		}

		\register_taxonomy_for_object_type( 'orbis_payment_method', 'orbis_subscription' );
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

		$sql = <<<SQL
			CREATE TABLE $wpdb->orbis_subscriptions (
				id BIGINT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
				customer_id BIGINT(20) UNSIGNED DEFAULT NULL,
				product_id BIGINT(16) UNSIGNED DEFAULT NULL,
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
				KEY customer_id (customer_id),
				KEY product_id (product_id),
				KEY domain_name_id (domain_name_id)
			) $charset_collate;
			SQL;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $sql );

		\maybe_convert_table_to_utf8mb4( $wpdb->orbis_subscriptions );

		$this->add_foreign_keys();
	}

	/**
	 * Add foreign keys.
	 *
	 * `dbDelta` does not support foreign keys, so they are added separately
	 * when they do not exist yet. References that would violate a foreign
	 * key are cleaned up first. The customer foreign key is only added when
	 * the Orbis Contacts table exists.
	 *
	 * @return void
	 */
	private function add_foreign_keys() {
		global $wpdb;

		$table = $wpdb->orbis_subscriptions;

		$contacts_table = $wpdb->prefix . 'orbis_contacts';

		$foreign_keys = [
			[
				'name'      => $wpdb->prefix . 'orbis_subscriptions_customer_id',
				'reference' => $contacts_table,
				'cleanup'   => "UPDATE $table SET customer_id = NULL WHERE customer_id IS NOT NULL AND customer_id NOT IN ( SELECT id FROM $contacts_table );",
				'sql'       => "ALTER TABLE $table ADD CONSTRAINT {$wpdb->prefix}orbis_subscriptions_customer_id FOREIGN KEY ( customer_id ) REFERENCES $contacts_table ( id ) ON DELETE SET NULL;",
			],
		];

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared -- `dbDelta` does not support foreign keys, the queries are built from table names only.
		foreach ( $foreign_keys as $foreign_key ) {
			$reference_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s;', $wpdb->esc_like( $foreign_key['reference'] ) ) );

			if ( null === $reference_exists ) {
				continue;
			}

			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = %s AND CONSTRAINT_NAME = %s AND CONSTRAINT_TYPE = 'FOREIGN KEY';",
					$table,
					$foreign_key['name']
				)
			);

			if ( null !== $exists ) {
				continue;
			}

			$wpdb->query( $foreign_key['cleanup'] );

			$wpdb->query( $foreign_key['sql'] );
		}
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared
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
