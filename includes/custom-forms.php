<?php

add_action( 'init', 'jr_forms_register_post_type', 11 );

add_action( 'wp_ajax_app-render-job-form', '_jr_forms_ajax_render_form' );
add_action( 'wp_ajax_app-render-resume-form', '_jr_forms_ajax_render_resume_form' );

add_action( 'jr_after_submit_job_form_category', '_jr_job_cat_custom_fields' );
add_action( 'jr_after_submit_resume_form_category', '_jr_resume_cat_custom_fields' );

add_action( 'registered_taxonomy', '_jr_custom_resume_tax_columns', 9, 3 );


### Hook Callbacks

function _jr_job_cat_custom_fields( $job ) {
	appthemes_load_template( array( 'includes/forms/submit-job/job-form-custom-fields.php', 'includes/job-form-custom-fields.php' ), array( 'job' => $job ) );
}

function jr_forms_register_post_type() {
	register_taxonomy_for_object_type( APP_TAX_CAT, APP_FORMS_PTYPE );
	register_taxonomy_for_object_type( APP_TAX_RESUME_CATEGORY, APP_FORMS_PTYPE );
}

function _jr_forms_ajax_render_form() {

	if ( empty( $_POST['job_category'] ) ) {
		die;
	}

	$cat = $_POST['job_category'];

	jr_render_custom_form( $cat );
	die;
}

function jr_render_custom_form( $cat, $taxonomy = APP_TAX_CAT, $post_id = 0 ) {

	foreach ( jr_get_custom_fields_for_cat( $cat, $taxonomy ) as $field ) {

		if ( ! isset($field['extra']['class']) ) {
			$field['extra']['class'] = '';
		}

 		if ( ! in_array( $field['type'], array( 'checkbox', 'radio' ) ) ) {
			$field['extra']['class'] .= ' text';
		}

		$field['extra']['class'] .= ' ' . _jr_get_custom_forms_extra_classes( $field, $taxonomy );

		$html = jr_wrap_custom_fields( $field, $post_id );

		echo apply_filters( 'jr_render_form_field', $html, $field, $post_id, $cat, $taxonomy );
	}

}

function jr_wrap_custom_fields( $field, $post_id = 0 ) {
	$label_tmp = $field['desc'];
	$field['desc'] = '';

	if ( isset($field['extra']['class']) && strpos( $field['extra']['class'], 'required' ) !== FALSE ) {
		$label_tmp .= ' *';
	}
	$label = html( 'label', $label_tmp );

	if ( empty( $_POST[ $field['name'] ] ) ) {
		$field_html = scbForms::input_from_meta( $field, $post_id );
	} else {
		$field_html = scbForms::input_with_value( $field, $_POST[ $field['name'] ] );
	}

	// hack to allow checkboxes and radio buttons to be set as required
	if ( in_array( $field['type'], array('checkbox', 'radio') ) && isset($field['extra']['class']) ) {
		$field_html = str_replace( '<input', '<input class="' . $field['extra']['class'] . '"', $field_html );
	}

	$field_html = str_replace( '<label>', '', $field_html );
	$field_html = str_replace( '</label>', '', $field_html );
	$field['desc'] = $label_tmp;

	return html( 'p', $label . $field_html );
}

function jr_get_custom_fields_for_cat( $cat, $taxonomy = APP_TAX_CAT ) {

	$args = array(
		'fields' => 'ids',
		'post_type' => APP_FORMS_PTYPE,
		'tax_query' => array(
			array(
				'taxonomy' => $taxonomy,
				'terms' => $cat,
				'field' => 'term_id',
				'include_children' => false
			)
		),
		'post_status' => 'publish',
		'posts_per_page' => 1
	);

	$forms = new WP_Query( $args );

	if ( empty( $forms->posts ) ) {
		return array();
	}

	return APP_Form_Builder::get_fields( $forms->posts[0] );
}


### Resumes Custom Forms

/**
 * @since 1.8
 */
function _jr_forms_ajax_render_resume_form() {

	if ( empty( $_POST['category'] ) ) {
		die;
	}

	$cat = $_POST['category'];

	jr_render_custom_form( $cat, APP_TAX_RESUME_CATEGORY );
	die;
}

/**
 * @since 1.8
 */
function _jr_resume_cat_custom_fields( $resume ) {
	appthemes_load_template( 'includes/forms/submit-resume/resume-form-custom-fields.php', array( 'resume' => $resume ) );
}

/**
 * Retrieve any extra classes for specific field types on custom forms.
 *
 * @since 1.8
 */
function _jr_get_custom_forms_extra_classes( $field, $taxonomy ) {

	$classes = array();

	switch ( $taxonomy ) {
		case APP_TAX_RESUME_CATEGORY:

			if ( 'textarea' == $field['type'] ) {
				$classes[] = 'custom-form';
				$classes[] = 'short';
			}
			break;

		default:
			break;
	}
	return implode( ' ', $classes );
}

/**
 * Force the resumes category to be displayed on the custom resume forms admin listing.
 *
 * @since 1.8
 */
function _jr_custom_resume_tax_columns( $taxonomy, $object_type, $args ) {
	global $wp_taxonomies;

	if ( ! is_admin() || empty( $_GET['post_type'] ) || APP_FORMS_PTYPE != $_GET['post_type'] || APP_TAX_RESUME_CATEGORY != $taxonomy ) {
		return $taxonomy;
	}

	$args['show_admin_column'] = true;
	$wp_taxonomies[ APP_TAX_RESUME_CATEGORY ] = (object) $args;

	return $taxonomy;
}
