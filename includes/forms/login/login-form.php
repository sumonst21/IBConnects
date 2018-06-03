<?php
/**
 * JobRoller Login Form.
 * Outputs the login form.
 *
 * @version 1.6.3
 * @author AppThemes
 * @package JobRoller\Forms\Login
 * @copyright 2010 all rights reserved
 */

add_action( 'jr_display_login_form', 'jr_login_form', 10, 2 );

function jr_login_form( $action = '', $redirect = '' ) {

	// make sure there's the correct url
	if ( ! $action ) {
		$action = site_url( 'wp-login.php' );
	}

	if ( ! $redirect ) {
		$redirect = jr_get_dashboard_url();
	}
?>
	<h2><?php _e( 'Already have an account?', APP_TD ); ?></h2>

	<form action="<?php echo esc_url( APP_Login::get_url() ); ?>" method="post" class="account_form" id="login-form">

		<p>
			<label for="login_username"><?php _e( 'Username', APP_TD ); ?></label><br />
			<input type="text" class="text required" name="log" tabindex="1" id="login_username" value="" />
		</p>

		<p>
			<label for="login_password"><?php _e( 'Password', APP_TD ); ?></label><br />
			<input type="password" class="text required" name="pwd" tabindex="2" id="login_password" value="" />
		</p>

		<p>
			<input type="checkbox" name="rememberme" class="checkbox" tabindex="3" id="rememberme" value="forever" checked="checked" />
			<label for="rememberme"><?php _e( 'Remember me', APP_TD ); ?></label>
		</p>

		<p>
			<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect ); ?>" />
			<input type="submit" class="submit" name="login" tabindex="4" value="<?php esc_attr_e( 'Login &rarr;', APP_TD ); ?>" />
			<a class="lostpass" href="<?php echo esc_url( appthemes_get_password_recovery_url() ); ?>" title="<?php esc_attr_e( 'Password Lost and Found', APP_TD ); ?>"><?php _e( 'Lost your password?', APP_TD ); ?></a>
		</p>

		<?php do_action( 'login_form' ); ?>

		<!-- autofocus the field -->
		<script type="text/javascript">try{document.getElementById('login_username').focus();}catch(e){}</script>

	</form>

<?php
}
