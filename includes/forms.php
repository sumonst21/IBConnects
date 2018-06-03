<?php
/**
 * JobRoller Forms Processing.
 * Handles data submitted by forms.
 *
 * @version 1.8
 * @author AppThemes
 * @package JobRoller\Forms
 * @copyright 2010 all rights reserved
 */

add_action( 'wp_loaded', 'jr_handle_job_submit_form' );
add_action( 'wp_loaded', 'jr_handle_job_confirmation' );

add_action( 'jr_listing_validate_fields', '_jr_validate_listing_category', 10 );
add_action( 'jr_listing_validate_fields', '_jr_validate_listing_fields', 11 );

add_action( 'jr_handle_listing_fields', 'jr_maybe_strip_tags', 10, 3 );
add_action( 'jr_handle_listing_fields', 'jr_format_contact_fields', 11, 3 );

add_filter( 'jr_handle_update_listing', 'jr_validate_update_listing' );
add_action( 'transition_post_status', '_jr_maybe_delete_submit_meta', 10, 3 );


### Main Form Views

class JR_Register_Form extends APP_View {

	function init() {
		add_filter( 'user_register', array( $this, 'user_register_role' ) );
		add_action( 'register_post', array( $this, 'process_register_form' ), 10, 3 );
	}

	function condition() {
		return true;
	}

	// update the user role
	function user_register_role( $user_id ) {
		global $posted;

		if ( !empty( $posted['role'] ) ) {
			$user_role = $posted['role'];
		} else {
			$user_role = 'job_lister';
		}

		wp_update_user( array ( 'ID' => $user_id, 'role' => $user_role ) );
	}

	// validate additional registration fields
	function process_register_form( $login, $email, $errors ) {
		global $posted, $jr_options;

		// Check terms acceptance
		if ( $jr_options->jr_terms_page_id || $jr_options->jr_enable_terms_conditions ) {
			if ( ! isset( $_POST['terms'] ) ) {
				$errors->add( 'empty_terms', __( '<strong>Notice</strong>: You must accept our terms and conditions in order to register.', APP_TD ) );
			}
		}

		// validate the  user role

		if ( $jr_options->jr_allow_job_seekers ) {

			if ( ! isset( $_POST['role'] ) ) {
				$errors->add( 'empty_role', __('<strong>Notice</strong>: Please select a role.', APP_TD) );
			} else {

				if ( ! in_array( $_POST['role'], array( 'job_lister', 'job_seeker', 'recruiter' ) ) ) {
					$errors->add( 'empty_role', __( '<strong>Notice</strong>: Invalid Role!', APP_TD ) );
				} else {
					$posted['role'] = stripslashes( trim( $_POST['role'] ) );
				}

			}

		}

	}

}

class JR_Jobs_Filter_Form extends APP_View {

	function init() {
		if ( ! $this->condition() ) {
			return;
		}

		add_filter( 'posts_orderby', array( $this, 'posts_orderby' ) );
	}

	function condition() {
		global $wp_query;

		return  ( ! empty( $_GET['location'] ) || ! empty( $wp_query->query_vars['location_search'] ) );
	}

	/**
	 * Sort location queries by the same order as the ID's on the found posts array ($find_posts_in).
	 */
	function posts_orderby( $orderby ) {
		$find_posts_in = get_query_var('post__in');

		if ( is_array( $find_posts_in ) && ! empty( $find_posts_in ) ) {
			$posts_in = implode(',', $find_posts_in );
			$orderby = 'FIELD(ID, ' . $posts_in . ')';
		}

		return $orderby;
	}

}

class JR_Job_Seeker_Prefs_Form extends APP_View {

	function init() {
		if ( ! $this->condition_save() ) {
			return;
		}

		do_action( 'jr_process_job_seeker_form' );
	}

	function condition() {
		return ( ! empty( $_POST['save_prefs'] ) );
	}

	function condition_save() {
		return ( ! empty( $_POST['save_prefs'] ) || ! empty( $_POST['save_alerts'] ) );
	}

	function template_redirect() {
		$this->process();
	}

	function process() {

		// get (and clean) data
		$fields = array(
			'career_status',
			'willing_to_relocate',
			'willing_to_travel',
			'keywords',
			'search_location',
			'availability_month',
			'availability_year'
		);

		foreach ( $fields as $field ) {

			if ( isset( $_POST[ $field ] ) ) {
				$posted[ $field ] = stripslashes( trim( $_POST[ $field ] ) );
			} else {
				$posted[ $field ] = '';
			}

		}

		$job_types = array();

		if ( isset( $_POST['prefs_job_types'] ) ) {
			$prefs_job_types = $_POST['prefs_job_types'];
		} else {
			$prefs_job_types = '';
		}

		if ( is_array( $prefs_job_types ) ) {

			foreach( $prefs_job_types as $key => $value ) {
				$job_types[] = $key;
			}

		}

		// save prefs metadata

		$user_id = get_current_user_id();

		update_user_meta( $user_id, 'availability_month', $posted['availability_month'] );
		update_user_meta( $user_id, 'availability_year', (int) $posted['availability_year'] );

		update_user_meta( $user_id, 'career_status', $posted['career_status'] );
		update_user_meta( $user_id, 'willing_to_relocate', $posted['willing_to_relocate'] );
		update_user_meta( $user_id, 'willing_to_travel', $posted['willing_to_travel'] );
		update_user_meta( $user_id, 'keywords', $posted['keywords'] );
		update_user_meta( $user_id, 'search_location', $posted['search_location'] );
		update_user_meta( $user_id, 'job_types', $job_types );

		appthemes_add_notice( 'prefs_saved', __( 'Preferences Saved', APP_TD ), 'success' );
	}

}

class JR_Job_Seeker_Alerts_Form extends JR_Job_Seeker_Prefs_Form {

	function condition() {
		return ( ! empty( $_POST['save_alerts'] ) );
	}

