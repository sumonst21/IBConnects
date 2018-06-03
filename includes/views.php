<?php
/**
 * JobRoller Views.
 * Views prepare and provide data to the page requested by the user.
 *
 * @version 1.7
 * @author AppThemes
 * @package JobRoller\Views
 * @copyright 2010 all rights reserved
 */

### Pages

/**
 * Registration Page view.
 */
class JR_Registration extends APP_View {

	/**
	 * Sets up view.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'appthemes_before_login_template', array( $this, 'maybe_enqueue_js_styles' ), 99 );
		add_filter( 'registration_errors', array( $this, 'recaptcha_verify' ) );
	}

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		return true;
	}

	public function maybe_enqueue_js_styles( $action ) {

		if ( ! in_array( $action, array( 'login', 'register' ) ) ) {
			return;
		}

		if ( current_theme_supports( 'app-recaptcha-register' ) ) {
			appthemes_enqueue_recaptcha_scripts();
		}

	}

	public function recaptcha_verify( $errors ) {

		// process the reCaptcha request if it's been enabled
		$response = jr_validate_recaptcha('app-recaptcha-register');

		if ( is_wp_error( $response ) ) {

			foreach ( $response->get_error_codes() as $code ) {
				$errors->add( $code, $response->get_error_message( $code ) );
			}

		}
		return $errors;
	}

}


/**
 * Home Archive page view.
 */
class JR_Home_Archive extends APP_View_Page {

	private static $_template;

	/**
	 * Sets up page view.
	 *
	 * @return void
	 */
	public function __construct() {
		self::$_template = 'index.php';
		parent::__construct( self::$_template, __( 'Home', APP_TD ) );
	}

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		$page_id = (int) get_query_var( 'page_id' );
		return $page_id && $page_id == self::get_id(); // for 'page_on_front'
	}

	function pre_get_posts( $wp_query ) {
		$wp_query->is_home = true;
	}

	/**
	 * Returns page ID.
	 *
	 * @return int
	 */
	public static function get_id() {
		return self::_get_page_id( self::$_template );
	}

}


/**
 * Blog page view.
 */
class JR_Blog_Page extends APP_View_Page {

	private static $_template;

	/**
	 * Sets up page view.
	 *
	 * @return void
	 */
	public function __construct() {
		self::$_template = 'tpl-blog.php';
		parent::__construct( self::$_template, __( 'Blog', APP_TD ) );

		add_action( 'appthemes_before_blog_post_content', array( $this, 'blog_featured_image' ) );
	}

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		return is_home();
	}

	/**
	 * Returns page ID.
	 *
	 * @return int
	 */
	public static function get_id() {
		return self::_get_page_id( self::$_template );
	}

	public function blog_featured_image() {
		if ( has_post_thumbnail() ) {
			echo html('a', array(
				'href' => get_permalink(),
				'title' => the_title_attribute( array( 'echo' => 0 ) ),
				), get_the_post_thumbnail( get_the_ID(), array( 420, 150 ), array( 'class' => 'alignleft' ) ) );
		}
	}

	/**
	 * Determines which template to include.
	 *
	 * @param string $path The path of the template to include.
	 *
	 * @return string
	 */
	public function template_include( $path ) {
		return appthemes_locate_template( 'tpl-blog.php' );
	}

	/**
	 * Fires before determining which template to load.
	 *
	 * @return void
	 */
	function template_redirect() {
		global $jr_options;

		if ( $jr_options->jr_disable_blog ) {
			wp_redirect( home_url() );
			exit;
		}
	}

}


/**
 * Contact form page view.
 */
class JR_Contact_Page extends APP_View_Page {

	private static $_template;

	/**
	 * Sets up page view.
	 *
	 * @return void
	 */
	public function __construct() {
		self::$_template = 'tpl-contact.php';
		parent::__construct( self::$_template, __( 'Contact', APP_TD ) );
	}

	/**
	 * Returns page ID.
	 *
	 * @return int
	 */
	public static function get_id() {
		return self::_get_page_id( self::$_template );
	}

}


/**
 * User Profile page view.
 */
class JR_User_Profile_Page extends APP_View_Page {

	private static $_template;

	/**
	 * Sets up page view.
	 *
	 * @return void
	 */
	public function __construct() {
		self::$_template = 'tpl-profile.php';
		parent::__construct( self::$_template, __( 'My Profile', APP_TD ) );
	}

	/**
	 * Returns page ID.
	 *
	 * @return int
	 */
	public static function get_id() {
		return self::_get_page_id( self::$_template );
	}

	/**
	 * Fires before determining which template to load.
	 *
	 * @return void
	 */
	function template_redirect() {
		nocache_headers();
		appthemes_auth_redirect_login();
	}

}


/**
 * User Dashboard page view.
 */
class JR_Dashboard_Page extends APP_View_Page {

	private static $_template;

	/**
	 * Sets up page view.
	 *
	 * @return void
	 */
	public function __construct() {
		self::$_template = 'tpl-dashboard.php';
		parent::__construct( self::$_template, __( 'My Dashboard', APP_TD ) );
	}

	/**
	 * Returns page ID.
	 *
	 * @return int
	 */
	public static function get_id() {
		return self::_get_page_id( self::$_template );
	}

	/**
	 * Fires before determining which template to load.
	 *
	 * @return void
	 */
	function template_redirect() {
		nocache_headers();
		appthemes_auth_redirect_login();
	}

}


/**
 * Date Archive page view.
 */
class JR_Date_Archive_Page extends APP_View_Page {

	private static $_template;

	/**
	 * Sets up page view.
	 *
	 * @return void
	 */
	public function __construct() {
		self::$_template = 'tpl-jobs-by-date.php';
		parent::__construct( self::$_template, __( 'Job Date Archive', APP_TD ) );
	}

	/**
	 * Returns page ID.
	 *
	 * @return int
	 */
	public static function get_id() {
		return self::_get_page_id( self::$_template );
	}

}


/**
 * Terms & Conditions page view.
 */
class JR_Terms_Conditions_Page extends APP_View_Page {

	private static $_template;

	/**
	 * Sets up page view.
	 *
	 * @return void
	 */
	public function __construct() {
		self::$_template = 'tpl-terms-conditions.php';
		parent::__construct( self::$_template, __( 'Terms & Conditions', APP_TD ) );
	}

	/**
	 * Returns page ID.
	 *
	 * @return int
	 */
	public static function get_id() {
		return self::_get_page_id( self::$_template );
	}

}


/**
 * Resume Edit page view.
 */
class JR_Resume_Edit_Page extends APP_View_Page {

	private static $_template;

	/**
	 * Sets up page view.
	 *
	 * @return void
	 */
	public function __construct() {
		self::$_template = 'tpl-edit-resume.php';
		parent::__construct( self::$_template, __( 'Edit Resume', APP_TD ) );
	}

	/**
	 * Returns page ID.
	 *
	 * @return int
	 */
	public static function get_id() {
		return self::_get_page_id( self::$_template );
	}

	/**
	 * Fires before determining which template to load.
	 *
	 * @return void
	 */
	function template_redirect() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_js_styles' ) );
		add_action( 'wp_footer', array( $this, 'script_init' ), 99 );
	}

	public function enqueue_js_styles() {
		self::resume_enqueue_js();
	}

	public function script_init() {
		global $wp_query;

		$resume = $wp_query->query_vars['resume'];

		jr_geolocation_scripts( $resume );
?>
		<script type="text/javascript">
			/* <![CDATA[ */
			jQuery( function($){

				/* Auto Complete */
				var availableTags = [
					<?php
						$terms_array = array();

						$terms = get_terms( APP_TAX_RESUME_LANGUAGES, 'hide_empty=0' );

						if ( $terms ) {
							foreach ( $terms as $term ) {
								$terms_array[] = '"'.$term->name.'"';
							}
						}
						echo implode( ',', $terms_array );
					?>
				];
				function split( val ) {
					return val.split( /,\s*/ );
				}
				function extractLast( term ) {
					return split( term ).pop();
				}
				$("#languages_wrap input").on( "keydown", function( event ) {
					if ( ( event.keyCode === $.ui.keyCode.TAB || event.keyCode === $.ui.keyCode.COMMA ) && $( this ).data("uiAutocomplete").menu.active ) {
						event.preventDefault();
					}
				}).autocomplete({
					minLength: 0,
					source: function( request, response ) {
						// delegate back to autocomplete, but extract the last term
						response( $.ui.autocomplete.filter( availableTags, extractLast( request.term ) ) );
					},
					focus: function() {
						$('input.ui-autocomplete-input').val('');
						// prevent value inserted on focus
						return false;
					},
					select: function( event, ui ) {
						var terms = split( this.value );

						// remove the current input
						terms.pop();
						// add the selected item
						terms.push( ui.item.value );
						// add placeholder to get the comma-and-space at the end
						terms.push( "" );
						//this.value = terms.join( ", " );
						this.value = terms.join( "" );

						$(this).blur();
						$(this).focus();

						return false;
					}
				});
			});
			/* ]]> */
		</script>
<?php
	}

	// views common javascript enqueue
	public static function resume_enqueue_js() {

		$suffix_js = jr_get_enqueue_suffix_for('js');

		wp_enqueue_script('validate');

		wp_enqueue_script(
			'jr-resume-form',
			get_template_directory_uri() . "/includes/js/resume-form-scripts{$suffix_js}.js",
			array( 'jquery', 'validate', 'jquery-ui-sortable' ),
			JR_VERSION,
			true
		);

		wp_localize_script(
			'jr-resume-form',
			'JR_i18n',
			array(
				'ajaxurl' 		=> admin_url( 'admin-ajax.php' ),
				'clear'	  		=> __( 'Clear', APP_TD ),
				'loading_img' 	=> get_template_directory_uri() . '/images/loading.gif',
				'loading_msg' 	=> __( 'Please wait, loading additional category related fields...', APP_TD ),
				'required_msg'	=> __( 'This field is required.', APP_TD ),
			)
		);

	}

}


/**
 * Job Submit page view.
 */
class JR_Job_Submit_Page extends APP_View_Page {

	private static $_template;

	/**
	 * Initializes class params.
	 *
	 * @return void
	 */
	public function init() {
		global $wp;

		$wp->add_query_var( 'edit' );
	}

	/**
	 * Sets up page view.
	 *
	 * @return void
	 */
	public function __construct() {
		self::$_template = 'tpl-submit.php';
		parent::__construct( self::$_template, __( 'Submit Job', APP_TD ) );
	}

	/**
	 * Returns page ID.
	 *
	 * @return int
	 */
	public static function get_id() {
		return self::_get_page_id( self::$_template );
	}

