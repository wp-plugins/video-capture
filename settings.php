<?php

if ( !class_exists( 'WP_Video_Capture_Settings' ) ) {
	class WP_Video_Capture_Settings {

		public function __construct() {

      // Initialize Mailer
			$site_url = parse_url( site_url() );
      $this->hostname = $site_url['host'];
      require_once plugin_dir_path( __FILE__ ) . 'inc/class.video-capture-email.php';
      $this->video_capture_email = new Video_Capture_Email( $this->hostname );

      // Initialize JS resources
      add_action( 'admin_enqueue_scripts', array( &$this, 'register_resources' ) );

			// Register actions
			add_action( 'admin_init', array( &$this, 'hide_registration_notice' ) );
			add_action( 'admin_init', array( &$this, 'admin_init' ) );
			add_action( 'admin_menu', array( &$this, 'add_menu' ) );

		}

    public function register_resources() {
      wp_register_script( 'record_video_admin_settings',
        plugin_dir_url( __FILE__ ) . 'js/record_video_admin_settings.js', array( 'jquery' ), '1.3', true );
    }

		public function validate_email( $email ) {
			if ( !is_email( $email ) ) {
				add_settings_error( 'registration_email', 'video-capture-invalid-email', 'Please enter a correct email' );
			} else {
				return $email;
			}
		}

		public function registration_email_notice() {
      printf(
        '<div class="update-nag"><p>%1$s <input type="button" class="button" value="%3$s" onclick="document.location.href=\'%2$s\';" /></div>',
        'Please enter your email to get notifications about newly uploaded videos',
        esc_url( add_query_arg( 'wp-video-capture-nag', wp_create_nonce( 'wp-video-capture-nag' ) ) ),
        'Dismiss'
      );
		}

    public function hide_registration_notice() {

      if ( !isset( $_GET['wp-video-capture-nag'] ) ) {
        return;
      }
 
      // Check nonce
      check_admin_referer( 'wp-video-capture-nag', 'wp-video-capture-nag' );
 
      // Update user meta to indicate dismissed notice
      update_user_meta( get_current_user_id(), '_wp-video-capture_hide_registration_notice', true );

    }

		public function admin_init() {

      global $pagenow;

			// Display notification if not registered
      if ( !get_option( 'registration_email' )
        && $pagenow == 'admin.php' 
        && $_GET['page'] == 'wp_video_capture_settings'
        && !get_user_meta( get_current_user_id(), '_wp-video-capture_hide_registration_notice', true ) ) {
				add_action( 'admin_notices', array( &$this, 'registration_email_notice' ) );
			}

			// Input validation
			register_setting( 'wp_video_capture-group', 'registration_email', array( &$this, 'validate_email' ) );

			// Add your settings section
			add_settings_section(
				'wp_video_capture-section',
				'WP Video Capture Settings',
				array( &$this, 'settings_section_wp_video_capture' ),
				'wp_video_capture'
			);

			// Add email setting
			add_settings_field(
				'wp_video_capture-registration_email',
				'Notification Email',
				array( &$this, 'settings_field_input_text' ),
				'wp_video_capture',
				'wp_video_capture-section',
				array(
					'field' => 'registration_email'
				)
			);

		}

		public function settings_section_wp_video_capture() {
			echo 'Please enter your email to get notifications about newly uploaded videos.<br/>By entering your email you automatically agree to the <a class="wp-video-capture-tnc-link" href="#">Terms and Conditions</a>.';
		}

		public function settings_field_input_text( $args ) {
			// Get the field name from the $args array
			$field = $args['field'];
			// Get the value of this setting
			$value = get_option( $field );
			// echo a proper input type="text"
			echo sprintf( '<input type="text" name="%s" id="%s" value="%s" />', $field, $field, $value );
		}

		public function settings_field_input_checkbox( $args ) {
			// Get the field name from the $args array
			$field = $args['field'];
			// Get the value of this setting
			$value = get_option( $field );
			// echo a proper input type="text"
			echo sprintf( '<input type="checkbox" name="%s" id="%s" value="true" %s/>', $field, $field, checked( $value, 'true', '' ) );
		}

		public function add_menu() {
			// Add a page to manage this plugin's settings
			add_menu_page(
				'Video Capture - Captured Videos',
				'Video Capture',
				'manage_options',
				'wp_video_capture_videos',
				array( &$this, 'plugin_videos_page' ),
        plugin_dir_url( __FILE__ ) . 'images/video-icon.gif',
        12.576
			);

			// Add a page to see captured videos
			add_submenu_page(
				'wp_video_capture_videos',
				'Video Capture - Settings',
				'Settings',
				'manage_options',
				'wp_video_capture_settings',
				array( &$this, 'plugin_settings_page' )
			);
		}

		public function plugin_settings_page() {
			if ( !current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}

      // Add helper JS
		  wp_enqueue_script( 'record_video_admin_settings' );

			// Render the settings template
			include plugin_dir_path( __FILE__ ) . 'templates/settings.php';

		}

		public function plugin_videos_page() {
			if ( !current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}

			// Include WP table class
			include plugin_dir_path( __FILE__ ) . 'inc/class.video-list-table.php';
			$video_list_table = new Video_List_Table();
			$video_list_table->prepare_items();

			// Render the videos template
			include plugin_dir_path( __FILE__ ) . 'templates/videos.php';

		}
	}
}