	function process() {
		global $user_ID, $errors, $jr_options;

		$job_types = $job_cats = array();

		$alert = '';

		$errors = new WP_Error();

		// Get (and clean) data
		$fields = array(
			'alert_keywords',
			'alert_location',
			'alert_status'
		);

		$required = array(
			'alert_keywords',
			'alert_location',
			'alert_job_type',
			'alert_job_cat',
		);

		$valid_alert = false;

		foreach ( $fields as $field ) {

			if ( isset( $_POST[$field] ) ) {
				$posted[$field] = stripslashes( trim( strtolower( $_POST[$field] ) ) );
			} else {
				$posted[$field] = '';
			}

		}

		foreach ( $required as $field ) {

			if ( !empty( $_POST[$field] ) ) {
				$valid_alert = true;
			}

		}

		// get (and clean) job types
		if ( isset( $_POST['alert_job_type'] ) ) {

			foreach ( $_POST['alert_job_type'] as $term ) {
				if ( term_exists( (int) $term, APP_TAX_TYPE ) ) {
					$job_types[] = $term;
				}
			}

		}

		// get (and clean) job categories
		if ( isset( $_POST['alert_job_cat'] ) ) {

			foreach ( $_POST['alert_job_cat'] as $term ) {
				if ( term_exists( (int) $term, APP_TAX_CAT ) ) {
					$job_cats[] = $term;
				}
			}

		}

		// keywords
		if ( ! empty($posted['alert_keywords']) ) {
			$keywords = explode(',', $posted['alert_keywords']);
			$keywords_regexp = array_map('trim',$keywords);
			$keywords_regexp = implode('|', $keywords_regexp);

			// store keywords as as a regular expression '(keyword1|keyword2)'
			update_user_meta( $user_ID, 'jr_alert_keywords', '(' . $keywords_regexp . ')');
			update_user_meta( $user_ID, 'jr_alert_meta_keyword', $keywords);
		} else {
			delete_user_meta( $user_ID, 'jr_alert_keywords');
			delete_user_meta( $user_ID, 'jr_alert_meta_keyword');
		}

		// locations
		if ( ! empty( $posted['alert_location'] ) ) {

			$i = 0;
			$locations = explode(',', $posted['alert_location']);
			foreach ( $locations as $location ) {
				if ( $i++ > 0 ) {
					$alert .= '|?';
				}
				$alert .= 'location=' . trim(strtolower($location));
			}

			update_user_meta( $user_ID, 'jr_alert_meta_location', $locations);

		} else {
			$alert = 'location=anywhere';
			delete_user_meta( $user_ID, 'jr_alert_meta_location');
		}

		if ( $alert ) {
			$alert .= '&';
		}

		// job types
		if ( ! empty($job_types) ) {

			$i = 0;
			foreach ( $job_types as $job_type ) {
				if ( $i++ > 0 ) {
					$alert .= '|?';
				}
				$alert .= 'job_type=' . trim(strtolower($job_type));
			}

			update_user_meta( $user_ID, 'jr_alert_meta_job_type', $job_types );

		} else {
			$alert .= 'job_type=all';
			delete_user_meta( $user_ID, 'jr_alert_meta_job_type');
		}

		if ( $alert ) {
			$alert .= '&';
		}

		// job categories
		if ( ! empty($job_cats) ) {

			$i = 0;
			foreach ( $job_cats as $job_cat ) {
				if ( $i++ > 0 ) {
					$alert .= '|?';
				}
				$alert .= 'job_cat=' . trim( strtolower( $job_cat ) );
			}

			update_user_meta( $user_ID, 'jr_alert_meta_job_cat', $job_cats );

		} else {
			$alert .= 'job_cat=all';
			delete_user_meta( $user_ID, 'jr_alert_meta_job_cat');
		}

		if ( ! $valid_alert ) {
			$errors->add('submit_error', __('<strong>ERROR</strong>: ', APP_TD).__('You haven\'t specified any jobs alerts criteria.', APP_TD));
			update_user_meta( $user_ID, 'jr_alert_status', 'inactive');
		}

		if ( $errors && sizeof( $errors ) > 0 && $errors->get_error_code() ) {
			$posted['errors'] = $errors;
		} else {

			$alert = '?' . $alert;
			$alert_status = empty( $posted['alert_status'] ) ? 'active' : $posted['alert_status']; // if site is only using feeds for job alerts always set alert status to 'active'

			// store the user alert and the status subscribed/unsubscribed
			update_user_meta( $user_ID, 'jr_alert', $alert );
			update_user_meta( $user_ID, 'jr_alert_status', $alert_status );

			if ( $jr_options->jr_job_alerts_feed ) {
				global $wp_rewrite;

				$user_feed_key = get_user_meta( $user_ID, 'jr_alert_feed_key', true );

				if ( 'active' == $alert_status ) {
					add_feed($user_feed_key, 'jr_job_alerts_do_feed');
				} else {
					$feed_key = array_search( $user_feed_key, $wp_rewrite->feeds );
					unset( $wp_rewrite->feeds[ $feed_key] );
				}

				// flush rewrite rules if User subscribes/unsubscribes the RSS feed
				flush_rewrite_rules();
			}

			appthemes_add_notice( 'alerts-saved', __( 'Job Alerts Preferences Saved', APP_TD ), 'success' );
		}

	}

}

class JR_Job_Application_Form extends APP_View {

	function init() {
		if ( ! $this->condition() ) {
			return;
		}

		add_action( 'job_main_section', array( $this, 'process_application_form' ), 1 );
	}

	function condition() {
		return ( ! empty( $_POST['apply_to_job'] ) );
	}

	function template_redirect() {

		if ( ! $this->validate() ) {
			return false;
		}

	}

	function validate() {

		if ( ! current_user_can( 'apply_to_job', get_the_ID() ) ) {
			$register_link = wp_login_url( get_permalink() . '#apply_form' );

			$errors = jr_get_listing_error_obj();
			$errors->add( 'submit_error', sprintf( __( 'Only registered users can apply for jobs. Please <a href="%s">login/register</a> first to apply for this job.', APP_TD ), esc_url( $register_link ) ) );
		}

		$errors = apply_filters( 'jr_job_application_validate', jr_get_listing_error_obj() );
		if ( $errors->get_error_codes() ) {
			return false;
		}

	}

