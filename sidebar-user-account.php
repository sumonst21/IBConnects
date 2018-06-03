<?php if ( is_user_logged_in() ): ?>

	<li class="widget widget_user_options">

		<h2 class="widget_title"><?php _e('Account Options',APP_TD); ?></h2>

		<div class="widget_content">
			<ul>
				<?php if ( is_user_logged_in() ) : ?>
					<li><a href="<?php echo get_permalink( JR_Dashboard_Page::get_id() ) ?>"><?php _e('My Dashboard',APP_TD)?></a></li>
				<?php endif; ?>

				<li><a href="<?php echo esc_url( get_author_posts_url( $userdata->ID, $userdata->user_nicename ) ); ?>"><?php _e('View Profile',APP_TD)?></a></li>
				<li><a href="<?php echo esc_url( get_permalink( JR_User_Profile_Page::get_id() ) ); ?>"><?php _e('Edit Profile',APP_TD)?></a></li>

				<?php if ( current_user_can('edit_others_posts') ): ?>
					<li><a href="<?php echo get_option('siteurl'); ?>/wp-admin/"><?php _e('WordPress Admin',APP_TD)?></a></li>
				<?php endif; ?>

				<li><a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>"><?php _e('Log Out',APP_TD)?></a></li>
			</ul>
		</div>

	</li>

	<li class="widget widget_user_info">

		<h2 class="widget_title"><?php _e('Account Info',APP_TD); ?></h2>

		<div class="widget_content">
			<ul>
				<li><strong><?php _e('Username:',APP_TD)?></strong> <?php echo $userdata->user_login; ?></li>
				<li>
					<strong><?php _e('Account type:',APP_TD)?></strong>
					<?php
						$user = new WP_User( $userdata->ID );

						if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
							foreach ( $user->roles as $role ) {
								echo jr_translate_role( $role ) . '<br/>' ;
							}
						}
					?>
				</li>
				<li><strong><?php _e('Member Since:',APP_TD)?></strong> <?php echo appthemes_display_date($userdata->user_registered); ?></li>
				<li><strong><?php _e('Last Login:',APP_TD); ?></strong> <?php echo appthemes_display_date( get_user_meta($userdata->ID, 'last_login', true) ); ?></li>
			</ul>
		</div>

	</li>

<?php endif;
