<?php
/**
 *
 * Emails that get called and sent out for JobRoller
 * @package JobRoller
 * @author AppThemes
 * @version 1.7
 * For wp_mail to work, you need the following:
 * settings SMTP and smtp_port need to be set in your php.ini
 * also, either set the sendmail_from setting in php.ini, or pass it as an additional header.
 *
 */


add_action( 'transition_post_status', 'jr_email_notifications', 10, 3 );

add_action( 'appthemes_transaction_completed', 'jr_order_complete_notify_owner', 11 );

add_action( 'appthemes_transaction_failed', 'jr_order_canceled', 12 );
add_action( 'appthemes_transaction_failed', 'jr_order_canceled_notify_owner', 13 );

add_action( 'after_setup_theme', 'jr_custom_registration_email', 1000 );

add_action( 'user_resume_subscription_started', 'jr_user_resume_subscription_started_email', 15 );
add_action( 'user_resume_subscription_started', 'jr_admin_resume_subscription_started_email', 15 );

add_action( 'user_resume_subscription_ended', 'jr_user_resume_subscription_ended_email' );
add_action( 'user_resume_subscription_ended', 'jr_admin_resume_subscription_ended_email' );

add_action( 'jr_resume_header', 'jr_resume_contact_author_email' );

add_action( 'jr_job_expiring_soon', 'jr_owner_job_expiring_soon', 10, 2 );
add_action( 'jr_job_expired', 'jr_owner_job_expired', 12, 2 );

add_action( 'jr_expire_user_pack', 'jr_owner_pack_expired_email', 10, 2 );

// trigger email notifications on job status change
function jr_email_notifications( $new_status, $old_status, $post ) {
	global $jr_options;

	if ( APP_POST_TYPE != $post->post_type || 'new' == $old_status || $old_status == $new_status ) {
		return;
	}

	if ( in_array( $new_status, array( 'publish', 'pending' ) ) && ! in_array( $old_status, array( 'publish', 'expired' ) ) ) {

		if ( $jr_options->jr_new_ad_email ) {
			jr_new_job_notify_admin( $post->ID, $new_status );
		}
		jr_new_job_notify_owner( $post->ID, $new_status );

	} elseif ( 'draft' != $new_status && 'pending' != $new_status ) {
		jr_job_status_change_notify_owner( $new_status, $old_status, $post );
	}

}

// notify admins on new posted jobs
function jr_new_job_notify_admin( $post_id, $status = 'publish' ) {
	global $jr_log;

	$job_info = get_post($post_id);

	$job_title = stripslashes($job_info->post_title);
	$job_author = stripslashes(get_the_author_meta('user_login', $job_info->post_author));
	$job_author_email = stripslashes(get_the_author_meta('user_email', $job_info->post_author));
	$job_status = stripslashes($job_info->post_status);
	$job_slug = get_permalink($post_id);
	$adminurl = admin_url("post.php?action=edit&post=$post_id");

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$mailto = get_option('admin_email');

	if ( 'publish' == $status ) {
		$subject = sprintf( __( '[%s] New Job Submitted', APP_TD ), $blogname );
	} else {
		$subject = sprintf( __( '[%s] New Job Pending Approval', APP_TD ), $blogname );
	}

	// Message

	$message  = __('Dear Admin,', APP_TD) . PHP_EOL . PHP_EOL;
	$message .= sprintf(__('The following job listing has just been submitted on your %s website.', APP_TD), $blogname) . PHP_EOL . PHP_EOL;
	$message .= __('Job Details', APP_TD) . PHP_EOL;
	$message .= '-----------------' . PHP_EOL;
	$message .= __('Title: ', APP_TD) . $job_title . PHP_EOL;
	$message .= __('Author: ', APP_TD) . $job_author . PHP_EOL;
	$message .= '-----------------' . PHP_EOL . PHP_EOL;
	$message .= __('View Job: ', APP_TD) . $job_slug . PHP_EOL;
	$message .= sprintf(__('Edit Job: %s', APP_TD), $adminurl) . PHP_EOL . PHP_EOL . PHP_EOL;

	$message .= jr_email_signature( 'new_job_admin' );

	// ok let's send the email
	wp_mail( $mailto, $subject, $message, _jr_email_headers() );

	$jr_log->write_log('Email Sent to Admin: New Job Submitted ('.$job_title.')');
}

// notify owner on new posted jobs
function jr_new_job_notify_owner( $post_id, $status = 'publish' ) {
	global $jr_log;

	$job_info = get_post($post_id);

	$job_title = stripslashes($job_info->post_title);
	$job_author = stripslashes(get_the_author_meta('user_login', $job_info->post_author));
	$job_author_email = stripslashes(get_the_author_meta('user_email', $job_info->post_author));

	$dashurl = trailingslashit( get_permalink( JR_Dashboard_Page::get_id() ) );

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$mailto = $job_author_email;
	$subject = sprintf( __( '[%s] Your Job Submission', APP_TD ), $blogname );

	// Message
	$message  = sprintf(__('Hi %s,', APP_TD), $job_author) . PHP_EOL . PHP_EOL;
	$message .= __( 'Thank you for your recent submission! ', APP_TD );

	if ( 'publish' == $status )
		$message .= __( 'Your job listing has been approved and is now live on our site .', APP_TD ) . PHP_EOL . PHP_EOL;
	else
		$message .= __( 'Your job listing has been submitted for review and will not appear live on our site until it has been approved.', APP_TD ) . PHP_EOL . PHP_EOL;

	$message .= sprintf( __( 'Below you will find a summary of your job listing on the %s website.', APP_TD ), $blogname ) . PHP_EOL . PHP_EOL;

	$message .= __('Job Details', APP_TD) . PHP_EOL;
	$message .= '-----------------' . PHP_EOL;
	$message .= __('Title: ', APP_TD) . $job_title . PHP_EOL;
	$message .= __('Author: ', APP_TD) . $job_author . PHP_EOL;
	$message .= '-----------------' . PHP_EOL . PHP_EOL;

	if ( 'publish' == $status ) {
		$message .= __('You can view your job by clicking on the following link:', APP_TD ) . PHP_EOL;
		$message .= get_permalink($post_id) . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;
	} else {
		$message .= __('You may check the status of your job(s) at anytime by logging into the "My Jobs" page.', APP_TD) . PHP_EOL;
		$message .= $dashurl . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;
	}

	$message .= jr_email_signature( 'new_job' );

	// ok let's send the email
	wp_mail( $mailto, $subject, $message, _jr_email_headers() );

	$jr_log->write_log('Email Sent to author ('.$job_author.'): Your Job Submission ('.$job_title.') on...');
}

