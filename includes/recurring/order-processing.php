<?php
/**
 * Order Processing class
 *
 * @package Components\Payments
 */
class APP_Order_Processing extends APP_Queue {

	public function __construct(){
		$args = array(
			'interval' => 'hourly',
			'limit' => 100
		);

		if( defined( 'APPTHEMES_ORDER_PROCESS_INTERVAL' ) )
			$args['interval'] = APPTHEMES_ORDER_PROCESS_INTERVAL;

		if( defined( 'APPTHEMES_ORDER_PROCESS_LIMIT' ) )
			$args['limit'] = APPTHEMES_ORDER_PROCESS_LIMIT;

		parent::__construct( 'app_order_processing', $args );
		add_filter( 'posts_clauses', array( $this, 'filter_before' ), 10, 2 );
		add_filter( 'posts_clauses', array( $this, 'filter_post_parents' ), 10, 2 );
	}

	public function process(){

		$items_processed = parent::process();
		return $items_processed;

	}

	/**
	 * Returns all order's that need to be processed.
	 * Recurring orders are children of other master orders and
	 * will be marked as pending when they are waiting to be processed.
	 *
	 * The post date is set to the time when the order should be processed.
	 * An order will only be returned if the current date is greater than
	 * the process date
	 *
	 * @return array
	 */
	protected function get_items(){

		return new WP_Query( array(
			'post_type' => APPTHEMES_ORDER_PTYPE,
			'post_parent__not_in' => array( '0' ),
			'post_status' => array( APPTHEMES_ORDER_PENDING, APPTHEMES_ORDER_COMPLETED ),
			'before_date' => time(),
			'showposts' => $this->args['limit']
		) );

	}

	public function process_item( $item ){

		$order = appthemes_get_order( $item->ID, true );
		if( !$order )
			return;

		if( $order->get_status() == APPTHEMES_ORDER_PENDING ){
			$gateway = $order->get_gateway();
			if( empty( $gateway ) ){
				return;
			}

			$gateway_object = APP_Gateway_Registry::get_gateway( $gateway );
			if( !$gateway_object || !$gateway_object->is_recurring() ){
				return;
			}

			appthemes_process_recurring_gateway( $gateway, $order );
		}

		$order = appthemes_get_order( $item->ID, true );
		if( $order->get_status() == APPTHEMES_ORDER_COMPLETED ){
			$order->log( 'Processing Queue: Order is completed. Activating' );
			$order->activate();
		}

	}

	/**
	 * Filter to specify that posts should only be returned if their post_date
	 * is before the given date
	 */
	public function filter_before( $clauses, $query ){

		$before_date = $query->get( 'before_date' );
		if( !empty( $before_date ) ){
			$time = date( 'Y-m-d H:i:s', $before_date );
			$clauses['where'] .= " AND ( post_date < '{$time}' ) ";
		}

		return $clauses;

	}

	/**
	 * Filter out posts that do not have the correct post parent
	 */
	public function filter_post_parents( $clauses, $query ){
		global $wpdb;

		$parents = $query->get( 'post_parent__not_in' );
		if( !empty( $parents ) ){
			$post_parent__not_in = implode( ',', array_map( 'absint', $parents ) );
			$clauses['where'] .= " AND {$wpdb->posts}.post_parent NOT IN ($post_parent__not_in)";
		}

		return $clauses;

	}

	/**
	 * Filter to specify that posts should only be returned if their post_date
	 * is after the given date
	 */
	public function filter_after( $clauses, $query ){

		$after_date = $query->get( 'after_date' );
		if( !empty( $after_date ) ){
			$time = date( 'Y-m-d H:i:s', strtotime( $after_date ) );
			$clauses['where'] = " AND ( post_date > '{$time}' ) ";
		}

		return $clauses;

	}
}
