<?php
/*
Plugin Name: Video Capture
Plugin URI: http://vidrack.com/demo
Description: Add a video camera to your website!
Version: 1.3
Author: Vidrack.com
Author URI: http://vidrack.com
License: Vidrack Proprietary License
*/

if ( !class_exists( 'WP_Video_Capture' ) ) {
	class WP_Video_Capture {

		public function __construct() {

			// Initialize Settings
			require_once plugin_dir_path( __FILE__ ) . 'settings.php';
			$wp_video_capture_settings = new WP_Video_Capture_Settings();

      // Initialize Mailer class
			$site_url = parse_url( site_url() );
      $this->hostname = $site_url['host'];
      require_once plugin_dir_path( __FILE__ ) . 'inc/class.video-capture-email.php';
      $this->video_capture_email = new Video_Capture_Email( $this->hostname );

      // Initialize JS and CSS resources
      add_action( 'wp_enqueue_scripts', array( &$this, 'register_resources' ) );

      // Initialize AJAX actions
			add_action( 'wp_ajax_store_video_file', array( &$this, 'store_video_file' ) );
			add_action( 'wp_ajax_nopriv_store_video_file', array( &$this, 'store_video_file' ) );

      // Initialize shortcode
			add_shortcode( 'record_video', array( &$this, 'record_video' ) );

		}

		public static function activate() {

			// Initialize table to store video information
			global $wpdb;
			$table_name = $wpdb->prefix . "video_capture";
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
				$sql = 'CREATE TABLE ' . $table_name. ' (
    		  id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    		  filename VARCHAR(255),
    		  ip VARCHAR(255),
    		  uploaded_at DATETIME
    		)';
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				dbDelta( $sql );
			}

			// Add settings options 
			add_option( 'registration_email' );

		}

		public static function deactivate() {
			// Remove database table
			global $wpdb;
			$table_name = $wpdb->prefix . "video_capture";
			$wpdb->query( 'DROP TABLE IF EXISTS ' . $table_name );

			// Remove registration_email option
			delete_option( 'registration_email' );

      // Remove hide notice information
      delete_user_meta( get_current_user_id(), '_wp-video-capture_hide_registration_notice' );

		}

    public function register_resources() {

      // JS
      wp_register_script( 'icheck',
        plugin_dir_url( __FILE__ ) . 'lib/js/icheck.min.js', array( 'jquery' ), '1.0.1', true );
      wp_register_script( 'record_video',
        plugin_dir_url( __FILE__ ) . 'js/record_video.js', array( 'jquery'), '1.3', true );
      wp_register_script( 'swfobject',
        plugin_dir_url( __FILE__ ) . 'lib/js/swfobject.js', array(), '2.2', true );

      // CSS
      wp_register_style( 'icheck-skin',
        plugin_dir_url( __FILE__ ) . 'lib/css/icheck-flat-skin/green.css' );
      wp_register_style( 'record_video',
        plugin_dir_url( __FILE__ ) . 'css/record_video.css' );

      // Pass variables to the frontend
			wp_localize_script(
				'record_video',
				'VideoCapture',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'timestamp' => current_time( 'timestamp' ),
          'ip' => $_SERVER['REMOTE_ADDR'],
          'site_name' => $this->hostname,
					'plugin_url' => plugin_dir_url( __FILE__ )
				)
			);

    }

		public function record_video( $atts, $content = null ) {

			// Extract attributes
			extract( shortcode_atts( array( 'align' => 'left' ), $atts ) );

			// Enable output buffering
			ob_start();

      // Render template
			wp_enqueue_script( 'icheck' );
			wp_enqueue_style( 'icheck-skin' );
			wp_enqueue_script( 'swfobject' );
			wp_enqueue_script( 'record_video' );
			wp_enqueue_style( 'record_video' );
			include plugin_dir_path( __FILE__ ) . 'templates/record_video.php';

			// Return buffer
			$record_video_contents = ob_get_contents();
			ob_end_clean();
			return $record_video_contents;
		}

		/**
		 * Process file uploading for mobile
		 */
		public function store_video_file() {

			header( 'Content-Type: application/json' );

			if ( !isset($_REQUEST['filename']) ) {
				echo json_encode( array( 'status' => 'error', 'message' => 'Filename is not set.' ) );
				die();
			}

			if ( !isset($_REQUEST['timestamp']) || !(
					( (string) (int) $_REQUEST['timestamp'] === $_REQUEST['timestamp'] )
					&& ( $_REQUEST['timestamp'] <= PHP_INT_MAX )
					&& ( $_REQUEST['timestamp']>= ~PHP_INT_MAX ) ) ) {
				echo json_encode( array( 'status' => 'error', 'message' => 'Timestamp is not set.' ) );
				die();
			}

			if ( !isset($_REQUEST['ip']) or !filter_var( $_REQUEST['ip'], FILTER_VALIDATE_IP ) ) {
				echo json_encode( array( 'status' => 'error', 'message' => 'IP address is not set.' ) );
				die();
			}

			// Insert new video info into the DB
			global $wpdb;
			$result = $wpdb->insert(
				$wpdb->prefix . 'video_capture',
				array(
					'filename' => $_REQUEST['filename'],
					'ip' => $_REQUEST['ip'],
					'uploaded_at' => date( 'Y-m-d H:i:s', $_REQUEST['timestamp'] )
				)
			);

			if ( !$result ) {

				echo json_encode( array( 'status' => 'error', 'message' => 'Cannot insert data into DB.' ) );

			} else {

				echo json_encode( array( 'status' => 'success', 'message' => 'Done!' ) );

        // Send email notification
        if ( $to = get_option( 'registration_email' ) ) {
          $this->video_capture_email->send_new_video_email( $to, $_REQUEST['filename'] );
        }

			}

			die();
		}
	}
}

if ( class_exists( 'WP_Video_Capture' ) ) {

	// Installation and uninstallation hooks
	register_activation_hook( __FILE__, array( 'WP_Video_Capture', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'WP_Video_Capture', 'deactivate' ) );

	// Instantiate the plugin class
	$wp_video_capture = new WP_Video_Capture();

	// Add a link to the settings page onto the plugin page
	if ( isset( $wp_video_capture ) ) {
		// Add the settings link to the plugins page
		function plugin_settings_link( $links ) {
			$settings_link = '<a href="admin.php?page=wp_video_capture_settings">Settings</a>';
			array_unshift( $links, $settings_link );
			return $links;
		}

		$plugin = plugin_basename( __FILE__ );
		add_filter( "plugin_action_links_$plugin", 'plugin_settings_link' );
	}
}

?>
