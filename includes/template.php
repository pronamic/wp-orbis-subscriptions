<?php

function orbis_subscriptions_render_invoices() {
	global $orbis_subscriptions_plugin;
	
	$orbis_subscriptions_plugin->plugin_include( 'templates/subscription-invoices.php' );
}

add_action( 'orbis_after_main_content', 'orbis_subscriptions_render_invoices' );
