<?php
/**
 * Template Name: Blog Template
 */
?>
	<div class="section">

		<?php appthemes_load_template('loop.php'); ?>

		<?php jr_paging(); ?>

		<div class="clear"></div>

	</div><!-- End section -->

	<div class="clear"></div>

</div><!-- end main content -->

<?php get_sidebar('blog'); ?>
