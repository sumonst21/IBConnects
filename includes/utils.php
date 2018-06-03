<?php
/**
 * Utility functions and hook callbacks. These can go away at any time. Don't rely on them.
 *
 * @version 1.8
 * @author AppThemes
 * @package JobRoller\Helper
 * @copyright 2010 all rights reserved
 */

add_action( 'wp_login','appthemes_last_login' );

// add a very low priority action to make sure any extra settings have been added to the permalinks global
add_action( 'admin_init', '_jr_enable_permalink_settings', 999999 );

add_action( 'pre_get_posts', '_jr_maybe_load_other_archive_integraton_jobs' );

### Hooks Callbacks

/**
 * Insert the last login date for each user.
 */
function appthemes_last_login($login) {
	$user = get_user_by( 'login', $login );
	update_user_meta( $user->ID, 'last_login', current_time('mysql') );
}

/**
 * Temporary workaround for wordpress bug #9296 http://core.trac.wordpress.org/ticket/9296
 * Although there is a hook in the options-permalink.php to insert custom settings,
 * it does not actually save any custom setting which is added to that page.
 */
function _jr_enable_permalink_settings() {
	global $new_whitelist_options;

	// save hook for permalinks page
	if ( isset( $_POST['permalink_structure'] ) || isset( $_POST['category_base'] ) ) {
		check_admin_referer( 'update-permalink' );

		$option_page = 'permalink';

		$capability = 'manage_options';
		$capability = apply_filters( "option_page_capability_{$option_page}", $capability );

		if ( !current_user_can( $capability ) ) {
			wp_die( __( 'Cheatin&#8217; uh?', APP_TD ) );
		}

		// get extra permalink options
		$options = $new_whitelist_options[$option_page];

		if ( $options ) {
			foreach( $options as $option ) {
				$option = trim( $option );
				$value = null;
				if ( isset( $_POST[$option] ) ) {
					$value = $_POST[$option];
				}
				if ( !is_array( $value ) ) {
					$value = trim( $value );
				}
				$value = stripslashes_deep( $value );

				// get the old values to merge
				$db_option = get_option( $option );

				if ( is_array( $db_option ) ) {
					update_option( $option, array_merge( $db_option, $value ) );
				} else {
					update_option( $option, $value );
				}
			}
		}

		/**
		 *  Handle settings errors
		 */
		set_transient( 'settings_errors', get_settings_errors(), 30 );
	}
}


### Helper Functions

/**
 * Get the visitor IP so we can include it with the job submission.
 */
if ( ! function_exists( 'jr_getIP' ) ) {
function jr_getIP() {

	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {  //check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {  //to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	$ip = ( $ip == '::1' ? '127.0.0.1' : $ip );

	// avoid multiple IP's
	$ip = explode( ',', $ip );
	$ip = reset( $ip );

	return $ip;
}
}

/**
 * Get the date/time of the post.
 */
if ( ! function_exists( 'jr_ad_posted' ) ) {
function jr_ad_posted( $m_time ) {
	$time = get_post_time( 'G', true );
	$time_diff = time() - $time;

	if ( $time_diff > 0 && $time_diff < 24 * 60 * 60 ) {
		$h_time = sprintf( __( '%s ago', APP_TD ), human_time_diff( $time ) );
	} else {
		$h_time = mysql2date( get_option( 'date_format' ), $m_time );
	}

	echo $h_time;
}
}

if ( ! function_exists( 'let_to_num' ) ) {
function let_to_num( $v ) {
	$l = substr( $v, -1 );
	$ret = substr( $v, 0, -1 );
	switch ( strtoupper( $l ) ) {
		case 'P':
			$ret *= 1024;
		case 'T':
			$ret *= 1024;
		case 'G':
			$ret *= 1024;
		case 'M':
			$ret *= 1024;
		case 'K':
			$ret *= 1024;
			break;
	}
	return $ret;
}
}

/**
 * Get the server country.
 */
function jr_get_server_country() {

	// Get user country
	if ( isset( $_SERVER['HTTP_X_FORWARD_FOR'] ) ) {
		$ip = $_SERVER['HTTP_X_FORWARD_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	$ip = strip_tags( $ip );
	$country = substr( $_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2 );

	$result = wp_remote_get( 'http://api.hostip.info/country.php?ip=' . $ip );
	if ( ! is_wp_error( $result ) && strtolower( $result['body'] ) != 'xx' ) {
		$country = $result['body'];
	}

	return strtolower( $country );
}

/**
 * Calculate and return the week start date.
 */
function jr_week_start_date( $week, $year, $format = "d-m-Y" ) {

	$first_day_year = date( "N", mktime( 0, 0, 0, 1, 1, $year ) );
	if ( $first_day_year < 5 ) {
		$shift = -($first_day_year - 1) * 86400;
	} else {
		$shift = (8 - $first_day_year) * 86400;
	}

	if ( $week > 1 ) {
		$week_seconds = ($week - 1) * 604800;
	} else {
		$week_seconds = 0;
	}
	$timestamp = mktime( 0, 0, 0, 1, 1, $year ) + $week_seconds + $shift;

	return date( $format, $timestamp );
}

/**
 * Allow post status translations.
 */
function jr_post_statuses_i18n( $status ) {
	$statuses = array(
		'draft' => __( 'Draft', APP_TD ),
		'pending' => __( 'Pending Review', APP_TD ),
		'private' => __( 'Private', APP_TD ),
		'publish' => __( 'Published', APP_TD ),
		'expired' => __( 'Expired', APP_TD )
	);

	if ( isset( $statuses[$status] ) ) {
		$i18n_status = $statuses[$status];
	} else {
		$i18n_status = $status;
	}
	return $i18n_status;
}

/**
 * Temporarily hook 3d other party feeds in jobs archive hook '_jobs_archive' introduced in 1.8.
 *
 * Will be removed as plugins are updated.
 *
 * @since 1.8
 */
function _jr_maybe_load_other_archive_integraton_jobs( $wp_query ) {
	global $jr_options, $wp_query;

	if ( ! $wp_query->is_main_query() ) {
		return;
	}

	if ( ! is_post_type_archive( APP_POST_TYPE ) ) {
		return;
	}

	$job_feeds = array(
		'careerjet'		=> 'jrcj_careerjet_html_placeholder',
		'linkedin'		=> 'jrlkdp_linkedin_html_placeholder',
		'simplyhired'	=> 'jrsh_simplyhired_html_placeholder',
	);

	foreach( $job_feeds as $feed => $callback ) {
		$option_position = "jr_{$feed}_results_position";
		$option_listings = "jr_{$feed}_all_listings";

		if ( $jr_options->$option_listings ) {
			$position = $jr_options->$option_position ? $jr_options->$option_position : 'before';

			if ( function_exists( $callback ) ) {
				add_action( $position . '_jobs_archive', $callback, 10, 2 );
			}
		}
	}

}

/**
 * Temp function. Do not rely on it.
 */
function _jr_is_recurring_available() {
	return true;
}
