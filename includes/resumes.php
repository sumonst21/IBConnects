<?php
/**
 * Resumes Related Functions
 *
 * @version 1.7
 * @author AppThemes
 * @package JobRoller
 * @copyright 2010 all rights reserved
 *
 */

add_action( 'wp_loaded', 'jr_handle_resume_plan_order', 11 );

add_action( 'appthemes_transaction_activated', '_jr_activate_resume_plan', 10 );

add_action( 'user_resume_subscription_started', 'jr_user_resume_subscription_start_meta', 10, 3 );
add_action( 'user_resume_subscription_ended', 'jr_user_resume_subscription_end_meta' );

add_action( 'jr_addon_resume_ended', 'jr_resume_addon_remove', 10, 2 );
add_action( 'jr_resume_header', 'jr_process_resume', 10, 1 );


/**
 * Handle resumes subscriptions
 *
 * @todo: move to 'views.php'
 */
function jr_handle_resume_plan_order() {

	if ( ! isset( $_POST['action'] ) || 'purchase-resume-plan' != $_POST['action'] || !empty($_POST['goback']) ) {
		return;
	}

	$errors = apply_filters( 'appthemes_validate_purchase_fields', jr_get_listing_error_obj() );
	if ( $errors->get_error_codes() ) {
		return false;
	}

	$plan_id = (int) $_POST['plan'];
	$plan = get_post( $plan_id );
	$plan_data = get_post_custom( $plan_id );

	$new_order = true;

	if ( empty( $_POST['order_id'] ) ) {
 		$order = _jr_create_order( $plan_id );
	} else {

		if ( ! current_user_can( 'access_post', (int) $_POST['order_id'] ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		$order = appthemes_get_order( (int) $_POST['order_id'] );
		$order->remove_item();

		$new_order = false;
	}

	$attach = get_post( $order->get_id() );

	do_action( 'appthemes_create_order', $order, $plan, $plan_data, $attach );

	// action hook executed only on each new order
	if ( $new_order ) {
		jr_after_insert_order( $order );
	}

	_jr_order_redirect_user( $order, $plan );
}

/**
 * Activate the resumes subscription and/or trial
 */
function _jr_activate_resume_plan( $order ){

	$order_data = _jr_get_order_job_info( $order );
	if ( ! $order_data ) {
		return;
	}

	extract( $order_data );

	if ( $plan->post_type != APPTHEMES_RESUMES_PLAN_PTYPE ) {
		return;
	}

	do_action( 'user_resume_subscription_started', $order->get_author(), $plan->ID, $plan_data );
}

/**
 * Check if resumes are enabled or not
 */
function jr_resumes_are_disabled() {
	global $jr_options;

	if ( ! $jr_options->jr_allow_job_seekers ) {
		return true;
	}
	return false;
}

/**
 * Check if resumes are visible or not
 */
function jr_resume_is_visible( $single = '' ) {
	global $jr_options;

	if ( ! $single ) {
		$visibility_option = $jr_options->jr_resume_listing_visibility;
	} else {
		$visibility_option = $jr_options->jr_resume_visibility;
	}

	/* Support keys so logged out users can view a resume if they are sent the link via email (apply form) */
	if ( is_single() ) :

		if ( ! empty( $_GET['key'] ) ) :
			global $post;
			$key = get_post_meta( $post->ID, '_view_key', true );
			if ( $key == $_GET['key']) :
				return true;
			endif;

		endif;

	endif;

	// if a subscriptions is required and the current user has a subscription or an active resume addon it will always have priority over the visibility settings
	// subscription check is skipped if the visibility setting is set to "Public"
	if ( jr_viewing_resumes_require_subscription() && 'public' != $visibility_option ) {

		// check for specific resumes access
		$resume_access = jr_user_resumes_access();
		if ( ( $single && empty($resume_access['access']['view']) ) || ( !$single && empty($resume_access['access']['browse']) ) ) {
			return false;
		} else {
			// user was given access
			return true;
		}

	}

	return jr_user_resume_visibility( $single );
}

/**
 * Check if current user can actually subscribe
 */
function jr_current_user_can_subscribe_for_resumes() {

	if ( ! is_user_logged_in() ) return false;

	if ( ! jr_viewing_resumes_require_subscription() ) return false;

	return jr_user_resume_visibility();
}

function jr_user_resume_visibility( $visibility_option = '' ) {
	global $jr_options;

	if ( ! $visibility_option ) {
		$visibility_option = $jr_options->jr_resume_listing_visibility;
	} else {
		$visibility_option = $jr_options->jr_resume_visibility;
	}

	switch ( $visibility_option ) {
		case "public" :
			return true;
		break;
		case "members" :
			if ( is_user_logged_in() ) :
				return true;
			endif;
		break;
		case "recruiters" :
		case "members_listers":
			if ( current_user_can('can_view_resumes') && current_user_can('can_submit_job') ) :
				return true;
			endif;
			// skip the break if checking for all listers members (can submit jobs)
			if ( 'members_listers' != $visibility_option ) {
				break;
			}
		case "listers" :
		case "members_listers":
			if ( ( current_user_can('can_submit_job') && !current_user_can('can_view_resumes') ) || current_user_can('manage_options') ) :
				return true;
			endif;
		break;
	}
	return false;
}

/**
 * Check if resumes require subscription
 */
function jr_viewing_resumes_require_subscription() {
	global $jr_options;
	return (bool) $jr_options->jr_resume_require_subscription;
}

/**
 * Check if resumes are disabled/visible and redirect.
 */
function jr_resume_page_auth() {
	if ( jr_resumes_are_disabled() ) {
		wp_redirect( home_url() );
		exit;
	}
}

/**
 * Checks for valid subscriptions (auto/manual) and ends expired manual subscriptions
 */
function jr_resume_valid_subscr( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	$valid = false;

	$active_subscr = get_user_meta( $user_id, '_valid_resume_subscription', true );
	if ( ! $active_subscr )
		return false;

	// Grab the stored subscription end date
	$end_date = get_user_meta( $user_id, '_valid_resume_subscription_end', true );
	if ( $end_date && $active_subscr ) :

		$days = ceil( ( $end_date-strtotime('NOW') ) / 86400 );
		//subscription ended
		if ( $days < 1 ):
			// end subscription
			do_action( 'user_resume_subscription_ended', $user_id );
		else:
			$valid = true;
		endif;

	endif;

	return apply_filters( 'jr_resume_valid_subscr', $valid, $user_id );
}

function jr_resume_addon_view_access( $valid, $user_id ) {
	if ( _jr_resume_addon_valid_access( $user_id, JR_ITEM_VIEW_RESUMES ) && is_single() ) {
		$valid = true;
	}
	return $valid;
}

function jr_resume_addon_browse_access( $valid, $user_id ) {
	if ( _jr_resume_addon_valid_access( $user_id, JR_ITEM_BROWSE_RESUMES ) && ! is_single() ) {
		$valid = true;
	}
	return $valid;
}

/**
 * Checks for valid temporary resumes access (auto/manual) and expires access if ended
 */
function _jr_resume_addon_valid_access( $user_id, $addon ) {

	$temp_access = get_user_meta( $user_id, $addon, true );
	if ( $temp_access ) :
		$start_date = get_user_meta( $user_id, $addon . '_start_date', true );
		$duration = get_user_meta( $user_id, $addon . '_duration', true );

		$end_date = strtotime( '+'.$duration.' DAYS', strtotime($start_date) );
		//subscription ended
		if ( $end_date < strtotime('NOW') ):
			// end subscription
			do_action( 'jr_addon_resume_ended', $user_id, $addon );
		else:
			return $end_date;
		endif;
	endif;
	return false;
}

function jr_resume_addon_remove( $user_id, $addon ){
	delete_user_meta( $user_id, $addon );
	delete_user_meta( $user_id, $addon .'_start_date' );
	delete_user_meta( $user_id, $addon .'_duration' );
}

function jr_add_resumes_access( $user_id, $addon, $duration ){
	update_user_meta( $user_id, $addon , true );

	$curr_duration = intval( get_user_meta( $user_id, $addon .'_duration', true ) );
	if ( $duration >= $curr_duration ) {
		update_user_meta( $user_id, $addon .'_start_date', current_time( 'mysql' ) );
		update_user_meta( $user_id, $addon .'_duration', $duration );
	}
}

/**
 * Update resume subscriptions user meta for ending subscriptions
 */
function jr_user_resume_subscription_end_meta( $user_id ) {
	delete_user_meta( $user_id, '_valid_resume_subscription' );
	delete_user_meta( $user_id, '_valid_resume_subscription_order' );
	delete_user_meta( $user_id, '_valid_resume_trial' );
}


/**
 * Update resume subscriptions user meta for new subscriptions
 */
function jr_user_resume_subscription_start_meta( $user_id, $plan_id, $plan_data ) {
	if ( !empty($plan_data[JR_FIELD_PREFIX.'trial'][0]) ) {
		update_user_meta( $user_id, '_valid_resume_trial', 1 );
	} else {
		delete_user_meta( $user_id, '_valid_resume_trial' );
	}
	update_user_meta( $user_id, '_valid_resume_subscription', $plan_id );
	update_user_meta( $user_id, '_valid_resume_subscription_start', strtotime("now") );
	update_user_meta( $user_id, '_valid_resume_subscription_end', jr_resume_calc_end_date( $plan_data[JR_FIELD_PREFIX.'duration'][0] ) );
}

/**
 * Calculate and return new resume subcription dates
 */
function jr_resume_calc_end_date( $length ) {
	$date = strtotime( '+'.$length.' days', current_time('timestamp') );
	return $date;

}

function jr_resume_valid_trial( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	return (bool) ( get_user_meta( $user_id, '_valid_resume_trial', true ) );
}

function jr_user_resumes_access( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	$valid_subscription = get_user_meta( $user_id, '_valid_resume_subscription', true );
	$access_end_date = get_user_meta( $user_id, '_valid_resume_subscription_end', true );
	$active_subscription = ( ( $valid_subscription || jr_resume_valid_trial( $user_id ) ) && $access_end_date );

	// if no valid subscriptions or trials look for temporary Resumes access
	if ( ! $active_subscription ) {

		$view_resumes = _jr_resume_addon_valid_access( $user_id, JR_ITEM_VIEW_RESUMES );
		$browse_resumes = _jr_resume_addon_valid_access( $user_id, JR_ITEM_BROWSE_RESUMES );
		$level = 'temporary';

	} else {
		$level = 'full';
		$view_resumes = $browse_resumes = (int)$access_end_date;
	}

	if ( ! $browse_resumes && ! $view_resumes )
		return;

	$resumes_access = array(
		'level' => $level,
		'access' => array(),
	);

	if ( $browse_resumes ) {
		$resumes_access['access']['browse'] = array (
			'description' => __('Browse Resumes',APP_TD),
			'end_date' => appthemes_display_date( $browse_resumes ),
		);
	}

	if ( $view_resumes ) {
		$resumes_access['access']['view'] = array (
			'description' => __('View Resumes',APP_TD),
			'end_date' => appthemes_display_date( $view_resumes ),
		);
	}

	return $resumes_access;
}

function jr_get_resume_subscribers( $show = 'active', $args = array() ) {
	$default_args = array(
		'meta_query' => array(
			array(
				'key' => '_valid_resume_subscription',
				'value' => 0,
				'compare' => '>',
			),
		),
	);
	$args = wp_parse_args( $args, $default_args );

	if ( 'inactive' == $show ) {

		$active_users = new WP_User_Query( $args );

		$active_users = wp_list_pluck( $active_users->get_results(), 'ID' );

		$args['exclude'] = $active_users;
		$args['meta_query'] = array();

	}
	return new WP_User_Query( $args );
}

// expire subscriptions on the pre-set date
function jr_check_expired_subscriptions() {

	$expired_subscr = new WP_User_Query( array(
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key'     => '_valid_resume_subscription',
				'value'   => 0,
				'compare' => '>',
			),
			array(
				'key'     => '_valid_resume_subscription_end',
				'value'   => current_time( 'timestamp' ),
				'compare' => '<'
			),
		),
	) );

	foreach ( $expired_subscr->results as $user ) {
		do_action( 'user_resume_subscription_ended', $user->ID );
	}

}


