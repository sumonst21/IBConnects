<?php
/**
 * Upgrade functions from JR 1.6.x and lower to JR 1.7.x
 *
 * @version 1.7
 * @author AppThemes
 * @package JobRoller\Admin\Upgrade
 * @copyright 2010 all rights reserved
 *
 */

define( 'JR_DB_UPGRADE_VERSION', '1.7' );

add_action( 'appthemes_first_run', 'jr_upgrade_17', 12 );
add_action( 'appthemes_first_run', 'jr_upgrade_18', 13 );
add_action( 'admin_notices', 'jr_upgrade_notices' );


### Hook Callbacks

function jr_upgrade_notices() {
	if ( $error = get_option( 'jobroller_upgrade_fail' ) ) {
		echo scb_admin_notice( sprintf( __( '<strong>Warning:</strong> There were errors upgrading the theme to %s - \'%s\'. <br/><br/>Please enable the debug log ((JobRoller > Settings > Advanced > Enable Debug Log)) and re-activate the theme.<br/><br/>If the problem persists you\'ll find more details about any errors on the debug log.', APP_TD ), JR_VERSION, $error ), 'error' );
	}
}


### UPGRADE TO 1.8

/**
 * Upgrade to JR 1.8.
 */
function jr_upgrade_18() {

	$jr_version = get_option( 'jobroller_version', 'null' );

	if ( version_compare( $jr_version, '1.7.5' ) <= 0 ) {

		$logo_url = get_option('jr_logo_url');

		$width = $height = '';

		$header_image_data = array();

		if ( ! $logo_url ) {
			// use default logo if user hasn't uploaded any
			$logo_url = get_template_directory_uri() . '/images/logo.png';
			$width = 240;
			$height = 45;
		}

		$default_image_data = array(
			'url'    => $logo_url,
			'width'  => $width,
			'height' => $height,
		);

		// set the custom logo from old option
		set_theme_mod( 'header_image', $logo_url );

		$header_image_data = wp_parse_args( $header_image_data, $default_image_data );

		set_theme_mod( 'header_image_data', (object) $header_image_data );

		_jr_options_upgrade_18();
	}

}

/**
 * On theme upgrade first run get defaults from the options table.
 */
function _jr_options_upgrade_18() {
	global $jr_options;

	$default_settings = $jr_options->get_defaults();

	$settings = array();

	foreach( $default_settings as $key => $default ) {
		if ( 'null' !== ( $value = get_option( $key, 'null' ) ) ) {

			// change old 'yes'/'no' options to 'yes'/''
			$yes_no = _jr_get_deprecated_yes_no_options();
			$yes_no = array_flip( $yes_no );

			if ( ! empty( $yes_no[ $key ] ) ) {
				$value = ( 'no' == $value ?  '' : $value );
			}

			$settings[ $key ] = $value;
		}
	}

	// new options - set value based on legacy settings
	$settings['indeed_publisher_enable'] = ( 'yes' == get_option('jr_indeed_front_page') || 'yes' == get_option('jr_indeed_all_listings') || ( get_option('jr_dynamic_search_results') && 'no' != get_option('jr_dynamic_search_results') ) );

	// merge all old and new defaults
	$settings = wp_parse_args( $settings, $default_settings );

	update_option('_jr_keep_legacy_options_sync', 1 );

	$jr_options->update( $settings );
}


### UPDGRADE TO 1.7

/**
 * Upgrade to JR 1.7.
 */
function jr_upgrade_17() {
	global $wpdb, $jr_log;

	if ( ! _jr_restore_db() ) {
		$curr_version = get_option( 'jobroller_version' );

		if ( ! get_option( 'jobroller_upgrade_fail' ) && ( JR_VERSION == $curr_version || version_compare( get_option( 'jobroller_version' ), JR_DB_UPGRADE_VERSION ) >= 0 ) ) {
			return;
		}

	} else {
		$jr_log->write_log( 'Theme Upgrade: Database was reset to a state prior to JR 1.7' );
	}

	$jr_log->write_log( 'Theme Upgrade: Start' );

	// skip the upgrade if the main Orders legacy table does not exist
	if ( ! $wpdb->get_results("SHOW TABLES LIKE '$wpdb->jr_orders' ") ) {
		$jr_log->write_log( 'Theme Upgrade: Legacy orders table not found - Skipping theme upgrade' );
		return;
	}

	delete_option( 'jobroller_upgrade_fail' );

	_jr_remove_obsolete_pages();

	_jr_upgrade_meta_keys();

	_jr_upgrade_job_duration();

	_jr_upgrade_featured_jobs();

	// upgrade old pricing options to pricing plans
	$pricing_plans = _jr_upgrade_pricing_plans();
	if ( is_wp_error( $pricing_plans ) ) {

		_jr_upgrade_error( $pricing_plans );

	} elseif ( !empty($pricing_plans) ) {

		// process orders after pricing plans are correctly processed
		$orders = _jr_upgrade_orders( $pricing_plans );
		if ( is_wp_error( $orders ) ) {
			_jr_upgrade_error( $orders );
		}

		// assign user packs
		$user_packs = _jr_upgrade_user_packs();
		if ( is_wp_error( $user_packs ) ) {
			_jr_upgrade_error( $user_packs );
		}

	}

	// upgrade old resumes subscription options to subscription plans
	$subscription_plans = _jr_upgrade_subscription_plans();
	if ( is_wp_error( $subscription_plans ) ) {

		_jr_upgrade_error( $subscription_plans );

	} elseif( $subscription_plans ) {

		// create orders for legacy resumes subscriptions
		$subscr_orders = jr_upgrade_create_subscription_orders();
		if ( is_wp_error( $subscr_orders ) ) {
			_jr_upgrade_error( $subscr_orders );
		}

	}

	_jr_options_upgrade_17();

	$error = get_option( 'jobroller_upgrade_fail' );
	$jr_log->write_log( sprintf ( 'Theme Upgrade: Ended %s', ( ! $error ? 'successfully!' : 'with errors' ) ) );
}

