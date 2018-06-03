<?php
/**
 * Template Name: User Profile
 */

global $wp_version;
?>
	<div class="section">

		<div class="section_content">

			<?php do_action( 'appthemes_notices' ); ?>

			<h1><?php printf( __('%s\'s Profile', APP_TD), ucwords( $userdata->user_login )); ?></h1>

			<form name="profile" id="your-profile" action="" method="post" class="main_form" autocomplete="off">

				<div>
					<?php wp_nonce_field('update-profile_' . $user_ID) ?>
					<input type="hidden" name="from" value="profile" />
					<input type="hidden" name="checkuser_id" value="<?php echo esc_attr( $user_ID ); ?>" />
					<input type="hidden" id="user_login" name="user_login" value="<?php echo esc_attr( $userdata->user_login ); ?>" />
				</div>

				<fieldset>
					<legend><?php _e('Your Details', APP_TD); ?></legend>

					<p>
						<label for="first_name"><?php _e('First Name',APP_TD) ?></label>
						<input type="text" name="first_name" class="text regular-text" id="first_name" value="<?php echo esc_attr( $userdata->first_name );  ?>" maxlength="100" />
					</p>

					<p>
						<label for="last_name"><?php _e('Last Name',APP_TD) ?></label>
						<input type="text" name="last_name" class="text regular-text" id="last_name" value="<?php echo esc_attr( $userdata->last_name ); ?>" maxlength="100" />
					</p>

					<p>
						<label for="nickname"><?php _e('Nickname/Company Name',APP_TD) ?></label>
						<input type="text" name="nickname" class="text regular-text" id="nickname" value="<?php echo esc_attr( $userdata->nickname ); ?>" maxlength="100" />
					</p>

					<p>
						<label for="display_name"><?php _e('Display Name',APP_TD) ?></label>
						<select name="display_name" class="select" id="display_name">
							<?php foreach( $public_display as $id => $item ) : ?>
									<option id="<?php echo $id; ?>" value="<?php echo esc_attr( $item ); ?>"><?php echo $item; ?></option>
							<?php endforeach; ?>
						</select>
					</p>

					<p>
						<label for="email"><?php _e('Email',APP_TD) ?></label>
						<input type="text" name="email" class="text regular-text" id="email" value="<?php echo esc_attr( $userdata->user_email ); ?>" maxlength="100" />
					</p>

				</fieldset>

				<fieldset>
					<legend><?php _e('Websites &amp; Social media', APP_TD); ?></legend>

					<p>
						<label for="url"><?php _e('Website',APP_TD) ?></label>
						<input type="text" name="url" class="text regular-text" id="url" value="<?php echo esc_url( $userdata->user_url ); ?>" maxlength="100" />
					</p>

					<p>
						<label for="twitter_id"><?php _e('Twitter ID',APP_TD) ?></label>
						<input type="text" name="twitter_id" class="text regular-text" id="twitter_id" value="<?php echo esc_attr( get_user_meta( $user_ID, 'twitter_id', true ) ); ?>" maxlength="100" />
					</p>

					<p>
						<label for="facebook_id"><?php _e('Facebook ID',APP_TD) ?></label>
						<input type="text" name="facebook_id" class="text regular-text" id="facebook_id" value="<?php echo esc_attr( get_user_meta( $user_ID, 'facebook_id', true ) ); ?>" maxlength="100" />
					</p>

					<p>
						<label for="linkedin_profile"><?php _e('LinkedIn profile URL',APP_TD) ?></label>
						<input type="text" name="linkedin_profile" class="text regular-text" id="linkedin_profile" value="<?php echo esc_attr( get_user_meta( $user_ID, 'linkedin_profile', true ) ); ?>" maxlength="100" />
					</p>
				</fieldset>

				<fieldset>
					<legend><?php _e('Profile', APP_TD); ?></legend>

					<p><?php _e('Enter a description below; this information will appear on your profile.', APP_TD); ?></p>

					<p>
						<label for="description"><?php _e('Profile content',APP_TD); ?></label>
						<textarea name="description" class="text regular-text" id="description" rows="10" cols="50"><?php echo esc_textarea( $userdata->description ); ?></textarea>
					</p>
				</fieldset>

				<?php $show_password_fields = apply_filters( 'show_password_fields', true ); ?>

				<?php if ( $show_password_fields ): ?>

					<fieldset>
						<legend><?php _e('New Password', APP_TD); ?></legend>

						<?php if ( $wp_version < 4.3 ) : ?>

							<p><?php _e('Leave this field blank unless you would like to change your password.',APP_TD); ?> <?php _e('Your password should be at least seven characters long.',APP_TD); ?></p>

							<p>
								<label for="pass1"><?php _e('New Password',APP_TD); ?></label>
								<input type="password" name="pass1" class="text regular-text" id="pass1" maxlength="50" value="" />
							</p>

							<p>
								<label for="pass1"><?php _e('Password Again',APP_TD); ?></label>
								<input type="password" name="pass2" class="text regular-text" id="pass2" maxlength="50" value="" />
							</p>

							<div id="pass-strength-result"><?php _e( 'Strength indicator', APP_TD ); ?></div>

						<?php else: ?>

							<div class="user-pass1-wrap manage-password">
								<button type="button" class="button submit wp-generate-pw hide-if-no-js"><?php _e( 'Generate Password', APP_TD ); ?></button>
								<div class="wp-pwd hide-if-js">
									<?php $initial_password = wp_generate_password( 24 ); ?>

									<input type="password" id="pass1" name="pass1" class="text regular-text" autocomplete="off" data-pw="<?php echo esc_attr( $initial_password ); ?>" aria-describedby="pass-strength-result" />
									<input type="text" style="display:none" name="pass2" id="pass2" autocomplete="off" />

									<label>
										<button type="button" class="button submit wp-hide-pw hide-if-no-js" data-start-masked="<?php echo (int) isset( $_POST['pass1'] ); ?>" data-toggle="0" aria-label="<?php esc_attr_e( 'Hide password' ); ?>">
											<span class="dashicons dashicons-hidden"></span>
											<span class="text"><?php _e( 'Hide', APP_TD ); ?></span>
										</button>
										<button type="button" class="button submit wp-cancel-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Cancel password change', APP_TD ); ?>">
											<span class="text"><?php _e( 'Cancel', APP_TD ); ?></span>
										</button>
									</label>

									<p><div id="pass-strength-result"><?php _e( 'Strength indicator', APP_TD ); ?></div></p>
								</div>
							</div>

						<?php endif; ?>

					</fieldset>

				<?php endif; ?>

				<?php do_action( 'profile_personal_options', $userdata ); ?>

				<?php do_action( 'show_user_profile', $userdata ); ?>

				<p>
					<input type="hidden" name="action" value="update" />
					<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr( $user_ID ); ?>" />
					<input type="submit" class="submit" name="submit" value="<?php esc_attr_e( 'Update Profile &raquo;', APP_TD ); ?>" />
				</p>
			</form>

		</div><!-- end section_content -->

	</div><!-- end section -->

	<div class="clear"></div>

</div><!-- end main content -->

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar('user'); endif; ?>
