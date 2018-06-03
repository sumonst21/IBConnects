<?php
/**
 * Template Name: My Jobs Template
 */
?>
	<div class="section myjobs">

		<div class="section_content">

		<?php do_action( 'appthemes_notices' ); ?>

			<h1><?php printf( __( "%s's Dashboard", APP_TD ), ucwords( $userdata->user_login ) ); ?></h1>

		<ul class="display_section">
			<?php do_action( 'jr_dashboard_tab_before', 'job_lister' ); ?>

			<li><a href="#live" class="noscroll"><?php _e( 'Live', APP_TD ); ?></a></li>
			<li><a href="#pending" class="noscroll"><?php _e( 'Pending', APP_TD ); ?></a></li>
			<li><a href="#ended" class="noscroll"><?php _e( 'Ended', APP_TD ); ?></a></li>
			<?php if ( 'pack' == $jr_options->plan_type && jr_charge_job_listings() ) : ?><li><a href="#packs" class="noscroll"><?php _e( 'Job Packs', APP_TD ); ?></a></li><?php endif; ?>
			<?php if ( $can_subscribe ): ?><li><a href="#subscriptions" class="noscroll"><?php _e( 'Subscriptions', APP_TD ); ?></a></li><?php endif; ?>
			<?php if ( $show_orders ): ?><li><a href="#orders" class="noscroll"><?php _e( 'Orders', APP_TD ); ?></a></li><?php endif; ?>

			<?php do_action( 'jr_dashboard_tab_after', 'job_lister' ); ?>
		</ul>

		<div id="live" class="myjobs_section">

			<h2><?php _e('Live Jobs', APP_TD); ?></h2>

			<?php $live_jobs = the_jr_user_live_jobs(); ?>

			<?php if ( $live_jobs->have_posts() ) : ?>

				<p><?php _e('Below you will find a list of jobs you have previously posted which are visible on the site.', APP_TD); ?></p>

				<table cellpadding="0" cellspacing="0" class="data_list footable">
					<thead>
						<tr>
							<th data-class="expand"><?php _e('Job Title',APP_TD); ?></th>
							<th class="center" data-hide="phone"><?php _e('Date Posted',APP_TD); ?></th>
							<th class="center" data-hide="phone"><?php _e('Days Remaining',APP_TD); ?></th>
							<th class="center" data-hide="phone"><?php _e('Views',APP_TD); ?></th>
							<th class="right" data-hide="phone"><?php _e('Actions',APP_TD); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php while ( $live_jobs->have_posts() ) : ?>

							<?php $live_jobs->the_post(); ?>

							<?php
								if ( $job_views = get_post_meta( $live_jobs->post->ID, 'jr_total_count', true ) ) {
									$job_views = number_format( $job_views );
								} else {
									$job_views = '-';
								}
							?>

							<?php if ( jr_check_expired( $post ) ): continue; endif; ?>

							<tr>
								<td>
									<strong><a href="<?php esc_url( the_permalink() ); ?>"><?php the_title(); ?></a><?php the_job_addons(); ?></strong>
								</td>
								<td class="date">
									<strong><?php the_time(__('j M',APP_TD)); ?></strong> <span class="year"><?php the_time(__('Y',APP_TD)); ?></span>
								</td>
								<td class="center days">
									<?php echo jr_remaining_days($live_jobs->post); ?>
								</td>
								<td class="center">
									<?php echo $job_views; ?>
								</td>
								<td class="actions">
									<?php the_job_edit_link( $live_jobs->post->ID ); ?>
									<?php the_job_end_link( $live_jobs->post->ID ); ?>
								</td>
							</tr>

						<?php endwhile; ?>
					</tbody>
				</table>

				<?php jr_paging( $live_jobs, 'paged', array ( 'add_args' => array( 'tab' => 'live' ) ) ); ?>

			<?php else: ?>
				<p><?php _e('No live jobs found.',APP_TD); ?></p>
			<?php endif; ?>

		</div>

		<?php if ( $buy_packs ): ?>
			<?php appthemes_load_template( array( 'dashboard-packs.php', 'includes/dashboard-packs.php' ) ); ?>
		<?php endif; ?>

		<div id="pending" class="myjobs_section">

			<h2><?php _e( 'Pending Jobs', APP_TD ); ?></h2>

			<?php $pending_jobs = the_jr_user_pending_jobs(); ?>

			<?php if ( $pending_jobs->have_posts() ): ?>

				<p><?php _e('The following jobs are pending and are not visible to users.', APP_TD); ?></p>

				<table cellpadding="0" cellspacing="0" class="data_list footable">
					<thead>
						<tr>
							<th data-class="expand"><?php _e( 'Job Title', APP_TD ); ?></th>
							<th class="center" data-hide="phone"><?php _e( 'Date Posted', APP_TD ); ?></th>
							<th class="center" data-hide="phone"><?php _e( 'Status', APP_TD ); ?></th>
							<th class="right" data-hide="phone"><?php _e( 'Actions', APP_TD ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php while ( $pending_jobs->have_posts() ): $pending_jobs->the_post(); ?>
							<tr>
								<td>
									<?php // only users with 'edit_jobs' capability can preview pending jobs ?>
									<?php if ( current_user_can( 'edit_jobs', $post->ID ) ): ?>
											<strong><a href="<?php esc_url( the_permalink() ); ?>"><?php the_title(); ?></a></strong>
									<?php else: ?>
											<strong><?php the_title(); ?></strong>
									<?php endif; ?>
								</td>
								<td class="date">
									<strong><?php the_time(__('j M',APP_TD)); ?></strong> <span class="year"><?php the_time(__('Y',APP_TD)); ?></span>
								</td>
								<td class="center">
									<?php
										$job_status = jr_get_job_status( $pending_jobs->post, $pending_payment_jobs );

										if ( $order_status = jr_get_job_order_status( $pending_jobs->post, $pending_payment_jobs ) ) {
											echo sprintf( ' %s', $order_status  );
										} else {
											echo sprintf( ' %s', $job_status );
										}
									?>
								</td>
								<td class="actions"><?php the_job_actions( $pending_jobs->post, $pending_payment_jobs ); ?></td>
							</tr>
						<?php endwhile; ?>
					</tbody>
				</table>

				<?php jr_paging( $pending_jobs, 'paged', array ( 'add_args' => array( 'tab' => 'pending' ) ) ); ?>

			<?php else : ?>
				<p><?php _e('No pending jobs found.', APP_TD); ?></p>
			<?php endif; ?>

		</div>

		<div id="ended" class="myjobs_section">

			<h2><?php _e('Ended/Expired Jobs', APP_TD); ?></h2>

			<?php $ended_jobs = the_jr_user_ended_jobs(); ?>

			<?php if ( $ended_jobs->have_posts() ): ?>

			<p><?php _e('The following jobs have expired or have been ended and are not visible to users.', APP_TD); ?></p>

			<table cellpadding="0" cellspacing="0" class="data_list footable">
				<thead>
					<tr>
						<th data-class="expand"><?php _e( 'Job Title', APP_TD ); ?></th>
						<th class="center" data-hide="phone"><?php _e( 'Date Posted', APP_TD ); ?></th>
						<th class="center" data-hide="phone"><?php _e( 'Status', APP_TD ); ?></th>
						<th class="center" data-hide="phone"><?php _e( 'Views', APP_TD ); ?></th>
						<th class="right" data-hide="phone"><?php _e( 'Actions', APP_TD ); ?></th>
					</tr>
				</thead>
				<tbody>

				<?php while ( $ended_jobs->have_posts() ): $ended_jobs->the_post(); ?>

					<?php
						if ( $job_views = get_post_meta( $ended_jobs->post->ID, 'jr_total_count', true ) ) {
							$job_views = number_format( $job_views );
						} else {
							$job_views = '-';
						}
					?>

					<tr>
						<td><strong><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></strong></td>
						<td class="date"><strong><?php the_time(__('j M',APP_TD)); ?></strong> <span class="year"><?php the_time(__('Y',APP_TD)); ?></span></td>
						<td class="center"><?php

							$job_status = jr_get_job_status( $ended_jobs->post );

							if ( $order_status = jr_get_job_order_status( $ended_jobs->post, $pending_payment_jobs ) ) {
								echo sprintf( ' %s', $order_status  );
							} else {
								echo sprintf( ' %s', $job_status );
							}

						?>
						<td class="center"><?php echo $job_views; ?></td>
						<td class="actions"><?php the_job_actions( $ended_jobs->post, $pending_payment_jobs ); ?></td>
					</tr>

				<?php endwhile; ?>

				</tbody>
			</table>

			<?php jr_paging( $ended_jobs, 'paged', array ( 'add_args' => array( 'tab' => 'ended' ) ) ); ?>

			<?php else: ?>
				<p><?php _e('No expired jobs found.', APP_TD); ?></p>
			<?php endif; ?>

		</div>

		<div id="subscriptions" class="myjobs_section">
			<h2><?php _e('Resume Subscriptions ', APP_TD); ?></h2>
			<?php appthemes_load_template( array( 'dashboard-resumes.php', 'includes/dashboard-resumes.php' ) ); ?>
		</div>

		<div id="orders" class="myjobs_section">
			<h2><?php _e('Orders', APP_TD); ?></h2>
			<?php appthemes_load_template( array( 'dashboard-orders.php', 'includes/dashboard-orders.php' ) ); ?>
		</div>

		<?php do_action( 'jr_dashboard_tab_content', 'job_lister' ); ?>

		</div><!-- end section_content -->

	</div><!-- end section -->

	<div class="clear"></div>

</div><!-- end main content -->

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar('user'); endif; ?>
