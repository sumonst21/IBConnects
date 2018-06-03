<?php
/**
 * Functions that hook into WordPress to allow customizing the theme using the customizer.
 */

add_action( 'after_setup_theme', '_jr_customizer_theme_support' );

add_action( 'customize_register', '_jr_customize_color_scheme' );
add_action( 'customize_register', '_jr_customize_site' );
add_action( 'customize_register', '_jr_customize_listings' );
add_action( 'customize_register', '_jr_customize_footer' );

add_action( 'customize_controls_enqueue_scripts', '_jr_enqueue_customizer_color_previewer' );
add_action( 'customize_save', '_jr_save_header_image' );

add_action( 'jr_footer_columns', '_jr_customize_footer_columns' );
add_action( 'wp_head', '_jr_customize_css', 999 );


### Hooks Callbacks

/**
 * Provide support for custom logos and backgrounds on customizer.
 *
 * @since 1.8
 */
function _jr_customizer_theme_support() {

	$defaults = array(
		'wp-head-callback' => '_jr_custom_background_cb',
	);
	add_theme_support( 'custom-background', $defaults );

	$defaults = array(
		'default-image'          => '%s/images/logo.png',
		'width'                  => 240,
		'height'                 => 45,
		'flex-height'            => true,
		'flex-width'             => true,
		'header-text'            => true,
		'default-text-color'     => 'fff',
		'uploads'                => true,
		'wp-head-callback'       => '_jr_custom_background_cb',
	);
	add_theme_support( 'custom-header', $defaults );
}

/**
 * Displays the theme color choices in the customizer.
 */
