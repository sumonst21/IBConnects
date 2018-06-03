<?php
/**
 * Template Name: Terms & Conditions
 */
?>
	<div class="section">

		<div class="section_content">

			<h1><?php the_title(); ?></h1>

			<?php while ( have_posts() ) : the_post(); ?>

					<?php the_content(); ?>

			<?php endwhile; // end of the loop. ?>

		</div><!-- end section_content -->

	</div><!-- end section -->

	<div class="clear"></div>

</div><!-- end main content -->

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar(); endif; ?>
