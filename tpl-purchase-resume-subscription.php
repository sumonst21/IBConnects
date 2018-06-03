<?php
/**
 *  Template Name: Purchase Resume Plans
 */
?>
	<div class="section">

		<div class="section_content">

			<?php do_action( 'appthemes_notices' ); ?>

			<h1><?php _e('Subscribe to Resumes', APP_TD); ?></h1>

			<ol class="steps">

				<?php foreach( $steps_trail as $step_trail ): ?>

					<li class="<?php esc_attr_e( $step_trail['classes'] ); ?>">
						<span class="<?php esc_attr_e( $step_trail['classes_desc'] ); ?>"><?php echo $step_trail['description']; ?></span>
					</li>

				<?php endforeach; ?>

				<div class="clear"></div>

			</ol>

			<?php appthemes_load_template( ! empty( $params['order_template'] ) ? $params['order_template'] : $template, $params ); ?>

		</div><!-- end section_content -->

	</div><!-- end section -->

	<div class="clear"></div>

</div><!-- end main content -->

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar('resume'); endif; ?>
