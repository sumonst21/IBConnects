<?php
/**
 * Jobs custom fields HTML template.
 */
?>
<div id="job-form-custom-fields">
<?php
	if ( $job->category ) {
		the_listing_files_editor( $job->ID );
		jr_render_custom_form( (int) $job->category, APP_TAX_CAT, $job->ID );
	}
?>
</div>