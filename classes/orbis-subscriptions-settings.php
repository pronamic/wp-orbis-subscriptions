<?php

class Orbis_Subscriptions_Settings {
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}
	
	public function register_settings() {
		register_setting( 'orbis_subscriptions', 'orbis_subscriptions_mail_subject' );
		register_setting( 'orbis_subscriptions', 'orbis_subscriptions_mail_body' );
		register_setting( 'orbis_subscriptions', 'orbis_subscriptions_update_url' );
		
		register_setting( 'orbis_subscriptions', 'orbis_subscriptions_days_before_expiration' );
		register_setting( 'orbis_subscriptions', 'orbis_subscriptions_maximum_reminders' );
		register_setting( 'orbis_subscriptions', 'orbis_subscriptions_between_reminders' );
		
	}
	
	public function admin_menu() {
		add_submenu_page(
			'edit.php?post_type=orbis_subscription',
			__( 'Expiration settings', 'orbis_subscriptions' ),
			__( 'Expiration settings', 'orbis_subscriptions' ),
			'orbis_subscriptions_settings',
			'orbis_subscriptions_settings',
			array( $this, 'expiration_settings_page' )
		);
	}
	
	public function expiration_settings_page() {
		$url = self::get_update_url();
		$mail_contents = self::get_mail_body();
		$mail_subject = self::get_mail_subject();
		
		$remind_days_before_expiration = self::get_remind_days_before_expiration();
		$max_number_of_reminders = self::get_max_number_of_reminders();
		$minimum_days_between_reminders = self::get_minimum_days_between_reminders();
		
		include ORBIS_SUBSCRIPTIONS_FOLDER . '/templates/expiration_settings_page.php';
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
