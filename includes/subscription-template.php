<?php

use Pronamic\WordPress\Money\Money;

function orbis_subscription_get_the_price() {
	global $post;

	$price = null;

	if ( isset( $post->subscription_type_price ) ) {
		$price = $post->subscription_type_price;
	}

	return $price;
}

function orbis_subscription_the_price() {
	$price = new Money( orbis_subscription_get_the_price(), 'EUR' );
	echo esc_html( $price->format_i18n() );
}
