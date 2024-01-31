<?php

use Pronamic\WordPress\Money\Money;

function get_subscriptions( $date, $interval ) {
	global $wpdb;
	global $orbis_subscriptions_plugin;

	// Query
	$day_function    = '';
	$join_condition  = 'subscription.id = invoice.subscription_id';
	$where_condition = '1 = 1';

	switch ( $interval ) {
		case 'M_OLD':
			$last_day_month = clone $date;
			$last_day_month->modify( 'last day of this month' );

			$day_function = 'DAYOFMONTH';

			$join_condition  .= $wpdb->prepare( ' AND ( YEAR( invoice.start_date ) = %d AND MONTH( invoice.start_date ) = %d )', $date->format( 'Y' ), $date->format( 'n' ) );
			$where_condition .= $wpdb->prepare( ' AND subscription.activation_date <= %s', $last_day_month->format( 'Y-m-d' ) );

			break;
		case 'Q':
			$last_day_month = clone $date;
			$last_day_month->modify( 'last day of this quarter' );

			$day_function = 'DAYOFYEAR';

			$join_condition  .= $wpdb->prepare( ' AND ( YEAR( invoice.start_date ) = %d AND MONTH( invoice.start_date ) = %d )', $date->format( 'Y' ), $date->format( 'n' ) );
			$where_condition .= $wpdb->prepare( ' AND subscription.activation_date <= %s', $last_day_month->format( 'Y-m-d' ) );

			break;
		case 'M':
		case 'Y':
		case '2Y':
		case '3Y':
		default:
			$last_day_month = clone $date;
			$last_day_month->modify( 'last day of this month' );

			$ahead_limit = new DateTime( '+1 month' );

			$day_function = 'DAYOFYEAR';

			// Check if the end date of invoice is in next year.
			//$join_condition  .= $wpdb->prepare( ' AND YEAR( invoice.end_date ) = %d', $date->format( 'Y' ) + 1 );
			/*
			$where_condition .= ' AND ( ';
			$where_condition .= $wpdb->prepare( ' DATE( subscription.activation_date ) <= %s', $last_day_month->format( 'Y-m-d' ) );
			$where_condition .= $wpdb->prepare( ' AND DATE_FORMAT( subscription.activation_date, %s ) <= %s', $date->format( 'Y' ) . '-%m-%d', $ahead_limit->format( '
				Y-m-d' ) );
			$where_condition .= ' AND invoice_number IS NULL';
			$where_condition .= ' ) OR ( ';
			*/
			$where_condition .= ' AND ( ';
			$where_condition .= ' ( subscription.billed_to IS NULL OR subscription.billed_to < DATE_ADD( CURDATE(), INTERVAL 14 DAY ) )';
			$where_condition .= ' AND ( subscription.cancel_date IS NULL OR subscription.cancel_date > DATE_SUB( subscription.expiration_date, INTERVAL 14 DAY ) )';
			$where_condition .= $wpdb->prepare( ' AND ( subscription.cancel_date IS NULL OR subscription.cancel_date > %s )', '2014-01-01' );
			$where_condition .= ' AND ( subscription.end_date IS NULL OR subscription.end_date > subscription.expiration_date )';
			$where_condition .= ' ) ';

			break;
	}

	$interval_condition = $wpdb->prepare( 'product.interval = %s', $interval );

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
			subscription.type_id,
			subscription.post_id,
			subscription.name,
			subscription.activation_date,
			subscription.expiration_date,
			subscription.cancel_date,
			subscription.billed_to,
			DAYOFYEAR( subscription.activation_date ) AS activation_dayofyear,
			invoice.invoice_number,
			invoice.start_date,
			(
				invoice.id IS NULL
					AND
				$day_function( subscription.activation_date ) < $day_function( NOW() )
			) AS too_late
		FROM
			$wpdb->orbis_subscriptions AS subscription
				LEFT JOIN
			$wpdb->orbis_companies AS company
					ON subscription.company_id = company.id
				LEFT JOIN
			$wpdb->orbis_subscription_products AS product
					ON subscription.type_id = product.id
				LEFT JOIN
			$wpdb->orbis_subscriptions_invoices AS invoice
					ON ( $join_condition )
		WHERE
			product.auto_renew
				AND
			$interval_condition
				AND
			$where_condition
		GROUP BY
			subscription.id
		ORDER BY
			DAYOFYEAR( subscription.activation_date )
		;";

	$subscriptions = $wpdb->get_results( $query ); //unprepared SQL

	return $subscriptions;
}

