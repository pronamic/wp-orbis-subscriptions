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

$query = $wpdb->prepare( "
	SELECT
		subscription.*,
		product.time_per_year
	FROM
		$wpdb->orbis_subscriptions AS subscription
			INNER JOIN
		$wpdb->orbis_subscription_products AS product
				ON subscription.type_id = product.id
	WHERE
		subscription.post_id = %d
	LIMIT
		1
	;",
	$post->ID
);

$subscription = $wpdb->get_row( $query );

$orbis_id        = $subscription->id;
$company_id      = $subscription->company_id;
$type_id         = $subscription->type_id;
$name            = $subscription->name;
$activation_date = $subscription->activation_date;
$expiration_date = $subscription->expiration_date;
$cancel_date     = $subscription->cancel_date;

$company_post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->orbis_companies WHERE id = %d;", $company_id ) );

$invoice_header_text      = get_post_meta( $post->ID, '_orbis_invoice_header_text', true );
$invoice_footer_text      = get_post_meta( $post->ID, '_orbis_invoice_footer_text', true );
$invoice_line_description = get_post_meta( $post->ID, '_orbis_invoice_line_description', true );

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

				<dt><?php esc_html_e( 'Status', 'orbis_subscriptions' ); ?></dt>
				<dd>
					<?php get_template_part( 'templates/subscription-badges' ); ?>
				</dd>

				<dt><?php esc_html_e( 'Activation Date', 'orbis_subscriptions' ); ?></dt>
				<dd><?php echo esc_html( date_i18n( 'D j M Y H:i:s', strtotime( $activation_date ) ) ); ?></dd>

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

				<?php if ( ! empty( $invoice_header_text ) ) : ?>

					<dt><?php esc_html_e( 'Invoice Header Text', 'orbis_subscriptions' ); ?></dt>
					<dd>
						<?php echo nl2br( esc_html( $invoice_header_text ) ); ?></a>
					</dd>

				<?php endif; ?>

				<?php if ( ! empty( $invoice_footer_text ) ) : ?>

					<dt><?php esc_html_e( 'Invoice Footer Text', 'orbis_subscriptions' ); ?></dt>
					<dd>
						<?php echo nl2br( esc_html( $invoice_footer_text ) ); ?></a>
					</dd>

				<?php endif; ?>

				<?php if ( ! empty( $invoice_line_description ) ) : ?>

					<dt><?php esc_html_e( 'Invoice Line Description', 'orbis_subscriptions' ); ?></dt>
					<dd>
						<?php echo nl2br( esc_html( $invoice_line_description ) ); ?></a>
					</dd>

				<?php endif; ?>

				<?php

				$agreement_id = get_post_meta( get_the_ID(), '_orbis_subscription_agreement_id', true );

				if ( ! empty( $agreement_id ) ) :
					$agreement = get_post( $agreement_id );
				?>

					<dt><?php esc_html_e( 'Agreement', 'orbis_subscriptions' ); ?></dt>
					<dd>
						<a href="<?php echo esc_attr( get_permalink( $agreement ) ); ?>"><?php echo get_the_title( $agreement ); ?></a>
					</dd>

				<?php endif; ?>

				<?php if ( null !== $subscription->time_per_year ) : ?>

					<dt><?php esc_html_e( 'Available time per year', 'orbis_subscriptions' ); ?></dt>
					<dd>
						<?php echo esc_html( orbis_time( $subscription->time_per_year ) ); ?>
					</dd>

				<?php endif; ?>
			</dl>
		</div>
	</div>

</div>
