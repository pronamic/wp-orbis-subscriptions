<div class="wrap">
	<?php screen_icon( 'orbis' ); ?>
	<h2><?php echo get_admin_page_title(); ?></h2>
	<form method="POST">
		<?php echo $nonce; ?>
		<?php submit_button( __( 'Send Reminder', 'orbis_subscriptions' ) ); ?>
		<table class="widefat">
			<thead>
				<tr>
					<th scope="col" class="manage-column check-column"><input type="checkbox"/></th>
					<th scope="col" class="manage-column" style="width:3em;"><?php _e( 'ID', 'orbis_subscriptions' ); ?></th>
					<th scope="col" class="manage-column"><?php _e( 'Company', 'orbis_subscriptions' ); ?></th>
					<th scope="col" class="manage-column"><?php _e( 'Type', 'orbis_subscriptions' ) ?></th>
					<th scope="col" class="manage-column"><?php _e( 'Name', 'orbis_subscriptions' ) ?></th>
					<th scope="col" class="manage-column"><?php _e( 'Activation Date', 'orbis_subscriptions' ) ?></th>
					<th scope="col" class="manage-column"><?php _e( 'Expiration Date', 'orbis_subscriptions' ) ?></th>
					<th scope="col" class="manage-column"><?php _e( 'Update Date', 'orbis_subscriptions' ) ?></th>
					<th scope="col" class="manage-column"><?php _e( 'Price', 'orbis_subscriptions' ) ?></th>
					<th scope="col" class="manage-column"><?php _e( 'License Key', 'orbis_subscriptions' ) ?></th>
					<th scope="col" class="manage-column"><?php _e( 'Sent', 'orbis_subscriptions' ); ?></th>
					<th scope="col" class="manage-column"><?php _e( 'E-Mail', 'orbis_subscriptions' ); ?></th>
					<th scope="col" class="manage-column"><?php _e( 'Actions', 'orbis_subscriptions' ) ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! empty( $subscriptions ) ) : ?>
					<?php $datetime_zone = new DateTimeZone( 'Europe/Amsterdam' ); ?>
					<?php $days	 = new DateInterval( 'P2D' ); ?>
					<?php $now = new DateTime( '', $datetime_zone ); ?>
					<?php foreach ( $subscriptions as $subscription ) : ?>
						<?php if ( $subscription->since_last_reminder( $days, $now ) ) : ?>
							<tr class="subscription">
								<td><input name="subscription_ids[]" type="checkbox" value="<?php echo $subscription->get_post_id(); ?>" /></td>
								<td><?php echo $subscription->get_id(); ?></td>
								<td><?php echo $subscription->get_company_name(); ?></td>
								<td><?php echo $subscription->get_type_name(); ?></td>
								<td>
									<a href="<?php echo get_permalink( $subscription->get_post_id() ); ?>" target="_blank">
										<?php echo $subscription->get_name(); ?>
									</a>

									<div class="row-actions">
										<span class="edit">
											<a href="<?php echo get_edit_post_link( $subscription->get_post_id() ); ?>" target="_blank">
												<?php _e( 'Edit', 'orbis_subscriptions' ); ?>
											</a>
										</span>
									</div>
								</td>
								<td><?php echo $subscription->get_activation_date()->setTimezone( $datetime_zone )->format( 'd-m-Y' ); ?></td>
								<td><?php echo $subscription->until_expiration_human(); ?></td>
								<td><?php echo $subscription->get_update_date()->setTimezone( $datetime_zone )->format( 'd-m-Y @ H:i' ); ?></td>
								<td><?php echo $subscription->get_type_price( '&euro;' ); ?></td>
								<td><?php echo $subscription->get_license_key(); ?></td>
								<td><?php echo $subscription->get_sent_notifications(); ?></td>
								<td><?php echo $subscription->get_email(); ?></td>
								<td>
									<button class="button-primary" name="submit_single" type="submit" value="<?php echo $subscription->get_post_id(); ?>"><?php _e( 'Send Reminder', 'orbis_subscriptions' ); ?></button>
									<button class="button-secondary" name="submit_extend" type="submit" value="<?php echo $subscription->get_post_id(); ?>"><?php _e( 'Extend License', 'orbis_subscriptions' ); ?></button>
								</td>
							</tr>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php else: ?>
					<tr>
						<td colspan="10"><?php _e( 'No subscriptions will expire within the next week', 'orbis_subscriptions' ); ?></td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
		<?php submit_button( __( 'Send Reminder', 'orbis_subscriptions' ) ); ?>
	</form>
</div>