// remove all obsolete pages
function _jr_remove_obsolete_pages() {
	global $jr_log;

	$jr_log->write_log( 'Theme Upgrade: Removing Obsolete Pages' );

	if ( $page_id = get_option( 'jr_add_new_confirm_page_id' ) ) {
		wp_delete_post( $page_id );

		$jr_log->write_log( 'Theme Upgrade: Removing Obsolete Pages - OK' );
	}

}

// upgrade old meta keys
function _jr_upgrade_meta_keys() {
	global $wpdb, $jr_log;

	$jr_log->write_log( 'Theme Upgrade: Update Meta Keys' );

	$wpdb->query( "UPDATE $wpdb->postmeta SET meta_key = '_jr_days_expire_reminder_email_sent' WHERE meta_key = 'reminder_email_sent' " );
}

// replace old '_expires' meta key containing the expiration date with the new job duration key
function _jr_upgrade_job_duration() {
	global $jr_log;

	$jr_log->write_log( 'Theme Upgrade: Update Job Durations' );

	$args = array(
		'post_type' => APP_POST_TYPE,
		'meta_key' 	=> '_expires',
		'post_status' => array( 'publish', 'pending', 'trash', 'private', 'future' ), // make sure to include all important post statuses (including trashed posts)
		'nopaging' 	=> true,
	);
	$legacy_expire = new WP_Query( $args );

	foreach ( $legacy_expire->posts as $post ) {
		$expire_date = appthemes_display_date( (int) get_post_meta( $post->ID, '_expires', true ) );
		$duration = appthemes_days_between_dates( $expire_date, $post->post_date, $precision = 0 );

		if ( intval($duration) < 0 ) {
			$duration = intval( get_option( 'jr_jobs_default_expires' ) );	// set the default job duration for expired jobs
			_jr_expire_job( $post->ID );
		}

		update_post_meta( $post->ID, JR_JOB_DURATION_META, $duration );

		$jr_log->write_log( sprintf( 'Theme Upgrade: Update Job Durations - Updated job #%d duration to %d', $post->ID, $duration ) );
	}
}

// update featured jobs using a job category with the new featured meta key
function _jr_upgrade_featured_jobs() {
	global $jr_log;

	$jr_log->write_log( 'Theme Upgrade: Update Featured Jobs' );

	// get the legacy featured category ID
	$featured_job_cat_id = get_option( 'jr_featured_category_id' );

	$args = array(
			'post_type'	=> APP_POST_TYPE,
			'nopaging' 	=> true,
			'tax_query' => array(
					array(
						'taxonomy' 	=> APP_TAX_CAT,
						'field' 	=> 'id',
						'terms' 	=> $featured_job_cat_id,
					),
			),
	);

	$legacy_featured = new WP_Query( $args );

	foreach ( $legacy_featured->posts as $post ) {

		$duration = get_post_meta( $post->ID, JR_JOB_DURATION_META, true );
		if ( ! $duration ){
			$expire_date = appthemes_display_date( (int) get_post_meta( $post->ID, '_expires', true ) );
			$duration = appthemes_days_between_dates( $expire_date, $post->post_date, $precision = 0 );
		}

		if ( intval($duration) < 0 ) $duration = 0;

		jr_add_featured( $post->ID, JR_ITEM_FEATURED_LISTINGS, $duration, $post->post_date );

		$jr_log->write_log( sprintf( 'Theme Upgrade: Update Featured Jobs - Updated featured job #%d duration to %d', $post->ID, $duration ) );
	}
}

