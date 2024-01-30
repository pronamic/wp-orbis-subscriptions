<?php

function orbis_subscriptions_render_invoices() {
	if ( is_singular( 'orbis_subscription' ) ) {
		include __DIR__ . '/../templates/subscription-invoices.php';
	}
}

add_action( 'orbis_after_main_content', 'orbis_subscriptions_render_invoices' );


function orbis_subscriptions_render_details() {
	if ( is_singular( 'orbis_subscription' ) ) {
		include __DIR__ . '/../templates/subscription-details.php';
	}
}

add_action( 'orbis_before_side_content', 'orbis_subscriptions_render_details' );


function orbis_subscriptions_render_domain_name_subscriptions() {
	if ( is_singular( 'orbis_domain_name' ) ) {
		include __DIR__ . '/../templates/domain-name-subscriptions.php';
	}
}

add_action( 'orbis_after_main_content', 'orbis_subscriptions_render_domain_name_subscriptions' );

/**
 * Orbis company subscriptions
 */
function orbis_subscriptions_render_company_subscriptions() {
	if ( is_singular( 'orbis_company' ) ) {
		include __DIR__ . '/../templates/company-subscriptions.php';
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
