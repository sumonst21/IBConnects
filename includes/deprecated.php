<?php
/**
 * Deprecated functions.
 *
 * @version 1.7
 * @author AppThemes
 * @package JobRoller\Deprecated
 * @copyright 2010 all rights reserved
 */

// provide compat with old 'yes/no' options with older plugins and themes
add_action( 'init', '_jr_init_deprecated_yes_no_options', 99 );

// keep old single option values mirrored with the global 'jr_options' for compatibility
add_action( 'update_option_jr_options', '_jr_maybe_sync_single_legacy_single_options_18', 10, 2 );


/**
 * tinyMCE text editor.
 *
 * @deprecated 1.7.3
 */
function jr_tinymce( $width = '', $height = '' ) {
	_deprecated_function( __FUNCTION__, '1.7.3', 'wp_editor()' );
	return;
}

/**
 * Was generating admin system info page.
 *
 * @deprecated 1.7.2
 */
function jr_system_info() {
	_deprecated_function( __FUNCTION__, '1.7.2' );
}

/**
 * Get Page URL.
 *
 * @deprecated 1.8
 */
function jr_get_current_url( $url = '' ) {
	_deprecated_function( __FUNCTION__, '1.8', 'get_the_jr_jobs_base_url' );

	return get_the_jr_jobs_base_url( $url );
}

/**
 * Was processing resume form parts.
 * @deprecated 1.8
 */
function jr_process_resume_parts() {
	global $post;

	_deprecated_function( __FUNCTION__, '1.8', 'jr_process_resume' );

	return jr_process_resume( $post );
}

/**
 * @deprecated 1.8
 */
function jr_get_taxonomy_terms( $taxonomy ) {
	_deprecated_function( __FUNCTION__, '1.8', 'jr_get_tax_terms' );

	return jr_get_tax_terms( $taxonomy );
}

/**
 * @deprecated 1.8
 */
function jr_output_alert_terms(  $terms, $user_options = array() ) {
	_deprecated_function( __FUNCTION__, '1.8', 'jr_output_alert_terms_items' );

	jr_output_alert_terms_items( $terms, $user_options );
}

/**
 * @deprecated 1.8
 */
function appthemes_get_stats() {
	_deprecated_function( __FUNCTION__, '1.8' );
	return;
}

/**
 * @deprecated 1.8
 */
function appthemes_get_profile_pic() {
	_deprecated_function( __FUNCTION__, '1.8' );
	return;
}

function appthemes_author_permalink() {
	_deprecated_function( __FUNCTION__, '1.8' );
	return;
}

/**
 * @deprecated 1.8
 */
function appthemes_round() {
	_deprecated_function( __FUNCTION__, '1.8' );
	return;
}

/**
 * @deprecated 1.8
 */
function appthemes_clean_price() {
	_deprecated_function( __FUNCTION__, '1.8' );
	return;
}

/**
 * @deprecated 1.8
 */
function appthemes_error_msg() {
	_deprecated_function( __FUNCTION__, '1.8' );
	return;
}

/**
 * @deprecated 1.8
 */
function appthemes_dashboard_appthemes() {
	_deprecated_function( __FUNCTION__, '1.8' );
	return;
}

/**
 * @deprecated 1.8
 */
function appthemes_dashboard_twitter() {
	_deprecated_function( __FUNCTION__, '1.8' );
	return;
}

/**
 * @deprecated 1.8
 */
function appthemes_dashboard_forum() {
	_deprecated_function( __FUNCTION__, '1.8' );
	return;
}

/**
 * @deprecated 1.8
 */
function appthemes_highlight_search_term() {
	_deprecated_function( __FUNCTION__, '1.8' );
	return;
}

/**
 * @deprecated 1.8
 */
function appthemes_first_login() {
	_deprecated_function( __FUNCTION__, '1.8' );
	return;
}

/**
 * @deprecated 1.8
 */
function appthemes_get_last_login() {
	_deprecated_function( __FUNCTION__, '1.8' );
	return;
}

/**
 * @deprecated 1.8
 */
function appthemes_get_reg_date() {
	_deprecated_function( __FUNCTION__, '1.8' );
	return;
}

/**
 * @deprecated 1.8
 */
function appthemes_custom_upload_mimes() {
	_deprecated_function( __FUNCTION__, '1.8' );
	return;
}

/**
 * @deprecated 1.8
 */
function appthemes_search_suggest() {
	_deprecated_function( __FUNCTION__, '1.8' );
	return;
}

/**
 * @deprecated 1.8
 */
function appthemes_delete_db_tables() {
	_deprecated_function( __FUNCTION__, '1.8' );
	return;
}

/**
 * @deprecated 1.8
 */
function appthemes_delete_all_options() {
	_deprecated_function( __FUNCTION__, '1.8' );
	return;
}

/**
 * @deprecated 1.8
 */
function the_listing_files( $post_id = 0 ) {
	_deprecated_function( __FUNCTION__, '1.8', 'the_job_listing_files' );
	return the_job_listing_files( $post_id );
}

/**
 * @deprecated 1.8
 */
function the_listing_logo_editor( $post_id = 0 ) {
	_deprecated_function( __FUNCTION__, '1.8', 'the_job_listing_logo_editor' );
	return the_job_listing_logo_editor( $post_id );
}

/**
 * @deprecated 1.8
 */
