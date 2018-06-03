<?php

/**
 * JobRoller Template Tags
 * This file defines functions to be used in the Loop and helper functions
 *
 *
 * @version 1.7
 * @author AppThemes
 * @package JobRoller
 * @copyright 2010 all rights reserved
 *
 */
function the_job_relist_link( $job_id = 0, $text = '' ) {

	if ( !jr_allow_relist() ) {
		return;
	}

	$job_id = $job_id ? $job_id : get_the_ID();

	if ( !jr_is_job_author( $job_id ) ) {
		return;
	}

	if ( empty( $text ) ) {
		$text = __( 'Relist', APP_TD );
	}

	echo html( 'a', array(
		'class' => 'job-relist-link',
		'href' => esc_url( jr_get_job_relist_url( $job_id ) ),
	), $text );
}

function jr_get_job_relist_url( $job_id ) {
	$query_args = array(
		'job_relist' => $job_id,
	);
	return add_query_arg( $query_args, get_permalink( JR_Job_Submit_Page::get_id() ) );
}

function the_job_cancel_link( $job_id = 0, $text = '' ) {
	$job_id = $job_id ? $job_id : get_the_ID();

	if ( !jr_is_job_author( $job_id ) ) {
		return;
	}

	if ( empty( $text ) ) {
		$text = __( 'Cancel', APP_TD );
	}

	echo html( 'a', array(
		'class' => 'delete',
		'href' => esc_url( jr_get_job_cancel_url( $job_id, $cancel = true ) ),
	), $text );
}

function jr_get_job_cancel_url( $job_id, $cancel = false ) {
	$args = array( 'job_end' => $job_id, 'cancel' => 1 );
	return add_query_arg( $args, get_permalink( JR_Dashboard_Page::get_id() ) );
}

function the_job_end_link( $job_id = 0, $text = '' ) {
	$job_id = $job_id ? $job_id : get_the_ID();

	if ( ! jr_is_job_author( $job_id ) ) {
		return;
	}

	if ( empty( $text ) ) {
		$text = __( 'End', APP_TD );
	}

	echo html( 'a', array(
		'class' => 'end',
		'href' => esc_url( jr_get_job_end_url( $job_id ) ),
	), $text );
}

function jr_get_job_end_url( $job_id ) {
	$args = array( 'job_end' => $job_id );
	return add_query_arg( $args, get_permalink( JR_Dashboard_Page::get_id() ) );
}

/**
 * @since 1.8.1
 */
function get_the_jr_job_edit_link( $job_id = 0 ) {
	$job_id = $job_id ? $job_id : get_the_ID();

	if ( ! current_user_can('can_submit_job') || ! current_user_can( 'edit_job', $job_id ) || ! jr_allow_editing() ) {
		return;
	}

	return jr_get_job_edit_url( $job_id );
}

function the_job_edit_link( $job_id = 0, $text = '' ) {

	if ( ! ( $edit_link = get_the_jr_job_edit_link( $job_id ) ) ) {
		return;
	}

	if ( empty( $text ) ) {
		$text = __( 'Edit&nbsp;&rarr;', APP_TD );
	}

	echo html( 'a', array(
		'class' => 'job-edit-link',
		'href' => esc_url( $edit_link ),
	), $text );
}

function jr_get_job_edit_url( $job_id ) {
	return add_query_arg( array( 'job_edit' => $job_id ), get_permalink( JR_Job_Edit_Page::get_id() ) );
}

function the_job_continue_link( $job_id = 0, $text = '' ) {
	$job_id = $job_id ? $job_id : get_the_ID();

	if ( ! jr_is_job_author( $job_id ) ) {
		return;
	}

	if ( empty( $text ) ) {
		$text = __( 'Continue&nbsp;&rarr;', APP_TD );
	}

	echo html( 'a', array(
		'class' => 'job-continue-link',
		'href' => esc_url( jr_get_job_continue_url( $job_id ) ),
	), $text );
}

function jr_get_job_continue_url( $job_id ) {
	return add_query_arg( array( 'job_id' => $job_id ), get_permalink( JR_Job_Submit_Page::get_id() ) );
}

function the_order_purchase_link( $text, $order ) {

	if ( empty( $text ) ) {
		$text = __( 'Pay', APP_TD );
	}

	$order_data = _jr_get_order_job_info( $order );
	if ( $order_data && APP_POST_TYPE == $order_data['post']->post_type ) {
		$job_id = $order_data['post_id'];
	}

	if ( empty( $job_id ) || ! ( $order_link = jr_get_the_order_purchase_url( $order, $job_id ) ) ) {
		return;
	}

	echo html( 'a', array(
		'class' => 'order-purchase-link',
		'href' => esc_url( $order_link ),
	), $text );
}

function jr_get_the_order_purchase_url( $order, $job_id = 0 ) {
	$args = array(
		'order_id' => $order->get_id(),
	);

	$plan = jr_plan_for_order( $order );
	if ( ! $plan ) {
		return;
	}

	if ( $job_id ) {
		$plan_type = 'job-plan';

		$args['job_id'] = $job_id;
	} else {
		$plan_type = $plan->post_type;
	}

	if ( $step = jr_get_step_by_name( 'select_plan' ) ) {
		$args['step'] = $step;
	}

	switch ( $plan_type ) {
		case APPTHEMES_RESUMES_PLAN_PTYPE:
			$url = jr_get_purchase_resume_plans_url( $args );
			break;
		case APPTHEMES_PRICE_PLAN_PTYPE:
			$url = jr_get_purchase_packs_url( $args );
			break;
		default:
			$url = jr_get_listing_create_url( $args );
	}

	return $url;
}

