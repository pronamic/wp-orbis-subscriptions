<?php

use Pronamic\WordPress\Money\Money;

global $post;

$query = new WP_Query( array(
	'post_type'       => 'orbis_subscription',
	'connected_type'  => 'orbis_subscriptions_to_purchases',
	'connected_items' => get_queried_object(),
	'nopaging'        => true, //phpcs:ignore WordPress.VIP.PostsPerPage.posts_per_page_nopaging
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
				<th scope="col"><?php esc_html_e( 'Name', 'orbis_subscriptions' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Price', 'orbis_subscriptions' ); ?></th>
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th scope="row"><?php esc_html_e( 'Revenue', 'orbis_subscriptions' ); ?></td>
				<td>
					<?php
					$revenue = new Money( $revenue, 'EUR' );
					echo esc_html( $revenue->format_i18n() );
					?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Costs', 'orbis_subscriptions' ); ?></td>
				<td>
					<?php
					$costs = new Money( $costs, 'EUR' );
					echo esc_html( $costs->format_i18n() );
					?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Profit', 'orbis_subscriptions' ); ?></td>
				<td>
					<?php
					$profit = new Money( $profit, 'EUR' );
					echo esc_html( $profit->format_i18n() );
					?>
				</td>
			</tr>
		</tfoot>

		<tbody>

			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
			?>

				<tr <?php post_class(); ?>>
					<td>
						<a href="<?php the_permalink(); ?>">
							<?php the_title(); ?>
						</a>
					</td>
					<td>
						<?php echo esc_html( orbis_subscription_the_price() ); ?>
					</td>
				</tr>

			<?php endwhile; ?>

		</tbody>
	</table>
</div>

<?php

wp_reset_postdata();
