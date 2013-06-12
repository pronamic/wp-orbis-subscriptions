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
			'menu_icon'     => $orbis_subscriptions_plugin->plugin_url( 'admin/images/subscription.png' ),
			'supports'      => array( 'editor', 'author', 'comments', 'thumbnail' ),
			'has_archive'   => true,
			'rewrite'       => array(
				'slug' => _x( 'subscriptions', 'slug', 'orbis_subscriptions' )
			)
		)
	);

	register_post_type(
		'orbis_subs_type',
		array(
			'label'         => __( 'Subscription Types', 'orbis_subscriptions' ),
			'labels'        => array(
				'name'               => _x( 'Subscription Types', 'post type general name', 'orbis_subscriptions' ),
				'singular_name'      => _x( 'Subscription Type', 'post type singular name', 'orbis_subscriptions' ),
				'add_new'            => _x( 'Add New', 'orbis_subs_type', 'orbis_subscriptions' ),
				'add_new_item'       => __( 'Add New Subscription Type', 'orbis_subscriptions' ),
				'edit_item'          => __( 'Edit Subscription Type', 'orbis_subscriptions' ),
				'new_item'           => __( 'New Subscription Type', 'orbis_subscriptions' ),
				'view_item'          => __( 'View Subscription Type', 'orbis_subscriptions' ),
				'search_items'       => __( 'Search Subscription Types', 'orbis_subscriptions' ),
				'not_found'          => __( 'No subscription types found', 'orbis_subscriptions' ),
				'not_found_in_trash' => __( 'No subscription types found in Trash', 'orbis_subscriptions' ),
				'parent_item_colon'  => __( 'Parent Subscription Type:', 'orbis_subscriptions' ),
				'menu_name'          => __( 'Types', 'orbis_subscriptions' )
			),
			'public'        => true,
			'menu_position' => 30,
			'show_in_menu'  => 'edit.php?post_type=orbis_subscription',
			'supports'      => array( 'title', 'editor', 'author', 'comments', 'thumbnail' ),
			'has_archive'   => true,
			'rewrite'       => array(
				'slug' => _x( 'subscription-types', 'slug', 'orbis_subscriptions' )
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
		__( 'Details', 'orbis_subscriptions' ),
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

	$orbis_subscriptions_plugin->plugin_include( 'admin/meta-box-subscription-details.php' );
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
		->set_type_id( $type_id )
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
 * Maybe mail license key
 */
function orbis_subscriptions_maybe_mail_license_key() {
	if ( isset( $_POST['orbis_subscriptions_nonce'] ) ) {
		$nonce = filter_input( INPUT_POST, 'orbis_subscriptions_nonce', FILTER_SANITIZE_STRING );

		if ( wp_verify_nonce( $nonce, 'orbis_subscription_mail_license_key' ) ) {
			global $orbis_subscriptions_plugin;
			global $orbis_email_title;

			$to      = filter_input( INPUT_POST, 'orbis_subscription_email', FILTER_VALIDATE_EMAIL );
			$subject = filter_input( INPUT_POST, 'orbis_subscription_subject', FILTER_SANITIZE_STRING );
			
			$orbis_email_title = $subject;

			$message_html = $orbis_subscriptions_plugin->get_template( 'emails/subscription-license.php', false );

			$message_plain = wpautop( wptexturize( strip_tags( $message_html ) ) );

			$headers = array(
				'From: Pronamic <support@pronamic.nl>',
				'Content-Type: text/html'
			);

			if ( $to ) {				
				$result = wp_mail( $to, $subject, $message_html, $headers );
	
				if ( $result ) {
					$comment_content = sprintf( 
						__( 'I sent the following message to %s: <blockquote>%s</blockquote>', 'orbis_subscriptions' ),
						$to,
						$message_plain
					);
	
					global $wpdb;
	
					$user = wp_get_current_user();
	
					if ( empty( $user->display_name ) )
						$user->display_name = $user->user_login;
	
					$comment_author       = $wpdb->escape( $user->display_name );
					$comment_author_email = $wpdb->escape( $user->user_email );
					$comment_author_url   = $wpdb->escape( $user->user_url );
	
					$comment_id = wp_insert_comment( array(
						'comment_post_ID'      => get_the_ID(),
						'comment_content'      => $comment_content,
						'comment_author'       => $comment_author,
						'comment_author_email' => $comment_author_email,
						'comment_author_url'   => $comment_author_url
					) );
					
					wp_safe_redirect( get_comment_link( $comment_id ) );
	
					exit;
				}
				
				$url = add_query_arg( 'mailed', $result ? 'yes' : 'no' );
	
				wp_redirect( $url );
				
				exit;
			}
		}
	}
}

add_action( 'template_redirect', 'orbis_subscriptions_maybe_mail_license_key' );

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

		$type_name = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM $wpdb->orbis_subscription_types WHERE id = %d;", $type_id ) );

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