function the_order_cancel_link( $text, $order, $job_id = 0 ) {

	if ( empty( $text ) ) {
		$text = __( 'Cancel', APP_TD );
	}

	echo html( 'a', array(
		'class' => 'order-cancel-link',
		'href' => esc_url( jr_get_the_order_cancel_url( $order->get_id() ) ),
			), $text );
}

function jr_get_the_order_cancel_url( $order_id ) {
	return add_query_arg( array( 'order_cancel' => $order_id ), get_permalink( JR_Dashboard_Page::get_id() ) );
}

function the_purchase_pack_link( $text = '' ) {

	if ( empty( $text ) ) {
		$text = __( 'Buy Packs &rarr;', APP_TD );
	}

	$aref = html( 'a', array(
		'class' => 'buy-pack button',
		'href' => esc_url( jr_get_purchase_packs_url() ),
	), html( 'span', $text ) );

	echo html( 'p', $aref );
}

function the_resume_purchase_plan_link( $text = '' ) {

	if ( ! jr_current_user_can_subscribe_for_resumes() ) {
		return;
	}

	if ( empty( $text ) ) {
		$text = __( 'Subscribe &rarr;', APP_TD );
	}

	$aref = html( 'a', array(
		'class' => 'subscribe-resumes button',
		'href' => esc_url( jr_get_purchase_resume_plans_url() ),
	), html( 'span', $text ) );

	echo html( 'p', $aref );
}

function jr_get_the_job_tax( $job_id, $taxonomy ) {
	$terms = get_the_terms( $job_id, $taxonomy );
	if ( ! $terms ) {
		return;
	}

	return reset( $terms );
}

function get_the_job_terms( $job_id, $taxonomy, $fields = 'all' ) {
	$params = array( 'fields' => $fields );
	$terms = get_the_terms( $job_id, $taxonomy, $params );
	if ( !$terms ) {
		return;
	}

	return reset( $terms );
}

function jr_get_single_term( $listing_id, $taxonomy ) {
	$terms = get_the_terms( $listing_id, $taxonomy );
	if ( !$terms ) {
		return;
	}

	return reset( $terms );
}

function jr_get_listing_create_url( $args = '' ) {
	$default_args = array();
	$args = wp_parse_args( $args, $default_args );

	return add_query_arg( $args, get_permalink( JR_Job_Submit_Page::get_id() ) );
}

function jr_get_purchase_packs_url( $args = '' ) {
	$default_args = array();
	$args = wp_parse_args( $args, $default_args );

	return add_query_arg( $args, get_permalink( JR_Packs_Purchase_Page::get_id() ) );
}

function jr_get_purchase_resume_plans_url( $args = '' ) {
	$default_args = array();
	$args = wp_parse_args( $args, $default_args );

	return add_query_arg( $args, get_permalink( JR_Resume_Plans_Purchase_Page::get_id() ) );
}

function jr_get_dashboard_url() {
	return get_permalink( JR_Dashboard_Page::get_id() );
}

function get_the_job_listing_category( $post_id ) {
	$terms = get_the_terms( $post_id, APP_TAX_CAT );
	if ( !$terms ) {
		return;
	}

	return reset( $terms );
}

function get_the_resume_listing_category( $post_id ) {
	$terms = get_the_terms( $post_id, APP_TAX_RESUME_CATEGORY );
	if ( !$terms ) {
		return;
	}

	return reset( $terms );
}

function the_job_listing_fields( $job_id = 0 ) {

	$job_id = $job_id ? $job_id : get_the_ID();

	$cat = get_the_job_listing_category( $job_id );
	if ( !$cat ) {
		return;
	}

	echo '<section id="listing-fields">';

	foreach ( jr_get_custom_fields_for_cat( (int) $cat->term_id ) as $field ) {
		if ( 'checkbox' == $field['type'] ) {
			$value = implode( ', ', get_post_meta( $job_id, $field['name'] ) );
		} else {
			$value = get_post_meta( $job_id, $field['name'], true );
		}

		if ( !$value ) {
			continue;
		}

		$field['id_tag'] = jr_make_custom_field_id_tag( $field['desc'] );

		echo html( 'p', array( 'class' => 'job-listing-custom-field', 'id' => $field['id_tag'] ), html( 'span', array( 'class' => 'custom-field-label' ), $field['desc'] ) . html( 'span', array( 'class' => 'custom-field-sep' ), ': ' ) . html( 'span', array( 'class' => 'custom-field-value' ), $value ) );
	}

	echo '</section>';
}

function jr_make_custom_field_id_tag( $desc, $prefix = 'job-listing-custom-field-' ) {
	$id_tag = $desc;
	$id_tag = strtolower( $id_tag );
	$id_tag = str_ireplace( ' ', '-', $id_tag );
	$id_tag = $prefix . $id_tag;
	return $id_tag;
}

