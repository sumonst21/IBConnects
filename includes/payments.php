<?php
define( 'APPTHEMES_PRICE_PLAN_PTYPE', 'pricing-plan' );
define( 'APPTHEMES_RESUMES_PLAN_PTYPE', 'resumes-pricing-plan' );
define( 'JR_ORDER_PACK_CONNECTION', 'pack-connection' );

add_action( 'appthemes_first_run', 'jr_payments_setup_upgrade' );

add_action( 'init', 'jr_pricing_setup' );
add_action( 'init', 'jr_resumes_pricing_setup' );

add_action( 'admin_menu', 'jr_pricing_add_menu', 11 );

add_filter( 'posts_clauses', 'jr_sales_time_span_sql', 10, 2 );
add_filter( 'appthemes_order_item_posts_types', 'jr_transaction_ptype' );
add_filter( 'parent_file', 'jr_pricing_menu_edit_page_menu_workaround' );

add_action( 'post_row_actions', '_jr_order_list_remove_actions', 15, 2 );

// legacy payments actions >>>
add_action( 'init', 'jr_init_legacy_payments_ipn_listener', 99 );
add_action( 'jr_valid_legacy_paypal_ipn_request', 'jr_process_legacy_payment' );
add_action( 'jr_valid_legacy_paypal_subscription_ipn_request', 'jr_process_legacy_subscription' );
// <<< legacy payments actions

add_action( 'after_setup_theme', 'jr_load_recurring', 998 );

if ( is_admin() ) {
	add_filter( 'the_title', 'jr_pricing_modify_title', 10, 2 );
}

function jr_load_recurring() {
	require_once( dirname( __FILE__ ) . '/recurring/load.php' );
}

/**
 * Add the 'transaction' post type to the list of valid p2p connections post types.
 */
function jr_transaction_ptype( $ptypes ) {
	$ptypes[] = APPTHEMES_ORDER_PTYPE;
	return $ptypes;
}

function jr_pricing_setup(){

	$labels = array(
		'name' => __( 'Job Plans', APP_TD ),
		'singular_name' => __( 'Job Plan', APP_TD ),
		'add_new' => __( 'Add New', APP_TD ),
		'add_new_item' => __( 'Add New Plan', APP_TD ),
		'edit_item' => __( 'Edit Plan', APP_TD ),
		'new_item' => __( 'New Plan', APP_TD ),
		'view_item' => __( 'View Plan', APP_TD ),
		'search_items' => __( 'Search Plans', APP_TD ),
		'not_found' => __( 'No Plans found', APP_TD ),
		'not_found_in_trash' => __( 'No Plans found in Trash', APP_TD ),
		'parent_item_colon' => __( 'Parent Plan:', APP_TD ),
		'menu_name' => __( 'Job Plans', APP_TD ),
	);

	$args = array(
		'labels' => $labels,
		'hierarchical' => false,
		'supports' => array( 'page-attributes' ),
		'taxonomies' => array( APP_TAX_CAT ),
		'public' => false,
		'show_ui' => true,
		'show_in_menu' => false,
	);

	register_post_type( APPTHEMES_PRICE_PLAN_PTYPE, $args );

	$plans = new WP_Query(array( 'post_type' => APPTHEMES_PRICE_PLAN_PTYPE, 'post_status' => 'any', 'nopaging' => 1));
	foreach( $plans->posts as $plan){
		$data = get_post_custom( $plan->ID );
		if( isset( $data['title']) )
			APP_Item_Registry::register( $plan->post_name, $data['title'][0] );
	}

}

