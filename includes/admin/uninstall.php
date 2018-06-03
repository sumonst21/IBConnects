<?php
/**
 * Uninstall related code.
 *
 * @version 1.8
 * @author AppThemes
 * @package JobRoller\Admin\Uninstall
 * @copyright 2010 all rights reserved
 *
 */

/**
 * Deletes all JR database tables.
 */
function jr_delete_db_tables() {
	global $jr_db_tables;

	$db_tables = array_merge( $jr_db_tables, $jr_legacy_db_tables );

	foreach ( $db_tables as $key => $value ) {
		scb_uninstall_table( $value );

		printf( __( "Table '%s' has been deleted.", APP_TD ), $value );
		echo '<br/>';
	}

	scb_uninstall_table( 'app_pop_daily' );
	_e( "Table 'app_pop_daily' has been deleted.", APP_TD );

	scb_uninstall_table( 'app_pop_total' );
	_e( "Table 'app_pop_total' has been deleted.", APP_TD );
}

/**
 * Deletes all JR related options from 'wp_options'.
 */
function jr_delete_all_options() {
	global $wpdb;

	$sql = "DELETE FROM " . $wpdb->options
			. " WHERE option_name like 'jr_%'";
	$wpdb->query( $sql );

	echo __( "All JobRoller options have been deleted.", APP_TD );
}