function the_job_addons( $job_id = 0 ) {
	$job_id = $job_id ? $job_id : get_the_ID();

	$job_meta = get_post_custom( $job_id );
	foreach ( jr_get_addons( 'job' ) as $addon ) {
		if ( !empty( $job_meta[$addon][0] ) && _jr_is_active_featured( $job_id, $addon ) ) {
			$days = get_post_meta( $job_id, $addon . '_duration', true );
			$expire_date = '';
			if ( $days >= 1 ) {
				$featured_time = get_post_meta( $job_id, $addon . '_start_date', true );
				$expire_date = strtotime( $featured_time . '+' . $days . ' days' );
				$expire_date = __( ' :: ends ', APP_TD ) . appthemes_display_date( $expire_date, 'date' );
			}
			echo html( 'div', array( 'class' => 'job-addon ' . $addon ), html( 'span', jr_get_addon_title( $addon ) . $expire_date ) );
		}
	}
}

function jr_get_addon_title( $addon ) {
	if ( !list($app_payments) = get_theme_support( 'app-payments' ) )
		return '';

	foreach ( $app_payments['items'] as $item ) {
		if ( $addon == $item['type'] )
			return $item['title'];
	}
}

function the_orders_history_job( $order ) {

	$job_id = jr_get_order_job_id( $order );
	if ( !$job_id ) {
		return;
	}

	$title = get_the_title( $job_id );

	$html = html( 'a', array( 'href' => get_permalink( $job_id ) ), $title );
	echo $html;
}

function jr_get_the_order_summary( $order, $output = 'plain' ) {

	$order_items = '';

	$items = $order->get_items();

	$plan = jr_plan_for_order( $order );

	foreach ( $items as $item ) {
		if ( !APP_Item_Registry::is_registered( $item['type'] ) ) {
			$item_title = __( 'Unknown', APP_TD );
		} else {
			$item_title = APP_Item_Registry::get_title( $item['type'] );
		}
		$item_html = ( 'html' == $output ? html( 'div', $item_title ) : ( $order_items ? ' / ' . $item_title : $item_title ) );
		$order_items .= $item_html;
	}

	if ( !$order_items )
		$order_items = '-';

	return $order_items;
}

function the_orders_history_payment( $order ) {
	$gateway_id = $order->get_gateway();

	if ( !empty( $gateway_id ) ) {
		$gateway = APP_Gateway_Registry::get_gateway( $gateway_id );
		if ( $gateway ) {
			$gateway = $gateway->display_name( 'admin' );
		} else {
			$gateway = __( 'Unknown', APP_TD );
		}
	} else {
		$gateway = __( 'Undecided', APP_TD );
	}

	$gateway = html( 'div', array( 'class' => 'order-history-gateway' ), $gateway );
	$status = html( 'div', array( 'class' => 'order-history-status' ), $order->get_display_status() );

	echo $gateway . $status;
}

function jr_is_job_author( $job_id ) {
	$post = get_post( $job_id );
	return ( get_current_user_id() == $post->post_author );
}

function jr_get_job_order_status( $job, $pending_payment = '' ) {

	$order_status = '';

	if ( isset( $pending_payment[$job->ID] ) ) {
		$order = appthemes_get_order( $pending_payment[$job->ID]['order_id'] );
		if ( $order ) {
			if ( APPTHEMES_ORDER_FAILED == $order->get_status() ) {
				$order_status = __( 'Payment Failed', APP_TD );
			} elseif ( 'undecided' == $pending_payment[$job->ID]['status'] ) {
				$order_status = __( 'Pending Payment', APP_TD );
			} else {
				$order_status = __( 'Pending Payment Approval', APP_TD );
			}
		}
	}
	return $order_status;
}

function jr_get_job_status( $job, $pending_payment = '' ) {

	switch ( $job->post_status ) {
		case 'pending':
			if ( !$pending_payment || !jr_get_job_order_status( $job, $pending_payment ) )
				$status = __( 'Pending Approval', APP_TD );
			else
				$status = __( 'Pending', APP_TD );
			break;
		case 'draft':
			$status = __( 'Incomplete Draft', APP_TD );
			break;
		case 'expired':
			$canceled_job = get_post_meta( $job->ID, '_jr_canceled_job', true );
			if ( $canceled_job )
				$status = __( 'Canceled', APP_TD );
			else
				$status = __( 'Expired', APP_TD );
			break;
		default:
			$status = '';
			break;
	}

	return $status;
}

function the_job_actions( $job, $pending_payment = '' ) {

	if ( !isset( $pending_payment[$job->ID] ) ) {

		if ( 'pending' == $job->post_status ) :
			the_job_edit_link( $job->ID );
		elseif ( 'draft' == $job->post_status ) :
			the_job_continue_link( $job->ID );
		elseif ( 'expired' == $job->post_status ) :
			$canceled_job = get_post_meta( $job->ID, '_jr_canceled_job', true );
			if ( $canceled_job )
				the_job_continue_link( $job->ID, __( 'Continue', APP_TD ) );
			else
				the_job_relist_link( $job->ID );
		endif;
	} elseif ( !empty( $pending_payment[$job->ID] ) && 'undecided' == $pending_payment[$job->ID]['status'] ) {
		$order = appthemes_get_order( $pending_payment[$job->ID]['order_id'] );
		the_order_purchase_link( __( 'Pay&nbsp;&rarr; ', APP_TD ), $order );
	}

	if ( 'pending' == $job->post_status || 'draft' == $job->post_status ) {
		the_job_cancel_link( $job->ID );
	}
}

