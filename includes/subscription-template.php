<?php

function orbis_subscription_get_the_price( ) {
	global $post;

	$price = null;

	if ( isset( $post->subscription_type_price ) ) {
		$price = $post->subscription_type_price;
	}

	return $price;
}

function orbis_subscription_the_price( ) {
	echo orbis_price( orbis_subscription_get_the_price() );
}
