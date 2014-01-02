<?php

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