function _jr_customize_color_scheme( $wp_customize ){
	global $jr_options;

	$color_defaults = jr_get_customizer_color_defaults();

	$wp_customize->add_setting( 'jr_options[jr_child_theme]', array(
		'default' => $jr_options->jr_child_theme,
		'type' => 'option',
	) );

	$wp_customize->add_control( 'jr_color_scheme', array(
		'label'      => __( 'Color Scheme', APP_TD ),
		'section'    => 'colors',
		'settings'   => 'jr_options[jr_child_theme]',
		'type'       => 'radio',
		'choices'	 => jr_get_color_choices(),
		'priority'	 => 1,
	) );

	$wp_customize->add_setting( 'jr_options[top_nav_bgcolor]', array(
		'default' => $color_defaults['jr_top_nav_bgcolor'],
		'type' => 'option'
	) );

	$wp_customize->add_control(
		new WP_Customize_Color_Control(	$wp_customize,
			'jr_top_nav_bgcolor',
			array(
				'label'		=> __( 'Top Navigation Bar Color', APP_TD ),
				'section'	=> 'colors',
				'settings'	=> 'jr_options[top_nav_bgcolor]',
				'priority'	=> 2,
			)
		)
	);

	$wp_customize->add_setting( 'jr_options[top_nav_links_color]', array(
		'default' => $color_defaults['jr_top_nav_links_color'],
		'type' => 'option'
	) );

	$wp_customize->add_control(
		new WP_Customize_Color_Control(	$wp_customize,
			'jr_top_nav_links_color',
			array(
				'label'		=> __( 'Top Navigation Links Color', APP_TD ),
				'section'	=> 'colors',
				'settings'	=> 'jr_options[top_nav_links_color]',
				'priority'	=> 3,
			)
		)
	);

	$wp_customize->add_setting( 'jr_options[top_nav_hover_bgcolor]', array(
		'default' => $color_defaults['jr_top_nav_hover_bgcolor'],
		'type' => 'option'
	) );

	$wp_customize->add_control(
		new WP_Customize_Color_Control(	$wp_customize,
			'jr_top_nav_hover_bgcolor',
			array(
				'label'		=> __( 'Top Navigation Hover Color', APP_TD ),
				'section'	=> 'colors',
				'settings'	=> 'jr_options[top_nav_hover_bgcolor]',
				'priority'	=> 3,
			)
		)
	);

	$wp_customize->add_setting( 'jr_options[top_nav_sep_color]', array(
		'default' => $color_defaults['jr_top_nav_sep_color'],
		'type' => 'option'
	) );

	$wp_customize->add_control(
		new WP_Customize_Color_Control(	$wp_customize,
			'jr_top_nav_sep_color',
			array(
				'label'		=> __( 'Top Navigation Separator Color', APP_TD ),
				'section'	=> 'colors',
				'settings'	=> 'jr_options[top_nav_sep_color]',
				'priority'	=> 4,
			)
		)
	);

	$wp_customize->add_setting( 'jr_options[header_bgcolor]', array(
		'default' => $color_defaults['jr_header_bgcolor'],
		'type' => 'option'
	) );

	$wp_customize->add_control(
		new WP_Customize_Color_Control(	$wp_customize,
			'jr_header_bgcolor',
			array(
				'label'		=> __( 'Header Background Color', APP_TD ),
				'section'	=> 'colors',
				'settings'	=> 'jr_options[header_bgcolor]',
				'priority'	=> 5,
			)
		)
	);

	$wp_customize->add_setting( 'jr_options[buttons_color]', array(
		'default' => $color_defaults['jr_buttons_color'],
		'type' => 'option'
	) );

	$wp_customize->add_control(
		new WP_Customize_Color_Control(	$wp_customize,
			'jr_buttons_color',
			array(
				'label'		=> __( 'Buttons Color', APP_TD ),
				'section'	=> 'colors',
				'settings'	=> 'jr_options[buttons_color]',
				'priority'	=> 12,
			)
		)
	);

	$wp_customize->add_setting( 'jr_options[buttons_hover_bgcolor]', array(
		'default' => $color_defaults['jr_buttons_hover_bgcolor'],
		'type' => 'option'
	) );

	$wp_customize->add_control(
		new WP_Customize_Color_Control(	$wp_customize,
			'jr_buttons_hover_bgcolor',
			array(
				'label'		=> __( 'Buttons Hover Color', APP_TD ),
				'section'	=> 'colors',
				'settings'	=> 'jr_options[buttons_hover_bgcolor]',
				'priority'	=> 12,
			)
		)
	);

	$wp_customize->add_setting( 'jr_options[buttons_nav_link_color]', array(
		'default' => $color_defaults['jr_buttons_nav_link_color'],
		'type' => 'option'
	) );

	$wp_customize->add_control(
		new WP_Customize_Color_Control(	$wp_customize,
			'jr_buttons_nav_link_color',
			array(
				'label'		=> __( 'Main Navigation Buttons Color', APP_TD ),
				'section'	=> 'colors',
				'settings'	=> 'jr_options[buttons_nav_link_color]',
				'priority'	=> 12,
			)
		)
	);

	$wp_customize->add_setting( 'jr_options[buttons_selected_bgcolor]', array(
		'default' => $color_defaults['jr_buttons_selected_bgcolor'],
		'type' => 'option'
	) );

	$wp_customize->add_control(
		new WP_Customize_Color_Control(	$wp_customize,
			'jr_buttons_selected_bgcolor',
			array(
				'label'		=> __( 'Main Navigation Button Selected Color', APP_TD ),
				'section'	=> 'colors',
				'settings'	=> 'jr_options[buttons_selected_bgcolor]',
				'priority'	=> 12,
			)
		)
	);

	$wp_customize->add_setting( 'jr_options[links_color]', array(
		'default' => $color_defaults['jr_links_color'],
		'type' => 'option'
	) );

	$wp_customize->add_control(
		new WP_Customize_Color_Control(	$wp_customize,
			'jr_links_color',
			array(
				'label'		=> __( 'Links Color', APP_TD ),
				'section'	=> 'colors',
				'settings'	=> 'jr_options[links_color]',
				'priority'	=> 13,
			)
		)
	);

	$wp_customize->add_setting( 'jr_options[footer_bgcolor]', array(
		'default' => $color_defaults['jr_footer_bgcolor'],
		'type' => 'option'
	) );

	$wp_customize->add_control(
		new WP_Customize_Color_Control(	$wp_customize,
			'jr_footer_bgcolor',
			array(
				'label'		=> __( 'Footer Background Color', APP_TD ),
				'section'	=> 'colors',
				'settings'	=> 'jr_options[footer_bgcolor]',
				'priority'	=> 13,
			)
		)
	);

	$wp_customize->add_setting( 'jr_options[footer_text_color]', array(
		'default' => $color_defaults['jr_footer_text_color'],
		'type' => 'option'
	) );

	$wp_customize->add_control(
		new WP_Customize_Color_Control(	$wp_customize,
			'jr_footer_text_color',
			array(
				'label'		=> __( 'Footer Text Color', APP_TD ),
				'section'	=> 'colors',
				'settings'	=> 'jr_options[footer_text_color]',
				'priority'	=> 13,
			)
		)
	);

	$wp_customize->add_setting( 'jr_options[footer_titles_color]', array(
		'default' => $color_defaults['jr_footer_titles_color'],
		'type' => 'option'
	) );

	$wp_customize->add_control(
		new WP_Customize_Color_Control(	$wp_customize,
			'jr_footer_titles_color',
			array(
				'label'		=> __( 'Footer Titles Color', APP_TD ),
				'section'	=> 'colors',
				'settings'	=> 'jr_options[footer_titles_color]',
				'priority'	=> 13,
			)
		)
	);

	$wp_customize->add_setting( 'jr_options[footer_links_color]', array(
		'default' => $color_defaults['jr_footer_links_color'],
		'type' => 'option'
	) );

	$wp_customize->add_control(
		new WP_Customize_Color_Control(	$wp_customize,
			'jr_footer_links_color',
			array(
				'label'		=> __( 'Footer Links Color', APP_TD ),
				'section'	=> 'colors',
				'settings'	=> 'jr_options[footer_links_color]',
				'priority'	=> 13,
			)
		)
	);

	$wp_customize->add_setting( 'jr_options[footer_sep_color]', array(
		'default' => $color_defaults['jr_footer_sep_color'],
		'type' => 'option'
	) );

	$wp_customize->add_control(
		new WP_Customize_Color_Control(	$wp_customize,
			'jr_footer_sep_color',
			array(
				'label'		=> __( 'Footer Separator Color', APP_TD ),
				'section'	=> 'colors',
				'settings'	=> 'jr_options[footer_sep_color]',
				'priority'	=> 13,
			)
		)
	);

}

