<?php

/**
 * Subscriptions to invoice shortcode
 * 
 * @param mixed $atts
 * @return string
 */
function orbis_shortcode_subscriptions_to_invoice( $atts ) {
	global $wpdb;
	global $orbis_subscriptions_plugin;

    $date = strtotime( filter_input( INPUT_GET, 'date', FILTER_SANITIZE_STRING ) );
	if ( false === $date ) {
		$date = time();
	}

	// Interval
	$interval = filter_input( INPUT_GET, 'interval', FILTER_SANITIZE_STRING );
	$interval = empty( $interval ) ? 'Y' : $interval;

	// Query
	switch ( $interval ) {
		case 'M':
			$day_function = 'DAYOFMONTH';
			$join_condition = $wpdb->prepare( '( YEAR( invoice.start_date ) = %d AND MONTH( invoice.start_date ) = %d )', date( 'Y', $date ), date( 'n', $date ) );
			$where_condition = $wpdb->prepare( '
				(
					YEAR( subscription.activation_date ) <= %d
						AND
					MONTH( subscription.activation_date ) <= %d
				)',
				date( 'Y', $date ),
				date( 'n', $date )
			);

			break;
		case 'Y':
		default:
			$day_function = 'DAYOFYEAR';
			$join_condition = $wpdb->prepare( 'YEAR( invoice.start_date ) = %d', date( 'Y', $date ) );
			$where_condition = $wpdb->prepare( '
				(
					YEAR( subscription.activation_date ) <= %d
						AND 
					MONTH( subscription.activation_date ) < ( MONTH( NOW() ) + 2 )
				)',
				date( 'Y', $date )
			);

			break;
	}

	$query = $wpdb->prepare(
		"
			SELECT
				company.name AS company_name,
				product.name AS subscription_name,
				product.price,
				product.twinfield_article,
				product.interval,
				subscription.id,
				subscription.type_id,
				subscription.post_id,
				subscription.name,
				subscription.activation_date,
				DAYOFYEAR( subscription.activation_date ) AS activation_dayofyear,
				invoice.invoice_number,
				invoice.start_date AS invoice_start_date,
				invoice.end_date AS invoice_end_date,
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
						ON
							subscription.id = invoice.subscription_id
								AND
							$join_condition
			WHERE
				cancel_date IS NULL
					AND
				product.auto_renew
					AND
				product.interval = %s
					AND
				$where_condition
			ORDER BY
				DAYOFYEAR( subscription.activation_date )
			;
		",
		$interval
	);

	global $orbis_subscriptions_to_invoice;

	$orbis_subscriptions_to_invoice = $wpdb->get_results( $query );

	ob_start();
	
	$orbis_subscriptions_plugin->plugin_include( 'templates/subscriptions-to-invoice.php' );
	
	$return = ob_get_contents();
	
	ob_end_clean();
	
	return $return;
}

add_shortcode( 'orbis_subscriptions_to_invoice', 'orbis_shortcode_subscriptions_to_invoice' );

/**
 * Subscriptions to invoice updater
 * 
 * @param mixed $atts
 * @return string
 */
function orbis_shortcode_subscriptions_to_invoice_updater( $atts ) {
	global $wpdb;
	global $orbis_subscriptions_plugin;

    $date = strtotime( filter_input( INPUT_GET, 'date', FILTER_SANITIZE_STRING ) );
	if ( false === $date ) {
		$date = time();
	}

	// Interval
	$interval = filter_input( INPUT_GET, 'interval', FILTER_SANITIZE_STRING );
	$interval = empty( $interval ) ? 'Y' : $interval;

	if ( is_user_logged_in() ) {
		switch ( $interval ) {
			case 'M':
				$day_function = 'DAYOFMONTH';
				$join_condition = $wpdb->prepare( '( YEAR( invoice.start_date ) = %d AND MONTH( invoice.start_date ) = %d )', date( 'Y', $date ), date( 'n', $date ) );
				$where_condition = $wpdb->prepare( '
					(
						YEAR( subscription.activation_date ) <= %d
							AND
						MONTH( subscription.activation_date ) <= %d
					)',
					date( 'Y', $date ),
					date( 'n', $date )
				);

				break;
			case 'Y':
			default:
				$day_function = 'DAYOFYEAR';
				$join_condition = $wpdb->prepare( 'YEAR( invoice.start_date ) = %d', date( 'Y', $date ) );
				$where_condition = $wpdb->prepare( '
					(
						YEAR( subscription.activation_date ) <= %d
							AND 
						MONTH( subscription.activation_date ) < ( MONTH( NOW() ) + 2 )
					)',
					date( 'Y', $date )
				);

				break;
		}

		$query = $wpdb->prepare(
			"
				SELECT
					company.name AS company_name,
					product.name AS subscription_name,
					product.price,
					product.twinfield_article,
					product.interval,
					subscription.id,
					subscription.type_id,
					subscription.post_id,
					subscription.name,
					subscription.activation_date,
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
							ON
								subscription.id = invoice.subscription_id
									AND
								$join_condition
				WHERE
					cancel_date IS NULL
						AND
					invoice_number IS NULL
						AND
					product.auto_renew
						AND
					product.interval = %s
						AND
					$where_condition
				ORDER BY
					DAYOFYEAR( subscription.activation_date )
				;
			",
			$interval
		);

		global $orbis_subscriptions_to_invoice;
	
		$orbis_subscriptions_to_invoice = $wpdb->get_results( $query );
	
		ob_start();
	
		$orbis_subscriptions_plugin->plugin_include( 'templates/subscriptions-to-invoice-updater.php' );
	
		$return = ob_get_contents();
	
		ob_end_clean();
	} else {
		$return = __( 'Login required', 'orbis_subscriptions' );
	}

	return $return;
}

