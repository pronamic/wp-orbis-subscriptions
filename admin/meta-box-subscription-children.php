<?php
global $post;


?>

<p class="post-attributes-label-wrapper">
	<label for="orbis_subscription_id">
		<b><?php esc_html_e( 'Add Child Subscription', 'orbis_subscriptions' ); ?></b>
	</label>
</p>

<?php $person_id = get_post_meta( $post->ID, '_orbis_subscription_child_id', true ); ?>

<select id="orbis_subscription_id" name="_orbis_subscription_child_id" class="orbis-id-control orbis-subscription-id-control regular-text">
	<option>
		<?php echo esc_html_e( 'Select subscription to connect.', 'orbis_subscriptions' ); ?>
	</option>
</select>