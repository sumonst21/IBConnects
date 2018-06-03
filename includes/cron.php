<?php
/**
 * JobRoller Cron Jobs
 * This file contains the cron jobs used on the theme.
 *
 *
 * @version 1.2
 * @author AppThemes
 * @package JobRoller
 * @copyright 2010 all rights reserved
 *
 */

add_action( 'init', 'jr_schedule_jobs_prune' );
add_action( 'init', 'jr_schedule_featured_prune' );
add_action( 'init', 'jr_schedule_subscriptions_end' );
add_action( 'init', 'jr_schedule_packs_end' );
add_action( 'init', 'jr_cron_init_alerts' );

add_filter( 'cron_schedules', 'jr_cron_add_custom_schedules' );
add_filter( 'pre_update_option_jr_job_alerts_cron', 'jr_cron_update_job_alerts' );

/**
* Schedule a cron job for expired jobs
*
* @return void
*/
function jr_schedule_jobs_prune() {
 	if ( !wp_next_scheduled( 'jr_check_jobs_expired' ) )
		wp_schedule_event( time(), 'hourly', 'jr_check_jobs_expired' );
}

function jr_schedule_featured_prune() {
	if ( !wp_next_scheduled( 'jr_prune_expired_featured' ) )
		wp_schedule_event( time(), 'daily', 'jr_prune_expired_featured' );
}

function jr_schedule_subscriptions_end() {
	if ( !wp_next_scheduled( 'jr_check_expired_subscriptions' ) )
		wp_schedule_event( time(), 'daily', 'jr_check_expired_subscriptions' );
}

function jr_schedule_packs_end() {
	if ( !wp_next_scheduled( 'jr_check_packs_expired' ) )
		wp_schedule_event( time(), 'daily', 'jr_check_packs_expired' );
}

// init the job alerts cron afer all the admin options finish loading
function jr_cron_init_alerts() {
	global $jr_options;

	if ( $jr_options->jr_job_alerts ) {
		jr_cron_job_alerts_schedule();
	} elseif ( wp_next_scheduled( 'jr_job_alerts' ) ) {
		jr_cron_job_alerts_clear();
	}

}

// schedule job alerts
function jr_cron_job_alerts_schedule() {
	global $jr_options;

	if  ( ! wp_next_scheduled('jr_job_alerts') ) {
		$recurrence = $jr_options->jr_job_alerts_cron;
		wp_schedule_event( time(), $recurrence, 'jr_job_alerts');
	}
}

// add custom job alerts schedules to cron
function jr_cron_add_custom_schedules() {

	$schedules['ten_minutes'] = array (
			'display'  => __('Every Ten Minutes', APP_TD),
			'interval' => 10*60,
	);

	$schedules['twenty_minutes'] = array (
			'display' => __('Every Twenty Minutes', APP_TD),
			'interval' => 20*60,
	);

	$schedules['thirty_minutes'] = array (
			'display'  => __('Every Thirty Minutes', APP_TD),
			'interval' => 30*60,
	);

	return $schedules;
}

// clear the job alerts cron
function jr_cron_job_alerts_clear() {
	wp_clear_scheduled_hook('jr_job_alerts');
}


// update the job alerts cron when the schedule is changed
function jr_cron_update_job_alerts( $option ) {
	global $jr_options;

	if ( $jr_options->jr_job_alerts_cron != $option ) {
		jr_cron_job_alerts_clear();
	}

	return $option;
}
