<?php
/**
 * Helper functions used through out the theme.
 *
 * @version 1.8
 * @author AppThemes
 * @package JobRoller\Helper
 * @copyright 2010 all rights reserved
 */


### Functions

/**
 * Retrieve the number of jobs per page defined in the admin options page.
 */
function jr_get_jobs_per_page() {
	global $jr_options;

	if ( is_home() ) {
		$option = 'jobs_frontpage';
	} else  {
		$option = 'jr_jobs_per_page';
	}

	return (int) $jr_options->$option;
}

/**
 * Checks if the 'Relist' option is enabled as set in the admin options page.
 */
function jr_allow_relist() {
	global $jr_options;
	return (bool) $jr_options->jr_allow_relist;
}

/**
 * Checks if the 'Edit' option is enabled as set in the admin options page.
 */
function jr_allow_editing() {
	global $jr_options;
	return (bool) $jr_options->jr_allow_editing;
}

/**
 * Resets an multi-dimensional array of single values and returns a one dimensional array.
 *
 * @param array $data The data array
 *
 * @return array A one dimensional array
 */
function jr_reset_data( $data ) {

	$new_data = array();
	foreach ( $data as $key => $value ) {
		$new_data[$key] = reset( $value );
	}

	return $new_data;
}

/**
 * Prefixes any given field.
 */
function jr_prefix_field( $field, $prefix = '' ) {
	global $app_abbr;

	if ( ! $prefix ) {
		$prefix = $app_abbr;
	}

	if ( is_array( $field ) ) {
		$key = key( $field );
		$field_prefixed[$app_abbr . '_' . $key] = $field;
		return $field_prefixed;
	} else {
		return $app_abbr . '_' . $field;
	}

}

function _jr_prefix_fields( $fields ) {
	foreach ( $fields as $key => $field ) {
		if ( 'title' != $field['name'] ) {
			$field['name'] = JR_FIELD_PREFIX . $field['name'];
		}
		$prefixed_fields[] = $field;
	}
	return $prefixed_fields;
}

function _jr_unprefix_fields( $fields ) {
	foreach ( $fields as $field => $value ) {
		$unprefixed_fields[str_replace( JR_FIELD_PREFIX, '', $field )] = $value;
	}
	return $unprefixed_fields;
}

function jr_no_access_permission( $message = '' ) {
	$login = '';

	if ( ! is_user_logged_in() ) {
		$login = sprintf( __( ' Please <a href="%s">login or register</a>.', APP_TD ), wp_login_url( get_permalink() ) );
	}
	echo html( 'p', $message . $login );

	echo html( 'a', array( 'href' => home_url() ), __( '&nbsp;&larr; Return to main page', APP_TD ) );
}

/**
 * Outputs pagination.
 */
if ( ! function_exists( 'jr_paging' ) ) {
function jr_paging( $query = null, $query_var = 'paged', $args = array() ) {
	global $wp_query;

	if ( ! $query ) {
		$query = $wp_query;
	}

	if ( $query->max_num_pages > 1 ) {
		appthemes_load_template( 'pagination.php', compact( 'query', 'query_var', 'args' ) );
	}
}
}


### Jobs Base URL

/**
 * Contextually retrieves the base URL for projects listings.
 */
function get_the_jr_jobs_base_url() {

	if ( is_tax( apply_filters( 'jr_job_filter_taxonomies', array( APP_TAX_CAT, APP_TAX_SALARY, APP_TAX_TYPE ) ) ) ) {
		return get_term_link( get_queried_object() );
	}

	if ( is_date() ) {
		return add_query_arg( array( 'jobs_by_date' => 1, 'time' => ! empty( $_GET['time'] ) ? $_GET['time'] : '' ), get_permalink( JR_Date_Archive_Page::get_id() ) );
	}

	return get_post_type_archive_link( APP_POST_TYPE );
}


/**
 * Returns an array of settings for WP Editor used on the frontend.
 *
 * @since 1.7.3
 *
 * @return array An array of WP Editor settings.
 */
