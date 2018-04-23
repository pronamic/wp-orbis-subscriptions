<?php

/**
 * Returns a row from the orbis_subscriptions table where the post_id matches the parameter.
 *
 * @global type $wpdb
 *
 * @param int $post_id
 * @return object
 */
function orbis_subscription_get_data( $post_id ) {
	global $wpdb;

	$query = "
		SELECT
			subscription.id,
			subscription.post_id,
			subscription.name,
			subscription.email,
			subscription.activation_date,
			subscription.expiration_date,
			subscription.cancel_date,
			subscription.update_date,
			company.id AS company_id,
			company.name AS company_name,
			company.e_mail AS company_email,
			product.id AS product_id,
			product.name AS product_name,
			product.price AS product_price,
			product.auto_renew AS product_auto_renew
		FROM
			$wpdb->orbis_subscriptions AS subscription
				LEFT JOIN
			$wpdb->orbis_subscription_products AS product
					ON subscription.type_id = product.id
				LEFT JOIN
			$wpdb->orbis_companies AS company
					ON subscription.company_id = company.id
		WHERE
			subscription.post_id = %d
	";

	$query = $wpdb->prepare( $query, $post_id );

	return $wpdb->get_row( $query );
}


if ( ! function_exists( 'orbis_date2mysql' ) ) :

	function orbis_date2mysql( DateTime $date = null ) {
		$result = null;

		if ( null !== $date ) {
			$result = $date->format( 'Y-m-d H:i:s' );
		}

		return $result;
	}

endif;

function orbis_subscriptions_suggest_subscription_id() {
	global $wpdb;

	$term = filter_input( INPUT_GET, 'term', FILTER_SANITIZE_STRING );

	$query = "
		SELECT
			subscription.id AS id,
			CONCAT( subscription.id, '. ', IFNULL( CONCAT( product.name, ' - ' ), '' ), subscription.name ) AS text
		FROM
			$wpdb->orbis_subscriptions AS subscription
				LEFT JOIN
			$wpdb->orbis_subscription_products AS product
					ON subscription.type_id = product.id
		WHERE
			subscription.expiration_date > NOW()
				AND
			(
				subscription.name LIKE %s
					OR
				product.name LIKE %s
			)
		GROUP BY
			subscription.id
		ORDER BY
			subscription.id
	";

	$like = '%' . $wpdb->esc_like( $term ) . '%';

	$query = $wpdb->prepare( $query, $like, $like ); // unprepared SQL

	$projects = $wpdb->get_results( $query ); // unprepared SQL

	$data = $wpdb->get_results( $query );

	echo wp_json_encode( $data );

	die();
}

add_action( 'wp_ajax_subscription_id_suggest', 'orbis_subscriptions_suggest_subscription_id' );

function orbis_subscriptions_suggest_keychain_id() {
	global $wpdb;

	$term = filter_input( INPUT_GET, 'term', FILTER_SANITIZE_STRING );

	$query = "
		SELECT
			keychain.ID AS id,
			keychain.post_title AS text
		FROM
			$wpdb->posts AS keychain
		WHERE
			keychain.post_title LIKE %s
		GROUP BY
			keychain.id
		ORDER BY
			keychain.id
	";

	$like = '%' . $wpdb->esc_like( $term ) . '%';

	$query = $wpdb->prepare( $query, $like ); // unprepared SQL

	$keychains = $wpdb->get_results( $query ); // unprepared SQL

	$data = $wpdb->get_results( $query );

	echo wp_json_encode( $data );

	die();
}

add_action( 'wp_ajax_keychain_id_suggest', 'orbis_subscriptions_suggest_keychain_id' );
