<?php

use Pronamic\WordPress\Money\Money;

global $wpdb, $post;

wp_nonce_field( 'orbis_save_subscription_details', 'orbis_subscription_details_meta_box_nonce' );

$subscription = get_orbis_subscription( $post );

$product_id = $subscription->get_product_id();

$query = $wpdb->prepare( "SELECT * FROM $wpdb->orbis_subscription_products WHERE ( NOT deprecated OR id = %d ) ORDER BY name;", $product_id );

$subscription_products = $wpdb->get_results( $query, OBJECT_K );

$person_id    = get_post_meta( $post->ID, '_orbis_subscription_person_id', true );
$agreement_id = get_post_meta( $post->ID, '_orbis_subscription_agreement_id', true );

$person_name = empty( $person_id ) ? '' : get_the_title( $person_id );

$invoice_header_text      = get_post_meta( $post->ID, '_orbis_invoice_header_text', true );
$invoice_footer_text      = get_post_meta( $post->ID, '_orbis_invoice_footer_text', true );
$invoice_line_description = get_post_meta( $post->ID, '_orbis_invoice_line_description', true );

$utc = new \DateTimeZone( 'UTC' );

?>
<table class="form-table">
	<tr valign="top">
		<th scope="row">
			<label for="orbis_subscription_id"><?php esc_html_e( 'Orbis ID', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<input id="orbis_subscription_id" name="_orbis_subscription_id" value="<?php echo esc_attr( $subscription->get_id() ); ?>" type="text" class="regular-text" readonly="readonly" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_subscription_company"><?php esc_html_e( 'Company ID', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<select id="orbis_subscription_company" name="_orbis_subscription_company_id" class="orbis-id-control orbis_company_id_field regular-text">
				<option value="<?php echo esc_attr( $subscription->get_company_id() ); ?>">
					<?php echo esc_html( $subscription->get_company_name() ); ?>
				</option>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_subscription_type"><?php esc_html_e( 'Type', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<select id="orbis_subscription_type" name="_orbis_subscription_type_id">
				<option value=""></option>

				<?php

				foreach ( $subscription_products as $subscription_product ) {
					$price = new Money( $subscription_product->price, 'EUR' );

					$text = sprintf(
						'%s (%s)',
						$subscription_product->name,
						$price->format_i18n()
					);

					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $subscription_product->id ),
						selected( $subscription_product->id, $product_id, false ),
						esc_html( $text )
					);
				}

				?>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_subscription_name"><?php esc_html_e( 'Name', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<input id="orbis_subscription_name" name="_orbis_subscription_name" value="<?php echo esc_attr( $subscription->get_name() ); ?>" type="text" class="regular-text" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_subscription_person_id"><?php esc_html_e( 'Person', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<?php $person_id = get_post_meta( $post->ID, '_orbis_subscription_person_id', true ); ?>
			<select id="orbis_subscription_person_id" name="_orbis_subscription_person_id" class="orbis-id-control orbis-person-id-control regular-text">
				<option value="<?php echo esc_attr( $person_id ); ?>">
					<?php echo esc_html( $person_name ); ?>
				</option>
			</select>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="orbis_subscription_email"><?php esc_html_e( 'Email', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<input id="orbis_subscription_email" name="_orbis_subscription_email" value="<?php echo esc_attr( $subscription->get_email() ); ?>" type="text" class="regular-text" />
		</td>
	</tr>
	<tr valign="top">
		<th scope="row">
			<label for="_orbis_subscription_agreement_id">
				<?php esc_html_e( 'Agreement ID', 'orbis_subscriptions' ); ?>
			</label>
		</th>
		<td>
			<input size="5" type="text" id="_orbis_subscription_agreement_id" name="_orbis_subscription_agreement_id" value="<?php echo esc_attr( $agreement_id ); ?>" />

			<a id="choose-from-library-link" class="button"
				data-choose="<?php esc_attr_e( 'Choose an Agreement', 'orbis_subscriptions' ); ?>"
				data-type="<?php echo esc_attr( 'application/pdf, plain/text' ); ?>"
				data-element="<?php echo esc_attr( '_orbis_subscription_agreement_id' ); ?>"
				data-update="<?php esc_attr_e( 'Set as Agreement', 'orbis_subscriptions' ); ?>"><?php esc_html_e( 'Choose a Agreement', 'orbis_subscriptions' ); ?></a>

			<p class="description">
				<?php esc_html_e( 'You can select an .PDF or .TXT file from the WordPress media library.', 'orbis_subscriptions' ); ?><br />
				<?php esc_html_e( 'If you received the agreement by mail print the complete mail conversation with an PDF printer.', 'orbis_subscriptions' ); ?>
			</p>
		</td>
	</tr>
	<tr valign="top">
		<?php

		$date = $subscription->get_activation_date();

		$value = \wp_date( 'Y-m-d\TH:i:s' );

		if ( $date ) {
			$value = \wp_date( 'Y-m-d\TH:i:s', $date->getTimestamp() );
		}

		$readonly = '';

		if ( $subscription->count_invoices() > 0 ) {
			$readonly = 'readonly="readonly"';
		} 

		?>
		<th scope="row">
			<label for="orbis_subscription_activation_date"><?php esc_html_e( 'Activation Date', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<input id="orbis_subscription_activation_date" name="_orbis_subscription_activation_date" value="<?php echo esc_attr( $value ); ?>" type="datetime-local" <?php echo $readonly; ?> class="regular-text" />
		</td>
	</tr>
	<tr valign="top">
		<?php

		$date = $subscription->get_expiration_date();

		$value = '';
		if ( $date ) {
			$value = \wp_date( 'Y-m-d\TH:i:s', $date->getTimestamp() );
		}

		?>
		<th scope="row">
			<label for="orbis_subscription_expiration_date"><?php esc_html_e( 'Expiration Date', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<input id="orbis_subscription_expiration_date" name="_orbis_subscription_expiration_date" value="<?php echo esc_attr( $value ); ?>" type="datetime-local" readonly="readonly" class="regular-text" />
		</td>
	</tr>
	<tr valign="top">
		<?php

		$date = $subscription->get_cancel_date();

		$value = '';
		if ( $date ) {
			$value = \wp_date( 'Y-m-d\TH:i:s', $date->getTimestamp() );
		}

		?>
		<th scope="row">
			<label for="orbis_subscription_cancel_date"><?php esc_html_e( 'Cancel Date', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<input id="orbis_subscription_cancel_date" name="orbis_subscription_cancel_date" value="<?php echo esc_attr( $value ); ?>" type="datetime-local" readonly="readonly" class="regular-text" />
		</td>
	</tr>
	<?php
	$terms = get_the_terms( $post->ID, 'orbis_payment_method' );

	if ( ! is_wp_error( $terms ) ) :
		$term = ( false !== $terms ) ? array_shift( $terms ) : $terms;
	?>

		<tr valign="top">
			<th scope="row">
				<label for="orbis_subscription_payment_method"><?php esc_html_e( 'Payment Method', 'orbis_subscriptions' ); ?></label>
			</th>
			<td>
				<?php
					wp_dropdown_categories( array(
						'name'             => 'tax_input[orbis_payment_method]',
						'show_option_none' => __( '— Select Payment Method —', 'orbis_subscriptions' ),
						'hide_empty'       => false,
						'selected'         => is_object( $term ) ? $term->term_id : false,
						'taxonomy'         => 'orbis_payment_method',
					) );
				?>
			</td>
		</tr>

	<?php endif; ?>

	<tr>
		<th scope="row">
			<label for="_orbis_invoice_header_text"><?php esc_html_e( 'Invoice Header Text', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<textarea id="_orbis_invoice_header_text" name="_orbis_invoice_header_text" rows="2" cols="60"><?php echo esc_textarea( $invoice_header_text ); ?></textarea>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="_orbis_invoice_footer_text"><?php esc_html_e( 'Invoice Footer Text', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<textarea id="_orbis_invoice_footer_text" name="_orbis_invoice_footer_text" rows="2" cols="60"><?php echo esc_textarea( $invoice_footer_text ); ?></textarea>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<label for="_orbis_invoice_line_description"><?php esc_html_e( 'Invoice Line Description', 'orbis_subscriptions' ); ?></label>
		</th>
		<td>
			<input type="text" id="_orbis_invoice_line_description" name="_orbis_invoice_line_description" value="<?php echo esc_attr( $invoice_line_description ); ?>" class="regular-text" />
		</td>
	</tr>
</table>

<script type="text/javascript">
	( function( $ ) {
		$( document ).ready( function() {
			var frame;

			$('#choose-from-library-link').click( function( event ) {
				var $el = $( this );

				event.preventDefault();

				// If the media frame already exists, reopen it.
				if ( frame ) {
					frame.open();
					return;
				}

				// Create the media frame.
				frame = wp.media.frames.projectAgreement = wp.media( {
					// Set the title of the modal.
					title: $el.data( 'choose' ),

					// Tell the modal to show only images.
					library: {
						type: $el.data( 'type' ),
					},

					// Customize the submit button.
					button: {
						// Set the text of the button.
						text: $el.data( 'update' ),
						// Tell the button not to close the modal, since we're
						// going to refresh the page when the image is selected.
						close: false
					}
				} );

				// When an image is selected, run a callback.
				frame.on( 'select', function() {
					// Grab the selected attachment.
					var attachment = frame.state().get( 'selection' ).first();

					var element_id = $el.data( 'element' );

					$( "#" + element_id ).val( attachment.id );

					frame.close();
				} );

				// Finally, open the modal.
				frame.open();
			} );
		} );
	} )( jQuery );
</script>
