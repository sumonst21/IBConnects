<?php
global $jr_options;

add_action( 'after_setup_theme', '_jr_setup_theme' );
add_action( 'wp_enqueue_scripts', '_jr_load_scripts' );
add_action( 'wp_head', 'jr_version' );
add_action( 'wp_head', 'jr_sharethis_head' );
add_action( 'wp_footer', 'jr_google_analytics_code' );

// remove the WordPress version meta tag
if ( $jr_options->jr_remove_wp_generator ) {
  remove_action('wp_head', 'wp_generator');
}

// remove the new 3.1 admin header toolbar visible on the website if logged in
if ( $jr_options->jr_remove_admin_bar ) {
  add_filter( 'show_admin_bar', '__return_false' );
}

set_post_thumbnail_size( 250, 250, false );
add_image_size('blog-thumbnail', 300, 300, true); // blog post thumbnail size, box resize mode
add_image_size('sidebar-thumbnail', 48, 48, true); // sidebar blog thumbnail size, box resize mode
add_image_size('listing-thumbnail', 28, 28, true);


## Hook Callbacks

/**
 * Define Nav Bar Locations.
 */
function _jr_setup_theme() {

  register_nav_menus( array(
    'primary'  => __( 'Primary Navigation', APP_TD ),
    'top'    => __( 'Top Bar Navigation', APP_TD ),
  ) );

}

// adds version number in the header for troubleshooting
function jr_version($app_version) {
    global $app_version;

    echo "\n\t" . '<!-- start wp_head -->' . "\n";
    echo "\n\t" .'<meta name="version" content="JobRoller '.$app_version.'" />' . "\n";
    echo "\n\t" . '<!-- end wp_head -->' . "\n\n";
}

// insert the google analytics tracking code in the footer
function jr_google_analytics_code() {
  global $jr_options;

    echo "\n\n" . '<!-- start wp_footer -->' . "\n\n";

    if ( $jr_options->jr_google_analytics ) {
        echo stripslashes( $jr_options->jr_google_analytics );
  }

    echo "\n\n" . '<!-- end wp_footer -->' . "\n\n";

}


/**
 * Enqueue scripts.
 *
 * @return void
 */