/**
 * Displays the theme listing options in the customizer.
 */
function _jr_customize_site( $wp_customize ) {
	global $jr_options;

	$wp_customize->add_section( 'jr_site', array(
		'title' => __( 'Site', APP_TD ),
		'priority' => 20,
	));

	$wp_customize->add_setting( 'jr_options[jr_show_sidebar]', array(
		'default' => $jr_options->jr_show_sidebar,
		'type' => 'option'
	));

	$wp_customize->add_control( 'jr_show_sidebar', array(
		'label'      => __( 'Show Sidebar', APP_TD ),
		'section'    => 'jr_site',
		'settings'   => 'jr_options[jr_show_sidebar]',
		'type'       => 'checkbox',
	) );

	$wp_customize->add_setting( 'jr_options[jr_show_searchbar]', array(
		'default' => $jr_options->jr_show_searchbar,
		'type' => 'option'
	));

	$wp_customize->add_control( 'jr_show_searchbar', array(
		'label'      => __( 'Show Search Bar', APP_TD ),
		'section'    => 'jr_site',
		'settings'   => 'jr_options[jr_show_searchbar]',
		'type'       => 'checkbox',
	) );

	$wp_customize->add_setting( 'jr_options[jr_show_filterbar]', array(
		'default' => $jr_options->jr_show_filterbar,
		'type' => 'option'
	));

	$wp_customize->add_control( 'jr_show_filterbar', array(
		'label'      => __( 'Show Filter Bar', APP_TD ),
		'section'    => 'jr_site',
		'settings'   => 'jr_options[jr_show_filterbar]',
		'type'       => 'checkbox',
	) );

	$wp_customize->add_setting( 'jr_options[breadcrumbs]', array(
		'default' => $jr_options->breadcrumbs,
		'type' => 'option'
	));

	$wp_customize->add_control( 'jr_breadcrumbs', array(
		'label'      => __( 'Show Breadcrumbs', APP_TD ),
		'section'    => 'jr_site',
		'settings'   => 'jr_options[breadcrumbs]',
		'type'       => 'checkbox',
	) );

	$wp_customize->add_setting( 'jr_options[jr_show_empty_categories]', array(
		'default' => $jr_options->jr_show_empty_categories,
		'type' => 'option'
	));

	$wp_customize->add_control( 'jr_show_empty_categories', array(
		'label'      => __( 'Show Empty Categories', APP_TD ),
		'section'    => 'jr_site',
		'settings'   => 'jr_options[jr_show_empty_categories]',
		'type'       => 'checkbox',
	) );

	$wp_customize->add_setting( 'jr_options[jr_disable_blog]', array(
		'default' => $jr_options->jr_disable_blog,
		'type' => 'option'
	));

	$wp_customize->add_control( 'jr_disable_blog', array(
		'label'      => __( 'Disable Blog', APP_TD ),
		'section'    => 'jr_site',
		'settings'   => 'jr_options[jr_disable_blog]',
		'type'       => 'checkbox',
	) );

	$wp_customize->add_setting( 'jr_options[jr_jobs_submit_text]', array(
		'default' => $jr_options->jr_jobs_submit_text,
		'type' => 'option'
	));

	$wp_customize->add_control( 'jr_jobs_submit_text', array(
		'label'      =>  __( '"Submit" Button Text (HTML is allowed)', APP_TD ),
		'section'    => 'jr_site',
		'settings'   => 'jr_options[jr_jobs_submit_text]',
		'type'       => 'textarea',
	) );

}

