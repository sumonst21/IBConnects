	<?php get_header('resume-search'); ?>

	<div class="section">

		<h1 class="pagetitle">
			<?php echo $term_heading.$location_heading . ( $paged > 1 ? ' ' . sprintf( __( '(page %d)', APP_TD ), number_format_i18n( $paged ) ) : '' ); ?>
		</h1>

		<?php appthemes_load_template('loop-resume.php'); ?>

        <?php jr_paging(); ?>

        <div class="clear"></div>

    </div><!-- end section -->

    <div class="clear"></div>

</div><!-- end main content -->

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar('resume'); endif; ?>
