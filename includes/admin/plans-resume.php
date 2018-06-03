<?php
/**
 * Custom 'Resume' related meta boxes for the 'Plans' single page.
 *
 * @version 1.6
 * @author AppThemes
 * @package JobRoller\Admin\Plans\Resume
 * @copyright 2010 all rights reserved
 */


### Classes

class JR_Resumes_Pricing_General_Box extends APP_Meta_Box{

	public function __construct(){
		parent::__construct( 'resumes-pricing-details', __( 'Resume Plan Details' , APP_TD ), APPTHEMES_RESUMES_PLAN_PTYPE, 'normal', 'default' );
	}

	public function before_form( $post ){
		?><style type="text/css">#notice{ display: none; }</style><?php
	}

	public function form(){

		$fields =  array(
			array(
				'title' => __( 'Name', APP_TD ),
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
				'title' => __( 'Trial', APP_TD ),
				'type' => 'checkbox',
				'name' => 'trial',
				'desc' => __( 'Allow Job Seeker\'s to Browse/View Resumes for a limited period of time' , APP_TD ),
				'extra' => array(
					'tabindex' => 3,
				),
			),
			array(
				'title' => __( 'Price', APP_TD ),
				'type' => 'text',
				'name' => 'price',
				'desc' => sprintf( __( 'Example: %s ( 0 = Free ) ' , APP_TD ), '15' ),
				'extra' => array(
					'class' => 'small-text',
					'tabindex' => 4,
				)
			),
			array(
				'title' => _jr_is_recurring_available() ? __( 'Recurs Every *', APP_TD ) : __( 'Duration', APP_TD ) ,
				'type' => 'text',
				'name' => 'duration',
				'desc' => __( 'day(s). The subscription duration' , APP_TD ),
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
			array(
				'title' => '',
				'type' => 'hidden',
				'name' => 'recurring',
			),
		);

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

	public function after_form( $post ) {

		if ( _jr_is_recurring_available() ) {
			echo html( 'p', __( '(*) Please note that auto recurring payments may not be available to all gateways. Subscriptions will default to one-off manual payments when auto-recurring is not available.', APP_TD ) );
		}

	}

	public function before_save( $data, $post_id ){

		$required_user_entry = $this->get_required_user_entry_fields();

		foreach( $data as $field => $value ) {

			if ( in_array( $field, $required_user_entry ) ) {
				continue;
			}

			if ( ! is_numeric( $data[ $field ] ) && '' === $data[ $field ] ) {
				$data[ $field ] = absint( $value );
			}
		}

		if ( ! $data[JR_FIELD_PREFIX.'trial'] ) {
			$data[JR_FIELD_PREFIX.'recurring'] = 1;
		}

		return $data;
	}

	private function get_required_user_entry_fields() {

		$fields = array(
			JR_FIELD_PREFIX.'price',
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

class JR_Resumes_Addon_Box extends APP_Meta_Box{

	public function __construct(){
		parent::__construct( 'resumes-addons', __( 'Resumes Addons', APP_TD ), APPTHEMES_PRICE_PLAN_PTYPE, 'normal', 'default' );
	}

	public function condition() {
		global $jr_options;
		return (bool) $jr_options->jr_resume_require_subscription;
	}

	public function form(){

		$output = array();

		foreach( (array) _jr_resumes_addons() as $addon ){

			$enabled = array(
				'title' => APP_Item_Registry::get_title( $addon ),
				'type' => 'checkbox',
				'name' => $addon,
				'desc' => __( 'Included', APP_TD ),
			);

			$duration = array(
				'title' => __( 'Duration', APP_TD ),
				'type' => 'text',
				'name' => $addon . '_duration',
				'desc' => __( 'days', APP_TD ),
				'extra' => array(
					'size' => '3',
					'tabindex' => 20,
				),
			);

			$output[] = $enabled;
			$output[] = $duration;

		}

		return $output;

	}

	public function before_save( $data, $post_id ){

		foreach( (array)_jr_resumes_addons() as $addon ){

			if( !empty( $data[ $addon ] ) && empty( $data[ $addon . '_duration' ] ) ){
				$data[ $addon . '_duration' ] = get_post_meta( $post_id, JR_FIELD_PREFIX . 'duration', true );
			}

			$data[ $addon . '_duration' ] = absint( $data[ $addon . '_duration' ] );

		}

		return $data;
	}

	public function validate_post_data( $data, $post_id ) {
		$errors = new WP_Error();

		$post_id = intval( $_POST['ID'] );

		$pack_duration = intval( get_post_meta( $post_id, JR_FIELD_PREFIX . 'pack_duration', true ) );
		foreach( (array)_jr_resumes_addons() as $addon ){

			if ( !empty( $data[ $addon . '_duration' ] ) ) {

				$addon_duration = $data[ $addon . '_duration' ];
				if ( !is_numeric( $addon_duration ) ) {
					$errors->add( $addon . '_duration', '' );
				}

				if ( intval( $addon_duration ) < 0 ) {
					$errors->add( $addon . '_duration', '' );
				}

			}

		}

		return $errors;
	}

	public function before_form( $post ){
		echo html( 'p', array(), __( 'You can include view/browse resumes access addons in a plan. These will be immediately assigned to the user upon purchase. After they run out, the customer can then purchase regular view/browse resumes access addons or subscribe to resumes access, separately.', APP_TD ) );
	}


	public function after_form( $post ){
		echo html( 'p', array('class' => 'howto'), __( 'Access will still be determined by your visibility settings. e.g. if you set the resume visibility for \'Recruiters\', only Recruiters will have the resumes addons included on the plan.', APP_TD ) );
	}

}