<?php

/**
 * Subscriptions to invoice shortcode
 * 
 * @param unknown $atts
 * @return string
 */
function orbis_shortcode_subscriptions_to_invoice( $atts ) {
	global $wpdb;
	global $orbis_subscriptions_plugin;

	$query = '
		SELECT
			c.name AS company_name,
			s.id,
			s.type_id,
			st.name AS subscription_name,
			st.price,
			st.twinfield_article,
			s.name,
			s.activation_date,
			DAYOFYEAR( s.activation_date ) AS activation_dayofyear,
			si.invoice_number,
			si.start_date,
			DAYOFYEAR( s.activation_date ) < DAYOFYEAR( NOW() ) AS to_late
		FROM
			orbis_subscriptions AS s
				LEFT JOIN
			orbis_companies AS c
					ON s.company_id = c.id
				LEFT JOIN
			orbis_subscription_types AS st
					ON s.type_id = st.id
				LEFT JOIN
			orbis_subscriptions_invoices AS si
					ON
						s.id = si.subscription_id
							AND
						YEAR( si.start_date ) = YEAR( NOW() )
		WHERE 
			cancel_date IS NULL
				AND
			MONTH( s.activation_date ) < ( MONTH( NOW() ) + 2 )
				AND 
					s.type_id NOT IN ( 11, 12 )
		ORDER BY
			DAYOFYEAR( s.activation_date )
		;
	';

	global $orbis_subscriptions_to_invoice;

	$orbis_subscriptions_to_invoice = $wpdb->get_results( $query );

	$return  = '';

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
 * @param unknown $atts
 * @return string
 */
function orbis_shortcode_subscriptions_to_invoice_updater( $atts ) {
	global $wpdb;
	global $orbis_subscriptions_plugin;
	
	$return  = '';

	if ( is_user_logged_in() ) {
		$query = '
			SELECT
				c.name AS company_name,
				s.id,
				s.type_id,
				s.post_id,
				st.name AS subscription_name,
				st.price,
				st.twinfield_article,
				s.name,
				s.activation_date,
				DAYOFYEAR( s.activation_date ) AS activation_dayofyear,
				si.invoice_number,
				si.start_date,
				DAYOFYEAR( s.activation_date ) < DAYOFYEAR( NOW() ) AS to_late
			FROM
				orbis_subscriptions AS s
					LEFT JOIN
				orbis_companies AS c
						ON s.company_id = c.id
					LEFT JOIN
				orbis_subscription_types AS st
						ON s.type_id = st.id
					LEFT JOIN
				orbis_subscriptions_invoices AS si
						ON
							s.id = si.subscription_id
								AND
							YEAR( si.start_date ) = YEAR( NOW() )
			WHERE
				cancel_date IS NULL
					AND
				invoice_number IS NULL
					AND
				MONTH( s.activation_date ) < ( MONTH( NOW() ) + 2 )
					AND
						s.type_id NOT IN ( 11, 12 )
			ORDER BY
				DAYOFYEAR( s.activation_date )
			;
		';
	
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
 * @param unknown $atts
 * @return string
 */
function orbis_shortcode_subscriptions_invoices( $atts ) {
	global $wpdb;
	global $orbis_subscriptions_plugin;
	
	$return  = '';

	$query = '
		SELECT
			s.id,
			s.type_id,
			s.post_id,
			st.name AS subscription_name,
			st.price,
			s.name,
			si.invoice_number,
			si.start_date,
			si.end_date,
			si.user_id,
			user.display_name AS user_display_name,
			si.create_date,
			c.name AS company_name
		FROM
			orbis_subscriptions_invoices AS si
				LEFT JOIN
			orbis_subscriptions AS s
					ON si.subscription_id = s.id
				LEFT JOIN
			orbis_subscription_types AS st
					ON s.type_id = st.id
				LEFT JOIN
			orbis_companies AS c
					ON s.company_id = c.id
				LEFT JOIN
			wp_users AS user
					ON user.ID = si.user_id
		ORDER BY
			si.create_date DESC
		LIMIT
			0, 100
		;
	';

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
			$updated = array();
			$failed  = array();
	
			foreach ( $subscriptions as $subscription ) {
				$id             = $subscription['id'];
				$post_id        = $subscription['post_id'];
				$invoice_number = $subscription['invoice_number'];
				$date_start     = $subscription['date_start'];
				$date_end       = $subscription['date_end'];
	
				if ( ! empty( $invoice_number ) ) {
					$result = $wpdb->insert(
						'orbis_subscriptions_invoices',
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
