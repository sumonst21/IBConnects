<?php
/**
 * Custom meta boxes used by JobRoller.
 *
 * @version 1.2
 * @author AppThemes
 * @package JobRoller\Admin\Meta-Box
 */

add_action( 'admin_init', 'jr_remove_metaboxes' );
add_action( 'admin_menu', 'jr_create_meta_box' );
add_action( 'save_post', 'jr_save_meta_box' );
add_action( 'add_meta_boxes', '_jr_sidebar_meta' );


### Hook Callbacks

/**
 * Removes unnecessary metaboxes.
 *
 * @return void
 */
function jr_remove_metaboxes() {
	$remove_boxes = array( 'commentstatusdiv', 'commentsdiv', 'revisionsdiv', 'trackbacksdiv' );

	foreach ( $remove_boxes as $id ) {
		remove_meta_box( $id, APP_POST_TYPE, 'normal' );
	}
}

function jr_create_meta_box() {

	add_meta_box( 'location-meta-boxes', __( 'Job Location', APP_TD ), 'jr_display_location_meta_box', APP_POST_TYPE, 'side', 'default' );
	add_meta_box( 'location-meta-boxes', __( 'Location', APP_TD ), 'jr_display_location_meta_box', APP_POST_TYPE_RESUME, 'side', 'default' );

	new JR_Job_Meta();
	new JR_Job_Meta_How_Apply();

	new JR_Resume_Skills_Meta();
	new JR_Resume_Meta_Educ_Exp();
	new JR_Resume_Meta_Basic();
	new JR_Job_Pricing_Meta();
	new JR_Job_Publish_Moderation();

	new JR_Pricing_General_Box();
	new JR_Featured_Addon_Box();
	new JR_Resumes_Pricing_General_Box();
	new JR_Resumes_Addon_Box();

	remove_meta_box( 'authordiv', APP_POST_TYPE, 'normal' );
	remove_meta_box( 'authordiv', APP_POST_TYPE_RESUME, 'normal' );
}