function jr_get_submit_footer_text() {
	global $jr_options;

	$text = $jr_options->jr_jobs_submit_text;

	if ( !$text && jr_charge_job_listings() && $plans = jr_get_plans_prices_duration() ) {
		if ( sizeof( $plans ) > 0 ) {
			$text = __( 'Starting at ', APP_TD );
		}
		reset( $plans );

		// display standard pricing
		$amount = $plans[0]['price'];
		$jobs_last = $plans[0]['duration'];
		if ( !$jobs_last ) {
			$jobs_last = 30; // 30 day default
		}

		if ( $amount && $amount > 0 ) {
			$text = sprintf( '<p class=\'pricing\'>%s <em>%s</em> %s <em>%s %s</em></p>', $text, appthemes_get_price( $amount ), __( 'for', APP_TD ), $jobs_last, _n( 'day', 'days', $jobs_last, APP_TD ) );
		}
	}
	$text = apply_filters( 'jr_submit_footer_text', wpautop( wptexturize( $text ) ) );

	return $text;
}

function jr_get_featured_jobs_rss_url() {

	if ( !is_tax( APP_TAX_CAT ) && !is_front_page() && !is_archive( APP_POST_TYPE ) ) {
		return add_query_arg( 'post_type', APP_POST_TYPE, get_bloginfo( 'rss2_url' ) );
	}

	$args = array(
		'rss_featured' => 1
	);
	if ( is_tax( APP_TAX_CAT ) ) {
		$args['rss_job_cat'] = get_queried_object_id();
	}

	return add_query_arg( $args, get_bloginfo( 'rss2_url' ) );
}

function jr_location( $with_comma = false ) {
	global $post;

	$address = get_post_meta( $post->ID, 'geo_short_address', true );

	if ( !$address ) {
		$address = __( 'Anywhere', APP_TD );
	}

	if ( is_singular( APP_POST_TYPE ) ) {
		echo html( 'strong class="job-location"', html( 'i class="icon dashicons-before"', "&nbsp;" ) . $address );
	} else {
		echo html( 'strong', $address );
	}

	$country = strip_tags( get_post_meta( $post->ID, 'geo_short_address_country', true ) );

	if ( $country ) {
		if ( $with_comma ) {
			echo ', ';
		}

		echo html( 'span', $country );
	}
}

/**
 * Outputs the site header logo applying any attributes from the WordPress site customizer.
 *
 * @uses apply_filters() Calls 'jr_header_logo'
 *
 * @since 1.8
 */
function the_jr_logo() {
	ob_start();
	?>
	<h1 id="logo">
		<?php if ( get_header_image() ): ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo custom-header-image"><img src="<?php header_image(); ?>" height="<?php echo get_custom_header()->height; ?>" width="<?php echo get_custom_header()->width; ?>" alt="<?php echo esc_attr( get_bloginfo( 'title' ) ); ?>" /></a>
		<?php else: ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo( get_bloginfo( 'title' ) ); ?></a>
		<?php endif; ?>

		<?php if ( display_header_text() ) : ?>
			<small><?php bloginfo( 'description' ); ?></small>
		<?php endif; ?>
	</h1>
	<?php
	$logo_html = ob_get_clean();

	echo apply_filters( 'jr_header_logo', $logo_html );
}

/**
 * @since 1.8
 */
function jr_get_the_how_to_apply( $job_id = 0 ) {
	$job_id = $job_id ? $job_id : get_the_ID();

	$how_to_apply = get_post_meta( $job_id, '_how_to_apply', true );

	return apply_filters( 'jr_how_to_apply_content', $how_to_apply );
}

/**
 * @since 1.8
 */
function the_jr_how_to_apply( $job_id = 0 ) {
	echo jr_get_the_how_to_apply( $job_id );
}

/**
 * @since 1.8
 */
function jr_get_the_stats_counter( $post_id = 0 ) {
	global $jr_options;

	if ( ! $jr_options->jr_ad_stats_all || ! current_theme_supports( 'app-stats' ) ) {
		return false;
	}

	$post_id = $post_id ? $post_id : get_the_ID();

	ob_start();

	appthemes_stats_counter( $post_id );

	return ob_get_clean();
}

/*
 * @since 1.8
 */
function the_jr_stats_counter( $post_id = 0 ) {
	echo jr_get_the_stats_counter( $post_id );
}

/**
 * @since 1.8
 */
function jr_get_the_curr_user_starred_jobs( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	return (array) get_user_meta( $user_id, '_starred_jobs', true );
}

/**
 * @since 1.8
 */
function the_curr_user_starred_jobs( $user_id = 0 ) {
	echo jr_get_the_curr_user_starred_jobs( $user_id );
}

/**
 * @since 1.8
 */
function jr_get_the_coordinate( $coord, $job_id = 0 ) {
	$job_id = $job_id ? $job_id : get_the_ID();

	$coords = array(
		'latitude' => '_jr_geo_latitude',
		'longitude' => '_jr_geo_longitude',
	);

	if ( !$coord || empty( $coords[$coord] ) ) {
		return;
	}

	return get_post_meta( $job_id, $coords[$coord], true );
}

/**
 * @since 1.8
 */
