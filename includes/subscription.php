<?php

/**
 * Posts clauses
 *
 * http://codex.wordpress.org/WordPress_Query_Vars
 * http://codex.wordpress.org/Custom_Queries
 *
 * @param array $pieces
 * @param WP_Query $query
 * @return string
 */
function orbis_subscriptions_posts_clauses( $pieces, $query ) {
	global $wpdb;

	$post_type = $query->get( 'post_type' );

	if ( $post_type == 'orbis_subscription' ) {
		// Fields
		$fields = ",
			subscription.activation_date AS subscription_activation_date,
			subscription.expiration_date AS subscription_expiration_date,
			subscription.cancel_date AS subscription_cancel_date,
			subscription.update_date AS subscription_update_date,
			subscription_type.name AS subscription_type_name,
			subscription_type.price AS subscription_type_price
		";

		// Join
		$join = "
			LEFT JOIN
				$wpdb->orbis_subscriptions AS subscription
					ON $wpdb->posts.ID = subscription.post_id
			LEFT JOIN
				$wpdb->orbis_subscription_products AS subscription_type
					ON subscription.type_id = subscription_type.id
		";

		// Where
		$where = '';

		$pieces['join']   .= $join;
		$pieces['fields'] .= $fields;
		$pieces['where']  .= $where;
	}

	return $pieces;
}

add_filter( 'posts_clauses', 'orbis_subscriptions_posts_clauses', 10, 2 );

/**
 * Get Orbis subscription
 *
 * @see https://github.com/woothemes/woocommerce/blob/v2.0.20/woocommerce-core-functions.php#L22
 * @see https://github.com/woothemes/woocommerce/blob/v2.0.20/classes/class-wc-product-factory.php#L16
 *
 * @param unknown $post
 */
function get_orbis_subscription( $post = null ) {
	$post = get_post( $post );

	if ( isset( $post ) && $post->post_type == 'orbis_subscription' ) {
		$subscription = new Orbis_Subscription( $post );
	}

	return $subscription;
}