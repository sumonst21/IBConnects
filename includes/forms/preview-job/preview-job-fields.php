<ol class="jobs">
	<li class="job" style="padding-left:0; padding-right:0;">
		<dl>
			<dt><?php _e( 'Type', APP_TD ); ?></dt>
			<dd class="type">
				<?php $job_type = get_term_by( 'id', (int) $job->type, APP_TAX_TYPE ); ?>
				<span class="ftype <?php echo esc_attr( $job_type->slug ); ?>"><?php echo wptexturize( $job_type->name ); ?></span>
				&nbsp;
			</dd>

			<dt><?php _e( 'Job', APP_TD ); ?></dt>
			<dd class="title"><strong><?php echo $job->post_title; ?> </strong>
				<?php
					$author = get_user_by('id', get_current_user_id());

					if ( $job->your_name ) {

						echo $job->your_name;

						if ( $author && $link = get_author_posts_url( $author->ID, $author->user_nicename ) ) {
							echo sprintf( __( ' &ndash; Posted by <a href="%s">%s</a>', APP_TD ), $link, $author->display_name );
						}

					} else {

						if ( $author && $link = get_author_posts_url( $author->ID, $author->user_nicename ) ) {
							echo sprintf( __( '<a href="%s">%s</a>', APP_TD ), $link, $author->display_name );
						}

					}
				?>
			</dd>

			<dt><?php _e( '_Location', APP_TD ); ?></dt>
			<dd class="location">
				<?php if ( $job->jr_geo_short_address ): ?>
					<strong><?php echo wptexturize( $job->jr_geo_short_address ); ?></strong><?php wptexturize( $job->jr_geo_short_address_country ); ?>
				<?php else: ?>
					<strong><?php echo __( 'Anywhere', APP_TD ); ?></strong>
				<?php endif; ?>
			</dd>

			<dt><?php _e('Date Posted', APP_TD); ?></dt>
			<dd class="date">
				<strong><?php echo date_i18n( __( 'j M', APP_TD ) ); ?></strong>
				<span class="year"><?php echo date_i18n( __( 'Y', APP_TD ) ); ?></span>
			</dd>

		</dl>
	</li>
</ol>

<p><?php _e( 'The job listing&rsquo;s page will contain the following information:', APP_TD ); ?></p>

<div class="job-preview-fields">
	<?php foreach( $preview_fields as $label => $value ): ?>

		<div class="job-preview-field">
			<h4><?php echo $label; ?></h4>
			<span><?php echo $value; ?></span>
		</div>

	<?php endforeach; ?>
</div>