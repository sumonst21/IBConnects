<?php
/**
 * The loop that displays the blog posts.
 *
 * @package AppThemes
 * @subpackage JobRoller
 *
 */
?>

<?php appthemes_before_blog_loop(); ?>

<?php if (have_posts()) : $alt = 1;?>

    <?php while (have_posts()) : the_post(); ?>

		<?php appthemes_before_blog_post(); ?>

		<?php
			$post_class = array('post');
			$alt = $alt * -1;
			if ($alt==1) {
				$post_class[] = 'post-alt';
			}
		?>

        <div class="section single <?php echo implode(' ', $post_class); ?>">

			<div class="section_header">

				<div class="comment-bubble"><?php comments_popup_link('0', '1', '%'); ?></div>

				<?php appthemes_before_blog_post_title(); ?>

				<h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>

				<?php appthemes_after_blog_post_title(); ?>

				<div class="clear"></div>

			</div><!-- end section_header -->

			<?php if ( ! is_author() ) : ?>

				<div class="section_content">

					<?php appthemes_before_blog_post_content(); ?>

					<?php // hack needed for "<!-- more -->" to work with templates
						  global $more;
						  $more = 0;
					?>

					<?php the_content(__('<p>Continue reading &raquo;</p>', APP_TD)); ?>

					<?php appthemes_after_blog_post_content(); ?>

					<div class="clear"></div>

				</div><!-- end section_content -->

			<?php endif; ?>

        </div><!-- end section single -->

<?php endwhile; ?>

	<?php appthemes_after_blog_endwhile(); ?>

<?php else: ?>

	<?php appthemes_blog_loop_else(); ?>

<?php endif; ?>

<?php appthemes_after_blog_loop(); ?>
