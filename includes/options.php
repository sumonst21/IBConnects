<?php
/**
 * All the default theme options are initialized here.
 *
 * @version 1.6
 * @author AppThemes
 * @package JobRoller\Options
 * @copyright 2010 all rights reserved
 */

$settings = array(

	# Settings

	### general tab

	// appearance
	'jr_child_theme' => 'style-pro-blue.css',
	'jr_use_logo'    => 'yes',
	'jr_logo_url'    => '',
	'jr_disable_blog'=> 'yes',
	'breadcrumbs'    => '',

	// footer
	'multi_column_footer' => 'yes',
	'footer_width'        => '990px',
	'footer_cols'         => '3',
	'footer_col_width'    => '290px',

	// colors
	'top_nav_bgcolor'           => '',
	'top_nav_links_color'       => '',
	'top_nav_hover_links_color' => '',
	'top_nav_sep_color'         => '',
	'header_bgcolor'            => '',
	'buttons_color'             => '',
	'buttons_nav_link_color'    => '',
	'buttons_hover_bgcolor'     => '',
	'buttons_selected_bgcolor'  => '',
	'links_color'               => '',
	'footer_bgcolor'            => '',
	'footer_links_color'        => '',
	'footer_text_color'         => '',
	'footer_titles_color'       => '',
	'footer_sep_color'          => '',

	// social
	'jr_feedburner_url'   => '',
	'jr_twitter_id'       => '',
	'jr_facebook_id'      => '',
	'jr_sharethis_id'     => '',
	'jr_google_analytics' => '',

	// google
	'jr_gmaps_lang'    => 'en',
	'jr_gmaps_region'  => 'US',
	'jr_distance_unit' => 'mi',
	'gmaps_api_key'    => '',

	// registration
	'jr_allow_registration_password' => 'yes',
	'jr_enable_terms_conditions'     => '',

	// general
	'jr_show_sidebar'			=> 'yes',
	'jr_show_searchbar'			=> 'yes',
	'jr_show_filterbar'			=> 'yes',
	'jr_show_empty_categories'	=> '1',
	'jr_jobs_submit_text'		=> '',
	'allow_job_comments'		=> '',

	### jobs tab

	// settings
	'jr_jobs_default_expires' => '30',
	'jr_jobs_charge'		=> '',
	'jr_allow_relist'		=> 'yes',
	'jr_allow_editing'		=> 'yes',
	'jr_submit_cat_required'=> '',
	'jr_submit_cat_editable'=> 'yes',
	'apply_reg_users_only'  => 'yes',

	// moderation
	'jr_jobs_require_moderation' => 'yes',
	'jr_editing_needs_approval'  => 'yes',

	// appearance
	'jr_ad_stats_all'					=> 'yes',
	'jr_submit_how_to_apply_display'	=> 'yes',
	'jr_enable_salary_field'			=> 'yes',
	'jr_html_allowed'					=> 'yes',
	'jr_expired_action'					=> 'display_message',

	// listings
	'jr_jobs_per_page'			=> get_option( 'posts_per_page' ),
	'jobs_frontpage'			=> get_option( 'posts_per_page' ),
	'featured_jobs_frontpage'	=> '',

	'jr_featured_jobs_per_page'	=> '',
	'jr_featured_jobs_sort'		=> 'newest',

	### resumes tab

	// general
	'jr_resume_listing_visibility'	=> 'listers',
	'jr_resume_visibility'			=> 'listers',
	'jr_resumes_per_page'			=> 15,

	// job seekers
	'jr_allow_job_seekers'			=> 'yes',
	'jr_allow_recruiters'			=> '',
	'jr_my_profile_button_text'		=> 'Submit your Resume, update your profile, and allow employers to find <em>you</em>!',
	'jr_submit_resume_button_text'	=> 'Register as a Job Seeker to submit your Resume.',

	// subscriptions
	'jr_resume_require_subscription'=> '',
	'jr_resume_subscription_notice' => 'Sorry, you do not have access to resumes. To access and/or view our resume database please subscribe.',

	// anti-spam
	'jr_resume_show_contact_form' => '',

	### security tab

	// settings
	'jr_admin_security' => 'read',

	// reCaptcha
	'jr_captcha_enable'					=> '',
	'jr_captcha_contact_forms_enable'	=> 'yes',
	'jr_captcha_application_form_enable'=> 'yes',

	// reCaptcha settings
	'jr_captcha_public_key'	=> '',
	'jr_captcha_private_key'=> '',
	'jr_captcha_theme'		=> 'light',

	// anti-spam
	'jr_antispam_question'	=> 'Is fire &ldquo;<em>hot</em>&rdquo; or &ldquo;<em>cold</em>&rdquo;?',
	'jr_antispam_answer'	=> 'hot',

	### advertising tab

	// header
	'jr_enable_header_banner'	=> '',
	'jr_header_banner'			=> '',

	// job listing banner
	'jr_enable_listing_banner' => '',
	'jr_listing_banner'			=> '',

	### advanced tab

	// settings
	'jr_debug_mode' => '',
	'jr_enable_log' => '',
	'jr_remove_wp_generator' => '',
	'jr_remove_admin_bar'	=> '',

	### emails tab

	// notifications
	'jr_nu_admin_email'			=> 'yes',
	'jr_new_ad_email'			=> 'yes',
	'jr_new_job_email_owner'	=> 'yes',
	'jr_expired_job_email_owner'=> 'yes',
	'jr_bcc_apply_emails'		=> 'yes',

	// new registration
	'jr_nu_custom_email'	=> '',
	'jr_nu_from_name'		=> '',
	'jr_nu_from_email'		=> '',
	'jr_nu_email_subject'	=> __( 'Thank you for registering, %username%', APP_TD ),
	'jr_nu_email_type'		=> 'text/plain',
	'jr_nu_email_body'		=> '',

	# Alerts

	### general tab

	// job alerts
	'jr_job_alerts'				=> '',
	'jr_job_alerts_batch_size'	=> 100,
	'jr_job_alerts_jobs_limit'	=> 5,
	'jr_job_alerts_cron'		=> 'hourly',

	// feeds
	'jr_job_alerts_feed' => '',

	### email format tab

	// job alerts
	'jr_job_alerts_from_name'		=> get_bloginfo( 'title' ),
	'jr_job_alerts_from_email'		=> '',
	'jr_job_alerts_email_subject'	=> __( 'Job Alerts', APP_TD ),
	'jr_job_alerts_email_type'		=> 'text/plain',
	'jr_job_alerts_email_template'	=> 'standard',

	// standard email
	'jr_job_alerts_email_body'	=> '',
	'jr_job_alerts_job_body'	=> '',

	# Integration

	### Indeed tab

	// general
	'indeed_publisher_enable'	=> '',
	'jr_indeed_publisher_id'	=> '',
	'jr_indeed_channel'			=> '',

	// queries
	'jr_indeed_front_page_count'	=> 5,
	'jr_indeed_site_type'			=> 'relevance',
	'jr_indeed_sort_order'			=> 'relevance',
	'jr_front_page_indeed_queries'	=> "web designer|US|fulltime\nweb developer|US|contract\nwordpress|US|parttime",
	'indeed_fixed_keywords'			=> '',

	// mappings
	'jr_indeed_jtypes_other'		=> "full-time|fulltime\npart-time|parttime",
	'jr_indeed_job_type_sponsored'	=> 'job-featured',

	// display
	'jr_indeed_front_page'			=> '',
	'jr_indeed_all_listings'		=> '',
	'jr_dynamic_search_results'		=> '',
	'jr_indeed_results_position'	=> 'after',

	// caching
	'jr_indeed_frontpage_cache' => '3600',

	### Careerjet tab

	// general
	'jr_careerjet_publisher_id'	=> '',
	'jr_careerjet_country'		=> 'en_US',

	// queries
	'jr_careerjet_front_page_count'		=> 5,
	'jr_careerjet_sort_order'			=> 'relevance',
	'jr_front_page_careerjet_queries'	=> "web designer|f|los angeles, CA\nweb developer|p|los angeles, CA\nwordpress|p|los angeles, CA",

	// mappings
	'jr_careerjet_jtypes_other' => "full-time|f\npart-time|p",

	// display
	'jr_careerjet_front_page'				=> '',
	'jr_careerjet_all_listings'				=> '',
	'jr_careerjet_dynamic_search_results'	=> '',
	'jr_careerjet_results_position'			=> 'after',

	// caching
	'jr_careerjet_frontpage_cache' => '3600',

	### SimplyHired tab

	// general
	'jr_simplyhired_publisher_id'	=> '',
	'jr_simplyhired_auth_key'		=> '',
	'jr_simplyhired_country'		=> 'us',
	'jr_simplyhired_domain_id'		=> '',

	// queries
	'jr_simplyhired_front_page_count'	=> 5,
	'jr_simplyhired_sort_order'			=> 'rd',
	'jr_simplyhired_onet_code'			=> '',
	'jr_front_page_simplyhired_queries'	=> "web designer|full-time|los angeles, CA\nweb developer|full-time\nwordpress|part-time",

	// mappings
	'jr_simplyhired_jtypes_other' => "",

	// styling
	'jr_simplyhired_job_type_sponsored'	=> 'job-featured',
	'jr_simplyhired_job_type_organic'	=> '',
	'jr_simplyhired_job_type_paid'		=> '',

	// display
	'jr_simplyhired_front_page'				=> '',
	'jr_simplyhired_all_listings'			=> '',
	'jr_simplyhired_dynamic_search_results'	=> '',
	'jr_simplyhired_results_position'		=> 'after',

	// caching
	'jr_simplyhired_frontpage_cache' => '3600',

	### LinkedIn tab

	// general
	'jr_linkedin_api_key'		=> '',
	'jr_linkedin_api_secret'	=> '',
	'jr_linkedin_country'		=> 'us',
	'jr_linkedin_industry'		=> 0,

	// queries
	'jr_linkedin_front_page_count'	=> 5,
	'jr_linkedin_sort_order'		=> 'rd',
	'jr_front_page_linkedin_queries'=> "web design|f|us\nweb developer|f\nwordpress|p",

	// mappings
	'jr_linkedin_jtypes_other' =>  "full-time|f\npart-time|p\ntemporary|t",

	// styling
	'jr_linkedin_job_type_sponsored_css' => 'job-sponsored',
	'jr_linkedin_job_type_companies_css' => 'job-key-companies',
	'jr_linkedin_job_type_companies'	 => '',

	// display
	'jr_linkedin_visibility'			=> 'all',
	'jr_linkedin_revoke'				=> 'yes',
	'jr_linkedin_front_page'			=> '',
	'jr_linkedin_all_listings'			=> '',
	'jr_linkedin_dynamic_search_results'=> '',
	'jr_linkedin_results_position'		=> 'after',

	// caching
	'jr_linkedin_frontpage_cache' => '3600',

	# Payments
	'allow_view_orders'   => false,
	'currency_code'       => 'USD',
	'currency_identifier' => 'symbol',
	'currency_position'   => 'left',
	'thousands_separator' => ',',
	'decimal_separator'   => '.',
	'tax_charge'          => 0,

	'plan_type' => 'single',

	'separate_packs'	=> 'no',
	'plan_display_cats' => 'no',

	// Featured Listings
	'addons' => array(
		JR_ITEM_FEATURED_LISTINGS => array(
			'enabled' 	=> '',
			'price' 	=> 0,
			'duration' 	=> 30,
		),

		JR_ITEM_FEATURED_CAT => array(
			'enabled' 	=> '',
			'price' 	=> 0,
			'duration' 	=> 30,
		),

		JR_ITEM_BROWSE_RESUMES => array(
			'enabled' 	=> '',
			'price'		=> 0,
			'duration' 	=> 30,
		),

		JR_ITEM_VIEW_RESUMES => array(
			'enabled' 	=> '',
			'price' 	=> 0,
			'duration' 	=> 30,
		),
	),

	// Gateways
	'gateways' => array(
		'enabled' => array(),
	),

	// Permalinks
	'jr_job_permalink'            => 'jobs',
	'jr_job_cat_tax_permalink'    => 'job-category',
	'jr_job_type_tax_permalink'   => 'job-type',
	'jr_job_tag_tax_permalink'    => 'job-tag',
	'jr_job_salary_tax_permalink' => 'job-salary',
	'jr_resume_permalink'         => 'resumes',
);

$GLOBALS['jr_options'] = new scbOptions( 'jr_options', false, $settings );