function jr_save_meta_box( $post_id ) {
	$key = 'job_meta';

	if ( ! isset( $_POST[$key . '_wpnonce'] ) ) {
		return $post_id;
	}

	if ( ! wp_verify_nonce( $_POST[$key . '_wpnonce'], plugin_basename( __FILE__ ) ) ) {
		return $post_id;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	// Update location
	if ( ! empty( $_POST['jr_address'] ) ) {
		jr_update_post_geo_metadata( $post_id, $_POST,  $_POST['jr_geo_latitude'],  $_POST['jr_geo_longitude'] );
	} else {
		// They left the field blank so we assume the job is for 'anywhere'
		delete_post_meta( $post_id, '_jr_geo_latitude' );
		delete_post_meta( $post_id, '_jr_geo_longitude' );
		delete_post_meta( $post_id, 'geo_address' );
		delete_post_meta( $post_id, 'geo_country' );
		delete_post_meta( $post_id, 'geo_short_address' );
		delete_post_meta( $post_id, 'geo_short_address_country' );
	}
}

function _jr_sidebar_meta() {
	foreach ( array( APP_POST_TYPE, APP_POST_TYPE_RESUME )as $post_type ) {
		add_meta_box( 'authordiv', __( 'Author', APP_TD ), 'post_author_meta_box', $post_type, 'side', 'low' );
	}
}


### Custom Metaboxes


/**
 * Job Meta Metabox.
 */
class JR_Job_Meta extends APP_Meta_Box {

	/**
	 * Sets up metabox.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct( 'new-meta-boxes', __( 'Job Meta', APP_TD ), APP_POST_TYPE, 'normal', 'high' );
	}

	/**
	 * Returns an array of form fields.
	 *
	 * @return array
	 */
	public function form() {

		$meta_boxes = array(
			array(
				'title' => __( 'Your Name/Company Name', APP_TD ),
 				'type' => 'text',
				'name' => '_Company',
				'desc' => html( 'em', __( 'The name of the company advertising the job.', APP_TD ) ),
			),
			array(
				'title' => __( 'Website', APP_TD ),
				'type' => 'text',
				'name' => '_CompanyURL',
				'desc' => html( 'em', __( 'Website URL of the company advertising the job.', APP_TD ) ),
			),
		);

		return $meta_boxes;
	}

	/**
	 * Displays extra HTML before the form.
	 *
	 * @param object $post
	 *
	 * @return void
	 */
	public function before_form( $post ) {
		_e( 'These fields control parts of job listings. Remember also that: <code>title</code> = Job title, <code>content</code> = Job description, and Post thumbnail/image is used for the company logo.', APP_TD );
	}

}


/**
 * How to Apply Metabox.
 */
class JR_Job_Meta_How_Apply extends APP_Meta_Box {

	/**
	 * Sets up metabox.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct( 'new-meta-boxes-how-apply', __( 'How to Apply', APP_TD ), APP_POST_TYPE, 'normal', 'high' );
	}

	/**
	 * Additional checks before registering the metabox.
	 *
	 * @return bool
	 */
	public function condition() {
		global $jr_options;

		return (bool) $jr_options->jr_submit_how_to_apply_display;
	}

	/**
	 * Displays content.
	 *
	 * @param object $post
	 *
	 * @return void
	 */
	public function display( $post ) {
		$field = '_how_to_apply';
		wp_editor(
			get_post_meta( $post->ID, $field, true ), $field, array(
				'media_buttons' => false,
				'textarea_name' => $field,
				'textarea_rows' => 10,
				'tabindex' => '2',
				'editor_css' => '',
				'editor_class' => '',
				'teeny' => false,
				'tinymce' => true,
				'quicktags' => array( 'buttons' => 'strong,em,link,block,ul,ol,li,code' )
			)
		);
	}

	/**
	 * Filter data before save.
	 *
	 * @param array $post_data
	 * @param int $post_id
	 *
	 * @return array
	 */
	protected function before_save( $post_data, $post_id ) {
		$field = '_how_to_apply';
		if ( isset( $_POST[ $field ] ) ) {
			$post_data[ $field ] = $_POST[ $field ];
		}

		return $post_data;
	}

}


/**
 * Resume Meta Metabox.
 */
class JR_Resume_Meta_Basic extends APP_Meta_Box {

	/**
	 * Sets up metabox.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct( 'resume-meta-boxes', __( 'Resume Meta', APP_TD ), APP_POST_TYPE_RESUME, 'normal', 'high' );
	}

	/**
	 * Returns an array of form fields.
	 *
	 * @return array
	 */
	public function form() {
		$meta_boxes = array(
			array(
				'title' => __( 'Desired Salary', APP_TD ),
				'type' => 'text',
				'name' => '_desired_salary',
				'desc' => html( 'em', __( 'Desired Salary (only numeric values)', APP_TD ) ),
			),
			array(
				'title' => __( 'Email', APP_TD ),
				'type' => 'text',
				'name' => '_email_address',
				'desc' => html( 'em', __( 'Email address', APP_TD ) ),
			),
			array(
				'title' => __( 'Telephone', APP_TD ),
				'type' => 'text',
				'name' => '_tel',
				'desc' => html( 'em', __( 'Telephone', APP_TD ) ),
			),
			array(
				'title' => __( 'Mobile', APP_TD ),
				'type' => 'text',
				'name' => '_mobile',
				'desc' => html( 'em', __( 'Mobile', APP_TD ) ),
			),
		);

		return $meta_boxes;
	}

}


/**
 * Resume Skills Metabox.
 */
class JR_Resume_Skills_Meta extends APP_Meta_Box {

	/**
	 * Sets up metabox.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct( 'new-meta-boxes-skills', __( 'Skills', APP_TD ), APP_POST_TYPE_RESUME, 'side' );
	}

	/**
	 * Returns an array of form fields.
	 *
	 * @return array
	 */
	public function form() {

		$meta_boxes = array(
			array(
				'title' => __( 'Skills', APP_TD ),
				'type' => 'textarea',
				'name' => '_skills',
				'desc' => html( 'em', __( 'Add Skills (one per line)', APP_TD ) ),
				'extra' => array(
					'rows' => 5,
				),
			),
		);

		return $meta_boxes;
	}

}


/**
 * Resume Education & Experience Metabox.
 */
class JR_Resume_Meta_Educ_Exp extends APP_Meta_Box {

	/**
	 * Sets up metabox.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct( 'resume-meta-boxes-educ-exp', __( 'Education & Experience', APP_TD ), APP_POST_TYPE_RESUME, 'normal', 'high' );
	}


	/**
	 * Displays content.
	 *
	 * @param object $post
	 *
	 * @return void
	 */
	public function display( $post ) {
		$fields = array( '_education' => __( 'Education', APP_TD ), '_experience' => __( 'Experience', APP_TD ) );

		foreach ( $fields as $field => $title ) {

			echo html( 'h2', $title );

			wp_editor(
				get_post_meta( $post->ID, $field, true ), $field, array(
				'media_buttons' => false,
				'textarea_name' => $field,
				'textarea_rows' => 10,
				'tabindex' => '2',
				'editor_css' => '',
				'editor_class' => '',
				'teeny' => false,
				'tinymce' => true,
				'quicktags' => array( 'buttons' => 'strong,em,link,block,ul,ol,li,code' ) )
			);
		}
	}

	/**
	 * Filter data before save.
	 *
	 * @param array $post_data
	 * @param int $post_id
	 *
	 * @return array
	 */
	protected function before_save( $post_data, $post_id ) {
		$fields = array( '_education', '_experience' );

		foreach ( $fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				$post_data[ $field ] = $_POST[ $field ];
			}
		}

		return $post_data;
	}

}


