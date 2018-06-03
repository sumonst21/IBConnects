<?php
/**
 * Custom 'Job' related meta boxes for the 'Plans' single page.
 *
 * @version 1.6
 * @author AppThemes
 * @package JobRoller\Admin\Plans\Job
 * @copyright 2010 all rights reserved
 */


### Classes

class JR_Pricing_General_Box extends APP_Meta_Box{

	public function __construct(){
		parent::__construct( 'pricing-details', __( 'Pricing Details' , APP_TD ), APPTHEMES_PRICE_PLAN_PTYPE, 'normal', 'default' );
	}

	public function before_form( $post ){
		?><style type="text/css">#notice{ display: none; }</style><?php
		global $jr_options;

		if ( 'pack' == $jr_options->plan_type ) {
			echo html( 'span', array( 'class' => 'pack-fields' ) );
		}
	}

	public function form(){
		global $jr_options;

		$fields =  array(
			array(
				'title' => __( 'Plan Name', APP_TD ),
				'type' => 'text',
				'name' => 'title',
				'extra' => array(
					'tabindex' => 1,
				),
			),
			array(
				'title' => __( 'Description', APP_TD ),
				'type' => 'custom',
				'render' => array( $this, 'rich_description' ),
				'name' => 'description',
				'sanitize' => 'wp_kses_post',
			),
			array(
				'title' => __( 'Price', APP_TD ),
				'type' => 'text',
				'name' => 'price',
				'desc' => sprintf( __( 'Example: %s ( 0 = Free )' , APP_TD ), '25' ),
				'extra' => array(
					'class' => 'small-text',
					'tabindex' => 3,
				)
			),
			array(
				'title' => __( 'Relist Price', APP_TD ),
				'type' => 'text',
				'name' => 'relist_price',
				'desc' => sprintf( __( 'Example: %s ( 0 = Free Relisting )' , APP_TD ), '15' ),
				'extra' => array(
					'class' => 'small-text',
					'tabindex' => 4,
				)
			),
			array(
				'title' => __( 'Job Duration', APP_TD ),
				'type' => 'text',
				'name' => 'duration',
				'desc' => __( 'day(s) ( 0 = Endless )', APP_TD),
				'extra' => array(
					'class' => 'small-text',
					'tabindex' => 5,
				)
			),
			array(
				'title' => __( 'Usage Limit', APP_TD ),
				'type' => 'text',
				'name' => 'limit',
				'desc' => __( 'use(s). How many times can this Plan be selectable by the same user? ( 0 = Unlimited Uses )', APP_TD),
				'extra' => array(
					'class' => 'small-text',
					'tabindex' => 6,
				)
			),
		);

		if ( ! jr_allow_relist() ) {
			unset( $fields[3] ); // remove allow relist option
		}

		if ( 'pack' == $jr_options->plan_type ) {

			$additional_pack_fields = array(
				array(
					'title' => __( 'Pack :: Job Count', APP_TD ),
					'type' => 'text',
					'name' => 'jobs_limit',
					'desc' => __( 'How many jobs can the user list with this Plan? ( 0 = Unlimited )' , APP_TD ),
					'extra' => array(
						'class' => 'small-text',
						'tabindex' => 7,
					)
				),
				array(
					'title' => __( 'Pack :: Job Offers', APP_TD ),
					'type' => 'text',
					'name' => 'job_offers_limit',
					'desc' => __( 'Job offers are added to the jobs count ( Total Jobs = Job Count + Job Offers ) ( 0 = No Offers )', APP_TD ),
					'extra' => array(
						'class' => 'small-text',
						'tabindex' => 8,
					)
				),
				array(
					'title' => __( 'Pack :: Duration', APP_TD ),
					'type' => 'text',
					'name' => 'pack_duration',
					'desc' => __( 'day(s). Days this Plan remains valid to use ( 0 = Endless ) ', APP_TD),
					'extra' => array(
						'class' => 'small-text',
						'tabindex' => 9,
					)
				),
			);

			$fields = array_merge( $fields, $additional_pack_fields );
		}

		return _jr_prefix_fields( $fields );

	}

	function rich_description() {
		global $post;

		$description = get_post_meta( $post->ID, JR_FIELD_PREFIX . 'description', true );

		ob_start();

		wp_editor( $description, JR_FIELD_PREFIX . 'description', array(
			'wpautop' => true,
			'textarea_name' =>  JR_FIELD_PREFIX . 'description',
			'textarea_rows' => 10,
		));
		return ob_get_clean();
	}

	public function validate_post_data( $data, $post_id ) {
		global $jr_options;

		$errors = new WP_Error();

		$fields = array(
			array(
				'name' => 'title',
				'type' => 'text'
			),
			array(
				'name' => 'price',
				'type' => 'numeric'
			),
			array(
				'name' => 'duration',
				'type' => 'numeric'
			),
			array(
				'name' => 'limit',
				'type' => 'numeric'
			),
		);

		if ( 'pack' == $jr_options->plan_type ) {
			$pack_fields = array(
				array(
					'name' => 'jobs_limit',
					'type' => 'numeric'
				),
				array(
					'name' => 'pack_duration',
					'type' => 'numeric'
				),
				array(
					'name' => 'job_offers_limit',
					'type' => 'numeric'
				),
			);

			$fields = array_merge( $fields, $pack_fields );
		}

		if ( jr_allow_relist() ) {
			$relist_field = array (
			 array(
				'name' => 'relist_price',
				'type' => 'numeric',
			) );
			$fields = array_merge( $fields, $relist_field );
		}

		$fields = _jr_prefix_fields( $fields );

		$required_user_entry = $this->get_required_user_entry_fields();

		foreach ( $fields as $field ) {

			$error = FALSE;
			if ( 'text' == $field['type'] ) {
				if ( empty( $data[ $field['name'] ] ) ) {
					$error = TRUE;
				}
			} else {
				if ( ! is_numeric( $data[ $field['name'] ] ) || $data[ $field['name'] ] < 0 ) {
					$error = TRUE;
				}
			}
			if ( $error ) {
				if ( in_array( $field['name'], $required_user_entry ) ) {
					$errors->add( $field['name'], '' );
				}
			}

		}

		// if there are errors make sure to save the description on the post meta
		if ( $errors->get_error_code() ) {
			update_post_meta( $post_id, JR_FIELD_PREFIX . 'description', $data[ JR_FIELD_PREFIX . 'description'] );
		}

		return $errors;
	}


