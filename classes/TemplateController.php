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
		\add_filter( 'template_include', $this->template_include( ... ) );

		\add_action( 'orbis_after_main_content', $this->maybe_include_subscription_invoices( ... ) );

		\add_action( 'orbis_before_side_content', $this->maybe_include_subscription_details( ... ) );

		\add_action( 'orbis_after_main_content', $this->maybe_include_domain_name_subscriptions( ... ) );

		\add_action( 'orbis_after_main_content', $this->maybe_include_product_subscriptions( ... ) );

		\add_filter( 'orbis_company_sections', $this->orbis_company_sections_subscriptions( ... ) );
	}

	/**
	 * Template include.
	 *
	 * Uses the single and archive subscription templates of this plugin, unless the theme has one.
	 *
	 * @param string $template Template.
	 * @return string
	 */
	public function template_include( $template ) {
		if ( \is_singular( 'orbis_subscription' ) && '' === \locate_template( 'single-orbis_subscription.php' ) ) {
			return __DIR__ . '/../templates/single-orbis_subscription.php';
		}

		if ( \is_post_type_archive( 'orbis_subscription' ) && '' === \locate_template( 'archive-orbis_subscription.php' ) ) {
			return __DIR__ . '/../templates/archive-orbis_subscription.php';
		}

		return $template;
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
			'callback' => function (): void {
				include __DIR__ . '/../templates/company-subscriptions.php';
			},
		];

		return $sections;
	}
}
