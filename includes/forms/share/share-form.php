<?php
/**
 * JobRoller Share form
 * Function outputs the share form
 *
 *
 * @version 1.0
 * @author AppThemes
 * @package JobRoller
 * @copyright 2010 all rights reserved
 *
 */
function jr_share_form() {
	if ( function_exists('selfserv_sexy') ): ?>
		<div id="share_form" class="section_content">
			<?php selfserv_sexy(); ?>
			<div class="clear"></div>
		</div>
	<?php endif;
}