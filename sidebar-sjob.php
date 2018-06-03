<?php if ( JR_Job_Submit_Page::get_id() ) : ?>

	<li class="widget widget-submit">

		<?php if ( !is_user_logged_in() || ( is_user_logged_in() && current_user_can('can_submit_job') ) ): ?>

			<div>
				<a href="<?php echo esc_url( jr_get_listing_create_url() ); ?>" class="button"><span><?php _e('Submit a Job',APP_TD); ?></span></a>
				<?php echo jr_get_submit_footer_text(); ?>
			</div>

		<?php endif; ?>

		<?php if ( is_user_logged_in() && current_user_can('can_submit_resume')) : ?>

			<?php if ( $jr_options->jr_allow_job_seekers ) : ?>
				<div>
					<a href="<?php echo esc_url( get_permalink( JR_Dashboard_Page::get_id() ) ); ?>" class="button"><span><?php _e('My Dashboard',APP_TD); ?></span></a>
					<?php the_jr_myprofile_button_text(); ?>
				</div>
			<?php endif; ?>

		<?php endif; ?>

	</li>

<?php endif;