	/**
	 * Fires before determining which template to load.
	 *
	 * @return void
	 */
	function template_redirect() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_js_styles' ) );
		add_filter( 'body_class', array( $this, 'body_class' ), 99 );
		add_action( 'wp_footer', array( $this, 'scripts_init' ), 99 );
	}

	public function enqueue_js_styles() {
		self::job_submit_enqueue_js();
	}

	public function body_class( $classes ) {
		$classes[] = 'jr_job_submit';
		return $classes;
	}

	public function scripts_init() {
		$job = jr_get_default_job_to_edit();
		jr_geolocation_scripts( $job );
	}

	// views common javascript enqueue
	public static function job_submit_enqueue_js() {

		$suffix_js = jr_get_enqueue_suffix_for( 'js' );

		wp_enqueue_script( 'validate' );

		wp_enqueue_script(
			'jr-job-form',
			get_template_directory_uri() . "/includes/js/job-form-scripts{$suffix_js}.js",
			array( 'validate', 'jquery-ui-sortable' ),
			JR_VERSION,
			true
		);

		wp_localize_script(
			'jr-job-form',
			'JR_i18n',
			array(
				'ajaxurl' 		=> admin_url( 'admin-ajax.php' ),
				'clear'	  		=> __( 'Clear', APP_TD ),
				'loading_img' 	=> get_template_directory_uri() . '/images/loading.gif',
				'loading_msg' 	=> __( 'Please wait, loading additional category related fields...', APP_TD ),
				'required_msg'	=> __( 'This field is required.', APP_TD ),
			)
		);

	}

}


/**
 * Job Edit page view.
 */
class JR_Job_Edit_Page extends APP_View_Page {

	private static $_template;

	/**
	 * Sets up page view.
	 *
	 * @return void
	 */
	public function __construct() {
		self::$_template = 'tpl-edit-job.php';
		parent::__construct( self::$_template, __( 'Edit Job', APP_TD ) );
	}

	/**
	 * Returns page ID.
	 *
	 * @return int
	 */
	public static function get_id() {
		return self::_get_page_id( self::$_template );
	}

	/**
	 * Fires before determining which template to load.
	 *
	 * @return void
	 */
	function template_redirect() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_js_styles' ) );
		add_filter( 'body_class', array( $this, 'body_class' ), 99 );
		add_action( 'wp_footer', array( $this, 'scripts_init' ), 99 );
	}

	public function enqueue_js_styles() {
		JR_Job_Submit_Page::job_submit_enqueue_js();
	}

	public function scripts_init() {
		$job = jr_get_default_job_to_edit();
		jr_geolocation_scripts( $job );
	}

	public function body_class( $classes ) {
		$classes[] = 'jr_job_edit';
		return $classes;
	}

}


/**
 * Job Packs Purchase page view.
 */
class JR_Packs_Purchase_Page extends APP_View_Page {

	private static $_template;

	/**
	 * Sets up page view.
	 *
	 * @return void
	 */
	public function __construct() {
		self::$_template = 'tpl-purchase-pack.php';
		parent::__construct( self::$_template, __( 'Purchase Job Pack', APP_TD ) );
	}

	/**
	 * Returns page ID.
	 *
	 * @return int
	 */
	public static function get_id() {
		return self::_get_page_id( self::$_template );
	}

	public function body_class( $classes ) {
		$classes[] = 'jr_packs_purchase';
		return $classes;
	}

}


/**
 * Resume Plans Purchase page view.
 */
class JR_Resume_Plans_Purchase_Page extends APP_View_Page {

	private static $_template;

	/**
	 * Sets up page view.
	 *
	 * @return void
	 */
	public function __construct() {
		self::$_template = 'tpl-purchase-resume-subscription.php';
		parent::__construct( self::$_template, __( 'Purchase Resume Subscription', APP_TD ) );
	}

	/**
	 * Returns page ID.
	 *
	 * @return int
	 */
	public static function get_id() {
		return self::_get_page_id( self::$_template );
	}

	public function body_class( $classes ) {
		$classes[] = 'jr_subscribe_resumes';
		return $classes;
	}

}


### Views

class JR_View extends APP_View {

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		return ! is_admin();
	}

	/**
	 * Retrieves vars used through the site.
	 *
	 * @uses apply_filters() Calls 'jr_template_vars'
	 *
	 */
	function template_vars() {
		global $jr_options;

		$footer_cols = jr_get_footer_columns();

		$template_vars = array(
			'jr_options'	=> $jr_options,
			'footer_cols'	=> $footer_cols ? range( 1, $footer_cols, 1 ) : array(),
			's_location'	=> ! empty( $_GET['location'] ) ? $_GET['location'] : '',
		);

		return apply_filters( 'jr_template_vars', $template_vars );
	}

	/**
	 * Determines which template to include.
	 *
	 * @param string $path The path of the template to include.
	 *
	 * @return string
	 */
	public function template_include( $path ) {
		add_action( 'wp_footer', array( $this, 'script_init' ), 100 );

		return $path;
	}

	public function script_init() {
?>
		<script type="text/javascript">
			/* <![CDATA[ */

			// Sidebar
			jQuery('ul.widgets li.widget.widget-nav div ul li ul, ul.widgets li.widget.widget-nav div').hide();
			jQuery('.widget-nav div.tabbed_section:eq(0), .widget-nav div.tabbed_section:eq(0) .contents').show();
			jQuery('.widget-nav ul.display_section li:eq(0)').addClass('active');

			// Tabs
			jQuery('.widget-nav ul.display_section li a').click(function(){

				jQuery('.widget-nav div.tabbed_section .contents').fadeOut();
				jQuery('.widget-nav div.tabbed_section').hide();

				jQuery(jQuery(this).attr('href')).show();
				jQuery(jQuery(this).attr('href') + ' .contents').fadeIn();

				jQuery('.widget-nav ul.display_section li').removeClass('active');
				jQuery(this).parent().addClass('active');

				return false;
			});

			// Sliding
			jQuery('ul.widgets li.widget.widget-nav div ul li a.top').click(function(){
				jQuery(this).parent().find('ul').slideToggle();
			});
			/* ]]> */
		</script>
<?php
	}

}

class JR_Date_Archive extends APP_View {

	/**
	 * Initializes class params.
	 *
	 * @return void
	 */
	public function init() {
		global $wp;

		$wp->add_query_var( 'jobs_by_date' );
	}

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		return get_query_var( 'jobs_by_date' );
	}

	function parse_query( $wp_query ) {
		$wp_query->is_archive = true;
	}

	function template_vars() {

		$show = '';
		if ( ! empty( $_GET['time'] ) ) {
			$show = $_GET['time'];
		} else {
			$show = 'week';
		}
		$dateargs = array();
		$title = '';
		$today = getdate();

		switch ($show) :
			case "week" :
				$title = __('This Week\'s Jobs',APP_TD);
				$dateargs = array( 'year' => $today["year"], 'w' => date('W') );
			break;
			case "lastweek" :
				$title = __('Last Week\'s Jobs',APP_TD);
				$week = date('W');
				$year = $today["year"];
				if ($week==0) :
					$week = 53;
					$year = $year-1;
				else :
					$week = $week-1;
				endif;
				$dateargs = array( 'year' => $year, 'w' => $week );
			break;
			case "today" :
				$title = __('Today\'s Jobs',APP_TD);
				$dateargs = array( 'year' => $today["year"], 'monthnum' => $today["mon"], 'day' => $today["mday"] );
			break;
			case "month" :
				$title = __('This Month\'s Jobs',APP_TD);
				$dateargs = array( 'year' => $today["year"], 'monthnum' => $today["mon"] );
			break;
		endswitch;

		$paged = get_query_var('paged') ? get_query_var('paged') : 1;

		$args = array(
			'post_type'				=> APP_POST_TYPE,
			'post_status'			=> 'publish',
			'posts_per_page'		=> jr_get_jobs_per_page(),
			'ignore_sticky_posts'	=> 1,
			'paged'					=> $paged
		);
		$args = array_merge( $dateargs, $args );

		$args = wp_parse_args( jr_process_filter_form(), $args );

		$template_vars = array(
			'timespan'		=> $show,
			'title'			=> $title,
			'query_args'	=> $args
		);

		return apply_filters( 'jr_jobs_by_date_template_vars', $template_vars );
	}

}

class JR_Job_Submit extends APP_View {

	/**
	 * Initializes class params.
	 *
	 * @return void
	 */
	public function init() {
		global $wp;

		$wp->add_query_var( 'job_id' );
		$wp->add_query_var( 'order_id' );
		$wp->add_query_var( 'step' );

		$this->handle_form();
	}

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		global $wp_query;
		return isset( $wp_query->queried_object ) && ( is_page( JR_Job_Submit_Page::get_id() ) || is_page( JR_Job_Edit_Page::get_id() ) );
	}

	/**
	 * Fires before determining which template to load.
	 *
	 * @return void
	 */
	function template_redirect() {
		appthemes_auth_redirect_login();

		if ( ! current_user_can( 'can_submit_job' ) ) {
			wp_die( __( 'You don\'t have permissions to post jobs.', APP_TD )  );
		}
	}

	function template_vars() {
		global $jr_options;

		$job = jr_get_default_job_to_edit();

		$step = jr_get_next_step();

		$steps = jr_steps();

		$this->job = $job;
		$this->step_name = $steps[ $step ]['name'];

		$params = array(
			'step' 			=> $step,
			'job' 			=> $this->job,
			'order_id'		=> get_query_var('order_id'),
			'post_action'	=> 'new-job',
			'form_action'	=> $_SERVER['REQUEST_URI'],
			'submit_text'	=> __( 'Next &rarr;', APP_TD ),
		);

		$template_vars = array(
			'job'			=> $this->job,
			'title'			=> __( 'Submit a Job', APP_TD ),
			'params'		=> $params,
			'steps_trail'	=> jr_get_steps_trail( $steps, $step ),
			'step'			=> $params['step'],
			'cat_required'  => $jr_options->jr_submit_cat_required ? 'required' : '',
		);

		if ( ! empty( $steps[ $step ]['template'] ) ) {
			$template_vars['template'] = $steps[ $step ]['template'];
		}

		return apply_filters( 'jr_job_submit_template_vars', $template_vars );
	}

	function handle_form() {
		$actions = array( 'edit-job', 'new-job', 'relist-job' );
		if ( empty( $_POST['action'] ) || ! in_array( $_POST['action'], $actions ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['nonce'], 'submit_job' ) ) {
			$errors = jr_get_listing_error_obj();
			$errors->add( 'submit_error', __( '<strong>ERROR</strong>: Sorry, your nonce did not verify.', APP_TD ) );
			return;
		}
	}
}


