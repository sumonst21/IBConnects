<div id="sidebar">

	<ul class="widgets">

		<?php appthemes_before_sidebar_widgets( 'resume' ); ?>

		<?php appthemes_load_template( array( 'sidebar-resume-nav.php', 'includes/sidebar-resume-nav.php' ) ); ?>

		<?php if (function_exists('dynamic_sidebar') && dynamic_sidebar('sidebar_resume')) : else : ?>

			<!-- no dynamic sidebar so don't do anything -->

		<?php endif; ?>

		<?php appthemes_after_sidebar_widgets( 'resume' ); ?>

	</ul>

</div><!-- end sidebar -->