/**
 * Returns resumes per page.
 *
 * @return int
 */
function jr_get_resumes_per_page() {
	global $jr_options;

	return (int) $jr_options->jr_resumes_per_page ? $jr_options->jr_resumes_per_page : 10;
}


/**
 * Handles "Add/Remove Website" resume field.
 * @since 1.8.
 *
 * @param object $post
 *
 * @return void
 */
function jr_process_resume( $post ) {
	global $message;
	// todo: stop using message global.

	$resume = $post->ID;

	if ( get_the_author_meta( 'ID' ) != get_current_user_id() ) {
		return;
	}

	if ( isset( $_POST['save_website'] ) ) {

		$websites = get_post_meta( $resume, '_resume_websites', true );
		if ( ! is_array( $websites ) ) {
			$websites = array();
		}

		if ( empty( $_POST['website_name'] ) || empty( $_POST['website_url'] ) ) {
			$message = __( 'All website fields are required.', APP_TD );
			return;
		}

		$website_name = wp_kses_data( $_POST['website_name'] );
		$website_url = appthemes_clean( $_POST['website_url'] );

		if ( ! wp_http_validate_url( $website_url ) ) {
			$message = __( 'Website URL is invalid.', APP_TD );
			return;
		}

		if ( $website_name && $website_url ) {
			$websites[] = array( 'name' => $website_name, 'url' => $website_url );
			sort( $websites );
			update_post_meta( $resume, '_resume_websites', $websites );
		}

		$message = __( 'Website Added', APP_TD );
		return;
	}
	
	if ( isset( $_GET['delete_website'] ) && is_numeric( $_GET['delete_website'] ) ) {

		$site_index = $_GET['delete_website'];

		$websites = get_post_meta( $resume, '_resume_websites', true );
		if ( ! is_array( $websites ) ) {
			$websites = array();
		}

		$new_websites = array();

		$loop = 0;
		foreach ( $websites as $website ) {
			if ( $site_index != $loop ) {
				$new_websites[] = $website;
			}
			$loop++;
		}

		update_post_meta( $resume, '_resume_websites', $new_websites );

		$message = __( 'Website successfully deleted', APP_TD );
		return;
	}

}

