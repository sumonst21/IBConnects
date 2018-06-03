<?php

add_action( 'pre_get_posts', 'custom_post_author_archive' );

if ( current_user_can('manage_options') ) {
  add_action( 'show_user_profile', 'jr_profile_fields', 10 );
  add_action( 'edit_user_profile', 'jr_profile_fields', 10 );
  add_action( 'personal_options_update', 'jr_save_profile_fields' );
  add_action( 'edit_user_profile_update', 'jr_save_profile_fields' );
}

add_action( 'appthemes_before_post', 'jr_viewed_jobs' );
add_action( 'appthemes_before_post', 'jr_star_jobs' );


/**
 * Allow changing of values from user page
 */
function jr_profile_fields( $user ) {
  global $jr_options;

  $plans = jr_get_available_plans();
?>

  <?php if ($plans ) : ?>

    <h3><?php _e('Job Plans', APP_TD); ?></h3>

    <table class="form-table" >

      <?php if ( 'pack' == $jr_options->plan_type ) : ?>

        <tr>
          <th><label><?php _e('Current User Packs', APP_TD); ?></label></th>
          <td>
            <?php
              $user_packs = jr_get_user_plan_packs( $user->ID );
              if ( sizeof($user_packs) > 0 ) :

                echo '
                <table class="job_packs">
                  <thead>
                    <tr>
                    <th>'.__( 'Name', APP_TD ).'</th>
                    <th>'.__( 'Jobs Remaining', APP_TD ).'</th>
                    <th>'.__( 'Jobs Duration', APP_TD ).'</th>
                    <th>'.__( 'Expire Date', APP_TD ).'</th>
                    <th>'.__( 'Delete Pack?', APP_TD ).'</th>
                    </tr>
                  </thead>
                  <tbody>';

                foreach ( $user_packs as $pack ) {

                  if ( ! $pack['meta']['jobs_limit'] ) {
                    $jobs_remain = __('Unlimited', APP_TD);
                  } else {
                    $jobs_remain = jr_pack_jobs_remain( $pack );
                  }

                  if ( $pack['meta']['end_date'] > 0 ) {
                    $expire_date = appthemes_display_date( $pack['meta']['end_date'] );
                  } else {
                    $expire_date = __( 'Endless', APP_TD );
                  }

                  if ( $pack['meta']['jobs_duration'] > 0 ) {
                    $jobs_duration = sprintf( '%s %s', $pack['meta']['jobs_duration'], _n( 'Day', 'Days' , $pack['meta']['jobs_duration'] ) );
                  } else {
                    $jobs_duration = __( 'Endless', APP_TD );
                  }

                  echo '<tr>
                    <td>'.$pack['plan_data']['title'].'</td>
                    <td>'.$jobs_remain.'</td>
                    <td>'.$jobs_duration.'</td>
                    <td>'.$expire_date.'</td>
                    <td><input type="checkbox" name="delete_pack[]" value="'.$pack['plan_ref_id'].'" /></td>
                  </tr>';

                }

                echo '</tbody></table>';

              else :
                ?><p><?php _e('No active Packs found.', APP_TD); ?></p><?php
              endif;
            ?>
          </td>
        </tr>
        <tr>
          <th><label><?php _e('Assign Pack', APP_TD); ?></label></th>
          <td>
            <select name="give_job_pack" class="assign-plan"><option value=""><?php _e('Choose a Pack...', APP_TD); ?></option>
            <?php
              $plans = jr_get_available_plans();
              if ( sizeof($plans) > 0 ) {

                foreach ( $plans as $key => $plan ) {
                  echo '<option value="'.$plan['post_data']->ID.'">'.$plan['post_data']->post_title.'</option>';
                }

              }
            ?>
            </select>
          </td>
        </tr>

      <?php endif; ?>

      <tr>
        <th><label><?php _e( 'Reset Usage', APP_TD ); ?></label></th>
        <td>
          <input type="checkbox" name="reset_job_plans_usage" value="1">
          <?php echo html( 'em', __( 'This option resets the plans usage for this user. All \'Job Plans\' will be available for selection until the usage limit is reached again.', APP_TD ) ); ?>
        </td>
      </tr>

    </table>

  <?php endif; ?>

  <?php if ( jr_viewing_resumes_require_subscription() || jr_resume_valid_subscr( $user->ID ) ): ?>

    <h3><?php _e('Resumes Subscriptions', APP_TD); ?></h3>

    <table class="form-table">

      <tr>
        <th><label><?php _e( 'Current Subscription', APP_TD ); ?></label></th>
        <td>
          <em>
          <?php
            $plan_id = get_user_meta( $user->ID, '_valid_resume_subscription', true );
            if ( $plan_id ) {

              if ( $plan_id > 1 ) {
                $plan_data = get_post_custom( $plan_id );
                echo $plan_data['title'][0];
              } else {
                echo __( 'N/A', APP_TD );
              }

            } else {
              echo html( 'p', __( 'None', APP_TD  ) );
              $resumes_access = jr_user_resumes_access( $user->ID );
              if ( ! empty($resumes_access['access']) ) {

                echo html( 'strong', __( 'Temporary access granted by purchased Plans: ', APP_TD ) );
                foreach ( $resumes_access['access'] as $key => $access ) {
                  echo html( 'div',  sprintf( __( ' %s until <strong>%s</strong>', APP_TD ), $access['description'], $access['end_date'] ) );
                }

              }
            }
          ?>
          </em>
        </td>
      </tr>

      <?php if ( $plan_id ) : ?>

        <tr>
          <th>&nbsp;</th>
          <td>
            <input type="checkbox" name="cancel_resume_subscription" value="1"> <?php _e( 'Cancel Subscription', APP_TD ); ?>
          </td>
        </tr>

      <?php endif; ?>

      <tr>
        <th><label><?php _e( 'Assign Subscription', APP_TD ); ?></label></th>

        <td>
          <select name="resumes_access" class="assign-plan"><option value=""><?php _e( 'Choose a Subscription...', APP_TD ); ?></option>
          <?php
            $plans = jr_get_available_plans( array( 'post_type' => APPTHEMES_RESUMES_PLAN_PTYPE ) );
            if ( sizeof($plans) > 0 ) {

              foreach ( $plans as $key => $plan ) {
                if ( $plan_id == $plan['post_data']->ID ) continue;
                echo '<option value="'.$plan['post_data']->ID.'">'.$plan['post_data']->post_title.'</option>';
              }

            }
          ?>
          </select>
        </td>
      </tr>

      <tr>
        <th><label><?php _e( 'Reset Usage', APP_TD ); ?></label></th>
        <td>
          <input type="checkbox" name="reset_resume_plans_usage" value="1">
          <?php echo html( 'em', __( 'This option resets the plans usage for this user. All \'Resume Plans\' will be available for selection until the usage limit is reached again.', APP_TD ) ); ?>
        </td>
      </tr>

    </table>

  <?php endif; ?>

<?php
}

