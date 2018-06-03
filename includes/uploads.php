<?php

define( 'JR_ATTACHMENT_FILE', 'file' );
define( 'JR_ATTACHMENT_GALLERY', 'gallery' );

define( 'JR_MAX_FEATURED', 5 );
define( 'JR_MAX_IMAGES', 1 );

add_filter( 'jr_render_form_field' , 'jr_display_allowed_extensions', 10, 4 );
add_filter( 'jr_render_form_field', 'jr_hide_file_upload', 10, 3 );

/**
 * Displays the allowed extensions for a specific custom form field
 *
 * @param string  $html			The HTML code to be displayed
 * @param array   $field		The field being displayed
 * @param int  	  $listing_id	The listing ID
 * @param int	  $cat			(optional) The category ID related to the field
 *
 */
function jr_display_allowed_extensions( $html, $field, $listing_id, $cat = '' ) {

	if ( isset( $field['type'] ) && 'file' == $field['type']  ) {

		$field_html = jr_wrap_custom_fields( $field, $listing_id, $field['desc'] );
		$field['desc'] = '';
		$html  = html( 'div class="form-field file-field"', $field_html );
		$html .= html( 'div', array(
			'class' => 'field-info',
		), sprintf( __( 'Allowed extensions: %1$s', APP_TD ), $field['extensions'] ) );

	}
	return $html;
}

/**
 * Limit file uploads on a custom form field by hidding/clearing the HTML output
 *
 * @param string  $html			The field HTML output
 * @param array	  $field		The field associative array
 * @param int     $listing_id	The listing ID
 *
 * @return string Returns the original or modified field HTML ouput
 */
function jr_hide_file_upload( $html, $field, $listing_id ) {
	$limit = 1;
	if ( 'file' == $field['type'] ) {
		if ( jr_field_uploads( $listing_id, $field ) >= $limit )
			$html = '';
	}
	return $html;
}

/**
 * Retrieves the valid mime types for a file or gallery upload
 *
 * @param array   $file				The file being uploaded
 * @param array	  $valid_extensions	(optional) Valid file extensions
 *
 * @return array  Returns a list of valid mime types
 */
function jr_valid_mime_types( $file, $valid_extensions = array() ) {

	if ( ! is_array( $valid_extensions ) ) $valid_extensions = explode( ',', $valid_extensions );

	if ( empty ( $valid_extensions) ) {

		// limit gallery uploads to image mime types
		$mimes = array (
			'jpg|jpeg|jpe' 	=> 'image/jpeg',
			'gif' 			=> 'image/gif',
			'png' 			=> 'image/png',
			'bmp' 			=> 'image/bmp',
			'tif|tiff' 		=> 'image/tiff',
			'ico' 			=> 'image/x-icon',
		);

	} else {

		// use the extension and mime type from the uploaded file, if valid
		if  ( in_array( $file['extension'], $valid_extensions ) )
			$mimes = array( $file['extension'] => $file['type'] );
		else
			$mimes = array( 'invalid' => 'invalid' );

	}

	return $mimes;
}

/**
 * Queries the database for attachments from custom fields or gallery
 * Uses the meta key '_jr_attachment_type' to filter the available attachment types: gallery | file
 *
 * @param int  	  $listing_id	The listing ID
 * @param int	  $how_many		(optional) The number of attachments to retrieve
 * @param string  $type			(optional) The type of attachment to return (gallery|file)
 * @param string  $fields		(optional) The fields to be returned
 *
 */
function jr_get_listing_attachments( $listing_id, $how_many = -1, $type = JR_ATTACHMENT_GALLERY, $fields = '' ) {
	if ( ! $listing_id ) {
		return array();
	}

	return get_posts( array(
		'post_type' 			 => 'attachment',
		'post_status' 			 => 'inherit',
		'post_parent' 			 => $listing_id,
		'posts_per_page' 		 => $how_many,
		'update_post_term_cache' => false,
		'orderby' 				 => 'menu_order',
		'order' 				 => 'asc',
		'meta_key' 				 => '_jr_attachment_type',
		'meta_value' 			 => $type,
		'fields'				 => $fields
	) );
}

