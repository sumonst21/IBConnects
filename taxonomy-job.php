	<?php get_header('search'); ?>

	<?php do_action('jobs_will_display'); ?>

	<?php do_action( 'before_jobs_taxonomy', $tax->taxonomy, $tax->slug ); ?>

	<div class="section">

		<?php do_action( 'before_jobs_taxonomy_title', $tax->taxonomy, $tax->slug ); ?>

		<h1 class="pagetitle">
			<small class="rss">
				<a href="<?php echo esc_url( get_the_jr_jobs_base_url() ); ?>"><i class="icon dashicons-before"></i></a>
			</small>
	
			<?php $tax_title = false; ?>

			<?php if ( in_array( $tax->taxonomy, array( APP_TAX_CAT, APP_TAX_TYPE ) ) ): ?>

				<?php $tax_title = wptexturize( $tax->name ) . ' ' . __( 'Jobs', APP_TD ); ?>

			<?php elseif ( APP_TAX_SALARY == $tax->taxonomy ): ?>

				<?php $tax_title = sprintf( __( 'Jobs with a salary of %1$s %2$s', APP_TD ), APP_Currencies::get_current_currency('symbol'), wptexturize( $tax->name ) ); ?>

			<?php elseif ( APP_TAX_TAG == $tax->taxonomy ): ?>

				<?php $tax_title = sprintf( __( 'Jobs tagged "%s"', APP_TD ), wptexturize( $tax->name ) ); ?>

			<?php endif; ?>

			<?php echo apply_filters( 'jobs_taxonomy_title', $tax_title, $tax ); ?>

		</h1>

		<?php do_action( 'after_jobs_taxonomy_title', $tax->taxonomy, $tax->slug ); ?>

		<?php jr_filter_form();	?>

		<?php appthemes_load_template( 'loop-job.php' ); ?>

		<?php jr_paging(); ?>

		<div class="clear"></div>

	</div><!-- end section -->

	<?php do_action('after_jobs_taxonomy', $tax->taxonomy, $tax->slug); ?>

	<div class="clear"></div>

</div><!-- end main content -->

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar(); endif; ?>