class JR_Job_Preview extends APP_View {


	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		return ( 3 == get_query_var( 'step' ) );
	}

	/**
	 * Fires before determining which template to load.
	 *
	 * @return void
	 */
	function template_redirect() {
		if ( ! get_query_var( 'job_id' ) ) {
			wp_die( __( 'Invalid job ID.', APP_TD ) );
		}

		if ( ! current_user_can( 'edit_job', (int) get_query_var( 'job_id' ) ) ) {
			wp_die( __( 'Cheatin&#8217; uh?', APP_TD ) );
		}
	}

	function template_vars() {
		$post = get_post( (int) get_query_var('job_id') );

		$preview_fields = $this->get_preview_fields( $post );

		$template_vars = array(
			'preview_fields' => $preview_fields
		);

		return apply_filters( 'jr_preview_job_template_vars', $template_vars );
	}

	/**
	 * Retrieves the fields that should be visible on the preview page.
	 *
	 * @uses apply_filters() Calls 'hrb_preview_custom_fields'
	 * @uses apply_filters() Calls 'hrb_preview_fields'
	 *
	 */
	function get_preview_fields( $job ) {
		global $jr_options;

		$args = array(
			'post_parent'	=> 0,
			'post_type'		=> 'attachment',
			'posts_per_page'=> -1,
			'post_status'	=> 'any',
			'post_parent'	=> $job->ID,
		);
		$attach_ids = get_children( $args );

		$logo_id = get_post_thumbnail_id( $job->ID );

		if ( $attach_ids || $logo_id ) {
			include_once APP_FRAMEWORK_DIR . '/media-manager/media-manager.php';
			remove_action( 'parse_query', '_appthemes_media_query_var', 10 );
			remove_filter( 'admin_url', '_appthemes_media_query_arg', 10, 3 );
			remove_filter( 'map_meta_cap','_appthemes_media_capabilities', 15, 4 );

			if ( $logo_id ) {
				$logo = appthemes_output_attachments( $logo_id, array( 'show_description' => false ), $output = false );
			}

			if ( $attach_ids ) {
				$attach_ids = array_keys( $attach_ids );
				$attach_ids = array_diff( $attach_ids, (array) $logo_id );
				$files = appthemes_output_attachments( $attach_ids, null, $output = false );
			}
		}

		$fields[ __( 'Logo', APP_TD ) ] = ! empty( $logo ) ? $logo : '-';

		$fields[ __( 'Job Description', APP_TD ) ] = wpautop( wptexturize( $job->post_content ) );
		$fields[ __( 'How To Apply', APP_TD ) ]	= wpautop( wptexturize( get_post_meta( $job->ID, '_how_to_apply', true ) ) );

		if ( $jr_options->jr_enable_salary_field ) {
			$terms = get_the_terms( $job->ID, APP_TAX_SALARY );
			if ( $terms ) {
				$term = reset( $terms );
				$salary = html( 'a href="'.esc_url( get_term_link( $term->slug, APP_TAX_SALARY ) ) . '"', APP_Currencies::get_current_currency('symbol') . ' ' . $term->name );
			}
			$fields[ __( 'Salary', APP_TD ) ] = ! empty( $salary ) ? $salary : '';
		}

		$terms = get_the_terms( $job->ID, APP_TAX_TAG );
		if ( $terms ) {
			foreach( $terms as $term ) {
				$tags[] = html( 'a href="'.esc_url( get_term_link( $term->slug, APP_TAX_TAG ) ) . '"', $term->name );
			}
		}

		$fields[ __( 'Tags', APP_TD ) ] = ! empty( $tags ) ? implode( ', ', $tags ) : '';

		$category = wp_get_post_terms( $job->ID, APP_TAX_CAT );
		$category = reset( $category );

		$custom_form_fields = array();

		if ( $category ) {
			$custom_form_fields = $this->custom_fields( $fields, $job->ID, $category->term_id );
		}

		$other_fields = array();

		if ( ! empty( $files ) ) {
			$other_fields = array(
				__( 'Files', APP_TD ) => $files,
			);
		}

		$fields = array_merge( $fields, $custom_form_fields, $other_fields );
		foreach( $fields as $key => $value ) {
			if ( empty( $value ) ) {
				$fields[ $key ] = '-';
			}
		}
		return apply_filters( 'jr_job_preview_fields', $fields );
	}

	/**
	 * Retrieves custom fields label/value pairs to be used for previewing a listing.
	 */
   function custom_fields( $fields, $post_id, $cat ) {

	   if ( ! current_theme_supports('app-form-builder') ) {
		   return;
	   }

	   $meta = get_post_custom( $post_id );

	   $custom_fields = jr_get_custom_fields_for_cat( $cat );
	   if ( empty( $custom_fields) ) {
		   return array();
	   }

	   $output = '';

	   foreach( $custom_fields as $name => $field ) {

		   if ( ! empty( $meta[ $field['name'] ][0] ) ) {

			   if ( 'file' == $field['type'] ) {
				   $attachment_ids = maybe_unserialize( $meta[ $field['name'] ][0] );
				   $output = jr_output_attachments( $attachment_ids, null, $output = false );
				   $fields[ $field['desc'] ] = $output;
			   } else {
				   $fields[ $field['desc'] ] = $meta[ $field['name'] ][0];
			   }

		   }
	   }

	   return $fields;
   }

}

class JR_Job_Relist extends JR_Job_Submit {

	/**
	 * Initializes class params.
	 *
	 * @return void
	 */
	public function init() {
		global $wp;

		$wp->add_query_var( 'job_relist' );
	}

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		return false !== get_query_var( 'job_relist', false );
	}

	/**
	 * Fires before determining which template to load.
	 *
	 * @return void
	 */
	function template_redirect() {
		parent::template_redirect();

		if ( ! get_query_var( 'job_relist' ) ) {
			wp_die( __( 'Invalid job ID.', APP_TD ) );
		}

		if ( ! current_user_can( 'relist_job', (int) get_query_var( 'job_relist' ) )  ) {
			wp_die( __( 'Sorry, you do not have permission to relist this job.', APP_TD ) );
		}

		if ( ! jr_allow_relist() ) {
			appthemes_add_notice( 'no_relisting', __( 'Relisting is disabled.', APP_TD ) );
			redirect_myjobs();
		}
	}

	function parse_query( $wp_query ) {
		$job_id = $wp_query->get( 'job_relist' );

		$wp_query->set( 'job_id', $job_id );
	}

	function template_vars() {
		$default_template_vars = parent::template_vars();

		$job = $default_template_vars['job'];

		$params = array(
			'post_action' => 'relist-job',
		);

		$template_vars = array(
			'title'	 => sprintf( __( 'Relisting %s', APP_TD ), html_link( get_permalink( $job ), get_the_title( $job->ID ) ) ),
			'params' => wp_parse_args( $params, $default_template_vars['params'] ),
		);
		$template_vars = wp_parse_args( $template_vars, $default_template_vars );

		return apply_filters( 'jr_job_relist_template_vars', $template_vars );
	}

	function title_parts( $parts ) {
		return array( sprintf( __( 'Relisting "%s"', APP_TD ), get_the_title( get_query_var('job_id') ) ) );
	}

	function body_class($classes) {
		$classes[] = 'jr_job_relist';
		return $classes;
	}

}

class JR_Job_Edit extends JR_Job_Submit {

	/**
	 * Initializes class params.
	 *
	 * @return void
	 */
	public function init() {
		global $wp;

		$wp->add_query_var( 'job_edit' );
	}

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		return false !== get_query_var( 'job_edit', false );
	}

	/**
	 * Fires before determining which template to load.
	 *
	 * @return void
	 */
	function template_redirect() {
		parent::template_redirect();

		if ( ! get_query_var( 'job_edit' ) ) {
			wp_die( __( 'Invalid job ID.', APP_TD ) );
		}

		if ( ! current_user_can( 'edit_job', (int) get_query_var( 'job_edit' ) ) ) {

			if ( ! jr_allow_editing() ) {
				wp_die( __( 'Sorry, job editing is not allowed.', APP_TD ) );
			} else {
				wp_die( __( 'Sorry, you do not have permission to edit this job.', APP_TD ) );
			}

		}
	}

	function parse_query( $wp_query ) {
		$job_id = $wp_query->get( 'job_edit' );

		$wp_query->set( 'job_id', $job_id );
	}

	function template_vars() {

		// retrieve the job details for new / edit / relist
		$job = jr_get_default_job_to_edit();

		if ( ! $job->ID ) {
			wp_redirect( home_url() );
			exit();
		}

		$params = array(
			'step'			=> 1,
			'job'			=> $job,
			'order_id'		=> 0,
			'post_action'	=> 'edit-job',
			'form_action'	=> $_SERVER['REQUEST_URI'],
			'submit_text' 	=> __( 'Save &rarr;' , APP_TD ),
		);

		$template_vars = array(
			'job'	 => $job,
			'params' => $params,
		);

		return apply_filters( 'jr_job_edit_template_vars', $template_vars );
	}

	function title_parts( $parts ) {
		return array( sprintf( __( 'Editing "%s"', APP_TD ), get_the_title( get_queried_object_id() ) ) );
	}

}

class JR_Packs_Purchase extends APP_View {

	/**
	 * Initializes class params.
	 *
	 * @return void
	 */
	public function init() {
		global $wp;

		$wp->add_query_var( 'job_id' );
		$wp->add_query_var( 'order_id' );
		$wp->add_query_var( 'step' );
		$wp->add_query_var( 'plan_type' );
	}

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		global $wp_query;
		return isset( $wp_query->queried_object ) && is_page( JR_Packs_Purchase_Page::get_id() );
	}

	function parse_query( $wp_query ) {

		$wp_query->set( 'plan_type', APPTHEMES_PRICE_PLAN_PTYPE );

		if ( ! current_user_can( 'can_submit_job' ) ) {
			wp_redirect( wp_login_url( get_permalink( JR_Packs_Purchase_Page::get_id() ) ) );
			exit();
		}

	}

	function template_vars() {

		$step = jr_get_next_step( $start = 1 );

		$steps = _jr_select_plans_steps();

		$this->step_name = $steps[ $step ]['name'];

		$args['meta_query'] = array(
			array(
				'key' 		=> JR_FIELD_PREFIX.'price',
				'value' 	=> 0,
				'compare' 	=> '>=',
			),
		);

		$plans = jr_get_available_plans( $args );

		$params = array(
			'step'		=> jr_get_next_step( $start = 1 ),
			'order_id' 	=> get_query_var('order_id'),
		);

		$template_vars = array(
			'params'		=> $params,
			'steps_trail'	=> jr_get_steps_trail( $steps, $step ),
			'template'		=> '/includes/forms/lister-packs/lister-packs-form.php',
			'plans'			=> $plans,
		);

		return apply_filters( 'jr_packs_purchase_template_vars', $template_vars );
	}

	function title_parts( $parts ) {
		return array( __( 'Purchasing Job Pack Plan', APP_TD ) );
	}
}

