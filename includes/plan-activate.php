<?php
add_action( 'appthemes_transaction_completed', 'jr_handle_completed_transaction', 11 );

add_action( 'pending_to_publish', '_jr_handle_moderated_transaction' );

add_action( 'appthemes_transaction_activated', '_jr_activate_pricing_plan', 10 );
add_action( 'appthemes_transaction_activated', '_jr_update_plan_stats', 15 );

add_action( 'appthemes_transaction_failed', '_jr_maybe_cancel_job', 10 );

add_action( 'jr_activate_plan_job_addons', '_jr_activate_addons_featured', 11, 2 );
add_action( 'jr_activate_plan_user_addons', '_jr_activate_addons_resumes', 11, 2 );

add_action( 'jr_addon_featured_activated', 'jr_add_featured', 10, 3 );
add_action( 'jr_addon_resume_activated', 'jr_add_resumes_access', 10, 3 );

/**
 * Handle completed order transactions
 *
 * @param object $order The completed order
 *
 * @return void
 */
function jr_handle_completed_transaction( $order ) {

	$order_data = _jr_get_order_job_info( $order );

	extract( $order_data );

	$status = '';

	// skip moderation on paid plans
	if ( ! _jr_moderate_jobs() || ( _jr_moderate_jobs() && $plan_data[JR_FIELD_PREFIX.'price'][0] > 0 ) ) {
		$status = 'publish';
	}

	if ( APP_POST_TYPE == get_post_type( $post_id ) ) {
		jr_update_post_status( $post_id, $status );
	}

	// if the order does not contain jobs activate it immediately - usually when purchasing separate pack plans or resumes subscriptions
	if ( APP_POST_TYPE != get_post_type( $post_id ) || 'publish' == $status ) {
		$order->activate();
	}

}

/**
 * Handle failed order transactions
 *
 * @param object $order The failed order
 *
 * @return void
 */
function _jr_maybe_cancel_job( $order ) {

	$order_data = _jr_get_order_job_info( $order );

	extract( $order_data );

	if ( APP_POST_TYPE == get_post_type( $post_id ) ) {
		_jr_end_job( $post_id, $cancel = true, 'order_failed' );
	}

}

/**
 * Handle jobs needing moderation when a post changes status
 *
 * @param object $post The post being updated
 *
 * @return void
 */
function _jr_handle_moderated_transaction( $post ){

	if ( $post->post_type != APP_POST_TYPE )
		return;

	$order = appthemes_get_order_connected_to( $post->ID );
	if ( $order && $order->get_status() !== APPTHEMES_ORDER_COMPLETED )
		return;

	if ( $order ) add_action( 'save_post', '_jr_activate_moderated_transaction', 11);

}

/**
 * Triggers the order activation as soon as the job is published
 *
 * @param oject $job_id The job being published
 *
 * @return void
 */
function _jr_activate_moderated_transaction( $job_id ){

	if ( get_post_type( $job_id ) != APP_POST_TYPE || wp_is_post_revision( $job_id ) )
		return;

	$order = appthemes_get_order_connected_to( $job_id );
	$order->activate();

}

/**
 * Retrieves the job ID for a specific order
 *
 * @param object $order The order
 *
 * @return int|bool The job ID or FALSE if no job is attached to the order
 */
function jr_get_order_job_id( $order ) {

	foreach ( $order->get_items() as $item ) {
		if ( APP_POST_TYPE == $item['post']->post_type )
			return $item['post_id'];
	}
	return false;
}

/**
 * Retrieves all the info (job_id, job, plan, plan_data) for a specific order
 *
 * @param object $order The order
 *
 * @return bool|array FALSE if no data, or an array with the order info
 */
function _jr_get_order_job_info( $order ){

	$plan = jr_plan_for_order( $order );
	if ( ! $plan )
		return false;

	$items = $order->get_items( $plan->post_name );
	if( $items ){

		$job_id = jr_get_order_job_id( $order );

		$plan_data = get_post_custom( $plan->ID );
		return array(
			'post_id' => $job_id ? $job_id : $items[0]['post_id'],
			'post' => $job_id ? get_post($job_id) : $items[0]['post'],
			'plan' => $plan,
			'plan_data' => $plan_data
		);
	}
	return false;
}

/**
 * Activates the plan and triggers the related addons actions
 *
 * @param object $order The order
 *
 * @return void
 */
function _jr_activate_pricing_plan( $order ){

	$order_data =  _jr_get_order_job_info( $order );
	if ( !$order_data ) {
		return;
	}

	extract( $order_data );

	if ( $plan->post_type != APPTHEMES_PRICE_PLAN_PTYPE ) {
		return;
	}

	// skip job publishing and job addons if the plan does not contain any jobs
	if ( APP_POST_TYPE == $post->post_type ) {

		if ( _jr_needs_publish( $post ) ) {
			jr_update_post_status( $post_id, 'publish' );
		}

		_jr_set_job_duration( $post_id, $plan_data[JR_FIELD_PREFIX.'duration'][0] );

		do_action( 'jr_activate_job', $post_id, $plan_data );

		do_action( 'jr_activate_plan_job_addons', $order, $plan_data );
	}

	do_action( 'jr_activate_plan_user_addons', $order, $plan_data );
}

