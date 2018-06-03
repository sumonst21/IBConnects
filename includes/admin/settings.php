<?php
/**
 * Admin options for the 'Settings' page.
 *
 * @version 1.8
 * @author AppThemes
 * @package JobRoller\Admin\Settings
 */


### Classes

class JR_Settings_Admin extends APP_Tabs_Page {

	protected $permalink_sections;
	protected $permalink_options;

	function setup() {
		$this->textdomain = APP_TD;

		$this->args = array(
			'page_title' => __( 'JobRoller Settings', APP_TD ),
			'menu_title' => __( 'Settings', APP_TD ),
			'page_slug' => 'app-settings',
			'parent' => 'app-dashboard',
			'screen_icon' => 'options-general',
			'admin_action_priority' => 10,
		);

		add_action( 'admin_menu', array( $this, 'remove_menus' ) );
	}

	### Hook Callbacks

	/**
	 * Remove the admin jobs menu if editing is disabled.
	 */
	function remove_menus() {
		global $jr_options;

		if ( ! $jr_options->jr_allow_editing && ! current_user_can( 'edit_jobs' ) ) {
			remove_menu_page( 'edit.php?post_type=' . APP_POST_TYPE );
		}
	}

	protected function init_tabs() {
		$_SERVER['REQUEST_URI'] = esc_url_raw( remove_query_arg( array( 'firstrun' ), $_SERVER['REQUEST_URI'] ) );

		$this->tabs->add( 'general', __( 'General', APP_TD ) );
		$this->tabs->add( 'jobs', __( 'Jobs', APP_TD ) );
		$this->tabs->add( 'resumes', __( 'Resumes', APP_TD ) );
		$this->tabs->add( 'security', __( 'Security', APP_TD ) );
		$this->tabs->add( 'advertising', __( 'Advertising', APP_TD ) );
		$this->tabs->add( 'advanced', __( 'Advanced', APP_TD ) );

		$this->tab_general();
		$this->tab_jobs();
		$this->tab_resumes();
		$this->tab_security();
		$this->tab_advertising();
		$this->tab_advanced();
	}

	protected function tab_general() {

		$this->tab_sections['general']['appearance'] = array(
			'title' => __( 'Appearance', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Design', APP_TD ),
					'desc' => sprintf( __( 'Customize the look and feel of your website by visiting the <a href="%s">WordPress customizer</a>.' , APP_TD), 'customize.php' ),
					'type' => 'text',
					'name' => '_blank',
					'extra' => array(
						'style' => 'display: none;'
					),
					'tip' => '',
				),
			),
		);

