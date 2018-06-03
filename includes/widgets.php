<?php
/**
 * Custom sidebar widgets
 *
 * @author AppThemes
 * @package JobRoller
 */

add_action( 'widgets_init', '_jr_widgets_init' );
add_action( 'widgets_init', '_jr_unregister_widgets' );


### Hook Callbacks

/**
 *
 * Register the custom sidebar widgets
 */
function _jr_widgets_init() {

	if ( ! is_blog_installed() ) {
		return;
	}

	register_widget( 'JR_Widget_125ads' );
	register_widget( 'JR_Widget_250ad' );
	register_widget( 'JR_Widget_pack_pricing' );
	register_widget( 'JR_Widget_Social' );
	register_widget( 'JR_Widget_Recent_Jobs' );
	register_widget( 'JR_Widget_Job_Categories' );
	register_widget( 'JR_Widget_Top_Listings_Today' );
	register_widget( 'JR_Widget_Top_Listings_Overall' );
	register_widget( 'JR_Widget_Jobs_Tag_Cloud' );
	register_widget( 'JR_Widget_Resumes_Tag_Cloud' );
	register_widget( 'JR_Widget_Resume_Categories' );
}

/**
 * Remove some of the default sidebar widgets.
 */
function _jr_unregister_widgets() {

	if ( ! is_admin() ) {
		return;
	}
	unregister_widget( 'WP_Widget_Calendar' );
	unregister_widget( 'WP_Widget_Search' );
}


### Widget Classes

/**
 * 125 Ads.
 */
class JR_Widget_125ads extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'description' => __( 'Places an ad space in the sidebar for 125x125 ads', APP_TD ) );
		$control_ops = array( 'width' => 500, 'height' => 350 );
		parent::__construct( false, __( 'JobRoller 125x125 Ad Space', APP_TD ), $widget_ops, $control_ops );
	}

	public function widget( $args, $instance ) {

		extract( $args );

		$title = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : false;
		$newin = isset( $instance['newin'] ) ? $instance['newin'] : false;

		if ( isset( $instance['ads'] ) ) :

			// separate the ad line items into an array
			$ads = explode( "\n", $instance['ads'] );

			if ( sizeof( $ads ) > 0 ) :

				echo $before_widget;

				if ( $title ) {
					echo $before_title . $title . $after_title;
				}

				echo '<div class="pad5"></div>';

				if ( $newin ) {
					$newin = 'target="_blank"';
				}
				?>

				<ul class="ads">
					<?php
					$alt = 1;
					foreach ( $ads as $ad ) :
						if ( $ad && strstr( $ad, '|' ) ) {
							$alt = $alt * -1;
							$this_ad = explode( '|', $ad );
							echo '<li class="';
							if ( $alt == 1 ) {
								echo 'alt';
							}
							echo '"><a href="' . $this_ad[0] . '" rel="' . $this_ad[3] . '" ' . $newin . '><img src="' . $this_ad[1] . '" width="125" height="125" alt="' . $this_ad[2] . '" /></a></li>';
						}
					endforeach;
					?>
				</ul>

				<?php
				echo $after_widget;

			endif;

		endif;
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['ads'] = strip_tags( $new_instance['ads'] );
		$instance['newin'] = $new_instance['newin'];

		return $instance;
	}

	public function form( $instance ) {

		// load up the default values
		$default_ads = "https://appthemes.com|" . get_template_directory_uri() . "/images/ad125a.gif|Ad 1|nofollow\n" . "https://appthemes.com|" . get_template_directory_uri() . "/images/ad125b.gif|Ad 2|follow\n" . "https://appthemes.com|" . get_template_directory_uri() . "/images/ad125a.gif|Ad 3|nofollow\n" . "https://appthemes.com|" . get_template_directory_uri() . "/images/ad125b.gif|Ad 4|follow";
		$defaults = array( 'title' => __( 'Sponsored Ads', APP_TD ), 'ads' => $default_ads, 'rel' => true );
		$instance = wp_parse_args( (array) $instance, $defaults );
	?>
		<p>
			<label><?php _e( 'Title:', APP_TD ) ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p>

		<p>
			<label><?php _e( 'Ads:', APP_TD ); ?></label>
			<textarea class="widefat" rows="16" cols="20" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name( 'ads' ); ?>" cols="5" rows="5"><?php echo $instance['ads']; ?></textarea>
			<?php _e( 'Enter one ad entry per line in the following format:<br /> <code>URL|Image URL|Image Alt Text|rel</code><br /><strong>Note:</strong> You must hit your &quot;enter/return&quot; key after each ad entry otherwise the ads will not display properly.', APP_TD ); ?>
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php (!empty( $instance['newin'] ) ? checked( $instance['newin'], 'on' ) : '' ) ?> id="<?php echo $this->get_field_id( 'newin' ); ?>" name="<?php echo $this->get_field_name( 'newin' ); ?>" />
			<label><?php _e( 'Open ads in a new window?', APP_TD ); ?></label>
		</p>
	<?php
	}

}


