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
				'description'         => \__( 'Searches Orbis subscriptions by name, company or product and returns the matching subscriptions with their company, product and dates.', 'orbis-subscriptions' ),
				'category'            => 'orbis-subscriptions',
				'input_schema'        => [
					'type'                 => 'object',
					'default'              => [],
					'properties'           => [
						'search'     => [
							'type'        => 'string',
							'description' => \__( 'Search term, matched against the subscription name, the subscription title, the company name and the product name.', 'orbis-subscriptions' ),
						],
						'company_id' => [
							'type'        => 'integer',
							'description' => \__( 'Limit the results to subscriptions of this Orbis company ID.', 'orbis-subscriptions' ),
							'minimum'     => 1,
						],
						'product_id' => [
							'type'        => 'integer',
							'description' => \__( 'Limit the results to subscriptions of this Orbis product ID.', 'orbis-subscriptions' ),
							'minimum'     => 1,
						],
						'status'     => [
							'type'        => 'string',
							'description' => \__( 'Limit the results to active subscriptions (not cancelled or not yet expired), cancelled subscriptions, or any subscription.', 'orbis-subscriptions' ),
							'enum'        => [ 'any', 'active', 'cancelled' ],
							'default'     => 'any',
						],
						'per_page'   => [
							'type'        => 'integer',
							'description' => \__( 'Maximum number of subscriptions to return.', 'orbis-subscriptions' ),
							'minimum'     => 1,
							'maximum'     => 100,
							'default'     => 20,
						],
						'page'       => [
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
									'company'         => [
										'type'       => [ 'object', 'null' ],
										'properties' => [
											'id'      => [ 'type' => 'integer' ],
											'post_id' => $nullable_integer,
											'name'    => [ 'type' => 'string' ],
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
				'search'     => '',
				'company_id' => null,
				'product_id' => null,
				'status'     => 'any',
				'per_page'   => 20,
				'page'       => 1,
			]
		);

		$has_companies = isset( $wpdb->orbis_companies );
		$has_products  = isset( $wpdb->orbis_products );

		$fields = '
			subscription.id,
			subscription.post_id,
			subscription.company_id,
			subscription.product_id,
			subscription.name,
			subscription.activation_date,
			subscription.expiration_date,
			subscription.cancel_date,
			subscription.end_date,
			subscription.billed_to,
			post.post_title
		';

		$join = "
			$wpdb->orbis_subscriptions AS subscription
				INNER JOIN
			$wpdb->posts AS post
					ON subscription.post_id = post.ID
		";

		if ( $has_companies ) {
			$fields .= ',
				company.post_id AS company_post_id,
				company.name AS company_name
			';

			$join .= "
				LEFT JOIN
			$wpdb->orbis_companies AS company
					ON subscription.company_id = company.id
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

			if ( $has_companies ) {
				$search_conditions[] = $wpdb->prepare( 'company.name LIKE %s', $like );
			}

			if ( $has_products ) {
				$search_conditions[] = $wpdb->prepare( 'product.name LIKE %s', $like );
			}

			$conditions[] = '( ' . \implode( ' OR ', $search_conditions ) . ' )';
		}

		if ( null !== $input['company_id'] ) {
			$conditions[] = $wpdb->prepare( 'subscription.company_id = %d', $input['company_id'] );
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

		return [
			'total'         => $total,
			'page'          => $page,
			'per_page'      => $per_page,
			'subscriptions' => \array_map( $this->format_subscription( ... ), $results ),
		];
	}

	/**
	 * Format subscription row for ability output.
	 *
	 * @param object $row Database row.
	 * @return array
	 */
	private function format_subscription( $row ) {
		$company = null;

		if ( null !== $row->company_id && isset( $row->company_name ) ) {
			$company = [
				'id'      => (int) $row->company_id,
				'post_id' => null === $row->company_post_id ? null : (int) $row->company_post_id,
				'name'    => $row->company_name,
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

		return [
			'id'              => (int) $row->id,
			'post_id'         => (int) $row->post_id,
			'title'           => $row->post_title,
			'name'            => $row->name,
			'url'             => (string) \get_permalink( (int) $row->post_id ),
			'company'         => $company,
			'product'         => $product,
			'activation_date' => $row->activation_date,
			'expiration_date' => $row->expiration_date,
			'cancel_date'     => $row->cancel_date,
			'end_date'        => $row->end_date,
			'billed_to'       => $row->billed_to,
		];
	}
}
