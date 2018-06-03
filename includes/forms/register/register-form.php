<?php
/**
 * JobRoller Registration Form.
 * Outputs the registration form.
 *
 * @version 1.6.3
 * @author AppThemes
 * @package JobRoller\Forms\Register
 * @copyright 2010 all rights reserved
 */

add_action( 'jr_display_register_form', 'jr_register_form', 10, 2 );

function jr_register_form( $redirect = '', $role = 'job_lister' ) {
	global $posted, $jr_options, $wp_version;

	if ( ! get_option('users_can_register') ) {
		return;
	}

	if ( ! $redirect ) {
		$redirect = get_permalink( JR_Dashboard_Page::get_id() );
	}

	$show_password_fields = apply_filters( 'show_password_fields_on_registration', true );
?>
	<h2><?php _e( 'Create a free account', APP_TD ); ?></h2>

	<form action="<?php echo appthemes_get_registration_url(); ?>" method="post" class="account_form" name="registerform" id="login-form">

		<?php if ( $jr_options->jr_allow_job_seekers ): ?>

				<?php if ( ! $role || $jr_options->jr_allow_recruiters ): ?>
					<p class="role">
						<label><input type="radio" name="role" tabindex="5" value="job_lister" <?php checked( empty( $posted['role'] ) || $posted['role'] == 'job_lister' ); ?> /> <?php _e( 'I am an <strong>Employer</strong>', APP_TD ); ?></label>

						<?php if ( ! is_page( JR_Job_Submit_Page::get_id() ) ): ?>
							<label class="alt"><input type="radio" tabindex="6" name="role" value="job_seeker" <?php checked( isset( $posted['role'] ) && $posted['role'] == 'job_seeker' ); ?> /> <?php _e( 'I am a <strong>Job Seeker</strong>', APP_TD ); ?></label>
						<?php endif; ?>
					</p>

					<?php if ( $jr_options->jr_allow_recruiters ): ?>
						<p class="role"><label class="alt"><input type="radio" tabindex="7" name="role" value="recruiter" <?php checked( isset( $posted['role'] ) && $posted['role'] == 'recruiter' ); ?> /> <?php _e( 'I am a <strong>Recruiter</strong>', APP_TD ); ?></label></p>
					<?php endif; ?>

				<?php
					elseif ( $role == 'job_lister' ) :
						echo '<input type="hidden" name="role" value="job_lister" />';
					elseif ( $role == 'job_seeker') :
						echo '<input type="hidden" name="role" value="job_seeker" />';
					elseif ( $role == 'recruiter' && $jr_options->jr_allow_recruiters ) :
						echo '<input type="hidden" name="role" value="recruiter" />';
					endif;
				?>

		<?php endif; ?>

		<div class="account_form_fields">
			<p>
				<label for="user_login"><?php _e( 'Username', APP_TD ); ?></label><br/>
				<input type="text" class="text required" tabindex="8" name="user_login" id="user_login" value="<?php if ( isset( $_POST['user_login'] ) ): esc_attr_e( stripslashes( $_POST['user_login'] ) ); endif; ?>" />
			</p>

			<p>
				<label for="user_email"><?php _e( 'Email', APP_TD ); ?></label><br/>
				<input type="text" class="text required" tabindex="9" name="user_email" id="user_email" value="<?php if ( isset( $_POST['user_email'] ) ): esc_attr_e( stripslashes( $_POST['user_email'] ) ); endif; ?>" />
			</p>

			<?php if ( $show_password_fields ) : ?>

				<?php if ( $wp_version < 4.3 ) : ?>

					<p>
						<label for="your_password"><?php _e( 'Enter a password', APP_TD ); ?></label><br/>
						<input type="password" class="text required" tabindex="10" name="pass1" id="pass1" value="" />
					</p>

					<p>
						<label for="your_password_2"><?php _e( 'Enter password again', APP_TD ); ?></label><br/>
						<input type="password" class="text required" tabindex="11" name="pass2" id="pass2" value="" />
					</p>

				<?php else: ?>

					<div class="user-pass1-wrap manage-password">

						<p>
							<label for="pass1"><?php _e( 'Password:', APP_TD ); ?></label>

							<?php $initial_password = isset( $_POST['pass1'] ) ? stripslashes( $_POST['pass1'] ) : wp_generate_password( 18 ); ?>

							<input tabindex="3" type="password" id="pass1" name="pass1" class="text required" autocomplete="off" data-reveal="1" data-pw="<?php echo esc_attr( $initial_password ); ?>" aria-describedby="pass-strength-result" />
							<input type="text" style="display:none" name="pass2" id="pass2" autocomplete="off" />

							<button type="button" class="button submit wp-hide-pw hide-if-no-js" data-start-masked="<?php echo (int) isset( $_POST['pass1'] ); ?>" data-toggle="0" aria-label="<?php esc_attr_e( 'Hide password', APP_TD ); ?>">
								<span class="dashicons dashicons-hidden"></span>
								<span class="text"><?php _e( 'Hide', APP_TD ); ?></span>
							</button>
						</p>

					</div>

				<?php endif; ?>

				<br/><div id="pass-strength-result" class="hide-if-no-js"><?php _e( 'Strength indicator', APP_TD ); ?></div>

				<p><span class="description indicator-hint"><?php _e( 'Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).', APP_TD ); ?></span></p>
			<?php endif; ?>

			<?php if ( jr_display_recaptcha('app-recaptcha-register') ): ?>
					<?php appthemes_display_recaptcha(); ?>
			<?php endif; ?>

			<?php if ( $jr_options->jr_terms_page_id > 0 || $jr_options->jr_enable_terms_conditions ): ?>
				<p>
					<input type="checkbox" name="terms" tabindex="12" value="yes" id="terms" class="required" <?php checked( isset( $_POST['terms'] ) ); ?> />
					<label for="terms"><?php _e( 'I accept the ', APP_TD ); ?><a href="<?php echo esc_url( get_permalink( JR_Terms_Conditions_Page::get_id() ) ); ?>" target="_blank"><?php _e( 'terms &amp; conditions', APP_TD ); ?></a>.</label>
				</p>
			<?php endif; ?>

			<?php do_action('register_form'); ?>

			<p>
				<input type="hidden" name="redirect_to" value="<?php esc_attr_e( $redirect ); ?>" />
				<input type="submit" class="submit" tabindex="13" name="register" value="<?php _e( 'Create Account &rarr;', APP_TD ); ?>" />
			</p>

		</div>

		<!-- autofocus the field -->
		<script type="text/javascript">try{document.getElementById('user_login').focus();}catch(e){}</script>

	</form>
<?php
}
