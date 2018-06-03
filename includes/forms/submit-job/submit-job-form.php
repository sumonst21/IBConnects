<?php
/**
 * JobRoller Submit/Edit/Relist Job form.
 * Outputs the job submit form.
 *
 * @version 1.7
 * @author AppThemes
 * @package JobRoller\Forms\Submit
 * @copyright 2010 all rights reserved
 */
?>
<form action="<?php echo esc_url( $form_action ) ?>" method="post" enctype="multipart/form-data" id="submit_form" class="submit_form main_form">
	<?php wp_nonce_field( 'submit_job', 'nonce' ); ?>

	<fieldset>
		<legend><?php _e( 'Company Details', APP_TD ); ?></legend>

		<p><?php _e( 'Fill in the company section to provide details of the company listing the job. Leave this section blank to show your display name and profile page link instead.', APP_TD ); ?></p>

		<p class="optional">
			<label for="your_name"><?php _e( 'Your Name/Company Name', APP_TD ); ?></label>
			<input type="text" class="text" name="your_name" id="your_name" value="<?php esc_attr_e( $job->your_name ); ?>" />
		</p>
		<p class="optional">
			<label for="website"><?php _e( 'Website', APP_TD ); ?></label>
			<input type="text" class="text" name="website" value="<?php echo esc_url( $job->website ); ?>" placeholder="http://" id="website" />
		</p>

		<?php the_job_listing_logo_editor( $job->ID ); ?>
	</fieldset>

	<fieldset>
		<legend><?php _e( 'Job Details', APP_TD ); ?></legend>

		<p><?php _e( 'Enter details about the job below. Be as descriptive as possible so that potential candidates can find your job listing easily.', APP_TD ); ?></p>

		<p>
			<label for="post_title"><?php _e( 'Job title', APP_TD ); ?> <span title="required">*</span></label>
			<input type="text" class="text required" name="post_title" id="post_title" value="<?php esc_attr_e( $job->post_title ); ?>" />
		</p>
		<p>
			<label for="job_type"><?php _e( 'Job type', APP_TD ); ?> <span title="required">*</span></label>

			<?php
				$args = array(
					'taxonomy'          => APP_TAX_TYPE,
					'name'              => 'job_term_type',
					'class'             => 'required',
					'selected'          => $job->type,
					'show_option_none'  => __( 'Select a a job type...', APP_TD ),
					'option_none_value' => '',
					'hide_empty'        => false,
				);
				wp_dropdown_categories( $args );
			?>
		</p>

		<p class="<?php echo ( ! $cat_required ? 'optional' : '' ); ?>">
			<label for="job_cat"><?php _e( 'Job Category', APP_TD ); ?><?php if ( $cat_required ) : ?><span title="required">*</span><?php endif; ?></label>
			<?php
			$args = array(
				'taxonomy'          => APP_TAX_CAT,
				'orderby'           => 'name',
				'order'             => 'ASC',
				'name'              => 'job_term_cat',
				'class'             => 'job_cat ' . $cat_required,
				'selected'          => $job->category,
				'hide_empty'        => false,
				'hierarchical'      => true,
				'show_option_none'  => __( 'Select a category&hellip;', APP_TD ),
				'option_none_value' => '',
				'echo'              => false,
			);
			$drop_cats = wp_dropdown_categories( $args );

			if ( $cat_required && get_query_var( 'job_edit' ) && ! empty( $job->category ) && ! $jr_options->jr_submit_cat_editable ) {
				$drop_cats = str_replace( '<select', '<select disabled', $drop_cats );
				$display_no_edit_cat_msg = __( 'The category cannot be edited', APP_TD );
				echo "<input type='hidden' name='job_term_cat' value='" . esc_attr( $job->category ) . "'>";
			}

			echo $drop_cats; ?>

			<?php if ( ! empty( $display_no_edit_cat_msg ) ): ?>
				<p><strong><?php echo sprintf( __( 'Note: %s', APP_TD ), $display_no_edit_cat_msg ); ?></strong></p>
			<?php endif; ?>

		</p>

		<?php do_action( 'jr_after_submit_job_form_category', $job ); ?>

		<?php if ( $jr_options->jr_enable_salary_field ) : ?>

			<p class="optional"><label for="job_term_salary"><?php _e( 'Job Salary', APP_TD ); ?></label>
			<?php
				$args = array(
					'orderby'      => 'ID',
					'order'        => 'ASC',
					'name'         => 'job_term_salary',
					'hierarchical' => false,
					'echo'         => false,
					'class'        => 'job_salary',
					'selected'     => $job->salary,
					'taxonomy'     => 'job_salary',
					'hide_empty'   => false,
				);
				$dropdown = wp_dropdown_categories( $args );

				$dropdown = str_replace( 'class=\'job_salary\' >', 'class=\'job_salary\' ><option value="">' . __( 'Select a salary&hellip;', APP_TD ) . '</option>', $dropdown );

				echo $dropdown;	?>
			</p>

		<?php endif; ?>

		<p class="optional">
			<label for="tax_input[<?php echo APP_TAX_TAG; ?>]"><?php _e( 'Tags (comma separated)', APP_TD ); ?></label>
			<input type="text" class="text" name="tax_input[<?php echo APP_TAX_TAG; ?>]" value="<?php esc_attr( the_job_listing_tags_to_edit( $job->ID ) ); ?>" id="tax_input[<?php echo APP_TAX_TAG; ?>]" />
		</p>
	</fieldset>

	<fieldset>
		<legend><?php _e( 'Job Location', APP_TD ); ?></legend>
		<p><?php _e( 'Leave blank if the location of the applicant does not matter e.g. the job involves working from home.', APP_TD ); ?></p>
		<div id="geolocation_box">
			<p>
				<input type="text" class="text" name="jr_address" id="geolocation-address" value="<?php echo esc_attr( $job->jr_address ); ?>" />
				<input type="hidden" class="text" name="jr_geo_latitude" id="geolocation-latitude" value="<?php echo esc_attr( $job->jr_geo_latitude ); ?>" />
				<input type="hidden" class="text" name="jr_geo_longitude" id="geolocation-longitude" value="<?php echo esc_attr( $job->jr_geo_longitude ); ?>" />
				<input type="hidden" class="text" name="jr_geo_country" id="geolocation-country" value="<?php echo esc_attr( $job->jr_geo_country ); ?>" />
				<input type="hidden" class="text" name="jr_geo_short_address" id="geolocation-short-address" value="<?php echo esc_attr( $job->jr_geo_short_address ); ?>" />
				<input type="hidden" class="text" name="jr_geo_short_address_country" id="geolocation-short-address-country" value="<?php echo esc_attr( $job->jr_geo_short_address_country ); ?>" />

				<label>
					<input id="geolocation-load" type="button" class="button geolocationadd submit" value="<?php _e( 'Find Address/Location', APP_TD ); ?>" />
				</label>
			</p>

			<div id="map_wrap" style="border:solid 2px #ddd;"><div id="geolocation-map" style="width:100%;height:350px;"></div></div>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php _e( 'Job Description', APP_TD ); ?> <span title="required">*</span></legend>
		<p><?php _e( 'Give details about the position, such as responsibilities &amp; salary.', APP_TD ); ?><?php if ( ! $jr_options->jr_html_allowed ): _e( ' HTML is not allowed.', APP_TD ); endif; ?></p>
		<p>
			<div class="wp_editor_wrapper <?php echo ( isset( $_POST['post_content'] ) && empty( $_POST['post_content'] ) ? 'wp_editor_empty' : '' ); ?>">

				<?php if ( $jr_options->jr_html_allowed && ! wp_is_mobile() ): ?>
					<?php wp_editor( $job->post_content, 'post_content', jr_get_editor_settings( array( 'editor_class' => 'required' ) ) ); ?>
				<?php else: ?>
					<textarea class="required" id="post_content" name="post_content" cols="30" rows="5"><?php echo esc_textarea( $job->post_content ); ?></textarea>
				<?php endif; ?>

			</div>
		</p>
	</fieldset>

	<?php if ( $jr_options->jr_submit_how_to_apply_display ) : ?>
		<fieldset>
			<legend><?php _e( 'How to apply', APP_TD ); ?></legend>
			<p><?php _e( 'Tell applicants how to apply &ndash; they will also be able to email you via the &ldquo;apply&rdquo; form on your job listing\'s page.', APP_TD ); ?><?php if ( ! $jr_options->jr_html_allowed ): _e( ' HTML is not allowed.', APP_TD ); endif; ?></p>
			<p>
				<div class="wp_editor_wrapper">

					<?php if ( $jr_options->jr_html_allowed && ! wp_is_mobile() ): ?>
						<?php wp_editor( $job->apply, 'apply', jr_get_editor_settings( array( 'editor_class' => 'how' ) ) ); ?>
					<?php else: ?>
						<textarea class="how" id="apply" name="apply" cols="30" rows="5" ><?php echo esc_textarea( $job->apply ); ?></textarea>
					<?php endif; ?>

				</div>
			</p>
		</fieldset>
	<?php endif; ?>

	<?php do_action( 'jr_after_submit_job_form', $job ); ?>

	<input type="hidden" name="action" value="<?php echo esc_attr( $post_action ); ?>" />
	<input type="hidden" name="step" value="<?php echo esc_attr( $step ); ?>"/>
	<input type="hidden" name="ID" value="<?php echo esc_attr( $job->ID ); ?>">
	<input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">
	<input type="hidden" name="preview_job" />

	 <?php if ( get_query_var( 'job_relist' ) ): ?>
		<input type="hidden" name="relist" value="1"/>
	<?php endif; ?>

	<p><input type="submit" class="submit" name="job_submit" value="<?php echo esc_attr( $submit_text ); ?>" /></p>

	<div class="clear"></div>

</form>