function the_jr_coordinate( $job_id = 0 ) {

	$coordinate = jr_get_the_coordinates( $job_id );

	echo $coordinate;
}

/**
 * Echoes the listing files list
 */
function the_job_listing_files( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	$attachments = jr_get_listing_attachments( $post_id, -1, JR_ATTACHMENT_FILE );

	if ( empty( $attachments ) ) {
		return;
	}

	echo '<section id="listing-files">';

	echo '<div class="listing-files">';

	echo '<strong>' . __( 'Listing Attachments:', APP_TD ) . '</strong>';

	foreach ( $attachments as $attachment ) {
		echo jr_get_file_link( $attachment->ID );
	}

	echo '</div>';

	echo '</section>';
}

function the_job_listing_logo_editor( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	$images = jr_get_listing_attachments( $post_id, 1 );

	if ( $images ) {

		echo '<div class="images"><ul class="uploaded company-logo">';

		foreach ( $images as $image ) :
			$meta = wp_get_attachment_metadata( $image->ID );

			if ( is_array( $meta ) && isset( $meta['width'] ) && isset( $meta['height'] ) ) {
				$media_dims = "<span id='media-dims-" . $image->ID . "'>" . $meta['width'] . '&nbsp;&times;&nbsp;' . $meta['height'] . "</span>";
			} else {
				$media_dims = '';
			}

			$alt = get_post_meta( $image->ID, '_wp_attachment_image_alt', true );
			?>
			<li>
				<?php echo wp_get_attachment_link( $image->ID, 'thumbnail' ); ?>
				<div class="image-meta"><strong><?php _e( 'File Info:', APP_TD ) ?></strong> <?php echo $media_dims; ?> <?php echo $image->post_mime_type; ?></div>
			</li>
			<?php
		endforeach;

		echo '</ul></div>';
	}
	?><p class="optional"><label for="company-logo"><?php _e( 'Logo (.jpg, .gif or .png)', APP_TD ); ?></label><input type="file" class="text" name="company-logo" id="company-logo" /></p><?php
}

/**
 * @since 1.8
 */
function the_resume_custom_fields( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	$cat = get_the_resume_listing_category( $post_id );

	if ( ! $cat ) {
		return;
	}

	echo '<section id="resume-custom-fields">';

	foreach ( jr_get_custom_fields_for_cat( (int) $cat->term_id, APP_TAX_RESUME_CATEGORY ) as $field ) {
		if ( 'checkbox' == $field['type'] ) {
			$value = implode( ', ', get_post_meta( $post_id, $field['name'] ) );
		} else {
			$value = get_post_meta( $post_id, $field['name'], true );
		}

		if ( ! $value ) {
			continue;
		}

		$field['id_tag'] = jr_make_custom_field_id_tag( $field['desc'] );

		echo html( 'h2', array( 'class' => 'resume_section_heading resume-custom-field ' . $field['id_tag'] ), html( 'span', $field['desc'] ) );

		echo html( 'div', array( 'class' => 'resume_section resume-custom-field', 'id' => $field['id_tag'] ), html( 'p', array( 'class' => 'custom-field-value' ), $value ) );

		echo html( 'div class="clear"', '&nbsp;' );
	}

	echo '</section>';
}

/**
 * Echoes the listing files list
 */
function the_resume_files( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	$attachments = jr_get_listing_attachments( $post_id, -1, JR_ATTACHMENT_FILE );

	if ( empty( $attachments ) ) {
		return;
	}

	echo html( 'h2', array( 'class' => 'resume_section_heading resume-custom-field ' ), html( 'span', __( 'Attachments', APP_TD ) ) );

	$files_html = '';

	foreach ( $attachments as $attachment ) {

		$field_data = get_post_meta( $attachment->ID, '_jr_field_data', true );

		// the uploaded file label
		if ( !empty( $field_data['desc'] ) ) {
			$files_html .= html( 'p class="file-upload-item"', html( 'strong', $field_data['desc'] ) );
		}
		$files_html .= jr_get_file_link( $attachment->ID );

		$files_html .= '<br/>';
	}

	echo html( 'div', array( 'class' => 'resume_section resume-custom-field' ), html( 'p', array( 'class' => 'custom-field-value' ), $files_html ) );

	echo html( 'div class="clear"', '&nbsp;' );
}

/**
 *
 * @since 1.8
 */
function the_resume_photo_editor( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	$images = jr_get_listing_attachments( $post_id, 1 );

	if ( $images ) {

		echo '<div class="images"><ul class="uploaded your-photo">';

		foreach ( $images as $image ) :
			$meta = wp_get_attachment_metadata( $image->ID );

			if ( is_array( $meta ) && isset( $meta['width'] ) && isset( $meta['height'] ) ) {
				$media_dims = "<span id='media-dims-" . $image->ID . "'>" . $meta['width'] . '&nbsp;&times;&nbsp;' . $meta['height'] . "</span>";
			} else {
				$media_dims = '';
			}

			$alt = get_post_meta( $image->ID, '_wp_attachment_image_alt', true );
			?>
			<li>
				<?php echo wp_get_attachment_link( $image->ID, 'thumbnail' ); ?>
				<div class="image-meta"><strong><?php _e( 'File Info:', APP_TD ) ?></strong> <?php echo $media_dims; ?> <?php echo $image->post_mime_type; ?></div>
			</li>
			<?php
		endforeach;

		echo '</ul></div>';
	}
	?>
	<p class="optional"><label for="your-photo"><?php _e( 'Resume Photo (.jpg, .gif or .png)', APP_TD ); ?></label><input type="file" class="text" name="your-photo" id="your-photo" /></p>
	<?php
}

