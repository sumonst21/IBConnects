<?php
/**
 * Additional options for the 'Payments' page.
 *
 * @version 1.6
 * @author AppThemes
 * @package JobRoller\Admin\Payments
 * @copyright 2010 all rights reserved
 */

add_action( 'admin_init', 'jr_payment_settings_setup_init' );
add_action( 'admin_print_styles', 'jr_payments_icon' );
add_action( 'after_setup_theme', 'jr_admin_order_columns', 1000 );

add_filter( 'manage_' . APPTHEMES_PRICE_PLAN_PTYPE . '_posts_columns', 'jr_plans_manage_columns', 11 );
add_action( 'manage_' . APPTHEMES_PRICE_PLAN_PTYPE . '_posts_custom_column', 'jr_plans_add_column_data', 10, 2 );
add_filter( 'manage_edit-' . APPTHEMES_PRICE_PLAN_PTYPE . '_sortable_columns', 'jr_plans_columns_sort' );

add_filter( 'manage_' . APPTHEMES_RESUMES_PLAN_PTYPE . '_posts_columns', 'jr_resumes_plans_manage_columns', 11 );
add_action( 'manage_' . APPTHEMES_RESUMES_PLAN_PTYPE . '_posts_custom_column', 'jr_plans_add_column_data', 10, 2 );
add_filter( 'manage_edit-' . APPTHEMES_RESUMES_PLAN_PTYPE . '_sortable_columns', 'jr_plans_columns_sort' );

add_filter( 'pre_get_posts', 'jr_sort_plans' );


### Hook Callbacks

function jr_payment_settings_setup_init() {
	global $admin_page_hooks, $jr_options;

	if ( ! isset($admin_page_hooks['app-payments']) ) {
		return;
	}

	add_action( 'tabs_'.$admin_page_hooks['app-payments'].'_page_app-payments-settings', array( 'JR_Plan_Type_Tab', 'init' ), 11 );

	if ( ( 'pack' == $jr_options->plan_type && ! isset($_POST['plan_type']) ) || ( isset($_POST['plan_type']) && 'pack' == $_POST['plan_type'] ) ) {
		add_action( 'tabs_'.$admin_page_hooks['app-payments'].'_page_app-payments-settings', array( 'JR_Plan_Settings_Tab', 'init' ), 12 );
	}

	// show resume addons only if subscriptions are enabled
	if ( $jr_options->jr_resume_require_subscription ) {
		add_action( 'tabs_'.$admin_page_hooks['app-payments'].'_page_app-payments-settings', array( 'JR_Resumes_Settings_Tab', 'init' ), 14 );
	}

	add_action( 'tabs_'.$admin_page_hooks['app-payments'].'_page_app-payments-settings', array( 'JR_Featured_Settings_Tab', 'init' ), 13 );

}

/**
 * Adds the Payments Icon to other pricing plans post types.
 */
function jr_payments_icon(){
	$url = appthemes_payments_image( 'payments-med.png' );
?>
<style type="text/css">
	.icon32-posts-resumes-pricing-plan {
		background-image: url('<?php echo $url; ?>');
		background-position: -5px -5px !important;
	}
</style>
<?php
}

function jr_plans_manage_columns( $columns ) {

	foreach ( $columns as $key => $column ) {

		if ( 'date' == $key ) {
			$columns_reorder['limit'] = __( 'Usage Limit', APP_TD );
			$columns_reorder['price'] = __( 'Price', APP_TD );
		}
		$columns_reorder[$key] = $column;

	}

	return $columns_reorder;
}

function jr_resumes_plans_manage_columns( $columns ) {

	foreach ( $columns as $key => $column ) {

		if ( 'date' == $key ) {
			$columns_reorder['limit'] = __( 'Usage Limit', APP_TD );

			if ( _jr_is_recurring_available() ) {
				$columns_reorder['duration'] = __( 'Recurs Every', APP_TD );
			} else {
				$columns_reorder['duration'] = __( 'Duration', APP_TD );
			}

			$columns_reorder['price'] = __( 'Price', APP_TD );
		}
		$columns_reorder[ $key ] = $column;

	}

	return $columns_reorder;
}

function jr_plans_add_column_data( $column_index, $post_id ) {
	switch ( $column_index ) {

		case 'price' :
			$price = get_post_meta( $post_id, JR_FIELD_PREFIX . 'price', true );
			$price = ! intval($price) ? __( 'Free', APP_TD ) : appthemes_get_price( $price );
			echo $price;
			break;

		case 'limit' :
			$limit = get_post_meta( $post_id, JR_FIELD_PREFIX . 'limit', true );
			echo $limit ? $limit : '-';
			break;

		case 'duration' :
			$trial = get_post_meta( $post_id, JR_FIELD_PREFIX . 'trial', true );
			if ( ! $trial ) $duration = get_post_meta( $post_id, JR_FIELD_PREFIX . 'duration', true );
			echo ! empty($duration) ? sprintf( _n( '%d day', '%d days', $duration ), $duration ) : '-';
			break;
	}
}

