<?php

/**
 * Query vars.
 *
 * @see https://developer.wordpress.org/reference/hooks/query_vars/
 */
function orbis_subscriptions_query_vars( $query_vars ) {
	$query_vars[] = 'subscriptions_like';

	return $query_vars;
}

add_filter( 'query_vars', 'orbis_subscriptions_query_vars' );

/**
 * Posts clauses
 *
 * @see http://codex.wordpress.org/WordPress_Query_Vars
 * @see http://codex.wordpress.org/Custom_Queries
 *
 * @param array $pieces
 * @param WP_Query $query
 * @return string
 */
function orbis_subscriptions_posts_clauses( $pieces, $query ) {
	global $wpdb;

	$post_type = $query->get( 'post_type' );

	if ( 'orbis_subscription' === $post_type ) {
		// Fields
		$fields = ',
			subscription.activation_date AS subscription_activation_date,
			subscription.expiration_date AS subscription_expiration_date,
			subscription.cancel_date AS subscription_cancel_date,
			subscription.update_date AS subscription_update_date,
			subscription_type.name AS subscription_type_name,
			subscription_type.price AS subscription_type_price
		';

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

	// Subscriptions like
	if ( 'orbis_company' === $post_type ) {
		$like = $query->get( 'subscriptions_like', null );

		if ( null !== $like ) {
			// Join
			$join = "
				LEFT JOIN
					$wpdb->orbis_companies AS company
						ON $wpdb->posts.ID = company.post_id
				LEFT JOIN
					$wpdb->orbis_subscriptions AS subscription
						ON subscription.company_id = company.id
				LEFT JOIN
					$wpdb->orbis_subscription_products AS subscription_product
						ON subscription.type_id = subscription_product.id
			";

			// Where
			$where = $wpdb->prepare( "AND subscription.cancel_date IS NULL AND subscription_product.name LIKE %s", $like );

			$pieces['join']   .= $join;
			$pieces['where']  .= $where;
		}
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

	if ( isset( $post ) && 'orbis_subscription' === get_post_type( $post ) ) {
		$subscription = new Orbis_Subscription( $post );
	}

	return $subscription;
}