/**
 * @since 1.8
 */
function get_the_jr_user_online_resumes( $user_id = 0, $args = array() ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	$defaults = array(
		'ignore_sticky_posts' => 1,
		'posts_per_page'      => -1,
		'author'              => $user_id,
		'post_type'           => APP_POST_TYPE_RESUME,
		'post_status'         => 'publish'
	);
	$args = wp_parse_args( $args, $defaults );

	$resumes = new WP_Query( $args );

	return $resumes;
}

/**
 * @since 1.8
 */
function the_user_online_resumes_dropdown( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();

	$my_query = get_the_jr_user_online_resumes( $user_id );

	if ( empty( $my_query->posts ) ) {
		return;
	}

	echo '<select name="your_online_cv" id="your_online_cv">';

	echo html( 'option', __( 'None', APP_TD ) );

	while ( $my_query->have_posts() ) : $my_query->the_post();

		$selected = !empty( $_POST['your_online_cv'] ) && $_POST['your_online_cv'] == $my_query->post->ID ? 'selected' : '';

		$args = array( 'value' => $my_query->post->ID );

		if ( $selected ) {
			$args['selected'] = 'selected';
		}

		echo html( 'option', $args, $my_query->post->post_title );

	endwhile;

	wp_reset_query();

	echo '</select>';
}

/**
 * @since 1.8
 */
function the_resume_posted_by( $text = '', $before = '', $after = '' ) {
	$text = $text ? $text : __( 'Resume posted by ', APP_TD );

	echo $text . $before . wptexturize( get_the_author_meta( 'display_name' ) ) . $after;
}

/**
 * @since 1.8
 */
function the_resume_category( $text = '', $before = '', $after = '' ) {
	$terms = wp_get_post_terms( get_the_ID(), APP_TAX_RESUME_CATEGORY );
	if ( $terms ) {
		$text = $text ? $text : __( ' in ', APP_TD );
		echo $text . $before . $terms[0]->name . $after;
	}
}

/**
 * @since 1.8
 */
function the_resume_salary( $text = '', $before = '', $after = '' ) {
	$desired_salary = get_post_meta( get_the_ID(), '_desired_salary', true );

	if ( $desired_salary ) {
		$text = $text ? $text : __( 'Desired salary: ', APP_TD );
		echo $text . $before . appthemes_get_price( $desired_salary ) . $after;
	}
}

/**
 * @since 1.8
 */
function the_resume_desired_position( $text = '', $text_alt = '', $before = '', $after = '' ) {
	$terms = wp_get_post_terms( get_the_ID(), APP_TAX_RESUME_JOB_TYPE );

	$text = $text ? $text : __( 'Desired position type: ', APP_TD );
	$text_alt = $text_alt ? $text_alt : __( 'Any', APP_TD );

	if ( $terms ) {
		echo $text . $before . $terms[0]->name . $after;
	} else {
		echo $text . $before . $text_alt . $after;
	}
}

/**
 * @since 1.8
 */
function the_resume_location( $text = '', $before = '', $after = '' ) {
	$address = get_post_meta( get_the_ID(), 'geo_short_address', true );
	$country = get_post_meta( get_the_ID(), 'geo_short_address_country', true );

	if ( $address ) {
		$text = $text ? $text : __( 'Location: ', APP_TD );
		echo $text . $before . wptexturize( $address ) . ( $country ? ' ' . wptexturize( $country ) : '' ) . $after;
	}
}

/**
 * @uases apply_filters() Calls 'jr_resume_contact_details'
 *
 * @since 1.8
 */
function the_contact_details( $text = '', $before = '', $after = '' ) {
	global $post, $jr_options;

	$contact_details = array(
		'email' => array(
			'label' => __( 'Email:', APP_TD ),
			'value' => get_post_meta( $post->ID, '_email_address', true ),
		),
		'tel' => array(
			'label' => __( 'Tel:', APP_TD ),
			'value' => get_post_meta( $post->ID, '_tel', true ),
		),
		'mobile' => array(
			'label' => __( 'Mobile:', APP_TD ),
			'value' => get_post_meta( $post->ID, '_mobile', true ),
		),
	);
	$contact_details = apply_filters( 'jr_resume_contact_details', $contact_details );

	if ( $jr_options->jr_resume_show_contact_form && $post->post_author != get_current_user_id() ) {
		?>
		<p class="button">
			<a class="contact_button inline noscroll" href="#contact">
				<?php echo sprintf( __( 'Contact %s', APP_TD ), wptexturize( get_the_author_meta( 'display_name' ) ) ); ?>
			</a>
		</p>
		<?php
	} else {

		$contact_html = '';

		foreach ( $contact_details as $key => $contact ) {
			$contact_html .= html( 'dt class="' . esc_attr( $key ) . '"', $contact['label'] );

			if ( 'email' == $key ) {
				$mailto = $contact['value'] . '?subject=' . __( 'Your Resume on', APP_TD ) . ' ' . get_bloginfo( 'name' );
				$contact['value'] = html( 'a href="mailto:' . $mailto . '"', $contact['value'] );
			}

			$contact_html .= html( 'dd', $contact['value'] );
		}

		if ( $contact_html ) {
			$contact_html = html( 'dl', $contact_html );

			echo $contact_html;
		}

	}

	$websites = get_post_meta( $post->ID, '_resume_websites', true );

	if ( $websites && is_array( $websites ) ) {
		$loop = 0;

		$websites_html = '';

		foreach ( $websites as $website ) {
			$websites_html .= html( 'dt class="email"', strip_tags( $website['name'] ) );

			$delete_html = '';
			if ( $post->post_author == get_current_user_id() ) {
				$delete_html = html( 'a class="delete" href="?delete_website=' . $loop . '"', ' [&times;]' );
			}

			$websites_html .= html( 'dd class="email"', html( 'a href="' . esc_url( $website['url'] ) . '" target="_blank" rel="nofollow"', strip_tags( $website['url'] ) ) . $delete_html );

			$loop++;
		}

		if ( $websites_html ) {
			$websites_html = html( 'dl', $websites_html );

			echo $websites_html;
		}
	}

	if ( $post->post_author == get_current_user_id() ) {
		echo html( 'p class="edit_button button"', html( 'a class="inline noscroll" href="#websites"', __( '+ Add Website', APP_TD ) ) );
	}
}

