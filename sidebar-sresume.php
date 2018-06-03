<li class="widget widget-submit">
	<?php if ( ! is_user_logged_in() ): ?>
			<div>
				<a href="<?php echo esc_url( site_url('wp-login.php') ); ?>" class="button"><span><?php _e('Submit your Resume',APP_TD); ?></span></a>
				<?php the_jr_submit_resume_button_text(); ?>
			</div>
	<?php else : ?>
			<div>
				<a href="<?php echo esc_url( get_permalink( JR_Dashboard_Page::get_id() ) ); ?>" class="button"><span><?php _e('My Dashboard',APP_TD); ?></span></a>
				<?php the_jr_myprofile_button_text(); ?>
			</div>
	<?php endif; ?>
</li>