<?php
/**
 * Resumes custom fields HTML template.
 */
?>
<div id="resume-form-custom-fields">
<?php
	if ( $resume->resume_category ) {
		the_listing_files_editor( $resume->ID );
		jr_render_custom_form( (int) $resume->resume_category, APP_TAX_RESUME_CATEGORY, $resume->ID );
	}
?>
</div>