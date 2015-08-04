<?php
/*
Plugin Name: Video Recorder
Plugin URI: http://vidrack.com
Description: Add a video camera to your website!
Version: 1.6.3
Author: Vidrack.com
Author URI: http://vidrack.com
License: GPLv2 or later
*/

if ( !class_exists( 'WP_Video_Capture' ) ) {
	class WP_Video_Capture {

    private static $vidrack_version = '1.6.3';

		public function __construct() {

			// Initialize Settings
			require_once plugin_dir_path( __FILE__ ) . 'settings.php';
			$wp_video_capture_settings = new WP_Video_Capture_Settings();

      // Initialize Mailer class
			$site_url = parse_url( site_url() );
      $this->hostname = $site_url['host'];
      require_once plugin_dir_path( __FILE__ ) . 'inc/class.video-capture-email.php';
      $this->video_capture_email = new Video_Capture_Email( $this->hostname );

      // Initialize Mobile Detect class
      require_once plugin_dir_path( __FILE__ ) . 'inc/class.mobile-detect.php';
      $this->mobile_detect = new Mobile_Detect;

      // Initialize JS and CSS resources
      add_action( 'wp_enqueue_scripts', array( &$this, 'register_resources' ) );

      // Initialize AJAX actions
			add_action( 'wp_ajax_store_video_file', array( &$this, 'store_video_file' ) );
			add_action( 'wp_ajax_nopriv_store_video_file', array( &$this, 'store_video_file' ) );

      // Check for DB update
      add_action( 'plugins_loaded', array( &$this, 'update_check' ) );

      // Initialize shortcode
			add_shortcode( 'record_video', array( &$this, 'record_video' ) );
      add_shortcode( 'vidrack', array( &$this, 'record_video' ) );

		}

		public static function activate() {

			// Initialize table to store video information
			global $wpdb;
			$table_name = $wpdb->prefix . "video_capture";
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
				$sql = "CREATE TABLE $table_name (
    		          id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    		          filename VARCHAR(255),
    		          ip VARCHAR(255),
    		          uploaded_at DATETIME
    		        )
        ";
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				dbDelta( $sql );
			}

			// Add settings options
			add_option( 'vidrack_registration_email' );
      add_option( 'vidrack_display_branding', 1 );
      add_option( 'vidrack_window_modal', 1 );
      add_option( 'vidrack_version', self::$vidrack_version );

		}

		public static function uninstall() {
			// Remove database table
			global $wpdb;
			$table_name = $wpdb->prefix . 'video_capture';
			$wpdb->query( 'DROP TABLE IF EXISTS ' . $table_name );

			// Remove registration_email option
			delete_option( 'vidrack_registration_email' );
      delete_option( 'vidrack_display_branding' );
      delete_option( 'vidrack_window_modal' );
      delete_option( 'vidrack_version ');

      // Remove hide notice information
      delete_user_meta( get_current_user_id(), '_wp-video-capture_hide_registration_notice' );

		}

    public static function update_check() {
      if ( get_site_option( 'vidrack_version' ) != self::$vidrack_version ) {
        self::update();
      }
    }

    public static function update() {
      $installed_ver = get_site_option( 'vidrack_version' );

      // Remove old options
      if ( version_compare($installed_ver, '1.6', '<') ) {
        delete_option( 'registration_email' );
        delete_option( 'display_branding' );
      }

      // Enable modal window by default
      if ( version_compare($installed_ver, '1.6.1', '<') ) {
        add_option( 'vidrack_window_modal', 1 );
      }

      update_option( 'vidrack_version', self::$vidrack_version );
    }

    public function register_resources() {

      // JS
      wp_register_script( 'magnific-popup',
        plugin_dir_url( __FILE__ ) . 'lib/js/magnific-popup.min.js', array( 'jquery' ), '1.0.0' );
      wp_register_script( 'swfobject',
        plugin_dir_url( __FILE__ ) . 'lib/js/swfobject.js', array(), '2.2' );
      wp_register_script( 'record_video',
        plugin_dir_url( __FILE__ ) . 'js/record_video.js', array( 'jquery', 'magnific-popup', 'swfobject' ), '1.6.3' );

      // CSS
      wp_register_style( 'magnific-popup',
        plugin_dir_url( __FILE__ ) . 'lib/css/magnific-popup.css', array(), '1.0.0', 'screen' );
      wp_register_style( 'record_video',
        plugin_dir_url( __FILE__ ) . 'css/record_video.css', array( 'magnific-popup' ), '1.6.3' );

      // Pass variables to the frontend
			wp_localize_script(
				'record_video',
				'VideoCapture',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
          'ip' => $_SERVER['REMOTE_ADDR'],
          'site_name' => $this->hostname,
					'plugin_url' => plugin_dir_url( __FILE__ ),
          'display_branding' => get_option( 'vidrack_display_branding' ),
          'window_modal' => get_option( 'vidrack_window_modal' ),
          'mobile' => $this->mobile_detect->isMobile()
				)
			);

    }

		public function record_video( $atts, $content = null ) {

			// Extract attributes
			extract( shortcode_atts( array( 'align' => 'left' ), $atts ) );

			// Enable output buffering
			ob_start();

      // Render template
			wp_enqueue_style( 'record_video' );
      wp_enqueue_script( 'record_video' );
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
					'uploaded_at' => current_time( 'mysql' )
				)
			);

			if ( !$result ) {

				echo json_encode( array( 'status' => 'error', 'message' => 'Cannot insert data into DB.' ) );

			} else {

        // Send email notification
        if ( $to = get_option( 'vidrack_registration_email' ) ) {
          $this->video_capture_email->send_new_video_email( $to, $_REQUEST['filename'] );
        }

        echo json_encode( array( 'status' => 'success', 'message' => 'Done!' ) );

			}

			die();
		}
	}
}

if ( class_exists( 'WP_Video_Capture' ) ) {

	// Installation and uninstallation hooks
	register_activation_hook( __FILE__, array( 'WP_Video_Capture', 'activate' ) );
	register_uninstall_hook( __FILE__, array( 'WP_Video_Capture', 'uninstall' ) );

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
