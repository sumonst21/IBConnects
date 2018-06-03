<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
	$classes = '';

	if ( ! $jr_options->jr_show_sidebar ) {
		$classes .= 'wider ';
	}

	if ( $jr_options->jr_child_theme ) {
		$classes .= str_replace( '.css', '', $jr_options->jr_child_theme ) . ' ';
	}
?>
<!--[if lt IE 7 ]> <html class="ie6" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7 ]>    <html class="ie7" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8 ]>    <html class="ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 9 ]>    <html class="ie9" <?php language_attributes(); ?>> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html <?php language_attributes(); ?>> <!--<![endif]-->
<head profile="http://gmpg.org/xfn/11">
    <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="viewport" content="width=device-width,initial-scale=1" />

    <title><?php wp_title(''); ?></title>

    <link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php echo esc_url( $jr_options->jr_feedburner_url ? $jr_options->jr_feedburner_url : get_bloginfo_rss('rss2_url') ); ?>" />
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

	<?php if ( is_singular() ): wp_enqueue_script( 'comment-reply' ); endif; ?>

    <?php wp_head(); ?>
</head>

<body id="top" <?php body_class( $classes ) ?> >

	<?php appthemes_before(); ?>

    <div id="wrapper">

		<?php if ( $jr_options->jr_debug_mode ): ?>
			<div class="debug"><h3><?php _e( 'Debug Mode On', APP_TD ); ?></h3><?php print_r( $wp_query->query_vars ); ?></div>
		<?php endif; ?>

		<?php appthemes_before_header(); ?>

		<?php get_header(); ?>

		<?php appthemes_after_header(); ?>

		<div class="clear"></div>

		<div id="content">
			<div class="inner">

				<div id="mainContent" class="<?php global $header_search; if ( ! $header_search ): echo 'nosearch'; endif; ?>">

				<?php if ( $jr_options->breadcrumbs && ! is_front_page() ): ?>

					<div id="breadcrumbs" class="container">
						<div class="row">
							<?php breadcrumb_trail( array(
								'separator'	=> '&raquo;',
								'show_browse' => false,
								'labels' => array(
									'home' => '<div class="breadcrumbs-home"><i class="dashicons-before"></i></div>',
								),
							) ); ?>
						</div>
					</div>

				<?php endif; ?>

				<?php load_template( app_template_path() ); ?>

				<div class="clear"></div>

			</div><!-- end inner -->
		</div><!-- end content -->

		<?php appthemes_before_footer(); ?>

		<?php get_footer( app_template_base() ); ?>

		<?php appthemes_after_footer(); ?>

	</div><!-- end wrapper -->

<?php appthemes_after(); ?>

<?php wp_footer(); ?>

</body>

</html>
