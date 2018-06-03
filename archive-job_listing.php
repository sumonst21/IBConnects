<?php
/**
 * Template Name: Job Listings
 */
?>

	<?php get_header( 'search' ); ?>

	<?php do_action( 'jobs_will_display' ); ?>

	<?php do_action( 'before_jobs_archive' ); ?>

	<div class="section">

		<?php do_action( 'appthemes_notices' ); ?>

		<h2 class="pagetitle">

			<small class="rss">
				<a href="<?php echo esc_url( add_query_arg( 'post_type', APP_POST_TYPE, get_bloginfo('rss2_url') ) ); ?>"><i class="icon dashicons-before"></i></a>
			</small>

			<?php printf( __( 'Latest Jobs%s', APP_TD ), ( ! empty( $paged ) && $paged > 1 ? ' ' . sprintf( __( '(page %d)', APP_TD ), $paged ) : '' ) ); ?>
		</h2>

		<?php jr_filter_form(); ?>

		<?php appthemes_load_template('loop-job.php'); ?>

		<?php jr_paging(); ?>

		<?php wp_reset_query(); ?>

		<div class="clear"></div>

	</div><!-- end section -->

	<?php do_action( 'after_jobs_archive' ); ?>

	<div class="clear"></div>

</div><!-- end main content -->

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar(); endif ?>
