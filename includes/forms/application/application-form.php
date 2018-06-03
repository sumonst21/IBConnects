<?php
/**
 * JobRoller Application form.
 * Outputs the job application form.
 *
 * @todo: use outside function
 *
 * @version 1.0
 * @author AppThemes
 * @package JobRoller\Forms\Application
 * @copyright 2010 all rights reserved
 */

function jr_application_form() {
	global $post, $app_form_results, $jr_options;

	$errors = $app_form_results['errors'];
	$posted = $app_form_results['posted'];

	if ( ! $posted && is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		$posted['your_name'] = $current_user->user_firstname . ' ' . $current_user->user_lastname;;
		$posted['your_email'] = $current_user->user_email;
	}
?>
	<div id="apply_form" class="section_content <?php esc_attr_e( $app_form_results['class'] ); ?>">

		<h2><?php _e( 'Apply for this Job', APP_TD ); ?></h2>

		<?php jr_show_errors( $errors, 'apply_form_result' ); ?>

		<form action="<?php echo get_permalink( $post->ID ); ?>#apply_form_result" method="post" class="main_form" enctype="multipart/form-data">

			<p>
				<label for="your_name"><?php _e( 'Name', APP_TD ); ?> <span title="required">*</span></label>
				<input type="text" class="text required" name="your_name" id="your_name" value="<?php if ( isset( $posted['your_name'] ) ) esc_attr_e( $posted['your_name'] ); ?>" />
			</p>
			<p>
				<label for="your_email"><?php _e( 'Email', APP_TD ); ?> <span title="required">*</span></label>
				<input type="text" class="text required" name="your_email" id="your_email" value="<?php if ( isset( $posted['your_email'] ) ) esc_attr_e( $posted['your_email'] ); ?>" />
			</p>
			<p>
				<label for="your_message"><?php _e( 'Message', APP_TD ); ?> <span title="required">*</span></label>
				<textarea rows="5" cols="30" class="required" name="your_message" id="your_message"><?php if ( isset( $posted['your_message'] ) ) echo esc_textarea( $posted['your_message'] ); ?></textarea>
			</p>

			<?php if ( current_user_can( 'link_resumes' ) ): ?>

				<p class="optional">
					<label for="your_online_cv"><?php _e( 'Link to Resum&eacute;', APP_TD ); ?></label>

					<?php the_user_online_resumes_dropdown(); ?>
				</p>

			<?php endif; ?>

			<p class="optional">
				<label for="your_cv"><?php echo sprintf( __( 'Upload resum&eacute; (%s)', APP_TD ), jr_get_allowed_extensions_for( 'apply_job_cv', ', ' ) ); ?></label>
				<input type="file" class="text" name="your_cv" id="your_cv" />
			</p>
			<p class="optional">
				<label for="your_coverletter"><?php echo sprintf( __( 'Upload cover letter (%s)', APP_TD ), jr_get_allowed_extensions_for( 'apply_job_cv_letter', ', ' ) ); ?></label>
				<input type="file" class="text" name="your_coverletter" id="your_coverletter" />
			</p>
			<?php
			// include the spam checker if enabled();
			if ( ! is_user_logged_in() && ! jr_display_recaptcha( 'app-recaptcha-application' ) && $jr_options->jr_antispam_question ) :
			?>
				<p><label for="antispam_answer" title="<?php esc_attr_e( 'This is to prevent spam', APP_TD ); ?>"><?php echo $jr_options->jr_antispam_question; ?> <span title="required">*</span></label> <input type="text" class="text small required-" name="antispam_answer" id="antispam_answer" value="<?php if ( isset( $posted['antispam_answer'] ) ) echo $posted['antispam_answer']; ?>" /></p>
			<?php
			elseif ( jr_display_recaptcha( 'app-recaptcha-application' ) ):
				// or include reCaptcha if enabled();
				appthemes_display_recaptcha();
			endif;
			?>
			<p><input type="submit" class="submit" name="apply_to_job" value="<?php esc_attr_e( 'Apply for Job', APP_TD ); ?>" /></p>

		</form>
	</div>
<?php
}
