<?php

class Orbis_Subscriptions_Settings {
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}
	
	public function register_settings() {
		// Settings - Reminder
		add_settings_section(
			'orbis_subscriptions_reminder', // id
			__( 'Reminder', 'orbis_subscriptions' ), // title
			'__return_false', // callback
			'orbis_subscriptions' // page
		);
		
		add_settings_field(
			'orbis_subscriptions_update_url', // id
			__( 'URL', 'orbis_subscriptions' ), // title
			array( __CLASS__, 'input_text' ), // callback
			'orbis_subscriptions', // page
			'orbis_subscriptions_reminder', // section
			array( 'label_for' => 'orbis_subscriptions_update_url' ) // args
		);
		
		add_settings_field(
			'orbis_subscriptions_mail_subject', // id
			__( 'Subject', 'orbis_subscriptions' ), // title
			array( __CLASS__, 'input_text' ), // callback
			'orbis_subscriptions', // page
			'orbis_subscriptions_reminder', // section
			array( 'label_for' => 'orbis_subscriptions_mail_subject' ) // args
		);
		
		add_settings_field(
			'orbis_subscriptions_mail_body', // id
			__( 'Template', 'orbis_subscriptions' ), // title
			array( __CLASS__, 'input_editor' ), // callback
			'orbis_subscriptions', // page
			'orbis_subscriptions_reminder', // section
			array( 'label_for' => 'orbis_subscriptions_mail_body' ) // args
		);

		// Register settings
		register_setting( 'orbis_subscriptions', 'orbis_subscriptions_update_url' );
		register_setting( 'orbis_subscriptions', 'orbis_subscriptions_mail_subject' );
		register_setting( 'orbis_subscriptions', 'orbis_subscriptions_mail_body' );
	}
	
	public function admin_menu() {
		add_submenu_page(
			'edit.php?post_type=orbis_subscription',
			__( 'Orbis Subscriptions Settings', 'orbis_subscriptions' ),
			__( 'Settings', 'orbis_subscriptions' ),
			'orbis_subscriptions_settings',
			'orbis_subscriptions_settings',
			array( $this, 'expiration_settings_page' )
		);
	}

	/**
	 * Input text
	 *
	 * @param array $args
	 */
	public static function input_text( $args ) {
		printf(
			'<input name="%s" id="%s" type="text" value="%s" class="%s" />',
			esc_attr( $args['label_for'] ),
			esc_attr( $args['label_for'] ),
			esc_attr( get_option( $args['label_for'] ) ),
			'regular-text'
		);
	}

	/**
	 * Input editor
	 *
	 * @param array $args
	 */
	public static function input_editor( $args ) {
		wp_editor( 
			get_option( $args['label_for'] ),
			$args['label_for']
		);
	}

	public function expiration_settings_page() {
		global $orbis_subscriptions_plugin;
	
		$orbis_subscriptions_plugin->plugin_include( 'admin/settings.php' );
	}
	
	public static function get_mail_subject() {
		$subject = wp_cache_get( 'orbis_subscriptions_mail_subject' );
		
		if ( ! $subject ) {
			$subject = get_option( 'orbis_subscriptions_mail_subject' );
			wp_cache_add( 'orbis_subscriptions_mail_subject', $subject );
		}
		
		return $subject;
	}
	
	public static function get_mail_body() {
		$body = wp_cache_get( 'orbis_subscriptions_mail_body' );
		
		if ( ! $body ) {
			$body = get_option( 'orbis_subscriptions_mail_body' );
			wp_cache_add( 'orbis_subscriptions_mail_body', $body );
		}
		
		return $body;
	}
	
	public static function get_update_url() {
		$url = wp_cache_get( 'orbis_subscriptions_update_url' );
		
		if ( ! $url ) {
			$url = get_option( 'orbis_subscriptions_update_url' );
			wp_cache_add( 'orbis_subscriptions_update_url', $url );
		}
		
		return $url;
	}
	
	public static function get_remind_days_before_expiration() {
		$expiration = wp_cache_get( 'orbis_subscriptions_days_before_expiration' );
		
		if ( ! $expiration ) {
			$expiration = get_option( 'orbis_subscriptions_days_before_expiration' );
			wp_cache_add( 'orbis_subscriptions_days_before_expiration', $expiration );
		}
		
		return $expiration;
	}
	
	public static function get_max_number_of_reminders() {
		$max = wp_cache_get( 'orbis_subscriptions_maximum_reminders' );
		
		if ( ! $max ) {
			$max = get_option( 'orbis_subscriptions_maximum_reminders' );
			wp_cache_add( 'orbis_subscriptions_maximum_reminders', $max );
		}
		
		return $max;
	}
	
	public static function get_minimum_days_between_reminders() {
		$between = wp_cache_get( 'orbis_subscriptions_between_reminders' );
		
		if ( ! $between ) {
			$between = get_option( 'orbis_subscriptions_between_reminders' );
			wp_cache_add( 'orbis_subscriptions_between_reminders', $between );
		}
		
		return $between;
	}
}
