<?php
/**
 * JobRoller Preview Job form.
 * Outputs the job preview form.
 *
 * @version 1.0
 * @author AppThemes
 * @package JobRoller\Forms\Preview
 * @copyright 2010 all rights reserved
 */
 ?>
<form action="<?php echo esc_url( $form_action ) ?>" method="post" enctype="multipart/form-data" id="submit_form" class="submit_form main_form">
	<?php wp_nonce_field('submit_job', 'nonce') ?>

	<p><?php _e( 'Below is a preview of what your job listing will look like when published:', APP_TD ); ?></p>

	<?php appthemes_load_template( 'includes/forms/preview-job/preview-job-fields.php', compact( $job, $preview_fields ) ); ?>

	<?php do_action( 'jr_after_preview_job_form' ); ?>

	<input type="hidden" name="action" value="<?php echo esc_attr( $post_action ); ?>" />
	<input type="hidden" name="ID" value="<?php echo esc_attr( $job->ID ); ?>">
	<input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">
	<input type="hidden" name="step" value="<?php echo esc_attr( $step ); ?>"/>

	<p>
		<input type="submit" class="goback" name="goback" value="<?php esc_attr_e( 'Go Back',APP_TD ); ?>"  />
		<input type="submit" class="submit" name="preview_submit" value="<?php esc_attr_e( 'Next &rarr;', APP_TD ); ?>" />
	</p>

	<div class="clear"></div>
</form>
<?php
