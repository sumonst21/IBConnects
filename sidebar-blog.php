<div id="sidebar">

	<ul class="widgets">
	
		<?php appthemes_before_sidebar_widgets( 'blog' ); ?>
		
		<?php if (function_exists('dynamic_sidebar') && dynamic_sidebar('sidebar_blog')) : else : ?>

			<!-- no dynamic sidebar so don't do anything -->

		<?php endif; ?>
		
		<?php appthemes_after_sidebar_widgets( 'blog' ); ?>

	</ul>

</div><!-- end sidebar -->