/**
 * Collects and returns the meta info for a specific attachment ID
 *
 * @param int  	  $attachment_id  The attachment ID
 *
 * @return array  Returns the attachment meta
 */
function jr_get_file_meta( $attachment_id ) {
	$filename = wp_get_attachment_url( $attachment_id );

	$title = trim( strip_tags( get_the_title( $attachment_id ) ) );
	$basename = basename( $filename );
	$size = size_format( filesize( get_attached_file( $attachment_id ) ), 2 );

	$meta = array (
		'title' 	=> ( ! $title ? $basename : $title ),
		'url' 		=> $filename,
		'mime_type' => get_post_mime_type( $attachment_id ),
		'size' 		=> $size,
	);
	return $meta;
}

/**
 * Returns the file link for a specific attachment ID
 *
 * @param int  	  $attachment_id  The attachment ID
 *
 * @return string Returns the HTML link
 */
function jr_get_file_link( $attachment_id ) {

	$file = jr_get_file_meta( $attachment_id );

	$link = html( 'a', array(
		'href' => $file['url'],
		'title' => $file['title'],
	), $file['title'] );

	return html( 'div', array(
		'class' => 'file-extension ' . jr_get_mime_type_icon( $file['mime_type'] ),
	), $link );
}

/**
 * Reads $_FILES data to separate custom form fields uploads from gallery uploads
 *
 * @param array   $listing_cat The listing category ID
 *
 * @return array  Returns the list of custom form file fields in $_FILES
 */
function jr_get_custom_form_file_fields( $listing_cat, $taxonomy ) {

	$file_fields = array();

	$fields = jr_get_custom_fields_for_cat( $listing_cat, $taxonomy );

	if ( ! empty( $fields ) ) {

		foreach ( $fields as $field ) {
			if ( 'file' == $field['type'] && isset( $_FILES[ $field['name'] ] ) && ! $_FILES[ $field['name'] ]['error'] ) {
				$file_fields[ $field['name'] ] = $field;
			}
		}

	}

	return $file_fields;
}

/**
 * Returns the related icon filename for a mime-type
 *
 * @param string  $mime_type
 *
 * @return string  Returns the mime type icon file name
 */
function jr_get_mime_type_icon( $mime_type ) {

	if ( ! $mime_type ) $mime_type = 'generic';

	$file_ext_ico = array (
		'pdf'  	  	   => 'file-pdf',
		'msword'  	   => 'file-word',
		'vnd.ms-excel' => 'file-excel',
		'csv' 		   => 'file-excel',
		'image'		   => 'file-image',
		'other'	   	   => 'file-other',
	);

	$mime_type = explode( '/' , $mime_type );
	if ( is_array( $mime_type ) )
		// simplify the mime match for image types by using the 'image' part (i.e: image/png, image/jpg, etc)
		$mime_type = ( 'image' == $mime_type[0] ? $mime_type[0] : $mime_type[1] );

	if ( ! isset( $file_ext_ico[ $mime_type ] ) ) $mime_type = 'other';

	return apply_filters( 'jr_mime_type_icon', $file_ext_ico[ $mime_type ], $mime_type );
}

/**
 * Updates the listing featured image after adding/deleting an gallery image
 *
 * @param int     $listing_id	The listing ID
 * @param int 	  $attach_id	(optional) The attachment ID
 * @param string  $type			(optional) The attachment type (gallery|file)
 *
 */
function jr_update_featured_image( $listing_id, $attach_id = '', $type = VA_ATTACHMENT_GALLERY ) {

	$has_feat_image = (bool) get_post_thumbnail_id( $listing_id );

	// an attachment was deleted, get the next featured image candidate
	if ( ! $attach_id && ! $has_feat_image ) {
		$gallery = jr_get_listing_attachments( $listing_id, -1, $type, 'ids' );
		$featured_id = array_shift ( $gallery );
	// an attachment was uploaded
	} elseif ( $attach_id ) {
		$featured_id = $attach_id;
	}

	// if user is uploading a gallery attachment, set the first available gallery image as the featured image
	if ( JR_ATTACHMENT_GALLERY == $type && isset( $featured_id ) && ! $has_feat_image ) {
		set_post_thumbnail( $listing_id, $featured_id );
	}
}