class JR_Resumes_Plans_Purchase extends APP_View {

	/**
	 * Initializes class params.
	 *
	 * @return void
	 */
	public function init() {
		global $wp;

		$wp->add_query_var( 'job_id' );
		$wp->add_query_var( 'order_id' );
		$wp->add_query_var( 'step' );
		$wp->add_query_var( 'plan_type' );
	}

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		global $wp_query;
		return isset( $wp_query->queried_object ) && is_page( JR_Resume_Plans_Purchase_Page::get_id() );
	}

	function parse_query( $wp_query ) {

		$wp_query->set( 'plan_type', APPTHEMES_RESUMES_PLAN_PTYPE );

		if ( ! jr_current_user_can_subscribe_for_resumes() ) {
			wp_redirect( home_url() );
			exit();
		}

		if ( jr_resume_valid_subscr() && ! jr_resume_valid_trial() ) {
			$errors = jr_get_listing_error_obj();
			$errors->add( 'resumes_error', __( 'You\'re already subscribed to Resumes.', APP_TD ) );
			return;
		}

	}

	function template_vars() {

		$step = jr_get_next_step( $start = 1 );

		$steps = _jr_select_plans_steps();

		$this->step_name = $steps[ $step ]['name'];

		$params = array(
			'step'		=> $step,
			'order_id' 	=> get_query_var('order_id'),
		);

		$template_vars = array(
			'params'		=> $params,
			'steps_trail'	=> jr_get_steps_trail( $steps, $step ),
			'template'		=> '/includes/forms/subscribe-resumes/subscribe-resumes-form.php',
		);

		return apply_filters( 'jr_resume_plans_purchase_template_vars', $template_vars );
	}

	function title_parts( $parts ) {
		return array( __( 'Subscribing to Resumes', APP_TD ) );
	}
}

class JR_Order_Go_Back extends APP_View {

	/**
	 * Initializes class params.
	 *
	 * @return void
	 */
	public function init() {
		global $wp;

		$wp->add_query_var( 'referer' );

		$this->handle_form();
	}

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		return (bool) ( ! empty( $_POST['action'] ) && $_POST['action'] == 'select-gateway' );
	}

	function handle_form() {

		if ( empty( $_POST['action'] ) || $_POST['action'] != 'select-gateway' || empty( $_POST['referer'] ) ) {
			return;
		}

		if ( empty( $_POST['step'] ) || empty( $_POST['order_id'] ) ) {
			return;
		}

		if ( ! empty( $_POST['goback'] ) && 'select-gateway' == $_POST['action'] ) {
			$args = array(
				'step'     => (int) ( $_POST['step'] - 1 ),
				'order_id' => (int) $_POST['order_id']
			);

			if ( ! empty( $_POST['ID'] ) ) {
				$args['job_id'] = (int) $_POST['ID'];

				if ( ! empty( $_POST['relist'] ) ) {
					$args['job_relist'] = $args['job_id'];
				}
			}

			$url = esc_url_raw( add_query_arg( $args, $_POST['referer'] ) );

			wp_safe_redirect( $url );
			exit();
		}

	}
}

class JR_Search extends APP_View {

	/**
	 * Initializes class params.
	 *
	 * @return void
	 */
	public function init() {
		global $wp;

		$wp->add_query_var( 'ptype' );
		$wp->add_query_var( 'radius' );
		$wp->add_query_var( 'location' );
		$wp->add_query_var( 'resume_search' );
	}

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		return ! is_admin() && ( is_search() || ! empty( $_GET['location'] ) || ! empty( $_GET['s'] ) );
	}

	function parse_query( $wp_query ) {

		$post_type = ! empty( $_GET['ptype'] ) && in_array( $_GET['ptype'], array( APP_POST_TYPE, APP_POST_TYPE_RESUME ) ) ? $_GET['ptype'] : APP_POST_TYPE;

		$wp_query->set( 'location', trim( get_query_var( 'location' ) ) );
		$wp_query->set( 'post_type', $post_type );

		$wp_query->is_home = false;
		$wp_query->is_archive = false;
		$wp_query->is_search = true;

		// @todo: replace with new Geolocation API
		$query_vars = JR_Search::get_location_search_query_vars();

		$query_vars = array_merge( $query_vars, JR_Jobs_Archive::jobs_filter_form( $query_vars ) );

		$wp_query->query_vars = array_merge( $wp_query->query_vars, $query_vars );
	}

	function template_vars() {
		global $jr_options;

		$post_type = get_query_var('ptype') ? get_query_var('ptype') : APP_POST_TYPE ;

		$post_type_obj = get_post_type_object( $post_type );

		$location_heading = $term_heading = '';

		$location = get_query_var('location') ? get_query_var('location') : ( ! empty( $_GET['location'] ) ? $_GET['location'] : '' );

		if ( $search = get_search_query() ) {
			$term_heading = sprintf( __( 'Searching %1$s for &ldquo;%2$s&rdquo;', APP_TD ), strtolower( $post_type_obj->labels->singular_name ), $search );
		} else {
			$term_heading = sprintf( __( 'Searching %s ', APP_TD ), strtolower( $post_type_obj->labels->singular_name ) );
		}

		if ( $location ) {
			$radius = get_query_var('radius');

			if ( 'km' == $jr_options->jr_distance_unit ) {
				$location_heading = sprintf( __( 'within %1$s kilometers of %2$s', APP_TD ), $radius, $location );
			} else {
				$location_heading = sprintf( __( 'within %1$s miles of %2$s', APP_TD ), $radius, $location );
			}

			$location_heading = $term_heading ? ' ' . $location_heading : $location_heading;

		}

		if ( ! $term_heading && ! $location_heading ) {
			$term_heading = __( 'Search Results', APP_TD );
		}

		$s_location = '';

		// sanitize searched location
		if ( ! empty( $_GET['location'] ) ) {
			$s_location = wp_strip_all_tags( $_GET['location'] );
			$s_location = urldecode( utf8_uri_encode( trim( $s_location ) ) );
		}

		$template_vars = array(
			's_location'		=> $s_location,
			'location_heading'	=> $location_heading,
			'term_heading'		=> $term_heading,
		);

		return apply_filters( 'jr_search_template_vars', $template_vars );
	}

	/**
	 * Determines which template to include.
	 *
	 * @param string $path The path of the template to include.
	 *
	 * @return string
	 */
	public function template_include( $path ) {

		// use the resumes search template on resumes search
		if ( ! empty( $_GET['resume_search'] ) ) {
			return appthemes_locate_template( 'search-resume.php' );
		}

		return $path;
	}

	/**
	 * Fires before determining which template to load.
	 *
	 * @return void
	 */
	function template_redirect() {

		if ( get_query_var( 'resume_search' ) ) {
			jr_resume_page_auth();

			if ( ! jr_resume_is_visible() ) {
				wp_redirect( get_post_type_archive_link( APP_POST_TYPE_RESUME ) );
				exit;
			}
		}
	}

	/**
	 * Does location search if applicable and retrieves query vars to be used on the main 'wp_query'.
	 */
	public static function get_location_search_query_vars() {
		$args = $find_posts_in = array();

		$radius = isset( $_GET['radius'] ) ? absint( $_GET['radius'] ) : 0;

		if ( !empty( $_GET['location'] ) ) {

			$location = wp_strip_all_tags( $_GET['location'] );
			$location = urldecode( utf8_uri_encode( trim( $location ) ) );

			// Get address from post data
			$address_array = '';

			if ( !empty( $_REQUEST['latitude'] ) && !empty( $_REQUEST['longitude'] ) && !empty( $_REQUEST['full_address'] ) ) {
				$address_array = array(
					'north_east_lng' => trim( stripslashes( $_REQUEST['north_east_lng'] ) ),
					'south_west_lng' => trim( stripslashes( $_REQUEST['south_west_lng'] ) ),
					'north_east_lat' => trim( stripslashes( $_REQUEST['north_east_lat'] ) ),
					'south_west_lat' => trim( stripslashes( $_REQUEST['south_west_lat'] ) ),
					'longitude' => trim( stripslashes( $_REQUEST['longitude'] ) ),
					'latitude' => trim( stripslashes( $_REQUEST['latitude'] ) ),
					'full_address' => trim( stripslashes( $_REQUEST['full_address'] ) )
				);
			}

			$find_posts_in = array();

			// Do radial search
			$radial_result = jr_radial_search( $location, $radius, $address_array );

			if ( is_array( $radial_result ) ) {
				if ( $radial_result['address'] ) {
					$location = $radial_result['address'];
				}
				$find_posts_in = $radial_result['posts'];
				$radius = $radial_result['radius'];
			}

			if ( !$radius ) {
				$radius = 50;
			}

			$args['radius'] = $radius;
			$args['location'] = $location;
			$args['post__in'] = $find_posts_in;
		}

		return $args;
	}

	// Show jobs archive as parent
	function breadcrumbs( $trail ) {

		if ( ! get_query_var('location') ) {
			return $trail;
		}

		$new_trail = array( $trail[0] );
		$new_trail[] = sprintf( __( 'Search results for "%1$s" in "%2$s"', APP_TD ), get_search_query(), get_query_var('location') );

		return $new_trail;
	}
}

class JR_Jobs_Archive extends APP_View {

	/**
	 * Initializes class params.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'pre_get_posts', array( $this, 'jobs_frontpage' ) );

		// @todo: use with new Geolocation API
		// add_action( 'posts_clauses', array( $this, '_posts_clauses' ), 99, 2 );
	}

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		$taxonomies = array( APP_TAX_CAT, APP_TAX_TAG, APP_TAX_SALARY, APP_TAX_TYPE );
		return ( ! is_admin() && ( is_post_type_archive( APP_POST_TYPE ) || is_tax( $taxonomies ) ) );
	}

	function parse_query( $wp_query ) {
		$wp_query->set( 'post_type', APP_POST_TYPE );
		$wp_query->set( 'posts_per_page', jr_get_jobs_per_page() );

		// @todo: replace with new Geolocation API
		$query_vars = JR_Search::get_location_search_query_vars();

		$query_vars = array_merge( $query_vars, self::jobs_filter_form( $query_vars ) );

		$wp_query->query_vars = array_merge( $wp_query->query_vars, $query_vars );
	}

	function template_vars() {
		$template_vars = array(
			'tax' => get_queried_object(),
		);

		return apply_filters( 'jr_jobs_archive_template_vars', $template_vars );
	}

	/**
	 * Determines which template to include.
	 *
	 * @param string $path The path of the template to include.
	 *
	 * @return string
	 */
	public function template_include( $path ) {
		if ( is_tax() ) {
			return appthemes_locate_template( 'taxonomy-job.php' );
		}

		return $path;
	}

