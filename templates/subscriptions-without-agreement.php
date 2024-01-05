<?php

use Pronamic\WordPress\Money\Money;

$query = new WP_Query(
	[
		'post_type'      => 'orbis_subscription',
		'posts_per_page' => 50,
		'meta_query'     => [ // WPCS: slow query ok.
			[
				'key'     => '_orbis_subscription_agreement_id',
				'compare' => 'NOT EXISTS',
			],
		],
	] 
);

$subscriptions = $query->posts;

if ( $query->have_posts() ) : ?>

	<div class="panel">
		<table class="table table-striped table-bordered">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Activation Date', 'orbis-subscriptions' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Subscription', 'orbis-subscriptions' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Price', 'orbis-subscriptions' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Actions', 'orbis-subscriptions' ); ?></th>
			</tr>
		</thead>

		<tbody>

			<?php foreach ( $subscriptions as $subscription ) : ?>

				<?php

				$classes = [ 'subscription' ];
				if ( $subscription->canceled ) {
					$classes[] = 'canceled';
				}

				?>
				<tr class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
					<td>
						<?php echo esc_html( date_i18n( 'D j M Y H:i:s', strtotime( $subscription->subscription_activation_date ) ) ); ?>
					</td>
					<td>
						<a href="<?php echo esc_url( get_permalink( $subscription->ID ) ); ?>" target="_blank">
							<?php echo esc_html( $subscription->post_title ); ?>
						</a>
					</td>
					<td>
						<?php
						$price = new Money( $subscription->subscription_type_price, 'EUR' );
						echo esc_html( $price->format_i18n() );
						?>
					</td>
					<td>
						<a href="<?php echo esc_url( get_edit_post_link( $subscription->ID ) ); ?>">
							<?php esc_html_e( 'Edit', 'orbis-subscriptions' ); ?>
						</a>
					</td>
				</tr>

			<?php endforeach; ?>

		</tbody>
	</table>
	</div>

<?php endif; ?>
