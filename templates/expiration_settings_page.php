<div class="wrap">

	<?php screen_icon('orbis'); ?>

	<h2>
		<?php _e('Expiration settings', 'pronamic-ideal-license-manager'); ?>
	</h2>
	
	<form method="post" action="options.php">
		
		<?php settings_fields('orbis_subscriptions'); ?>
		
		<table cellspacing="0">
			
			<tr>
				<th colspan="3"><?php _e('E-Mail settings', 'pronamic-ideal-license-manager'); ?></th>
			</tr>
			
			<tr>
				<td><?php _e('URL', 'pronamic-ideal-license-manager'); ?></td>
				<td>
					<input 
						type="text"
						name="orbis_subscriptions_update_url"
						value="<?php echo $url ?>"
						size="50"
					/>
					<label for="pronamic-ideal-license-expiration-mail-renew-licence-url">
						<?php _e('The URL linking to the webpage the user can renew its subscription', 'pronamic-ideal-license-manager'); ?>
					</label>
				</td>
				<td></td>
			</tr>
			
			<tr>
				<td><?php _e('Mail subject', 'pronamic-ideal-license-manager'); ?></td>
				<td>
					<input 
						type="text"
						name="orbis_subscriptions_mail_subject"
						value="<?php echo $mail_subject; ?>"
						size="100"
					/>
				</td>
				<td></td>
			</tr>
			
			<tr>
				<td><?php _e('Mail template', 'pronamic-ideal-license-manager'); ?></td>
				<td>
					<textarea 
						name="orbis_subscriptions_mail_body"
						cols="150"
						rows="20"
					><?php echo $mail_contents ?></textarea>
				</td>
				<td>
					<?php _e('The following variable tags can be used in the template:', 'pronamic-ideal-license-manager'); ?><br /><br />
					<?php foreach( array( '%company_name%', '%days_to_expiration%', '%renew_license_url%' ) as $tag ): ?>
						<i><?php echo $tag; ?></i><br />
					<?php endforeach; ?>
				</td>
			</tr>
			
			<tr>
				<th colspan="3"><?php _e('Reminder settings', 'pronamic-ideal-license-manager'); ?></th>
			</tr>
			
			<tr>
				<td><?php _e('', 'pronamic-ideal-license-manager'); ?></td>
				<td>
					<input 
						type="text"
						name="orbis_subscriptions_days_before_expiration"
						value="<?php echo $remind_days_before_expiration; ?>"
						size="50"
					/>
					<label for="orbis_subscriptions_days_before_expiration">
						<?php _e('Number of days within expiration date to start reminding users of their expiring licenses', 'pronamic-ideal-license-manager'); ?>
					</label>
				</td>
				<td></td>
			</tr>
			
			<tr>
				<td><?php _e('', 'pronamic-ideal-license-manager'); ?></td>
				<td>
					<input 
						type="text"
						name="orbis_subscriptions_maximum_reminders"
						value="<?php echo $max_number_of_reminders; ?>"
						size="50"
					/>
					<label for="orbis_subscriptions_maximum_reminders">
						<?php _e('Maximum number of reminders', 'pronamic-ideal-license-manager'); ?>
					</label>
				</td>
				<td></td>
			</tr>
			
			<tr>
				<td><?php _e('', 'pronamic-ideal-license-manager'); ?></td>
				<td>
					<input 
						type="text"
						name="orbis_subscriptions_between_reminders"
						value="<?php echo $minimum_days_between_reminders; ?>"
						size="50"
					/>
					<label for="orbis_subscriptions_between_reminders">
						<?php _e('Minimum number of days between reminders', 'pronamic-ideal-license-manager'); ?>
					</label>
				</td>
				<td></td>
			</tr>
			
		</table>
		
		<?php submit_button(); ?>
		
	</form>

</div>