function jr_plans_columns_sort($columns) {
	$columns['price'] = 'price';
	return $columns;
}

function jr_sort_plans( $query ) {

	if( $query->is_admin ) {
		if ( APPTHEMES_PRICE_PLAN_PTYPE == $query->get( 'post_type' ) ||  APPTHEMES_RESUMES_PLAN_PTYPE == $query->get( 'post_type' ) ) {
			$query->set( 'orderby', 'menu_order' );
			$query->set( 'order', 'ASC' );
		}
	}
	return $query;
}

/**
 *  Only load additional order columns after payments are completely loaded.
 */
function jr_admin_order_columns() {
	add_action( 'manage_' . APPTHEMES_ORDER_PTYPE . '_posts_custom_column', 'jr_orders_add_column_data', 15, 2 );
	add_filter( 'manage_' . APPTHEMES_ORDER_PTYPE . '_posts_columns', 'jr_orders_manage_columns', 15 );
	add_filter( 'manage_edit-' . APPTHEMES_ORDER_PTYPE . '_sortable_columns', 'jr_orders_columns_sort' );

	// displays legacy information for upgraded orders
	new JR_APP_Order_Legacy();
}

function jr_orders_add_column_data( $column_index, $post_id ) {

	$order = appthemes_get_order( $post_id );

	switch ( $column_index ) {

		case 'plan_type':
			$order_data = _jr_get_order_job_info( $order );
			if ( ! $order_data ) {
				return;
			}

			extract( $order_data );

			$obj = get_post_type_object( $plan->post_type );
			echo $obj->labels->singular_name;
			break;

		case 'order' :
			$legacy_order_id = get_post_meta( $post_id, '_jr_legacy_order_id', true );
			$legacy_order_key = get_post_meta( $post_id, '_jr_legacy_order_key', true );
			if ( $legacy_order_id && $legacy_order_key  ) {
				$legacy = sprintf( '#%s [%s]', $legacy_order_id, $legacy_order_key );
				echo html( 'div', array ( 'class' => 'legacy-order-id' ),  $legacy );
			}
			break;
	}
}

function jr_orders_manage_columns( $columns ) {

	foreach ( $columns as $key => $column ) {
		if ( 'order_author' == $key ) {
			$columns_reorder['plan_type'] = __( 'Type', APP_TD );
		}
		$columns_reorder[$key] = $column;

	}
	return $columns_reorder;
}

function jr_orders_columns_sort($columns) {
	$columns['plan_type'] = 'plan_type';
	return $columns;
}


### Extra Tabs for Payments

class JR_Plan_Type_Tab {

	private static $page;

	static function init( $page ) {
		self::$page = $page;

		$fields = array();

		$fields = array(
			array(
				'title' => __( 'Job Plan Type', APP_TD ),
				'type' => 'select',
				'name' => 'plan_type',
				'values' => array (
					'single'=> __('Single', APP_TD),
					'pack'	=> __('Pack', APP_TD),
				),
				'tip' => __( 'Single plans expire after each job purchase. Pack plans remain active until their job limit or duration reaches it\'s limits.', APP_TD ),
			),
		);

		$orders = new WP_Query( array(
			'post_type' => APPTHEMES_ORDER_PTYPE,
			'posts_per_page' => 1,
		));

		$page->tab_sections['general']['plan'] = array(
			'title' => __( 'Plan Type', APP_TD ),
			'fields' => $fields,
		);
	}

}

class JR_Plan_Settings_Tab {

	private static $page;

	static function init( $page ) {

		self::$page = $page;

		$fields = array(
			array(
				'title' => __( 'Buy Separate Job Packs', APP_TD ),
				'type' => 'checkbox',
				'name' => 'separate_packs',
				'desc' => __( 'Enable', APP_TD ),
				'tip'=> sprintf( __( 'Allow job listers to purchase job packs from their dashboard or from the job packs <a href="%s">widget</a> at anytime, without submitting jobs.', APP_TD ), 'admin.php?widgets.php'),
			),
			array(
				'title' => __( 'Display Categories', APP_TD ),
				'type' => 'checkbox',
				'name' => 'plan_display_cats',
				'desc' => __( 'Enable', APP_TD ),
				'tip'=> __( 'Displays the categories where each Pack is available for purchase.', APP_TD ),
			),
		);

		$page->tab_sections['general']['plan_settings'] = array(
			'title' => __( 'Packs Settings', APP_TD ),
			'fields' => $fields,
		);

	}

}

class JR_Featured_Settings_Tab {

	private static $page;

	static function init( $page ) {
		self::$page = $page;

		$fields = array();

		foreach ( _jr_featured_addons() as $addon ) {
			$fields = array_merge( $fields, self::generate_fields( $addon ) );
		}

		$page->tab_sections['general']['featured'] = array(
			'title' => __( 'Featured Add-ons', APP_TD ),
			'renderer' => array( __CLASS__, 'render' ),
			'fields' => $fields
		);
	}

