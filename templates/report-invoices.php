<?php
/**
 * Report invoices
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Subscriptions
 */

use Pronamic\WordPress\Money\Money;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$period_options = [
	'-1 week'   => \__( 'Last week', 'orbis-subscriptions' ),
	'-1 month'  => \__( 'Last month', 'orbis-subscriptions' ),
	'-3 months' => \__( 'Last 3 months', 'orbis-subscriptions' ),
	'-6 months' => \__( 'Last 6 months', 'orbis-subscriptions' ),
	'-1 year'   => \__( 'Last year', 'orbis-subscriptions' ),
	'all'       => \__( 'All time', 'orbis-subscriptions' ),
];

$period = \array_key_exists( 'start', $_GET ) ? \sanitize_text_field( \wp_unslash( $_GET['start'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

if ( ! \array_key_exists( $period, $period_options ) ) {
	$period = '-1 week';
}

$condition = '1 = 1';

if ( 'all' !== $period ) {
	$condition = $wpdb->prepare(
		'invoice.invoice_date >= %s',
		\gmdate( 'Y-m-d', \strtotime( $period ) )
	);
}

$customer_select = 'NULL AS customer_name, NULL AS customer_post_id';
$customer_join   = '';

if ( \class_exists( \Pronamic\Orbis\Contacts\ContactsTable::class ) ) {
	$contacts_table = \Pronamic\Orbis\Contacts\ContactsTable::get_table_name();

	$customer_select = 'customer.name AS customer_name, customer.post_id AS customer_post_id';
	$customer_join   = "LEFT JOIN $contacts_table AS customer ON subscription.customer_id = customer.id";
}

$query = "
	SELECT
		invoice.invoice_number,
		invoice.invoice_date,
		invoice.invoice_data,
		MIN( invoice_line.start_date ) AS start_date,
		MAX( invoice_line.end_date ) AS end_date,
		subscription.name AS subscription_name,
		subscription.post_id AS subscription_post_id,
		product.name AS product_name,
		$customer_select,
		SUM( invoice_line.amount ) AS amount
	FROM
		$wpdb->orbis_invoices_lines AS invoice_line
			INNER JOIN
		$wpdb->orbis_invoices AS invoice
				ON invoice.id = invoice_line.invoice_id
			INNER JOIN
		$wpdb->orbis_subscriptions AS subscription
				ON subscription.id = invoice_line.subscription_id
			LEFT JOIN
		$wpdb->orbis_products AS product
				ON product.id = subscription.product_id
		$customer_join
	WHERE
		$condition
	GROUP BY
		invoice.id, subscription.id
	ORDER BY
		invoice.invoice_date DESC, invoice.invoice_number DESC
	;
";

$invoices = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

\get_header();

?>
<form class="d-flex justify-content-end gap-2 mb-3" method="get" action="">
	<label class="visually-hidden" for="orbis-subscriptions-report-period"><?php \esc_html_e( 'Period', 'orbis-subscriptions' ); ?></label>

	<select name="start" id="orbis-subscriptions-report-period" class="form-select w-auto">
		<?php

		foreach ( $period_options as $value => $label ) {
			\printf(
				'<option value="%s" %s>%s</option>',
				\esc_attr( $value ),
				\selected( $period, $value, false ),
				\esc_html( $label )
			);
		}

		?>
	</select>

	<button type="submit" class="btn btn-primary"><?php \esc_html_e( 'Filter', 'orbis-subscriptions' ); ?></button>
</form>

<div class="card">
	<div class="table-responsive">
		<table class="table table-striped table-hover mb-0">
			<thead>
				<tr>
					<th scope="col"><?php \esc_html_e( 'Customer', 'orbis-subscriptions' ); ?></th>
					<th scope="col"><?php \esc_html_e( 'Product', 'orbis-subscriptions' ); ?></th>
					<th scope="col"><?php \esc_html_e( 'Subscription', 'orbis-subscriptions' ); ?></th>
					<th scope="col"><?php \esc_html_e( 'Start Date', 'orbis-subscriptions' ); ?></th>
					<th scope="col"><?php \esc_html_e( 'End Date', 'orbis-subscriptions' ); ?></th>
					<th scope="col"><?php \esc_html_e( 'Invoice', 'orbis-subscriptions' ); ?></th>
					<th scope="col" class="text-end"><?php \esc_html_e( 'Amount', 'orbis-subscriptions' ); ?></th>
				</tr>
			</thead>

			<tfoot>
				<tr>
					<th scope="row" colspan="6"><?php \esc_html_e( 'Total', 'orbis-subscriptions' ); ?></th>
					<td class="text-end">
						<?php

						$total = new Money( \array_sum( \wp_list_pluck( $invoices, 'amount' ) ), 'EUR' );

						echo \esc_html( $total->format_i18n() );

						?>
					</td>
				</tr>
			</tfoot>

			<tbody>

				<?php foreach ( $invoices as $invoice ) : ?>

					<tr>
						<td>
							<?php if ( null !== $invoice->customer_post_id ) : ?>

								<a href="<?php echo \esc_url( \get_permalink( $invoice->customer_post_id ) ); ?>"><?php echo \esc_html( $invoice->customer_name ); ?></a>

							<?php endif; ?>
						</td>
						<td>
							<?php echo \esc_html( $invoice->product_name ); ?>
						</td>
						<td>
							<a href="<?php echo \esc_url( \get_permalink( $invoice->subscription_post_id ) ); ?>"><?php echo \esc_html( $invoice->subscription_name ); ?></a>
						</td>
						<td>
							<?php echo \esc_html( \date_i18n( 'D j M Y', \strtotime( $invoice->start_date ) ) ); ?>
						</td>
						<td>
							<?php echo \esc_html( \date_i18n( 'D j M Y', \strtotime( $invoice->end_date ) ) ); ?>
						</td>
						<td>
							<?php

							$invoice_url  = \apply_filters( 'orbis_invoice_url', '', $invoice->invoice_data );
							$invoice_text = \apply_filters( 'orbis_invoice_text', $invoice->invoice_number, $invoice->invoice_data );

							if ( '' !== $invoice_url ) {
								\printf(
									'<a href="%s" target="_blank">%s</a>',
									\esc_url( $invoice_url ),
									\esc_html( $invoice_text )
								);
							} else {
								echo \esc_html( $invoice_text );
							}

							?>
						</td>
						<td class="text-end">
							<?php

							$amount = new Money( $invoice->amount, 'EUR' );

							echo \esc_html( $amount->format_i18n() );

							?>
						</td>
					</tr>

				<?php endforeach; ?>

			</tbody>
		</table>
	</div>
</div>
<?php

\get_footer();
