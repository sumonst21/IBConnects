<?php
	$post = get_queried_object();
	$order = appthemes_get_order( $post->ID );
	$redirect_to = jr_get_redirect_to_url( $order );
?>
<div class="order-wrapper">

	<?php do_action( 'appthemes_notices' ); ?>

	<div class="section-head">
		<h1><?php _e( 'Order Summary', APP_TD ); ?></h1>
	</div>

	<div class="order-summary completed">
		<p><?php the_order_summary(); ?></p>

		<p class="thank-you"><?php _e( 'Thank You!', APP_TD ); ?></p>
		<p><?php _e( 'Your order has been completed.', APP_TD ); ?></p>

		<form class="main_form">
			<p><input type="submit" class="submit" value="<?php esc_attr_e( 'Continue', APP_TD ); ?>" onClick="location.href='<?php echo esc_url( $redirect_to ); ?>';return false;"></p>
		</form>

	</div>

</div>