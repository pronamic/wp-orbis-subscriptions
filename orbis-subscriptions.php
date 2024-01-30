<?php
/**
 * Orbis Subscriptions
 *
 * @package   Pronamic\Orbis\Subscriptions
 * @author    Pronamic
 * @copyright 2024 Pronamic
 * @license   GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Orbis Subscriptions
 * Plugin URI:        https://wp.pronamic.directory/plugins/orbis-subscriptions/
 * Description:       The Orbis Subscriptions plugin extends your Orbis environment with the option to add subscription products and subscriptions.
 * Version:           1.2.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Pronamic
 * Author URI:        https://www.pronamic.eu/
 * Text Domain:       orbis-subscriptions
 * Domain Path:       /languages/
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:        https://wp.pronamic.directory/plugins/orbis-subscriptions/
 * GitHub URI:        https://github.com/pronamic/wp-orbis-subscriptions
 */

namespace Pronamic\Orbis\Subscriptions;

/**
 * Autoload.
 */
require_once __DIR__ . '/vendor/autoload_packages.php';

/**
 * Bootstrap.
 */
add_action(
	'plugins_loaded',
	function () {
		\load_plugin_textdomain( 'orbis-subscriptions', false, \dirname( \plugin_basename( __FILE__ ) ) . '/languages' );

		require_once 'includes/functions.php';

		Plugin::instance();
	}
);