function _jr_load_scripts() {
  global $jr_options;

  if ( is_admin() ) {
    return;
  }

  $protocol = is_ssl() ? 'https' : 'http';
  $suffix_js = jr_get_enqueue_suffix_for( 'js' );

  wp_enqueue_script( 'jquery-ui-core' );

  // enqueue the strength meter script
  if ( ( ! is_user_logged_in() && is_page( JR_Job_Submit_Page::get_id() ) ) || is_page_template('tpl-login.php') || is_page_template('tpl-profile.php') ) {
    wp_enqueue_script('password-strength-meter');
    wp_enqueue_script('user-profile');
  }

  // used for the fields placeholders to display default values
  wp_enqueue_script( 'defaultvalue', get_template_directory_uri() . '/includes/js/jquery.defaultvalue.js', array( 'jquery' ), '' );

  // adds fancy tags to tag fields
  wp_enqueue_script( 'jquery-tag', get_template_directory_uri() . '/includes/js/jquery.tag.min.js', array( 'jquery' ), '' );

  // adds smooth scroll to top
  wp_enqueue_script( 'jquery-smooth-scroll', get_template_directory_uri() . '/includes/js/jquery.smooth-scroll.min.js', array( 'jquery' ), '2.0' );

  // delays loading of images in long web pages
  wp_enqueue_script( 'lazyload', get_template_directory_uri() . '/includes/js/jquery.lazyload.mini.js', array( 'jquery' ), '1.5.0' );

  // makes textareas grow and shrink to fit it’s content; inspired by the auto growing textareas on Facebook
  wp_enqueue_script( 'elastic', get_template_directory_uri() . '/includes/js/jquery.elastic.min.js', array( 'jquery' ), '1.0' );

  // displays images, html content and multi-media in a Mac-style "lightbox" that floats overtop of web page
  wp_enqueue_script( 'fancybox', get_template_directory_uri() . '/includes/js/jquery.fancybox-1.3.4.pack.js', array( 'jquery' ), '1.3.4' );

  // used to display small balloon tooltips
  wp_enqueue_script( 'jquery-qtip', get_template_directory_uri() . '/includes/js/jquery.qtip.min.js', array( 'jquery' ), '3.0.3' );

  // appthemes javascript functions
  wp_enqueue_script( 'general', get_template_directory_uri() . "/includes/js/theme-scripts{$suffix_js}.js", array( 'jquery', 'validate' ), JR_VERSION );

  // used to transform tables on mobile devices
  wp_enqueue_script( 'footable' );

  wp_enqueue_style( 'dashicons' );

  // script must be enqueued on header to work properly
  wp_deregister_script( 'jquery-ui-autocomplete' );
  wp_enqueue_script(
    'jquery-ui-autocomplete',
    site_url( '/wp-includes/js/jquery/ui/autocomplete.min.js' ),
    array( 'jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position', 'jquery-ui-menu' )
  );

  wp_enqueue_script( 'googlemaps', $protocol . '://www.google.com/jsapi' );

  /* Script variables */
  $params = array(
    'lazyload_placeholder' => get_template_directory_uri() . '/images/grey.gif',
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'get_sponsored_results_nonce'   => wp_create_nonce( 'get-sponsored-results' ),
    'nonce' => wp_create_nonce( 'jr-nonce' ),

    'si_empty' => __( 'Strength indicator', APP_TD ),
    'si_short' => __( 'Very weak', APP_TD ),
    'si_bad' => __( 'Weak', APP_TD ),
    'si_good' => __( 'Medium', APP_TD ),
    'si_strong' => __( 'Strong', APP_TD ),
    'si_mismatch' => __( 'Mismatch', APP_TD ),

    'msg_invalid_email' => __( 'Invalid email address.', APP_TD ),
    'msg_fields_required' => __( 'All fields are required.', APP_TD ),

    'no_more_results' => __( 'No more results to show.', APP_TD ),
  );

  wp_localize_script( 'general', 'jobroller_params', $params );

  ### theme stylesheets

  $main_css = get_stylesheet_uri();

  wp_enqueue_style( 'at-main-css', $main_css, false, JR_VERSION );

  if ( apply_filters( 'jr_load_style', ! is_child_theme() ) ) {
    $child_theme = $jr_options->jr_child_theme ? $jr_options->jr_child_theme : 'style-default.css';
    $stylesheet = '/styles/'.$child_theme;
    $from_child_theme = is_child_theme() && file_exists( get_stylesheet_directory() . $stylesheet );
    $stylesheet_url = ( $from_child_theme ? get_stylesheet_directory_uri() : get_template_directory_uri() ) . $stylesheet;
    wp_enqueue_style( 'at-color-css', $stylesheet_url, array( 'at-main-css' ), JR_VERSION  );
  }

  if ( ! is_front_page() && is_singular() ) {
    $stylesheet = '/styles/print.css';
    $from_child_theme = file_exists( get_stylesheet_directory() . $stylesheet );
    $stylesheet_url = ( $from_child_theme ? get_stylesheet_directory_uri() : get_template_directory_uri() ) . $stylesheet;
    wp_enqueue_style( 'at-print-css', $stylesheet_url, array( 'at-main-css' ), JR_VERSION, 'print' );
  }
}

// enables the share buttons on job and blog posts
function jr_sharethis_head() {
  global $jr_options;

  if ( ! $jr_options->jr_sharethis_id ) {
    return;
  }

    //fba9432a-d597-4509-800d-999395ce552a
    $pub_id = $jr_options->jr_sharethis_id;

    $http = (is_ssl()) ? 'https' : 'http';

    echo "\n\t" . '<script type="text/javascript" src="'.$http.'://w.sharethis.com/button/buttons.js"></script>' . "\n";
    echo "\n\t" . '<script type="text/javascript">stLight.options({publisher:"'.$pub_id.'"});</script>' . "\n";

}


### Helper Functions

function default_primary_nav() {
  global $wp_query;

  $is_frontpage = ( ( is_front_page() && JR_Home_Archive::get_id() == get_option( 'page_on_front' ) ) || ( is_post_type_archive(APP_POST_TYPE) && JR_Home_Archive::get_id() != get_option( 'show_on_front' ) ) );

  $curr_page_item_class = $is_frontpage  ? 'current_page_item' : '';
  $li_html = html( 'li class="page_item '.$curr_page_item_class.'"', html( 'a href="'.esc_url( get_post_type_archive_link( APP_POST_TYPE ) ).'"', __('Latest Jobs', APP_TD) ) );

  $args = array(
      'hierarchical'       => false,
      'parent'               => 0
  );
  $terms = get_terms( 'job_type', $args );

  if ( $terms ) {

    foreach( $terms as $term ) {
      $curr_page_item_class = ! empty( $wp_query->queried_object->slug ) && $wp_query->queried_object->slug == $term->slug ? 'current_page_item' : '';;
      $li_html .= html( 'li class="page_item '.$curr_page_item_class.'"', html( 'a href="'.get_term_link( $term->slug, 'job_type' ).'"', $term->name ) );
    }

  }

  echo html( 'ul', $li_html );
}

