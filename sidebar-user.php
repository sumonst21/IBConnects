<div id="sidebar">

	<ul class="widgets">

		<?php appthemes_before_sidebar_widgets( 'user' ); ?>

		<?php appthemes_load_template('sidebar-user-account.php'); ?>

		<?php if (function_exists('dynamic_sidebar') && dynamic_sidebar('sidebar_user')) : else : ?>

			<!-- no dynamic sidebar setup -->

		<?php endif; ?>

		<?php appthemes_after_sidebar_widgets( 'user' ); ?>

	</ul>

</div><!-- end sidebar -->
