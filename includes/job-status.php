<?php
/**
 * Job status related functions
 *
 * @version 1.7
 * @author AppThemes
 * @package JobRoller\Job-Status
 * @copyright 2010 all rights reserved
 *
 */

add_action( 'init', 'jr_register_new_post_statuses' );

add_filter( 'posts_clauses', 'jr_expired_jobs_sql', 10, 2 );

add_action( 'jr_check_jobs_expired', 'jr_check_jobs_expired' );

add_action( 'jr_job_expiring_soon' , '_jr_set_expire_reminder_meta', 11, 2 );
add_action( 'jr_job_expired' , '_jr_expire_job', 10, 3 );
add_action( 'jr_job_expired' , '_jr_expire_job_addons', 11 );

add_action( 'transition_post_status', '_jr_clear_meta_flags', 10, 3 );

add_action( 'publish_' . APP_POST_TYPE, '_publish_post_hook', 5, 1 );

add_action( 'expired_to_publish', 'jr_update_job_start_date' );
add_action( 'draft_to_publish', 'jr_update_job_start_date' );

//add_action( 'job_main_section', 'jr_expired_message', 1, 1 );

### Hook Callbacks.

/**
 * Register new post statuses.
 *
 * @return void
 */
function jr_register_new_post_statuses(){
	register_post_status( 'expired', array(
		'label' => _x( 'Expired', 'post', APP_TD ),
		'public' => true,
		'exclude_from_search' => false,
		'show_in_admin_all_list' => true,
		'show_in_admin_status_list' => true,
		'label_count' => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>' ),
	) );
}

/**
 * Retrieve a list of days that should trigger a user notification when a job reaches it.
 *
 * @uses apply_filters() Calls 'jr_expired_job_days_notify' - change the default reminder days list
 *
 * @return array The days list
 */
function jr_get_expired_job_days_notify() {
	$days_notify = array( 1, 5 );
	return apply_filters( 'jr_expired_job_days_notify', $days_notify );
}

/**
 * Searches for expiring soon jobs and triggers actions to notify users.
 */
function jr_check_jobs_expired() {
	global $jr_options;

	// expired
	$expired_posts = new WP_Query( array(
		'post_type' => APP_POST_TYPE,
		'post_status' => 'publish',
		'expiring_jobs' => true,
		'nopaging' => true,
	) );

	foreach ( $expired_posts->posts as $post ) {
		_jr_end_job( $post->ID );
	}

	// expiring soon

	if ( $jr_options->jr_expired_job_email_owner ) {

		// notify users when the job is about to expire - default is 1 day before and 5 days before
		$days_notify = jr_get_expired_job_days_notify();

		if ( ! $days_notify ) {
			return;
		}

		$notified_ids = array();

		foreach ( $days_notify as $key => $days ) :

			$notification_list = array();

			// retrieve jobs expiring within n days
			$expiring_soon = new WP_Query( array(
				'post_type' => APP_POST_TYPE,
				'post_status' => 'publish',
				'expiring_jobs' => true,
				'expire_days' => $days,
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => '_jr_days_expire_reminder_email_sent',
						'compare' => 'NOT EXISTS'
					),
					// only notify about jobs whose duration is superior to the notification day
					array(
						'key' => JR_JOB_DURATION_META,
						'compare' => '>',
						'value' => $days,
					)
				),
				'nopaging' => true,
			) );

			$expiring_notified = array();
			if ( isset($days_notify[$key+1]) && $days < $days_notify[$key+1] ) {

				// retrieve expiring jobs with notifications sent to user that are greater then the current expiration time interval
				$expiring_notified = new WP_Query( array(
					'post_type' => APP_POST_TYPE,
					'post_status' => 'publish',
					'expiring_jobs' => true,
					'expire_days' => $days,
					'meta_query' => array(
						array(
							'key' => '_jr_days_expire_reminder_email_sent',
							'value' => $days_notify[$key+1],
							'compare' => '='
						)
					),
					'nopaging' => true,
				) );

			}

			if ( $expiring_soon->post_count > 0 ) {
				foreach ( $expiring_soon->posts as $post ) {
					$notification_list[] = $post->ID;
				}
			}

			if ( sizeof($expiring_notified) > 0 ) {
				foreach ( $expiring_notified->posts as $post ) {
					$notification_list[] = $post->ID;
				}
			}

			if ( sizeof($notification_list) > 0 ) {

				foreach ( $notification_list as $id ) {
					if ( ! in_array( $id, $notified_ids ) ) {
						do_action( 'jr_job_expiring_soon', $id, $days );
						$notified_ids[] = $id;
					}
				}

			}

		endforeach;

	}

}

