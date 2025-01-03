<?php
/**
 * Query controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Subscriptions
 */

namespace Pronamic\Orbis\Subscriptions;

use WP_Query;

/**
 * Query controller class
 */
class QueryController {
	/**
	 * Setup.
	 * 
	 * @return void
	 */
	public function setup() {
		\add_filter( 'posts_clauses', [ $this, 'posts_clauses' ], 10, 2 );

		\add_filter( 'posts_orderby', [ $this, 'posts_orderby' ], 10, 2 );

		\add_filter( 'query_vars', [ $this, 'query_vars' ] );
	}

	/**
	 * Posts clauses
	 *
	 * @link http://codex.wordpress.org/WordPress_Query_Vars
	 * @link http://codex.wordpress.org/Custom_Queries
	 * @param array    $pieces
	 * @param WP_Query $query
	 * @return string
	 */
	public function posts_clauses( $pieces, $query ) {
		global $wpdb;

		$post_type = $query->get( 'post_type' );

		if ( 'orbis_subscription' === $post_type ) {
			// Fields
			$fields = ',
				subscription.activation_date AS subscription_activation_date,
				subscription.expiration_date AS subscription_expiration_date,
				subscription.cancel_date AS subscription_cancel_date,
				subscription.update_date AS subscription_update_date,
				product.name AS product_name,
				product.price AS product_price
			';

			// Join
			$join = "
				LEFT JOIN
					$wpdb->orbis_subscriptions AS subscription
						ON $wpdb->posts.ID = subscription.post_id
				LEFT JOIN
					$wpdb->orbis_products AS product
						ON subscription.product_id = product.id
			";

			// Where
			$where = '';

			$product_post_id = $query->get( 'orbis_product_post_id' );

			if ( '' !== $product_post_id ) {
				$where = $wpdb->prepare( 'AND product.post_id = %d', $product_post_id );
			}

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
						$wpdb->orbis_products AS product
							ON subscription.product_id = product.id
				";

				// Where
				$where = $wpdb->prepare( 'AND subscription.cancel_date IS NULL AND subscription_product.name LIKE %s', $like );

				$pieces['join']    .= $join;
				$pieces['where']   .= $where;
				$pieces['groupby'] .= "$wpdb->posts.ID";
			}
		}

		return $pieces;
	}

	/**
	 * Query vars.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/query_vars/
	 * @param array $query_vars Query vars.
	 * @return array
	 */
	public function query_vars( $query_vars ) {
		$query_vars[] = 'subscriptions_like';
		$query_vars[] = 'orbis_product_post_id';

		return $query_vars;
	}

	/**
	 * Posts orderby.
	 *
	 * @see https://github.com/WordPress/WordPress/blob/4.6.1/wp-includes/query.php#L3355-L3363
	 * @see https://github.com/WordPress/WordPress/blob/4.6.1/wp-includes/query.php#L2310-L2403
	 * @param string $orderby
	 * @param WP_Query $query
	 * @return string
	 */
	public function posts_orderby( $orderby, $query ) {
		if ( 'active_subscriptions' === $query->get( 'orderby' ) ) {
			$orderby = 'subscription.cancel_date ' . $query->get( 'order' );
		}

		return $orderby;
	}
}
