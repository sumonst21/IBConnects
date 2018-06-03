<?php
/**
 * Main loop for displaying resumes
 *
 * @package JobRoller
 * @author AppThemes
 *
 */
?>

<?php appthemes_before_loop( APP_POST_TYPE_RESUME ); ?>

<?php if (have_posts()) : $alt = 1; ?>

    <ol class="resumes">

        <?php while (have_posts()) : the_post(); ?>

			<?php appthemes_before_post( APP_POST_TYPE_RESUME ); ?>

            <li class="resume" title="<?php echo esc_attr( htmlspecialchars( jr_seeker_prefs( get_the_author_meta('ID') ), ENT_QUOTES ) ); ?>">

                <dl>

					<?php appthemes_before_post_title( APP_POST_TYPE_RESUME ); ?>

                    <dt><?php _e('Resume title', APP_TD); ?></dt>

                    <dd class="title">

						<strong><a href="<?php esc_url( the_permalink() ); ?>"><?php the_title(); ?></a></strong>

					<?php
						if ( 'public' != $jr_options->jr_resume_listing_visibility ) {
							the_resume_posted_by( __( 'Resume posted by ', APP_TD ) );
						}

						$terms = wp_get_post_terms( $post->ID, APP_TAX_RESUME_CATEGORY );
						if ($terms): ?>

							<?php echo __( ' in ',APP_TD ); ?><a href="<?php echo esc_url( get_term_link( $terms[0]->slug, APP_TAX_RESUME_CATEGORY ) ); ?>"><?php echo $terms[0]->name; ?></a>

						<?php endif; ?>

                    </dd>

					<?php appthemes_after_post_title( APP_POST_TYPE_RESUME ); ?>

					<dt><?php _e('Photo',APP_TD); ?></dt>
                    <dd class="photo"><a href="<?php esc_url( the_permalink() ); ?>"><?php if ( has_post_thumbnail() ): the_post_thumbnail('listing-thumbnail'); endif; ?></a></dd>

                    <dt><?php _e('Location', APP_TD); ?></dt>
					<dd class="location"><?php jr_location(); ?></dd>

                    <dt><?php _e('Date Posted', APP_TD); ?></dt>
                    <dd class="date"><strong><?php echo date_i18n( 'j M', strtotime( $post->post_date ) ); ?></strong> <span class="year"><?php echo date_i18n( 'Y', strtotime( $post->post_date ) ); ?></span></dd>

                </dl>

            </li>

			<?php appthemes_after_post( APP_POST_TYPE_RESUME ); ?>

        <?php endwhile; ?>

		<?php appthemes_after_endwhile( APP_POST_TYPE_RESUME ); ?>

    </ol>

<?php else: ?>

	<?php appthemes_loop_else( APP_POST_TYPE_RESUME ); ?>

<?php endif; ?>

<?php appthemes_after_loop( APP_POST_TYPE_RESUME ); ?>
