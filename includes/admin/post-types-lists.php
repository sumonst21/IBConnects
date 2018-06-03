<?php
/**
 * Admin post types customizations.
 *
 * @version 1.2
 * @author AppThemes
 * @package JobRoller\Admin\Post-Types\List
 * @copyright 2010 all rights reserved
 *
 */

//Display the custom column data for each user
add_action( 'manage_users_columns', 'jr_manage_users_columns' );
add_action( 'manage_users_custom_column', 'jr_manage_users_custom_column', 10, 3 );

add_filter( 'manage_post_posts_columns', 'jr_post_thumbnail_column' );
add_action( 'manage_posts_custom_column', 'jr_custom_thumbnail_column' );

add_action( 'manage_' . APP_POST_TYPE . '_posts_custom_column', 'jr_jobs_custom_columns' );
add_filter( 'manage_edit-' . APP_POST_TYPE . '_sortable_columns', 'jr_listing_columns_sort' );
add_filter( 'manage_edit-' . APP_POST_TYPE . '_columns', 'jr_edit_jobs_columns' );

add_action( 'manage_' . APP_POST_TYPE_RESUME . '_posts_custom_column', 'jr_resumes_custom_columns' );
add_filter( 'manage_edit-' . APP_POST_TYPE_RESUME . '_columns', 'jr_edit_resumes_columns' );
add_filter( 'manage_edit-' . APP_POST_TYPE_RESUME . '_sortable_columns', 'jr_resumes_columns_sort' );

add_filter( 'pre_get_posts', 'jr_orderby_addon' );
add_filter( 'pre_get_posts', 'jr_orderby_expiration' );
add_filter( 'posts_orderby', 'jr_orderby_expiration_sql', 10, 2 );

### Hooks Callbacks

/**
 * Display the coumn values for each user.
 */
function jr_manage_users_custom_column( $r, $column_name, $user_id ) {

	// count the total jobs for the user
	if ( 'jr_jobs_count' == $column_name ) {
		global $jobs_counts;

		if ( ! isset( $jobs_counts ) ) {
			$jobs_counts = jr_count_custom_post_types( APP_POST_TYPE );
		}

		if ( ! array_key_exists( $user_id, $jobs_counts ) ) {
			$jobs_counts = jr_count_custom_post_types( APP_POST_TYPE );
		}

		if ( $jobs_counts[$user_id] > 0 ) {
			$r .= "<a href='edit.php?post_type=" . APP_POST_TYPE . "&author=$user_id' title='" . esc_attr__( 'View jobs by this author', APP_TD ) . "' class='edit'>";
			$r .= $jobs_counts[$user_id];
			$r .= '</a>';
		} else {
			$r .= 0;
		}

	}

	// count the total resumes for the user
	if ( 'jr_resumes_count' == $column_name ) {
		global $resumes_counts;

		if ( ! isset( $resumes_counts ) ) {
			$resumes_counts = jr_count_custom_post_types( APP_POST_TYPE_RESUME );
		}

		if ( ! array_key_exists( $user_id, $resumes_counts ) ) {
			$resumes_counts = jr_count_custom_post_types( APP_POST_TYPE_RESUME );
		}

		if ( $resumes_counts[$user_id] > 0 ) {
			$r .= "<a href='edit.php?post_type=" . APP_POST_TYPE_RESUME . "&author=$user_id' title='" . esc_attr__( 'View resumes by this author', APP_TD ) . "' class='edit'>";
			$r .= $resumes_counts[$user_id];
			$r .= '</a>';
		} else {
			$r .= 0;
		}
	}

	// get the user last login date
	if ('last_login' == $column_name) {
		$r = get_user_meta($user_id, 'last_login', true);
	}

	// get the user registration date
	if ('registered' == $column_name) {
		$user_info = get_userdata($user_id);
		$r = $user_info->user_registered;
	}

	if ('jr_resume_subscription' == $column_name) {

		$status = (int) get_user_meta($user_id, '_valid_resume_subscription', true);
		if ( $status > 0 ) {
			$r = __( 'Yes', APP_TD );
		} else {
			$r = '&ndash;';
		}

	}

	return $r;
}

/**
 * Add a thumbnail column to the edit posts screen.
 */
function jr_post_thumbnail_column( $cols ) {
	$cols['thumbnail'] = __('Thumbnail', APP_TD);
	return $cols;
}

