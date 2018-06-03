<?php
/**
 * Function to prevent visitors without admin permissions
 * to access the wordpress backend. If you wish to permit
 * others besides admins acces, change the user_level
 * to a different number.
 *
 * http://codex.wordpress.org/Roles_and_Capabilities#level_8
 *
 * @global <type> $user_level
 *
 * in order to use this for wpmu, you need to follow the comment
 * instructions below in all locations and make the changes
 */

add_action( 'admin_init', 'jr_security_check', 1 );

function jr_security_check() {
	global $jr_options;

	if ( 'disable' == $jr_options->jr_admin_security || defined('DOING_AJAX') ) {
		return;
	}

	// secure the backend for non ajax calls
	if ( isset( $_SERVER['SCRIPT_NAME'] ) && basename( $_SERVER['SCRIPT_NAME'] ) != 'admin-ajax.php' ) {
		$jr_access_level = $jr_options->jr_admin_security;
	}

	if ( ! isset( $jr_access_level ) || $jr_access_level == '' ) {
		$jr_access_level = 'read'; // if there's no value then give everyone access
	}

	if ( is_user_logged_in() && !current_user_can( $jr_access_level ) ) {
		// comment out the above two lines and uncomment this line if you are using
		// wpmu and want to block back office access to everyone except admins
		// if (!is_site_admin()) {
		status_header(404);
		nocache_headers();
		include( get_404_template() );
		exit;
    }

}