/**
 * 250x250 Ad Space Widget.
 */
class JR_Widget_250ad extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'description' => __( 'Places an ad space in the sidebar for a 250x250 ad', APP_TD ) );
		$control_ops = array( 'width' => 500, 'height' => 350 );
		parent::__construct( false, __( 'JobRoller 250x250 Ad Space', APP_TD ), $widget_ops, $control_ops );
	}

	public function widget( $args, $instance ) {

		extract( $args );

		$title = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : false;
		$newin = isset( $instance['newin'] ) ? $instance['newin'] : false;

		if ( isset( $instance['ads'] ) ) {

			// separate the ad line items into an array
			$ads = explode( "\n", $instance['ads'] );

			if ( sizeof( $ads ) > 0 ) {

				echo $before_widget;

				if ( $title ) {
					echo $before_title . $title . $after_title;
				}

				echo '<div class="pad5"></div>';

				if ( $newin ) {
					$newin = 'target="_blank"';
				}

				foreach( $ads as $ad ):
					if ( $ad && strstr( $ad, '|' ) ) {
						$this_ad = explode( '|', $ad );
						echo '<a href="' . esc_url( $this_ad[0] ) . '" rel="' . esc_attr( $this_ad[3] ) . '" ' . $newin . '><img src="' . esc_url( $this_ad[1] ) . '" width="250" height="250" alt="' . esc_attr( $this_ad[2] ) . '" /></a><div class="pad5"></div>';
					}
				endforeach;

				echo $after_widget;

			}

		}
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['ads'] = strip_tags( $new_instance['ads'] );
		$instance['newin'] = $new_instance['newin'];

		return $instance;
	}

	public function form( $instance ) {

		// load up the default values
		$default_ads = "https://appthemes.com|" . get_template_directory_uri() . "/images/ad250.png|Ad 1|follow\n";
		$defaults = array( 'title' => __( 'Sponsored Ads', APP_TD ), 'ads' => $default_ads, 'rel' => true );
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
			<label><?php _e( 'Title:', APP_TD ) ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p>

		<p>
			<label><?php _e( 'Ads:', APP_TD ); ?></label>
			<textarea class="widefat" rows="16" cols="20" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name( 'ads' ); ?>" cols="5" rows="5"><?php echo $instance['ads']; ?></textarea>
		<?php _e( 'Enter one ad entry per line in the following format:<br /> <code>URL|Image URL|Image Alt Text|rel</code><br /><strong>Note:</strong> You must hit your &quot;enter/return&quot; key after each ad entry otherwise the ads will not display properly.', APP_TD ); ?>
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php if ( isset( $instance['newin'] ) ) checked( $instance['newin'], 'on' ); ?> id="<?php echo $this->get_field_id( 'newin' ); ?>" name="<?php echo $this->get_field_name( 'newin' ); ?>" />
			<label><?php _e( 'Open ad in a new window?', APP_TD ); ?></label>
		</p>
		<?php
	}

}


/**
 * Job Packs and Pricing Widget.
 */
