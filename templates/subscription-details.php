<?php
/**
 * Subscriptions details
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Subscriptions
 */

namespace Pronamic\Orbis\Subscriptions;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;

global $wpdb, $post;

$orbis_id        = get_post_meta( $post->ID, '_orbis_subscription_id', true );
$company_id      = get_post_meta( $post->ID, '_orbis_subscription_company_id', true );
$type_id         = get_post_meta( $post->ID, '_orbis_subscription_type_id', true );
$name            = get_post_meta( $post->ID, '_orbis_subscription_name', true );
$activation_date = get_post_meta( $post->ID, '_orbis_subscription_activation_date', true );
$expiration_date = get_post_meta( $post->ID, '_orbis_subscription_expiration_date', true );
$cancel_date     = get_post_meta( $post->ID, '_orbis_subscription_cancel_date', true );
$email           = get_post_meta( $post->ID, '_orbis_subscription_email', true );

$query = $wpdb->prepare(
	"
	SELECT
		subscription.*,
		product.time_per_year,
		product.interval
	FROM
		$wpdb->orbis_subscriptions AS subscription
			INNER JOIN
		$wpdb->orbis_subscription_products AS product
				ON subscription.type_id = product.id
	WHERE
		subscription.post_id = %d
	LIMIT
		1
	;",
	$post->ID
);

$subscription = $wpdb->get_row( $query );

$orbis_id        = $subscription->id;
$company_id      = $subscription->company_id;
$type_id         = $subscription->type_id;
$name            = $subscription->name;
$activation_date = $subscription->activation_date;
$expiration_date = $subscription->expiration_date;
$cancel_date     = $subscription->cancel_date;

$company_post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->orbis_companies WHERE id = %d;", $company_id ) );

$invoice_reference        = get_post_meta( $post->ID, '_orbis_invoice_reference', true );
$invoice_line_description = get_post_meta( $post->ID, '_orbis_invoice_line_description', true );

// Current Period End Date.
$current_period_end_date = Subscription::get_current_period_end_date(
	$subscription->activation_date,
	$subscription->interval,
	$subscription->cancel_date,
	$subscription->end_date
);

// Dates.
$utc = new \DateTimeZone( 'UTC' );

$activation_date = null;

if ( ! empty( $subscription->activation_date ) ) {
	$activation_date = new \DateTimeImmutable( $subscription->activation_date, $utc );
}

$expiration_date = null;

if ( ! empty( $subscription->expiration_date ) ) {
	$expiration_date = new \DateTimeImmutable( $subscription->expiration_date, $utc );
}

$cancel_date = null;

if ( ! empty( $subscription->cancel_date ) ) {
	$cancel_date = new \DateTimeImmutable( $subscription->cancel_date, $utc );
}

$billed_to = null;

if ( ! empty( $subscription->billed_to ) ) {
	$billed_to = new \DateTimeImmutable( $subscription->billed_to, $utc );
}


?>
<div class="card mb-3">
	<div class="card-header"><?php esc_html_e( 'Subscription Details', 'orbis-subscriptions' ); ?></div>
	<div class="card-body">

		<div class="content">
			<dl>
				<dt><?php esc_html_e( 'Company', 'orbis-subscriptions' ); ?></dt>
				<dd>
					<a href="<?php echo esc_url( get_permalink( $company_post_id ) ); ?>"><?php echo esc_html( get_the_title( $company_post_id ) ); ?></a>
				</dd>

				<dt><?php esc_html_e( 'Status', 'orbis-subscriptions' ); ?></dt>
				<dd>
					<?php get_template_part( 'templates/subscription-badges' ); ?>
				</dd>

				<?php if ( null !== $activation_date ) : ?>

					<dt><?php esc_html_e( 'Activation Date', 'orbis-subscriptions' ); ?></dt>
					<dd><?php echo \esc_html( \wp_date( 'D j M Y H:i:s', $activation_date->getTimestamp() ) ); ?></dd>

				<?php endif; ?>

				<dt><?php esc_html_e( 'Current Period', 'orbis-subscriptions' ); ?></dt>
				<dd>
					<?php 

					printf(
						__( 'to %s', 'orbis-subscriptions' ),
						$current_period_end_date->format( 'd-m-Y' )
					);

					?>
				</dd>

				<?php if ( ! empty( $cancel_date ) ) : ?>

					<dt><?php esc_html_e( 'Cancel Date', 'orbis-subscriptions' ); ?></dt>
					<dd><?php echo \esc_html( \wp_date( 'D j M Y H:i:s', $cancel_date->getTimestamp() ) ); ?></dd>

				<?php endif; ?>

				<?php if ( ! empty( $billed_to ) ) : ?>

					<dt><?php esc_html_e( 'Billed To', 'orbis-subscriptions' ); ?></dt>
					<dd>
						<?php

						echo \esc_html( \wp_date( 'D j M Y', $billed_to->getTimestamp() ) );

						$anchor_1 = new DateTime( '+1 month' );
						$anchor_2 = new DateTime( '+1 year' );

						if ( $billed_to <= $anchor_1 ) : 
							?>

							<div class="alert alert-primary mt-2" role="alert">
								<?php esc_html_e( '📣 Please note: this subscription may be billed again soon.', 'orbis-subscriptions' ); ?>
							</div>

						<?php endif; ?>

						<?php if ( $billed_to > $anchor_2 ) : ?>

							<div class="alert alert-danger mt-2" role="alert">
								<?php esc_html_e( '🚨 Please note: this subscription is billed further in advance than usual.', 'orbis-subscriptions' ); ?>
							</div>

						<?php endif; ?>
					</dd>

				<?php endif; ?>

				<dt><?php esc_html_e( 'Price', 'orbis-subscriptions' ); ?></dt>
				<dd><?php orbis_subscription_the_price(); ?></dd>

				<?php if ( has_term( null, 'orbis_payment_method' ) ) : ?>

					<dt><?php esc_html_e( 'Payment Method', 'orbis-subscriptions' ); ?></dt>
					<dd><?php the_terms( null, 'orbis_payment_method' ); ?></dd>

				<?php endif; ?>

				<?php if ( ! empty( $invoice_reference ) ) : ?>

					<dt><?php esc_html_e( 'Invoice reference', 'orbis-subscriptions' ); ?></dt>
					<dd>
						<?php echo nl2br( esc_html( $invoice_reference ) ); ?></a>
					</dd>

				<?php endif; ?>

				<?php if ( ! empty( $invoice_line_description ) ) : ?>

					<dt><?php esc_html_e( 'Invoice Line Description', 'orbis-subscriptions' ); ?></dt>
					<dd>
						<?php echo nl2br( esc_html( $invoice_line_description ) ); ?></a>
					</dd>

				<?php endif; ?>

				<?php

				$agreement_id = get_post_meta( get_the_ID(), '_orbis_subscription_agreement_id', true );

				if ( ! empty( $agreement_id ) ) :
					$agreement = get_post( $agreement_id );
					?>

					<dt><?php esc_html_e( 'Agreement', 'orbis-subscriptions' ); ?></dt>
					<dd>
						<a href="<?php echo esc_url( get_permalink( $agreement ) ); ?>"><?php echo get_the_title( $agreement ); ?></a>
					</dd>

				<?php endif; ?>

				<?php if ( null !== $subscription->time_per_year ) : ?>

					<dt><?php esc_html_e( 'Available time per year', 'orbis-subscriptions' ); ?></dt>
					<dd>
						<?php echo esc_html( orbis_time( $subscription->time_per_year ) ); ?>
					</dd>

				<?php endif; ?>
			</dl>
		</div>
	</div>

</div>
