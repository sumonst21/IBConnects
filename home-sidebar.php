                    <div class="col-md-4">
                        <div class="sidebar side-bar right-sidebar">

                            <div class="widget sidebar-newsletter">
                                <h3 class="side-title">Sign up For Newsletter</h3>
                                <div class="cp-newsletter-holder">
                                    <p>Join ibConnects Newsletter Subscription for all updates and news.</p>
                                    <form>
                                        <div class="input-group">
                                            <input type="text" class="form-control" aria-label="Amount (to the nearest dollar)">
                                            <button class="btn btn-submit waves-effect waves-button" type="button"><i class="fa fa-arrow-right"></i></button>
                                        </div>
                                    </form>
                                </div>

                            </div>

                            <!--
                            <div class="widget sidebar-textwidget">
                                <h3 class="side-title">Text Widget</h3>
                                <div class="cp-sidebar-content">
                                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex commodo
                                        consequat duis aute irure dolor.</p>
                                </div>
                            </div>
                          -->


                            <div class="widget sidebar-featured-post">
                                <h3 class="side-title">Advertisement</h3>
                                <div class="cp-sidebar-content">
                                    <div class="side-featured-slider owl-carousel owl-theme" style="opacity: 1; display: block;">
                                        <div class="owl-wrapper-outer">
                                            <div class="owl-wrapper" style="width: 1440px; left: 0px; display: block;">
                                                <div class="owl-item" style="width: 360px;">
                                                    <div class="item"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/side-fim1.jpg" alt="">
                                                        <div class="cp-post-content">
                                                            <div class="catname"><a href="#" class="catname-btn btn-orange waves-effect waves-button">AdChoices</a></div>
                                                            <h3><a href="#">Morbi iaculis eros eget urna blandit</a></h3>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="owl-item" style="width: 360px;">
                                                    <div class="item"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/side-fim2.jpg" alt="">
                                                        <div class="cp-post-content">
                                                            <div class="catname"><a href="#" class="catname-btn btn-gray waves-effect waves-button">AdChoices</a></div>
                                                            <h3><a href="#">Lorem ipsum dolor sit amet adipiscing elit</a></h3>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="owl-controls clickable">
                                            <div class="owl-buttons">
                                                <div class="owl-prev">prev</div>
                                                <div class="owl-next">next</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php
                            // args - query latest jobs - widget
                            $args = array(
                                'numberposts'	=> 5,
                                'posts_per_page' => '5',
                                'post_type'		=> 'job_listing',
                                'post_status'		=> 'publish',
                                /*
                                'tax_query' => array(
                                    array(
                                      'taxonomy' => 'job_type',
                                      'field'    => 'slug',
                                      'terms'    => 'teacher',
                                    ),
                                  ),
                                  */
                                'orderby' => 'date',
                                'order'   => 'DESC'
                                /*
                                'meta_key'		=> '_jr_featured-cat',
                                'meta_value'	=> 1
                                */
                            );
                            // query
                            $the_query = new WP_Query( $args );
                            ?>
                            <?php if( $the_query->have_posts() ): ?>
                            <div class="widget latest-posts">
                                <h3 class="side-title">Latest Jobs</h3>
                                <div class="cp-sidebar-content">
                                    <ul class="small-grid">
                                      <?php while( $the_query->have_posts() ) : $the_query->the_post(); ?>
                                        <?php
                                        $featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'large');  //grab the url for the small size featured image
                                        $post_date = get_the_date( 'M j, Y' ); // get the listing date with format
                                        $job_cats = wp_get_post_terms($post->ID, 'job_cat', array("fields" => "all")); // get listing categories
                                        $job_typs = wp_get_post_terms($post->ID, 'job_type', array("fields" => "all")); // get listing types
                                        ?>
                                        <li>
                                            <div class="small-post">
                                                <div class="cp-thumb"><img alt="" src="<?php echo $featured_img_url; ?>"></div>
                                                <div class="cp-post-content">
                                                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                                    <ul class="cp-post-tools">
                                                        <li><i class="icon-1"></i> <?php echo $post_date; ?></li>
                                                        <li><i class="icon-18"></i> <a class="purple-text " href="<?php echo get_site_url(); ?>/ib-job-type/<?php foreach($job_typs as $job_typ) { echo $job_typ->slug; } ?>"><?php foreach($job_typs as $job_typ) { echo $job_typ->name; } ?></a></li>
                                                        <li style="display: none;"><i class="icon-4"></i> 57 Comments</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                      <?php endwhile; ?>
                                    </ul>
                                </div>
                            </div>
                          <?php endif; ?>
                          <?php wp_reset_query(); ?>


                            <?php
                            // args - query Popular jobs by Views - widget
                            $args = array(
                                'numberposts'	=> 3,
                                'posts_per_page' => '3',
                                'post_type'		=> 'job_listing',
                                'post_status'		=> 'publish',
                                /*
                                'tax_query' => array(
                                    array(
                                      'taxonomy' => 'job_type',
                                      'field'    => 'slug',
                                      'terms'    => 'teacher',
                                    ),
                                  ),
                                  */
                                'orderby' => 'date',
                                'order'   => 'DESC'
                                /*
                                'meta_key'		=> '_jr_featured-cat',
                                'meta_value'	=> 1
                                */
                            );
                            // query
                            $the_query = new WP_Query( $args );
                            ?>
                            <?php if( $the_query->have_posts() ): ?>
                            <div class="widget popular-post">
                                <h3 class="side-title">Popular Jobs</h3>
                                <div class="cp-sidebar-content">
                                    <ul class="small-grid">
                                      <?php while( $the_query->have_posts() ) : $the_query->the_post(); ?>
                                        <?php
                                        $featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'large');  //grab the url for the small size featured image
                                        $post_date = get_the_date( 'M j, Y' ); // get the listing date with format
                                        $job_cats = wp_get_post_terms($post->ID, 'job_cat', array("fields" => "all")); // get listing categories
                                        $job_typs = wp_get_post_terms($post->ID, 'job_type', array("fields" => "all")); // get listing types
                                        ?>
                                        <li>
                                            <div class="small-post">
                                                <div class="cp-thumb"><img alt="" src="<?php echo $featured_img_url; ?>"></div>
                                                <div class="cp-post-content">
                                                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                                    <ul class="cp-post-tools">
                                                        <li><i class="icon-1"></i> <?php echo $post_date; ?></li>
                                                        <li><i class="icon-18"></i> <a class="purple-text " href="<?php echo get_site_url(); ?>/ib-job-type/<?php foreach($job_typs as $job_typ) { echo $job_typ->slug; } ?>"><?php foreach($job_typs as $job_typ) { echo $job_typ->name; } ?></a></li>
                                                        <li style="display: none;"><i class="icon-4"></i> 57 Comments</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </li>
                                      <?php endwhile; ?>
                                    </ul>
                                </div>
                            </div>
                          <?php endif; ?>
                          <?php wp_reset_query(); ?>

                          <!--
                            <div class="widget sidebar-video">
                                <h3 class="side-title">Video Widget</h3>
                                <div class="cp-sidebar-content">
                                    <iframe src="<?php //echo get_stylesheet_directory_uri(); ?>/assets/4238052.html"></iframe>
                                </div>
                            </div>


                            <div class="widget most-commented">
                                <h3 class="side-title">Most Commented</h3>
                                <div class="cp-sidebar-content">
                                    <ul>
                                        <li><a href="#">Donec aliquam odio ac tempor semper.</a> <i>20</i></li>
                                        <li><a href="#">Nulla ut lectus in dui egestas rhoncus...</a> <i>17</i></li>
                                        <li><a href="#">Aenean blandit neque egestas sagittis...</a> <i>15</i></li>
                                        <li><a href="#">In id dolor facilisis dui tempor euismod...</a> <i>11</i></li>
                                    </ul>
                                </div>
                            </div>


                            <div class="widget top-authors">
                                <h3 class="side-title">Top Authors</h3>
                                <div class="cp-sidebar-content">
                                    <ul class="authors">
                                        <li>
                                            <a href="#"><img src="<?php //echo get_stylesheet_directory_uri(); ?>/assets/images/ta-1.jpg" alt=""></a>
                                        </li>
                                        <li>
                                            <a href="#"><img src="<?php //echo get_stylesheet_directory_uri(); ?>/assets/images/ta-2.jpg" alt=""></a>
                                        </li>
                                        <li>
                                            <a href="#"><img src="<?php //echo get_stylesheet_directory_uri(); ?>/assets/images/ta-3.jpg" alt=""></a>
                                        </li>
                                        <li>
                                            <a href="#"><img src="<?php //echo get_stylesheet_directory_uri(); ?>/assets/images/ta-4.jpg" alt=""></a>
                                        </li>
                                        <li>
                                            <a href="#"><img src="<?php //echo get_stylesheet_directory_uri(); ?>/assets/images/ta-5.jpg" alt=""></a>
                                        </li>
                                        <li>
                                            <a href="#"><img src="<?php //echo get_stylesheet_directory_uri(); ?>/assets/images/ta-6.jpg" alt=""></a>
                                        </li>
                                        <li>
                                            <a href="#"><img src="<?php //echo get_stylesheet_directory_uri(); ?>/assets/images/ta-7.jpg" alt=""></a>
                                        </li>
                                        <li>
                                            <a href="#"><img src="<?php //echo get_stylesheet_directory_uri(); ?>/assets/images/ta-8.jpg" alt=""></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>


                            <div class="widget latest-reviews">
                                <h3 class="side-title">Latest Reviews</h3>
                                <div class="cp-sidebar-content">
                                    <ul class="reviews">
                                        <li>
                                            <h4><a href="#">Donec consequat diam ut pharetra auctor</a></h4>
                                            <div class="cp-rating"><a href="#"><i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-10"></i></a></div>
                                            <i class="tag">8.2</i> </li>
                                        <li>
                                            <h4><a href="#">Morbi vel metus vitae nunc fermentum </a></h4>
                                            <div class="cp-rating"><a href="#"><i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-10"></i></a></div>
                                            <i class="tag">9.2</i> </li>
                                        <li>
                                            <h4><a href="#">Proin ut sapien tempor laoreet mauris</a></h4>
                                            <div class="cp-rating"><a href="#"><i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-10"></i></a></div>
                                            <i class="tag">7.2</i> </li>
                                        <li>
                                            <h4><a href="#">Vivamus feugiat lacus vitae aliquet</a></h4>
                                            <div class="cp-rating"><a href="#"><i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-9"></i> <i class="icon-10"></i></a></div>
                                            <i class="tag">5.2</i> </li>
                                    </ul>
                                </div>
                            </div>
                                -->

                            <div class="widget advertisement">
                                <div class="ad-holder"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/sidebarad.jpg" alt=""></div>
                            </div>

                            <?php
                            /* Job Categories Widget Section */
                            $args = array( 'hide_empty=0' );

                            $terms = get_terms( 'job_cat', $args );
                            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                                $count = count( $terms );
                                $i = 0;
                                $term_list = '<div class="widget categories"><h3 class="side-title">job Categories</h3><div class="cp-sidebar-content"><ul class="cat-holder">';
                                foreach ( $terms as $term ) {
                                    $i++;
                                    $term_list .= '<li><a href="' . esc_url( get_term_link( $term ) ) . '" alt="' . esc_attr( sprintf( __( 'View all jobs filed under %s', 'my_localization_domain' ), $term->name ) ) . '">' . $term->name . '</a><i class="count">' . $term->count . '</i></li>';
                                    if ( $count != $i ) {
                                        //$term_list .= ' &middot; </li>';
                                    }
                                    else {
                                        $term_list .= '</ul></div></div>';
                                    }
                                }
                                echo $term_list;
                            }
                            ?>


                            <?php
                            /* Job Types Widget Section */
                            $args = array( 'hide_empty=0' );

                            $terms = get_terms( 'job_type', $args );
                            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                                $count = count( $terms );
                                $i = 0;
                                $term_list = '<div class="widget categories"><h3 class="side-title">job Types</h3><div class="cp-sidebar-content"><ul class="cat-holder">';
                                foreach ( $terms as $term ) {
                                    $i++;
                                    $term_list .= '<li><a href="' . esc_url( get_term_link( $term ) ) . '" alt="' . esc_attr( sprintf( __( 'View all jobs filed under %s', 'my_localization_domain' ), $term->name ) ) . '">' . $term->name . '</a><i class="count">' . $term->count . '</i></li>';
                                    if ( $count != $i ) {
                                        //$term_list .= ' &middot; </li>';
                                    }
                                    else {
                                        $term_list .= '</ul></div></div>';
                                    }
                                }
                                echo $term_list;
                            }
                            ?>


                            <?php
                            /* Job Salary Widget Section */
                            $args = array( 'hide_empty=0' );

                            $terms = get_terms( 'job_salary', $args );
                            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                                $count = count( $terms );
                                $i = 0;
                                $term_list = '<div class="widget categories"><h3 class="side-title">job Salary</h3><div class="cp-sidebar-content"><ul class="cat-holder">';
                                foreach ( $terms as $term ) {
                                    $i++;
                                    $term_list .= '<li><a href="' . esc_url( get_term_link( $term ) ) . '" alt="' . esc_attr( sprintf( __( 'View all jobs filed under %s', 'my_localization_domain' ), $term->name ) ) . '">' . $term->name . '</a><i class="count">' . $term->count . '</i></li>';
                                    if ( $count != $i ) {
                                        //$term_list .= ' &middot; </li>';
                                    }
                                    else {
                                        $term_list .= '</ul></div></div>';
                                    }
                                }
                                echo $term_list;
                            }
                            ?>

                                <!--
                            <div class="widget archives">
                                <h3 class="side-title">Archives</h3>
                                <div class="cp-sidebar-content">
                                    <ul class="cat-holder">
                                        <li><a href="#">April 2016</a> <i class="count">07</i></li>
                                        <li><a href="#">March 2016</a> <i class="count">13</i></li>
                                        <li><a href="#">February 2016</a> <i class="count">22</i></li>
                                        <li><a href="#">January 2016</a> <i class="count">05</i></li>
                                        <li><a href="#">December 2014</a> <i class="count">33</i></li>
                                        <li><a href="#">November 2014</a> <i class="count">25</i></li>
                                        <li><a href="#">October 2014</a> <i class="count">11</i></li>
                                    </ul>
                                </div>
                            </div>
                                -->

                            <div class="widget facebook-widget">
                                <h3 class="side-title">Facebook</h3>
                                <div class="cp-sidebar-content">
                                    <div id="fb-root" class=" fb_reset">
                                        <div style="position: absolute; top: -10000px; height: 0px; width: 0px;">
                                            <div><iframe name="fb_xdm_frame_http" frameborder="0" allowtransparency="true" allowfullscreen="true" scrolling="no" allow="encrypted-media" id="fb_xdm_frame_http" aria-hidden="true" title="Facebook Cross Domain Communication Frame"
                                                    tabindex="-1" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/RQ7NiRXMcYA.html" style="border: none;"></iframe><iframe name="fb_xdm_frame_https" frameborder="0" allowtransparency="true" allowfullscreen="true" scrolling="no" allow="encrypted-media"
                                                    id="fb_xdm_frame_https" aria-hidden="true" title="Facebook Cross Domain Communication Frame" tabindex="-1" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/RQ7NiRXMcYA(1).html" style="border: none;"></iframe></div>
                                        </div>
                                        <div style="position: absolute; top: -10000px; height: 0px; width: 0px;">
                                            <div></div>
                                        </div>
                                    </div>
                                    <script id="facebook-jssdk" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/js/sdk.js"></script>
                                    <script>
                                        (function(d, s, id) {

                                            var js, fjs = d.getElementsByTagName(s)[0];

                                            if (d.getElementById(id)) return;

                                            js = d.createElement(s);
                                            js.id = id;

                                            js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.3&appId=133982306765662";

                                            fjs.parentNode.insertBefore(js, fjs);

                                        }(document, 'script', 'facebook-jssdk'));
                                    </script>
                                    <div class="fb-page fb_iframe_widget" data-href="https://www.facebook.com/crunchpress.themes" data-height="300px" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true" data-show-posts="true" fb-xfbml-state="rendered"
                                        fb-iframe-plugin-query="adapt_container_width=true&amp;app_id=133982306765662&amp;container_width=360&amp;height=300&amp;hide_cover=false&amp;href=https%3A%2F%2Fwww.facebook.com%2Fcrunchpress.themes&amp;locale=en_US&amp;sdk=joey&amp;show_facepile=true&amp;show_posts=true&amp;small_header=false"><span style="vertical-align: bottom; width: 0px; height: 0px;"><iframe name="fe940f53d069b4" width="1000px" height="300px" frameborder="0" allowtransparency="true" allowfullscreen="true" scrolling="no" allow="encrypted-media" title="fb:page Facebook Social Plugin" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/page.html" style="border: none; visibility: visible; width: 0px; height: 0px;" class=""></iframe></span></div>
                                </div>
                            </div>


                            <div class="widget tags-widget">
                                <h3 class="side-title">Tags</h3>
                                <div class="cp-sidebar-content"> <a href="#">Lifestyle</a> <a href="#">Business</a> <a href="#">Audio</a>                                    <a href="#">Sports</a> <a href="#">Photography</a> <a href="#">Fashion</a>                                    <a href="#">Technology</a> <a href="#">Reviews</a> <a href="#">Politics</a>                                    <a href="#">Video</a> </div>
                            </div>

                        </div>
                    </div>
