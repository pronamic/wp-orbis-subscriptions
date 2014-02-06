=== Orbis Subscriptions ===
Contributors: pronamic, remcotolsma
Donate link: http://www.orbiswp.com/
Tags: orbis, subscription, licence
Requires at least: 3.5
Tested up to: 3.8.1
Stable tag: 1.1.4
License: Copyright (c) Pronamic
License URI: http://www.pronamic.eu/copyright/



== Description ==



== Installation ==



== Frequently Asked Questions ==



== Screenshots ==



== Developers ==

*	php ~/wp/svn/i18n-tools/makepot.php wp-plugin ~/wp/git/orbis-subscriptions ~/wp/git/orbis-subscriptions/languages/orbis_subscriptions.pot


== Changelog ==

= 1.1.4 =
*	Fix - Fatal error: Call to a member function format() on a non-object.
*	Fix - Fatal error: Call to undefined method Orbis_Subscription::set_type_id().

= 1.1.3 =
*	Tweak - Enabled support for the Orbis persons suggest field.

= 1.1.2 =
*	Tweak - Updated Bootstrap support from v2.1.1 to v3.0.3.
*	Fix - Adjusted some reference to custom tables without the WordPress table prefix.

= 1.1.1 =
*   Fix - A bug in the invoices query hid subscriptions that had a date in the past, but a month with a higher number than the current month.
*   Fix - Start and end dates created by the invoice updater are now correctly set.
*   Fix - Subscriptions in the same year, but with a month later in that year, are now hidden.

= 1.1.0 =
*   Feature - Added a field for selecting the interval of a subscription product.
*   Feature - Added pagination to subscriptions invoices updater shortcodes.
*   Fix - Replaced static table names with variables.

= 1.0.0 =
*	Initial release.