function jr_resumes_pricing_setup(){

	$labels = array(
		'name' => __( 'Resume Plans', APP_TD ),
		'singular_name' => __( 'Resume Plan', APP_TD ),
		'add_new' => __( 'Add New', APP_TD ),
		'add_new_item' => __( 'Add New Plan', APP_TD ),
		'edit_item' => __( 'Edit Plan', APP_TD ),
		'new_item' => __( 'New Plan', APP_TD ),
		'view_item' => __( 'View Plan', APP_TD ),
		'search_items' => __( 'Search Plan', APP_TD ),
		'not_found' => __( 'No Plans found', APP_TD ),
		'not_found_in_trash' => __( 'No Plans found in Trash', APP_TD ),
		'parent_item_colon' => __( 'Parent Plan:', APP_TD ),
		'menu_name' => __( 'Resume Plans', APP_TD ),
	);

	$args = array(
		'labels' => $labels,
		'hierarchical' => false,
		'supports' => array( 'page-attributes' ),
		'public' => false,
		'show_ui' => true,
		'show_in_menu' => false,
	);

	register_post_type( APPTHEMES_RESUMES_PLAN_PTYPE, $args );

	$plans = new WP_Query(array( 'post_type' => APPTHEMES_RESUMES_PLAN_PTYPE, 'nopaging' => 1));
	foreach( $plans->posts as $plan){
		$data = get_post_custom( $plan->ID );
		if( isset( $data['title']) )
			APP_Item_Registry::register( $plan->post_name, $data['title'][0] );
	}

}

function jr_pricing_add_menu(){
	global $pagenow, $typenow;

	$ptype = APPTHEMES_PRICE_PLAN_PTYPE;
	$ptype_obj = get_post_type_object( $ptype );

	add_submenu_page( 'app-payments', $ptype_obj->labels->name, $ptype_obj->labels->all_items, $ptype_obj->cap->edit_posts, "edit.php?post_type=$ptype" );

	$ptype = APPTHEMES_RESUMES_PLAN_PTYPE;
	$ptype_obj = get_post_type_object( $ptype );

	add_submenu_page( 'app-payments', $ptype_obj->labels->name, $ptype_obj->labels->all_items, $ptype_obj->cap->edit_posts, "edit.php?post_type=$ptype" );

	if($pagenow == 'post-new.php' && $typenow == $ptype) {
		add_submenu_page( 'app-payments', $ptype_obj->labels->new_item, $ptype_obj->labels->new_item, $ptype_obj->cap->edit_posts, "post-new.php?post_type=$ptype" );
	}
}

function jr_pricing_menu_edit_page_menu_workaround($parent_file) {
	global $pagenow, $typenow;

	$ptype = APPTHEMES_PRICE_PLAN_PTYPE;
	$ptype_obj = get_post_type_object( $ptype );

	if($parent_file == "edit.php?post_type=$ptype" && ($pagenow == 'post.php' || $pagenow == 'post-new.php') && $typenow == $ptype) {
		return 'app-payments';
	}

	$ptype = APPTHEMES_RESUMES_PLAN_PTYPE;
	$ptype_obj = get_post_type_object( $ptype );

	if($parent_file == "edit.php?post_type=$ptype" && ($pagenow == 'post.php' || $pagenow == 'post-new.php') && $typenow == $ptype) {
		return 'app-payments';
	}

	return $parent_file;
}

function jr_pricing_modify_title( $title, $post_id = 0 ) {

	$post = get_post( $post_id );
	if( isset($post->post_type) && $post->post_type != APPTHEMES_PRICE_PLAN_PTYPE && $post->post_type != APPTHEMES_RESUMES_PLAN_PTYPE ){
		return $title;
	}

	return get_post_meta( $post_id, 'title', true );
}

function jr_payments_setup_upgrade(){
	if ( current_user_can( 'administrator' ) && JR_VERSION != get_option( 'jobroller_version' ) ) {
		appthemes_upgrade_item_addons();
	}
}

function jr_daily_orders_sales( $args = array() ) {

	$default_args = array(
		'post_type' => APPTHEMES_ORDER_PTYPE,
		'post_status' => array( APPTHEMES_ORDER_COMPLETED, APPTHEMES_ORDER_ACTIVATED ),
		'nopaging' => true,
	);
	$args = wp_parse_args( $args, $default_args );

	$orders = new WP_Query( $args );

	$sales = array();
	foreach( $orders->posts as $post ) {
		$order = appthemes_get_order( $post->ID );
		$the_day = date('Y-m-d', strtotime($post->post_date));
		if ( empty( $sales[$the_day] ) ) $sales[$the_day] = 0;
		$sales[$the_day] += $order->get_total();
	}
	return $sales;
}

