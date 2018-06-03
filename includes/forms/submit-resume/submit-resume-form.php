<?php
/**
 * JobRoller Resume form
 * Function outputs the resume submit form
 *
 * @version 1.4
 * @author AppThemes
 * @package JobRoller
 * @copyright 2010 all rights reserved
 */
?>
<form id="submit_form" action="<?php echo esc_url( get_query_var('edit') ? jr_get_the_resume_edit_link( get_query_var('edit') ): get_permalink( JR_Resume_Edit_Page::get_id() ) ); ?>" method="post" enctype="multipart/form-data" class="submit_form main_form">
	<?php wp_nonce_field( 'submit_resume', 'nonce' ); ?>

	<p><?php _e('Enter your resume details below. Once saved you will be able to view your resume and optionally add links to your websites/social networks if you wish.', APP_TD); ?></p>

	<fieldset>
		<legend><?php _e('Your Resume', APP_TD); ?></legend>

		<p>
			<label for="resume_name"><?php _e( 'Resume Title', APP_TD ); ?>	<span title="required">*</span></label>
			<input type="text" class="text required" name="resume_name" id="resume_name" class="text" placeholder="<?php esc_attr_e( 'e.g. Lead Developer', APP_TD ); ?>" value="<?php esc_attr_e( $resume->resume_name ); ?>" />
		</p>

		<p>
			<label for="summary"><?php _e( 'Resume Summary', APP_TD ); ?> <span title="required">*</span></label>
			<textarea rows="5" cols="30" name="summary" id="summary" placeholder="<?php _e( 'Briefly describe yourself.', APP_TD ); ?>" class="short required" style="height:100px;"><?php echo esc_textarea( $resume->summary ); ?></textarea>
		</p>

		<p class="optional">
			<label for="resume_cat"><?php _e( 'Resume Category', APP_TD ); ?></label>
			<?php
			   $args = array(
					'orderby'         => 'name',
					'order'           => 'ASC',
					'name'            => 'resume_cat',
					'hierarchical'    => true,
					'class'           => 'resume_cat',
					'selected'        => $resume->resume_category,
					'taxonomy'        => APP_TAX_RESUME_CATEGORY,
					'show_option_all' => __( 'Select a category&hellip;', APP_TD ),
					'hide_empty'      => false
			   );
			   wp_dropdown_categories( $args );
		   ?>
		</p>

		<?php do_action( 'jr_after_submit_resume_form_category', $resume ); ?>

		<?php the_resume_photo_editor( $resume->ID ); ?>

		<p class="optional">
			<label for="desired_salary"><?php _e( 'Desired Salary (only numeric values)', APP_TD ); ?></label>
			<input type="text" class="tags text" name="desired_salary" id="desired_salary" placeholder="<?php _e( 'e.g. 25000', APP_TD ); ?>" value="<?php esc_attr_e( $resume->desired_salary ); ?>" />
		</p>

		<p class="optional">
			<label for="desired_position"><?php _e( 'Desired Type of Position', APP_TD ); ?></label>
			<?php
			   $args = array(
					'orderby'         => 'name',
					'order'           => 'ASC',
					'name'            => 'desired_position',
					'hierarchical'    => true,
					'class'           => 'resume_cat',
					'selected'        => $resume->resume_job_type,
					'taxonomy'        => APP_TAX_RESUME_JOB_TYPE,
					'show_option_all' => __( 'Any', APP_TD ),
					'hide_empty'      => false
			   );
			   wp_dropdown_categories( $args );
			?>
		</p>
	</fieldset>

	<fieldset>
		<legend><?php _e('Your Contact Details', APP_TD); ?></legend>

		<p><?php _e('Optionally fill in your contact details below to have them appear on your resume. This is important if you want employers to be able to contact you!', APP_TD); ?></p>

		<p class="optional">
			<label for="email_address"><?php _e('Email Address', APP_TD); ?></label>
			<input type="text" class="text" name="email_address" value="<?php esc_attr_e( $resume->email_address ); ?>" id="email_address" placeholder="<?php echo esc_attr__( 'you@yourdomain.com', APP_TD ); ?>" />
		</p>
		<p class="optional"><label for="tel"><?php _e('Telephone', APP_TD); ?></label>
			<input type="text" class="text" name="tel" value="<?php esc_attr_e( $resume->tel ); ?>" id="tel" placeholder="<?php _e( 'Telephone including area code', APP_TD ); ?>" />
		</p>
		<p class="optional"><label for="mobile"><?php _e('Mobile', APP_TD); ?></label>
			<input type="text" class="text" name="mobile" value="<?php echo esc_attr_e( $resume->mobile ); ?>" id="mobile" placeholder="<?php _e( 'Mobile number', APP_TD ); ?>" />
		</p>

	</fieldset>

	<fieldset>
		<legend><?php _e( 'Resume Location', APP_TD ); ?> <span title="required">*</span></legend>

		<p><?php _e( 'Entering your location will help employers find you.', APP_TD ); ?></p>

		<div id="geolocation_box">
			<p>
				<label>
					<input id="geolocation-load" type="button" class="button geolocationadd submit" value="<?php esc_attr_e( 'Find Address/Location', APP_TD ); ?>" />
				</label>

				<input type="text" class="text required" name="jr_address" id="geolocation-address" value="<?php esc_attr_e( $resume->jr_address ); ?>" autocomplete="off" />
				<input type="hidden" class="text" name="jr_geo_latitude" id="geolocation-latitude" value="<?php esc_attr_e( $resume->jr_geo_latitude ); ?>" />
				<input type="hidden" class="text" name="jr_geo_longitude" id="geolocation-longitude" value="<?php esc_attr_e( $resume->jr_geo_longitude ); ?>" />
				<input type="hidden" class="text" name="jr_geo_country" id="geolocation-country" value="<?php esc_attr_e( $resume->jr_geo_country ); ?>" />
				<input type="hidden" class="text" name="jr_geo_short_address" id="geolocation-short-address" value="<?php esc_attr_e( $resume->jr_geo_short_address ); ?>" />
				<input type="hidden" class="text" name="jr_geo_short_address_country" id="geolocation-short-address-country" value="<?php esc_attr_e( $resume->jr_geo_short_address_country ); ?>" />
			</p>

			<div id="map_wrap" style="border:solid 2px #ddd;"><div id="geolocation-map" style="width:100%;height:300px;"></div></div>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php _e( 'Education', APP_TD ); ?></legend>

		<p><?php _e( 'Detail your education, including details on your qualifications and schools/universities attended.', APP_TD ); ?></p>

		<p class="education">

			<?php if ( $jr_options->jr_html_allowed  && ! wp_is_mobile() ): ?>

				<?php wp_editor( $resume->education, 'education', jr_get_editor_settings() ); ?>

			<?php else: ?>

				<textarea id="education" name="education" cols="30" rows="5" ><?php echo esc_textarea( $resume->education ); ?></textarea>

			<?php endif; ?>

		</p>
	</fieldset>

	<fieldset>
		<legend><?php _e( 'Experience', APP_TD ); ?></legend>

		<p><?php _e( 'Detail your work experience, including details on your employers and job roles and responsibilities.', APP_TD ); ?></p>

		<p class="experience">
			<?php if ( $jr_options->jr_html_allowed && ! wp_is_mobile() ): ?>

				<?php wp_editor( $resume->experience, 'experience', jr_get_editor_settings() ); ?>

			<?php else: ?>

				<textarea id="experience" name="experience" cols="30" rows="5" ><?php echo esc_textarea( $resume->experience ); ?></textarea>

			<?php endif; ?>
		</p>
	</fieldset>

	<fieldset>
		<legend><?php _e( 'Skills &amp; Specialties', APP_TD ); ?></legend>
			<p class="optional">
				<label for="skills"><?php _e( 'Skills <small>(one per line)</small>', APP_TD ); ?></label>
				<textarea rows="1" cols="30" name="skills" id="skills" class="short grow" placeholder="<?php _e( 'e.g. XHTML (5 years experience)', APP_TD ); ?>"><?php echo esc_textarea( $resume->skills ); ?></textarea>
			</p>

			<p class="optional"><label for="specialities"><?php _e( 'Specialties <small>e.g. Public speaking, Team management</small>', APP_TD ); ?></label>
				<input type="text" class="tags text tag-input-commas" data-separator="," name="specialities" id="specialities" placeholder="<?php _e( 'e.g. Public Speaking, Team Management', APP_TD ); ?>" value="<?php esc_attr_e( $resume->specialities ); ?>" />
			</p>

			<p class="optional"><label for="groups"><?php _e( 'Groups/Associations <small>e.g. IEEE, W3C</small>', APP_TD ); ?></label>
				<input type="text" class="text text tag-input-commas" data-separator="," name="groups" value="<?php esc_attr_e( $resume->groups ); ?>" id="groups" placeholder="<?php _e( 'e.g. IEEE, W3C', APP_TD ); ?>" />
			</p>

			<p class="optional" id="languages_wrap"><label for="languages"><?php _e( 'Spoken Languages <small>e.g. English, French</small>', APP_TD ); ?></label>
				<input type="text" class="text text tag-input-commas" data-separator="," name="languages" value="<?php esc_attr_e( $resume->languages ); ?>" id="languages" placeholder="<?php _e( 'e.g. English, French', APP_TD ); ?>" />
			</p>
	</fieldset>

	<?php do_action( 'jr_after_submit_resume_form', $resume ); ?>

	<p><input type="submit" class="submit" name="save_resume" value="<?php _e( 'Save &rarr;', APP_TD ); ?>" /></p>

	<div class="clear"></div>

</form>
