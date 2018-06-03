<?php if ( $active_subscription || $valid_trial ): ?>
	<p><?php echo sprintf ( __( 'Your <em>%s</em> Resume Subscription is Active:', APP_TD ), ( $valid_trial ? __( 'Trial', APP_TD ) : '' ) ); ?></p>
<?php else: ?>
	<p><?php _e( 'No active Resume subscriptions', APP_TD );?></p>

	<?php the_resume_purchase_plan_link(); ?>
<?php endif;?>

<?php if ( ! empty($resumes_access) ): ?>

	<?php if ( 'temporary' == $resumes_access['level'] ) : ?>
		<div class="dashboard-resumes-temp-access">
			<h3><?php echo __('Temporary Access to Resumes',APP_TD); ?></h3>
			<p><?php echo __('Your purchased Plans give you temporary access to Resumes:',APP_TD); ?></p>
		</div>
	<?php endif; ?>

	<p>
		<?php foreach ( $resumes_access['access'] as $key => $access ): ?>
			<?php echo sprintf( __( ' %s until <strong>%s</strong>', APP_TD ), $access['description'], $access['end_date'] ); ?><br/>
		<?php endforeach; ?>
	</p>

	<?php if ( $subscr_duration ): ?>
		<div class="subscription-duration <?php echo _jr_is_recurring_available() ? 'recurring-subscription' : ''; ?>">
			<a><i class="icon dashicons-before"></i></a><p><?php echo $subscr_duration_text; ?></p>
		</div>
	<?php endif; ?>

<?php endif; ?>

<div class="clear"></div>
