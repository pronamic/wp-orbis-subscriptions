<?php

class Orbis_Subscriptions_Expiration {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		
		add_action( 'init', array( $this, 'expiring_licenses_submit' ) );
		add_action( 'init', array( $this, 'extend_license_submit' ) );
	}
	
	public function admin_menu() {
		add_submenu_page(
			'edit.php?post_type=orbis_subscription',
			__( 'Orbis Expiring Subscriptions', 'orbis_subscriptions' ),
			__( 'Expiring Subscriptions', 'orbis_subscriptions' ),
			'orbis_view_subscriptions',
			'orbis_view_subscriptions',
			array( $this, 'expiring_licenses_manager' )
		);
	}
	
	public function expiring_licenses_manager() {
		// Get all subscriptions that are going to expire
		$subscription_factory = new Orbis_Subscriptions_Expiration_Factory();
		
		// 7 Days
		$one_week = new DateTime();
		$one_week->add( new DateInterval( 'P1W') );
		
		$subscriptions = $subscription_factory->get_expiring_in( $one_week );
		
		$nonce = wp_nonce_field( 'orbis_subscription_expiration_manager', 'orbis_subscription_expiration_manager_nonce', true, false );
				
		// Load view
		include ORBIS_SUBSCRIPTIONS_FOLDER . '/templates/expiring_licenses_manager.php';
	}
	
	public function expiring_licenses_submit() {
		if ( ! isset( $_POST ) )
			return;
				
		if ( ! isset( $_POST['orbis_subscription_expiration_manager_nonce' ] ) )
			return;
		
		if ( ! wp_verify_nonce( $_POST['orbis_subscription_expiration_manager_nonce'], 'orbis_subscription_expiration_manager' ) )
			return;
		
		// Get mail contents
		$mail_subject = Orbis_Subscriptions_Settings::get_mail_subject();
		$mail_body = Orbis_Subscriptions_Settings::get_mail_body();
		$url = Orbis_Subscriptions_Settings::get_update_url();
				
		if ( isset( $_POST['submit_single'] ) ) {
			$subscription_ids = array( filter_input( INPUT_POST, 'submit_single', FILTER_VALIDATE_INT ) );
		} else {
			$subscription_ids = filter_input( INPUT_POST, 'subscription_ids', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY );
		}
		
		foreach ( $subscription_ids as $subscription_id ) {
			$subscription = new Orbis_Subscription( $subscription_id );
			$subscription->send_reminder( $mail_subject, $mail_body, $url );
		}
	}
	
	public function extend_license_submit() {
		if ( ! isset( $_POST ) )
			return;
				
		if ( ! isset( $_POST['submit_extend'] ) )
			return;
		
		$subscription_id = filter_input( INPUT_POST, 'submit_extend', FILTER_VALIDATE_INT );
		
		// Extend the license
		$subscription = new Orbis_Subscription( $subscription_id );
		$subscription->extend();
	}
}
