<div class="wrap">
	<?php screen_icon( 'orbis' ); ?>

	<h2><?php echo get_admin_page_title(); ?></h2>
	
	<?php 
	
	global $wpdb;
	
	$query = "
		SELECT
			product.name AS name,
			COUNT( IF( subscription.cancel_date IS NULL, subscription.id, NULL ) ) AS active_count,
			COUNT( IF( subscription.expiration_date < NOW(), subscription.id, NULL ) ) AS expired_count,
			COUNT( subscription.id ) AS total_count
		FROM
			$wpdb->orbis_subscription_products AS product
				LEFT JOIN
			$wpdb->orbis_subscriptions AS subscription
					ON subscription.type_id = product.id
		GROUP BY
			product.id
		;
	";
	
	// echo '<pre>', $query, '</pre>';
	
	$expired_stats = $wpdb->get_results( $query );
		
	?>
	<ul class="subsubsub">
		<li class="all">
			<a href=""><?php _e( 'All', 'orbis_subscriptions' ); ?> <span class="count">(<?php echo count( $expired_stats ); ?>)</span></a></li>
	</ul>

	<table class="widefat">
		<thead>
			<tr>
				<th scope="col"><?php _e( 'Product', 'orbis_subscriptions' ); ?></th>
				<th scope="col"><?php _e( 'Active', 'orbis_subscriptions' ); ?></th>
				<th scope="col"><?php _e( 'Expired', 'orbis_subscriptions' ); ?></th>
				<th scope="col"><?php _e( 'Total', 'orbis_subscriptions' ); ?></th>
				<th scope="col"><?php _e( 'Expire Rate', 'orbis_subscriptions' ); ?></th>
			</tr>
		</thead>
		
		<tbody>

			<?php foreach ( $expired_stats as $stat ) : ?>

				<tr>
					<td>
						<?php echo $stat->name; ?>
					</td>
					<td>
						<?php echo $stat->active_count; ?>
					</td>
					<td>
						<?php echo $stat->expired_count; ?>
					</td>
					<td>
						<?php echo $stat->total_count; ?>
					</td>
					<td>
						<?php 
						
						$percent = 0;
						if ( $stat->total_count > 0 ) {
							$percent = ( 100 / $stat->total_count ) * $stat->expired_count;
						}

						echo number_format( $percent, 2, ',', '.' ); 
						
						?>
					</td>
				</tr>

			<?php endforeach; ?>
					
		</tbody>
	</table>
</div>