/**
 * Deletes $_POST data files
 *
 * @param int  $listing_id  The listing ID
 *
 * @return array The deleted files list
 */
function jr_handle_delete_files( $listing_id ) {

	$deleted_files = array();
	if ( isset( $_POST['files_to_delete'] ) ) {
		array_walk( $_POST['files_to_delete'], 'wp_delete_attachment' );
		$deleted_files = $_POST['files_to_delete'];

		jr_update_featured_image( $listing_id );
	}

	return $deleted_files;
}

/**
 * Updates $_POST data file descriptions
 *
 * @param array  $files_to_skip	(optional) List of files to skip the update
 */
function jr_handle_file_descriptions( $files_to_skip = array() ) {

	if ( isset( $_POST['file_descriptions'] ) ) {

		foreach ( $_POST['file_descriptions'] as $file_id => $desc ) {
			if  ( in_array( $file_id, $files_to_skip ) )
				continue;

			$mime_type = get_post_mime_type( $file_id );
			$type = explode( '/', $mime_type );

			$value = strip_tags( stripslashes( $desc ) );

			// update the attachment
			$post = array();
			$post['ID'] = $file_id;
			$post['post_title'] = $value;

			// update the post in the database
			wp_update_post( $post );

			// update additional meta for images
			if ( 'image' == $type[0] ) {
				update_post_meta( $file_id, '_wp_attachment_image_alt', $value );
			}

		}

	}

}

function jr_company_logo_upload_dir( $pathdata ) {
	$subdir = '/company_logos'.$pathdata['subdir'];
 	$pathdata['path'] = str_replace($pathdata['subdir'], $subdir, $pathdata['path']);
 	$pathdata['url'] = str_replace($pathdata['subdir'], $subdir, $pathdata['url']);
	$pathdata['subdir'] = str_replace($pathdata['subdir'], $subdir, $pathdata['subdir']);
	return $pathdata;
}


/**
 * Returns size for different file types and dinamically applies filters to allow changing sizes
 *
 * @param string $type Valid types are: 'company_logo','resumes', CONST JR_ATTACHMENT_FILE, CONST JR_ATTACHMENT_GALLERY
 *
 * @return array The file size array
 */
function jr_get_file_size( $type ) {

	// default size 3MB
	$default_sizes = array (
		'size' => 3,
		'unit' => 'MB',
		'unit_size' => 1048576
	);
	return apply_filters( 'jr_upload_file_size', $default_sizes, $type );

}

/**
 * Handles uploaded files
 *
 * @param int  	  $listing_id	The listing ID
 * @param int	  $listing_cat	The listing category ID
 *
 */
function jr_handle_image_upload( $listing_id, $file_key = 'company-logo' ) {
	require_once ABSPATH . '/wp-admin/includes/file.php';
	require_once ABSPATH . '/wp-admin/includes/image.php';
	require_once ABSPATH . '/wp-admin/includes/media.php';

	if ( empty($_FILES[ $file_key ]['size']) ) {
		return;
	}

	if ( 'company-logo' == $file_key ) {
		add_filter( 'upload_dir', 'jr_company_logo_upload_dir' );
	}

	$allowed = array( 'png', 'gif', 'jpg','jpeg' );

	$errors = jr_get_listing_error_obj();

	// if the job alredy contains a company logo, delete it
	$post_thumbnail_id = get_post_thumbnail_id( $listing_id );

	// iterate through the uploaded files (gallery/files)
	foreach ( $_FILES as $key => $file ) {

		if ( $file_key != $key || ! $file['name'] ) {
			continue;
		}

		$file['extension'] = pathinfo( $file['name'], PATHINFO_EXTENSION );
		$valid_mimes = jr_valid_mime_types( $file,  $allowed );

		$file_id = media_handle_upload( $key, $listing_id, $post_data = array(), array( 'test_form' => false, 'mimes' => $valid_mimes ) );
		if ( is_wp_error( $file_id ) ) {
			$errors->add( $file_id->get_error_code(), $file_id->get_error_message() .  ' :: ' . $file['name'] . ' :: ' );
			$errors->add( 'upload_mime_warning', sprintf( __( 'Please make sure you are uploading a valid image file type.', APP_TD ) ) );
			continue;
		}

		$image_size = jr_get_file_size( $file_key );

		if ( $file['size'] > ( $image_size['size'] * $image_size['unit_size'] ) ) {
			$errors->add( 'upload_size_warning', sprintf( __( 'File exceeds %d%s size limit.', APP_TD ), ($image_size['size'] * $image_size['unit_size']) / $image_size['unit_size'], $image_size['unit'] ) );
			continue;
		}

		update_post_meta( $listing_id, '_thumbnail_id', $file_id );
		update_post_meta( $file_id, '_jr_attachment_type', JR_ATTACHMENT_GALLERY );

	}

	if ( ! $errors->get_error_codes() ) {
		wp_delete_attachment( $post_thumbnail_id );
		set_post_thumbnail( $listing_id, $file_id );
	}

	if ( 'company-logo' == $file_key ) {
		remove_filter( 'upload_dir', 'company_logo_upload_dir' );
	}
}

