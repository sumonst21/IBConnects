<?php
/**
 * Outputs the pack plans forms.
 *
 * @version 1.6
 * @author AppThemes
 * @package JobRoller\Forms\Lister-Packs
 * @copyright 2010 all rights reserved
 */
?>

<div id="main">
	<div class="section-head">
		<h1><?php _e( 'Select a Plan', APP_TD ); ?></h1>
	</div>
	<form id="purchase-plan" class="submit_form main_form" method="POST">
		<?php wp_nonce_field( 'purchase_plan', 'nonce' ); ?>

		<fieldset>
			<div class="pricing-options">
				<?php  if ( ! empty( $plans ) ): ?>

					<?php
						$display_options = array (
							'order'		 => 'yes',
							'selectable' => 'yes',
						);

						jr_display_packs( 'new', $plans, $display_options, $default = 1 );
					?>

				<?php else: ?>
					<em><?php _e( 'No Plans are currently available. Please come back later.', APP_TD ); ?></em>
				<?php endif; ?>
			</div>
		</fieldset>

		<?php do_action( 'jr_after_purchase_plan_new_form' ); ?>

		<?php do_action( 'appthemes_purchase_fields' ); ?>

		<?php if ( ! empty( $plans ) ): ?>
			<p>
				<input type="hidden" name="action" value="purchase-separate-plan">
				<input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">
				<input type="hidden" name="step" value="<?php echo esc_attr( $step ); ?>"/>
				<input type="submit" class="submit" value="<?php echo esc_attr( 'Continue', APP_TD ) ?>" />
			</p>
		<?php endif; ?>

	</form>
</div>
