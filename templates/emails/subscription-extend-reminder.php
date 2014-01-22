<?php 

global $wpdb, $post, $orbis_subscription;

$name            = get_post_meta( $post->ID, '_orbis_subscription_name', true );
$license_key     = get_post_meta( $post->ID, '_orbis_subscription_license_key', true );
$expiration_date = '';

if ( true ) {
	$subscription =  $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->orbis_subscriptions WHERE post_id = %d;", $post->ID ) );

	if ( $subscription ) {
		$name            = $subscription->name;
		$license_key     = $subscription->license_key;
		$expiration_date = $subscription->expiration_date;
	}
}

$extend_url = add_query_arg(
	array(
		'domain_name' => $name,
		'license'     => $license_key
	),
	get_option( 'orbis_subscriptions_update_url' )
);

?>
<?php do_action( 'orbis_email_header' ); ?>

<p>
	Geachte heer, mevrouw,
</p>

<p>
	Uw Pronamic iDEAL licentiesleutel verloopt over enkele dagen. 
	Om gebruik te kunnen blijven maken van de Pronamic iDEAL plugin 
	moet u uw licentiesleutel vernieuwen.
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
	Klik op de link hieronder om uw licentiesleutel te vernieuwen:
</p>

<p>
	<a href="<?php echo esc_attr( $extend_url ); ?>"><?php echo $extend_url; ?></a>
</p>

<p>
	Na uw betaling ontvangt u binnen 7 werkdagen per e-mail een factuur.
</p>

<p>
	Met vriendelijke groet,
</p>

<p>
	WP iDEAL <em>(initiatief van <a title="Pronamic Internet &amp; Marketing" href="http://www.pronamic.nl/">Pronamic</a>)</em><br />
	<a href="mailto:support@pronamic.nl">support@pronamic.nl</a>
</p>

<?php do_action( 'orbis_email_footer' ); ?>