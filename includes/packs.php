<?php
/**
 * Pack Related Functions
 *
 * @version 1.7
 * @author AppThemes
 * @package JobRoller
 * @copyright 2010 all rights reserved
 *
 */

add_action( 'wp_loaded', 'jr_handle_pack_order', 12 );
add_action( 'jr_pack_validate_plan', 'jr_check_plan' );
add_action( 'appthemes_create_order', 'jr_new_user_pack_order', 8, 4 );

add_action( 'appthemes_transaction_completed', 'jr_complete_user_pack_order', 9 );

add_action( 'jr_pack_order', 'jr_update_user_pack_meta', 10, 2 );
add_action( 'jr_pack_order', 'jr_maybe_expire_user_pack', 11, 2 );

add_filter( 'jr_addons_output', 'jr_optional_featured_addons_output', 10, 3 );
add_filter( 'jr_plan_price', 'jr_pack_price', 10, 2 );

add_filter( 'jr_new_user_pack_meta', 'jr_new_user_pack_job_offers_meta', 10, 2 );
add_filter( 'jr_new_user_pack_meta', 'jr_new_user_pack_addon_offers_meta', 10, 2 );
add_filter( 'jr_new_user_pack_meta', 'jr_new_user_pack_access_meta', 10, 2 );

add_filter( 'jr_user_pack_jobs_limit', 'jr_user_pack_limit_add_job_offers', 10, 2 );

/**
* Handle separate pack plans with no jobs attached
*
* @return void
*/
function jr_handle_pack_order() {

	if ( ! isset( $_POST['action'] ) || 'purchase-separate-plan' != $_POST['action'] || ! empty( $_POST['goback'] ) ) {
		return;
	}

	$errors = apply_filters( 'jr_pack_validate_plan', jr_get_listing_error_obj() );
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

		$order_id = (int) $_POST['order_id'];
		$order = appthemes_get_order( $order_id );
		$order->remove_item();

		$new_order = false;
	}

	$attach = get_post( $order->get_id() );

	do_action( 'appthemes_create_order', $order, $plan, $plan_data, $attach );

	// action hook executed only on each new order
	if ( $new_order ) {
		jr_after_insert_order( $order );
	}

	_jr_order_redirect_user( $order, $plan, $attach );
}

/**
* Temporarily store the ordered user pack ID as user meta to later identify it as an existing user Pack order
* @param object $order The order
* @param array $plan The plan
* @param array $plan_data The plan data
* @param object $attach The attached object - a Job or Order objects
*
* @return void
*/
function jr_new_user_pack_order( $order, $plan, $plan_data, $attach ) {
	global $jr_options;

	if ( 'pack' != $jr_options->plan_type  )
		return;

	if ( $plan->post_type != APPTHEMES_PRICE_PLAN_PTYPE )
		return;

	if ( isset($plan_data['user_pack']) ) {
		$pack_umeta_id = $plan_data['user_pack']['pack_umeta_id'];
		update_user_meta( $order->get_author(), '_jr_user_pack_order-' . $order->get_id(), $pack_umeta_id );
	}
}

/**
* Assigns a new user pack to the user and triggers sactions to handle the related user meta after the order is completed
* @param object $order The order
*
*/
function jr_complete_user_pack_order( $order ) {
	global $jr_options;

	if ( 'pack' != $jr_options->plan_type  ) {
		return;
	}

	$plan = jr_plan_for_order( $order );
	if ( ! $plan ) {
		return;
	}

	if ( $plan->post_type != APPTHEMES_PRICE_PLAN_PTYPE ) {
		return;
	}

	// retrieve a existing pack user meta ID, or assign a new pack user meta ID to the order author
	$umeta_id = get_user_meta( $order->get_author(), '_jr_user_pack_order-' . $order->get_id(), true );
	if ( ! $umeta_id ) {
		$umeta_id = jr_give_pack_to_user( $order->get_author(), $plan->ID, $order->get_id() );
	}

	// delete the temporary user meta key
	delete_user_meta( $order->get_author(), '_jr_user_pack_order-' . $order->get_id() );

	do_action( 'jr_pack_order', $order, $umeta_id );
}