/**
 * Displays the theme listing options in the customizer.
 */
function _jr_customize_footer( $wp_customize ) {
	global $jr_options;

	$wp_customize->add_section( 'jr_footer', array(
		'title' => __( 'Footer', APP_TD ),
		'priority' => 100,
	));

	$wp_customize->add_setting( 'jr_options[multi_column_footer]', array(
		'default' => $jr_options->multi_column_footer,
		'type' => 'option'
	));

	$wp_customize->add_control( 'jr_multi_column_footer', array(
		'label'      => __( 'Multi-Column Footer', APP_TD ),
		'section'    => 'jr_footer',
		'settings'   => 'jr_options[multi_column_footer]',
		'type'       => 'checkbox',
	) );

	$wp_customize->add_setting( 'jr_options[footer_width]', array(
		'default' => $jr_options->footer_width,
		'type' => 'option'
	));

	$wp_customize->add_control( 'jr_multi_column_footer_width', array(
		'label'      => __( 'Multi-Column Footer (specify % or px)', APP_TD ),
		'section'    => 'jr_footer',
		'settings'   => 'jr_options[footer_width]',
		'type'       => 'text',
	) );

	$wp_customize->add_setting( 'jr_options[footer_cols]', array(
		'default' => $jr_options->footer_cols,
		'type' => 'option'
	));

	$wp_customize->add_control( 'jr_footer_cols', array(
		'label'      => __( 'Columns', APP_TD ),
		'section'    => 'jr_footer',
		'settings'   => 'jr_options[footer_cols]',
		'type'       => 'text',
	) );

	$wp_customize->add_setting( 'jr_options[footer_col_width]', array(
		'default' => $jr_options->footer_col_width,
		'type' => 'option'
	));

	$wp_customize->add_control( 'jr_footer_width', array(
		'label'      => __( 'Columns Width (specify % or px)', APP_TD ),
		'section'    => 'jr_footer',
		'settings'   => 'jr_options[footer_col_width]',
		'type'       => 'text',
	) );

}

/**
 * Displays the theme listing options in the customizer.
 */
function _jr_customize_listings( $wp_customize ){
	global $jr_options;

	$wp_customize->add_section( 'jr_listings', array(
		'title' => __( 'Listings', APP_TD ),
		'priority' => 205,
	));

	$wp_customize->add_setting( 'jr_options[jobs_frontpage]', array(
		'default' => $jr_options->jobs_frontpage,
		'type' => 'option'
	) );

	$wp_customize->add_setting( 'jr_options[featured_jobs_frontpage]', array(
		'default' => $jr_options->featured_jobs_frontpage,
		'type' => 'option'
	) );

	$wp_customize->add_setting( 'jr_options[jr_jobs_per_page]', array(
		'default' => $jr_options->jr_jobs_per_page,
		'type' => 'option'
	) );

	$wp_customize->add_setting( 'jr_options[jr_featured_jobs_per_page]', array(
		'default' => $jr_options->jr_featured_jobs_per_page,
		'type' => 'option'
	) );

	$wp_customize->add_setting( 'jr_options[jr_resumes_per_page]', array(
		'default' => $jr_options->jr_resumes_per_page,
		'type' => 'option'
	) );

	$wp_customize->add_control( 'jobs_frontpage', array(
		'label'      => __( 'Front Page (Regular Jobs)', APP_TD ),
		'section'    => 'jr_listings',
		'settings'   => 'jr_options[jobs_frontpage]',
		'type'       => 'text',
	) );

	$wp_customize->add_control( 'featured_jobs_frontpage', array(
		'label'      => __( 'Front Page (Featured Jobs )', APP_TD ),
		'section'    => 'jr_listings',
		'settings'   => 'jr_options[featured_jobs_frontpage]',
		'type'       => 'text',
	) );

	$wp_customize->add_control( 'jr_jobs_per_page', array(
		'label'      => __( 'Listings (Regular Jobs Per Page)', APP_TD ),
		'section'    => 'jr_listings',
		'settings'   => 'jr_options[jr_jobs_per_page]',
		'type'       => 'text',
	) );

	$wp_customize->add_control( 'jr_featured_jobs_per_page', array(
		'label'      => __( 'Listings (Featured Jobs Per Page)', APP_TD ),
		'section'    => 'jr_listings',
		'settings'   => 'jr_options[jr_featured_jobs_per_page]',
		'type'       => 'text',
	) );

	$wp_customize->add_control( 'jr_resumes_per_page', array(
		'label'      => __( 'Listings (Resumes Per Page)', APP_TD ),
		'section'    => 'jr_listings',
		'settings'   => 'jr_options[jr_resumes_per_page]',
		'type'       => 'text',
	) );


}

