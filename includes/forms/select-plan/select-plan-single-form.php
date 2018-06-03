<?php $plans_count = 0; $checked = true; ?>
<div id="main">
	<div class="section-head">
		<h1><?php _e( 'Select a Plan', APP_TD ); ?></h1>
	</div>
	<form id="purchase-plan" class="submit_form main_form" method="POST" action="<?php echo esc_url( $form_action ) ?>">
		<?php wp_nonce_field('purchase_plan', 'nonce') ?>
		<fieldset>
			<div class="pricing-options">
				<?php if ( ! empty( $plans ) ): ?>

					<?php foreach( $plans as $key => $plan ): ?>

						<?php
							// skip plans that are over the usage limit
							if ( ! jr_plan_is_selectable( $plan['post_data']->ID, jr_reset_data($plan) ) ) {
								continue;
							}

							$args = array(
								'plan_id' => $plan['post_data']->ID,
								'plan_data' => jr_reset_data( $plan ),
								'job' => $job ,
							);

							$plans_count++;
						?>

						<div class="plan">
							<div class="content">
								<div class="plan-content">
									<div class="title"><?php echo $plan['title'][0]; ?></div>
									<div class="plan-description"><?php echo $plan[JR_FIELD_PREFIX.'description'][0]; ?></div>

									<?php do_action( 'jr_plan_other_additional_options_before', $args ); ?>

									<?php if ( ! _jr_no_addons_available( $plan ) ) : ?>
										<div class="option-header"><?php _e( 'No additional options available for this price plan.', APP_TD ); ?></div>
									<?php else: ?>
										<div class="job-pack-additional-options">
											<div class="option-header"><?php _e( 'Please choose additional featured options:', APP_TD ); ?></div>

											<?php do_action( 'jr_plan_additional_options', $args ); ?>
										</div>
									<?php endif; ?>

									<?php do_action( 'jr_plan_other_additional_options_after', $args ); ?>
								</div>
							</div>
							<div class="price-box">
								<div class="price"><?php appthemes_display_price( jr_plan_price( jr_reset_data($plan) ) ); ?></div>
								<div class="duration">
									<?php if( $plan[JR_FIELD_PREFIX.'duration'][0] != 0 ): ?>
										<?php printf( _n( 'for <br /> %s day', 'for <br /> %s days', $plan[JR_FIELD_PREFIX.'duration'][0], APP_TD ), $plan[JR_FIELD_PREFIX.'duration'][0] ); ?>
									<?php else: ?>
										<?php _e( 'Unlimited</br> days', APP_TD ); ?>
									<?php endif; ?>
								</div>
								<div class="radio-button">
									<label>
										<input name="plan" type="radio" <?php checked( $checked ); ?> value="<?php echo esc_attr( $plan['post_data']->ID ); ?>" />
										<?php _e( 'Choose this option', APP_TD ); ?>
									</label>
									<?php if ( ! empty($plan[JR_FIELD_PREFIX.'limit'][0]) ) : ?>
										<?php $remain_uses = jr_plan_remain_usage( $plan['post_data']->ID, jr_reset_data($plan) ); ?>
										<div class="plan-limit"><?php echo sprintf ( __( 'Limited Availability (%d %s)', APP_TD ), $remain_uses, __( 'remaining', APP_TD ) ); ?></div>
									<?php endif; ?>
								</div>
							</div>
						</div>

						<?php $checked = false; ?>

					<?php endforeach; ?>

				<?php endif; ?>

				<?php if ( ! $plans_count ): ?>
						<em><?php _e( 'No Plans are currently available for this category. Please come back later.', APP_TD ); ?></em>
				<?php endif; ?>
			</div>
		</fieldset>

		<div class="clear"></div>

		<?php do_action( 'jr_after_purchase_plan_new_form', $job ); ?>
		<?php do_action( 'appthemes_purchase_fields' ); ?>

		<input type="hidden" name="action" value="<?php echo esc_attr($post_action); ?>">
		<input type="hidden" name="ID" value="<?php echo esc_attr($job->ID); ?>">
		<input type="hidden" name="order_id" value="<?php echo esc_attr($order_id); ?>">
		<input type="hidden" name="step" value="<?php echo esc_attr($step); ?>"/>

		<?php if ( get_query_var('job_relist') ): ?>
			<input type="hidden" name="relist" value="1"/>
		<?php endif; ?>
		<p><input type="submit" name="goback" class="goback" value="<?php _e('Go Back',APP_TD) ?>"  />
		<?php if ( $plans_count ): ?>
			<input type="submit" class="submit" value="<?php _e( 'Continue', APP_TD ) ?>" />
		<?php endif; ?>
		</p>
	</form>
</div>