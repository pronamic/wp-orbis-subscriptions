<?php

/**
 * File for generate functions for subscription usages
 * 
 * @author Leon Rowland <leon@rowland.nl>
 */

if ( ! defined( 'orbis_subscription_get_data' ) ) :
	
	function orbis_subscription_get_data( $subscription_id ) {
		global $wpdb;
		
		$query = "
			SELECT
				s.id,
				s.company_id,
				s.post_id,
				s.name,
				s.activation_date,
				s.expiration_date,
				s.cancel_date,
				s.update_date,
				s.license_key,
				s.license_key_md5
			FROM
				orbis_subscriptions as s
			WHERE
				s.post_id = %d
			ORDER BY
				s.id,
				s.update_date
		";
		
		return $wpdb->get_row( $wpdb->prepare( $query, $subscription_id ) );
	}
	
endif;
	