	public function jobs_frontpage( $wp_query ) {

		if ( ! empty( $wp_query->query_vars['is_jobs_frontpage_archive'] ) ) {
			$this->parse_query( $wp_query );
		}

	}

	public static function jobs_filter_form( $args = array() ) {
		global $jr_options;

		$defaults = array(
			'post_status' => array( 'publish' ),
		);
		$args = wp_parse_args( $args, $defaults );

		$action = $jr_options->jr_expired_action;

		if ( 'hide' != $action && ! is_search() ) {
			$args['post_status'][] = 'expired';
			$args['meta_query'] = array(
				array(
					'key' 	=> '_jr_canceled_job',
					'compare' => 'NOT EXISTS',
				),
			);
		}

		$cats = array();
		$filter_args = array();

		if ( ! empty( $args['post__in'] ) ) {
			$find_posts_in = $args['post__in'];
		}

		if ( isset( $_GET['action'] ) && 'Filter' == $_GET['action'] ) {

			$job_types = get_terms( APP_TAX_TYPE, array( 'hide_empty' => '0' ) );
			if ( $job_types && sizeof( $job_types ) > 0 ) {

				foreach ( $job_types as $type ) {
					if ( ! empty( $_GET[$type->slug] ) ) {
						// Filter is ON
						$cats[] = $type->term_id;
					}
				}

			}

			if ( sizeof( $cats ) == 0 ) {
				$cats = array(0);
			}

			$post_ids = get_objects_in_term( $cats, APP_TAX_TYPE );

			// If we are doing location search, find common ids
			if ( ! empty( $find_posts_in ) ) {
				$post_ids = array_intersect( $post_ids, $find_posts_in );
			}

			$args['post__in'] = $post_ids;
		}

		// force zero results if the location did not returned any results and the search query is empty
		if ( ! get_search_query() && get_query_var('location') && empty( $args['post__in'] ) ) {
			$args['p'] = -1;
		}

		return $args;
	}

	// @todo: use with 'app-geo-query'
	function _posts_clauses( $clauses, $wp_query ) {

		$geo_query = $wp_query->get( 'app_geo_query' );

		if ( ! $geo_query ) {
			return $clauses;
		}

		$count = 1;

		$clauses['join'] = str_replace( 'INNER JOIN', 'LEFT JOIN', $clauses['join'], $count );
		$clauses['where'] = str_replace( 'AND distance', 'AND ( distance ', $clauses['where'] );
		$clauses['where'] .= ' OR distances.post_id is null )';

		return $clauses;
	}

	// Show jobs archive as parent
	function breadcrumbs( $trail ) {

		if ( is_post_type_archive() && ! is_tax() ) {
			return $trail;
		}

		$trail_end = array_splice( $trail, 1 );
		$new_trail[0] = $trail[0];
		$new_trail[1] = html_link( get_post_type_archive_link( APP_POST_TYPE ), __( 'Jobs', APP_TD ) );
		$new_trail = array_merge( $new_trail, $trail_end );

		return $new_trail;
	}

}

class JR_Resumes_Archive extends APP_View {

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		$taxonomies = array( APP_TAX_RESUME_SPECIALITIES, APP_TAX_RESUME_GROUPS, APP_TAX_RESUME_LANGUAGES, APP_TAX_RESUME_CATEGORY, APP_TAX_RESUME_JOB_TYPE );
		return ( ! is_admin() && ( is_post_type_archive( APP_POST_TYPE_RESUME ) || is_tax( $taxonomies ) || ( is_search() && get_query_var( 'resume_search' ) ) ) );
	}

	function parse_query( $wp_query ) {
		$wp_query->set( 'post_type', APP_POST_TYPE_RESUME );
		$wp_query->set( 'posts_per_page', jr_get_resumes_per_page() );

		// @todo: replace with new Geolocation API
		$query_vars = JR_Search::get_location_search_query_vars();

		$wp_query->query_vars = array_merge( $wp_query->query_vars, $query_vars );
	}

	/**
	 * Fires before determining which template to load.
	 *
	 * @return void
	 */
	function template_redirect() {
		jr_resume_page_auth();
	}

	function template_vars() {

		$template_vars = array(
			'filter_text' => $this->get_filter_text(),
		);

		return apply_filters( 'jr_resumes_archive', $template_vars );
	}

	function get_filter_text() {

		if ( ! ( $taxonomy = get_query_var( 'taxonomy' ) ) || ! ( $term = get_query_var( 'term' ) ) ) {
			return;
		}

		$term = get_term_by( 'slug', $term, $taxonomy );

		$text = array(
			APP_TAX_RESUME_CATEGORY		=> sprintf( __( ' in the %s category.', APP_TD ), $term->name ),
			APP_TAX_RESUME_LANGUAGES	=> sprintf( __( ' of people who speak %s.', APP_TD ), $term->name ),
			APP_TAX_RESUME_GROUPS		=> sprintf( __( ' of members of %s.', APP_TD ), $term->name ),
			APP_TAX_RESUME_SPECIALITIES => sprintf( __( ' of people specialising in %s.', APP_TD ), $term->name ),
			APP_TAX_RESUME_JOB_TYPE		=> sprintf( __( ' of people wanting a %s job.', APP_TD ), $term->name ),
		);

		if ( ! empty( $text[ $taxonomy ] ) ) {
			return $text[ $taxonomy ];
		}
		return;
	}

}

class JR_Job_Single extends APP_View {

	/**
	 * Initializes class params.
	 *
	 * @return void
	 */
	public function init() {
		global $wp;

		$wp->add_query_var( 'updated' );
		$wp->add_query_var( 'star' );
	}

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		return is_singular( APP_POST_TYPE );
	}

	/**
	 * Displays notices.
	 *
	 * @return void
	 */
	public function notices() {
		global $jr_options;

		$post = get_queried_object();
		$status = get_post_status( $post );

		switch( $status ){
			case 'pending' :
				appthemes_display_notice( 'success-pending', __( 'This job is currently pending and must be approved by an administrator.', APP_TD ) );
				break;
			case 'draft' :
				appthemes_display_notice( 'draft-pending', __( 'This is a draft job and must be approved by an administrator.', APP_TD ) );
				break;
			case 'expired' :
				appthemes_display_notice( 'notice', __( 'This job listing has expired and may no longer be relevant!', APP_TD ) );
				break;
		}

		switch( get_query_var( 'updated' ) ){
			case 1 :
				appthemes_display_notice( 'success', __( 'The job has been successfully updated.', APP_TD ) );
				break;
		}

		switch ( get_query_var( 'star' ) ) {
			case 'true' :
				appthemes_display_notice( 'success', __( 'The job has was Starred.', APP_TD ) );
				break;
			case 'false' :
				appthemes_display_notice( 'success', __( 'The job has was un-Starred.', APP_TD ) );
				break;
		}

		if ( ! is_user_logged_in() && 'publish' === $status && $jr_options->apply_reg_users_only ) {
			$register_link = wp_login_url( get_permalink() );
			appthemes_display_notice( 'notice', sprintf( __( 'Please <a href="%s">login/register</a> to apply for this job.', APP_TD ), esc_url( $register_link ) ) );
		}

	}

	// Show parent categories instead of listing archive
	function breadcrumbs( $trail ) {
		$cat = get_the_terms( get_queried_object_id(), APP_TAX_CAT );

		if ( ! $cat ) {
			return $trail;
		}

		$cat = array_pop( $cat );
		$cat = (int) $cat->term_id;
		$chain = array_reverse( get_ancestors( $cat, APP_TAX_CAT ) );
		$chain[] = $cat;

		$new_trail = array( $trail[0], $trail[1] );

		foreach ( $chain as $cat ) {
			$cat_obj = get_term( $cat, APP_TAX_CAT );
			$new_trail[] = html_link( get_term_link( $cat_obj ), $cat_obj->name );
		}

		$new_trail[] = array_pop( $trail );

		return $new_trail;
	}

	/**
	 * Fires before determining which template to load.
	 *
	 * @return void
	 */
	function template_redirect() {
		add_action( 'wp_footer', array( $this, 'script_init' ), 99 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_js_styles' ) );
	}

	public function enqueue_js_styles() {

		// Enqueue the reCaptcha library.
		if ( current_theme_supports( 'app-recaptcha-application' ) ) {
			wp_enqueue_script( APP_Recaptcha::JS_HANDLE );
		}
	}

	public function script_init() {
		jr_geolocation_scripts();
?>
		<script type="text/javascript">
			/* <![CDATA[ */
			jQuery(document).ready(function($) {

				if ( $('.notice.error').html() != undefined && $('.apply_online').html() != undefined ) {
					$('html, body').animate({ scrollTop: $(".notice.error").offset().top }, "slow");
				}

			});
			/* ]]> */
		</script>
<?php
	}
}

class JR_Dashboard extends APP_View {

	/**
	 * Initializes class params.
	 *
	 * @return void
	 */
	public function init() {
		global $wp;

		$wp->add_query_var( 'tab' );
		$wp->add_query_var( 'order_cancel' );
		$wp->add_query_var( 'job_end' );
		$wp->add_query_var( 'cancel' );
		$wp->add_query_var( 'confirm' );
		$wp->add_query_var( 'confirm_order_cancel' );
		$wp->add_query_var( 'order_status' );
	}

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		global $wp_query;
		// @todo: use 'dashboard' rewrite rule
		return isset( $wp_query->queried_object ) && is_page( JR_Dashboard_Page::get_id() );
	}

	/**
	 * Determines which template to include.
	 *
	 * @param string $path The path of the template to include.
	 *
	 * @return string
	 */
	public function template_include( $path ) {
		if ( ! is_user_logged_in() || ( ! current_user_can( 'can_submit_resume' ) && ! current_user_can( 'can_submit_job' ) ) ) {
			appthemes_add_notice( 'no-access', __( 'You don\'t have permissions to access the Dashboard.<br />You must be a Job Lister or Job Seeker. Please contact the site owner for help.', APP_TD ) );
			return appthemes_locate_template( '404.php' );
		}

		return $path;
	}

	function template_vars() {

		$subscr_duration = jr_get_recurring_subscr_duration();

		if ( _jr_is_recurring_available() ) {
			$duration_text = __( 'Recurs every', APP_TD );
		} else {
			$duration_text = __( 'Duration', APP_TD );
		}
		$subsc_duration_text = sprintf( __( '%1$s <strong>%2$d</strong> %3$s', APP_TD ), $duration_text, $subscr_duration, _n( 'day', 'days', $subscr_duration ) );

		$template_vars = array(
			'active_subscription'	=> jr_resume_valid_subscr(),
			'resumes_access'		=> jr_user_resumes_access(),
			'valid_trial'			=> jr_resume_valid_trial(),
			'orders'				=> $this->get_orders(),
			'subscr_duration'		=> $subscr_duration,
			'subscr_duration_text'	=> $subsc_duration_text,
		);

		return apply_filters( 'jr_lister_dashboard_template_vars', $template_vars );
	}

	function get_orders() {

		$paged = 1;

		if ( get_query_var('tab') && 'orders' == get_query_var('tab') ) {
			$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
		}

		$args = array(
			'ignore_sticky_posts'	=> true,
			'author' 				=> get_current_user_id(),
			'post_type' 			=> APPTHEMES_ORDER_PTYPE,
			'posts_per_page' 		=> 15,
			'paged' 				=> $paged,
		);

		if ( get_query_var('order_status') ) {
			$args['post_status'] = get_query_var('order_status');
		}

		return new WP_Query($args);
	}

}

