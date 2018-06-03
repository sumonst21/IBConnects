<?php
/**
 * JobRoller Job Alerts
 * This file contains helper functions for the job alerts.
 *
 *
 * @version 1.6
 * @author AppThemes
 * @package JobRoller
 * @copyright 2010 all rights reserved
 *
 */

add_filter( 'init', 'jr_job_alerts_add_feeds' );
add_action( 'jr_after_insert_job', 'jr_job_alerts_set_new', 10, 2 );
add_action( 'jr_job_alerts', 'jr_job_alerts_email_subscribers' );

add_action( 'transition_post_status', 'jr_manual_job_alerts_set_new', 10, 3 );

/**
 * Check if the current user is allowed to access the job alerts screen.
 * This function is filterable.
 *
 * @since 1.6.0
 *
 * @return TRUE|FALSE True if user has access. False otherwise
 */
function jr_job_alerts_auth() {
	global $user_ID, $jr_options;

	if ( ! $jr_options->jr_job_alerts && ! $jr_options->jr_job_alerts_feed ) {
		return false;
	}

	// apply filters to validate access to the job alerts page - default is TRUE
	$access = apply_filters( 'jr_job_alerts_access', $user_ID, TRUE );

	return $access;
}


/**
 * Reads the job posted vars and returns a regular expression string representing the alert type.
 * String format : (location=*.*).*(job_type=*.*).*(job_cat=*.*)
 * The regular expression is later evaluated against the users alert query strings
 *
 * @since 1.6.0
 *
 * @param int $post_id The Job ID
 * @return string The alert type string
 *
 */
function jr_job_alerts_alert_type( $post_id ) {

	$alert = '';
	$location = 'location=anywhere.*';
	$job_type = 'job_type=all.*';
	$job_cat = 'job_cat=all.*';

	$job = get_post( $post_id );
	$meta = get_post_custom( $post_id );

	if ( !empty($meta['geo_address'][0]) )
		$location .= '|location=' . trim($meta['geo_address'][0]) . '.*';

	$alert .= '(' . $location . ')';

	if ( $types = wp_get_object_terms( $post_id, APP_TAX_TYPE, array( 'fields' => 'ids' ) ) )
		$job_type .= '|job_type=' . reset($types);

	$alert .= '.*(' . $job_type . ')';

	if ( $cats = wp_get_object_terms( $post_id, APP_TAX_CAT, array( 'fields' => 'ids' ) ) )
		$job_cat .= '|job_cat=' . reset($cats);

	$alert .= '.*(' . $job_cat . ')';

	return $alert;

}

/**
 * Triggers job alert additions to the DB when job listers add new jobs manually from the backend.
 *
 * @since 1.6.0
 *
 * @param string $new_status The new post status
 * @param string $old_status The old post status
 * @param object $old_status The post object
 *
 */
function jr_manual_job_alerts_set_new( $new_status, $old_status, $post ) {
	global $jr_options;

	if ( ! is_admin() || APP_POST_TYPE != $post->post_type ) {
		return;
	}

	if ( ! $jr_options->jr_job_alerts ) {
		return;
	}

	if ( $new_status == 'publish' ) {
		jr_job_alerts_set_new( $post->ID );
	}
}

/**
 * Stores a new job alert criteria on the custom job alerts table when job listers add new jobs.
 *
 * @since 1.6.0
 *
 * @param int $post_id The Job ID
 * @param string (optional) $action Post action: insert|update|relist
 * @return string|false The XML body or False if an error occurs
 *
 */
function jr_job_alerts_set_new( $post_id, $action = 'insert' ) {
	global $wpdb, $jr_options;

	if ( ! $jr_options->jr_job_alerts || 'update' == $action ) {
		return;
	}

	$alert_type = jr_job_alerts_alert_type( $post_id );

	if ( $alert_type ) {

		// check for existing alert for the current job - skip alert for jobs already inserted
		$alert = $wpdb->get_var( $wpdb->prepare( "SELECT count(1) FROM $wpdb->jr_alerts WHERE post_id = %d", $post_id ) );
		if ( $alert && 'relist' != $action ) {
			return;
		} elseif ( $alert && 'relist' == $action ) {
			// clear the existing alert and let it create a new one for the relisted job
			jr_delete_job_alert( $post_id );
		}

		$wpdb->insert( $wpdb->jr_alerts, array(
				'post_id'       => $post_id,
				'alert_type'    => $alert_type,
				'last_user_id'  => 0,
				'last_activity' => 0,
			), array( '%d','%s','%d','%d' ) );

	}

}

