<?php
/**
 * Indeed Integration
 * Dynamic results from indeed
 *
 *
 * @version 1.2
 * @author AppThemes
 * @package JobRoller
 * @copyright 2010 all rights reserved
 *
 */
define('INDEED_VERSION', 2);

// Indeed Job Types
define('INDEED_FULLTIME', 'fulltime');
define('INDEED_PARTTIME', 'parttime');
define('INDEED_CONTRACT', 'contract');
define('INDEED_TEMP', 'temporary');
define('INDEED_INTERN', 'internship');

// JR Job Types
define('INDEED_JR_FULLTIME', 'full-time');
define('INDEED_JR_PARTTIME', 'part-time');
define('INDEED_JR_CONTRACT', 'freelance');
define('INDEED_JR_TEMP', 'temporary');
define('INDEED_JR_INTERN', 'internship');

// globalize job type mappings
global $jr_indeed_jtype_mappings, $default_jtype_mappings;

add_action( 'wp_enqueue_scripts', 'jr_load_indeed_js' );
add_action( 'after_setup_theme', 'jr_indeed_init' );
add_action( 'tabs_jobroller_page_jr-integration_form_handler' , 'jr_indeed_clear_cached_results' );

/**
 * Enqueue Indeed specific JS scripts
 *
 * @since 1.6.4
 */
function jr_load_indeed_js() {
	global $jr_options;

	$http = is_ssl() ? 'https' : 'http';

		// skip is disabled entirely
	if ( ! $jr_options->indeed_publisher_enable ) {
		return;
	}

	$jr_enable_indeed_feeds = ( $jr_options->jr_indeed_front_page || $jr_options->jr_indeed_all_listings || $jr_options->jr_dynamic_search_results );
	if ( $jr_enable_indeed_feeds ) {
		 wp_enqueue_script('indeed-api', ''.$http.'://gdc.indeed.com/ads/apiresults.js');
	}

}

/**
 * Load Indeed integration only after the theme initializes
 *
 * @since 1.6.1
 */
function jr_indeed_init() {
	global $jr_options, $jr_indeed_jtype_mappings, $default_jtype_mappings;

	// skip is disabled entirely
	if ( ! $jr_options->indeed_publisher_enable ) {
		return;
	}

	$jr_indeed_publisher_id = trim( $jr_options->jr_indeed_publisher_id );

	if ( ! $jr_indeed_publisher_id ) {
		return;
	}

	$jr_indeed_jtype_mappings = array();
	if ( $job_type_mappings = $jr_options->jr_indeed_jtypes_other ) {
		$jr_indeed_jtype_mappings = explode( "\n", $job_type_mappings );
	}

	// default job type mappings jr => indeed
	$default_jtype_mappings = array(
		'full-time' => 'fulltime',
		'part-time' => 'parttime'
	);

	// indeed results position - possible values: before, after
	$position = $jr_options->jr_indeed_results_position ? $jr_options->jr_indeed_results_position : 'before';

	if ( $jr_options->jr_indeed_all_listings ) {
		add_action( $position . '_jobs_archive', 'jr_indeed_html_placeholder', 10, 2 );
		add_action( $position . '_jobs_taxonomy', 'jr_indeed_html_placeholder', 10, 2 );
		add_action( $position . '_jobs_by_date', 'jr_indeed_html_placeholder', 10, 2 );
	}

	if ( $jr_options->jr_indeed_front_page ) {
		add_action( $position . '_front_page_jobs', 'jr_indeed_html_placeholder' );
	}

	// Search results indeed search
	if ( 'yes' == $jr_options->jr_dynamic_search_results ) {
		add_action( 'after_search_results', 'jr_indeed_search_results' );
	} elseif ( 'noresults' == $jr_options->jr_dynamic_search_results ) {
		add_action( 'appthemes_job_listing_loop_else', 'jr_indeed_search_results' );
	}

	// AJAX get indeed results
	add_action( 'wp_ajax_get_indeed_results', 'jr_get_indeed_results' );
	add_action( 'wp_ajax_nopriv_get_indeed_results', 'jr_get_indeed_results' );
}

/**
 * Ajax callback to call Indeed frontpage, filter or search functions.
 *
 * @since 1.4.2
 *
 * @return string Echoes the results in HTML format
 */