/**
 * Job Pricing/Expiration Details Metabox.
 */
class JR_Job_Pricing_Meta extends APP_Meta_Box {

	/**
	 * Sets up metabox.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct( 'job-listing-pricing', __( 'Expiration Details', APP_TD ), APP_POST_TYPE, 'normal', 'high' );
	}

	/**
	 * Enqueues admin scripts.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		if ( is_admin() ) {
			wp_enqueue_style( 'jquery-ui-style' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'wp-jquery-ui-datepicker', APP_FRAMEWORK_URI . '/styles/datepicker/datepicker.css' );
		}
	}

	/**
	 * Filter data before display.
	 *
	 * @param array $form_data
	 * @param object $post
	 *
	 * @return array
	 */
	public function before_display( $form_data, $post ) {

		$form_data['_blank' . JR_JOB_DURATION_META . '_start_date'] = $post->post_date;
		$form_data['_blank_js' . JR_JOB_DURATION_META . '_start_date'] = mysql2date( 'U', $post->post_date );

		$date_format = get_option( 'date_format' );
		$date_format = str_ireplace( 'm', 'n', $date_format );
		$date_format = str_ireplace( 'd', 'j', $date_format );

		if ( ! empty( $form_data[JR_ITEM_FEATURED_LISTINGS . '_start_date'] ) ) {
			$form_data['_blank' . JR_ITEM_FEATURED_LISTINGS . '_start_date'] = mysql2date( $date_format, $form_data[JR_ITEM_FEATURED_LISTINGS . '_start_date'] );
			$form_data['_blank_js' . JR_ITEM_FEATURED_LISTINGS . '_start_date'] = mysql2date( 'U', $form_data[JR_ITEM_FEATURED_LISTINGS . '_start_date'] );
			$form_data[JR_ITEM_FEATURED_LISTINGS . '_start_date'] = date( 'm/d/Y', strtotime( $form_data[JR_ITEM_FEATURED_LISTINGS . '_start_date'] ) );
		}

		if ( ! empty( $form_data[JR_ITEM_FEATURED_CAT . '_start_date'] ) ) {
			$form_data['_blank' . JR_ITEM_FEATURED_CAT . '_start_date'] = mysql2date( $date_format, $form_data[JR_ITEM_FEATURED_CAT . '_start_date'] );
			$form_data['_blank_js' . JR_ITEM_FEATURED_CAT . '_start_date'] = mysql2date( 'U', $form_data[JR_ITEM_FEATURED_CAT . '_start_date'] );
			$form_data[JR_ITEM_FEATURED_CAT . '_start_date'] = date( 'm/d/Y', strtotime( $form_data[JR_ITEM_FEATURED_CAT . '_start_date'] ) );
		}

		return $form_data;
	}