	function process_application_form() {
		global $post, $app_form_results, $jr_options;

		$errors = jr_get_listing_error_obj();

		if ( $errors->get_error_codes() ) {
			return;
		}

		$posted = array();

		$is_valid = true;

		$apply_form_class = 'open';

		$linked_resume = '';

		// Get (and clean) data
		$fields = array(
			'your_name',
			'your_email',
			'your_message',
			'antispam_answer',
			'your_online_cv'
		);

		foreach( $fields as $field ) {

			if ( isset( $_POST[ $field] ) ) {
				$posted[$field] = stripslashes( trim( $_POST[ $field ] ) );
			} else {
				$posted[$field] = '';
			}

		}

		// Check required fields
		$required = array(
			'your_name' => __('Name', APP_TD),
			'your_email' => __('Email', APP_TD),
			'your_message' => __('Message', APP_TD),
		);

		foreach ( $required as $field => $name ) {
			if ( empty( $posted[$field] ) ) {
				$errors->add( 'submit_error_' . $name, __( '<strong>ERROR</strong>: &ldquo;', APP_TD ) . $name . __( '&rdquo; is a required field.', APP_TD ) );
			}
		}

		// Check the e-mail address
		if ( ! empty( $posted['your_email'] ) && ! is_email( $posted['your_email'] ) ) {
			$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: The email address isn&#8217;t correct.', APP_TD ) );
			$posted['your_email'] = '';
		}

		// Check linked CV
		if ( $posted['your_online_cv'] && $posted['your_online_cv'] > 0 ) {

			$posted['your_online_cv'] = (int) $posted['your_online_cv'];

			$linked_resume = get_post( $posted['your_online_cv'] );

			if ( ! is_wp_error( $linked_resume ) && $linked_resume->post_author == get_current_user_id() && $linked_resume->post_status == 'publish' ) {
				// Resume is okay :)
			} else {
				$errors->add( 'invalid_resume', __( '<strong>ERROR</strong>: Invalid resume.', APP_TD ) );
			}

		}

		// Check file extensions
		$allowed = jr_get_allowed_extensions_for('apply_job_cv');

		if ( is_uploaded_file( $_FILES['your_cv']['tmp_name'] ) ) {
			$uploaded_files['your_cv'] = $_FILES['your_cv'];
			$extension = strtolower( substr( strrchr( $_FILES['your_cv']['name'], "." ), 1 ) );
			if ( !in_array( $extension, $allowed ) ) {
				$errors->add( 'file_submit_error', sprintf( __( '<strong>ERROR</strong>: Only %s files are allowed for the resum&eacute; field.', APP_TD ), implode( ', ', $allowed ) ) );
			}
		}

		$allowed = jr_get_allowed_extensions_for('apply_job_cv_letter');

		if ( is_uploaded_file( $_FILES['your_coverletter']['tmp_name'] ) ) {
			$uploaded_files['your_coverletter'] = $_FILES['your_coverletter'];
			$extension = strtolower( substr( strrchr( $_FILES['your_coverletter']['name'], "." ), 1 ) );
			if ( !in_array( $extension, $allowed ) ) {
				$errors->add( 'file_submit_error', sprintf( __( '<strong>ERROR</strong>: Only %s files are allowed for the cover letter field.', APP_TD ), implode( ', ', $allowed ) ) );
			}
		}

		// Check AntiSpam Field
		if ( ! is_user_logged_in() && !jr_display_recaptcha( 'app-recaptcha-application' ) ) {

			$ans = strtolower( trim( $jr_options->jr_antispam_answer ) );
			if ( empty( $posted['antispam_answer'] ) || strtolower( trim( $posted['antispam_answer'] ) ) !== $ans ) {
				$errors->add( 'submit_error_antispam', __( '<strong>ERROR</strong>: Incorrect anti-spam answer. The correct answer is ', APP_TD ) . '"' . $ans . '".' );
			}

		}

		// Process the reCaptcha request if it's been enabled.
		$errors_recaptcha = jr_validate_recaptcha('app-recaptcha-application');
		if ( $errors_recaptcha && sizeof($errors_recaptcha)>0 ) {
			$errors->errors = array_merge( $errors->errors, $errors_recaptcha->errors);
		}

		if ( $errors && sizeof( $errors ) > 0 && $errors->get_error_code() ) {
			// There are errors!
			$is_valid = false;
		} else {
			$attachments = array();
			$attachment_urls = array();

			// Continue, upload files
			if ( is_uploaded_file( $_FILES['your_cv']['tmp_name'] ) || is_uploaded_file( $_FILES['your_coverletter']['tmp_name'] ) ) {

				// Find max filesize in bytes - we say 10mb becasue the file will be attached to an email, also checks system variables in case they are lower
				$max_sizes = array( '10485760' );

				if ( ( ini_get( 'post_max_size' ) ) ) {
					$max_sizes[] = let_to_num( ini_get( 'post_max_size' ) );
				}

				if ( ( ini_get( 'upload_max_filesize' ) ) ) {
					$max_sizes[] = let_to_num( ini_get( 'upload_max_filesize' ) );
				}

				if ( ( WP_MEMORY_LIMIT ) ) {
					$max_sizes[] = let_to_num( WP_MEMORY_LIMIT );
				}

				$max_filesize = min( $max_sizes );

				if ( ( $_FILES["your_cv"]["size"] + $_FILES["your_coverletter"]["size"] ) > $max_filesize ) {
					$errors->add( 'file_submit_error', __( '<strong>ERROR</strong>: ', APP_TD ) . 'Attachments too large. Maximum file size for all attachments is ' . ($max_filesize / (1024 * 1024)) . 'MB' );
				} else {

					/** WordPress Administration File API */
					include_once(ABSPATH . 'wp-admin/includes/file.php');
					/** WordPress Media Administration API */
					include_once(ABSPATH . 'wp-admin/includes/media.php');

					add_filter( 'upload_dir', array( $this, 'cv_upload_dir' ) );

					$time = current_time( 'mysql' );
					$overrides = array( 'test_form' => false );

					if ( is_uploaded_file( $_FILES['your_cv']['tmp_name'] ) ) {

						$file = wp_handle_upload( $_FILES['your_cv'], $overrides, $time );
						if ( !isset( $file['error'] ) ) {
							$attachments[] = $file['file'];
							$attachment_urls[] = $file['url'];
						} else {
							$errors->add( 'file_submit_error', __( '<strong>ERROR</strong>: ', APP_TD ) . $file['error'] . '' );
						}

					}

					if ( is_uploaded_file( $_FILES['your_coverletter']['tmp_name'] ) ) {

						$file = wp_handle_upload( $_FILES['your_coverletter'], $overrides, $time );
						if ( !isset( $file['error'] ) ) {
							$attachments[] = $file['file'];
							$attachment_urls[] = $file['url'];
						} else {
							$errors->add( 'file_submit_error', __( '<strong>ERROR</strong>: ', APP_TD ) . $file['error'] . '' );
						}

					}

				}

				remove_filter( 'upload_dir', 'company_logo_upload_dir' );
			}

			if ( $errors && sizeof( $errors ) > 0 && $errors->get_error_code() ) {
				$is_valid = false;
			} else {
				$headers = 'From: ' . $posted['your_name'] . ' <' . $posted['your_email'] . '>' . "\r\n\\";
				$message = __( "Applicant Name: ", APP_TD ) . $posted['your_name'] . "\n" . __( "Applicant Email: ", APP_TD ) . $posted['your_email'];

				if ( $posted['your_online_cv'] && $linked_resume ) {

					// Load or generate a key so that the recipient can view the resume without being logged in
					$view_key = get_post_meta( $linked_resume->ID, '_view_key', true );

					if ( !$view_key ) {
						$view_key = uniqid();
						update_post_meta( $linked_resume->ID, '_view_key', $view_key );
					}

					$link = add_query_arg( 'key', $view_key, get_permalink( $linked_resume->ID ) );
					$title = $linked_resume->post_title;

					$message .= "\n" . sprintf( __( 'Applicant\'s online resume: %1$s - %2$s', APP_TD ), $title, esc_url( $link ) );

				}

				$message .= "\n\n" . $posted['your_message'];

				wp_mail( get_the_author_meta( 'user_email' ), sprintf( __( 'Application for job "%s"', APP_TD ), $post->post_title ), $message, $headers, $attachments );

				// CC
				wp_mail( $posted['your_email'], sprintf( __( '[copy] Application for job "%s"', APP_TD ), $post->post_title ), $message, '', $attachments );

				// CC Admin
				if ( $jr_options->jr_bcc_apply_emails ) {
					wp_mail( get_option( 'admin_email' ), sprintf( __( '[copy] Application for job "%s"', APP_TD ), $post->post_title ), $message, '', $attachments );
				}

				if ( sizeof( $attachments ) > 0 ) {
					foreach ( $attachments as $attach ) {
						@unlink( $attach );
					}
				}
				appthemes_display_notice( 'success', __( 'Your application has been sent successfully.', APP_TD ) );

				$apply_form_class = "";

				$posted = array();
			}
		}

		$app_form_results = array(
			'class' => $apply_form_class,
			'errors' => $errors,
			'posted' => $posted
		);

		// if there are errors, warn the user to re-upload any attached files
		if ( isset( $uploaded_files ) && ! $is_valid && ! in_array( 'file_submit_error', $errors->get_error_codes() ) ) {
			$app_form_results['files_warning'] = __( 'Note: Please re-upload your files after correcting the errors.', APP_TD );
		}
	}

