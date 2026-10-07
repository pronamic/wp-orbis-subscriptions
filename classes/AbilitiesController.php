<?php
/**
 * Abilities controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Subscriptions
 */

namespace Pronamic\Orbis\Subscriptions;

/**
 * Abilities controller class
 *
 * @link https://developer.wordpress.org/news/2025/11/introducing-the-wordpress-abilities-api/
 */
class AbilitiesController {
	/**
	 * Setup.
	 *
	 * @return void
	 */
	public function setup() {
		\add_action( 'wp_abilities_api_categories_init', $this->register_ability_categories( ... ) );
		\add_action( 'wp_abilities_api_init', $this->register_abilities( ... ) );

		\add_filter( 'orbis_mcp_server_tools', $this->mcp_server_tools( ... ) );
	}

	/**
	 * Add abilities to the Orbis MCP server tools.
	 *
	 * @link https://github.com/pronamic/orbis-mcp-server
	 * @param string[] $tools Ability names.
	 * @return string[]
	 */
	public function mcp_server_tools( $tools ) {
		$tools[] = 'orbis-subscriptions/search';

		return $tools;
	}

	/**
	 * Register ability categories.
	 *
	 * @return void
	 */
	public function register_ability_categories() {
		\wp_register_ability_category(
			'orbis-subscriptions',
			[
				'label'       => \__( 'Orbis Subscriptions', 'orbis-subscriptions' ),
				'description' => \__( 'Abilities for working with Orbis subscriptions.', 'orbis-subscriptions' ),
			]
		);
	}

