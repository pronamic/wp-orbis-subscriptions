<?php
/**
 * Archive subscriptions
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\Orbis\Subscriptions
 */

namespace Pronamic\Orbis\Subscriptions;

use DateTimeImmutable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

\get_header();

?>
<div class="card">
	<?php \get_template_part( 'templates/search_form' ); ?>

	<?php if ( \have_posts() ) : ?>

		<div class="table-responsive">
			<table class="table table-striped table-condense table-hover">
				<thead>
					<tr>
						<th><?php \esc_html_e( 'Title', 'orbis-subscriptions' ); ?></th>
						<th><?php \esc_html_e( 'Price', 'orbis-subscriptions' ); ?></th>
						<th><?php \esc_html_e( 'Expiration or renewal date', 'orbis-subscriptions' ); ?></th>
						<th><?php \esc_html_e( 'Status', 'orbis-subscriptions' ); ?></th>
						<th><span class="visually-hidden"><?php \esc_html_e( 'Actions', 'orbis-subscriptions' ); ?></span></th>
					</tr>
				</thead>
				<tbody>
					<?php

					while ( \have_posts() ) :
						\the_post();

						?>

						<tr id="post-<?php \the_ID(); ?>" <?php \post_class(); ?>>
							<td>
								<a href="<?php \the_permalink(); ?>"><?php \the_title(); ?></a>

								<?php \get_template_part( 'templates/table-cell-comments' ); ?>
							</td>
							<td>
								<?php \orbis_subscription_the_price(); ?>
							</td>
							<td>
								<?php

								$expiration_date_string = (string) \get_post_field( 'subscription_expiration_date' );

								if ( '' !== $expiration_date_string ) {
									$expiration_date = DateTimeImmutable::createFromFormat( 'Y-m-d', $expiration_date_string );

									if ( false !== $expiration_date ) {
										echo \esc_html( (string) \wp_date( (string) \get_option( 'date_format' ), $expiration_date->getTimestamp() ) );
									}
								}

								?>
							</td>
							<td>
								<?php include __DIR__ . '/subscription-badges.php'; ?>
							</td>
							<td>
								<?php \get_template_part( 'templates/table-cell-actions' ); ?>
							</td>
						</tr>

					<?php endwhile; ?>
				</tbody>
			</table>
		</div>

	<?php else : ?>

		<div class="card-body">
			<?php \get_template_part( 'templates/content-none' ); ?>
		</div>

	<?php endif; ?>
</div>

<?php

if ( \function_exists( 'orbis_content_nav' ) ) {
	\orbis_content_nav();
} else {
	\the_posts_pagination();
}

\get_footer();
