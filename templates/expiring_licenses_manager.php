<div class="wrap">
	<?php screen_icon( 'orbis' ); ?>
	<h2><?php echo get_admin_page_title(); ?></h2>
	<table class="widefat">
		<thead>
			<tr>
				<th scope="col" class="manage-column check-column"><input type="checkbox"/></th>
				<th scope="col" class="manage-column" style="width:3em;"><?php _e( 'ID', 'orbis' ); ?></th>
				<th scope="col" class="manage-column"><?php _e( 'Company', 'orbis' ); ?></th>
				<th scope="col" class="manage-column"><?php _e( 'Type', 'orbis' ) ?></th>
				<th scope="col" class="manage-column"><?php _e( 'Name', 'orbis' ) ?></th>
				<th scope="col" class="manage-column"><?php _e( 'Activation Date', 'orbis' ) ?></th>
				<th scope="col" class="manage-column"><?php _e( 'Expiration Date', 'orbis' ) ?></th>
				<th scope="col" class="manage-column"><?php _e( 'Update Date', 'orbis' ) ?></th>
				<th scope="col" class="manage-column"><?php _e( 'Price', 'orbis' ) ?></th>
				<th scope="col" class="manage-column"><?php _e( 'License Key', 'orbis' ) ?></th>
				<th scope="col" class="manage-column"><?php _e( 'Actions', 'orbis' ) ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $subscriptions as $subscription ) : ?>
				<tr class="subscription">
					<td><input name="subscription_ids[]" type="checkbox" value="<?php echo $subscription->get_id(); ?>" /></td>
					<td><?php echo $subscription->get_id(); ?></td>
					<td><?php echo $subscription->get_company_name(); ?></td>
					<td><?php echo $subscription->get_type_name(); ?></td>
					<td><?php echo $subscription->get_name(); ?></td>
					<td><?php echo $subscription->get_activation_date()->format( 'd-m-Y' ); ?></td>
					<td><?php echo human_time_diff( $subscription->get_expiration_date()->format( 'U' ) ); ?></td>
					<td><?php echo $subscription->get_update_date()->format( 'd-m-Y' ); ?></td>
					<td><?php echo $subscription->get_type_price(); ?></td>
					<td><?php echo $subscription->get_license_key(); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>