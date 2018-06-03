<?php
/**
 * Job Seeker Preferences form.
 * Outputs the Job Seeker Preferences form.
 *
 * @version 1.4
 * @author AppThemes
 * @package JobRoller
 * @copyright 2010 all rights reserved
 */

function jr_job_seeker_prefs_form() {
	global $post;

	$user_id = get_current_user_id();

	$career_status 			= get_user_meta($user_id, 'career_status', true);
	$willing_to_relocate 	= get_user_meta($user_id, 'willing_to_relocate', true);
	$willing_to_travel 		= get_user_meta($user_id, 'willing_to_travel', true);
	$keywords 				= get_user_meta($user_id, 'keywords', true);
	$search_location 		= get_user_meta($user_id, 'search_location', true);
	$job_types 				= get_user_meta($user_id, 'job_types', true);
	$availability_month 	= get_user_meta($user_id, 'availability_month', true);
	$availability_year 		= (int) get_user_meta($user_id, 'availability_year', true);
?>
<form action="<?php echo esc_url( add_query_arg( 'tab', 'prefs#prefs', get_permalink( JR_Dashboard_Page::get_id() ) ) ); ?>" method="post" id="submit_form" class="submit_form main_form">

		<fieldset>
			<legend><?php _e('Publicly visible details', APP_TD); ?></legend>
			<p><?php _e('These options control what is shown publicly on your resumes.', APP_TD); ?></p>

			<p class="optional">
				<label for="availability_month"><?php _e('Your Availability <small>(Leave blank for immediate availability)</small>', APP_TD); ?></label>
				<span class="date_field_wrap">
					<?php jr_dropdown_months( $availability_month ); ?>
					<input type="text" class="text" name="availability_year" maxlength="4" size="4" placeholder="<?php esc_attr_e('YYYY',APP_TD); ?>" value="<?php echo esc_attr( $availability_year ); ?>" id="availability_year" />
				</span>
			</p>
		</fieldset>

		<fieldset>
			<legend><?php _e('Your Career', APP_TD); ?></legend>
			<p>
				<label for="career_status"><?php _e('Career status', APP_TD); ?></label>
				<select name="career_status" id="career_status">
					<option <?php selected( $career_status, 'looking' ); ?> value="looking"><?php _e( 'Actively looking', APP_TD ); ?></option>
						<option <?php selected( $career_status, 'open' ); ?> value="open"><?php _e( 'Open to new opportunities', APP_TD ); ?></option>
						<option <?php selected( $career_status, 'notlooking' ); ?> value="notlooking"><?php _e( 'Not actively looking', APP_TD ); ?></option>
				</select>
			</p>
			<p>
				<label for="willing_to_relocate"><?php _e('Are you willing to relocate?', APP_TD); ?></label>
				<select name="willing_to_relocate" id="willing_to_relocate">
					<option <?php selected( $willing_to_relocate, 'yes'); ?> value="yes"><?php _e('Yes', APP_TD); ?></option>
					<option <?php selected( $willing_to_relocate, 'no'); ?> value="no"><?php _e('No', APP_TD); ?></option>
				</select>
			</p>
			<p>
				<label for="willing_to_travel"><?php _e('Are you willing to travel?', APP_TD); ?></label>
				<select name="willing_to_travel" id="willing_to_travel">
					<option <?php selected( $willing_to_travel, '100' ); ?> value="100"><?php _e( '100% willing to travel', APP_TD ); ?></option>
					<option <?php selected( $willing_to_travel, '75' ); ?> value="75"><?php _e( 'Fairly willing to travel', APP_TD ); ?></option>
					<option <?php selected( $willing_to_travel, '50' ); ?> value="50"><?php _e( 'Not very willing to travel', APP_TD ); ?></option>
					<option <?php selected( $willing_to_travel, '25' ); ?> value="25"><?php _e( 'Interested in local opportunities only', APP_TD ); ?></option>
					<option <?php selected( $willing_to_travel, '0' ); ?> value="0"><?php _e( 'Not willing to travel/working from home', APP_TD ); ?></option>
				</select>
			</p>
		</fieldset>

		<fieldset>
			<legend><?php _e('Other Information', APP_TD); ?></legend>
			<p><?php _e('These options control what job recommendations your receive on your dashboard.', APP_TD); ?></p>

			<p>
				<label for="keywords"><?php _e('Key Words <small>(comma separated)</small>', APP_TD); ?></label>
				<input type="text" class="tags text" name="keywords" id="keywords" placeholder="<?php esc_attr_e('e.g. Web Design, Designer', APP_TD); ?>" value="<?php echo esc_attr( $keywords ); ?>" />
			</p>

			<p>
				<label for="search_location"><?php _e('Search location', APP_TD); ?></label>
				<input type="text" class="tags text" name="search_location" id="search_location" placeholder="<?php esc_attr_e('e.g. London, United Kingdom', APP_TD); ?>" value="<?php echo esc_attr( $search_location ); ?>" />
			</p>

			<div class="optional prefs_job_types">
				<label><?php _e('Types of Job', APP_TD); ?></label>
				<?php echo jr_job_types_checklist( $job_types ); ?>
			</div>

		</fieldset>

		<p><input type="submit" class="submit" name="save_prefs" value="<?php esc_attr_e('Save &rarr;', APP_TD); ?>" /></p>

		<div class="clear"></div>

	</form>
<?php
}
