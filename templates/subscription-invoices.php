<?php
/**
 * Subscriptions invoices
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Subscriptions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $wpdb->orbis_subscriptions WHERE post_id = %d;", get_the_ID() ) );

$query = $wpdb->prepare(
	"
	SELECT
		user.display_name AS user_display_name,
		subscription_invoice.created_at,
		subscription_invoice.start_date,
		subscription_invoice.end_date,
		subscription_invoice.invoice_number,
		subscription_invoice.invoice_data
	FROM
		$wpdb->orbis_subscriptions_invoices AS subscription_invoice
			LEFT JOIN
		$wpdb->users AS user
				ON user.ID = subscription_invoice.user_id
	WHERE
		subscription_id = %d
	ORDER BY
		start_date ASC
	;",
	$id
);

$invoices = $wpdb->get_results( $query );

if ( $invoices ) : ?>

	<div class="card mb-3">
		<div class="card-header">
			<?php esc_html_e( 'Invoices', 'orbis-subscriptions' ); ?>
		</div>

		<div class="table-responsive">
			<table class="table table-striped table-bordered mb-0">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Create Date', 'orbis-subscriptions' ); ?></th>
						<th scope="col"><?php esc_html_e( 'User', 'orbis-subscriptions' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Start Date', 'orbis-subscriptions' ); ?></th>
						<th scope="col"><?php esc_html_e( 'End Date', 'orbis-subscriptions' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Invoice', 'orbis-subscriptions' ); ?></th>
					</tr>
				</thead>

				<tbody>

					<?php foreach ( $invoices as $invoice ) : ?>

						<tr>
							<td>
								<?php echo esc_html( date_i18n( 'D j M Y H:i:s', strtotime( $invoice->created_at ) ) ); ?>
							</td>
							<td>
								<?php echo esc_html( $invoice->user_display_name ); ?>
							</td>
							<td>
								<?php echo esc_html( date_i18n( 'D j M Y', strtotime( $invoice->start_date ) ) ); ?>
							</td>
							<td>
								<?php echo esc_html( date_i18n( 'D j M Y', strtotime( $invoice->end_date ) ) ); ?>
							</td>
							<td>
								<?php

								$invoice_url  = \apply_filters( 'orbis_invoice_url', '', $invoice->invoice_data );
								$invoice_text = \apply_filters( 'orbis_invoice_text', $invoice->invoice_number, $invoice->invoice_data );

								if ( '' !== $invoice_url ) {
									printf(
										'<a href="%s" target="_blank">%s</a>',
										esc_url( $invoice_url ),
										esc_html( $invoice_text )
									);
								} else {
									echo esc_html( $invoice->invoice_number );
								}

								?>
							</td>
						</tr>

					<?php endforeach; ?>

				</tbody>
			</table>
		</div>
	</div>

<?php endif; ?>
