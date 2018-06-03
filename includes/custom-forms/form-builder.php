<?php

define( 'APP_FORMS_PTYPE', 'custom-form' );

add_action( 'init', array( 'APP_Form_Builder', 'init' ) );

class APP_Form_Builder {

	public static function init () {
		if ( self::get_args( 'register_post_type' ) ) {
			self::register_post_type();
			add_action( 'save_post', array( 'APP_Form_Builder', 'save_fields' ) );
			add_filter( 'post_updated_messages', array( 'APP_Form_Builder', 'form_updated_messages' ) );
			add_action( 'parent_file', array( 'APP_Form_Builder', 'forms_tax_menu_fix' ), 99 );
		}
	}

	protected static function get_args( $option = '' ) {

		static $original = array();
		static $generated = array();

		$args_sets = get_theme_support( 'app-form-builder' );

		if ( $original != $args_sets ) {

			// numeric array, contains multiple sets of arguments
			// first item contains preferable set
			$original = $args_sets;

			if ( ! is_array( $args_sets ) ) {
				$args_sets = array();
			}

			$args = array();
			foreach ( $args_sets as $args_set ) {

				if ( ! isset( $args_set['register_post_type'] ) ) {
					$args_set['register_post_type'] = true;
				}

				foreach ( $args_set as $key => $arg ) {
					if ( ! isset( $args[ $key ] ) ) {
						$args[ $key ] = $arg;
					} elseif ( 'show_in_menu' === $key && $arg ) {
						$args[ $key ] = true;
					} elseif ( 'register_post_type' === $key && $arg ) {
						$args[ $key ] = true;
					}
				}
			}

			$defaults = array(
				'url'                => get_template_directory_uri() . '/includes/custom-forms/',
				'show_in_menu'       => false,
			);

			$generated = wp_parse_args( $args, $defaults );

		}

		if ( empty( $option ) ) {
			return $generated;
		} else {
			return $generated[ $option ];
		}
	}

	public static function register_post_type() {
		$labels = array(
			'name'               => __( 'Forms', APP_TD ),
			'singular_name'      => __( 'Form', APP_TD ),
			'add_new'            => __( 'Add New', APP_TD ),
			'add_new_item'       => __( 'Add New Form', APP_TD ),
			'edit_item'          => __( 'Edit Form', APP_TD ),
			'new_item'           => __( 'New Form', APP_TD ),
			'view_item'          => __( 'View Forms', APP_TD ),
			'search_items'       => __( 'Search Forms', APP_TD ),
			'not_found'          => __( 'No forms found', APP_TD ),
			'not_found_in_trash' => __( 'No forms found in Trash', APP_TD ),
			'menu_name'          => __( 'Forms', APP_TD )
		);

		$ptype_args = array(
			'labels'               => $labels,
			'supports'             => array( 'title' ),
			'register_meta_box_cb' => array( __CLASS__, 'register_meta_box' ),
			'show_in_menu'         => self::get_args( 'show_in_menu' ),
			'menu_icon'            => 'dashicons-feedback',
			'capability_type'      => 'page',
			'hierarchical'         => false,
			'show_ui'              => true,
			'show_in_nav_menus'    => false,
			'publicly_queryable'   => false,
			'exclude_from_search'  => true,
			'has_archive'          => false,
			'query_var'            => false,
			'can_export'           => true,
		);

		$ptype_args = apply_filters( 'appthemes_custom_forms_ptype_args', $ptype_args );

		register_post_type( APP_FORMS_PTYPE, $ptype_args );
	}

