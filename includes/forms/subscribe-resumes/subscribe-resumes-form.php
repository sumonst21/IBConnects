<?php
/**
 * JobRoller Resumes Subscription form
 * Function outputs the resumes subscription form
 *
 *
 * @version 1.0
 * @author AppThemes
 * @package JobRoller
 * @copyright 2010 all rights reserved
 */

if ( jr_resume_valid_subscr() && ! jr_resume_valid_trial() ) {
	return;
}

$plans = jr_get_available_plans( array( 'post_type' => APPTHEMES_RESUMES_PLAN_PTYPE ) );
$plans_count = 0;
$checked = true;
?>
<div id="main">
	<div class="section-head">
		<h1><?php _e( 'Select a Plan', APP_TD ); ?></h1>
	</div>
	<form id="purchase-resumes-plan" class="subscribe_resumes main_form" method="POST">
		<?php wp_nonce_field('purchase_resumes_plan', 'nonce') ?>
		<fieldset>
			<div class="pricing-options">

				<?php if ( ! empty( $plans ) ): ?>

					<?php foreach( $plans as $key => $plan ): ?>

						<?php
							// skip plans that are over the usage limit
							if ( ! jr_plan_is_selectable( $plan['post_data']->ID, jr_reset_data($plan) ) ) {
								continue;
							}

							$plans_count++;
						?>

						<div class="plan">
							<div class="content">
								<div class="title">
									<?php echo $plan['title'][0]; ?>
									<?php echo!empty( $plan[JR_FIELD_PREFIX . 'trial'][0] ) ? html( 'span', array( 'class' => 'resume-trial' ), __( '(Trial)', APP_TD ) ) : ''; ?>
								</div>
								<div class="description"><?php echo $plan[JR_FIELD_PREFIX.'description'][0]; ?></div>
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
								</div>
							</div>
						</div>

						<?php $checked = false; ?>

					<?php endforeach; ?>

				<?php endif; ?>

				<?php if ( ! $plans_count ): ?>
						<em><?php _e( 'No subscription Plans are currently available. Please come back later.', APP_TD ); ?></em>
				<?php endif; ?>
			</div>
		</fieldset>

		<?php do_action( 'jr_after_purchase_resume_plan_new_form' ); ?>
		<?php do_action( 'appthemes_purchase_fields' ); ?>

		<?php if ( $plans_count ): ?>
		<p>
			<input type="hidden" name="action" value="purchase-resume-plan">
			<input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">
			<input type="hidden" name="step" value="<?php echo esc_attr( $step ); ?>"/>
			<input type="submit" class="submit" value="<?php esc_attr_e( 'Continue', APP_TD ) ?>" />
		</p>
		<?php endif; ?>
	</form>
</div>