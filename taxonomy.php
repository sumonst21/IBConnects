<?php
	$taxonomies = array( APP_TAX_RESUME_SPECIALITIES, APP_TAX_RESUME_GROUPS, APP_TAX_RESUME_LANGUAGES, APP_TAX_RESUME_CATEGORY, APP_TAX_RESUME_JOB_TYPE );
	if ( APP_POST_TYPE_RESUME == get_post_type() || is_tax( $taxonomies ) ) {
		appthemes_load_template('archive-resume.php');
	} else{
		appthemes_load_template('archive.php');
	}