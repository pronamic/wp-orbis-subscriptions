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

$contacts_table = $wpdb->prefix . 'orbis_contacts';

$query = "
	SELECT
		user.ID AS user_id,
		user.display_name AS user_display_name,
		user.user_email AS user_email,
		COUNT( organization.id ) AS number_organizations,
		COUNT( subscription.id ) AS number_subscriptions
	FROM
		$wpdb->users AS user
			LEFT JOIN
		{$wpdb->prefix}p2p AS user_organization_p2p
				ON (
					user_organization_p2p.p2p_type = 'orbis_users_to_organizations'
						AND
					user_organization_p2p.p2p_from = user.ID
				)
			LEFT JOIN
		$contacts_table AS organization
				ON organization.post_id = user_organization_p2p.p2p_to
			LEFT JOIN
		(
			SELECT
				subscription.id,
				subscription.customer_id
			FROM
				$wpdb->orbis_subscriptions AS subscription
					INNER JOIN
				$wpdb->orbis_products AS product
						ON subscription.product_id = product.id
					INNER JOIN
				$wpdb->posts AS post
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
			ON organization.id = subscription.customer_id
	GROUP BY
		user.ID
	;
";

$data = $wpdb->get_results( $query );

header( 'Content-Type: text/plain' );

$out = fopen( 'php://output', 'w' );

$date = \date( 'Y-m-d' );

foreach ( $data as $item ) {
	$fields = [
		$item->user_email,
		$item->user_display_name,
		$item->number_subscriptions > 0 ? 'yes' : 'no',
		$date,
		// implode( ', ', wp_list_pluck( $active_subscriptions, 'subscription_name' ) ),
	];

	fputcsv( $out, $fields );
}
