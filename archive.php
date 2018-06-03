	<?php get_header('search'); ?>

    <div class="section">

        <?php if ( is_category() ): ?>

            <h1 class="pagetitle"><?php single_cat_title(); ?></h1>

        <?php /* If this is a tag archive */
			elseif( is_tag() ): ?>

			<h1 class="pagetitle"><?php _e( 'Posts Tagged', APP_TD ); ?> &ldquo;<?php single_tag_title(); ?>&rdquo;</h1>

		<?php /* If this is a daily archive */ elseif ( is_day() ): ?>
			<h1 class="pagetitle"><?php _e( 'Archive for', APP_TD ); ?> <?php the_time( 'F jS, Y' ); ?></h1>

		<?php /* If this is a monthly archive */ elseif ( is_month() ): ?>
			<h1 class="pagetitle"><?php _e( 'Archive for', APP_TD ); ?> <?php the_time( 'F, Y' ); ?></h1>

		<?php /* If this is a yearly archive */ elseif ( is_year() ): ?>
			<h1 class="pagetitle"><?php _e( 'Archive for', APP_TD ); ?> <?php the_time( 'Y' ); ?></h1>

		<?php /* If this is an author archive */ elseif ( is_author() ): ?>
			<h1 class="pagetitle"><?php _e( 'Author Archive', APP_TD ); ?></h1>

		<?php /* If this is a paged archive */ elseif ( !empty( $_GET['paged'] ) ): ?>
			<h1 class="pagetitle"><?php _e( 'Blog Archives', APP_TD ); ?></h1>

        <?php endif; ?>

        <?php appthemes_load_template('loop.php'); ?>

        <?php jr_paging(); ?>

        <div class="clear"></div>

    </div><!-- end section -->

    <div class="clear"></div>

</div><!-- end main content -->

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar(); endif; ?>