/**
 * Returns clauses to be used on the wp_query to look for expired jobs.
 *
 * @param array $clauses Existing wp_query clauses
 * @param object $wp_query The wp_query object
 *
 * @return array Updated clauses
 */
function jr_expired_jobs_sql( $clauses, $wp_query ) {
	global $wpdb;

	if ( $wp_query->get( 'expiring_jobs' ) ) {
		$clauses['join'] .= " INNER JOIN " . $wpdb->postmeta ." AS exp1 ON (" . $wpdb->posts .".ID = exp1.post_id)";

		if ( $wp_query->get( 'expire_days' ) ) {
			$days = $wp_query->get( 'expire_days' );
		}
		if ( ! empty($days) ) {
			$clauses['where'] .= " AND ( exp1.meta_key = '" . JR_JOB_DURATION_META . "' AND DATE_ADD(post_date, INTERVAL exp1.meta_value DAY) < DATE_ADD('".current_time( 'mysql' )."', INTERVAL $days DAY) AND exp1.meta_value > 0 )";
		} else {
			$clauses['where'] .= " AND ( exp1.meta_key = '" . JR_JOB_DURATION_META . "' AND DATE_ADD(post_date, INTERVAL exp1.meta_value DAY) < '" . current_time( 'mysql' ) . "' AND exp1.meta_value > 0 )";
		}
	}

	return $clauses;
}

/**
 * Checks if a job needs to be published.
 *
 * @param object $job The job to be checked
 *
 * @return bool TRUE if the job needs to be published, FALSE otherwise
 */
function _jr_needs_publish( $job ){
	return in_array( $job->post_status, array( 'draft', 'expired' ) );
}

/**
 * Updates a job status based on the admin moderation option.
 *
 * @param int $job_id The job ID
 * @param (optional) string $status The new status
 *
 * @return void
 */
function jr_update_post_status( $job_id, $status = '' ) {
	if ( ! $status ) {
		$status = _jr_maybe_publish_job( $job_id );
	}
	_jr_update_post_status( $job_id, $status );
}

/**
 * Updates a job status.
 *
 * @param int $job_id The job ID
 * @param string $status The new status
 *
 * @return void
 */
function _jr_update_post_status( $job_id, $status ) {
	wp_update_post( array(
		'ID' => $job_id,
		'post_status' => $status
	) );
}

/**
 * Checks if jobs require moderation before being published.
 *
 * @return bool TRUE if moderation is required, FALSE otherwise
 */
function _jr_moderate_jobs() {
	global $jr_options;
	return (bool) ( $jr_options->jr_jobs_require_moderation );
}

/**
 * Checks if edited jobs require moderation before being published.
 *
 * @param object $job The job being edited
 *
 * @return bool TRUE if moderation is required, FALSE otherwise
 */
function _jr_edited_job_requires_moderation( $job ) {
	global $jr_options;
	return ( in_array( $job->post_status, array( 'publish', 'draft' ) ) && $jr_options->jr_editing_needs_approval );
}

/**
 * Retrieve the new status for a job based on the moderation settings.
 *
 * @param int $job_id The job id
 *
 * @return string The new status
 */
function _jr_maybe_publish_job( $job_id ) {

	if ( _jr_moderate_jobs() ) {
		$status = 'pending';
	} else {
		$status = 'publish';
	}

	return $status;
}

/**
 * Store job ending reminders on the job meta.
 *
 * @param int $job_id The job ID
 * @param int $days The reminder days ( 0 =  expired job reminder )
 *
 * @return void
 */
function _jr_set_expire_reminder_meta( $job_id, $days = 0 ) {
	update_post_meta( $job_id, '_jr_days_expire_reminder_email_sent', $days );
}

/**
 * Clear any existing flags previously assigned to a job (usually after  a relisting).
 *
 * @param string $new_status The new post status
 * @param string $old_status The old post status
 * @param object $post The post object
 *
 * @return void
 */
function _jr_clear_meta_flags( $new_status, $old_status, $post ) {

	if ( APP_POST_TYPE != $post->post_type ) {
		return;
	}

	if ( 'publish' == $new_status ) {
		delete_post_meta( $post->ID, '_jr_days_expire_reminder_email_sent' );
		delete_post_meta( $post->ID, '_jr_canceled_job' );
	}

}

/**
 * Expire a job by updating it's status to 'expired'.
 *
 * @param int $job_id The job ID
 * @param bool (optional) $canceled TRUE to set the job as 'expired/canceled', FALSE to set it as 'expired' only
 * @param string (optional) $canceled_desc Short description to identify the cancel originator
 *
 *  @return void
 */
