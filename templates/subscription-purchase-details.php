<?php

global $post;

$query = new WP_Query( array(
	'post_type'       => 'orbis_subscription',
	'connected_type'  => 'orbis_subscriptions_to_purchases',
	'connected_items' => get_queried_object(),
	'nopaging'        => true,
) );

// Costs
$costs = get_post_meta( $post->ID, '_orbis_subscription_purchase_price', true );

// Revenue
$revenue = 0;

if ( $query->have_posts() ) {
	while ( $query->have_posts() ) {
		$query->the_post();

		if ( empty( $post->subscription_cancel_date ) ) {
			$revenue += orbis_subscription_get_the_price();
		}
	}

	wp_reset_postdata();
}

update_post_meta( $post->ID, '_orbis_subscription_purchase_revenue', $revenue );

// Profit
$profit = $revenue - $costs;

?>

<div class="panel">
	<table class="table table-striped table-bordered">
		<thead>
			<tr>
				<th scope="col"><?php _e( 'Name', 'orbis_subscriptions' ); ?></th>
				<th scope="col"><?php _e( 'Price', 'orbis_subscriptions' ); ?></th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th scope="row"><?php _e( 'Revenue', 'orbis_subscriptions' ); ?></td>
				<td><?php echo orbis_price( $revenue ); ?></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Costs', 'orbis_subscriptions' ); ?></td>
				<td><?php echo orbis_price( $costs ); ?></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Profit', 'orbis_subscriptions' ); ?></td>
				<td><?php echo orbis_price( $profit ); ?></td>
			</tr>
		</tfoot>

		<tbody>

			<?php while ( $query->have_posts() ) : $query->the_post(); ?>

				<tr <?php post_class(); ?>>
					<td>
						<a href="<?php the_permalink(); ?>">
							<?php the_title(); ?>
						</a>
					</td>
					<td>
						<?php echo orbis_subscription_the_price(); ?>
					</td>
				</tr>
			
			<?php endwhile; ?>

		</tbody>
	</table>
</div>

<?php 

wp_reset_postdata();