	/**
	 * Displays extra HTML before the form.
	 *
	 * @param object $post
	 *
	 * @return void
	 */
	public function before_form( $post ) {
		$date_format = get_option( 'date_format', 'm/d/Y' );

		switch ( $date_format ) {
			case "d/m/Y":
			case "j/n/Y":
				$ui_display_format = 'dd/mm/yy';
				break;
			case "Y/m/d":
			case "Y/n/j":
				$ui_display_format = 'yy/mm/dd';
				break;
			case "m/d/Y":
			case "n/j/Y":
			default:
				$ui_display_format = 'mm/dd/yy';
				break;
		}
		?>
		<script type="text/javascript">
			jQuery(function ($) {
				if ( $("#<?php echo JR_JOB_DURATION_META; ?>").val() == '' ) {
					$("#<?php echo JR_JOB_DURATION_META; ?>").val(0);
				}

				createExpireHandler(undefined, $("#<?php echo JR_JOB_DURATION_META; ?>"), $("#_blank<?php echo JR_JOB_DURATION_META; ?>_start_date"), $("#_blank_js<?php echo JR_JOB_DURATION_META; ?>_start_date"), $(''), $("#_blank_expire<?php echo JR_JOB_DURATION_META; ?>"), $);
				$("#_blank<?php echo JR_JOB_DURATION_META; ?>_start_date").closest('tr').hide();
				$("#_blank_js<?php echo JR_JOB_DURATION_META; ?>_start_date").closest('tr').hide();

				createExpireHandler($("#<?php echo JR_ITEM_FEATURED_LISTINGS; ?>"), $("#<?php echo JR_ITEM_FEATURED_LISTINGS; ?>_duration"), $("#<?php echo JR_ITEM_FEATURED_LISTINGS; ?>_start_date"), $("#_blank_js<?php echo JR_ITEM_FEATURED_LISTINGS; ?>_start_date"), $("#_blank<?php echo JR_ITEM_FEATURED_LISTINGS; ?>_start_date"), $("#_blank_expire<?php echo JR_ITEM_FEATURED_LISTINGS; ?>"), $);
				$("#_blank<?php echo JR_ITEM_FEATURED_LISTINGS; ?>_start_date").datepicker({
					dateFormat: "<?php echo $ui_display_format; ?>",
					altField: "#<?php echo JR_ITEM_FEATURED_LISTINGS; ?>_start_date",
					altFormat: "mm/dd/yy"
				});

				$("#<?php echo JR_ITEM_FEATURED_LISTINGS; ?>_start_date").closest('tr').hide();
				$("#_blank_js<?php echo JR_ITEM_FEATURED_LISTINGS; ?>_start_date").closest('tr').hide();

				createExpireHandler($("#<?php echo JR_ITEM_FEATURED_CAT; ?>"), $("#<?php echo JR_ITEM_FEATURED_CAT; ?>_duration"), $("#<?php echo JR_ITEM_FEATURED_CAT; ?>_start_date"), $("#_blank_js<?php echo JR_ITEM_FEATURED_CAT; ?>_start_date"), $("#_blank<?php echo JR_ITEM_FEATURED_CAT; ?>_start_date"), $("#_blank_expire<?php echo JR_ITEM_FEATURED_CAT; ?>"), $);
				$("#_blank<?php echo JR_ITEM_FEATURED_CAT; ?>_start_date").datepicker({
					dateFormat: "<?php echo $ui_display_format; ?>",
					altField: "#<?php echo JR_ITEM_FEATURED_CAT; ?>_start_date",
					altFormat: "mm/dd/yy"
				});

				$("#<?php echo JR_ITEM_FEATURED_CAT; ?>_start_date").closest('tr').hide();
				$("#_blank_js<?php echo JR_ITEM_FEATURED_CAT; ?>_start_date").closest('tr').hide();
			});

			function createExpireHandler(enableBox, durationBox, startDateBox, startDateU, startDateDisplayBox, textBox, $) {

				$(enableBox).change(function () {
					if ($(this).attr("checked") == "checked" && $(startDateBox).val() == "") {
						$(startDateDisplayBox).val(dateToString(new Date));
						$(startDateBox).val(dateToStdString(new Date));
						$(durationBox).val('0');
					} else {
						$(startDateBox).val('');
						$(startDateDisplayBox).val('');
						$(durationBox).val('');
						$(textBox).val('');
					}
				});

				var checker = function () {
					var string = "";
					if (enableBox === undefined) {
						string = get_expiration_time();
					}
					else if ($(enableBox).attr('checked') !== undefined) {
						string = get_expiration_time();
					}
					update(string);
				}

				var get_expiration_time = function () {

					var startDate = $(startDateU).val() * 1000;
					if (startDate == "") {
						startDate = new Date().getTime();
					}

					var duration = $(durationBox).val();
					if (duration == "") {
						return "";
					}

					return getDateString(parseInt(duration, 10), startDate);
				}

				var getDateString = function (duration, start_date) {
					if (isNaN(duration))
						return "";

					if (duration === 0)
						return "<?php _e( 'Never', APP_TD ); ?>";

					var _duration = parseInt(duration) * 24 * 60 * 60 * 1000;
					var _expire_time = parseInt(start_date) + parseInt(_duration);
					var expireTime = new Date(_expire_time);

					return dateToString(expireTime);
				}

				var update = function (string) {
					if (string != $(textBox).val()) {
						$(textBox).val(string);
					}
				}

				var dateToStdString = function (date) {
					return (date.getMonth() + 1) + "/" + date.getDate() + "/" + date.getFullYear();
				}

				var dateToString = function (date) {
		<?php
		$date_format = get_option( 'date_format', 'm/d/Y' );

		switch ( $date_format ) {
			case "d/m/Y":
			case "j/n/Y":
				$js_date_format = 'date.getDate() + "/" + ( date.getMonth() + 1 ) + "/" + date.getFullYear()';
				break;
			case "Y/m/d":
			case "Y/n/j":
				$js_date_format = 'date.getFullYear() + "/" + ( date.getMonth() + 1 ) + "/" + date.getDate()';
				break;
			case "m/d/Y":
			case "n/j/Y":
			default:
				$js_date_format = '( date.getMonth() + 1 )+ "/" + date.getDate() + "/" + date.getFullYear()';
				break;
		}
		?>
					return <?php echo $js_date_format; ?>;
				}

				setInterval(checker, 10);
			}
		</script>
		<p><?php _e( 'These settings allow you to override the defaults that have been applied to the job listings based on the plan the owner chose. They will apply until the job listing expires.', APP_TD ); ?>
		<p><?php _e( 'Settings are automatically filled and can only be changed after the job is published.', APP_TD ); ?></p>
		<?php
	}


