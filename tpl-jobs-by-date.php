<?php
/*
 * Template Name: Jobs by date
 */
?>

	<?php get_header('search'); ?>

	<?php do_action( 'jobs_will_display' ); ?>

	<?php do_action( 'before_jobs_by_date', 'date', $timespan ); ?>

	<div class="section">

		<?php query_posts( $query_args ); ?>

		<h1 class="pagetitle"><?php echo $title; ?></h1>

		<?php jr_filter_form(); ?>

		<?php appthemes_load_template('loop-job.php'); ?>

		<?php jr_paging(); ?>

		<?php wp_reset_query(); ?>

		<div class="clear"></div>

	</div><!-- end section -->

	<?php do_action( 'after_jobs_by_date', 'date',  $timespan ); ?>

	<div class="clear"></div>

</div><!-- end main content -->

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar(); endif; ?>
