<?php

function orbis_subscriptions_create_initial_post_types() {
	global $orbis_subscriptions_plugin;

	register_post_type(
		'orbis_subscription',
		array(
			'label'         => __( 'Subscriptions', 'orbis' ),
			'labels'        => array(
				'name'          => __( 'Subscriptions', 'orbis' ),
				'singular_name' => __( 'Subscription', 'orbis' )
			),
			'public'        => true,
			'menu_position' => 30,
			'menu_icon'     => plugins_url( 'images/subscription.png', $orbis_subscriptions_plugin->file ),
			'supports'      => array( 'title', 'editor', 'author', 'comments', 'thumbnail' ),
			'has_archive'   => true,
			'rewrite'       => array(
				'slug' => _x( 'subscriptions', 'slug', 'orbis' )
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
		__( 'Details', 'orbis' ),
		'orbis_subscription_details_meta_box',
		'orbis_subscription',
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

	include dirname( $orbis_subscriptions_plugin->file ) . '/admin/meta-box-subscription-details.php';
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
		'_orbis_subscription_person_id'  => FILTER_SANITIZE_STRING
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

	// OK
	global $wpdb;

	// Orbis company ID
	$query = $wpdb->prepare( "SELECT id FROM orbis_subscriptions WHERE post_id = %d;", $post_id );

	$orbis_id = $wpdb->get_var( $query );

	if ( empty( $orbis_id ) ) {
		$company_id  = get_post_meta( $post_id, '_orbis_subscription_company_id', true );
		$type_id     = get_post_meta( $post_id, '_orbis_subscription_type_id', true );
		$name        = get_post_meta( $post_id, '_orbis_subscription_name', true );

		$license_key     = md5( '' . $company_id . $type_id . $name );
		$license_key_md5 = md5( $license_key );
		
		$activation_time = time();
		$expiration_time = strtotime( '+1 year', $activation_time ); 

		$result = $wpdb->insert(
			'orbis_subscriptions' ,
			array(
				'company_id'      => $company_id,
				'type_id'         => $type_id,
				'post_id'         => $post_id,
				'name'            => $name,
				'activation_date' => date( 'Y-m-d H:i:s', $activation_time ),
				'expiration_date' => date( 'Y-m-d H:i:s', $expiration_time ),
				'license_key'     => $license_key,
				'license_key_md5' => $license_key_md5
			),
			array(
				'company_id'      => '%d',
				'type_id'         => '%d',
				'post_id'         => '%d',
				'name'            => '%s',
				'activation_date' => '%s',
				'expiration_date' => '%s',
				'license_key'     => '%s',
				'license_key_md5' => '%s'
			)
		);
	
		if ( $result !== false ) {
			$orbis_id = $wpdb->insert_id;
	
			update_post_meta( $post_id, '_orbis_subscription_id', $orbis_id );
		}
	}
}

add_action( 'save_post', 'orbis_save_subscription_sync', 20, 2 );


/**
 * Keychain content
*/
function orbis_subscription_the_content( $content ) {
	if ( get_post_type() == 'orbis_subscription' ) {
		$id = get_the_ID();

		$person_id = get_post_meta( $id, '_orbis_subscription_person_id', true );

		$str  = '';

		$str .= '<h2>' . __( 'Persons', 'orbis' ) . '</h2>';

		$str .= '<dl>';

		if ( ! empty( $person_id ) ) {
			$str .= '	<dt>' . __( 'Person', 'orbis' ) . '</dt>';
			$str .= '	<dd>' . sprintf( '<a href="%s">%s</a>', get_permalink( $person_id ), get_the_title( $person_id ) ) . '</dd>';
		}

		$str .= '</dl>';

		$content .= $str;
	}

	return $content;
}

add_filter( 'the_content', 'orbis_subscription_the_content' );

/**
 * Keychain edit columns
*/
function orbis_subscription_edit_columns( $columns ) {
	return array(
		'cb'                        => '<input type="checkbox" />',
		'title'                     => __( 'Title', 'orbis' ),
		'orbis_subscription_person' => __( 'Person', 'orbis' ),
		'author'                    => __( 'Author', 'orbis' ),
		'comments'                  => __( 'Comments', 'orbis' ),
		'date'                      => __( 'Date', 'orbis' )
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
