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
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    8.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    8.0.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

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
			wp_enqueue_style( $this->plugin_name, ROP_LITE_URL . 'assets/css/rop.css', array( $this->plugin_name . '_core' ), $this->version, 'all' );
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
		if ( ! isset( $screen->id ) ) {
			return;
		}
		if ( in_array( $screen->id, array( 'toplevel_page_TweetOldPost' ) ) ) {
			wp_enqueue_media();
			wp_enqueue_style( $this->plugin_name . '_fa', ROP_LITE_URL . 'assets/css/font-awesome.min.css', array(), $this->version );

			wp_register_script( $this->plugin_name . '_main', ROP_LITE_URL . 'assets/js/build/rop' . ( ( ROP_DEBUG ) ? '' : '.min' ) . '.js', array(), ( ROP_DEBUG ) ? time() : $this->version, false );

			$array_nonce = array(
				'root' => esc_url_raw( rest_url( '/tweet-old-post/v8/api/' ) ),
			);
			if ( current_user_can( 'manage_options' ) ) {
				$array_nonce = array(
					'root'  => esc_url_raw( rest_url( '/tweet-old-post/v8/api/' ) ),
					'nonce' => wp_create_nonce( 'wp_rest' ),
				);
			}
			$global_settings             = new Rop_Global_Settings();
			$array_nonce['license_type'] = $global_settings->license_type();
			$array_nonce['labels']       = Rop_I18n::get_labels();
			$array_nonce['upsell_link']  = Rop_I18n::UPSELL_LINK;
			$array_nonce['debug']        = ( ( ROP_DEBUG ) ? 'yes' : 'no' );
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
			__( 'Revive Old Posts', 'tweet-old-post' ), __( 'Revive Old Posts', 'tweet-old-post' ), 'manage_options', 'TweetOldPost',
			array(
				$this,
				'rop_main_page',
			),
			'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMjIuMyAxMjIuMyI+PGRlZnM+PHN0eWxlPi5he2ZpbGw6I2U2ZTdlODt9PC9zdHlsZT48L2RlZnM+PHRpdGxlPkFzc2V0IDI8L3RpdGxlPjxwYXRoIGNsYXNzPSJhIiBkPSJNNjEuMTUsMEE2MS4xNSw2MS4xNSwwLDEsMCwxMjIuMyw2MS4xNSw2MS4yMiw2MS4yMiwwLDAsMCw2MS4xNSwwWm00MC41NCw2MC4xMUw4Ni41Nyw3NS42Miw0Ny45MywzMi4zOWwtMzMuMDcsMjdIMTJhNDkuMTksNDkuMTksMCwwLDEsOTguMzUsMS4yNFpNMTA5LjM1LDcxYTQ5LjIsNDkuMiwwLDAsMS05Ni42My0xLjJoNS44NEw0Ni44LDQ2Ljc0LDg2LjI0LDkwLjg2bDE5LjU3LTIwLjA3WiIvPjwvc3ZnPg=='
		);
	}

	/**
	 * The Cron Job for the plugin.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function rop_cron_job() {
		$queue           = new Rop_Queue_Model();
		$services_model  = new Rop_Services_Model();
		$logger          = new Rop_Logger();
		$queue_stack     = $queue->build_queue();
		$service_factory = new Rop_Services_Factory();
		foreach ( $queue_stack as $account => $events ) {
			foreach ( $events as $index => $event ) {
				/**
				 * Trigger share if we have an event in the past, and the timestamp of that event is in the last 15mins.
				 */
				if ( $event['time'] <= Rop_Scheduler_Model::get_current_time() && ( Rop_Scheduler_Model::get_current_time() - $event['time'] ) < ( 15 * MINUTE_IN_SECONDS ) ) {
					$posts = $event['posts'];
					$queue->remove_from_queue( $event['time'], $account );
					$account_data = $services_model->find_account( $account );
					try {
						$service = $service_factory->build( $account_data['service'] );
						$service->set_credentials( $account_data['credentials'] );
						foreach ( $posts as $post ) {
							$post_data = $queue->prepare_post_object( $post, $account );
							$logger->info( 'Posting', array( 'extra' => $post_data ) );
							$service->share( $post_data, $account_data );
						}
					} catch ( Exception $exception ) {
						$error_message = sprintf( Rop_I18n::get_labels( 'accounts.service_error' ), $account_data['service'] );
						$logger->alert_error( $error_message . ' Error: ' . $exception->getTrace() );
					}
				}
			}
		}
	}

}