	function cv_upload_dir( $pathdata ) {
		$subdir = '/uploaded_cvs' . $pathdata['subdir'];
		$pathdata['path'] = str_replace( $pathdata['subdir'], $subdir, $pathdata['path'] );
		$pathdata['url'] = str_replace( $pathdata['subdir'], $subdir, $pathdata['url'] );
		$pathdata['subdir'] = str_replace( $pathdata['subdir'], $subdir, $pathdata['subdir'] );

		return $pathdata;
	}

}

class JR_Resume_Edit_Form extends APP_View {

	function init() {
		global $wp;

		$wp->add_query_var('edit');

		// make we we handle the form at the right timing to allow 'custom forms' support to be loaded
		add_action( 'wp_loaded', array( $this, 'handle_form' ) );

		add_action( 'jr_resume_validate_fields', array( $this, 'validate_category' ), 10 );
		add_action( 'jr_resume_validate_fields', array( $this, 'validate_fields' ), 11 );

		add_action( 'jr_handle_resume_fields', array( $this, 'maybe_strip_tags' ), 10, 3 );
		add_filter( 'jr_handle_update_resume', array( $this, 'validate_update_listing' ) );
	}

	function condition() {
		return ( ! empty( $_POST['save_resume'] ) );
	}

	function handle_form() {
		if ( ! $this->condition() ) {
			return;
		}

		if ( ! empty( $_POST['nonce'] ) && ! wp_verify_nonce( $_POST['nonce'], 'submit_resume' ) ) {
			$errors = jr_get_listing_error_obj();
			$errors->add( 'submit_error', __( '<strong>ERROR</strong>: Sorry, your nonce did not verify.', APP_TD ) );
			return;
		}

		$this->process();
	}

	protected function process() {
		$resume = $this->handle_update_resume();
		if ( ! $resume ) {
			// there are errors, return to current page
			return;
		}

		$url = get_permalink( $resume->ID );
		if ( ! $url ) {
			$url = get_permalink( JR_Dashboard_Page::get_id() );
		}

		appthemes_add_notice( 'saved', sprintf( __( 'Resume Saved', APP_TD ) ), 'success' );

		// redirect to resume
		wp_redirect( $url );
		exit();
	}