class JR_Widget_pack_pricing extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'description' => __( 'Displays Job Packs and Pricing information to the user.', APP_TD ) );
		parent::__construct( false, __( 'JobRoller Job Packs', APP_TD ), $widget_ops );
	}

	public function widget( $args, $instance ) {

		extract( $args );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Available Job Packs', APP_TD ) : $instance['title'], $instance, $this->id_base );
		$buy = isset( $instance['buy'] ) && jr_allow_purchase_separate_packs() ? $instance['buy'] : 'no';

		$packs = jr_get_plan_packs( jr_get_available_plans() );

		if ( sizeof( $packs ) > 0 ) :
			echo $before_widget;

			if ( isset( $title ) && $title ) {
				echo $before_title . $title . $after_title;
			}

			echo '<ul class="pack_overview">';

			foreach ( $packs as $pack ) :
				$cost = 0;

				if ( empty( $pack['plan_data'][JR_FIELD_PREFIX . 'jobs_limit'] ) ) {
					$pack['plan_data'][JR_FIELD_PREFIX . 'jobs_limit'] = __( 'Unlimited', APP_TD );
				}

				if ( !empty( $pack['plan_data'][JR_FIELD_PREFIX . 'pack_duration'] ) ) {
					$pack['plan_data'][JR_FIELD_PREFIX . 'pack_duration'] = ', ' . __( ' usable within ', APP_TD ) . $pack['plan_data'][JR_FIELD_PREFIX . 'pack_duration'] . __( ' days', APP_TD );
				} else {
					$pack['plan_data'][JR_FIELD_PREFIX . 'pack_duration'] = '';
				}

				if ( $pack['plan_data'][JR_FIELD_PREFIX . 'duration'] ) {
					$pack['plan_data'][JR_FIELD_PREFIX . 'duration'] = __( ' lasting ', APP_TD ) . $pack['plan_data'][JR_FIELD_PREFIX . 'duration'] . __( ' days', APP_TD );
				} else {
					$pack['plan_data'][JR_FIELD_PREFIX . 'duration'] = __( ' Endless', APP_TD );
				}

				if ( $pack['plan_data'][JR_FIELD_PREFIX . 'price'] ) {
					$pack['plan_data'][JR_FIELD_PREFIX . 'price'] = appthemes_get_price( $pack['plan_data'][JR_FIELD_PREFIX . 'price'] );
					$cost = 1;
				} else {
					$pack['plan_data'][JR_FIELD_PREFIX . 'price'] = __( 'Free', APP_TD );
				}

				echo '<li><span class="cost">' . $pack['plan_data'][JR_FIELD_PREFIX . 'price'] . '</span><p><strong>' . $pack['plan_data']['title'] . '</strong><br />' . $pack['plan_data'][JR_FIELD_PREFIX . 'jobs_limit'] . ' ' . __( 'Jobs', APP_TD ) . '' . $pack['plan_data'][JR_FIELD_PREFIX . 'duration'] . $pack['plan_data'][JR_FIELD_PREFIX . 'pack_duration'] . '</p></li>';

				$checked = '';
			endforeach;

			if ( 'yes' == $buy ) :
			?>
				<li>
					<a class="button buy-pack-small" href="<?php echo esc_url( add_query_arg( array( 'tab' => 'packs' ), jr_get_purchase_packs_url() ) ); ?>"><span><?php _e( 'Buy Packs', APP_TD ); ?></span></a>
				</li>
			<?php
			endif;

			echo '</ul>';

			echo $after_widget;
		endif;
	}

	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}

	public function form( $instance ) {
		$enable_buy = jr_allow_purchase_separate_packs();

		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$buy = isset( $instance['buy'] ) ? esc_attr( $instance['buy'] ) : 'no';
	?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', APP_TD ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php if ( $enable_buy == 'yes' ): ?>
			<p>
				<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'buy' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'buy' ) ); ?>" value="yes" <?php echo checked( $buy == 'yes' ); ?>> <?php _e( 'Enable <i>Buy Now</i> button', APP_TD ); ?>
			</p>
		<?php endif;
	}

}


/**
 * Facebook Like Box Widget.
 */
class JR_Widget_Facebook extends APP_Widget_Facebook {

	public function __construct() {

		$args = array(
			'id_base'  => 'jr_facebook',
			'name'     => __( ' JobRoller Facebook Like Box', APP_TD ),
			'defaults' => array(
				'width'  => '260',
				'height' => '365',
			),
		);

		parent::__construct( $args );
	}

	public function content( $instance ) {

		// Migrate old widget.
		if ( isset( $instance['fid'] ) ) {
			$this->defaults['pid'] = $instance['fid'];
		}

		parent::content( $instance );
	}