	static function render( $section ) {
		$columns = array(
			'type' => __( 'Type', APP_TD ),
			'enabled' => __( 'Enabled', APP_TD ),
			'price' => __( 'Price', APP_TD ),
			'duration' => __( 'Duration', APP_TD ),
		);

		$header = '';
		foreach ( $columns as $key => $label ) {
			$header .= html( 'th', $label );
		}

		$rows = '';
		foreach ( _jr_featured_addons() as $addon ) {
			$row = html( 'td', APP_Item_Registry::get_title( $addon ) );

			foreach ( self::generate_fields( $addon ) as $field ) {
				$row .= html( 'td', self::$page->input( $field ) );
			}

			$rows .= html( 'tr', $row );
		}

		echo html( 'table id="featured-pricing" class="widefat payment-addons"', html( 'tr', $header ), html( 'tbody', $rows ) );
	}

	private static function generate_fields( $addon ) {
		return array(
			array(
				'type' => 'checkbox',
				'name' => array( 'addons', $addon, 'enabled' ),
				'desc' => __( 'Yes', APP_TD ),
			),
			array(
				'type' => 'text',
				'name' => array( 'addons', $addon, 'price' ),
				'sanitize' => 'appthemes_absfloat',
				'extra' => array( 'size' => 3 ),
			),
			array(
				'type' => 'text',
				'name' => array( 'addons', $addon, 'duration' ),
				'sanitize' => 'absint',
				'extra' => array( 'size' => 3 ),
				'desc' => __( 'days', APP_TD )
			),
		);
	}
}

class JR_Resumes_Settings_Tab {

	private static $page;

	static function init( $page ) {
		self::$page = $page;

		$fields = array();

		foreach ( _jr_resumes_addons() as $addon ) {
			$fields = array_merge( $fields, self::generate_fields( $addon ) );
		}

		$page->tab_sections['general']['resumes'] = array(
			'title' => __( 'Resumes Add-ons', APP_TD ),
			'renderer' => array( __CLASS__, 'render' ),
			'fields' => $fields
		);
	}

	static function render( $section ) {
		$columns = array(
			'type' => __( 'Type', APP_TD ),
			'enabled' => __( 'Enabled', APP_TD ),
			'price' => __( 'Price', APP_TD ),
			'duration' => __( 'Duration', APP_TD ),
		);

		$header = '';
		foreach ( $columns as $key => $label ) {
			$header .= html( 'th', $label );
		}

		$rows = '';
		foreach ( _jr_resumes_addons() as $addon ) {
			$row = html( 'td', APP_Item_Registry::get_title( $addon ) );

			foreach ( self::generate_fields( $addon ) as $field ) {
				$row .= html( 'td', self::$page->input( $field ) );
			}

			$rows .= html( 'tr', $row );
		}

		echo html( 'table id="resumes-pricing" class="widefat payment-addons"', html( 'tr', $header ), html( 'tbody', $rows ) );
	}

	private static function generate_fields( $addon ) {
		return array(
			array(
				'type' => 'checkbox',
				'name' => array( 'addons', $addon, 'enabled' ),
				'desc' => __( 'Yes', APP_TD ),
			),
			array(
				'type' => 'text',
				'name' => array( 'addons', $addon, 'price' ),
				'sanitize' => 'appthemes_absfloat',
				'extra' => array( 'size' => 3 ),
			),
			array(
				'type' => 'text',
				'name' => array( 'addons', $addon, 'duration' ),
				'sanitize' => 'absint',
				'extra' => array( 'size' => 3 ),
				'desc' => __( 'days', APP_TD )
			),
		);
	}
}


### Legacy Order Class

/**
 * Controls the Order Status Meta Box
 */
class JR_APP_Order_Legacy extends scbPostMetabox {

	/**
	 * Sets up the meta box with Wordpress
	 */
	function __construct(){

		parent::__construct( 'order-legacy', __( 'Legacy Information', APP_TD ), array(
			'post_type' => APPTHEMES_ORDER_PTYPE,
			'context' => 'side',
			'priority' => 'high'
		) );

	}

	function condition() {
		return (bool) ( ! empty($_GET['post']) && get_post_meta( intval($_GET['post']), '_jr_legacy_order_id', true ) );
	}

	/**
	 * Displays the order status summary
	 * @param  object $post Wordpress Post object
	 * @return void
	 */
	function display( $post ){

		$order = appthemes_get_order( $post->ID );
		$legacy_order_id = get_post_meta( $post->ID, '_jr_legacy_order_id', true );
		$legacy_order_key = get_post_meta( $post->ID, '_jr_legacy_order_key', true );

		?>
		<style type="text/css">
			#admin-order-status th{
				padding-right: 10px;
				text-align: right;
				width: 40%;
			}
		</style>
		<table id="admin-order-status">
			<tbody>
				<tr>
					<th><?php _e( 'ID', APP_TD ); ?>: </th>
					<td><?php echo $legacy_order_id; ?></td>
				</tr>
				<tr>
					<th><?php _e( 'Key', APP_TD ); ?>: </th>
					<td><?php echo $legacy_order_key; ?></td>
				</tr>
			</tbody>
		</table>
		<?php

	}
}
