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
	 * Allowed screen ids used for assets enqueue.
	 *
	 * @var array Array of script vs. page slugs. If page slugs is an array, then an exact match will occur.
	 */
	private $allowed_screens;

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
	public function __construct( $plugin_name = '', $version = '' ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$general_settings = new Rop_Settings_Model;

		$post_types = wp_list_pluck( $general_settings->get_selected_post_types(), 'value' );
		$attachment_post_type = array_search( 'attachment', $post_types );
		unset( $post_types[ $attachment_post_type ] );

		$this->allowed_screens = array(
			'dashboard'   => 'TweetOldPost',
			'exclude'     => 'rop_content_filters',
			'publish_now' => $post_types,
		);

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    8.0.0
	 */
	public function enqueue_styles() {

		$page = $this->get_current_page();
		if ( empty( $page ) ) {
			return;
		}

		$deps = array();
		if ( 'publish_now' !== $page ) {
			wp_enqueue_style( $this->plugin_name . '_core', ROP_LITE_URL . 'assets/css/rop_core.css', array(), $this->version, 'all' );
			$deps = array( $this->plugin_name . '_core' );
		}
		wp_enqueue_style( $this->plugin_name, ROP_LITE_URL . 'assets/css/rop.css', $deps, $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '_fa', ROP_LITE_URL . 'assets/css/font-awesome.min.css', array(), $this->version );

	}

	/**
	 * Return current ROP admin page.
	 *
	 * @return bool|string Page slug.
	 */
	private function get_current_page() {
		$screen = get_current_screen();

		if ( ! isset( $screen->id ) ) {
			return false;
		}
		$page = false;
		foreach ( $this->allowed_screens as $script => $id ) {
			if ( is_array( $id ) ) {
				foreach ( $id as $page_id ) {
					if ( $screen->id === $page_id ) {
						$page = $script;
						break;
					}
				}
			} else {
				if ( strpos( $screen->id, $id ) !== false ) {
					$page = $script;
					continue;
				}
			}
		}

		return $page;
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    8.0.0
	 */
	public function enqueue_scripts() {

		$page = $this->get_current_page();
		if ( empty( $page ) ) {
			return;
		}

		wp_register_script( $this->plugin_name . '-dashboard', ROP_LITE_URL . 'assets/js/build/dashboard' . ( ( ROP_DEBUG ) ? '' : '.min' ) . '.js', array(), ( ROP_DEBUG ) ? time() : $this->version, false );
		wp_register_script( $this->plugin_name . '-exclude', ROP_LITE_URL . 'assets/js/build/exclude' . ( ( ROP_DEBUG ) ? '' : '.min' ) . '.js', array(), ( ROP_DEBUG ) ? time() : $this->version, false );

		$array_nonce = array(
			'root' => esc_url_raw( rest_url( '/tweet-old-post/v8/api/' ) ),
		);
		if ( current_user_can( 'manage_options' ) ) {
			$array_nonce = array(
				'root'  => esc_url_raw( rest_url( '/tweet-old-post/v8/api/' ) ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			);
		}

		$services        = new Rop_Services_Model();
		$active_accounts = $services->get_active_accounts();

		$global_settings             = new Rop_Global_Settings();
		$settings                   = new Rop_Settings_Model();

		$array_nonce['license_type'] = $global_settings->license_type();
		$array_nonce['labels']       = Rop_I18n::get_labels();
		$array_nonce['upsell_link']  = Rop_I18n::UPSELL_LINK;
		$array_nonce['staging']      = $this->rop_site_is_staging();
		$array_nonce['debug']        = ( ( ROP_DEBUG ) ? 'yes' : 'no' );
		$array_nonce['publish_now']  = array(
			'action'   => $settings->get_instant_sharing_by_default(),
			'accounts' => $active_accounts,
		);

		if ( 'publish_now' === $page && $global_settings->license_type() > 0 ) {
			$array_nonce['publish_now'] = apply_filters( 'rop_publish_now_attributes', $array_nonce['publish_now'] );
			wp_register_script( $this->plugin_name . '-publish_now', ROP_LITE_URL . 'assets/js/build/publish_now' . ( ( ROP_DEBUG ) ? '' : '.min' ) . '.js', array(), ( ROP_DEBUG ) ? time() : $this->version, false );
		}

		wp_localize_script( $this->plugin_name . '-' . $page, 'ropApiSettings', $array_nonce );
		wp_localize_script( $this->plugin_name . '-' . $page, 'ROP_ASSETS_URL', ROP_LITE_URL . 'assets/' );
		wp_enqueue_script( $this->plugin_name . '-' . $page );

	}

	/**
	 * Set our supported mime types.
	 *
	 * @since   8.1.0
	 * @access  public
	 *
	 * @return array
	 */
	public function rop_supported_mime_types() {

		$accepted_mime_types = array();

		$image_mime_types = apply_filters(
			'rop_accepted_image_mime_types',
			array(
				'image/jpeg',
				'image/png',
				'image/gif',
			)
		);

		$video_mime_types = apply_filters(
			'rop_accepted_video_mime_types',
			array(
				'video/mp4',
				'video/x-m4v',
				'video/quicktime',
				'video/x-ms-asf',
				'video/x-ms-wmv',
				'video/avi',
			)
		);

		$accepted_mime_types['image'] = $image_mime_types;

		$accepted_mime_types['video'] = $video_mime_types;
		// We use empty for non-attachament posts query.
		$accepted_mime_types['all'] = array_merge( $image_mime_types, $video_mime_types, array( '' ) );

		return $accepted_mime_types;

	}

	/**
	 * Detects if is a staging environment
	 *
	 * @since     8.0.4
	 * @return    bool   true/false
	 */
	public static function rop_site_is_staging() {

		$rop_known_staging = array(
			'IS_WPE_SNAPSHOT',
			'KINSTA_DEV_ENV',
			'WPSTAGECOACH_STAGING',
		);

		foreach ( $rop_known_staging as $rop_staging_const ) {
			if ( defined( $rop_staging_const ) ) {

				return apply_filters( 'rop_dont_work_on_staging', true );

			}
		}
		// wp engine staging function
		if ( function_exists( 'is_wpe_snapshot' ) ) {
			if ( is_wpe_snapshot() ) {

				return apply_filters( 'rop_dont_work_on_staging', true );

			}
		}
		// JETPACK_STAGING_MODE if jetpack is installed and picks up on a staging environment we're not aware of
		if ( defined( 'JETPACK_STAGING_MODE' ) && JETPACK_STAGING_MODE == true ) {
			return apply_filters( 'rop_dont_work_on_staging', true );
		}

		return false;

	}

	/**
	 * Legacy auth callback.
	 */
	public function legacy_auth() {
		$code    = sanitize_text_field( isset( $_GET['code'] ) ? $_GET['code'] : '' );
		$state   = sanitize_text_field( isset( $_GET['state'] ) ? $_GET['state'] : '' );
		$network = sanitize_text_field( isset( $_GET['network'] ) ? $_GET['network'] : '' );
		/**
		 * For twitter we don't have code/state params.
		 */
		if ( ( empty( $code ) || empty( $state ) ) && $network !== 'twitter' ) {
			return;
		}

		$oauth_token    = sanitize_text_field( isset( $_GET['oauth_token'] ) ? $_GET['oauth_token'] : '' );
		$oauth_verifier = sanitize_text_field( isset( $_GET['oauth_verifier'] ) ? $_GET['oauth_verifier'] : '' );
		/**
		 * For twitter we don't have code/state params.
		 */
		if ( ( empty( $oauth_token ) || empty( $oauth_verifier ) ) && $network === 'twitter' ) {
			return;
		}
		switch ( $network ) {
			case 'linkedin':
				$lk_service = new Rop_Linkedin_Service();
				$lk_service->authorize();
				break;
			case 'twitter':
				$twitter_service = new Rop_Twitter_Service();
				$twitter_service->authorize();
				break;
			case 'pinterest':
				$pinterest_service = new Rop_Pinterest_Service();
				$pinterest_service->authorize();
				break;
			default:
				$fb_service = new Rop_Facebook_Service();
				$fb_service->authorize();
		}
	}

	/**
	 * The display method for the main page.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function rop_main_page() {
		$this->wrong_pro_version();
		?>
		<div id="rop_core" style="margin: 20px 20px 40px 0;">
			<main-page-panel></main-page-panel>
		</div>
		<?php
	}

	/**
	 * Notice for wrong pro version usage.
	 */
	private function wrong_pro_version() {
		if ( defined( 'ROP_PRO_VERSION' ) && ( - 1 === version_compare( ROP_PRO_VERSION, '2.0.0' ) ) ) {
			?>
			<div class="error">
				<p>In order to use the premium features for <b>v8.0</b> of Revive Old Posts you will need to update the
					Premium addon to at least 2.0. In case that you don't see the update, please download from your <a
							href="https://revive.social/your-purchases/" target="_blank">purchase history</a></p>
			</div>
			<?php
		}
	}

	/**
	 * The display method for the main page.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function content_filters() {
		$this->wrong_pro_version();
		?>
		<div id="rop_content_filters" style="margin: 20px 20px 40px 0;">
			<exclude-posts-page></exclude-posts-page>
		</div>
		<?php
	}

	/**
	 * Add admin menu items for plugin.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function menu_pages() {
		add_menu_page(
			__( 'Revive Old Posts', 'tweet-old-post' ),
			__( 'Revive Old Posts', 'tweet-old-post' ),
			'manage_options',
			'TweetOldPost',
			array(
				$this,
				'rop_main_page',
			),
			'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMjIuMyAxMjIuMyI+PGRlZnM+PHN0eWxlPi5he2ZpbGw6I2U2ZTdlODt9PC9zdHlsZT48L2RlZnM+PHRpdGxlPkFzc2V0IDI8L3RpdGxlPjxwYXRoIGNsYXNzPSJhIiBkPSJNNjEuMTUsMEE2MS4xNSw2MS4xNSwwLDEsMCwxMjIuMyw2MS4xNSw2MS4yMiw2MS4yMiwwLDAsMCw2MS4xNSwwWm00MC41NCw2MC4xMUw4Ni41Nyw3NS42Miw0Ny45MywzMi4zOWwtMzMuMDcsMjdIMTJhNDkuMTksNDkuMTksMCwwLDEsOTguMzUsMS4yNFpNMTA5LjM1LDcxYTQ5LjIsNDkuMiwwLDAsMS05Ni42My0xLjJoNS44NEw0Ni44LDQ2Ljc0LDg2LjI0LDkwLjg2bDE5LjU3LTIwLjA3WiIvPjwvc3ZnPg=='
		);
		add_submenu_page(
			'TweetOldPost',
			__( 'Dashboard', 'tweet-old-post' ),
			__( 'Dashboard', 'tweet-old-post' ),
			'manage_options',
			'TweetOldPost',
			array(
				$this,
				'rop_main_page',
			)
		);
		add_submenu_page(
			'TweetOldPost',
			__( 'Exclude Posts', 'tweet-old-post' ),
			__( 'Exclude Posts', 'tweet-old-post' ),
			'manage_options',
			'rop_content_filters',
			array(
				$this,
				'content_filters',
			)
		);
	}

	/**
	 * Publish now upsell
	 *
	 * @since   8.1.0
	 * @access  public
	 */
	public function publish_now_upsell() {

		$page = $this->get_current_page();
		if ( empty( $page ) ) {
			return;
		}

		$global_settings = new Rop_Global_Settings;
		$settings = new Rop_Settings_Model;

		if ( $global_settings->license_type() <= 0 && $settings->get_instant_sharing() ) {
			echo '<div class="misc-pub-section  " style="font-size: 13px;text-align: center;line-height: 1.7em;color: #888;"><span class="dashicons dashicons-lock"></span>' .
				__(
					'Instant social sharing is available on the extended version for ',
					'tweet-old-post'
				) . '<a href="' . ROP_PRO_URL . '" target="_blank">Revive Old Posts </a>
						</div>';
		}
	}

	/**
	 * The publish now Cron Job for the plugin.
	 *
	 * @since   8.1.0
	 * @access  public
	 */
	public function rop_cron_job_publish_now() {
		$queue           = new Rop_Queue_Model();
		$services_model  = new Rop_Services_Model();
		$logger          = new Rop_Logger();
		$service_factory = new Rop_Services_Factory();

		$queue_stack = $queue->build_queue_publish_now();
		$logger->info( 'Fetching publish now queue', array( 'queue' => $queue_stack ) );
		foreach ( $queue_stack as $account => $events ) {
			foreach ( $events as $index => $event ) {
				$posts        = $event['posts'];
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

		$cron = new Rop_Cron_Helper();
		$cron->create_cron( false );

		foreach ( $queue_stack as $account => $events ) {
			foreach ( $events as $index => $event ) {
				/**
				 * Trigger share if we have an event in the past, and the timestamp of that event is in the last 15mins.
				 */
				if ( $event['time'] <= Rop_Scheduler_Model::get_current_time() ) {
					$posts = $event['posts'];
					$queue->remove_from_queue( $event['time'], $account );
					if ( ( Rop_Scheduler_Model::get_current_time() - $event['time'] ) < ( 15 * MINUTE_IN_SECONDS ) ) {
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
		$cron->create_cron( false );
	}

}
