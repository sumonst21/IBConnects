<?php
/**
 * Admin related functions and hooks.
 *
 * @version 1.8
 * @author AppThemes
 * @package JobRoller\Admin
 * @copyright 2010 all rights reserved
 */

add_action( 'admin_enqueue_scripts', 'appthemes_load_admin_scripts' );


### Hook Callbacks

// correctly load all the scripts so they don't conflict with plugins
function appthemes_load_admin_scripts( $hook ) {
	global $is_IE;

	wp_enqueue_script('jquery-ui-tabs');
	wp_enqueue_script('media-upload'); // needed for image upload

	wp_enqueue_script('thickbox'); // needed for image upload
	wp_enqueue_style('thickbox'); // needed for image upload

	wp_enqueue_script('easytooltip', get_template_directory_uri().'/includes/js/easyTooltip.js', array('jquery'), JR_VERSION );

	wp_enqueue_style('font-awesome');

	if ( $is_IE ) { // only load this support js when browser is IE
		wp_enqueue_script('excanvas', get_template_directory_uri().'/includes/js/flot/excanvas.min.js', array('jquery'), JR_VERSION );
	}

	wp_enqueue_script('flot', get_template_directory_uri().'/includes/js/flot/jquery.flot.min.js', array('jquery'), JR_VERSION );

	$admin_pages = array(
		'toplevel_page_app-dashboard',
		'jobroller_page_app-settings',
		'jobroller_page_jr-emails',
		'jobroller_page_jr-alerts',
		'jobroller_page_jr-integration',
		'jobroller_page_app-system-info',
		'edit-pricing-plan',
		'edit-resumes-pricing-plan',
		'payments_page_app-payments-settings',
	);

	if ( ! in_array( get_current_screen()->id, $admin_pages ) ) {
		return;
	}

	wp_enqueue_script( 'admin-settings', get_template_directory_uri() . '/includes/admin/scripts/settings.js', array( 'jquery', 'media-upload', 'thickbox' ), JR_VERSION );

	/* Script variables */
	$params = array(
		'default_logo_url' => get_template_directory_uri() . '/images/logo.png',
		'remove' => __( 'Remove', APP_TD ),
		'text_before_delete_tables' => __( 'WARNING: You are about to completely delete all JobRoller database tables. Are you sure you want to proceed? (This cannot be undone)', APP_TD ),
		'text_before_delete_options' => __( 'WARNING: You are about to completely delete all JobRoller configuration options from the wp_options database table. Are you sure you want to proceed? (This cannot be undone)', APP_TD ),
	);
	wp_localize_script( 'admin-settings', 'jobroller_admin_params', $params );
}

