<?php

global $wpdb, $post;

$orbis_id        = get_post_meta( $post->ID, '_orbis_subscription_id', true );
$company_id      = get_post_meta( $post->ID, '_orbis_subscription_company_id', true );
$type_id         = get_post_meta( $post->ID, '_orbis_subscription_type_id', true );
$name            = get_post_meta( $post->ID, '_orbis_subscription_name', true );
$license_key     = get_post_meta( $post->ID, '_orbis_subscription_license_key', true );
$activation_date = get_post_meta( $post->ID, '_orbis_subscription_activation_date', true );
$expiration_date = get_post_meta( $post->ID, '_orbis_subscription_activation_date', true );
$cancel_date     = get_post_meta( $post->ID, '_orbis_subscription_cancel_date', true );

if ( true ) { // empty( $orbis_id ) ) {
	$subscription =  $wpdb->get_row( $wpdb->prepare( "SELECT * FROM orbis_subscriptions WHERE post_id = %d;", $post->ID ) );

	if ( $subscription ) {
		$orbis_id        = $subscription->id;
		$company_id      = $subscription->company_id;
		$type_id         = $subscription->type_id;
		$name            = $subscription->name;
		$license_key     = $subscription->license_key;
		$activation_date = $subscription->activation_date;
		$expiration_date = $subscription->expiration_date;
		$cancel_date     = $subscription->cancel_date;
	}
}

?>
<div class="panel">
	<header>
		<h3><?php _e( 'Subscription Details', 'orbis_subscriptions' ); ?></h3>
	</header>

	<div class="content">
		<dl>
			<dt><?php _e( 'Orbis ID', 'orbis_subscriptions' ); ?></dt>
			<dd><?php echo $orbis_id; ?></dd>

			<dt><?php _e( 'License Key', 'orbis_subscriptions' ); ?></dt>
			<dd><?php echo $license_key; ?></dd>

			<dt><?php _e( 'Activation Date', 'orbis_subscriptions' ); ?></dt>
			<dd><?php echo date_i18n( 'D j M Y H:i:s', strtotime( $activation_date ) ); ?></dd>

			<dt><?php _e( 'Expiration Date', 'orbis_subscriptions' ); ?></dt>
			<dd><?php echo date_i18n( 'D j M Y H:i:s', strtotime( $activation_date ) ); ?></dd>

			<dt><?php _e( 'Cancel Date', 'orbis_subscriptions' ); ?></dt>
			<dd><?php echo date_i18n( 'D j M Y H:i:s', strtotime( $activation_date ) ); ?></dd>
		</dl>
	</div>
</div>