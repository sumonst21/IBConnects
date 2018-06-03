<?php
/**
 * Template Name: Contact Page
 */
?>
	<div class="section">

		<div class="section_content">

			<?php do_action( 'appthemes_notices' ); ?>

			<?php if ( ! get_query_var( 'contact_success' ) ): ?>

				<?php if ( have_posts() ) : ?>

					<?php while (have_posts()) : the_post(); ?>

						<h1><?php the_title(); ?></h1>

						<?php the_content(); ?>

							<!-- Contact Form -->
							<form method="post" id="contact_form" action="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" class="main_form">

								<p>
									<label for="your_name"><?php _e( 'Your Name/Company Name', APP_TD ); ?> <span title="required">*</span></label>
									<input type="text" class="text required" name="your_name" id="your_name" value="<?php echo esc_attr( $posted['your_name'] ); ?>" />
								</p>
								<p>
									<label for="email"><?php _e( 'Your email', APP_TD ); ?> <span title="required">*</span></label>
									<input type="text" class="text required" name="email" id="email" value="<?php echo esc_attr( $posted['email'] ); ?>" />
								</p>

								<p>
									<label for="message"><?php _e( 'Message', APP_TD ); ?> <span title="required">*</span></label>
									<textarea name="message" id="message" cols="60" rows="8" class="required"><?php echo esc_textarea( $posted['message'] ); ?></textarea>
								</p>

								<?php if ( jr_display_recaptcha( 'app-recaptcha-contact' ) ): ?>
									<?php appthemes_display_recaptcha(); ?>
								<?php endif; ?>

								<p class="button">
									<input type="submit" name="submit-form" class="submit" id="submit-form" value="<?php esc_attr_e( 'Submit', APP_TD ); ?>" />
									<input type="text" name="honeypot" value="" style="position: absolute; left: -999em;" title="" />
									<input type="hidden" name="contact_form" value="1">
								</p>
							</form>

					<?php endwhile; ?>

				<?php endif; ?>

			<?php else: ?>

				<?php echo html( 'a', array( 'href' => esc_url( home_url() ) ), __( '&nbsp;&larr; Return to main page', APP_TD ) ); ?>

			<?php endif; ?>

			<div class="clear"></div>

		</div><!-- end section_content -->

	</div><!-- end section -->

	<div class="clear"></div>

</div><!-- end main content -->

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar( 'page' ); endif; ?>
