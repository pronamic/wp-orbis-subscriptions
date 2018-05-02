<?php
global $wpdb, $post;

wp_nonce_field( 'orbis_save_subscription_parent', 'orbis_subscription_parent_meta_box_nonce' );

if ( $post->post_parent ) {
	$parent = get_the_title( $post->post_parent );
} else {
	$parent = __( 'Choose a subscription', 'orbis_subscriptions' );
}
?>

<div>
	<p class="post-attributes-label-wrapper">
		<label for="orbis_subscription_id">
			<b><?php esc_html_e( 'Choose Parent Subscription', 'orbis_subscriptions' ); ?></b>
		</label>
	</p>

	<select id="orbis_subscription_id" name="_orbis_subscription_parent_id" class="regular-text" data-post-suggest="orbis/subscriptions">
		<option value="<?php echo esc_attr( $subscription_id ); ?>">
			<?php echo esc_html( $parent ); ?>
		</option>
	</select>
</div>