// when a job's status changes, send the job owner an email
function jr_job_status_change_notify_owner( $new_status, $old_status, $post ) {
	global $wpdb, $jr_log, $jr_options;

	$job_info = get_post($post->ID);

	if ( APP_POST_TYPE != $job_info->post_type ) {
		return;
	}

	$job_title = stripslashes($job_info->post_title);
	$job_author_id = $job_info->post_author;
	$job_author = stripslashes(get_the_author_meta('user_login', $job_info->post_author));
	$job_author_email = stripslashes(get_the_author_meta('user_email', $job_info->post_author));

	$mailto = $job_author_email;

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode( get_option('blogname'), ENT_QUOTES );

	// make sure the admin wants to send emails
	$send_approved_email = $jr_options->jr_new_job_email_owner;

	// if the job has been approved send email to ad owner only if owner is not equal to approver
	// admin approving own jobs or job owner pausing and reactivating ad on his dashboard don't need to send email
	if ( $old_status == 'pending' && $new_status == 'publish' && get_current_user_id() != $job_author_id && $send_approved_email == 'yes' ) {

		$subject = sprintf( __( '[%s] Your Job Has Been Approved', APP_TD ), $blogname );

		$message  = sprintf(__('Hi %s,', APP_TD), $job_author) . PHP_EOL . PHP_EOL;
		$message .= sprintf(__('Your job listing, "%s" has been approved and is now live on our site.', APP_TD), $job_title) . PHP_EOL . PHP_EOL;

		$message .= __('You can view your job by clicking on the following link:', APP_TD) . PHP_EOL;
		$message .= get_permalink($post->ID) . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;

		$message .= jr_email_signature( 'job_approved' );

		// ok let's send the email
		wp_mail( $mailto, $subject, $message, _jr_email_headers() );

		$jr_log->write_log('Email Sent to author ('.$job_author.'): Your Job Has Been Approved ('.$job_title.')');

	}

}

// Edited Jobs that require moderation
function jr_edited_job_pending( $post_id ) {
	global $jr_log;

	$job_info = get_post($post_id);

	$job_title = stripslashes($job_info->post_title);
	$job_author = stripslashes(get_the_author_meta('user_login', $job_info->post_author));
	$adminurl = admin_url("post.php?action=edit&post=$post_id");

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$mailto = get_option('admin_email');
	$subject = sprintf( __( '[%s] Edited Job Pending Approval', APP_TD ), $blogname );

	// Message

	$message  = __('Dear Admin,', APP_TD) . PHP_EOL . PHP_EOL;
	$message .= sprintf(__('The following job listing has just been edited on your %s website.', APP_TD), $blogname) . PHP_EOL . PHP_EOL;
	$message .= __('Job Details', APP_TD) . PHP_EOL;
	$message .= '-----------------' . PHP_EOL;
	$message .= __('Title: ', APP_TD) . $job_title . PHP_EOL;
	$message .= __('Author: ', APP_TD) . $job_author . PHP_EOL;
	$message .= '-----------------' . PHP_EOL . PHP_EOL;
	$message .= __('Preview Job: ', APP_TD) . get_permalink($post_id) . PHP_EOL;
	$message .= sprintf(__('Edit Job: %s', APP_TD), $adminurl) . PHP_EOL . PHP_EOL . PHP_EOL;

	$message .= jr_email_signature( 'edited_job_admin' );

	// ok let's send the email
	wp_mail( $mailto, $subject, $message, _jr_email_headers() );

	$jr_log->write_log('Email Sent to Admin: Edited Job Pending Approval ('.$job_title.')');
}

// notify admins on new orders
function jr_new_order_notify_admin( $order ) {

	if ( get_transient( 'jr_notified_admin_order_' . $order->get_id() ) )
		return;

	$recipient = get_bloginfo('admin_email');

	$orders_url = admin_url('edit.php?post_type='.APPTHEMES_ORDER_PTYPE.'&post_status='.APPTHEMES_ORDER_PENDING);

	$job_id = jr_get_order_job_id( $order );

	$item = '';
	if ( $job_id ) {
		foreach ( $order->get_items() as $item ) {
			$item = html( 'p', html_link( get_permalink( $item['post']->ID ), $item['post']->post_title ) );
			break;
		}
	}

	$table = new APP_Order_Summary_Table( $order );
	ob_start();
	$table->show();
	$table_output = ob_get_clean();

	$content = '';
	$content .= html( 'p', __( 'Dear Admin,', APP_TD ) );
	$content .= html( 'p', sprintf( __( 'A new Order #%d has just been submitted on your %s website.', APP_TD ), $order->get_id(), get_bloginfo( 'name' ) ) );
	if ( $item ) $content .= $item;
	$content .= html( 'p', __( 'Order Summary:', APP_TD ) );
	$content .= $table_output;

	// check if order was paid with a user pack
	if ( $order->get_total() == 0 && $user_plans_history = _jr_user_plans_history( $order->get_author() ) ) {
		$plan = jr_plan_for_order( $order );
		if ( isset($user_plans_history[$plan->ID]) ) {
			$content .= html( 'p', __( 'Note: This Order is empty because it was paid with a User Pack.', APP_TD ) );
		}
	}

	$content .= html( 'p', sprintf( __( '<a href="%s">View all pending Orders</a>', APP_TD ), esc_url( $orders_url ) ) );
	$content .= html( 'p', '&nbsp;' );

	$content .= jr_email_signature( 'new_order_admin', 'html' );

	$subject = sprintf( __( '[%s] New Order #%d', APP_TD ), get_bloginfo( 'name' ), $order->get_id() );

	// avoid sending duplicate emails while the order is being processed by gateways
	set_transient( 'jr_notified_admin_order_' . $order->get_id(), $order->get_id(), 60 * 5  );

	appthemes_send_email( $recipient, $subject, $content );
}


