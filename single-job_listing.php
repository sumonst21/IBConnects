  <?php get_header('search'); ?>

  <div class="section single">

  <?php do_action( 'appthemes_notices' ); ?>

  <?php appthemes_before_loop(); ?>

    <?php if ( have_posts() ) : ?>

      <?php while ( have_posts() ) : the_post(); ?>

        <?php appthemes_before_post(); ?>

        <?php appthemes_stats_update( $post->ID ); //records the page hit ?>

        <div class="section_header">

          <div class="date">
            <strong><?php the_time( __( 'j M', APP_TD ) ); ?></strong>
            <span class="year"><?php the_time( __( 'Y', APP_TD ) ); ?></span>
          </div>

          <?php appthemes_before_post_title( APP_POST_TYPE ); ?>

          <h1 class="title">
            <span class="type"><?php jr_get_custom_taxonomy( $post->ID, 'job_type', 'jtype' ); ?></span>

            <?php the_title(); ?>
          </h1>

          <?php appthemes_after_post_title( APP_POST_TYPE ); ?>

          <p class="meta">
            <?php jr_job_author(); ?>

            &ndash;

            <?php jr_location( true ); ?>
          </p>

          <div class="clear"></div>

        </div><!-- end section_header -->

        <div class="section_content">

          <?php do_action('job_main_section', $post); ?>

          <?php if ( $jr_options->jr_sharethis_id ): ?>

            <p class="sharethis">
              <span class="st_twitter_hcount" displayText="Tweet"></span>
              <span class="st_facebook_hcount" displayText="Share"></span>
            </p>

          <?php endif; ?>

          <?php if ( has_post_thumbnail() ): the_post_thumbnail(); endif; ?>

          <h2><?php _e( 'Job Description', APP_TD ); ?></h2>

          <?php appthemes_before_post_content(); ?>

          <?php the_content(); ?>

          <?php the_job_listing_fields(); ?>

          <?php the_job_listing_files(); ?>

          <?php appthemes_after_post_content(); ?>

          <?php if ( $jr_options->jr_enable_listing_banner ): ?>

            <div id="listingAd"><?php echo stripslashes( $jr_options->jr_listing_banner ); ?></div>

          <?php endif; ?>

          <?php if ( $jr_options->jr_submit_how_to_apply_display && $how_to_apply = jr_get_the_how_to_apply() ): ?>

            <h2 class="how-to-apply"><?php _e( 'How to Apply', APP_TD ) ?></h2>

            <?php echo $how_to_apply; ?>

          <?php endif; ?>

          <p class="meta meta-taxonomies">
            <em>
              <span class="job-taxonomies"><i class="icon dashicons-before"></i> <?php the_taxonomies(); ?></span>

              <?php if ( ! jr_check_expired( $post ) ): ?>

                <?php if ( jr_job_expires( $post ) ): ?>

                  <div class="job-expires-in"><i class="icon dashicons-before"></i> <?php echo sprintf( __( 'Job expires in <strong>%s</strong>', APP_TD ), jr_remaining_days( $post ) ); ?>.</div>

                <?php else: ?>

                  <div class="job-expires-in"><i class="icon dashicons-before"></i> <strong><?php echo jr_remaining_days( $post ); ?></strong>.</div>

                <?php endif; ?>

              <?php endif; ?>

            </em>

            <?php if ( $stats_counter = jr_get_the_stats_counter() ): ?>

              <p class="stats"><?php echo $stats_counter; ?></p>

            <?php endif; ?>

          </p>

        </div><!-- end section_content -->

        <?php do_action('job_footer'); ?>

        <ul class="section_footer" style="display:none;">

          <?php if ( current_user_can( 'apply_to_job', $post->ID ) ): ?>

            <li class="apply">
              <i class="icon dashicons-before"></i>
              <a href="#apply_form" class="apply_online"><?php _e( 'Apply Online', APP_TD ); ?></a>
            </li>

          <?php endif ?>

          <?php if ( current_user_can( 'star_job', $post->ID ) ): ?>

              <?php $starred = jr_get_the_curr_user_starred_jobs(); ?>

              <?php if ( ! in_array( $post->ID, $starred ) ) : ?>

                <li class="star">
                  <i class="icon dashicons-before"></i>
                  <a href="<?php echo esc_url( add_query_arg( 'star', 'true', get_permalink() ) ); ?>" class="star"><?php _e( 'Star Job', APP_TD ); ?></a>
                </li>

              <?php else : ?>

                <li class="star">
                  <i class="icon dashicons-before"></i>
                  <a href="<?php echo esc_url( add_query_arg( 'star', 'false', get_permalink() ) ); ?>" class="star"><?php _e( 'Un-star Job', APP_TD ); ?></a>
                </li>

              <?php endif; ?>

          <?php endif; ?>

          <li class="print">
            <i class="icon dashicons-before"></i>
            <a href="javascript:window.print();"><?php _e( 'Print Job', APP_TD ); ?></a>
          </li>

          <?php if ( jr_get_the_coordinate('latitude') && jr_get_the_coordinate('longitude') ): ?>

            <li class="map">
              <i class="icon dashicons-before"></i>
              <a href="#map" class="toggle_map"><?php _e( 'View Map', APP_TD ); ?></a>
            </li>

          <?php endif; ?>

          <?php if ( function_exists('selfserv_sexy') ): ?>

            <li class="sexy share">
              <a href="#share_form" class="share"><?php _e( 'Share Job', APP_TD ); ?></a>
            </li>

          <?php endif; ?>

          <?php if ( get_the_jr_job_edit_link() ): ?>

            <li class="edit-job">
              <i class="icon dashicons-before"></i>
              <?php the_job_edit_link(); ?>
            </li>

          <?php endif; ?>

        </ul>

        <?php comments_template(); ?>

        <?php appthemes_after_post(); ?>

      <?php endwhile; ?>

        <?php appthemes_after_endwhile(); ?>

    <?php else: ?>

      <?php appthemes_loop_else(); ?>

    <?php endif; ?>

    <?php appthemes_after_loop(); ?>

  </div><!-- end section -->

  <div class="clear"></div>

</div><!-- end main content -->

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar('job'); endif; ?>
