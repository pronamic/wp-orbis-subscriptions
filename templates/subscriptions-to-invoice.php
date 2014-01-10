<?php

$date = date_parse( filter_input( INPUT_GET, 'date', FILTER_SANITIZE_STRING ) );

if ( ! $date['year'] ) {
    $date['year'] = date( 'Y' );
}

if ( ! $date['month'] ) {
    $date['month'] = date( 'm' );
}

$date_string = '01-' . $date['month'] . '-' . $date['year'];

?>

<form class="form-inline" action="" method="get">
    <div class="row">
        <div class="span2">
            <div class="btn-group">
                <a href="<?php echo add_query_arg( array( 'date' => date( 'd-m-Y', strtotime( $date_string . ' - 1 year' ) ) ) ); ?>" class="btn">&lt;&lt;</a>
                <a href="<?php echo add_query_arg( array( 'date' => date( 'd-m-Y', strtotime( $date_string . ' - 1 month' ) ) ) ); ?>" class="btn">&lt;</a>
                <a href="<?php echo add_query_arg( array( 'date' => date( 'd-m-Y', strtotime( $date_string . ' + 1 month' ) ) ) ); ?>" class="btn">&gt;</a>
                <a href="<?php echo add_query_arg( array( 'date' => date( 'd-m-Y', strtotime( $date_string . ' + 1 year' ) ) ) ); ?>" class="btn">&gt;&gt;</a>
                <a href="<?php echo remove_query_arg( array( 'date' ) ); ?>" class="btn"><?php _e( 'This month', 'orbis_subscriptions' ); ?></a>
            </div>
        </div>
    </div>
</form>
<hr />

<?php

global $orbis_subscriptions_to_invoice;

$results = $orbis_subscriptions_to_invoice;

$action = '';
if ( function_exists( 'twinfield_get_form_action' ) ) {
	$action = twinfield_get_form_action( 'invoice' );
}

?>

<h2><?php echo date( 'M Y', strtotime( $date_string ) ); ?></h2>
<form method="post" action="<?php echo esc_attr( $action ); ?>">
	<div class="panel">
		<div class="content">
			<input type="hidden" name="invoiceType" value="FACTUUR" />

			<button type="submit">Factuur maken</button>
		</div>
	</div>

    <?php foreach ( array( 'm' => __( 'Monthly subscriptions', 'orbis_subscriptions' ), 'Y' => __( 'Yearly subscriptions', 'orbis_subscriptions' ) ) as $duration => $duration_title ) : ?>

    <h3><?php echo $duration_title; ?></h3>
	<div class="panel">
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th scope="col"></th>
					<th scope="col"><?php _e( 'ID', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'Company', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'Subscription', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'Price', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'Name', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'Activation Date', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'Invoice Number', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'Notice', 'orbis_subscriptions' ); ?></th>
				</tr>
			</thead>
	
			<tbody>
	
				<?php foreach ( $results as $i => $result ) : ?>

                    <?php if ( $result->duration !== $duration ) continue; ?>
				
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

						$date_start = date_i18n( 'n M Y', $date_start->format( 'U' ) );
						$date_end   = date_i18n( 'n M Y', $date_end->format( 'U' ) );
						
						$freetext2 = sprintf( '%s tot %s', $date_start, $date_end );
						
						$freetext3 = '';

						?>
						<td>
							<input name="<?php printf( $name, $i, 'active' ); ?>" value="1" type="checkbox" />
						</td>
						<td>
							<?php echo $result->id; ?>
						</td>
						<td>
							<?php echo $result->company_name; ?>
						</td>
						<td>
							<input name="<?php printf( $name, $i, 'article' ); ?>" value="<?php echo $result->twinfield_article; ?>" type="hidden" />
							<?php echo $result->subscription_name; ?>
						</td>
						<td>
							<input name="<?php printf( $name, $i, 'quantity' ); ?>" value="1" type="hidden" />
							<input name="<?php printf( $name, $i, 'unitspriceexcl' ); ?>" value="<?php echo $result->price; ?>" type="hidden" />
							<?php echo $result->price; ?>
						</td>
						<td>
							<input name="<?php printf( $name, $i, 'freetext1' ); ?>" value="<?php echo $freetext1; ?>" type="hidden" />
							<?php echo $result->name; ?>
						</td>
						<td>
							<input name="<?php printf( $name, $i, 'freetext2' ); ?>" value="<?php echo $freetext2; ?>" type="hidden" />
							<input name="<?php printf( $name, $i, 'freetext3' ); ?>" value="<?php echo $freetext3; ?>" type="hidden" />
							<?php echo $result->activation_date; ?>
						</td>
						<td>
							<?php 
							
							$invoice_link = orbis_get_invoice_link( $result->invoice_number );
							
							if ( ! empty( $invoice_link ) ) {
								printf(
									'<a href="%s" target="_blank">%s</a>',
									esc_attr( $invoice_link ),
									$result->invoice_number
								);
							} else {
								echo $result->invoice_number;
							}
							
							?>
						</td>
						<td>
							<?php if ( $result->too_late ) : ?>
								<span class="text-error">!!!</span>
							<?php endif; ?>
						</td>
					</tr>
				
				<?php endforeach; ?>
	
			</tbody>
		</table>
	</div>

    <?php endforeach; ?>

</form>