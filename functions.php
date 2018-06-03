<?php
/**
 * Theme functions file
 *
 * DO NOT MODIFY THIS FILE. Make a child theme instead: http://codex.wordpress.org/Child_Themes
 *
 * @package JobRoller
 * @author AppThemes
 */

// Define vars and globals.
global $app_version, $app_form_results, $jr_log, $app_abbr, $jr_options;

// Current version.
$app_theme = 'JobRoller';
$app_abbr = 'jr';
$app_version = '1.8.7';

define( 'APP_TD', 'jobroller' );
define( 'JR_VERSION' , $app_version );
define( 'JR_FIELD_PREFIX', '_' . $app_abbr . '_' );

// Include framework and modules.
require_once( dirname(__FILE__) . '/framework/load.php' );
require_once( dirname(__FILE__) . '/theme-framework/load.php' );
require_once( dirname(__FILE__) . '/includes/payments/load.php' );
require_once( dirname(__FILE__) . '/includes/stats/load.php' );
require_once( dirname(__FILE__) . '/includes/recaptcha/load.php' );
require_once( dirname(__FILE__) . '/includes/custom-forms/form-builder.php' );
require_once( dirname(__FILE__) . '/includes/widgets/load.php' );
require_once( dirname(__FILE__) . '/includes/core.php' );

scb_register_table( 'app_pop_daily', 'jr_counter_daily' );
scb_register_table( 'app_pop_total', 'jr_counter_total' );
