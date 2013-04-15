<?php

if ( ! class_exists( 'Orbis_Subscription' ) ) :

	class Orbis_Subscription {
	
		/**
		 * Holds the Post object that this
		 * subscription represents
		 * 
		 * @access private
		 * @var WP_Post
		 */
		private $post;

		/**
		 * Holds the PK for this subscription, 
		 * from the orbis_subscription table
		 * 
		 * @access private
		 * @var int
		 */
		private $id;

		/**
		 * Holds the company associated id,
		 * from the orbis_subscription table
		 * 
		 * @access private
		 * @var int
		 */
		private $company_id;

		/**
		 * Holds the type associated id,
		 * from the orbis_subscription table
		 * 
		 * @access private
		 * @var int
		 */
		private $type_id;

		/**
		 * Holds the domain name id,
		 * from the orbis_subscription table
		 * 
		 * @access private
		 * @var int
		 */
		private $domain_name_id;

		/**
		 * Holds the associated post_id with
		 * this subscription
		 * 
		 * @access private
		 * @var int
		 */
		private $post_id;

		/**
		 * Holds the name from the 
		 * orbis_subscription table
		 * 
		 * @access private
		 * @var string
		 */
		private $name;

		/**
		 * Holds the activation date
		 * of the subscription.  In DateTime
		 * 
		 * @access private
		 * @var DateTime
		 */
		private $activation_date;

		/**
		 * Holds the expiration date
		 * of the subscription. In DateTime
		 * 
		 * @access private
		 * @var DateTime
		 */
		private $expiration_date;

		/**
		 * Holds the cancel date
		 * of the subscription. In DateTime
		 * 
		 * @access private
		 * @var DateTime
		 */
		private $cancel_date;

		/**
		 * Holds the update date
		 * of the subscription. In DateTime
		 * 
		 * @access private
		 * @var DateTime
		 */
		private $update_date;

		/**
		 * Holds the license key
		 * 
		 * @access private
		 * @var string
		 */
		private $license_key;

		/**
		 * Holds the md5'ed license key
		 * 
		 * @access private
		 * @var string
		 */
		private $license_key_md5;

		public function __construct( $subscription = null ) {
			global $post;

			// Use global post or get post from ID
			if ( null === $subscription ) {
				$this->post = $post;
			} elseif ( is_numeric( $subscription ) ) {
				$this->post = get_post( $subscription );
			}

			// Check the subscription from post exists
			if ( ! $subscription )
				return false;

			// Get the subscription id and post type
			$subscription_id = absint( $this->post->ID );
			$post_type		 = $this->post->post_type;

			// Check this is a orbis_subscription
			if ( 'orbis_subscription' === $post_type ) {

				// Get all data from the custom table
				$subscription_data = orbis_subscription_get_data( $subscription_id );

				// Set the properties for this subscription
				$this->set_id( $subscription_data->id );
				$this->set_company_id( $subscription_data->company_id );
				$this->set_post_id( $subscription_data->post_id );
				$this->set_name( $subscription_data->name );
				$this->set_activation_date( new DateTime( $subscription_data->activation_date ) );
				$this->set_expiration_date( new DateTime( $subscription_data->expiration_date ) );
				$this->set_cancel_date( new DateTime( $subscription_data->cancel_date ) );
				$this->set_update_date( new DateTime( $subscription_data->update_date ) );
				$this->set_license_key( $subscription_data->license_key );
				$this->set_license_key_md5( $subscription_data->license_key_md5 );
			}
		}

		public function expire() {
			
		}

		public function extend() {
			
		}

		public function send_reminder() {
			
		}

		public function activate() {
			
		}

		public function save() {
			
		}

		public function remove() {
			
		}

		/**
		 * ====================
		 * 
		 * SETTERS AND GETTERS
		 * 
		 * ====================
		 */
		public function get_id() {
			return $this->id;
		}

		public function set_id( $id ) {
			$this->id = $id;
		}

		public function get_company_id() {
			return $this->company_id;
		}

		public function set_company_id( $company_id ) {
			$this->company_id = $company_id;
		}

		public function get_type_id() {
			return $this->type_id;
		}

		public function set_type_id( $type_id ) {
			$this->type_id = $type_id;
		}

		public function get_domain_name_id() {
			return $this->domain_name_id;
		}

		public function set_domain_name_id( $domain_name_id ) {
			$this->domain_name_id = $domain_name_id;
		}

		public function get_post_id() {
			return $this->post_id;
		}

		public function set_post_id( $post_id ) {
			$this->post_id = $post_id;
		}

		public function get_name() {
			return $this->name;
		}

		public function set_name( $name ) {
			$this->name = $name;
		}

		public function get_activation_date() {
			return $this->activation_date;
		}

		public function set_activation_date( DateTime $activation_date ) {
			$this->activation_date = $activation_date;
		}

		public function get_expiration_date() {
			return $this->expiration_date;
		}

		public function set_expiration_date( DateTime $expiration_date ) {
			$this->expiration_date = $expiration_date;
		}

		public function get_cancel_date() {
			return $this->cancel_date;
		}

		public function set_cancel_date( DateTime $cancel_date ) {
			$this->cancel_date = $cancel_date;
		}

		public function get_update_date() {
			return $this->update_date;
		}

		public function set_update_date( DateTime $update_date ) {
			$this->update_date = $update_date;
		}

		public function get_license_key() {
			return $this->license_key;
		}

		public function set_license_key( $license_key ) {
			$this->license_key = $license_key;
		}

		public function get_license_key_md5() {
			return $this->license_key_md5;
		}

		public function set_license_key_md5( $license_key_md5 ) {
			$this->license_key_md5 = $license_key_md5;
		}

	}

	

endif;