/**
* Creates and assigns a pack plan to a user and initializes all related meta data
* @param int $user_id The user ID
* @param int $plan_id The plan ID
* @param int $order_id The order ID
* @param int $start_time The pack start date
*
* @return int The new user pack ID
*/
function jr_give_pack_to_user( $user_id, $plan_id, $order_id = 0, $start_time = '' ) {

	if ( ! $plan_id ) {
		return;
	}

	if ( ! $start_time ) $start_time = strtotime("now");

	$data = get_post_custom( $plan_id );
	$data = jr_reset_data( $data );

	$user_pack = array(
		'order_id' => $order_id,
		'plan_id' => $plan_id,
		'plan_title' => $data['title'],
		'start_date' => $start_time,
		'end_date' => _jr_calc_pack_expire_date( $start_time, $data[JR_FIELD_PREFIX.'pack_duration'] ),
		'jobs_count' => 0,
		'jobs_limit' => $data[JR_FIELD_PREFIX.'jobs_limit'],
		'jobs_duration' => $data[JR_FIELD_PREFIX.'duration'],
	);
	$user_pack = apply_filters( 'jr_new_user_pack_meta', $user_pack, $data );

	### generate the new user pack ID and store the pack meta

	$temp_uniqid =  sha1( time() . rand() );

	$umeta_id = add_user_meta( $user_id, '_jr_user_pack-' . $temp_uniqid, $temp_uniqid );
	delete_user_meta( $user_id, '_jr_user_pack-' . $temp_uniqid );

	$umeta_id = $plan_id . '-' . $umeta_id;

	add_user_meta( $user_id, '_jr_user_pack-' . $umeta_id, $user_pack );

	### additionally store the new pack ID on a separate user key to know which user packs are still active

	$user_packs = jr_get_user_packs( $user_id );
	$user_packs[$umeta_id] = $plan_id;

	// store order<=>plan value pairs in user meta separately
	update_user_meta( $user_id, '_jr_user_packs_active', $user_packs );

	// if the order ID is empty it's because the pack is being given (not purchased)
	if ( ! $order_id ) {
		_jr_activate_addons_resumes_no_order( $user_id, $data );
	}

	return $umeta_id;
}

/**
* Initialize and retrieve all the user pack meta related with job offers, for the current plan data
* @param array $user_pack The user pack
* @param array $plan_data The plan data
*
* @return array The user pack job offers meta
*/
function jr_new_user_pack_job_offers_meta( $user_pack, $plan_data ) {

	$offers = jr_pack_job_offers();
	$key = key($offers);

	if ( ! $plan_data[JR_FIELD_PREFIX.'job_offers_limit'] ) {
		return $user_pack;
	}

	$user_pack[$key] = 'on';
	$user_pack[$key . '_limit'] = $plan_data[$key.'_limit'];
	$user_pack[$key . '_count'] = 0;

	return $user_pack;
}

/**
* Initialize and retrieve the user meta related with addon offers, for the current plan data
* @param array $user_pack The user pack
* @param array $plan_data The plan data
*
* @return array The user pack addon offers meta
*/
function jr_new_user_pack_addon_offers_meta( $user_pack, $plan_data ) {

	$offers = jr_pack_addons_offers( $plan_data );

	foreach ( $offers as $key => $offer ) {

		if ( ! empty($plan_data[$key]) ) {
			$user_pack[$key] = 'on';

			if ( isset($plan_data[$key.'_limit']) ) {
				$user_pack[$key . '_limit'] = intval($plan_data[$key.'_limit']);
				$user_pack[$key . '_count'] = 0;
				$enabled = true;
			}

			if ( isset($plan_data[$key.'_duration']) ) {
				$user_pack[$key . '_duration'] = intval($plan_data[$key.'_duration']);
				$enabled = true;
			}

		}
	}
	return $user_pack;
}


/**
* Initialize and retrieve the user meta related with access addon offers, for the current plan data
* @param array $user_pack The user pack
* @param array $plan_data The plan data
*
* @return array The user pack access addon offers meta
*/
function jr_new_user_pack_access_meta( $user_pack, $plan_data ) {

	$access = jr_get_pack_access( $plan_data );

	foreach ( $access as $key => $access ) {
		if ( ! empty($plan_data[$key]) ) {
			$user_pack[$key] = 'on';

			if ( ! empty($plan_data[$key.'_duration']) ) {
				$user_pack[$key . '_duration'] = $plan_data[$key.'_duration'];
			}
		}
	}
	return $user_pack;
}


