<?php
/**
 * Template Name: Password Reset
 */

global $wp_version;
?>

	<div class="section">

    	<div class="section_content">

			<?php do_action( 'appthemes_notices' ); ?>

			<h1><?php _e('Password Reset', APP_TD); ?></h1>

			<p><?php _e('Enter your new password below.', APP_TD); ?></p>

			<form action="<?php echo appthemes_get_password_reset_url(); ?>" method="post" class="main_form password-reset-form" name="resetpassform" id="login-form">

					<input type="hidden" id="user_login" value="<?php echo esc_attr( $_GET['login'] ); ?>" autocomplete="off" />

					<?php if ( $wp_version < 4.3 ) : ?>

						<p>
							<label for="pass1"><?php _e('New password', APP_TD); ?></label>
							<input type="password" name="pass1" id="pass1" class="text" size="20" value="" autocomplete="off" />
						</p>

						<p>
							<label for="pass2"><?php _e('Confirm new password', APP_TD); ?></label>
							<input type="password" name="pass2" id="pass2" class="text" size="20" value="" autocomplete="off" />
						</p>

					<?php else: ?>

						<div class="user-pass1-wrap manage-password">
							<p>
								<label for="pass1"><?php _e( 'New Password', APP_TD ); ?></label>

								<?php $initial_password = isset( $_POST['pass1'] ) ? stripslashes( $_POST['pass1'] ) : wp_generate_password( 18 ); ?>

								<input tabindex="3" type="password" id="pass1" name="pass1" class="text required" autocomplete="off" data-reveal="1" data-pw="<?php echo esc_attr( $initial_password ); ?>" aria-describedby="pass-strength-result" />
								<input type="text" style="display:none" name="pass2" id="pass2" autocomplete="off" />
							</p>
							<p>
								<button type="button" class="button submit wp-hide-pw hide-if-no-js" data-start-masked="<?php echo (int) isset( $_POST['pass1'] ); ?>" data-toggle="0" aria-label="<?php esc_attr_e( 'Hide password', APP_TD ); ?>">
									<span class="dashicons dashicons-hidden"></span>
									<span class="text"><?php _e( 'Hide', APP_TD ); ?></span>
								</button>
							</p>
						</div>

					<?php endif; ?>

					<div class="strength-meter">
						<div id="pass-strength-result" class="hide-if-no-js"><?php _e('Strength indicator', APP_TD); ?></div>
						<span class="description indicator-hint"><?php _e('Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).', APP_TD); ?></span>
					</div>

					<div id="checksave">
						<?php do_action('lostpassword_form'); ?>
						<p><input type="submit" class="submit" name="resetpass" id="resetpass" value="<?php _e('Reset Password', APP_TD); ?>" tabindex="100" /></p>
					</div>

					<!-- autofocus the field -->
					<script type="text/javascript">try{document.getElementById('pass1').focus();}catch(e){}</script>

			</form>

			<div class="clear"></div>

    	</div><!-- end section_content -->

		<div class="clear"></div>

	</div><!-- end section -->

    <div class="clear"></div>

</div><!-- end main content -->

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar('page'); endif; ?>
