<?php
/**
 * Plan purchase related function
 *
 * @version 1.7
 * @author AppThemes
 * @package JobRoller
 * @copyright 2010 all rights reserved
 *
 */

add_action( 'wp_loaded', 'jr_handle_new_order', 11 );

add_action( 'appthemes_validate_purchase_fields', 'jr_check_plan' );

add_action( 'appthemes_create_order', 'jr_handle_plan', 9, 4 );
add_action( 'appthemes_create_order', 'jr_handle_plan_addons', 10, 4 );
add_action( 'appthemes_create_order', 'jr_handle_recurring_plan', 11, 2 );
add_action( 'appthemes_create_order', 'jr_set_order_description', 12, 2 );

add_action( 'jr_plan_additional_options', 'jr_plan_featured_addons_output' );
add_action( 'jr_plan_additional_options', 'jr_plan_resumes_addons_output' );

add_filter( 'jr_pricing_addons', 'jr_exclude_categories_disabled', 10, 2 );

add_filter( 'jr_resumes_addons_output', 'jr_resume_access_permissions' );

 /**
 * Handle new plan orders
 *
 * @return void
 */
function jr_handle_new_order() {
	global $jr_options;

	if ( ! isset( $_POST['action'] ) || 'purchase-job-plan' != $_POST['action'] || !empty( $_POST['goback'] ) ) {
		return;
	}

	if ( empty( $_POST['ID'] ) ) {
		return false;
	}

	if ( ! current_user_can( 'edit_job', (int) $_POST['ID'] ) ) {
		wp_die( __( 'Cheatin&#8217; uh?' ) );
	}

	$errors = apply_filters( 'appthemes_validate_purchase_fields', jr_get_listing_error_obj() );
	if ( $errors->get_error_codes() ) {
		return false;
	}

	### parse the plan

	// single plans - contains only the 'plan_id' - users can only purchase new plans
	// user plans (packs) - contains the 'plan_id' and also the 'pack_umeta_id' - users can purchase new plans and/or use existing active plans

	$job = get_post( (int) $_POST['ID'] );

	// check if the job already has orders connected to avoid creating additional orders
	if ( ! empty( $_POST['order_id'] ) ) {

		if ( ! current_user_can( 'access_post', (int) $_POST['order_id'] ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		$order_id = (int) $_POST['order_id'];
		$order = appthemes_get_order( $order_id );

	} elseif ( empty( $_POST['relist'] ) ) {
		$order = appthemes_get_order_connected_to( $job->ID );
		if ( $order ) {
			// reset order
			$order->pending();
			$order->clear_gateway();

			$order_id = $order->get_id();
		}
	}

	$posted_plan = explode( '-', $_POST['plan'] );
	$plan_id = (int) $posted_plan[0];

	$plan = get_post( $plan_id );
	$plan_data = get_post_custom( $plan_id );

	$new_order = true;

	if ( empty($order) ) {
		$order = _jr_create_order( $plan_id );
	} else {
		$order->remove_item();

		$new_order = false;
	}

	### user packs only

	// check if user selected a user pack (owned plan)
	if ( 'pack' == $jr_options->plan_type && sizeof($posted_plan) > 1 ) {

		// get the sanitized user pack plan ID
		$pack_umeta_id = _jr_sanitized_plan( $_POST['plan'] );

		$user_pack = _jr_user_pack_meta( $order->get_author(), $pack_umeta_id );
		if ( !empty( $user_pack ) ) {
			$plan_data['user_pack'] = array(
				'pack_umeta_id' => $pack_umeta_id,
				'pack_data'     => $user_pack,
			);
		}
	}

	###

	do_action( 'appthemes_create_order', $order, $plan, $plan_data, $job );

	// action hook executed only on each new order
	if ( $new_order ) {
		jr_after_insert_order( $order );
	}

	_jr_order_redirect_user( $order, $plan, $job );
}

function _jr_create_order( $plan_id ) {
	jr_before_insert_order( $plan_id );

	$order = appthemes_new_order();
	$order->set_currency( APP_Currencies::get_current_currency('code') );

	return $order;
}

/**
 * Calculates the plan price and adds it to the order
 * @param $order The order object
 * @param $plan The plan array
 * @param $plan_data The plan data array
 * @param $attach The attached post - a Job or Order object
 *
 * @return void
 */
function jr_handle_plan( $order, $plan, $plan_data, $attach ) {

	$price = jr_plan_price( jr_reset_data( $plan_data ) );

	$order->add_item( $plan->post_name, $price, $attach->ID );
}

/**
 * Calculates each addon price and adds them to the order
 * @param $order The order object
 * @param $plan The plan array
 * @param $plan_data The plan data array
 * @param $attach The attached post - a Job or Order object
 *
 * @return void
 */
function jr_handle_plan_addons( $order, $plan, $plan_data, $attach ){

	foreach( jr_get_addons() as $addon ){

		$price = jr_addon_price( $addon );

		if ( $price < 0 )
			continue;

		$order->add_item( $addon, $price, $attach->ID );
	}

}

/**
 * Redirect user to the Order page
 * @param $order The order object
 * @param $plan The plan array
 * @param $attach The attached post - a Job or Order object
 *
 * @return void
 */
function _jr_order_redirect_user( $order, $plan, $attach = '' ) {

	$args = array (
		'order' => $order->get_id(),
	);

	if ( $attach && APP_POST_TYPE == $attach->post_type ) {
		$args['job_id'] = $attach->ID;			// order with job attached
	} else {
		$args['plan_type'] = $plan->post_type;	// order with no jobs attached
	}

	// is the job being relisted?
	if ( !empty($_POST['relist']) ) {
		$args['job_relist'] = $attach->ID;
	}

	// move form to next step
	if ( !empty($_POST['step']) ) {
		$args['step'] = intval($_POST['step'])+1;
	}

	// set the previous page URl if the user decides to chooses a different plan while selecting the gateway
	$args['referer'] = urlencode($_SERVER['REDIRECT_URL']);

	$redirect_to = add_query_arg(
		$args,
		$order->get_return_url()
	);

	$redirect_to = esc_url_raw( $redirect_to );

	wp_redirect( $redirect_to );
	exit();
}

/**
 * Check for invalid plans
 * @param $errors The errors object
 *
 * @return errors
 */
function jr_check_plan( $errors ){

	if ( empty( $_POST['plan'] ) ) {
		$errors->add( 'no-plan', __( 'You must select a Plan to continue.', APP_TD ) );
		return $errors;
	}

	$plan_id = (int) $_POST['plan'];
	$plan = get_post( $plan_id );
	if ( ! $plan ) {

		$user_plan_id = _jr_sanitized_plan( $_POST['plan'] );
		$user_plan = _jr_user_pack_meta( get_current_user_id(), $user_plan_id );

		if ( ! $user_plan ) {
			$errors->add( 'invalid-plan', __( 'Invalid Plan.', APP_TD ) );
			return $errors;
		}
	}

	return $errors;
}


/**
 * Handle recurring plans by setting the recurring period
 * @param $order The order object
 * @param $plan The plan array
 *
 * @return void
 */
function jr_handle_recurring_plan( $order, $plan ) {

	if ( ! _jr_is_recurring_available() ) {
		return;
	}

	$plan_data = get_post_custom( $plan->ID );
	if ( !empty($plan_data[JR_FIELD_PREFIX.'recurring'][0]) && empty($plan_data[JR_FIELD_PREFIX.'trial'][0]) && appthemes_recurring_available( $order->get_gateway() ) ) {
		$recurring_period = $plan_data[JR_FIELD_PREFIX.'duration'][0];
		$order->set_recurring_period( $recurring_period );
	}
}

/**
 * Sets the Order description that will later be displayed on the payment gateway page
 * @param $order The order object
 * @param $plan The plan array
 *
 * @return void
 */
function jr_set_order_description( $order, $plan ) {
	$order_summary = '';
	$job_id = jr_get_order_job_id( $order );
	if ( $job_id ) {
		$order_summary .= get_the_title( $job_id ) . ' :: ';
	}
	$order_summary .= jr_get_the_order_summary( $order );
	$order->set_description( $order_summary );
}

/**
 * Calculate and retrieve a posted addon price
 * @param $addon The addon to which calculate the price
 *
 * filters: jr_addon_price - allows changing the final addon price
 *
 * @return int The addon price
 */
function jr_addon_price( $addon ) {

	// sanitize
	$plan_id = _jr_sanitized_plan( $_POST['plan'] );

	$price = -1;

	// on optional FREE offers make sure we don't charge the addon price it it's added to the plan
	if ( ! empty( $_POST['free-'.$addon.'_'.$plan_id] ) ) {
		$price = 0;
	} elseif ( ! empty( $_POST[$addon.'_'.$plan_id] ) ) {
		$price = APP_Item_Registry::get_meta( $addon, 'price' );
	}
	return apply_filters( 'jr_addon_price', $price, $addon );
}

/**
 * Calculate and retrieve the current plan price
 * @param $plan_data The plan data array
 *
 * filters: jr_plan_price - allows changing the final plan price
 *
 * @return int The plan price
 */
function jr_plan_price( $plan_data ) {

	$price = $plan_data[JR_FIELD_PREFIX.'price'];

	if ( ( get_query_var('job_relist') || isset($_POST['relist']) ) && isset($plan_data[JR_FIELD_PREFIX.'relist_price']) ) {
		$price = intval($plan_data[JR_FIELD_PREFIX.'relist_price']);
	}
	return apply_filters( 'jr_plan_price', $price, $plan_data );
}

/**
 * Get a job addon expiration date
 * @param $addon The addon name
 * @param $job_id The job ID
 *
 * @return string The addon expiration date
 */
function _jr_job_addon_expire_date( $addon, $job_id ) {

	$enabled = get_post_meta( $job_id, $addon, true);
	if ( ! $enabled )
		return;

	$start_date = get_post_meta( $job_id, $addon . '_start_date', true);
	$duration = get_post_meta( $job_id, $addon.'_duration', true );

	if( !$start_date || !$duration ){
		return 'never';
	}

	return jr_get_expiration_date( $start_date, $duration );
}

/**
 * Get a user addon expiration date
 * @param $user_id The user ID
 * @param $addon The addon name
 *
 * @return string The addon expiration date
 */
function _jr_user_addon_expire_date( $user_id, $addon ) {

	$enabled = get_user_meta( $user_id, $addon, true);
	if ( ! $enabled )
		return;

	$start_date = get_user_meta( $user_id, $addon . '_start_date', true);
	$duration = get_user_meta( $user_id, $addon.'_duration', true );

	if( !$start_date || !$duration ){
		return 'never';
	}
	return jr_get_expiration_date( $start_date, $duration );
}

/**
 * Retrieve a job or user addon expiration date
 * @param $addon The addon name
 * @param $job_id (optional) The job ID
 * @param $user_id (optional) The user ID
 *
 * @return string The addon expiration date
 */
function jr_get_addon_expire_date( $addon, $job_id = 0, $user_id = 0 ) {

	$user_id = !empty( $user_id ) ? $user_id : get_current_user_id();

	$expire_date = '';

	$addons = jr_get_addons( 'job' );
	if ( in_array( $addon, $addons ) && $job_id ) {
		$expire_date = _jr_job_addon_expire_date( $job_id, $addon );
	} else {
		$addons = jr_get_addons( 'user' );
		if ( in_array( $addon, $addons ) )
			$expire_date =_jr_user_addon_expire_date( $user_id, $addon );
	}
	return $expire_date;
}

/**
 * Check if a job or user addon is already active
 * @param $addon The addon name
 * @param $job_id (optional) The job ID
 * @param $user_id (optional) The user ID
 *
 * @return bool TRUE if the addon is active, FALSE otherwise
 */
function _jr_addon_already_active( $addon, $job_id = 0, $user_id = 0 ) {

	$user_id = !empty( $user_id ) ? $user_id : get_current_user_id();

	$addons = jr_get_addons( 'job' );
	if ( $job_id && in_array( $addon, $addons ) ) {
		$meta = get_post_meta( $job_id, $addon, true );
	} else {
		$addons = jr_get_addons( 'user' );
		if ( in_array( $addon, $addons ) )
			$meta = get_user_meta( $user_id, $addon, true );
	}

	if ( ! empty($meta) )
		return true;
	else
		return false;
}

/**
 * Check if a specifc addon is valid for display
 * @param $addon The addon name
 * @param $job_id (optional) The job ID
 *
 * @return bool TRUE if the addon is valid for display, FALSE otherwise
 */
function _jr_addon_valid( $addon, $job_id = 0 ) {

	$valid = true;

	if ( JR_ITEM_FEATURED_CAT == $addon && ! jr_get_the_job_tax( $job_id, APP_TAX_CAT ) )
		$valid = false;

	// don't display resume addons if the user has a valid resume subscription
	if ( in_array( $addon, _jr_resumes_addons() ) && jr_resume_valid_subscr() )
		$valid = false;

	return apply_filters( 'jr_addon_valid', $valid, $addon, $job_id );
}

/**
 * Checks if an addon is disabled
 * @param $addon The addon name
 *
 * @return bool The addon status
 */
function _jr_addon_disabled( $addon ){
	global $jr_options;
	return empty( $jr_options->addons[ $addon ]['enabled'] );
}

/**
 * Checks if there are addons available for a plan
 * @param $plan The plan array
 * @param (optional) $addons The addon list to check. Defaults to all addons (Job and User addons)
 *
 * filters: jr_no_addons_available - allows changing the criteria used to look for available adddons
 *
 * @return bool
 */
 function _jr_no_addons_available( $plan, $addons = '' ) {

	$addons = $addons ? $addons: jr_get_addons();

	$active_addons = array();
	$plans_count = $empty_count = $disabled_count = 0;
	foreach ( $addons as $addon ) {
		if ( empty($plan[$addon][0])) {
			$empty_count++;
		}
		if ( _jr_addon_disabled( $addon ) || ! _jr_addon_valid( $addon ) ) {
			$disabled_count++;
		}
		if ( ! empty($plan[$addon][0]) || ! _jr_addon_disabled( $addon ) ) {
			$active_addons[] = $addon;
		}
		$plans_count++;
	}

	if ( $disabled_count == $plans_count  ) {
		$available = false;
	} elseif ( $empty_count == $plans_count ) {
		if( $disabled_count == $plans_count ) {
			$available = false;
		} else {
			$available = true;
		}
	} else {
		$available = true;
	}

	return apply_filters( 'jr_no_addons_available', $available, $plan );
}

/**
 * Return errors
 * @return void
 */
function jr_get_listing_error_obj() {
	static $errors;

	if ( ! $errors ) {
		$errors = new WP_Error();
	}
	return $errors;
}

/**
 * Retrieve all featured addons - featured addons are assigned to jobs
 *
 * filters: jr_featured_addons
 *
 * @return array List of featured addons
 */
function _jr_featured_addons() {
	$addons = array( JR_ITEM_FEATURED_LISTINGS, JR_ITEM_FEATURED_CAT );

	return apply_filters( 'jr_featured_addons', $addons );
}

/**
 * Retrieve all resume addons - resume addons are assigned to users
 *
 * filters: jr_resumes_addons
 *
 * @return array List of resumes addons
 */
function _jr_resumes_addons() {
	$addons = array( JR_ITEM_BROWSE_RESUMES, JR_ITEM_VIEW_RESUMES );

	return apply_filters('jr_resumes_addons', $addons );
}


/**
 * Filters the resumes addons by checking if resumes need to be subscribed to, and if the current user can view/browse resumes
 * @param $addons The resumes list of addons to be filtered
 *
 * @return array Empty list if the validation fails or the passed resumes list
 */
function jr_resume_access_permissions( $addons ) {
	global $jr_options;

	if ( ! jr_current_user_can_subscribe_for_resumes() ) {
		return array();
	}

	$browse_visibility = $jr_options->jr_resume_listing_visibility;
	$single_visibility = $jr_options->jr_resume_visibility;

	$resumes_addons = array();
	foreach ( $addons as $addon ) {
		if ( ( JR_ITEM_VIEW_RESUMES == $addon && ! jr_user_resume_visibility( $single_visibility ) ) || ( JR_ITEM_BROWSE_RESUMES == $addon && ! jr_user_resume_visibility( $browse_visibility ) ) ) {
			continue;
		}
		$resumes_addons[] = $addon;

	}
	return $resumes_addons;
}

/**
 * Retrieve all existing addons or filtered by type and/or sub-type: job :: featured|other; user :: resumes|other
 * Job addons are stored as post meta
 * User addons are stored as user meta
 * @param $type (optional) The addon type to retrieve: job|user
 * @param $sub_type (optional) The addon sub-type to retrieve: featured|resumes
 *
 * filters: jr_addons - allows changing the available addon types
 *
 * @return array List with all addons
 */
function jr_get_addons( $type = '', $sub_type = '' ) {

	$addons_by_type = array();

	$addons = array(
		'job' => array( 'featured' => _jr_featured_addons() ),
		'user' => array( 'resumes' => _jr_resumes_addons() ),
	);
	$addons = apply_filters( 'jr_addons', $addons );

	foreach ( $addons as $key => $addon ) {
		if ( ! $type || $type == $key ) {
			if ( ! $sub_type ) {
				$addon_val = array_values( $addon );
				$addons_by_type = array_merge( $addons_by_type, $addon_val[0] );
			} elseif ( isset($addon[$sub_type]) ) {
				$sub_type_val = array_values( $addon[$sub_type] );
				$addons_by_type[] = array_merge( $addons_by_type, $sub_type_val[0] );
			}
		}
	}
	return $addons_by_type;
}

/**
 * Skip 'featured-cat' addon if the job does not have a category (categories are optional)
 * @param $addons The addon array list to search
 * @param $job (optional) A job object
 *
 * @return array List of filtered addons
 */
function jr_exclude_categories_disabled( $addons, $job = '' ) {
	if ( $job ) {
		$terms = wp_get_post_terms( $job->ID, APP_TAX_CAT );
		if( sizeof($terms) == 0 ) {
			$addons = array_diff( $addons, array( JR_ITEM_FEATURED_CAT ) );
		}
	}
	return $addons;
}

/**
 * Display the featured addons output
 * @param $args Mixed arguments: $plan_id, (array) $plan_data, (object) $job, (optional) (array) $pack
 *
 * @return void Outputs the addon HTML
 */
function jr_plan_featured_addons_output( $args ) {
	extract( $args );

	// don't display featured addons if job is not present - mainly when purchasing plans with no jobs attached
	if ( empty($job) )
		return;

	$featured_addons_output = apply_filters( 'jr_featured_addons_output', _jr_featured_addons(), $args );
	echo jr_addons_output( _jr_featured_addons(), $args );
}

/**
 * Display the resumes addons output
 * @param $args Mixed arguments: $plan_id, (array) $plan_data, (object) $job, (optional) (array) $pack
 *
 * @return void Outputs the addon HTML
 */
function jr_plan_resumes_addons_output( $args ) {
	$resumes_addons_output = apply_filters( 'jr_resumes_addons_output', _jr_resumes_addons(), $args );
	echo jr_addons_output( $resumes_addons_output, $args );
}

/**
 * Enables customers to extend their addons if set to return TRUE. If set to return FALSE, addons can only be extended after they expire (when submitting a job from the user pack).
 * @param $addon (optional) The specific addon that should be extended (it defaults to all addons if empty)
 *
 * @return bool Return TRUE or FALSE. Default is FALSE.
 */
function _jr_extend_addon_date( $addon = '' ) {
	// TODO: apply filter based on admin option 'Extend Addon' (to be created)
	return apply_filters( 'jr_extend_addon_date', false, $addon );
}

/**
 * Return the HTML ouput for an addon list
 * @param $addons The array list of addons
 * @param $args Mixed arguments: $plan_id, (array) $plan_data, (object) $job, (optional) (array) $pack
 *
 * filters: jr_addons_output - allows hooking into the addons HTML output and change it
 *
 * @return string HTML string with the addons output
 */
function jr_addons_output( $addons, $args ) {
	$html = '';

	extract( $args );

	$job_id = ! empty($job) ? $job->ID : 0;

	foreach ( $addons as $addon ) {
		$option_html = '';

		if ( ! _jr_addon_valid( $addon, $job_id ) )
			continue;

		if ( ! empty( $plan_data[$addon] ) && ( ! _jr_addon_already_active( $addon, $job_id ) || _jr_extend_addon_date() ) ) {

			$option_html = _jr_addon_option( $addon, true,$plan_id, $echo = false, 'free-' );
			if ( $plan_data[$addon.'_duration'] != 0 ) {
				$option_html .= sprintf( _n( '%s is included in this plan for %d day.', '%s is included in this plan for %d days.', $plan_data[$addon.'_duration'], APP_TD ), APP_Item_Registry::get_title( $addon ), $plan_data[$addon.'_duration'] );
			} else {
				$option_html .= sprintf( __( '%s is included in this plan for Unlimited days.', APP_TD ), APP_Item_Registry::get_title( $addon ) );
			}

		} else {

			// display the active addon expiration date but don't let the customer extend the date (addon data is not posted)
			$option_html .= _jr_show_addon_options( $addon, $plan_id, $job_id, false );

		}

		// if the addon is active, display the expiration info and let the addon date be extended (addon data is posted)
		if ( _jr_addon_already_active( $addon, $job_id ) && _jr_extend_addon_date() ) {

			$option_html .= _jr_show_active_addon_expire_info( $addon, $plan_id, $job_id, false );
		}

		if ( $option_html ) {
			$div_html = html('div', array( 'class' => 'featured_option' ), $option_html );
			$html .= html('label', array( 'class' => 'featured_option' ), $div_html );
		}
	}
	return apply_filters( 'jr_addons_output', $html, $addons, $args );
}

/**
 * Displays active addon expire information
 * @param $addon The addon name
 * @param $plan_id the Plan ID for new plans and the user meta ID for user plans (user packs)
 * @param $job_id (optional) The job ID
 * @param $echo (optional) TRUE output the options | FALSE returns the options
 *
 * @return void|string If echo is set to TRUE output the options | FALSE returns the options
 */
function _jr_show_active_addon_expire_info( $addon, $plan_id, $job_id = 0,  $echo = true ){
	global $jr_options;

	$output = '';

	$addon_title = APP_Item_Registry::get_title( $addon );
	$addon_price = appthemes_get_price( APP_Item_Registry::get_meta( $addon , 'price' ) );
	$addon_duration = $jr_options->addons[$addon]['duration'];

	if ( _jr_addon_already_active( $addon, $job_id ) ) {
		$expiration_date = jr_get_addon_expire_date( $addon, $job_id );
	}

	// If already an active addon, output disabled checkbox with expiration date
	if ( !empty($expiration_date) ) {

		if( 'never' == $expiration_date ) {
			$output = ' (' . __( 'currently active for Unlimited days', APP_TD ) . ')';
		} else {
			$output = ' (' . sprintf( __( 'currently active until %s', APP_TD ), html( 'strong', $expiration_date ) ) . ')';
		}

	}
	if ( $echo ) echo $output;

	return $output;
}


/**
 * Display addon options
 * @param $addon The addon name
 * @param $plan_id the Plan ID for new plans and the user meta ID for user plans (user packs)
 * @param $job_id (optional) The job ID
 * @param $echo (optional) TRUE output the options | FALSE returns the options
 *
 * @return void|string If echo is set to TRUE output the options | FALSE returns the options
 */
function _jr_show_addon_options( $addon, $plan_id, $job_id = 0,  $echo = true ){
	global $jr_options;

	$addon_title = APP_Item_Registry::get_title( $addon );
	$addon_price = appthemes_get_price( APP_Item_Registry::get_meta( $addon , 'price' ) );
	$addon_duration = $jr_options->addons[$addon]['duration'];

	if ( _jr_addon_already_active( $addon, $job_id ) ) {
		$expiration_date = jr_get_addon_expire_date( $addon, $job_id );
	}

	// If already an active addon, output disabled checkbox with expiration date
	if ( !empty($expiration_date) ) {
		$output =_jr_addon_option( $addon, true, $plan_id, $echo );

		if ( ! _jr_extend_addon_date( $addon ) ) {

			if( 'never' == $expiration_date ) {
				$output .= sprintf( __( '%s :: Already active for Unlimited days', APP_TD ), $addon_title);
			} else {
				$output .= sprintf( __( '%s :: Already active until %s', APP_TD ), $addon_title, $expiration_date );
			}
			if ( $echo ) echo $output;

			return $output;

		}
	}

	// If the addon is disabled, don't bother
	if( _jr_addon_disabled( $addon ) ){
		return;
	}

	$output = _jr_addon_option( $addon, $disable_option = false, $plan_id, $echo_option = false );
	if( $addon_duration == 0 ){
		$string = __( ' %s for Unlimited days for only %s more.', APP_TD );
		$output .= sprintf( $string, $addon_title, $addon_price );
	}else{
		$string = __( ' %s for %d days for only %s more.', APP_TD );
		$output .= sprintf( $string, $addon_title, $addon_duration, $addon_price );
	}
	if ( $echo ) echo $output;
	return $output;
}

/**
 * Display the input HTML
 * @param $addon The addon name
 * @param $enabled Bool to enable or disable the input
 * @param $plan_id The plan ID
 * @param $echo (optional) TRUE output the options | FALSE returns the options
 * @param $field_prefix (optional) A field prefix to be used on the input name - prefixed inputs are hidden and posted as normal data
 *
 * @return void|string If echo is set to TRUE output the options | FALSE returns the options
 */
function _jr_addon_option( $addon, $disabled, $plan_id, $echo = true, $field_prefix = '' ){

	$html = html( 'input', array(
		'name' => $field_prefix.$addon.'_'.$plan_id,
		'type' => 'checkbox',
		'disabled' => $disabled,
		'checked' => $disabled
	) );

	if ( $field_prefix && $disabled ) {
		// duplicate the field as hidden to enable post data
		$html .= html( 'input', array(
			'name' => $field_prefix.$addon.'_'.$plan_id,
			'type' => 'hidden',
			'value' => '1'
		) );
	}

	if ( $echo ) echo $html;
	return $html;
}

/**
 * Return all payable jobs for a specific user (status: undecided)
 * @param $user_id The user ID
 *
 * @return array List of undecided jobs with the 'order_id' and 'status' and the job ID as the array key
 */
function _jr_pending_payment_jobs_for_user( $user_id ) {

	if ( ! jr_charge_job_listings() ) {
		return false;
	}

	$undecided_jobs = array();

	### get all 'undecided' job orders ( gateway not selected )

	$args = array(
		'connected_type'  => APPTHEMES_ORDER_CONNECTION,
		'connected_query' => array( 'post_status' => APPTHEMES_ORDER_PENDING ),
		'post_type'       => APP_POST_TYPE,
		'post_status'     => 'any',
		'nopaging'        => true,
		'orderby'         => 'ID',
		'connected'       => 'any',
		'author'          => $user_id,
	);
	$draft_order_jobs = new WP_Query( $args );

	foreach ( $draft_order_jobs->posts as $draft_order_job ) {
		$order = appthemes_get_order( $draft_order_job->p2p_from );
		$gateway = $order->get_gateway();
		$undecided_jobs[$draft_order_job->ID] = array (
			'status'   => ( empty($gateway) ? 'undecided' : 'pending' ),
			'order_id' => $order->get_id(),
		);
	}

	return $undecided_jobs;
}

/**
 * Check if jobs are charged
 * @return bool
 */
function jr_charge_job_listings() {
	global $jr_options;
	return (bool) $jr_options->jr_jobs_charge;
}

/**
* Load the correct template based on the queried Order and it's plan type
 *
 * @todo: move to View
 *
* @param array $params List of additional vars to be passed to the template
*
* @return void
*/
function jr_load_order_template( $params ) {

	$order_id = get_queried_object()->ID;
	$order = appthemes_get_order( $order_id );

	$plan = jr_plan_for_order( $order );
	$plan_data = get_post_custom( $plan->ID );

	if ( $job_id = get_query_var('job_id') ) {
		$plan_type = 'job-plan';
	} elseif ( get_query_var('plan_type') ) {
		$plan_type = get_query_var('plan_type');
	} else {
		$job_id = jr_get_order_job_id( $order );
		if ( $job_id ) {
			$plan_type = 'job-plan';
		} else {
			$plan_type = $plan->post_type;
		}
	}

	switch( $plan_type ) {
		case APPTHEMES_RESUMES_PLAN_PTYPE:
			$params['redirect_to'] = jr_get_final_redirect_to_url( $plan_type );
			break;
		case APPTHEMES_PRICE_PLAN_PTYPE:
			$params['redirect_to'] = jr_get_final_redirect_to_url( $plan_type );
			break;
		default:
			$params['redirect_to'] = get_permalink( $job_id );
			$params['step'] = _jr_steps_get_last();
	}

	$params['order_id'] = $order_id;
	$params['plan_data'] = $plan_data;

	$parent_template = jr_get_order_template( $plan_type );

	if ( $job_id ) {
		$steps = jr_steps();
	} else {
		$steps = _jr_select_plans_steps();
	}

	$step = ! empty( $params['step'] ) ? $params['step'] : jr_get_next_step();

	$args = array(
		'params'		=> $params,
		'step'			=> $step,
		'steps_trail'	=> jr_get_steps_trail( $steps, $step ),
	);

	if ( ! empty( $steps[ $step ]['title'] ) ) {
		$args['title'] = $steps[ $step ]['title'];
	}

	if ( ! empty( $steps[ $step ]['template'] ) ) {
		$args['template'] = $steps[ $step ]['template'];
	}

	appthemes_load_template( $parent_template, $args );
}

/**
* Retrieves the correct template file name for a specific Order
* @param string $plan_type The plan type
*
* @return string $template The template file name
*/
function jr_get_order_template( $plan_type ) {

	switch( $plan_type ){
		case APPTHEMES_RESUMES_PLAN_PTYPE:
			$page_id = JR_Resume_Plans_Purchase_Page::get_id();
			break;
		case APPTHEMES_PRICE_PLAN_PTYPE:
			$page_id = JR_Packs_Purchase_Page::get_id();
			break;
		default:
			$page_id = JR_Job_Submit_Page::get_id();
	}

	$template = get_post_meta( $page_id, '_wp_page_template', true );

	return $template;
}

/**
* Sanitizes a plan ID by removing invalid characters
* @param array $plan The plan array
*
* @return string The sanitized plan ID
*/
function _jr_sanitized_plan( $plan ) {
	return preg_replace('/[^-0-9]/', null, $plan );
}

/**
* Updates the plan usage for a user
* @param int $user_id The user ID
* @param $plan_type The plan type (Resume or Pricing plan)
*
* @return void
*/
function jr_reset_user_plan_usage( $user_id, $plan_type ) {
	$reset_plans = array();

	$user_plans = _jr_user_plans_history( $user_id );
	foreach ( $user_plans as $plan_id => $usage ) {
		$curr_plan_type = get_post_type( $plan_id );
		if ( $plan_type != $curr_plan_type ) {
			$reset_plans[$plan_id] = $usage;
		}
	}
	update_user_meta( $user_id, '_jr_user_plans_history', $reset_plans );
}