function default_top_nav() {
  global $jr_options;

  echo '<ul id="menu-top" class="menu">';

  $exclude_pages = array();

  $exclude_pages[] = get_option('page_on_front');
  $exclude_pages[] = JR_Dashboard_Page::get_id();
  $exclude_pages[] = JR_Resume_Plans_Purchase_Page::get_id();
  $exclude_pages[] = JR_Packs_Purchase_Page::get_id();
  $exclude_pages[] = JR_Resume_Edit_Page::get_id();
  $exclude_pages[] = JR_Job_Submit_Page::get_id();
  $exclude_pages[] = JR_User_Profile_Page::get_id();
  $exclude_pages[] = JR_Job_Edit_Page::get_id();
  $exclude_pages[] = JR_Date_Archive_Page::get_id();

  if ( current_theme_supports ('app-login') ) {
    $exclude_pages[] = APP_Registration::get_id();
    $exclude_pages[] = APP_Login::get_id();
    $exclude_pages[] = APP_Password_Recovery::get_id();
    $exclude_pages[] = APP_Password_Reset::get_id();
  }

  if ( $jr_options->jr_disable_blog ) {
    $exclude_pages[] = JR_Blog_Page::get_id();
  }

  $exclude_pages = implode( ',', $exclude_pages );

  echo wp_list_pages( 'sort_column=menu_order&title_li=&echo=0&link_before=&link_after=&depth=1&exclude=' . $exclude_pages );

  echo jr_top_nav_links();

  echo '</ul>';
}

// Add items to top nav
function jr_top_nav_links( $items = '', $menu = null ) {

  if ( ! empty( $menu ) && $menu->theme_location != 'top') {
    return $items;
  }

  if ( is_user_logged_in() ) {
    $items .= '<li class="right"><a href="'.wp_logout_url( home_url() ).'">'.__('Logout', APP_TD).'</a></li>';

    if ( JR_Dashboard_Page::get_id() && is_user_logged_in()) {

      $items .= '<li class="right ';
      if ( is_page( JR_Dashboard_Page::get_id() ) ) {
        $items .= 'current_page_item';
      }
      $items .= '"><a href="'.get_permalink( JR_Dashboard_Page::get_id() ).'">'.__('My Dashboard', APP_TD).'</a></li>';

    }

  } else {
    global $pagenow;

    if ( isset( $_GET['action'] ) ) {
      $theaction = $_GET['action'];
    } else {
      $theaction = '';
    }

    $items .= '<li class="right ';
    if ( $pagenow == 'wp-login.php' && $theaction !== 'lostpassword' && ! isset( $_GET['key'] ) ) {
      $items .= 'current_page_item';
    }
    $items .= '"><a href="'.site_url('wp-login.php').'">'.__('Login/Register', APP_TD).'</a></li>';
  }

  if ( jr_resume_is_visible() || jr_user_resume_visibility() ) {
    $items .= '<li class="right ';
    if ( is_post_type_archive('resume') ) {
      $items .= 'current_page_item';
    }
    $items .= '"><a href="'.get_post_type_archive_link('resume').'">'.__('Browse Resumes', APP_TD).'</a></li>';
  }

  if ( JR_Job_Submit_Page::get_id() && (!is_user_logged_in() || (is_user_logged_in() && current_user_can('can_submit_job'))) ) {

    $items .= '<li class="right ';
    if ( is_page( JR_Job_Submit_Page::get_id() ) ) {
      $items .= 'current_page_item';
    }
    $items .= '"><a href="'.get_permalink( JR_Job_Submit_Page::get_id() ).'">'.__('Submit a Job', APP_TD).'</a></li>';

  }

  return $items;
}

/**
 * Retrieves the '.min' suffix for CSS/JS files on a live site considering SCRIPT_DEBUG constant.
 */
function jr_get_enqueue_suffix_for() {
  return ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
}
