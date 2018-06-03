<?php
/**
 * Admin options for the 'Integrations' page.
 *
 * @version 1.8
 * @author AppThemes
 * @package JobRoller\Admin\Integration
 */


### Classes

class JR_Integration_Admin extends APP_Tabs_Page {

	protected $legacy_html_output;

	function setup() {
		$this->textdomain = APP_TD;

		$this->args = array(
			'page_title' => __( '3rd Party Integration', APP_TD ),
			'menu_title' => __( 'Integration', APP_TD ),
			'parent' => 'app-dashboard',
			'page_slug' => 'jr-integration',
		);
	}

	protected function init_tabs() {
		$this->tabs->add( 'indeed', __( 'Indeed', APP_TD ) );

		$this->tab_indeed();

		// legacy integration settings
		$options_integration = apply_filters( 'jr_filter_integration_values', array() );

		$this->maybe_convert_old_integration_options( $options_integration );
	}

	protected function tab_indeed() {

		$this->tab_sections['indeed']['enable'] = array(
			'title' => __( 'General', APP_TD ),
			'desc' => __( 'Setup integration with Indeed and pull jobs directly from their API.', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Enable', APP_TD ),
					'name' => 'indeed_publisher_enable',
					'type' => 'checkbox',
					'desc' => __( 'Display jobs from Indeed.com', APP_TD ),
					'tip' => sprintf( __( 'If enabled, your site will be able to pull and display jobs directly from <a href="%s" target="_blank">Indeed.com</a>. These jobs are not stored on your database. Clicking any of these jobs will take customers to the Indeed site, directly.', APP_TD ), 'www.indeed.com' ),
				),

				array(
					'title' => __( 'Publisher ID', APP_TD ),
					'name' => 'jr_indeed_publisher_id',
					'type' => 'text',
					'desc' => sprintf( __( "Sign up for a free <a target='_new' href='%s'>Indeed account</a>", APP_TD ), 'https://ads.indeed.com/jobroll/' ),
					'tip' => __( 'Enter your unique Indeed publisher ID.', APP_TD ),
				),
				array(
					'title' => __( 'Channel Name', APP_TD ),
					'name' => 'jr_indeed_channel',
					'type' => 'text',
					'desc' => __( 'Channel name you setup within your Indeed account', APP_TD ),
					'tip' => __( 'If you add a channel name, you can monitor site performance from your Indeed account. This field is optional but recommended.', APP_TD ),
				),
				array(
					'title' => __( 'CSS Class', APP_TD ),
					'name' => 'jr_indeed_job_type_sponsored',
					'type' => 'text',
					'desc' => __( 'Apply a custom style to Indeed job results', APP_TD ),
					'tip' => __( 'Set a CSS class that should be applied to sponsored jobs to improve visibility and generate more clicks. You can also style these types of jobs using existing the <code>ty_indeed_sponsored</code> class (advanced users).', APP_TD ),
				),
			),
		);