	// creates/updates the resume and all it's meta and terms
	protected function handle_update_resume() {

		$categories   = jr_get_listing_tax( 'resume_cat', APP_TAX_RESUME_CATEGORY );
		$position     = jr_get_listing_tax( 'desired_position', APP_TAX_RESUME_JOB_TYPE );
		$specialities = jr_get_listing_tax( 'specialities', APP_TAX_RESUME_SPECIALITIES, $term_id = false );
		$groups       = jr_get_listing_tax( 'groups', APP_TAX_RESUME_GROUPS, $term_id = false );
		$languages    = jr_get_listing_tax( 'languages', APP_TAX_RESUME_LANGUAGES, $term_id = false );

		$args = wp_array_slice_assoc( $_POST, array( 'resume_name', 'summary' ) );

		$args['post_author'] = get_current_user_id();
		$args['post_title'] = wp_strip_all_tags( $args['resume_name'] );
		$args['post_content'] = jr_maybe_strip_tags( $args['summary'], 'summary' );
		// Strip shortcodes
		if ( ! current_user_can( 'edit_others_posts' ) && ! is_admin() ) {
			$args['post_content'] = strip_shortcodes( $args['post_content'] );
		}
		$args['post_type'] = APP_POST_TYPE_RESUME;

		$errors = apply_filters( 'jr_resume_validate_fields', jr_get_listing_error_obj() );
		if ( $errors->get_error_codes() ) {
			return false;
		}

		if ( get_query_var('edit') ) {
			$resume_id = (int) get_query_var('edit');
		} elseif( ! empty( $_GET['edit'] ) ) {
			$resume_id = (int) $_GET['edit'];
		}

		if ( empty($resume_id) ) {
			$action = 'insert';
		} else {
			$resume = get_post( $resume_id );
			$action = 'update';
		}

		// do_action hook
		jr_before_insert_resume( $action );

		if ( empty( $resume ) ) {

			$args['post_status'] = 'private';
			$args['post_name'] = get_current_user_id() . uniqid( rand( 10, 1000 ), false );

			$resume_id = wp_insert_post( $args );
		} else {

			$args['ID'] = $resume_id;

			$resume_id = wp_update_post( $args );
		}

		### TERMS

		wp_set_object_terms( $resume_id, (int) $categories, APP_TAX_RESUME_CATEGORY );
		wp_set_object_terms( $resume_id, (int) $position, APP_TAX_RESUME_JOB_TYPE );

		$thetags = explode( ',', $specialities );
		$specialities = array_map( 'trim', $thetags );

		wp_set_object_terms( $resume_id, $specialities, APP_TAX_RESUME_SPECIALITIES );

		$thetags = explode( ',', $groups );
		$groups = array_map( 'trim', $thetags );

		wp_set_object_terms( $resume_id, $groups, APP_TAX_RESUME_GROUPS );

		$thetags = explode( ',', $languages );
		$languages = array_map( 'trim', $thetags );

		wp_set_object_terms( $resume_id, $languages, APP_TAX_RESUME_LANGUAGES );

		### META

		foreach ( jr_get_resume_listing_fields() as $field => $meta_name ) {
			$field_value = apply_filters( 'jr_handle_resume_fields', _jr_get_initial_field_value( $field ), $field, $resume_id );
			update_post_meta( $resume_id, $meta_name, $field_value );
		}

		jr_set_coordinates( $resume_id );

		### CUSTOM FIELDS

		jr_update_form_builder( $categories, $resume_id, APP_TAX_RESUME_CATEGORY );

		jr_handle_image_upload( $resume_id, 'your-photo' );

		jr_handle_files( $resume_id, $categories, APP_TAX_RESUME_CATEGORY, 'your-photo' );

		// do_action hook
		jr_after_insert_resume( $resume_id, $action );

		return apply_filters( 'jr_handle_update_resume', get_post( $resume_id ) );
	}

	// validate the job listing fields
	function validate_fields( $errors ) {

		// required fields
		$required = array(
			'resume_name'     => __( 'Title', APP_TD ),
			'summary'         => __( 'Summary', APP_TD ),
			'jr_geo_latitude' => __( 'Location', APP_TD ),
		);

		$fields = apply_filters( 'jr_resume_required_fields', $required );
		foreach ( $fields as $key => $name ) {
			if ( empty( $_POST[ $key ] ) ) {
				$errors->add( 'submit_error', __( '<strong>ERROR</strong>: Please fill in all required fields.', APP_TD ) );
			}
		}
		return $errors;
	}

	// validate the job listing category
	function validate_category( $errors ){
		return $errors;
	}

	// skip strips tags for fields where HTML is allowed
	function maybe_strip_tags( $field_value, $field, $post_id = 0 ) {
		global $jr_options;

		if ( $jr_options->jr_html_allowed ) {

			if ( in_array( $field, array( 'summary', 'education', 'experience' ) ) ) {
				return wp_kses_post( $field_value );
			}

		}

		return strip_tags( $field_value );
	}

	// validates the listing data and returns the post if there are no errors. In case of errors, returns false
	function validate_update_listing( $listing ) {

		$errors = jr_get_listing_error_obj();
		if ( $errors->get_error_codes() ) {
			return false;
		}
		return $listing;
	}

}


### Other Forms

// @todo: move to Views

/**
 * Handle free jobs - update the job status after user confirmation
 */
