<?php
/**
 * JobRoller logging class.
 */
class jrLog {

	var $log_file;
	var $fp;

	/**
	 * Class constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->log_file = self::get_log_file_path();
		$this->fp = null;

		add_action( 'appthemes_first_run', array( __CLASS__, 'create_files' ) );
	}

	/**
	 * Writes message to the log file.
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	function write_log( $message ) {
		global $jr_options;

		if ( $jr_options->jr_enable_log ) {
			// if file pointer doesn't exist, then open log file
			if ( ! $this->fp ) {
				$this->open_log();
			}
			// define script name
			$script_name = basename( $_SERVER['PHP_SELF'] );
			$script_name = substr( $script_name, 0, -4 );
			// define current time
			$time = date_i18n( 'H:i:s' );
			// write current time, script name and message to the log file
			fwrite( $this->fp, "$time ($script_name) $message\n" );
		}
	}

	/**
	 * Opens log file.
	 *
	 * @return void
	 */
	function open_log() {
		// define log file path and name
		$lfile = $this->log_file;
		// open log file for writing only; place the file pointer at the end of the file
		// if the file does not exist, attempt to create it
		$this->fp = fopen( $lfile, 'a' ) or exit( "Can't open $lfile!" );
	}

	/**
	 * Clears log file.
	 *
	 * @return void
	 */
	function clear_log() {
		$lfile = $this->log_file;
		$fp = @fopen( $lfile, 'w' );
		@fclose( $fp );
	}

	/**
	 * Get a log dir path.
	 *
	 * @return string The log dir path.
	 */
	public static function get_log_dir_path() {
		$upload_dir = wp_upload_dir();
		$log_dir = $upload_dir['basedir'] . '/at-logs/';

		return $log_dir;
	}

	/**
	 * Get a log file path.
	 *
	 * @return string The log file path.
	 */
	public static function get_log_file_path() {

		return trailingslashit( self::get_log_dir_path() ) . self::get_log_file_name();
	}

	/**
	 * Get a log file name.
	 *
	 * @return string The log file name.
	 */
	public static function get_log_file_name() {
		$filename = 'jobroller-' . sanitize_file_name( wp_hash( 'jobroller' ) ) . '.log';

		return $filename;
	}

	/**
	 * Create files/directories.
	 * 
	 * @return void
	 */
	public static function create_files() {
		// Install files and folders for storing log files
		$log_dir = self::get_log_dir_path();

		$files = array(
			array(
				'base'    => $log_dir,
				'file'    => '.htaccess',
				'content' => 'deny from all'
			),
			array(
				'base'    => $log_dir,
				'file'    => 'index.html',
				'content' => ''
			),
			array(
				'base'    => $log_dir,
				'file'    => self::get_log_file_name(),
				'content' => ''
			),
		);

		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
				}
			}
		}

	}

}
