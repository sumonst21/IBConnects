	<?php jr_resume_page_auth(); ?>

	<div class="section single">

		<?php do_action( 'appthemes_notices' ); ?>

		<?php appthemes_before_loop(); ?>

			<?php if ($resume_access_level != 'none' && have_posts()): ?>

				<?php while (have_posts()) : the_post(); ?>

					<?php appthemes_before_post(); ?>

					<?php jr_resume_header($post); ?>

					<?php appthemes_stats_update($post->ID); //records the page hit ?>

						<?php appthemes_before_post_title(); ?>

						<?php if ( 'subscribe' == $resume_access_level ): ?>

								<div class="section_header resume_header">

									<?php if ( $notice = $jr_options->jr_resume_subscription_notice ): ?>
										<p><?php echo wptexturize( $notice ); ?></p>
									<?php endif; ?>

									<?php the_resume_purchase_plan_link(); ?>

									<div class="clear"></div>

								</div><!-- end section_header -->

						<?php else: ?>

								<div class="section_header resume_header">

									<?php if ( has_post_thumbnail() ): the_post_thumbnail( 'blog-thumbnail', array( 'class' => 'resume-thumbnail' ) ); endif; ?>

									<h1 class="title resume-title"><span><?php the_title(); ?></span></h1>

									<div class="user_prefs_wrap" style="display: none"><?php echo jr_seeker_prefs( get_the_author_meta('ID') ); ?></div>

									<p class="meta">
										<?php the_resume_posted_by( __( 'Resume posted by ', APP_TD ), '<strong>', '</strong>' ); ?>

										<?php the_resume_category( __(' in ',APP_TD ), '<strong>', '</strong>. ' ); ?>

										<br/><?php the_resume_salary( __( 'Desired salary: ', APP_TD ), '<strong>', '</strong>' ); ?>

										<br/><?php the_resume_desired_position( __( 'Desired position type: ', APP_TD ), __( 'Any', APP_TD ), '<strong>', '</strong>' ); ?>

										<br/><?php the_resume_location( __( 'Location: ', APP_TD ) ); ?>
									</p>

									<?php the_contact_details(); ?>

									<?php appthemes_after_post_title(); ?>

								</div><!-- end section_header -->

								<div class="section_content">

									<?php do_action( 'resume_main_section', $post ); ?>

									<?php appthemes_before_post_content(); ?>

									<h2 class="resume_section_heading"><span><?php _e( 'Summary', APP_TD ); ?></span></h2>

									<div class="resume_section summary"><?php the_content(); ?></div>

									<div class="clear"></div>

									<?php appthemes_after_post_content(); ?>

									<?php the_resume_fields(); ?>

									<?php the_resume_custom_fields(); ?>

									<?php the_resume_files(); ?>

									<?php if ( get_the_author_meta('ID') == get_current_user_id() ): ?>
										<p class="button edit_resume"><a href="<?php echo esc_url( jr_get_the_resume_edit_link() ); ?>"><?php _e( 'Edit Resume&nbsp;&rarr;', APP_TD ); ?></a></p>
									<?php endif; ?>

									<?php if ( $jr_options->jr_ad_stats_all && current_theme_supports('app-stats') ): ?>
										<p class="stats"><?php appthemes_stats_counter($post->ID); ?></p>
									<?php endif; ?>

									<div class="clear"></div>

								</div><!-- end section_content -->

						<?php endif; ?>

					<?php appthemes_after_post(); ?>

					<?php jr_resume_footer($post); ?>

				<?php endwhile; ?>

					<?php appthemes_after_endwhile(); ?>

			<?php else: ?>

				<?php jr_no_access_permission(); ?>

				<?php appthemes_loop_else(); ?>

			<?php endif; ?>

			<?php appthemes_after_loop(); ?>

	</div><!-- end section -->

	<div class="clear"></div>

</div><!-- end main content -->

<?php
if ( $jr_options->jr_show_sidebar ) {

	if ( get_the_author_meta('ID') == get_current_user_id() ) {
		get_sidebar('user');
	} else {
		get_sidebar('resume');
	}

}