/**
 * This is the main job alerts function. It's triggered by a CRON job every n minutes.
 * Queries the database for pending job alerts and loops through them to find matching user alerts limiting the results to the user maximum batch size.
 * Builds a list of the matching jobs for each user keeping the last user ID for each job ID.
 * Loops through the matching subscribers and sends the job alert email.
 * After the emails are sent, checks if the total subscribers is lower then the maximum batch size for each job alert (job ID).
 * If the total subscribers are lower then the batch size the job alert is deleted, else, it's updated with the last user id to later
 * send the alerts to the remaining subscribers.
 *
 * @since 1.6.0
 *
 */
function jr_job_alerts_email_subscribers() {
	global $wpdb, $jr_log, $jr_options;

	$notified_users = $subscribers = array();

	$max_jobs =  $jr_options->jr_job_alerts_jobs_limit;
	$batch_size = $batch_remain = $jr_options->jr_job_alerts_batch_size;

	// get the latest alerts from the databse
	$sql = "SELECT * FROM $wpdb->jr_alerts alerts, $wpdb->posts posts
			WHERE post_id = id AND post_status = 'publish' ORDER BY last_activity ASC LIMIT {$max_jobs}";

	$jr_log->write_log('Job Alerts *** SQL: '.$sql);

	$job_alerts = $wpdb->get_results( $sql );

	if ( ! $job_alerts ) {
		$jr_log->write_log('Job Alerts *** No published job alerts found!');
		return;
	}

	$jr_log->write_log('Job Alerts *** Results: '.print_r($job_alerts,true));

	// loop through the job alerts and get all the subscribers and the related jobs
	$subscribers = array();
	foreach ( $job_alerts as $job_alert ) :

		// limit the list to the user specified size
		if ( $batch_remain <= 0 ) break;

		// compare the job data with each user alert criteria
		$sql = " SELECT distinct SQL_CALC_FOUND_ROWS user_id  FROM $wpdb->usermeta user_meta
				 WHERE
				 	( meta_key = 'jr_alert'
				 	 AND (
				 			/* compare job criteria with the user criteria */
				 			meta_value REGEXP '".addslashes_gpc($job_alert->alert_type)."'
				 	 	 )
				 	 AND (
				 	 		 /* add the keyword as additional criteria if user is using them */
				 	 		( user_id IN (
				 	 		   				SELECT user_id FROM $wpdb->usermeta user_alert_keyword WHERE meta_key = 'jr_alert_keywords'
				 	 		   			    AND ( '".addslashes_gpc($job_alert->post_content)."' REGEXP meta_value
												 OR '".addslashes_gpc($job_alert->post_title)."' REGEXP meta_value
												 )
											AND user_alert_keyword.user_id = user_meta.user_id
				 	 		   			  ) )
				 	 		/* ignore keywords for users that do not use them */
				 	 		OR user_id NOT IN
				 	 			         ( SELECT user_id FROM $wpdb->usermeta user_alert_keyword WHERE meta_key = 'jr_alert_keywords'
				 	 			           AND user_alert_keyword.user_id = user_meta.user_id )
				 	 	 )
				    )
				   /* get alerts from users with active subscriptions only */
				 AND user_id IN (
							       SELECT user_id FROM $wpdb->usermeta user_alert_status
							 	   WHERE meta_key = 'jr_alert_status'
							 	   AND user_alert_status.user_id = user_meta.user_id
							 	   AND meta_value = 'active'
							 	 )
				 AND user_id > {$job_alert->last_user_id}
				 ORDER BY user_id ASC
				 LIMIT {$batch_remain}
				 ";

		$job_subscribers = $wpdb->get_results( $sql );

		// get the total found rows
		$sql = "SELECT FOUND_ROWS();";
		$total_subscribers = $wpdb->get_var( $sql );

		$jr_log->write_log('Job Alerts *** Subscribers: '.sizeof($job_subscribers));

		// build the list of subscribers that match the job alert
		if ( sizeof($job_subscribers) > 0 ):

			 foreach ( $job_subscribers as $job_subscriber ) {
				$subscribers[$job_subscriber->user_id][] = $job_alert->post_id;
			 }

			// keep the last user id
			$last_subscriber = end($job_subscribers);

			// store the last subscriber id for each job alert
			$alert[$job_alert->post_id] = array( 'last_user_id' => $last_subscriber->user_id, 'list_size' => $total_subscribers );

			// subtract the allowed batch size from the matching subscribers
			$batch_remain -= sizeof($job_subscribers);

		endif;

	endforeach;

	$jr_log->write_log('Job Alerts *** Matching Subscribers: '.sizeof($subscribers));

	if ( !empty($alert) ) {
		$jr_log->write_log('Job Alerts *** Notified ( alert[] ): '.print_r($alert, true));
	}

	// continue only if there's any subscribers for the fetched jobs
	if ( sizeof($subscribers) > 0 ) :

		// loop through the subscribers and send the alert email
		foreach ( $subscribers as $subscriber_id => $subscriber_jobs ) :

			// send the email alert to each matching subscriber
			if ( jr_job_alerts_send_email( $subscriber_id, $subscriber_jobs ) ) {
				$notified_users[] = array ( $subscriber_id => $subscriber_jobs );
			} else {
				// email could not be sent - skip this user
				$jr_log->write_log('Job Alerts *** Error sending email to user #'.$subscriber_id. ' - skipped.');
			}

		endforeach;

		$jr_log->write_log('Job Alerts *** Notified users: '.sizeof($notified_users));

		if ( sizeof($notified_users) > 0 ) foreach ( $alert as $job_id => $alert_info ) :

			// if the total subscribers are lower then the maximum batch size clear the job alert
			if ( $alert_info['list_size'] <= $batch_size ) {

				jr_delete_job_alert( $job_id );

				$jr_log->write_log('Job Alerts *** Deleted #'.$job_id);

			} else {

				// update the email alerts activity to use on the next scheduled job email alerts batch
				$wpdb->query( $wpdb->prepare("UPDATE $wpdb->jr_alerts
							  SET last_activity = CURRENT_TIMESTAMP, last_user_id = %d
							  WHERE post_id = %d", $alert_info['last_user_id'], $job_id) );

				$jr_log->write_log('Job Alerts *** Updated #'.$job_id.'; User ID #'.$alert_info['last_user_id']);

			}

		endforeach;

		set_transient( 'jr_job_alerts_last_activity', time() );

	endif;
}

/**
 * Reads a job alert template file and returns it's contents.
 * The template path is filterable.
 *
 * @since 1.6.0
 *
 * @param int $template The template file name
 * @return string The file contents
 *
 */
function jr_job_alerts_read_template( $template = '' ) {
	global $jr_log;

	$template_path = apply_filters( 'jr_job_alerts_template_path', get_template_directory() );
	$template =  $template_path . '/' . $template;

	if ( !file_exists( $template ) ) {
		$jr_log->write_log("Alert template file '". $template ."' not found!");
		return false;
	}

	// reads an entire file into a string and returns it
	return file_get_contents($template);
}

/**
 * Reads and returns a list of template files based on a specific file pattern, found on the template path.
 * The template path and the returned array list are filterable.
 *
 * @since 1.6.0
 *
 * @return array The file contents
 *
 */
if (!function_exists('jr_job_alerts_get_templates')) :
function jr_job_alerts_get_templates( $template_pattern = '*tmpl-*.html' ) {

	$template_path = apply_filters( 'jr_job_alerts_template_path', get_template_directory() );
	$template_pattern =  $template_path . '/' . $template_pattern;

	$templates = array();

	// get all the available job alert templates
	foreach (glob($template_pattern) as $filename) {
		$tmpl = array( basename( $filename ) => sprintf( __( 'External HTML Template (%s).', APP_TD ), basename( $filename ) ) );
		$templates = array_merge($tmpl, $templates);
	}

	return apply_filters('jr_job_alerts_email_templates', $templates);
}
endif;

/**
 * Adds a new alert RSS feed for users with a specific role.
 * Feeds are unique to each user
 *
 * feed format: jobalerts-uniqueid-user_id
 *
 * @since 1.6.0
 *
 * @param int $user_id The user ID that should be associated with the feed.
 *
 */
function jr_job_alerts_add_new_feed( $user_id ) {
	global $jr_options;

	if ( ! current_user_can('job_seeker') ) {
		return;
	}

	if ( $jr_options->jr_job_alerts_feed && ! $user_feed_key = get_user_meta($user_id, 'jr_alert_feed_key', true) ) {
		$feed_id = uniqid();
		$user_feed_key = 'jobalerts-' . $feed_id . '-' . $user_id;
		update_user_meta($user_id, 'jr_alert_feed_key', $user_feed_key);
	}
}

/**
 * Initializes the alert RSS feeds already stored on the user meta.
 *
 * @since 1.6.0
 *
 */
function jr_job_alerts_add_feeds() {
	global $wpdb, $user_ID, $jr_options;

	if ( ! $jr_options->jr_job_alerts_feed ) {
		return;
	}

	// get the latest alerts
	$sql = "SELECT user_id, meta_value as feed_key FROM $wpdb->usermeta WHERE meta_key = 'jr_alert_feed_key'
			AND user_id IN
			(
				SELECT user_id
				FROM $wpdb->usermeta
				WHERE meta_key = 'jr_alert_status'
				AND meta_value = 'active'
			)";
	$feeds = $wpdb->get_results( $sql );

	foreach ( $feeds as $feed ) {
		add_feed($feed->feed_key, 'jr_job_alerts_do_feed');
	}

	// add feed for the current user
	jr_job_alerts_add_new_feed( $user_ID );
}

/**
 * Delete an existing job alert
 *
 * @since 1.7.0
 *
 * @param int $job_id The alert job id to be deleted
 */
function jr_delete_job_alert( $job_id ) {
	global $wpdb;

	$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->jr_alerts WHERE post_id = %d", $job_id ) );
}

/**
 * Outputs an RSS alert feed from a unique RSS URL.
 * Parses the URL to get the user id and compares the key to the value on the user meta. If there's a match displays the user alert feed.
 *
 * @since 1.6.0
 *
 *
 */
function jr_job_alerts_do_feed() {
	global $post, $wpdb, $find_posts_in;

	$feed = get_query_var( 'feed' );

	if ( ! $feed ) {
		return;
	}

	$feed_parts = explode('-', get_query_var( 'feed' ));

	// get the user ID and check if exists
	$user_id = $feed_parts[2];
	$user = get_userdata( $user_id );

	if ( ! $user ) {
		return;
	}

	// compare the feed key with the user stored feed key
	$db_feed_key = get_user_meta($user_id, 'jr_alert_feed_key', true);

	if ( $db_feed_key != $feed ) {
		return;
	}

	/*
	Gather user alert criteria
	*/
	$keywords 	= get_user_meta($user_id, 'jr_alert_meta_keyword', true);
	$locations 	= get_user_meta($user_id, 'jr_alert_meta_location', true);
	$job_types 	= get_user_meta($user_id, 'jr_alert_meta_job_type', true);
	$job_cats 	= get_user_meta($user_id, 'jr_alert_meta_job_cat', true);

	$found_posts = array();

	if ( is_array( $keywords ) && sizeof( $keywords ) > 0 ) {
		foreach( $keywords as $keyword ) {
			$keyword = trim($keyword);
			$result = $wpdb->get_col("SELECT ID from $wpdb->posts WHERE post_title LIKE '%$keyword%' OR post_content LIKE '%$keyword%';");
			if ($result) $found_posts = array_merge($result, $found_posts);
		}
	}

	if ( $locations ) {

		$find_posts_in = array();

		$radius = 0;

		if ( !is_array($locations) ) {
			$locations = array( $locations );
		}

		foreach ($locations as $location) {

			$radial_result = jr_radial_search($location, $radius);
			if ( is_array( $radial_result ) ) {
				$find_posts_in = array_merge( $find_posts_in, $radial_result['posts'] );
			}

		}

		if ( !empty($found_posts) ) {
			$found_posts = array_intersect($found_posts, $find_posts_in);
		} else {
			$found_posts = $find_posts_in;
		}

	}

	if ( empty( $job_types ) ) {
		$job_types = array();
	}

	if ( empty( $job_cats ) ) {
		$job_cats = array();
	}

	if ( (is_array($found_posts) && sizeof($found_posts) > 0 ) || sizeof($job_types) > 0 || sizeof($job_cats) > 0 ) :

		$args = array(
			'post_type'				=> APP_POST_TYPE,
			'post_status'			=> 'publish',
			'ignore_sticky_posts'	=> 1,
			'posts_per_page'		=> 50,
			'tax_query'				=> array(
				'relation' => 'AND',
			)
		);

		if ( sizeof($found_posts) > 0 )
			$args['post__in'] = $found_posts;

		if ( sizeof($job_types) > 0 )
			$args['tax_query'][] = array(
					'taxonomy' 	=> 'job_type',
					'field' 	=> 'id',
					'terms' 	=> $job_types,
					'operator'  => 'IN',
			);

		if ( sizeof($job_cats) > 0 )
			$args['tax_query'][] = 	array(
					'taxonomy' 	=> 'job_cat',
					'field' 	=> 'id',
					'terms' 	=> $job_cats,
					'operator'  => 'IN',
			);

		if ( isset($radial_result['address']) ) {
			$args['location_search'] = 1;
		}

		$jobs = query_posts($args);

	endif;

	header('Content-Type: ' . feed_content_type('rss2') . '; charset=' . get_option('blog_charset'), true);
	echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>
	<rss version="2.0"
		xmlns:content="http://purl.org/rss/1.0/modules/content/"
		xmlns:dc="http://purl.org/dc/elements/1.1/"
		xmlns:atom="http://www.w3.org/2005/Atom"
		xmlns:sy="http://purl.org/rss/1.0/modules/syndication/">
	<channel>
		<title><?php bloginfo_rss('name'); echo __(' / Job Alerts Feed for ',APP_TD) . $user->user_login; ?></title>
		<link><?php bloginfo_rss('url') ?></link>
		<description><?php bloginfo_rss("description") ?></description>
		<language><?php echo get_option('rss_language'); ?></language>
		<sy:updatePeriod><?php echo apply_filters( 'jr_job_alerts_rss_update_period', 'hourly' ); ?></sy:updatePeriod>
		<sy:updateFrequency><?php echo apply_filters( 'jr_job_alerts_rss_update_frequency', '1' ); ?></sy:updateFrequency>
<?php

			if (!empty($jobs)) while ( sizeof($jobs) > 0 && have_posts()) : the_post();

					$job_type_terms = $job_cat_terms = array();

					$company_name = strip_tags(get_post_meta($post->ID, '_Company', true));

					if ($company_name) $author  = $company_name;
					else $author = get_user_by('id', $post->post_author)->display_name;

					$terms = get_the_terms( $post->ID, APP_TAX_TYPE );
					foreach ( $terms as $term ) $job_type_terms[] = $term->name;

					$terms = get_the_terms( $post->ID, APP_TAX_CAT );
					foreach ( $terms as $term ) $job_cat_terms[] = $term->name;

					$header = sprintf("<p>by: %s @ %s | %s | %s</p>", $author, $post->post_date, implode(',', $job_type_terms), implode(',', $job_cat_terms) );
					$thumbnail = get_the_post_thumbnail( $post->ID, 'thumbnail');
					if ($thumbnail)	$thumbnail = "<p>" . get_the_post_thumbnail( $post->ID, 'thumbnail') . "</p>";
					$description = sprintf("<p>%s</p>", get_the_content());

	?>
					<item>
						<title><?php the_title(); ?></title>
						<link><?php the_permalink(); ?></link>
						<description><![CDATA[

							<?php
								echo $header;
								echo $thumbnail;
								echo $description;
							?>

							]]>

						</description>
						<pubDate><?php  $post->post_date?></pubDate>
					</item>
			<?php endwhile; ?>

	</channel>
	</rss>
<?php
	wp_reset_query();
}