function jr_save_profile_fields( $user_id ) {

  if ( ! current_user_can( 'edit_user', $user_id ) ) {
    return false;
  }

  if ( ! empty($_POST['reset_job_plans_usage']) ) {
    jr_reset_user_plan_usage( $user_id, APPTHEMES_PRICE_PLAN_PTYPE );
  }

  if ( ! empty($_POST['reset_resume_plans_usage']) ) {
    jr_reset_user_plan_usage( $user_id, APPTHEMES_RESUMES_PLAN_PTYPE );
  }

  if ( ! empty($_POST['resumes_access']) ) {

    // start subscription
    $plan_id = intval($_POST['resumes_access']);
    $plan_data = get_post_custom( $plan_id );

    if ( !$plan_data ) return;

    do_action( 'user_resume_subscription_started', $user_id, $plan_id, $plan_data );

  } elseif ( !empty($_POST['cancel_resume_subscription']) ) {
    // end subscription
    do_action( 'user_resume_subscription_ended', $user_id );
  }

  if ( ! empty($_POST['give_job_pack']) ) {
    $plan_id = (int) $_POST['give_job_pack'];

    jr_give_pack_to_user( $user_id, $plan_id );
  }

  if ( isset($_POST['delete_pack']) && is_array($_POST['delete_pack']) && sizeof($_POST['delete_pack']) > 0 ) {

    foreach ( $_POST['delete_pack'] as $plan_umeta_id ) {
      jr_expire_user_pack( $user_id, $plan_umeta_id );
    }

  }

}

/**
 * Track User Job Views
 */
