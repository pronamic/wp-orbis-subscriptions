<?php

/**
 * File for generate functions for subscription usages
 * 
 * @author Leon Rowland <leon@rowland.nl>
 * @author Remco Tolsma <remco@pronamic.nl>
 */

if ( ! function_exists( 'orbis_subscription_get_data' ) ) :
	
	/**
	 * Returns a row from the orbis_subscriptions table 
	 * where the post_id matches the parameter
	 * 
	 * @global type $wpdb
	 * 
	 * @param int $post_id
	 * @return object
	 */
	function orbis_subscription_get_data( $post_id ) {
		global $wpdb;
		
		$query = "
			SELECT
				s.id,
				s.post_id,
				s.name,
				s.email,
				s.activation_date,
				s.expiration_date,
				s.cancel_date,
				s.update_date,
				s.license_key,
				s.license_key_md5,
				s.sent_notifications,
				c.id as company_id,
				c.name as company_name,
				c.e_mail as company_email,
				t.id as type_id,
				t.name as type_name,
				t.price as type_price,
				t.auto_renew as type_auto_renew
			FROM
				$wpdb->orbis_subscriptions as s
					LEFT JOIN
				$wpdb->orbis_subscription_products as t
						ON s.type_id = t.id
					LEFT JOIN
				$wpdb->orbis_companies as c
						ON s.company_id = c.id
			WHERE
				s.post_id = %d
		";
		
		$query =  $wpdb->prepare( $query, $post_id );

		return $wpdb->get_row( $query );
	}
	
endif;


if ( ! function_exists( 'orbis_date2mysql' ) ) :

	function orbis_date2mysql( DateTime $date = null ) {
		$result = null;

		if ( $date !== null ) {
			$result = $date->format( 'Y-m-d H:i:s' );
		}

		return $result;
	}

endif;


function orbis_subscription_get_email_comment( $to, $message_plain ) {
	return sprintf(
		__( 'I sent the following message to %s: <blockquote>%s</blockquote>', 'orbis_subscriptions' ),
		$to,
		$message_plain
	);
}


function orbis_subscriptions_comment( $comment_content, $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;

	$user = wp_get_current_user();
	
	if ( empty( $user->display_name ) )
		$user->display_name = $user->user_login;
	
	$comment_author       = esc_sql( $user->display_name );
	$comment_author_email = esc_sql( $user->user_email );
	$comment_author_url   = esc_sql( $user->user_url );
	
	$comment_id = wp_insert_comment( array(
		'comment_post_ID'      => $post_id,
		'comment_content'      => $comment_content,
		'comment_author'       => $comment_author,
		'comment_author_email' => $comment_author_email,
		'comment_author_url'   => $comment_author_url
	) );
	
	return $comment_id;
}


function orbis_subscriptions_comment_email( $to, $message_plain, $post_id = null ) {
	$post_id = ( null === $post_id ) ? get_the_ID() : $post_id;

	$comment_content = orbis_subscription_get_email_comment( $to, $message_plain );

	return orbis_subscriptions_comment( $comment_content, $post_id );
}



function orbis_subscriptions_suggest_subscription_id() {
	global $wpdb;

	$term = filter_input( INPUT_GET, 'term', FILTER_SANITIZE_STRING );

	$query = $wpdb->prepare( "
		SELECT
			subscription.id AS id,
			CONCAT( subscription.id, '. ', product.name, ' - ', subscription.name ) AS text
		FROM
			$wpdb->orbis_subscriptions AS subscription
				LEFT JOIN
			$wpdb->orbis_subscription_products AS product
					ON subscription.type_id = product.id
		WHERE
			subscription.cancel_date IS NULL
				AND
			(
				subscription.name LIKE '%%%1\$s%%'
					OR
				product.name LIKE '%%%1\$s%%'
			)
		GROUP BY
			subscription.id
		ORDER BY
			subscription.id
		", $term
	);

	$data = $wpdb->get_results( $query );
	
	echo json_encode( $data );
	
	die();
}

add_action( 'wp_ajax_subscription_id_suggest', 'orbis_subscriptions_suggest_subscription_id' );
