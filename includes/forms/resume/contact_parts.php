<?php
/**
 * JobRoller Resumes Contact Form.
 * Outputs the resumes contact form.
 *
 * @version 1.6.3
 * @author AppThemes
 * @package JobRoller\Forms\Contact
 * @copyright 2010 all rights reserved
 */

add_action( 'jr_resume_footer', 'jr_contact_resume_parts' );

function jr_contact_resume_parts( $post ) {
?>
	<div style="display:none">
		<form id="contact" action="<?php echo esc_url( get_permalink( $post ) ); ?>" class="submit_form main_form contact_form modal_form" method="post">

			<h2><?php echo sprintf( __( 'Contact %s', APP_TD ), wptexturize( get_the_author_meta( 'display_name' ) ) ); ?></h2>

			<p><?php echo sprintf( __( 'Please fill in the following form to contact %s', APP_TD ), wptexturize( get_the_author_meta( 'display_name' ) ) ); ?></p>

			<?php wp_nonce_field( 'contact-resume-author_' . $post->post_author ) ?>

			<p><label for="contact_name"><?php _e( 'Your Name', APP_TD ); ?> <span title="required">*</span></label> <input type="text" class="text required" name="contact_name" id="contact_name" /></p>
			<p><label for="contact_name"><?php _e( 'Your Email', APP_TD ); ?> <span title="required">*</span></label> <input type="text" class="text required" name="contact_email" id="contact_email" /></p>
			<p><label for="contact_subject"><?php _e( 'Subject', APP_TD ); ?> <span title="required">*</span></label> <input type="text" class="text required" name="contact_subject" id="contact_subject" /></p>
			<p><label for="contact_message"><?php _e( 'Message', APP_TD ); ?> <span title="required">*</span></label> <textarea class="required" name="contact_message" id="contact_message" ></textarea></p>

			<?php if ( jr_display_recaptcha('app-recaptcha-contact') ): ?>
				<?php appthemes_recaptcha(); ?>
			<?php endif; ?>

			<p>
				<input type="submit" class="submit contact_job_seeker" name="send_message" value="<?php esc_attr_e( 'Send', APP_TD ); ?>" >
			</p>
		</form>
	</div>
<?php
}
