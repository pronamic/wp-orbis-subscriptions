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

	register_post_type(
		'orbis_subs_product',
		[
			'label'         => __( 'Subscription Products', 'orbis-subscriptions' ),
			'labels'        => [
				'name'               => _x( 'Subscription Products', 'post type general name', 'orbis-subscriptions' ),
				'singular_name'      => _x( 'Subscription Product', 'post type singular name', 'orbis-subscriptions' ),
				'add_new'            => _x( 'Add New', 'orbis_subs_type', 'orbis-subscriptions' ),
				'add_new_item'       => __( 'Add New Subscription Product', 'orbis-subscriptions' ),
				'edit_item'          => __( 'Edit Subscription Product', 'orbis-subscriptions' ),
				'new_item'           => __( 'New Subscription Product', 'orbis-subscriptions' ),
				'view_item'          => __( 'View Subscription Product', 'orbis-subscriptions' ),
				'search_items'       => __( 'Search Subscription Products', 'orbis-subscriptions' ),
				'not_found'          => __( 'No subscription products found', 'orbis-subscriptions' ),
				'not_found_in_trash' => __( 'No subscription products found in Trash', 'orbis-subscriptions' ),
				'parent_item_colon'  => __( 'Parent Subscription Product:', 'orbis-subscriptions' ),
				'menu_name'          => __( 'Products', 'orbis-subscriptions' ),
			],
			'public'        => true,
			'menu_position' => 30,
			'show_in_menu'  => 'edit.php?post_type=orbis_subscription',
			'supports'      => [
				'title',
				'editor',
				'author',
				'comments',
				'thumbnail',
				'revisions',
			],
			'has_archive'   => true,
			'rewrite'       => [
				'slug' => _x( 'subscription-products', 'slug', 'orbis-subscriptions' ),
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

	add_meta_box(
		'orbis_subscription_product_details',
		__( 'Subscription Product Details', 'orbis-subscriptions' ),
		'orbis_subscription_product_details_meta_box',
		'orbis_subs_product',
		'normal',
		'high'
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
 * Subscription product details meta box
 *
 * @param array $post
 */
function orbis_subscription_product_details_meta_box( $post ) {
	include __DIR__ . '/../admin/meta-box-subscription-product-details.php';
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
		'_orbis_subscription_type_id'         => FILTER_SANITIZE_STRING,
		'_orbis_subscription_name'            => FILTER_SANITIZE_STRING,
		'_orbis_subscription_person_id'       => FILTER_SANITIZE_STRING,
		'_orbis_subscription_email'           => FILTER_VALIDATE_EMAIL,
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

	$company_id = get_post_meta( $post_id, '_orbis_subscription_company_id', true );
	$type_id    = get_post_meta( $post_id, '_orbis_subscription_type_id', true );
	$name       = get_post_meta( $post_id, '_orbis_subscription_name', true );
	$email      = get_post_meta( $post_id, '_orbis_subscription_email', true );
	$agreement  = get_post_meta( $post_id, '_orbis_subscription_agreement_id', true );

	// Get the subscription object
	$subscription = new Pronamic\Orbis\Subscriptions\Subscription( $post );

	// Set this subscriptions details
	$subscription
		->set_company_id( $company_id )
		->set_product_id( $type_id )
		->set_post_id( $post_id )
		->set_email( $email )
		->set_name( $name )
		->set_agreement_id( $agreement );

	if ( ! $subscription->get_id() ) {
		// Current DateTime
		$current = new DateTime();

		$subscription->set_activation_date( $current );

		// Expiration DateTime
		$expiration = clone $current;
		$expiration->modify( '+1 year' );

		$subscription->set_expiration_date( $expiration );
	}

	if ( 0 === $subscription->count_invoices() ) {
		$activation_date_string = get_post_meta( $post_id, '_orbis_subscription_activation_date', true );

		$activation_date = new \DateTime( $activation_date_string, \wp_timezone() );

		$subscription->set_activation_date( $activation_date );

		// Expiration DateTime
		$expiration_date = clone $activation_date;
		$expiration_date->modify( '+1 year' );

		$subscription->set_expiration_date( $expiration_date );
	}

	// Save this subscription!
	$subscription->save();
}

add_action( 'save_post', 'orbis_save_subscription_sync', 20, 2 );

/**
 * Keychain edit columns
 */
function orbis_subscription_edit_columns( $columns ) {
	return [
		'cb'                        => '<input type="checkbox" />',
		'title'                     => __( 'Title', 'orbis-subscriptions' ),
		'orbis_subscription_person' => __( 'Person', 'orbis-subscriptions' ),
		'author'                    => __( 'Author', 'orbis-subscriptions' ),
		'comments'                  => __( 'Comments', 'orbis-subscriptions' ),
		'date'                      => __( 'Date', 'orbis-subscriptions' ),
	];
}

add_filter( 'manage_edit-orbis_subscription_columns', 'orbis_subscription_edit_columns' );

/**
 * Keychain column
 *
 * @param string $column
 */
function orbis_subscription_column( $column ) {
	$id = get_the_ID();

	switch ( $column ) {
		case 'orbis_subscription_person':
			$person_id = get_post_meta( $id, '_orbis_subscription_person_id', true );

			if ( ! empty( $person_id ) ) {
				printf(
					'<a href="%s" target="_blank">%s</a>',
					esc_attr( get_permalink( $person_id ) ),
					esc_html( get_the_title( $person_id ) )
				);
			}

			break;
	}
}

add_action( 'manage_posts_custom_column', 'orbis_subscription_column' );

/**
 * Insert post data
 *
 * @see https://github.com/WordPress/WordPress/blob/3.5.1/wp-includes/post.php#L2864
 */
function orbis_subscriptions_insert_post_data( $data, $postarr ) {
	if ( isset( $data['post_type'] ) && 'orbis_subscription' === $data['post_type'] ) {
		global $wpdb;

		$type_id = filter_input( INPUT_POST, '_orbis_subscription_type_id', FILTER_SANITIZE_STRING );
		$name    = filter_input( INPUT_POST, '_orbis_subscription_name', FILTER_SANITIZE_STRING );

		$type_name = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM $wpdb->orbis_subscription_products WHERE id = %d;", $type_id ) );

		if ( ! empty( $type_name ) && ! empty( $name ) ) {
			$post_title = $type_name . ' - ' . $name;

			$post_name = sanitize_title_with_dashes( $post_title );

			$data['post_title'] = $post_title;
			$data['post_name']  = $post_name;
		}
	}

	return $data;
}

add_filter( 'wp_insert_post_data', 'orbis_subscriptions_insert_post_data', 10, 2 );



/**
 * Save subscription product details
 */
function orbis_save_subscription_product_details( $post_id, $post ) {
	// Doing autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Verify nonce
	$nonce = filter_input( INPUT_POST, 'orbis_subscription_product_details_meta_box_nonce', FILTER_SANITIZE_STRING );
	if ( ! wp_verify_nonce( $nonce, 'orbis_save_subscription_product_details' ) ) {
		return;
	}

	// Check permissions
	if ( ! ( 'orbis_subs_product' === get_post_type( $post_id ) && current_user_can( 'edit_post', $post_id ) ) ) {
		return;
	}

	// OK
	global $wp_locale;

	$definition = [
		'_orbis_subscription_product_price'       => [
			'filter'  => FILTER_VALIDATE_FLOAT,
			'flags'   => FILTER_FLAG_ALLOW_THOUSAND,
			'options' => [ 'decimal' => $wp_locale->number_format['decimal_point'] ],
		],
		'_orbis_subscription_product_cost_price'  => [
			'filter'  => FILTER_VALIDATE_FLOAT,
			'flags'   => FILTER_FLAG_ALLOW_THOUSAND,
			'options' => [ 'decimal' => $wp_locale->number_format['decimal_point'] ],
		],
		'_orbis_subscription_product_auto_renew'  => FILTER_VALIDATE_BOOLEAN,
		'_orbis_subscription_product_deprecated'  => FILTER_VALIDATE_BOOLEAN,
		'_orbis_subscription_product_interval'    => FILTER_SANITIZE_STRING,
		'_orbis_subscription_product_description' => FILTER_SANITIZE_STRING,
		'_orbis_subscription_product_link'        => FILTER_SANITIZE_STRING,
		'_orbis_subscription_product_cancel_note' => FILTER_SANITIZE_STRING,
	];

	$data = filter_input_array( INPUT_POST, $definition );

	update_orbis_subscription_product_meta( $post_id, $data );
}

add_action( 'save_post', 'orbis_save_subscription_product_details', 10, 2 );

/**
 * Update Orbis task meta data
 *
 * @param int   $post_id
 * @param array $data
 */
function update_orbis_subscription_product_meta( $post_id, array $data = null ) {
	if ( is_array( $data ) ) {
		// Meta
		foreach ( $data as $key => $value ) {
			if ( '' === $value || null === $value ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $value );
			}
		}

		// Sync
		orbis_save_subscription_product_sync( $post_id );
	}
}

/**
 * Sync subscription producct with Orbis tables
 */
function orbis_save_subscription_product_sync( $post_id ) {
	// OK
	global $wpdb;

	// Orbis subscription product ID
	$orbis_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $wpdb->orbis_subscription_products WHERE post_id = %d;", $post_id ) );

	$price      = get_post_meta( $post_id, '_orbis_subscription_product_price', true );
	$cost_price = get_post_meta( $post_id, '_orbis_subscription_product_cost_price', true );
	$auto_renew = get_post_meta( $post_id, '_orbis_subscription_product_auto_renew', true );
	$deprecated = get_post_meta( $post_id, '_orbis_subscription_product_deprecated', true );
	$interval   = get_post_meta( $post_id, '_orbis_subscription_product_interval', true );

	$data = [];
	$form = [];

	$data['name'] = get_the_title( $post_id );
	$form['name'] = '%s';

	if ( ! empty( $price ) ) {
		$data['price'] = $price;
		$form['price'] = '%s';
	}

	if ( ! empty( $cost_price ) ) {
		$data['cost_price'] = $cost_price;
		$form['cost_price'] = '%s';
	}

	$data['auto_renew'] = $auto_renew;
	$form['auto_renew'] = '%d';

	$data['deprecated'] = $deprecated;
	$form['deprecated'] = '%d';

	$data['interval'] = $interval;
	$form['interval'] = '%s';

	if ( empty( $orbis_id ) ) {
		$data['post_id'] = $post_id;
		$form['post_id'] = '%d';

		$result = $wpdb->insert( $wpdb->orbis_subscription_products, $data, $form );

		if ( false !== $result ) {
			$orbis_id = $wpdb->insert_id;
		}
	} else {
		$result = $wpdb->update(
			$wpdb->orbis_subscription_products,
			$data,
			[ 'id' => $orbis_id ],
			$form,
			[ '%d' ]
		);
	}

	update_post_meta( $post_id, '_orbis_subscription_product_id', $orbis_id );
}



/**
 * Subscription product edit columns
 */
function orbis_subscription_product_edit_columns( $columns ) {
	$columns['orbis_subscription_product_price']      = __( 'Price', 'orbis-subscriptions' );
	$columns['orbis_subscription_product_cost_price'] = __( 'Cost Price', 'orbis-subscriptions' );
	$columns['orbis_subscription_product_deprecated'] = __( 'Deprecated', 'orbis-subscriptions' );
	$columns['orbis_subscription_product_id']         = __( 'Orbis ID', 'orbis-subscriptions' );

	$new_columns = [];

	foreach ( $columns as $name => $label ) {
		if ( 'author' === $name || 'twinfield_article' === $name ) {
			$new_columns['orbis_subscription_product_price']      = $columns['orbis_subscription_product_price'];
			$new_columns['orbis_subscription_product_cost_price'] = $columns['orbis_subscription_product_cost_price'];
			$new_columns['orbis_subscription_product_deprecated'] = $columns['orbis_subscription_product_deprecated'];
			$new_columns['orbis_subscription_product_id']         = $columns['orbis_subscription_product_id'];
		}

		$new_columns[ $name ] = $label;
	}

	$columns = $new_columns;

	return $columns;
}

add_filter( 'manage_edit-orbis_subs_product_columns', 'orbis_subscription_product_edit_columns' );

/**
 * Project column
 *
 * @param string $column
 * @param int    $post_id
 */
function orbis_subscription_product_column( $column, $post_id ) {
	switch ( $column ) {
		case 'orbis_subscription_product_id':
			$id = get_post_meta( $post_id, '_orbis_subscription_product_id', true );

			if ( empty( $id ) ) {
				echo '&mdash;';
			} else {
				$url = sprintf( 'http://orbis.pronamic.nl/projecten/details/%s/', $id );

				printf( '<a href="%s" target="_blank">%s</a>', esc_attr( $url ), esc_html( $id ) );
			}

			break;
		case 'orbis_subscription_product_price':
			$price = get_post_meta( $post_id, '_orbis_subscription_product_price', true );

			if ( empty( $price ) ) {
				echo '&mdash;';
			} else {
				$price = new Money( $price, 'EUR' );
				echo esc_html( $price->format_i18n() );
			}

			break;
		case 'orbis_subscription_product_cost_price':
			$price = get_post_meta( $post_id, '_orbis_subscription_product_cost_price', true );

			if ( empty( $price ) ) {
				echo '&mdash;';
			} else {
				$price = new Money( $price, 'EUR' );
				echo esc_html( $price->format_i18n() );
			}

			break;
		case 'orbis_subscription_product_deprecated':
			$deprecated = get_post_meta( $post_id, '_orbis_subscription_product_deprecated', true );

			if ( '' === $deprecated ) {
				echo '&mdash;';
			} else {
				echo esc_html( $deprecated ? __( 'Yes', 'orbis-subscriptions' ) : __( 'No', 'orbis-subscriptions' ) );
			}

			break;
	}
}

add_action( 'manage_posts_custom_column', 'orbis_subscription_product_column', 10, 2 );
