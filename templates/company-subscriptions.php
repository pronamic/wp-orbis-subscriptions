<?php

global $wpdb;

$id = $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM orbis_companies WHERE post_id = %d;', get_the_ID() ) );

$query = $wpdb->prepare( '
	SELECT
		s.id, 
		s.type_id,
		st.name AS subscription_name,
		st.price,
		s.name,
		s.activation_date,
		s.cancel_date IS NOT NULL AS canceled,
		s.post_id
	FROM
		orbis_subscriptions AS s
			LEFT JOIN
		orbis_subscription_types AS st
				ON s.type_id = st.id
	WHERE
		company_id = %d
	ORDER BY
		activation_date ASC
	;',
	$id
);

$subscriptions = $wpdb->get_results( $query );

if ( $subscriptions ) : ?>

	<div class="panel">
		<header>
			<h3><?php _e( 'Subscriptions', 'orbis_subscriptions' ); ?></h3>
		</header>

		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th scope="col"><?php _e( 'Activation Date', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'Subscription', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'Name', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'Price', 'orbis_subscriptions' ); ?></th>
				</tr>
			</thead>

			<tbody>
				
				<?php foreach ( $subscriptions as $subscription ) : ?>

					<?php 
					
					$classes = array( 'subscription' );
					if ( $subscription->canceled ) {
						$classes[] = 'canceled';
					}
					
					?>
					<tr class="<?php echo implode( ' ', $classes ); ?>">
						<td>
							<?php echo date_i18n( 'D j M Y H:i:s', strtotime( $subscription->activation_date ) ); ?>
						</td>
						<td>
							<a href="<?php echo get_permalink( $subscription->post_id ); ?>" target="_blank">
								<?php echo $subscription->subscription_name; ?>
							</a>
						</td>
						<td>
							<a href="<?php echo get_permalink( $subscription->post_id ); ?>" target="_blank">
								<?php echo $subscription->name; ?>
							</a>
						</td>
						<td>
							<?php echo orbis_price( $subscription->price ); ?>
						</td>
					</tr>
				
				<?php endforeach; ?>

			</tbody>
		</table>
	</div>

<?php endif; ?>