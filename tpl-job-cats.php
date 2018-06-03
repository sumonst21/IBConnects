<?php
/**
 * Template Name: Job Category List Template
*/
?>
	<div class="section">

		<div class="section_content">

			<?php if (have_posts()) : ?>

				<?php while (have_posts()) : the_post(); ?>

					<h1><?php the_title(); ?></h1>

					<?php the_content(); ?>

					<ul class="job_cats"><?php
						// List Job Cats
						$cat_args = array('orderby' => 'name', 'show_count' => -1, 'hierarchical' => true, 'taxonomy' => 'job_cat', 'title_li' => '');
						wp_list_categories( $cat_args );
					?></ul>

				<?php endwhile; ?>

				<?php else: ?>

				<p><?php _e('Sorry, no posts matched your criteria.', APP_TD); ?></p>

			<?php endif; ?>

			<div class="clear"></div>

		</div><!-- end section_content -->

	</div><!-- end section -->

	<?php //if (comments_open()) comments_template(); ?>

	<div class="clear"></div>

</div><!-- end main content -->

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar('page'); endif; ?>
