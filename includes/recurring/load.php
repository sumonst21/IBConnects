<?php

require dirname(__FILE__) . '/order-processing.php';

$processing = new APP_Order_Processing();
add_action( 'init', array( $processing, 'process' ) );

require dirname(__FILE__) . '/order-recurring-class.php';