class JR_Lister_Dashboard extends JR_Dashboard {

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		return parent::condition() && current_user_can( 'can_submit_job' );
	}

	function parse_query( $wp_query ) {

		if ( ! current_user_can( 'can_submit_job' ) ) {
			wp_redirect( home_url() );
			exit();
		}

		if ( get_query_var( 'order_cancel' ) || get_query_var( 'order_status' ) ) {
			$wp_query->set( 'tab', 'orders' );
		}

		if ( get_query_var( 'order_status' ) ) {
			$wp_query->set( 'order_status', array_map( 'wp_strip_all_tags', get_query_var( 'order_status' ) ) );
		}

		if ( get_query_var( 'order_cancel' ) ) {

			$order = appthemes_get_order( intval( get_query_var( 'order_cancel' ) ) );

			if ( get_current_user_id() != $order->get_author() ) {
				$wp_query->set( 'order_cancel_msg', -1 );
				return;
			}

			if ( APPTHEMES_ORDER_COMPLETED == $order->get_status() ) {
				$wp_query->set( 'order_cancel_msg', -2 );
				return;
			}

			if ( !empty($order) && get_query_var( 'confirm_order_cancel' ) ) {
				$order->failed();
				$wp_query->set( 'order_cancel_success', 1 );
			}

		} elseif ( get_query_var( 'job_end' ) && get_query_var( 'confirm' ) ) {

				$job_id = intval( get_query_var( 'job_end' ) );
				$job = get_post( $job_id );

				if ( $job->ID != $job_id || $job->post_author != get_current_user_id() ) :
					$wp_query->set( 'job_action', -1 );
					return;
				endif;

				if ( get_query_var( 'cancel' ) ) {
					$order = appthemes_get_order_connected_to( $job_id );
					if ( $order && ! in_array( $order->get_status(), array( APPTHEMES_ORDER_ACTIVATED, APPTHEMES_ORDER_COMPLETED ) ) ) {
						$order->failed(); // job will be canceled with the order
					} else{
						_jr_end_job( $job_id, $cancel = true );
					}

					$wp_query->set( 'job_action', 1 );
				} else {
					_jr_end_job( $job_id );

					$wp_query->set( 'job_action', 2 );
				}
		}

	}

	/**
	 * Fires before determining which template to load.
	 *
	 * @return void
	 */
	function template_redirect() {
		if ( ! current_user_can( 'can_submit_job' ) ) {
			redirect_profile();
		}

		add_action( 'wp_footer', array( $this, 'script_init' ), 99 );
	}

	function template_vars() {
		global $userdata, $user_ID, $jr_options;

		$can_subscribe = jr_current_user_can_subscribe_for_resumes();

		$template_vars = array(
			'user_ID'				=> $user_ID,
			'userdata'				=> $userdata,
			'pending_payment_jobs'	=> _jr_pending_payment_jobs_for_user( $user_ID ),
			'can_subscribe'			=> $can_subscribe,
			'can_edit'				=> jr_allow_editing(),
			'show_orders'			=> jr_charge_job_listings() || $can_subscribe || jr_get_user_orders_count() > 0,
			'buy_packs'				=> 'pack' == $jr_options->plan_type && jr_charge_job_listings(),
			'active_subscription'	=> jr_resume_valid_subscr(),
			'resumes_access'		=> jr_user_resumes_access(),
			'valid_trial'			=> jr_resume_valid_trial(),
		);

		return apply_filters( 'jr_lister_dashboard_template_vars', $template_vars );
	}

	/**
	 * Displays notices.
	 *
	 * @return void
	 */
	public function notices() {
		switch( get_query_var( 'order_cancel_success' ) ) {
			case 1 :
				appthemes_display_notice( 'success', __( 'The Order was successfully canceled.', APP_TD ) );
				break;
			case -1:
				appthemes_display_notice( 'error', __( 'You do not have permission to cancel this Order.', APP_TD ) );
				break;
			case -2:
				appthemes_display_notice( 'error',  __( 'This Order cannot be canceled. It\'s already completed..', APP_TD ) );
				break;
		}

		switch( get_query_var( 'job_action' ) ) {
			case -1 :
				appthemes_display_notice( 'error', __( 'Invalid action.', APP_TD ) );
				break;
			case 1 :
				appthemes_display_notice( 'success', __( 'Job listing was successfully canceled.', APP_TD ) );
				break;
			case 2 :
				appthemes_display_notice( 'success', __( 'Job listing was ended early.', APP_TD ) );
				break;
		}
	}

	public function script_init() {
	?>
		<script type="text/javascript">
		/* <![CDATA[ */
			jQuery(function() {

				jQuery('a.delete').click(function(){
					var answer = confirm("<?php _e('Are you sure you want to cancel this job listing? This action cannot be undone.', APP_TD); ?>")
					if (answer){
						jQuery(this).attr('href', jQuery(this).attr('href') + '&confirm=true');
						return true;
					}
					else{
						return false;
					}
				});

				jQuery('a.end').click(function(){
					var answer = confirm("<?php _e('Are you sure you want to expire this job listing? This action cannot be undone.', APP_TD); ?>")
					if (answer){
						jQuery(this).attr('href', jQuery(this).attr('href') + '&confirm=true');
						return true;
					}
					else{
						return false;
					}
				});

				jQuery('a.order-cancel-link').click(function(){
					var answer = confirm("<?php _e( 'Are you sure you want to cancel this Order? This action cannot be undone.', APP_TD ); ?>")
					if (answer){
						jQuery(this).attr('href', jQuery(this).attr('href') + '&confirm_order_cancel=true');
						return true;
					}
					else{
						return false;
					}
				});

				jQuery('.myjobs ul.display_section li a').click(function(){
					jQuery('.myjobs div.myjobs_section').hide();
					jQuery(jQuery(this).attr('href')).show();
					jQuery('.myjobs ul.display_section li').removeClass('active');
					jQuery(this).parent().addClass('active');
					return false;
				});
				jQuery('.myjobs ul.display_section li a:eq(0)').click();

				// trigger the selected tab
				<?php if ( get_query_var('tab') ): ?>
						jQuery('.myjobs ul.display_section li a[href="#<?php echo get_query_var('tab'); ?>"]').trigger('click');
				<?php endif; ?>

			});
		/* ]]> */
		</script>
	<?php
	}

}

class JR_Contact extends APP_View {

	/**
	 * Initializes class params.
	 *
	 * @return void
	 */
	public function init() {
		global $wp;

		$wp->add_query_var( 'contact_form' );
		$wp->add_query_var( 'contact_success' );
	}

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		global $wp_query;
		return isset( $wp_query->queried_object ) && is_page( JR_Contact_Page::get_id() );
	}

	function parse_query( $wp_query ) {

		$errors = $this->error_obj();

		// Form Processing Script
		if ( isset( $_POST['submit-form'] ) ) {

			$data = $this->validate_data();
			if ( ! is_wp_error( $data ) ) {
				$result = jr_contact_site_email( $data );
				if ( ! is_wp_error( $data ) ) {
					$wp_query->set( 'contact_success', 1 );
				}
			}

		}

	}

	function template_vars() {

		$loc_keys = array(
			'your_name' => '',
			'email'		=> '',
			'message'	=> '',
			'honeypot'	=> '',
		);

		$posted = $loc_keys;

		if ( ! empty( $_POST['submit-form'] ) && ! get_query_var('contact_success') ) {

			foreach( $loc_keys as $key => $value ) {
				if ( empty( $_POST[ $key ] ) ) {
					continue;
				}
				$posted[ $key ] = $_POST[ $key ];
			}

			$posted = stripslashes_deep( $posted );
		}

		$template_vars = array(
			'posted' => $posted,
		);

		return apply_filters( 'jr_contact_template_vars', $template_vars );
	}

	/**
	 * Fires before determining which template to load.
	 *
	 * @return void
	 */
	function template_redirect() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_js_styles' ) );
		add_action( 'wp_footer', array( $this, 'print_js_styles' ) );
	}

	public function enqueue_js_styles() {
		wp_enqueue_script( 'validate' );

		// Enqueue the reCaptcha library.
		if ( current_theme_supports( 'app-recaptcha-contact' ) ) {
			wp_enqueue_script( APP_Recaptcha::JS_HANDLE );
		}
	}

	public function print_js_styles() {
?>
		<script type="text/javascript">
		/* <![CDATA[ */
			jQuery('#contact_form.main_form').validate({
				rules: {
				  // simple rule, converted to {required:true}
				  name: "required",
				  // compound rule
				  email: {
					required: true,
					email: true
				  }
				}
			  }
			);
		/* ]]> */
		</script>
<?php
	}

	function validate_data() {

		$errors = $this->error_obj();

		$required = array('your_name', 'email', 'message');

		// Identify exploits
		$inpt_expl = "/(content-type|to:|bcc:|cc:|document.cookie|document.write|onclick|onload)/i";

		// Get post data
		$posted = array();

		$posted['your_name'] = $_POST['your_name'];
		$posted['email'] = $_POST['email'];
		$posted['message'] = $_POST['message'];
		$posted['spam-trap'] = $_POST['honeypot'];

		$loc_keys = array(
			'your_name' => __( 'Name', APP_TD ),
			'email' => __( 'Email', APP_TD ),
			'message' => __( 'Message', APP_TD ),
			'spam-trap' => __( 'Spam-Trap', APP_TD ),
		);

		// Clean post data & validate fields
		foreach ( $posted as $key => $val ) {
			$val = strip_tags( stripslashes( trim( $val ) ) );

			if ( in_array( $key, $required ) ) {
				if ( empty($val) ) $errors->add( 'submit_error_'.$key, sprintf( __('Required field "%s" missing', APP_TD ), $loc_keys[$key] ) );
			}

			if ( $key == 'email' && !empty( $val ) ) {

				if ( ! is_email( $posted['email'] ) ) {
					$errors->add( 'submit_error_invalid_email', __( 'Invalid email address.', APP_TD ) );
				}
			}

			if ( ! empty( $posted['spam-trap'] ) ) {
				$errors->add( 'submit_error_spam', __( 'Possible spam: You filled the honeypot spam-trap field!', APP_TD ) );
			}

			if ( preg_match( $inpt_expl, $val ) ) {
				$errors->add( 'submit_error_exploit', __( 'Injection Exploit Detected: It seems that you&#8217;re possibly trying to apply a header or input injection exploit in our form. If you are, please stop at once! If not, please go back and check to make sure you haven&#8217;t entered <strong>content-type</strong>, <strong>to:</strong>, <strong>bcc:</strong>, <strong>cc:</strong>, <strong>document.cookie</strong>, <strong>document.write</strong>, <strong>onclick</strong>, or <strong>onload</strong> in any of the form inputs. If you have and you&#8217;re trying to send a legitimate message, for security reasons, please find another way of communicating these terms.', APP_TD ) );
			}
		}

		// process the reCaptcha request if it's been enabled
		$errors_recaptcha = jr_validate_recaptcha('app-recaptcha-contact');
		if ( $errors_recaptcha && sizeof($errors_recaptcha)>0 ) {
			$errors->errors = array_merge( $errors->errors, $errors_recaptcha->errors );
		}

		if ( $errors->get_error_code() ) {
			return $errors;
		}
		return $posted;
	}

	function error_obj(){
		static $errors;

		if ( ! $errors ){
			$errors = new WP_Error();
		}
		return $errors;
	}

	/**
	 * Displays notices.
	 *
	 * @return void
	 */
	public function notices() {
		$errors = $this->error_obj();
		if ( $errors->get_error_code() ) {
			jr_show_errors( $errors );
		} elseif( get_query_var( 'contact_success' ) ) {
			appthemes_display_notice( 'success', __( 'Thank you. Your message has been sent.', APP_TD ) );
		}
	}
}

