<?php
/**
 * JobRoller Filter Form.
 * Outputs the job filters form.
 *
 * @version 1.0
 * @author AppThemes
 * @package JobRoller\Forms\Filter
 * @copyright 2010 all rights reserved
 */

function jr_filter_form() {
	global $jr_options;

	if ( ! $jr_options->jr_show_filterbar || is_tax( APP_TAX_TYPE ) ) {
		return;
	}
?>
	<form class="filter" method="get" action="<?php echo esc_url( get_the_jr_jobs_base_url() ); ?>">
<?php
		$job_types = get_terms( 'job_type', array( 'hide_empty' => '0' ) );
		if ( $job_types && sizeof( $job_types ) > 0 ) :
			foreach ( $job_types as $type ) : ?>
				<p>
					<input type="checkbox" name="<?php echo esc_attr( $type->slug ); ?>" id="<?php echo esc_attr( $type->slug ); ?>" <?php checked( ! empty( $_GET[ $type->slug ] ) || empty( $_GET['action'] ) ); ?> value="show" />
					<label for="<?php echo esc_attr( $type->slug ); ?>"><?php echo $type->name; ?></label>
				</p>
<?php
			endforeach;
		endif;
?>
		<p>
			<input type="submit" value="<?php esc_attr_e( 'Filter', APP_TD ); ?>" class="submit" />
			<input type="hidden" name="action" value="Filter" />
<?php
			// hidden fields for date filters
			if ( ! empty( $_GET['time'] ) ) {
				echo '<input type="hidden" name="time" value="' . esc_attr( $_GET['time'] ) . '" />';
				echo '<input type="hidden" name="jobs_by_date" value="1" />';
			}
			// hidden fields for search
			if ( ! empty( $_GET['s'] ) ) {
				echo '<input type="hidden" name="s" value="' . esc_attr( $_GET['s'] ) . '" />';
			}
			if ( ! empty( $_GET['location'] ) ) {
				echo '<input type="hidden" name="location" value="' . esc_attr( $_GET['location'] ) . '" />';
			}
			if ( ! empty( $_GET['radius'] ) ) {
				echo '<input type="hidden" name="radius" value="' . esc_attr( $_GET['radius'] ) . '" />';
			}
?>
		</p>

		<?php if ( jr_is_job_filter() ): ?>
			<span class="job-filter"> &mdash; <a href="<?php echo esc_url( get_the_jr_jobs_base_url() ); ?>"><?php _e( 'Remove Filters', APP_TD ); ?></a></span>
		<?php endif; ?>

		<div class="clear"></div>
	</form>
<?php
}