function jr_get_editor_settings( $settings = array() ) {
	$defaults = array(
		'wpautop' => true,
		'media_buttons' => false,
		'textarea_rows' => 10,
		'editor_class' => '',
		'teeny' => false,
		'dfw' => true,
		'tinymce' => true,
		'quicktags' => array(
			'buttons' => 'strong,em,ul,ol,li,link,close',
		),
	);

	$settings = wp_parse_args( $settings, $defaults );

	return $settings;
}

/**
 * Redirects a user to my jobs.
 */
if ( ! function_exists( 'redirect_myjobs' ) ) {
function redirect_myjobs( $query_string = '' ) {
	$url = get_permalink( JR_Dashboard_Page::get_id() );
	if ( is_array( $query_string ) ) {
		$url = esc_url_raw( add_query_arg( $query_string, $url ) );
	}
	wp_redirect( $url );
	exit();
}
}

/**
 * Redirects a user to my profile.
 */
if ( ! function_exists( 'redirect_profile' ) ) {
function redirect_profile( $query_string = '' ) {
	$url = get_permalink( JR_User_Profile_Page::get_id() );
	if ( is_array( $query_string ) ) {
		$url = esc_url_raw( add_query_arg( $query_string, $url ) );
	}
	wp_redirect( $url );
	exit();
}
}

// Output errors
if ( ! function_exists( 'jr_show_errors' ) ) {
function jr_show_errors( $errors, $id = 0 ) {
	if ( $errors && sizeof( $errors ) > 0 && $errors->get_error_code() ) {
		appthemes_display_notice( 'error', $errors );
	}
}
}

/**
 * Returns the translated month.
 */
function jr_translate_months( $month ) {

	$translated_months = array(
		'january'	=> __( 'January', APP_TD ),
		'february'	=> __( 'February', APP_TD ),
		'march'		=> __( 'March', APP_TD ),
		'april'		=> __( 'April', APP_TD ),
		'may'		=> __( 'May', APP_TD ),
		'june'		=> __( 'June', APP_TD ),
		'july'		=> __( 'July', APP_TD ),
		'august'	=> __( 'August', APP_TD ),
		'september' => __( 'September', APP_TD ),
		'october'	=> __( 'October', APP_TD ),
		'november'	=> __( 'November', APP_TD ),
		'december'	=> __( 'December', APP_TD ),
	);

	return $translated_months[ strtolower( trim( $month ) ) ];
}


### Frontent User Dashboard.

/**
 * Sets or/and retrieves the current tab for pagination.
 */
function jr_dashboard_curr_page_tab( $new_tab = '' ) {
	static $tab;

	if ( $new_tab ) {
		$tab = $new_tab;
	}
	return $tab;
}

/**
 * Applies filters necessary for 'wp-pagenavi' pagination to work properly on the dashboard tabs.
 */
function jr_wp_pagenavi_tab_pagination( $args ) {

	if ( empty( $args['add_args']['tab'] ) ) {
		return;
	}

	jr_dashboard_curr_page_tab( $args['add_args'] );

	add_filter( 'get_pagenum_link', 'jr_wp_pagenavi_add_tab' );
}

/**
 * Adds a 'tab' query var to the pagination link to allows pagination on the dashboard tabs.
 */
function jr_wp_pagenavi_add_tab( $result ) {
	$tab = jr_dashboard_curr_page_tab();
	return add_query_arg( $tab, $result );
}


### 3d Party Job Feeds

/**
 * Output sponsored listings.
 *
 * @uses apply_filters() Calls 'jr_external_job_attribution'
 */