/**
 * Overrides the theme styles with the Customizer options.
 *
 * @since 1.8
 */
function _jr_customize_css() {
	global $jr_options;

?>
    <style type="text/css">
		<?php if ( $jr_options->links_color ) : ?>
			a { color: <?php echo $jr_options->links_color; ?>; }
		<?php endif; ?>

		<?php if ( $jr_options->header_bgcolor ) : ?>
			#header { background: <?php echo $jr_options->header_bgcolor; ?>; }
		<?php endif; ?>

		<?php if ( $jr_options->buttons_color ) : ?>
			a.button, #mainNav li { background: <?php echo $jr_options->buttons_color; ?>; }
		<?php endif; ?>

		<?php if ( $jr_options->buttons_nav_link_color ) : ?>
			#mainNav li a { background: <?php echo $jr_options->buttons_nav_link_color; ?>; }
		<?php endif; ?>

		<?php if ( $jr_options->buttons_selected_bgcolor ) : ?>
			#mainNav li.current_page_item a { background: <?php echo $jr_options->buttons_selected_bgcolor; ?>; }
		<?php endif; ?>

		<?php if ( $jr_options->buttons_hover_bgcolor ) : ?>
			a.button:hover { background-color: <?php echo $jr_options->buttons_hover_bgcolor; ?>; }
		<?php endif; ?>

		<?php if ( $jr_options->top_nav_bgcolor ) : ?>
			#topNav { background: <?php echo $jr_options->top_nav_bgcolor; ?>; }
		<?php endif; ?>

		<?php if ( $jr_options->top_nav_links_color ) : ?>
			#topNav li a { color: <?php echo $jr_options->top_nav_links_color; ?>; }
		<?php endif; ?>

		<?php if ( $jr_options->top_nav_sep_color ) : ?>
			#topNav ul, #topNav li a:not(:hover), #topNav li.right a:not(:hover) { border-left-color:<?php echo $jr_options->top_nav_sep_color; ?>; border-right-color:<?php echo $jr_options->top_nav_sep_color; ?>; }
		<?php endif; ?>

		<?php if ( $jr_options->top_nav_hover_bgcolor ) : ?>
			#topNav li.current-menu-item a,
			#topNav li.current_page_item a, #topNav li a:hover, #topNav li a:focus  { background:<?php echo $jr_options->top_nav_hover_bgcolor; ?>; }
		<?php endif; ?>

		<?php if ( $jr_options->footer_bgcolor ) : ?>
			div#footer { background-color: <?php echo $jr_options->footer_bgcolor; ?>; }
		<?php endif; ?>

		<?php if ( $jr_options->footer_text_color ) : ?>
			div#footer, div#footer p, div#footer span, div#footer li { color: <?php echo $jr_options->footer_text_color; ?>; }
		<?php endif; ?>

		<?php if ( $jr_options->footer_titles_color ) : ?>
			#footer h1, #footer h2, #footer h3 { color: <?php echo $jr_options->footer_titles_color; ?>; }
		<?php endif; ?>

		<?php if ( $jr_options->footer_links_color ) : ?>
			div#footer a, div#footer li a, #footer i:before { color: <?php echo $jr_options->footer_links_color; ?>; }
		<?php endif; ?>

		<?php if ( $jr_options->footer_sep_color ) : ?>
			#footer .column:not(:first-child) { border-left: 1px solid <?php echo $jr_options->footer_sep_color; ?>; }
			#footer h1, #footer h2, #footer h3 { border-bottom: 1px solid <?php echo $jr_options->footer_sep_color; ?>; }
			#footer .copyright { border-top: 1px solid <?php echo $jr_options->footer_sep_color; ?>; }
		<?php endif; ?>

		<?php if ( $jr_options->footer_width ) : ?>
			#footer .inner { width: <?php echo $jr_options->footer_width; ?>; }
			@media screen and (max-width: 860px) {
				#footer .inner {
					float: left;
					width: 95%;
				}
			}
		<?php endif; ?>

		<?php if ( $jr_options->multi_column_footer && $jr_options->footer_col_width ) : ?>
			#footer .column { width: <?php echo $jr_options->footer_col_width; ?>; }
			@media screen and (max-width: 860px) {
				#footer .column {
					float: left;
					width: 95%;
				}
			}
		<?php endif; ?>

    </style>
