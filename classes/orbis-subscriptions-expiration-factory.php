<?php

class Orbis_Subscriptions_Expiration_Factory {
	private $db;
	
	public function __construct() {
		global $wpdb;
		$this->db = $wpdb;
	}
	
	public function get_sent_reminders( DateTime $from ) {
		$query = "
			SELECT
				subscription.id,
				subscription.post_id,
				subscription.type_id,
				subscription.expiration_date,
				type.id,
				type.auto_renew
			FROM
				{$this->db->orbis_subscriptions} AS subscription
					LEFT JOIN
				{$this->db->orbis_subscription_types} as type
						ON subscription.type_id = type.id
			WHERE 
				type.auto_renew = 0
					AND 
				subscription.update_date <= %s
					AND
				cancel_date IS NULL
					AND
				expiration_date < NOW() + INTERVAL 3 MONTH
					AND
				sent_notifications > 0
		";
		
		$query = $this->db->prepare( $query, $from->format( 'Y-m-d H:i:s' ) );

		$results = $this->db->get_results( $query );
		
		if ( empty( $results ) )
			return array();
		
		$subscriptions = array();
		foreach ( $results as $result ) {
			$subscriptions[] = new Orbis_Subscription( $result->post_id );
		}

		return $subscriptions;
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
		$query = "
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
				type.auto_renew as auto_renew
			FROM
				{$this->db->orbis_subscriptions} AS subscription
					LEFT JOIN
				orbis_companies AS company
						ON subscription.company_id = company.id
					LEFT JOIN
				{$this->db->orbis_subscription_types} AS type
						ON subscription.type_id = type.id
			WHERE
				type.auto_renew = 0
					AND
				cancel_date IS NULL
			ORDER BY
				subscription.update_date ,
				subscription.id
		";
		
		return $this->db->get_results( $query );
	}
	
	public function get_expiring_in( DateTime $date ) {
		$query = "
			SELECT
				subscription.id,
				subscription.post_id,
				subscription.type_id,
				subscription.expiration_date,
				type.id,
				type.auto_renew
			FROM
				{$this->db->orbis_subscriptions} AS subscription
					LEFT JOIN
				{$this->db->orbis_subscription_types} as type
						ON subscription.type_id = type.id
			WHERE 
				type.auto_renew = 0
					AND 
				( 
					subscription.expiration_date <= NOW() 
						OR 
					( subscription.expiration_date <= %s AND subscription.expiration_date >= NOW() ) 
				)
					AND
				cancel_date IS NULL
			ORDER BY
				subscription.sent_notifications ASC,
				subscription.expiration_date ASC
		";
		
		$results = $this->db->get_results( $this->db->prepare( $query, $date->format( 'Y-m-d H:i:s' ) ) );
		
		if ( empty( $results ) )
			return array();

		$subscriptions = array();
		foreach ( $results as $result ) {
			$subscriptions[] = new Orbis_Subscription( $result->post_id );
		}
		
		return $subscriptions;
	}
}
