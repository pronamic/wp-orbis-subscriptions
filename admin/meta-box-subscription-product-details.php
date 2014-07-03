<?php

global $wpdb, $post;

wp_nonce_field( 'orbis_save_subscription_product_details', 'orbis_subscription_product_details_meta_box_nonce' );

$orbis_id    = get_post_meta( $post->ID, '_orbis_subscription_product_id', true );
$price       = get_post_meta( $post->ID, '_orbis_subscription_product_price', true );
$cost_price  = get_post_meta( $post->ID, '_orbis_subscription_product_cost_price', true );
$auto_renew  = get_post_meta( $post->ID, '_orbis_subscription_product_auto_renew', true );
$deprecated  = get_post_meta( $post->ID, '_orbis_subscription_product_deprecated', true );
$interval    = get_post_meta( $post->ID, '_orbis_subscription_product_interval', true );

$currency_setting = get_option( 'orbis_currency' );
switch ($currency_setting) {
case 'eur':
    $currency = '&euro;';
    empty( $cost ) ? '' : $price_format = number_format($price, 2, ',', '.');
    empty( $cost_price ) ? '' : $cost_price_format = number_format($cost_price, 2, ',', '.');
    break;
case 'usd':
    $currency = '$';
    empty( $price ) ? '' : $price_format = number_format($price, 2, '.', ',');
    empty( $cost_price ) ? '' : $cost_price_format = number_format($cost_price, 2, '.', ',');
    break;
}
?>
<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_subscription_product_id"><?php _e( 'Orbis ID', 'orbis' ); ?></label>
			</th>
			<td>
				<input id="orbis_subscription_product_id" name="_orbis_subscription_product_id" value="<?php echo esc_attr( $orbis_id ); ?>" type="text" class="regular-text" readonly="readonly" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_subscription_product_price"><?php _e( 'Price', 'orbis_subscriptions' ); ?></label>
			</th>
			<td>
				<input type="text" id="orbis_subscription_product_price" name="_orbis_subscription_product_price" value="<?php echo empty( $price ) ? '' : esc_attr( $price_format ); ?>" class="regular-text" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_subscription_product_cost_price"><?php _e( 'Cost Price', 'orbis_subscriptions' ); ?></label>
			</th>
			<td>
				<input type="text" id="orbis_subscription_product_cost_price" name="_orbis_subscription_product_cost_price" value="<?php echo empty( $cost_price ) ? '' : esc_attr( $cost_price_format ); ?>" class="regular-text" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_subscription_product_auto_renew">
					<?php _e( 'Auto Renew', 'orbis_subscriptions' ); ?>
				</label>
			</th>
			<td>
				<label for="orbis_subscription_product_auto_renew">
					<input type="checkbox" value="yes" id="orbis_subscription_product_auto_renew" name="_orbis_subscription_product_auto_renew" <?php checked( $auto_renew ); ?> />
					<?php _e( 'Auto renew subscription product', 'orbis_subscriptions' ); ?>
				</label>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_subscription_product_deprecated">
					<?php _e( 'Deprecated', 'orbis_subscriptions' ); ?>
				</label>
			</th>
			<td>
				<label for="orbis_subscription_product_deprecated">
					<input type="checkbox" value="yes" id="orbis_subscription_product_deprecated" name="_orbis_subscription_product_deprecated" <?php checked( $deprecated ); ?> />
					<?php _e( 'Deprecated subscription product', 'orbis_subscriptions' ); ?>
				</label>
			</td>
		</tr>
        <tr valign="top">
            <th scope="row">
                <label for="orbis_subscription_product_interval">
                    <?php _e( 'Interval', 'orbis_subscriptions' ); ?>
                </label>
            </th>
            <td>
                <label for="orbis_subscription_product_interval">
                    <select id="orbis_subscription_product_interval" name="_orbis_subscription_product_interval">
                        <option value=""></option>
                        <option value="Y" <?php selected( $interval, 'Y' ); ?>><?php _e( 'Yearly', 'orbis_subscriptions' ); ?></option>
                        <option value="M" <?php selected( $interval, 'M' ); ?>><?php _e( 'Monthly', 'orbis_subscriptions' ); ?></option>
                    </select>
                </label>
            </td>
        </tr>
	</tbody>
</table>