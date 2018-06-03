<?php if ( jr_search_bar_active() ): ?>
	<form action="<?php echo esc_url( home_url() ); ?>/" method="get" id="searchform">

		<div class="search-wrap">

			<div>
				<input type="text" id="search" title="" name="s" class="text" placeholder="<?php _e( 'All Jobs', APP_TD ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" />
				<input type="text" id="near" title="<?php esc_attr_e( 'Location', APP_TD ); ?>" name="location" class="text" placeholder="<?php esc_attr_e( 'Location', APP_TD ); ?>" value="<?php echo esc_attr( $s_location ); ?>" />
				<label for="search"><button type="submit" title="<?php esc_attr_e( 'Go', APP_TD ); ?>" class="submit"><?php _e( 'Go', APP_TD ); ?></button></label>

				<input type="hidden" name="ptype" value="<?php echo esc_attr( APP_POST_TYPE ); ?>" />

				<input type="hidden" name="latitude" id="field_latitude" value="" />
				<input type="hidden" name="longitude" id="field_longitude" value="" />
				<input type="hidden" name="full_address" id="field_full_address" value="" />
				<input type="hidden" name="north_east_lng" id="field_north_east_lng" value="" />
				<input type="hidden" name="south_west_lng" id="field_south_west_lng" value="" />
				<input type="hidden" name="north_east_lat" id="field_north_east_lat" value="" />
				<input type="hidden" name="south_west_lat" id="field_south_west_lat" value="" />
			</div>

			<?php jr_radius_dropdown(); ?>

		</div><!-- end search-wrap -->

	</form>

<?php endif; ?>
