<?php 

global $wpdb, $post;

$name            = get_post_meta( $post->ID, '_orbis_subscription_name', true );
$license_key     = get_post_meta( $post->ID, '_orbis_subscription_license_key', true );
$expiration_date = '';

if ( true ) {
	$subscription =  $wpdb->get_row( $wpdb->prepare( "SELECT * FROM orbis_subscriptions WHERE post_id = %d;", $post->ID ) );

	if ( $subscription ) {
		$name            = $subscription->name;
		$license_key     = $subscription->license_key;
		$expiration_date = $subscription->expiration_date;
	}
}

?>
<?php do_action( 'orbis_email_header' ); ?>

<p>
	Geachte heer, mevrouw,
</p>

<p>
	Gefeliciteerd, de Pronamic iDEAL licentie is succesvol verlengd. Hiermee 
	kunt u weer een jaar lang profiteren van updates en support. De 
	licentiesleutel blijft ongewijzigd dus er hoeft niks gewijzigd te worden.
</p>

<table>
	<tbody>
		<tr>
			<th scope="row">Domeinnaam</th>
			<td><?php echo $name; ?></td>
		</tr>
		<tr>
			<th scope="row">Licentiesleutel</th>
			<td><pre><?php echo $license_key; ?></pre></td>
		</tr>
		<tr>
			<th scope="row">Vervaldatum</th>
			<td><?php echo mysql2date( 'D j M Y H:i:s', $expiration_date ); ?></td>
		</tr>
	</tbody>
</table>

<p>
	Met vriendelijke groet,
</p>

<p>
	Pronamic<br />
	<a href="mailto:support@pronamic.nl">support@pronamic.nl</a>
</p>

<?php do_action( 'orbis_email_footer' ); ?>