/**
 * Activate featured addons
 *
 * @param object $order The order
 * @param array $plan_data The plan data
 *
 * @return void
 */
function _jr_activate_addons_featured( $order, $plan_data ){
	global $jr_options;

	// featured addons
	foreach( _jr_featured_addons() as $addon ) {
		foreach( $order->get_items( $addon ) as $item ) {
			if ( ! empty($plan_data[$addon][0]) )
				$duration = $plan_data[ $addon . '_duration' ][0];
			else
				$duration = $jr_options->addons[$addon]['duration'];

			do_action( 'jr_addon_featured_activated', $item['post_id'], $addon, $duration );
		}
	}
}

/**
 * Activate resumes addons
 *
 * @param object $order The order
 * @param array $plan_data The plan data
 *
 * @return void
 */
function _jr_activate_addons_resumes( $order, $plan_data ){
	global $jr_options;

	$addons = 0;

	// resumes addons
	foreach( _jr_resumes_addons() as $addon ) {
		foreach( $order->get_items( $addon ) as $item ){
			if ( ! empty($plan_data[$addon][0]) ) {
				$duration = $plan_data[ $addon . '_duration' ][0];
			} else {
				$duration = $jr_options->addons[$addon]['duration'];
			}

			do_action( 'jr_addon_resume_activated', $order->get_author(), $addon, $duration );
			$addons++;
		}
	}

	if ( $addons ) {
		jr_user_resume_subscription_started_email( $order->get_author(), $addon = true );
	}

}

/**
 * Activate resumes addons with no Order associated
 *
 * @param int $user_id The user ID to assign the addon
 * @param array $plan_data The plan data
 *
 * @return void
 */
function _jr_activate_addons_resumes_no_order( $user_id, $plan_data ){
	global $jr_options;

	$addons = 0;

	// resumes addons
	foreach( _jr_resumes_addons() as $addon ) {

		if ( ! empty( $plan_data[$addon][0] ) ) {

			if ( ! empty( $plan_data[ $addon . '_duration' ][0] ) ) {
				$duration = $plan_data[ $addon . '_duration' ][0];
			} else {
				$duration = $jr_options->addons[$addon]['duration'];
			}

			do_action( 'jr_addon_resume_activated', $user_id, $addon, $duration );
			$addons++;

		}

	}

	if ( $addons ) {
		jr_user_resume_subscription_started_email( $user_id, $addon = true );
	}

}

/**
 * Get all the available price plans
 *
 * @param array $args Additional arguments to be used on the query
 *
 * @return array The plans data
 */
function jr_get_available_plans( $args = array() ) {
	global $jr_options;

	$default_args = array(
		'post_type'   => APPTHEMES_PRICE_PLAN_PTYPE,
		'post_status' => 'publish',
		'nopaging'    => 1,
		'orderby'     => 'menu_order',
		'order'       => 'ASC',
	);
	$args = wp_parse_args( $args, $default_args );

	$plans = new WP_Query( $args );

	if ( 'pack' == $jr_options->plan_type ) {

		// pack meta defaults (inits pack meta in case the site owner switches from single plans to pack plans without updating the plan in the backend)
		$pack_meta_defaults = array(
			JR_FIELD_PREFIX.'jobs_limit' => array( 0 ),
			JR_FIELD_PREFIX.'pack_duration' => array( 0 ),
			JR_FIELD_PREFIX.'job_offers_limit' => array( 0 ),
		);
	}

	$plans_data = array();
	$key = 0;
	foreach( $plans->posts as $plan ){
		$plan_data = get_post_custom( $plan->ID );
		if ( ! empty( $pack_meta_defaults ) ) {
			$plan_data = wp_parse_args( $plan_data, $pack_meta_defaults );
		}
		$plans_data[$key] = $plan_data;
		$plans_data[$key]['post_data'] = $plan;
		$key++;
	}

	return $plans_data;
}

/**
 * Retrieve the prices and duration for the available price plans
 *
 * @return array The price/duration list
 */
function jr_get_plans_prices_duration() {
	$plans = jr_get_available_plans();
	if ( ! $plans )
		return;

	$prices = array();
	foreach ( $plans as $plan ) {
		if ( ! empty( $plan[JR_FIELD_PREFIX.'price'][0] ) && $plan[JR_FIELD_PREFIX.'price'][0] > 0 ) {
			$prices[] = array (
				'price' => $plan[JR_FIELD_PREFIX.'price'][0],
				'duration' => $plan[JR_FIELD_PREFIX.'duration'][0]
			);
		}
	}
	sort( $prices );
	return $prices;
}