function jr_get_indeed_results() {

	check_ajax_referer( 'get-sponsored-results', 'security' );

	$page = (isset($_POST['page'])) ? $_POST['page'] : 1;

	// indeed search results
	if (isset($_POST['load']) && $_POST['load']=='search') {

		jr_indeed_search_results( true, $page );

	} else {

		// indeed filter results by categories
		if (isset($_POST['load']) && ($_POST['load']=='filter_results' || $_POST['load']=='more_filter_results')) {

			$is_ajax = ((isset($_POST['load']) && $_POST['load']=='filter_results')?false:true);

			$args = array (
				'tax' => $_POST['tax'],
				'term' => $_POST['term']
			);

			jr_indeed_filter_results( $is_ajax, $page, $args );

		}  else {

			// indeed frontend results
			$is_ajax = ((isset($_POST['load']) && $_POST['load']=='front_results')?false:true);

			jr_indeed_results( $is_ajax, $page );

		}

	}

	die();

}

/**
 * Main function to retrieve jobs from the remote Indeed XML feed.
 *
 * @since 1.4.2
 *
 * @param int $limit The total jobs limit
 * @param int $start Jobs page start number
 * @param array $query The query args to pass to the XML feed
 * @return string|false The XML body or False if an error occurs
 */
function jr_get_indeed_jobs( $limit, $start = 0, $query = array() ) {
	global $jr_options;

	$jr_indeed_publisher_id = trim( $jr_options->jr_indeed_publisher_id );
	$jr_indeed_sort_order = $jr_options->jr_indeed_sort_order;
	$jr_indeed_site_type = $jr_options->jr_indeed_site_type;

	$ip = jr_getIP();
	$useragent = urlencode($_SERVER['HTTP_USER_AGENT']);

	if ( ! $jr_indeed_publisher_id ) {
		return false;
	}

	// set defaults
	$defaults = array(
		'jtype_is_param'  => false,						// set job type as a parameter or keyword
		'country' 		  => 'us',
		'job_type' 		  => INDEED_FULLTIME,
		'radius'		  => '50',
		'datespan' 		  => 'any'
	);
	$query = wp_parse_args( $query, $defaults );

	// convert radius from miles to km if specified
	if ( isset( $query['radius'] ) ) {
		// radius KM/Miles
		if ( $jr_options->jr_distance_unit == 'km' ) {
			$query['radius'] = $query['radius'] / 1.609344;
		}
	}

	if ( !$query['jtype_is_param'] ) {
		$query['keyword'] .= ' ' . $query['job_type'];
		$query['job_type'] = '';
	}

	// map Indeed params to JR parameters
	$feed_param_mappings = array(
		 'co' 		=> 'country',
		 'q'		=> 'keyword',
		 'salary' 	=> 'salary',
		 'jt'		=> 'job_type',
		 'l'		=> 'location',
		 'radius'	=> 'radius',
		 'fromage' 	=> 'datespan'
	);

	// add existing values to each XML feed key
	$feed_param = '';
	foreach ( $feed_param_mappings as $key => $value ) {

		if ( !empty( $query[ $value ]) ) {
			$feed_param .= sprintf("&%s=%s", $key, urlencode(strtolower(trim($query[$value]))));
		}

	}

	$xml_feed_url =
		'http://api.indeed.com/ads/apisearch?v=' . INDEED_VERSION .
			$feed_param .
			( $jr_indeed_site_type != 'all' ? '&st=' . $jr_indeed_site_type : '' ) .
			'&publisher=' 	. $jr_indeed_publisher_id .
			'&sort=' 		. $jr_indeed_sort_order .
			'&limit=' 		. $limit .
			'&userip='		. $ip .
			'&useragent='	. $useragent .
			'&start=' 		. $start;

    if (  $channel = $jr_options->jr_indeed_channel  ) {
        $xml_feed_url .= '&chnl='.$channel;
    }

	$indeed_result = @wp_remote_get( $xml_feed_url );

	// uncomment the following line to test the Indeed API parameters
	//echo '<p><strong>copy&paste the following line on your browser to test the API parameters</strong><br/>';
	//echo $xml_feed_url . '</p>';

	if ( is_wp_error( $indeed_result ) ) {
		return false;
	}

	return $indeed_result['body'];
}

/**
 * Parses the Indeed XML feed and returns the results on an tidy array.
 *
 * @since 1.4.2
 *
 * @param array $queries The job queries to run
 * @param int $limit The total jobs limit
 * @param int $start Jobs page start number
 * @return null|array The queries results or null if none returned
 */
