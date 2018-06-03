<?php
/**
 * JobRoller Edit Resume Form.
 * Outputs the edit resume form.
 *
 * @version 1.6.3
 * @author AppThemes
 * @package JobRoller\Forms\Edit-Resume
 * @copyright 2010 all rights reserved
 */

add_action( 'jr_resume_footer', 'jr_edit_resume_parts', 10, 1 );

/**
 * Displays "Add Website" modal form.
 *
 * @param object $post
 *
 * @return void
 */
function jr_edit_resume_parts( $post ) {
	$resume = $post->ID;
?>
	<div style="display:none">

		<form id="websites" action="<?php echo esc_url( get_permalink( $resume ) ); ?>" class="submit_form main_form modal_form" method="post">
			<h2><?php _e( 'Add Website', APP_TD ); ?></h2>

			<p><?php _e( 'Add a website below to add it to your resume e.g. your portfolio or a Twitter account.', APP_TD ); ?></p>
			<p><label for="website_name"><?php _e( 'Website Name', APP_TD ); ?></label> <input type="text" class="text required" name="website_name" id="website_name" /></p>
			<p><label for="website_url"><?php _e( 'Website URL', APP_TD ); ?></label> <input type="text" class="text required" name="website_url" id="website_url" /></p>
			<p><input type="submit" class="submit" name="save_website" value="<?php esc_attr_e( 'Add', APP_TD ); ?>" /></p>

		</form>
	</div>
<?php
}
