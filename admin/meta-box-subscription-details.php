<?php

global $wpdb, $post;

wp_nonce_field( 'orbis_save_subscription_details', 'orbis_subscription_details_meta_box_nonce' );

$subscription_types   = $wpdb->get_results( 'SELECT * FROM orbis_subscription_types', OBJECT_K );

$orbis_id   = get_post_meta( $post->ID, '_orbis_subscription_id', true );
$company_id = get_post_meta( $post->ID, '_orbis_subscription_company_id', true );
$type_id    = get_post_meta( $post->ID, '_orbis_subscription_type_id', true );
$name       = get_post_meta( $post->ID, '_orbis_subscription_name', true );

if ( empty( $orbis_id ) ) {
	$subscription =  $wpdb->get_row( $wpdb->prepare( "SELECT * FROM orbis_subscriptions WHERE post_id = %d;", $post->ID ) );

	if ( $subscription ) {
		$company_id = $subscription->company_id;
		$type_id    = $subscription->type_id;
		$name       = $subscription->name;
	}
}

?>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<label for="orbis_subscription_company"><?php _e( 'Company ID', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<input type="text" id="orbis_subscription_company" name="_orbis_subscription_company_id" value="<?php echo esc_attr( $company_id ); ?>" class="orbis_company_id_field regular-text" data-text="<?php echo esc_attr( $company_id ); ?>" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_subscription_type"><?php _e( 'Type', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			
			<select id="orbis_subscription_type" name="_orbis_subscription_type_id">
				<option value=""></option>

				<?php 
				
				foreach ( $subscription_types as $subscription_type ) {
					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $subscription_type->id ),
						selected( $subscription_type->id, $type_id, false ),
						$subscription_type->name
					);
				}
				
				?>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_subscription_name"><?php _e( 'Name', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<input id="orbis_subscription_name" name="_orbis_subscription_name" value="<?php echo $name; ?>" type="text" class="regular-text" />
		</td>
	</tr>
	
	<?php if ( false ) : ?>
	
		<tr valign="top">
			<th scope="row">
				<label for="orbis_subscription_activation_date"><?php _e( 'Activation Date', 'orbis_subscriptions' ); ?></label>
			</th>
			<td>
				<input id="orbis_subscription_activation_date" name="_orbis_subscription_activation_date" value="<?php echo get_post_meta( $post->ID, '_orbis_subscription_activation_date', true ); ?>" type="text" class="regular-text" />
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">
				<label for="orbis_subscription_expiration_date"><?php _e( 'Expiration Date', 'orbis_subscriptions' ); ?></label>
			</th>
			<td>
				<input id="orbis_subscription_expiration_date" name="_orbis_subscription_expiration_date" value="<?php echo get_post_meta( $post->ID, '_orbis_subscription_expiration_date', true ); ?>" type="text" class="regular-text" />
			</td>
		</tr>
	
	<?php endif; ?>

	<tr valign="top">
		<th scope="row">
			<label for="orbis_subscription_person_id"><?php _e( 'Person', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<input id="orbis_subscription_person_id" name="_orbis_subscription_person_id" value="<?php echo get_post_meta( $post->ID, '_orbis_subscription_person_id', true ); ?>" type="text" class="regular-text" />
		</td>
	</tr>
</table>