	/**
	 * Returns an array of form fields.
	 *
	 * @return array
	 */
	public function form() {

		$output = array(
			array(
				'title' => __( 'Job Duration', APP_TD ),
				'type' => 'number',
				'name' => JR_JOB_DURATION_META,
				'desc' => __( 'days', APP_TD ),
				'extra' => array(
					'class' => 'small-text',
				),
			),
			array(
				'title' => __( 'Job Start Date', APP_TD ),
				'type' => 'text',
				'name' => '_blank' . JR_JOB_DURATION_META . '_start_date',
			),
			array(
				'title' => __( 'Job Start Date', APP_TD ),
				'type' => 'text',
				'name' => '_blank_js' . JR_JOB_DURATION_META . '_start_date',
			),
			array(
				'title' => __( 'Expires on', APP_TD ),
				'type' => 'text',
				'name' => '_blank',
				'extra' => array(
					'disabled' => 'disabled',
					'style' => 'background-color: #EEEEEF;',
					'id' => '_blank_expire' . JR_JOB_DURATION_META
				)
			)
		);

		foreach ( _jr_featured_addons() as $addon ) {

			$enabled = array(
				'title' => APP_Item_Registry::get_title( $addon ),
				'type' => 'checkbox',
				'name' => $addon,
				'desc' => __( 'Yes', APP_TD ),
				'extra' => array(
					'id' => $addon,
				)
			);

			$duration = array(
				'title' => __( 'Duration', APP_TD ),
				'desc' => __( 'days (0 = Infinite)', APP_TD ),
				'type' => 'number',
				'name' => $addon . '_duration',
				'extra' => array(
					'class' => 'small-text',
				),
			);

			$start = array(
				'title' => __( 'Start Date', APP_TD ),
				'type' => 'text',
				'name' => $addon . '_start_date',
			);

			$start_display = array(
				'title' => __( 'Start Date', APP_TD ),
				'type' => 'text',
				'name' => '_blank' . $addon . '_start_date',
			);

			$start_js = array(
				'title' => __( 'Start Date', APP_TD ),
				'type' => 'text',
				'name' => '_blank_js' . $addon . '_start_date',
			);

			$expires = array(
				'title' => __( 'Expires on', APP_TD ),
				'type' => 'text',
				'name' => '_blank',
				'extra' => array(
					'disabled' => 'disabled',
					'style' => 'background-color: #EEEEEF;',
					'id' => '_blank_expire' . $addon,
				)
			);

			$output = array_merge( $output, array( $enabled, $duration, $start, $start_display, $start_js, $expires ) );
		}

		return $output;
	}