function jr_new_order_notify_owner( $order ) {

	if ( get_transient( 'jr_notified_author_new_order_' . $order->get_id() ) )
		return;

	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$recipient = get_user_by( 'id', $order->get_author() );

	$item = '';
	foreach ( $order->get_items() as $item ) {
		$item = html( 'p', html_link( get_permalink( $item['post']->ID ), $item['post']->post_title ) );
		break;
	}

	$table = new APP_Order_Summary_Table( $order );
	ob_start();
	$table->show();
	$table_output = ob_get_clean();

	$content = '';
	$content .= html( 'p', sprintf( __( 'Hello %s,', APP_TD ), $recipient->display_name ) );
	$content .= html( 'p', __( 'Your order has been submitted with success and will be available as soon as the payment clears.', APP_TD ) );
	$content .= $item;
	$content .= html( 'p', __( 'Order Summary:', APP_TD ) );
	$content .= $table_output;
	$content .= html( 'p', '&nbsp;' );

	$content .= jr_email_signature( 'new_order', 'html' );

	$subject = sprintf( __( '[%s] Pending Order #%d', APP_TD ), get_bloginfo( 'name' ), $order->get_id() );

	// avoid sending duplicate emails while the order is being processed by gateways
	set_transient( 'jr_notified_author_new_order_' . $order->get_id(), $order->get_id(), 60 * 5  );

	appthemes_send_email( $recipient->user_email, $subject, $content );
}

function jr_order_complete_notify_owner( $order ) {

	$recipient = get_user_by( 'id', $order->get_author() );

	$item = '';
	foreach ( $order->get_items() as $item ) {
		$item = html( 'p', html_link( get_permalink( $item['post']->ID ), $item['post']->post_title ) );
		break;
	}

	$table = new APP_Order_Summary_Table( $order );
	ob_start();
	$table->show();
	$table_output = ob_get_clean();

	$content = '';
	$content .= html( 'p', sprintf( __( 'Hello %s,', APP_TD ), $recipient->display_name ) );
	$content .= html( 'p', __( 'This email confirms that you have purchased the following items:', APP_TD ) );
	$content .= $item;
	$content .= html( 'p', __( 'Order Summary:', APP_TD ) );
	$content .= $table_output;
	$content .= html( 'p', '&nbsp;' );

	$content .= jr_email_signature( 'order_complete', 'html' );

	$subject = sprintf( __( '[%s] Receipt for Your Order #%d', APP_TD ), get_bloginfo( 'name' ), $order->get_id() );

	appthemes_send_email( $recipient->user_email, $subject, $content );
}

// notify admins on canceled orders
function jr_order_canceled( $order ) {

	$recipient = get_bloginfo('admin_email');

	$orders_url = admin_url('edit.php?post_type='.APPTHEMES_ORDER_PTYPE.'&post_status='.APPTHEMES_ORDER_FAILED);

	$content = '';
	$content .= html( 'p', __( 'Dear Admin,', APP_TD ) );
	$content .= html( 'p', sprintf( __( 'Order number #%d has just been canceled on your %s website.', APP_TD ), $order->get_id(), get_bloginfo( 'name' ) ) );
	$content .= html( 'p', sprintf( __( '<a href="%s">View all canceled Orders</a>', APP_TD ), esc_url( $orders_url ) ) );
	$content .= html( 'p', '&nbsp;' );

	$content .= jr_email_signature( 'order_canceled_admin', 'html' );

	$subject = sprintf( __( '[%s] Order canceled #%d', APP_TD ), get_bloginfo( 'name' ), $order->get_id() );

	appthemes_send_email( $recipient, $subject, $content );
}

// notify admins on canceled orders
function jr_order_canceled_notify_owner( $order ) {

	$recipient = get_user_by( 'id', $order->get_author() );

	$item = '';
	foreach ( $order->get_items() as $item ) {
		$item = html( 'p', html_link( get_permalink( $item['post']->ID ), $item['post']->post_title ) );
		break;
	}

	$table = new APP_Order_Summary_Table( $order );
	ob_start();
	$table->show();
	$table_output = ob_get_clean();

	$content = '';
	$content .= html( 'p', sprintf( __( 'Hello %s,', APP_TD ), $recipient->display_name ) );
	$content .= html( 'p', sprintf( __( 'Your Order #%d has just been canceled.', APP_TD ), $order->get_id() ) );

	$content .= $item;
	$content .= html( 'p', __( 'Order Summary:', APP_TD ) );
	$content .= $table_output;
	$content .= html( 'p', '&nbsp;' );

	$content .= jr_email_signature( 'order_canceled', 'html' );

	$subject = sprintf( __( '[%s] Order canceled #%d', APP_TD ), get_bloginfo( 'name' ), $order->get_id() );

	appthemes_send_email( $recipient->user_email, $subject, $content );
}

// Job will expire soon
function jr_owner_job_expiring_soon( $post_id, $days_remaining ) {
	global $jr_log;

	$job_info = get_post($post_id);

	$days_text = sprintf( '%d %s', $days_remaining, _n( 'day' , 'days', $days_remaining ) );

	$job_title = stripslashes($job_info->post_title);
	$job_author = stripslashes(get_the_author_meta('user_login', $job_info->post_author));
	$job_author_email = stripslashes(get_the_author_meta('user_email', $job_info->post_author));

	$dashurl = trailingslashit( get_permalink( JR_Dashboard_Page::get_id() ) );

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$mailto = $job_author_email;
	$subject = sprintf( __( '[%s] Your Job Submission expires in %s', APP_TD ), $blogname, $days_text );

	// Message
	$message  = sprintf(__('Hi %s,', APP_TD), $job_author) . PHP_EOL . PHP_EOL;
	$message .= sprintf( __('Your job listing is set to expire in %s', APP_TD ), $days_text ) . PHP_EOL . PHP_EOL;
	$message .= __('Job Details', APP_TD) . PHP_EOL;
	$message .= '-----------------' . PHP_EOL;
	$message .= __('Title: ', APP_TD) . $job_title . PHP_EOL;
	$message .= __('Author: ', APP_TD) . $job_author . PHP_EOL;
	$message .= '-----------------' . PHP_EOL . PHP_EOL;
	$message .= __('You may check the status of your job(s) at anytime by logging into the "My Jobs" page.', APP_TD) . PHP_EOL;
	$message .= $dashurl . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;

	$message .= jr_email_signature( 'job_expiring' );

	// ok let's send the email
	wp_mail( $mailto, $subject, $message, _jr_email_headers() );

	$jr_log->write_log('Email Sent to author ('.$job_author.'): Your Job Submission ('.$job_title.') on...expires in '.$days_text);
}