function jr_query_indeed_search_results( $queries, $limit, $start ) {
	global $jr_options;

	$xml_limit = ceil($limit / sizeof($queries));

	$search_results = array();

	foreach ($queries as $query) {

		// for valid Indeed job types use them as a filter, else, use as keyword
		if ( jr_indeed_is_valid_job_type($query['job_type']) ) {
			$query['jtype_is_param'] = true;
		}

		$indeed_result = jr_get_indeed_jobs( $xml_limit, $start, $query );

		if ( ! $indeed_result ) continue;

		$xml = new SimpleXMLElement($indeed_result);

		$xmlresults = $xml->results;

		foreach( $xmlresults->result as $result ) {

			$x = array();

			if (isset($result->jobkey)) 			$x['jobkey'] = (string) $result->jobkey;
			if (isset($result->jobtitle)) 			$x['jobtitle'] = (string) $result->jobtitle;
			if (isset($result->snippet)) 			$x['snippet'] = (string) $result->snippet;
			if (isset($result->company)) 			$x['company'] = (string) $result->company;
			if (isset($result->url)) 				$x['url'] = (string) $result->url;
			if (isset($result->formattedLocation)) 	$x['location'] = (string) $result->formattedLocation;
			if (isset($result->country)) 			$x['country'] = (string) $result->country;
			if (isset($result->onmousedown))		$x['onmousedown'] = (string) $result->onmousedown;
			if (isset($result->date)) 				$x['date'] = (string) $result->date;

			// set the CSS classes for special job types (i.e: sponsored)
			if ( isset($result->sponsored) && $result->sponsored != 'false' ) {
				$x['type'] = 'sponsored';
				$x['class'] = $jr_options->jr_indeed_job_type_sponsored;
			}

			// try to map the user job type with an existing JR job type
			$x['jobtype'] = jr_indeed_jr_job_type_mappings( $query['job_type'] ) ;
		 	$x['jobtype_name'] =  $x['jobtype'];

			$x = array_map('trim', $x);

			$timestamp = strtotime($x['date']);

			$search_results[ $timestamp ] = $x;

		}

	}

	if ( sizeof( $search_results ) == 0 ) {
		return;
	}

	// sort results by date
	if ( 'date' == $jr_options->jr_indeed_sort_order ) {
		krsort( $search_results, SORT_NUMERIC );
	}

	$search_results = array_slice( $search_results, 0, $limit );

	return $search_results;
}


/**
 * Output Indeed jobs to the Frontpage.
 * Allows filtering the results to change title, image, etc
 *
 * @since 1.4.2
 *
 * @param bool $is_ajax Identifies an Ajax call
 * @param int $page The jobs page number
 * @return string The HTML jobs output
 */
function jr_indeed_results( $is_ajax = false, $page = 1 ) {
	global $jr_options;

	if ( get_query_var('paged') ) {
		$paged = get_query_var('paged');
	} elseif ( get_query_var('page') ) {
		$paged = get_query_var('page');
	} else {
		$paged = 1;
	}

	if ( ! $jr_options->jr_indeed_front_page || $paged > 1 ) {
		return;
	}

	$limit = $jr_options->jr_indeed_front_page_count ? $jr_options->jr_indeed_front_page_count : 5;
	$start = $page > 1 ? $limit * ( $page - 1 ) : 0;

	$search_results = array();

	$queries = jr_indeed_get_job_queries();

	$cache_time = $jr_options->jr_indeed_frontpage_cache;

	// set the Indeed results on a transient var to allow caching the results and speed up page loads
	$cached = get_transient( 'jr_indeed_results_frontpage' );

	if ( $cache_time && $cached && $page == 1 ) {
		$search_results = $cached;
	} else {
		$search_results = jr_query_indeed_search_results( $queries, $limit, $start );
		if ( $cache_time && $page == 1 ) {
			set_transient( 'jr_indeed_results_frontpage' , $search_results, (int) $cache_time );
		}
	}

	if ( sizeof( $search_results ) == 0 ) {
		return;
	}

	$params = array (
		'source'	  => 'Indeed',
		'url'		  => 'http://www.indeed.com/',
		'title' 	  => __('Sponsored Job Listings', APP_TD),
		'jobs_by_img' => 'http://www.indeed.com/p/jobsearch.gif',
		'link_class'  => array('more_sponsored_results', 'front_page'),
		'callback'	  => 'get_indeed_results'
	);

	jr_display_sponsored_results( $search_results, apply_filters('jr_indeed_frontpage_results',$params), $is_ajax, $page );
}

