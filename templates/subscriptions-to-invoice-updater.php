<?php 

global $wpdb;
global $orbis_subscriptions_to_invoice;

$results = $orbis_subscriptions_to_invoice;

$action = '';
if ( function_exists( 'twinfield_get_form_action' ) ) {
	$action = twinfield_get_form_action( 'invoice' );
}

if ( isset( $_POST['update'] ) ) {
	$subscriptions = $_POST['subscriptions'];

	if ( ! empty( $subscriptions ) ) {
		echo '<ul>';

		foreach ( $subscriptions as $subscription ) {
			$id             = $subscription['id'];
			$invoice_number = $subscription['invoice_number'];
			$date_start     = $subscription['date_start'];
			$date_end       = $subscription['date_end'];

			if ( ! empty( $invoice_number ) ) {
				$result = $wpdb->insert(
					'orbis_subscriptions_invoices',
					array(
						'subscription_id' => $id,
						'invoice_number'  => $invoice_number,
						'start_date'      => $date_start,
						'end_date'        => $date_end
					),
					array(
						'%d',
						'%s',
						'%s',
						'%s'
					)
				);
	
				echo '<li>';
	
				if ( $result === false ) {
					echo 'Failed';
				} else {
					echo 'Added';
				}
				
				echo ' - ';
				
				printf( 'ID %s, Invoice Number: %s, Periode: %s - %s', $id, $invoice_number, $date_start, $date_end );
				
				echo '</li>';
			}
		}

		echo '</ul>';
	}
}

?>
<form method="post" action="">
	<div class="panel">
		<div class="content">
			<button name="update" type="submit"><?php _e( 'Update', 'orbis_subscriptions' ); ?></button>
		</div>
	</div>
	
	<div class="panel">
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th scope="col"><?php _e( 'ID', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'Company', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'Subscription', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'Price', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'Name', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'Activation Date', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'Invoice Number', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'Notice', 'orbis_subscriptions' ); ?></th>
				</tr>
			</thead>
	
			<tbody>
	
				<?php foreach ( $results as $i => $result ) : ?>
				
					<tr>
						<?php 
						
						$name = 'subscriptions[%d][%s]';
						
						$date_start = new DateTime( $result->activation_date );
						$date_end   = new DateTime( $result->activation_date );
						
						$year  = date( 'Y' );
						$month = $date_start->format( 'm' );
						$day   = $date_start->format( 'd' );

						$date_start->setDate( $year, $month, $day );
						$date_end->setDate( $year + 1, $month, $day );

						$date_start = $date_start->format( 'Y-m-d H:i:s' );
						$date_end   = $date_end->format( 'Y-m-d H:i:s' );

						?>
						<td>
							<?php echo $result->id; ?>
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
							<?php echo $result->activation_date; ?>
						</td>
						<td>
							<?php echo $result->invoice_number; ?>
							<input name="<?php printf( $name, $i, 'id' ); ?>" value="<?php echo $result->id; ?>" type="hidden" />
							<input name="<?php printf( $name, $i, 'invoice_number' ); ?>" value="" type="text" />
							<input name="<?php printf( $name, $i, 'date_start' ); ?>" value="<?php echo $date_start; ?>" type="hidden" />
							<input name="<?php printf( $name, $i, 'date_end' ); ?>" value="<?php echo $date_end; ?>" type="hidden" />
						</td>
						<td>
							<?php if ( $result->to_late ) : ?>
								<span class="text-error">!!!</span>
							<?php endif; ?>
						</td>
					</tr>
				
				<?php endforeach; ?>
	
			</tbody>
		</table>
	</div>
</form>