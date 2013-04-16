<?php

if ( !class_exists( 'Orbis_Subscription' ) ) :

	class Orbis_Subscription {

		/**
		 * Holds the WPDB class object
		 * 
		 * @access private
		 * @var WPDB
		 */
		private $db;

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
		 * Holds the company name.
		 * 
		 * This company information could probably
		 * be split into its own class. That is outside
		 * the scope of this project for now.
		 * 
		 * @access private
		 * @var string
		 */
		private $company_name;
		
		/**
		 * Holds the company email
		 * 
		 * This company information could probably
		 * be split into its own class. That is outside
		 * the scope of this project for now.
		 * 
		 * @access private
		 * @var string
		 */
		private $company_email;
		
		/**
		 * Holds the type associated id,
		 * from the orbis_subscription table
		 * 
		 * @access private
		 * @var int
		 */
		private $type_id;
		
		/**
		 * Holds the type name from the
		 * orbis_subscription table.
		 * 
		 * @access private
		 * @var string
		 */
		private $type_name;
		
		/**
		 * 
		 * @access private
		 * @var string
		 */
		private $type_price;

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
		 * Holds the email from the
		 * orbis_subscription table
		 * 
		 * @access private
		 * @var string
		 */
		private $email;

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
			global $wpdb;
			$this->db = $wpdb;

			if ( null !== $subscription )
				$this->load( $subscription );
		}

		public function load( $subscription = null ) {
			// Will get global post if null set
			if ( null === $subscription ) {

				global $post;
				$this->post = $post;

				// Or if a raw WP_Post object, load that
			} elseif ( $subscription instanceof WP_Post ) {

				$this->post = $subscription;

				// Or if just an id, find that post!
			} elseif ( is_numeric( $subscription ) ) {
				
				$this->post = get_post( $subscription );
			}

			// Check the subscription from post exists
			if ( !$this->post )
				return false;

			// Get the subscription id and post type
			$post_id = absint( $this->post->ID );
			$post_type = $this->post->post_type;
			

			// Check this is a orbis_subscription
			if ( 'orbis_subscription' === $post_type ) {

				// Get all data from the custom table
				$subscription_data = orbis_subscription_get_data( $post_id );

				if ( !empty( $subscription_data ) ) {
					// Set the properties for this subscription
					$this->set_id( $subscription_data->id );
					$this->set_company_id( $subscription_data->company_id );
					$this->set_company_name( $subscription_data->company_name );
					$this->set_company_email( $subscription_data->company_email );
					$this->set_post_id( $subscription_data->post_id );
					$this->set_type_id( $subscription_data->type_id );
					$this->set_type_name( $subscription_data->type_name );
					$this->set_type_price( $subscription_data->type_price );
					$this->set_name( $subscription_data->name );
					$this->set_email( $subscription_data->email );
					$this->set_activation_date( new DateTime( $subscription_data->activation_date ) );
					$this->set_expiration_date( new DateTime( $subscription_data->expiration_date ) );
					$this->set_cancel_date( new DateTime( $subscription_data->cancel_date ) );
					$this->set_update_date( new DateTime( $subscription_data->update_date ) );
					$this->set_license_key( $subscription_data->license_key );
					$this->set_license_key_md5( $subscription_data->license_key_md5 );
				}
			} else {
				return false;
			}
		}

		public function expire() {
			
		}

		/**
		 * Extends the subscription for a passed DateInterval.
		 * 
		 * If no DateInterval is passed, then it defaults to 1 year extension.
		 * 
		 * @access public
		 * @param DateInterval $date_interval
		 * @return boolean
		 */
		public function extend( DateInterval $date_interval = null ) {
			// If no date interval supplied, default to 1 year
			if ( !$date_interval )
				$date_interval = new DateInterval( 'P1Y' );

			// Add the interval period to the expiration date
			$this->expiration_date->add( $date_interval );

			// Query string
			$query = "
				UPDATE
					orbis_subscriptions
				SET
					expiration_date = '%s',
					update_date = NOW()
				WHERE
					id = %d
			";

			// Update the database
			$response = $this->db->query(
					$this->db->prepare( $query, $this->get_expiration_date(), $this->get_id() )
			);

			// Because 0 can be returned, a boolean type response will return a false negative
			if ( false === $response )
				return false;
			else
				return true;
		}

		/**
		 * Using wp_mail, a mail is sent to this subscriptions
		 * stored company_email
		 * 
		 * If isn't a valid email, or isn't set, false is returned
		 * 
		 * Uses the mail_subject and mail_contents from the settings
		 * 
		 * @access public
		 * @return boolean
		 */
		public function send_reminder( $raw_subject, $raw_body, $url ) {
			// Check email is set and valid
			if ( is_email( $this->get_email() ) ) {

				// Get interval till expiration
				$expiration_interval = $this->until_expiration();

				// Build the renew url
				$update_url = $this->renew_url( $url );

				// Keys in body
				$content_keys = array(
					'%company_name%' => $this->get_name(),
					'%days_to_expiration%' => $expiration_interval->format( '%r%a' ),
					'%renew_license_url%' => $update_url
				);

				// Replace the placeholder body contents
				$body = str_replace( array_keys( $content_keys ), $content_keys, $raw_body );

				// Attempt to send the mail
				if ( wp_mail( $this->get_email(), $raw_subject, $body ) ) {
					// Store a comment note of successful reminder
					$this->store_note();
				} else {
					return false;
				}
			} else {
				return false;
			}
		}

		/**
		 * Determine how long till this account expires.  Will return
		 * a DateInterval of the difference from now till expiration
		 * 
		 * @access public
		 * @return DateInterval
		 */
		public function until_expiration() {
			// Current DateTime
			$date_now = new DateTime();
			return $date_now->diff( $this->get_expiration_date() );
		}

		/**
		 * Returns the url to update this subscription
		 * 
		 * @access public
		 * @return string
		 */
		public function renew_url( $url ) {
			return add_query_arg( array( 'license' => $this->get_license_key_md5() ), $url );
		}

		/**
		 * Generates and sets a license key for this subscription
		 * 
		 * Uses an md5 string of the company id, type id and name.
		 * 
		 * @access public
		 * @return string
		 */
		public function generate_license_key() {
			if ( !isset( $this->company_id ) && !isset( $this->type_id ) && !isset( $this->name ) )
				return false;

			$license_key = md5( '' . $this->get_company_id() . $this->get_type_id() . $this->get_name() );
			$license_key_md5 = md5( $license_key );

			$this->set_license_key( $license_key );
			$this->set_license_key_md5( $license_key_md5 );

			return $license_key;
		}

		public function activate() {
			
		}

		public function save() {
			// Must be new
			if ( !$this->get_id() ) {

				$data = array(
					'company_id' => $this->get_company_id(),
					'type_id' => $this->get_type_id(),
					'post_id' => $this->get_post_id(),
					'name' => $this->get_name(),
					'activation_date' => $this->get_activation_date(),
					'expiration_date' => $this->get_expiration_date(),
					'license_key' => $this->get_license_key(),
					'license_key_md5' => $this->get_license_key_md5()
				);

				$format = array(
					'company_id' => '%d',
					'type_id' => '%d',
					'post_id' => '%d',
					'name' => '%s',
					'activation_date' => '%s',
					'expiration_date' => '%s',
					'license_key' => '%s',
					'license_key_md5' => '%s'
				);

				$result = $this->db->insert( 'orbis_subscriptions', $data, $format );
			} else {
				$data = array(
					'company_id' => $this->get_company_id(),
					'type_id' => $this->get_type_id(),
					'name' => $this->get_name()
				);

				$where = array( 'id' => $this->get_id() );

				$format = array(
					'company_id' => '%d',
					'type_id' => '%d',
					'name' => '%s'
				);

				// Update!
				$result = $this->db->update( 'orbis_subscriptions', $data, $where, $format );
			}

			return $result;
		}

		public function remove() {
			
		}

		/**
		 * Stores a note of a sent expiration reminder
		 * 
		 * @access private
		 * @return void
		 */
		private function store_note() {
			$comment = array(
				'comment_post_ID' => $this->get_post_id(),
				'comment_author' => 'System',
				'comment_content' => 'A license expiration reminder has been sent to ' . $this->get_name() . ' ( ' . $this->get_email() . ' ) '
			);

			return wp_insert_comment( $comment );
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
			return $this;
		}

		public function get_company_id() {
			return $this->company_id;
		}

		public function set_company_id( $company_id ) {
			$this->company_id = $company_id;
			return $this;
		}
		
		public function get_company_name() {
			return $this->company_name;
		}

		public function set_company_name( $company_name ) {
			$this->company_name = $company_name;
			return $this;
		}
		
		public function get_company_email() {
			return $this->company_email;
		}

		public function set_company_email( $company_email ) {
			$this->company_email = $company_email;
			return $this;
		}
		
		public function get_type_id() {
			return $this->type_id;
		}

		public function set_type_id( $type_id ) {
			$this->type_id = $type_id;
			return $this;
		}
		
		public function get_type_name() {
			return $this->type_name;
		}

		public function set_type_name( $type_name ) {
			$this->type_name = $type_name;
			return $this;
		}
		
		public function get_type_price( $symbol = '', $dec_point = ',', $thousand = '.' ) {
			return $symbol . number_format( $this->type_price, 2, $dec_point, $thousand );
		}
		
		public function set_type_price( $type_price ) {
			$this->type_price = $type_price;
			return $this;
		}

		public function get_domain_name_id() {
			return $this->domain_name_id;
		}

		public function set_domain_name_id( $domain_name_id ) {
			$this->domain_name_id = $domain_name_id;
			return $this;
		}

		public function get_post_id() {
			return $this->post_id;
		}

		public function set_post_id( $post_id ) {
			$this->post_id = $post_id;
			return $this;
		}

		public function get_name() {
			return $this->name;
		}

		public function set_name( $name ) {
			$this->name = $name;
			return $this;
		}

		public function get_email() {
			return $this->email;
		}

		public function set_email( $email ) {
			$this->email = $email;
			return $this;
		}

		public function get_activation_date() {
			return $this->activation_date;
		}

		public function set_activation_date( DateTime $activation_date ) {
			$this->activation_date = $activation_date;
			return $this;
		}

		public function get_expiration_date() {
			return $this->expiration_date;
		}

		public function set_expiration_date( DateTime $expiration_date ) {
			$this->expiration_date = $expiration_date;
			return $this;
		}

		public function get_cancel_date() {
			return $this->cancel_date;
		}

		public function set_cancel_date( DateTime $cancel_date ) {
			$this->cancel_date = $cancel_date;
			return $this;
		}

		public function get_update_date() {
			return $this->update_date;
		}

		public function set_update_date( DateTime $update_date ) {
			$this->update_date = $update_date;
			return $this;
		}

		public function get_license_key() {
			return $this->license_key;
		}

		public function set_license_key( $license_key ) {
			$this->license_key = $license_key;
			return $this;
		}

		public function get_license_key_md5() {
			return $this->license_key_md5;
		}

		public function set_license_key_md5( $license_key_md5 ) {
			$this->license_key_md5 = $license_key_md5;
			return $this;
		}

	}

	

	

	

	

	

	

	

	

	

	

	

	

	

	

	

endif;