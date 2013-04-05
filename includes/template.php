<?php

function orbis_subscriptions_render_invoices() {
	if ( is_singular( 'orbis_subscription' ) ) {
		global $orbis_subscriptions_plugin;
	
		$orbis_subscriptions_plugin->plugin_include( 'templates/subscription-invoices.php' );
	}
}

add_action( 'orbis_after_main_content', 'orbis_subscriptions_render_invoices' );


function orbis_subscriptions_render_company_subscriptions() {
	if ( is_singular( 'orbis_company' ) ) {
		global $orbis_subscriptions_plugin;

		$orbis_subscriptions_plugin->plugin_include( 'templates/company-subscriptions.php' );
	}
}

add_action( 'orbis_after_main_content', 'orbis_subscriptions_render_company_subscriptions' );