// retrieve the available fields (meta) for a job listing - array( 'field_name' => 'meta_name' )
function jr_get_resume_listing_fields() {

	$fields = array(
		'desired_salary'   => '_desired_salary',
		'desired_position' => '_desired_position',
		'mobile'           => '_mobile',
		'tel'              => '_tel',
		'email_address'    => '_email_address',
		'education'        => '_education',
		'experience'       => '_experience',
		'skills'           => '_skills',
	);
	return apply_filters( 'jr_resume_fields', $fields, $_POST );
}

/**
 * Returns an object of default resume to edit.
 *
 * @returns object
 */
function jr_get_default_resume_to_edit() {

	$all_meta_fields = array_merge( jr_get_resume_listing_fields(), jr_get_geo_fields() );

	if ( get_query_var( 'edit' ) ) {
		$resume_id = (int) get_query_var( 'edit' );
	} elseif( ! empty( $_GET['edit'] ) ) {
		$resume_id = (int) $_GET['edit'];
	}

	if ( ! empty( $resume_id ) ) {
		$resume = get_post( $resume_id );

		$resume->resume_name = $resume->post_title;
		$resume->summary = $resume->post_content;

		$cat_tax_terms = wp_get_post_terms( $resume->ID, array(
				APP_TAX_RESUME_CATEGORY,
				APP_TAX_RESUME_GROUPS,
				APP_TAX_RESUME_JOB_TYPE,
				APP_TAX_RESUME_LANGUAGES,
				APP_TAX_RESUME_SPECIALITIES,
			)
		);

		$taxonomies = get_object_taxonomies( $resume );

		$terms_for = array();

		foreach( $taxonomies as $taxonomy ) {
			$terms = wp_get_post_terms( $resume->ID, $taxonomy );

			$terms_for[ $taxonomy ] = $terms;

			$terms = wp_list_pluck( $terms, 'term_id' );

			// resume cats and types onlly accept 1 option
			if ( in_array( $taxonomy, array( APP_TAX_RESUME_JOB_TYPE, APP_TAX_RESUME_CATEGORY ) ) ) {
				$terms = reset( $terms );
			}

			$resume->$taxonomy = $terms;
		}

		if ( ! empty( $terms_for[ APP_TAX_RESUME_SPECIALITIES ] ) ) {
			$resume->specialities = implode( ', ', wp_list_pluck( $terms_for[ APP_TAX_RESUME_SPECIALITIES ], 'name' ) );
		}

		if ( ! empty( $terms_for[ APP_TAX_RESUME_GROUPS ] ) ) {
			$resume->groups = implode( ', ', wp_list_pluck( $terms_for[ APP_TAX_RESUME_SPECIALITIES ], 'name' ) );
		}

		if ( ! empty( $terms_for[ APP_TAX_RESUME_LANGUAGES ] ) ) {
			$resume->languages = implode( ', ', wp_list_pluck( $terms_for[ APP_TAX_RESUME_LANGUAGES ], 'name' ) );
		}

		foreach ( $all_meta_fields as $field => $meta_name ) {
			$resume->$field = get_post_meta( $resume->ID, $meta_name, true );
		}

	} else {

		require_once( ABSPATH . '/wp-admin/includes/post.php' );
		$resume = get_default_post_to_edit( APP_POST_TYPE_RESUME );

		$resume->resume_category  = jr_get_listing_tax( 'resume_cat', APP_TAX_RESUME_CATEGORY );
		$resume->resume_job_types = jr_get_listing_tax( 'resume_job_type', APP_TAX_RESUME_JOB_TYPE );
		$resume->specialities     = jr_get_listing_tax( 'specialities', APP_TAX_RESUME_SPECIALITIES );
		$resume->groups           = jr_get_listing_tax( 'groups', APP_TAX_RESUME_GROUPS );
		$resume->languages        = jr_get_listing_tax( 'languages', APP_TAX_RESUME_LANGUAGES );

		foreach ( array( 'resume_name', 'summary' ) as $field ) {
			$resume->$field = _jr_get_initial_field_value( $field );
		}

		foreach ( $all_meta_fields as $field => $meta_name ) {
			$resume->$field = _jr_get_initial_field_value( $field );
		}
	}

	return $resume;
}

/**
 * @since 1.8
 */
function jr_get_recurring_subscr_duration( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	if ( !jr_resume_valid_subscr() || ! appthemes_recurring_available() ) {
		return false;
	}

	$resume_plan_id = (int) get_user_meta( $user_id, '_valid_resume_subscription', true );
	$resume_plan_meta = get_post_meta( $resume_plan_id );

	if ( ! empty( $resume_plan_meta[JR_FIELD_PREFIX.'recurring'][0] ) ) {
		return $resume_plan_meta[JR_FIELD_PREFIX.'duration'][0];
	}
	return false;
}