function jr_viewed_jobs() {
  global $post;

  if ( is_single() && is_user_logged_in() && get_post_type() == 'job_listing' ) {

    $_viewed_jobs = get_user_meta( get_current_user_id(), '_viewed_jobs', true );
    if ( ! is_array( $_viewed_jobs ) ) {
      $_viewed_jobs = array();
    }

    if ( ! in_array( $post->ID, $_viewed_jobs ) ) {
      $_viewed_jobs[] = $post->ID;
    }

    $_viewed_jobs = array_reverse($_viewed_jobs);
    $_viewed_jobs = array_slice($_viewed_jobs, 0, 5);
    $_viewed_jobs = array_reverse($_viewed_jobs);

    update_user_meta(get_current_user_id(), '_viewed_jobs', $_viewed_jobs);
  }

}


/**
 * Star Jobs
 */
function jr_star_jobs() {
  global $post;

  if ( isset($_GET['star']) && is_single() && is_user_logged_in() && get_post_type() == 'job_listing' ) {

    $_starred_jobs = get_user_meta(get_current_user_id(), '_starred_jobs', true);
    if ( ! is_array( $_starred_jobs ) ) {
      $_starred_jobs = array();
    }

    if ( $_GET['star'] == 'true' ) {
      if ( ! in_array($post->ID, $_starred_jobs ) ) {
        $_starred_jobs[] = $post->ID;
      }
    } else {
      $_starred_jobs = array_diff($_starred_jobs, array($post->ID));
    }

    update_user_meta(get_current_user_id(), '_starred_jobs', $_starred_jobs);
  }

}

/**
 * Get job seeker prefs table
 */
function jr_seeker_prefs( $user_id ) {

  $prefs = '<table cellspacing="0" class="user_prefs">';

  $availability_month   = get_user_meta($user_id, 'availability_month', true);
  $availability_year   = get_user_meta($user_id, 'availability_year', true);
  //$your_location      = get_user_meta($user_id, 'your_location', true);
  $career_status       = get_user_meta($user_id, 'career_status', true);
  $willing_to_relocate   = get_user_meta($user_id, 'willing_to_relocate', true);
  $willing_to_travel     = get_user_meta($user_id, 'willing_to_travel', true);
  //$where_you_can_work   = get_user_meta($user_id, 'where_you_can_work', true);

  if ( $career_status ) {
    $prefs .= '<tr><th>' . __('Career Status:', APP_TD) . '</th><td>';
    switch ($career_status) :
      case "looking" :
        $prefs .= __('Actively looking', APP_TD);
      break;
      case "open" :
        $prefs .= __('Open to new opportunities', APP_TD);
      break;
      case "notlooking" :
        $prefs .= __('Not actively looking', APP_TD);
      break;
    endswitch;
    echo '</td></tr>';
  }

  //if ($your_location) $prefs .= '<tr><th>' . __('Location:', APP_TD) . '</th><td>' . wptexturize($your_location) . '</td></tr>';

  if ( $availability_month && $availability_year ) {
    $prefs .= '<tr><th>' . __('Availability:', APP_TD) . '</th><td>' .  jr_translate_months( date('F', mktime(0, 0, 0, $availability_month, 11, $availability_year)) ). ' ' . date('Y', mktime(0, 0, 0, $availability_month, 11, $availability_year)). '</td></tr>';
  } else {
    $prefs .= '<tr><th>' . __('Availability:', APP_TD) . '</th><td>' .  __('Immediate', APP_TD) . '</td></tr>';
  }

  if ( $willing_to_relocate ) {
    $prefs .= '<tr><th>' . __('Willing to relocate:', APP_TD) . '</th><td class="rellocate-yes"><i class="load dashicons-before" /></td></tr>';
  }

  if ( $willing_to_travel ) {
    $prefs .= '<tr><th>' . __('Willingness to travel:', APP_TD) . '</th><td>';
    switch ($willing_to_travel) :
      case "100" :
        $prefs .= __('Willing to travel', APP_TD);
      break;
      case "75" :
        $prefs .= __('Fairly willing to travel', APP_TD);
      break;
      case "50" :
        $prefs .= __('Not very willing to travel', APP_TD);
      break;
      case "25" :
        $prefs .= __('Local opportunities only', APP_TD);
      break;
      case "0" :
        $prefs .= __('Not willing to travel/working from home', APP_TD);
      break;
    endswitch;
    $prefs .='</td></tr>';
  }

  $prefs .= '</table>';
  return $prefs;
}


/**
 * Return the translated role display name.
 */
function jr_translate_role( $role ) {
  global $wp_roles;

  $roles = $wp_roles->get_names();

  $translated_roles = array(
    'job_lister' => __( 'Job Lister', APP_TD ),
    'job_seeker' => __( 'Job Seeker', APP_TD ),
    'recruiter' => __( 'Recruiter', APP_TD ),
  );

  if ( ! array_key_exists( $role, $translated_roles ) ) {
    return $roles[ $role ];
  }

  return $translated_roles[ $role ];
}

