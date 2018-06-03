<?php
/**
 * JobRoller core file. This file is the backbone and includes all the theme dependent files.
 * Modifying this will void your warranty and could cause problems with your instance of JR.
 * Proceed at your own risk!
 *
 * @package JobRoller\Core
 * @author AppThemes
 * @url https://www.appthemes.com
 */

// setup the custom post types and taxonomies as constants
// do not modify this after installing or it will break your theme!

define( 'APP_POST_TYPE', 'job_listing' );
define( 'APP_POST_TYPE_RESUME', 'resume' );
define( 'APP_TAX_CAT', 'job_cat' );
define( 'APP_TAX_TAG', 'job_tag' );
define( 'APP_TAX_TYPE', 'job_type' );
define( 'APP_TAX_SALARY', 'job_salary' );
define( 'APP_TAX_RESUME_SPECIALITIES', 'resume_specialities' );
define( 'APP_TAX_RESUME_GROUPS', 'resume_groups' );
define( 'APP_TAX_RESUME_LANGUAGES', 'resume_languages' );
define( 'APP_TAX_RESUME_CATEGORY', 'resume_category' );
define( 'APP_TAX_RESUME_JOB_TYPE', 'resume_job_type' );

// meta keys
define( 'JR_JOB_DURATION_META', JR_FIELD_PREFIX . 'job_duration' );

define( 'JR_ITEM_FEATURED_LISTINGS', JR_FIELD_PREFIX . 'featured-listings' );
define( 'JR_ITEM_FEATURED_CAT', JR_FIELD_PREFIX . 'featured-cat' );

define( 'JR_ITEM_BROWSE_RESUMES', JR_FIELD_PREFIX . 'browse_resumes' );
define( 'JR_ITEM_VIEW_RESUMES', JR_FIELD_PREFIX . 'view_resumes' );


### Hooks

// Actions
add_action( 'init', 'buffer_the_output' );
add_action( 'init', 'jr_check_rewrite_rules_transient', 9999 );
add_action( 'appthemes_notices', 'jr_notices' );
add_action( 'wp_ajax_jr_ajax_validate_recaptcha', 'jr_ajax_validate_recaptcha' );
add_action( 'wp_ajax_nopriv_jr_ajax_validate_recaptcha', 'jr_ajax_validate_recaptcha' );

add_action( 'after_setup_theme', 'remove_default_notices', 9999 );
add_action( 'appthemes_display_notice', 'jr_custom_notices', 10, 2 );


// Filters
add_filter( 'get_pagenum_link', 'location_query_arg' );
add_filter( 'the_excerpt', 'custom_excerpt' );
add_filter( 'mce_buttons', 'jr_editor_modify_buttons', 10, 2 );


### Other

// Tables
$jr_db_tables = array( 'jr_alerts' );

foreach ( $jr_db_tables as $jr_db_table ) {
	scb_register_table( $jr_db_table );
}

// Legacy Tables
$jr_legacy_db_tables = array( 'jr_orders', 'jr_job_packs', 'jr_customer_packs' );

foreach ( $jr_legacy_db_tables as $jr_db_table ) {
	scb_register_table( $jr_db_table );
}

### Dependent Files

$load_files = array(
	'options.php',
	'post-types.php',
	'setup-theme.php',
	'views.php',
	'forms.php',
	'helper.php',
	'utils.php',
	'deprecated.php',
	'capabilities.php',
	'template-tags.php',

	'customizer.php',
	'log.php',
	'hooks.php',
	'security.php',
	'sidebars.php',
	'comments.php',
	'widgets.php',
	'emails.php',
	'geolocation.php',
	'actions.php',
	'alerts.php',
	'stats.php',
	'users.php',
	'resumes.php',
	'packs.php',
	'featured.php',
	'theme-support.php',
	'cron.php',
	'payments.php',
	'plan-purchase.php',
	'plan-activate.php',
	'job-status.php',
	'search.php',
	'uploads.php',
	'custom-forms.php',
	'indeed/indeed.php',
);
appthemes_load_files( dirname( __FILE__ ) . '/', $load_files );


// logging
$jr_log = new jrLog();


### Views

