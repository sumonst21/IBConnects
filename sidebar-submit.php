<div id="sidebar">

	<ul class="widgets">
	
		<?php appthemes_before_sidebar_widgets( 'submit' ); ?>
	  
		<?php if (function_exists('dynamic_sidebar') && dynamic_sidebar('sidebar_submit')) : else : ?>

			<!-- no dynamic sidebar so don't do anything -->

		<?php endif; ?>
		
		<?php appthemes_after_sidebar_widgets( 'submit' ); ?>

	</ul>

</div><!-- end sidebar -->
