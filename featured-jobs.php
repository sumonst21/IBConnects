<div class="section">
	<h2 class="pagetitle">
		<small class="rss">
			<a href="<?php echo esc_url( jr_get_featured_jobs_rss_url() ); ?>"><i class="icon dashicons-before"></i></a>
		</small>
		<?php _e( 'Featured Jobs', APP_TD ); ?>
	</h2>

	<ol class="jobs">

		<?php while ( $featured_jobs->have_posts() ) : $featured_jobs->the_post(); ?>

			<?php
				$post_class = array( 'job', 'job-featured' );

				$found = true;

				$alt = $alt * -1;
				if ( $alt == 1 ) {
					$post_class[] = 'job-alt';
				}
			?>

			<li class="<?php echo implode( ' ', $post_class ); ?>">
				<dl>
					<dt><?php _e( 'Type', APP_TD ); ?></dt>
					<dd class="type"><?php jr_get_custom_taxonomy( $post->ID, 'job_type', 'ftype' ); ?></dd>

					<dt><?php _e( 'Job', APP_TD ); ?></dt>
					<dd class="title">
						<strong><a href="<?php esc_url( the_permalink() ); ?>"><?php the_title(); ?></a></strong>
						<?php jr_job_author(); ?>
					</dd>

					<dt><?php _e( 'Location', APP_TD ); ?></dt>
					<dd class="location"><?php jr_location(); ?></dd>

					<dt><?php _e( 'Date Posted', APP_TD ); ?></dt>
					<dd class="date">
						<strong><?php echo date_i18n( __( 'j M', APP_TD ), strtotime( $featured_jobs->post->post_date ) ); ?></strong>
						<span class="year"><?php echo date_i18n( __( 'Y', APP_TD ), strtotime( $featured_jobs->post->post_date ) ); ?></span>
					</dd>
				</dl>
			</li>
		<?php endwhile; ?>
	</ol>
</div><!-- End section -->