// if the job has expired, send an email to the job owner
function jr_owner_job_expired( $post_id, $canceled = false ) {
	global $jr_log, $jr_options;

	if ( ! $jr_options->jr_expired_job_email_owner && ! $canceled ) {
		return;
	}

	$job_info = get_post($post_id);

	$job_title = stripslashes($job_info->post_title);
	$job_author = stripslashes(get_the_author_meta('user_login', $job_info->post_author));
	$job_author_email = stripslashes(get_the_author_meta('user_email', $job_info->post_author));

	$dashurl = trailingslashit( get_permalink( JR_Dashboard_Page::get_id() ) );

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$mailto = $job_author_email;

	if ( $canceled ) {
		$subject = sprintf( __( '[%s] Your Job was Canceled', APP_TD ), $blogname );

		$message  = sprintf(__('Hi %s,', APP_TD), $job_author) . PHP_EOL . PHP_EOL;
		$message .= sprintf(__('Your job listing, "%s" was canceled.', APP_TD), $job_title) . PHP_EOL . PHP_EOL;
		$message .= __('You can still access your job on your dashboard and submit it again, if you wish.', APP_TD) . PHP_EOL . PHP_EOL;
		$message .= $dashurl . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;

	} else {
		$subject = sprintf( __( '[%s] Your Job Has Expired', APP_TD ), $blogname );

		$message  = sprintf( __('Hi %s,', APP_TD), $job_author ) . PHP_EOL . PHP_EOL;
		$message .= sprintf( __( 'Your job listing, "%s" has expired.', APP_TD), $job_title ) . PHP_EOL . PHP_EOL;

		if ( jr_allow_relist() ) {
			$message .= __('If you would like to relist your job please go to your Dashboard, and in the "Ended" Tab, click the "relist" link.', APP_TD) . PHP_EOL;
			$message .= $dashurl . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;
		}
	}

	$message .= jr_email_signature( 'expired_job' );

	// ok let's send the email
	wp_mail( $mailto, $subject, $message, _jr_email_headers() );

	$jr_log->write_log('Email Sent to author ('.$job_author.'): Your Job Has Expired ('.$job_title.')');

}

// if the pack has expired, send an email to the pack owner
function jr_owner_pack_expired_email( $user_id, $plan_umeta_id ) {
	global $jr_log;

	$pack_meta = _jr_user_pack_meta( $user_id, $plan_umeta_id );
	if ( empty($pack_meta['plan_id']) )
		return;

	$plan = get_post_custom( $pack_meta['plan_id'] );
	if ( empty($plan['title'][0]) )
		return;

	$pack_name = $plan['title'][0];

	$pack_author = stripslashes(get_the_author_meta('user_login', $user_id));
	$pack_author_email = stripslashes(get_the_author_meta('user_email', $user_id));

	$dashurl = trailingslashit( get_permalink( JR_Dashboard_Page::get_id() ) );

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$mailto = $pack_author_email;

	$subject = sprintf( __( '[%s] Your Pack Has Expired', APP_TD ), $blogname );

	$message  = sprintf( __( 'Hi %s,', APP_TD), $pack_author ) . PHP_EOL . PHP_EOL;
	$message .= sprintf( __( 'Your "%s" pack, purchased in "%s", has ended.', APP_TD), $pack_name, appthemes_display_date( intval($pack_meta['start_date']), 'date' ) ) . PHP_EOL . PHP_EOL;

	$message .= __( 'Note that you\'ll only be able to submit jobs if you have active packs.', APP_TD ) . PHP_EOL . PHP_EOL;

	if ( jr_allow_purchase_separate_packs() ) {
		$message .= __( 'To purchase new packs, please go to your Dashboard and click "Buy Packs", under the "Job Packs" Tab.', APP_TD ) . PHP_EOL;
		$message .= $dashurl . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;
	}

	$message .= jr_email_signature( 'expired_pack' );

	// ok let's send the email
	wp_mail( $mailto, $subject, $message, _jr_email_headers() );

	$jr_log->write_log( 'Email Sent to author ('.$pack_author.'): Your Pack Has Expired ('.$pack_author.')' );

}

/**
 * Replaces default registration email.
 * @since 1.6.4
 *
 * @return void
 */
function jr_custom_registration_email() {
	remove_action( 'appthemes_after_registration', 'appthemes_new_user_notification', 10, 2 );
	add_action( 'appthemes_after_registration', 'jr_new_user_notification', 10, 2 );
}

/**
 * Sends new user notification.
 *
 * @param int $user_id
 * @param string $plaintext_pass (optional)
 *
 * @return void
 */