	public static function register_meta_box( $post ) {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ), 99 );
		add_action( 'admin_print_scripts', array( __CLASS__, 'print_templates' ), 99 );
		add_meta_box( 'app-form-builder', __( 'Form Builder', APP_TD ), array( __CLASS__, 'meta_box' ), APP_FORMS_PTYPE, 'normal', 'core' );
	}

	public static function print_templates() {
		require_once 'templates.php';
	}

	public static function enqueue_scripts() {

		$url = self::get_args( 'url' );
		wp_enqueue_script(
			'form-builder',
			$url. 'form-builder.js',
			array(
				'jquery',
				'jquery-ui-core',
				'jquery-ui-sortable',
				'jquery-color',
				'validate',
				'validate-lang',
				'underscore',
				'wp-util'
			),
			'20150704'
		);
		wp_enqueue_script(
			'form-builder-helper',
			$url . 'form-builder-helper.js',
			array( 'jquery' ),
			'20150505'
		);
		wp_localize_script(
			'form-builder',
			'l10n',
			array(
				'save'               => __( 'Save', APP_TD ),
				'add_new_field'      => __( 'Add New Field...', APP_TD ),
				'input_text'         => __( 'Text Field', APP_TD ),
				'title'              => __( 'Title', APP_TD ),
				'textarea'           => __( 'Text Area', APP_TD ),
				'checkbox'           => __( 'Checkboxes', APP_TD ),
				'radio'              => __( 'Radio', APP_TD ),
				'select'             => __( 'Select List', APP_TD ),
				'text_field'         => __( 'Text Field', APP_TD ),
				'tooltip'            => __( 'Tooltip', APP_TD ),
				'file'               => __( 'File Upload', APP_TD),
				'file_extensions'    => __( 'Allowed Extensions', APP_TD),
				'file_tip'           => __( '(comma separated. i.e: pdf, doc)', APP_TD),
				'label'              => __( 'Label', APP_TD ),
				'textarea_field'     => __( 'Text Area Field', APP_TD ),
				'select_options'     => __( 'Options', APP_TD ),
				'add'                => __( 'Add', APP_TD ),
				'checkbox_group'     => __( 'Checkbox Group', APP_TD ),
				'remove'             => __( 'Delete this field?', APP_TD ),
				'radio_group'        => __( 'Radio Group', APP_TD ),
				'selections_message' => __( 'Allow Multiple Selections', APP_TD ),
				'expand_all'         => __( 'Expand All', APP_TD ),
				'required'           => __( 'Required', APP_TD ),
				'collapse_all'       => __( 'Collapse All', APP_TD ),
				'number'             => __( 'Number', APP_TD ),
			)
		);

		wp_enqueue_style( 'form-builder', $url . 'form-builder.css', array(), '20150505' );

	}

	protected static function get_form ( $post_id = '' ) {
		if ( ! $form = get_post_meta( $post_id, 'va_form', true ) ) {
			$form = array();
		}

		// check if form has old data scheme
		if ( ! empty( $form ) && isset( $form[0]['cssClass'] ) ) {
			$form = self::_upgrade_scheme( $form );
		}

		return $form;
	}

	// TODO: move to the theme to the upgrade procedure
	protected static function _upgrade_scheme( $form ) {
		foreach ( $form as &$field ) {
			$field['type'] = $field['cssClass'];
			$field['props'] = array();
			$field['props']['required'] = $field['required'] === 'checked' ? 1 : 0;

			if ( in_array( $field['type'], array( 'input_text', 'textarea', 'file' ) ) ) {
				$field['props']['label'] = $field['values'];

				if ( 'file' == $field['type'] ) {
					$field['props']['extensions'] = $field['extensions'];
					unset( $field['extensions'] );
				}

			} else {
				$field['props']['label'] = $field['title'];
				unset( $field['title'] );

				$field['props']['options'] = $field['values'];

				foreach ( $field['props']['options'] as &$value ) {
					$value['baseline'] = $value['baseline'] === 'checked' ? 1 : 0;
				}
			}

			unset( $field['cssClass'] );
			unset( $field['required'] );
			unset( $field['values'] );
		}

		return $form;
	}

	public static function meta_box( $post = null ) {

		$post_id = '';

		if ( isset( $post ) ) {
			$post_id = $post->ID;
		}

		$form = self::get_form( $post_id );

		self::display_form( $form );
	}

	public static function display_form( $form = '' ) {
?>
		<script type="text/javascript">
		jQuery( function($) {
			$('#app-form-builder-div').formbuilder();
			$(function() {
				$("#app-form-builder ul").sortable({ opacity: 0.6, cursor: 'move'});
			});
		});
		</script>

	<div id="app-form-builder-div">
		<input type="hidden" name="app_form" id="app-form-data" value='<?php echo esc_attr( json_encode( $form ) ); ?>' />
	</div>

<?php
	}


	/**
	 * Change default post updated messages
	 *
	 * @param array $messages
	 *
	 * @return array
	 */
	public static function form_updated_messages( $messages ) {
		global $post;

		$messages[ APP_FORMS_PTYPE ] = array(
			 1 => __( 'Form updated.', APP_TD ),
			 4 => __( 'Form updated.', APP_TD ),
			 5 => isset( $_GET['revision'] ) ? sprintf( __( 'Form restored to revision from %s', APP_TD ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			 6 => __( 'Form published.', APP_TD ),
			 7 => __( 'Form saved.', APP_TD ),
			 8 => __( 'Form submitted.', APP_TD ),
			 9 => sprintf( __( 'Form scheduled for: <strong>%1$s</strong>.', APP_TD ), date_i18n( __( 'M j, Y @ G:i', APP_TD ) ), strtotime( $post->post_date ) ),
			10 => __( 'Form draft updated.', APP_TD ),
		);

		return $messages;
	}

	static function make_id_unique( $id, $field_id, $fields, $unique_id = 1 ) {
		if ( empty( $id ) ) {
			$field_id = 'app_' . $unique_id;
		}

		if ( in_array( $field_id, $fields ) ) {

			if ( false === strpos( $field_id , '_' . $unique_id ) ) {
				$field_id .= '_' . $unique_id;
			} else {
				$unique_id_old = $unique_id;
				$unique_id++;
				$field_id = str_replace( '_' . $unique_id_old, '_' . $unique_id, $field_id );
			}

			return self::make_id_unique( $id, $field_id, $fields, $unique_id );
		}

		return $field_id;
	}

	public static function save_fields( $form_id ) {

		if ( ! isset( $_POST['app_form'] ) ) {
			return;
		}

		$form = self::parse_form( $_POST['app_form'] );

		update_post_meta( $form_id, 'va_form', $form );
	}

	public static function parse_form( $form = '' ) {

		$fields    = array();
		$ids       = array();
		$id        = array();
		$unique_id = 1;

		parse_str( $form, $fields );

		if ( isset( $fields['app_form'] ) ) {
			$fields = $fields['app_form'];
		}

		if ( ! is_array( $fields ) ) {
			$fields = array();
		}

		foreach ( $fields as &$field ) {
			$type  = $field['type'];
			$label = $field['props']['label'];

			if ( ! isset( $id[ $type ] ) ) {
				$id[ $type ] = 1;
			}

			// sanitize file extensions
			if ( 'file' === $type ) {
				$field['props']['extensions'] = preg_replace( '/[^A-Za-z0-9,]/', '', $field['props']['extensions'] );
			}

			if ( ! $label )	{
				$label = $type . '_' . $id[ $type ]++;
			}

			if ( ! $field['id'] ) {
				$unique_id = strtolower( $label );
				$unique_id = str_replace( ' ', '-', $unique_id );
				$unique_id = preg_replace( '/[^a-z0-9_\-]/', '', $unique_id );
				$field['id'] = 'app_' . $unique_id;
			} else {
				$unique_id = substr( $field['id'], 4 );
			}

			// avoid duplicate field ids
			$field['id'] = self::make_id_unique( $unique_id, $field['id'], $ids );
			$ids[] = $field['id'];
		}

		return $fields;
	}

	public static function prepare_fields( $form ) {

		if ( empty( $form ) ) {
			return array();
		}

		$fields = array();

		foreach ( $form as $field ) {

			$args = array(
				'name'  => $field['id'],
				'type'  => $field['type'],
				'props' => $field['props'],
			);

			if ( isset( $args['props']['tip'] ) ) {
				$args['tip'] = $args['props']['tip'];
				unset( $args['props']['tip'] );
			}

			if ( 'input_text' == $args['type'] ) {
				$args['type'] = 'text';
			}

			switch ( $args['type'] ) {
				case 'select':
				case 'radio':
				case 'checkbox':
					$values = array();
					$checked = array();

					foreach ( $args['props']['options'] as $option ) {
						$values[] = $option['value'];

						if ( $option['baseline'] ) {
							$checked[] = $option['value'];
						}
					}

					$args['values'] = $values;

					if ( 'checkbox' == $args['type'] ) {
						$args['default'] = $checked;
					} elseif ( ! empty( $checked[0] ) ) {
						$args['default'] = $checked[0];
					}

					break;

				case 'file':
					$args['extensions'] = $args['props']['extensions'];
					unset( $args['props']['extensions'] );
					break;

			}

			if ( ! empty( $field['props']['required'] ) ) {

				if ( ! isset( $args['extra']['class'] ) ) {
					$args['extra']['class'] = '';
				}

				$args['extra']['class'] = trim( $args['extra']['class'] . ' required' );
			}

			$args['desc'] = $args['props']['label'];
			$args['desc_pos'] = 'before';

			unset( $args['props']['label'] );

			$fields[] = $args;
		}

		return $fields;
	}

	public static function get_fields( $form_id, $supress_filters = false ) {
		$form = self::get_form( $form_id );
		$form = self::prepare_fields( $form );

		if ( ! $supress_filters ) {
			$form = apply_filters( 'appthemes_get_custom_form_fields', $form, $form_id );
		}

		return $form;
	}

	/**
	 * Remove taxonomies from custom forms single menu.
	 */
	public static function forms_tax_menu_fix( $parent_file ) {
		global $submenu;

		$taxonomies = get_object_taxonomies( APP_FORMS_PTYPE, 'objects' );

		foreach( $taxonomies as $taxonomy ) {
			if ( isset( $submenu['edit.php?post_type='.APP_FORMS_PTYPE] ) ) {
				foreach( $submenu['edit.php?post_type='.APP_FORMS_PTYPE] as $k => $submenu_item ) {
					if ( $submenu_item[0] == $taxonomy->labels->menu_name ) {
						unset( $submenu['edit.php?post_type='.APP_FORMS_PTYPE][$k] );
					}
				}
			}
		}
		return $parent_file;
	}

}

