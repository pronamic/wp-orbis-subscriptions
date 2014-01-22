<?php 

global $wpdb, $post;

$name        = get_post_meta( $post->ID, '_orbis_subscription_name', true );
$license_key = get_post_meta( $post->ID, '_orbis_subscription_license_key', true );

if ( true ) {
	$subscription =  $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->orbis_subscriptions WHERE post_id = %d;", $post->ID ) );

	if ( $subscription ) {
		$name            = $subscription->name;
		$license_key     = $subscription->license_key;
	}
}

?>
<?php do_action( 'orbis_email_header' ); ?>

<p>
	Geachte heer, mevrouw,
</p>

<p>
	Hierbij sturen wij uw licentiesleutel voor de 
	domeinnaam <strong><?php echo $name; ?></strong>:
</p>

<pre><?php echo $license_key; ?></pre>

<p>
	Met vriendelijke groet,
</p>

<p>
	Pronamic<br />
	<a href="mailto:support@pronamic.nl">support@pronamic.nl</a>
</p>

<?php do_action( 'orbis_email_footer' ); ?>