/**
* Update all the user pack meta stats: job count, job offers, etc
* @param object $order The order
* @param int $plan_umeta_id The plan user meta ID
*
* @return void
*/
function jr_update_user_pack_meta( $order, $plan_umeta_id ) {
	global $jr_options;

	if ( 'pack' != $jr_options->plan_type )
		return;

	$user_pack = _jr_user_pack_meta( $order->get_author(), $plan_umeta_id );
	if ( empty($user_pack) )
		return;

	### count jobs

	// dount update counts if the order does not contain any jobs
	$job_id = jr_get_order_job_id( $order );
	if ( ! $job_id )
		return;

	@$user_pack['jobs_count']++;

	### count offers

	$count = 0;
	$offers = jr_get_pack_all_offers( $user_pack );
	foreach ( $offers as $key => $offer ) {

		if ( empty( $user_pack[$key] ) )
			continue;

		// job offers are counted after the job limit is reached
		if ( JR_FIELD_PREFIX.'job_offers' == $key && $user_pack['jobs_count'] > $user_pack['jobs_limit'] ) {
			$count = $user_pack['jobs_count'] - $user_pack['jobs_limit'];
			@$user_pack[$key . '_count'] = $count;
		} else {
			$count = _jr_count_addon_offers( $order, $key );
			@$user_pack[$key . '_count'] += $count;
		}

	}
	update_user_meta( $order->get_author(), '_jr_user_pack-' . $plan_umeta_id, $user_pack );
}

/**
* Expire user pack if limits are reached (wrapper)
* @param object $order The order
* @param int $plan_umeta_id The plan user meta ID
*
* @return void
*/
function jr_maybe_expire_user_pack( $order, $plan_umeta_id ) {

	_jr_maybe_expire_user_pack( $order->get_author(), $plan_umeta_id );

}

/**
* Expire user pack if limits are reached
* @param object $order The order
* @param int $plan_umeta_id The plan user meta ID
*
* @return void
*/
function _jr_maybe_expire_user_pack( $user_id, $plan_umeta_id ) {
	global $jr_options;

	if ( 'pack' != $jr_options->plan_type )
		return;

	$user_pack = _jr_user_pack_meta( $user_id, $plan_umeta_id );
	if ( empty($user_pack) )
		return;

	if ( jr_is_expired_user_pack( $user_id, $plan_umeta_id ) ) {
		jr_expire_user_pack( $user_id, $plan_umeta_id );
	}

}

/**
* Expire the user pack by removing it from the list of active user packs
* @param int $user_id The user ID
* @param int $plan_umeta_id The plan user meta ID
*
* @return void
*/
function jr_expire_user_pack( $user_id, $plan_umeta_id ) {

	$user_packs = jr_get_user_packs( $user_id );
	if ( ! empty($user_packs) ) {

		// expire user plan for current order
		$expired[$plan_umeta_id] = $user_packs[$plan_umeta_id];
		$user_packs = array_diff_assoc( $user_packs, $expired );

		update_user_meta( $user_id, '_jr_user_packs_active', $user_packs );

		do_action( 'jr_expire_user_pack', $user_id, $plan_umeta_id );
	}

}

/**
* Sums existing job offers to the total jobs limit of a pack
* @param int $jobs_limit
* @param array $user_pack
*
* @return int The job limit + job offers
*/
function jr_user_pack_limit_add_job_offers( $jobs_limit, $user_pack ) {

	$job_offers = jr_pack_job_offers();

	if ( ! empty($user_pack[key($job_offers)]) ) {
		$jobs_limit += $user_pack[key($job_offers).'_limit'];
	}

	return $jobs_limit;
}

/**
* Check if a user pack has expired
* @param int $user_id The user ID
* @param int $plan_umeta_id The plan user meta ID
*
* @return bool TRUE if expired, False otherwise
*/
function jr_is_expired_user_pack( $user_id, $plan_umeta_id ) {

	$user_pack = _jr_user_pack_meta( $user_id, $plan_umeta_id );
	if ( empty($user_pack) )
		return true;

	extract( $user_pack );

	$pack_jobs_limit = apply_filters( 'jr_user_pack_jobs_limit', $jobs_limit, $user_pack );

	// validate pack jobs limit
	if ( intval($pack_jobs_limit) > 0 && $jobs_count >= $pack_jobs_limit )
		return true; 	// expired

	if ( is_string( $end_date ) ) {
		$end_date = strtotime( $end_date );
	}

	// validate pack duration
	if ( $end_date > 0 && time() > $end_date  )
		return true;	//expired

	return false;
}