$views = array(
	'JR_View',
	'JR_Registration',
	'JR_Home_Archive',
	'JR_Blog_Page',
	'JR_Contact_Page',
	'JR_Dashboard_Page',
	'JR_User_Profile_Page',
	'JR_Date_Archive_Page',
	'JR_Terms_Conditions_Page',
	'JR_Resume_Edit_Page',
	'JR_Job_Edit_Page',
	'JR_Job_Submit_Page',
	'JR_Packs_Purchase_Page',
	'JR_Resume_Plans_Purchase_Page',

	'JR_Date_Archive',
	'JR_Single',
	'JR_Job_Single',
	'JR_Jobs_Archive',
	'JR_Resumes_Archive',
	'JR_Contact',
	'JR_Job_Edit',
	'JR_Job_Relist',
	'JR_Job_Submit',
	'JR_Job_Preview',
	'JR_Packs_Purchase',
	'JR_Resumes_Plans_Purchase',
	'JR_Resume_Edit',
	'JR_Resume_Submit',
	'JR_Resume_Single',
	'JR_Order_Go_Back',
	'JR_Search',
	'JR_Dashboard',
	'JR_Lister_Dashboard',
	'JR_Seeker_Dashboard',
	'JR_Order_Summary',
	'JR_User_Profile',
	'JR_Author',
);
appthemes_add_instance( $views );

appthemes_add_instance( 'JR_Widget_Facebook' );

### Forms

$forms = array(
	'JR_Register_Form',
	'JR_Jobs_Filter_Form',
	'JR_Job_Seeker_Prefs_Form',
	'JR_Job_Seeker_Alerts_Form',
	'JR_Job_Application_Form',
	'JR_Resume_Edit_Form',
);
appthemes_add_instance( $forms );


### Others
APP_Mail_From::init();


### Admin Includes

if ( is_admin() ) {

	// importer
	require_once( APP_FRAMEWORK_DIR . '/admin/importer.php' );

	$load_files = array(
		'admin.php',
		'settings.php',
		'dashboard.php',
		'emails.php',
		'alerts.php',
		'integration.php',
		'subscriptions.php',
		'post-types-lists.php',
		'dashboard.php',
		'system-info.php',
		'payments.php',
		'plans-job.php',
		'plans-resume.php',
		'meta-box.php',
		'theme-upgrade.php',

		// modules
		'importer.php',
		'addons-mp/load.php',

		// install/uninstall
		'install.php',
		'uninstall.php',
	);
	appthemes_load_files( dirname( __FILE__ ) . '/admin/', $load_files );

	$classes = array(
		'JR_Admin_Dashboard',
		'JR_Settings_Admin'		 => array( $jr_options ),
		'JR_Emails_Admin'		 => array( $jr_options ),
		'JR_Alerts_Admin'		 => array( $jr_options ),
		'JR_Integration_Admin'	 => array( $jr_options ),
		'JR_Subscriptions_Admin',
		'JR_Theme_System_Info',
	);

	appthemes_add_instance( $classes );

	// integrate custom permalinks in WP permalinks page
	$settings = appthemes_get_instance('JR_Settings_Admin');
	add_action( 'admin_init', array( $settings, 'init_integrated_options' ), 10 );

} else {

	// child-themeable templates

	// @todo: strip out all code from functions

	appthemes_load_template( 'includes/forms/register/register-form.php' );
	appthemes_load_template( 'includes/forms/application/application-form.php' );
	appthemes_load_template( 'includes/forms/filter/filter-form.php' );
	appthemes_load_template( 'includes/forms/share/share-form.php' );
	appthemes_load_template( 'includes/forms/login/login-form.php' );
	appthemes_load_template( 'includes/forms/resume/edit_parts.php' );
	appthemes_load_template( 'includes/forms/resume/contact_parts.php' );
	appthemes_load_template( 'includes/forms/seeker-prefs/seeker-prefs-form.php' );
	appthemes_load_template( 'includes/forms/seeker-alerts/seeker-alerts-form.php' );

}


### Theme init

// run the appthemes_init() action hook
appthemes_init();


### Hook Callbacks

/**
 * Buffer the output so headers work correctly.
 */
function buffer_the_output() {
	ob_start();
}

/**
 *  Use a transient to flush the rewrite rules.
 */