/**
 * Processes order pages and locates templates for the page being displayed
 * Temporary patch to keep compatibility with Payments before switching to Dynamic Checkout module.
 */
class JR_Order_Summary extends APP_View {

	public function __construct() {
		add_filter( 'appthemes_disable_order_summary_template', '__return_true' );
		parent::__construct();
	}

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		return is_singular( APPTHEMES_ORDER_PTYPE );
	}

	/**
	 * Determines which template to include.
	 *
	 * @param string $path The path of the template to include.
	 *
	 * @return string
	 */
	public function template_include( $path ) {

		$order = get_order();

		$currentuser = wp_get_current_user();
		if ( $order->get_author() != $currentuser->ID ) {
			return appthemes_locate_template( '404.php' );
		}

		if ( apply_filters( 'appthemes_order_summary_skip_checks', false ) == true ) {
			return appthemes_locate_template( 'order-checkout.php' );
		}

		if ( $order->get_total() == 0 ) {

			if ( count( $order->get_items() ) > 0 && $order->get_status() != APPTHEMES_ORDER_ACTIVATED ) {
				$order->complete();
			}

			return appthemes_locate_template( 'order-summary.php' );
		}

		if ( ! in_array( $order->get_status(), array( APPTHEMES_ORDER_PENDING, APPTHEMES_ORDER_FAILED ) ) ) {
			return appthemes_locate_template( 'order-summary.php' );
		}

		$gateway = $this->resolve_gateway( $order );
		if ( empty( $gateway ) ) {
			return appthemes_locate_template( 'order-checkout.php' );
		} else {
			return appthemes_locate_template( 'order-gateway.php' );
		}

	}

	function resolve_gateway( $order ) {

		if ( isset( $_GET['cancel'] ) ) {
			$order->clear_gateway();
		}

		$gateway = $order->get_gateway();
		if ( ! empty( $_POST['payment_gateway'] ) && empty( $gateway ) ) {
			$order->set_gateway( $_POST['payment_gateway'] );
		}

		return $order->get_gateway();

	}

}

/**
 * Resume Edit view.
 */
class JR_Resume_Edit extends APP_View {

	/**
	 * Initializes class params.
	 *
	 * @return void
	 */
	public function init() {
		global $wp;

		$wp->add_query_var( 'edit' );
	}

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		global $wp_query;
		return isset( $wp_query->queried_object ) && is_page( JR_Resume_Edit_Page::get_id() ) && get_query_var( 'edit' );
	}

	/**
	 * Fires before determining which template to load.
	 *
	 * @return void
	 */
	function template_redirect() {
		### Prevent Caching
		nocache_headers();
		appthemes_auth_redirect_login();

		$dashboard_url = get_permalink( JR_Dashboard_Page::get_id() );

		if ( ! get_query_var( 'edit' ) ) {
			appthemes_add_notice( 'edit-invalid-id', __( 'You can not edit this resume. Invalid resume ID.', APP_TD ), 'error' );
			wp_redirect( $dashboard_url );
			exit();
		}

		if ( get_query_var( 'edit' ) != appthemes_numbers_only( get_query_var( 'edit' ) ) ) {
			appthemes_add_notice( 'edit-invalid-id', __( 'You can not edit this resume. Invalid resume ID.', APP_TD ), 'error' );
			wp_redirect( $dashboard_url );
			exit();
		}

		$post = get_post( get_query_var( 'edit' ) );
		if ( ! $post ) {
			appthemes_add_notice( 'edit-invalid-id', __( 'You can not edit this resume. Invalid resume ID.', APP_TD ), 'error' );
			wp_redirect( $dashboard_url );
			exit();
		}

		if ( $post->post_type != APP_POST_TYPE_RESUME ) {
			appthemes_add_notice( 'edit-invalid-type', __( 'You can not edit this resume. This is not an resume.', APP_TD ), 'error' );
			wp_redirect( $dashboard_url );
			exit();
		}

		if ( ! current_user_can( 'edit_resume', (int) get_query_var( 'edit' ) ) ) {
			appthemes_add_notice( 'edit-invalid-permission', __( "You don't have permissions to edit resumes.", APP_TD ), 'error' );
			wp_redirect( $dashboard_url );
			exit();
		}

	}

	function template_vars() {
		$resume = jr_get_default_resume_to_edit();

		$template_vars = array(
			'resume' => $resume,
			'editing' => (bool) $resume->ID,
		);

		return apply_filters( 'jr_resume_edit_template_vars', $template_vars );
	}

}

/**
 * Resume Submit view.
 */
class JR_Resume_Submit extends APP_View {

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		global $wp_query;
		return isset( $wp_query->queried_object ) && is_page( JR_Resume_Edit_Page::get_id() ) && ! get_query_var( 'edit' );
	}

	/**
	 * Fires before determining which template to load.
	 *
	 * @return void
	 */
	function template_redirect() {
		### Prevent Caching
		nocache_headers();
		appthemes_auth_redirect_login();

		$dashboard_url = get_permalink( JR_Dashboard_Page::get_id() );

		if ( ! current_user_can( 'can_submit_resume' ) ) {
			appthemes_add_notice( 'edit-invalid-permission', __( "You don't have permissions to submit resumes.", APP_TD ), 'error' );
			wp_redirect( $dashboard_url );
			exit();
		}

	}

	function template_vars() {
		$resume = jr_get_default_resume_to_edit();

		$template_vars = array(
			'resume' => $resume,
			'editing' => (bool) $resume->ID,
		);

		return apply_filters( 'jr_resume_submit_template_vars', $template_vars );
	}

}


class JR_Single extends APP_View {

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		return is_singular( 'post' );
	}

	/**
	 * Fires before determining which template to load.
	 *
	 * @return void
	 */
	function template_redirect() {
		global $jr_options;

		### Disabled blog check
		if ( $jr_options->jr_disable_blog ) {
			wp_redirect( home_url() );
			exit;
		}
	}
}