	public function form( $instance ) {
		// Migrate old widget.
		if ( isset( $instance['fid'] ) ) {
			$this->defaults['pid'] = $instance['fid'];
		}

		parent::form( $instance );
	}

}


/**
 * Social RSS and Twitter Widget.
 */
class JR_Widget_Social extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'description' => __( 'This places Twitter and RSS Feed icons in your sidebar.', APP_TD ) );
		parent::__construct( false, __( 'JobRoller Twitter &amp; RSS Icons', APP_TD ), $widget_ops );
	}

	public function widget( $args, $instance ) {
		global $jr_options;

		extract( $args );
	?>

		<?php echo $before_widget; ?>

		<ul class="social-widgets">
			<li class="rss-balloon">
				<a href="<?php echo esc_url( $jr_options->jr_feedburner_url ? $jr_options->jr_feedburner_url : get_bloginfo_rss( 'rss2_url' ) . '?post_type=job_listing' );  ?>"><i class="icon dashicons-before"></i><?php _e( 'Subscribe', APP_TD ) ?></a><br/>
				<span><?php _e( 'Receive the latest job listings', APP_TD ) ?></span>
			</li>

			<li class="twitter-balloon">
				<a href="https://www.twitter.com/<?php echo esc_attr_e( $jr_options->jr_twitter_id) ; ?>" title="<?php esc_attr_e( 'Follow us on Twitter', APP_TD ); ?>"><i class="icon dashicons-before"></i><?php _e( 'Follow Us', APP_TD ); ?></a><br/>
				<span><?php _e( 'Come join us on Twitter', APP_TD ) ?></span>
			</li>

		</ul>

		<?php echo $after_widget; ?>

		<?php
	}

	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}

	public function form( $instance ) {
	?>
		<p><?php _e( 'There are no options for this widget.', APP_TD ) ?></p>
	<?php
	}

}


/**
 * Recent Job Listings Widget.
 */
class JR_Widget_Recent_Jobs extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'classname' => 'widget_recent_entries', 'description' => __( "The most recent job listings on your site", APP_TD ) );
		parent::__construct( 'recent-jobs', __( 'JobRoller New Job Listings', APP_TD ), $widget_ops );
		$this->alt_option_name = 'widget_recent_entries';

		add_action( 'save_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( &$this, 'flush_widget_cache' ) );
	}

	public function widget( $args, $instance ) {
		$cache = wp_cache_get( 'widget_recent_jobs', 'widget' );

		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		if ( isset( $cache[$args['widget_id']] ) ) {
			echo $cache[$args['widget_id']];
			return;
		}

		ob_start();

		extract( $args );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'New Job Listings', APP_TD ) : $instance['title'], $instance, $this->id_base );

		if ( !$number = (int) $instance['number'] ) {
			$number = 10;
		} else if ( $number < 1 ) {
			$number = 1;
		} else if ( $number > 15 ) {
			$number = 15;
		}

		$r = new WP_Query( array( 'showposts' => $number, 'nopaging' => 0, 'post_status' => 'publish', 'post_type' => 'job_listing', 'ignore_sticky_posts' => 1 ) );
		if ( $r->have_posts() ) :
		?>
			<?php echo $before_widget; ?>

			<?php if ( $title ): echo $before_title . $title . $after_title; endif; ?>
			<ul>
				<?php while ( $r->have_posts() ) : $r->the_post(); ?>
						<li><a href="<?php the_permalink() ?>" title="<?php echo esc_attr( get_the_title() ? get_the_title() : get_the_ID() ); ?>"><?php if ( get_the_title() ) the_title();
					else the_ID(); ?></a></li>
				<?php endwhile; ?>
			</ul>

			<?php echo $after_widget; ?>

			<?php
			// Reset the global $the_post as this query will have stomped on it
			wp_reset_postdata();

		endif;

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_set( 'widget_recent_jobs', $cache, 'widget' );
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = (int) $new_instance['number'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['widget_recent_entries'] ) ) {
			delete_option( 'widget_recent_entries' );
		}

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete( 'widget_recent_jobs', 'widget' );
	}

	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		if ( !isset( $instance['number'] ) || !$number = (int) $instance['number'] ) {
			$number = 5;
		}
	?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', APP_TD ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of jobs to show:', APP_TD ); ?></label>
			<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" />
		</p>
	<?php
	}

}


/**
 * Job Categories Widget.
 */
