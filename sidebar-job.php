<div id="sidebar">

	<ul class="widgets">

		<?php appthemes_before_sidebar_widgets( APP_POST_TYPE ); ?>

		<?php appthemes_load_template( array( 'sidebar-nav.php', 'includes/sidebar-nav.php' ) ); ?>

		<?php if (function_exists('dynamic_sidebar') && dynamic_sidebar('sidebar_job')) : else : ?>

			<!-- no dynamic sidebar so don't do anything -->

		<?php endif; ?>

		<?php appthemes_after_sidebar_widgets( APP_POST_TYPE ); ?>

	</ul>

</div><!-- end sidebar -->
