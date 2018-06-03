<?php
/**
 * JobRoller Actions.
 * Hooks into various actions in the theme.
 *
 * @version 1.0
 * @author AppThemes
 * @package JobRoller
 * @copyright 2011 all rights reserved
 */

add_action('appthemes_after_blog_post_title', 'jr_blog_post_meta');
add_filter('the_author_posts_link','jr_author_posts_link');
add_action('appthemes_after_blog_post_content', 'jr_blog_post_after');
add_action('appthemes_page_loop_else', 'jr_page_loop_else');
add_action('appthemes_blog_loop_else', 'jr_blog_loop_else');
add_action('appthemes_resume_loop_else', 'jr_resume_loop_else');
add_action('appthemes_job_listing_loop_else', 'jr_job_loop_else');


/* Footer */
add_action('job_footer', 'jr_application_form', 1);
add_action('job_footer', 'jr_share_form', 2);
add_action('job_footer', 'jr_job_map', 3);

add_filter( 'show_password_fields_on_registration', 'jr_password_fields_support' );


### Hook Callbacks

/**
 * Add the post meta before the blog post content.
 */
function jr_blog_post_meta() {
	global $post;

	if ( is_page() ) {
		return; // don't do post-meta on pages
	}
?>
	<p class="meta"><em><?php _e('Posted by', APP_TD); ?></em> <?php the_author_posts_link(); ?> | <?php echo jr_ad_posted($post->post_date); ?> | <?php the_category(', '); ?></p>
<?php
}

/**
 * Add additional argument to url on blog posts to retrieve all the author posts instead of any of jobroller's custom post types.
 */
function jr_author_posts_link( $link ) {
	global $authordata, $post;

	$author_posts_url = get_author_posts_url( $authordata->ID, $authordata->user_nicename );
	$author_posts_url = ( 'post' == get_post_type( $post ) ? add_query_arg( 'blog_posts', '1', $author_posts_url ) : $author_posts_url );

	$link = sprintf(
		'<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
		esc_url( $author_posts_url ),
		esc_attr( sprintf( __( 'Posts by %s', APP_TD ), get_the_author() ) ),
		get_the_author()
	);

	return $link;
}


// add the post tags and counter after the blog post content only on single blog page
function jr_blog_post_after() {
	global $post, $jr_options;

	if ( ! is_singular('post') ) {
		return; // only show on blog post single page
	}

	if ( is_page() ) {
		return; // don't do post-meta on pages
	}

	if ( $jr_options->jr_ad_stats_all && current_theme_supports( 'app-stats' ) ):
		?><p class="stats"><?php appthemes_stats_counter($post->ID); ?></p><?php
	endif;

	the_tags('<p class="tags">' . __('Tags:', APP_TD) . ' ', ', ', '</p>');
}

/**
 * Add the error message if no pages are found.
 */
function jr_page_loop_else() {
?>
	<p><?php _e('Sorry, no posts matched your criteria.', APP_TD); ?></p>
<?php
}

/**
 * Add the error message if no blog posts are found.
 */
function jr_blog_loop_else() {
?>
	<p class="posts"><?php _e('No blog posts found.', APP_TD); ?></p>
<?php
}

/**
 * Add the error message if no resume posts are found.
 */
function jr_resume_loop_else() {
?>
	<p class="resumes"><?php _e('No matching resumes found.', APP_TD); ?></p>
<?php
}

/**
 * Add the error message if no pages are found.
 */
function jr_job_loop_else() {
?>
	<p class="jobs"><?php _e('No jobs found.', APP_TD); ?></p>
<?php
}


### Others

/**
 * Controls password fields visibility.
 *
 * @since 1.6.4
 */
function jr_password_fields_support( $bool ) {
	global $jr_options;

	return (bool) $jr_options->jr_allow_registration_password;
}
