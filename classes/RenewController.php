<?php
/**
 * Renew controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Subscriptions
 */

namespace Pronamic\Orbis\Subscriptions;

use DateTimeImmutable;

/**
 * Renew controller class
 */
class RenewController {
	/**
	 * Setup.
	 * 
	 * @return void
	 */
	public function setup() {
		\add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initialize.
	 * 
	 * @return void
	 */
	public function init() {
		\add_action( 'orbis_subscriptions_renew_subscriptions', [ $this, 'renew_subscriptions' ] );

		if ( false === \as_has_scheduled_action( 'orbis_subscriptions_renew_subscriptions' ) ) {
			\as_schedule_recurring_action(
				\strtotime( 'tomorrow' ),
				\DAY_IN_SECONDS,
				'orbis_subscriptions_renew_subscriptions',
				[],
				'orbis-subscriptions',
				true
			);
		}
	}

	/**
	 * Renew subscriptions.
	 * 
	 * @return void
	 */
	public function renew_subscriptions() {
		global $wpdb;

		$date = new DateTimeImmutable( '+1 month' );

		$data = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT
					subscription.id AS subscription_id,
					subscription.post_id AS subscription_post_id,
					subscription.expiration_date AS subscription_expiration_date,
					product.interval AS product_interval
				FROM
					$wpdb->orbis_subscriptions AS subscription
						INNER JOIN
					$wpdb->orbis_subscription_products AS product
							ON subscription.type_id = product.id
				WHERE
					product.auto_renew
						AND
					subscription.expiration_date < %s
						AND
					(
						subscription.cancel_date IS NULL
							OR
						subscription.cancel_date > DATE_SUB( subscription.expiration_date, INTERVAL 1 MONTH )
					)
						AND
					(
						subscription.end_date IS NULL
							OR
						subscription.end_date > subscription.expiration_date
					)
				;
				",
				$date->format( 'Y-m-d' )
			)
		);

		foreach ( $data as $item ) {
			$expiration_date_old = DateTimeImmutable::createFromFormat( 'Y-m-d', $item->subscription_expiration_date );

			if ( false === $expiration_date_old ) {
				throw new \Exception( 'Cannot process the expiration date: ' . \esc_html( $item->subscription_expiration_date ) );
			}

			switch ( $item->product_interval ) {
				case 'Y':
					$expiration_date_new = $expiration_date_old->modify( '+1 year' );

					break;
				case 'M':
					$expiration_date_new = $expiration_date_old->modify( '+1 month' );

					break;
				default:
					throw new \Exception( 'Unsupported product interval: ' . \esc_html( $item->product_interval ) );
			}

			$result = $wpdb->update(
				$wpdb->orbis_subscriptions,
				[
					'expiration_date' => $expiration_date_new->format( 'Y-m-d' ),
				],
				[
					'id' => $item->subscription_id,
				],
				[
					'expiration_date' => '%s',
				],
				[
					'id' => '%d',
				]
			);

			if ( false === $result ) {
				throw new \Exception( 'Could not update subscription expiration date: ' . \esc_html( $item->subscription_id ) );
			}

			\wp_insert_comment(
				[
					'comment_author'       => \__( 'Orbis subscriptions renewer', 'orbis-subscriptions' ),
					'comment_author_email' => 'orbis@pronamic.nl',
					'comment_author_url'   => \home_url( '/' ),
					'comment_content'      => \sprintf(
						/* translators: 1: Old expiration date, 2: New expiration date, 3: Notice period. */
						\__( 'Orbis has automatically renewed this subscription from "%1$s" to "%2$s", taking into account a notice period of %3$s month.', 'orbis-subscriptions' ),
						\wp_date( \get_option( 'date_format' ), $expiration_date_old->getTimestamp() ),
						\wp_date( \get_option( 'date_format' ), $expiration_date_new->getTimestamp() ),
						'1'
					),
					'comment_post_ID'      => $item->subscription_post_id,
					'comment_type'         => 'orbis_comment',
					'comment_meta'         => [
						'_orbis_subscription_expiration_date_old' => $expiration_date_old->format( 'Y-m-d' ),
						'_orbis_subscription_expiration_date_new' => $expiration_date_new->format( 'Y-m-d' ),
					],
				]
			);
		}
	}
}
