<div id="topNav">

	<div class="inner">

		<?php the_jr_top_nav_menu(); ?>

		<div class="clear"></div>

	</div><!-- end inner -->

</div><!-- end topNav -->

<div id="header">

	<div class="inner">

		<div class="logo_wrap">

			<?php the_jr_logo(); ?>

			<?php if ( $jr_options->jr_enable_header_banner ): ?>

				<div id="headerAd"><?php echo stripslashes( $jr_options->jr_header_banner ); ?></div>

			<?php else : ?>

				<div id="mainNav"><?php the_jr_main_nav_menu();?></div>

			<?php endif; ?>

			<div class="clear"></div>

		</div><!-- end logo_wrap -->

	</div><!-- end inner -->

</div><!-- end header -->
