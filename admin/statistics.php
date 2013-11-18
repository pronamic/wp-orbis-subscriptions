<div class="wrap">
	<?php screen_icon( 'orbis' ); ?>

	<h2><?php echo get_admin_page_title(); ?></h2>
	
	<?php 
	
	global $wpdb;
	
	$query = "
		SELECT
			type.name AS name,
			COUNT( IF( subscription.expiration_date < NOW(), subscription.id, NULL ) ) AS expired_count,
			COUNT( subscription.id ) AS total_count
		FROM
			$wpdb->orbis_subscription_types AS type
				LEFT JOIN
			$wpdb->orbis_subscriptions AS subscription
					ON subscription.type_id = type.id
		GROUP BY
			type.id
		;
	";
	
	// echo '<pre>', $query, '</pre>';
	
	$expired_stats = $wpdb->get_results( $query );
		
	?>
	<table>
		<thead>
			<tr>
				<th scope="col"><?php _e( 'Type', 'orbis_subscriptions' ); ?></th>
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
						<?php echo $stat->expired_count; ?>
					</td>
					<td>
						<?php echo $stat->total_count; ?>
					</td>
					<td>
						<?php 
						
						$percent = ( 100 / $stat->total_count ) * $stat->expired_count;

						echo number_format( $percent, 2, ',', '.' ); 
						
						?>
					</td>
				</tr>

			<?php endforeach; ?>
					
		</tbody>
	</table>
</div>