function jr_handle_job_confirmation() {

	if ( empty( $_POST['job_confirm'] ) ) {
		return;
	}

	if ( ! empty( $_POST['ID'] ) ) {

		if ( ! current_user_can( 'edit_job', (int) $_POST['ID'] ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		$job_id = (int) $_POST['ID'];
	} else {
		$errors = jr_get_listing_error_obj();
		$errors->add( 'submit_error', __( '<strong>ERROR</strong>: Cannot update job status. Job ID not found.', APP_TD ) );
		return;
	}

	jr_update_post_status( $job_id );

	_jr_set_job_duration( $job_id );

	do_action( 'jr_activate_job', $job_id );

	wp_redirect( get_permalink( $job_id ) );
	exit();
}

/**
 * Handle the main job submit form.
 */
function jr_handle_job_submit_form() {

	if ( ! isset($_POST['job_submit']) ) {
		return;
	}

	$actions = array( 'edit-job', 'new-job', 'relist-job' );
	if ( empty( $_POST['action'] ) || !in_array( $_POST['action'], $actions ) ) {
		return;
	}

	if ( ! current_user_can( 'can_submit_job' ) ) {
		return;
	}

	$job = jr_handle_update_job_listing();
	if ( ! $job ) {
		// there are errors, return to current page
		return;
	}

	if ( 'edit-job' == $_POST['action'] ) {

		// maybe update job status
		if ( _jr_edited_job_requires_moderation( $job ) ) {
			jr_update_post_status( $job->ID, 'pending' );

			// send notification email
			jr_edited_job_pending( $job->ID );
		}

		$url = add_query_arg( 'updated', '1', get_permalink( $job->ID ) );
		$url = esc_url_raw( $url );

		wp_redirect( $url );
		exit();
	}

	$args = array(
		'job_id' => $job->ID,
		'step' => jr_get_next_step()
	);

	if ( ! empty( $_POST['relist'] ) ) {
		$args['job_relist'] = $job->ID;
	}

	if ( ! empty( $_POST['order_id'] ) ) {

		if ( ! current_user_can( 'access_post', (int) $_POST['order_id'] ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		$args['order_id'] = (int) $_POST['order_id'];
	}

	$url = add_query_arg( $args, jr_get_listing_create_url() );
	$url = esc_url_raw( $url );

	// redirect to next step
	wp_redirect( $url );
	exit();

}

// creates/updates the job listing and all it's meta and terms
function jr_handle_update_job_listing() {
	global $jr_options;

	$job_cat = jr_get_listing_tax( 'job_term_cat', APP_TAX_CAT );
	$job_type = jr_get_listing_tax( 'job_term_type', APP_TAX_TYPE );
	$job_salary = jr_get_listing_tax( 'job_term_salary', APP_TAX_SALARY );

	$args = wp_array_slice_assoc( $_POST, array( 'ID', 'post_title', 'post_content', 'tax_input' ) );

	$args['post_content'] = jr_maybe_strip_tags( $args['post_content'], 'post_content' );
	// Strip shortcodes
	if ( ! current_user_can( 'edit_others_posts' ) && ! is_admin() ) {
		$args['post_content'] = strip_shortcodes( $args['post_content'] );
	}
	$args['post_type'] = APP_POST_TYPE;

	$args['comment_status'] = $jr_options->allow_job_comments ? 'open' : 'closed';

	$errors = apply_filters( 'jr_listing_validate_fields', jr_get_listing_error_obj() );
	if ( $errors->get_error_codes() ) {
		return false;
	}

	if ( ! empty( $_POST['ID'] ) ) {

		if ( ! current_user_can( 'edit_job', (int) $_POST['ID'] ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		$job_id = (int) $_POST['ID'];
		$job = get_post( $job_id );
	}

	if ( empty( $job ) ) {
		$action = 'insert';
	} elseif ( isset( $_POST['relist'] ) ) {

		if ( ! current_user_can( 'relist_job', $job_id ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		$action = 'relist';
	} else {
		$action = 'update';
	}

	// do_action hook
	jr_before_insert_job( $action );

	if ( empty($job) ) {
		$job_id = wp_insert_post( $args );
	} else {

		// temp mark this job as being relisted to secure relistings
		if ( 'expired' == $job->post_status ) {
			update_post_meta( $job_id, '_relisting', 1 );
			$args['post_status'] = 'draft';
		}

		$job_id = wp_update_post( $args );
	}

	### TERMS

	wp_set_object_terms( $job_id, (int) $job_type, APP_TAX_TYPE );
	wp_set_object_terms( $job_id, (int) $job_cat, APP_TAX_CAT );
	wp_set_object_terms( $job_id, (int) $job_salary, APP_TAX_SALARY );

	$tags = jr_get_listing_tags( $args['tax_input'][APP_TAX_TAG] );
	wp_set_object_terms( $job_id, $tags, APP_TAX_TAG );

	### META

	foreach ( jr_get_job_listing_fields() as $field => $meta_name ) {
		$field_value = apply_filters('jr_handle_listing_fields', _jr_get_initial_field_value( $field ), $field, $job_id );
		update_post_meta( $job_id, $meta_name, $field_value );
	}

	// store the user IP
	update_post_meta( $job_id, 'user_IP', appthemes_get_ip(), true );

	jr_set_coordinates( $job_id );

	### CUSTOM FIELDS

	jr_update_form_builder( $job_cat, $job_id, APP_TAX_CAT );

	jr_handle_image_upload( $job_id );

	jr_handle_files( $job_id, $job_cat );

	// do_action hook
	jr_after_insert_job( $job_id, $action );

	return apply_filters( 'jr_handle_update_listing', get_post( $job_id ) );
}


// set the job listing geo coordinates meta
function jr_set_coordinates( $post_id ) {

	$data = array();

	foreach ( jr_get_geo_fields() as $field => $meta_name ) {
		$data[ $field ] = _jr_get_initial_field_value( $field );
	}

	if ( empty( $data['jr_address'] ) ) {
		return;
	}

	if ( ! empty( $data['jr_geo_latitude'] ) && ! empty( $data['jr_geo_longitude'] ) ) {
		jr_update_post_geo_metadata( $post_id, $data, $data['jr_geo_latitude'], $data['jr_geo_longitude'] );
	}
}

/**
 * Update geo metadata for a given post.
 *
 * @since 1.8
 */
function jr_update_post_geo_metadata( $post_id, $data = array(), $latitude = '', $longitude = '' ) {

	// @todo: use with new Geolocation API
	//appthemes_set_coordinates( $post_id, $latitude, $longitude );

	$latitude = jr_clean_coordinate( $latitude );
	$longitude = jr_clean_coordinate( $longitude );

	// if we don't have address data, do a look-up
	if ( ! empty( $data['jr_geo_short_address'] ) && ! empty( $data['jr_geo_country'] ) && ! empty( $data['jr_geo_short_address'] ) && ! empty( $data['jr_geo_short_address_country'] ) ) {
		update_post_meta( $post_id, 'geo_address', $data['jr_geo_short_address'] );
		update_post_meta( $post_id, 'geo_country', $data['jr_geo_country'] );
		update_post_meta( $post_id, 'geo_short_address', $data['jr_geo_short_address'] );
		update_post_meta( $post_id, 'geo_short_address_country', $data['jr_geo_short_address_country'] );

		$jr_address = $data['jr_address'];
	} else {
		$address = jr_reverse_geocode( $latitude, $longitude );

		update_post_meta( $post_id, 'geo_address', $address['address'] );
		update_post_meta( $post_id, 'geo_country', $address['country'] );
		update_post_meta( $post_id, 'geo_short_address', $address['short_address'] );
		update_post_meta( $post_id, 'geo_short_address_country', $address['short_address_country'] );

		$jr_address = $address['address'];
	}

	update_post_meta( $post_id, '_jr_address', $jr_address );
	update_post_meta( $post_id, '_jr_geo_latitude', $latitude );
	update_post_meta( $post_id, '_jr_geo_longitude', $longitude );
}

// skip strips tags for fields where HTML is allowed
function jr_maybe_strip_tags( $field_value, $field, $job_id = 0 ) {
	global $jr_options;

	if ( ( 'apply' == $field || 'post_content' == $field ) && $jr_options->jr_html_allowed ) {
			return $field_value;
	}
	return strip_tags( $field_value );
}

// add special formatting to specific fields
function jr_format_contact_fields( $field_value, $field, $job_id ){

	if( 'website' == $field ) {
		$field_value = str_ireplace('http://', '', $field_value);
	}
	return $field_value;
}

// default values when post data does not exist
function _jr_get_initial_field_value( $field ) {
	return isset( $_POST[$field] ) ? stripslashes( $_POST[$field] ) : '';
}

// retrieve the available fields (meta) for a job listing - array( 'field_name' => 'meta_name' )
function jr_get_job_listing_fields() {
	$fields = array(
		'your_name' => '_Company',
		'website'   => '_CompanyURL',
		'apply'     => '_how_to_apply',
	);
	return apply_filters( 'jr_job_fields', $fields, $_POST );
}

// retrieve the available geolocation fields (meta) for a job listing - array( 'field_name' => 'meta_name' )
function jr_get_geo_fields() {
	$fields = array(
		'jr_address'                   => '_jr_address',
		'jr_geo_latitude'              => '_jr_geo_latitude',
		'jr_geo_longitude'             => '_jr_geo_longitude',
		'jr_geo_country'               => 'geo_country',
		'jr_geo_short_address'         => 'geo_short_address',
		'jr_geo_short_address_country' => 'geo_short_address_country',
	);
	return $fields;
}

// retrieve the tags for a job listing
function jr_get_listing_tags( $tags_string ) {
	$trim_strings = explode( ',', $tags_string );
	return array_map( 'trim', $trim_strings );
}

/**
 * Validate the job listing fields.
 *
 * @param object $errors
 *
 * @return object
 */
function _jr_validate_listing_fields( $errors ) {

	// validate required fields
	$fields = wp_array_slice_assoc( $_POST, array( 'post_title', 'post_content', 'tax_input' ) );
	$fields = apply_filters( 'jr_job_required_fields', $fields );
	foreach ( $fields as $key => $name ) {
		if ( empty( $_POST[ $key ] ) ) {
			$errors->add( 'submit_error', __( '<strong>ERROR</strong>: Please fill in all required fields.', APP_TD ) );
		}
	}

	return $errors;
}

// validate the job listing category
function _jr_validate_listing_category( $errors ){
	global $jr_options;

	if ( ! $jr_options->jr_submit_cat_required ) {
		return $errors;
	}

	$listing_cat = jr_get_listing_tax( 'job_term_cat', APP_TAX_CAT );
	if ( ! $listing_cat ) {
		$errors->add( 'wrong-cat', __( 'No category was submitted.', APP_TD ) );
	}

	return $errors;
}


// validates the listing data and returns the post if there are no errors. In case of errors, returns false
function jr_validate_update_listing( $listing ) {

	$errors = jr_get_listing_error_obj();
	if ( $errors->get_error_codes() ) {
		return false;
	}
	return $listing;
}

// update the custom form fields
function jr_update_form_builder( $cat, $post_id, $taxonomy ) {
	$fields = jr_get_custom_fields_for_cat( $cat, $taxonomy );

	$to_update = scbForms::validate_post_data( $fields );

	scbForms::update_meta( $fields, $to_update, $post_id );
}

// retrieve the job listing tags
function the_job_listing_tags_to_edit( $listing_id ) {
	$tags = get_the_terms( $listing_id, APP_TAX_TAG );

	if ( empty( $tags ) ) {
		return;
	}

	echo esc_attr( implode( ', ', wp_list_pluck( $tags, 'name' ) ) );
}

// retrieve the job required fields
function jr_job_required_fields() {

	// Check required fields
	$required = array(
		'job_title' 	=> __('Job title', APP_TD),
		'job_term_type' => __('Job type', APP_TD),
		'details' 		=> __('Job description', APP_TD),
	);

	return apply_filters( 'jr_job_required_fields', $required );
}

function _jr_needs_purchase( $job = '' ){
	return ( jr_charge_job_listings() );
}

function jr_get_default_job_to_edit() {

	$all_meta_fields = array_merge( jr_get_job_listing_fields(), jr_get_geo_fields() );

	if ( $job_id = get_query_var('job_id') ) {
		$job = get_post( $job_id );

		$job_cat_tax =  jr_get_the_job_tax( $job->ID, APP_TAX_CAT );

		if ( $job_cat_tax ) {
			$job->category = $job_cat_tax->term_id;
		}

		$job_type = jr_get_the_job_tax( $job->ID, APP_TAX_TYPE );

		if ( $job_type ) {
			$job->type = $job_type->term_id;
		} else {
			$job->type = '';
		}

		if ( $job->salary = jr_get_the_job_tax( $job->ID, APP_TAX_SALARY ) ) {
			$job->salary = jr_get_the_job_tax( $job->ID, APP_TAX_SALARY )->term_id;
		}

		foreach ( $all_meta_fields as $field => $meta_name ) {
			$job->$field = get_post_meta( $job->ID, $meta_name, true );
		}

	} else {

		require_once ABSPATH . '/wp-admin/includes/post.php';
		$job = get_default_post_to_edit( APP_POST_TYPE );

		$job->category = jr_get_listing_tax( 'job_term_cat', APP_TAX_CAT );
		$job->type = jr_get_listing_tax( 'job_term_type', APP_TAX_TYPE );
		$job->salary = jr_get_listing_tax( 'job_term_salary', APP_TAX_SALARY );

		foreach ( array( 'post_title', 'post_content' ) as $field ) {
			$job->$field = _jr_get_initial_field_value( $field );
		}

		foreach ( $all_meta_fields as $field => $meta_name ) {
			$job->$field = _jr_get_initial_field_value( $field );
		}
	}

	return $job;
}

function jr_get_listing_cat_id() {
	static $cat_id;

	if ( is_null( $cat_id ) ) {
		if ( isset( $_REQUEST['_'.APP_TAX_CAT] ) && $_REQUEST['_'.APP_TAX_CAT] != -1 ) {
			$listing_cat = get_term( $_REQUEST['_'.APP_TAX_CAT], APP_TAX_CAT );
			$cat_id = is_wp_error( $listing_cat ) ? false : $listing_cat->term_id;
		} else {
			$cat_id = false;
		}
	}

	return $cat_id;
}

function jr_job_details( $job_id = 0 ) {
	$job_id = $job_id ? $job_id : get_the_ID();

	$job_details = get_post( $job_id );
	$meta = get_post_custom( $job_id );
	$data = jr_reset_data( $meta );

	if ( ! $job_details ) {
		return;
	}

	$category = '';

	$cat_terms = get_the_job_terms( $job_details->ID, APP_TAX_CAT );
	if ( $cat_terms ) {
		$category = get_the_job_terms( $job_details->ID, APP_TAX_CAT )->term_id;
	}

	$salary = '';

	$salary_terms = get_the_job_terms( $job_details->ID, APP_TAX_SALARY );
	if ( $salary_terms ) {
		$salary = get_the_job_terms( $job_details->ID, APP_TAX_SALARY )->term_id;
	}

	$tags = '';

	$tags_terms = get_the_terms( $job_details->ID, APP_TAX_TAG );
	if ( $tags_terms ) {
		foreach ($tags_terms as $term) {
			$job_tags[] = $term->name;
		}
		$tags = implode(', ', $job_tags );
	}

	$details = array(
		'your_name'        => $data['_Company'],
		'website'          => $data['_CompanyURL'],
		'job_title'        => $job_details->post_title,
		'job_term_type'    => get_the_job_terms( $job_details->ID, APP_TAX_TYPE )->slug,
		'job_term_cat'     => $category,
		'job_term_salary'  => $salary,
		'jr_address'       =>  ( !empty($data['geo_address']) ? $data['geo_address'] : '' ),
		'jr_geo_latitude'  => ( !empty($data['_jr_geo_latitude']) ? $data['_jr_geo_latitude'] : '' ),
		'jr_geo_longitude' => ( !empty($data['_jr_geo_longitude']) ? $data['_jr_geo_longitude'] : '' ),
		'details'          => $job_details->post_content,
		'apply'            => $data['_how_to_apply'],
		'tags'             => $tags,
	);
	return apply_filters( 'jr_job_details', $details );
}

// get the last step
function _jr_steps_get_last( $steps = '' ) {
	if ( ! $steps  ) {
		$steps = jr_steps();
	}
	return max( array_keys( $steps ) );
}

// steps descriptions and templates
function _jr_job_submit_steps() {

	$steps = array(
		1 => array (
			'name'        => 'register',
			'title'       => __( 'Register', APP_TD ),
			'description' => __('Create account', APP_TD),
			'template'    => '',
		),
		2 => array (
			'name'        => 'submit_job',
			'title'       => __( 'Submit a Job 123', APP_TD ),
			'description' => __('Enter Job Details', APP_TD),
			'template'    => '/includes/forms/submit-job/submit-job-form.php',
		),
		3 => array (
			'name'        => 'preview_job',
			'description' => __('Preview', APP_TD),
			'title'       => __( 'Preview Job', APP_TD ),
			'template'    => '/includes/forms/preview-job/preview-job-form.php',
		),
	);

	return $steps;
}

// steps descriptions and templates
function jr_steps() {
	$steps = _jr_job_submit_steps();

	if ( jr_charge_job_listings() ) {
		$steps[] = _jr_select_job_plan_step();
		$description = __('Pay/Thank You', APP_TD);
	} else {
		$description = __('Confirm', APP_TD);
	}

	$steps[] = _jr_confirm_step( $description );

	return apply_filters( 'jr_job_submit_steps', $steps );
}

function jr_get_step_by_name( $name ) {
	foreach( jr_steps() as $key => $step ) {
		if ( $name == $step['name'] ) {
			return $key;
		}
	}
	return false;
}

function _jr_select_plans_steps() {
	$steps = array(
		1 => array (
			'name'        => 'select_plan',
			'title'       => __( 'Select Plan', APP_TD ),
			'description' => __('Select Plan', APP_TD),
		),
		2  => array (
			'name'        => 'select_gateway',
			'title'       => __('Pay/Thank You', APP_TD),
			'description' => __('Pay/Thank You', APP_TD),
		),
	);
	return $steps;
}

function _jr_select_job_plan_step() {
	$step = array (
		'name'        => 'select_plan',
		'title'       => __( 'Select Plan', APP_TD ),
		'description' => __('Select Plan', APP_TD),
		'template'    => '/includes/forms/select-plan/select-plan.php',
	);
	return $step;
}

function _jr_confirm_step( $description ) {

	$step = array (
		'name'        => 'confirm_job',
		'title'       => __( 'Submit a Job', APP_TD ),
		'description' => $description,
		'template'    => '/includes/forms/confirm-job/confirm-job-form.php',
	);
	return $step;
}

function _jr_curr_step( $start ) {
	if ( get_query_var('step') ) {
		return get_query_var('step');
	} else {
		return $start;
	}
}

function jr_get_next_step( $start = 2 ) {
	if ( ! is_user_logged_in() ) {
		$step = 1;
	} else {
		$step =  _jr_next_step( jr_get_listing_error_obj(), $start );
	}

	return $step;
}

// dinamically return the next step
function _jr_next_step( $errors, $start ) {

	$previous_step = _jr_curr_step( $start );

	$step = $previous_step;

	if ( ! empty($_POST) && ! $errors->get_error_codes() ) {

		if ( empty($_POST['goback']) ) {
			$step++;
		} else {
			$step = $start;
		}

	} elseif ( $errors->get_error_codes() ) {
		$step = _jr_curr_step( $start );
	}

	if ( $step > _jr_steps_get_last() ) {
		$step = $previous_step;
	}

	return apply_filters( 'jr_next_job_submit_step', $step, $previous_step );
}

/**
 * @since 1.8
 */
function jr_get_steps_trail( $steps, $step ) {

	$step_trail = array();

	foreach ( $steps as $i => $value ) {

		$classes = $classes_desc = array();

		if ( $step == $i ) {
			$classes[] = 'current';
		} elseif( ( $step - 1 ) == $i ) {
			$classes[] = 'previous';
		} elseif( $i < $step ) {
			$classes[] = 'done';
		}

		if ( $i == 1 ) {
			$classes_desc[] = 'first';
		} elseif( $i == count( $steps ) ) {
			$classes_desc[] = 'last';
		}

		$step_trail[ $i ] = array(
			'classes'      => implode( ' ', $classes ),
			'classes_desc' => implode( ' ', $classes_desc ),
			'description'  => $value['description'],
		);

	}

	return $step_trail;
}

/**
 * @since 1.8.1
 */
function _jr_maybe_delete_submit_meta( $new_status, $old_status, $post ) {

	if ( APP_POST_TYPE != $post->post_type || 'publish' != $new_status ) {
		return;
	}

	delete_post_meta( $post->ID, '_relisting' );
}



### Helper Functions

/**
 * @since 1.8
 */
function jr_process_filter_form( $args = array() ) {
	return JR_Jobs_Archive::jobs_filter_form( $args );
}