/**
* Retrieve the meta for a specific user pack
* @param int $user_id The user ID
* @param int $pack_umeta_id The user pack meta ID
*
* @return array The user pack meta
*/
function _jr_user_pack_meta( $user_id, $pack_umeta_id ) {
	$user_pack = get_user_meta( $user_id, '_jr_user_pack-' . $pack_umeta_id );
	if ( empty($user_pack) )
		return array();

	return $user_pack[0];
}

/**
* Retrieve the active packs for a specific user
* @param int $user_id The user ID
*
* @return array The user active packs list
*/
function jr_get_user_packs( $user_id ) {

	$user_packs = get_user_meta( $user_id, '_jr_user_packs_active' );
	if  ( !empty($user_packs[0]) ) {
		return $user_packs[0];
	} else {
		return array();
	}
}

/**
* Counts offers for a specific addon order (price = 0)
* @param object $order The order
* @param string $addon The addon name
*
*/
function _jr_count_addon_offers( $order, $addon ) {
	$count = 0;

	$items = $order->get_items( $addon );
	foreach ( $items as $item ) {
		if ( ! $item['price'] ) $count++;
	}
	return $count;
}

/**
* Calculate and retrieve a valid date based on a duration
* @param (optional) int $start_time The pack start time
* @param (optional) int $duration The duration to be calculated
*
* @return int The resulting date
*/
function _jr_calc_pack_expire_date( $start_time = '', $duration = '' ) {
	if ( ! $duration )
		return -1;

	if ( ! $start_time ) $start_time = current_time('timestamp');

	$date = strtotime( '+' . $duration . ' DAYS', $start_time );
	return $date;
}


/**
* Retrieve all the available plans or a specified plan list with additional pack data
* @param (optional) array $plans A plan list
*
* @return The plan list with additional pack data
*/
function jr_get_plan_packs( $plans = '' ) {

	$plans = $plans ? $plans : jr_get_available_plans();

	$packs = array();

	foreach( $plans as $key => $plan ) {

		if ( ! jr_plan_is_selectable( $plan['post_data']->ID, jr_reset_data($plan) ) ) {
			continue;
		}

		$pack = array (
			'plan_id' 		=> $plan['post_data']->ID,
			'plan_ref_id'	=> $plan['post_data']->ID,
			'plan_data'		=> jr_reset_data($plan),
			'plan_cats'		=> wp_get_object_terms(  $plan['post_data']->ID, APP_TAX_CAT, array( 'fields' => 'ids' ) ),
		);
		$packs[] = $pack;

	}

	return $packs;
}

/**
* Retrieve all the purchased plans for the current user with additional pack data
* @param (optional) int $user_id The user ID
* @param (optional) array $plans A plans list
*
* @return The user plan list with additional pack data
*/
function jr_get_user_plan_packs( $user_id = 0, $plans = '' ) {

	$user_id = $user_id ? $user_id : get_current_user_id();

	$plans = $plans ? $plans : jr_get_available_plans();

	$packs = array();

	foreach( $plans as $plan ) {
		$plan_data[$plan['post_data']->ID] = $plan;
	}

	$user_packs = jr_get_user_packs( $user_id );
	if ( ! $user_packs || empty($plan_data) ) {
		return $packs;
	}

	foreach( $user_packs as $pack_umeta_id => $plan_id ) {

		// check if plan still exists
		if ( ! isset( $plan_data[ $plan_id ] ) ) {
			$plan_data[ $plan_id ] = array();
		}

		$pack_meta = _jr_user_pack_meta( $user_id, $pack_umeta_id );

		$pack = array (
			'plan_id' 		=> $plan_id,
			'plan_ref_id'	=> $pack_umeta_id,
			'plan_data' 	=> jr_reset_data( $plan_data[ $plan_id ] ),
			'plan_cats'		=> wp_get_object_terms( $plan_id , APP_TAX_CAT, array( 'fields' => 'ids' ) ),
			'meta' 			=> $pack_meta,
		);

		$packs[] = $pack;
	}

	$date = array();
	foreach ( $packs as $key => $row ) {
		$date[ $key ] = $row['meta']['start_date'];
	}

	// sort user packs  by most recent
	array_multisort( $date, SORT_DESC, $packs );

	return $packs;
}


