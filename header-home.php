<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "site-content" div.
 *
 * @package IbConnect
 * @author  Sumons I <i@sumonst21.com>
 * @license General Public License (GPL)
 * @link    www.sumonst21.com
 */
?><!DOCTYPE html>
<html lang="en">
<?php include_once('vars.php'); ?>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="CrunchPress Material Mag">
    <link rel="shortcut icon" href="http://html.crunchpress.com/materialmag/images/fav.ico" type="image/x-icon">
    <title><?php wp_title(''); ?></title>
    <link href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/custom.css" rel="stylesheet">

    <link href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/color.css" rel="stylesheet">

    <link href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/mega-menu.css" rel="stylesheet">

    <link href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/bootstrap.css" rel="stylesheet">

    <link href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/bootstrap-theme.min.css" rel="stylesheet">

    <link href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/materialize.css" rel="stylesheet">

    <link href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/font-awesome.min.css" rel="stylesheet">

    <link href="<?php echo get_stylesheet_directory_uri(); ?>/assets/css/owl.slider.css" rel="stylesheet">

    <!--[if lt IE 9]>

      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>

      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

<![endif]-->
<?php include_once('custom-style.php'); ?>
</head>

<body>

    <div id="wrapper" class="wrapper">

        <div id="cp-header" class="cp-header">

            <div class="cp-topbar">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="toplinks">
                                <li class="waves-effect waves-button"><a href="<?php echo $siteurl; ?>">Home</a></li>
                                <li class="waves-effect waves-button"><a href="<?php echo $siteurl.'/ib-jobs/'; ?>">Jobs</a></li>
                                <li class="waves-effect waves-button"><a href="<?php echo $siteurl; ?>#">FAQ’s</a></li>
                                <li class="waves-effect waves-button"><i class="fa fa-phone"></i> + 800 123 4567</li>
                                <li class="waves-effect waves-button"><i class="fa fa-envelope-o"></i> <a href="mailto:info@ibconnects.com">info@ibconnects.com</a></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <div class="cp-toptools pull-right">
                                <ul>
                                    <li class="waves-effect"><a href="http://html.crunchpress.com/materialmag/login.html"><i class="icon-2"></i></a></li>
                                    <li class="waves-effect"><a href="http://html.crunchpress.com/materialmag/register.html"><i class="fa fa-sign-in"></i></a></li>
                                    <li class="waves-effect"><a href="<?php echo $siteurl; ?>#"><i class="fa fa-cart-arrow-down"></i></a></li>
                                </ul>
                            </div>
                            <div class="cp-topsocial pull-right">
                                <ul>
                                    <li class="waves-effect"><a href="<?php echo $siteurl; ?>#"><i class="fa fa-twitter"></i></a></li>
                                    <li class="waves-effect"><a href="<?php echo $siteurl; ?>#"><i class="fa fa-facebook"></i></a></li>
                                    <li class="waves-effect"><a href="<?php echo $siteurl; ?>#"><i class="fa fa-linkedin"></i></a></li>
                                    <li class="waves-effect"><a href="<?php echo $siteurl; ?>#"><i class="fa fa-youtube"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="cp-logo-row">
                <div class="container">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="logo">
                                <a href="<?php echo $siteurl; ?>"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/ibconnects-logo.png" alt=""></a>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="cp-advertisement waves-effect"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/ad-large.gif" alt=""></div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="cp-megamenu sticky">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="cp-mega-menu">
                                <label for="mobile-button"> <i class="fa fa-bars"></i> </label>

                                <input id="mobile-button" type="checkbox">
                                <ul class="collapse main-menu">
                                    <li class="slogo" style="display: none;">
                                        <a href="<?php echo $siteurl; ?>"><img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/logo-micon.png" alt=""></a>
                                    </li>
                                    <li><a href="<?php echo $siteurl; ?>#">Home</a>
                                        <ul class="drop-down one-column hover-expand" style="display: none;">

                                            <li> <a href="<?php echo $siteurl; ?>">Home Layout One</a> </li>
                                            <li> <a href="#">Home Layout Two</a> </li>
                                            <li> <a href="#">Home Layout Three</a> </li>
                                        </ul>
                                    </li>
                                    <li> <a href="#">Browse IB Jobs</a>
                                        <ul class="drop-down one-column hover-expand">

                                            <li> <a href="<?php echo $siteurl; ?>/ib-jobs/">Find a Job</a> </li>
                                            <li> <a href="#">List of Partnered Schools</a> </li>
                                            <li> <a href="#">IB Teaching Jobs</a> </li>
                                        </ul>
                                    </li>
                                    <li><a href="#">Post a Resume</a></li>
                                    <li> <a href="#">Employers</a>
                                        <ul class="drop-down one-column hover-expand">

                                            <li> <a href="<?php echo $siteurl; ?>/">Pricing & Plans</a> </li>
                                            <li> <a href="#">Purchase Cart</a> </li>
                                        </ul>
                                    </li>
                                    <li> <a href="<?php echo $siteurl; ?>#">Blog</a>
                                        <ul class="drop-down full-width blog-menu hover-expand">
                                            <li>
                                                <ul>
                                                    <li>
                                                        <a href="<?php echo $siteurl; ?>#"> <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/mm-1.jpg" alt=""> </a>
                                                        <h3><a href="<?php echo $siteurl; ?>#">Proin id diam in nulla sagittempor</a></h3>
                                                    </li>
                                                    <li>
                                                        <a href="<?php echo $siteurl; ?>#"> <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/mm-2.jpg" alt=""> </a>
                                                        <h3><a href="<?php echo $siteurl; ?>#">Proin id diam in nulla sagittempor</a></h3>
                                                    </li>
                                                </ul>
                                            </li>
                                            <li>
                                                <ul>
                                                    <li>
                                                        <a href="<?php echo $siteurl; ?>#"> <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/mm-3.jpg" alt=""> </a>
                                                        <h3><a href="<?php echo $siteurl; ?>#">Proin id diam in nulla sagittempor</a></h3>
                                                    </li>
                                                    <li>
                                                        <a href="<?php echo $siteurl; ?>#"> <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/mm-4.jpg" alt=""> </a>
                                                        <h3><a href="<?php echo $siteurl; ?>#">Proin id diam in nulla sagittempor</a></h3>
                                                    </li>
                                                </ul>
                                            </li>
                                            <li>
                                                <ul>

                                                    <li class="validation">
                                                        <h2>Blog Layouts</h2>
                                                    </li>
                                                    <li> <a href="http://html.crunchpress.com/materialmag/blog-full.html"> Blog Full</a> </li>
                                                    <li> <a href="http://html.crunchpress.com/materialmag/blog-medium.html"> Blog Medium</a> </li>
                                                    <li> <a href="http://html.crunchpress.com/materialmag/blog-column.html"> Blog Colum</a> </li>
                                                    <li> <a href="http://html.crunchpress.com/materialmag/blog-grid-modern.html"> Blog Grid Modren</a> </li>
                                                    <li> <a href="http://html.crunchpress.com/materialmag/blog-top-featured.html"> Blog Top Featured</a> </li>
                                                    <li> <a href="http://html.crunchpress.com/materialmag/blog-masonry.html"> Blog Masonry</a> </li>
                                                    <li> <a href="http://html.crunchpress.com/materialmag/single-post.html"> Single Post</a> </li>
                                                </ul>
                                            </li>
                                            <li>
                                                <ul>

                                                    <li class="validation">
                                                        <h2>Blog Category</h2>
                                                    </li>
                                                    <li> <a href="http://html.crunchpress.com/materialmag/category-layout-3.html">Photography</a> </li>
                                                    <li> <a href="http://html.crunchpress.com/materialmag/category-layout-2.html">Sports</a> </li>
                                                    <li> <a href="http://html.crunchpress.com/materialmag/category-layout-1.html">Fashion</a> </li>
                                                    <li> <a href="http://html.crunchpress.com/materialmag/category-layout-4.html">Lifestyle</a> </li>
                                                    <li> <a href="<?php echo $siteurl; ?>#">World</a> </li>
                                                    <li> <a href="<?php echo $siteurl; ?>#">Health</a> </li>
                                                    <li> <a href="<?php echo $siteurl; ?>#">Technology</a> </li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </li>
                                    <li><a href="#">FAQ</a></li>
                                    <li><a href="#">Contact Us</a></li>
                                    <li class="search-bar"> <i class="icon-7"></i>
                                        <ul class="drop-down hover-expand">
                                            <li>
                                                <form method="post">
                                                    <table>
                                                        <tbody>
                                                            <tr>
                                                                <td><input type="text" name="serach_bar" placeholder="Type Keyword Here"></td>
                                                                <td><input type="submit" value="Search"></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </form>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="random"><a href="#"><i class="icon-6"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
