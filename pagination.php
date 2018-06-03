<div class="clear"></div>

<div class="paging">

	<?php if ( jr_is_jobs_front_archive() ): ?>

		<a href="<?php echo esc_url( get_post_type_archive_link( APP_POST_TYPE ) ); ?>" title="<?php echo esc_attr( __( 'More...', APP_TD ) ); ?>"><?php echo esc_attr( __( 'More...', APP_TD ) ); ?></a>

	<?php else: ?>

		<?php
			// loads 'wp_pagenavi' if exists or 'appthemes_pagenavi', can also be filtered to add a custom paginator
			jr_load_paginator( $query, $query_var, $args );
		?>

	<?php endif; ?>

	<div class="top"><a href="#top" title="<?php esc_attr_e( 'Back to top', APP_TD ); ?>"><?php _e( 'Top &uarr;', APP_TD ); ?></a></div>
</div>