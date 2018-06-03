	<?php get_header('search'); ?>

    <div class="section" id="profile">

		<div class="section_content">

	        <?php echo get_avatar( $wp_query->get_queried_object()->ID, '192' ); ?>

			<h1>
				<?php echo wptexturize( $wp_query->get_queried_object()->display_name ); ?>
				<?php if ( $url = $wp_query->get_queried_object()->user_url ): ?>
					&ndash; <a href="<?php echo esc_url( $url ) ?>"><?php echo strip_tags($url); ?></a>
				<?php endif; ?>
			</h1>

			<?php if ( isset( $wp_query->get_queried_object()->description ) && ! empty( $wp_query->get_queried_object()->description ) ): ?>
				<?php echo wpautop( wptexturize( $wp_query->get_queried_object()->description ) ); ?>
			<?php endif; ?>

			<?php
				// @todo: move to AppThemes social API

				$social = array();
				if ($twitter = get_user_meta( $wp_query->get_queried_object()->ID, 'twitter_id', true)) :
					$social[] = '<li class="twitter"><i class="fa"></i><a href="https://twitter.com/'.urlencode( $twitter ).'">'. esc_html( sprintf( __('Follow %s on Twitter', APP_TD), $wp_query->get_queried_object()->display_name ) ).'</a></li>';
				endif;

				if ($facebook = get_user_meta( $wp_query->get_queried_object()->ID, 'facebook_id', true)) :
					$social[] = '<li class="facebook"><i class="fa"></i><a href="https://facebook.com/'.urlencode( $facebook ).'">'. esc_html( sprintf( __('Add %s on Facebook', APP_TD), $wp_query->get_queried_object()->display_name ) ).'</a></li>';
				endif;

				if ($linkedin = get_user_meta( $wp_query->get_queried_object()->ID, 'linkedin_profile', true)) :
					$social[] = '<li class="linkedin"><i class="fa"></i><a href="'.esc_url( $linkedin ).'">'. esc_html( sprintf( __('View %s on LinkedIn', APP_TD), $wp_query->get_queried_object()->display_name ) ).'</a></li>';
				endif;

				if (sizeof($social)>0) :
					echo '<ul class="social">'.implode('', $social).'</ul>';
				endif;
			?>

			<div class="clear"></div>

		</div>

		<?php if ( ! empty( $_GET['blog_posts'] ) ): ?>

				<h2 class="pagetitle"><?php echo esc_html( sprintf( __('%s\'s posts', APP_TD), $wp_query->get_queried_object()->display_name) ); ?></h2>

				<?php jr_output_author_blog_posts( $wp_query->get_queried_object()->ID ); ?>

		<?php elseif( user_can( $wp_query->get_queried_object()->ID, 'can_submit_job' ) ): ?>
			<h2 class="pagetitle"><?php echo esc_html( sprintf( __('%s\'s job listings', APP_TD), $wp_query->get_queried_object()->display_name) ); ?></h2>

			<?php jr_output_author_jobs( $wp_query->get_queried_object()->ID ); ?>

		<?php else : ?>

			<h2><?php echo esc_html( sprintf( __('%s\'s resumes', APP_TD), $wp_query->get_queried_object()->display_name) ); ?></h2>

			<?php jr_output_author_resumes( $wp_query->get_queried_object()->ID ); ?>

		<?php endif; ?>

		<div class="clear"></div>

    </div><!-- end section -->

    <div class="clear"></div>

</div><!-- end main content -->

<?php wp_reset_query(); ?>

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar(); endif; ?>