function jr_new_user_notification( $user_id, $plaintext_pass = '' ) {
	global $jr_options;

	$user = get_user_by( 'id', $user_id );

	$user_login = $user->user_login;
	$user_email = $user->user_email;

	// variables that can be used by admin to dynamically fill in email content
	$find = array( '/%username%/i', '/%password%/i', '/%blogname%/i', '/%siteurl%/i', '/%loginurl%/i', '/%useremail%/i' );
	if ( 'text/html' == $jr_options->jr_nu_email_type ) {
		$replace = array( $user_login, $plaintext_pass, get_bloginfo( 'name' ), html_link( home_url( '/' ) ), html_link( wp_login_url() ), $user_email );
	} else {
		$replace = array( $user_login, $plaintext_pass, get_bloginfo( 'name' ), home_url( '/' ), wp_login_url(), $user_email );
	}

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

	// send the site admin an email everytime a new user registers
	if ( $jr_options->jr_nu_admin_email ) {
		$subject = sprintf( __( '[%s] New User Registration', APP_TD ), $blogname );

		$message  = html( 'p', sprintf( __( 'New user registration on your site %s:', APP_TD ), $blogname ) ) . PHP_EOL;
		$message .= html( 'p', sprintf( __( 'Username: %s', APP_TD ), $user_login ) ) . PHP_EOL;
		$message .= html( 'p', sprintf( __( 'E-mail: %s', APP_TD ), $user_email ) ) . PHP_EOL;

		$email = array( 'to' => get_option( 'admin_email' ), 'subject' => $subject, 'message' => $message );
		$email = apply_filters( 'jr_email_admin_new_user', $email, $user_id, $plaintext_pass );

		appthemes_send_email( $email['to'], $email['subject'], $email['message'] );
	}

	if ( empty( $plaintext_pass ) ) {
		return;
	}

	// check and see if the custom email option has been enabled
	// if so, send out the custom email instead of the default WP one
	if ( $jr_options->jr_nu_custom_email ) {

		// email sent to new user starts here
		$from_name = strip_tags( $jr_options->jr_nu_from_name );
		$from_email = strip_tags( $jr_options->jr_nu_from_email );

		// search and replace any user added variable fields in the subject line
		$subject = stripslashes( $jr_options->jr_nu_email_subject );
		$subject = preg_replace( $find, $replace, $subject );
		$subject = preg_replace( "/%.*%/", "", $subject );

		// search and replace any user added variable fields in the body
		$message = $jr_options->jr_nu_email_body;
		$message = preg_replace( $find, $replace, $message );
		$message = preg_replace( "/%.*%/", "", $message );

		// maybe convert to html
		if ( 'text/html' == $jr_options->jr_nu_email_type ) {
			$tmp_msg = strip_tags( $jr_options->jr_nu_email_body );
			// message seems to be plain text
			if ( $tmp_msg == $jr_options->jr_nu_email_body ) {
				$message = wpautop( $message );
			}
		}

		$email = array( 'to' => $user_email, 'subject' => $subject, 'message' => $message, 'from' => $from_email, 'from_name' => $from_name );
		$email = apply_filters( 'jr_email_user_new_user_custom', $email, $user_id, $plaintext_pass );

		APP_Mail_From::apply_once( array( 'email' => $email['from'], 'name' => $email['from_name'] ) );
		if ( 'text/html' == $jr_options->jr_nu_email_type ) {
			appthemes_send_email( $email['to'], $email['subject'], $email['message'] );
		} else {
			wp_mail( $email['to'], $email['subject'], $email['message'] );
		}

	// send the default email
	} else {

		$subject = sprintf( __( '[%s] Your username and password', APP_TD ), $blogname );

		$message  = html( 'p', sprintf( __( 'Username: %s', APP_TD ), $user_login ) ) . PHP_EOL;
		$message .= html( 'p', sprintf( __( 'Password: %s', APP_TD ), $plaintext_pass ) ) . PHP_EOL;
		$message .= html( 'p', html_link( wp_login_url() ) ) . PHP_EOL;

		$email = array( 'to' => $user_email, 'subject' => $subject, 'message' => $message );
		$email = apply_filters( 'jr_email_user_new_user', $email, $user_id, $plaintext_pass );

		appthemes_send_email( $email['to'], $email['subject'], $email['message'] );

	}

}

// Email sent to users when starting subscriptions
function jr_user_resume_subscription_started_email( $user_id, $addon = false ) {
	global $jr_log;

	$echo_browse = __('browse',APP_TD);
	$echo_view = __('view',APP_TD);
	$echo_and = __(' and ', APP_TD);

	if ( $addon ) {
		$view_resumes = _jr_user_addon_expire_date( $user_id, JR_ITEM_VIEW_RESUMES );
		$browse_resumes = _jr_user_addon_expire_date( $user_id, JR_ITEM_BROWSE_RESUMES );

		$resume_access='';
		if ( $view_resumes || $browse_resumes ) {
			$access_type = __( 'temporary access', APP_TD );

			$resume_access  = $browse_resumes ? $echo_browse : '';
			$resume_access .= $view_resumes ? ( $resume_access? $echo_and :'' ) . $echo_view : '';
		};
	} else {
		$trial = get_user_meta( $user_id, '_valid_resume_trial', true );
		$access_type = sprintf( __( '%s access subscription', APP_TD ), ( $trial ? __( 'trial', APP_TD ) : '' ) );
		$resume_access = $echo_browse . $echo_and . $echo_view;
	}

	$user_name = stripslashes(get_the_author_meta('user_login', $user_id));
	$user_email = stripslashes(get_the_author_meta('user_email', $user_id));

	$siteurl = trailingslashit( home_url() );
	$dashurl = trailingslashit( get_permalink( JR_Dashboard_Page::get_id() ) );

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$mailto = $user_email;
	$subject = sprintf( __( '[%s] Your resume %s is now active', APP_TD ), $blogname, $access_type );

	// Message
	$message  = sprintf( __('Hi %s,', APP_TD), $user_name ) . PHP_EOL . PHP_EOL;
	$message .= sprintf( __('Your resume %s has just been activated. You can now %s resumes on %s.', APP_TD), $access_type, $resume_access, $blogname ) . PHP_EOL . PHP_EOL;
	$message .= $dashurl . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;

	$message .= jr_email_signature( 'resume_subscription_start' );

	// ok let's send the email
	wp_mail( $mailto, $subject, $message, _jr_email_headers() );

	$jr_log->write_log('Email Sent to user ('.$user_name.'): Your resume access subscription is now active');
}

