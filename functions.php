<?php
/**
 * Theme functions file
 *
 * DO NOT MODIFY THIS FILE. Make a child theme instead: http://codex.wordpress.org/Child_Themes
 *
 * @package JobRoller
 * @author AppThemes
 */

// Define vars and globals.
global $app_version, $app_form_results, $jr_log, $app_abbr, $jr_options;

// Current version.
$app_theme = 'JobRoller';
$app_abbr = 'jr';
$app_version = '1.8.7';

define( 'APP_TD', 'jobroller' );
define( 'JR_VERSION' , $app_version );
define( 'JR_FIELD_PREFIX', '_' . $app_abbr . '_' );

// Include framework and modules.
require_once( dirname(__FILE__) . '/framework/load.php' );
require_once( dirname(__FILE__) . '/theme-framework/load.php' );
require_once( dirname(__FILE__) . '/includes/payments/load.php' );
require_once( dirname(__FILE__) . '/includes/stats/load.php' );
require_once( dirname(__FILE__) . '/includes/recaptcha/load.php' );
require_once( dirname(__FILE__) . '/includes/custom-forms/form-builder.php' );
require_once( dirname(__FILE__) . '/includes/widgets/load.php' );
require_once( dirname(__FILE__) . '/includes/core.php' );

scb_register_table( 'app_pop_daily', 'jr_counter_daily' );
scb_register_table( 'app_pop_total', 'jr_counter_total' );

/**
 * Filter the except length to 20 words.
 *
 * @param int $length Excerpt length.
 * @return int (Maybe) modified excerpt length.
 */
 function wpex_get_excerpt( $args = array() ) {

 	// Defaults
 	$defaults = array(
 		'post'            => '',
 		'length'          => 20,
 		'readmore'        => false,
 		'readmore_text'   => esc_html__( 'read more', 'text-domain' ),
 		'readmore_after'  => '',
 		'custom_excerpts' => true,
 		'disable_more'    => false,
 	);

 	// Apply filters
 	$defaults = apply_filters( 'wpex_get_excerpt_defaults', $defaults );

 	// Parse args
 	$args = wp_parse_args( $args, $defaults );

 	// Apply filters to args
 	$args = apply_filters( 'wpex_get_excerpt_args', $defaults );

 	// Extract
 	extract( $args );

 	// Get global post data
 	if ( ! $post ) {
 		global $post;
 	}

 	// Get post ID
 	$post_id = $post->ID;

 	// Check for custom excerpt
 	if ( $custom_excerpts && has_excerpt( $post_id ) ) {
 		$output = $post->post_excerpt;
 	}

 	// No custom excerpt...so lets generate one
 	else {

 		// Readmore link
 		$readmore_link = '<a href="' . get_permalink( $post_id ) . '" class="readmore">' . $readmore_text . $readmore_after . '</a>';

 		// Check for more tag and return content if it exists
 		if ( ! $disable_more && strpos( $post->post_content, '<!--more-->' ) ) {
 			$output = apply_filters( 'the_content', get_the_content( $readmore_text . $readmore_after ) );
 		}

 		// No more tag defined so generate excerpt using wp_trim_words
 		else {

 			// Generate excerpt
 			$output = wp_trim_words( strip_shortcodes( $post->post_content ), $length );

 			// Add readmore to excerpt if enabled
 			if ( $readmore ) {

 				$output .= apply_filters( 'wpex_readmore_link', $readmore_link );

 			}

 		}

 	}

 	// Apply filters and echo
 	return apply_filters( 'wpex_get_excerpt', $output );

 }