		$this->tab_sections['general']['social'] = array(
			'title' => __( 'Social', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Feedburner URL', APP_TD ),
					'desc' => sprintf( __( '%1$s Sign up for a free <a target="_new" href="%2$s">Feedburner account</a>.', APP_TD ), '<i class="social-ico dashicons-before feedburnerico"></i>', 'https://feedburner.google.com' ),
					'tip' => __( 'Automatically redirect your default RSS feed to Feedburner.', APP_TD ),
					'name' => 'jr_feedburner_url',
					'type' => 'text',
					'extra' => array(
						'class' => 'regular-text code'
					),
				),
				array(
					'title' => __( 'Twitter ID', APP_TD ),
					'desc' => sprintf( __( '%1$s Sign up for a free <a target="_new" href="%2$s">Twitter account</a>.', APP_TD ), '<i class="social-ico dashicons-before twitterico"></i>', 'https://twitter.com' ),
					'name' => 'jr_twitter_id',
					'type' => 'text',
					'tip' => __( 'Used in the Twitter sidebar widget.', APP_TD ),
					'extra' => array(
						'class' => 'regular-text code'
					),
				),
				array(
					'title' => __( 'Facebook ID', APP_TD ),
					'name' => 'jr_facebook_id',
					'type' => 'text',
					'desc' => sprintf( __( '%1$s Sign up for a free <a target="_new" href="%2$s">Facebook account</a>.', APP_TD ), '<i class="social-ico dashicons-before facebookico"></i>', 'https://www.facebook.com' ),
					'tip' => __( 'Used in the Facebook Like Box sidebar widget.', APP_TD ),
					'extra' => array(
						'class' => 'regular-text code'
					),
				),
				array(
					'title' => __( 'ShareThis ID', APP_TD ),
					'name' => 'jr_sharethis_id',
					'type' => 'text',
					'desc' => sprintf( __( '%1$s Sign up for a free <a target="_new" href="%2$s">ShareThis account</a>.', APP_TD ), '<i class="social-ico dashicons-before sharethisico"></i>', 'http://sharethis.com' ),
					'tip' => __( 'Show the ShareThis buttons on the blog post and job listings.', APP_TD ),
					'extra' => array(
						'class' => 'regular-text code'
					),
				),
				array(
					'title' => __( 'Analytics Code', APP_TD ),
					'name' => 'jr_google_analytics',
					'type' => 'textarea',
					'desc' =>  sprintf( __( 'Sign up for a free <a target="_new" href="%s">Google Analytics account</a>.', APP_TD ), 'https://www.google.com/analytics/' ),
					'tip' => __( 'You can use Google Analytics or other providers as well.', APP_TD ),
					'extra' => array(
						'rows' => 10,
						'cols' => 50,
						'class' => 'large-text code'
					),
					'sanitize' => 'appthemes_clean',
				),
			),
		);

		$this->tab_sections['general']['google'] = array(
			'title' => __( 'Google Maps', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Language', APP_TD ),
					'name' => 'jr_gmaps_lang',
					'type' => 'text',
					'desc'	 => sprintf( __( 'Find your two-letter <a href="%s" target="_blank">language code</a>', APP_TD ), 'http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes' ),
					'tip'	 => __( 'Used to format the address and map controls.', APP_TD ),
					'extra' => array(
						'class' => 'small-text',
					),
				),
				array(
					'title' => __( 'Region Biasing', APP_TD ),
					'name' => 'jr_gmaps_region',
					'type' => 'text',
					'desc'	 => sprintf( __( 'Find your two-letter <a href="%s" target="_blank">region code</a>', APP_TD ), 'http://en.wikipedia.org/wiki/ISO_3166-1' ),
					'tip'	 => __( "If you set this to 'IT' and a user enters 'Florence' in the location search field, it will target 'Florence, Italy' rather than 'Florence, Alabama'.", APP_TD ),
					'extra' => array(
						'class' => 'small-text',
					),
				),
				array(
					'title' => __( 'Distance Unit', APP_TD ),
					'name' => 'jr_distance_unit',
					'type' => 'select',
					'tip' => '',
					'values' => array(
						'km' => __( 'Kilometers', APP_TD ),
						'mi' => __( 'Miles', APP_TD ),
					),
				),
				array(
					'title' => __( 'API Key', APP_TD ),
					'name' => 'gmaps_api_key',
					'type' => 'text',
					'desc' => sprintf( __( 'Get started using the <a href="%s" target="_blank">Geocoding API</a>', APP_TD ), 'https://developers.google.com/maps/documentation/geocoding/index#api_key' ),
					'tip' => __( 'Create a project in the Google Developers Console and paste in the API key here. This field is optional but recommended.', APP_TD ) .
							 '<br/><br/>'. __( "Make sure you enable the <code>'Google Maps Javascript API'</code> and the <code>'Geocoding API'</code> on your Google Developers Console.", APP_TD ),
				),
			),
		);

	}

	protected function tab_jobs() {

		$this->tab_sections['jobs']['general'] = array(
			'title' => __( 'General', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Editing', APP_TD ),
					'name' => 'jr_allow_editing',
					'type' => 'checkbox',
					'desc'	 => __( 'Allow users to edit and republish their jobs', APP_TD ),
					'tip'	 => __( 'They can manage and edit jobs from their dashboard.', APP_TD ),
				),
				array(
					'title' => __( 'Relisting', APP_TD ),
					'name' => 'jr_allow_relist',
					'type' => 'checkbox',
					'desc'	 => __( 'Allow users to relist and pay for their expired job posts', APP_TD ),
					'tip' => sprintf( __( 'Set a relisting fee on each price plan. <a href="%s">Manage your pricing plans</a> on the Payments Menu.', APP_TD ), 'edit.php?post_type=' . APPTHEMES_PRICE_PLAN_PTYPE ),
				),
				array(
					'title' => __( 'Listing Period', APP_TD ),
					'name' => 'jr_jobs_default_expires',
					'type' => 'number',
					'desc'	 => __( 'Days each job will be listed ', APP_TD ),
					'tip' => __( 'This option is overridden by the job duration set on each price plan.', APP_TD ),
					'extra' => array(
						'class' => 'small-text',
					),
				),
				array(
					'title' => __( 'Home Page', APP_TD ),
					'name' => 'jobs_frontpage',
					'type' => 'number',
					'desc' => __( 'How many jobs per page to show on the home page', APP_TD ),
					'tip' => __( '10 or 20 is recommended. Set this to -1 to display all job listings (no pagination).', APP_TD ),
					'extra' => array(
						'class' => 'small-text',
					),
				),
				array(
					'title' => __( 'Jobs Per Page', APP_TD ),
					'name' => 'jr_jobs_per_page',
					'type' => 'number',
					'desc' => __( 'How many jobs per page to show', APP_TD ),
					'tip' => __( 'These jobs are displayed below featured job listings (if any).', APP_TD ),
					'extra' => array(
						'class' => 'small-text',
					),
				),
				array(
					'title' => __( 'Switch Categories', APP_TD ),
					'name' => 'jr_submit_cat_editable',
					'type' => 'checkbox',
					'desc' => __( 'Allow users to change job categories after submission', APP_TD ),
					'tip' => __( "If you have multiple job categories assigned to different price plans, it is recommended to disable this option. It ensures that users can't change job categories after purchasing a plan from a different category.", APP_TD ),
				),
			),
		);

		$this->tab_sections['jobs']['pricing'] = array(
			'title' => __( 'Pricing', APP_TD ),
			'desc' => sprintf( __( 'Once enabled, make sure to setup your <a href="%s">payment settings</a>.', APP_TD ), 'admin.php?page=app-payments-settings' ),
			'fields' => array(
				array(
					'title' => __( 'Charge for Listings', APP_TD ),
					'name' => 'jr_jobs_charge',
					'type' => 'checkbox',
					'desc' => __( 'Start accepting payments', APP_TD ),
					'tip' => __( 'This activates the payments system. Left unchecked, listings will be free to post.', APP_TD ),
				),
				array(
					'title' => __( 'Job Plans', APP_TD ),
					'name' => '_blank',
					'type' => '',
					'desc' => sprintf( __( 'Setup your <a href="%s">job plans</a>', APP_TD ), 'edit.php?post_type='.APPTHEMES_PRICE_PLAN_PTYPE ),
					'extra' => array(
						'style' => 'display: none;'
					),
					'tip' => __( 'Custom pricing and feature packages that are available on your website.', APP_TD ),
				),
			),
		);

		$this->tab_sections['jobs']['new_listings'] = array(
			'title' => __( 'New Listings', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'How to Apply', APP_TD ),
					'name' => 'jr_submit_how_to_apply_display',
					'type' => 'checkbox',
					'desc' => __( 'Show this field on the job submission form', APP_TD ),
					'tip' => '',
				),
				array(
					'title' => __( 'Job Salary', APP_TD ),
					'name' => 'jr_enable_salary_field',
					'type' => 'checkbox',
					'desc' => __( 'Show this field on the job submission form', APP_TD ),
					'tip' => '',
				),
				array(
					'title' => __( 'Job Categories', APP_TD ),
					'name' => 'jr_submit_cat_required',
					'type' => 'checkbox',
					'desc' => __( 'Require at least one job category for new job listings', APP_TD ),
					'tip' => __( 'Make sure you have at least one job category created before enabling this option. If you charge for jobs and leave the category as optional, users that do not select a category will be presented with job plans from any job category.', APP_TD ),
				),

			),
		);

		$this->tab_sections['jobs']['listings_page'] = array(
			'title' => __( 'Listing Page', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Views Counter', APP_TD ),
					'name' => 'jr_ad_stats_all',
					'type' => 'checkbox',
					'desc' => __( 'Show a daily and total views counter', APP_TD ),
					'tip' => __( "This will appear at the bottom of each job listing page and blog post.", APP_TD ),
				),
				array(
					'title' => __( 'Allow HTML', APP_TD ),
					'name' => 'jr_html_allowed',
					'type' => 'checkbox',
					'desc' => __( 'Permit users to use HTML within their job listings', APP_TD ),
					'tip' => __( 'Turns on the TinyMCE editor on text area fields and allows the job owner to use html markup. Other fields do not allow html by default.', APP_TD ),
				),
				array(
					'title' => __( 'Applicants', APP_TD ),
					'name' => 'apply_reg_users_only',
					'type' => 'checkbox',
					'desc' => __( 'Only registered users can apply for jobs', APP_TD ),
					'tip' => '',
				),
				array(
					'title' => __( 'Allow Comments', APP_TD ),
					'name' => 'allow_job_comments',
					'type' => 'checkbox',
					'desc' => __( 'Permit visitors to post comments on job listings', APP_TD ),
					'tip' => __( 'This option is only applicable for new job postings. Existing jobs comments must be enabled/disabled on a job by job basis.', APP_TD ),
				),
				array(
					'title' => __( 'Expired Jobs', APP_TD ),
					'name' => 'jr_expired_action',
					'type' => 'select',
					'desc' => __( 'How to handle expired job listings', APP_TD ),
					'tip' => __( "Selecting 'display message' will keep the job visible and display a 'job expired' message. Selecting 'hide' will remove all expired jobs from view but not delete them.", APP_TD ),
					'values' => array(
						'display_message' => __( 'Display Message', APP_TD ),
						'hide' => __( 'Hide', APP_TD )
					),
				),
			),
		);

		$this->tab_sections['jobs']['featured'] = array(
			'title' => __( 'Featured', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Home Page', APP_TD ),
					'name' => 'featured_jobs_frontpage',
					'type' => 'number',
					'desc' => __( 'How many featured jobs per page to show on the home page', APP_TD ),
					'tip' => __( 'Leave blank to display unlimited featured jobs.', APP_TD ),
					'extra' => array(
						'class' => 'small-text',
					),
				),
				array(
					'title' => __( 'Featured Per Page', APP_TD ),
					'name' => 'jr_featured_jobs_per_page',
					'type' => 'number',
					'desc' => __( 'How many featured jobs per page to show', APP_TD ),
					'tip' => __( 'Leave blank to display unlimited featured jobs.', APP_TD ),
					'extra' => array(
						'class' => 'small-text',
					),
				),
				array(
					'title' => __( 'Sorting', APP_TD ),
					'name' => 'jr_featured_jobs_sort',
					'type' => 'select',
					'desc' => __( 'Default view of featured jobs', APP_TD ),
					'tip' => '',
					'values' => array(
						'newest' => __( 'Newest First', APP_TD ),
						'oldest' => __( 'Oldest First', APP_TD ),
						'random' => __( 'Random', APP_TD )
					),
				),
			),
		);

		$this->tab_sections['jobs']['moderate'] = array(
			'title' => __( 'Moderate', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Listings', APP_TD ),
					'name' => 'jr_jobs_require_moderation',
					'type' => 'checkbox',
					'desc'	 => __( 'Manually approve and publish each new listing', APP_TD ),
					'tip'	 => __( 'Left unchecked, listings go live immediately without being moderated (unless it has not been paid for).', APP_TD ),
				),
				array(
					'title' => __( 'Edited Jobs', APP_TD ),
					'name' => 'jr_editing_needs_approval',
					'type' => 'checkbox',
					'desc'	 => __( 'Manually approve and publish user edited listing', APP_TD ),
					'tip'	 => __( 'Left unchecked, edited listings stay live without being moderated.', APP_TD ),
				),
			),
		);

	}

	protected function tab_resumes() {

		$this->tab_sections['resumes']['general'] = array(
			'title' => __( 'General', APP_TD ),
			'desc' => '',
			'fields' => array(
				array(
					'title' => __( 'Resume Listings Visibility', APP_TD ),
					'name' => 'jr_resume_listing_visibility',
					'type' => 'select',
					'tip' => __( 'Lets you define who can browse through submitted resumes.', APP_TD ),
					'values' => array(
						'public' => __( 'Anyone', APP_TD ),
						'listers' => __( 'Job Listers', APP_TD ),
						'recruiters' => __( 'Recruiters', APP_TD ),
						'members_listers' => __( 'Recruiters &amp; Job Listers', APP_TD ),
						'members' => __( 'Registered Users', APP_TD ),
					),
				),
				array(
					'title' => __( 'Resume Visibility', APP_TD ),
					'name' => 'jr_resume_visibility',
					'type' => 'select',
					'tip' => __( 'Lets you define who can view submitted resumes.', APP_TD ),
					'values' => array(
						'public' => __( 'Anyone', APP_TD ),
						'listers' => __( 'Job Listers', APP_TD ),
						'recruiters' => __( 'Recruiters', APP_TD ),
						'members_listers' => __( 'Recruiters &amp; Job Listers', APP_TD ),
						'members' => __( 'All Registered Users', APP_TD ),
					),
				),
				array(
					'title' => __( 'Resumes Per Page', APP_TD ),
					'name' => 'jr_resumes_per_page',
					'type' => 'number',
					'desc' => __( 'How many resumes per page to show', APP_TD ),
					'extra' => array(
						'class' => 'small-text',
					),
				),
				array(
					'title' => __( 'Resume Privacy', APP_TD ),
					'name' => 'jr_resume_show_contact_form',
					'type' => 'checkbox',
					'desc' => __( "Hide the resume author's contact information", APP_TD ),
					'tip' => __( 'A pop-up form will be used instead to let employers send resume authors a message.', APP_TD ),
				),
			),
		);

		$this->tab_sections['resumes']['pricing'] = array(
			'title' => __( 'Pricing', APP_TD ),
			'desc' => sprintf( __( 'Once enabled, make sure to setup your <a href="%s">payment settings</a>.', APP_TD ), 'admin.php?page=app-payments-settings' ),
			'fields' => array(
				array(
					'title' => __( 'Resume Plans', APP_TD ),
					'name' => '_blank',
					'type' => '',
					'desc' => sprintf( __( 'Setup your <a href="%s">resume plans</a>', APP_TD ), 'edit.php?post_type='.APPTHEMES_RESUMES_PLAN_PTYPE ),
					'tip' => __( 'Custom pricing and feature packages that are available on your website.', APP_TD ),
					'extra' => array(
						'style' => 'display: none;'
					),
				),
			),
		);

		$this->tab_sections['resumes']['recruiter'] = array(
			'title' => __( 'Recruiters', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Enable', APP_TD ),
					'name' => 'jr_allow_recruiters',
					'type' => 'checkbox',
          'desc' => __( 'Allow recruiters to register', APP_TD ),
          'tip' => __( "Recruiters can submit and view resumes (if you don't have resume subscriptions enabled). In comparision, job listers can only submit jobs. If your site requires a subscription to view/browse resumes, access will always depend on your visibility settings.", APP_TD ),
				),
			),
		);

		$this->tab_sections['resumes']['job_seeker'] = array(
			'title' => __( 'Job Seekers', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Enable', APP_TD ),
					'name' => 'jr_allow_job_seekers',
					'type' => 'checkbox',
					'desc' => __( 'Allow job seekers to register', APP_TD ),
					'tip' => __( 'Job seekers cannot submit jobs. They can only browse jobs and apply.', APP_TD ),
				),
				array(
					'title' => __( 'My Profile', APP_TD ),
					'name' => 'jr_my_profile_button_text',
					'type' => 'textarea',
					'sanitize' => 'appthemes_clean',
					'desc' => __( 'HTML is allowed', APP_TD ),
					'tip' => __( "Appears below the 'My Profile' button.", APP_TD ),
					'extra' => array(
						'rows' => 5,
						'cols' => 50,
						'class' => 'large-text code'
					),
				),
				array(
					'title' => __( 'Submit Resume', APP_TD ),
					'name' => 'jr_submit_resume_button_text',
					'type' => 'textarea',
					'sanitize' => 'appthemes_clean',
					'desc' => __( 'HTML is allowed', APP_TD ),
					'tip' => __( "Appears below the 'Submit Your Resume' button when browsing resumes.", APP_TD ),
					'extra' => array(
						'rows' => 5,
						'cols' => 50,
						'class' => 'large-text code'
					),
				),
			),
		);

		$this->tab_sections['resumes']['subscriptions'] = array(
			'title' => __( 'Subscriptions', APP_TD ),
			'desc' => '',
			'fields' => array(
				array(
					'title' => __( 'Enable', APP_TD ),
					'name' => 'jr_resume_require_subscription',
					'type' => 'checkbox',
					'desc' => __( 'Require a subscription for resume browsing', APP_TD ),
					'tip' => sprintf( __( "Enabling this option will block access to the resume section if the user does not have an active subscription. Access will still be determined by your visibility settings (if set to 'Recruiters', only recruiters will be able to subscribe). To subscribe, the user must be logged in.<br/><br/><strong>Note:</strong> If you set the visibility settings to 'Anyone', this option will be ignored since anyone will be able to browse resumes without a subscription.<br/><br/>You can create resume plans that recruiters or even job seekers can subscribe to on the <a href='%s'>Resumes Plans</a> page.", APP_TD ), 'edit.php?post_type=' . APPTHEMES_RESUMES_PLAN_PTYPE ),
				),
				array(
					'title' => __( 'Notice', APP_TD ),
					'name' => 'jr_resume_subscription_notice',
					'type' => 'textarea',
					'desc' => __( 'Appears above the subscription button.', APP_TD ),
					'tip' => '',
					'extra' => array(
						'rows' => 5,
						'cols' => 50,
						'class' => 'large-text code'
					),
				),
			),
		);

	}

	protected function tab_security() {

		$this->tab_sections['security']['settings'] = array(
			'title' => __( 'General', APP_TD ),
			'desc' => __( 'Prevent certain types of users from accessing the WordPress back-end.', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'WP-Admin', APP_TD ),
					'desc' => sprintf( __( "Restrict access by <a target='_blank' href='%s'>specific role</a>.", APP_TD ), 'http://codex.wordpress.org/Roles_and_Capabilities' ),
					'type' => 'select',
					'name' => 'jr_admin_security',
					'values' => array(
						'manage_options' => __( 'Admins Only', APP_TD ),
						'edit_others_posts' => __( 'Admins, Editors', APP_TD ),
						'publish_posts' => __( 'Admins, Editors, Authors', APP_TD ),
						'edit_posts' => __( 'Admins, Editors, Authors, Contributors', APP_TD ),
						'read' => __( 'All Access', APP_TD ),
						'disable' => __( 'Disable', APP_TD ),
					),
					'tip' => '',
				),
			),
		);

		$this->tab_sections['security']['recaptcha'] = array(
			'title'  => __( 'reCaptcha', APP_TD ),
			'desc'   => sprintf( __( "Free <a target='_blank' href='%s'>anti-spam service</a> provided by Google. It protects your website from spam and abuse while letting real people pass through with ease.", APP_TD ), esc_url( 'https://www.google.com/recaptcha/' ) ),
			'fields' => array(
				array(
					'title' => __( 'Public Key', APP_TD ),
					'desc'  => '',
					'type'  => 'text',
					'name'  => 'jr_captcha_public_key',
					'desc'  => '',
				),
				array(
					'title' => __( 'Private Key', APP_TD ),
					'desc'  => '',
					'type'  => 'text',
					'name'  => 'jr_captcha_private_key',
					'tip'   => '',
				),
				array(
					'title'  => __( 'Theme', APP_TD ),
					'type'   => 'select',
					'name'   => 'jr_captcha_theme',
					'values' => array(
						'light' => __( 'Light', APP_TD ),
						'dark'  => __( 'Dark', APP_TD ),
					),
					'tip' => '',
				),
				array(
					'title' => __( 'Registration', APP_TD ),
					'name'  => 'jr_captcha_enable',
					'type'  => 'checkbox',
					'desc'  => '',
					'tip'   => __( 'Displays a verification box on your registration page to prevent your website from spam and abuse.', APP_TD ),
				),
				array(
					'title'  => __( 'Contact Form', APP_TD ),
					'name'   => 'jr_captcha_contact_forms_enable',
					'type'   => 'select',
					'desc'   => __( 'Require this type', APP_TD ),
					'tip'    => __( 'Require this type of user to complete a reCaptcha before they can submit a contact request (affects the contact us and online resume pages).', APP_TD ),
					'values' => array(
						'all'      => __( 'All', APP_TD ),
						'no'       => __( 'No', APP_TD ),
						'visitors' => __( 'Visitors', APP_TD ),
					),
				),
				array(
					'title'  => __( 'Apply Online Form', APP_TD ),
					'name'   => 'jr_captcha_application_form_enable',
					'type'   => 'select',
					'desc'   => __( "Enable on the 'Apply Online' form", APP_TD ),
					'tip'    => __( 'Require this type of user to complete a reCaptcha before they can submit an application.', APP_TD ),
					'values' => array(
						'all'      => __( 'All', APP_TD ),
						'no'       => __( 'No', APP_TD ),
						'visitors' => __( 'Visitors', APP_TD ),
					),
				),
			),
		);

		$this->tab_sections['security']['anti-spam'] = array(
			'title'  => __( 'Anti-Spam', APP_TD ),
			'desc'   => __( 'Prevent spam on job applications.', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Question', APP_TD ),
					'name'  => 'jr_antispam_question',
					'type'  => 'text',
					'desc'  => '',
					'tip'   => '',
					'extra' => array(
						'class' => 'regular-text code'
					),
				),
				array(
					'title' => __( 'Answer', APP_TD ),
					'name'  => 'jr_antispam_answer',
					'type'  => 'text',
					'desc'  => '',
					'tip'   => '',
					'extra' => array(
						'class' => 'regular-text code'
					),
				),
			),
		);
	}

	protected function tab_advertising() {

		$this->tab_sections['advertising']['header-banner'] = array(
			'title' => __( 'Header Ad (468x60)', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Enable', APP_TD ),
					'name' => 'jr_enable_header_banner',
					'type' => 'checkbox',
					'desc' => __( 'Displayed in the header', APP_TD ),
					'tip' => __( 'Replace the default header navigation with your custom banner.', APP_TD ),
				),
				array(
					'title' => __( 'Code', APP_TD ),
					'name' => 'jr_header_banner',
					'type' => 'textarea',
					'desc' => __( 'Image, link, or JavaScript for the ad space.', APP_TD ),
					'tip' => '',
					'extra' => array(
						'rows' => 15,
						'cols' => 50,
						'class' => 'large-text code'
					),
					'sanitize' => 'appthemes_clean',
				),
			),
		);

		$this->tab_sections['advertising']['job-listing-banner'] = array(
			'title' => __( 'Content Ad (468x60)', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Enable', APP_TD ),
					'name' => 'jr_enable_listing_banner',
					'type' => 'checkbox',
					'desc' => __( 'Displayed on the single job listing page', APP_TD ),
					'tip' => '',
				),
				array(
					'title' => __( 'Code', APP_TD ),
					'name' => 'jr_listing_banner',
					'type' => 'textarea',
					'desc' => __( 'Image, link, or JavaScript for the ad space.', APP_TD ),
					'tip' => '',
					'extra' => array(
						'rows' => 15,
						'cols' => 50,
						'class' => 'large-text code'
					),
					'sanitize' => 'appthemes_clean',
				),
			),
		);
	}

	protected function tab_advanced() {

		$this->tab_sections['advanced']['user'] = array(
			'title' => __( 'User', APP_TD ),
			'fields' => array(
				array(
					'title'	 => __( 'Set Password', APP_TD ),
					'name'	 => 'jr_allow_registration_password',
					'type'	 => 'checkbox',
					'desc'	 => __( 'Let the user create their own password vs a system generated one', APP_TD ),
					'tip'	 => '',
				),
				array(
					'title' => __( 'Terms & Conditions', APP_TD ),
					'name' => 'jr_enable_terms_conditions',
					'type' => 'checkbox',
					'desc' => __( 'Display an opt-in checkbox for new registrations', APP_TD ),
					'tip'  => sprintf( __( 'Require all new users accept your terms and conditions before they can register. Edit the <a href="%s">terms page</a> to add your own content.', APP_TD ), 'post.php?action=edit&post=' . JR_Terms_Conditions_Page::get_id() ),
				),
				array(
					'title' => __( 'Disable Toolbar', APP_TD ),
					'name' => 'jr_remove_admin_bar',
					'type' => 'checkbox',
					'desc' => __( 'Hide the WordPress toolbar for logged in users', APP_TD ),
					'tip' => '',
				),
			),
		);

		$this->tab_sections['advanced']['developer'] = array(
			'title' => __( 'Developer', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Debug Mode', APP_TD ),
					'name' => 'jr_debug_mode',
					'type' => 'checkbox',
					'desc' => __( 'Print out the <code>$wp_query->query_vars</code> array at the top of your website', APP_TD ),
					'tip' => '',
				),
				array(
					'title' => __( 'Hide Version', APP_TD ),
					'name' => 'jr_remove_wp_generator',
					'type' => 'checkbox',
					'desc' => __( 'Remove the WordPress version meta tag from your website', APP_TD ),
					'tip' => __( "An added security measure so snoopers won't be able to tell what version of WordPress you're running.", APP_TD ),
				),
				array(
					'title' => __( 'Debug Log', APP_TD ),
					'name' => 'jr_enable_log',
					'type' => 'checkbox',
					'desc' => sprintf( __( 'Log emails and transactions in <code>%s</code>' , APP_TD ), jrLog::get_log_file_path() ),
					'tip' => __( "Make sure to delete the logs when you're finished debugging since they contain sensitive information.", APP_TD ),
				),
			),
		);
	}


	### Helper Methods

	/**
	 * Display additional section on the permalinks page.
	 */
	function init_integrated_options() {
		$this->permalink_sections();
	}


	### Permalinks

	function permalink_sections() {

		$option_page = 'permalink';
		$new_section = 'jr_options'; // store permalink options on global 'jr_options'

		$this->permalink_sections = array(
			'jobs' => __( 'Jobs Custom Post Type & Taxonomy URLs', APP_TD ),
			'resumes' => __( 'Resumes Custom Post Type', APP_TD ),
		);

		$this->permalink_options['jobs'] = array(
			'jr_job_permalink' => __( 'Job Listing Base URL', APP_TD ),
			'jr_job_cat_tax_permalink' => __( 'Job Category Base URL', APP_TD ),
			'jr_job_type_tax_permalink' => __( 'Job Type Base URL', APP_TD ),
			'jr_job_tag_tax_permalink' => __( 'Job Tag Base URL', APP_TD ),
			'jr_job_salary_tax_permalink' => __( 'Job Salary Base URL', APP_TD ),
		);

		$this->permalink_options['resumes'] = array(
			'jr_resume_permalink' => __( 'Resume Base URL', APP_TD ),
		);

		register_setting(
				$option_page, $new_section, array( $this, 'permalink_options_validate' )
		);

		foreach ( $this->permalink_sections as $section => $title ) {

			add_settings_section(
					$section, $title, '__return_false', $option_page
			);

			foreach ( $this->permalink_options[$section] as $id => $title ) {

				add_settings_field(
						$new_section . '_' . $id, $title, array( $this, 'permalink_section_add_option' ), // callback to output the new options
						$option_page, // options page
						$section, // section
						array( 'id' => $id )	   // callback args [ database option, option id ]
				);
			}
		}
	}

	function permalink_section_add_option( $option ) {
		global $jr_options;

		echo scbForms::input( array(
			'type' => 'text',
			'name' => 'jr_options[' . $option['id'] . ']',
			'extra' => array( 'size' => 53 ),
			'value' => $jr_options->{$option['id']},
		) );
	}

	/**
	 * Validate/sanitize permalinks.
	 */
	function permalink_options_validate( $input ) {
		global $jr_options;

		$error_html_id = '';

		foreach ( $this->permalink_sections as $section => $title ) {

			foreach ( $this->permalink_options[$section] as $key => $value ) {

				if ( empty( $input[$key] ) ) {
					$error_html_id = $key;
					// set option to previous value
					$input[$key] = $jr_options->$key;
				} else {
					if ( !is_array( $input[$key] ) ) {
						$input[$key] = trim( $input[$key] );
					}
					$input[$key] = stripslashes_deep( $input[$key] );
				}
			}
		}

		if ( $error_html_id ) {

			add_settings_error(
					'jr_options', $error_html_id, __( 'Custom post types and taxonomy URLs cannot be empty. Empty options will default to previous value.', APP_TD ), 'error'
			);
		}
		return $input;
	}

}
