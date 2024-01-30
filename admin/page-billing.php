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
$action_url = add_query_arg( array(
	'post_type' => 'orbis_subscription',
	'page'      => 'orbis_twinfield',
	'date'      => $date->format( 'd-m-Y' ),
	'interval'  => $interval,
), admin_url( 'edit.php' ) );

// Subscriptions
$subscriptions = get_subscriptions( $date, $interval );

$companies = array();

foreach ( $subscriptions as $subscription ) {
	$company_id = $subscription->company_id;

	if ( ! isset( $companies[ $company_id ] ) ) {
		$company = new stdClass();

		$company->id            = $subscription->company_id;
		$company->name          = $subscription->company_name;
		$company->post_id       = $subscription->company_post_id;
		$company->subscriptions = array();

		$companies[ $company_id ] = $company;
	}

	$companies[ $company_id ]->subscriptions[] = $subscription;
}

?>
<div class="wrap">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<?php

	$statuses = array(
		'inserted' => __( 'Inserted', 'orbis_twinfield' ),
		'failed'   => __( 'Failed', 'orbis_twinfield' ),
	);

	foreach ( $statuses as $status => $label ) {
		if ( filter_has_var( INPUT_GET, $status ) ) {
			$ids = filter_input( INPUT_GET, $status, FILTER_SANITIZE_STRING );
			$ids = explode( ',', $ids );

			if ( ! empty( $ids ) ) {
				echo '<h3>', esc_html( $label ), '</h3>';

				$subscriptions = new WP_Query( array(
					'post_type'      => 'any',
					'post__in'       => $ids,
					'posts_per_page' => 50,
				) );

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

		$header_texts = array(
			get_post_meta( $company->post_id, '_orbis_invoice_header_text', true ),
		);

		$footer_texts = array(
			get_post_meta( $company->post_id, '_orbis_invoice_footer_text', true ),
		);

		$vies_countries = array(
			'AT' => 'Oostenrijk',
			'BE' => 'België',
			'BG' => 'Bulgarije',
			'CY' => 'Cyprus',
			'CZ' => 'Tsjechië',
			'DE' => 'Duitsland',
			'DK' => 'Denemarken',
			'EE' => 'Estland',
			'EL' => 'Griekenland',
			'ES' => 'Spanje',
			'FI' => 'Finland',
			'FR' => 'Frankrijk',
			'GB' => 'Verenigd Koninkrijk',
			'HR' => 'Kroatië',
			'HU' => 'Hongarije',
			'IE' => 'Ierland',
			'IT' => 'Italy',
			'LT' => 'Litouwen',
			'LU' => 'Luxemburg',
			'LV' => 'Letland',
			'MT' => 'Malta',
			'NL' => 'Nederland',
			'PL' => 'Polen',
			'PT' => 'Portugal',
			'RO' => 'Roemenië',
			'SE' => 'Zweden',
			'SI' => 'Slovenië',
			'SK' => 'Slowakije',
		);

		unset( $vies_countries['NL'] );

		$vat_code = 'VH';

		if ( isset( $vies_countries[ $country ] ) ) {
			$vat_code = 'VHEE'; // or perhaps 'VHV'

			$header_texts[] = 'Btw verlegd.';
		} elseif ( 'NL' !== $country ) {
			$vat_code = 'VHEW';
		}

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

		$footer_texts[] = sprintf(
			__( 'Invoice created by Orbis on %s.', 'orbis_twinfield' ),
			date_i18n( 'D j M Y @ H:i' )
		);

		$sales_invoice = new Pronamic\WordPress\Twinfield\SalesInvoices\SalesInvoice();

		$header = $sales_invoice->get_header();

		$header->set_office( get_option( 'twinfield_default_office_code' ) );
		$header->set_type( get_option( 'twinfield_default_invoice_type' ) );
		$header->set_customer( $twinfield_customer );
		$header->set_status( Pronamic\WordPress\Twinfield\SalesInvoices\SalesInvoiceStatus::STATUS_CONCEPT );
		$header->set_payment_method( Pronamic\WordPress\Twinfield\PaymentMethods::BANK );

		$header_texts = array_filter( $header_texts );
		$header_texts = array_unique( $header_texts );

		$footer_texts = array_filter( $footer_texts );
		$footer_texts = array_unique( $footer_texts );

		$header->set_header_text( implode( "\r\n\r\n", $header_texts ) );
		$header->set_footer_text( implode( "\r\n\r\n", $footer_texts ) );

		$register_invoices = array();

		?>

		<form method="post" action="<?php echo esc_attr( $action_url ); ?>">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">
						<a href="<?php echo esc_attr( get_permalink( $company->post_id ) ); ?>"><?php echo esc_html( $company->name ); ?></a>
					</h3>
				</div>

				<div class="panel-body">
					<dl class="dl-horizontal">
						<dt><?php esc_html_e( 'Customer', 'orbis_twinfield' ); ?></dt>
						<dd><?php echo esc_html( $twinfield_customer ); ?></dd>

						<dt><?php esc_html_e( 'Header', 'orbis_twinfield' ); ?></dt>
						<dd><?php echo nl2br( esc_html( $header->get_header_text() ) ); ?></dd>

						<dt><?php esc_html_e( 'Footer', 'orbis_twinfield' ); ?></dt>
						<dd><?php echo nl2br( esc_html( $header->get_footer_text() ) ); ?></dd>
					</dl>
				</div>

				<!-- Table -->
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'ID', 'orbis_twinfield' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Exclude', 'orbis_twinfield' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Subscription', 'orbis_twinfield' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Price', 'orbis_twinfield' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Name', 'orbis_twinfield' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Free Text 1', 'orbis_twinfield' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Start Date', 'orbis_twinfield' ); ?></th>
							<th scope="col"><?php esc_html_e( 'End Date', 'orbis_twinfield' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Cancel Date', 'orbis_twinfield' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Vat Code', 'orbis_twinfield' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Twinfield', 'orbis_twinfield' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Manual Invoice', 'orbis_twinfield' ); ?></th>
						</tr>
					</thead>

					<tfoot>
						<tr>
							<td>
								<?php

								printf(
									'<input name="company" value="%s" type="hidden" />',
									esc_attr( $company->id )
								);

								?>
							</td>
							<td>
								<?php

								submit_button(
									__( 'Update', 'orbis_twinfield' ),
									'secondary',
									'orbis_twinfield_update',
									false
								);

								?>
							</td>
							<td>

							</td>
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
							<td colspan="5">

							</td>
							<td>
								<?php

								submit_button(
									__( 'Create Invoice', 'orbis_twinfield' ),
									'secondary',
									'orbis_twinfield_create_invoice',
									false
								);

								?>
							</td>
							<td>
								<?php

								submit_button(
									__( 'Register Invoices', 'orbis_twinfield' ),
									'secondary',
									'orbis_twinfield_register_invoices',
									false
								);

								?>
							</td>
						</tr>
					</tfoot>

					<tbody>

						<?php foreach ( $company->subscriptions as $i => $result ) : ?>

							<?php

							$name = 'subscriptions[%s][%s]';

							$exclude = false;

							// phpcs:disable
							$post_subscriptions = $_POST['subscriptions'];
							// phpcs:enable
							// CSRF, sanitization, input var, input validation ok.

							if ( isset( $post_subscriptions, $post_subscriptions[ $result->post_id ], $post_subscriptions[ $result->post_id ]['exclude'] ) ) {
								$exclude = true;
							}

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

							$twinfield_article_code    = get_post_meta( $result->product_post_id, '_twinfield_article_code', true );
							$twinfield_subarticle_code = get_post_meta( $result->product_post_id, '_twinfield_subarticle_code', true );

							$line = null;

							if ( ! $exclude ) {
								$line = $sales_invoice->new_line();
								$line->set_article( $twinfield_article_code );
								$line->set_subarticle( $twinfield_subarticle_code );
								$line->set_vat_code( $vat_code );
								$line->set_units_price_excl( (float) $result->price );

								$free_text_1 = get_post_meta( $result->post_id, '_orbis_invoice_line_description', true );

								if ( empty( $free_text_1 ) ) {
									$free_text_1 = $result->name;
								}

								if ( strlen( $free_text_1 ) > 36 ) {
									// opmerkingen mag maximaal 36 tekens bevatten wanneer het een vrije tekst betreft.
									$free_text_1 = substr( $free_text_1, 0, 35 ) . '…';
								}
								$line->set_free_text_1( $free_text_1 );

								$line->set_free_text_2( sprintf(
									'%s - %s',
									date_i18n( 'D j M Y', $date_start->getTimestamp() ),
									date_i18n( 'D j M Y', $date_end->getTimestamp() )
								) );
								$line->set_free_text_3( $result->id );

								if ( 'VHEE' === $vat_code ) {
									$line->set_performance_type( Pronamic\WordPress\Twinfield\PerformanceTypes::SERVICES );
									$line->set_performance_date( $date_start );
								}

								$register_invoices[] = (object) array(
									'post_id'    => $result->post_id,
									'start_date' => $date_start,
									'end_date'   => $date_end,
								);
							}

							?>
							<tr>
								<td>
									<?php echo esc_html( $result->id ); ?>
								</td>
								<td>
									<input name="<?php echo esc_attr( sprintf( $name, $result->post_id, 'exclude' ) ); ?>" value="1" type="checkbox" <?php checked( $exclude ); ?> />
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
									<?php 

									if ( null !== $line ) {
										echo esc_html( $line->get_free_text_1() );
									}

									?>
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
								<td>
									<code><?php echo esc_html( $vat_code ); ?></code>
								</td>
								<td>
									<?php

									if ( ! empty( $twinfield_article_code ) ) {
										printf(
											'<strong>%s</strong>: %s',
											esc_html__( 'Article', 'orbis_twinfield' ),
											esc_html( $twinfield_article_code )
										);
									}

									echo '<br />';

									if ( ! empty( $twinfield_subarticle_code ) ) {
										printf(
											'<strong>%s</strong>: %s',
											esc_html__( 'Subarticle', 'orbis_twinfield' ),
											esc_html( $twinfield_subarticle_code )
										);
									}

									?>
								</td>
								<td>
									<?php

									$name = 'subscriptions[%d][%s]';

									?>
									<input name="<?php echo esc_attr( sprintf( $name, $result->post_id, 'id' ) ); ?>" value="<?php echo esc_attr( $result->id ); ?>" type="hidden" />
									<input name="<?php echo esc_attr( sprintf( $name, $result->post_id, 'post_id' ) ); ?>" value="<?php echo esc_attr( $result->post_id ); ?>" type="hidden" />
									<input name="<?php echo esc_attr( sprintf( $name, $result->post_id, 'invoice_number' ) ); ?>" value="" type="text" />
									<input name="<?php echo esc_attr( sprintf( $name, $result->post_id, 'date_start' ) ); ?>" value="<?php echo esc_attr( $date_start->format( DATE_W3C ) ); ?>" type="hidden" />
									<input name="<?php echo esc_attr( sprintf( $name, $result->post_id, 'date_end' ) ); ?>" value="<?php echo esc_attr( $date_end->format( DATE_W3C ) ); ?>" type="hidden" />
								</td>
							</tr>

						<?php endforeach; ?>

					</tbody>
				</table>

				<div class="panel-footer">
					<?php

					if ( filter_has_var( INPUT_POST, 'orbis_twinfield_create_invoice' ) ) {
						$posted_company = filter_input( INPUT_POST, 'company', FILTER_SANITIZE_STRING );

						if ( $company->id === $posted_company ) {
							$twinfield_client = \apply_filters( 'pronamic_twinfield_client', null );

							$organisation = $twinfield_client->get_organisation();

							$office = $organisation->office( '66470' );

							$xml_processor = $twinfield_client->get_xml_processor();

							$xml_processor->set_office( $office );

							$service = new Pronamic\WordPress\Twinfield\SalesInvoices\SalesInvoiceService( $xml_processor );

							try {
								$sales_invoice = $service->insert_sales_invoice( $sales_invoice );

								$number = $sales_invoice->get_header()->get_number();

								foreach ( $register_invoices as $object ) {
									$subscription = new Orbis_Subscription( $object->post_id );

									$result = $subscription->register_invoice( $number, $object->start_date, $object->end_date );
								}

								esc_html_e( 'Twinfield invoice created.', 'orbis_twinfield' );
							} catch ( \Pronamic\WordPress\Twinfield\XML\XmlPostErrors $errors ) {
								$xml = $errors->get_simplexml();
								$xsl = simplexml_load_file( __DIR__ . '/../admin/twinfield-salesinvoices.xsl' );

								$proc = new XSLTProcessor();
								$proc->importStyleSheet( $xsl );

								echo $proc->transformToXML( $xml ); // WPCS: xss ok
							} catch ( \Exception $exception ) {
								\wp_die( $exception->getMessage() );
							}
						}
					}

					?>
				</div>
			</div>
		</form>

	<?php endforeach; ?>
</div>
