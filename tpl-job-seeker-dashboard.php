<?php
/**
 * Template Name: Job Seeker Dashboard
 */
?>
	<div class="section">

		<div class="section_content">

			<?php do_action( 'appthemes_notices' ); ?>

			<h1><?php printf( __( '%s\'s Dashboard', APP_TD ), ucwords( $userdata->user_login ) ); ?></h1>

			<ul class="display_section">

				<?php do_action( 'jr_dashboard_tab_before', 'job_seeker' ); ?>

				<li><a href="#dashboard" class="noscroll"><?php _e( 'Dashboard', APP_TD ); ?></a></li>
				<li><a href="#resumes" class="noscroll"><?php _e( 'Resumes', APP_TD ); ?></a></li>
				<?php if ( $can_subscribe ) : ?><li><a href="#subscriptions" class="noscroll"><?php _e( 'Subscriptions', APP_TD ); ?></a></li><?php endif; ?>
				<?php if ( jr_job_alerts_auth() ): ?>	<li><a href="#alerts" class="noscroll"><?php _e( 'Job Alerts', APP_TD ); ?></a></li>	<?php endif; ?>
				<?php if ( $show_orders ) : ?><li><a href="#orders" class="noscroll"><?php _e( 'Orders', APP_TD ); ?></a></li><?php endif; ?>

				<?php do_action( 'jr_dashboard_tab_after', 'job_seeker' ); ?>

				<li><a href="#prefs" class="noscroll"><?php _e( 'Preferences', APP_TD ); ?></a></li>

			</ul>

			<div id="dashboard" class="myprofile_section">
				<h2><?php _e( 'Dashboard', APP_TD ); ?></h2>

				<?php jr_before_job_seeker_dashboard(); ?>

				<h3><?php _e( 'Starred Jobs', APP_TD ); ?></h3>

				<?php if ( $my_starred_jobs = the_jr_user_starred_jobs() ): ?>
					<?php echo $my_starred_jobs; ?>
				<?php else: ?>
						<p><?php echo __( 'You have not starred any jobs yet. You can star jobs from the individual job listing pages.', APP_TD ); ?></p>
				<?php endif; ?>

				<h3><?php _e('Recently Viewed Jobs', APP_TD); ?></h3>

				<?php if ( $my_viewed_jobs = the_jr_user_viewed_jobs() ): ?>
					<?php echo $my_viewed_jobs; ?>
				<?php else: ?>
						<p><?php echo __( 'You have not viewed any jobs yet. When you do, the 5 most recent will display here.', APP_TD ); ?></p>
				<?php endif; ?>

				<h3><?php _e( 'Job Recommendations', APP_TD ); ?></h3>

				<?php if ( $my_recommendations = the_jr_user_job_recommendations() ): ?>
					<?php echo $my_recommendations; ?>
				<?php else: ?>
						<p><?php echo __( 'You don\'t have any recommendations: try adjusting your preferences in order to get more results.', APP_TD ); ?></p>
				<?php endif; ?>

				<?php jr_after_job_seeker_dashboard(); ?>

			</div>
			<div id="resumes" class="myprofile_section">
				<h2><?php _e( 'My Resumes', APP_TD ); ?></h2>
				<p><?php _e( 'Your resumes are displayed below. From this page you can create a new resume, edit a resume, and set whether or not it is visible on the site or private.', APP_TD ); ?></p>

				<table cellpadding="0" cellspacing="0" class="data_list">
					<thead>
						<tr>
							<th><?php _e( 'Resume Title', APP_TD ); ?></th>
							<th class="center"><?php _e( 'Date Created', APP_TD ); ?></th>
							<th class="center"><?php _e( 'Last Modified', APP_TD ); ?></th>
							<th class="center"><?php _e( 'Views', APP_TD ); ?></th>
							<th class="center"><?php _e( 'Visibility', APP_TD ); ?></th>
							<th class="right"><?php _e( 'Actions', APP_TD ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php $my_resumes = the_jr_user_resumes(); ?>

						<?php if ( $my_resumes->have_posts() ) : ?>

							<?php while ( $my_resumes->have_posts() ) : ?>

								<?php $my_resumes->the_post(); ?>

								<?php
									if ( $resume_views = get_post_meta( $my_resumes->post->ID, 'jr_total_count', true ) ) {
										$resume_views = number_format( $resume_views );
									} else {
										$resume_views = '-';
									}
								?>

								<tr>
									<td><strong><a href="<?php esc_url( the_permalink() ); ?>"><?php the_title(); ?></a></strong></td>
									<td class="date"><strong><?php the_time( 'j M' ); ?></strong> <span class="year"><?php the_time( 'Y' ); ?></span></td>

									<td class="date"><strong><?php echo date( 'j M', strtotime( $my_resumes->post->post_modified ) ); ?></strong>
										<span class="year"><?php echo date( 'Y', strtotime( $my_resumes->post->post_modified ) ); ?></span>
									</td>

									<td class="center"><?php echo $resume_views; ?></td>
									<td class="center"><?php echo jr_post_statuses_i18n( $my_resumes->post->post_status ); ?></td>

									<td class="actions">
										<a href="<?php echo esc_url( jr_get_the_resume_edit_link() ); ?>"><?php _e( 'Edit&nbsp;&rarr;', APP_TD ); ?></a>&nbsp;
										<a href="<?php echo esc_url( jr_get_the_resume_toggle_vis_link() ); ?>"><?php echo ( $my_resumes->post->post_status == 'private' ? __( 'Publish', APP_TD ) : __( 'Hide', APP_TD ) ); ?></a>&nbsp;
										<a href="<?php echo esc_url( jr_get_the_resume_delete_link() ); ?>" class="delete-resume"><?php _e( 'Delete', APP_TD ); ?></a>
									</td>
								</tr>

							<?php endwhile; ?>

						<?php else : ?>
								<tr><td colspan="6"><?php _e( 'No resumes found.', APP_TD ); ?></td></tr>
						<?php endif; ?>

						<?php wp_reset_query(); ?>
					</tbody>
				</table>


				<form class="submit_form main_form" method="post" action="<?php echo esc_url( get_permalink( JR_Resume_Edit_Page::get_id() ) ); ?>">
					<p><input type="submit" class="submit" value="<?php _e('Add Resume &raquo;', APP_TD)?>" /></p>
				</form>

				<div class="clear"></div>

			</div>

			<div id="subscriptions" class="myprofile_section">
				<h2><?php _e( 'Resume Subscriptions ', APP_TD ); ?></h2>
				<?php appthemes_load_template( array( 'dashboard-resumes.php', 'includes/dashboard-resumes.php' ) ); ?>
			</div>

			<div id="alerts" class="myprofile_section">
				<h2><?php _e( 'My Job Alerts', APP_TD ); ?></h2>
				<?php jr_job_seeker_alerts_form(); ?>
			</div>

			<div id="orders" class="myprofile_section">
				<h2><?php _e( 'Orders', APP_TD ); ?></h2>
				<?php appthemes_load_template( array( 'dashboard-orders.php', 'includes/dashboard-orders.php' ) ); ?>
			</div>

			<div id="prefs" class="myprofile_section">
				<h2><?php _e( 'My Preferences', APP_TD ); ?></h2>
				<?php jr_job_seeker_prefs_form(); ?>
			</div>

			<?php do_action( 'jr_dashboard_tab_content', 'job_seeker' ); ?>

		</div><!-- end section_content -->

	</div><!-- end section -->

	<div class="clear"></div>

</div><!-- end main content -->

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar('user'); endif; ?>
