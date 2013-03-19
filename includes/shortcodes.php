<?php

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
