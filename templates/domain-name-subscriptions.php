<?php

use Pronamic\WordPress\Money\Money;

global $wpdb;

$query = $wpdb->prepare(
	"
	SELECT
		s.id, 
		s.type_id,
		st.name AS subscription_name,
		st.price,
		s.name,
		s.activation_date,
		s.cancel_date IS NOT NULL AS canceled,
		s.post_id,
		c.post_id AS company_post_id,
		c.name AS company_name
	FROM
		$wpdb->orbis_subscriptions AS s
			LEFT JOIN
		$wpdb->orbis_subscription_products AS st
				ON s.type_id = st.id
			LEFT JOIN
		$wpdb->orbis_companies AS c
				ON s.company_id = c.id
	WHERE
		s.name LIKE %s
	ORDER BY
		activation_date ASC
	;",
	get_the_title() . '%'
);

$subscriptions = $wpdb->get_results( $query );

if ( $subscriptions ) : ?>

	<div class="panel">
		<header>
			<h3><?php esc_html_e( 'Subscriptions', 'orbis-subscriptions' ); ?></h3>
		</header>

		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Activation Date', 'orbis-subscriptions' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Company', 'orbis-subscriptions' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Subscription', 'orbis-subscriptions' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Name', 'orbis-subscriptions' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Price', 'orbis-subscriptions' ); ?></th>
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
							<?php echo esc_html( date_i18n( 'D j M Y H:i:s', strtotime( $subscription->activation_date ) ) ); ?>
						</td>
						<td>
							<a href="<?php echo esc_url( get_permalink( $subscription->company_post_id ) ); ?>" target="_blank">
								<?php echo esc_html( $subscription->company_name ); ?>
							</a>
						</td>
						<td>
							<a href="<?php echo esc_url( get_permalink( $subscription->post_id ) ); ?>" target="_blank">
								<?php echo esc_html( $subscription->subscription_name ); ?>
							</a>
						</td>
						<td>
							<a href="<?php echo esc_url( get_permalink( $subscription->post_id ) ); ?>" target="_blank">
								<?php echo esc_html( $subscription->name ); ?>
							</a>
						</td>
						<td>
							<?php
							$price = new Money( $subscription->price, 'EUR' );
							echo esc_html( $price->format_i18n() );
							?>
						</td>
					</tr>

				<?php endforeach; ?>

			</tbody>
		</table>
	</div>

<?php endif; ?>
