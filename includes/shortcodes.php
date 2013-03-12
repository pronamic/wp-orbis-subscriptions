<?php

function orbis_shortcode_subscriptions_to_invoice( $atts ) {
	global $wpdb;

	$query = '
		SELECT
			c.name AS company_name,
			s.id,
			s.type_id,
			st.name AS subscription_name,
			st.price,
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

	$results = $wpdb->get_results( $query );

	$return  = '';

	$return .= '<div class="panel">';
	$return .= '<table class="table table-striped table-bordered">';
	$return .= '<thead>';
	$return .= '<tr>';
	$return .= '<th scope="col">ID</th>';
	$return .= '<th scope="col">Company</th>';
	$return .= '<th scope="col">Subscription</th>';
	$return .= '<th scope="col">Price</th>';
	$return .= '<th scope="col">Name</th>';
	$return .= '<th scope="col">Activation Date</th>';
	$return .= '<th scope="col">Invoice Number</th>';
	$return .= '<th scope="col">Notice</th>';
	$return .= '</tr>';
	$return .= '</thead>';
	$return .= '<tbody>';
	
	foreach ( $results as $result ) {
		$return .= '<tr>';
		$return .= '<td>' . $result->id . '</td>';
		$return .= '<td>' . $result->company_name . '</td>';
		$return .= '<td>' . $result->subscription_name . '</td>';
		$return .= '<td>' . $result->price . '</td>';
		$return .= '<td>' . $result->name . '</td>';
		$return .= '<td>' . $result->activation_date . '</td>';
		$return .= '<td>' . $result->invoice_number . '</td>';
		$return .= '<td>';
		
		if ( $result->to_late ) {
			$return.= '<span class="text-error">!!!</span>';
		}
		
		$return .= '</td>';
		$return .= '</tr>';
	}
	
	$return .= '</tbody>';
	$return .= '</table>';
	$return .= '</div>';
	
	return $return;
}

add_shortcode( 'orbis_subscriptions_to_invoice', 'orbis_shortcode_subscriptions_to_invoice' );
