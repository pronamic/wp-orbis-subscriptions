<?php
/**
 * Page billing
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Subscriptions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Pronamic\WordPress\Money\Money;

global $wpdb;

$where_condition = '1 = 1';

$where_condition .= ' AND ( ';
$where_condition .= ' ( subscription.billed_to IS NULL OR subscription.billed_to < DATE_ADD( CURDATE(), INTERVAL 14 DAY ) )';
$where_condition .= ' AND ( subscription.cancel_date IS NULL OR subscription.cancel_date > DATE_SUB( subscription.expiration_date, INTERVAL 14 DAY ) )';
$where_condition .= ' AND ( subscription.end_date IS NULL OR subscription.end_date > subscription.expiration_date )';
$where_condition .= ' ) ';

$query = "
	SELECT
		company.id AS company_id,
		company.name AS company_name,
		company.post_id AS company_post_id,
		product.name AS subscription_name,
		product.price,
		product.twinfield_article,
		product.interval,
		product.post_id AS product_post_id,
		subscription.id,
		subscription.product_id,
		subscription.post_id,
		subscription.name,
		subscription.activation_date,
		subscription.expiration_date,
		subscription.cancel_date,
		subscription.billed_to
	FROM
		$wpdb->orbis_subscriptions AS subscription
			LEFT JOIN
		$wpdb->orbis_companies AS company
				ON subscription.company_id = company.id
			LEFT JOIN
		$wpdb->orbis_products AS product
				ON subscription.product_id = product.id
	WHERE
		product.auto_renew
			AND
		$where_condition
	GROUP BY
		subscription.id
	ORDER BY
		DAYOFYEAR( subscription.activation_date )
	;";

$subscriptions = $wpdb->get_results( $query );

$companies = [];

foreach ( $subscriptions as $subscription ) {
	$company_id = $subscription->company_id;

	if ( ! isset( $companies[ $company_id ] ) ) {
		$company = new stdClass();

		$company->id            = $subscription->company_id;
		$company->name          = $subscription->company_name;
		$company->post_id       = $subscription->company_post_id;
		$company->subscriptions = [];

		$companies[ $company_id ] = $company;
	}

	$companies[ $company_id ]->subscriptions[] = $subscription;
}

?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<?php foreach ( $companies as $company ) : ?>

		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">
					<a href="<?php echo esc_url( get_permalink( $company->post_id ) ); ?>"><?php echo esc_html( $company->name ); ?></a>
				</h2>
			</div>

			<div class="panel-body">
				<p>
					<?php

					$ids = wp_list_pluck( $company->subscriptions, 'id' );

					$url = add_query_arg(
						[
							'orbis_company_id'       => $company->id,
							'orbis_subscription_ids' => implode( ',', $ids ),
						],
						home_url( 'moneybird/sales-invoices/new' )
					);

					printf(
						'<a href="%s">%s</a>',
						esc_url( $url ),
						esc_html( $url )
					);

					?>
				</p>
			</div>

			<!-- Table -->
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'ID', 'orbis-subscriptions' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Subscription', 'orbis-subscriptions' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Interval', 'orbis-subscriptions' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Price', 'orbis-subscriptions' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Name', 'orbis-subscriptions' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Start Date', 'orbis-subscriptions' ); ?></th>
						<th scope="col"><?php esc_html_e( 'End Date', 'orbis-subscriptions' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Cancel Date', 'orbis-subscriptions' ); ?></th>
					</tr>
				</thead>

				<tfoot>
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td>
							<?php

							$total = 0;
							foreach ( $company->subscriptions as $i => $result ) {
								$total += $result->price;
							}

							$total_price = new Money( $total, 'EUR' );

							echo esc_html( $total_price->format_i18n() );

							?>
						</td>
						<td colspan="4">

						</td>
					</tr>
				</tfoot>

				<tbody>

					<?php foreach ( $company->subscriptions as $i => $result ) : ?>

						<?php

						$name = 'subscriptions[%s][%s]';

						$date_start = new DateTimeImmutable( empty( $result->billed_to ) ? $result->activation_date : $result->billed_to );
						$date_end   = clone $date_start;

						switch ( $result->interval ) {
							case 'M':
								$date_end = $date_start->modify( '+1 month' );

								break;
							case 'Q':
								$date_end = $date_start->modify( '+3 month' );

								break;
							case '2Y':
								$date_end = $date_start->modify( '+2 year' );

								break;
							case '3Y':
								$date_end = $date_start->modify( '+3 year' );

								break;
							case 'Y':
							default:
								$date_end = $date_start->modify( '+1 year' );

								break;
						}

						?>
						<tr>
							<td>
								<?php echo esc_html( $result->id ); ?>
							</td>
							<td>
								<a href="<?php echo esc_url( get_permalink( $result->post_id ) ); ?>">
									<?php echo esc_html( $result->subscription_name ); ?>
								</a>
							</td>
							<td>
								<?php echo esc_html( $result->interval ); ?>
							</td>
							<td>
								<?php
								$price = new Money( $result->price, 'EUR' );
								echo esc_html( $price->format_i18n() );
								?>
							</td>
							<td>
								<?php echo esc_html( $result->name ); ?>
							</td>
							<td>
								<?php echo esc_html( date_i18n( 'D j M Y', $date_start->getTimestamp() ) ); ?>
							</td>
							<td>
								<?php echo esc_html( date_i18n( 'D j M Y', $date_end->getTimestamp() ) ); ?>
							</td>
							<td>
								<?php echo esc_html( $result->cancel_date ); ?>
							</td>
						</tr>

					<?php endforeach; ?>

				</tbody>
			</table>

			<div class="panel-footer">

			</div>
		</div>

	<?php endforeach; ?>
</div>
