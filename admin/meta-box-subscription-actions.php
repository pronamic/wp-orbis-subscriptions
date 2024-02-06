<?php
/**
 * Meta box subscription actions
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Subscriptions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<?php esc_html_e( 'Cancel', 'orbis-subscriptions' ); ?>
		</th>
		<td>
			<?php wp_nonce_field( 'orbis_subscription_cancel', 'orbis_subscription_cancel_nonce' ); ?>

			<?php

			$quicktags_settings = [ 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,close' ];

			wp_editor(
				'',
				'orbis_subscription_cancel_content',
				[
					'media_buttons' => false,
					'tinymce'       => false,
					'quicktags'     => $quicktags_settings,
				] 
			);

			?>
			<br />
			<?php

			submit_button(
				__( 'Cancel Subscription', 'orbis-subscriptions' ),
				'secondary',
				'orbis_subscription_cancel',
				false
			);

			?>
		</td>
	</tr>
</table>