<?php
}

function _jr_customize_footer_columns( $cols ) {
	global $jr_options;

	if ( ! $jr_options->multi_column_footer ) {
		return 0;
	}

	return $jr_options->footer_cols ? $jr_options->footer_cols : $cols;
}


### Helper functions & Other Callbacks

/**
 * Default callback to output a background image/color.
 *
 * @since 1.8
 */
function _jr_custom_background_cb() {

	$background = get_background_image();
	$color = get_background_color();

	if ( ! $background && ! $color ) {
		return;
	}

	$style = $color ? "background-color: #$color;" : '';

	if ( $background ) {

		$image = " background-image: url('$background');";
		$repeat = get_theme_mod( 'background_repeat', 'repeat' );

		if ( ! in_array( $repeat, array( 'no-repeat', 'repeat-x', 'repeat-y', 'repeat' ) ) ) {
			$repeat = 'repeat';
		}

		$repeat = " background-repeat: $repeat;";
		$position = get_theme_mod( 'background_position_x', 'left' );

		if ( ! in_array( $position, array( 'center', 'right', 'left' ) ) ) {
			$position = 'left';
		}

		$position = " background-position: top $position;";
		$attachment = get_theme_mod( 'background_attachment', 'scroll' );
		if ( ! in_array( $attachment, array( 'fixed', 'scroll' ) ) ) {
			$attachment = 'scroll';
		}

		$attachment = " background-attachment: $attachment;";
		$style .= $image . $repeat . $position . $attachment;

	} else if ( ! $background && $color ) {
		$style .= " background-image: none; ";
	}
?>
	<style type="text/css">
		body.custom-background { <?php echo trim( $style ); ?> }
	</style>
<?php
}

/**
 * Updates the color pickers using javascript each time the user changes the theme color scheme.
 */
function _jr_enqueue_customizer_color_previewer() {
	global $jr_options;

	$suffix_js = jr_get_enqueue_suffix_for('js');

	wp_enqueue_script( 'jr_themecustomizer', get_template_directory_uri()."/includes/js/theme-customizer{$suffix_js}.js",  array( 'customize-controls' ), JR_VERSION, true );

	$params = array(
		'color_scheme' => $jr_options->jr_child_theme,
		'colors' => jr_get_customizer_color_defaults('all'),
	);

	wp_localize_script( 'jr_themecustomizer', 'customizer_params', $params );
}

/**
 * Populates the theme dropdown with the default styles and adds any custom .css styles found on the styles path.
 * Styles must be placed under the child folder \styles\ (fallback to the parent /styles folder if directory does not exist).
 * The resulting styles array is filterable to allow adding custom theme styles.
 *
 * @uses apply_filters() Calls 'jr_theme_styles'
 */
function jr_get_color_choices() {

	$styles_path = get_stylesheet_directory() . '/styles/';
	if ( ! apply_filters( 'jr_load_style', ! is_child_theme() ) || ! file_exists( $styles_path ) ) {
		$styles_path = get_template_directory() . '/styles/';
	}

	$styles_pattern = $styles_path . 'style*.css';

	$styles = array(
		'style-default.css'		=> __( 'Default', APP_TD ),
		'style-pro-blue.css'	=> __( 'Blue Pro', APP_TD ),
		'style-pro-green.css'	=> __( 'Green Pro', APP_TD ),
		'style-pro-orange.css'	=> __( 'Orange Pro', APP_TD ),
		'style-pro-gray.css'	=> __( 'Gray Pro', APP_TD ),
		'style-pro-red.css'		=> __( 'Red Pro', APP_TD ),
		'style-basic.css'		=> __( 'Basic Plain', APP_TD )
	);

	// get all the available theme styles and append them to the defaults
	foreach ( glob( $styles_pattern ) as $filename ) {

		if ( FALSE !== strpos( $filename, '.min' ) ) continue;

		if ( ! array_key_exists( basename( $filename ), $styles ) ) {
			$styles[ basename( $filename ) ] = __( 'Custom Theme', APP_TD ) . ' (' . basename( $filename ) . ')';
		}
	}

	return apply_filters( 'jr_theme_styles', $styles );
}