if ( ! function_exists( 'jr_display_sponsored_results' ) ) {
function jr_display_sponsored_results( $search_results, $params, $is_ajax = false, $page = 1 ) {

	$defaults = array(
		'link_class' => array( 'more_sponsored_results', 'front_page' ),
		'tax' => '',
		'term' => ''
	);
	$params = wp_parse_args( $params, $defaults );

	$alt = 1;
	$first = true;

	if ( ! $is_ajax ) {
		echo sprintf( '<div class="section"><h2 class="pagetitle">%s</h2>', esc_html( $params['title'] ) );
		echo sprintf( '<ol class="jobs sponsored_results" source="%s">', esc_attr( $params['source'] ) );
	}

	foreach ( $search_results as $job ) {

		$job_defaults = array(
			'onmousedown' => '',
		);
		$job = wp_parse_args( $job, $job_defaults );

		$post_class = array( 'job' );
		if ( $alt == 1 ) {
			$post_class[] = 'job-alt';
		}

		// check for the special sponsored job types (i.e: paid, sponsored or organic) and add them as classes
		if ( isset( $job['type'] ) && $job['type'] ) {
			$post_class[] = 'ty_' . strtolower( $params['source'] ) . '_' . $job['type'];
		}

		// check for the additional classes to add
		if ( isset( $job['class'] ) && $job['class'] ) {
			$post_class[] = $job['class'];
		}

		appthemes_load_template( 'external-job-feed-item.php', compact( 'job', 'post_class', 'is_ajax', 'first', 'page'  ) );
	}

	if ( ! $is_ajax ) {

		if ( ! empty( $params['attribution_html'] ) ) {
			$attribution_html = $params['attribution_html'];
		} else {
			$attribution_html = sprintf( '<p class="attribution"><a href="' . esc_url( $params['url'] ) . '">%s</a> %s <a href="' . esc_url( $params['url'] ) . '" title="%s" target="_new"><img src="' . esc_attr( $params['jobs_by_img'] ) . '" alt="' . esc_attr( $params['source'] ) . ' %s" /></a></p>', __( 'Jobs', APP_TD ), __( 'by', APP_TD ), __( 'job search', APP_TD ), __( 'job search', APP_TD ) );
		}

		echo '</ol>
		  <div class="paging sponsored_results_paging">
			<div style="float:left;"><a href="#more" source="' . esc_attr( $params['source'] ) . '" callback="' . esc_attr( $params['callback'] ) . '" class="' . esc_attr( implode( ' ', $params['link_class'] ) ) . '" tax="' . esc_attr( $params['tax'] ) . '" term="' . esc_attr( $params['term'] ) . '" rel="2" >' . __( 'Load More &raquo;', APP_TD ) . '</a></div>'
		. $attribution_html .
		'</div></div>';

		$attribution_html = apply_filters( 'jr_external_job_attribution', $attribution_html, $params );
	}
}
}


### Recaptchas Related

function jr_display_recaptcha( $support ) {

	if ( ! current_theme_supports( $support ) ) {
		return false;
	}

	list( $options ) = get_theme_support( $support );

	if ( ( 'app-recaptcha-register' === $support ) || ( ! empty( $options['display_rule'] ) && ( ( 'visitors' === $options['display_rule'] && ! is_user_logged_in() ) || ( 'all' === $options['display_rule'] ) ) ) ) {
		return true;
	} else {
		return false;
	}

}

function jr_validate_recaptcha( $support ) {

	$errors = new WP_Error();

	// reCaptcha 2.0 check.
	if ( current_theme_supports( $support ) ) {

		// Verify the user response.
		$response = appthemes_recaptcha_verify();

		if ( is_wp_error( $response ) ) {

			foreach ( $response->get_error_codes() as $code ) {
				$errors->add( $code, $response->get_error_message( $code ) );
			}

		}

	}
	return $errors;
}

function jr_is_jobs_front_archive() {
	global $wp_query;

	return ( ! empty( $wp_query->query_vars['is_jobs_frontpage_archive'] ) );
}

/**
 *
 * @since 1.8
 */
function jr_load_paginator( $query, $query_var, $args ) {

	if ( function_exists( 'wp_pagenavi' ) ) {
		$args['query'] = $query;
		$callback = 'wp_pagenavi';
	} else {
		$callback = 'appthemes_pagenavi';
	}

	jr_wp_pagenavi_tab_pagination( $args );

	$callback = apply_filters( 'jr_paginator', $callback );

	call_user_func( $callback,  $query, $query_var, $args );
}

/**
 * @since 1.8
 */
function jr_is_job_filter() {
	return ( ! empty( $_GET['action'] ) && 'Filter' == $_GET['action'] );
}

/**
 * Retrieves the number of columns to use in the footer.
 *
 * @since 1.8
 */
function jr_get_footer_columns() {
	global $jr_options;

	if ( ! $jr_options->multi_column_footer ) {
		return 0;
	}

	return $jr_options->footer_cols ? $jr_options->footer_cols : 3;
}

/**
 * Return list of terms from taxonomy.
 */
