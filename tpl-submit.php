<?php
/**
 * Template Name: Submit Job Template
 */
?>
	<div class="section">

		<div class="section_content">

			<?php do_action( 'appthemes_notices' );	?>

			<h1><?php echo $title; ?></h1>

			<ol class="steps <?php echo ( count( $steps_trail ) > 4 ? 'more-steps' : '' ); ?>">

				<?php foreach( $steps_trail as $step_trail ): ?>

					<li class="<?php esc_attr_e( $step_trail['classes'] ); ?>">
						<span class="<?php esc_attr_e( $step_trail['classes_desc'] ); ?>"><?php echo $step_trail['description']; ?></span>
					</li>

				<?php endforeach; ?>

				<div class="clear"></div>

			</ol>

			<?php do_action( 'jr_before_step', $step ); ?>

			<?php if ( 1 === $step ): ?>

					<p><?php _e('You must login or create an account in order to post a job &mdash; this will enable you to view, remove, or relist your listing in the future.', APP_TD); ?></p>
					<div class="col-1">
						<?php do_action( 'jr_display_register_form', get_permalink( $post->ID ), 'job_lister' );?>
					</div>
					<div class="col-2">
						<?php do_action( 'jr_display_login_form', get_permalink( $post->ID ), get_permalink( $post->ID ) ); ?>
					</div>
					<div class="clear"></div>

			<?php else : ?>

					<?php appthemes_load_template( $template, $params ); ?>

			<?php endif; ?>

			<?php do_action('jr_after_step', $step ); ?>

		</div><!-- end section_content -->

	</div><!-- end section -->

	<div class="clear"></div>

</div><!-- end main content -->

<?php if ( $jr_options->jr_show_sidebar ): get_sidebar('submit'); endif; ?>