// New user subscription
function jr_admin_resume_subscription_started_email( $user_id ) {
	global $jr_log;

	$user_name = stripslashes(get_the_author_meta('user_login', $user_id));
	$user_email = stripslashes(get_the_author_meta('user_email', $user_id));

	$user_admin_url = admin_url('user-edit.php?user_id='.$user_id);

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$mailto = get_option('admin_email');
	$subject = sprintf( __( '[%s] New Resume Subscription', APP_TD ), $blogname );

	// Message
	$message  = __('Dear Admin,', APP_TD) . PHP_EOL . PHP_EOL;
	$message .= sprintf(__('The following user has just been granted resume access on your %s website.', APP_TD), $blogname) . PHP_EOL . PHP_EOL;
	$message .= __('User Details', APP_TD) . PHP_EOL;
	$message .= '-----------------' . PHP_EOL;
	$message .= __('Name: ', APP_TD) . $user_name . PHP_EOL;
	$message .= __('Email: ', APP_TD) . $user_email . PHP_EOL;
	$message .= '-----------------' . PHP_EOL . PHP_EOL;
	$message .= __('View User: ', APP_TD) . $user_admin_url . PHP_EOL;

	$message .= jr_email_signature( 'resume_access_admin' );

	// ok let's send the email
	wp_mail( $mailto, $subject, $message, _jr_email_headers() );

	$jr_log->write_log( sprintf( 'Email Sent to Admin: New Resume Subscription - User ID #%d ', $user_id ) );
}

// Expired user subscription
function jr_user_resume_subscription_ended_email( $user_id ) {
	global $jr_log;

	$user_name = stripslashes(get_the_author_meta('user_login', $user_id));
	$user_email = stripslashes(get_the_author_meta('user_email', $user_id));

	$dashurl = trailingslashit( get_permalink( JR_Dashboard_Page::get_id() ) );

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$mailto = $user_email;
	$subject = sprintf( __( '[%s] Your resume access subscription has expired', APP_TD ), $blogname );

	// Message
	$message  = sprintf(__('Hi %s,', APP_TD), $user_name) . PHP_EOL . PHP_EOL;
	$message .= sprintf(__('Your resume access subscription has just expired. To continue browsing resumes on %s you need to subscribe.', APP_TD), $blogname) . PHP_EOL . PHP_EOL;
	$message .= $dashurl . PHP_EOL . PHP_EOL . PHP_EOL;

	$message .= jr_email_signature( 'resume_subscription_end' );

	// ok let's send the email
	wp_mail( $mailto, $subject, $message, _jr_email_headers() );

	$jr_log->write_log('Email Sent to user ('.$user_name.'): Your resume access subscription has expired');
}

// Expired user subscription - admin email
function jr_admin_resume_subscription_ended_email( $user_id ) {
	global $jr_log;

	$user_name = stripslashes(get_the_author_meta('user_login', $user_id));
	$user_email = stripslashes(get_the_author_meta('user_email', $user_id));

	$user_admin_url = admin_url('user-edit.php?user_id='.$user_id);

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$mailto = get_option('admin_email');
	$subject = sprintf( __( '[%s] Expired Resume Subscription', APP_TD ), $blogname );

	// Message
	$message  = __('Dear Admin,', APP_TD) . PHP_EOL . PHP_EOL;
	$message .= sprintf(__('The resume subscription for the following user has just expired on your %s website.', APP_TD), $blogname) . PHP_EOL . PHP_EOL;
	$message .= __('User Details', APP_TD) . PHP_EOL;
	$message .= '-----------------' . PHP_EOL;
	$message .= __('Name: ', APP_TD) . $user_name . PHP_EOL;
	$message .= __('Email: ', APP_TD) . $user_email . PHP_EOL;
	$message .= '-----------------' . PHP_EOL . PHP_EOL;
	$message .= __('View User: ', APP_TD) . $user_admin_url . PHP_EOL . PHP_EOL;

	$message .= jr_email_signature( 'resume_subscription_end_admin' );

	// ok let's send the email
	wp_mail( $mailto, $subject, $message, _jr_email_headers() );

	$jr_log->write_log('Email Sent to Admin: Expired Resume Subscription');
}

// Send email to resume authors from the contact form
function jr_resume_contact_author_email() {
	global $jr_log, $post, $message;

	if (isset($_POST['_wpnonce']) && !wp_verify_nonce($_POST['_wpnonce'], 'contact-resume-author_' . $post->post_author)) :

		$arr_params = array (
			'resume_contact'=> 0,
		);

		$log_message = 'Invalid security token while sending email to resume author ('.$resume_author.'): Reply to Resume - ' . $resume_title;

	else:

		if (isset($_POST['send_message'])) :

			$siteurl = trailingslashit( home_url() );
			$resume_title = $post->post_title;

			$resume_author = stripslashes(get_the_author_meta('user_login', $post->post_author));
			$resume_email = stripslashes(get_the_author_meta('user_email', $post->post_author));

			$contact_name = stripslashes( $_POST['contact_name'] );
			$contact_email = stripslashes( $_POST['contact_email'] );
			$contact_subject = stripslashes( $_POST['contact_subject'] );
			$contact_message = strip_tags( $_POST['contact_message'] );

			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

			$mailto = $resume_email;
			$subject = sprintf( __( '[%s] Reply to your resume \'%s\'', APP_TD ), $blogname, $resume_title );

			// Message
			$message  = sprintf(__('Hi %s,', APP_TD), $resume_author) . PHP_EOL . PHP_EOL;
			$message .= sprintf(__('%s has just sent you a message in reply to your resume \'%s\'. Please read details below:', APP_TD), $contact_name, $resume_title) . PHP_EOL . PHP_EOL;
			$message .= sprintf(__('From: %s <%s>.', APP_TD), $contact_name, $contact_email) . PHP_EOL;
			$message .= sprintf(__('Subject: %s.', APP_TD), $contact_subject) . PHP_EOL;
			$message .= sprintf(__('Message: %s.', APP_TD), $contact_message) . PHP_EOL . PHP_EOL. PHP_EOL;

			$message .= jr_email_signature( 'reply_to_resume' );

			// ok let's send the email
			$result = wp_mail( $mailto, $subject, $message, _jr_email_headers() );

			if ($result) :
				$notify_message = 1;
				$log_message = 'Email Sent to user ('.$resume_author.'): Reply to Resume - ' . $resume_title;
			else:
				$notify_message = 0;
				$log_message = 'Error sending email to user ('.$resume_author.'): Reply to Resume - ' . $resume_title;
			endif;

			$arr_params = array (
				'resume_contact' => $notify_message,
			);

		endif;

	endif;

	if ( isset($arr_params) && is_array($arr_params) ):
		$redirect = add_query_arg( $arr_params );
		$jr_log->write_log($log_message);
		$redirect = esc_url_raw( $redirect );
		wp_redirect( $redirect );
		exit;
	endif;

}


