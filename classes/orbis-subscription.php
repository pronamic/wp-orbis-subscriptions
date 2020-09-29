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
	 * Holds the product associated id,
	 * from the orbis_subscription table
	 *
	 * @access private
	 * @var int
	 */
	private $product_id;

	/**
	 * Holds the type name from the
	 * orbis_subscription table.
	 *
	 * @access private
	 * @var string
	 */
	private $product_name;

	/**
	 * Product price.
	 *
	 * @access private
	 * @var string
	 */
	private $product_price;

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
	 * Holds the name from the orbis_subscription table.
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

	public function __construct( $subscription = null ) {
		if ( null !== $subscription ) {
			$this->load( $subscription );
		}
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
		if ( ! $this->post ) {
			return false;
		}

		// Get the subscription id and post type
		$post_id   = absint( $this->post->ID );
		$post_type = $this->post->post_type;

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
				$this->set_product_id( $subscription_data->product_id );
				$this->set_type_name( $subscription_data->product_name );
				$this->set_type_price( $subscription_data->product_price );
				$this->set_name( $subscription_data->name );
				$this->set_email( $subscription_data->email );
				$this->set_activation_date( new DateTime( $subscription_data->activation_date ) );
				$this->set_expiration_date( new DateTime( $subscription_data->expiration_date ) );
				if ( $subscription_data->cancel_date ) {
					$this->set_cancel_date( new DateTime( $subscription_data->cancel_date ) );
				}
				$this->set_update_date( new DateTime( $subscription_data->update_date ) );
			}
		} else {
			return false;
		}
	}

	public function expire() {

	}

	public function activate() {

	}

	public function save() {
		global $wpdb;

		$utc = new \DateTimeZone( 'UTC' );

		// Must be new
		if ( ! $this->get_id() ) {
			$activation_date = $this->get_activation_date();

			if ( null !== $activation_date ) {
				$activation_date = \DateTimeImmutable::createFromMutable( $activation_date );
			}

			$expiration_date = $this->get_expiration_date();

			if ( null !== $expiration_date ) {
				$expiration_date = \DateTimeImmutable::createFromMutable( $expiration_date );
			}

			$data = array(
				'company_id'      => $this->get_company_id(),
				'type_id'         => $this->get_product_id(),
				'post_id'         => $this->get_post_id(),
				'name'            => $this->get_name(),
				'email'           => $this->get_email(),
				'activation_date' => ( null === $activation_date ) ? null : $activation_date->setTimezone( $utc )->format( 'Y-m-d H:i:s' ),
				'expiration_date' => ( null === $expiration_date ) ? null : $expiration_date->setTimezone( $utc )->format( 'Y-m-d H:i:s' ),
			);

			$format = array(
				'company_id'      => '%d',
				'type_id'         => '%d',
				'post_id'         => '%d',
				'name'            => '%s',
				'email'           => '%s',
				'activation_date' => '%s',
				'expiration_date' => '%s',
			);

			$result = $wpdb->insert( $wpdb->orbis_subscriptions, $data, $format );
		} else {
			$update_date = $this->get_activation_date();

			if ( null !== $update_date ) {
				$update_date = \DateTimeImmutable::createFromMutable( $update_date );
			}

			$data = array(
				'company_id'  => $this->get_company_id(),
				'type_id'     => $this->get_product_id(),
				'name'        => $this->get_name(),
				'email'       => $this->get_email(),
				'update_date' => ( null === $update_date ) ? null : $update_date->setTimezone( $utc )->format( 'Y-m-d H:i:s' ),
			);

			$where = array( 'id' => $this->get_id() );

			$format = array(
				'company_id'  => '%d',
				'type_id'     => '%d',
				'name'        => '%s',
				'email'       => '%s',
				'update_date' => '%s',
			);

			// Update it!
			$result = $wpdb->update( $wpdb->orbis_subscriptions, $data, $where, $format );
		}

		return $result;
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

	public function get_product_id() {
		return $this->product_id;
	}

	public function set_product_id( $product_id ) {
		$this->product_id = $product_id;
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

	public function get_agreement_id() {
		return $this->agreement_id;
	}

	public function set_agreement_id( $agreement_id ) {
		$this->agreement_id = $agreement_id;
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

	//////////////////////////////////////////////////

	public function register_invoice( $invoice_number, DateTime $start_date, DateTime $end_date ) {
		global $wpdb;

		// Insert subscription invoice
		$result = $wpdb->insert(
			$wpdb->orbis_subscriptions_invoices,
			array(
				'subscription_id' => $this->get_id(),
				'invoice_number'  => $invoice_number,
				'start_date'      => $start_date->format( 'Y-m-d H:i:s' ),
				'end_date'        => $end_date->format( 'Y-m-d H:i:s' ),
				'user_id'         => get_current_user_id(),
				'create_date'     => date( 'Y-m-d H:i:s' ),
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
			)
		);

		// Update subscription
		$wpdb->update(
			$wpdb->orbis_subscriptions,
			// Data
			array(
				'expiration_date' => $end_date->format( 'Y-m-d H:i:s' ),
			),
			// Where
			array(
				'id' => $this->get_id(),
			),
			// Format
			array(
				'%s',
			),
			// Where format
			array(
				'%d',
			)
		);

		// Update billed to.
		$query = '
			UPDATE
				orbis_subscriptions AS subscription
					INNER JOIN
				(
					SELECT
						subscription_invoice.subscription_id,
						subscription_invoice.invoice_number,
						subscription_invoice.end_date
					FROM
						orbis_subscriptions_invoices AS subscription_invoice
							INNER JOIN
						(
							SELECT
								subscription_id,
								MAX( invoice_number ) AS invoice_number
							FROM
								orbis_subscriptions_invoices
							GROUP BY
								subscription_id
						) AS last_subscription_invoice
								ON (
									last_subscription_invoice.subscription_id = subscription_invoice.subscription_id
										AND
									last_subscription_invoice.invoice_number = subscription_invoice.invoice_number
								)
					GROUP BY
						subscription_invoice.subscription_id
				) AS subscription_invoice
					ON subscription.id = subscription_invoice.subscription_id
			SET
				subscription.billed_to = subscription_invoice.end_date
			;
		';

		$wpdb->query( $query );

		return $result;
	}

	public static function get_current_period_end_date( $date, $interval, $cancel_date_string = null ) {
		// Current Period End Date.
		$start = new \DateTimeImmutable( $date );

		$current_date = new \DateTimeImmutable();

		$interval = new \DateInterval( 'P1' . $interval );

		$end = $current_date->add( $interval );

		$anchor_date = $current_date->modify( '+30 days' );

		$period = new \DatePeriod( $start, $interval, $end );

		$current_period_end_date = $end;

		foreach ( $period as $d ) {
			$start_date = $d;
			$end_date   = $d->add( $interval );

			$is_current = $anchor_date >= $start_date && $anchor_date < $end_date;

			if ( $is_current ) {
				$current_period_end_date = $end_date;
			}

			if ( null !== $cancel_date_string ) {
				$cancel_date = new \DateTimeImmutable( $cancel_date_string );

				if ( $cancel_date <= $end_date->modify( '-30 days' ) ) {
					return $end_date;
				}
			}
		}

		return $current_period_end_date;
	}
}
