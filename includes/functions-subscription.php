<?php

/**
 * File for generate functions for subscription usages
 * 
 * @author Leon Rowland <leon@rowland.nl>
 */

if ( ! function_exists( 'orbis_subscription_get_data' ) ) :
	
	/**
	 * Returns a row from the orbis_subscriptions table 
	 * where the post_id matches the parameter
	 * 
	 * @global type $wpdb
	 * 
	 * @param int $post_id
	 * @return object
	 */
	function orbis_subscription_get_data( $post_id ) {
		global $wpdb;
		
		$query = "
			SELECT
				s.id,
				s.post_id,
				s.name,
				s.email,
				s.activation_date,
				s.expiration_date,
				s.cancel_date,
				s.update_date,
				s.license_key,
				s.license_key_md5,
				c.id as company_id,
				c.name as company_name,
				c.e_mail as company_email,
				t.id as type_id,
				t.name as type_name,
				t.price as type_price,
				d.domain_name as domain_name
			FROM
				orbis_subscriptions as s
			LEFT JOIN
				orbis_companies as c
				ON s.company_id = c.id
			LEFT JOIN
				orbis_subscription_types as t
				ON s.type_id = t.id
			LEFT JOIN
				orbis_domain_names as d
				ON s.domain_name_id = d.id
			WHERE
				s.post_id = %d
			ORDER BY
				s.id,
				s.update_date
		";
		
		return $wpdb->get_row( $wpdb->prepare( $query, $post_id ) );
	}
	
endif;


if ( ! function_exists( 'orbis_date2mysql' ) ) :

	function orbis_date2mysql( DateTime $date = null ) {
		$result = null;

		if ( $date !== null ) {
			$result = $date->format( 'Y-m-d H:i:s' );
		}

		return $result;
	}

endif;
	