/**
 * Sends an email from contact form page.
 *
 * @param array $posted
 *
 * @return bool|object
 */
function jr_contact_site_email( $posted ) {

	$errors = jr_get_listing_error_obj();

	// Prepare email
	$from_name = appthemes_filter( appthemes_clean( $posted['your_name'] ) );
	$from_email = appthemes_clean( $posted['email'] );
	$posted_message = appthemes_filter( appthemes_clean( $posted['message'] ) );

	// check for required post data
	$expected = array( 'your_name', 'email', 'message' );
	foreach ( $expected as $field_name ) {
		if ( empty( $posted[ $field_name ] ) ) {
			$errors->add( 'empty_field', __( 'ERROR: All fields are required.', APP_TD ) );
			return $errors;
		}
	}

	// verify email
	if ( ! is_email( $posted['email'] ) ) {
		$errors->add( 'invalid_email', __( 'ERROR: Incorrect email address.', APP_TD ) );
	}

	if ( $errors->get_error_code() ) {
		return $errors;
	}


	$blogname = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	$subject = sprintf ( __( '[%1$s] Contact from %2$s', APP_TD ), $blogname, $from_name );
	$send_to = get_option( 'admin_email' );

	$ltd = date( "l, F jS, Y \\a\\t g:i a", time() );
	$ip = getenv( 'REMOTE_ADDR' );
	$hr = getenv( 'HTTP_REFERER' );
	$hst = gethostbyaddr( $_SERVER['REMOTE_ADDR'] );
	$ua = $_SERVER['HTTP_USER_AGENT'];


	$content = html( 'p', __( 'Hello,', APP_TD ) ) . PHP_EOL;
	$content .= html( 'p', sprintf( __( 'You are being contacted via %1$s by %2$s.', APP_TD ), $blogname, $from_name ) ) . PHP_EOL;
	$content .= html( 'p', sprintf( __( '%s has provided the following information so you may contact them:', APP_TD ), $from_name ) ) . PHP_EOL;
	$content .= html( 'p',
		sprintf( __( 'Email: %s', APP_TD ), $from_email ) . '<br />' .
		sprintf( __( 'Message: %s', APP_TD ), '<br />' . '"' . wordwrap( nl2br( $posted_message ), 70 ) . '"' )
	) . PHP_EOL;
	$content .= html( 'p',
		__( '-----------------', APP_TD ) . '<br />' .
		__( 'Other Data and Information:', APP_TD ) 
	) . PHP_EOL;
	$content .= html( 'p',
		sprintf( __( 'IP Address: %s', APP_TD ), $ip ) . '<br />' .
		sprintf( __( 'Time Stamp: %s', APP_TD ), $ltd ) . '<br />' .
		sprintf( __( 'Referrer: %s', APP_TD ), $hr ) . '<br />' .
		sprintf( __( 'Host: %s', APP_TD ), $hst ) . '<br />' .
		sprintf( __( 'User Agent: %s', APP_TD ), $ua )
	) . PHP_EOL;


	$email = array( 'to' => $send_to, 'subject' => $subject, 'message' => $content, 'from' => $from_email, 'from_name' => $from_name );
	$email = apply_filters( 'jr_email_contact_form', $email, $posted );

	APP_Mail_From::apply_once( array( 'reply_email' => $email['from'], 'reply_name' => $email['from_name'], 'reply' => true ) );
	appthemes_send_email( $email['to'], $email['subject'], $email['message'] );

	return true;
}


/**
 * Send the job alerts email.
 */
function jr_job_alerts_send_email( $user_id, $jobs ) {
	global $jr_log, $jr_options;

	$subscriber = get_userdata($user_id);

	if ( ! $subscriber ) {
		$jr_log->write_log("User ID #'{$user_id}' not found!");
		return false;
	}

	$user_login = stripslashes( $subscriber->user_login );
	$user_email = stripslashes( $subscriber->user_email );

	$siteurl = trailingslashit( home_url() );
	$dashurl = trailingslashit( get_permalink( JR_Dashboard_Page::get_id() ) );

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text area of emails.
	$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	$content_type = $jr_options->jr_job_alerts_email_type;

	//email sent to new user starts here
	$from_name = strip_tags( $jr_options->jr_job_alerts_from_name );
	$from_email = strip_tags( $jr_options->jr_job_alerts_from_email );

	// assemble the header
	$headers = "From: $from_name <$from_email>" . PHP_EOL;
	$headers .= "Reply-To: $from_name <$from_email>" . PHP_EOL;
	$headers .= "Content-Type: " . $content_type . PHP_EOL;

	// check if user is using an alert html template
	$template = $jr_options->jr_job_alerts_email_template;
	if ( $template != 'standard' ) {
		$job_body_template = jr_job_alerts_read_template( $template );
	}

	// fallback to default template if the template	fails to load
	if ( empty( $job_body_template ) ) {
		$job_body_template = $jr_options->jr_job_alerts_job_body;
	}

	// template clean
	$job_body_template = stripslashes( $job_body_template );
	$job_body_template = preg_replace( '/<script\b[^>]*>(.*?)<\/script>/is', '', $job_body_template );

	// change double line-breaks in the text into HTML paragraphs for standard templates
	if ( $template == 'standard' ) {
		$job_body_template = wpautop( $job_body_template );
	}

	// check if user is using dynamic job details length
	$jobdetails_length = explode( '_', $job_body_template );
	if ( ! empty( $jobdetails_length[1] ) ) {

		$jobdetails_length = (int) $jobdetails_length[1];
		$dynamic_find_replace = array(
			'jobdetails_find' => '/%jobdetails_' . $jobdetails_length . '%/i',
			'jobdetails_length' => $jobdetails_length,
		);

	} else {
		$dynamic_find_replace['jobdetails_find'] = '/%jobdetails%/i';
	}

	$job_body = '';

	foreach ( $jobs as $job ) {

		$job_info = get_post( $job );

		// check if the job exists
		if ( $job_info ) {

			// get the find/replace valus for the current job
			$job_find_replace = jr_job_alerts_joblist_find_replace( $job_info, $dynamic_find_replace );

			// allow changing the job list body using a filter
			$job_body_template = apply_filters( 'jr_job_alerts_format_joblist', $job_body_template, $job_info );

			$job_body .= preg_replace( array_keys( $job_find_replace ), $job_find_replace, $job_body_template );
		}

	}

	// store the last job title to allow using it on the email subject - useful for single job emails
	$job_title = $job_info->post_title;

	// if the job body is null return true to consider email as sent and delete it from the list
	if ( ! $job_body ) {
		return true;
	}

	if ( $content_type == 'text/html' ) {
		$job_body = sprintf( '<html><body>%s</body></html>', $job_body );
	}

	// variables that can be used by admin to dynamically fill in email content
	$find = array( '/%username%/i', '/%joblist%/i', '/%jobtitle%/i', '/%blogname%/i', '/%siteurl%/i', '/%loginurl%/i', '/%useremail%/i', '/%dashboardurl%/i' );
	$replace = array( $user_login, $job_body, $job_title, get_option( 'blogname' ), get_option( 'siteurl' ), get_option( 'siteurl' ) . '/wp-login.php', $user_email, $dashurl );

	// search and replace any user added variable fields in the subject line
	$subject = stripslashes( $jr_options->jr_job_alerts_email_subject );
	$subject = preg_replace( $find, $replace, $subject );
	$subject = preg_replace( "/%.*%/", "", $subject );

	// search and replace any user added variable fields in the body
	$message = stripslashes( $jr_options->jr_job_alerts_email_body );
	$message = preg_replace( $find, $replace, $message );
	$message = preg_replace( "/%.*%/", "", $message );

	if ( $content_type != 'text/html' ) {
		$message = wp_strip_all_tags( $message );
	}

	// ok let's send the new user an email
	$result = wp_mail( $user_email, $subject, $message, $headers );

	if ( ! $result ) {
		$jr_log->write_log( "Job alert error sending email to '$user_login' ('{$user_email}') / subject: '{$subject}' / message: '{$message}' / headers: '{$headers}'" );
	}

	return $result;
}