function jr_custom_thumbnail_column( $column ){
	global $post;
	$custom = get_post_custom();
	switch ($column) {
		case 'thumbnail' :
			if (has_post_thumbnail($post->ID))
				echo get_the_post_thumbnail($post->ID, 'sidebar-thumbnail');
			break;
	}
}

function jr_listing_columns_sort( $columns ) {
	$columns['expire_date'] = 'expire_date';
	$columns['addon'] = 'addon';
	return $columns;
}

function jr_jobs_custom_columns( $column ) {
	global $post;

	$custom = get_post_custom();
	switch ( $column ) {
		case 'company':
			if ( isset( $custom['_Company'][0] ) && !empty( $custom['_Company'][0] ) )  {
				if ( isset( $custom['_CompanyURL'][0] ) && !empty( $custom['_CompanyURL'][0] ) ) {
					echo '<a href="' . $custom['_CompanyURL'][0] . '">' . $custom['_Company'][0] . '</a>';
				} else {
					echo $custom['_Company'][0];
				}
			}
			break;

		case 'location':
			if ( isset( $custom['geo_address'][0] ) && !empty( $custom['geo_address'][0] ) ) {
				echo $custom['geo_address'][0];
			} else {
				_e( 'Anywhere', APP_TD );
			}
			break;

		case 'addon':
			$addons = get_the_jr_job_addons( $post->ID );
			if ( ! empty( $addons ) ) {
				echo implode( ', ', $addons );
			} else {
				echo '-';
			}
			break;

		case APP_TAX_TYPE :
		case APP_TAX_SALARY :
		case APP_TAX_CAT :
			echo get_the_term_list( $post->ID, $column, '', ', ', '' );
			break;

		case 'expire_date' :
			$expiration_date = jr_get_job_expiration_date( $post->ID );
			if ( $expiration_date ) {

				echo $expiration_date;
				if ( jr_check_expired( $post ) ) {
					echo html( 'p', array( 'class' => 'admin-job-expired' ), __( 'Expired', APP_TD ) );
				}

			} else {
				echo __( 'Endless', APP_TD );
			}
			break;

		case 'logo':
			if ( has_post_thumbnail( $post->ID ) ) {
				echo get_the_post_thumbnail( $post->ID, 'sidebar-thumbnail' );
			}
			break;
	}
}

function jr_edit_jobs_columns( $columns ){
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __('Job Name', APP_TD),
		'author' => __('Job Author', APP_TD),
		'job_cat' => __('Job Category', APP_TD),
		'job_type' => __('Job Type', APP_TD),
		'job_salary' => __('Salary', APP_TD),
		'company' => __('Company', APP_TD),
		'location' => __('Location', APP_TD),
		'addon' => __('Addon(s)', APP_TD),
		'expire_date' => __('Expire Date', APP_TD),
		'date' => __('Date', APP_TD),
		'logo' => __('Logo', APP_TD),
	);
	return $columns;
}

function jr_resumes_custom_columns( $column ){
	global $post;

	$custom = get_post_custom();
	switch ( $column ) {
		case 'location':
			if ( isset( $custom['geo_address'][0] ) && !empty( $custom['geo_address'][0] ) ) {
				echo $custom['geo_address'][0];
			} else {
				_e( 'Anywhere', APP_TD );
			}
			break;

		case APP_TAX_RESUME_JOB_TYPE :
		case APP_TAX_RESUME_CATEGORY :
		case APP_TAX_RESUME_SPECIALITIES :
		case APP_TAX_RESUME_LANGUAGES :
			echo get_the_term_list( $post->ID, $column, '', ', ', '' );
			break;

		case 'logo':
			if ( has_post_thumbnail( $post->ID ) ) {
				echo get_the_post_thumbnail( $post->ID, 'sidebar-thumbnail' );
			}
			break;
	}
}

// custom user page columns
function jr_manage_users_columns( $columns ) {
	$columns['jr_jobs_count'] = __('Jobs', APP_TD);
	$columns['jr_resumes_count'] = __('Resumes', APP_TD);
	$columns['jr_resume_subscription'] = __('Resume Subscription', APP_TD);
	$columns['last_login'] = __('Last Login', APP_TD);
	$columns['registered'] = __('Registered', APP_TD);
	return $columns;
}

