<?php

global $wpdb;

$id = $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM orbis_subscriptions WHERE post_id = %d;', get_the_ID() ) );

$query = $wpdb->prepare( '
	SELECT
		start_date,
		end_date,
		invoice_number
	FROM
		orbis_subscriptions_invoices
	WHERE
		subscription_id = %d
	;',
	$id
);

$invoices = $wpdb->get_results( $query );

if ( $invoices ) : ?>

	<div class="panel">
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th scope="col"><?php _e( 'Start Date', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'End Date', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'Invoice Number', 'orbis_subscriptions' ); ?></th>
				</tr>
			</thead>

			<tbody>
				
				<?php foreach ( $invoices as $invoice ) : ?>
				
					<tr>
						<td>
							<?php echo $invoice->start_date; ?>
						</td>
						<td>
							<?php echo $invoice->end_date; ?>
						</td>
						<td>
							<a href="/facturen/<?php echo $invoice->invoice_number; ?>/" target="_blank">
								<?php echo $invoice->invoice_number; ?>
							</a>
						</td>
					</tr>
				
				<?php endforeach; ?>

			</tbody>
		</table>
	</div>

<?php endif; ?>