function jr_sales_time_span_sql( $clauses, $wp_query ) {
	global $wpdb;
	if ( $wp_query->get( 'jr_sales_time_span' ) ) {
		$time_span = $wp_query->get( 'jr_sales_time_span' );
		$clauses['where'] .= " AND  post_date > '" . $time_span . "'";
	}
	return $clauses;
}

/**
 * Process IPN payment requests started on older JR versions ( < JR 1.7 ).
 */
function jr_process_legacy_payment( $posted ) {
	global $jr_log;

	$jr_log->write_log('Valid legacy IPN response detected: '. print_r( $posted, true ) );

	if ( !empty($posted['txn_type']) ) {

		$legacy_order_key = $posted['item_number'];

		$args = array(
			'post_type' 	=> APPTHEMES_ORDER_PTYPE,
			'post_status'	=> 'any',
			'meta_key' 		=> '_jr_legacy_order_key',
			'meta_value' 	=> $legacy_order_key,
			'nopaging' 		=> true
		);
		$order_query = new WP_Query( $args );

		if ( ! $order_query )
			return;

		$order_id = $order_query->post->ID;
		$order = appthemes_get_order( $order_id );

		$order->set_gateway('legacy_paypal');

		if ( $posted['test_ipn'] && 'pending' == $posted['payment_status'])
			$posted['payment_status'] = 'completed';

		// We are here so lets check status and do actions
		switch (strtolower($posted['payment_status'])) :
			case 'completed' :
				// Payment was made so we can approve the job
				$order->complete();
				break;
			case 'denied' :
			case 'expired' :
			case 'failed' :
			case 'voided' :
				// In these cases the payment failed so we can trash the order
				$order->failed();
				break;
		endswitch;

		$jr_log->write_log( sprintf( 'Legacy IPN Payment \'%s\' for Order #%d', $posted['txn_type'], $order_id ) );
	}
}

/**
 * Process IPN subscriptions requests started on older JR versions ( < JR 1.7 ).
 */
function jr_process_legacy_subscription( $posted ) {
	global $jr_log;

	$jr_log->write_log('Valid legacy IPN response (Subscriptions) detected: '. print_r( $posted, true ) );

	if ( !empty($posted['txn_type']) ) {

		$user_id = $posted['custom']; // old resume subscriptions stored the user_id using the 'custom' key

		$legacy_order_key = get_user_meta( $user_id, '_valid_resume_subscription_order', true );

		$args = array(
			'post_type' 	=> APPTHEMES_ORDER_PTYPE,
			'post_status'	=> 'any',
			'meta_key' 		=> '_jr_legacy_order_key',
			'meta_value' 	=> $legacy_order_key,
			'nopaging' 		=> true
		);
		$order_query = new WP_Query( $args );

		if ( ! $order_query )
			return;

		$order_id = $order_query->post->ID;
		$order = appthemes_get_order( $order_id );

		if ( ! $order )
			return;

		$order->set_gateway('legacy_paypal');

		// Check for manual subscriptions and change the transaction type accordingly
		if (isset($posted['manual_subscr'])) $posted['txn_type'] = $posted['manual_subscr'];

		switch (strtolower($posted['txn_type'])) :
			case 'subscr_trial' :
			case 'subscr_signup':
				$order->complete();
				break;
			case 'subscr_cancel' :
			case 'subscr_failed' :
			case 'subscr_eot' :
				$order->failed();
				break;
		endswitch;

		$jr_log->write_log( sprintf( 'Legacy IPN Subscription \'%s\' for Order #%d', $posted['txn_type'], $order_id ) );
	}

}

/**
 * Initialize the legacy payments IPN listener.
 */
