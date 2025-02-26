<?php
/**
 * Subscribers export controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Subscriptions
 */

namespace Pronamic\Orbis\Subscriptions;

use DateTimeImmutable;

/**
 * Subscribers export controller class
 */
class SubscribersExportController {
	/**
	 * Setup.
	 * 
	 * @return void
	 */
	public function setup() {
		\add_action( 'init', [ $this, 'init' ] );

		\add_filter( 'query_vars', [ $this, 'query_vars' ] );
		\add_filter( 'redirect_canonical', [ $this, 'disable_redirect_canonical_for_csv_export' ] );
		\add_filter( 'template_include', [ $this, 'template_include' ] );
	}

	/**
	 * Query vars.
	 * 
	 * @link https://developer.wordpress.org/reference/hooks/query_vars/
	 * @param string[] $query_vars Query vars.
	 * @return string[]
	 */
	public function query_vars( $query_vars ) {
		$query_vars[] = 'orbis_subscriptions_route';

		return $query_vars;
	}

	/**
	 * Initialize.
	 * 
	 * @link https://make.wordpress.org/core/2015/10/07/add_rewrite_rule-accepts-an-array-of-query-vars-in-wordpress-4-4/
	 * @return void
	 */
	public function init() {
		\add_rewrite_rule(
			'abonnementen/abonnees.csv$', 
			[
				'orbis_subscriptions_route' => 'subscribers_csv_export',
			],
			'top'
		);
	}

	/**
	 * Disable redirect 
	 * 
	 * @link https://github.com/WordPress/WordPress/blob/365645c608586b6e0871187d12eafeeb076c633d/wp-includes/canonical.php#L811-L821
	 * @param string $redirect_url Redurect URL.
	 * @return string|false
	 */
	public function disable_redirect_canonical_for_csv_export( $redirect_url ) {
		if ( \get_query_var( 'orbis_subscriptions_route' ) === 'subscribers_csv_export' ) {
			return false;
		}

		return $redirect_url;
	}

	/**
	 * Template include.
	 * 
	 * @param string $template Template.
	 * @return string
	 */
	public function template_include( $template ) {
		$route = \get_query_var( 'orbis_subscriptions_route', null );

		if ( null === $route ) {
			return $template;
		}

		switch ( $route ) {
			case 'subscribers_csv_export':
				return $this->template_include_subscribers_csv_export( $template );
			default:
				return $template;
		}
	}

	/**
	 * Template include subscribers CSV export.
	 * 
	 * @param string $template Template.
	 * @return string
	 */
	private function template_include_subscribers_csv_export( $template ) {
		$template = __DIR__ . '/../templates/subscribers-csv-export.php';

		return $template;
	}
}
