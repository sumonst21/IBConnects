<div class="order-wrapper">

	<div class="section-head">
		<h1><?php _e( 'Order Summary', APP_TD ); ?></h1>
	</div>

	<div class="order-summary">
		<?php the_order_summary(); ?>
	</div>

	<form class="main_form" action="<?php the_order_return_url(); ?>" method="POST">
		<div class="order-gateways">
			<p><?php _e( 'Please select a method for processing your payment:', APP_TD ); ?></p>

			<?php
				if ( APPTHEMES_RESUMES_PLAN_PTYPE == get_query_var('plan_type') && empty($plan_data[JR_FIELD_PREFIX.'trial'][0]) ) {
					appthemes_list_gateway_dropdown( 'payment_gateway', appthemes_recurring_available() );
				} else {
					appthemes_list_gateway_dropdown( 'payment_gateway' );
				}
			?>
		</div>

		<input type="hidden" name="action" value="select-gateway" />
		<input type="hidden" name="referer" value="<?php echo esc_url( get_query_var('referer') ); ?>" />
		<input type="hidden" name="order_id" value="<?php echo esc_attr( get_queried_object()->ID ); ?>" />
		<input type="hidden" name="ID" value="<?php echo esc_attr( get_query_var('job_id') ); ?>" />
		<input type="hidden" name="step" value="<?php echo esc_attr( get_query_var('step') ); ?>"/>

		 <?php if ( get_query_var('job_relist') ): ?>
 			<input type="hidden" name="relist" value="1"/>
		<?php endif; ?>

		<p>
			<input type="submit" name="goback" class="goback" value="<?php esc_attr_e('Go Back',APP_TD) ?>" />
			<input type="submit" class="submit" value="<?php esc_attr_e( 'Pay', APP_TD ); ?>" />
		</p>

		<div class="clear"></div>

	</form>

</div>
