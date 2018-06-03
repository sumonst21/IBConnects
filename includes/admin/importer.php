<?php
/**
 * JobRoller importer.
 * Handles the import functionality.
 *
 * @version 1.8
 * @package JobRoller\Importer
 * @author AppThemes
 * @url https://www.appthemes.com
 */


// Importer
add_action( 'wp_loaded', '_jr_csv_importer' );

class JR_Importer extends APP_Importer {

	function setup() {
		$this->textdomain = APP_TD;

		$post_type_obj = get_post_type_object( $this->post_type );

		$this->args = array(
			'page_title' => __( 'CSV ' . $post_type_obj->labels->name . ' Importer', APP_TD ),
			'menu_title' => __( $post_type_obj->labels->name . ' Importer', APP_TD ),
			'page_slug' => 'app-importer-' . $post_type_obj->name,
			'parent' => "edit.php?post_type=".$this->post_type,
			'screen_icon' => 'tools',
		);

		add_filter( 'appthemes_importer_import_row_data', array( $this, 'prevent_duplicate' ), 10, 1 );
		add_action( 'appthemes_after_import_upload_row', array( $this, 'example_csv_files' ) );
		add_action( 'appthemes_after_import_upload_row', array( $this, 'geocode_jobs' ) );
		add_action( 'appthemes_importer_import_row_after', array( __CLASS__, 'geocode_listing_on_import' ), 10, 2 );
	}

	/**
	 * Prevents duplicate entries while importing.
	 *
	 * @param array $data
	 *
	 * @return array|bool
	 */
	public function prevent_duplicate( $data ) {
		if ( ! empty( $data['post_meta']['jr_id'] ) ) {
			if ( $this->get_listing_by_ref( $data['post_meta']['jr_id'] ) ) {
				return false;
			}
		}
		return $data;
	}

	/**
	 * Inserts links to example CSV files into Importer page.
	 */
	public function example_csv_files() {
		$link1 = html( 'a', array( 'href' => get_template_directory_uri() . '/examples/jobs.csv', 'title' => __( 'Download CSV file', APP_TD ) ), __( 'Jobs', APP_TD ) );
		$link2 = html( 'a', array( 'href' => get_template_directory_uri() . '/examples/jobs-with-attachments.csv', 'title' => __( 'Download CSV file', APP_TD ) ), __( 'Jobs with attachments', APP_TD ) );

		$link = sprintf( __( 'Download example CSV files: %1$s, %2$s', APP_TD ), $link1, $link2 );
?>
		<tr>
			<th>
				<label><?php _e( 'Template', APP_TD ); ?></label>
			</th>
			<td>
				<p><?php echo $link; ?></p>
			</td>
		</tr>
<?php
	}

	public function geocode_jobs() {
?>
		<tr>
			<th>
				<label><?php _e( 'Geocoding', APP_TD ); ?></label>
			</th>
			<td>
				<p><input type="checkbox" name="geocode_imported" value="1" /><?php _e( 'Geocode jobs on import', APP_TD ); ?></p>
				<p class="description"><?php _e( 'Limit of 2,500 requests per 24 hours (unless you have a premium Google Maps API account).', APP_TD ); ?></p>
			</td>
		</tr>
<?php
	}

	/**
	 * Retrieves listing data by given reference ID.
	 *
	 * @param string $reference_id An listing reference ID.
	 *
	 * @return object|bool A listing object, boolean False otherwise.
	 */
   protected function get_listing_by_ref( $reference_id ) {

	   if ( empty( $reference_id ) || ! is_string( $reference_id ) ) {
		   return false;
	   }
	   $reference_id = appthemes_numbers_letters_only( $reference_id );

	   $ptypes = array( APP_POST_TYPE );

	   $listing_q = get_posts( array(
		   'post_status' => 'any',
		   'post_type' => $ptypes,
		   'meta_key' => 'jr_id',
		   'meta_value' => $reference_id,
		   'posts_per_page' => 1,
		   'suppress_filters' => true,
		   'no_found_rows' => true,
	   ) );

	   if ( empty( $listing_q ) ) {
		   return false;
	   }
	   return $listing_q[0];
   }

	/**
	 * Update geo data for imported jobs.
	 */
	public static function geocode_listing_on_import( $post_id, $row, $geocode = false ) {

		if ( APP_POST_TYPE != get_post_type( $post_id ) ) {
			return;
		}

		if ( ! empty( $row['lat'] ) && ! empty( $row['lng'] ) ) {
			return;
		}

		if ( empty( $row['location'] ) ) {
			return;
		}

		// dont do geocode but set a static location if exists
		if ( empty( $_POST['geocode_imported'] ) && ! $geocode ) {
			update_post_meta( $post_id, 'geo_address', $row['location'] );
			update_post_meta( $post_id, 'geo_short_address', $row['location'] );
			update_post_meta( $post_id, '_jr_address', $row['location'] );
			return;
		}

		$coord = jr_get_coordinates_by_location( $row['location'] );

		if ( ! empty( $coord['error_message'] ) ) {
			echo scb_admin_notice( "Warning: Geocoding failed with error <strong>". $coord['error_message'] . "</strong>", 'error' );
			return;
		}

		if ( ! empty( $coord['latitude'] ) && ! empty( $coord['longitude'] ) ) {
			jr_update_post_geo_metadata( $post_id, $data = array(), $coord['latitude'], $coord['longitude'] );
		}

	}

}

### Hook Callbacks

function _jr_csv_importer() {

	if ( ! is_admin()  ) {
		return;
	}

	$fields = array(
		'title'       => 'post_title',
		'description' => 'post_content',
		'author'      => 'post_author',
		'date'        => 'post_date',
		'slug'        => 'post_name',
		'status'      => 'post_status'
	);

	$args = array(
		'taxonomies' => array( APP_TAX_CAT, APP_TAX_TYPE, APP_TAX_SALARY, APP_TAX_TAG ),

		'custom_fields' => array(
			'id'				=> 'jr_id',
			'company'			=> array( 'internal_key' => '_Company', 'default' => '' ),
			'website'           => array( 'internal_key' => '_CompanyURL', 'default' => '' ),
			'how_to_apply'		=> array( 'internal_key' => '_how_to_apply', 'default' => '' ),
			'job_duration'		=> array( 'internal_key' => JR_JOB_DURATION_META, 'default' => 0 ),

			// geodata - @todo: move to APP_GEO
			'location'			=> array( 'internal_key' => '_jr_address', 'default' => '' ),
		),

		'geodata' => false,
		'attachments' => true
	);

	$args = apply_filters( 'jr_csv_importer_args', $args );

	$importer = new JR_Importer( APP_POST_TYPE, $fields, $args );
}
