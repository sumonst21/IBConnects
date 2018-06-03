<?php
	$args = array();

	$cats_enabled = $jr_options->jr_submit_cat_required;
	if ( ! empty( $job->ID ) && 'yes' == $cats_enabled ) {
		if ( ! $terms = jr_get_the_job_tax( $job->ID, APP_TAX_CAT ) ) {
			$term_id = 0;
		} else {
			$term_id = $terms->term_id;
		}

		$args['tax_query'] = array(
			array(
				'taxonomy' => APP_TAX_CAT,
				'field' => 'id',
				'terms' => $term_id,
				'include_children' => false
			)
		);
	}

	$plans_data = jr_get_available_plans( $args );

	if ( 'pack' == $jr_options->plan_type ) {
		$template = '/includes/forms/select-plan/select-plan-packs-form.php';
	} else {
		$template = '/includes/forms/select-plan/select-plan-single-form.php';
	}

	$args = array(
		'step' => $step,
		'job' => $job,
		'plans' => $plans_data,
		'form_action' => $form_action,
		'post_action' => 'purchase-job-plan',
	);

	appthemes_load_template( $template, $args );
