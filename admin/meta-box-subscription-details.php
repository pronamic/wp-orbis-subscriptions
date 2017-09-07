<?php

global $wpdb, $post;

wp_nonce_field( 'orbis_save_subscription_details', 'orbis_subscription_details_meta_box_nonce' );

$subscription = get_orbis_subscription( $post );

$product_id = $subscription->get_product_id();

$query = $wpdb->prepare( "SELECT * FROM $wpdb->orbis_subscription_products WHERE ( NOT deprecated OR id = %d ) ORDER BY name;", $product_id );

$subscription_products = $wpdb->get_results( $query, OBJECT_K );

?>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<label for="orbis_subscription_id"><?php esc_html_e( 'Orbis ID', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<input id="orbis_subscription_id" name="_orbis_subscription_id" value="<?php echo esc_attr( $subscription->get_id() ); ?>" type="text" class="regular-text" readonly="readonly" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_subscription_company"><?php esc_html_e( 'Company ID', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<input type="text" id="orbis_subscription_company" name="_orbis_subscription_company_id" value="<?php echo esc_attr( $subscription->get_company_id() ); ?>" class="orbis-id-control orbis_company_id_field regular-text" data-text="<?php echo esc_attr( $subscription->get_company_name() ); ?>" placeholder="<?php esc_attr_e( 'Select Company', 'orbis_subscriptions' ); ?>" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_subscription_type"><?php esc_html_e( 'Type', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<select id="orbis_subscription_type" name="_orbis_subscription_type_id">
				<option value=""></option>

				<?php

				foreach ( $subscription_products as $subscription_product ) {
					$text = sprintf(
						'%s (%s)',
						$subscription_product->name,
						orbis_price( $subscription_product->price )
					);

					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $subscription_product->id ),
						selected( $subscription_product->id, $product_id, false ),
						esc_html( $text )
					);
				}

				?>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_subscription_name"><?php esc_html_e( 'Name', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<input id="orbis_subscription_name" name="_orbis_subscription_name" value="<?php echo esc_attr( $subscription->get_name() ); ?>" type="text" class="regular-text" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_subscription_person_id"><?php esc_html_e( 'Person', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<?php $person_id = get_post_meta( $post->ID, '_orbis_subscription_person_id', true ); ?>
			<input id="orbis_subscription_person_id" name="_orbis_subscription_person_id" value="<?php echo esc_attr( $person_id ); ?>" type="text" class="orbis-id-control orbis-person-id-control regular-text" data-text="<?php echo esc_attr( $person_id ); ?>" placeholder="<?php esc_attr_e( 'Select Person', 'orbis_subscriptions' ); ?>" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_subscription_email"><?php esc_html_e( 'Email', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<input id="orbis_subscription_email" name="_orbis_subscription_email" value="<?php echo esc_attr( $subscription->get_email() ); ?>" type="text" class="regular-text" />
		</td>
	</tr>
	<tr valign="top">
		<?php

		$date = $subscription->get_activation_date();

		$value = '';
		if ( $date ) {
			$value = date_i18n( 'D j M Y H:i:s', $date->format( 'U' ) );
		}

		?>
		<th scope="row">
			<label for="orbis_subscription_activation_date"><?php esc_html_e( 'Activation Date', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<input id="orbis_subscription_activation_date" name="_orbis_subscription_activation_date" value="<?php echo esc_attr( $value ); ?>" type="text" readonly="readonly" class="regular-text" />
		</td>
	</tr>
	<tr valign="top">
		<?php

		$date = $subscription->get_expiration_date();

		$value = '';
		if ( $date ) {
			$value = date_i18n( 'D j M Y H:i:s', $date->format( 'U' ) );
		}

		?>
		<th scope="row">
			<label for="orbis_subscription_expiration_date"><?php esc_html_e( 'Expiration Date', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<input id="orbis_subscription_expiration_date" name="_orbis_subscription_expiration_date" value="<?php echo esc_attr( $value ); ?>" type="text" readonly="readonly" class="regular-text" />
		</td>
	</tr>
	<tr valign="top">
		<?php

		$date = $subscription->get_cancel_date();

		$value = '';
		if ( $date ) {
			$value = date_i18n( 'D j M Y H:i:s', $date->format( 'U' ) );
		}

		?>
		<th scope="row">
			<label for="orbis_subscription_cancel_date"><?php esc_html_e( 'Cancel Date', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<input id="orbis_subscription_cancel_date" name="orbis_subscription_cancel_date" value="<?php echo esc_attr( $value ); ?>" type="text" readonly="readonly" class="regular-text" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_subscription_payment_method"><?php _e( 'Payment Method', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<?php

			$terms = wp_get_post_terms( $post->ID, 'orbis_payment_method' );

			$term = array_shift( $terms );

			wp_dropdown_categories( array(
				'name'             => 'tax_input[orbis_payment_method]',
				'show_option_none' => __( '— Select Payment Method —', 'orbis_subscriptions' ),
				'hide_empty'       => false,
				'selected'         => is_object( $term ) ? $term->term_id : false,
				'taxonomy'         => 'orbis_payment_method',
			) );

			?>
		</td>
	</tr>
</table>
