<?php 

global $orbis_subscriptions_invoices;

$results = $orbis_subscriptions_invoices;

?>
<div class="panel">
	<table class="table table-striped table-bordered">
		<thead>
			<tr>
				<th scope="col"><?php _e( 'ID', 'orbis_subscriptions' ); ?></th>
				<th scope="col"><?php _e( 'Create Date', 'orbis_subscriptions' ); ?></th>
				<th scope="col"><?php _e( 'User', 'orbis_subscriptions' ); ?></th>
				<th scope="col"><?php _e( 'Company', 'orbis_subscriptions' ); ?></th>
				<th scope="col"><?php _e( 'Subscription', 'orbis_subscriptions' ); ?></th>
				<th scope="col"><?php _e( 'Price', 'orbis_subscriptions' ); ?></th>
				<th scope="col"><?php _e( 'Name', 'orbis_subscriptions' ); ?></th>
				<th scope="col"><?php _e( 'Start Date', 'orbis_subscriptions' ); ?></th>
				<th scope="col"><?php _e( 'End Date', 'orbis_subscriptions' ); ?></th>
				<th scope="col"><?php _e( 'Invoice Number', 'orbis_subscriptions' ); ?></th>
			</tr>
		</thead>

		<tbody>

			<?php foreach ( $results as $i => $result ) : ?>
			
				<tr>
					<td>
						<?php echo $result->id; ?>
					</td>
					<td>
						<?php echo date_i18n( 'D j M Y', strtotime( $result->create_date ) ); ?>
					</td>
					<td>
						<?php echo $result->user_display_name; ?>
					</td>
					<td>
						<?php echo $result->company_name; ?>
					</td>
					<td>
						<?php echo $result->subscription_name; ?>
					</td>
					<td>
						<?php echo $result->price; ?>
					</td>
					<td>
						<?php echo $result->name; ?>
					</td>
					<td>
						<?php echo date_i18n( 'D j M Y', strtotime( $result->start_date ) ); ?>
					</td>
					<td>
						<?php echo date_i18n( 'D j M Y', strtotime( $result->end_date ) ); ?>
					</td>
					<td>
						<a href="/facturen/<?php echo $result->invoice_number; ?>" target="_blank">
							<?php echo $result->invoice_number; ?>
						</a>
					</td>
				</tr>
			
			<?php endforeach; ?>

		</tbody>
	</table>
</div>