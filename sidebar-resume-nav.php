<li class="widget widget-nav">
	<ul class="display_section">
		<li><a href="#browseby" class="noscroll"><?php _e('Browse by&hellip;', APP_TD); ?></a></li>
		<li><a href="#specialities" class="noscroll"><?php _e('Specialty', APP_TD); ?></a></li>
		<li><a href="#groups" class="noscroll"><?php _e('Group', APP_TD); ?></a></li>
	</ul>
	<div id="browseby" class="tabbed_section">
		<div class="contents">
			<ul>
				<?php the_jr_sidebar_nav_tax_item( APP_TAX_RESUME_CATEGORY, __('Job Category', APP_TD ) ); ?>

				<?php the_jr_sidebar_nav_tax_item( APP_TAX_RESUME_JOB_TYPE, __('Job Type', APP_TD ) ); ?>

				<?php the_jr_sidebar_nav_tax_item( APP_TAX_RESUME_LANGUAGES, __('Spoken Languages', APP_TD ) ); ?>

				<?php jr_sidebar_resume_nav_browseby(); ?>

				<li><a class="top" href="<?php echo esc_url( get_post_type_archive_link( APP_POST_TYPE_RESUME ) ); ?>"><?php _e('View all resumes', APP_TD); ?></a></li>

			</ul>
		</div>
	</div>
	<div id="specialities" class="tabbed_section">
		<div class="contents">

			<?php the_jr_sidebar_nav_tab( APP_TAX_RESUME_SPECIALITIES ); ?>

		</div>
	</div>
	<div id="groups" class="tabbed_section">
		<div class="contents">

			<?php the_jr_sidebar_nav_tab( APP_TAX_RESUME_GROUPS ); ?>

		</div>
	</div>
</li>