/**
 * Output Indeed jobs when using the job search bar.
 * Allows filtering the results to change title, image, etc
 *
 * @since 1.4.2
 *
 * @param bool $is_ajax Identifies an Ajax call
 * @param int $page The jobs page number
 * @return string The HTML jobs output
 */
function jr_indeed_search_results( $is_ajax = false, $page = 1 ) {
	global $jr_options;

	// append any fixed keywords set by the user
	if ( $fixed_keywords = $jr_options->indeed_fixed_keywords ) {
		// strip all quotes and double quotes added by the user
		$sanitized_fixed_keywords = str_replace( '"', '', str_replace( "'", '', $fixed_keywords ) );
		$fixed_keywords = explode( ',', $sanitized_fixed_keywords );

		// clean empty values
		$fixed_keywords = array_filter( $fixed_keywords );

		// wrap each imploded set of words in double quotes
		$fixed_keywords = '"' . implode('", "', $fixed_keywords ) . '"' ;
	}

	// use a transient for the search query to allow paginating searches with the parameters when using the Ajax 'Load More'
	if ($page == 1) {
		$search =  array (
			'query' => trim( get_search_query() . ' ' . $fixed_keywords ),
			'param' => isset($_GET) ? array_map('wp_strip_all_tags', $_GET) : ''
		);
		set_transient('jr_search_query', $search, 60);
	} else {
		$search = get_transient('jr_search_query');
	}

	$keyword = $search['query'];
	$limit = $jr_options->jr_indeed_front_page_count ? $jr_options->jr_indeed_front_page_count : 5;

	$start = ($page>1) ? $limit * ($page-1) : 0;

	// Location
	$location_query = '';
	$country = '';
	$radius = '';

	if (isset($search['param']['location'])) $location = trim($search['param']['location']); else $location = '';
	if (isset($search['param']['radius']) && $search['param']['radius']>0) $radius = trim($search['param']['radius']); else $radius = 50;

	if ( $location ) {
		$address = _jr_get_geolocation_url( $location );
		$address = esc_url_raw( $address );

		$cached = wp_cache_get( 'geo_'.urlencode($location) );

		if ( $cached ) {
			$address = $cached;
		} else {
			$address = json_decode( wp_remote_retrieve_body( wp_remote_get( $address ) ), true );
			if ( is_array( $address ) ) {
				wp_cache_set( 'geo_' . urlencode( $location ), $address );
			}
		}

		if ( is_array( $address ) ) {
			if ( isset( $address['results'][0]['shortname'] ) ) {
				$country = $address['results'][0]['shortname'];
			}
		}

		$location_query = $location;

	} else {
		$country = jr_get_server_country();
		$country = $country ? $country : 'us';
	}

	$queries = array();

	// for searches based on location only iterate through the job queries to retrieve relevant jobs
	$query_values = $query_defaults = array(
		'keyword'  => $keyword 			? $keyword 			: '',
		'location' => $location_query 	? $location_query 	: '',
		'radius'   => $radius 			? $radius 			: '',
	);

	$queries = jr_indeed_get_job_queries( $query_values, $query_defaults );

	// return earlier for empty queries
	if ( sizeof( $queries ) == 0 ) {
		return;
	}

	$search_results = jr_query_indeed_search_results( $queries, $limit, $start );

	if ( sizeof( $search_results ) == 0 ) {
		return;
	}

	$params = array (
		'source'	  => 'Indeed',
		'url'		  => 'http://www.indeed.com/',
		'title' 	  => __('Sponsored Search Results', APP_TD),
		'jobs_by_img' => 'http://www.indeed.com/p/jobsearch.gif',
		'link_class'  => array('more_sponsored_results', 'search_page'),
		'callback'	  => 'get_indeed_results'
	);

	jr_display_sponsored_results( $search_results, apply_filters('jr_indeed_search_results',$params), $is_ajax, $page );

}

