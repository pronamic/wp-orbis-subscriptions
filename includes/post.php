<?php

function orbis_subscriptions_create_initial_post_types() {
	global $orbis_subscriptions_plugin;

	register_post_type(
		'orbis_subscription',
		array(
			'label'         => __( 'Subscriptions', 'orbis_subscriptions' ),
			'labels'        => array(
				'name'               => _x( 'Subscriptions', 'post type general name', 'orbis_subscriptions' ),
				'singular_name'      => _x( 'Subscription', 'post type singular name', 'orbis_subscriptions' ),
				'add_new'            => _x( 'Add New', 'orbis_subscription', 'orbis_subscriptions' ),
				'add_new_item'       => __( 'Add New Subscription', 'orbis_subscriptions' ),
				'edit_item'          => __( 'Edit Subscription', 'orbis_subscriptions' ),
				'new_item'           => __( 'New Subscription', 'orbis_subscriptions' ),
				'view_item'          => __( 'View Subscription', 'orbis_subscriptions' ),
				'search_items'       => __( 'Search Subscriptions', 'orbis_subscriptions' ),
				'not_found'          => __( 'No subscriptions found', 'orbis_subscriptions' ),
				'not_found_in_trash' => __( 'No subscriptions found in Trash', 'orbis_subscriptions' ),
				'parent_item_colon'  => __( 'Parent Subscription:', 'orbis_subscriptions' ),
				'menu_name'          => __( 'Subscriptions', 'orbis_subscriptions' )
			),
			'public'        => true,
			'menu_position' => 30,
			'menu_icon'     => 'dashicons-share-alt',
			'supports'      => array( 'editor', 'author', 'comments', 'revisions' ),
			'has_archive'   => true,
			'rewrite'       => array(
				'slug' => _x( 'subscriptions', 'slug', 'orbis_subscriptions' )
			)
		)
	);

	register_post_type(
		'orbis_subs_product',
		array(
			'label'         => __( 'Subscription Products', 'orbis_subscriptions' ),
			'labels'        => array(
				'name'               => _x( 'Subscription Products', 'post type general name', 'orbis_subscriptions' ),
				'singular_name'      => _x( 'Subscription Product', 'post type singular name', 'orbis_subscriptions' ),
				'add_new'            => _x( 'Add New', 'orbis_subs_type', 'orbis_subscriptions' ),
				'add_new_item'       => __( 'Add New Subscription Product', 'orbis_subscriptions' ),
				'edit_item'          => __( 'Edit Subscription Product', 'orbis_subscriptions' ),
				'new_item'           => __( 'New Subscription Product', 'orbis_subscriptions' ),
				'view_item'          => __( 'View Subscription Product', 'orbis_subscriptions' ),
				'search_items'       => __( 'Search Subscription Products', 'orbis_subscriptions' ),
				'not_found'          => __( 'No subscription products found', 'orbis_subscriptions' ),
				'not_found_in_trash' => __( 'No subscription products found in Trash', 'orbis_subscriptions' ),
				'parent_item_colon'  => __( 'Parent Subscription Product:', 'orbis_subscriptions' ),
				'menu_name'          => __( 'Products', 'orbis_subscriptions' )
			),
			'public'        => true,
			'menu_position' => 30,
			'show_in_menu'  => 'edit.php?post_type=orbis_subscription',
			'supports'      => array( 'title', 'editor', 'author', 'comments', 'thumbnail', 'revisions' ),
			'has_archive'   => true,
			'rewrite'       => array(
				'slug' => _x( 'subscription-products', 'slug', 'orbis_subscriptions' )
			)
		)
	);
}

add_action( 'init', 'orbis_subscriptions_create_initial_post_types', 0 ); // highest priority

/**
 * Add domain keychain meta boxes
 */
function orbis_sbuscriptions_add_meta_boxes() {
	add_meta_box(
		'orbis_subscription_details',
		__( 'Subscription Details', 'orbis_subscriptions' ),
		'orbis_subscription_details_meta_box',
		'orbis_subscription',
		'normal',
		'high'
	);

	add_meta_box(
		'orbis_subscription_actions',
		__( 'Subscription Actions', 'orbis_subscriptions' ),
		'orbis_subscription_actions_meta_box',
		'orbis_subscription',
		'normal',
		'high'
	);

	add_meta_box(
		'orbis_subscription_product_details',
		__( 'Subscription Product Details', 'orbis_subscriptions' ),
		'orbis_subscription_product_details_meta_box',
		'orbis_subs_product',
		'normal',
		'high'
	);
}

add_action( 'add_meta_boxes', 'orbis_sbuscriptions_add_meta_boxes' );