/**
* Outputs the packs selection to the current user
* @param string $pack_type The pack type: user|new
* @param array $plans The plans list
* @param array $display_options Additional display options
* @param (optional) int $default Identifies the default pack
* @param (optional) object $job The job
*
* @return bool TRUE on success, FALSE on failure - no valid packs available
*/
function jr_display_packs( $pack_type, $plans, $display_options, $default = 0, $job = 0 ) {

	if ( 'user' == $pack_type ) {
		$packs = jr_get_user_plan_packs( get_current_user_id(), $plans );
	} else {
		$packs = jr_get_plan_packs( $plans );
	}

	if ( ! $packs ) {
		return false;
	}

	foreach ($packs as $pack) {

		if ( 'user' == $pack_type ) {
			$output = jr_user_pack_output( $pack );
		} else {
			$output = jr_new_pack_output( $pack );
		}

		$commons_output = jr_pack_commons_output( $pack_type, $output, $display_options );

		$pack = $commons_output;

		$plan_id = $pack['plan_ref_id'];
		$plan_data = $pack['plan_data'];

		$pack_selection_text = 'user' == $pack['output']['type'] ? __( 'Choose This Pack!', APP_TD ) : __( 'Buy This Pack!', APP_TD );

		$vars = array_merge( compact( 'pack', 'plan_id', 'plan_data', 'job', 'default', 'pack_selection_text' ), $pack['output'] );

		appthemes_load_template( array( 'includes/forms/select-plan/single-pack.php', 'includes/single-pack.php' ), $vars );

		$default = 0;
	}

	return true;
}

/**
* Retrieves a multi-dimensional array representing the pack job offers
*
* @return array A multi-dimensional array with the job offer name and description
*/
function jr_pack_job_offers() {
	$job_offers = array (
		JR_FIELD_PREFIX.'job_offers'	=> array (
			'name'	=> __('Job Offers', APP_TD),
		),

	);
	return $job_offers;
}

/**
* Retrieves jobs addon offers
* @param array $plan_data The plan data
*
* @return array A list of the jobs addon offers
*/
function jr_pack_addons_offers( $plan_data ) {

	$addon_offers = array();

	foreach ( jr_get_addons('job') as $addon ) {
		$title  = APP_Item_Registry::get_title( $addon );

		if ( ! empty( $plan_data[$addon] ) ) {
			$addon_offers[$addon]['name'] = $title;
		}
	}

	return $addon_offers;
}

/**
* Retrieves all available offers - associated with jobs
* @param array $plan_data
*
* @return array A list of jobs addon offers
*/
function jr_get_pack_all_offers( $plan_data ) {
	$all_offers =  array_merge( jr_pack_job_offers(), jr_pack_addons_offers( $plan_data ) );
	return $all_offers;
}

/**
* Retrieves special access addon offers (i.e: resumes) - associated with the user
* @param array $plan_data The plan data
*
* @return array A list of access addon offers
*/
function jr_get_pack_access( $plan_data ) {

	$pack_access = array();

	foreach ( jr_get_addons('user')  as $addon ) {
		$title  = APP_Item_Registry::get_title( $addon );

		if ( ! empty( $plan_data[$addon . '_duration'] ) ) {
			$pack_access[$addon]['name'] = $title;
		}
	}

	return $pack_access;
}

/**
* Calculates and returns the remaining offers for a given offer name from a pack
* @param string $offer The offer name
* @param array $pack The pack data
* @param (optional) string $type The pack type: user|new
*
* @return int The remaining offers
*/
function _jr_pack_calc_remain_offers( $offer, $pack, $type = 'user' ) {

	if ( 'user' == $type && ! empty($pack['meta'][$offer.'_limit']) && ! empty($pack['meta'][$offer]) ) {
		$count = $pack['meta'][$offer . '_limit'] - $pack['meta'][$offer . '_count'];
	} else {

		if ( ! empty($pack['plan_data'][$offer .'_limit']) ) {
			$count = $pack['plan_data'][$offer .'_limit'];
		} else {
			$count = 0;	// no offers if job offer / unlimited if addon offer
		}

	}

	if ( $count < 0 ) $count = 0;

	return $count;
}

/**
* Calculates and returns the limits for a specific offer from a pack
* @param string $offer The offer name
* @param array $pack The pack data
*
* @return int The offer limits
*/
function _jr_pack_calc_offer_limit( $offer, $pack ) {

	if ( ! empty($pack['plan_data'][ $offer .'_limit' ]) )
		$limit = $pack['plan_data'][ $offer .'_limit' ];
	else
		$limit = 0;

	return $limit;
}

