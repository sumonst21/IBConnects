/*
 * JobRoller admin jQuery functions
 * Written by AppThemes
 *
 * Copyright (c) 2010 App Themes (http://appthemes.com)
 *
 * Built for use with the jQuery library
 * http://jquery.com
 */

// <![CDATA[

/* Used for deleting theme database tables */
function jr_confirmBeforeDeleteTables() {
	return confirm( jobroller_admin_params.text_before_delete_tables );
}

/* Used for deleting theme options */
function jr_confirmBeforeDeleteOptions() {
	return confirm( jobroller_admin_params.text_before_delete_options );
}

