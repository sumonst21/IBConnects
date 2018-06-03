<?php
/**
 * Custom post types and taxonomies
 *
 * @version 1.8
 * @author AppThemes
 * @package JobRoller\Post-Types
 * @copyright 2010 all rights reserved
 */

add_action( 'init', 'jr_post_type', 10 );

add_filter( 'request', 'jr_rss_request' );
add_filter( 'pre_get_posts', 'jr_rss_pre_get_posts' );


### Hook Callbacks.

/**
 * Create the custom post type and category taxonomy for job listings.
 */
function jr_post_type() {
  global $jr_options;

  // make sure the new roles are added to the DB before registering the post types
  if ( isset( $_GET['firstrun'] ) ) {
    jr_init_roles();
  }

  // get the slug value for the ad custom post type & taxonomies
  if ( $jr_options->jr_job_permalink ) {
    $post_type_base_url = $jr_options->jr_job_permalink;
  } else {
    $post_type_base_url = 'jobs';
  }

  if ( $jr_options->jr_job_cat_tax_permalink ) {
    $cat_tax_base_url = $jr_options->jr_job_cat_tax_permalink;
  } else {
    $cat_tax_base_url = 'job-category';
  }

  if ( $jr_options->jr_job_type_tax_permalink ) {
    $type_tax_base_url = $jr_options->jr_job_type_tax_permalink;
  } else {
    $type_tax_base_url = 'job-type';
  }

  if ( $jr_options->jr_job_tag_tax_permalink ) {
    $tag_tax_base_url = $jr_options->jr_job_tag_tax_permalink;
  } else {
    $tag_tax_base_url = 'job-tag';
  }

  if ( $jr_options->jr_job_salary_tax_permalink ) {
    $sal_tax_base_url = $jr_options->jr_job_salary_tax_permalink;
  } else {
    $sal_tax_base_url = 'salary';
  }

  if ( $jr_options->jr_resume_permalink ) {
    $resume_post_type_base_url = $jr_options->jr_resume_permalink;
  } else {
    $resume_post_type_base_url = 'resumes';
  }

  // register the new job category taxonomy
  register_taxonomy(
    APP_TAX_CAT, array( APP_POST_TYPE ), array(
      'hierarchical' => true,
      'labels' => array(
        'name'      => __( 'Job Categories', APP_TD ),
        'singular_name' => __( 'Job Category', APP_TD ),
        'search_items'  => __( 'Search Job Categories', APP_TD ),
        'all_items'    => __( 'All Job Categories', APP_TD ),
        'parent_item'  => __( 'Parent Job Category', APP_TD ),
        'parent_item_colon' => __( 'Parent Job Category:', APP_TD ),
        'edit_item'    => __( 'Edit Job Category', APP_TD ),
        'update_item'  => __( 'Update Job Category', APP_TD ),
        'add_new_item'  => __( 'Add New Job Category', APP_TD ),
        'new_item_name' => __( 'New Job Category Name', APP_TD ),
        'add_or_remove_items' => __( 'Add or remove job categories', APP_TD ),
        'menu_name' => _x( 'Categories', 'Admin menu name', APP_TD ),
      ),
      'show_ui'      => true,
      'show_admin_column' => true,
      'query_var'      => true,
      'update_count_callback' => '_update_post_term_count',
      'rewrite'      => array( 'slug' => $cat_tax_base_url, 'hierarchical' => true ),
    )
  );

  // register the new job type taxonomy
  register_taxonomy(
    APP_TAX_TYPE, array( APP_POST_TYPE ), array(
      'hierarchical' => true,
      'labels' => array(
        'name'      => __( 'Job Types', APP_TD ),
        'singular_name' => __( 'Job Type', APP_TD ),
        'search_items'  => __( 'Search Job Types', APP_TD ),
        'all_items'    => __( 'All Job Types', APP_TD ),
        'parent_item'  => __( 'Parent Job Type', APP_TD ),
        'parent_item_colon' => __( 'Parent Job Type:', APP_TD ),
        'edit_item'    => __( 'Edit Job Type', APP_TD ),
        'update_item'  => __( 'Update Job Type', APP_TD ),
        'add_new_item'  => __( 'Add New Job Type', APP_TD ),
        'new_item_name' => __( 'New Job Type Name', APP_TD ),
        'add_or_remove_items' => __( 'Add or remove job type', APP_TD ),
        'menu_name' => _x( 'Types', 'Admin menu name', APP_TD ),
      ),
      'show_ui'  => true,
      'query_var' => true,
      'update_count_callback' => '_update_post_term_count',
      'rewrite'  => array( 'slug' => $type_tax_base_url, 'hierarchical' => true ),
    )
  );

  // register the new job tag taxonomy
  register_taxonomy(
    APP_TAX_TAG, array( APP_POST_TYPE ), array(
      'hierarchical' => false,
      'labels' => array(
        'name'      => __( 'Job Tags', APP_TD ),
        'singular_name' => __( 'Job Tag', APP_TD ),
        'search_items'  => __( 'Search Job Tags', APP_TD ),
        'all_items'    => __( 'All Job Tags', APP_TD ),
        'parent_item'  => __( 'Parent Job Tag', APP_TD ),
        'parent_item_colon' => __( 'Parent Job Tag:', APP_TD ),
        'edit_item'    => __( 'Edit Job Tag', APP_TD ),
        'update_item'  => __( 'Update Job Tag', APP_TD ),
        'add_new_item'  => __( 'Add New Job Tag', APP_TD ),
        'new_item_name' => __( 'New Job Tag Name', APP_TD ),
        'add_or_remove_items' => __( 'Add or remove job tag', APP_TD ),
        'menu_name' => _x( 'Tags', 'Admin menu name', APP_TD ),
      ),
      'show_ui'  => true,
      'query_var' => true,
      'rewrite'  => array( 'slug' => $tag_tax_base_url ),
      'update_count_callback' => '_update_post_term_count'
    )
  );

  // register the salary taxonomy
  register_taxonomy(
    APP_TAX_SALARY, array( APP_POST_TYPE ), array(
      'hierarchical' => true,
      'labels' => array(
        'name'      => __( 'Salaries', APP_TD ),
        'singular_name' => __( 'Salary', APP_TD ),
        'search_items'  => __( 'Search Salaries', APP_TD ),
        'all_items'    => __( 'All Salaries', APP_TD ),
        'parent_item'  => __( 'Parent Salary', APP_TD ),
        'parent_item_colon' => __( 'Parent Salary:', APP_TD ),
        'edit_item'    => __( 'Edit Salary', APP_TD ),
        'update_item'  => __( 'Update Salary', APP_TD ),
        'add_new_item'  => __( 'Add New Salary', APP_TD ),
        'new_item_name' => __( 'New Salary', APP_TD ),
        'add_or_remove_items' => __( 'Add or remove salary', APP_TD ),
        'menu_name' => _x( 'Salaries', 'Admin menu name', APP_TD ),
      ),
      'show_ui'  => true,
      'query_var' => true,
      'rewrite'  => array( 'slug' => $sal_tax_base_url ),
    )
  );

  $custom_caps = array(
    'edit_posts' => 'edit_jobs', // enables job listers to view pending jobs
  );

  // create the custom post type and category taxonomy for job listings
  register_post_type(
    APP_POST_TYPE, array( 'labels' => array(
      'name'      => __( 'Jobs', APP_TD ),
      'singular_name' => __( 'Job', APP_TD ),
      'add_new'    => __( 'Add New', APP_TD ),
      'add_new_item'  => __( 'Add New Job', APP_TD ),
      'edit'      => __( 'Edit', APP_TD ),
      'edit_item'    => __( 'Edit Job', APP_TD ),
      'new_item'    => __( 'New Job', APP_TD ),
      'view'      => __( 'View Jobs', APP_TD ),
      'view_item'    => __( 'View Job', APP_TD ),
      'search_items'  => __( 'Search Jobs', APP_TD ),
      'not_found'    => __( 'No jobs found', APP_TD ),
      'not_found_in_trash' => __( 'No jobs found in trash', APP_TD ),
      'parent'    => __( 'Parent Job', APP_TD ),
    ),
    'description'  => __( 'This is where you can create new job listings on your site.', APP_TD ),
    'public'    => true,
    'show_ui'    => true,
    'capabilities'  => $custom_caps,
    'map_meta_cap'  => true,
    'publicly_queryable' => true,
    'exclude_from_search' => false,
    'menu_position' => 8,
    'has_archive'  => true,
    'menu_icon'    => 'dashicons-portfolio',
    'hierarchical'  => false,
    'rewrite'    => array( 'slug' => $post_type_base_url, 'with_front' => false ), /* Slug set so that permalinks work when just showing post name */
    'query_var'    => true,
    'supports'    => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'sticky' ),
    )
  );

  if ( $jr_options->jr_allow_job_seekers ) {
    $show_ui = true;
  } else {
    $show_ui = false;
  }

  register_taxonomy(
    APP_TAX_RESUME_CATEGORY, array( APP_POST_TYPE_RESUME ), array(
      'hierarchical' => true,
      'labels' => array(
        'name'      => __( 'Resume Categories', APP_TD ),
        'singular_name' => __( 'Resume Category', APP_TD ),
        'search_items'  => __( 'Search Resume Categories', APP_TD ),
        'all_items'    => __( 'All Resume Categories', APP_TD ),
        'parent_item'  => __( 'Parent Resume Category', APP_TD ),
        'parent_item_colon' => __( 'Parent Resume Category:', APP_TD ),
        'edit_item'    => __( 'Edit Resume Category', APP_TD ),
        'update_item'  => __( 'Update Resume Category', APP_TD ),
        'add_new_item'  => __( 'Add New Resume Category', APP_TD ),
        'new_item_name' => __( 'New Resume Category Name', APP_TD ),
        'add_or_remove_items' => __( 'Add or remove resume category', APP_TD ),
        'menu_name' => _x( 'Categories', 'Admin menu name', APP_TD ),
      ),
      'show_ui'  => $show_ui,
      'query_var' => true,
      'rewrite'  => array( 'slug' => 'resume/category', 'with_front' => false ),
      'update_count_callback' => '_update_post_term_count'
    )
  );

  register_taxonomy(
    APP_TAX_RESUME_JOB_TYPE, array( APP_POST_TYPE_RESUME ), array(
      'hierarchical' => false,
      'labels' => array(
        'name'      => __( 'Resume Job Types', APP_TD ),
        'singular_name' => __( 'Resume Job Type', APP_TD ),
        'search_items'  => __( 'Search Resume Job Types', APP_TD ),
        'all_items'    => __( 'All Resume Job Types', APP_TD ),
        'parent_item'  => __( 'Parent Resume Job Type', APP_TD ),
        'parent_item_colon' => __( 'Parent Resume Job Type:', APP_TD ),
        'edit_item'    => __( 'Edit Resume Job Type', APP_TD ),
        'update_item'  => __( 'Update Resume Job Type', APP_TD ),
        'add_new_item'  => __( 'Add New Resume Job Type', APP_TD ),
        'new_item_name' => __( 'New Resume Job Type Name', APP_TD ),
        'add_or_remove_items' => __( 'Add or remove resume type', APP_TD ),
        'menu_name' => _x( 'Types', 'Admin menu name', APP_TD ),
      ),
      'show_ui'  => $show_ui,
      'rewrite'  => array( 'slug' => 'resume/job-type', 'with_front' => false ),
      'query_var' => true,
      'update_count_callback' => '_update_post_term_count'
    )
  );

  register_taxonomy(
    APP_TAX_RESUME_SPECIALITIES, array( APP_POST_TYPE_RESUME ), array(
      'hierarchical' => false,
      'labels' => array(
        'name'      => __( 'Resume Specialties', APP_TD ),
        'singular_name' => __( 'Resume Specialty', APP_TD ),
        'search_items'  => __( 'Search Resume Specialties', APP_TD ),
        'all_items'    => __( 'All Resume Specialties', APP_TD ),
        'parent_item'  => __( 'Parent Resume Specialty', APP_TD ),
        'parent_item_colon' => __( 'Parent Resume Specialty:', APP_TD ),
        'edit_item'    => __( 'Edit Resume Specialty', APP_TD ),
        'update_item'  => __( 'Update Resume Specialty', APP_TD ),
        'add_new_item'  => __( 'Add New Resume Specialty', APP_TD ),
        'new_item_name' => __( 'New Resume Specialty Name', APP_TD ),
        'add_or_remove_items' => __( 'Add or remove resume specialty', APP_TD ),
        'menu_name' => _x( 'Specialties', 'Admin menu name', APP_TD ),
      ),
      'show_ui'  => $show_ui,
      'rewrite'  => array( 'slug' => 'resume/speciality', 'with_front' => false ),
      'query_var' => true,
      'update_count_callback' => '_update_post_term_count'
    )
  );

  register_taxonomy(
    APP_TAX_RESUME_GROUPS, array( APP_POST_TYPE_RESUME ), array(
      'hierarchical' => false,
      'labels' => array(
        'name'      => __( 'Resume Groups', APP_TD ),
        'singular_name' => __( 'Resume Group', APP_TD ),
        'search_items'  => __( 'Search Groups', APP_TD ),
        'all_items'    => __( 'All Groups', APP_TD ),
        'parent_item'  => __( 'Parent Group', APP_TD ),
        'parent_item_colon' => __( 'Parent Group:', APP_TD ),
        'edit_item'    => __( 'Edit Group', APP_TD ),
        'update_item'  => __( 'Update Group', APP_TD ),
        'add_new_item'  => __( 'Add New Group', APP_TD ),
        'new_item_name' => __( 'New Group Name', APP_TD ),
        'add_or_remove_items' => __( 'Add or remove resume group', APP_TD ),
        'menu_name' => _x( 'Groups', 'Admin menu name', APP_TD ),
      ),
      'show_ui'  => $show_ui,
      'query_var' => true,
      'rewrite'  => array( 'slug' => 'resume/group', 'with_front' => false ),
      'update_count_callback' => '_update_post_term_count'
    )
  );

  register_taxonomy(
    APP_TAX_RESUME_LANGUAGES, array( APP_POST_TYPE_RESUME ), array(
      'hierarchical' => false,
      'labels' => array(
        'name'      => __( 'Resume Languages', APP_TD ),
        'singular_name' => __( 'Resume Langauge', APP_TD ),
        'search_items'  => __( 'Search Resume Languages', APP_TD ),
        'all_items'    => __( 'All Resume Languages', APP_TD ),
        'parent_item'  => __( 'Parent Resume Language', APP_TD ),
        'parent_item_colon' => __( 'Parent Resume Language:', APP_TD ),
        'edit_item'    => __( 'Edit Resume Language', APP_TD ),
        'update_item'  => __( 'Update Resume Language', APP_TD ),
        'add_new_item'  => __( 'Add New Resume Language', APP_TD ),
        'new_item_name' => __( 'New Resume Language Name', APP_TD ),
        'add_or_remove_items' => __( 'Add or remove resume language', APP_TD ),
        'menu_name' => _x( 'Languages', 'Admin menu name', APP_TD ),
      ),
      'show_ui'  => $show_ui,
      'query_var' => true,
      'rewrite'  => array( 'slug' => 'resume/language', 'with_front' => false ),
      'update_count_callback' => '_update_post_term_count'
    )
  );

  register_post_type(
    APP_POST_TYPE_RESUME, array(
      'labels' => array(
        'name'      => __( 'Resumes', APP_TD ),
        'singular_name' => __( 'Resume', APP_TD ),
        'add_new'    => __( 'Add New', APP_TD ),
        'add_new_item'  => __( 'Add New Resume', APP_TD ),
        'edit'      => __( 'Edit', APP_TD ),
        'edit_item'    => __( 'Edit Resume', APP_TD ),
        'new_item'    => __( 'New Resume', APP_TD ),
        'view'      => __( 'View Resumes', APP_TD ),
        'view_item'    => __( 'View Resume', APP_TD ),
        'search_items'  => __( 'Search Resumes', APP_TD ),
        'not_found'    => __( 'No Resumes found', APP_TD ),
        'not_found_in_trash' => __( 'No Resumes found in trash', APP_TD ),
        'parent'    => __( 'Parent Resume', APP_TD ),
      ),
      'description'  => __( 'Resumes are created and edited by job_seekers.', APP_TD ),
      'public'    => true,
      'show_ui'    => $show_ui,
      'capability_type'   => 'post',
      'publicly_queryable' => true,
      'exclude_from_search'=> false,
      'menu_position' => 8,
      'menu_icon'    => 'dashicons-id-alt',
      'hierarchical'  => false,
      'rewrite'    => array( 'slug' => $resume_post_type_base_url, 'with_front' => false ), /* Slug set so that permalinks work when just showing post name */
      'query_var'    => true,
      'has_archive'  => $resume_post_type_base_url,
      'supports'    => array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields' ),
    )
  );

}

/**
 * Get the custom taxonomy array and loop through the values.
 */
function jr_get_custom_taxonomy( $post_id, $tax_name, $tax_class ) {

  $tax_array = get_terms( $tax_name, array( 'hide_empty' => '0' ) );
  if ( $tax_array && sizeof( $tax_array ) > 0 ) {

    foreach ( $tax_array as $tax_val ) {
      if ( is_object_in_term( $post_id, $tax_name, array( $tax_val->term_id ) ) ) {
        echo '<span class="' . esc_attr( $tax_class . ' ' . $tax_val->slug ) . '">' . $tax_val->name . '</span>';
        break;
      }
    }

  }

}

/**
 * Add custom post types to the Main RSS feed.
 */
function jr_rss_request( $qv ) {
  if ( isset( $qv['feed'] ) && ! isset( $qv['post_type'] ) ) {
    $qv['post_type'] = array( 'post', 'job_listing' );
  }
  return $qv;
}

/**
 * Only retrieve published jobs in RSS feed.
 */
function jr_rss_pre_get_posts( $query ) {
  if ( $query->is_feed ) {
    $query->set( 'post_status', 'publish' );
  }
  return $query;
}
