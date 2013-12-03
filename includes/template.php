<?php

function orbis_subscriptions_render_invoices() {
	if ( is_singular( 'orbis_subscription' ) ) {
		global $orbis_subscriptions_plugin;

		$orbis_subscriptions_plugin->plugin_include( 'templates/subscription-invoices.php' );
	}
}

add_action( 'orbis_after_main_content', 'orbis_subscriptions_render_invoices' );


function orbis_subscriptions_render_details() {
	if ( is_singular( 'orbis_subscription' ) ) {
		global $orbis_subscriptions_plugin;

		$orbis_subscriptions_plugin->plugin_include( 'templates/subscription-details.php' );
	}
}

add_action( 'orbis_before_side_content', 'orbis_subscriptions_render_details' );


function orbis_subscriptions_render_renewal() {
	if ( is_singular( 'orbis_subscription' ) ) {
		global $orbis_subscriptions_plugin;

		$orbis_subscriptions_plugin->plugin_include( 'templates/subscription-renewal.php' );
	}
}

add_action( 'orbis_before_side_content', 'orbis_subscriptions_render_renewal' );


function orbis_subscriptions_render_domain_name_subscriptions() {
	if ( is_singular( 'orbis_domain_name' ) ) {
		global $orbis_subscriptions_plugin;

		$orbis_subscriptions_plugin->plugin_include( 'templates/domain-name-subscriptions.php' );
	}
}

add_action( 'orbis_after_main_content', 'orbis_subscriptions_render_domain_name_subscriptions' );

/**
 * Orbis company subscriptions
 */
function orbis_subscriptions_render_company_subscriptions() {
	if ( is_singular( 'orbis_company' ) ) {
		global $orbis_subscriptions_plugin;

		$orbis_subscriptions_plugin->plugin_include( 'templates/company-subscriptions.php' );
	}
}

// add_action( 'orbis_after_main_content', 'orbis_subscriptions_render_company_subscriptions' );

function orbis_company_sections_subscriptions( $sections ) {
	$sections[] = array(
		'id'       => 'subscriptions',
		'name'     => __( 'Subscriptions', 'orbis_subscriptions' ),
		'callback' => 'orbis_subscriptions_render_company_subscriptions',
	);

	return $sections;
}

add_filter( 'orbis_company_sections', 'orbis_company_sections_subscriptions' );

/**
 * Orbis subscriptions post class
 *
 * @param array $classes
 */
function orbis_subscriptions_post_class( $classes ) {
	global $post;

	if ( isset( $post->subscription_cancel_date ) && ! empty( $post->subscription_cancel_date ) ) {
		$classes[] = 'orbis-status-cancelled';
	}

	return $classes;
}

add_filter( 'post_class', 'orbis_subscriptions_post_class' );