function jr_check_rewrite_rules_transient() {

	if ( get_transient( 'jr_flush_rewrite_rules' ) ) {
		delete_transient( 'jr_flush_rewrite_rules' );
		// files required for hard reset of rewrite rules
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/misc.php');
		flush_rewrite_rules();
	}

}

/**
 * Displays notices.
 */
if ( ! function_exists( 'jr_notices' ) ) {
function jr_notices() {
	global $post, $message, $errors;

	if ( $err_obj = jr_get_listing_error_obj() ) {
		if ( $err_obj->get_error_codes() ) {
			$errors = $err_obj;
		}
	}

	if ( is_wp_error( $errors ) ) {
		jr_show_errors( $errors );
		return;
	} elseif ( !empty( $message ) ) {
		if ( is_array( $message ) ) {
			$message = reset( $message );
		}
		appthemes_display_notice( 'success', strip_tags( stripslashes( $message ) ) );
		return;
	}

	if ( isset( $post ) ) {

		// dashboard notices
		if ( $post->ID == JR_Dashboard_Page::get_id() ) {

			if ( isset( $_GET['relist_success'] ) && is_numeric( $_GET['relist_success'] ) ) {
				appthemes_display_notice( 'success', __( 'Job relisted successfully', APP_TD ) );
			} elseif ( isset( $_GET['edit_success'] ) && is_numeric( $_GET['edit_success'] ) ) {
				appthemes_display_notice( 'success', __( 'Job edited successfully', APP_TD ) );
			} elseif ( isset( $_POST['payment_status'] ) && strtolower( $_POST['payment_status'] ) == 'completed' ) {
				appthemes_display_notice( 'success', __( 'Thank you for your Order!', APP_TD ) );
			}

			// single resume notices
		} else {

			if ( is_singular( APP_POST_TYPE_RESUME ) ) {

				if ( isset( $_GET['resume_contact'] ) && is_numeric( $_GET['resume_contact'] ) ) {
					if ( $_GET['resume_contact'] > 0 ) {
						appthemes_display_notice( 'success', __( 'Your message was sent', APP_TD ) );
					} else {
						appthemes_display_notice( 'error', __( 'Could not send message at this time. Please try again later', APP_TD ) );
					}
				}

			}
		}

	}
}
}

/**
 * Validates reCaptchas.
 */
function jr_ajax_validate_recaptcha() {

	$support = strval( $_POST['support'] );
	$nonce = strval( $_POST['nonce'] );

	if ( ! wp_verify_nonce( $nonce, 'jr-nonce' ) ) {
		die( 'Busted!' );
	}

	$errors = jr_validate_recaptcha( $support );
	if ( $errors && sizeof( $errors ) > 0 && $errors->get_error_code() ) {
		echo $errors->get_error_message();
	} else {
		echo "1";
	}
	die();
}

/**
 * Fixes location encoding in URL's.
 */
function location_query_arg( $link ) {

	if ( ! empty( $_GET['location'] ) ) {
		$location = wp_strip_all_tags( $_GET['location'] );
		$link = add_query_arg( 'location', urlencode( utf8_uri_encode( $location ) ), $link );
	}

	return $link;
}

/**
 * Custom excerpt.
 */
function custom_excerpt( $text ) {
	global $post;
	return str_replace( ' [...]', '&hellip; <a href="' . get_permalink( $post->ID ) . '" class="more">' . __( 'read more', APP_TD ) . '</a>', $text );
}

/**
 * Modify available buttons in html editor.
 *
 * @since 1.7.3
 */
function jr_editor_modify_buttons( $buttons, $editor_id ) {
	if ( is_admin() || ! is_array( $buttons ) ) {
		return $buttons;
	}

	$remove = array( 'wp_more', 'spellchecker' );

	return array_diff( $buttons, $remove );
}

/**
 * @since 1.8
 */
function remove_default_notices() {
	remove_action( 'appthemes_display_notice', array( 'APP_Notices', 'outputter' ), 10 );
}

/**
 * @since 1.8
 */
function jr_custom_notices( $class, $msgs ) {
	//$msgs = array_unique( $msgs );
?>
	<div class="notice <?php echo esc_attr( $class ); ?>">
		<?php foreach ( $msgs as $msg ): ?>
			<div><i class="icon dashicons-before"></i><?php echo $msg; ?></div>
		<?php endforeach; ?>
	</div>
<?php
}