/**
* Calculates and returns an offer duration from a pack
* @param string $offer The offer name
* @param array $pack The pack data
*
* @return int|string The offer duration - empty if a duration is not set
*/
function _jr_pack_calc_offer_duration( $offer, $pack ) {
	if ( empty($pack['plan_data'][ $offer . '_duration' ]) ) {
		$duration = '';
	} else {
		$duration = $pack['plan_data'][ $offer . '_duration' ];
	}
	return $duration;
}

/**
* Sets the plan price to 0 if user is 'purchasing' a job using a user pack
* @param int $price The plan default price
* @param array $plan_data The plan data
*
* @return int The updated plan price
*/
function jr_pack_price( $price, $plan_data ) {

	if ( ! empty($plan_data['user_pack']) ) {
		$price = 0;
	}
	return $price;
}

/**
* Retrieves the jobs remaining for a specific user pack
* @param array $pack The pack data
*
* @return int The jobs remaining
*/
function jr_pack_jobs_remain( $pack ) {
	$offers = jr_pack_job_offers();
	$key = key($offers);
	if ( isset($pack['meta'][$key]) ) {
		$job_offers = $pack['meta'][$key . '_limit'];
		$pack['meta']['jobs_limit'] += $job_offers;
	}
	return $pack['meta']['jobs_limit'] - $pack['meta']['jobs_count'];
}

/**
* Build and retrieve all the output data unique to user packs
* @param array $pack The pack data
*
* @return The pack with additional output data
*/
function jr_user_pack_output( $pack ) {

	$remain_job_offers = _jr_pack_calc_remain_offers( JR_FIELD_PREFIX.'job_offers', $pack );
	$remain_jobs = $pack['meta']['jobs_limit'] - $pack['meta']['jobs_count'];

	if ( empty( $pack['meta']['jobs_limit'] ) ):
		$jobs_count = __('Unlimited', APP_TD);
	else :
		$jobs_count = ( $remain_jobs >= 0 ? $remain_jobs : 0 ). ( $remain_job_offers > 0 ? ' (+' . $remain_job_offers . __( ' Free', APP_TD ) . ')' : '' );
	endif;

	if ( $pack['meta']['end_date'] > 0 ) {
		$echo_pack_duration_expire = sprintf( '%s <small>%s</small>',__('Usable before ', APP_TD), appthemes_display_date( $pack['meta']['end_date'], 'date' ) );
	} else {
		$echo_pack_duration_expire =  __('Endless', APP_TD);
	}

	$echo_pack_cost = __('Purchased',APP_TD);
	$echo_pack_jobs = sprintf( '<small>%s</small> %s', $jobs_count, _n(' Job Remaining', ' Jobs Remaining', $jobs_count, APP_TD));

	if ( $pack['meta']['jobs_duration'] ) {
		$echo_pack_jobs .= sprintf(' (%s <small>%s</small>)', __('lasting ', APP_TD), $pack['meta']['jobs_duration'] . __(' days' ,APP_TD) );
	} else {
		$echo_pack_jobs .= sprintf(' (<small>%s</small>)', __('endless', APP_TD) );
	}

	$date_format = get_option('date_format');
	$echo_activation_date = date( $date_format, $pack['meta']['start_date'] );

	$pack['output'] = array (
		'title'				=> $pack['meta']['plan_title'],
		'cost' 				=> $echo_pack_cost,
		'jobs' 				=> $echo_pack_jobs,
		'expiration'		=> $echo_pack_duration_expire,
		'activation_date'	=> $echo_activation_date,
	);

	return $pack;

}