// Date
$date = new DateTimeImmutable( 'first day of this month' );

// Interval
$interval = 'Y';

// Action URL
$action_url = add_query_arg(
	[
		'post_type' => 'orbis_subscription',
		'page'      => 'orbis_twinfield',
		'date'      => $date->format( 'd-m-Y' ),
		'interval'  => $interval,
	],
	admin_url( 'edit.php' ) 
);

// Subscriptions
$subscriptions = get_subscriptions( $date, $interval );

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
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<?php

	$statuses = [
		'inserted' => __( 'Inserted', 'orbis_twinfield' ),
		'failed'   => __( 'Failed', 'orbis_twinfield' ),
	];

	foreach ( $statuses as $status => $label ) {
		if ( filter_has_var( INPUT_GET, $status ) ) {
			$ids = filter_input( INPUT_GET, $status, FILTER_SANITIZE_STRING );
			$ids = explode( ',', $ids );

			if ( ! empty( $ids ) ) {
				echo '<h3>', esc_html( $label ), '</h3>';

				$subscriptions = new WP_Query(
					[
						'post_type'      => 'any',
						'post__in'       => $ids,
						'posts_per_page' => 50,
					] 
				);

				$subscriptions = $subscriptions->posts;

				if ( ! empty( $subscriptions ) ) {
					echo '<ul>';

					foreach ( $subscriptions as $subscription ) {
						echo '<li>';
						printf(
							'<a href="%s" target="_blank">%s</a>',
							esc_attr( get_permalink( $subscription->ID ) ),
							esc_html( get_the_title( $subscription->ID ) )
						);
						echo '</li>';
					}

					echo '</ul>';
				}
			}
		}
	}

	?>

	<ul class="subsubsub">
		<li>
			<?php echo esc_html( date_i18n( 'M Y', $date->getTimestamp() ) ); ?> |
		</li>
		<li>
			<a href="<?php echo esc_attr( remove_query_arg( 'date' ) ); ?>" class="btn btn-default">
				<?php esc_html_e( 'This month', 'orbis_twinfield' ); ?>
			</a>
		</li>
	</ul>

	<form method="get">
		<div class="tablenav top">
			<div class="alignleft actions bulkactions">
				<select name="interval">
					<option value="-1" selected="selected"><?php esc_html_e( 'Interval', 'orbis_twinfield' ); ?></option>
					<option value="Y" <?php selected( $interval, 'Y' ); ?>><?php esc_html_e( 'Yearly', 'orbis_twinfield' ); ?></option>
					<option value="2Y" <?php selected( $interval, '2Y' ); ?>><?php esc_html_e( 'Two Yearly', 'orbis_twinfield' ); ?></option>
					<option value="3Y" <?php selected( $interval, '3Y' ); ?>><?php esc_html_e( 'Three Yearly', 'orbis_twinfield' ); ?></option>
					<option value="Q" <?php selected( $interval, 'Q' ); ?>><?php esc_html_e( 'Quarterly', 'orbis_twinfield' ); ?></option>
					<option value="M" <?php selected( $interval, 'M' ); ?>><?php esc_html_e( 'Monthly', 'orbis_twinfield' ); ?></option>
				</select>

				<input type="hidden" name="post_type" value="orbis_subscription" />
				<input type="hidden" name="page" value="orbis_twinfield" />

				<input type="submit" class="button action" name="action" value="<?php esc_attr_e( 'Execute', 'orbis_twinfield' ); ?>" />
			</div>

			<div class="tablenav-pages">
				<span class="pagination-links">
					<?php

					$date_prev = clone $date;
					$date_prev->modify( '-1 month' );

					$link_prev = add_query_arg( 'date', $date_prev->format( 'd-m-Y' ) );

					$date_next = clone $date;
					$date_next->modify( '+1 month' );

					$link_next = add_query_arg( 'date', $date_next->format( 'd-m-Y' ) );

					?>
					<a class="prev-page" href="<?php echo esc_attr( $link_prev ); ?>">
						<span class="screen-reader-text">Vorige pagina</span><span aria-hidden="true">‹</span>
					</a>

					<a class="next-page" href="<?php echo esc_attr( $link_next ); ?>">
						<span class="screen-reader-text">Volgende pagina</span><span aria-hidden="true">›</span>
					</a>
				</span>

			</div>
		</div>
	</form>

	<?php foreach ( $companies as $company ) : ?>

		<?php

		$twinfield_customer = get_post_meta( $company->post_id, '_twinfield_customer_id', true );
		$country            = get_post_meta( $company->post_id, '_orbis_country', true );

		$header_texts = [
			get_post_meta( $company->post_id, '_orbis_invoice_header_text', true ),
		];

		$footer_texts = [
			get_post_meta( $company->post_id, '_orbis_invoice_footer_text', true ),
		];

		$terms = wp_get_post_terms( $company->post_id, 'orbis_payment_method' );

		$payment_method_term = array_shift( $terms );

		foreach ( $company->subscriptions as $i => $subscription ) {
			$terms = wp_get_post_terms( $subscription->post_id, 'orbis_payment_method' );

			$term = array_shift( $terms );

			if ( is_object( $term ) ) {
				$payment_method_term = $term;
			}

			$header_texts[] = get_post_meta( $subscription->post_id, '_orbis_invoice_header_text', true );
			$footer_texts[] = get_post_meta( $subscription->post_id, '_orbis_invoice_footer_text', true );
		}

		if ( is_object( $payment_method_term ) ) {
			$header_texts[] = $payment_method_term->description;
		}

		$header_texts[] = get_option( 'orbis_invoice_header_text' );
		$footer_texts[] = get_option( 'orbis_invoice_footer_text' );

		$header_texts = array_filter( $header_texts );
		$header_texts = array_unique( $header_texts );

		$footer_texts = array_filter( $footer_texts );
		$footer_texts = array_unique( $footer_texts );

		?>

		<form method="post" action="<?php echo esc_attr( $action_url ); ?>">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">
						<a href="<?php echo esc_attr( get_permalink( $company->post_id ) ); ?>"><?php echo esc_html( $company->name ); ?></a>
					</h3>
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
							<th scope="col"><?php esc_html_e( 'ID', 'orbis_twinfield' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Subscription', 'orbis_twinfield' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Price', 'orbis_twinfield' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Name', 'orbis_twinfield' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Start Date', 'orbis_twinfield' ); ?></th>
							<th scope="col"><?php esc_html_e( 'End Date', 'orbis_twinfield' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Cancel Date', 'orbis_twinfield' ); ?></th>
						</tr>
					</thead>

					<tfoot>
						<tr>
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

							$date_start = new DateTime( empty( $result->billed_to ) ? $result->activation_date : $result->billed_to );
							$date_end   = clone $date_start;

							$day   = $date_start->format( 'd' );
							$month = $date_start->format( 'n' );

							switch ( $result->interval ) {
								// Month
								case 'M':
									$date_end = clone $date_start;
									$date_end->modify( '+1 month' );

									break;
								// Quarter
								case 'Q':
									$date_end = new DateTime( $result->expiration_date );
									$date_end->modify( '+3 month' );

									break;
								// Year
								case '2Y':
									$date_end = clone $date_start;
									$date_end->modify( '+2 year' );

									break;
								case '3Y':
									$date_end = clone $date_start;
									$date_end->modify( '+3 year' );

									break;
								case 'Y':
								default:
									$date_end = clone $date_start;
									$date_end->modify( '+1 year' );

									break;
							}

							?>
							<tr>
								<td>
									<?php echo esc_html( $result->id ); ?>
								</td>
								<td>
									<a href="<?php echo esc_attr( get_permalink( $result->post_id ) ); ?>">
										<?php echo esc_html( $result->subscription_name ); ?>
									</a>
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
		</form>

	<?php endforeach; ?>
</div>
