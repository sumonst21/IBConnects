<?php
/**
 * JobRoller Confirm Job form.
 * Outputs the job confirmation form.
 *
 * @version 1.7
 * @author AppThemes
 * @package JobRoller\Forms\Confirm
 * @copyright 2010 all rights reserved
 *
 */

	if ( APPTHEMES_ORDER_PTYPE == get_queried_object()->post_type ) {
		appthemes_load_template( $order_template );
		return;
	}
?>
<form method="POST" class="submit_form main_form" action="<?php echo esc_url( $form_action ) ?>">
	<?php appthemes_load_template( 'includes/forms/confirm-job/confirm-job-form-preview.php', array( 'job' => $job ) );	?>

	<p>
		<input type="submit" name="goback" class="goback" value="<?php esc_attr_e( 'Go Back', APP_TD ) ?>"  />
		<input type="submit" class="submit" name="job_confirm" value="<?php esc_attr_e( 'Confirm / Continue to Job', APP_TD ); ?>">
		<input type="hidden" name="step" value="<?php echo esc_attr( $step ); ?>"/>
		<input type="hidden" name="ID" value="<?php echo esc_attr( $job->ID ); ?>">
	</p>

	<div class="clear"></div>
</form>