class JR_Widget_Job_Categories extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'classname' => 'widget_job_categories', 'description' => __( "A list or dropdown of job categories", APP_TD ) );
		parent::__construct( 'job_categories', __( 'Job Categories', APP_TD ), $widget_ops );
	}

	public function widget( $args, $instance ) {
		global $jr_options;

		extract( $args );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Job Categories', APP_TD ) : $instance['title'], $instance, $this->id_base );
		$c = (int) $instance['count'];
		$h = (int) $instance['hierarchical'];
		$d = (int) $instance['dropdown'];

		echo $before_widget;

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		if ( $d ) {

			$args = array(
				'app_pad_counts' => true,
				'hide_empty' => ! $jr_options->jr_show_empty_categories,
			);

			$terms = get_terms( APP_TAX_CAT, $args );
			$output = '<select name="job_cat" id="dropdown_job_cat"><option value="">' . __( 'Select Category', APP_TD ) . '</option>';
			if ( $terms ) {
				$post_count = '';
				foreach ( $terms as $term ) {
					if ( $c ) {
						$post_count = sprintf( ' (%d)', $term->count );
					}
					$output .= "<option value='" . esc_attr( $term->slug ) . "'>" . $term->name . $post_count . "</option>";
				}
			}
			$output .= "</select>";
			echo $output;
			?>

			<script type='text/javascript'>
				/* <![CDATA[ */
				var dropdown = document.getElementById("dropdown_job_cat");
				function onCatChange() {
					if (dropdown.options[dropdown.selectedIndex].value) {
						location.href = "<?php echo home_url(); ?>/?job_cat=" + dropdown.options[dropdown.selectedIndex].value;
					}
				}
				dropdown.onchange = onCatChange;
				/* ]]> */
			</script>

			<?php
		} else {
			?>
			<ul>
				<?php
				$cat_args = array(
					'title_li'   => '',
					'taxonomy'   => APP_TAX_CAT,
					'show_count' => $c,
					'hide_empty' => ! $jr_options->jr_show_empty_categories,
				);
				wp_list_categories( apply_filters( 'widget_job_categories_args', $cat_args ) );
				?>
			</ul>
			<?php
		}

		echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['count'] = !empty( $new_instance['count'] ) ? 1 : 0;
		$instance['hierarchical'] = !empty( $new_instance['hierarchical'] ) ? 1 : 0;
		$instance['dropdown'] = !empty( $new_instance['dropdown'] ) ? 1 : 0;

		return $instance;
	}

	public function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = esc_attr( $instance['title'] );
		$count = isset( $instance['count'] ) ? (bool) $instance['count'] : false;
		$hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
		$dropdown = isset( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;
	?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', APP_TD ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'dropdown' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'dropdown' ) ); ?>"<?php checked( $dropdown ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'dropdown' ) ); ?>"><?php _e( 'Show as dropdown', APP_TD ); ?></label><br />

			<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>"<?php checked( $count ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php _e( 'Show post counts', APP_TD ); ?></label><br />

			<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'hierarchical' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hierarchical' ) ); ?>"<?php checked( $hierarchical ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'hierarchical' ) ); ?>"><?php _e( 'Show hierarchy', APP_TD ); ?></label>
		</p>
	<?php
	}

}


/**
 * Top Listings Today Widget.
 */
class JR_Widget_Top_Listings_Today extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'description' => __( 'Your sidebar top listings today', APP_TD ) );
		parent::__construct( 'top_listings', __( 'JobRoller Popular Listings Today', APP_TD ), $widget_ops );
	}

	public function widget( $args, $instance ) {

		extract( $args );

		$post_type = (isset( $instance['post_type'] ) && $instance['post_type']) ? $instance['post_type'] : 'job_listing';

		if ( $post_type == 'job_listing' ) {
			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Popular Jobs Today', APP_TD ) : $instance['title'] );
		} else {
			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Popular Resumes Today', APP_TD ) : $instance['title'] );
		}

		echo $before_widget;

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		jr_todays_count_widget( $post_type, 10 );

		echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {
		$instance['title'] = strip_tags( stripslashes( $new_instance['title'] ) );
		$instance['post_type'] = strip_tags( stripslashes( $new_instance['post_type'] ) );
		return $instance;
	}

	public function form( $instance ) {

		$post_type = (isset( $instance['post_type'] )) ? $instance['post_type'] : 'job_listing';
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', APP_TD ) ?></label>
		    <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php if ( isset( $instance['title'] ) ) {
			echo esc_attr( $instance['title'] );
		} ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php _e( 'Post type:', APP_TD ) ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'post_type' ); ?>" name="<?php echo $this->get_field_name( 'post_type' ); ?>">
				<option value="job_listing" <?php selected( 'job_listing', $post_type ) ?>><?php _e( 'Job', APP_TD ) ?></option>
				<option value="resume" <?php selected( 'resume', $post_type ) ?>><?php _e( 'Resume', APP_TD ) ?></option>
			</select>
		</p>
		<?php
	}

}


