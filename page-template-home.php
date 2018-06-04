<?php
/**
 * Template Name: ibConnects Home
 *
 * @package IbConnect
 * @author  Sumons I <i@sumonst21.com>
 * @license General Public License (GPL)
 * @link    www.sumonst21.com
 */
?>
    <!-- cp-featured-news-slider -->

        <div class="main-content">
            <div class="container">
                <div class="row">

                    <div class="col-md-8">

                        <div class="cp-news-grid-style-2">
                            <div class="section-title orange-border">
                                <h2>Top Listings</h2>
                                <small>Lorem ipsum dolor sit amet, consectetur adipiscing</small> </div>
                            <div class="row">
                                <div class="col-md-12">
                                <?php
                                // args - query single featured post for grid section
                                $args = array(
                                    'numberposts'	=> 1,
                                    'posts_per_page' => '1',
                                    'post_type'		=> 'job_listing',
                                    'post_status'		=> 'publish',
                                    'orderby' => 'date',
                                    'order'   => 'DESC',
                                    'meta_key'		=> '_jr_featured-listings',
                                    'meta_value'	=> 1
                                );
                                // query
                                $the_query = new WP_Query( $args );
                                ?>
                                <?php if( $the_query->have_posts() ): ?>
                                    <div class="cp-fullwidth-news-post-excerpt">
                                    <?php while( $the_query->have_posts() ) : $the_query->the_post(); ?>
                                    <?php
                                    $featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'full');  //grab the url for the full size featured image
                                    $post_date = get_the_date( 'M j, Y' ); // get the listing date with format
                                    $job_cats = wp_get_post_terms($post->ID, 'job_cat', array("fields" => "all")); // get listing categories
                                    $job_typs = wp_get_post_terms($post->ID, 'job_type', array("fields" => "all")); // get listing types
                                    ?>

                                        <div class="cp-thumb"><img src="<?php echo esc_url($featured_img_url); ?>" alt=""></div>
                                        <div class="cp-post-content">
                                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                            <ul class="cp-post-tools">
                                                <li><i class="icon-1"></i> <?php echo $post_date; ?></li>
                                                <li><i class="icon-2"></i> <?php echo get_post_meta( get_the_ID(), '_Company', true ); ?></li>
                                                <li><i class="icon-3"></i> <?php foreach($job_cats as $job_cat) { echo $job_cat->name; } ?></li>
                                                <li><i class="icon-18"></i> <?php foreach($job_typs as $job_typ) { echo $job_typ->name; } ?></li>
                                            </ul>
                                            <p><?php echo wp_strip_all_tags( get_the_excerpt(), true ); ?></p>
                                        </div>
                                    <?php endwhile; ?>
                                    </div>
                                <?php endif; ?>
                                <?php wp_reset_query();	 // Restore global post data stomped by the_post(). ?>
                                </div>

                                <?php
                                // args - query featured posts for small grid section
                                $args = array(
                                    'numberposts'	=> 6,
                                    'posts_per_page' => '6',
                                    'post_type'		=> 'job_listing',
                                    'post_status'		=> 'publish',
                                    'orderby' => 'date',
                                    'order'   => 'DESC',
                                    'meta_key'		=> '_jr_featured-listings',
                                    'meta_value'	=> 1,
                                    'offset'    => 1
                                );
                                // query
                                $the_query = new WP_Query( $args );
                                ?>
                                <?php if( $the_query->have_posts() ): ?>
                                <ul class="small-grid">
                                    <?php while( $the_query->have_posts() ) : $the_query->the_post(); ?>
                                    <?php
                                    $featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'thumbnail');  //grab the url for the small size featured image
                                    $post_date = get_the_date( 'M j, Y' ); // get the listing date with format
                                    $job_cats = wp_get_post_terms($post->ID, 'job_cat', array("fields" => "all")); // get listing categories
                                    $job_typs = wp_get_post_terms($post->ID, 'job_type', array("fields" => "all")); // get listing types
                                    ?>
                                    <li class="col-md-6 col-sm-6">
                                        <div class="small-post">
                                            <div class="cp-thumb"><img src="<?php echo esc_url($featured_img_url); ?>" alt=""></div>
                                            <div class="cp-post-content">
                                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                                <ul class="cp-post-tools">
                                                    <li><i class="icon-1"></i> <?php echo $post_date; ?></li>
                                                    <li style="display: none;"><i class="icon-2"></i> <?php echo get_post_meta( get_the_ID(), '_Company', true ); ?></li>
                                                    <li><i class="icon-18"></i> <?php foreach($job_typs as $job_typ) { echo $job_typ->name; } ?></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>
                                    <?php endwhile; ?>
                                </ul>
                                <?php endif; ?>
                                <?php wp_reset_query();	 // Restore global post data stomped by the_post(). ?>
                            </div>
                        </div>


                        <div class="cp-news-grid-style-1 m20">
                            <div class="section-title blue-border">
                                <h2>Featured</h2>
                                <small>Lorem ipsum dolor sit amet, consectetur adipiscing</small> </div>
                            <div class="row">
                                <ul class="grid">
                                  <?php
                                  // args - query single technology featured post for grid section
                                  $args = array(
                                      'numberposts'	=> 1,
                                      'posts_per_page' => '1',
                                      'post_type'		=> 'job_listing',
                                      'post_status'		=> 'publish',
                                      'tax_query' => array(
                                      		array(
                                      			'taxonomy' => 'job_type',
                                      			'field'    => 'slug',
                                      			'terms'    => 'technology',
                                      		),
                                      	),
                                      'orderby' => 'date',
                                      'order'   => 'DESC',
                                      'meta_key'		=> '_jr_featured-cat',
                                      'meta_value'	=> 1
                                  );
                                  // query
                                  $the_query = new WP_Query( $args );
                                  ?>
                                  <?php if( $the_query->have_posts() ): ?>
                                    <?php while( $the_query->have_posts() ) : $the_query->the_post(); ?>
                                    <?php
                                    $featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'large');  //grab the url for the small size featured image
                                    $post_date = get_the_date( 'M j, Y' ); // get the listing date with format
                                    $job_cats = wp_get_post_terms($post->ID, 'job_cat', array("fields" => "all")); // get listing categories
                                    $job_typs = wp_get_post_terms($post->ID, 'job_type', array("fields" => "all")); // get listing types
                                    ?>
                                    <li class="col-md-6 col-sm-6">
                                        <div class="cp-news-post-excerpt">
                                            <div class="cp-thumb"><img src="<?php echo esc_url($featured_img_url); ?>" alt=""></div>
                                            <div class="cp-post-content">
                                                <div class="catname"><a class="catname-btn btn-purple waves-effect waves-button" href="#">Technology</a></div>
                                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                                <ul class="cp-post-tools">
                                                    <li><i class="icon-1"></i> <?php echo $post_date; ?></li>
                                                    <li><i class="icon-2"></i> <?php echo get_post_meta( get_the_ID(), '_Company', true ); ?></li>
                                                    <li><i class="icon-18"></i> <?php foreach($job_typs as $job_typ) { echo $job_typ->name; } ?></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>
                                    <?php endwhile; ?>
                                  <?php endif; ?>
                                  <?php wp_reset_query();	 // Restore global post data stomped by the_post(). ?>

                                  <?php
                                  // args - query single Research and Policy featured post for grid section
                                  $args = array(
                                      'numberposts'	=> 1,
                                      'posts_per_page' => '1',
                                      'post_type'		=> 'job_listing',
                                      'post_status'		=> 'publish',
                                      'tax_query' => array(
                                      		array(
                                      			'taxonomy' => 'job_type',
                                      			'field'    => 'slug',
                                      			'terms'    => 'research-and-policy',
                                      		),
                                      	),
                                      'orderby' => 'date',
                                      'order'   => 'DESC',
                                      'meta_key'		=> '_jr_featured-cat',
                                      'meta_value'	=> 1
                                  );
                                  // query
                                  $the_query = new WP_Query( $args );
                                  ?>
                                  <?php if( $the_query->have_posts() ): ?>
                                    <?php while( $the_query->have_posts() ) : $the_query->the_post(); ?>
                                    <?php
                                    $featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'large');  //grab the url for the small size featured image
                                    $post_date = get_the_date( 'M j, Y' ); // get the listing date with format
                                    $job_cats = wp_get_post_terms($post->ID, 'job_cat', array("fields" => "all")); // get listing categories
                                    $job_typs = wp_get_post_terms($post->ID, 'job_type', array("fields" => "all")); // get listing types
                                    ?>
                                    <li class="col-md-6 col-sm-6">
                                        <div class="cp-news-post-excerpt">
                                            <div class="cp-thumb"><img src="<?php echo esc_url($featured_img_url); ?>" alt=""></div>
                                            <div class="cp-post-content">
                                                <div class="catname"><a class="catname-btn btn-pink waves-effect waves-button" href="#">Research and Policy</a></div>
                                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                                <ul class="cp-post-tools">
                                                    <li><i class="icon-1"></i> <?php echo $post_date; ?></li>
                                                    <li><i class="icon-2"></i> <?php echo get_post_meta( get_the_ID(), '_Company', true ); ?></li>
                                                    <li><i class="icon-18"></i> <?php foreach($job_typs as $job_typ) { echo $job_typ->name; } ?></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>
                                    <?php endwhile; ?>
                                  <?php endif; ?>
                                  <?php wp_reset_query();	 // Restore global post data stomped by the_post(). ?>

                                  <?php
                                  // args - query single Reporter or Editor featured post for grid section
                                  $args = array(
                                      'numberposts'	=> 1,
                                      'posts_per_page' => '1',
                                      'post_type'		=> 'job_listing',
                                      'post_status'		=> 'publish',
                                      'tax_query' => array(
                                      		array(
                                      			'taxonomy' => 'job_type',
                                      			'field'    => 'slug',
                                      			'terms'    => 'reporter-or-editor',
                                      		),
                                      	),
                                      'orderby' => 'date',
                                      'order'   => 'DESC',
                                      'meta_key'		=> '_jr_featured-cat',
                                      'meta_value'	=> 1
                                  );
                                  // query
                                  $the_query = new WP_Query( $args );
                                  ?>
                                  <?php if( $the_query->have_posts() ): ?>
                                    <?php while( $the_query->have_posts() ) : $the_query->the_post(); ?>
                                    <?php
                                    $featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'large');  //grab the url for the small size featured image
                                    $post_date = get_the_date( 'M j, Y' ); // get the listing date with format
                                    $job_cats = wp_get_post_terms($post->ID, 'job_cat', array("fields" => "all")); // get listing categories
                                    $job_typs = wp_get_post_terms($post->ID, 'job_type', array("fields" => "all")); // get listing types
                                    ?>
                                    <li class="col-md-6 col-sm-6">
                                        <div class="cp-news-post-excerpt">
                                            <div class="cp-thumb"><img src="<?php echo esc_url($featured_img_url); ?>" alt=""></div>
                                            <div class="cp-post-content">
                                                <div class="catname"><a class="catname-btn btn-gray waves-effect waves-button" href="#">Reporter or Editor</a></div>
                                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                                <ul class="cp-post-tools">
                                                    <li><i class="icon-1"></i> <?php echo $post_date; ?></li>
                                                    <li><i class="icon-2"></i> <?php echo get_post_meta( get_the_ID(), '_Company', true ); ?></li>
                                                    <li><i class="icon-18"></i> <?php foreach($job_typs as $job_typ) { echo $job_typ->name; } ?></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>
                                    <?php endwhile; ?>
                                  <?php endif; ?>
                                  <?php wp_reset_query();	 // Restore global post data stomped by the_post(). ?>

                                  <?php
                                  // args - query single Principal or Vice Principal featured post for grid section
                                  $args = array(
                                      'numberposts'	=> 1,
                                      'posts_per_page' => '1',
                                      'post_type'		=> 'job_listing',
                                      'post_status'		=> 'publish',
                                      'tax_query' => array(
                                      		array(
                                      			'taxonomy' => 'job_type',
                                      			'field'    => 'slug',
                                      			'terms'    => 'principal-or-vice-principal',
                                      		),
                                      	),
                                      'orderby' => 'date',
                                      'order'   => 'DESC',
                                      'meta_key'		=> '_jr_featured-cat',
                                      'meta_value'	=> 1
                                  );
                                  // query
                                  $the_query = new WP_Query( $args );
                                  ?>
                                  <?php if( $the_query->have_posts() ): ?>
                                    <?php while( $the_query->have_posts() ) : $the_query->the_post(); ?>
                                    <?php
                                    $featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'large');  //grab the url for the small size featured image
                                    $post_date = get_the_date( 'M j, Y' ); // get the listing date with format
                                    $job_cats = wp_get_post_terms($post->ID, 'job_cat', array("fields" => "all")); // get listing categories
                                    $job_typs = wp_get_post_terms($post->ID, 'job_type', array("fields" => "all")); // get listing types
                                    ?>
                                    <li class="col-md-6 col-sm-6">
                                        <div class="cp-news-post-excerpt">
                                            <div class="cp-thumb"><img src="<?php echo esc_url($featured_img_url); ?>" alt=""></div>
                                            <div class="cp-post-content">
                                                <div class="catname"><a class="catname-btn btn-green waves-effect waves-button" href="#">Principal or Vice Principal</a></div>
                                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                                <ul class="cp-post-tools">
                                                    <li><i class="icon-1"></i> <?php echo $post_date; ?></li>
                                                    <li><i class="icon-2"></i> <?php echo get_post_meta( get_the_ID(), '_Company', true ); ?></li>
                                                    <li><i class="icon-18"></i> <?php foreach($job_typs as $job_typ) { echo $job_typ->name; } ?></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>
                                    <?php endwhile; ?>
                                  <?php endif; ?>
                                  <?php wp_reset_query();	 // Restore global post data stomped by the_post(). ?>
                                </ul>
                            </div>
                        </div>

                        <?php
                        // args - query posts for OPERATIONS grid section
                        $args = array(
                            'numberposts'	=> 3,
                            'posts_per_page' => '3',
                            'post_type'		=> 'job_listing',
                            'post_status'		=> 'publish',
                            'tax_query' => array(
                                array(
                                  'taxonomy' => 'job_type',
                                  'field'    => 'slug',
                                  'terms'    => 'operations',
                                ),
                              ),
                            'orderby' => 'date',
                            'order'   => 'DESC',
                            'meta_key'		=> '_jr_featured-cat',
                            'meta_value'	=> 1
                        );
                        // query
                        $the_query = new WP_Query( $args );
                        ?>
                        <?php if( $the_query->have_posts() ): ?>
                        <div class="cp-news-grid-style-3 m20">
                            <div class="section-title purple-border">
                                <h2>Operations</h2>
                                <small>Lorem ipsum dolor sit amet, consectetur adipiscing</small> </div>
                            <div class="grid-holder">
                                <div class="row">
                                    <ul class="cp-load-newsgrid">
                                      <?php while( $the_query->have_posts() ) : $the_query->the_post(); ?>
                                        <?php
                                        $featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'large');  //grab the url for the small size featured image
                                        $post_date = get_the_date( 'M j, Y' ); // get the listing date with format
                                        $job_cats = wp_get_post_terms($post->ID, 'job_cat', array("fields" => "all")); // get listing categories
                                        $job_typs = wp_get_post_terms($post->ID, 'job_type', array("fields" => "all")); // get listing types
                                        ?>
                                        <li class="col-md-4 col-sm-4 cp-news-post">
                                            <div class="cp-thumb"><img src="<?php echo $featured_img_url; ?>" alt=""></div>
                                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                        </li>
                                        <?php endwhile; ?>
                                    </ul>
                                    <div class="load-more loadmore-holder"> <a href="#" class="loadmore waves-effect waves-button">View More <i class="icon-5"></i></a> </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php wp_reset_query();	 // Restore global post data stomped by the_post(). ?>

                                <!--
                        <div class="cp-news-grid-style-4 m50">
                            <div class="section-title pink-border">
                                <h2>Fashion</h2>
                                <small>Lorem ipsum dolor sit amet, consectetur adipiscing</small> </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="cp-fullwidth-news-post-excerpt">
                                        <div class="cp-thumb"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/fashion1.jpg" alt=""></div>
                                        <div class="cp-post-content">
                                            <div class="cp-post-rating"><a href="#"><i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-10"></i></a></div>
                                            <h3><a href="http://html.crunchpress.com/materialmag/single-post.html">Maecenas scelerisque massa sit amet tellus commodo vel</a></h3>
                                            <ul class="cp-post-tools">
                                                <li><i class="icon-1"></i> Few Minuts Ago</li>
                                                <li><i class="icon-2"></i> Roy Miller</li>
                                                <li><i class="icon-3"></i> Lifestyle</li>
                                                <li><i class="icon-4"></i> 57 Comments</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-9">
                                    <div class="row">
                                        <ul class="grid">
                                            <li class="col-md-6 col-sm-6">
                                                <div class="cp-post">
                                                    <div class="cp-thumb"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/fashion2.jpg" alt=""></div>
                                                    <div class="cp-post-content">
                                                        <div class="cp-post-rating"><a href="#"><i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-10"></i></a></div>
                                                        <h3><a href="http://html.crunchpress.com/materialmag/single-post.html">Proin id diam in nulla sagittis</a></h3>
                                                        <ul class="cp-post-tools">
                                                            <li><i class="icon-1"></i> May 7, 2016</li>
                                                            <li><i class="icon-4"></i> 57 Comments</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="col-md-6 col-sm-6">
                                                <div class="cp-post">
                                                    <div class="cp-thumb"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/fashion3.jpg" alt=""></div>
                                                    <div class="cp-post-content">
                                                        <div class="cp-post-rating"><a href="#"><i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-10"></i></a></div>
                                                        <h3><a href="http://html.crunchpress.com/materialmag/single-post.html">Proin id diam in nulla sagittis</a></h3>
                                                        <ul class="cp-post-tools">
                                                            <li><i class="icon-1"></i> May 7, 2016</li>
                                                            <li><i class="icon-4"></i> 57 Comments</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="col-md-6 col-sm-6">
                                                <div class="cp-post">
                                                    <div class="cp-thumb"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/fashion4.jpg" alt=""></div>
                                                    <div class="cp-post-content">
                                                        <div class="cp-post-rating"><a href="#"><i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-10"></i></a></div>
                                                        <h3><a href="http://html.crunchpress.com/materialmag/single-post.html">Proin id diam in nulla sagittis</a></h3>
                                                        <ul class="cp-post-tools">
                                                            <li><i class="icon-1"></i> May 7, 2016</li>
                                                            <li><i class="icon-4"></i> 57 Comments</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="col-md-6 col-sm-6">
                                                <div class="cp-post">
                                                    <div class="cp-thumb"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/fashion5.jpg" alt=""></div>
                                                    <div class="cp-post-content">
                                                        <div class="cp-post-rating"><a href="#"><i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-10"></i></a></div>
                                                        <h3><a href="http://html.crunchpress.com/materialmag/single-post.html">Proin id diam in nulla sagittis</a></h3>
                                                        <ul class="cp-post-tools">
                                                            <li><i class="icon-1"></i> May 7, 2016</li>
                                                            <li><i class="icon-4"></i> 57 Comments</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="v-ad"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/vertical-ad.jpg" alt=""></div>
                                </div>
                            </div>
                        </div>
                        -->
                        <?php
                        // args - query posts for Teachers section
                        $args = array(
                            'numberposts'	=> 4,
                            'posts_per_page' => '4',
                            'post_type'		=> 'job_listing',
                            'post_status'		=> 'publish',
                            'tax_query' => array(
                                array(
                                  'taxonomy' => 'job_type',
                                  'field'    => 'slug',
                                  'terms'    => 'teacher',
                                ),
                              ),
                            'orderby' => 'date',
                            'order'   => 'DESC',
                            'meta_key'		=> '_jr_featured-cat',
                            'meta_value'	=> 1
                        );
                        // query
                        $the_query = new WP_Query( $args );
                        ?>
                        <?php if( $the_query->have_posts() ): ?>
                        <div class="cp-news-grid-style-5 m20">
                            <div class="section-title orange-border">
                                <h2>Teacher</h2>
                                <small>Lorem ipsum dolor sit amet, consectetur adipiscing</small> </div>
                            <div>

                              <?php while( $the_query->have_posts() ) : $the_query->the_post(); ?>
                                <?php
                                $featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'large');  //grab the url for the small size featured image
                                $post_date = get_the_date( 'M j, Y' ); // get the listing date with format
                                $job_cats = wp_get_post_terms($post->ID, 'job_cat', array("fields" => "all")); // get listing categories
                                $job_typs = wp_get_post_terms($post->ID, 'job_type', array("fields" => "all")); // get listing types
                                ?>

                                <div class="cp-news-list">
                                    <ul class="row">
                                        <li class="col-md-6 col-sm-6">
                                            <div class="cp-thumb"><img src="<?php echo $featured_img_url; ?>" alt=""></div>
                                        </li>
                                        <li class="col-md-6 col-sm-6">
                                            <div class="cp-post-content">
                                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                                <ul class="cp-post-tools">
                                                    <li><i class="icon-1"></i> <?php echo $post_date; ?></li>
                                                    <li><i class="icon-2"></i> <?php echo get_post_meta( get_the_ID(), '_Company', true ); ?></li>
                                                    <li><i class="icon-18"></i> <?php foreach($job_typs as $job_typ) { echo $job_typ->name; } ?></li>
                                                    <li style="display: none;"><a href="#"><i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-10"></i></a></li>
                                                </ul>
                                                <p><?php //echo wp_strip_all_tags( get_the_excerpt(), true ); ?>
                                                  <?php echo wpex_get_excerpt ( $defaults = array(
                                                  	'length'          => 20,
                                                  	'readmore'        => true,
                                                  	'readmore_text'   => esc_html__( 'read more', 'wpex-boutique' ),
                                                  	'custom_excerpts' => true,
                                                  ) ); ?>
                                                </p>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                              <?php endwhile; ?>
                              <!--
                                <div class="pagination-holder">
                                    <nav>
                                        <ul class="pagination">
                                            <li>
                                                <a href="#" aria-label="Previous"> <span aria-hidden="true"><i class="fa fa-angle-left"></i></span> </a>
                                            </li>
                                            <li class="active"><a href="#">1 <span class="sr-only">(current)</span></a></li>
                                            <li><a href="#">2</a></li>
                                            <li><a href="#">3</a></li>
                                            <li>
                                                <a href="#" aria-label="Next"> <span aria-hidden="true"><i class="fa fa-angle-right"></i></span> </a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                              -->
                            </div>
                        </div>
                      <?php endif; ?>
                      <?php wp_reset_query(); ?>

                    </div>


<?php include_once('home-sidebar.php'); // get the custom home sidebar ?>

                </div>
            </div>
        </div>

<!-- footer section -->