/**
 * Fixes paging on author pages.
 */
function custom_post_author_archive( &$query ) {
  if ( $query->is_author ) {
    $query->set( 'post_type', array( 'post', 'resume', 'job_listing' ) );
  }
  remove_action( 'pre_get_posts', 'custom_post_author_archive' );
}


function jr_job_author() {
  global $post;

  $company_name = wptexturize( strip_tags( get_post_meta( $post->ID, '_Company', true ) ) );

  if ( $company_name ) {
    if ( $company_url = esc_url( get_post_meta( $post->ID, '_CompanyURL', true ) ) ) {
      ?><!--<a href="<?php //echo esc_url( $company_url ); ?>" rel="nofollow"><?php //echo $company_name; ?></a>--><?php
      echo $company_name;
    } else {
      echo $company_name;
    }
    $format = __( ' &ndash; Posted by <a href="%s">%s</a>', APP_TD );
  } else {
    $format = '<a href="%s">%s</a>';
  }

  $author = get_user_by( 'id', $post->post_author );
  if ( $author && $link = get_author_posts_url( $author->ID, $author->user_nicename ) ) {
    //echo sprintf( $format, $link, $author->display_name );
  }

}


### User Template Tag Functions

/**
 * @since 1.8
 */
function the_jr_user_starred_jobs( $user_id = 0 ) {
  $user_id = $user_id ? $user_id : get_current_user_id();

  $_starred_jobs = get_user_meta( $user_id, '_starred_jobs', true );

  if ( is_array( $_starred_jobs ) && sizeof( $_starred_jobs ) > 0 ) {
    $args = array(
      'post_type'        => APP_POST_TYPE,
      'post_status'      => 'publish',
      'ignore_sticky_posts'  => true,
      'post__in'        => $_starred_jobs,
      'nopaging'        => true
    );
    query_posts( $args );

    ob_start();

    appthemes_load_template('loop-job.php');

    $loop = ob_get_clean();

    wp_reset_query();

    return $loop;
  } else {
    return false;
  }
}

/**
 * @since 1.8
 */
function the_jr_user_viewed_jobs( $user_id = 0 ) {
  $user_id = $user_id ? $user_id : get_current_user_id();

  $_viewed_jobs = get_user_meta( $user_id, '_viewed_jobs', true );

  if ( is_array( $_viewed_jobs ) && sizeof( $_viewed_jobs ) > 0 ) {
    $args = array(
      'post_type'        => APP_POST_TYPE,
      'post_status'      => 'publish',
      'ignore_sticky_posts'  => true,
      'post__in'        => $_viewed_jobs,
      'posts_per_page'    => 5
    );
    query_posts( $args );

    ob_start();

    appthemes_load_template('loop-job.php');

    $loop = ob_get_clean();

    wp_reset_query();

    return $loop;
  } else {
    return false;
  }
}

/**
 * @since 1.8
 */
