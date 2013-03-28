<?php

global $wpdb;

$id = $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM orbis_subscriptions WHERE post_id = %d;', get_the_ID() ) );

$query = $wpdb->prepare( '
	SELECT
		user.display_name AS user_display_name,
		si.create_date,
		si.start_date,
		si.end_date,
		si.invoice_number
	FROM
		orbis_subscriptions_invoices AS si
				LEFT JOIN
			wp_users AS user
					ON user.ID = si.user_id
	WHERE
		subscription_id = %d
	ORDER BY
		start_date ASC
	;',
	$id
);

$invoices = $wpdb->get_results( $query );

if ( $invoices ) : ?>

	<div class="panel">
		<header>
			<h3><?php _e( 'Invoices', 'orbis_subscriptions' ); ?></h3>
		</header>

		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th scope="col"><?php _e( 'Create Date', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'User', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'Start Date', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'End Date', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'Invoice Number', 'orbis_subscriptions' ); ?></th>
				</tr>
			</thead>

			<tbody>
				
				<?php foreach ( $invoices as $invoice ) : ?>
				
					<tr>
						<td>
							<?php echo date_i18n( 'D j M Y H:i:s', strtotime( $invoice->create_date ) ); ?>
						</td>
						<td>
							<?php echo $invoice->user_display_name; ?>
						</td>
						<td>
							<?php echo date_i18n( 'D j M Y', strtotime( $invoice->start_date ) ); ?>
						</td>
						<td>
							<?php echo date_i18n( 'D j M Y', strtotime( $invoice->end_date ) ); ?>
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