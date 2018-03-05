<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Rop
 * @subpackage Rop/admin
 * @author     ThemeIsle <friends@themeisle.com>
 */
class Rop_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    8.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    8.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    8.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    8.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rop_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rop_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$screen = get_current_screen();
		if ( in_array( $screen->id, array( 'toplevel_page_TweetOldPost' ) ) ) {
			wp_enqueue_style( $this->plugin_name . '_core', ROP_LITE_URL . 'assets/css/rop_core.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name, ROP_LITE_URL . 'assets/css/rop.css', array($this->plugin_name . '_core'), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    8.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rop_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rop_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$screen = get_current_screen();

		if ( in_array( $screen->id, array( 'toplevel_page_TweetOldPost' ) ) ) {
			wp_enqueue_media();
			wp_enqueue_script( $this->plugin_name . '_fa', 'https://use.fontawesome.com/af4c3f0b39.js', array(), $this->version, false );

			wp_register_script( $this->plugin_name . '_main',  ROP_LITE_URL . 'assets/js/build/rop.js', array( $this->plugin_name . '_fa' ), $this->version, false );
			$array_nonce = array(
				'root' => esc_url_raw( rest_url( '/tweet-old-post/v8/api/' ) ),
			);
			if ( current_user_can( 'manage_options' ) ) {
			    $array_nonce = array(
					'root' => esc_url_raw( rest_url( '/tweet-old-post/v8/api/' ) ),
					'nonce' => wp_create_nonce( 'wp_rest' ),
				);
			}
			$global_settings = new Rop_Global_Settings();
			$array_nonce['has_pro'] = $global_settings->has_pro();
			$array_nonce['available_post_types'] = $global_settings->get_available_post_types();
			$array_nonce['available_taxonomies'] = $global_settings->get_available_taxonomies();
			wp_localize_script( $this->plugin_name . '_main', 'ropApiSettings', $array_nonce );
			wp_localize_script( $this->plugin_name . '_main', 'ROP_ASSETS_URL', ROP_LITE_URL . 'assets/' );
			wp_enqueue_script( $this->plugin_name . '_main' );
		}

	}

	/**
	 * The display method for the main page.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function rop_main_page() {
		echo '
	    <div id="rop_core" style="margin: 20px 20px 40px 0;">
	        <main-page-panel></main-page-panel>
        </div>';
	}

	/**
	 * Add admin menu items for plugin.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function menu_pages() {
		add_menu_page(
			__( 'Revive Old Posts', 'rop' ), __( 'Revive Old Posts', 'rop' ), 'manage_options', 'TweetOldPost',
			array(
				$this,
				'rop_main_page',
			)
		);
	}

	/**
	 * The Cron Job for the plugin.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function rop_cron_job() {
		$queue = new Rop_Queue_Model();
		$services_model = new Rop_Services_Model();
		$log = new Rop_Logger();

		$queue_stack = $queue->get_ordered_queue();
		foreach ( $queue_stack as $index => $event ) {
			if ( strtotime( $event['time'] ) <= current_time( 'timestamp', 0 ) ) {
				$account_data = $services_model->find_account( $event['account_id'] );
				$service_factory = new Rop_Services_Factory();
				try {
					$service = $service_factory->build( $account_data['service'] );
					$service->set_credentials( $account_data['credentials'] );
					$queue_event = $queue->remove_from_queue( $index, $event['account_id'] );
					if ( $service->share( $queue_event, $account_data ) ) {
						$info_message = sprintf( esc_html__( 'The post was shared successfully with the %1$s network', 'tweet-old-post' ), $account_data['service'] );
						$log->info( $info_message );
					} else {
						$error_message = sprintf( esc_html__( 'The post was not shared with the %1$s network. An error occured.', 'tweet-old-post' ), $account_data['service'] );
						$log->warn( $error_message );
					}
					$this->response->set_data( $queue->get_ordered_queue() );
				} catch ( Exception $exception ) {
					// The service can not be built or was not found.
					$error_message = sprintf( esc_html__( 'The service %1$s can NOT be built or was not found', 'tweet-old-post' ), $account_data['service'] );
					$log->warn( $error_message, $exception );
				}
			}
		}
	}

}
