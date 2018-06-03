<div id="packs" class="myjobs_section">
	<h2 class="pack_select dashboard"><?php _e( 'My Packs', APP_TD ); ?></h2>

	<?php if ( $user_packs = jr_get_user_packs( $user_ID ) ): ?>
		<p><?php echo __( 'Below you will find a list of active packs you have purchased.', APP_TD ); ?></p>
	<?php else: ?>
		<p><?php echo __( 'No active packs found.', APP_TD ); ?></p>
	<?php endif; ?>

	<?php jr_display_the_user_packs(); ?>

	<?php if ( jr_allow_purchase_separate_packs() ): ?>
		<?php the_purchase_pack_link(); ?>
	<?php endif; ?>
</div>