/**
 * Output Indeed jobs relevant to the filters selected on the sidebar.
 * Allows filtering the results to change title, image, etc
 *
 * @since 1.4.2
 *
 * @param bool $is_ajax Identifies an Ajax call
 * @param int $page The jobs page number
 * @param array $args Additional taxonomy arguments to filter the results
 * @return string The HTML jobs output
 */
function jr_indeed_filter_results( $is_ajax = false, $page = 1, $args = array() ) {
	global $jr_options;

	if ( ! $jr_options->jr_indeed_all_listings ) {
		return;
	}

	$limit = $jr_options->jr_indeed_front_page_count ? $jr_options->jr_indeed_front_page_count : 5;
	$start = $page > 1  ? $limit * ( $page - 1 ) : 0;

	$queries = $user_queries = array();

	$filter = array (
		'type'   => '',
		'value'  => '',
	);

	// taxonomy / term
	$taxonomy = $args['tax'];
	$term = $args['term'];

	### Filters

	# Filter by Job Category / Job Tag

	if ($taxonomy == APP_TAX_CAT || $taxonomy == APP_TAX_TAG) {

		$filter['type'] = 'keyword';
		$filter['value'] = get_term_by('slug', $term, $taxonomy)->name;

	} else {

		# Filter by Job Type

		if ($taxonomy == APP_TAX_TYPE) {

			$filter['type'] = 'job_type';
			$filter['value'] = jr_indeed_jr_job_type_mappings( $term, 'custom', 'jr' );

		} else {

			# Filter by Job Salary

			if ($taxonomy == APP_TAX_SALARY) {

				$salary = $term;
				$salary = get_term_by( 'slug', $salary, APP_TAX_SALARY)->name;

				// try to format the salary so indeed can read it (e.g.: 40,000 or 40K-90K)
				$salary = preg_replace('/[^0-9\-\,]/i','', $salary);	// strip everything but numbers and the delimiter (-)
				$salary = preg_replace('/\./',',', $salary);			// replace . (dot) with , (comma)
				$salary = preg_replace('/\,000/','k', $salary);			// replace .000 (thousands) with a 'k'

				$filter['type'] = 'salary';
				$filter['value'] = $salary;

			} else {

				# Filter by Date

				$date = time();
				$week_start_date = jr_week_start_date( date('W'), date('o') );
				$days_between = $date - strtotime($week_start_date);

				switch ($term) :
					case "today" :
						$datespan = 0;
					break;
					case "week" :
						$datespan = floor($days_between/(60*60*24));
					break;
					case "lastweek" :
						$last_week = strtotime ( '7 days' , $days_between) ;
						$datespan = floor($last_week/(60*60*24));
					break;
					case "month" :
						$datespan =  date('d');
					break;
				endswitch;

				$filter['type'] = 'datespan';
				$filter['value'] = (int) $datespan;

			};

		};

	}

	$queries = array();

	// get the selected filter type and value
	foreach ( $filter as $key ):

		if ( $filter['type'] == $key ) :
			$query_values = array( $key => $filter['value'] );
			break;
		endif;

	endforeach;

	if ( $filter['type'] == 'job_type' ) $query_values['remove_duplicates'] = 'keyword';

	$user_queries = jr_indeed_get_job_queries( $query_values );

	if ( sizeof($user_queries)==0 ) return;

	// iterate through the queries to find matches for the selected filter.
	// returns as soon as it finds a match
	if ( $filter['type'] == 'job_type' ) :

		foreach ($user_queries as $query) :

			$values = explode(',', $query[ $filter['type'] ]);
			foreach ($values as $value):
				$match = strcasecmp( strtolower(trim($filter['value'])), strtolower(trim($value)) );

				// for every match add as valid query
				if ( $match == 0 ) :
					$queries[] = $query;
					break;
				endif;

			endforeach;

		endforeach;

	else:

		$queries = $user_queries;

	endif;

	// return earlier with empty queries
	if (sizeof($queries)==0) return;

	$search_results = jr_query_indeed_search_results( $queries, $limit, $start );

	// return earlier with no results
	if (sizeof($search_results)==0) return;

	$params = array (
		'source'	  => 'Indeed',
		'url'		  => 'http://www.indeed.com/',
		'title' 	  =>  __('Sponsored Search Results', APP_TD),
		'jobs_by_img' => 'http://www.indeed.com/p/jobsearch.gif',
		'link_class'  => array('more_sponsored_results', 'filter'),
		'callback'	  => 'get_indeed_results',
		'tax'		  => $args['tax'],
		'term'		  => $args['term']
	);

	jr_display_sponsored_results( $search_results, apply_filters('jr_indeed_filter_results',$params), $is_ajax, $page );

}