/**
 * Top Listings Overall Widget.
 */
class JR_Widget_Top_Listings_Overall extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'description' => __( 'Your sidebar top listings overall', APP_TD ) );
		parent::__construct( 'top_listings_overall', __( 'JobRoller Popular listings Overall', APP_TD ), $widget_ops );
	}

	public function widget( $args, $instance ) {

		extract( $args );

		$post_type = (isset( $instance['post_type'] ) && $instance['post_type']) ? $instance['post_type'] : 'job_listing';

		if ( $post_type == 'job_listing' ) {
			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Popular Jobs Overall', APP_TD ) : $instance['title'] );
		} else {
			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Popular Resumes Overall', APP_TD ) : $instance['title'] );
		}

		echo $before_widget;

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		jr_todays_overall_count_widget( $post_type, 10 );

		echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {
		$instance['title'] = strip_tags( stripslashes( $new_instance['title'] ) );
		$instance['post_type'] = strip_tags( stripslashes( $new_instance['post_type'] ) );
		return $instance;
	}

	public function form( $instance ) {

		$post_type = (isset( $instance['post_type'] )) ? $instance['post_type'] : 'job_listing';
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', APP_TD ) ?></label>
		    <input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php if ( isset( $instance['title'] ) ) {
			echo esc_attr( $instance['title'] );
		} ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php _e( 'Post type:', APP_TD ) ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'post_type' ); ?>" name="<?php echo $this->get_field_name( 'post_type' ); ?>">
				<option value="job_listing" <?php selected( 'job_listing', $post_type ) ?>><?php _e( 'Job', APP_TD ) ?></option>
				<option value="resume" <?php selected( 'resume', $post_type ) ?>><?php _e( 'Resume', APP_TD ) ?></option>
			</select>
		</p>
		<?php
	}

}


/**
 * Jobs Tag Cloud Widget.
 */
