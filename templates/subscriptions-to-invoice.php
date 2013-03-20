<?php 

global $orbis_subscriptions_to_invoice;

$results = $orbis_subscriptions_to_invoice;

$action = '';
if ( function_exists( 'twinfield_get_form_action' ) ) {
	$action = twinfield_get_form_action( 'invoice' );
}

?>
<div class="panel">
	<form method="post" action="<?php echo esc_attr( $action ); ?>">
		<p>
			<input type="hidden" name="invoiceType" value="FACTUUR" />

			<button type="submit">Factuur maken</button>
		</p>

		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th scope="col"></th>
					<th scope="col">ID</th>
					<th scope="col">Company</th>
					<th scope="col">Subscription</th>
					<th scope="col">Price</th>
					<th scope="col">Name</th>
					<th scope="col">Activation Date</th>
					<th scope="col">Invoice Number</th>
					<th scope="col">Notice</th>
				</tr>
			</thead>
	
			<tbody>
	
				<?php foreach ( $results as $i => $result ) : ?>
				
					<tr>
						<?php 
						
						$name = 'lines[%d][%s]';
						
						$date_start = new DateTime( $result->activation_date );
						$date_end   = new DateTime( $result->activation_date );
						
						$year  = date( 'Y' );
						$month = $date_start->format( 'm' );
						$day   = $date_start->format( 'd' );

						$date_start->setDate( $year, $month, $day );
						$date_end->setDate( $year + 1, $month, $day );
						
						$freetext1 = $result->name;
						$freetext2 = date_i18n( 'n M Y', $date_start->format( 'U' ) );
						$freetext3 = date_i18n( 'n M Y', $date_end->format( 'U' ) );

						?>
						<td>
							<input name="<?php printf( $name, $i, 'active' ); ?>" value="true" type="checkbox" />
						</td>
						<td>
							<?php echo $result->id; ?>
						</td>
						<td>
							<?php echo $result->company_name; ?>
						</td>
						<td>
							<input name="<?php printf( $name, $i, 'article' ); ?>" value="<?php echo $result->twinfield_article; ?>" type="text" />
							<?php echo $result->subscription_name; ?>
						</td>
						<td>
							<input name="<?php printf( $name, $i, 'quantity' ); ?>" value="1" type="text" />
							<input name="<?php printf( $name, $i, 'unitspriceexcl' ); ?>" value="<?php echo $result->price; ?>" type="text" />
							<?php echo $result->price; ?>
						</td>
						<td>
							<input name="<?php printf( $name, $i, 'freetext1' ); ?>" value="<?php echo $freetext1; ?>" type="text" />
							<?php echo $result->name; ?>
						</td>
						<td>
							<input name="<?php printf( $name, $i, 'freetext2' ); ?>" value="<?php echo $freetext2; ?>" type="text" />
							<input name="<?php printf( $name, $i, 'freetext2' ); ?>" value="<?php echo $freetext3; ?>" type="text" />
							<?php echo $result->activation_date; ?>
						</td>
						<td>
							<?php echo $result->invoice_number; ?>
						</td>
						<td>
							<?php if ( $result->to_late ) : ?>
								<span class="text-error">!!!</span>
							<?php endif; ?>
						</td>
					</tr>
				
				<?php endforeach; ?>
	
			</tbody>
		</table>
	</form>
</div>