function jr_edit_resumes_columns( $columns ){

	foreach ( $columns as $key => $column ) {
		if ( 'date' == $key ) {
			$columns_reorder[APP_TAX_RESUME_JOB_TYPE] = __( 'Job Types', APP_TD );
			$columns_reorder[APP_TAX_RESUME_CATEGORY] = __( 'Job Categories', APP_TD );
			$columns_reorder[APP_TAX_RESUME_SPECIALITIES] = __( 'Job Specialties', APP_TD );
			$columns_reorder[APP_TAX_RESUME_LANGUAGES] = __( 'Spoken Languages', APP_TD );
			$columns_reorder['location'] = __( 'Location', APP_TD );
		}
		$columns_reorder[$key] = $column;
	}

	$columns_reorder['thumbnail'] = __( 'Photo', APP_TD );

	return $columns_reorder;
}

function jr_resumes_columns_sort( $columns ) {
	$columns[APP_TAX_RESUME_JOB_TYPE] = APP_TAX_RESUME_JOB_TYPE;
	$columns[APP_TAX_RESUME_CATEGORY] = APP_TAX_RESUME_CATEGORY;
	$columns[APP_TAX_RESUME_SPECIALITIES] = APP_TAX_RESUME_SPECIALITIES;
	$columns[APP_TAX_RESUME_LANGUAGES] = APP_TAX_RESUME_LANGUAGES;

	return $columns;
}


### Helper Functions

/**
 * Count the number of job listings & resumes for the user
 */
function jr_count_custom_post_types( $post_type ) {
	global $wpdb, $wp_list_table;

	$users = array_keys( $wp_list_table->items );
	$userlist = implode( ',', $users );

	$result = $wpdb->get_results( "SELECT post_author, COUNT(*) FROM $wpdb->posts WHERE post_type = '$post_type' AND post_author IN ($userlist) GROUP BY post_author", ARRAY_N );
	foreach ( $result as $row ) {
		$count[ $row[0] ] = $row[1];
	}

	foreach ( $users as $id ) {
		if ( ! isset( $count[ $id ] ) ) {
			$count[ $id ] = 0;
		}
	}

	return $count;
}


function jr_orderby_addon( $query ) {

	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( 'addon' == $query->get('orderby') ) {

		foreach( _jr_featured_addons() as $addon ) {
			$meta_query[] = array(
				'key'		=> $addon,
				'value'		=> '',
				'compare'	=> '!=',
			);
		}

		$meta_query['relation'] = 'OR';

		$args = array(
			'post_type'		=> APP_POST_TYPE,
			'post_status'	=> 'any',
			'fields'		=> 'ids',
			'nopaging'		=> true,
			'meta_query'	=> $meta_query,
			'order'			=> $query->get('order'),
		);

		// featured posts
		$posts_feat = new WP_Query( $args );
		$posts_feat = $posts_feat->posts;

		$meta_query_not_exists[] = array(
			'key'		=> $addon,
			'value'		=> '',
			'compare'	=> '=',
		);

		foreach( _jr_featured_addons() as $addon ) {
			$meta_query_not_exists[] = array(
				'key'		=> $addon,
				'compare'	=> 'NOT EXISTS',
			);
		}

		$meta_query_not_exists['relation'] = 'OR';
		$args['meta_query'] = $meta_query_not_exists;

		// Non-featured posts
		$posts_not_feat = new WP_Query( $args );
		$posts_not_feat = $posts_not_feat->posts;

		if ( 'DESC' == strtoupper( $query->get('order') ) ) {
			$posts = array_merge( $posts_feat, $posts_not_feat );
		} else {
			$posts = array_merge( $posts_not_feat, $posts_feat );
		}

		$query->set( 'post__in', $posts );
		$query->set( 'orderby', 'post__in' );
	}
	return $query;
}

/**
 * Order posts by expiration date.
 */
function jr_orderby_expiration( $query ) {

	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( 'expire_date' == $query->get('orderby') ) {
		$query->set( 'meta_key', JR_JOB_DURATION_META );
		$query->set( 'orderby', 'meta_value' );
		$query->set( 'orderby_expire_date', true );
	}

	return $query;
}

/**
 * Hook into the main 'orderby' SQL to order posts by expiration date.
 */
function jr_orderby_expiration_sql( $orderby, $query ) {

	if ( ! $query->get('orderby_expire_date') ) {
		return $orderby;
	}

	$orderby = " DATE_ADD( post_date, INTERVAL IF( meta_value = '' or meta_value = null, 9999, meta_value) DAY ) " . $query->get('order');

	return $orderby;
}