/**
 * Handles uploaded files
 *
 * @param int  	  $listing_id	The listing ID
 * @param int	  $listing_cat	The listing category ID
 *
 */
function jr_handle_files( $listing_id, $listing_cat, $taxonomy = APP_TAX_CAT, $skip_check = 'company-logo' ) {
	require_once ABSPATH . '/wp-admin/includes/file.php';
	require_once ABSPATH . '/wp-admin/includes/image.php';
	require_once ABSPATH . '/wp-admin/includes/media.php';

	if ( ! isset( $_FILES ) ) {
		return;
	}

	$errors = jr_get_listing_error_obj();

	$deleted_files = jr_handle_delete_files( $listing_id );
	jr_handle_file_descriptions( $files_to_skip = $deleted_files );

	// get the custom form file fields
	$cust_form_fields = jr_get_custom_form_file_fields( $listing_cat, $taxonomy );

	$files = jr_get_listing_attachments( $listing_id, -1, JR_ATTACHMENT_FILE, 'ids' );
	$gallery = jr_get_listing_attachments( $listing_id );

	$count_gallery = count( $gallery );
	$count_files = count( $files );
	$menu_order = $count_files + $count_gallery;

	$attachments = array (
		JR_ATTACHMENT_GALLERY => array (
			'text'    	=> __( 'gallery', APP_TD),
			'current' 	=> $count_gallery,
			'limit'	  	=> JR_MAX_IMAGES,
			'left' 	  	=> JR_MAX_IMAGES - $count_gallery,
			'valid_ext' => array(),
		),
	);

	// iterate through the uploaded files (gallery/files)
	foreach ( $_FILES as $key => $file ) {

		if ( $skip_check == $key || ! $file['name'] ) {
			continue;
		}

		// check for a file OR gallery upload
		if ( isset( $cust_form_fields[ $key ] ) ) {
			$type = JR_ATTACHMENT_FILE;
		} else {
			$type = JR_ATTACHMENT_GALLERY;
		}

		if ( JR_ATTACHMENT_FILE == $type ) {
			$field_uploads = jr_field_uploads( $listing_id, $cust_form_fields[$key], $files );
			$attachments[ JR_ATTACHMENT_FILE ] = array (
				'text'      => __( 'attachment', APP_TD),
				'limit'	    => 1,
				'current'   => $field_uploads,
				'left' 	    => 1 - $field_uploads,
				'valid_ext' => $cust_form_fields[$key]['extensions'],
			);
		}

		// skip the image/file if there's no available slots
		if ( ! $attachments[$type]['left'] ) {
			$errors->add( 'upload_limit',
				sprintf( __('Maximum %1$s uploads reached [max: %2$s]', APP_TD), $attachments[$type]['text'], $attachments[$type]['limit'] )
			);
			continue;
		}

		$file['extension'] = pathinfo( $file['name'], PATHINFO_EXTENSION );
		$valid_mimes = jr_valid_mime_types( $file,  $attachments[$type]['valid_ext'] );

		$file_id = media_handle_upload( $key, $listing_id, $post_data = array(), array( 'test_form' => false, 'mimes' => $valid_mimes ) );
		if ( is_wp_error( $file_id ) ) {
			$errors->add( $file_id->get_error_code(), $file_id->get_error_message() .  ' :: ' . $file['name'] . ' :: ' );
			$errors->add( 'upload_mime_warning', sprintf( __( 'Please make sure you are uploading a valid %1$s file type.', APP_TD ), $attachments[$type]['text'] ) );
			continue;
		}

		$file_size = jr_get_file_size( $type );

		if ( $file['size'] > ( $file_size['size'] * $file_size['unit_size'] ) ) {
			$errors->add( 'upload_size_warning', sprintf( __( 'File exceeds %d%s size limit.', APP_TD ), ($file_size['size'] * $file_size['unit_size']) / $file_size['unit_size'], $file_size['unit'] ) );
			continue;
		}

		if ( JR_ATTACHMENT_FILE == $type ) {
			update_post_meta( $file_id, '_jr_field_' . $cust_form_fields[$key]['name'], 1 );
		} else {
			jr_update_featured_image( $listing_id, $file_id, $type );
		}

		if ( ! empty( $cust_form_fields[ $key ] ) ) {
			update_post_meta( $file_id, '_jr_field_data', $cust_form_fields[ $key ] );
		}

		set_post_field( 'menu_order', $menu_order, $file_id );

		update_post_meta( $file_id, '_jr_attachment_type', $type );
		$attachments[$type]['left']--;
	}

}

