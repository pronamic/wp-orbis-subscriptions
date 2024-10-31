<?php

use Pronamic\WordPress\Money\Money;

function orbis_subscriptions_create_initial_post_types() {
	global $orbis_subscriptions_plugin;

	register_post_type(
		'orbis_subscription',
		[
			'label'         => __( 'Subscriptions', 'orbis-subscriptions' ),
			'labels'        => [
				'name'               => _x( 'Subscriptions', 'post type general name', 'orbis-subscriptions' ),
				'singular_name'      => _x( 'Subscription', 'post type singular name', 'orbis-subscriptions' ),
				'add_new'            => _x( 'Add New', 'orbis_subscription', 'orbis-subscriptions' ),
				'add_new_item'       => __( 'Add New Subscription', 'orbis-subscriptions' ),
				'edit_item'          => __( 'Edit Subscription', 'orbis-subscriptions' ),
				'new_item'           => __( 'New Subscription', 'orbis-subscriptions' ),
				'view_item'          => __( 'View Subscription', 'orbis-subscriptions' ),
				'search_items'       => __( 'Search Subscriptions', 'orbis-subscriptions' ),
				'not_found'          => __( 'No subscriptions found', 'orbis-subscriptions' ),
				'not_found_in_trash' => __( 'No subscriptions found in Trash', 'orbis-subscriptions' ),
				'parent_item_colon'  => __( 'Parent Subscription:', 'orbis-subscriptions' ),
				'menu_name'          => __( 'Subscriptions', 'orbis-subscriptions' ),
			],
			'public'        => true,
			'menu_position' => 30,
			'menu_icon'     => 'dashicons-share-alt',
			'supports'      => [
				'title',
				'editor',
				'author',
				'comments',
				'revisions',
			],
			'has_archive'   => true,
			'show_in_rest'  => true,
			'rest_base'     => 'orbis/subscriptions',
			'rewrite'       => [
				'slug' => _x( 'subscriptions', 'slug', 'orbis-subscriptions' ),
			],
		]
	);
}

add_action( 'init', 'orbis_subscriptions_create_initial_post_types', 0 ); // highest priority

/**
 * Add domain keychain meta boxes
 */
function orbis_subscriptions_add_meta_boxes() {
	add_meta_box(
		'orbis_subscription_details',
		__( 'Subscription Details', 'orbis-subscriptions' ),
		'orbis_subscription_details_meta_box',
		'orbis_subscription',
		'normal',
		'high'
	);

	add_meta_box(
		'orbis_subscription_actions',
		__( 'Subscription Actions', 'orbis-subscriptions' ),
		'orbis_subscription_actions_meta_box',
		'orbis_subscription',
		'normal',
		'default'
	);
}

add_action( 'add_meta_boxes', 'orbis_subscriptions_add_meta_boxes' );

/**
 * Post clauses
 *
 * @param array    $pieces
 * @param WP_Query $query
 * @return array
 */
function orbis_subscription_post_clauses( $pieces, $query ) {
	global $wpdb;
	$only_active = filter_input( INPUT_GET, 'only_active', FILTER_VALIDATE_BOOLEAN );

	$join = "
		LEFT JOIN
			$wpdb->orbis_subscriptions AS sub
				ON $wpdb->posts.ID = sub.post_id
	";

	$where = '';

	if ( $only_active ) {
		$where .= '
			AND
			(
				sub.cancel_date IS NULL
			OR
				sub.expiration_date >= CURRENT_DATE()
			)
		';
	}

	$pieces['join']  .= $join;
	$pieces['where'] .= $where;

	return $pieces;
}

add_filter( 'posts_clauses', 'orbis_subscription_post_clauses', 10, 2 );

/**
 * Subscription details meta box
 *
 * @param array $post
 */
function orbis_subscription_details_meta_box( $post ) {
	include __DIR__ . '/../admin/meta-box-subscription-details.php';
}

/**
 * Subscription actions meta box
 *
 * @param array $post
 */
function orbis_subscription_actions_meta_box( $post ) {
	include __DIR__ . '/../admin/meta-box-subscription-actions.php';
}

/**
 * Save subscription details
 */
