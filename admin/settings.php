<div class="wrap">
	<?php screen_icon( 'orbis' ); ?>

	<h2><?php echo get_admin_page_title(); ?></h2>
	
	<form method="post" action="options.php">
		<?php settings_fields( 'orbis_subscriptions' ); ?>

		<?php do_settings_sections( 'orbis_subscriptions' ); ?>
		
		<?php submit_button(); ?>
	</form>
</div>