	/**
	 * Filter data before save.
	 *
	 * @param array $post_data
	 * @param int $post_id
	 *
	 * @return array
	 */
	protected function before_save( $data, $post_id ) {
		global $jr_options;

		unset( $data['_blank' . JR_JOB_DURATION_META . '_start_date'] );
		unset( $data['_blank_js' . JR_JOB_DURATION_META . '_start_date'] );
		unset( $data['_blank'] );

		foreach ( _jr_featured_addons() as $addon ) {

			unset( $data['_blank' . $addon . '_start_date'] );
			unset( $data['_blank_js' . $addon . '_start_date'] );

			if ( $data[ $addon . '_start_date'] ) {
				$data[ $addon . '_start_date'] = date( 'Y-m-d H:i:s', strtotime( $data[ $addon . '_start_date'] ) );
			}

			if ( $data[ $addon ] ) {

				if ( $data[ $addon . '_duration'] !== '0' && empty( $data[ $addon . '_duration'] ) ) {
					$data[ $addon . '_duration'] = $jr_options->addons[ $addon ]['duration'];
				}

				if ( empty( $data[ $addon . '_start_date'] ) ) {
					$data[ $addon . '_start_date'] = current_time( 'mysql' );
				}
			}
		}

		return $data;
	}

}


/**
 * Job Moderation Queue Metabox.
 */
class JR_Job_Publish_Moderation extends APP_Meta_Box {

	/**
	 * Sets up metabox.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct( 'job-listing-publish-moderation', __( 'Moderation Queue', APP_TD ), APP_POST_TYPE, 'side', 'high' );

		add_action( 'admin_init', array( $this, 'cancel_job' ) );

		if ( isset( $_GET['rejected'] ) ) {
			add_action( 'admin_notices', array( $this, 'rejected_job_notice' ) );
		}
	}

	/**
	 * Additional checks before registering the metabox.
	 *
	 * @return bool
	 */
	public function condition() {
		return ( is_admin() && isset( $_GET['post'] ) && get_post_status( $_GET['post'] ) == 'pending' );
	}

	/**
	 * Displays content.
	 *
	 * @param object $post
	 *
	 * @return void
	 */
	public function display( $post ) {

		echo html( 'p', array(), __( 'You must approve this job before it can be published.', APP_TD ) );

		echo html( 'input', array(
			'type' => 'submit',
			'class' => 'button-primary',
			'value' => __( 'Accept', APP_TD ),
			'name' => 'publish',
			'style' => 'padding-left: 30px; padding-right: 30px; margin-right: 20px; margin-left: 15px;',
		) );

		echo html( 'a', array(
			'class' => 'button',
			'style' => 'padding-left: 30px; padding-right: 30px;',
			'href' => $this->get_edit_post_link( $post->ID, 'display', array( 'rejected' => 1 ) ),
				), __( 'Reject', APP_TD ) );

		echo html( 'p', array(
			'class' => 'howto'
				), __( 'Rejecting a job will cancel it and mark it as \'Expired\'. The author will be notified by email.', APP_TD ) );
	}

