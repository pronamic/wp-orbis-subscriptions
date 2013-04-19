<div class="wrap">
	<?php screen_icon( 'orbis' ); ?>
	<h2><?php echo get_admin_page_title(); ?></h2>
	<form method="POST">
		<?php echo $nonce; ?>
		<?php submit_button( __( 'Send Reminder', 'orbis' ) ); ?>
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
				<?php if ( ! empty( $subscriptions ) ) : ?>
					<?php foreach ( $subscriptions as $subscription ) : ?>
						<tr class="subscription">
							<td><input name="subscription_ids[]" type="checkbox" value="<?php echo $subscription->get_post_id(); ?>" /></td>
							<td><?php echo $subscription->get_id(); ?></td>
							<td><?php echo $subscription->get_company_name(); ?></td>
							<td><?php echo $subscription->get_type_name(); ?></td>
							<td><?php echo $subscription->get_name(); ?></td>
							<td><?php echo $subscription->get_activation_date()->format( 'd-m-Y' ); ?></td>
							<td><?php echo $subscription->until_expiration_human(); ?></td>
							<td><?php echo $subscription->get_update_date()->format( 'd-m-Y' ); ?></td>
							<td><?php echo $subscription->get_type_price( '&euro;' ); ?></td>
							<td><?php echo $subscription->get_license_key(); ?></td>
							<td>
								<button class="button-primary" name="submit_single" type="submit" value="<?php echo $subscription->get_post_id(); ?>"><?php _e( 'Send Reminder', 'orbis' ); ?></button>
								<button class="button-secondary" name="submit_extend" type="submit" value="<?php echo $subscription->get_post_id(); ?>"><?php _e( 'Extend License', 'orbis' ); ?></button>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr>
						<td colspan="10"><?php _e( 'No subscriptions will expire within the next week', 'orbis' ); ?></td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
		<?php submit_button( __( 'Send Reminder', 'orbis' ) ); ?>
	</form>
</div>