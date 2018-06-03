<?php
/*
Plugin Name: AppThemes Recurring Payments
Description: Create subscription-based billing plans to automatically collect payments. Requires recurring enabled themes from AppThemes.
AppThemes ID: recurring-payments
Version: 1.0
Author: AppThemes
Author URI: https://www.appthemes.com
Text Domain: appthemes-recurring-payments
*/

add_action( 'init', 'appthemes_recurring_payments_setup' );

function appthemes_recurring_payments_setup() {

	if ( !current_theme_supports( 'app-payments' ) ) {
	  	add_action( 'admin_notices', 'appthemes_recurring_display_version_warning' );
		return;
	}

	require dirname(__FILE__) . '/load.php';

}


function appthemes_recurring_display_version_warning(){
	$message = __( 'AppThemes Recurring Payments could not run.', 'appthemes-recurring-payments' );
	if( !current_theme_supports( 'app-payments' ) ) {
		$message = __( 'AppThemes Recurring Payments does not support the current theme. Please use a compatible AppThemes Product.', 'appthemes-recurring-payments' );
	}

	echo '<div class="error fade"><p>' . esc_html( $message ) .'</p></div>';
	deactivate_plugins( plugin_basename( __FILE__ ) );
}
