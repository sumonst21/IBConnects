<?php
/**
 *  Template Name: Edit Job Template
 */
?>
	<div class="section">

		<div class="section_content">

			<?php do_action( 'appthemes_notices' ); ?>

			<h1><?php echo sprintf( __( 'Editing %s', APP_TD ), html_link( get_permalink( $job->ID ), get_the_title( $job->ID ) ) ); ?></h1>

			<?php appthemes_load_template( '/includes/forms/submit-job/submit-job-form.php', $params ); ?>

		</div><!-- end section_content -->

	</div><!-- end section -->

	<div class="clear"></div>

</div><!-- end main content -->

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar('submit'); endif; ?>