function jr_render_job_form( $cat, $post_id = 0 ) {
	_deprecated_function( __FUNCTION__, '1.8', 'jr_render_custom_form' );
	jr_render_custom_form( $cat, APP_TAX_CAT, $post_id );
}

/**
 * @deprecated 1.8
 */
function jr_submit_resume_form( $resume_id = 0 ) {
	_deprecated_function( __FUNCTION__, '1.8' );
	jr_submit_resume_form( $resume_id );
}

/**
 * @deprecated 1.8
 */
function _jr_get_location_search_query_vars() {
	_deprecated_function( __FUNCTION__, '1.8' );
	return JR_Search::get_location_search_query_vars();
}

/**
 * @deprecated 1.8
 */
function jr_handle_company_logo( $post_id ) {
	_deprecated_function( __FUNCTION__, '1.8', 'jr_handle_image_upload' );
	return jr_handle_image_upload( $post_id );
}

/**
 * Hook every 'yes'/'no' to the action 'option_$option' to get boolean results instead.
 *
 * This is temporary until all references to 'get_option()' are replaced with a call to '$jr_options'.
 *
 * @deprecated 1.8
 */
function _jr_init_deprecated_yes_no_options() {

	$keep_legacy_sync = get_option('_jr_keep_legacy_options_sync');

	if ( ! $keep_legacy_sync ) {
		return;
	}

	$yes_no_options = _jr_get_deprecated_yes_no_options();

	foreach( $yes_no_options as $option ) {
		add_action( 'option_' . $option, '_jr_yes_no_compat' );
	}

}

/**
 * Keep old single option values mirrored with the global 'jr_options' for compatibility sake.
 *
 * Temporary use until all 'get_option()' calls to JR options are replaced with '$jr_options->option'.
 *
 * @deprecated 1.8
 */
function _jr_maybe_sync_single_legacy_single_options_18( $old_value, $value ) {

	$keep_legacy_sync = get_option('_jr_keep_legacy_options_sync');

	if ( ! $keep_legacy_sync ) {
		return;
	}

	foreach( $value as $key => $val ) {
		update_option( $key, $val );
	}

}

/**
 * Return 'yes' or 'no' for options that still rely on 'yes' or 'no' comparisons and are already set on the global '$jr_options'.
 *
 * This is temporary until all references to 'get_option()' are replaced with a call to '$jr_options'.
 *
 * @deprecated 1.8
 */
function _jr_yes_no_compat( $value ) {
	global $jr_options;

	$option = str_replace( 'option_', '', current_filter() );

	if ( is_bool( $jr_options->$option ) || empty( $jr_options->$option )  ) {

		if ( ! is_admin() ) {
			_deprecated_function( "get_option('".$option."')", '1.8', '$jr_options->'.$option.' to retrieve option from database' );
		}

		return $jr_options->$option ? 'yes' : 'no';
	}

	return $jr_options->$option;
}

function _jr_get_deprecated_yes_no_options() {

	$yes_no_options = array(
		'jr_use_logo',
		'jr_disable_blog',
		'jr_allow_registration_password',
		'jr_enable_terms_conditions',
		'jr_show_sidebar',
		'jr_show_searchbar',
		'jr_show_filterbar',
		'jr_show_empty_categories',
		'jr_jobs_charge',
		'jr_allow_relist',
		'jr_allow_editing',
		'jr_submit_cat_required',
		'jr_submit_cat_editable',
		'jr_jobs_require_moderation',
		'jr_editing_needs_approval',
		'jr_ad_stats_all',
		'jr_submit_how_to_apply_display',
		'jr_enable_salary_field',
		'jr_html_allowed',
		'jr_allow_job_seekers',
		'jr_allow_recruiters',
		'jr_resume_require_subscription',
		'jr_resume_show_contact_form',
		'jr_captcha_enable',
		'jr_enable_header_banner',
		'jr_enable_listing_banner',
		'jr_debug_mode',
		'jr_enable_log',
		'jr_google_jquery',
		'jr_remove_wp_generator',
		'jr_remove_admin_bar',

		'jr_job_alerts',
		'jr_job_alerts_feed',

		'jr_nu_admin_email',
		'jr_new_ad_email',
		'jr_new_job_email_owner',
		'jr_expired_job_email_owner',
		'jr_bcc_apply_emails',
		'jr_nu_custom_email',

		'jr_indeed_front_page',
		'jr_indeed_all_listings',

		'jr_careerjet_front_page',
		'jr_careerjet_all_listings',

		'jr_simplyhired_front_page',
		'jr_simplyhired_all_listings',

		'jr_linkedin_front_page',
		'jr_linkedin_all_listings',
	);

	return $yes_no_options;
}

/**
 * Displayed Google reCaptcha 1.0.
 *
 * @deprecated 1.8.3
 */
function appthemes_recaptcha() {
	_deprecated_function( __FUNCTION__, '1.8.3', 'appthemes_display_recaptcha()' );

	appthemes_display_recaptcha();
}


/**
 * Sends custom new user notification.
 *
 * @deprecated 1.8.6
 * @deprecated Use jr_new_user_notification()
 * @see jr_new_user_notification()
 *
 * @param int $user_id
 * @param string $plaintext_pass (optional)
 *
 * @return void
 */
function app_new_user_notification( $user_id, $plaintext_pass = '' ) {
	_deprecated_function( __FUNCTION__, '1.8.6', 'jr_new_user_notification' );
	jr_new_user_notification( $user_id, $plaintext_pass );
}

