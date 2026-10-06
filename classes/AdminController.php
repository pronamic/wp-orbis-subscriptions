<?php
/**
 * Admin controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Subscriptions
 */

namespace Pronamic\Orbis\Subscriptions;

/**
 * Title: Orbis Subscriptions admin class
 * Description:
 * Copyright: Copyright (c) 2005 - 2014
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 */
class AdminController {
	/**
	 * Constructs and initialize an Orbis Subscriptions admin object
	 */
	public function __construct(
		/**
		 * Plugin
		 */
		private $plugin
	) {
		// Actions
		add_action( 'admin_menu', $this->admin_menu( ... ) );

		add_action( 'save_post', $this->save_post( ... ) );

		add_action( 'admin_enqueue_scripts', $this->admin_enqueue_scripts( ... ) );
	}

	/**
	 * Admin enqueue scripts.
	 *
	 * The contact picker script is registered by the Orbis Contacts plugin.
	 *
	 * @param string $hook_suffix Hook suffix.
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {
		if ( ! \in_array( $hook_suffix, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}

		$screen = \get_current_screen();

		if ( null === $screen || 'orbis_subscription' !== $screen->post_type ) {
			return;
		}

		if ( \wp_script_is( 'orbis-contact-select2', 'registered' ) ) {
			\wp_enqueue_script( 'orbis-contact-select2' );
		}
	}

	/**
	 * Admin menu
	 */
	public function admin_menu() {
		\add_submenu_page(
			'edit.php?post_type=orbis_subscription',
			__( 'Orbis Subscriptions Statistics', 'orbis-subscriptions' ),
			__( 'Statistics', 'orbis-subscriptions' ),
			'manage_options',
			'orbis_subscriptions_statistics',
			$this->page_statistics( ... )
		);

		\add_submenu_page(
			'edit.php?post_type=orbis_subscription',
			\__( 'Orbis Subscriptions Billing', 'orbis-subscriptions' ),
			\__( 'Billing', 'orbis-subscriptions' ),
			'manage_options',
			'orbis_subscriptions_billing',
			$this->page_billing( ... )
		);
	}

	/**
	 * Page statistics.
	 *
	 * @return void
	 */
	public function page_statistics() {
		include __DIR__ . '/../admin/page-statistics.php';
	}

	/**
	 * Page billing.
	 *
	 * @return void
	 */
	public function page_billing() {
		include __DIR__ . '/../admin/page-billing.php';
	}

	/**
	 * Save post
	 */
	public function save_post( $post_id ) {
		if ( filter_has_var( INPUT_POST, 'orbis_subscription_cancel' ) ) {
			$nonce = filter_input( INPUT_POST, 'orbis_subscription_cancel_nonce', FILTER_SANITIZE_STRING );

			if ( wp_verify_nonce( $nonce, 'orbis_subscription_cancel' ) ) {
				global $wpdb;

				$result = $wpdb->update(
					$wpdb->orbis_subscriptions,
					[ 'cancel_date' => current_time( 'mysql', true ) ],
					[ 'post_id' => $post_id ],
					[ '%s' ],
					[ '%d' ]
				);

				// Comment
				$user = wp_get_current_user();

				$comment_content = sprintf(
					__( "This subscription is just '%1\$s' by %2\$s.", 'orbis-subscriptions' ), //phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
					__( 'canceled', 'orbis-subscriptions' ),
					$user->display_name
				);

				$content = wp_kses_post( filter_input( INPUT_POST, 'orbis_subscription_cancel_content', FILTER_UNSAFE_RAW ), wp_kses_allowed_html() );
				if ( ! empty( $content ) ) {
					$comment_content .= "\r\n\r\n";

					$comment_content .= $content;
				}

				$data = [
					'comment_post_ID' => $post_id,
					'comment_content' => $comment_content,
					'comment_author'  => 'Orbis',
					'comment_type'    => 'orbis_comment',
				];

				wp_insert_comment( $data );
			}
		}
	}
}