class JR_Widget_Jobs_Tag_Cloud extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'description' => __( 'Your most used job tags in cloud format', APP_TD ) );
		parent::__construct( 'job_tag_cloud', __( 'JobRoller Jobs Tag Cloud', APP_TD ), $widget_ops );
	}

	public function widget( $args, $instance ) {
		extract( $args );
		$current_taxonomy = $this->_get_current_taxonomy( $instance );
		if ( !empty( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			if ( 'job_tag' == $current_taxonomy ) {
				$title = __( 'Job Tags', APP_TD );
			} else {
				$tax = get_taxonomy( $current_taxonomy );
				$title = $tax->labels->name;
			}
		}
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $before_widget;

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		echo '<div class="tag_cloud">';

		wp_tag_cloud( apply_filters( 'widget_tag_cloud_args', array( 'taxonomy' => $current_taxonomy ) ) );

		echo "</div>\n";

		echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {
		$instance['title'] = strip_tags( stripslashes( $new_instance['title'] ) );
		$instance['taxonomy'] = stripslashes( $new_instance['taxonomy'] );
		return $instance;
	}

	public function form( $instance ) {
		$current_taxonomy = $this->_get_current_taxonomy( $instance );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', APP_TD ) ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( isset( $instance['title'] ) ? $instance['title'] : '' ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php _e( 'Taxonomy:', APP_TD ) ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'taxonomy' ); ?>" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>">
				<?php
				foreach ( get_object_taxonomies( 'job_listing' ) as $taxonomy ) :
					$tax = get_taxonomy( $taxonomy );
					if ( !$tax->show_tagcloud || empty( $tax->labels->name ) ): continue; endif;
					?>
					<option value="<?php echo esc_attr( $taxonomy ) ?>" <?php selected( $taxonomy, $current_taxonomy ) ?>><?php echo $tax->labels->name; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	function _get_current_taxonomy( $instance ) {
		if ( !empty( $instance['taxonomy'] ) && taxonomy_exists( $instance['taxonomy'] ) ) {
			return $instance['taxonomy'];
		}

		return 'post_tag';
	}

}


/**
 * Resumes Tag Cloud Widget.
 */
class JR_Widget_Resumes_Tag_Cloud extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'description' => __( 'Your most used resume tags in cloud format', APP_TD ) );
		parent::__construct( 'resume_tag_cloud', __( 'JobRoller Resumes Tag Cloud', APP_TD ), $widget_ops );
	}

	public function widget( $args, $instance ) {
		extract( $args );
		$current_taxonomy = $this->_get_current_taxonomy( $instance );
		if ( !empty( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			if ( 'job_tag' == $current_taxonomy ) {
				$title = __( 'Resume Tags', APP_TD );
			} else {
				$tax = get_taxonomy( $current_taxonomy );
				$title = $tax->labels->name;
			}
		}
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $before_widget;

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		echo '<div class="tag_cloud">';

		wp_tag_cloud( apply_filters( 'widget_tag_cloud_args', array( 'taxonomy' => $current_taxonomy ) ) );

		echo "</div>\n";

		echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {
		$instance['title'] = strip_tags( stripslashes( $new_instance['title'] ) );
		$instance['taxonomy'] = stripslashes( $new_instance['taxonomy'] );
		return $instance;
	}

	public function form( $instance ) {
		$current_taxonomy = $this->_get_current_taxonomy( $instance );
	?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', APP_TD ) ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( isset( $instance['title'] ) ? $instance['title'] : '' ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php _e( 'Taxonomy:', APP_TD ) ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'taxonomy' ); ?>" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>">
				<?php
				foreach ( get_object_taxonomies( 'resume' ) as $taxonomy ) :
					$tax = get_taxonomy( $taxonomy );
					if ( ! $tax->show_tagcloud || empty( $tax->labels->name ) ): continue; endif;
				?> <option value="<?php echo esc_attr( $taxonomy ) ?>" <?php selected( $taxonomy, $current_taxonomy ) ?>><?php echo $tax->labels->name; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
	<?php
	}

	function _get_current_taxonomy( $instance ) {
		if ( ! empty( $instance['taxonomy'] ) && taxonomy_exists( $instance['taxonomy'] ) ) {
			return $instance['taxonomy'];
		}

		return 'resume_specialities';
	}

}


/**
 * Resume Categories Widget.
 */
class JR_Widget_Resume_Categories extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 'classname' => 'widget_resume_categories', 'description' => __( "A list of resume categories", APP_TD ) );
		parent::__construct( 'resume_categories', __( 'Resume Categories', APP_TD ), $widget_ops );
	}

	public function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Resume Categories', APP_TD ) : $instance['title'], $instance, $this->id_base );
		$c = $instance['count'] ? '1' : '0';
		$h = $instance['hierarchical'] ? '1' : '0';

		echo $before_widget;

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		$cat_args = array( 'orderby' => 'name', 'show_count' => $c, 'hierarchical' => $h, 'taxonomy' => 'resume_category', 'title_li' => '' );

		echo '<ul>';

		wp_list_categories( apply_filters( 'widget_job_categories_args', $cat_args ) );

		echo '</ul>';

		echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['count'] = !empty( $new_instance['count'] ) ? 1 : 0;
		$instance['hierarchical'] = !empty( $new_instance['hierarchical'] ) ? 1 : 0;
		return $instance;
	}

	public function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = esc_attr( $instance['title'] );
		$count = isset( $instance['count'] ) ? (bool) $instance['count'] : false;
		$hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
	?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', APP_TD ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>"<?php checked( $count ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php _e( 'Show post counts', APP_TD ); ?></label>
		</p>

		<p>
			<input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'hierarchical' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hierarchical' ) ); ?>"<?php checked( $hierarchical ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'hierarchical' ) ); ?>"><?php _e( 'Show hierarchy', APP_TD ); ?></label>
		</p>
	<?php
	}

}
