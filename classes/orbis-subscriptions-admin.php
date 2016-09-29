<?php

/**
 * Title: Orbis Subscriptions admin class
 * Description:
 * Copyright: Copyright (c) 2005 - 2014
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 */
class Orbis_Subscriptions_Admin {
	/**
	 * Plugin
	 */
	private $plugin;

	//////////////////////////////////////////////////

	/**
	 * Constructs and initialize an Orbis Subscriptions admin object
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		// Actions
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_action( 'save_post', array( $this, 'save_post' ) );
	}

	//////////////////////////////////////////////////

	/**
	 * Admin menu
	 */
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

	//////////////////////////////////////////////////

	/**
	 * Page statistics
	 */
	public function page_statistics() {
		$this->plugin->plugin_include( 'admin/page-statistics.php' );
	}

	//////////////////////////////////////////////////

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
					array( 'cancel_date' => current_time( 'mysql' ) ),
					array( 'post_id' => $post_id ),
					array( '%s' ),
					array( '%d' )
				);

				// Comment
				$user = wp_get_current_user();

				$comment_content = sprintf(
					__( "This subscription is just '%s' by %s.", 'orbis_subscriptions' ),
					__( 'canceled', 'orbis_subscriptions' ),
					$user->display_name
				);

				$content = wp_kses_post( filter_input( INPUT_POST, 'orbis_subscription_cancel_content', FILTER_UNSAFE_RAW ), wp_kses_allowed_html() );
				if ( ! empty( $content ) ) {
					$comment_content .= "\r\n\r\n";

					$comment_content .= $content;
				}

				$data = array(
					'comment_post_ID' => $post_id,
					'comment_content' => $comment_content,
					'comment_author'  => 'Orbis',
					'comment_type'    => 'orbis_comment',
				);

				wp_insert_comment( $data );
			}
		}
	}
}
