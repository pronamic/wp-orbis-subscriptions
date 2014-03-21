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
	<div class="btn-group">
		<a href="<?php echo add_query_arg( array( 'date' => date( 'd-m-Y', strtotime( $date_string . ' - 1 month' ) ) ) ); ?>" class="btn btn-default">&lt;</a>
		<a href="<?php echo add_query_arg( array( 'date' => date( 'd-m-Y', strtotime( $date_string . ' + 1 month' ) ) ) ); ?>" class="btn btn-default">&gt;</a>
		<a href="<?php echo remove_query_arg( array( 'date' ) ); ?>" class="btn btn-default"><?php _e( 'This month', 'orbis_subscriptions' ); ?></a>
	</div>

	<div class="pull-right">
		<select name="interval" class="form-control">
			<option value=""></option>
			<option value="Y" <?php selected( $interval, 'Y' ); ?>><?php _e( 'Yearly', 'orbis_subscriptions' ); ?></option>
			<option value="M" <?php selected( $interval, 'M' ); ?>><?php _e( 'Monthly', 'orbis_subscriptions' ); ?></option>
		</select>

		<button class="btn btn-default" type="submit">Filter</button>
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

    <h3><?php _e( 'Subscriptions', 'orbis_subscriptions' ); ?></h3>

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
					<th scope="col"><?php _e( 'Start Date', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'End Date', 'orbis_subscriptions' ); ?></th>
					<th scope="col"><?php _e( 'Invoice Number', 'orbis_subscriptions' ); ?></th>
				</tr>
			</thead>
	
			<tbody>
	
				<?php foreach ( $results as $i => $result ) : ?>

					<?php 
					
					$classes = array();
					if ( $result->too_late ) {
						$classes[] = 'warning';
					}
					
					?>
					<tr class="<?php echo implode( ' ', $classes ); ?>">
						<?php 
						
						$name = 'lines[%d][%s]';
						
						$date_start = new DateTime( $result->activation_date );
						$date_end   = new DateTime( $result->activation_date );

                        $day = $date_start->format( 'd' );

                        if ( $result->interval === 'Y' ) {
                            $date_end_timestamp = strtotime( $date_string . ' + 1 year' );
                        } else if ( $result->interval === 'M' ) {
                            $date_end_timestamp = strtotime( $date_string . ' + 1 month' );
                        } else {
                            $date_end_timestamp = strtotime( $date_string );
                        }

                        $date_start->setDate( $date['year'], $date['month'], $day );
                        $date_end->setDate( date( 'Y', $date_end_timestamp ), date( 'm', $date_end_timestamp ), $day );
						
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

							<a href="<?php echo get_permalink( $result->post_id ); ?>">
								<?php echo $result->subscription_name; ?>
							</a>
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
							<?php echo $result->invoice_start_date; ?>
						</td>
						<td>
							<?php echo $result->invoice_end_date; ?>
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
					</tr>
				
				<?php endforeach; ?>
	
			</tbody>
		</table>
	</div>
</form>