<?php
/**
 * Main loop for displaying jobs
 *
 * @package JobRoller
 * @author AppThemes
 */
?>

<?php appthemes_before_loop( APP_POST_TYPE ); ?>

<?php if (have_posts()) : $alt = 1; ?>

    <ol class="jobs">

        <?php while (have_posts()) : the_post(); ?>

      <?php appthemes_before_post( APP_POST_TYPE ); ?>

            <?php
        $post_class = array('job');
        $expired = jr_check_expired( $post );

        if ( $expired ) {
          $post_class[] = 'job-expired';
        }
        $alt = $alt * -1;

        if ( $alt == 1 ) {
          $post_class[] = 'job-alt';
        }

        if ( jr_is_listing_featured( $post->ID ) ) {
          $post_class[] = 'job-featured';
        }

            ?>

            <li class="<?php echo implode(' ', $post_class); ?>">

                <dl>

                    <dt><?php _e('Type',APP_TD); ?></dt>
                    <dd class="type"><?php jr_get_custom_taxonomy($post->ID, 'job_type', 'ftype'); ?></dd>

                    <dt><?php _e('Job', APP_TD); ?></dt>

          <?php appthemes_before_post_title( APP_POST_TYPE ); ?>

                    <dd class="title">
            <strong><a href="<?php esc_url( the_permalink() ); ?>"><?php the_title(); ?></a></strong>
            <?php jr_job_author(); ?>
                    </dd>

          <?php appthemes_after_post_title( APP_POST_TYPE ); ?>

                    <dt><?php _e('Location', APP_TD); ?></dt>
          <dd class="location"><?php jr_location(); ?></dd>

                    <dt><?php _e('Date Posted', APP_TD); ?></dt>
                    <dd class="date"><strong><?php echo date_i18n( __( 'j M', APP_TD ), strtotime( $post->post_date ) ); ?></strong> <span class="year"><?php echo date_i18n( __( 'Y', APP_TD ), strtotime( $post->post_date ) ); ?></span></dd>

                </dl>

            </li>

      <?php appthemes_after_post( APP_POST_TYPE ); ?>

        <?php endwhile; ?>

    <?php appthemes_after_endwhile( APP_POST_TYPE ); ?>

    </ol>

<?php else: ?>

  <?php appthemes_loop_else( APP_POST_TYPE ); ?>

<?php endif; ?>

<?php appthemes_after_loop( APP_POST_TYPE ); ?>
