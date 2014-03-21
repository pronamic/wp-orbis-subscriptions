<?php

$date = date_parse( filter_input( INPUT_GET, 'date', FILTER_SANITIZE_STRING ) );

if ( ! $date['year'] ) {
    $date['year'] = date( 'Y' );
}

if ( ! $date['month'] ) {
    $date['month'] = date( 'm' );
}

$date_string = '01-' . $date['month'] . '-' . $date['year'];

// Interval
$interval = filter_input( INPUT_GET, 'interval', FILTER_SANITIZE_STRING );

?>

<form class="form-inline" action="" method="get">
	<div class="btn-group">
		<a href="<?php echo add_query_arg( array( 'date' => date( 'd-m-Y', strtotime( $date_string . ' - 1 month' ) ) ) ); ?>" class="btn btn-default">&lt;</a>
		<a href="<?php echo add_query_arg( array( 'date' => date( 'd-m-Y', strtotime( $date_string . ' + 1 month' ) ) ) ); ?>" class="btn btn-default">&gt;</a>
		<a href="<?php echo remove_query_arg( array( 'date' ) ); ?>" class="btn btn-default"><?php _e( 'This month', 'orbis_subscriptions' ); ?></a>
	</div>

	<div class="pull-right">
		<select name="interval" class="form-control">
			<option value=""></option>
			<option value="Y" <?php selected( $interval, 'Y' ); ?>><?php _e( 'Yearly', 'orbis_subscriptions' ); ?></option>
			<option value="M" <?php selected( $interval, 'M' ); ?>><?php _e( 'Monthly', 'orbis_subscriptions' ); ?></option>
		</select>

		<button class="btn btn-default" type="submit">Filter</button>
	</div>
</form>

<hr />

<?php

global $wpdb;
global $orbis_subscriptions_to_invoice;

$results = $orbis_subscriptions_to_invoice;

$statuses = array(
	'inserted' => __( 'Inserted', 'orbis_subscriptions' ),
	'failed'   => __( 'Failed', 'orbis_subscriptions' ),
);

foreach ( $statuses as $status => $label ) {
	if ( isset( $_GET[ $status ] ) ) {
		$ids = $_GET[ $status ];
		$ids = explode( ',', $ids );

		if ( ! empty( $ids ) ) {
			echo '<h2>', $label, '</h2>';
			
			$subscriptions = get_posts( array(
				'post_type' => 'any',
				'post__in'  => $ids,
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

<h2><?php echo date( 'M Y', strtotime( $date_string ) ); ?></h2>
<form method="post" action="">
	<div class="panel">
		<div class="content">
			<button name="subscriptions_invoices_update" type="submit"><?php _e( 'Update', 'orbis_subscriptions' ); ?></button>
		</div>
	</div>

    <h3><?php _e( 'Subscriptions', 'orbis_subscriptions' ); ?></h3>

	<div class="panel">
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th scope="col"><?php _e( 'ID', 'orbis_subscriptions' ); ?></th>
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
			
					<?php 
					
					$classes = array();
					if ( $result->too_late ) {
						$classes[] = 'warning';
					}
					
					?>
					<tr class="<?php echo implode( ' ', $classes ); ?>">
						<?php 
						
						$name = 'subscriptions[%d][%s]';
						
						$date_start = new DateTime( $result->activation_date );
						$date_end   = new DateTime( $result->activation_date );

						$day   = $date_start->format( 'd' );
                        $month = $date_start->format( 'm' );

                        if ( $result->interval === 'Y' ) {
                            $date_start->setDate( $date['year'], $month, $day );

                            $date_end_timestamp = strtotime( $date['year'] . '-' . $month . '-' . $day . ' + 1 year' );
                        } else if ( $result->interval === 'M' ) {
                            $date_start->setDate( $date['year'], $date['month'], $day );

                            $date_end_timestamp = strtotime( $date['year'] . '-' . $date['month'] . '-' . $day . ' + 1 month' );
                        } else {
                            $date_end_timestamp = strtotime( $date_string );
                        }

						$date_end->setDate( date( 'Y', $date_end_timestamp ), date( 'm', $date_end_timestamp ), $day );

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
							<a href="<?php echo get_permalink( $result->post_id ); ?>">
								<?php echo $result->subscription_name; ?>
							</a>
						</td>
						<td>
							<?php echo orbis_price( $result->price ); ?>
						</td>
						<td>
							<?php echo $result->name; ?>
						</td>
						<td>
							<?php echo $date_start; ?>
						</td>
						<td>
							<?php echo $date_end; ?>
						</td>
						<td>
							<?php echo $result->invoice_number; ?>
							<input name="<?php printf( $name, $i, 'id' ); ?>" value="<?php echo $result->id; ?>" type="hidden" />
							<input name="<?php printf( $name, $i, 'post_id' ); ?>" value="<?php echo $result->post_id; ?>" type="hidden" />
							<input name="<?php printf( $name, $i, 'invoice_number' ); ?>" value="" type="text" />
							<input name="<?php printf( $name, $i, 'date_start' ); ?>" value="<?php echo $date_start; ?>" type="hidden" />
							<input name="<?php printf( $name, $i, 'date_end' ); ?>" value="<?php echo $date_end; ?>" type="hidden" />
						</td>
					</tr>
				
				<?php endforeach; ?>
	
			</tbody>
		</table>
	</div>
</form>