// returns the job alerts job list find/replace array
function jr_job_alerts_joblist_find_replace( $job_info, $dynamic_fr ) {

	### truncate the job details if needed
	if ( !empty($dynamic_fr['jobdetails_length']) ) {

		$jobdetails_length =  $dynamic_fr['jobdetails_length'];
		$jobdetails_replace = substr($job_info->post_content, 0, $jobdetails_length);
		$pos = strrpos($jobdetails_replace, " ");
		if ($pos > 0) {
			$jobdetails_replace = substr($jobdetails_replace, 0, $pos) . '...';
		}

	} else {

		$jobdetails_length = strlen($job_info->post_content);
		$jobdetails_replace = $job_info->post_content;

	}

	### taxonomies
	$jobtype_replace = '';
	$jobtypes = get_the_terms($job_info->ID, APP_TAX_TYPE);
	if ($jobtypes) foreach ($jobtypes as $jobtype) {
		if ($jobtype_replace) $jobtype_replace .= ',';
		$jobtype_replace .=  $jobtype->name;
	}

	$jobcat_replace = '';
	$jobcats = get_the_terms($job_info->ID, APP_TAX_CAT);
	if ($jobcats) foreach ($jobcats as $jobcat) {
		if ($jobcat_replace) $jobcat_replace .= ',';
		$jobcat_replace .=  $jobcat->name;
	}

	### location
	$address = get_post_meta($job_info->ID, 'geo_short_address', true);
	if ( !$address ) $address = __( 'Anywhere', APP_TD );

	$thumb = wp_get_attachment_image_src( get_post_thumbnail_id($job_info->ID), 'thumbnail');
	if ( !empty( $thumb[0] ) ) $thumb = $thumb[0];
	else $thumb = '';

	$job_find_replace = array (
		'/%jobtitle%/i' 				=> $job_info->post_title,
		'/%jobtime%/i' 					=> $job_info->post_date,
		$dynamic_fr['jobdetails_find']  => $jobdetails_replace,
		'/%company%/i'  				=> get_post_meta( $job_info->ID, '_Company', true ),
		'/%location%/i'  				=> $address,
		'/%jobtype%/i' 					=> $jobtype_replace,
		'/%jobcat%/i' 					=> $jobcat_replace,
		'/%author%/i' 					=> get_the_author_meta( 'user_login', $job_info->post_author ),
		'/%permalink%/i'				=> esc_url( get_permalink( $job_info->ID ) ),
		'/%thumbnail%/i'				=> wp_get_attachment_image( $job_info->ID ),
		'/%thumbnail_url%/i'			=> $thumb,
	);

	// allow changing the find/replace values using a filter
	$job_find_replace = apply_filters( 'jr_job_alerts_joblist_find_replace', $job_find_replace, $job_info );

	return $job_find_replace;
}

// set the generic email header for emails not using 'appthemes_send_email()'
function _jr_email_headers( $headers ='' ) {
	// Strip 'www.' from URL
	$domain = preg_replace( '#^www\.#', '', strtolower( $_SERVER['SERVER_NAME'] ) );

	if ( ! $headers ) $headers = sprintf( 'From: %1$s <%2$s', get_bloginfo( 'name' ), "wordpress@$domain" ) . PHP_EOL;
	return apply_filters( 'jr_email_headers', $headers ) ;
}

/**
* Returns the email signature
* @param string $identifier The signature identifier (each email has a string identifier)
* @param string $content_type: 'html' or 'plain'
*
* @return string The filterable email signature
*/
function jr_email_signature( $identifier, $content_type = 'plain' ) {

	$text = __( 'Regards,', APP_TD );
	$sitename = get_bloginfo('name');

	if ( 'html' == $content_type ) {
		$signature = html( 'p', sprintf( '%s', $text ) );
		$signature .= html( 'p', sprintf( '%s', $sitename ) );
		$signature .= html( 'p', home_url() );
	} else {
		$signature = $text . PHP_EOL . PHP_EOL;
		$signature .= sprintf( '%s', $sitename ) . PHP_EOL;
		$signature .= home_url();
	}
	return apply_filters( 'jr_email_signature', $signature, $identifier, $content_type );
}