function the_jr_user_job_recommendations( $user_id = 0 ) {
   global $wpdb;

   $user_id = $user_id ? $user_id : get_current_user_id();

   $willing_to_relocate   = get_user_meta($user_id, 'willing_to_relocate', true);
   $willing_to_travel   = get_user_meta($user_id, 'willing_to_travel', true);
   $keywords       = get_user_meta($user_id, 'keywords', true);
   $search_location     = get_user_meta($user_id, 'search_location', true);
   $job_types       = get_user_meta($user_id, 'job_types', true);

   $found_posts = array();

   if ( $keywords ) {
     $keywords = explode( ',', $keywords );
   }

   if ( is_array( $keywords ) && sizeof( $keywords) > 0 ) {

     foreach( $keywords as $keyword ) {
       $keyword = trim( $keyword );
       $result = $wpdb->get_col( $wpdb->prepare( "SELECT ID from $wpdb->posts WHERE post_title LIKE '%s' OR post_content LIKE '%s';", "%$keyword%", "%$keyword%" ) );

       if ( $result ) {
         $found_posts = array_merge( $result, $found_posts );
       }
     }

   }

   if ( !empty( $job_types ) ) {
     $args = array(
       'post_type' => APP_POST_TYPE,
       'nopaging' => TRUE,
       'tax_query' => array(
         array(
           'taxonomy' => 'job_type',
           'field' => 'slug',
           'terms' => $job_types
         )
       )
     );

     if (!empty($found_posts) ) {
       $args['post__in'] = $found_posts;
     }

     $posts_by_job_type = new WP_Query( $args );
     $posts_by_job_type = wp_list_pluck( $posts_by_job_type->posts, 'ID' );

     $found_posts = array_merge( $found_posts, $posts_by_job_type );
   }

   if ( $search_location ) {

     $find_posts_in = array();

     $radius = 0;
     if ( $willing_to_relocate == 'yes' ) {
       $radius += 8000;
     } else {
       if ( $willing_to_travel == 100 ) {
         $radius += 100;
       } elseif ( $willing_to_travel == 75 ) {
         $radius += 75;
       } elseif ( $willing_to_travel == 50 ) {
         $radius += 50;
       } elseif ( $willing_to_travel == 25 ) {
         $radius += 10;
       }
     }

     if ( $radius == 0 ) {
       $radius = 500;
     }

     $radial_result = jr_radial_search( $search_location, $radius );
     if ( is_array( $radial_result ) ) {
       $find_posts_in = array_merge( $radial_result['posts'], $find_posts_in );
     }
     $found_posts = array_intersect( $found_posts, $find_posts_in );
   }

   if ( sizeof( $job_types ) == 0 ) {
     $job_types = array();
   }

   if ( is_array( $found_posts ) && sizeof( $found_posts ) > 0 ) {
     $args = array(
       'post_type' => APP_POST_TYPE,
       'post_status' => 'publish',
       'ignore_sticky_posts' => 1,
       'post__in' => $found_posts,
       'posts_per_page' => 5,
       'tax_query' => array(
         array(
           'taxonomy' => 'job_type',
           'field' => 'slug',
           'terms' => $job_types
         )
       )
     );

     if ( isset( $radial_result['address'] ) ) {
       $args['location_search'] = 1;
     }
     query_posts( $args );

     ob_start();

     appthemes_load_template('loop-job.php');

     $loop = ob_get_clean();

     wp_reset_query();

     return $loop;
   } else {
     return false;
   }
}

/**
 * @since 1.8
 */
function the_jr_user_resumes( $user_id = 0 ) {
   $user_id = $user_id ? $user_id : get_current_user_id();

  $args = array(
    'ignore_sticky_posts'  => true,
    'nopaging'        => true,
    'author'        => $user_id,
    'post_type'        => APP_POST_TYPE_RESUME
  );
  return new WP_Query( $args );
}

/**
 * @since 1.8
 */
function jr_get_user_jobs( $user_id = 0, $args = array() ) {
  $user_id = $user_id ? $user_id : get_current_user_id();

  $defaults = array(
    'ignore_sticky_posts'  => true,
    'author'         => get_current_user_id(),
    'post_type'       => APP_POST_TYPE,
    'post_status'       => 'publish',
  );
  $args = wp_parse_args( $args, $defaults );

  if ( ! empty( $args['tab'] ) ) {
    $paged = 1;

    if ( get_query_var('tab') && $args['tab'] == get_query_var('tab') ) {
      $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
    }

    $args['posts_per_page'] = jr_get_jobs_per_page();
    $args['paged'] = $paged;
  }

  return new WP_Query( $args );
}

/**
 * @since 1.8
 */
function the_jr_user_live_jobs( $user_id = 0, $args = array() ) {
  $user_id = $user_id ? $user_id : get_current_user_id();

  $defaults = array(
    'tab' => 'live',
  );
  $args = wp_parse_args( $args, $defaults );

  return jr_get_user_jobs( $user_id, $args );
}

/**
 * @since 1.8
 */
function the_jr_user_pending_jobs( $user_id = 0, $args = array() ) {
  $user_id = $user_id ? $user_id : get_current_user_id();

  $defaults = array(
    'tab' => 'pending',
    'post_status' => array( 'pending', 'draft' ),
  );
  $args = wp_parse_args( $args, $defaults );

  return jr_get_user_jobs( $user_id, $args );
}

/**
 * @since 1.8
 */
function the_jr_user_ended_jobs( $user_id = 0, $args = array() ) {
  $user_id = $user_id ? $user_id : get_current_user_id();

  $defaults = array(
    'tab' => 'ended',
    'post_status' => array( 'expired' ),
  );
  $args = wp_parse_args( $args, $defaults );

  return jr_get_user_jobs( $user_id, $args );
}
