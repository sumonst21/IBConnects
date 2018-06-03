<?php
/**
 * JobRoller Theme Support
 * This file defines 'theme support' so WordPress knows what new features it can handle.
 *
 * @version 1.0
 * @author AppThemes
 * @package JobRoller
 * @copyright 2010 all rights reserved
 */

global $jr_options;

// Actions
add_action( 'appthemes_init', 'jr_recaptcha_support' );
add_filter( 'wp_nav_menu_items', 'jr_top_nav_links', 2, 10 );

add_theme_support( 'menus' );
add_theme_support( 'post-thumbnails', array( 'post', 'job_listing', 'resume' ) );

add_theme_support( 'app-wrapping' );

add_theme_support( 'app-versions', array(
	'app_name'        => 'JobRoller',
	'update_page'     => 'admin.php?page=app-settings&firstrun=1',
	'current_version' => JR_VERSION,
	'option_key'      => 'jobroller_version',
) );

add_theme_support( 'app-login', array(
	'login'    => 'tpl-login.php',
	'register' => 'tpl-registration.php',
	'recover'  => 'tpl-password-recovery.php',
	'reset'    => 'tpl-password-reset.php',
) );

add_theme_support( 'app-stats', array(
	'cache'       => 'today',
	'table_daily' => 'jr_counter_daily',
	'table_total' => 'jr_counter_total',
	'meta_daily'  => 'jr_daily_count',
	'meta_total'  => 'jr_total_count',
) );

add_theme_support( 'app-term-counts', array(
	'post_type'   => array( APP_POST_TYPE ),
	'post_status' => array( 'publish' ),
	'taxonomy'    => array( APP_TAX_CAT ),
) );

add_theme_support( 'app-payments', array(
	'items' => array(
		array(
			'type'  => JR_ITEM_FEATURED_LISTINGS,
			'title' => __( 'Feature on Homepage and Listings', APP_TD ),
			'meta'  => array(
				'price' => $jr_options->addons[ JR_ITEM_FEATURED_LISTINGS]['price']
			)
		),
		array(
			'type'  => JR_ITEM_FEATURED_CAT,
			'title' => __( 'Feature on Category', APP_TD ),
			'meta'  => array(
				'price' => $jr_options->addons[ JR_ITEM_FEATURED_CAT ]['price']
			)
		),
		// Resumes
		array(
			'type'  => JR_ITEM_BROWSE_RESUMES,
			'title' => __( 'Browse Resumes', APP_TD ),
			'meta'  => array(
				'price' => $jr_options->addons[ JR_ITEM_BROWSE_RESUMES ]['price']
			)
		),
		array(
			'type'  => JR_ITEM_VIEW_RESUMES,
			'title' => __( 'View Resumes', APP_TD ),
			'meta'  => array(
				'price' => $jr_options->addons[ JR_ITEM_VIEW_RESUMES ]['price']
			)
		)
	),
	'items_post_types' => array( APP_POST_TYPE ),
	'options' => $jr_options,
) );

add_theme_support( 'app-price-format', array(
	'currency_default'    => $jr_options->currency_code,
	'currency_identifier' => $jr_options->currency_identifier,
	'currency_position'   => $jr_options->currency_position,
	'thousands_separator' => $jr_options->thousands_separator,
	'decimal_separator'   => $jr_options->decimal_separator,
	'hide_decimals'       => (bool) ( ! $jr_options->decimal_separator ),
) );

add_theme_support( 'app-form-builder', array( 'show_in_menu' => true ) );

/**
 * Add reCaptcha support.
 */
function jr_recaptcha_support() {
	global $jr_options;

	if ( ! $jr_options->jr_captcha_public_key || ! $jr_options->jr_captcha_private_key ) {
		return;
	}

	if ( $jr_options->jr_captcha_enable ) {
		$support_name = 'app-recaptcha-register';
		$support[] = $support_name;
		$display_rule[ $support_name ] = $jr_options->jr_captcha_enable;
	}

	if ( $jr_options->jr_captcha_contact_forms_enable ) {
		$support_name = 'app-recaptcha-contact';
		$support[] = $support_name;
		$display_rule[ $support_name ] = $jr_options->jr_captcha_contact_forms_enable;
	}

	if ( $jr_options->jr_captcha_application_form_enable ) {
		$support_name = 'app-recaptcha-application';
		$support[] = $support_name;
		$display_rule[ $support_name ] = $jr_options->jr_captcha_application_form_enable;
	}

	if ( ! empty($support) ) {

		$support_name = 'app-recaptcha';
		$support[] = $support_name;
		$display_rule[ $support_name ] = 1;

		foreach ( $support as $name ) {
			add_theme_support( $name, array(
				'theme'        => $jr_options->jr_captcha_theme,
				'public_key'   => $jr_options->jr_captcha_public_key,
				'private_key'  => $jr_options->jr_captcha_private_key,
				'display_rule' => $display_rule[ $name ]
			) );
		}
	}
}

/**
 * Add-ons Marketplace
 *
 * @since 1.8
 */
add_theme_support( 'app-addons-mp', array(
	'product' => 'jobroller',
) );

add_theme_support( 'app-require-updater', true );