/**
 * Subscription details meta box
 *
 * @param array $post
*/
function orbis_subscription_details_meta_box( $post ) {
	global $orbis_subscriptions_plugin;

	$orbis_subscriptions_plugin->plugin_include( 'admin/meta-box-subscription-details.php' );
}

/**
 * Subscription actions meta box
 *
 * @param array $post
*/
function orbis_subscription_actions_meta_box( $post ) {
	global $orbis_subscriptions_plugin;

	$orbis_subscriptions_plugin->plugin_include( 'admin/meta-box-subscription-actions.php' );
}

/**
 * Subscription product details meta box
 *
 * @param array $post
*/
function orbis_subscription_product_details_meta_box( $post ) {
	global $orbis_subscriptions_plugin;

	$orbis_subscriptions_plugin->plugin_include( 'admin/meta-box-subscription-product-details.php' );
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
	if ( ! ( $post->post_type == 'orbis_subscription' && current_user_can( 'edit_post', $post_id ) ) ) {
		return;
	}

	// OK
	$definition = array(
		'_orbis_subscription_company_id' => FILTER_SANITIZE_STRING,
		'_orbis_subscription_type_id'    => FILTER_SANITIZE_STRING,
		'_orbis_subscription_name'       => FILTER_SANITIZE_STRING,
		'_orbis_subscription_person_id'  => FILTER_SANITIZE_STRING,
		'_orbis_subscription_email'		 => FILTER_VALIDATE_EMAIL
	);

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
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE) {
		return;
	}

	// Check post type
	if ( ! ( $post->post_type == 'orbis_subscription' ) ) {
		return;
	}

	// Revision
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	// Publish
	if ( $post->post_status != 'publish' ) {
		return;
	}

	$company_id = get_post_meta( $post_id, '_orbis_subscription_company_id', true );
	$type_id    = get_post_meta( $post_id, '_orbis_subscription_type_id', true );
	$name       = get_post_meta( $post_id, '_orbis_subscription_name', true );
	$email      = get_post_meta( $post_id, '_orbis_subscription_email', true );

	// Get the subscription object
	$subscription = new Orbis_Subscription( $post );

	// Set this subscriptions details
	$subscription
		->set_company_id( $company_id )
		->set_product_id( $type_id )
		->set_post_id( $post_id )
		->set_email( $email )
		->set_name( $name );

	// Must be new, make a new license key for this subscription
	if ( ! $subscription->get_id() ) {
		$subscription->generate_license_key();

		// Current DateTime
		$current = new DateTime();

		$subscription->set_activation_date( $current );

		// Expiration DateTime
		$expiration = clone $current;
		$expiration->modify( '+1 year' );

		$subscription->set_expiration_date( $expiration );
	}

	// Save this subscription!
	$subscription->save();
}

add_action( 'save_post', 'orbis_save_subscription_sync', 20, 2 );

/**
 * Keychain edit columns
*/
function orbis_subscription_edit_columns( $columns ) {
	return array(
		'cb'                        => '<input type="checkbox" />',
		'title'                     => __( 'Title', 'orbis_subscriptions' ),
		'orbis_subscription_person' => __( 'Person', 'orbis_subscriptions' ),
		'author'                    => __( 'Author', 'orbis_subscriptions' ),
		'comments'                  => __( 'Comments', 'orbis_subscriptions' ),
		'date'                      => __( 'Date', 'orbis_subscriptions' )
	);
}

add_filter( 'manage_edit-orbis_subscription_columns' , 'orbis_subscription_edit_columns' );

/**
 * Keychain column
 *
 * @param string $column
*/
function orbis_subscription_column( $column ) {
	$id = get_the_ID();

	switch ( $column ) {
		case 'orbis_subscription_person' :
			$person_id = get_post_meta( $id, '_orbis_subscription_person_id', true );

			if ( ! empty( $person_id ) ) {
				printf(
					'<a href="%s" target="_blank">%s</a>',
					get_permalink( $person_id ),
					get_the_title( $person_id )
				);
			}

			break;
	}
}

add_action( 'manage_posts_custom_column' , 'orbis_subscription_column' );

/**
 * Insert post data
 *
 * @see https://github.com/WordPress/WordPress/blob/3.5.1/wp-includes/post.php#L2864
 */