/**
 * Retrieves the number of uploads for a listing file field
 *
 * @param int     $listing_id		The listing ID
 * @param array	  $field			The field associative array
 * @param array	  $attacments		(optional) The listing attachments
 *
 * @return int     Returns the total uploads for a file field
 */
function jr_field_uploads( $listing_id, $field, $attachments = array() ) {

	$total = 0;
	if ( empty ( $attachments ) ) {
		$attachments = jr_get_listing_attachments( $listing_id, -1, JR_ATTACHMENT_FILE, 'ids' );
	}

	foreach ( $attachments as $attach_id ) {
		$total = get_post_meta( $attach_id, '_jr_field_' .$field['name'], true );
		if ( $total ) break;
	}
	return $total;
}

/**
 * Echoes the listing file editor
 *
 * @param int $listing_id  The listing ID
 *
 */
function the_listing_files_editor( $listing_id ) {
	$attachments = jr_get_listing_attachments( $listing_id, $limit = -1, JR_ATTACHMENT_FILE );

	if ( ! $attachments ) {
		return;
	}

	echo '<div class="form-field">';

	echo '<strong>' . __( 'Uploaded Files', APP_TD ) . '</strong>';

	echo '<ul class="uploaded file-editor">';

	foreach ( $attachments as $attachment ) :
		$meta = wp_get_attachment_metadata( $attachment->ID );

		$file = jr_get_file_meta( $attachment->ID );

		$field_data = get_post_meta( $attachment->ID, '_jr_field_data', true );

		$label = '';

		// the uploaded file label
		if ( ! empty( $field_data['desc'] ) ) {
			$label = $field_data['desc'];
		}
		ob_start();
?>
		<li>
			<p class="file-delete"><label><input class="checkbox" type="checkbox" name="files_to_delete[]" value="<?php echo $attachment->ID; ?>">&nbsp;<?php _e( 'Delete File', APP_TD ); ?></label></p>

			<?php if ( $label ): ?>
				<p class="file-upload-label"><strong><?php echo $label; ?></strong></p>
			<?php endif; ?>

			<p class="file-upload-file"><?php echo jr_get_file_link( $attachment->ID ); ?></p>

			<p class="file-meta"><strong><?php _e('File Info:', APP_TD) ?></strong> <?php echo $file['size']; ?> <?php echo $attachment->post_mime_type; ?></p>

			<p class="file-title">
				<label><?php _e( 'Description:', APP_TD ); ?>
					<input type="text" class="text" name="file_descriptions[<?php echo $attachment->ID; ?>]" value="<?php echo esc_attr( $file['title'] ); ?>" />
				</label>
			</p>

		</li>
<?php
		$file_edit_html = ob_get_contents();

		ob_end_clean();

		echo apply_filters( 'jr_uploaded_file_edit_html', $file_edit_html, $attachment, $file, $field_data );

	endforeach;

	echo '</ul>';

	echo '</div>';
}