/**
 * @since 1.8
 */
function the_resume_fields() {
	global $post;

	### Education & Experience

	$meta_fields = array(
		'_education' => __( 'Education', APP_TD ),
		'_experience' => __( 'Experience', APP_TD ),
	);

	foreach ( $meta_fields as $meta_key => $section ) :
		?>
		<h2 class="resume_section_heading"><span><?php echo $section; ?></span></h2>
		<div class="resume_section">
		<?php echo wpautop( wptexturize( get_post_meta( $post->ID, $meta_key, true ) ) ); ?>
		</div>
		<div class="clear"></div>
		<?php
	endforeach;

	### Skills

	$skills = array_map( 'trim', explode( "\n", get_post_meta( $post->ID, '_skills', true ) ) );

	if ( $skills ) {

		$skills_html = '';

		foreach ( $skills as $skill ) {
			if ( !empty( $skill ) ) {
				$skills_html .= html( 'li', wptexturize( $skill ) );
			}
		}

		if ( $skills_html ):
			$skills_html = html( 'ul', $skills_html );
			?>
			<h2 class="resume_section_heading"><span><?php echo __( 'Skills', APP_TD ); ?></span></h2>
			<div class="resume_section"><?php echo $skills_html; ?></div>
			<div class="clear"></div>
			<?php
		endif;
	}

	### Taxonomies

	$use_taxonomies = array(
		APP_TAX_RESUME_SPECIALITIES => __( 'Specialties', APP_TD ),
		APP_TAX_RESUME_LANGUAGES => __( 'Spoken Languages', APP_TD ),
		APP_TAX_RESUME_GROUPS => __( 'Groups &amp; Associations', APP_TD )
	);

	$taxonomies = get_object_taxonomies( $post, 'objects' );

	$terms_html = '';

	foreach ( $taxonomies as $taxonomy ):

		if ( !isset( $use_taxonomies[$taxonomy->name] ) ) {
			continue;
		}

		$terms = wp_get_post_terms( $post->ID, $taxonomy->name );

		$terms_html = '';
		if ( !empty( $terms ) ) {
			$terms_name = wp_list_pluck( $terms, 'name' );

			$terms_html .= implode( '</li>, </li> ', $terms_name );
		}

		if ( $terms_html ) :
			$terms_html = html( 'ul', $terms_html );
			?>
			<h2 class="resume_section_heading"><span><?php echo $use_taxonomies[$taxonomy->name]; ?></span></h2>
			<div class="resume_section"><?php echo $terms_html; ?></div>
			<div class="clear"></div>
			<?php
		endif;

	endforeach;
}

/**
 * @since 1.8
 */
function the_jr_submit_resume_button_text() {
	global $jr_options;

	if ( $text = $jr_options->jr_submit_resume_button_text ) {
		echo wpautop( wptexturize( $text ) );
	}
}

/**
 * @since 1.8
 */
function the_jr_myprofile_button_text() {
	global $jr_options;

	if ( $text = $jr_options->jr_my_profile_button_text ) {
		echo wpautop( wptexturize( $text ) );
	}
}

/**
 * @since 1.8
 */
