	<div class="section">

		<div class="section_content">

			<?php appthemes_before_page_loop(); ?>

			<?php if (have_posts()) : ?>

				<?php while (have_posts()) : the_post(); ?>

					<?php appthemes_before_page(); ?>

					<?php appthemes_before_page_title(); ?>

					<h1><?php the_title(); ?></h1>

					<?php appthemes_after_page_title(); ?>

					<?php appthemes_before_page_content(); ?>

					<?php the_content(); ?>

					<?php appthemes_after_page_content(); ?>

					<?php appthemes_after_page(); ?>

				<?php endwhile; ?>

				<?php appthemes_after_page_endwhile(); ?>

			<?php else: ?>

				<?php appthemes_page_loop_else(); ?>

			<?php endif; ?>

			<?php appthemes_after_page_loop(); ?>

			<div class="clear"></div>

		</div>

	</div>

	<?php if (comments_open()): comments_template('/comments-page.php'); endif; ?>

	<div class="clear"></div>

</div><!-- end main content -->

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar('page'); endif; ?>