/**
 * Returns a Custom or JR job type corresponding to the $map_type parameter.
 *
 * @since 1.0.0
 *
 * @param string $mapping The mapping being searched
 * @param string $map_type The mapping corresponding to the returned job type. 'custom' = returns the custom job type, 'jr' = returns the JR job type
 * @return string The custom or JR job types depending on the $map_type parameter value
 */
function jr_indeed_find_jtype_mapping( $mapping, $map_type ) {

	$arr_mapping = explode('|', $mapping);
	return strtolower(trim($arr_mapping[ ( $map_type == 'custom' ? 1 : 0 ) ]));

}

/**
 * Finds a mapping for a $job_type and returns the related Custom or JR job type value depending on the $map parameter.
 *
 * examples:
 * $map = array(custom, jr) matches the $job_type parameter with the custom job type and returns the related JR job type
 * $map = array(jr, custom) matches the $job_type parameter with the JR job type and returns the related custom job type
 *
 * @since 1.0.0
 *
 * @param string $job_type The job type being mapped
 * @param string $map The mapping type
 * @return string The related Custom or JR job type if a mapping is found. If not found, returns the passed job type.
 */
function jr_indeed_jr_job_type_mappings( $job_type, $map = array('custom', 'jr') ) {
	global $jr_indeed_jtype_mappings, $default_jtype_mappings;

	if ( sizeof($jr_indeed_jtype_mappings)> 0 ):

		// fill an array with the mapping index that should be used to return the job type value when comparing existing mappings
		$map_type = array_fill(0, sizeof($jr_indeed_jtype_mappings), $map[0]);

		// search for this job type key on each mapping
		$key = array_search ( strtolower(trim($job_type)), array_map('jr_indeed_find_jtype_mapping', $jr_indeed_jtype_mappings, $map_type ) );

		if ( FALSE !== $key ) :
			$jr_job_type = explode('|', $jr_indeed_jtype_mappings[ $key ]);
			// maps the custom job type or the JR job type
			$jr_job_type = $jr_job_type[ ( $map[1] == 'jr' ? 0 : 1 ) ];
		endif;

	endif;

	// if there are no mappings use the defaults
	if ( empty($jr_job_type) && $jr_def_job_type = array_search(strtolower(trim($job_type)), $default_jtype_mappings) )
		$jr_job_type = $jr_def_job_type;
	elseif ( empty($jr_job_type) )
		$jr_job_type = $job_type;

	return $jr_job_type;

}

/**
 * Returns an array with the job queries set by the user, or the defaults if not queries are set.
 *
 * @since 1.6.0
 *
 * @param array $query_values An optional array with new values that should replace the query values
 * @param array $query_defaults If array contains 'no-defaults' disables the defaults, else, contains the new default values that should be used instead of the regular defaults.
 * @return array An array with all the job queries
 */