		$this->tab_sections['indeed']['queries'] = array(
			'title' => __( 'Jobs', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Limit', APP_TD ),
					'name' => 'jr_indeed_front_page_count',
					'type' => 'number',
					'desc' => __( 'Number of jobs to pull from Indeed', APP_TD ),
					'extra' => array(
						'class' => 'small-text'
					),
				),
				array(
					'title' => __( 'Cache', APP_TD ),
					'name' => 'jr_indeed_frontpage_cache',
					'type' => 'number',
					'desc' => __( 'Number of seconds', APP_TD ),
					'tip' => __( 'To speed up Indeed home page loading, you can cache the results for a set period of time. <code>3600 = 1 hour</code>. Leave blank to disable caching.', APP_TD ),
					'extra' => array(
						'class' => 'small-text'
					),
					'std' => '3600',
				),
				array(
					'title' => __( 'Type', APP_TD ),
					'name' => 'jr_indeed_site_type',
					'type' => 'select',
					'values' => array(
						'all' => __( 'All', APP_TD ),
						'jobsite' => __( 'Job Sites', APP_TD ),
						'employer' => __( 'Direct Employers', APP_TD ),
					),
					'desc' => __( 'Pull from job boards, direct employers or both', APP_TD ),
				),
				array(
					'title' => __( 'Sort Order', APP_TD ),
					'name' => 'jr_indeed_sort_order',
					'type' => 'select',
					'tip' => '',
					'values' => array(
						'date' => __( 'Date', APP_TD ),
						'relevance' => __( 'Most Relevant (recommended)', APP_TD ),
					)
				),
				array(
					'title' => __( 'Criteria', APP_TD ),
					'desc' => sprintf( __( "Use the following format (one query per line): <code>keyword|country|job type|location</code> (optional values are post code and city).
									<br/><br/><strong>Examples</strong>
									<br/><code>web designer|GB|fulltime</code> - retrieves all full-time web design jobs in the UK
									<br/><code>web designer OR web developer|GB|fulltime</code> - retrieves full-time web design and web development jobs in the UK
									<br/><br/>Some job types may need to be mapped to match your JobRoller job types slugs. See more details on the <em>Mappings</em> option below. For available country codes and other parameters, <a target='_new' href='%s'>login to your Indeed account</a>.
									", APP_TD ), 'http://www.indeed.com/publisher' ),
					'tip' => __( "Setup your criteria and category mappings to pull in Indeed job listings.
					   <br/><br/><strong>Home Page</strong> - All jobs found based on your criteria will be displayed on your home page.
					   <br/><br/><strong>Search Results</strong> - Dynamically uses your criteria based on the user's search. For example, if the user is searching jobs by keyword, your queries keywords will be skipped in favour of the user's. It will use all the other queries information like job type or location.
					   <br><br/><strong>Filters</strong> - Dynamically uses your criteria based on the user's filter. For example, when users filter jobs by job type, your queries job types will be skipped in favour of the user selected job type. This means that even if you only set queries for two job types
					   users can get results from any filterable job type.<br/><br/>Each query will run, be merged together, and displayed with your other job listings. Do not add too many queries since this will slow your site down significantly.", APP_TD ),
					'name' => 'jr_front_page_indeed_queries',
					'type' => 'textarea',
					'extra' => array(
						'rows' => 5,
						'cols' => 50,
						'class' => 'large-text'
					),
				),
				array(
					'title' => __( 'Mapping', APP_TD ),
					'name' => 'jr_indeed_jtypes_other',
					'type' => 'textarea',
					'desc' => __( "Use the following format (one mapping per line): <code>your-slug|indeed-slug</code>.
							<br><br/><strong>Examples</strong>
							<br/><code>freelance|contract</code>
							<br/><code>full-time|fulltime</code>
						", APP_TD ),
					'tip' => __( "If you've setup custom job types that don't match the default Indeed values, you'll need to manually map each one otherwise you won't get any matches. Indeed recognizes the following job types: <code>fulltime, parttime, contract, internship, temporary</code>.", APP_TD ),
					'extra' => array(
						'rows' => 5,
						'cols' => 50,
						'class' => 'large-text'
					),
				),
				array(
					'title' => __( 'Keywords', APP_TD ),
					'desc' => __( 'Comma separated list of keywords to append on every user search (not visible to user).', APP_TD ),
					'name' => 'indeed_fixed_keywords',
					'type' => 'textarea',
					'extra' => array(
						'rows' => 3,
						'cols' => 50,
						'class' => 'large-text'
					),
					'tip' => __( 'Specify any keywords that you want to append to every user search. These keywords can help retrieve more accurate job results to your target audience. ', APP_TD ) .
							'<br/>'. __( 'For example, if your site is related to the healthcare industry you can add <code>healthcare</code>. When a user searches for a job using a keyword like <em>\'marketing\'</em>, for example, the keyword <code>healthcare</code> is appended internally and results are narrowed down to <em>\'healthcare marketing</em>\' jobs.', APP_TD ) .
							'<br/><br/>'. __( '<strong>Note:</strong> Static keywords might not always return the most relevant results but they should help on most occasions. Avoid using too many static keywords as it might affect search performance.', APP_TD ),
				),
			),
		);

		$this->tab_sections['indeed']['display'] = array(
			'title' => __( 'Display Results', APP_TD ),
			'fields' => array(
				array(
					'title' => __( 'Home Page', APP_TD ),
					'name' => 'jr_indeed_front_page',
					'type' => 'checkbox',
					'desc' => __( 'Show Indeed jobs on your home page', APP_TD ),
				),
				array(
					'title' => __( 'Listings', APP_TD ),
					'name' => 'jr_indeed_all_listings',
					'type' => 'checkbox',
					'desc' => __( 'Show Indeed jobs on all job listing pages', APP_TD ),

					'tip' => sprintf( __( "Only jobs matching your criteria set above, are returned when browsing via the sidebar widget (type, category, salary, post date). <br/><br/><strong>Example:</strong> Having <code>Design|GB|fulltime</code> in your criteria will return fulltime design jobs when users browse the design jobs category.
						<br/><br/>If you want to allow visitors to browse jobs in empty categories, you can enable the <em>'Show Empty Categories'</em> option in the <a href='%s'>WordPress customizer</a> under 'Site'.
						", APP_TD ), 'customize.php' ),
				),
				array(
					'title' => __( 'Search', APP_TD ),
					'name' => 'jr_dynamic_search_results',
					'type' => 'select',
					'desc' => __( 'Show Indeed jobs in search results', APP_TD ),
					'tip' => __( 'This option will dynamically pull in search results from Indeed when your job board has no results.', APP_TD ),
					'values' => array(
						'yes' => __( 'All the time', APP_TD ),
						'noresults' => __( 'When no results are found', APP_TD ),
						'no' => __( 'Never', APP_TD ),
					),
				),
				array(
					'title' => __( 'Position', APP_TD ),
					'name' => 'jr_indeed_results_position',
					'type' => 'select',
					'values' => array(
						'before' => __( 'Before Site Listings', APP_TD ),
						'after' => __( 'After Site Listings', APP_TD )
					),
					'tip' => '',
				),
			),
		);

	}


	### Helper Methods

	function do_nothing() {
		return null;
	}

	/**
	 * Converts integration options in old format (e.g: careerjet, simplyhired, linkedin ) to new options API.
	 *
	 * Will be removed after plugins are updated with new options API.
	 */
	protected function maybe_convert_old_integration_options( $options ) {

		$default = array(
			'title' => 'title',
			'name' => 'name',
		);

		foreach( $options as $field ) {

			$field = wp_parse_args( $field, $default );

			if ( ! empty( $field['type'] ) && 'tab' == $field['type'] ) {

				$old_section_slug = $section_slug = '';
				$fields = array();

				$tab_slug = sanitize_title( $field['tabname'] );

				$this->tabs->add( $tab_slug, $field['tabname'] );

			} elseif ( ! empty( $field['type'] ) && 'title' == $field['type'] ) {

				$section_slug = sanitize_title( $field['name'] );
				$section = $field['name'];

			} elseif ( ! empty( $field['type'] ) && 'logo' == $field['type'] ) {
				$logo = $field['name'];

				$this->tab_sections[ $tab_slug ]['logo'] = array(
					'fields' => array(
						array(
							'title' => "<div class='integration-logo {$tab_slug}'>{$logo}</div>",
							'name' => '_blank',
							'type' => 'custom',
							'render' => array( $this, 'do_nothing' ),
						),
					),
				);

			} else {

				// add the collected fields to the current tab on each new section
				if ( ( $old_section_slug && $old_section_slug != $section_slug  ) || ( ! empty( $field['type'] ) && 'tabend' == $field['type'] ) ) {

					$this->tab_sections[ $tab_slug ][ $old_section_slug ] = array(
						'title'  => $old_section,
						'fields' => $fields,
					);

					$fields = array();
				}

				// some plugins old options use the 'html' attribute to ouput content
				if ( ! empty( $field['html'] ) ) {

					ob_start();
					echo $field['html'];
					$this->legacy_html_output = ob_get_clean();

					$field = array(
						'title'  => '',
						'name'	 => '_blank',
						'type'   => 'custom',
						'render' => array( $this, 'html_output' ),
					);

				// map any old attributes to the new ones
				} elseif ( ! empty( $field['id'] ) ) {

					if ( ! empty( $field['name'] ) ) {
						$field['title'] = $field['name'];
					}
					$field['name'] = $field['id'];

					if ( ! empty( $field['desc'] ) ) {
						$field['desc'] = $field['desc'];
					}

					if ( ! empty( $field['options'] ) ) {
						$field['values'] = $field['options'];
						unset( $field['options'] );
					}

					if ( empty( $field['css'] ) ) {

						if ( 'select' == $field['type'] ) {
							$field['css'] = 'min-width: 50px;';
						} else {
							$field['css'] = 'width: 50px;';
							}

					} elseif ( false !== strpos( $field['name'], 'publisher_id' ) ) {
						$field['css'] = 'min-width: 300px;';
					}
					$field['extra'] = array( 'style' => $field['css'] );

					if ( 'text' == $field['type'] ) {

						if ( FALSE !== strpos( $field['extra']['style'], 'min-width' ) ) {
							$field['extra']['class'] = 'regular-text';
						} elseif ( FALSE !== strpos( $field['extra']['style'], 'max-width' ) || FALSE !== strpos( $field['extra']['style'], 'width' ) ) {
							$field['extra']['class'] = 'small-text';
						}

						$field['extra']['style'] = '';

					} elseif ( 'textarea' == $field['type'] ) {
						$field['extra'] = array(
							'rows' => 10,
							'cols' => 50,
							'class' => 'large-text'
						);
					}

					unset( $field['id'] );

					$fields[] = $field;
				}

				$old_section_slug = $section_slug;
				$old_section = $section;
			}
		}

	}

	/**
	 * Output old 'html' output as an amind notice.
	 */
	function html_output() {
		echo scb_admin_notice( $this->legacy_html_output, 'error' )	;
	}

}
