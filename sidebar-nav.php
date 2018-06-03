<li class="widget widget-nav">
	<ul class="display_section">
		<li><a href="#browseby" class="noscroll"><?php _e('Browse by&hellip;', APP_TD); ?></a></li>
		<li><a href="#tags" class="noscroll"><?php _e('Tags', APP_TD); ?></a></li>
	</ul>
	<div id="browseby" class="tabbed_section">
		<div class="contents">
			<ul>
				<?php the_jr_sidebar_nav_tax_item( APP_TAX_TYPE, __('Job Type', APP_TD ), array( 'hide_empty' => ! $jr_options->jr_show_empty_categories ) ); ?>

				<?php the_jr_sidebar_nav_tax_item( APP_TAX_SALARY, __('Job Salary', APP_TD ), array( 'hide_empty' => ! $jr_options->jr_show_empty_categories ) ); ?>

				<?php the_jr_sidebar_nav_tax_item( APP_TAX_CAT, __('Job Category', APP_TD ), array( 'hide_empty' => ! $jr_options->jr_show_empty_categories ) ); ?>

				<?php the_jr_sidebar_nav_date_items(); ?>

				<?php jr_sidebar_nav_browseby(); ?>

			</ul>
		</div>
	</div>
	<div id="tags" class="tabbed_section">
		<div class="contents">

			<?php the_jr_sidebar_nav_tab( APP_TAX_TAG, array( 'hide_empty' => ! $jr_options->jr_show_empty_categories ) ); ?>

		</div>
	</div>
</li>
