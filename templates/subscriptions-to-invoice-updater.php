<?php 

global $wpdb;
global $orbis_subscriptions_to_invoice;

$results = $orbis_subscriptions_to_invoice;

$statuses = array(
	'inserted' => __( 'Inserted', 'orbis_subscriptions' ),
	'failed'   => __( 'Failed', 'orbis_subscriptions' )
);

foreach ( $statuses as $status => $label ) {
	if ( isset( $_GET[$status] ) ) {
		$ids = $_GET[$status];
		$ids = explode( ',', $ids );

		if ( ! empty( $ids ) ) {
			echo '<h2>', $label, '</h2>';
			
			$subscriptions = get_posts( array(
				'post_type' => 'any',
				'post__in'  => $ids
			) );
			
			if ( ! empty( $subscriptions ) ) {
				echo '<ul>';
				
				foreach ( $subscriptions as $subscription ) {
					echo '<li>';
					printf( '<a href="%s" target="_blank">%s</a>', get_permalink( $subscription->ID ), get_the_title( $subscription->ID ) );
					echo '</li>';
				}
				
				echo '</ul>';
			}
		}
	}
}

?>
<form method="post" action="">
	<div class="panel">
		<div class="content">
			<button name="subscriptions_invoices_update" type="submit"><?php _e( 'Update', 'orbis_subscriptions' ); ?></button>
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
							<input name="<?php printf( $name, $i, 'post_id' ); ?>" value="<?php echo $result->post_id; ?>" type="hidden" />
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