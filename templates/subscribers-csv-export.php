<?php
/**
 * Subscribers CSV export
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2025 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Subscriptions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$where = '1 = 1';

if ( \array_key_exists( 'product', $_GET ) ) {
	$product_filter_string = \sanitize_text_field( \wp_unslash( $_GET['product'] ) );

	$product_slugs = \wp_parse_list( $product_filter_string );

	$where .= $wpdb->prepare(
		sprintf(
			' AND post.post_name IN ( %s )',
			implode( ', ', array_fill( 0, count( $product_slugs ), '%s' ) )
		),
		$product_slugs
	);
}

$query = "
	SELECT
		user.ID AS user_id,
		user.display_name AS user_display_name,
		user.user_email AS user_email,
		COUNT( company.id ) AS number_companies,
		COUNT( subscription.id ) AS number_subscriptions
	FROM
		wp_users AS user
			LEFT JOIN
		wp_p2p AS user_company_p2p
				ON (
					user_company_p2p.p2p_type = 'orbis_users_to_companies'
						AND
					user_company_p2p.p2p_from = user.ID
				)
			LEFT JOIN
		wp_orbis_companies AS company
				ON company.post_id = user_company_p2p.p2p_to
			LEFT JOIN
		(
			SELECT
				subscription.id,
				subscription.company_id
			FROM
				wp_orbis_subscriptions AS subscription
					INNER JOIN
				wp_orbis_products AS product
						ON subscription.product_id = product.id
					INNER JOIN
				wp_posts AS post
						ON product.post_id = post.ID
			WHERE
				$where
					AND
				(
					subscription.cancel_date IS NULL
						OR
					subscription.expiration_date > NOW()
				)
		) AS subscription
			ON company.id = subscription.company_id
	GROUP BY
		user.ID
	;
";

$data = $wpdb->get_results( $query );

header( 'Content-Type: text/plain' );

$out = fopen( 'php://output', 'w' );

foreach ( $data as $item ) {
	$fields = [
		$item->user_email,
		$item->user_display_name,
		$item->number_subscriptions > 0 ? 'yes' : 'no',
		// implode( ', ', wp_list_pluck( $active_subscriptions, 'subscription_name' ) ),
	];

	fputcsv( $out, $fields );
}
