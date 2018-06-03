<?php
/**
 * JobRoller Sidebars
 * This file defines sidebars for widgets.
 *
 *
 * @version 1.0
 * @author AppThemes
 * @package JobRoller
 */

add_action( 'after_setup_theme', 'jr_sidebars_init' );
add_action( 'appthemes_before_sidebar_widgets', 'jr_sidebar_sjob' );


### Hook Callbacks

/**
 * Initialize all the sidebars so they are widgetized.
 *
 * @uses apply_filters() Calls 'jr_footer_columns'
 */
function jr_sidebars_init() {

    if ( ! function_exists('register_sidebars') ) {
        return;
	}

    register_sidebar( array(
        'name'          => __( 'Main Sidebar', APP_TD ),
        'id'            => 'sidebar_main',
        'description'   => '',
        'before_widget' => '<li id="%1$s" class="widget %2$s"><div>',
        'after_widget'  => '</div></li>',
        'before_title'  => '</div><h2 class="widget_title">',
        'after_title'   => '</h2><div class="widget_content">',
    ));

    register_sidebar( array(
        'name'          => __( 'Blog Sidebar', APP_TD ),
        'id'            => 'sidebar_blog',
        'description'   => '',
        'before_widget' => '<li id="%1$s" class="widget %2$s"><div>',
        'after_widget'  => '</div></li>',
        'before_title'  => '</div><h2 class="widget_title">',
        'after_title'   => '</h2><div class="widget_content">',
    ));

    register_sidebar( array(
        'name'          => __( 'Page Sidebar', APP_TD ),
        'id'            => 'sidebar_page',
        'description'   => '',
        'before_widget' => '<li id="%1$s" class="widget %2$s"><div>',
        'after_widget'  => '</div></li>',
        'before_title'  => '</div><h2 class="widget_title">',
        'after_title'   => '</h2><div class="widget_content">',
    ));

    register_sidebar( array(
        'name'          => __( 'Job Sidebar',APP_TD ),
        'id'            => 'sidebar_job',
        'description'   => '',
        'before_widget' => '<li id="%1$s" class="widget %2$s"><div>',
        'after_widget'  => '</div></li>',
        'before_title'  => '</div><h2 class="widget_title">',
        'after_title'   => '</h2><div class="widget_content">',
    ));

	register_sidebar(array(
        'name'          => __( 'User Sidebar', APP_TD ),
        'id'            => 'sidebar_user',
        'description'   => '',
        'before_widget' => '<li id="%1$s" class="widget %2$s"><div>',
        'after_widget'  => '</div></li>',
        'before_title'  => '</div><h2 class="widget_title">',
        'after_title'   => '</h2><div class="widget_content">',
    ));

    register_sidebar(array(
        'name'          => __( 'Submit Job Sidebar', APP_TD ),
        'id'            => 'sidebar_submit',
        'description'   => '',
        'before_widget' => '<li id="%1$s" class="widget %2$s"><div>',
        'after_widget'  => '</div></li>',
        'before_title'  => '</div><h2 class="widget_title">',
        'after_title'   => '</h2><div class="widget_content">',
    ));

    register_sidebar( array(
        'name'          => __( 'Resume Sidebar', APP_TD ),
        'id'            => 'sidebar_resume',
        'description'   => '',
        'before_widget' => '<li id="%1$s" class="widget %2$s"><div>',
        'after_widget'  => '</div></li>',
        'before_title'  => '</div><h2 class="widget_title">',
        'after_title'   => '</h2><div class="widget_content">',
    ));

	// change to number of columns you want to add to your footer
	// note: adding more then 3 columns requires CSS width ajustments
	$footer_columns = jr_get_footer_columns();

	if ( $footer_columns ) {

		foreach ( range( 1, $footer_columns ) as $number ) {
			$sidebar_name = sprintf( __('Footer Column %d', APP_TD ), $number );
			register_sidebar( array(
				'name'			=> $sidebar_name,
				'id'			=> 'footer_col_' . $number,
				'description' 	=> '',
				'before_widget' => '<li id="%1$s" class="widget %2$s">',
				'after_widget' 	=> '</li>',
				'before_title' 	=> '<h2 class="widgettitle">',
				'after_title'	=> '</h2>',
			) );
		}

	}

}

/**
 * Include the submit a job sidebar button using a hook.
 */
function jr_sidebar_sjob() {
	$resume_tax = array( 'resume_specialities', 'resume_groups', 'resume_languages', 'resume_category', 'resume_job_type' );

	if ( is_page( JR_Resume_Edit_Page::get_id() )|| APP_POST_TYPE_RESUME == get_post_type() || is_post_type_archive('resume') || is_tax( $resume_tax ) ) {
		appthemes_load_template( array( 'sidebar-sresume.php', 'includes/sidebar-sresume.php' ) );
	} else {
		appthemes_load_template( array( 'sidebar-sjob.php', 'includes/sidebar-sjob.php' ) );
	}

}