// upgrade old pricing plans to the new payments post types
function _jr_upgrade_pricing_plans() {
	global $wpdb, $jr_log;

	$jr_log->write_log( 'Theme Upgrade: Create Pricing Plans' );

	$errors = jr_get_upgrade_error_obj();

	$new_plans = array();

	// check if the site already has plans created
	if ( ! $plans = jr_get_available_plans( array( 'post_status' => 'any' ) ) ) {

		// retrieve the site plans history (old orders could originate from packs or single plans)
		$plan_types = $wpdb->get_col( "SELECT DISTINCT IF( pack_id > 0, 'pack', 'single' ) AS plan_types FROM $wpdb->jr_orders" );

		$jr_log->write_log( 'Theme Upgrade: Create Pricing Plans - Pricing plan types to be created based on legacy orders table: ' . print_r( $plan_types, true ) );

		$active_job_packs = $wpdb->get_var( "SELECT count(1) FROM $wpdb->jr_job_packs" );

		// default plan type
		$active_plan_type = $active_job_packs ? 'pack' : 'single';

		if ( 'pack' == $active_plan_type ) {

			// check if site was using packs
			$legacy_packs = $wpdb->get_results( "
				SELECT * FROM $wpdb->jr_job_packs
				WHERE id NOT IN ( SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '_jr_legacy_plan_id' ) "
			);

		}

		// if site used 'single' plans at least once, create a plan based on legacy settings
		if ( in_array( 'single', $plan_types ) ) {

			$jr_log->write_log( 'Theme Upgrade: Create Pricing Plans - Found single prices to be upgraded to plans' );

			$title =  __( 'Job Plan', APP_TD );
			$description = __( 'Single price plan.', APP_TD );
			$post_categories = wp_list_pluck( get_terms( APP_TAX_CAT, array( 'hide_empty' => false ) ), 'term_id' );

			$plan = array(
				'post_title' 		=> $title,
				'post_name' 		=> $title,
				'post_type' 		=> APPTHEMES_PRICE_PLAN_PTYPE,
				'post_status'		=> 'single' == $active_plan_type ? 'publish' : 'pending',
			);
			$plan_id = wp_insert_post( $plan, true );
			if ( is_wp_error($plan_id) ) {
				$jr_log->write_log( sprintf( 'Upgrade Pricing Plan: Error creating legacy Plan - %s - "%s"', $title, $plan_id->get_error_message() ) );

				$errors->add( 'upgrade-error', sprintf( 'Upgrade Pricing Plan: Error creating legacy Plan - %s -',  $title ) );
				return $errors;
			}

			wp_set_post_terms( $plan_id, $post_categories, APP_TAX_CAT );

			add_post_meta( $plan_id, 'title', $title, true );
			add_post_meta( $plan_id, JR_FIELD_PREFIX.'description', $description, true );
			add_post_meta( $plan_id, JR_FIELD_PREFIX.'price', get_option( 'jr_jobs_listing_cost', 0 ), true );
			add_post_meta( $plan_id, JR_FIELD_PREFIX.'relist_price', get_option( 'jr_jobs_relisting_cost', 0 ), true );
			add_post_meta( $plan_id, JR_FIELD_PREFIX.'duration', get_option( 'jr_jobs_default_expires', 30 ), true );
			add_post_meta( $plan_id, JR_FIELD_PREFIX.'limit', 0, true );

			// set a meta key to mark this legacy plan as processed
			add_post_meta( $plan_id, '_jr_legacy_plan_id', 0, true );

			// store the key, value pairs for the old plan ID (defaults to 0), and plan name
			$plan = get_post( $plan_id );
			$new_plans[0] = array(
				'plan_name' => $plan->post_name,
				'plan_id'	=> $plan->ID,
			);

			$jr_log->write_log( sprintf( 'Theme Upgrade: Create Pricing Plans - Single Pricing Plan #%d \'%s\'', $plan->ID, $plan->post_name ) );
		}

		// if site used 'pack' plans at least once, create a plan based on legacy settings
		if ( 'pack' == $active_plan_type ) {

			if ( $legacy_packs )
				$jr_log->write_log( 'Theme Upgrade: Create Pricing Plans - Found legacy packs to be upgraded to plans: ' . print_r( $legacy_packs, true ) );

			foreach( $legacy_packs as $legacy_pack ) {

				$title =  $legacy_pack->pack_name;
				$description = $legacy_pack->pack_description;
				$usage_limit = $legacy_pack->pack_cost ? 0 : get_option( 'jr_packs_free_limit', 0 );

				$plan = array(
					'post_title' 	=> $title,
					'post_name' 	=> $title,
					'post_type' 	=> APPTHEMES_PRICE_PLAN_PTYPE,
					'post_status'	=> 'pack' == $active_plan_type ? 'publish' : 'pending',
					'menu_order'	=> intval($legacy_pack->pack_order),
				);

				$plan_id = wp_insert_post( $plan, true );
				if ( is_wp_error($plan_id) ) {
					$jr_log->write_log( sprintf( 'Upgrade Pricing Plan: Error creating legacy Pack Plan - %s - "%s"', $title, $plan_id->get_error_message() ) );

					$errors->add( 'upgrade-error', sprintf( 'Upgrade Pricing Plan: Error creating legacy Pack Plan - %s -', $title ) );
					return $errors;
				}

				$post_categories = explode( ',', $legacy_pack->job_cats );
				if ( empty($post_categories[0]) ) {
					$post_categories = wp_list_pluck( get_terms( APP_TAX_CAT, array( 'hide_empty' => false ) ), 'term_id' );
				}

				wp_set_post_terms( $plan_id, $post_categories, APP_TAX_CAT );

				add_post_meta( $plan_id, 'title', $title, true );
				add_post_meta( $plan_id, JR_FIELD_PREFIX.'description', $description, true );
				add_post_meta( $plan_id, JR_FIELD_PREFIX.'price', intval($legacy_pack->pack_cost), true );
				add_post_meta( $plan_id, JR_FIELD_PREFIX.'relist_price', get_option( 'jr_jobs_relisting_cost', 0 ), true );
				add_post_meta( $plan_id, JR_FIELD_PREFIX.'duration', $legacy_pack->job_duration, true );
				add_post_meta( $plan_id, JR_FIELD_PREFIX.'limit', $usage_limit, true );
				add_post_meta( $plan_id, JR_FIELD_PREFIX.'pack_duration', $legacy_pack->pack_duration, true );
				add_post_meta( $plan_id, JR_FIELD_PREFIX.'jobs_limit', $legacy_pack->job_count, true );
				add_post_meta( $plan_id, JR_FIELD_PREFIX.'job_offers_limit', $legacy_pack->job_offers, true );

				### update included addons

				// featured addons

				if ( $legacy_pack->feat_job_offers ) {
					$addon = JR_ITEM_FEATURED_LISTINGS;
					add_post_meta( $plan_id, $addon, 'on', true );
					add_post_meta( $plan_id, $addon.'_duration', $legacy_pack->job_duration, true );
					add_post_meta( $plan_id, $addon.'_limit', $legacy_pack->feat_job_offers, true );
				}

				// resume addons

				$resumes_relation = array (
					'resume_browse' => JR_ITEM_BROWSE_RESUMES,
					'resume_view'	=> JR_ITEM_VIEW_RESUMES,
				);

				$resumes_addons = explode( ',', $legacy_pack->access );
				foreach ( $resumes_addons as $resumes_addon ) {
					if ( 'none' == $resumes_addon ) continue;
					$addon = $resumes_relation[$resumes_addon];
					add_post_meta( $plan_id, $addon, 'on', true );
					add_post_meta( $plan_id, $addon.'_duration', $legacy_pack->pack_duration, true );
					add_post_meta( $plan_id, $addon.'_limit', 0, true );
				}

				// set a meta key to mark this legacy pack as processed
				add_post_meta( $plan_id, '_jr_legacy_plan_id', $legacy_pack->id, true );

				// store the key, value pairs for the old pack ID, and new plan name
				$plan = get_post( $plan_id );
				$new_plans[$legacy_pack->id] = array(
					'plan_name' => $plan->post_name,
					'plan_id'	=> $plan->ID,
				);

				$jr_log->write_log( sprintf( 'Theme Upgrade: Create Pricing Plans - Pack Pricing Plan #%d \'%s\' / old ID #%d (jr_job_packs) ', $plan->ID, $plan->post_name, $legacy_pack->id) );
			}
		}

		// store a temporary transient to help set the default plan type on theme activation
		set_transient( 'jr_plan_type', $active_plan_type, 60 * 60 * 24 );

	} else {

		// retrieve plans created from legacy values in case something went wrong the first time
		foreach( $plans as $key => $plan ){
			$legacy_plan_id = get_post_meta( $plan['post_data']->ID, '_jr_legacy_plan_id', true );

			if ( $legacy_plan_id >= 0 ) {
				$new_plans[$legacy_plan_id] = array(
					'plan_name' => $plan['post_data']->post_name,
					'plan_id'	=> $plan['post_data']->ID,
				);
			}
		}

		$jr_log->write_log( 'Theme Upgrade: Create Pricing Plans - Retrieved Legacy Pricing Plans' );
	}
	return $new_plans;
}

