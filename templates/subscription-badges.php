<?php
/**
 * Subscription badges
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Subscriptions
 */

namespace Pronamic\Orbis\Subscriptions;

use DateTimeImmutable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$badges = [];

$badges_current_date    = new DateTimeImmutable( 'midnight' );
$badges_expiration_date = ( new DateTimeImmutable( (string) $post->subscription_expiration_date ) )->setTime( 0, 0 );

$badges_is_active    = empty( $post->subscription_cancel_date ) || $badges_expiration_date > $badges_current_date;
$badges_is_cancelled = isset( $post->subscription_cancel_date );
$badges_is_expired   = $badges_current_date > $badges_expiration_date;

if ( $badges_is_active ) {
	$badges[] = [
		'variation' => 'success',
		'content'   => \__( 'Active', 'orbis-subscriptions' ),
	];
}

if ( $badges_is_cancelled ) {
	$badges[] = [
		'variation' => 'warning',
		'content'   => \__( 'Cancelled', 'orbis-subscriptions' ),
	];
}

if ( $badges_is_expired ) {
	$badges[] = [
		'variation' => $badges_is_cancelled ? 'danger' : 'info',
		'content'   => \__( 'Expired', 'orbis-subscriptions' ),
	];
}

foreach ( $badges as $badge ) {
	\printf(
		'<span class="%s">%s</span> ',
		\esc_attr( 'badge text-bg-' . $badge['variation'] ),
		\esc_html( $badge['content'] )
	);
}