	function get_edit_post_link( $post_id, $context, $vars ) {
		$link = get_edit_post_link( $post_id, $context );

		if ( !empty( $vars ) && is_array( $vars ) ) {
			$context_and = 'display' == $context ? '&amp;' : '&';
			foreach ( $vars as $k => $v ) {
				$link .= $context_and . $k . '=' . $v;
			}
		}

		return $link;
	}

	public function cancel_job() {

		if ( ! isset( $_GET['rejected'] ) || !isset( $_GET['post'] ) ) {
			return;
		}

		if ( ! empty( $_GET['rejected'] ) ) {
			_jr_end_job( intval( $_GET['post'] ), $cancel = true );
		}
	}

	function rejected_job_notice() {
		echo scb_admin_notice( __( 'This job was canceled.', APP_TD ) );
	}

}


### Helper Functions

function jr_display_location_meta_box() {
	global $post;

	$key = 'job_meta';

	jr_geolocation_scripts();
	?>
	<div class="">
	<?php wp_nonce_field( plugin_basename( __FILE__ ), $key . '_wpnonce', false, true ); ?>

		<p><?php _e( 'Leave blank if the location of the applicant does not matter e.g. the job involves working from home.', APP_TD ); ?></p>

		<div id="geolocation_box">

			<?php
				$jr_geo_latitude = get_post_meta( $post->ID, '_jr_geo_latitude', true );
				$jr_geo_longitude = get_post_meta( $post->ID, '_jr_geo_longitude', true );

				if ( $jr_geo_latitude && $jr_geo_longitude ) :
					$jr_address = get_post_meta( $post->ID, 'geo_address', true );
					$jr_geo_country = get_post_meta( $post->ID, 'geo_country', true );
					$jr_geo_short_address = get_post_meta( $post->ID, 'geo_short_address', true );
					$jr_geo_short_address_country = get_post_meta( $post->ID, 'geo_short_address_country', true );
				else :
					$jr_address = 'Anywhere';
					$jr_geo_latitude = '';
					$jr_geo_longitude = '';
					$jr_geo_country = '';
					$jr_geo_short_address = '';
					$jr_geo_short_address_country = '';
				endif;
			?>

			<div>
				<input type="text" class="text" name="jr_address" id="geolocation-address" style="width: 180px;" autocomplete="off" value="" /><label><input id="geolocation-load" type="button" class="button geolocationadd" value="<?php esc_attr_e( 'Find', APP_TD ); ?>" /></label>
				<input type="hidden" class="text" name="jr_geo_latitude" id="geolocation-latitude" value="<?php echo esc_attr( $jr_geo_latitude ); ?>" />
				<input type="hidden" class="text" name="jr_geo_longitude" id="geolocation-longitude" value="<?php echo esc_attr( $jr_geo_longitude ); ?>" />
				<input type="hidden" class="text" name="jr_geo_country" id="geolocation-country" value="<?php echo esc_attr( $jr_geo_country ); ?>" />
				<input type="hidden" class="text" name="jr_geo_short_address" id="geolocation-short-address" value="<?php echo esc_attr( $jr_geo_short_address ); ?>" />
				<input type="hidden" class="text" name="jr_geo_short_address_country" id="geolocation-short-address-country" value="<?php echo esc_attr( $jr_geo_short_address_country ); ?>" />
			</div>

			<div id="map_wrap" style="margin-top:5px; border:solid 2px #ddd;"><div id="geolocation-map" style="width:100%;height:200px;"></div></div>

		</div>

		<p><strong><?php _e( 'Current location:', APP_TD ); ?></strong><br/><?php echo $jr_address; ?><?php
		if ( $jr_geo_latitude && $jr_geo_longitude ) {
			echo '<br/><em>Latitude:</em> ' . $jr_geo_latitude;
			echo '<br/><em>Longitude:</em> ' . $jr_geo_longitude;
		}
		?></p>
	</div>
	<?php
}
