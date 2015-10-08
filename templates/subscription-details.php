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
$email           = get_post_meta( $post->ID, '_orbis_subscription_email', true );

$subscription = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->orbis_subscriptions WHERE post_id = %d;", $post->ID ) );

$orbis_id        = $subscription->id;
$company_id      = $subscription->company_id;
$type_id         = $subscription->type_id;
$name            = $subscription->name;
$license_key     = $subscription->license_key;
$activation_date = $subscription->activation_date;
$expiration_date = $subscription->expiration_date;
$cancel_date     = $subscription->cancel_date;

$company_post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->orbis_companies WHERE id = %d;", $company_id ) );

?>
<div class="panel">
	<header>
		<h3><?php esc_html_e( 'Subscription Details', 'orbis_subscriptions' ); ?></h3>
	</header>

	<div class="content">
		<dl>
			<dt><?php esc_html_e( 'Company', 'orbis_subscriptions' ); ?></dt>
			<dd>
				<a href="<?php echo esc_attr( get_permalink( $company_post_id ) ); ?>"><?php echo esc_html( get_the_title( $company_post_id ) ); ?></a>
			</dd>

			<dt><?php esc_html_e( 'Activation Date', 'orbis_subscriptions' ); ?></dt>
			<dd><?php echo esc_html( date_i18n( 'D j M Y H:i:s', strtotime( $activation_date ) ) ); ?></dd>

			<dt><?php esc_html_e( 'Expiration Date', 'orbis_subscriptions' ); ?></dt>
			<dd><?php echo esc_html( date_i18n( 'D j M Y H:i:s', strtotime( $expiration_date ) ) ); ?></dd>

			<?php if ( ! empty( $cancel_date ) ) : ?>
	
				<dt><?php esc_html_e( 'Cancel Date', 'orbis_subscriptions' ); ?></dt>
				<dd><?php echo esc_html( date_i18n( 'D j M Y H:i:s', strtotime( $cancel_date ) ) ); ?></dd>

			<?php endif; ?>

			<?php if ( ! empty( $license_key ) ) : ?>
	
				<dt><?php esc_html_e( 'License Key', 'orbis_subscriptions' ); ?></dt>
				<dd><?php echo esc_html( $license_key ); ?></dd>

			<?php endif; ?>

			<dt><?php esc_html_e( 'Price', 'orbis_subscriptions' ); ?></dt>
			<dd><?php orbis_subscription_the_price(); ?></dd>
		</dl>

		<form method="post" action="">
			<?php wp_nonce_field( 'orbis_subscription_mail_license_key', 'orbis_subscriptions_nonce' ); ?>

			<div class="input-group">
				<input name="orbis_subscription_subject" type="hidden" value="<?php echo esc_attr__( 'Pronamic iDEAL License Key', 'orbis_subscriptions' ); ?>" />
				<input name="orbis_subscription_email" type="email" value="<?php echo esc_attr( $email ); ?>" placeholder="<?php echo esc_attr__( 'Email', 'orbis_subscriptions' ); ?>" class="form-control" />

				<span class="input-group-btn">
					<button name="orbis_subscription_mail" type="submit" class="btn btn-default"><?php esc_html_e( 'Send License', 'orbis_subscriptions' ); ?></button>
				</span>
			</div>
		</form>
	</div>
</div>
