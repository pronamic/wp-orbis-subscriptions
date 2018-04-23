<?php

global $wpdb, $post;

$orbis_id        = get_post_meta( $post->ID, '_orbis_subscription_id', true );
$company_id      = get_post_meta( $post->ID, '_orbis_subscription_company_id', true );
$type_id         = get_post_meta( $post->ID, '_orbis_subscription_type_id', true );
$name            = get_post_meta( $post->ID, '_orbis_subscription_name', true );
$activation_date = get_post_meta( $post->ID, '_orbis_subscription_activation_date', true );
$expiration_date = get_post_meta( $post->ID, '_orbis_subscription_expiration_date', true );
$cancel_date     = get_post_meta( $post->ID, '_orbis_subscription_cancel_date', true );
$email           = get_post_meta( $post->ID, '_orbis_subscription_email', true );
$keychain_id     = get_post_meta( $post->ID, '_orbis_subscription_keychain_id', true );

$keychain = $wpdb->get_row( $wpdb->prepare( "
	SELECT
		keychain.post_title AS name,
		keychain.guid AS url
	FROM
		$wpdb->posts AS keychain
	WHERE
		keychain.id = %d",
	$keychain_id
	)
);

$subscription = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->orbis_subscriptions WHERE post_id = %d;", $post->ID ) );

$orbis_id        = $subscription->id;
$company_id      = $subscription->company_id;
$type_id         = $subscription->type_id;
$name            = $subscription->name;
$activation_date = $subscription->activation_date;
$expiration_date = $subscription->expiration_date;
$cancel_date     = $subscription->cancel_date;

$company_post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->orbis_companies WHERE id = %d;", $company_id ) );

$args = array(
	'post_parent' => $post->ID,
	'post_type'   => 'orbis_subscription',
);

$children = get_children( $args ); //phpcs:ignore WordPress.VIP.RestrictedFunctions.get_posts_get_children

?>
<div class="card mb-3">
	<div class="card-header"><?php esc_html_e( 'Subscription Details', 'orbis_subscriptions' ); ?></div>
	<div class="card-body">

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

				<dt><?php esc_html_e( 'Price', 'orbis_subscriptions' ); ?></dt>
				<dd><?php orbis_subscription_the_price(); ?></dd>

				<?php if ( has_term( null, 'orbis_payment_method' ) ) : ?>

					<dt><?php esc_html_e( 'Payment Method', 'orbis_subscriptions' ); ?></dt>
					<dd><?php the_terms( null, 'orbis_payment_method' ); ?></dd>

				<?php endif; ?>

				<?php if ( ! empty( $keychain ) ) : ?>

					<dt><?php esc_html_e( 'Connected Keychain', 'orbis_subscriptions' ); ?></dt>
					<dd><a href="<?php echo esc_html( $keychain->url ); ?>"></a><?php echo esc_html( $keychain->name ); ?></dd>

				<?php endif; ?>

				<?php if ( $children ) : ?>
					<dt><?php esc_html_e( 'Child Subscriptions', 'orbis_subscriptions' ); ?></dt>
					<dd>
						<?php foreach ( $children as $child ) : ?>
							<li><a href="<?php echo esc_url( $child->guid ); ?>"><?php echo esc_html( $child->post_title ); ?></a></li>
						<?php endforeach ?>
					</dd>
				<?php endif; ?>
			</dl>
		</div>
	</div>

</div>
