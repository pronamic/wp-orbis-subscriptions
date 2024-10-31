<?php
/**
 * Template controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Subscriptions
 */

namespace Pronamic\Orbis\Subscriptions;

use DateTimeImmutable;

/**
 * Template controller class
 */
class TemplateController {
	/**
	 * Setup.
	 * 
	 * @return void
	 */
	public function setup() {
		\add_action( 'orbis_after_main_content', [ $this, 'maybe_include_subscription_invoices' ] );

		\add_action( 'orbis_before_side_content', [ $this, 'maybe_include_subscription_details' ] );

		\add_action( 'orbis_after_main_content', [ $this, 'maybe_include_domain_name_subscriptions' ] );

		\add_action( 'orbis_after_main_content', [ $this, 'maybe_include_product_subscriptions' ] );

		\add_filter( 'orbis_company_sections', [ $this, 'orbis_company_sections_subscriptions' ] );
	}

	/**
	 * Maybe include subscription invoices.
	 * 
	 * @return void
	 */
	public function maybe_include_subscription_invoices() {
		if ( ! \is_singular( 'orbis_subscription' ) ) {
			return;
		}

		include __DIR__ . '/../templates/subscription-invoices.php';
	}

	/**
	 * Maybe include subscription details.
	 * 
	 * @return void
	 */
	public function maybe_include_subscription_details() {
		if ( ! \is_singular( 'orbis_subscription' ) ) {
			return;
		}
		
		include __DIR__ . '/../templates/subscription-details.php';
	}

	/**
	 * Maybe include domain name subscriptions.
	 * 
	 * @return void
	 */
	public function maybe_include_domain_name_subscriptions() {
		if ( ! \is_singular( 'orbis_domain_name' ) ) {
			return;
		}

		include __DIR__ . '/../templates/domain-name-subscriptions.php';
	}

	/**
	 * Maybe include product subscriptions.
	 * 
	 * @return void
	 */
	public function maybe_include_product_subscriptions() {
		if ( ! \is_singular( 'orbis_product' ) ) {
			return;
		}

		include __DIR__ . '/../templates/product-subscriptions.php';
	}

	/**
	 * Company sections subscriptions.
	 * 
	 * @param array $sections Sections.
	 * @return array
	 */
	public function orbis_company_sections_subscriptions( $sections ) {
		$sections[] = [
			'id'       => 'subscriptions',
			'name'     => \__( 'Subscriptions', 'orbis-subscriptions' ),
			'callback' => function () {
				include __DIR__ . '/../templates/company-subscriptions.php';
			}
		];

		return $sections;
	}
}
