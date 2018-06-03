<?php
/**
 * Template Name: Password Recovery
 */
?>
	<div class="section">

    	<div class="section_content">

			<?php do_action( 'appthemes_notices' ); ?>

			<h1><?php _e('Password Recovery', APP_TD); ?></h1>

		    <p><?php _e('Please enter your username or email address. A new password will be emailed to you.', APP_TD) ?></p>
		    <form action="<?php echo appthemes_get_password_recovery_url(); ?>" method="post" name="lostpassform" id="login-form" class="main_form">
		        <p>
					<label for="login_username"><?php _e('Username/Email', APP_TD); ?></label>
					<input type="text" class="text" name="user_login" id="login_username" />
				</p>

		        <p>
					<?php do_action('lostpassword_form'); ?>
					<input type="submit" id="lostpass" name="lostpass" class="submit" value="<?php _e('Get New Password',APP_TD); ?>" />
				</p>

				<!-- autofocus the field -->
				<script type="text/javascript">try{document.getElementById('login_username').focus();}catch(e){}</script>
		    </form>

			<div class="clear"></div>

    	</div><!-- end section_content -->

		<div class="clear"></div>

	</div><!-- end section -->

    <div class="clear"></div>

</div><!-- end main content -->

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar('page'); endif; ?>
