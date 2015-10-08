<?php

global $wpdb, $post;

wp_nonce_field( 'orbis_save_subscription_purchase_details', 'orbis_subscription_purchase_details_meta_box_nonce' );

$price = get_post_meta( $post->ID, '_orbis_subscription_purchase_price', true );

?>
<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_subscription_purchase_price"><?php esc_html_e( 'Price', 'orbis_subscriptions' ); ?></label>
			</th>
			<td>
				<input type="text" id="orbis_subscription_purchase_price" name="_orbis_subscription_purchase_price" value="<?php echo esc_attr( empty( $price ) ? '' : number_format_i18n( $price, 2 ) ); ?>" class="regular-text" />
			</td>
		</tr>
	</tbody>
</table>
