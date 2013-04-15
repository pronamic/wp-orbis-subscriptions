<?php

class Orbis_Subscriptions_Expiration_Factory {
	private $db;
	
	public function __construct() {
		global $wpdb;
		$this->db = $wpdb;
	}
	
	/**
	 * Returns an array of results, that get all subscriptions
	 * 
	 * Response example:
	 * @todo
	 * 
	 * @access public
	 * @return array
	 */
	public function get_all() {
		$query = '
			SELECT
				subscription.id ,
				subscription.post_id AS postId ,
				subscription.name AS subscriptionName ,
				subscription.activation_date AS activationDate ,
				subscription.expiration_date AS expirationDate ,
				subscription.cancel_date AS cancelDate ,
				subscription.update_date AS updateDate ,
				subscription.license_key AS licenseKey ,
				company.id AS companyId ,
				company.name AS companyName ,
				company.e_mail AS companyEMail ,
				type.name AS typeName ,
				type.price AS price ,
				domain_name.domain_name AS domainName
			FROM
				orbis_subscriptions AS subscription
			LEFT JOIN
				orbis_companies AS company
				ON subscription.company_id = company.id
			LEFT JOIN
				orbis_subscription_types AS type
				ON subscription.type_id = type.id
			LEFT JOIN
				orbis_domain_names AS domain_name
				ON subscription.domain_name_id = domain_name.id
			WHERE
				type_id IN (11, 12, 20)
			ORDER BY
				subscription.update_date ,
				subscription.id
		';
		
		return $this->db->get_results( $query );
	}
	
	public function get_past_date( DateTime $date ) {
		$query = '
			SELECT
				subscription.id ,
				subscription.post_id AS postId ,
				subscription.name AS subscriptionName ,
				subscription.activation_date AS activationDate ,
				subscription.expiration_date AS expirationDate ,
				subscription.cancel_date AS cancelDate ,
				subscription.update_date AS updateDate ,
				subscription.license_key AS licenseKey ,
				company.id AS companyId ,
				company.name AS companyName ,
				company.e_mail AS companyEMail ,
				type.name AS typeName ,
				type.price AS price ,
				domain_name.domain_name AS domainName
			FROM
				orbis_subscriptions AS subscription
			LEFT JOIN
				orbis_companies AS company
				ON subscription.company_id = company.id
			LEFT JOIN
				orbis_subscription_types AS type
				ON subscription.type_id = type.id
			LEFT JOIN
				orbis_domain_names AS domain_name
				ON subscription.domain_name_id = domain_name.id
			WHERE
				type_id IN (11, 12, 20)
			AND
			WHERE
				expirationDate BETWEEN NOW() AND %s
			ORDER BY
				subscription.update_date ,
				subscription.id
		';
		
		return $this->db->get_results( 
				$this->db->prepare( $query, $date->format( 'd-m-Y H:i:s' ) ) 
		);
	}
}