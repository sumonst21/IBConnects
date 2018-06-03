<?php

class APP_Recurring_Order extends APP_Instant_Order {
	
	/**
	 * Returns true if the order recurrs
	 */
	public function is_recurring(){
		return ! empty( $this->payment['recurring_period'] );
	}

	/**
	 * Sets up the order to recur upon completion
	 */
	public function set_recurring_period( $recurring_period, $recurring_period_type = self::RECUR_PERIOD_TYPE_DAYS ) {
		$this->payment['recurring_period'] = $recurring_period;
		$this->payment['recurring_period_type'] = $recurring_period_type;
		$this->update_meta( 'recurring_period', $this->payment['recurring_period'] );
		$this->update_meta( 'recurring_period_type', $this->payment['recurring_period_type'] );
	}

	/**
	 * Returns the order's recurring period
	 */
	public function get_recurring_period(){
		return $this->payment['recurring_period'];
	}

	/**
	 * Returns the order's recurring period type
	 */
	public function get_recurring_period_type(){
		return $this->payment['recurring_period_type'];
	}

	/**
	 * Stops the order from recurring upon completion
	 */
	public function clear_recurring_period(){
		$this->payment['recurring_period'] = null;
		$this->payment['recurring_period_type'] = null;
		$this->update_meta( 'recurring_period', $this->payment['recurring_period'] );
		$this->update_meta( 'recurring_period_type', $this->payment['recurring_period_type'] );
	}
	
}