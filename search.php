	<?php get_header('search'); ?>

	<?php do_action('jobs_will_display'); ?>

	<div class="section">

		<h2 class="pagetitle">
			<?php echo $term_heading.$location_heading . ( $paged > 1 ? ' ' . sprintf( __( '(page %d)', APP_TD ), number_format_i18n( $paged ) ) : '' ); ?>
		</h2>

		<?php jr_filter_form(); ?>

		<?php appthemes_load_template( 'loop-job.php' ); ?>

		<?php jr_paging(); ?>

		<div class="clear"></div>

	</div><!-- end section -->

	<?php do_action('after_search_results'); ?>

	<div class="clear"></div>

</div><!-- end main content -->

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar(); endif; ?>