/**
* Build and retrieve all the output data unique for new pack
* @param array $pack The pack data
*
* @return The pack with additional output data
*/
function jr_new_pack_output( $pack ) {

	$price = jr_plan_price( $pack['plan_data'] );
	$echo_pack_cost = $pack['plan_data'][JR_FIELD_PREFIX.'price'] ? appthemes_get_price( $price ) : __('Free',APP_TD);

	if ( $pack['plan_data'][JR_FIELD_PREFIX.'pack_duration'] ) {
		$echo_pack_duration_expire = sprintf('%s <small>%s</small>',__(' usable within ', APP_TD), $pack['plan_data'][JR_FIELD_PREFIX.'pack_duration'].__(' days', APP_TD) );
	} else {
		$echo_pack_duration_expire =  __('Unlimited', APP_TD);
	}

	$echo_pack_jobs = ( empty($pack['plan_data'][JR_FIELD_PREFIX.'jobs_limit']) ? __('Unlimited', APP_TD) : sprintf( '<small>%s</small>', $pack['plan_data'][JR_FIELD_PREFIX.'jobs_limit'] . __(' Jobs', APP_TD)) );
	if ( ! empty($pack['plan_data'][JR_FIELD_PREFIX.'duration']) ) {
		$echo_pack_jobs .= sprintf('%s <small>%s</small>',__(' lasting ', APP_TD), $pack['plan_data'][JR_FIELD_PREFIX.'duration'] . __(' days' ,APP_TD) );
	} else {
		$echo_pack_jobs .= sprintf(' (<small>%s</small>)', __('endless', APP_TD) );
	}

	$pack['output'] = array (
		'title'				=> $pack['plan_data']['title'],
		'cost' 				=> $echo_pack_cost,
		'jobs' 				=> $echo_pack_jobs,
		'expiration' 		=> $echo_pack_duration_expire,
	);

	return $pack;

}

/**
* Build and retrieve user and new packs common output data
* @param string $type The pack type: user|new
* @param array $pack The pack data
* @param (optional) array $display_options Additional display options
*
* @return The pack with additional output data
*/
function jr_pack_commons_output( $type, $pack, $display_options = array() ) {
	global $jr_options;

	$cats_display = ! $jr_options->plan_display_cats ? 'no' : 'all';

	$display_defaults = array (
		'class'			=> '',				// additional CSS class for the pack
		'categories'	=> $cats_display,	// the categories display type
		'order'			=> 'no', 			// if set to 'yes' displays the Pack order
		'selectable'	=> 'yes', 			// should the job pack be user selectable
		'addons'		=> array()
	);
	$display_options = wp_parse_args( $display_options, $display_defaults);

	$display_options['class'][] = 'pack-id-' . $pack['plan_id'];
	$display_options['class'][] = $type . '-pack';

	### categories

	$echo_pack_job_cats = '';

	$cats_enabled = $jr_options->jr_submit_cat_required;

	$job_cats = '';
	$args = array (
		'fields'	 => 'ids',
		'hide_empty' => 0,
	);
	$job_categories = get_terms( APP_TAX_CAT, $args);

	if ( sizeof($pack['plan_cats']) == 0 ) $pack_cats = $job_categories;

	if ( empty($pack['plan_cats']) || count($pack['plan_cats']) == count($job_categories) ) {
		$echo_pack_job_cats = __('All', APP_TD);
	} else {

		foreach ( $pack['plan_cats'] as $job_cat ) {
			if ( $echo_pack_job_cats ) $echo_pack_job_cats .= ', ';
			$echo_pack_job_cats .= '<a href="'.get_term_link( (int)$job_cat, APP_TAX_CAT ).'">'.get_term_by('id', (int)$job_cat, APP_TAX_CAT)->name.'</a>';
		}
	}

	### Offers

	$echo_pack_offers = '';

	$pack_offers = jr_get_pack_all_offers( $pack['plan_data'] );

	foreach ( $pack_offers as $key => $p_offer ):

		$count = _jr_pack_calc_remain_offers( $key, $pack, $type );
		$limit = _jr_pack_calc_offer_limit( $key, $pack );
		$duration = _jr_pack_calc_offer_duration( $key, $pack );

		// limit = 0 with job offers equal 0 offers
		// limit = 0 with addons equals unlimited offers

		$offer_used_class = $remain_text = '';
		if ( $limit > 0 || ( $limit == 0 && JR_FIELD_PREFIX.'job_offers' != $key ) ):

			if ( 'user' == $type ) {
				$remain_text = __(' Remaining',APP_TD);

				if ( $count == 0 && $limit > 0 ) {
					$offer_used_class = 'pack-offer used';
					$count = $limit;
				}

			}

			if ( $count == 0 ) $count = __( 'Unlimited', APP_TD );

			if ( $duration ) $duration = sprintf('(%s)', $duration . ' ' . _n( 'day', 'days', $duration, APP_TD ) );

			$echo_pack_offers .= sprintf('%s <span class="pack-offer %s"><small>%s</small> %s <small><em>%s</em></small></span>', ( $echo_pack_offers ? ', ' : '' ), $offer_used_class, $count, $p_offer['name'], $duration );

		endif;

	endforeach;

	if ( ! $echo_pack_offers ) {
		$echo_pack_offers = __('None',APP_TD);
	} else {
		if ( $remain_text ) {
			$echo_pack_offers .= $remain_text;
		}
	}

	### Access

	$echo_pack_access = '';

	$basic_access = $jr_options->jr_allow_editing ? __( 'Add/Edit/Delete Jobs', APP_TD ) : __( 'Add/Delete Jobs', APP_TD );

	$pack_access = jr_get_pack_access( $pack['plan_data'] );
	$pack_basic_access['job_lister']['name'] = $basic_access;

	$pack_access = array_merge( $pack_basic_access, $pack_access );

	foreach ( $pack_access as $key => $p_access ):
		$echo_pack_access .= ( $echo_pack_access ? ', ' : '' ) . $p_access['name'];
	endforeach;

	$pack['output'] = array_merge( $pack['output'],
		array(
			'type'				=> $type,
			'categories' 		=> $echo_pack_job_cats,
			'offers' 			=> $echo_pack_offers,
			'access' 			=> $echo_pack_access,
			'display_options' 	=> $display_options,
		) );

	return $pack;

}

