<?php

/**
 * Comment form defaults
 */
function orbis_subscriptions_comment_form($post_id) {
	// Some themes call this function, don't show the checkbox again
	remove_action( 'comment_form', __FUNCTION__ );

	if ( get_post_type( $post_id ) == 'orbis_subscription' ) {
		$str  = '';

		$str .= '<p>';
		$str .=	'	<label class="checkbox">';
		$str .= '		<input type="checkbox" name="orbis_subscription_extend_request" value="true" /> ';
		$str .= '		' . sprintf( __( 'Extend this subscription with 1 year.', 'orbis_subscriptions' ) );
		$str .= '	</label>';
		$str .= '</p>';

		echo $str;
	}
}

add_filter( 'comment_form', 'orbis_subscriptions_comment_form' );

/**
 * Subscription comment post
 *
 * @param string $comment_id
 * @param string $approved
 */
function orbis_subscription_comment_post( $comment_id, $approved ) {
	$is_extend_request = filter_input( INPUT_POST, 'orbis_subscription_extend_request', FILTER_VALIDATE_BOOLEAN );

	if ( $is_extend_request ) {
		add_comment_meta( $comment_id, 'orbis_subscription_extend_request', $is_extend_request, true );
		
		$comment = get_comment( $comment_id );

		if ( $comment ) {
			$subscription = new Orbis_Subscription( $comment->comment_post_ID );
			
			add_comment_meta( $comment_id, 'orbis_subscription_expiration_date_before', $subscription->get_expiration_date()->format( 'Y-m-d H:i:s' ), true );

			$subscription->extend( '+1 year' );
			
			add_comment_meta( $comment_id, 'orbis_subscription_expiration_date_after', $subscription->get_expiration_date()->format( 'Y-m-d H:i:s' ), true );
		}
	}
}

add_action( 'comment_post', 'orbis_subscription_comment_post', 50, 2 );

/**
 * Subscription extend comment text
 */
function orbis_subscription_get_comment_text( $text, $comment ) {
	$is_extend_request = get_comment_meta( $comment->comment_ID, 'orbis_subscription_extend_request', true );

	if ( $is_extend_request ) {
		$str = '';

		$str .= '<div style="font-style: italic;">';

		$expiration_date_before = get_comment_meta( $comment->comment_ID, 'orbis_subscription_expiration_date_before', true );
		$expriation_date_after  = get_comment_meta( $comment->comment_ID, 'orbis_subscription_expiration_date_after', true );

		$str .= '<p>';
		$str .= '	' . sprintf(
			__( 'This comment was an subscription extend request (before: %s, after: %s).', 'orbis_subscriptions' ),
			$expiration_date_before,
			$expriation_date_after
		);
		$str .= '</p>';
		
		$str .= '</div>';

		$text .= $str;
	}

	return $text;
}

add_filter( 'comment_text', 'orbis_subscription_get_comment_text', 20, 2 );
