<?php
/**
 * JobRoller Theme Comments
 * This file defines comment templates used in the front end.
 *
 *
 * @version 1.0
 * @author AppThemes
 * @package JobRoller
 * @copyright 2010 all rights reserved
 *
 */

// comment callback for the job listing single page template
function jr_job_comment_template($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment; ?>

	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">

            <div id="comment-<?php comment_ID(); ?>" class="comment_container">

                <?php echo get_avatar( $comment, $size='48' ); ?>

                <div class="comment-text">

                    <?php if ($comment->comment_approved == '0') : ?>

                            <p class="meta"><em><?php _e('Your comment is awaiting approval',APP_TD); ?></em></p>

                    <?php else : ?>

                            <p class="meta">

								<a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>"><?php _e('Comment',APP_TD); ?></a>
								<?php _e('made by',APP_TD); ?> <strong><?php comment_author_link(); ?></strong>
								<?php _e('on',APP_TD); ?> <?php echo get_comment_date('M jS Y'); ?> at <?php echo get_comment_time(); ?>: <?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
								<?php edit_comment_link(__('(Edit)', APP_TD),'  ','') ?>

                            </p>

                    <?php endif; ?>

                    <?php comment_text(); ?>

                    <div class="clear"></div>

                </div><!-- end comment-text -->

                <div class="clear"></div>

            </div><!-- end comment_container -->

			<?php appthemes_comment(); ?>

<?php  // no ending </li> tag because of comment threading
}


// comment callback for the blog listing single page template
function jr_blog_comment_template($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment; ?>

	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">

            <div id="comment-<?php comment_ID(); ?>" class="comment_container">

                <?php echo get_avatar( $comment, $size='48' ); ?>

                <div class="comment-text">

                    <?php if ($comment->comment_approved == '0') : ?>

                            <p class="meta"><em><?php _e('Your comment is awaiting approval',APP_TD); ?></em></p>

                    <?php else : ?>

                            <p class="meta">

								<a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>"><?php _e('Comment',APP_TD); ?></a>
								<?php _e('made by',APP_TD); ?> <strong><?php comment_author_link(); ?></strong>
								<?php _e('on',APP_TD); ?> <?php echo get_comment_date('M jS Y'); ?> at <?php echo get_comment_time(); ?>: <?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
								<?php edit_comment_link(__('(Edit)', APP_TD),'  ','') ?>

                            </p>

                    <?php endif; ?>

                    <?php comment_text(); ?>

                    <div class="clear"></div>

                </div><!-- end comment-text -->

                <div class="clear"></div>

            </div><!-- end comment_container -->

<?php  // no ending </li> tag because of comment threading
}


// comment callback for the page listing single page template
function jr_page_comment_template($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment; ?>

	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">

            <div id="comment-<?php comment_ID(); ?>" class="comment_container">

                <?php echo get_avatar( $comment, $size='48' ); ?>

                <div class="comment-text">

                    <?php if ($comment->comment_approved == '0') : ?>

                            <p class="meta"><em><?php _e('Your comment is awaiting approval',APP_TD); ?></em></p>

                    <?php else : ?>

                            <p class="meta">

								<a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>"><?php _e('Comment',APP_TD); ?></a>
								<?php _e('made by',APP_TD); ?> <strong><?php comment_author_link(); ?></strong>
								<?php _e('on',APP_TD); ?> <?php echo get_comment_date('M jS Y'); ?> at <?php echo get_comment_time(); ?>: <?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
								<?php edit_comment_link(__('(Edit)', APP_TD),'  ','') ?>

                            </p>

                    <?php endif; ?>

                    <?php comment_text(); ?>

                    <div class="clear"></div>

                </div><!-- end comment-text -->

                <div class="clear"></div>

            </div><!-- end comment_container -->

			<?php appthemes_page_comment(); ?>

<?php  // no ending </li> tag because of comment threading
}


/**
 * These functions output the comments form for jobs, pages, blog posts
 *
 * @since 1.3
 */

// include the comments form for job listings
function jr_job_comments_form() {
	$args = array('comment_notes_after' => ''); // remove the "You may use these HTML tags and attributes" text
	comment_form($args);
}
add_action('appthemes_comments_form', 'jr_job_comments_form');



// include the comments form for blog posts
function jr_blog_comments_form() {
	$args = array('comment_notes_after' => ''); // remove the "You may use these HTML tags and attributes" text
	comment_form($args);
}
add_action('appthemes_blog_comments_form', 'jr_blog_comments_form');



// include the comments form for pages
function jr_page_comments_form() {
	$args = array('comment_notes_after' => ''); // remove the "You may use these HTML tags and attributes" text
	comment_form($args);
}
add_action('appthemes_page_comments_form', 'jr_page_comments_form');



/**
 * These functions output the list comments for jobs, pages, blog posts
 *
 * @since 1.3
 */

// include the comment list for the job
function jr_job_list_comments() {
	wp_list_comments(array('type' => 'comment', 'callback' => 'jr_job_comment_template'));
}
add_action('appthemes_list_comments', 'jr_job_list_comments');


// include the comment list for the blog
function jr_blog_list_comments() {
	wp_list_comments(array('type' => 'comment', 'callback' => 'jr_blog_comment_template'));
}
add_action('appthemes_list_blog_comments', 'jr_blog_list_comments');


// include the comment list for the page
function jr_page_list_comments() {
	wp_list_comments(array('type' => 'comment', 'callback' => 'jr_page_comment_template'));
}
add_action('appthemes_list_page_comments', 'jr_page_list_comments');



/**
 * These functions output the list pings for jobs, pages, blog posts
 *
 * @since 1.3
 */

 // include the ping list for the job
function jr_job_list_pings() {
	wp_list_comments(array('type' => 'pings'));
}
add_action('appthemes_list_pings', 'jr_job_list_pings');


// include the ping list for the blog
function jr_blog_list_pings() {
	wp_list_comments(array('type' => 'pings'));
}
add_action('appthemes_list_blog_pings', 'jr_blog_list_pings');


// include the ping list for the page
function jr_page_list_pings() {
	wp_list_comments(array('type' => 'pings'));
}
add_action('appthemes_list_page_pings', 'jr_page_list_pings');