function jr_indeed_get_job_queries( $query_values = array(), $query_defaults = array() ) {
	global $jr_options;

	$queries = array();
	$keywords = array();

	$jr_indeed_queries = array();
	if ( $job_queries = $jr_options->jr_front_page_indeed_queries ) {
		$jr_indeed_queries = explode("\n", $job_queries);
	}

	// if there are no user queries use defaults based on the user input, if any (job_type OR keywords)
	if ( sizeof($jr_indeed_queries)==0 ){

		$default = array(
				'keyword'  => ( !empty($query_values['keyword'])  ? $query_values['keyword']  : '' ),
				'country'  => ( !empty($query_values['country'])  ? $query_values['country']  : 'us' ),
		);

		$jr_def_query_1[] = $default['keyword'];
		$jr_def_query_1[] = $default['country'];
		$jr_def_query_1[] = !empty($query_values['job_type']) ? $query_values['job_type'] : INDEED_FULLTIME ;

		$jr_indeed_queries[] = implode('|',$jr_def_query_1);

		$jr_def_query_2[] = $default['keyword'];
		$jr_def_query_2[] = $default['country'];
		$jr_def_query_2[] = !empty($query_values['job_type']) ? $query_values['job_type'] : INDEED_PARTTIME;

		$jr_indeed_queries[] = implode('|',$jr_def_query_2);

	}

	// check if there are any job queries set by the user
	if ( sizeof($jr_indeed_queries)>0 ) foreach ($jr_indeed_queries as $query_row) :

		$query = explode('|', $query_row);

		if (sizeof($query)>2) :

			$keyword = $query[0];

			// check for duplicate keywords
			if ( isset($query_values['remove_duplicates']) && $query_values['remove_duplicates'] == 'keyword' && in_array( $keyword, $keywords) )
				continue;

			$country = trim($query[1]);
			$job_type = $query[2];
			if (isset($query[3])) $job_loc = $query[3]; else $job_loc = '';

			$queries[] = array(
				'keyword'  => ( !empty($query_values['keyword'])  ? $query_values['keyword']  : $keyword ),
				'country'  => ( !empty($query_values['country'])  ? $query_values['country']  : $country ),
				'job_type' => ( !empty($query_values['job_type']) ? $query_values['job_type'] : $job_type ),
				'location' => ( !empty($query_values['location']) ? $query_values['location'] : $job_loc ),
				'salary'   => ( !empty($query_values['salary'])   ? $query_values['salary']   : '' ),
				'datespan' => ( !empty($query_values['datespan']) ? $query_values['datespan'] : '' ),
				'radius'   => ( !empty($query_values['radius'])   ? $query_values['radius']   : '' )
			);

			$keywords[] = $keyword;

		endif;

	endforeach;

	// return earlier if no defaults are requested
	if ( isset($query_defaults[0]) && $query_defaults[0] == 'no-defaults' ) return $queries;

	if ( sizeof($queries)==0 ) :

		// Use defaults (mainly used for display default results on front page when there is no user criteria)
		$default = array(
			'keyword'  => ( !empty($query_defaults['keyword'])  ? $query_defaults['keyword']  : '' ),
			'country'  => ( !empty($query_defaults['country'])  ? $query_defaults['country']  : 'us' ),
			'location' => ( !empty($query_defaults['location']) ? $query_defaults['location'] : '' ),
			'datespan' => ( !empty($query_defaults['datespan']) ? $query_defaults['datespan'] : '' ),
			'radius'   => ( !empty($query_defaults['radius'])   ? $query_defaults['radius']   : '' )
		);

		$queries[] = $default;
		$queries[0]['job_type'] = INDEED_FULLTIME;

		$queries[] = $default;
		$queries[1]['job_type'] = INDEED_PARTTIME;

	endif;

	return $queries;

}

/**
 * Check if a specific job type is a valid Indeed job type
 *
 * @since 1.5.3
 *
 * @param string $job_type The job type being checked
 * @return bool True for valid job types. False, otherwise
 */
function jr_indeed_is_valid_job_type( $job_type ) {
	$valid_jtypes = array ( INDEED_FULLTIME, INDEED_PARTTIME, INDEED_CONTRACT, INDEED_TEMP, INDEED_INTERN );

	return (in_array( strtolower(trim($job_type)), $valid_jtypes ));
}

/**
 * Sanitize a specific job types to later validate it against the Indeed job types
 *
 * @since 1.5.3
 *
 * @param string $job_type The job type being sanitized
 * @return string The sanitized job type
 */
function jr_indeed_sanitize_job_type( $job_type ) {
	return trim(strtolower(preg_replace('/[^A-Za-z]/i', '', $job_type)));
}

/**
 * Outputs the HTML placeholder where the sponsored results will be attached
 *
 * @since 1.5.3
 *
 * @param string $taxonomy An optional taxonomy if the placeholder is being displayed on a taxonomy page
 * @param string $term An optional term if the placeholder is being displayed on a taxonomy page
 * @return string The HTML output
 */
function jr_indeed_html_placeholder ( $taxonomy = '', $term = '' ) {
?>
	<div class="async_sponsored_results" source="indeed" callback="get_indeed_results" tax="<?php echo $taxonomy; ?>" term="<?php echo $term; ?>"><div class="sponsored-results-placeholder"></div>
	<p><?php _e('Fetching Indeed Job Listings',APP_TD); ?></p></div>
<?php

}

/**
 * Clear any cached results.
 *
 * @since 1.8
 */
function jr_indeed_clear_cached_results() {
	delete_transient( 'jr_indeed_results_frontpage' );
}