function _jr_expire_job( $job_id, $canceled = false, $canceled_desc = 'user_canceled' ) {

	if ( $canceled ) {
		update_post_meta( $job_id, '_jr_canceled_job', $canceled_desc );
	}
	jr_update_post_status( $job_id, 'expired' );

}

/**
 * Expire job addons by deleting the related meta data.
 *
 * @param int $job_id The job ID
 *
 * @return void
 */
function _jr_expire_job_addons( $job_id ) {
	// featured addons
	foreach( _jr_featured_addons() as $addon ) {
		jr_remove_featured( $job_id, $addon );
	}
}

/**
 * Store the job duration meta.
 *
 * @param int $job_id The job ID
 * @param int (optional) $duration The duration in days
 *
 * @return void
 */
function _jr_set_job_duration( $job_id, $duration = '' ) {
	global $jr_options;
	if ( ! $duration && ! jr_charge_job_listings() ) {
		$duration = $jr_options->jr_jobs_default_expires;
	}
	update_post_meta( $job_id, JR_JOB_DURATION_META, $duration );
}

/**
 * Updates the job start date.
 *
 * @param object $post The post object
 *
 * @return void
 */
function jr_update_job_start_date( $post ) {

	if ( $post->post_type == APP_POST_TYPE ) {
		wp_update_post( array(
			'ID' => $post->ID,
			'post_date' => current_time( 'mysql' )
		) );
	}
}

/**
 * End a job by setting the status to 'expired'.
 *
 * @param int $job_id The job ID
 * @param bool (optional) $canceled TRUE to set the job as 'expired/canceled', FALSE to set it as 'expired' only
 * @param string (optional) $canceled_desc Short description to identify the cancel originator
 *
 * @return void
 */
function _jr_end_job( $job_id, $canceled = false, $canceled_desc = 'user_canceled' ) {

	if ( ! $job_id ) {
		return;
	}

	do_action( 'jr_job_expired', $job_id, $canceled, $canceled_desc );
}

/**
 * Checks if a job expires (contains a duration).
 * '0' duration jobs are endless.
 *
 * @since 1.8.1
 *
 * @param  object $post The post object
 * @return boolean TRUE if the job expires, FALSE otherwise
 */
function jr_job_expires( $post ) {
	return (bool) get_post_meta( $post->ID, JR_JOB_DURATION_META, true );
}

/**
 * Remaining days function.
 *
 * @uses apply_filters() Calls 'jr_remaining_days'.
 */
function jr_remaining_days( $post ) {
	$remain_days = __( 'Endless', APP_TD );

	$days = get_post_meta( $post->ID, JR_JOB_DURATION_META, true );
	if ( $days ) {

		if ( $days >= 1 ) {

			$expire_date = strtotime( $post->post_date . '+' . $days . ' days' );
			$days = appthemes_days_between_dates( date( 'y-m-d', $expire_date ), date( 'y-m-d', strtotime( 'NOW' ) ), $precision = 0 );
			if ( $days >= 1 ) {
				$remain_days = $days . ' ' . _n( 'day', 'days', $days, APP_TD );
			} else {
				$remain_days = human_time_diff( strtotime( 'NOW' ), $expire_date );
			}

		} else {
			$remain_days = __( 'Expired', APP_TD );
		}

	}

	return apply_filters( 'jr_remaining_days', $remain_days );
}

/**
 * Expiry check function.
 */
if ( ! function_exists( 'jr_check_expired' ) ) {
function jr_check_expired( $post ) {
	return ( 'expired' == $post->post_status );
}
}

/**
 * Retrieves the expiration date for the current or given job.
 */
function jr_get_job_expiration_date( $job_id = 0 ) {
	global $post;

	$job_id = !empty( $job_id ) ? $job_id : get_the_ID();

	$duration = get_post_meta( $job_id, JR_JOB_DURATION_META, true );
	if ( empty( $duration ) ) {
		return 0;
	}

	return jr_get_expiration_date( $post->post_date, $duration );
}

/**
 * Retrieves an expiration date based on a start date and the number of days to be added to the date.
 */
function jr_get_expiration_date( $start_date, $duration ) {
	$expiration_date = strtotime( $start_date . ' + ' . $duration . 'days' );
	return appthemes_display_date( $expiration_date, 'date' );
}

/**
 * Outputs the 'Expired' message.
 */
if ( !function_exists( 'jr_expired_message' ) ) {
function jr_expired_message( $post ) {
	$expired = jr_check_expired( $post );
	if ( $expired ) :
		?><p class="expired"><?php _e( '<strong>NOTE:</strong> This job listing has expired and may no longer be relevant!', APP_TD ); ?></p><?php
	endif;
}
}
