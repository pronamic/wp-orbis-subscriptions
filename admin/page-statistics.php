<?php
/**
 * Page statistics
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Subscriptions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<?php

	global $wpdb;

	$query = "
		SELECT
			product.name AS name,
			COUNT( IF( subscription.cancel_date IS NULL, subscription.id, NULL ) ) AS active_count,
			COUNT( IF( subscription.expiration_date < NOW(), subscription.id, NULL ) ) AS expired_count,
			COUNT( subscription.id ) AS total_count
		FROM
			$wpdb->orbis_products AS product
				LEFT JOIN
			$wpdb->orbis_subscriptions AS subscription
					ON subscription.product_id = product.id
		GROUP BY
			product.id
		;
	";

	$expired_stats = $wpdb->get_results( $query );

	?>
	<ul class="subsubsub">
		<li class="all">
			<a href=""><?php esc_html_e( 'All', 'orbis-subscriptions' ); ?> <span class="count">(<?php echo esc_html( count( $expired_stats ) ); ?>)</span></a></li>
	</ul>

	<table class="widefat">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Product', 'orbis-subscriptions' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Active', 'orbis-subscriptions' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Expired', 'orbis-subscriptions' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Total', 'orbis-subscriptions' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Expire Rate', 'orbis-subscriptions' ); ?></th>
			</tr>
		</thead>

		<tbody>

			<?php foreach ( $expired_stats as $stat ) : ?>

				<tr>
					<td>
						<?php echo esc_html( $stat->name ); ?>
					</td>
					<td>
						<?php echo esc_html( $stat->active_count ); ?>
					</td>
					<td>
						<?php echo esc_html( $stat->expired_count ); ?>
					</td>
					<td>
						<?php echo esc_html( $stat->total_count ); ?>
					</td>
					<td>
						<?php

						$percent = 0;
						if ( $stat->total_count > 0 ) {
							$percent = ( 100 / $stat->total_count ) * $stat->expired_count;
						}

						echo esc_html( number_format( $percent, 2, ',', '.' ) );

						?>
					</td>
				</tr>

			<?php endforeach; ?>

		</tbody>
	</table>
</div>
