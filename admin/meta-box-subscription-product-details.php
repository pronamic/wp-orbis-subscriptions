<?php

global $wpdb, $post;

wp_nonce_field( 'orbis_save_subscription_product_details', 'orbis_subscription_product_details_meta_box_nonce' );

$orbis_id    = get_post_meta( $post->ID, '_orbis_subscription_product_id', true );
$price       = get_post_meta( $post->ID, '_orbis_subscription_product_price', true );
$cost_price  = get_post_meta( $post->ID, '_orbis_subscription_product_cost_price', true );
$auto_renew  = get_post_meta( $post->ID, '_orbis_subscription_product_auto_renew', true );
$deprecated  = get_post_meta( $post->ID, '_orbis_subscription_product_deprecated', true );
$interval    = get_post_meta( $post->ID, '_orbis_subscription_product_interval', true );
$description = get_post_meta( $post->ID, '_orbis_subscription_product_description', true );
$link        = get_post_meta( $post->ID, '_orbis_subscription_product_link', true );
$cancel_note = get_post_meta( $post->ID, '_orbis_subscription_product_cancel_note', true );

?>
<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_subscription_product_id"><?php esc_html_e( 'Orbis ID', 'orbis-subscriptions' ); ?></label>
			</th>
			<td>
				<input id="orbis_subscription_product_id" name="_orbis_subscription_product_id" value="<?php echo esc_attr( $orbis_id ); ?>" type="text" class="regular-text" readonly="readonly" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_subscription_product_price"><?php esc_html_e( 'Price', 'orbis-subscriptions' ); ?></label>
			</th>
			<td>
				<input type="text" id="orbis_subscription_product_price" name="_orbis_subscription_product_price" value="<?php echo esc_attr( empty( $price ) ? '' : number_format_i18n( $price, 2 ) ); ?>" class="regular-text" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_subscription_product_cost_price"><?php esc_html_e( 'Cost Price', 'orbis-subscriptions' ); ?></label>
			</th>
			<td>
				<input type="text" id="orbis_subscription_product_cost_price" name="_orbis_subscription_product_cost_price" value="<?php echo esc_attr( empty( $cost_price ) ? '' : number_format_i18n( $cost_price, 2 ) ); ?>" class="regular-text" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_subscription_product_auto_renew">
					<?php esc_html_e( 'Auto Renew', 'orbis-subscriptions' ); ?>
				</label>
			</th>
			<td>
				<label for="orbis_subscription_product_auto_renew">
					<input type="checkbox" value="yes" id="orbis_subscription_product_auto_renew" name="_orbis_subscription_product_auto_renew" <?php checked( $auto_renew ); ?> />
					<?php esc_html_e( 'Auto renew subscription product', 'orbis-subscriptions' ); ?>
				</label>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_subscription_product_deprecated">
					<?php esc_html_e( 'Deprecated', 'orbis-subscriptions' ); ?>
				</label>
			</th>
			<td>
				<label for="orbis_subscription_product_deprecated">
					<input type="checkbox" value="yes" id="orbis_subscription_product_deprecated" name="_orbis_subscription_product_deprecated" <?php checked( $deprecated ); ?> />
					<?php esc_html_e( 'Deprecated subscription product', 'orbis-subscriptions' ); ?>
				</label>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_subscription_product_interval">
					<?php esc_html_e( 'Interval', 'orbis-subscriptions' ); ?>
				</label>
			</th>
			<td>
				<label for="orbis_subscription_product_interval">
					<select id="orbis_subscription_product_interval" name="_orbis_subscription_product_interval">
						<?php

						$intervals = [
							''   => '',
							'Y'  => __( 'Yearly', 'orbis-subscriptions' ),
							'2Y' => __( 'Two Yearly', 'orbis-subscriptions' ),
							'3Y' => __( 'Three Yearly', 'orbis-subscriptions' ),
							'M'  => __( 'Monthly', 'orbis-subscriptions' ),
							'Q'  => __( 'Quarterly', 'orbis-subscriptions' ),
						];

						foreach ( $intervals as $value => $label ) {
							printf(
								'<option value="%s" %s>%s</option>',
								esc_attr( $value ),
								selected( $interval, $value, false ),
								esc_html( $label )
							);
						}

						?>
					</select>
				</label>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_subscription_product_description">
					<?php esc_html_e( 'Description', 'orbis-subscriptions' ); ?>
				</label>
			</th>
			<td>
				<textarea id="orbis_subscription_product_description" name="_orbis_subscription_product_description" rows="3" cols="60"><?php echo \esc_textarea( $description ); ?></textarea>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_subscription_product_link">
					<?php esc_html_e( 'Link', 'orbis-subscriptions' ); ?>
				</label>
			</th>
			<td>
				<input id="orbis_subscription_product_link" name="_orbis_subscription_product_link" value="<?php echo \esc_attr( $link ); ?>" type="url" class="regular-text" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_subscription_product_cancel_note">
					<?php esc_html_e( 'Cancel Note', 'orbis-subscriptions' ); ?>
				</label>
			</th>
			<td>
				<textarea id="orbis_subscription_product_cancel_note" name="_orbis_subscription_product_cancel_note" rows="3" cols="60"><?php echo \esc_textarea( $cancel_note ); ?></textarea>
			</td>
		</tr>
	</tbody>
</table>
