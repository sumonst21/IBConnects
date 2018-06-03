<?php $core_args = compact( 'pack', 'job', 'plan_id', 'plan_data' ); ?>

<div class="job-pack <?php echo esc_attr( implode( ' ', $display_options['class'] ) ); ?>" >

		<div class="job-pack-title">
			<h2><?php echo $title; ?> : <span class="job-pack-price"> <?php echo $cost ?></span></h2>
			<?php if ( 'user' == $type ): ?>
				<div class="pack-activation-date"><?php echo $activation_date; ?></div>
			<?php endif; ?>
		</div>

		<?php if ( 'yes' == $display_options['selectable'] ): ?>
			<div class="job-pack-choose">
				<label><?php echo $pack_selection_text; ?>
					<input type="radio" name="plan" value="<?php echo esc_attr( $pack['plan_ref_id'] ); ?>" <?php checked( $default, 1 ); ?> />
				</label>
				<?php if ( 'user' != $type && ! empty( $pack['plan_data'][JR_FIELD_PREFIX.'limit'] ) ): ?>
					<?php $remain_uses = jr_plan_remain_usage( $pack['plan_id'], $pack['plan_data'] ); ?>
					<div class="pack-limit"><?php echo sprintf ( __( 'Limited Availability (%d %s)', APP_TD ), $remain_uses, __( 'remaining', APP_TD ) ); ?></div>
				<?php endif; ?>
		 	</div>
		<?php endif; ?>

		<?php if ( 'user' != $type ): ?>
			<p class="job-pack-description"> <?php echo $pack['plan_data'][JR_FIELD_PREFIX.'description']; ?> </p>
		<?php else: ?>
			<p>&nbsp;</p>
		<?php endif; ?>

		<?php do_action( 'jr_plan_other_details_before', $core_args ); ?>

		<ul class="job-pack-details">
			<li class="job-pack-duration">
				<i class="icon dashicons-before"></i><strong><?php _e('Duration:',APP_TD); ?></strong> <?php echo $expiration; ?>
			</li>
			<li class="job-pack-jobs-duration">
				<i class="icon dashicons-before"></i><strong><?php _e('Jobs:',APP_TD); ?></strong> <?php echo $jobs; ?>
			</li>
		</ul>
		<ul class="job-pack-details">
			<li class="job-pack-offers">
				<i class="icon dashicons-before"></i><strong><?php _e('Offers:',APP_TD); ?></strong> <?php echo $offers; ?>
			</li>
			<li class="job-pack-resume">
				<i class="icon dashicons-before"></i><strong><?php _e('Access:',APP_TD); ?></strong> <?php echo $access; ?>
			</li>
		</ul>

		<?php do_action( 'jr_plan_other_details_after', $core_args ); ?>

		<?php if ( 'no' != $display_options['categories'] ) : ?>
			<ul class="job-pack-details categories-list">
				<li class="job-pack-categories">
					<i class="icon dashicons-before"></i><strong><?php _e('Categories:',APP_TD); ?></strong> <?php echo $categories; ?>
				</li>
			</ul>
		<?php endif; ?>

		<?php do_action( 'jr_plan_other_additional_options_before', $core_args ); ?>

		<?php if ( ! is_page( JR_Dashboard_Page::get_id() ) ): ?>

			<?php if ( ! _jr_no_addons_available( $pack['plan_data'], ( is_page( JR_Packs_Purchase_Page::get_id() ) ? jr_get_addons('user') : '' ) ) ): ?>
				<div class="option-header">
					<?php _e( 'No additional options available for this plan.', APP_TD ); ?>
				</div>
			<?php else: ?>
				<div class="job-pack-additional-options">
					<p><strong><?php _e( 'Additional options:', APP_TD ); ?></strong></p>

					<?php do_action( 'jr_plan_additional_options', $core_args ); ?>
				</div>
			<?php endif; ?>

		<?php endif; ?>

		<?php do_action( 'jr_plan_other_additional_options_after', $core_args ); ?>

	</div><!-- job-pack -->
