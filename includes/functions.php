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

function orbis_subscriptions_suggest_subscription_id() {
	global $wpdb;

	$term = filter_input( INPUT_GET, 'term', FILTER_SANITIZE_STRING );

	$fields = '';
	$join   = '';
	$where  = '';

	if ( isset( $wpdb->orbis_timesheets ) ) {
		$start = new \Pronamic\WordPress\DateTime\DateTime( 'first day of January this year' );

		$fields .= ', SUM( timesheet.number_seconds ) AS registered_time';
		$fields .= ', product.time_per_year';

		$join .= "
			LEFT JOIN
				$wpdb->orbis_timesheets AS timesheet
					ON (
						timesheet.subscription_id = subscription.id
							AND
						timesheet.date > DATE_ADD( subscription.activation_date, INTERVAL TIMESTAMPDIFF( YEAR, subscription.activation_date, NOW() ) YEAR )
					)
		";
	}

	$query = "
		SELECT
			subscription.id AS id,
			subscription.status,
			CONCAT( subscription.id, '. ', IFNULL( CONCAT( product.name, ' - ' ), '' ), subscription.name ) AS text
			$fields
		FROM
			$wpdb->orbis_subscriptions AS subscription
				LEFT JOIN
			$wpdb->orbis_subscription_products AS product
					ON subscription.type_id = product.id
			$join
		WHERE
			(
				subscription.cancel_date IS NULL
					OR
				subscription.expiration_date > NOW()
			)
				AND
			(
				subscription.name LIKE %s
					OR
				product.name LIKE %s
			)
			$where
		GROUP BY
			subscription.id
		ORDER BY
			subscription.id
		;
	";

	$like = '%' . $wpdb->esc_like( $term ) . '%';

	$query = $wpdb->prepare( $query, $like, $like ); // unprepared SQL

	$subscriptions = $wpdb->get_results( $query ); // unprepared SQL

	$data = [];

	foreach ( $subscriptions as $subscription ) {
		$result     = new stdClass();
		$result->id = $subscription->id;

		$text = $subscription->text;

		if ( isset( $subscription->time_per_year ) ) {
			$text = sprintf(
				'%s ( %s / %s )',
				$text,
				orbis_time( \intval( $subscription->registered_time ) ),
				orbis_time( $subscription->time_per_year )
			);
		}

		if ( 'strippenkaart' === $subscription->status ) {
			$text = sprintf(
				'%s » %s',
				$text,
				'Tijdregistraties op strippenkaart'
			);

			/**
			 * Mark option as disabled.
			 *
			 * @link https://select2.org/data-sources/formats
			 */
			$result->disabled = true;
		}

		$result->text = $text;

		$data[] = $result;
	}

	wp_send_json( $data );
}

add_action( 'wp_ajax_subscription_id_suggest', 'orbis_subscriptions_suggest_subscription_id' );
