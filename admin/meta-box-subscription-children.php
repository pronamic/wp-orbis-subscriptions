<?php
global $wpdb, $post;

wp_nonce_field( 'orbis_save_subscription_children', 'orbis_subscription_children_meta_box_nonce' );

$child_subscriptions = $wpdb->get_results( $wpdb->prepare("
	SELECT
		post_title,
		ID,
		guid
	FROM
		$wpdb->posts
	WHERE
		post_parent = %d
			AND
		post_status = 'publish'
	;",
	$post->ID
) );

?>

<div>
	<p class="post-attributes-label-wrapper">
		<label for="orbis_subscription_id">
			<b><?php esc_html_e( 'Add Child Subscription', 'orbis_subscriptions' ); ?></b>
		</label>
	</p>

	<?php $person_id = get_post_meta( $post->ID, '_orbis_subscription_child_id', true ); ?>

	<select id="orbis_subscription_id" name="_orbis_subscription_child_id" class="orbis-id-control orbis-subscription-id-control regular-text">
		<option value="<?php echo esc_attr( $subscription_id ); ?>">
			<?php echo esc_html( 'Choose a subscription', 'orbis_subscriptions' ); ?>
		</option>
	</select>
</div>

<?php if ( $child_subscriptions ) : ?>
	<p class="post-attributes-label-wrapper">
		<label for="orbis_subscription_id">
			<b><?php esc_html_e( 'Child Subscriptions', 'orbis_subscriptions' ); ?></b>
		</label>
	</p>

	<ul>
		<?php foreach ( $child_subscriptions as $child ): ?>
			<li><a href="<?php echo $child->guid ?>"><?php echo $child->post_title ?></a></li>
		<?php endforeach ?>
	</ul>
<?php endif ?>
