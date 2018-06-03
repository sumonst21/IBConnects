<?php
/**
 * Template Name: Edit Resume Template
 */
?>
	<div class="section">

		<div class="section_content">

			<?php do_action( 'appthemes_notices' ); ?>

			<h1><?php ( $editing ? _e( 'Edit Resume', APP_TD ) : _e( 'Add Resume', APP_TD ) ); ?></h1>

			<?php appthemes_load_template( '/includes/forms/submit-resume/submit-resume-form.php', array( 'resume' => $resume ) ); ?>

		</div><!-- end section_content -->

	</div><!-- end section -->

	<div class="clear"></div>

</div><!-- end main content -->

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar('resume'); endif; ?>
