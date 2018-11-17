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
	private $allowed_screens = array(
		'dashboard'   => 'TweetOldPost',
		'plugins'     => 'plugins',
		'settings' 		=>'options-general',
		'exclude'     => 'rop_content_filters',
		'publish_now' => array( 'post' ),
	);
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

	}

	/**
	 * Shows a notice for sites running PHP less than 5.6.
	 *
	 * @since    8.1.2
	 */
	public function rop_php_notice() {

		if ( version_compare( PHP_VERSION, '5.6.0', '<' ) ) {
			?>

			<div class="notice notice-error is-dismissible">
				<?php printf( __( '%1$s You\'re using a PHP version lower than 5.6! Revive Old Posts requires at least %2$sPHP 5.6%3$s to function properly. %4$sLearn more here%5$s. %6$s', 'tweet-old-post' ), '<p>', '<b>', '</b>', '<a href="https://docs.revive.social/article/947-how-to-update-your-php-version" target="_blank">', '</a>', '</p>' ); ?>
			</div>
			<?php

		}

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

		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );

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

			/**
			* Determines if the tutorial has run.
			*
			* @since   8.1.4
			* @access  public
			*/
			public function rop_get_tutorial_status(){
				( !empty( get_option( 'rop_tutorial_queued' ) ) ? true : false );
			}


			/**
			* Tutorial pointers for personal plan.
			*
			* @since   8.1.4
			* @access  public
			*/
			public function create_rop_license_activation_tutorial_first() {

				//Run activation pointer for Pro plugin if exists and activation has not run pointer
				if( ! class_exists( 'Rop_Pro' ) || get_option( 'rop_start_activation' ) ){
					return;
				}

				// if( ! class_exists( 'Rop_Pro' ) ){
				// 	return;
				// }


				$pointers = array(
					'pointers' => array(
						'settings'          => array(
							'target'       => '#menu-settings',
							'next'         => '',
							'next_trigger' => array(
								'target' => '#menu-settings',
								'event'  => 'click',
							),
							'options'      => array(
								'content'  => '<h3>' . esc_html__( 'Activate Your New Plugin', 'tweet-old-post' ) . '</h3>' .
								'<p>' . esc_html__( 'Click here to start activating Revive Old Posts(ROP).', 'tweet-old-post' ) . '</p>',
								'position' => array(
									'edge'  => 'left',
									'align' => 'right',
								),
							),
						),
					),
				);

				update_option( 'rop_start_activation', 1 );
				return $pointers;
			}

			/**
			* Tutorial pointers for personal plan.
			*
			* @since   8.1.4
			* @access  public
			*/
			public function create_rop_license_activation_tutorial_last() {

				//Only show if plugin was activated
				if ( get_option( 'rop_end_activation' ) ){
					return;
				};

				$pointers = array(
					'pointers' => array(
						'settings'          => array(
							'target'       => '#tweet_old_post_pro_license',
							'next'         => 'rop-menu',
							'next_trigger' => array(
								'target' => '#tweet_old_post_pro_license',
								'event'  => 'click',
							),
							'options'      => array(
								'content'  => '<h3>' . esc_html__( 'Enter License Key', 'tweet-old-post' ) . '</h3>' .
								'<p>' . __( sprintf('Grab your license key from your purchase history %shere%s. Then activate it.', '<a href="https://revive.social/your-purchases/" target="_blank">', '</a>'), 'tweet-old-post' ) . '</p>',
								'position' => array(
									'edge'  => 'left',
									'align' => 'right',
								),
							),
						),
						'rop-menu'        => array(
							'target'       => '#toplevel_page_TweetOldPost',
							'next'         => '',
							'next_trigger' => array(),
							'options'      => array(
								'content'  => '<h3>' . esc_html__( 'Learn How it Works', 'tweet-old-post' ) . '</h3>' .
								'<p>' . esc_html__( 'Then click here to get started with your new plugin.', 'tweet-old-post' ) . '</p>',
								'position' => array(
									'edge'  => 'left',
									'align' => 'right',
								),
							),
						),
					),
				);

				update_option( 'rop_end_activation', 1 );
				return $pointers;
			}

			/**
			* Tutorial pointers for personal plan.
			*
			* @since   8.1.4
			* @access  public
			*/
			public function create_rop_personal_plan_tutorial() {

				if( get_option( 'rop_tutorial_queued' ) ){
					return;
				}

				$pointers = array(
					'pointers' => array(
						'accounts'          => array(
							'target'       => '#accounts',
							'next'         => 'add-account',
							'next_trigger' => array(
								'target' => '#accounts',
								'event'  => 'click',
							),
							'options'      => array(
								'content'  => '<h3>' . esc_html__( 'Accounts Area', 'tweet-old-post' ) . '</h3>' .
								'<p>' . esc_html__( 'Your social media accounts will show here once connected.', 'tweet-old-post' ) . '</p>',
								'position' => array(
									'edge'  => 'top',
									'align' => 'left',
								),
							),
						),
						'add-account'        => array(
							'target'       => '#rop-add-account-btn',
							'next'         => 'general',
							'next_trigger' => array(
								'target' => '#rop-add-account-btn',
								'event'  => 'click',
							),
							'options'      => array(
								'content'  => '<h3>' . esc_html__( 'Adding Accounts', 'tweet-old-post' ) . '</h3>' .
								'<p>' . esc_html__( 'You can add your social media accounts by clicking this button. Let\'s do this later.', 'tweet-old-post' ) . '</p>',
								'position' => array(
									'edge'  => 'bottom',
									'align' => 'left',
								),
							),
						),
						'general'        => array(
							'target'       => '#generalsettings',
							'next'         => 'min-interval',
							'next_trigger' => array(
								'target' => '#generalsettings',
								'event'  => 'click',
							),
							'options'      => array(
								'content'  => '<h3>' . esc_html__( 'General Settings', 'tweet-old-post' ) . '</h3>' .
								'<p>' . esc_html__( 'This is the main configuration page of the plugin, we\'ll go through a few of the settings, click it now.', 'tweet-old-post' ) . '</p>',
								'position' => array(
									'edge'  => 'top',
									'align' => 'left',
								),
							),
						),
						'min-interval'        => array(
							'target'       => '#default_interval',
							'next'         => 'min-post-age',
							'next_trigger' => array(
								'target' => '#default_interval',
								'event'  => 'input click change',
							),
							'options'      => array(
								'content'  => '<h3>' . esc_html__( 'Time Between Shares', 'tweet-old-post' ) . '</h3>' .
								'<p>' . esc_html__( 'Here you can set how many hours you\'d like between shares.', 'tweet-old-post' ) . '</p>',
								'position' => array(
									'edge'  => 'left',
									'align' => 'right',
								),
							),
						),
						'min-post-age'        => array(
							'target'       => '#min_post_age',
							'next'         => 'max-post-age',
							'next_trigger' => array(
								'target' => '#min_post_age',
								'event'  => 'input click change',
							),
							'options'      => array(
								'content'  => '<h3>' . esc_html__( 'Minimum Post Age', 'tweet-old-post' ) . '</h3>' .
								'<p>' . esc_html__( 'Here you can set how old posts should be before they are eligible to be shared.', 'tweet-old-post' ) . '</p>',
								'position' => array(
									'edge'  => 'left',
									'align' => 'right',
								),
							),
						),
						'max-post-age'        => array(
							'target'       => '#max_post_age',
							'next'         => 'share-more-than-once',
							'next_trigger' => array(
								'target' => '#max_post_age',
								'event'  => 'input click change',
							),
							'options'      => array(
								'content'  => '<h3>' . esc_html__( 'Maximum Post Age', 'tweet-old-post' ) . '</h3>' .
								'<p>' . esc_html__( 'Here you can set the maximum age of posts that are eligible to be shared.', 'tweet-old-post' ) . '</p>' .
								'<p>'. esc_html__( 'E.g. setting this option to 15 would mean that posts older than 15 days will not be shared.', 'tweet-old-post' ) .'</p>',
								'position' => array(
									'edge'  => 'left',
									'align' => 'right',
								),
							),
						),
						'share-more-than-once'        => array(
							'target'       => '#share_more_than_once',
							'next'         => 'post-types',
							'next_trigger' => array(
								'target' => '#share_more_than_once',
								'event'  => 'input click change',
							),
							'options'      => array(
								'content'  => '<h3>' . esc_html__( 'Autopilot', 'tweet-old-post' ) . '</h3>' .
								'<p>' . esc_html__( 'Checking this option ensures that your posts share continuosly without stop.', 'tweet-old-post' ) . '</p>',
								'position' => array(
									'edge'  => 'left',
									'align' => 'right',
								),
							),
						),
						'post-types'        => array(
							'target'       => '#rop_post_types',
							'next'         => 'taxonomies',
							'next_trigger' => array(
								'target' => '#rop_post_types',
								'event'  => 'input click change',
							),
							'options'      => array(
								'content'  => '<h3>' . esc_html__( 'Post types', 'tweet-old-post' ) . '</h3>' .
								'<p>' . esc_html__( 'Rop works with any post type, from products to posts to custom post types.', 'tweet-old-post' ) . '</p>',
								'position' => array(
									'edge'  => 'left',
									'align' => 'right',
								),
							),
						),
						'taxonomies'        => array(
							'target'       => '#rop_taxonomies',
							'next'         => 'instant-share',
							'next_trigger' => array(
								'target' => '#rop_taxonomies',
								'event'  => 'input click change',
							),
							'options'      => array(
								'content'  => '<h3>' . esc_html__( 'Taxonomy filtering', 'tweet-old-post' ) . '</h3>' .
								'<p>' . esc_html__( 'Here you can set which WordPress taxonomies you\'d like to include/exclude from sharing.', 'tweet-old-post' ) . '</p>' .
								'<p>' . __( sprintf( '%sNote:%s', '<strong>', '</strong>' ), 'tweet-old-post' ) . '</p>' .
								'<p>' . __( sprintf( 'Selecting options here and %1$schecking%2$s the Exclude box will %1$sprevent%2$s posts in those taxonomies from sharing.', '<strong>', '</strong>' ), 'tweet-old-post' ) . '</p>' .
								'<p>' . __( sprintf( 'Selecting options here and leaving the Exclude box %1$sunchecked%2$s will %1$sonly share%2$s posts in those taxonomies.', '<strong>', '</strong>' ), 'tweet-old-post' ) . '</p>',
								'position' => array(
									'edge'  => 'left',
									'align' => 'right',
								),
							),
						),
						'instant-share'        => array(
							'target'       => '#rop_instant_share',
							'next'         => 'custom-share',
							'next_trigger' => array(
								'target' => '#rop_instant_share',
								'event'  => 'input click change',
							),
							'options'      => array(
								'content'  => '<h3>' . esc_html__( 'Share on Publish', 'tweet-old-post' ) . '</h3>' .
								'<p>' . esc_html__( 'ROP not only works on autopilot, it can also be used to push new posts to your social networks immediately!', 'tweet-old-post' ) . '</p>',
								'position' => array(
									'edge'  => 'left',
									'align' => 'right',
								),
							),
						),
						'custom-share'        => array(
							'target'       => '#rop_custom_share_msg',
							'next'         => 'post-format',
							'next_trigger' => array(
								'target' => '#rop_custom_share_msg',
								'event'  => 'input click change',
							),
							'options'      => array(
								'content'  => '<h3>' . esc_html__( 'Custom Messages', 'tweet-old-post' ) . '</h3>' .
								'<p>' . esc_html__( 'You can add multiple custom messages to individual posts! ROP will randomly select one to share.', 'tweet-old-post' ) . '</p>',
								'position' => array(
									'edge'  => 'left',
									'align' => 'right',
								),
							),
						),
						'post-format'        => array(
							'target'       => '#postformat',
							'next'         => 'custom-schedule',
							'next_trigger' => array(
								'target' => '#postformat',
								'event'  => 'input click change',
							),
							'options'      => array(
								'content'  => '<h3>' . esc_html__( 'Post Format', 'tweet-old-post' ) . '</h3>' .
								'<p>' . esc_html__( 'Once you\'ve connected an account(s) you\'ll be able to configure the settings for the account(s) here.', 'tweet-old-post' ) . '</p>',
								'position' => array(
									'edge'  => 'left',
									'align' => 'right',
								),
							),
						),
						'custom-schedule'        => array(
							'target'       => '#customschedule',
							'next'         => 'sharing-queue',
							'next_trigger' => array(
								'target' => '#customschedule',
								'event'  => 'click change',
							),
							'options'      => array(
								'content'  => '<h3>' . esc_html__( 'Custom Schedule', 'tweet-old-post' ) . '</h3>' .
								'<p>' . esc_html__( 'Custom scheduling allows you to refine the post times and days of your posts.', 'tweet-old-post' ) . '</p>',
								'<p>' . __( sprintf( '%sLearn More Here%s', '<a href="#" target="_blank">', '</a>' ), 'tweet-old-post' ) . '</p>',
								'position' => array(
									'edge'  => 'left',
									'align' => 'right',
								),
							),
						),
						'sharing-queue'        => array(
							'target'       => '#sharingqueue',
							'next'         => 'log',
							'next_trigger' => array(
								'target' => '#sharingqueue',
								'event'  => 'click change',
							),
							'options'      => array(
								'content'  => '<h3>' . esc_html__( 'Sharing Queue', 'tweet-old-post' ) . '</h3>' .
								'<p>' . esc_html__( 'You\'ll be able to have look at the posts scheduled to go out by ROP. You can even schedule or ban them from sharing in the future!', 'tweet-old-post' ) . '</p>' .
								'<p>' . __( sprintf( '%s%sLearn More Here%s%s', '<strong>', '<a href="#" target="_blank">', '</a>', '</strong>'), 'tweet-old-post' ) . '</p>',
								'position' => array(
									'edge'  => 'left',
									'align' => 'right',
								),
							),
						),
						'log'        => array(
							'target'       => '#logs',
							'next'         => 'start-stop',
							'next_trigger' => array(
								'target' => '',
								'event'  => '',
							),
							'options'      => array(
								'content'  => '<h3>' . esc_html__( 'Share Log', 'tweet-old-post' ) . '</h3>' .
								'<p>' . esc_html__( 'You can track the success and failings of your shares here.', 'tweet-old-post' ) . '</p>' .
								'<p>' . __( sprintf( 'The resolution to most of these possible errors can be found %s%sHere%s%s', '<strong>', '<a href="https://docs.revive.social/" target="_blank">', '</a>', '</strong>' ), 'tweet-old-post' ) . '</p>',
								'position' => array(
									'edge'  => 'left',
									'align' => 'right',
								),
							),
						),
						'start-stop'        => array(
							'target'       => '#rop_start_stop_btn',
							'next'         => '',
							'next_trigger' => array(
								'target' => '',
								'event'  => '',
							),
							'options'      => array(
								'content'  => '<h3>' . esc_html__( 'Start & Forget', 'tweet-old-post' ) . '</h3>' .
								'<p>' . esc_html__( 'Once you\'ve connected your accounts and setup their Post Format settings, use this button to start the plugin.', 'tweet-old-post' ) . '</p>' .
								'<p>' . __( sprintf( 'The resolution to most of these possible errors can be found %s%sHere%s%s', '<strong>', '<a href="https://docs.revive.social/" target="_blank">', '</a>', '</strong>' ), 'tweet-old-post' ) . '</p>',
								'position' => array(
									'edge'  => 'right',
									'align' => 'left',
								),
							),
						),
					),
				);

				update_option( 'rop_tutorial_queued', 1 );
				return $pointers;
			}

			/**
			* Enqueus the pointer's scripts.
			*
			* @since   8.1.4
			* @access  public
			*/
			public function rop_enqueue_pointers() {

				if ( ! $screen = get_current_screen() ) {
					return;
				}

				switch ( $screen->id ) {
					case 'plugins':
					$pointers = $this->create_rop_license_activation_tutorial_first();
					break;
					case 'options-general':
					$pointers = $this->create_rop_license_activation_tutorial_last();
					break;
					case 'toplevel_page_TweetOldPost':
					$pointers = $this->create_rop_personal_plan_tutorial();
					break;
				}

				$pointers = wp_json_encode( $pointers );

				?>
				<script type="text/javascript">
				jQuery( function( $ ) {
					var rop_pointer = <?php echo $pointers ?>;

					setTimeout( init_rop_pointer, 800 );

					function init_rop_pointer() {
						$.each( rop_pointer.pointers, function( i ) {
							show_rop_pointer( i );
							return false;
						});
					}

					function show_rop_pointer( id ) {
						var pointer = rop_pointer.pointers[ id ];
						var options = $.extend( pointer.options, {
							pointerClass: 'wp-pointer rop-pointer',
							close: function() {
								if ( pointer.next ) {
									show_rop_pointer( pointer.next );
								}
							},
							buttons: function( event, t ) {
								if (pointer.next !== 'min-interval') {

								var close   = " <?php echo esc_js( __( 'Dismiss', 'tweet-old-post' ) ) ?>",
								next    = "<?php echo esc_js( __( 'Next', 'tweet-old-post' ) ) ?>",

								button  = $( '<a class=\"close\" href=\"#\">' + close + '</a>' ),
								button2 = $( '<a class=\"button button-primary next\" href=\"#\">' + next + '</a>' ),
								wrapper = $( '<div class=\"rop-pointer-buttons\" />' );

								button.bind( 'click.pointer', function(e) {
									e.preventDefault();
									t.element.pointer('destroy');
								});

								button2.bind( 'click.pointer', function(e) {
									e.preventDefault();
									t.element.pointer('close');

									switch( pointer.next ){
										case 'activate-rop':
										window.scrollBy(0, 400);
										break;
										case 'rop-menu':
										window.scrollBy(0, 400);
										break;
										case 'post-types':
										window.scrollBy(0, 400);
										break;
										case 'custom-share':
										window.scrollBy(0, 100);
										break;
										case 'post-format':
										window.scrollBy(0, -550);
										break;
									}
								});

								wrapper.append( button );
								wrapper.append( button2 );

								return wrapper;
							}
						},
						} );

						var this_pointer = $( pointer.target ).pointer( options );
						this_pointer.pointer( 'open' );

						if ( pointer.next_trigger ) {
							$( pointer.next_trigger.target ).on( pointer.next_trigger.event, function() {
								setTimeout( function() { this_pointer.pointer( 'close' ); }, 400 );
							});
						}
					}
				});
				</script>
				<?php

			}

}