/**
* Applies a different output to featured addons when displaying pack plans
* @param string $html The HTML output
* @param array $addons The featured addons list
* @param array $args Additional mixed arguments
*
*/
function jr_optional_featured_addons_output( $html, $addons, $args ) {
	global $jr_options;

	if ( 'pack' != $jr_options->plan_type )
		return $html;

	extract( $args );

	if ( empty($pack) || $addons != _jr_featured_addons() )
		return $html;

	$html = '';

	foreach ( $addons as $addon ) :
		$option_html = '';

		if ( ! _jr_addon_valid( $addon, $job->ID ) )
			continue;

		if ( ! empty($plan_data[$addon]) )
			$remain_offers = _jr_pack_calc_remain_offers( $addon, $pack );
		else
			$remain_offers = 0;

		if ( ! $remain_offers && ! $plan_data[$addon.'_limit'] )
			$remain_offers = 99999; // unlimited;

		if ( ! empty($plan_data[ $addon ]) && ! _jr_addon_already_active( $addon, $job->ID ) && $remain_offers ) {
			$option_html = _jr_addon_option( $addon, false, $pack['plan_ref_id'], $echo = false, 'free-' );

			if ( $pack['plan_data'][$addon.'_duration'] != 0 ) {
				$option_html .= sprintf( _n( ' %s may be included FREE for this job for %d day.', ' %s may be included FREE for this job for %d days.', $plan_data[$addon.'_duration'], APP_TD ), APP_Item_Registry::get_title( $addon ), $plan_data[$addon.'_duration'] );
			} else {
				$option_html .= sprintf( __( ' %s may be included FREE for this job for Unlimited days.', APP_TD ), APP_Item_Registry::get_title( $addon ) );
			}
		} else {
				$option_html .= _jr_show_addon_options( $addon, $pack['plan_ref_id'], $job->ID, false );
		}

		if ( $option_html ) {
			$div_html = html('div', array( 'class' => 'featured_option' ), $option_html );
			$html .= html('label', array( 'class' => 'featured_option' ), $div_html );
		}

	endforeach;

	return $html;
}

/**
* Checks if the packs can be sold separately - no jobs attached
*
* @return bool TRUE if it can be sold, FALSE otherwise
*/
function jr_allow_purchase_separate_packs() {
	global $jr_options;
	return ( 'pack' == $jr_options->plan_type && $jr_options->separate_packs && jr_charge_job_listings() );
}

/**
* Checks for expired packs
*
* @return void
*/
function jr_check_packs_expired() {

	$args = array(
		'meta_query' => array(
			array(
				'key' => '_jr_user_packs_active',
				'compare' => 'EXISTS',
			),
		),
	);

	$active_user_packs = new WP_User_Query( $args );

	foreach( $active_user_packs->results as $user ) {
		$user_packs = jr_get_user_packs( $user->ID );

		if ( empty($user_packs) )
			continue;

		$pack_umeta_id = key( $user_packs );
		_jr_maybe_expire_user_pack( $user->ID, $pack_umeta_id );
	}

}

function jr_display_the_user_packs() {
	$display_options = array (
		'selectable' => 'no',
	);

	$plans_data = jr_get_available_plans();

	jr_display_packs( 'user', $plans_data, $display_options );
}