function orbis_subscriptions_insert_post_data( $data, $postarr ) {
	if ( isset( $data['post_type'] ) && $data['post_type'] == 'orbis_subscription' ) {
		global $wpdb;

		$type_id = filter_input( INPUT_POST, '_orbis_subscription_type_id', FILTER_SANITIZE_STRING );
		$name    = filter_input( INPUT_POST, '_orbis_subscription_name', FILTER_SANITIZE_STRING );

		$type_name = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM $wpdb->orbis_subscription_products WHERE id = %d;", $type_id ) );

		if ( ! empty( $type_name ) && ! empty( $name ) ) {
			$post_title = $type_name . ' - ' . $name;

			// Add unique post ID in front of post name if available
			$post_name  = sanitize_title_with_dashes( ( isset( $postarr['ID'] ) ? $postarr['ID'] . '. ' : '' ) . $post_title );

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
	if ( ! ( $post->post_type == 'orbis_subs_product' && current_user_can( 'edit_post', $post_id ) ) ) {
		return;
	}

	// OK
	global $wp_locale;

	$definition = array(
		'_orbis_subscription_product_price'      => array(
			'filter'  => FILTER_VALIDATE_FLOAT,
			'flags'   => FILTER_FLAG_ALLOW_THOUSAND,
			'options' => array( 'decimal' => $wp_locale->number_format['decimal_point'] ),
		),
		'_orbis_subscription_product_cost_price' => array(
			'filter'  => FILTER_VALIDATE_FLOAT,
			'flags'   => FILTER_FLAG_ALLOW_THOUSAND,
			'options' => array( 'decimal' => $wp_locale->number_format['decimal_point'] ),
		),
		'_orbis_subscription_product_auto_renew' => FILTER_VALIDATE_BOOLEAN,
		'_orbis_subscription_product_deprecated' => FILTER_VALIDATE_BOOLEAN,
        '_orbis_subscription_product_interval'   => FILTER_SANITIZE_STRING,
	);

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
			if ( $value === '' || $value === null ) {
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
	$orbis_id       = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $wpdb->orbis_subscription_products WHERE post_id = %d;", $post_id ) );

	$price       = get_post_meta( $post_id, '_orbis_subscription_product_price', true );
	$cost_price  = get_post_meta( $post_id, '_orbis_subscription_product_cost_price', true );
	$auto_renew  = get_post_meta( $post_id, '_orbis_subscription_product_auto_renew', true );
	$deprecated  = get_post_meta( $post_id, '_orbis_subscription_product_deprecated', true );
	$interval    = get_post_meta( $post_id, '_orbis_subscription_product_interval', true );

	$data = array();
	$form = array();

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

		if ( $result !== false ) {
			$orbis_id = $wpdb->insert_id;
		}
	} else {
		$result = $wpdb->update(
			$wpdb->orbis_subscription_products,
			$data,
			array( 'id' => $orbis_id ),
			$form,
			array( '%d' )
		);
	}

	update_post_meta( $post_id, '_orbis_subscription_product_id', $orbis_id );
}



/**
 * Subscription product edit columns
 */
function orbis_subscription_product_edit_columns($columns) {
	return array(
		'cb'                                    => '<input type="checkbox" />',
		'title'                                 => __( 'Title', 'orbis_subscriptions' ),
		'orbis_subscription_product_price'      => __( 'Price', 'orbis_subscriptions' ),
		'orbis_subscription_product_cost_price' => __( 'Cost Price', 'orbis_subscriptions' ),
		'orbis_subscription_product_deprecated' => __( 'Deprecated', 'orbis_subscriptions' ),
		'orbis_subscription_product_id'         => __( 'Orbis ID', 'orbis_subscriptions' ),
		'author'                                => __( 'Author', 'orbis_subscriptions' ),
		'comments'                              => __( 'Comments', 'orbis_subscriptions' ),
		'date'                                  => __( 'Date', 'orbis_subscriptions' ),
	);
}

add_filter( 'manage_edit-orbis_subs_product_columns' , 'orbis_subscription_product_edit_columns' );

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

				printf( '<a href="%s" target="_blank">%s</a>', $url, $id );
			}

			break;
		case 'orbis_subscription_product_price':
			$price = get_post_meta( $post_id, '_orbis_subscription_product_price', true );

			if ( empty( $price ) ) {
				echo '&mdash;';
			} else {
				echo orbis_price( $price );
			}

			break;
		case 'orbis_subscription_product_cost_price':
			$price = get_post_meta( $post_id, '_orbis_subscription_product_cost_price', true );

			if ( empty( $price ) ) {
				echo '&mdash;';
			} else {
				echo orbis_price( $price );
			}

			break;
		case 'orbis_subscription_product_deprecated':
			$deprecated = get_post_meta( $post_id, '_orbis_subscription_product_deprecated', true );

			if ( $deprecated == '' ) {
				echo '&mdash;';
			} else {
				echo $deprecated ? __( 'Yes', 'orbis_subscriptions' ) : __( 'No', 'orbis_subscriptions' );
			}

			break;
	}
}

add_action( 'manage_posts_custom_column', 'orbis_subscription_product_column', 10, 2 );