function jr_init_legacy_payments_ipn_listener() {
	global $jr_log;

	if ( ! isset($_GET['paypalListener']) || ! in_array( $_GET['paypalListener'], array( 'RESUME_SUBSCRIPTION', 'RESUME_TRIAL', 'IPN' ) ) )
		return;

	$_POST = stripslashes_deep($_POST);

	if ( ! jr_is_valid_legacy_ipn_request() )
		return;

	if ( isset($_GET['paypalListener']) && 'IPN' == $_GET['paypalListener'] ) {

		do_action( 'jr_valid_legacy_paypal_ipn_request', $_POST );

	} elseif ( isset($_GET['paypalListener']) && in_array( $_GET['paypalListener'], array( 'RESUME_SUBSCRIPTION', 'RESUME_TRIAL' ) ) ) {

		if ($_GET['paypalListener'] == 'RESUME_TRIAL'):
			$_POST['manual_subscr'] = 'subscr_trial';
		else:
			$_POST['manual_subscr'] = 'subscr_signup';
		endif;

		do_action( 'jr_valid_legacy_paypal_subscription_ipn_request', $_POST );
	}

	$jr_log->write_log('Valid legacy IPN listener detected - post_data:'. print_r( $_POST, true ) );
	$jr_log->write_log('Valid legacy IPN listener detected - get_data:'. print_r( $_GET, true ) );

}

/**
 * Check for valid IPN requests (legacy payments).
 */
function jr_is_valid_legacy_ipn_request() {
	global $jr_log;

	$jr_log->write_log( 'Checking validity of IPN Request. '. print_r( $_POST, true ) );

	// add the paypal cmd to the post array
	$_POST['cmd'] = '_notify-validate';

	// send the message back to PayPal just as we received it
	$params = array(
		'body' => $_POST,
		'timeout' 	=> 30
	);

	// get the correct paypal url to post request to
	$paypal_adr = APP_PayPal::get_request_url();

	// post it all back to paypal to get a response code
	$response = wp_remote_post( $paypal_adr, $params );

	// Retry
	if ( is_wp_error($response) ) {
		$params['sslverify'] = false;
		$response = wp_remote_post( $paypal_adr, $params );
	}

	// cleanup
	unset($_POST['cmd']);

	// check to see if the request was valid
	if ( ! is_wp_error($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && ( strcmp( $response['body'], "VERIFIED" ) == 0 ) ) {
		return true;
	} else {
		// response was invalid - don'do nothing
		$jr_log->write_log( 'Invalid IPN request - Response: ' . print_r( $response, true ) );
		return false;
	}

}

/**
 * Retrieve the number of orders for a user.
 */
function jr_get_user_orders_count( $user_id = 0, $args = array() ) {

	if ( ! $user_id ) $user_id = get_current_user_id();

	$order_statuses = array(
		APPTHEMES_ORDER_PENDING,
		APPTHEMES_ORDER_FAILED,
		APPTHEMES_ORDER_COMPLETED,
		APPTHEMES_ORDER_ACTIVATED
	);

	$default_args = array(
		'post_type' => APPTHEMES_ORDER_PTYPE,
		'post_status' => $order_statuses,
		'nopaging' => true,
		'author' => $user_id,
	);
	$args = wp_parse_args( $args, $default_args );

	$orders = new WP_Query( $args );

	return $orders->found_posts;
}


function jr_get_order_statuses_verbiages( $status = '' ) {

	$statuses = array(
		APPTHEMES_ORDER_PENDING		=> __( 'Pending', APP_TD ),
		APPTHEMES_ORDER_FAILED		=> __( 'Failed', APP_TD ),
		APPTHEMES_ORDER_COMPLETED	=> __( 'Completed', APP_TD ),
		APPTHEMES_ORDER_ACTIVATED	=> __( 'Activated', APP_TD ),
	);

	if ( $status && ! empty( $statuses ) ) {
		return $statuses[ $status ];
	} elseif( ! $status ) {
		return $statuses;
	}
	return;
}

/**
 * Hide 'View' link in orders list.
 *
 * @since 1.8.3
 */
function _jr_order_list_remove_actions( $actions, $post ) {

	if ( APPTHEMES_ORDER_PTYPE != $post->post_type ) {
		return $actions;
	}

	unset( $actions['view'] );

	return $actions;
}