function orbis_save_subscription_details( $post_id, $post ) {
	// Doing autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Verify nonce
	$nonce = filter_input( INPUT_POST, 'orbis_subscription_details_meta_box_nonce', FILTER_SANITIZE_STRING );
	if ( ! wp_verify_nonce( $nonce, 'orbis_save_subscription_details' ) ) {
		return;
	}

	// Check permissions
	if ( ! ( 'orbis_subscription' === get_post_type( $post_id ) && current_user_can( 'edit_post', $post_id ) ) ) {
		return;
	}

	// OK
	$definition = [
		'_orbis_subscription_company_id'      => FILTER_SANITIZE_STRING,
		'_orbis_subscription_product_id'      => FILTER_SANITIZE_STRING,
		'_orbis_subscription_name'            => FILTER_SANITIZE_STRING,
		'_orbis_subscription_agreement_id'    => FILTER_SANITIZE_STRING,
		'_orbis_subscription_activation_date' => FILTER_SANITIZE_STRING,
		'_orbis_invoice_reference'            => FILTER_SANITIZE_STRING,
		'_orbis_invoice_line_description'     => FILTER_SANITIZE_STRING,
	];

	$data = filter_input_array( INPUT_POST, $definition );

	foreach ( $data as $key => $value ) {
		if ( empty( $value ) ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}

add_action( 'save_post', 'orbis_save_subscription_details', 10, 2 );

/**
 * Sync subscription with Orbis tables
 */
function orbis_save_subscription_sync( $post_id, $post ) {
	// Doing autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check post type
	if ( ! ( 'orbis_subscription' === get_post_type( $post_id ) ) ) {
		return;
	}

	// Revision
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	// Publish
	if ( 'publish' !== $post->post_status ) {
		return;
	}

	$company_id      = get_post_meta( $post_id, '_orbis_subscription_company_id', true );
	$product_id      = get_post_meta( $post_id, '_orbis_subscription_product_id', true );
	$name            = get_post_meta( $post_id, '_orbis_subscription_name', true );
	$agreement       = get_post_meta( $post_id, '_orbis_subscription_agreement_id', true );

	// Get the subscription object
	$subscription = new Pronamic\Orbis\Subscriptions\Subscription( $post );

	// Set this subscriptions details
	$subscription
		->set_company_id( $company_id )
		->set_product_id( $product_id )
		->set_post_id( $post_id )
		->set_name( $name )
		->set_agreement_id( $agreement );

	/**
	 * Activation date.
	 * 
	 * @link https://github.com/pronamic/wp-orbis-subscriptions/issues/40
	 */
	$activation_date_string = get_post_meta( $post_id, '_orbis_subscription_activation_date', true );

	try {
		$result = DateTimeImmutable::createFromFormat( 'Y-m-d', $activation_date_string, new DateTimeZone( 'GMT' ) );

		if ( false !== $result ) {
			$activation_date = $result->setTime( 0, 0, 0 );

			$subscription->set_activation_date( $activation_date );
		}
	} catch ( \Exception $e ) {
		// Nothing to-do.
	}

	$subscription->save();
}

add_action( 'save_post', 'orbis_save_subscription_sync', 20, 2 );

/**
 * Insert post data
 *
 * @see https://github.com/WordPress/WordPress/blob/3.5.1/wp-includes/post.php#L2864
 */
function orbis_subscriptions_insert_post_data( $data, $postarr ) {
	if ( isset( $data['post_type'] ) && 'orbis_subscription' === $data['post_type'] ) {
		global $wpdb;

		$product_id = filter_input( INPUT_POST, '_orbis_subscription_product_id', FILTER_SANITIZE_STRING );
		$name       = filter_input( INPUT_POST, '_orbis_subscription_name', FILTER_SANITIZE_STRING );

		$product_name = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM $wpdb->orbis_products WHERE id = %d;", $product_id ) );

		if ( ! empty( $product_name ) && ! empty( $name ) ) {
			$post_title = $product_name . ' - ' . $name;

			$post_name = sanitize_title_with_dashes( $post_title );

			$data['post_title'] = $post_title;
			$data['post_name']  = $post_name;
		}
	}

	return $data;
}

add_filter( 'wp_insert_post_data', 'orbis_subscriptions_insert_post_data', 10, 2 );
