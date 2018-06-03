<div id="main">
	<div class="section-head">
		<h1><?php _e( 'Select a Plan', APP_TD ); ?></h1>
	</div>
	<form id="purchase-plan" class="submit_form main_form" method="POST" action="<?php echo esc_url( $form_action ) ?>">
	<?php wp_nonce_field('purchase_plan', 'nonce') ?>
		<fieldset>
			<div class="pricing-options">
				<?php if( !empty( $plans ) ) {

 						$display_options = array (
							'order'		 => 'yes',
							'selectable' => 'yes',
						);

						$default = 1;
						if ( !empty($job->ID) ) {
							$user_packs = jr_display_packs( 'user', $plans, $display_options, $default, $job );
							if ( $user_packs ) $default = 0;
						}

						$new_packs = jr_display_packs( 'new', $plans, $display_options, $default, $job );

					} else { ?>
						<em><?php _e( 'No Plans are currently available for this category. Please come back later.', APP_TD ); ?></em>
				<?php } ?>
			</div>
		</fieldset>

		<div class="clear"></div>

		<?php do_action( 'jr_after_purchase_plan_new_form', $job ); ?>
		<?php do_action( 'appthemes_purchase_fields' ); ?>

		<input type="hidden" name="action" value="<?php echo esc_attr($post_action); ?>">
		<input type="hidden" name="ID" value="<?php echo $job->ID; ?>">
		<input type="hidden" name="order_id" value="<?php echo esc_attr($order_id); ?>">
		<input type="hidden" name="step" value="<?php echo esc_attr($step); ?>"/>

		 <?php if ( get_query_var('job_relist') ): ?>
 			<input type="hidden" name="relist" value="1"/>
		<?php endif; ?>
		<p>
			<?php if ( $step > 1 ) { ?>
				<input type="submit" name="goback" class="goback" value="<?php _e('Go Back',APP_TD) ?>"  />
			<?php } ?>
			<?php if( !empty($user_packs) || ! empty($new_packs) ){ ?>
				<input type="submit" class="submit" value="<?php _e( 'Continue', APP_TD ) ?>" />
			<?php } ?>
		</p>
	</form>
</div>