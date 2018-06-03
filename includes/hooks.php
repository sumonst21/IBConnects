<?php

/**
 * Reserved for any theme-specific hooks
 * For general AppThemes hooks, see framework/kernel/hooks.php
 *
 * @since 1.3
 * @uses add_action() calls to trigger the hooks.
 *
 */

/**
 * called before the new job is created
 * make sure to return $data in your function
 *
 * @since 1.3
 * @param string $action Post action: insert|update|relist
 */
function jr_before_insert_job( $action = 'insert' ) {
	do_action( 'jr_before_insert_job', $action );
}

/**
 * called after the new job is created
 *
 * @since 1.3
 * @param string $post_id Passes in newly new or existing job id
 * @param string $action Post action: insert|update|relist
 */
function jr_after_insert_job( $post_id, $action = 'insert' ) {
	do_action( 'jr_after_insert_job', $post_id, $action );
}


/**
 * Called above a single resume.
 * @since 1.4
 *
 * @param object $post
 *
 * @return void
 */
function jr_resume_header( $post ) {
	do_action( 'jr_resume_header', $post );
}


/**
 * Called below a single resume.
 * @since 1.4
 *
 * @param object $post
 *
 * @return void
 */
function jr_resume_footer( $post ) {
	do_action( 'jr_resume_footer', $post );
}


/**
 * called in tpl-job-seeker-dashboard.php before dashboard jobs
 *
 * @since 1.4
 */
function jr_before_job_seeker_dashboard() {
	do_action('jr_before_job_seeker_dashboard');
}

/**
 * called in tpl-job-seeker-dashboard.php after dashboard jobs
 *
 * @since 1.4
 */
function jr_after_job_seeker_dashboard() {
	do_action('jr_after_job_seeker_dashboard');
}

/**
 * called in sidebar-nav.php after filters
 *
 * @since 1.4
 */
function jr_sidebar_nav_browseby() {
	do_action('jr_sidebar_nav_browseby');
}

/**
 * called in sidebar-resume-nav.php after filters
 *
 * @since 1.4
 */
function jr_sidebar_resume_nav_browseby() {
	do_action('jr_sidebar_resume_nav_browseby');
}

/**
 * called before a new order is inserted in the database
 * make sure to return $data in your function
 *
 * @since 1.5.3
 * @param int $plan_id The plan ID
 */
function jr_before_insert_order( $plan_id ) {
	do_action( 'jr_before_insert_order', $plan_id );
}

/**
 * called after a new order is inserted in the database
 * make sure to return $data in your function
 *
 * @since 1.5.3
 * @param object $order Order data
 */
function jr_after_insert_order( $order ) {
	do_action( 'jr_after_insert_order', $order );
}

/**
 * called before the new resume is created
 * make sure to return $data in your function
 *
 * @since 1.8
 * @param string $action Post action: insert|update|relist
 */
function jr_before_insert_resume( $action = 'insert' ) {
	do_action( 'jr_before_insert_resume', $action );
}

/**
 * called after the new resume is created
 *
 * @since 1.8
 * @param string $post_id Passes in newly new or existing job id
 * @param string $action Post action: insert|update|relist
 */
function jr_after_insert_resume( $post_id, $action = 'insert' ) {
	do_action( 'jr_after_insert_resume', $post_id, $action );
}