<?php
/**
 * Product subscriptions
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

$query = $wpdb->prepare(
	"
	SELECT
		subscription.id, 
		subscription.product_id,
		product.name AS product_name,
		product.price,
		subscription.name,
		subscription.activation_date,
		subscription.cancel_date IS NOT NULL AS canceled,
		subscription.post_id,
		company.post_id AS company_post_id,
		company.name AS company_name
	FROM
		$wpdb->orbis_subscriptions AS subscription
			LEFT JOIN
		$wpdb->orbis_products AS product
				ON subscription.product_id = product.id
			LEFT JOIN
		$wpdb->orbis_companies AS company
				ON subscription.company_id = company.id
	WHERE
		product.post_id = %d
	ORDER BY
		activation_date DESC
	LIMIT 10
	;",
	get_the_ID()
);

$subscriptions = $wpdb->get_results( $query );

if ( $subscriptions ) : ?>

	<div class="card mb-4">
		<div class="card-header">
			<?php esc_html_e( 'Most recent subscriptions', 'orbis-subscriptions' ); ?>
		</div>

		<table class="table table-striped table-bordered mb-0">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Activation date', 'orbis-subscriptions' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Company', 'orbis-subscriptions' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Name', 'orbis-subscriptions' ); ?></th>
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
							<a href="<?php echo esc_url( get_permalink( $subscription->company_post_id ) ); ?>" target="_blank">
								<?php echo esc_html( $subscription->company_name ); ?>
							</a>
						</td>
						<td>
							<a href="<?php echo esc_url( get_permalink( $subscription->post_id ) ); ?>" target="_blank">
								<?php echo esc_html( $subscription->name ); ?>
							</a>
						</td>
					</tr>

				<?php endforeach; ?>

			</tbody>
		</table>

		<div class="card-footer">
    		<?php

    		$url = \add_query_arg(
    			[
    				'orbis_product_post_id' => \get_the_ID(),
    			],
    			\get_post_type_archive_link( 'orbis_subscription' )
    		);

    		\printf(
    			'<a href="%s" class="card-link">%s</a>',
    			\esc_url( $url ),
    			\esc_html__( 'View all subscriptions', 'orbis-subscriptions' )
    		);

    		?>
    	</div>
	</div>

<?php endif; ?>
