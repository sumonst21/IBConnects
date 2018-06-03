	<?php get_header('resume-search'); ?>

	<?php do_action( 'before_resumes_archive' ); ?>

    <div class="section">

		<?php do_action( 'appthemes_notices' ); ?>

		<?php if ( jr_resume_is_visible() ): ?>

	        <h1 class="pagetitle">
				<?php echo sprintf( __( 'Resumes%s', APP_TD ), ( ! empty( $paged ) && $paged > 1 ? ' ' . sprintf( __( '(page %d)', APP_TD ), $paged ) : '' ) ); ?>
				<?php echo $filter_text; ?>
			</h1>

	        <?php appthemes_load_template('loop-resume.php'); ?>

	        <?php jr_paging(); ?>

        <?php else : ?>

			<h1 class="pagetitle"><?php _e( 'Resumes', APP_TD ); ?></h1>

			<?php if ( jr_current_user_can_subscribe_for_resumes() ): ?>

        		<?php if ( $notice = $jr_options->jr_resume_subscription_notice ): ?>
					<p><?php echo wptexturize( $notice ); ?></p>
				<?php endif; ?>

				<?php the_resume_purchase_plan_link(); ?>

			<?php else: ?>

				<?php jr_no_access_permission( __( 'Sorry, you do not have permission to Browse or View Resumes.', APP_TD ) ); ?>

			<?php endif; ?>

        <?php endif; ?>

        <div class="clear"></div>

    </div><!-- end section -->

	<?php do_action( 'after_resumes_archive' ); ?>

    <div class="clear"></div>

</div><!-- end main content -->

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar('resume'); endif ?>