	/**
	 * Register abilities.
	 *
	 * @return void
	 */
	public function register_abilities() {
		$nullable_string = [
			'type' => [ 'string', 'null' ],
		];

		$nullable_integer = [
			'type' => [ 'integer', 'null' ],
		];

		\wp_register_ability(
			'orbis-subscriptions/search',
			[
				'label'               => \__( 'Search subscriptions', 'orbis-subscriptions' ),
				'description'         => \__( 'Searches Orbis subscriptions by name, customer or product and returns the matching subscriptions with their customer, product, dates, comment count and latest comments. Use orbis/search-comments for the full comment history of a subscription.', 'orbis-subscriptions' ),
				'category'            => 'orbis-subscriptions',
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'properties'           => [
						'search'      => [
							'type'        => 'string',
							'description' => \__( 'Search term, matched against the subscription name, the subscription title, the customer name and the product name.', 'orbis-subscriptions' ),
						],
						'customer_id' => [
							'type'        => 'integer',
							'description' => \__( 'Limit the results to subscriptions of this customer, an Orbis contact ID (person or organization).', 'orbis-subscriptions' ),
							'minimum'     => 1,
						],
						'product_id'  => [
							'type'        => 'integer',
							'description' => \__( 'Limit the results to subscriptions of this Orbis product ID.', 'orbis-subscriptions' ),
							'minimum'     => 1,
						],
						'status'      => [
							'type'        => 'string',
							'description' => \__( 'Limit the results to active subscriptions (not cancelled or not yet expired), cancelled subscriptions, or any subscription.', 'orbis-subscriptions' ),
							'enum'        => [ 'any', 'active', 'cancelled' ],
							'default'     => 'any',
						],
						'comments'    => [
							'type'        => 'integer',
							'description' => \__( 'Number of latest comments to include per subscription. Use orbis/search-comments for the full comment history.', 'orbis-subscriptions' ),
							'minimum'     => 0,
							'maximum'     => 10,
							'default'     => 3,
						],
						'per_page'    => [
							'type'        => 'integer',
							'description' => \__( 'Maximum number of subscriptions to return.', 'orbis-subscriptions' ),
							'minimum'     => 1,
							'maximum'     => 100,
							'default'     => 20,
						],
						'page'        => [
							'type'        => 'integer',
							'description' => \__( 'Page of results to return.', 'orbis-subscriptions' ),
							'minimum'     => 1,
							'default'     => 1,
						],
					],
					'additionalProperties' => false,
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'total'         => [ 'type' => 'integer' ],
						'page'          => [ 'type' => 'integer' ],
						'per_page'      => [ 'type' => 'integer' ],
						'subscriptions' => [
							'type'  => 'array',
							'items' => [
								'type'       => 'object',
								'properties' => [
									'id'              => [ 'type' => 'integer' ],
									'post_id'         => [ 'type' => 'integer' ],
									'title'           => [ 'type' => 'string' ],
									'name'            => [ 'type' => 'string' ],
									'url'             => [ 'type' => 'string' ],
									'customer'        => [
										'type'       => [ 'object', 'null' ],
										'properties' => [
											'id'      => [ 'type' => 'integer' ],
											'post_id' => [ 'type' => 'integer' ],
											'name'    => [ 'type' => 'string' ],
											'type'    => [ 'type' => 'string' ],
										],
									],
									'product'         => [
										'type'       => [ 'object', 'null' ],
										'properties' => [
											'id'       => [ 'type' => 'integer' ],
											'post_id'  => $nullable_integer,
											'name'     => [ 'type' => 'string' ],
											'price'    => [ 'type' => [ 'number', 'null' ] ],
											'interval' => $nullable_string,
										],
									],
									'activation_date' => $nullable_string,
									'expiration_date' => $nullable_string,
									'cancel_date'     => $nullable_string,
									'end_date'        => $nullable_string,
									'billed_to'       => $nullable_string,
									'comment_count'   => [ 'type' => 'integer' ],
									'comments'        => [
										'type'  => 'array',
										'items' => [
											'type'       => 'object',
											'properties' => [
												'id'      => [ 'type' => 'integer' ],
												'type'    => [ 'type' => 'string' ],
												'author'  => [ 'type' => 'string' ],
												'date'    => [ 'type' => 'string' ],
												'content' => [ 'type' => 'string' ],
												'url'     => [ 'type' => 'string' ],
											],
										],
									],
								],
							],
						],
					],
				],
				'execute_callback'    => $this->search_subscriptions( ... ),
				'permission_callback' => fn() => \current_user_can( 'edit_posts' ),
				'meta'                => [
					'show_in_rest' => true,
					'annotations'  => [
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					],
				],
			]
		);
	}

	/**
	 * Search subscriptions.
	 *
	 * @param array $input Input.
	 * @return array
	 */
	public function search_subscriptions( $input = [] ) {
		global $wpdb;

		$input = \wp_parse_args(
			(array) $input,
			[
				'search'      => '',
				'customer_id' => null,
				'product_id'  => null,
				'status'      => 'any',
				'comments'    => 3,
				'per_page'    => 20,
				'page'        => 1,
			]
		);

		$has_customers = \class_exists( \Pronamic\Orbis\Contacts\ContactsTable::class );
		$has_products  = isset( $wpdb->orbis_products );

		$fields = '
			subscription.id,
			subscription.post_id,
			subscription.customer_id,
			subscription.product_id,
			subscription.name,
			subscription.activation_date,
			subscription.expiration_date,
			subscription.cancel_date,
			subscription.end_date,
			subscription.billed_to,
			post.post_title,
			post.comment_count
		';

		$join = "
			$wpdb->orbis_subscriptions AS subscription
				INNER JOIN
			$wpdb->posts AS post
					ON subscription.post_id = post.ID
		";

		if ( $has_customers ) {
			$contacts_table = \Pronamic\Orbis\Contacts\ContactsTable::get_table_name();

			$fields .= ',
				customer.post_id AS customer_post_id,
				customer.name AS customer_name,
				customer_post.post_type AS customer_type
			';

			$join .= "
				LEFT JOIN
			$contacts_table AS customer
					ON subscription.customer_id = customer.id
				LEFT JOIN
			$wpdb->posts AS customer_post
					ON customer.post_id = customer_post.ID
			";
		}

		if ( $has_products ) {
			$fields .= ',
				product.post_id AS product_post_id,
				product.name AS product_name,
				product.price AS product_price,
				product.interval AS product_interval
			';

			$join .= "
				LEFT JOIN
			$wpdb->orbis_products AS product
					ON subscription.product_id = product.id
			";
		}

		$conditions = [
			"post.post_status = 'publish'",
		];

		$search = \trim( (string) $input['search'] );

		if ( '' !== $search ) {
			$like = '%' . $wpdb->esc_like( $search ) . '%';

			$search_conditions = [
				$wpdb->prepare( 'subscription.name LIKE %s', $like ),
				$wpdb->prepare( 'post.post_title LIKE %s', $like ),
			];

			if ( $has_customers ) {
				$search_conditions[] = $wpdb->prepare( 'customer.name LIKE %s', $like );
			}

			if ( $has_products ) {
				$search_conditions[] = $wpdb->prepare( 'product.name LIKE %s', $like );
			}

			$conditions[] = '( ' . \implode( ' OR ', $search_conditions ) . ' )';
		}

		if ( null !== $input['customer_id'] ) {
			$conditions[] = $wpdb->prepare( 'subscription.customer_id = %d', $input['customer_id'] );
		}

		if ( null !== $input['product_id'] ) {
			$conditions[] = $wpdb->prepare( 'subscription.product_id = %d', $input['product_id'] );
		}

		switch ( $input['status'] ) {
			case 'active':
				$conditions[] = '( subscription.cancel_date IS NULL OR subscription.expiration_date >= CURRENT_DATE() )';

				break;
			case 'cancelled':
				$conditions[] = 'subscription.cancel_date IS NOT NULL';

				break;
		}

		$where = \implode( ' AND ', $conditions );

		$per_page = \max( 1, \min( 100, (int) $input['per_page'] ) );
		$page     = \max( 1, (int) $input['page'] );
		$offset   = ( $page - 1 ) * $per_page;

		$total = (int) $wpdb->get_var( "SELECT COUNT( subscription.id ) FROM $join WHERE $where;" );

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT $fields FROM $join WHERE $where ORDER BY subscription.id DESC LIMIT %d OFFSET %d;",
				$per_page,
				$offset
			)
		);

		$post_ids = \array_map( fn( $row ) => (int) $row->post_id, $results );

		\_prime_post_caches( $post_ids, false, false );

		$comments_per_subscription = \max( 0, \min( 10, (int) $input['comments'] ) );

		if ( $comments_per_subscription > 0 ) {
			$comments = $this->get_latest_comments( $post_ids, $comments_per_subscription );

			foreach ( $results as $row ) {
				$row->comments = $comments[ (int) $row->post_id ] ?? [];
			}
		}

		return [
			'total'         => $total,
			'page'          => $page,
			'per_page'      => $per_page,
			'subscriptions' => \array_map( $this->format_subscription( ... ), $results ),
		];
	}

	/**
	 * Get the latest approved comments of the specified posts in a single query.
	 *
	 * @param int[] $post_ids Post IDs.
	 * @param int   $number   Maximum number of comments per post.
	 * @return array<int, object[]> Comment rows grouped by post ID, newest first.
	 */
	private function get_latest_comments( $post_ids, $number ) {
		global $wpdb;

		if ( 0 === \count( $post_ids ) ) {
			return [];
		}

		$placeholders = \implode( ', ', \array_fill( 0, \count( $post_ids ), '%d' ) );

		$query = "
			SELECT
				comment_ID,
				comment_post_ID,
				comment_type,
				comment_author,
				comment_date,
				comment_content
			FROM
				(
					SELECT
						comment_ID,
						comment_post_ID,
						comment_type,
						comment_author,
						comment_date,
						comment_content,
						ROW_NUMBER() OVER ( PARTITION BY comment_post_ID ORDER BY comment_date_gmt DESC, comment_ID DESC ) AS comment_rank
					FROM
						$wpdb->comments
					WHERE
						comment_post_ID IN ( $placeholders )
							AND
						comment_approved = '1'
				) AS ranked
			WHERE
				comment_rank <= %d
			ORDER BY
				comment_post_ID,
				comment_rank
			;
		";

		$rows = $wpdb->get_results( $wpdb->prepare( $query, ...[ ...$post_ids, $number ] ) );

		$comments = [];

		foreach ( $rows as $row ) {
			$comments[ (int) $row->comment_post_ID ][] = $row;
		}

		return $comments;
	}

	/**
	 * Format subscription row for ability output.
	 *
	 * @param object $row Database row.
	 * @return array
	 */
	private function format_subscription( $row ) {
		$customer = null;

		if ( null !== $row->customer_id && isset( $row->customer_name ) ) {
			$customer = [
				'id'      => (int) $row->customer_id,
				'post_id' => (int) $row->customer_post_id,
				'name'    => $row->customer_name,
				'type'    => (string) $row->customer_type,
			];
		}

		$product = null;

		if ( null !== $row->product_id && isset( $row->product_name ) ) {
			$product = [
				'id'       => (int) $row->product_id,
				'post_id'  => null === $row->product_post_id ? null : (int) $row->product_post_id,
				'name'     => $row->product_name,
				'price'    => null === $row->product_price ? null : (float) $row->product_price,
				'interval' => $row->product_interval,
			];
		}

		$url = (string) \get_permalink( (int) $row->post_id );

		$subscription = [
			'id'              => (int) $row->id,
			'post_id'         => (int) $row->post_id,
			'title'           => $row->post_title,
			'name'            => $row->name,
			'url'             => $url,
			'customer'        => $customer,
			'product'         => $product,
			'activation_date' => $row->activation_date,
			'expiration_date' => $row->expiration_date,
			'cancel_date'     => $row->cancel_date,
			'end_date'        => $row->end_date,
			'billed_to'       => $row->billed_to,
			'comment_count'   => (int) $row->comment_count,
		];

		if ( isset( $row->comments ) ) {
			$subscription['comments'] = \array_map(
				fn( $comment ) => [
					'id'      => (int) $comment->comment_ID,
					'type'    => $comment->comment_type,
					'author'  => $comment->comment_author,
					'date'    => $comment->comment_date,
					'content' => \wp_html_excerpt( \wp_strip_all_tags( $comment->comment_content ), 300, '…' ),
					'url'     => $url . '#comment-' . $comment->comment_ID,
				],
				$row->comments
			);
		}

		return $subscription;
	}
}
