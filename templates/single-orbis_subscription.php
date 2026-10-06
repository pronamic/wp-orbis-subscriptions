<?php
/**
 * Single subscription
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Subscriptions
 */

namespace Pronamic\Orbis\Subscriptions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

\get_header();

while ( \have_posts() ) :
	\the_post();

	$content = \get_the_content();

	?>
	<div id="post-<?php \the_ID(); ?>" <?php \post_class(); ?>>
		<div class="row">
			<div class="col-md-8">
				<?php \do_action( 'orbis_before_main_content' ); ?>

				<?php if ( \has_post_thumbnail() || '' !== \trim( $content ) ) : ?>

					<div class="card mb-3">
						<div class="card-header"><?php \esc_html_e( 'Description', 'orbis-subscriptions' ); ?></div>

						<div class="card-body">
							<?php if ( \has_post_thumbnail() ) : ?>

								<div class="thumbnail mb-3">
									<?php \the_post_thumbnail( 'thumbnail' ); ?>
								</div>

							<?php endif; ?>

							<?php \the_content(); ?>
						</div>
					</div>

				<?php endif; ?>

				<?php \do_action( 'orbis_after_main_content' ); ?>

				<?php \comments_template( '', true ); ?>
			</div>

			<div class="col-md-4">
				<?php \do_action( 'orbis_before_side_content' ); ?>

				<div class="card mb-3">
					<div class="card-header"><?php \esc_html_e( 'Additional Information', 'orbis-subscriptions' ); ?></div>

					<div class="card-body">
						<dl>
							<dt><?php \esc_html_e( 'Posted on', 'orbis-subscriptions' ); ?></dt>
							<dd><?php echo \esc_html( (string) \get_the_date() ); ?></dd>

							<dt><?php \esc_html_e( 'Posted by', 'orbis-subscriptions' ); ?></dt>
							<dd><?php echo \esc_html( \get_the_author() ); ?></dd>

							<?php if ( null !== \get_edit_post_link() ) : ?>

								<dt><?php \esc_html_e( 'Actions', 'orbis-subscriptions' ); ?></dt>
								<dd><?php \edit_post_link( \__( 'Edit', 'orbis-subscriptions' ) ); ?></dd>

							<?php endif; ?>
						</dl>
					</div>
				</div>

				<?php \do_action( 'orbis_after_side_content' ); ?>
			</div>
		</div>
	</div>
	<?php

endwhile;

\get_footer();
