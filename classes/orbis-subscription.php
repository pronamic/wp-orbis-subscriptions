<?php

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

	/**
	 * Holds the total sent number of
	 * notifications to this subscription
	 * 
	 * @access private
	 * @var int
	 */
	private $sent_notifications;

	/**
	 * Holds the subject sent
	 * out with the email notification
	 * 
	 * @access private
	 * @var string
	 */
	private $email_subject;

	/**
	 * Holds the body sent
	 * out with the email notification
	 * 
	 * @access private
	 * @var string
	 */
	private $email_body;

	public function __construct( $subscription = null ) {
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
		if ( ! $this->post )
			return false;

		// Get the subscription id and post type
		$post_id	 = absint( $this->post->ID );
		$post_type	 = $this->post->post_type;


		// Check this is a orbis_subscription
		if ( 'orbis_subscription' === $post_type ) {

			// Get all data from the custom table
			$subscription_data = orbis_subscription_get_data( $post_id );

			if ( ! empty( $subscription_data ) ) {
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
				$this->set_sent_notifications( $subscription_data->sent_notifications );
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
	 * @param string $modify
	 * @return boolean
	 */
	public function extend( $modify = '+1 year' ) {
		global $wpdb;

		// Modify the expiration date
		$expiration = $this->get_expiration_date()->modify( $modify );

		$data = array(
			'expiration_date' => $expiration->format( 'Y-m-d H:i:s' ),
			'update_date'     => date( 'Y-m-d H:i:s' )
		);

		$where = array( 'id' => $this->get_id() );

		$format = array(
			'expiration_date' => '%s',
			'update_date'     => '%s'
		);

		$response = $wpdb->update( $wpdb->orbis_subscriptions, $data, $where, $format );

		// Because 0 can be returned, a boolean type response will return a false negative
		if ( false === $response ) {
			return false;
		} else {
			$this->set_expiration_date( $expiration );
			return true;
		}
	}

	/**
	 * Determines if this subscription has been the
	 * DateInterval parameter since the last reminder.
	 * 
	 * You can supply a DateTime object as the second
	 * param, if you wish to compare with another time
	 * other than current.
	 * 
	 * Would also be a good idea to fill with an object
	 * outside the loop, if this is called from within.
	 * 
	 * @param DateInterval $interval
	 * @param DateTime $now
	 * @return boolean
	 */
	public function since_last_reminder( DateInterval $interval, DateTime $now = null ) {
		// Get this subscriptions update date, 
		$date = $this->get_update_date();
		
		//and add the interval
		$date->add( $interval );
		
		// If no datetime supplied, make a new one
		if ( ! $now ) $now = new DateTime();
		
		return ( $now > $date );
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

			// Build the renew url
			$update_url = $this->renew_url( $url );

			$timestamp       = $this->get_expiration_date()->format( 'U' );
			$expiration_date = date_i18n( __( 'j F, Y @ G:i:s', 'orbis_subscriptions' ), $timestamp );

			// Keys in body
			$content_keys = array(
					'{company_name}'       => $this->get_company_name(),
					'{days_to_expiration}' => $this->until_expiration_human(),
					'{expiration_date}'    => $expiration_date,
					'{renew_license_url}'  => $update_url
			);
			
			// Mail
			global $orbis_subscriptions_plugin;
			global $orbis_email_title;
			global $post;
			global $orbis_subscription;
			
			$post = $this->post;
			$orbis_subscription = $this;
			
			$to      = $this->get_email();
			$subject = $raw_subject;
				
			$orbis_email_title = $raw_subject;
			
			$message_html  = $orbis_subscriptions_plugin->get_template( 'emails/subscription-extend-reminder.php', false );
			$message_plain = wpautop( wptexturize( strip_tags( $message_html ) ) );

			$headers = array(
				'From: Pronamic <support@pronamic.nl>',
				'Content-Type: text/html'
			);

			// Replace the placeholder body contents
			// $this->email_subject = $raw_subject;
			// $this->email_body	 = wpautop( str_replace( array_keys( $content_keys ), $content_keys, $raw_body ) );

			// $headers = array( 
			// 	'Content-Type: text/html' 
			// );

			$result = wp_mail( $to, $subject, $message_html, $headers );

			// Attempt to send the mail
			if ( $result ) {

				// Update the date this was updated (-_-)
				$this->set_update_date( new DateTime() );

				$sent = $this->get_sent_notifications();

				$this->set_sent_notifications(  ++ $sent );
				$this->save();

				// Store a comment note of successful reminder
				$comment_id = orbis_subscriptions_comment_email( $to, $message_plain );
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Conditional method to determine if this subscription has passed
	 * expiration or not.
	 * 
	 * @access public
	 * @param DateTime $now | A custom comparison datetime
	 * @return boolean
	 */
	public function passed_expiration( DateTime $now = null ) {
		if ( ! $now )
			$now = new DateTime();

		return ( $now > $this->get_expiration_date() );
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
	 * Returns a human readable difference between the expiration date
	 * and now.
	 * 
	 * Perhaps requires parameters to be changed to sprintf formats so you
	 * can have the order how you want.
	 * 
	 * @param string $passed | default: 'ago'
	 * @param string $till | default: 'In'
	 * @return string
	 */
	public function until_expiration_human( $passed = 'ago', $till = 'In' ) {
		$expires = $this->get_expiration_date()->format( 'U' );

		// If now is greater, then its already expired
		if ( $this->passed_expiration() ) {
			return human_time_diff( $expires ) . ' ' . $passed;
		} else {
			return $till . ' ' . human_time_diff( $expires );
		}
	}

	/**
	 * Returns the url to update this subscription
	 * 
	 * @access public
	 * @return string
	 */
	public function renew_url( $url ) {
		return add_query_arg( array( 
			'domain_name' => $this->get_name(),
			'license'     => $this->get_license_key() 
		), $url );
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
		$license_key = md5( uniqid() );
		$this->set_license_key( $license_key );

		return $license_key;
	}

	public function activate() {
		
	}

	public function save() {
		global $wpdb;

		// Must be new
		if ( ! $this->get_id() ) {

			$data = array(
				'company_id'      => $this->get_company_id(),
				'type_id'         => $this->get_type_id(),
				'post_id'         => $this->get_post_id(),
				'name'            => $this->get_name(),
				'email'           => $this->get_email(),
				'activation_date' => orbis_date2mysql( $this->get_activation_date() ),
				'expiration_date' => orbis_date2mysql( $this->get_expiration_date() ),
				'license_key'     => $this->get_license_key(),
				'license_key_md5' => $this->license_key_md5
			);

			$format = array(
				'company_id'      => '%d',
				'type_id'         => '%d',
				'post_id'         => '%d',
				'name'            => '%s',
				'email'           => '%s',
				'activation_date' => '%s',
				'expiration_date' => '%s',
				'license_key'     => '%s',
				'license_key_md5' => '%s'
			);

			$result = $wpdb->insert( $wpdb->orbis_subscriptions, $data, $format );
		} else {
			$data = array(
				'company_id'         => $this->get_company_id(),
				'type_id'            => $this->get_type_id(),
				'name'               => $this->get_name(),
				'email'              => $this->get_email(),
				'update_date'        => $this->get_update_date()->format( 'Y-m-d H:i:s' ),
				'sent_notifications' => $this->get_sent_notifications()
			);

			$where = array( 'id' => $this->get_id() );

			$format = array(
				'company_id'         => '%d',
				'type_id'            => '%d',
				'name'               => '%s',
				'email'              => '%s',
				'update_date'        => '%s',
				'sent_notifications' => '%d'
			);

			// Update!
			$result = $wpdb->update( $wpdb->orbis_subscriptions, $data, $where, $format );
		}
        
		return $result;
	}

	public function remove() {
		
	}

	/**
	 * Stores a note of a sent expiration reminder
	 * 
	 * @access private
	 * @return int
	 */
	private function store_note() {
		$comment = array(
			'comment_post_ID' => $this->get_post_id(),
			'comment_author'  => 'System',
			'comment_content' => sprintf(
				__( 'A license expiration reminder has been sent to %s (%s).', 'orbis_subscriptions' ),
				$this->get_company_name(),
				$this->get_email()
			) . 
			'<blockquote>' . $this->email_body . '</blockquote>'
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
		$this->license_key     = $license_key;
		$this->license_key_md5 = md5( $license_key );
		return $this;
	}

	public function get_license_key_md5() {
		return $this->license_key_md5;
	}

	public function get_sent_notifications() {
		return $this->sent_notifications;
	}

	public function set_sent_notifications( $sent_notifications ) {
		$this->sent_notifications = $sent_notifications;
	}
}
