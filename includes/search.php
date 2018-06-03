<?php
/**
 * Search related code.
 *
 * @version 1.8
 * @author AppThemes
 * @package JobRoller\Search
 * @copyright 2010 all rights reserved
 *
 */


add_filter( 'get_search_query', 'jr_search_query' );

if ( ! is_admin() ) {
	// search on custom fields
	add_filter( 'posts_join', 'custom_search_join' );
	add_filter( 'posts_where', 'custom_search_where' );
	add_filter( 'posts_groupby', 'custom_search_groupby' );
}


### Hook Callbacks

/**
 * Search on custom fields.
 */
function custom_search_join( $join ) {
	global $wpdb;

	if ( is_search() && get_search_query() ) {
		$join = " LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id ";
	}
	return $join;
}

/**
 * Search on custom fields.
 */
function custom_search_groupby( $groupby ) {
	global $wpdb;

	if ( is_search() && get_search_query() ) {
		$groupby = " $wpdb->posts.ID ";
	}
	return($groupby);
}

/**
 * Search on custom fields.
 */
function custom_search_where( $where ) {
	global $wpdb;

	$old_where = $where;

	if ( is_admin() || !is_search() || !get_search_query() || ( get_query_var( 'post_type' ) != APP_POST_TYPE && get_query_var( 'post_type' ) != APP_POST_TYPE_RESUME ) ) {
		return $where;
	}

	$query = $searchand = '';

	$criteria = $where;
	$n = '%';

	// search jobs
	if ( APP_POST_TYPE == get_query_var( 'post_type' ) ) {

		// additional custom fields for search results
		$custom_fields = array(
			'_Company',
		);
		$custom_fields = apply_filters( 'jr_custom_field_search', $custom_fields, APP_POST_TYPE );

		// search resumes
	} else {

		// add additional custom fields here to include them in search results
		$custom_fields = array(
			'_desired_position',
			'_experience',
			'_education',
			'_skills',
			'_desired_salary',
		);
		$custom_fields = apply_filters( 'jr_custom_field_search', $custom_fields, APP_POST_TYPE_RESUME );
	}

	$search_terms = get_query_var( 'search_terms' );

	foreach ( (array) $search_terms as $term ) {
		$term = addslashes_gpc( $term );

		$query .= "{$searchand}(";
		$query .= "($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
		$query .= " OR ($wpdb->posts.post_content LIKE '{$n}{$term}{$n}')";

		$post_search = "($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
		$post_search .= " OR ($wpdb->posts.post_content LIKE '{$n}{$term}{$n}')";

		// remove post content/title search from the query main criteria since we're searching them with additional custom fields
		$criteria = str_replace( $post_search, '1=1', $criteria );

		foreach ( $custom_fields as $custom_field ) {
			$query .= " OR (";
			$query .= "($wpdb->postmeta.meta_key = '$custom_field')";
			$query .= " AND ($wpdb->postmeta.meta_value  LIKE '{$n}{$term}{$n}')";
			$query .= ")";
		}
		$query .= ")";
		$searchand = ' AND ';
	}

	if ( ! empty( $query ) ) {
		$where = " AND ({$query}) {$criteria}";
	}

	return $where;
}

/**
 * Sanitize search keywords.
 */
function jr_search_query( $query ) {
	if ( empty( $query ) && !empty( $_GET['s'] ) ) {
		$query = wp_strip_all_tags( $_GET['s'] );
	}
	return $query;
}