	public function before_save( $data, $post_id ) {

		$required_user_entry = $this->get_required_user_entry_fields();

		foreach( $data as $field => $value ) {

			if ( in_array( $field, $required_user_entry ) ) {
				continue;
			}

			if ( ! is_numeric( $data[ $field ] ) && '' === $data[ $field ] ) {
				$data[ $field ] = absint( $value );
			}
		}
		return $data;
	}

	private function get_required_user_entry_fields() {

		$fields = array(
			JR_FIELD_PREFIX.'price',
			JR_FIELD_PREFIX.'relist_price',
			JR_FIELD_PREFIX.'duration'
		);

		return $fields;
	}

	public function post_updated_messages( $messages ) {
		$messages[ APPTHEMES_PRICE_PLAN_PTYPE ] = array(
		 	1 => __( 'Plan updated.', APP_TD ),
		 	4 => __( 'Plan updated.', APP_TD ),
		 	6 => __( 'Plan created.', APP_TD ),
		 	7 => __( 'Plan saved.', APP_TD ),
		 	9 => __( 'Plan scheduled.', APP_TD ),
			10 => __( 'Plan draft updated.', APP_TD ),
		);
		return $messages;
	}

}

class JR_Featured_Addon_Box extends APP_Meta_Box {

	public function __construct(){
		parent::__construct( 'pricing-addons', __( 'Featured Addons', APP_TD ), APPTHEMES_PRICE_PLAN_PTYPE, 'normal', 'default' );
	}

	public function form(){
		global $jr_options;

		$output = array();

		foreach( _jr_featured_addons() as $addon ){

			$enabled = array(
				'title' => APP_Item_Registry::get_title( $addon ),
				'type' => 'checkbox',
				'name' => $addon,
				'desc' => __( 'Included', APP_TD ),
				'extra' => array(
				),
			);

			$duration = array(
				'title' => __( 'Duration', APP_TD ),
				'type' => 'text',
				'name' => $addon . '_duration',
				'desc' => __( 'days', APP_TD ),
				'extra' => array(
					'size' => '3',
					'tabindex' => 15,
				),
			);

			$output[] = $enabled;
			$output[] = $duration;

			if ( 'pack' == $jr_options->plan_type ) {
				$uses = array(
					'title' => __( 'Limit', APP_TD ),
					'type' => 'text',
					'name' => $addon . '_limit',
					'desc' => __( 'use(s) ( 0 = Unlimited Uses )', APP_TD ),
					'extra' => array(
						'size' => '3',
						'tabindex' => 16,
					),
				);
				$output[] = $uses;
			}

		}

		return $output;

	}

	public function before_save( $data, $post_id ){

		foreach( _jr_featured_addons() as $addon ){

			if ( !empty( $data[ $addon ] ) && empty( $data[ $addon . '_duration' ] ) ) {
				$data[ $addon . '_duration' ] = get_post_meta( $post_id, JR_FIELD_PREFIX . 'duration', true );
			}

			$data[ $addon . '_duration' ] = absint( $data[ $addon . '_duration' ] );

		}

		return $data;
	}

	public function validate_post_data( $data, $post_id ) {
		$errors = new WP_Error();

		$limits = 0;

		$post_id = intval( $_POST['ID'] );

		$jobs_limit = intval( get_post_meta( $post_id, JR_FIELD_PREFIX . 'jobs_limit', true ) );
		$jobs_limit += intval( get_post_meta( $post_id, JR_FIELD_PREFIX . 'job_offers_limit', true ) );
		$job_listing_duration = intval( get_post_meta( $post_id, JR_FIELD_PREFIX . 'duration', true ) );
		foreach( _jr_featured_addons() as $addon ){

			if ( !empty( $data[ $addon . '_duration' ] ) ) {

				$addon_duration = $data[ $addon . '_duration' ];
				if ( !is_numeric( $addon_duration ) ) {
					$errors->add( $addon . '_duration', '' );
				}

				if ( intval( $addon_duration ) > $job_listing_duration && $job_listing_duration != 0 ) {
					$errors->add( $addon . '_duration', '' );
				}

				if ( intval( $addon_duration ) < 0 ) {
					$errors->add( $addon . '_duration', '' );
				}

			}

			if ( ! empty( $data[ $addon . '_limit' ] ) ) {
				if ( $data[ $addon . '_limit' ] > $jobs_limit ) {
					$errors->add( $addon . '_limit', '' );
				}
			}

		}
		return $errors;
	}

	public function before_form( $post ){
		echo html( 'p', array(), __( 'You can include featured addons in a plan. These options will be selectable free of charge by the user before purchase. After they run out, the customer can then purchase regular featured addons.', APP_TD ) );
	}


	public function after_form( $post ){
		global $jr_options;

		echo html( 'p', array('class' => 'howto'), __( 'Durations must be shorter or equal than the Job duration.', APP_TD ) );
		if ( 'pack' == $jr_options->plan_type ) {
			echo html( 'p', array('class' => 'howto'), __( 'Limits must be lower than the job count + job offers sum.', APP_TD ) );
		}
	}

}