class JR_Resume_Single extends APP_View {

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		return is_singular( APP_POST_TYPE_RESUME );
	}

	/**
	 * Fires before determining which template to load.
	 *
	 * @return void
	 */
	function template_redirect() {
		if ( ! $this->pre_load() ) {
			return;
		}

		add_action( 'wp_footer', array( $this, 'script_init' ), 99 );
	}

	function template_vars() {
		global $post, $userdata;

		$access_level = 'all';

		if ( ! current_user_can( 'view_resume', $post->ID ) ) {

			if ( jr_current_user_can_subscribe_for_resumes() ) {
				$access_level = 'subscribe';
			} else {
				$access_level = 'none';
			}

		}

		$template_vars = array(
			'userdata' => $userdata,
			'resume_access_level' => $access_level,
		);

		return apply_filters( 'jr_single_resume_template_vars', $template_vars );
	}

	function pre_load() {
		global $post;

		if ( ! empty( $_GET['publish'] ) && $post->post_author == get_current_user_id() ) {

			$post_id = $post->ID;
			$post_to_edit = get_post( $post_id );

			if ( $post_to_edit->ID == $post_id && $post_to_edit->post_author == get_current_user_id() ) {
				$update_resume = array();
				$update_resume['ID'] = $post_to_edit->ID;
				if ( 'private' == $post_to_edit->post_status ) {
					$update_resume['post_status'] = 'publish';
				} else {
					$update_resume['post_status'] = 'private';
				}

				appthemes_add_notice( 'resume-visibility-change', sprintf( __( 'Your Resume is now %s.', APP_TD ), 'publish' == $update_resume['post_status'] ? __( 'Visible', APP_TD ) : __( 'Hidden', APP_TD ) ), 'success' );

				wp_update_post( $update_resume );
				wp_safe_redirect( get_permalink( $post_to_edit->ID ) );
				exit;
			}

		}
		return true;
	}

	/**
	 * Displays notices.
	 *
	 * @return void
	 */
	public function notices() {
		$post = get_queried_object();
		$status = get_post_status( $post );

		if ( ! current_user_can( 'view_resume', $post->ID ) ) {
			appthemes_display_notice( 'error', __( 'Sorry, you do not have permission to view individual resumes.', APP_TD ) );
			return;
		}

		switch( $status ){
			case 'private' :
				appthemes_display_notice( 'notice', sprintf( __( 'Your resume is currently hidden &mdash; <a href="%s">click here to publish it</a>.', APP_TD ), esc_url( add_query_arg( 'publish', 'true ') ) ) );
				break;
		}


	}

	public function script_init() {
		global $jr_options;

		if ( $jr_options->jr_resume_show_contact_form ):

			$ajax_url = admin_url( 'admin-ajax.php' );
			$nonce = wp_create_nonce( 'jr-nonce' );
?>
			<script type="text/javascript">
			/* <![CDATA[ */
				jQuery('a.contact_button').fancybox({
					'speedIn'		:	600,
					'speedOut'		:	200,
					'overlayShow'	:	true,
					'centerOnScroll':	true,
					'overlayColor'	:	'#555',
					'hideOnOverlayClick' : false,
					'onComplete': function() {
							jQuery('#contact').validate({
								// rules and options for validate plugin,
								rules: {
									contact_email: {
										required: true,
										email: true
									}
								}
							});
						}
				});

				/* Recaptcha Validation on contact form */

				jQuery('#contact').submit( function( event ){
					var recaptchaErrors = false;

					jQuery("input[name=send_message]").after('<div class="processing-placeholder"></div>');

					jQuery('.notice.error').remove();

					// check if reCaptcha  enable and validate it
					recaptchaErrors = ! isValidRecaptcha();

					if ( recaptchaErrors ) {
						jQuery('.validation_error').wrapAll('<div class="notice error"><span><ul class="errors">');
						jQuery('.processing-placeholder').remove();
						// refresh catpcha
						Recaptcha.reload();
						event.preventDefault();
					}

				});

				// Recaptcha
				function isValidRecaptcha() {
					var challengeField = jQuery("input#recaptcha_challenge_field").val();
					var responseField = jQuery("input#recaptcha_response_field").val();

					var form = jQuery(this).parents('form');

					var data = {
						action: 'jr_ajax_validate_recaptcha',
						nonce: "<?php echo $nonce; ?>",
						support: 'app-recaptcha-contact',
						recaptcha_challenge_field: challengeField,
						recaptcha_response_field: responseField
					};
					var valid = true;

					jQuery.ajax({
						type: 'post',
						async: false,
						data: data,
						url: "<?php echo $ajax_url; ?>",
						success: function( result, statusText ) {
							if ( result != '1' ) {
								jQuery('#fancybox-content form ul.errors').remove();
								jQuery('#fancybox-content form h2').after('<li class="validation_error">'+result+'</li>');
								valid = false;
							}
						},
						error: function( XMLHttpRequest, textStatus, errorThrown ) {
							//console.log(arguments);
							alert('Error: ' + errorThrown + ' - ' + textStatus + ' - ' + XMLHttpRequest.status);
							valid = false;
						}
					});
					return valid;
				}
			/* ]]> */
			</script>
<?php
		endif;

		if ( get_the_author_meta('ID') == get_current_user_id() ): ?>

			<script type="text/javascript">
				/* <![CDATA[ */
				jQuery('p.edit_button a, a.edit_button').fancybox({
					'speedIn'		:	600,
					'speedOut'		:	200,
					'overlayShow'	:	true,
					'centerOnScroll':	true,
					'overlayColor'	:	'#555',
					'hideOnOverlayClick' : false,
					'onComplete': function() {
						jQuery('#websites').validate({
							// rules and options for validate plugin,
							rules: {
								website_url: {
									required: true,
									url: true
								}
							}
						});
					}
				});

				jQuery('a.delete').click(function(){
					var answer = confirm ("<?php _e( 'Are you sure you want to delete this? This action cannot be undone...', APP_TD ); ?>")
					if (answer)
						return true;
					return false;
				});
				/* ]]> */
			</script>
<?php
		endif;
	}
}

class JR_User_Profile extends APP_View {

	protected $errmsg;

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		return is_page( JR_User_Profile_Page::get_id() );
	}

	/**
	 * Fires before determining which template to load.
	 *
	 * @return void
	 */
	function template_redirect() {
		global $user_ID;

		// check to see if the form has been posted. If so, validate the fields
		if ( ! empty( $_POST['submit'] ) ) {

			require_once(ABSPATH . 'wp-admin/includes/user.php');

			check_admin_referer( 'update-profile_' . get_current_user_id() );

			$errors = edit_user( $user_ID );

			$errmsg = '';
			if ( is_wp_error( $errors ) ) {
				foreach( $errors->get_error_messages() as $message ) {
					$errmsg = $message;
				}
			}

			$this->errmsg = $errmsg;

			// if there are no errors, then process the ad updates
			if ( ! $errmsg ) {
				// update the user fields
				do_action( 'personal_options_update', $user_ID );

				// update the custom user fields
				foreach ( array( 'twitter_id', 'facebook_id', 'linkedin_profile' ) as $field ) {
					update_user_meta( $user_ID, $field, strip_tags( stripslashes( $_POST[ $field ] ) ) );
				}

				$d_url = $_POST['dashboard_url'];
				wp_redirect( './?updated=true&d=' . $d_url );
			}

		}

	}

	function template_vars() {
		global $userdata;

		$public_display['display_displayname'] = $userdata->display_name;
		$public_display['display_nickname'] = $userdata->nickname;
		$public_display['display_username'] = $userdata->user_login;
		$public_display['display_firstname'] = $userdata->first_name;
		$public_display['display_firstlast'] = $userdata->first_name . ' ' . $userdata->last_name;
		$public_display['display_lastfirst'] = $userdata->last_name . ' ' . $userdata->first_name;
		$public_display = array_unique(array_filter(array_map('trim', $public_display)));

		$template_vars = array(
			'userdata' => $userdata,
			'public_display' => $public_display,
		);

		return apply_filters( 'jr_user_profile_template_vars', $template_vars );
	}

	/**
	 * Displays notices.
	 *
	 * @return void
	 */
	public function notices() {

		if ( empty( $this->errmsg ) && isset( $_GET['updated'] ) ) {
			appthemes_display_notice( 'success', __( 'Your profile has been updated.', APP_TD ) );
		}

	}

}

class JR_Seeker_Dashboard extends JR_Dashboard {

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		return parent::condition() && current_user_can( 'can_submit_resume' );
	}

	/**
	 * Fires before determining which template to load.
	 *
	 * @return void
	 */
	function template_redirect() {
		### Prevent Caching
		nocache_headers();
		appthemes_auth_redirect_login();

		$this->pre_load();

		add_action( 'wp_footer', array( $this, 'script_init' ), 99 );
	}

	function pre_load() {
		$user_id = get_current_user_id();

		if ( ( isset( $_GET['toggle_visibility'] ) && is_numeric( $_GET['toggle_visibility'] ) ) ) {
			$post_id = $_GET['toggle_visibility'];
			$post_to_edit = get_post( $post_id );

			if ( $post_to_edit->ID == $post_id && $post_to_edit->post_author == $user_id ) {
				$update_resume = array();
				$update_resume['ID'] = $post_to_edit->ID;
				if ( $post_to_edit->post_status == 'private' ) {
					$update_resume['post_status'] = 'publish';
				} else {
					$update_resume['post_status'] = 'private';
				}
				wp_update_post( $update_resume );
				appthemes_add_notice( 'resume-vis-mod', __( 'Resume visibility modified.', APP_TD ), 'success' );
			}

		}

		if ( isset( $_GET['delete_resume'] ) && is_numeric( $_GET['delete_resume'] ) ) {

			if ( isset( $_GET['confirm'] ) ) {
				$post_id = $_GET['delete_resume'];
				$post_to_remove = get_post( $post_id );

				if ( $post_to_remove->ID == $post_id && $post_to_remove->post_author == $user_id ) {
					wp_delete_post( $post_to_remove->ID );
					appthemes_add_notice( 'resume-deleted', __( 'Resume deleted.', APP_TD ), 'success' );
				}

			}
		}

	}

	function template_vars() {
		global $find_posts_in, $userdata;

		$can_subscribe = jr_current_user_can_subscribe_for_resumes();

		$template_vars = array(
			'activeTab'			=> 0,
			'user_id'			=> get_current_user_id(),
			'can_subscribe'		=> $can_subscribe,
			'find_posts_in'		=> $find_posts_in,
			'userdata'			=> $userdata,
			'show_orders'		=> $can_subscribe || jr_get_user_orders_count() > 0,
			'active_subscription'	=> jr_resume_valid_subscr(),
			'resumes_access'		=> jr_user_resumes_access(),
			'valid_trial'			=> jr_resume_valid_trial(),
		);

		return apply_filters( 'jr_seeker_dashboard', $template_vars );
	}

	public function script_init() {

		$active_tab = 0;
		if ( ( ! empty( $_GET['delete_resume'] ) && ! empty( $_GET['confirm'] ) ) ||  ! empty( $_GET['toggle_visibility'] ) ) {
			$active_tab = 1;
		}
?>
		<script type="text/javascript">
			/* <![CDATA[ */
			jQuery('ul.display_section li a').click(function(){

				jQuery('div.myprofile_section').hide();

				jQuery(jQuery(this).attr('href')).show();

				jQuery('ul.display_section li').removeClass('active');

				jQuery(this).parent().addClass('active');

				return false;
			});
			jQuery('ul.display_section li a:eq(<?php echo $active_tab; ?>)').click();

			jQuery('a.delete-resume').click(function(){
				var answer = confirm("<?php _e('Are you sure you want to delete this resume? This action cannot be undone.',APP_TD); ?>")
				if (answer){
					jQuery(this).attr('href', jQuery(this).attr('href') + '&confirm=true');
					return true;
				}
				else{
					return false;
				}
			});
		/* ]]> */
		</script>
<?php
	}
}

class JR_Author extends APP_View {

	/**
	 * Checks if class should handle current view.
	 *
	 * @return bool
	 */
	public function condition() {
		return ! is_admin() && is_author();
	}

	function parse_query( $wp_query ) {

		if ( empty( $_GET['blog_posts'] ) ) {

			$user = get_user_by( 'slug', $wp_query->query_vars['author_name'] );

			// pagination for custom post types in authors page
			if ( user_can( $user->ID, 'can_submit_resume' ) ) {
				$wp_query->set( 'post_type', APP_POST_TYPE_RESUME );
				$wp_query->set( 'posts_per_page', jr_get_resumes_per_page() );
				$wp_query->set( 'paged', $wp_query->query_vars['paged'] );
			} else {
				$wp_query->set( 'post_type', APP_POST_TYPE );
				$wp_query->set( 'posts_per_page', jr_get_jobs_per_page() );
			}

		}

	}

	/**
	 * Fires before determining which template to load.
	 *
	 * @return void
	 */
	function template_redirect() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	public function enqueue() {
		wp_enqueue_style( 'font-awesome' );
	}

}
