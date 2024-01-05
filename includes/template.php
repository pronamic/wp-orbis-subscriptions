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

function orbis_company_sections_subscriptions( $sections ) {
	$sections[] = [
		'id'       => 'subscriptions',
		'name'     => __( 'Subscriptions', 'orbis-subscriptions' ),
		'callback' => 'orbis_subscriptions_render_company_subscriptions',
	];

	return $sections;
}

add_filter( 'orbis_company_sections', 'orbis_company_sections_subscriptions' );

/**
 * Orbis subscription purchase
 */
function orbis_subscriptions_render_purchase_details() {
	if ( is_singular( 'orbis_subs_purchase' ) ) {
		global $orbis_subscriptions_plugin;

		$orbis_subscriptions_plugin->plugin_include( 'templates/subscription-purchase-details.php' );
	}
}

add_action( 'orbis_after_main_content', 'orbis_subscriptions_render_purchase_details' );
