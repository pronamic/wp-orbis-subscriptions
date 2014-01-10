<?php

global $wpdb, $post;

wp_nonce_field( 'orbis_save_subscription_product_details', 'orbis_subscription_product_details_meta_box_nonce' );

$orbis_id    = get_post_meta( $post->ID, '_orbis_subscription_product_id', true );
$price       = get_post_meta( $post->ID, '_orbis_subscription_product_price', true );
$cost_price  = get_post_meta( $post->ID, '_orbis_subscription_product_cost_price', true );
$auto_renew  = get_post_meta( $post->ID, '_orbis_subscription_product_auto_renew', true );
$deprecated  = get_post_meta( $post->ID, '_orbis_subscription_product_deprecated', true );
$duration    = get_post_meta( $post->ID, '_orbis_subscription_product_duration', true );

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
				<input type="text" id="orbis_subscription_product_price" name="_orbis_subscription_product_price" value="<?php echo empty( $price ) ? '' : esc_attr( number_format( $price, 2, ',', '.' ) ); ?>" class="regular-text" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_subscription_product_cost_price"><?php _e( 'Cost Price', 'orbis_subscriptions' ); ?></label>
			</th>
			<td>
				<input type="text" id="orbis_subscription_product_cost_price" name="_orbis_subscription_product_cost_price" value="<?php echo empty( $cost_price ) ? '' : esc_attr( number_format( $cost_price, 2, ',', '.' ) ) ?>" class="regular-text" />
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
                <label for="orbis_subscription_product_duration">
                    <?php _e( 'Duration', 'orbis_subscriptions' ); ?>
                </label>
            </th>
            <td>
                <label for="orbis_subscription_product_duration">
                    <select id="orbis_subscription_product_duration" name="_orbis_subscription_product_duration">
                        <option value="Y" <?php selected( $duration, 'Y' ); ?>><?php _e( 'One year', 'orbis_subscriptions' ); ?></option>
                        <option value="m" <?php selected( $duration, 'm' ); ?>><?php _e( 'One month', 'orbis_subscriptions' ); ?></option>
                    </select>
                </label>
            </td>
        </tr>
	</tbody>
</table>