function the_jr_sidebar_nav_tax_item( $taxonomy, $label, $args = array() ) {

	$defaults = array(
		'hierarchical' => false,
		'parent' => 0,
	);
	$args = wp_parse_args( $args, $defaults );

	$terms = get_terms( $taxonomy, apply_filters( 'jr_nav_' . $taxonomy, $args ) );

	$curr_term = get_queried_object();

	if ( $terms ):

		ob_start();
		?>
		<li>
			<a class="top" href="#open"><?php echo $label; ?></a>
			<ul>
				<?php foreach ( $terms as $term ) : ?>
				<li class="page_item <?php echo is_tax() && $curr_term && $curr_term->slug == $term->slug ? 'current_page_item' : ''; ?>">
						<a href="<?php echo esc_url( get_term_link( $term->slug, $taxonomy ) ); ?>"><?php echo $term->name; ?></a>
					</li>

					<?php
					if ( $term->count ):
						//	echo terms childrens
						$children = get_term_children( $term->term_id, $taxonomy );

						if ( is_array( $children ) ) :
						?>
							<?php foreach ( $children as $child ):
									$child_term = get_term_by( 'id', $child, 'job_cat' );
									?>
									<li class="page_item page_item_children <?php echo is_tax() && $curr_term && $curr_term->slug == $child_term->slug ? 'current_page_item' : ''; ?>">
										<a href="<?php echo esc_url( get_term_link( $child_term->slug, $taxonomy ) ); ?>">- <?php echo $child_term->name; ?></a>
									</li>
							<?php endforeach; ?>

						<?php endif; ?>

					<?php endif; ?>

				<?php endforeach; ?>
			</ul>
		</li>
		<?php
			$html = ob_get_clean();

			echo apply_filters( 'jr_sidebar_nav_tax_item', $html, $taxonomy, $label, $args );
	endif;
}

/**
 * @since 1.8
 */
function the_jr_sidebar_nav_tab( $taxonomy, $args = array() ) {

	$defaults = array(
		'hierarchical' => false,
		'parent' => 0
	);
	$args = wp_parse_args( $args, $defaults );

	$terms = get_terms( $taxonomy, apply_filters( 'jr_nav_' . $taxonomy, $args ) );

	if ( $terms ):

		ob_start();
?>
		<ul class="job_tags">
		<?php foreach ( $terms as $term ) : ?>
				<li><a href="<?php echo esc_url( get_term_link( $term->slug, $taxonomy ) ); ?>"><?php echo $term->name; ?></a></li>
		<?php endforeach; ?>
		</ul>
<?php
		$html = ob_get_clean();

		echo apply_filters( 'jr_sidebar_nav_tab', $html, $taxonomy, $args );
	endif;
}

/**
 * @since 1.8
 */
function the_jr_sidebar_nav_date_items() {

	if ( !( $datepage = JR_Date_Archive_Page::get_id() ) ) {
		return;
	}
	$datepagelink = get_permalink( $datepage );

	$dates = array(
		'today' => __( 'Today', APP_TD ),
		'week' => __( 'This Week', APP_TD ),
		'lastweek' => __( 'Last Week', APP_TD ),
		'month' => __( 'This Month', APP_TD ),
	);

	$items_html = '';

	foreach ( $dates as $key => $label ) {
		$items_html .= html( 'li', html( 'a href="' . esc_url( add_query_arg( array( 'time' => $key, 'jobs_by_date' => 1 ), $datepagelink ) ) . '"', $label ) );
	}

	$items_html = html( 'li', html( 'a class="top" href="#open"', __( 'Date posted', APP_TD ) ) . html( 'ul', $items_html ) );

	echo apply_filters( 'jr_sidebar_nav_date_tiems', $items_html );
}

/**
 * @since 1.8
 */
function the_jr_top_nav_menu() {
	wp_nav_menu( array( 'theme_location' => 'top', 'sort_column' => 'menu_order', 'container' => 'menu-header', 'fallback_cb' => 'default_top_nav' ) );
}

/**
 * @since 1.8
 */
function the_jr_main_nav_menu() {
	wp_nav_menu( array( 'theme_location' => 'primary', 'container' => '', 'depth' => 1, 'fallback_cb' => 'default_primary_nav' ) );
}

/**
 * @since 1.8
 */
function jr_output_author_blog_posts( $user_id ) {
	$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

	$args = array(
		'author' => $user_id,
		'post_status' => 'publish',
		'paged' => $paged,
		'posts_per_page' => get_option( 'posts_per_page' ),
	);
	query_posts( $args );

	appthemes_load_template( 'loop.php' );

	jr_paging();

	wp_reset_query();
}

/**
 * @since 1.8
 */
function jr_output_author_jobs( $user_id ) {
	global $jr_options;

	$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

	$args = array(
		'jr_author_jobs' => true,
		'author' => $user_id,
		'post_type' => APP_POST_TYPE,
		'post_status' => 'publish',
		'paged' => $paged,
		'posts_per_page' => jr_get_jobs_per_page(),
	);
	query_posts( $args );

	appthemes_load_template( 'loop-job.php', compact( 'jr_options' ) );

	jr_paging();

	wp_reset_query();
}

/**
 * @since 1.8
 */
function jr_output_author_resumes( $user_id ) {
	global $jr_options;

	$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

	$args = array(
		'jr_author_resumes' => true,
		'author'            => $user_id,
		'post_type'         => APP_POST_TYPE_RESUME,
		'post_status'       => 'publish',
		'paged'             => $paged,
		'posts_per_page'    => jr_get_resumes_per_page(),
	);
	query_posts( $args );

	appthemes_load_template( 'loop-resume.php', compact( 'jr_options' ) );

	jr_paging();

	wp_reset_query();
}

/**
 * @since 1.8
 */
function jr_get_the_resume_edit_link( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return add_query_arg( 'edit', $post_id, get_permalink( JR_Resume_Edit_Page::get_id() ) );
}

/**
 * @since 1.8
 */
function jr_get_the_resume_toggle_vis_link( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return add_query_arg( 'toggle_visibility', $post_id );
}

/**
 * @since 1.8
 */
function jr_get_the_resume_delete_link( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return add_query_arg( 'delete_resume', $post_id );
}
