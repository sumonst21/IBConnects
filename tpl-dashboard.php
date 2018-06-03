<?php
/**
 * Template Name: My Dashboard Template
 */

if ( current_user_can('can_submit_job'))  {
	appthemes_load_template('tpl-myjobs.php');
} else {
	appthemes_load_template('tpl-job-seeker-dashboard.php');
}