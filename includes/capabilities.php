<?php
/**
 * Hooks and functions that control and add/extend roles and capabilities.
 */

add_filter( 'map_meta_cap', '_jr_job_map_capabilities', 15, 4 );
add_filter( 'map_meta_cap', '_jr_resume_map_capabilities', 15, 4 );
add_filter( 'map_meta_cap', '_jr_post_map_capabilities', 15, 4 );

/**
 * Meta cababilities for jobs.
 */
function _jr_job_map_capabilities( $caps, $cap, $user_id, $args ) {
	global $jr_options;

	switch( $cap ) {

		case 'edit_job':
			$caps = array( 'exist' );

			if ( current_user_can('manage_options') ) {
				break;
			}

			$post = get_post( $args[0] );

			// do not allow if not author or editing is disabled
			if ( ! $post || $post->post_author != $user_id ) {
				$caps[] = 'do_not_allow';
				break;
			}
			break;

		case 'relist_job':
			$caps = array( 'exist' );

			if ( current_user_can('manage_options') ) {
				break;
			}

			$post = get_post( $args[0] );

			// do not allow if not author or job is not expired
			if ( ! $post || $post->post_author != $user_id ) {
				$caps[] = 'do_not_allow';
				break;
			}

			// security check to make sure it's a valid relisting
			if ( 'expired' !== $post->post_status && ! get_post_meta( $post->ID, '_relisting', true ) ) {
				$caps[] = 'do_not_allow';
				break;
			}

			break;

		case 'apply_to_job':
			$caps = array( 'exist' );

			// don't allow if user is not logged in and setting do not allow visitors to apply
			if ( ! is_user_logged_in() && $jr_options->apply_reg_users_only ) {
				$caps[] = 'do_not_allow';
				break;
			}

			$post = get_post( $args[0] );

			// don't allow job author to apply to own job
			if ( ! $post || $post->post_author == $user_id ) {
				$caps[] = 'do_not_allow';
				break;
			}

			// Don't allow if job is not published.
			if ( 'publish' !== $post->post_status ) {
				$caps[] = 'do_not_allow';
				break;
			}

			break;

		case 'star_job':
			$caps = array( 'exist' );

			// don't allow if user is not logged in or is not a job seeker
			if ( ! is_user_logged_in() || ! current_user_can('can_submit_resume') ) {
				$caps[] = 'do_not_allow';
				break;
			}
			break;

		case 'link_resumes':
			$caps = array( 'exist' );

			// don't allow if user is not logged in or is not a job seeker
			if ( ! is_user_logged_in() || ! current_user_can( 'can_submit_resume' )  ) {
				$caps[] = 'do_not_allow';
				break;
			}

			$resumes = get_the_jr_user_online_resumes( $user_id );
			// don't allow if user does not have published online resumes
			if ( ! $resumes->post_count ) {
				$caps[] = 'do_not_allow';
				break;
			}
		break;

	}
	return $caps;
}

/**
 * Meta cababilities for resumes.
 */
function _jr_resume_map_capabilities( $caps, $cap, $user_id, $args ) {

	switch( $cap ) {

		case 'view_resume':
			$caps = array( 'exist' );

			if ( current_user_can('manage_options') ) {
				break;
			}

			$post = get_post( $args[0] );

			// don't allow if resumes are not visibile or current user not resume author
			if ( ! jr_resume_is_visible('single') && ( ! $post || $post->post_author != $user_id ) ) {
				$caps[] = 'do_not_allow';
				break;
			}
			break;

		case 'edit_resume':
			$caps = array( 'exist' );

			if ( current_user_can('manage_options') ) {
				break;
			}

			$post = get_post( $args[0] );

			// don't allow if resumes are not visibile or current user not resume author
			if ( ! $post || $post->post_author != $user_id  ) {
				$caps[] = 'do_not_allow';
				break;
			}
		break;

	}
	return $caps;
}

/**
 * Generic meta cababilities applied to any post type.
 *
 * @since 1.8.1
 */
function _jr_post_map_capabilities( $caps, $cap, $user_id, $args ) {

	switch( $cap ) {

		case 'access_post':
			$caps = array( 'exist' );

			// always allow access to admins
			if ( current_user_can('manage_options') ) {
				break;
			}

			$post = get_post( $args[0] );

			// don't allow access for the current user if he's not the post author
			if ( ! $post || $post->post_author != $user_id ) {
				$caps[] = 'do_not_allow';
				break;
			}
		break;

	}
	return $caps;
}