// upgrade old orders stored on separate tables to new payments framework
function _jr_upgrade_orders( $new_plans ) {
	global $wpdb, $jr_log;

	$jr_log->write_log( 'Theme Upgrade: Create Orders' );

	$errors = jr_get_upgrade_error_obj();

	$jr_log->write_log( sprintf( 'Theme Upgrade: Create Orders - New Plans: %s', print_r($new_plans, true) ) );

	if ( empty($new_plans) )
		return;

	// retrieve all legacy orders not yet upgraded
	$legacy_orders = $wpdb->get_results( "
		SELECT * FROM $wpdb->jr_orders
		WHERE id NOT IN ( SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '_jr_legacy_order_id' )
	" );

	// old => new
	$gateways_relation = array(
		'paypal' 		=> 'paypal',
		'authorize.net' => 'authorize-net',
		'2checkout' 	=> '2checkout',
		'google' 		=> 'google-wallet',
		'manual' 		=> 'bank-transfer',
	);

	// old => new
	$status_relation = array(
		'completed'			=> APPTHEMES_ORDER_COMPLETED,
		'pending_payment'	=> APPTHEMES_ORDER_PENDING,
		'cancelled'			=> APPTHEMES_ORDER_FAILED
	);

	$errors_total = 0;
	foreach ( $legacy_orders as $legacy_order ) {

		// payment type
		$payment_type = strtolower( $legacy_order->payment_type );

		if ( $payment_type && isset($gateways_relation[$payment_type]) ) {
			$payment_type = $gateways_relation[$payment_type];
		} else {
			$payment_type = __( 'N/A', APP_TD );
		}

		// status
		if ( ! isset($status_relation[$legacy_order->status]) ) {
			$legacy_order->status = 'cancelled';
		}

		$draft_order = new APP_Draft_Order();
		$draft_order->set_gateway( $payment_type );
		$draft_order->set_currency( get_option( 'jr_jobs_paypal_currency' ) );

		$order = appthemes_new_order( $draft_order );

		// post id attached to the order
		if ( $legacy_order->job_id ) {
			$post_id = $legacy_order->job_id;

			$jr_log->write_log( sprintf( 'Theme Upgrade: Create Orders - Order #%d - Found post_id #%d assigned to legacy Order', $order->get_id(), $post_id ) );
		} else {
			$post_id = 0;
		}

		if ( empty($post_id) ) $post_id = $order->get_id();

		$plan_key = $legacy_order->pack_id ? $legacy_order->pack_id : 0;
		$plan_name = isset($new_plans[$plan_key]['plan_name']) ? $new_plans[$plan_key]['plan_name'] : '';

		$order->add_item( $plan_name, $legacy_order->cost, $post_id );

		if ( $post_id != $order->get_id() ) {
			// add legacy featured jobs as addons to the current Order
			$featured_job_cat_id = get_option( 'jr_featured_category_id' );
			if ( $featured_job_cat_id ) {
				$job_terms = wp_get_object_terms( $post_id, APP_TAX_CAT, array('fields' => 'ids') );
				if ( in_array( $featured_job_cat_id, $job_terms ) ) {
					$price = get_option( 'jr_cost_to_feature', 0 );
					$order->add_item( JR_ITEM_FEATURED_LISTINGS, $price, $post_id );
				}
			}

			// restore trashed posts from cancelled orders and set their status to 'expired', instead
			if ( APPTHEMES_ORDER_FAILED == $status_relation[$legacy_order->status] ) {
				wp_untrash_post( $post_id );
				_jr_expire_job( $post_id, $canceled = true, 'order_failed' );
			}
		}

		$order_post_data = array(
			'ID' 			=> $order->get_id(),
			'post_type' 	=> APPTHEMES_ORDER_PTYPE,
			'post_status' 	=> $status_relation[$legacy_order->status],
			'post_author'	=> $legacy_order->user_id,
			'post_date'		=> $legacy_order->order_date,
		);
		$updated_post_id = wp_insert_post( $order_post_data, true );
		if ( is_wp_error( $updated_post_id ) ) {
			$jr_log->write_log( sprintf( 'Upgrade Orders: Error updating Order - %d - "%s"', $title, $updated_post_id->get_error_message() ) );

			$errors->add( 'upgrade-error', sprintf( 'Upgrade Orders: Error updating Order - %d -', $order->get_id() ) );
			$errors_total++;
			continue;
		}

		$jr_log->write_log( sprintf( 'Theme Upgrade: Create Orders - Assigned post_id #%d to Order #%d ', $post_id, $order->get_id() ) );

		// set a meta key to mark this legacy order as processed
		add_post_meta( $order->get_id(), '_jr_legacy_order_id', $legacy_order->id, true );

		// store the legacy order_key to identify active PayPal orders
		add_post_meta( $order->get_id(), '_jr_legacy_order_key', $legacy_order->order_key, true );

		$jr_log->write_log( sprintf( 'Theme Upgrade: Create Orders - Order #%d created from legacy Order #%d (Order Key: %s) [status: %s]', $order->get_id(), $legacy_order->id, $legacy_order->order_key, $status_relation[$legacy_order->status] ) );
	}

	if ( $errors_total ) {
		$errors->add( 'upgrade-error', sprintf( 'Could not upgrade all legacy orders' ) );
		return $errors;
	}
	return true;
}

// upgrade old user packs stored on separate tables to new payments framework
function _jr_upgrade_user_packs() {
	global $wpdb, $jr_log;

	$jr_log->write_log( 'Theme Upgrade: Assign User Packs - Searching for legacy User Packs' );

	$errors = jr_get_upgrade_error_obj();

	// retrieve upgraded pack plans ( pack_id > 0 )
	$args = array(
		'meta_key'			=> '_jr_legacy_plan_id',
		'meta_value' 		=> '0',
		'meta_compare' 		=> '>',
	);
	$plans = jr_get_available_plans( $args );
	if ( ! $plans )
		return;

	// relate the old pack ID with the new plan ID
	foreach( $plans as $plan ) {
		$plan_pack[$plan['_jr_legacy_plan_id'][0]] = $plan['post_data']->ID;
	}

	// retrieve all active legacy user packs not yet assigned to the user
	$legacy_user_packs = $wpdb->get_results( "
		SELECT * FROM $wpdb->jr_customer_packs a
		WHERE ( pack_expires > NOW() OR pack_expires = NULL OR pack_expires = '0000-00-00 00:00:00' )
		AND ( jobs_count+job_offers_count < jobs_limit+job_offers OR jobs_limit = 0 )
		AND id NOT IN ( SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = '_jr_legacy_customer_pack' AND user_id = a.user_id )
	" );

	$errors_total = 0;
	foreach ( $legacy_user_packs as $legacy_user_pack ) {
		$plan_id = $plan_pack[$legacy_user_pack->pack_id];

		$jr_log->write_log( sprintf( 'Theme Upgrade: Assign User Packs - Preparing to assign Plan ID #%d', $plan_id ) );

		// if the original Packs were removed we are not able to assign them to users
		if ( ! $plan = get_post( $plan_id ) )
			continue;

		$jr_log->write_log( sprintf( 'Theme Upgrade: Assign User Packs - Assigning Plan ID #%d to user ID #%d', $plan_id, $legacy_user_pack->user_id ) );

		// give the pack to the user
		$plan_umeta_id = jr_give_pack_to_user( $legacy_user_pack->user_id, $plan_id, $order_id = 0, strtotime($legacy_user_pack->pack_purchased) );
		if ( !$plan_umeta_id ) {
			$jr_log->write_log( sprintf( 'Theme Upgrade: Assign User Packs - Failed assigning Plan ID #%d to user ID #%d. Plan meta ID is empty', $plan_id, $legacy_user_pack->user_id ) );
			$errors_total++;
			continue;
		}

		$jr_log->write_log( sprintf( 'Theme Upgrade: Assign User Packs - Assigned Plan ID #%d to user ID #%d with meta ID #%d', $plan_id, $legacy_user_pack->user_id, $plan_umeta_id ) );

		// retrieve the user pack array to update value counts
		$user_pack = _jr_user_pack_meta( $legacy_user_pack->user_id, $plan_umeta_id );

		// add offers
		if ( $legacy_user_pack->job_offers_count ) {
			$offers = jr_pack_job_offers();
			$key = key($offers);
			$user_pack[$key.'_count'] = $legacy_user_pack->feat_job_offers_count;
		}

		// add featured addons offers
		if ( $legacy_user_pack->feat_job_offers_count ) {
			$user_pack[JR_ITEM_FEATURED_LISTINGS.'_count'] = $legacy_user_pack->feat_job_offers_count;
		}

		// add resume access addons offers
		$access_relation = array(
			'resume_view' => JR_ITEM_VIEW_RESUMES,
			'resume_browse' => JR_ITEM_BROWSE_RESUMES,
		);

		$access = explode( ',', $legacy_user_pack->access );
		if ( 'none' != $access[0] ) {
			foreach( $access as $access_type ) {
				$duration = appthemes_days_between_dates( $legacy_user_pack->pack_expires, $legacy_user_pack->pack_purchased );
				$user_pack[$access_relation[$access_type].'_duration'] = $duration;
				jr_add_resumes_access( $legacy_user_pack->user_id, $access_relation[$access_type], $duration );
			}
		}

		// update the jobs count
		$user_pack['jobs_count'] = $legacy_user_pack->jobs_count;

		update_user_meta( $legacy_user_pack->user_id, '_jr_user_pack-' . $plan_umeta_id, $user_pack );

		add_user_meta( $legacy_user_pack->user_id, '_jr_legacy_customer_pack', $legacy_user_pack->id );

		$jr_log->write_log( sprintf( 'Theme Upgrade: Assign User Packs - Assigned new meta plan ID  #%d / old ID = #%d (jr_customer_packs) to user #%d', $plan_umeta_id, $legacy_user_pack->id, $legacy_user_pack->user_id ) );

	}

	if ( $errors_total ) {
		$errors->add( 'upgrade-error', sprintf( 'Could not assign all legacy User Packs', APP_TD ) );
		return $errors;
	}
	return true;
}

// upgrade old resumes subscriptions plans to the new payments post types
function _jr_upgrade_subscription_plans() {
	global $wpdb, $jr_log;

	$jr_log->write_log( 'Theme Upgrade: Create Resumes Subscription Plans' );

	$errors = jr_get_upgrade_error_obj();

	// check if the site already has plans created
	if ( ! $plans = jr_get_available_plans( array( 'post_type' => APPTHEMES_RESUMES_PLAN_PTYPE, 'post_status' => 'any' ) ) ) {

		$legacy_plan_parts = array( 'access' );
		if ( 'yes' == get_option( 'jr_resume_allow_trial' ) ) {
			$legacy_plan_parts[] = 'trial';
		}

		foreach( $legacy_plan_parts as $plan_part ) {

			if ( 'access' == $plan_part ) {
				$title = __( 'Browse/View Resumes Subscription', APP_TD );
				$description = __( 'Complete access to our Resumes database.', APP_TD );
			} else {
				$title = __( 'Trial Subscription', APP_TD );
				$description = __( 'Trial our Resumes service for a limited time.', APP_TD );
			}

			$plan = array(
				'post_title' 	=> $title,
				'post_type' 	=> APPTHEMES_RESUMES_PLAN_PTYPE,
				'post_status'	=> 'publish',
			);
			$plan_id = wp_insert_post( $plan, true );
			if ( is_wp_error($plan_id) ) {
				$jr_log->write_log( sprintf( 'Upgrade Subscription Plan: Error creating Subscription plan - %s - "%s"', $title, $plan_id->get_error_message() ) );

				$errors->add( 'upgrade-error', sprintf( 'Upgrade Subscription Plan: Error creating Subscription plan - %s -', $title ) );
				return $errors;
			}

			$duration = get_option( 'jr_resume_' . $plan_part . '_length' );
			$duration_unit = get_option( 'jr_resume_' . $plan_part . '_unit' );

			if ( $duration && $duration_unit ) {

				$units_desc = array(
					'D' => 1,
					'M' => 30,
					'W' => 7,
					'Y' => 365
				);

				$new_duration = $duration * $units_desc[$duration_unit];

				add_post_meta( $plan_id, 'title', $title, true );
				add_post_meta( $plan_id, JR_FIELD_PREFIX.'description', $description, true );
				add_post_meta( $plan_id, JR_FIELD_PREFIX.'price', intval(get_option( 'jr_resume_' . $plan_part . '_cost', 0 )), true );
				add_post_meta( $plan_id, JR_FIELD_PREFIX.'duration', $new_duration, true );
				add_post_meta( $plan_id, JR_FIELD_PREFIX.'limit', 0, true );

				if ( 'trial' == $plan_part ) {
					add_post_meta( $plan_id, JR_FIELD_PREFIX.'trial', 'on', true );
					update_post_meta( $plan_id, JR_FIELD_PREFIX.'limit', 1 );
				} else {
					add_post_meta( $plan_id, JR_FIELD_PREFIX.'recurring', 1, true );
				}

				// update the users '_valid_resume_subscription' meta key with the new plan ID
				$wpdb->query( "UPDATE $wpdb->usermeta SET meta_value = $plan_id WHERE meta_key = '_valid_resume_subscription' AND meta_value = 1 " );

				// set a meta key to mark this legacy plan as processed
				add_post_meta( $plan_id, '_jr_legacy_subscription_plan', 1, true );

				$jr_log->write_log( sprintf( 'Theme Upgrade: Create Resumes Subscription Plans - Created Plan #%d', $plan_id ) );
			}

		}

	}
	return true;
}

// create orders for legacy subscriptions
function jr_upgrade_create_subscription_orders() {
	global $jr_log;

	$jr_log->write_log( 'Theme Upgrade: Create Subscriptions Orders' );

	$args = array(
		'post_type' => APPTHEMES_RESUMES_PLAN_PTYPE,
		'post_status' => 'any',
		'meta_query' => array(
			array(
				'key' => '_jr_legacy_subscription_plan',
				'compare' => 'EXISTS'
			),
		),
	);
	$subscription_plans = jr_get_available_plans( $args );

	if ( ! $subscription_plans ) {
		return;
	}

	$jr_log->write_log( sprintf( 'Theme Upgrade: Create Subscriptions Orders - Found valid subscription plans to assign to Orders' ) );

	foreach( $subscription_plans as $subscription_plan ) {
		if ( !empty($subscription_plan[JR_FIELD_PREFIX.'trial'][0]) ) {
			$plans['trial'] = $subscription_plan;
		} else {
			$plans['subscription'] = $subscription_plan;
		}
	}

	$args = array(
		'meta_query' => array(
			array(
				'key' => '_valid_resume_trial',
				'compare' => 'EXISTS',
			),
		),
	);
	$trial_subscribers = new WP_User_Query( $args );

	if ( !empty($plans['trial']) ) {

		$jr_log->write_log( sprintf( 'Theme Upgrade: Create Subscriptions Orders - Found %d trial subscribers', $trial_subscribers->total_users ) );

		foreach( $trial_subscribers->results as $subscriber ) {
			// make sure users who already used trial are correctly assigned the correct usage limit
			$user_plans_history = _jr_user_plans_history( $subscriber->ID );
			$user_plans_history[$plans['trial']['post_data']->ID] = 1;
			update_user_meta( $subscriber->ID, '_jr_user_plans_history', $user_plans_history );

			$jr_log->write_log( sprintf( 'Theme Upgrade: Create Subscriptions Orders - Assigned trial usage to user #%d ', $subscriber->ID ) );
		}
	}

	$args = array(
		'meta_query' => array(
			array(
				'key' => '_valid_resume_subscription_order',
				'compare' => 'EXISTS'
			),
			array(
				'key' => '_jr_legacy_subscription_id',
				'compare' => 'NOT EXISTS'
			),
		),
	);
	$paying_subscribers = new WP_User_Query( $args );

	$jr_log->write_log( sprintf( 'Theme Upgrade: Create Subscriptions Orders - Pending Subscribers: %d ', $paying_subscribers->total_users ) );

	$errors_total = 0;
	foreach( $paying_subscribers->results as $subscriber ) {
		$user_meta = get_user_meta( $subscriber->ID );

		$legacy_order_id = $user_meta['_valid_resume_subscription_order'][0]; // contains a date

		$plan_type = !empty($user_meta['_valid_resume_trial'][0]) ? 'trial' : 'subscription';
		$plan = $plans[$plan_type];

		$draft_order = new APP_Draft_Order();
		$draft_order->set_gateway( 'paypal' );
		$draft_order->set_currency( get_option( 'jr_jobs_paypal_currency' ) );

		$order = appthemes_new_order( $draft_order );

		$order->add_item( $plan['post_data']->post_name, $plan[JR_FIELD_PREFIX.'price'][0], $order->get_id() );

		if ( 'trial' != $plan_type && 'automatic' == get_option( 'jr_resume_subscr_recurr_type' ) ) {
			$recurring_period = $plan[JR_FIELD_PREFIX.'duration'][0];
			if ( _jr_is_recurring_available() ) {
				$order->set_recurring_period( $recurring_period );
			}
		}

		$order_post_data = array(
			'ID' 			=> $order->get_id(),
			'post_type' 	=> APPTHEMES_ORDER_PTYPE,
			'post_status' 	=> APPTHEMES_ORDER_PENDING,
			'post_author'	=> $subscriber->ID,
			'post_date'		=> date( 'Y-m-d H:i:s', $legacy_order_id ),
		);

		$new_order = wp_insert_post( $order_post_data, true );
		if ( is_wp_error( $new_order ) ) {
			$jr_log->write_log( sprintf( 'Error creating subscription order - #%d for user #%d - "%s"', $order->get_id(), $subscriber->ID, $new_order->get_error_message() ) );
			$errors_total++;
			continue;
		}

		// store the legacy subscription identifier on the user meta
		add_user_meta( $subscriber->ID, '_jr_legacy_subscription_id', $legacy_order_id );

		// store the legacy order_key to identify active PayPal subscription orders
		add_post_meta( $order->get_id(), '_jr_legacy_order_key', $legacy_order_id, true );

		$jr_log->write_log( sprintf( 'Theme Upgrade: Create Subscriptions Orders - Created Order #%d', $order->get_id() ) );
	}

	if ( $errors_total ) {
		$errors->add( 'upgrade-error', sprintf( 'Could not create all orders for legacy subscriptions' ) );
		return $errors;
	}
	return true;
}

/**
 * Return errors
 * @return void
 */
function jr_get_upgrade_error_obj(){
	static $errors;

	if ( !$errors ){
		$errors = new WP_Error();
	}
	return $errors;
}

// store errors on log file
function _jr_upgrade_error( $errors ) {
	if ( $errors->get_error_code() ) {
		update_option( 'jobroller_upgrade_fail', $errors->get_error_message() );
	}
}

// restore DB to JR 1.6.5
function _jr_restore_db() {
	global $wpdb;

	// set to TRUE to reset database to 1.6.5
	// set to FALSE to skip RESET
	$reset_to_165 = false;

	if ( $reset_to_165 ) {

		$wpdb->query( "
			DELETE FROM $wpdb->posts
			WHERE
			ID IN ( SELECT post_id FROM $wpdb->postmeta where meta_key  in( '_jr_legacy_plan_id', '_jr_legacy_subscription_plan', '_jr_legacy_order_id', '_jr_legacy_order_key' ) );
		" );

		$wpdb->query( "
			DELETE FROM $wpdb->postmeta
			WHERE meta_key in( '_jr_legacy_plan_id', '_jr_legacy_subscription_plan', '_jr_legacy_order_id', '_jr_legacy_order_key' );
		" );

		$wpdb->query( "
			DELETE FROM $wpdb->usermeta
			WHERE meta_key IN ( '_jr_legacy_customer_pack', '_jr_legacy_subscription_id' )
			OR meta_key LIKE '_jr_user_pack%' OR meta_key LIKE '_jr_browse%' OR meta_key LIKE '_jr_view%' OR meta_key = '_jr_user_plans_history';
		" );

		$wpdb->query( "
			UPDATE $wpdb->usermeta SET meta_value = 1 WHERE meta_key = '_valid_resume_subscription' AND meta_value > 0
		" );

		$wpdb->query( "
			UPDATE $wpdb->postmeta SET meta_key = 'reminder_email_sent' WHERE meta_key = '_jr_days_expire_reminder_email_sent'
		");

		$wpdb->query( "
			DELETE FROM $wpdb->options WHERE option_name = 'jr_options';
		" );

		$wpdb->query( "
			UPDATE $wpdb->options SET option_value = '1.6.5' WHERE option_name = 'jobroller_version';
		" );

		$wpdb->query( "
			UPDATE $wpdb->posts set post_status='publish' WHERE post_status = 'expired';
		" );

	}

	return $reset_to_165;
}

/**
 * Retrieve default options based on legacy settings.
 */
function _jr_options_upgrade_17() {

	$sep_relation = array( 'comma' => ',', 'decimal' => '.' );
	$featured_price = get_option( 'jr_cost_to_feature', 0 );
	$plan_type = get_transient( 'jr_plan_type' );

	$gateways = array();

	if ( $paypal_email = get_option( 'jr_jobs_paypal_email' ) ) {
		$gateways = array(
			'enabled' => array( 'paypal' => 'yes' ),
			'paypal' => array (
				'email_address'		=> $paypal_email,
				'ipn_enabled' 		=> 'no' == get_option( 'jr_enable_paypal_ipn', 'no' ) ? '' : 'on',
				'sandbox_enabled'	=> 'no' == get_option( 'jr_use_paypal_sandbox', 'no' ) ? '': 'on',
				'business_account'	=> 'manual' == get_option( 'jr_resume_subscr_recurr_type' ) ? '' : 'on',
			),
		);
	} else {
		$gateways = array(
			'enabled' => array(),
		);
	}

	$legacy_options = array(
		'job_duration' 			=> get_option( 'jr_jobs_default_expires', 30 ),
		'featured_price' 		=> $featured_price,
		'featured_enabled' 		=> (bool) $featured_price,
		'separate_packs'		=> get_option( 'jr_packs_dashboard_buy', 'no' ),
		'plan_type' 			=> $plan_type ? $plan_type : 'single',
		'currency_decimal_sep' 	=> $sep_relation[ get_option( 'jr_curr_decimal_separator', '.' ) ],
		'currency_thousands_sep'=> $sep_relation[ get_option( 'jr_curr_thousands_separator', ',' ) ],
		'currency_position'		=> get_option( 'jr_curr_symbol_pos', 'left' ),
		'currency_code'			=> get_option( 'jr_jobs_paypal_currency', 'USD' ),
		'gateways'				=> $gateways,
		'plan_display_cats'		=> get_option( 'jr_packs_job_categories', 'no' ),
		'jr_jobs_charge'		=> _jr_legacy_option_charge_jobs(),
	);

	foreach( $legacy_options as $option => $value ) {
		update_option( $option, $value );
	}

}

/**
 * Compatibility method for older versions.
 */
function _jr_legacy_option_charge_jobs() {
   global $wpdb;

   $active_job_packs = false;

   $single_pricing = get_option( 'jr_jobs_listing_cost' );

   if ( ! $single_pricing ) {
	   $active_job_packs = (bool) $wpdb->get_results( "SHOW TABLES LIKE '$wpdb->jr_job_packs' " ) && (bool) $wpdb->get_var( "SELECT count(1) FROM $wpdb->jr_job_packs" );
   }
   return ( (bool) $active_job_packs || (bool) $single_pricing ? 'yes' : 'no' );
}
