<div class="wrap">
	<?php screen_icon( 'orbis' ); ?>

	<h2><?php echo get_admin_page_title(); ?></h2>
	
	<?php 
	
	global $wpdb;
	
	$query = "
		SELECT
			type.name AS name ,
			COUNT( sub.id ) AS number
		FROM
			$wpdb->orbis_subscriptions AS sub
				LEFT JOIN
			$wpdb->orbis_subscription_types AS type
					ON sub.type_id = type.id
		WHERE
			expiration_date < NOW()
		GROUP BY
			type_id
		;
	";
	
	$expired_stats = $wpdb->get_results( $query );
		
	?>
	<table>
		<thead>
			<tr>
				<th scope="col"><?php _e( 'Type', 'orbis_subscriptions' ); ?></th>
				<th scope="col"><?php _e( 'Number', 'orbis_subscriptions' ); ?></th>
			</tr>
		</thead>
		
		<tbody>

			<?php foreach ( $expired_stats as $stat ) : ?>

				<tr>
					<td>
						<?php echo $stat->name; ?>
					</td>
					<td>
						<?php echo $stat->number; ?>
					</td>
				</tr>

			<?php endforeach; ?>
					
		</tbody>
	</table>
</div>