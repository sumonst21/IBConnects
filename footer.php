<div id="footer">

	<div class="footer-widgets">

		<div class="inner">

			<?php foreach ( $footer_cols as $number ): ?>

				<?php $sidebar_id = "footer_col_{$number}"; ?>

				<div class="column">
					<div id="<?php echo esc_attr( $sidebar_id ); ?>">
						<?php dynamic_sidebar( $sidebar_id ); ?>
					</div>
				</div>

			<?php endforeach; ?>

			<div class="clear"></div>

			<div class="copyright">
			Copyright © 2018 <a href="https://www.ibconnects.com/" target="_blank" rel="nofollow">iBConnects.com</a>  <?php //_e('Powered by', APP_TD); ?> <a href="https://wordpress.org" target="_blank" rel="nofollow"></a>
			</div>

		</div><!-- end inner -->

	</div><!-- end footer-widgets -->

</div><!-- end footer -->
