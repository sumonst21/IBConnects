<?php
/**
 * Template file for the external jobs feed HTML (Indeed, SimplyHired, LinkedIn and Careerjet).
 */
?>
<li class="<?php echo esc_attr( implode( ' ', $post_class ) ); ?>" <?php echo ( $is_ajax && $first ? 'id="' . esc_attr( "more-{$page}" ) . '"' : '' ); ?>>
	<dl>
		<dt><?php _e( 'Type', APP_TD ); ?></dt>
		<dd class="type"><span class="ftype <?php echo esc_attr( $job['jobtype'] ); ?>"><?php echo ucwords( esc_html( $job['jobtype_name'] ) ); ?></span></dd>

		<dt><?php _e( 'Job', APP_TD ); ?></dt>
		<dd class="title">
			<strong><a href="<?php echo esc_url( $job['url'] ); ?>" target="_blank" rel="nofollow" onmousedown="<?php echo esc_attr( $job['onmousedown'] ); ?>"><?php echo esc_html( $job['jobtitle'] ); ?></a></strong>
			<?php echo wptexturize( $job['company'] ); ?>
		</dd>

		<dt><?php _e( 'Location', APP_TD ); ?></dt>
		<dd class="location"><strong><?php echo esc_html( $job['location'] ); ?></strong> <?php echo esc_html( $job['country'] ); ?></dd>

		<dt><?php _e( 'Date Posted', APP_TD ); ?></dt>
		<dd class="date"><strong><?php echo date_i18n( 'j M', strtotime( $job['date'] ) ); ?></strong> <span class="year"><?php echo date_i18n( 'Y', strtotime( $job['date'] ) ); ?></span></dd>
	</dl>
</li>

