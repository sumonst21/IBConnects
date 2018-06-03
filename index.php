<?php get_header( 'search' ); ?>

<?php do_action( 'jobs_will_display' ); ?>

<?php do_action('before_front_page_jobs'); ?>

<div class="section">

	<h2 class="pagetitle">

		<small class="rss">
			<a href="<?php echo esc_url( add_query_arg( 'post_type', APP_POST_TYPE, get_bloginfo('rss2_url') ) ); ?>"><i class="icon dashicons-before"></i></a>
		</small>

		<?php _e( 'Latest Jobs', APP_TD ); ?>

	</h2>

	<?php query_posts( array( 'is_jobs_frontpage_archive' => true ) ); ?>

	<?php jr_filter_form(); ?>

	<?php appthemes_load_template('loop-job.php'); ?>

	<?php jr_paging(); ?>

	<?php wp_reset_query(); ?>

	<div class="clear"></div>

</div><!-- end section -->

<?php do_action( 'after_front_page_jobs' ); ?>

<div class="clear"></div>

</div><!-- end main content -->

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar(); endif; ?>
