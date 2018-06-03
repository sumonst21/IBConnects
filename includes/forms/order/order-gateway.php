<?php
	$success = process_the_order();

	$order_post = get_queried_object();
	$order = get_order();

	$is_returning = isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], $order->get_gateway() );

	if ( $success && ( in_array( $order->get_status(), array( APPTHEMES_ORDER_PAID, APPTHEMES_ORDER_COMPLETED, APPTHEMES_ORDER_ACTIVATED ) ) ) ) {

		// redirect the user to the order page to display the order summary
		wp_redirect( $order->get_return_url() );
		exit();

	} elseif ( APPTHEMES_ORDER_PENDING == $order->get_status() && $success !== NULL && ! $success ) {
		$text = html( 'p', __( 'There was a problem processing your order. Please try again later.', APP_TD ) );
		$text .= html( 'p', sprintf( __( 'If the problem persists, contact the site owner and refer your <strong>Order ID: %d</strong>', APP_TD ), $order->get_id() ) );

		// output sanitized link for previous page
		$location = wp_sanitize_redirect( $order->get_return_url() );
		$location = wp_validate_redirect( $location, admin_url() );

		$text .= html( 'a', array( 'href' => $location ), __( '&#8592; Return', APP_TD ) );
		echo html( 'span', array( 'class' => 'redirect-text' ), $text );

	} else {

		// notify admin and author about the new order
		jr_new_order_notify_admin( $order );
		jr_new_order_notify_owner( $order );
	}

