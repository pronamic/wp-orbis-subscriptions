<?php
global $wpdb, $post;

wp_nonce_field( 'orbis_save_subscription_parent', 'orbis_subscription_parent_meta_box_nonce' );

$parent = $wpdb->get_row( $wpdb->prepare("
	SELECT
		post_title,
		ID,
		guid
	FROM
		$wpdb->posts
	WHERE
		ID = %d
			AND
		post_status = 'publish'
	;",
	$post->post_parent
) );
?>

<div>
	<p class="post-attributes-label-wrapper">
		<label for="orbis_subscription_id">
			<b><?php esc_html_e( 'Choose Parent Subscription', 'orbis_subscriptions' ); ?></b>
		</label>
	</p>

	<?php $person_id = get_post_meta( $post->ID, '_orbis_subscription_parent_id', true ); ?>

	<select id="orbis_subscription_id" name="_orbis_subscription_parent_id" class="regular-text" data-post-suggest="orbis/subscriptions">
		<option value="<?php echo esc_attr( $subscription_id ); ?>">
			<?php echo esc_html( 'Choose a subscription', 'orbis_subscriptions' ); ?>
		</option>
	</select>
</div>

<?php if ( $parent ) : ?>
	<p class="post-attributes-label-wrapper">
		<label for="orbis_subscription_id">
			<b><?php esc_html_e( 'Parent Subscription', 'orbis_subscriptions' ); ?></b>
		</label>
	</p>

	<ul>
		<li><a href="<?php echo esc_url( $parent->guid ); ?>"><?php echo esc_html( $parent->post_title ); ?></a></li>
	</ul>
<?php endif ?>