add_shortcode( 'orbis_subscriptions_to_invoice_updater', 'orbis_shortcode_subscriptions_to_invoice_updater' );

/**
 * Subscriptions to invoice updater
 * 
 * @param mixed $atts
 * @return string
 */
function orbis_shortcode_subscriptions_invoices( $atts ) {
	global $wpdb;
	global $orbis_subscriptions_plugin;

	$query = "
		SELECT
			s.id,
			s.type_id,
			s.post_id,
			st.name AS subscription_name,
			st.price,
			s.name,
			si.id AS sid,
			si.invoice_number,
			si.start_date,
			si.end_date,
			si.user_id,
			user.display_name AS user_display_name,
			si.create_date,
			c.name AS company_name
		FROM
			$wpdb->orbis_subscriptions_invoices AS si
				LEFT JOIN
			$wpdb->orbis_subscriptions AS s
					ON si.subscription_id = s.id
				LEFT JOIN
			$wpdb->orbis_subscription_products AS st
					ON s.type_id = st.id
				LEFT JOIN
			$wpdb->orbis_companies AS c
					ON s.company_id = c.id
				LEFT JOIN
			$wpdb->users AS user
					ON user.ID = si.user_id
		ORDER BY
			si.create_date DESC
		LIMIT
			0, 100
		;
	";

	global $orbis_subscriptions_invoices;

	$orbis_subscriptions_invoices = $wpdb->get_results( $query );

	ob_start();

	$orbis_subscriptions_plugin->plugin_include( 'templates/subscriptions-invoices.php' );

	$return = ob_get_contents();

	ob_end_clean();

	return $return;
}

add_shortcode( 'orbis_subscriptions_invoices', 'orbis_shortcode_subscriptions_invoices' );

function orbis_subscriptions_to_invoice_updater() {
	global $wpdb;

	if ( isset( $_POST['subscriptions_invoices_update'] ) && is_user_logged_in() ) {
		$subscriptions = $_POST['subscriptions'];
	
		if ( ! empty( $subscriptions ) ) {
			$failed  = array();
	
			foreach ( $subscriptions as $subscription ) {
				$id             = $subscription['id'];
				$post_id        = $subscription['post_id'];
				$invoice_number = $subscription['invoice_number'];
				$date_start     = $subscription['date_start'];
				$date_end       = $subscription['date_end'];
	
				if ( ! empty( $invoice_number ) ) {
					$result = $wpdb->insert(
						$wpdb->orbis_subscriptions_invoices,
						array(
							'subscription_id' => $id,
							'invoice_number'  => $invoice_number,
							'start_date'      => $date_start,
							'end_date'        => $date_end,
							'user_id'         => get_current_user_id(),
							'create_date'     => date( 'Y-m-d H:i:s' )
						),
						array(
							'%d',
							'%s',
							'%s',
							'%s',
							'%d',
							'%s'
						)
					);
	
					if ( $result === false ) {
						$failed[]   = $post_id;
					} else {
						$inserted[] = $post_id;
					}
				}
			}
			
			$url = add_query_arg( array(
				'inserted' => empty( $inserted ) ? false : implode( $inserted, ',' ),
				'failed'   => empty( $failed ) ? false : implode( $failed, ',' ),
			) );

			wp_redirect( $url );
			
			exit;
		}
	}
}

add_action( 'init', 'orbis_subscriptions_to_invoice_updater' );