function jr_get_tax_terms( $taxonomy ) {

	$args = array(
	  'hide_empty'	=> 0,
	  'taxonomy' 	=> $taxonomy,
	);

	return get_terms( $taxonomy, $args );
}

/**
 * Outputs terms for the job alerts.
 */
function jr_output_alert_terms_items( $terms, $user_options = array() ) {

	$output = '';

	ob_start();

	foreach( $terms as $term ):
?>
		<li>
			<label for="<?php esc_attr_e( $term->slug ) ; ?>">
				<input type="checkbox" name="<?php esc_attr_e( 'alert_' . $term->taxonomy . '[' . $term->slug . ']' ); ?>" id="<?php esc_attr_e( $term->slug ); ?>"
				<?php echo checked( is_array( $user_options ) && in_array( $term->term_id, $user_options ), 1, FALSE ); ?> value="<?php esc_attr_e( $term->term_id ); ?>" /><?php echo $term->name; ?>
			</label>
		</li>
<?php
	endforeach;

	$items_html = ob_get_clean();

	echo html( 'ul', $items_html );
}

/**
 * Retrieve allowed extensions for file uploads.
 *
 * @uses apply_filters Calls 'jr_<field>_allowed_extensions'
 *
 * @since 1.8
 */
function jr_get_allowed_extensions_for( $field, $delimiter = '' ) {

	$allowed = array();

	switch ( $field ) {
		case 'apply_job_photo':

			// Check valid extension
			$allowed = array(
				'png',
				'gif',
				'jpg',
				'jpeg'
			);
			$allowed = apply_filters( 'jr_'.$field.'_allowed_extensions', $allowed );
		break;

		case 'apply_job_cv':
		case 'apply_job_cv_letter':

			$allowed = array(
				'pdf',
				'doc',
				'docx',
				'zip',
				'txt',
				'rtf'
			);
			$allowed = apply_filters( 'jr_'.$field.'_allowed_extensions', $allowed );

		default:
			break;
	}

	if ( $delimiter ) {
		return implode( $delimiter, (array) $allowed );
	}
	return $allowed;
}

// retrieve the term id for a specific taxonomy
function jr_get_listing_tax( $name, $taxonomy, $use_term_id = true ) {

	if ( isset( $_REQUEST[$name] ) && $_REQUEST[$name] != -1 ) {

		$listing_tax = get_term( $_REQUEST[ $name ], $taxonomy );
		if ( $use_term_id ) {
			$term_id = is_wp_error( $listing_tax ) || empty( $listing_tax ) ? false : $listing_tax->term_id;
		} else {
			$term_id = $_REQUEST[ $name ];
		}

	} else {
		$term_id = false;
	}

	return $term_id;
}

/**
 * @since 1.8
 */
function jr_search_bar_active() {
	global $jr_options;
	return $jr_options->jr_show_searchbar;
}

/**
 * @since 1.8
 */
function jr_dropdown_months( $selected = '' ) {

	$options_html = html( 'option value=""', __( 'Month&hellip;', APP_TD ) );

	for( $i = 1; $i <= 12; $i++ ) {
		$month = date( 'F', mktime( 0, 0, 0, $i, 11, 1978 ) );
		$options_html .= html( 'option ' . selected( $selected, $i, false ), array( 'value' => $i ), jr_translate_months( $month ) );
	}

	echo html( 'select name="availability_month" id="availability_month"', $options_html );
}

/**
 * @since 1.8
 */
function jr_job_types_checklist( $checked = '' ) {
	$all_job_types = get_terms( APP_TAX_TYPE, array( 'hide_empty' => '0' ) );

	if ( empty( $all_job_types ) ) {
		return;
	}

	ob_start();

	foreach( $all_job_types as $type ):
?>
		<li>
			<label for="<?php echo esc_attr( $type->slug ); ?>">
				<input type="checkbox" <?php checked( in_array( $type->slug, (array) $checked ) ); ?>  name="prefs_job_types[<?php echo esc_attr( $type->slug ); ?>]" id="<?php echo esc_attr( $type->slug ); ?>" value="show" /><?php echo $type->name; ?>
			</label>
		</li>
<?php
	endforeach;

	$items_html = ob_get_clean();

	echo html( 'ul', $items_html );
}