/**
 * Retrieve the URL that will redirect the user to his order content
 *
 * @param object $order The order
 *
 * @return string The URL to where the user should be redirected
 */
function jr_get_redirect_to_url( $order ) {

	$job_id = jr_get_order_job_id( $order );
	if ( $job_id ) {
		$url = get_permalink( $job_id );
	} else {
		$plan = jr_plan_for_order( $order );
		$url = jr_get_final_redirect_to_url( $plan->post_type );
	}

	return $url;

}

/**
 * Outputs the redirect HTML
 *
 * @param string $url The URL to redirect the user to
 *
 * @return void
 */
function jr_js_redirect( $url ) {
	echo html( 'a', array( 'href' => $url ), __( 'Continue', APP_TD ) );
	echo html( 'script', 'location.href="' . $url . '"' );
}

/**
 * Retrieves the URL to where the user should be redirect based on the plan type
 *
 * @param string $plan_type The plan type
 *
 * @return string The URL to redirect the user to
 */
function jr_get_final_redirect_to_url( $plan_type ) {

	switch( $plan_type ){
		case APPTHEMES_PRICE_PLAN_PTYPE:
			$url = add_query_arg( array( 'tab' => 'packs' ) , get_permalink( JR_Dashboard_Page::get_id() ) );
			break;
		default:
			$url = get_post_type_archive_link( APP_POST_TYPE_RESUME );
	}
	return $url;
}

/**
 * Update the user purchased plan history - used to limit a plan usage
 *
 * @param object $order The order
 *
 * @return void
 */
function _jr_update_plan_stats( $order ) {

	$plan = jr_plan_for_order( $order );
	if ( ! $plan )
		return;

	$user_plans_history = _jr_user_plans_history( $order->get_author() );

	if ( empty( $user_plans_history[$plan->ID] ) ) {
		$user_plans_history[$plan->ID] = 1;
	} else {
		$user_plans_history[$plan->ID]++;
	}

	update_user_meta( $order->get_author(), '_jr_user_plans_history', $user_plans_history );
}

/**
 * Retrieves all the purchased plans for a specific user
 *
 * @param int $user_id The user ID
 *
 * @return array The user plans or an empty list
 */
function _jr_user_plans_history( $user_id ) {
	$user_plans = get_user_meta( $user_id, '_jr_user_plans_history' );
	if  ( !empty($user_plans[0]) ) {
		return $user_plans[0];
	} else {
		return array();
	}
}

/**
 * Checks if a specific plan is selectable by the current user
 *
 * @param int $plan_id The plan ID
 * @param array $plan_data The plan data
 * @param int $user_id The user ID
 *
 * @return bool TRUE if the plan is selectable, FALSE otherwise
 */
function jr_plan_is_selectable( $plan_id, $plan_data, $user_id = 0 ) {

	$user_id = $user_id ? $user_id : get_current_user_id();

	$user_plans_history = _jr_user_plans_history( $user_id );
	if ( ! empty( $plan_data[JR_FIELD_PREFIX.'limit'] ) && $plan_data[JR_FIELD_PREFIX.'limit'] > 0 && ! empty($user_plans_history[$plan_id]) ) {

		if ( $user_plans_history[$plan_id] >= $plan_data[JR_FIELD_PREFIX.'limit'] )
			return false; // skip plan - usage limit reached
	}
	return true;
}

/**
 * Retrieve the remaining usage limits for a specific plan
 *
 * @param int $plan_id The plan ID
 * @param array $plan_data The plan data
 * @param int $user_id The user ID
 *
 * @return int The current usage limits
 */
function jr_plan_remain_usage( $plan_id, $plan_data, $user_id = 0 ) {
	global $user_ID;

	$user_id = $user_id ? $user_id : $user_ID;

	$user_plans_history = _jr_user_plans_history( $user_id );
	if ( ! empty( $plan_data[JR_FIELD_PREFIX.'limit'] ) && $plan_data[JR_FIELD_PREFIX.'limit'] > 0 && ! empty($user_plans_history[$plan_id]) ) {
		return $plan_data[JR_FIELD_PREFIX.'limit'] - $user_plans_history[$plan_id];
	}
	return intval($plan_data[JR_FIELD_PREFIX.'limit']);
}

/**
 * Retrieves the plan for a specific order
 *
 * @param object $order The order
 *
 * @return array The order plan
 */
function jr_plan_for_order( $order ) {

	$post_types = array ( APPTHEMES_PRICE_PLAN_PTYPE, APPTHEMES_RESUMES_PLAN_PTYPE );

	$plans = new WP_Query( array( 'post_type' => $post_types, 'nopaging' => true, 'post_status' => 'any' ) );

	foreach( $plans->posts as $key => $plan) {
		$plan_slug = $plan->post_name;

		$items = $order->get_items( $plan_slug );
		if ( $items && $plan_slug ) {
			return $plan;
		}
	}

	return false;
}