/**
 * Retrieves the customizer default colors based on the theme color scheme.
 *
 * @uses apply_filters() Calls 'jr_customizer_color_defaults'
 */
function jr_get_customizer_color_defaults( $scheme = '' ) {
	global $jr_options;

	if ( ! $scheme ) {
		$scheme = $jr_options->jr_child_theme;
	}

	$color_defaults = array(
		'style-default.css'	=> array(
			// top nav
			'jr_top_nav_bgcolor'		=> '#e8e8e8',
			'jr_top_nav_links_color'	=> '#555',
			'jr_top_nav_hover_bgcolor'	=> '#d8d8d8',
			'jr_top_nav_sep_color'		=> '#d8d8d8',
			// header
			'jr_header_bgcolor'			=> '',
			// other
			'jr_buttons_color'			=> '#9B9B9B',
			'jr_buttons_hover_bgcolor'	=> '#515151',
			'jr_buttons_selected_bgcolor'=>'#D7D7D7',
			'jr_buttons_nav_link_color'	=> '#fff',
			'jr_links_color'			=> '#3388BB',
			// footer
			'jr_footer_bgcolor'			=> '#E8E8E8',
			'jr_footer_text_color'		=> '#999999',
			'jr_footer_links_color'		=> '#3388BB',
			'jr_footer_titles_color'	=> '#000',
			'jr_footer_sep_color'		=> '#CFCFCF',
		),
		'style-pro-blue.css' => array(
			// top nav
			'jr_top_nav_bgcolor'		=> '#2c4975',
			'jr_top_nav_links_color'	=> '#bbcce5',
			'jr_top_nav_hover_bgcolor'	=> '#476593',
			'jr_top_nav_sep_color'		=> '#2f4f7e',
			// header
			'jr_header_bgcolor'			=> '#335588',
			// other
			'jr_buttons_color'			=> '#3d5e8e',
			'jr_buttons_hover_bgcolor'	=> '#1A3966',
			'jr_buttons_selected_bgcolor'=> '#fff',
			'jr_buttons_nav_link_color'	=> '#40608f',
			'jr_links_color'			=> '#39669e',
			// footer
			'jr_footer_bgcolor'			=> '#2B4772',
			'jr_footer_text_color'		=> '#fff',
			'jr_footer_links_color'		=> '#AABECF',
			'jr_footer_titles_color'	=> '#fff',
			'jr_footer_sep_color'		=> '#2f4f7e',
		),
		'style-pro-green.css'	=> array(
			// top nav
			'jr_top_nav_bgcolor'		=> '#375816',
			'jr_top_nav_links_color'	=> '#e0e9c7',
			'jr_top_nav_hover_bgcolor'	=> '#659337',
			'jr_top_nav_sep_color'		=> '#3e6419',
			// header
			'jr_header_bgcolor'			=> '#558822',
			// other
			'jr_buttons_color'			=> '#4c791e',
			'jr_buttons_hover_bgcolor'	=> '#355C0C',
			'jr_buttons_selected_bgcolor'=> '#fff',
			'jr_buttons_nav_link_color'	=> '#40661a',
			'jr_links_color'			=> '#669933',
			// footer
			'jr_footer_bgcolor'			=> '#375816',
			'jr_footer_text_color'		=> '#fff',
			'jr_footer_links_color'		=> '#AACFAD',
			'jr_footer_titles_color'	=> '#fff',
			'jr_footer_sep_color'		=> '#6D806E',
		),
		'style-pro-orange.css'	=> array(
			// top nav
			'jr_top_nav_bgcolor'		=> '#333',
			'jr_top_nav_links_color'	=> '#f8efeb',
			'jr_top_nav_hover_bgcolor'	=> '#e07528',
			'jr_top_nav_sep_color'		=> '#3e3e3e',
			// header
			'jr_header_bgcolor'			=> '#dd6611',
			// other
			'jr_buttons_color'			=> '#dd6611',
			'jr_buttons_hover_bgcolor'	=> '#A34E11',
			'jr_buttons_selected_bgcolor'=> '#fff',
			'jr_buttons_nav_link_color'	=> '#bf580e',
			'jr_links_color'			=> '#e07528',
			// footer
			'jr_footer_bgcolor'			=> '#333333',
			'jr_footer_text_color'		=> '#fff',
			'jr_footer_links_color'		=> '#e07528',
			'jr_footer_titles_color'	=> '#fff',
			'jr_footer_sep_color'		=> '#513D2F',
		),
		'style-pro-gray.css'	=> array(
			// top nav
			'jr_top_nav_bgcolor'		=> '#333',
			'jr_top_nav_links_color'	=> '#f8efeb',
			'jr_top_nav_hover_bgcolor'	=> '#e0e0e0',
			'jr_top_nav_sep_color'		=> '#3e3e3e',
			// header
			'jr_header_bgcolor'			=> '#ddd',
			// other
			'jr_buttons_color'			=> '#555',
			'jr_buttons_hover_bgcolor'	=> '#3C3838',
			'jr_buttons_selected_bgcolor'=> '#fff',
			'jr_buttons_nav_link_color'	=> '#555',
			'jr_links_color'			=> '#2277bb',
			// footer
			'jr_footer_bgcolor'			=> '#333333',
			'jr_footer_text_color'		=> '#fff',
			'jr_footer_links_color'		=> '#2277bb',
			'jr_footer_titles_color'	=> '#fff',
			'jr_footer_sep_color'		=> '#575757',
		),
		'style-pro-red.css'		=> array(
			// top nav
			'jr_top_nav_bgcolor'		=> '#333',
			'jr_top_nav_links_color'	=> '#ccc',
			'jr_top_nav_hover_bgcolor'	=> '#b5504f',
			'jr_top_nav_sep_color'		=> '#393939',
			// header
			'jr_header_bgcolor'			=> '#ae3d3c',
			// other
			'jr_buttons_color'			=> '#9b3735',
			'jr_buttons_hover_bgcolor'	=> '#79211F',
			'jr_buttons_selected_bgcolor'=> '#fff',
			'jr_buttons_nav_link_color'	=> '#9b3735',
			'jr_links_color'			=> '#c24649',
			// footer
			'jr_footer_bgcolor'			=> '#333333',
			'jr_footer_text_color'		=> '#fff',
			'jr_footer_links_color'		=> '#c24649',
			'jr_footer_titles_color'	=> '#fff',
			'jr_footer_sep_color'		=> '#513D2F',
		),
		'style-basic.css'		=> array(
			// top nav
			'jr_top_nav_bgcolor'		=> '',
			'jr_top_nav_links_color'	=> '#999',
			'jr_top_nav_hover_bgcolor'	=> '',
			'jr_top_nav_sep_color'		=> '#ccc',
			// header
			'jr_header_bgcolor'			=> '',
			// other
			'jr_buttons_color'			=> '#f8f8f8',
			'jr_buttons_hover_bgcolor'	=> '#D3D3D3',
			'jr_buttons_selected_bgcolor'=> '#E5E5E5',
			'jr_buttons_nav_link_color'	=> 'inherit',
			'jr_links_color'			=> '#999999',
			// footer
			'jr_footer_bgcolor'			=> '#E8E8E8',
			'jr_footer_text_color'		=> '#999999',
			'jr_footer_links_color'		=> '#888888',
			'jr_footer_titles_color'	=> '#000',
			'jr_footer_sep_color'		=> '#CFCFCF',
		),
	);

	$color_defaults = apply_filters( 'jr_customizer_color_defaults', $color_defaults );

	if ( ! empty( $color_defaults[ $scheme ] ) ) {
		return $color_defaults[ $scheme ];
	}

	if ( 'all' == $scheme ) {
		return $color_defaults;
	}

	return $color_defaults['style-default.css'];
}

/**
 * WP seems to fail saving a default header image (not uploaded to the media library).
 * This forces the header image to be saved.
 *
 * @since 1.8
 */
function _jr_save_header_image( $wp_customizer ) {
	global $jr_options;

	check_ajax_referer( 'save-customize_' . $wp_customizer->get_stylesheet(), 'nonce' );

	$header = $wp_customizer->unsanitized_post_values();

	if ( ! empty( $header['header_image'] ) ) {
		set_theme_mod( 'header_image', $header['header_image'] );

		// provide compat. with legacy option
		$jr_options->jr_logo_url = $header['header_image'];

		$default_image = get_theme_mod('default-image');

		// always use defaults for the default logo
		if ( $default_image = $header['header_image'] ) {
			remove_theme_mod( 'header_image_data' );
		}
	}
}
