<?php
/**
 * Company subscriptions
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Subscriptions
 */

use Pronamic\WordPress\Money\Money;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $wpdb->orbis_companies WHERE post_id = %d;", get_the_ID() ) );

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
		s.post_id
	FROM
		$wpdb->orbis_subscriptions AS s
			LEFT JOIN
		$wpdb->orbis_subscription_products AS st
				ON s.type_id = st.id
	WHERE
		company_id = %d
	ORDER BY
		activation_date ASC
	;",
	$id
);

$subscriptions = $wpdb->get_results( $query );

if ( $subscriptions ) : ?>

	<table class="table table-striped mb-0">
		<thead>
			<tr>
				<th class="border-top-0" scope="col"><?php esc_html_e( 'Activation Date', 'orbis-subscriptions' ); ?></th>
				<th class="border-top-0" scope="col"><?php esc_html_e( 'Subscription', 'orbis-subscriptions' ); ?></th>
				<th class="border-top-0" scope="col"><?php esc_html_e( 'Name', 'orbis-subscriptions' ); ?></th>
				<th class="border-top-0" scope="col"><?php esc_html_e( 'Price', 'orbis-subscriptions' ); ?></th>
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
						<?php echo esc_html( date_i18n( 'D j M Y', strtotime( $subscription->activation_date ) ) ); ?>
					</td>
					<td>
						<a href="<?php echo esc_attr( get_permalink( $subscription->post_id ) ); ?>" target="_blank">
							<?php echo esc_html( $subscription->subscription_name ); ?>
						</a>
					</td>
					<td>
						<a href="<?php echo esc_attr( get_permalink( $subscription->post_id ) ); ?>" target="_blank">
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

<?php else : ?>

	<div class="card-body">
		<p class="text-muted m-0">
			<?php esc_html_e( 'No subscriptions found.', 'orbis-subscriptions' ); ?>
		</p>
	</div>

<?php endif; ?>
