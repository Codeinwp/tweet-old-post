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
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    8.0.0
	 */
	public function __construct( $plugin_name = '', $version = '' ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->set_allowed_screens();
		add_action( 'admin_notices', array( &$this, 'display_global_status_warning' ) );
	}


	/**
	 * Will display an admin notice if there are ROP_STATUS_ALERT consecutive errors.
	 *
	 * @since 8.4.4
	 * @access public
	 */
	public function display_global_status_warning() {
		$log                  = new Rop_Logger();
		$is_status_logs_alert = $log->is_status_error_necessary(); // true | false
		if ( $is_status_logs_alert ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p>
					<strong><?php echo esc_html( Rop_I18n::get_labels( 'general.plugin_name' ) ); ?></strong>:
					<?php echo Rop_I18n::get_labels( 'general.status_error_global' ); ?>
				</p>
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
	 * Check if a shortener is in use.
	 *
	 * @param string $shortener The shortener to check.
	 *
	 * @return bool If shortener is in use.
	 * @since    8.1.5
	 */
	public function check_shortener_service( $shortener ) {

		$model       = new Rop_Post_Format_Model;
		$post_format = $model->get_post_format();

		$shorteners = array();

		foreach ( $post_format as $account_id => $option ) {
			$shorteners[] = $option['short_url_service'];
		}

		return ( in_array( $shortener, $shorteners ) ) ? true : false;
	}

	/**
	 * Show notice to upgrade bitly.
	 *
	 * @since    8.1.5
	 */
	public function bitly_shortener_upgrade_notice() {

		if ( ! $this->check_shortener_service( 'bit.ly' ) ) {
			return;
		}

		$bitly = get_option( 'rop_shortners_bitly' );

		if ( array_key_exists( 'generic_access_token', $bitly['bitly_credentials'] ) ) {
			return;
		}
		?>
		<div class="notice notice-error is-dismissible">
			<?php echo sprintf( __( '%1$s%2$sRevive Old Posts:%3$s Please upgrade your Bit.ly keys. See this %4$sarticle for instructions.%5$s%6$s', 'tweet-old-post' ), '<p>', '<b>', '</b>', '<a href="https://docs.revive.social/article/976-how-to-connect-bit-ly-to-revive-old-posts" target="_blank">', '</a>', '</p>' ); ?>
		</div>
		<?php
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    8.1.5
	 */
	private function set_allowed_screens() {

		$general_settings = new Rop_Settings_Model;

		$post_types           = wp_list_pluck( $general_settings->get_selected_post_types(), 'value' );
		$attachment_post_type = array_search( 'attachment', $post_types );

		if ( ! empty( $attachment_post_type ) ) {
			unset( $post_types[ $attachment_post_type ] );
		}

		$this->allowed_screens = array(
			'dashboard'   => 'TweetOldPost',
			'exclude'     => 'rop_content_filters',
			'publish_now' => $post_types,
		);

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
	 * Whether we will display the toast message related to facebook
	 *
	 * @return mixed
	 * @since 8.4.3
	 */
	private function facebook_exception_toast_display() {
		$show_the_toast = get_option( 'rop_facebook_domain_toast', 'no' );
		// Will comment this return for now, might be of use later on.
		// return filter_var( $show_the_toast, FILTER_VALIDATE_BOOLEAN );
		return false;
	}

	/**
	 * Method used to decide whether or not to limit taxonomy select
	 *
	 * @return  bool
	 * @since   8.5.0
	 * @access  public
	 */
	public function limit_tax_dropdown_list() {
		$installed_at_version = get_option( 'rop_first_install_version' );
		if ( empty( $installed_at_version ) ) {
			return 0;
		}
		if ( version_compare( $installed_at_version, '8.5.3', '>=' ) ) {
			return 1;
		}

		return 0;
	}

	/**
	 * Method used to decide whether or not to limit exclude posts.
	 *
	 * @return  bool
	 * @since   8.5.4
	 * @access  public
	 */
	public function limit_exclude_list() {
		$installed_at_version = get_option( 'rop_first_install_version' );
		if ( empty( $installed_at_version ) ) {
			return 0;
		}
		if ( version_compare( $installed_at_version, '8.5.4', '>=' ) ) {
			return 1;
		}

		return 0;
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
		wp_enqueue_media();
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
		$li_service      = new Rop_Linkedin_Service();
		$tmblr_service   = new Rop_Tumblr_Service();
		$active_accounts = $services->get_active_accounts();

		$added_services = $services->get_authenticated_services();
		$added_networks = 0;
		if ( $added_services ) {
			$added_networks = count( array_unique( wp_list_pluck( array_values( $added_services ), 'service' ) ) );
		}

		$global_settings = new Rop_Global_Settings();
		$settings        = new Rop_Settings_Model();

		$array_nonce['license_type']            = $global_settings->license_type();
		$array_nonce['fb_domain_toast_display'] = $this->facebook_exception_toast_display();
		$array_nonce['labels']                  = Rop_I18n::get_labels();
		$array_nonce['upsell_link']             = Rop_I18n::UPSELL_LINK;
		$array_nonce['pro_installed']           = ( defined( 'ROP_PRO_VERSION' ) ) ? true : false;
		$array_nonce['staging']                 = $this->rop_site_is_staging();
		$array_nonce['show_li_app_btn']         = $li_service->rop_show_li_app_btn();
		$array_nonce['show_tmblr_app_btn']      = $tmblr_service->rop_show_tmblr_app_btn();
		$array_nonce['debug']                   = ( ( ROP_DEBUG ) ? 'yes' : 'no' );
		$array_nonce['tax_apply_limit']         = $this->limit_tax_dropdown_list();
		$array_nonce['exclude_apply_limit']     = $this->limit_exclude_list();
		$array_nonce['publish_now']             = array(
			'action'   => $settings->get_instant_sharing_by_default(),
			'accounts' => $active_accounts,
		);
		$array_nonce['added_networks']          = $added_networks;

		$admin_url = get_admin_url( get_current_blog_id(), 'admin.php?page=TweetOldPost' );
		$token     = get_option( ROP_INSTALL_TOKEN_OPTION );
		$signature = md5( $admin_url . $token );

		$rop_auth_app_data = array(
			'adminEmail'          => base64_encode( get_option( 'admin_email' ) ),
			'authAppUrl'          => ROP_AUTH_APP_URL,
			'authAppFacebookPath' => ROP_APP_FACEBOOK_PATH,
			'authAppTwitterPath'  => ROP_APP_TWITTER_PATH,
			'authAppLinkedInPath' => ROP_APP_LINKEDIN_PATH,
			'authAppBufferPath'   => ROP_APP_BUFFER_PATH,
			'authAppTumblrPath'   => ROP_APP_TUMBLR_PATH,
			'authAppGmbPath'        => ROP_APP_GMB_PATH,
			'authToken'           => $token,
			'adminUrl'            => urlencode( $admin_url ),
			'authSignature'       => $signature,
		);

		if ( 'publish_now' === $page ) {
			$array_nonce['publish_now'] = apply_filters( 'rop_publish_now_attributes', $array_nonce['publish_now'] );
			wp_register_script( $this->plugin_name . '-publish_now', ROP_LITE_URL . 'assets/js/build/publish_now' . ( ( ROP_DEBUG ) ? '' : '.min' ) . '.js', array(), ( ROP_DEBUG ) ? time() : $this->version, false );
		}

		wp_localize_script( $this->plugin_name . '-' . $page, 'ropApiSettings', $array_nonce );
		wp_localize_script( $this->plugin_name . '-' . $page, 'ROP_ASSETS_URL', ROP_LITE_URL . 'assets/' );
		wp_localize_script( $this->plugin_name . '-' . $page, 'ropAuthAppData', $rop_auth_app_data );
		wp_enqueue_script( $this->plugin_name . '-' . $page );

	}

	/**
	 * Set our supported mime types.
	 *
	 * @return array
	 * @since   8.1.0
	 * @access  public
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
	 * @return    bool   true/false
	 * @since     8.0.4
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
		if ( ( empty( $code ) && empty( $state ) ) && $network !== 'twitter' ) {
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
			case 'buffer':
				$buffer_service = new Rop_Buffer_Service();
				$buffer_service->authorize();
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

		add_submenu_page(
			'TweetOldPost',
			__( 'Roadmap', 'tweet-old-post' ),
			__( 'Plugin Roadmap', 'tweet-old-post' ),
			'manage_options',
			'https://trello.com/b/svAZqXO1/roadmap-revive-old-posts'
		);
	}

	/**
	 * Open roadmap in new tab
	 *
	 * @since   8.5.0
	 * @access  public
	 */
	function rop_roadmap_new_tab() {
		?>
		<script type="text/javascript">
		   jQuery( document ).ready( function ( $ ) {
			   $( "ul#adminmenu a[href$='https://trello.com/b/svAZqXO1/roadmap-revive-old-posts']" ).attr( 'target', '_blank' );
		   } );
		</script>
		<?php
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
		$settings        = new Rop_Settings_Model;

		$services        = new Rop_Services_Model();
		$active_accounts = $services->get_active_accounts();

		if ( $settings->get_instant_sharing() && count( $active_accounts ) >= 2 && ! defined( 'ROP_PRO_VERSION' ) ) {
			echo '<div class="misc-pub-section  " style="font-size: 11px;text-align: center;line-height: 1.7em;color: #888;"><span class="dashicons dashicons-lock"></span>' .
				__(
					'Share to more accounts by upgrading to the extended version for ',
					'tweet-old-post'
				) . '<a href="' . ROP_PRO_URL . '" target="_blank">Revive Old Posts </a>
						</div>';
		}
	}

	/**
	 * Creates publish now metabox.
	 *
	 * @since   8.5.0
	 * @access  public
	 */
	public function rop_publish_now_metabox() {

		$settings_model = new Rop_Settings_Model();
		// Get selected post types from General settings
		$screens = wp_list_pluck( $settings_model->get_selected_post_types(), 'value' );

		if ( empty( $screens ) ) {
			return;
		}

		if ( ! $settings_model->get_instant_sharing() ) {
			return;
		}

		foreach ( $screens as $screen ) {
			add_meta_box(
				'rop_publish_now_metabox',
				'Revive Old Posts',
				array( $this, 'rop_publish_now_metabox_html' ),
				$screen,
				'side',
				'high'
			);
		}
	}

	/**
	 * Publish now metabox html.
	 *
	 * @since   8.5.0
	 * @access  public
	 */
	public function rop_publish_now_metabox_html() {

		wp_nonce_field( 'rop_publish_now_nonce', 'rop_publish_now_nonce' );
		include_once ROP_LITE_PATH . '/includes/admin/views/publish_now.php';

		$this->publish_now_upsell();

	}


	/**
	 * Adds the publish now buttons.
	 */
	public function add_publish_actions() {
		global $post, $pagenow;

		$settings_model  = new Rop_Settings_Model();
		$global_settings = new Rop_Global_Settings();

		$post_types = wp_list_pluck( $settings_model->get_selected_post_types(), 'value' );
		if ( in_array( $post->post_type, $post_types ) && in_array(
			$pagenow,
			array(
				'post.php',
				'post-new.php',
			)
		) && ( ( method_exists( $settings_model, 'get_instant_sharing' ) && $settings_model->get_instant_sharing() ) || ! method_exists( $settings_model, 'get_instant_sharing' ) )
		) {
			wp_nonce_field( 'rop_publish_now_nonce', 'rop_publish_now_nonce' );
			include_once ROP_LITE_PATH . '/includes/admin/views/publish_now.php';
		}
	}

	/**
	 * Publish now attributes to be provided to the javascript.
	 *
	 * @param array $default The default attributes.
	 */
	public function publish_now_attributes( $default ) {
		global $post;

		if ( 'publish' === $post->post_status ) {
			$default['action'] = 'yes' === get_post_meta( $post->ID, 'rop_publish_now', true );
		}
		$default['active'] = get_post_meta( $post->ID, 'rop_publish_now_accounts', true );

		return $default;
	}

	/**
	 * Publish now, if enabled.
	 *
	 * @param int $post_id The post ID.
	 */
	public function maybe_publish_now( $post_id ) {
		if ( ! isset( $_POST['rop_publish_now_nonce'] ) || ! wp_verify_nonce( $_POST['rop_publish_now_nonce'], 'rop_publish_now_nonce' ) ) {
			return;
		}

		if ( get_post_status( $post_id ) !== 'publish' ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['publish_now'] ) || empty( $_POST['publish_now'] ) ) {
			delete_post_meta( $post_id, 'rop_publish_now' );
			delete_post_meta( $post_id, 'rop_publish_now_accounts' );

			return;
		}

		$enabled = $_POST['publish_now_accounts'];

		if ( ! is_array( $enabled ) ) {
				$enabled = array();
		}

		$services = new Rop_Services_Model();
		$settings = new Rop_Settings_Model();

		$active   = array_keys( $services->get_active_accounts() );
		// has something been added extra?
		$extra = array_diff( $enabled, $active );
		// reject the extra.
		$enabled = array_diff( $enabled, $extra );

		$instant_share_custom_content = array();

		foreach ( $enabled as $account_id ) {
				$custom_message = ! empty( $_POST[ $account_id ] ) ? $_POST[ $account_id ] : '';
				$instant_share_custom_content[ $account_id ] = $custom_message;
		}

		// If user wants to run this operation on page refresh instead of via Cron.
		if ( $settings->get_true_instant_share() ) {
			$this->rop_cron_job_publish_now( $post_id, $instant_share_custom_content );
			return;
		}

		update_post_meta( $post_id, 'rop_publish_now', 'yes' );
		update_post_meta( $post_id, 'rop_publish_now_accounts', $instant_share_custom_content );

		if ( ! $enabled ) {
			return;
		}

		$cron = new Rop_Cron_Helper();
		$cron->manage_cron( array( 'action' => 'publish-now' ) );
	}

	/**
	 * Method to share future scheduled WP posts to social media on publish.
	 *
	 * @param object $post The post object.
	 *
	 * @access  public
	 * @since   8.5.2
	 */
	public function share_scheduled_future_post( $post ) {

		$post_id = $post->ID;
		$settings            = new Rop_Settings_Model();
		$selected_post_types = wp_list_pluck( $settings->get_selected_post_types(), 'value' );

		if ( ! $settings->get_instant_share_future_scheduled() ) {
			return;
		}

		if ( ! in_array( $post->post_type, $selected_post_types ) ) {
			return;
		}

		$global_settings = new Rop_Global_Settings();

		$services = new Rop_Services_Model();
		$active_accounts = array_keys( $services->get_active_accounts() );

		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
		$rop_active_status = in_array( 'tweet-old-post-pro/tweet-old-post-pro.php', $active_plugins );

		// this would only be possible in Pro plugin
		if ( $global_settings->license_type() > 0 && $rop_active_status ) {

			$logger          = new Rop_Logger();
			// Get the current plugin options.
			$options = get_option( 'rop_data' );

			$social_accounts = array();
			$post_formats = array_key_exists( 'post_format', $options ) ? $options['post_format'] : '';
			$account_from_formats = array_keys( $post_formats );

			if ( empty( $post_formats ) ) {
				$logger->alert_error( Rop_I18n::get_labels( 'post_format.no_post_format_error' ) );
				return;
			}

			foreach ( $post_formats as $key => $value ) {

				// check if an account is active, but has no post format saved in the DB
				 // if it doesn't then sharing scheduled posts on publish would not work for that account
				foreach ( $active_accounts as $account ) {
						$active_social_network = ucfirst( explode( '_', $account )[0] );
					if ( ! in_array( $account, $account_from_formats ) ) {
						$logger->alert_error( Rop_I18n::get_labels( 'post_format.active_account_no_post_format_error' ) . $active_social_network );
					}
				}

				if ( ! array_key_exists( 'taxonomy_filter', $value ) ) {
					// share to accounts where no filters are selected
					$social_accounts[] = $key;
					continue;
				}

				// get account specific taxonomy filter
				$taxonomy_filter = array_column( $value['taxonomy_filter'], 'tax', 'value' );
				$taxonomies_are_excluded = $value['exclude_taxonomies'];

				$taxonomies_slug = array_values( $taxonomy_filter );
				$taxonomies_ids = array_keys( $taxonomy_filter );

				// get term ids for the taxonomies selected on General Settings of ROP that are present in the current post
				$post_term_ids = wp_get_post_terms( $post_id, $taxonomies_slug, array( 'fields' => 'ids' ) );

				// get the common term ids between what's assigned to the post and what's selected in General Settings
				$common = array_intersect( $taxonomies_ids, $post_term_ids );

				// if the post contains any of the taxonomies that are excluded, bail
				if ( count( $common ) > 0 && $taxonomies_are_excluded ) {
					continue;
				}
				// if the post doesn't contain any of the selected taxonomies that are whitelisted for posting, bail
				if ( count( $common ) < 1 && ! $taxonomies_are_excluded ) {
					continue;
				}

				$social_accounts[] = $key;

			}

			// accounts to share to
			$active_accounts = array_intersect( $active_accounts, $social_accounts );

			if ( empty( $active_accounts ) ) {
				return;
			}
		}

		// get taxonomies selected in general settings
		$selected_taxonomies = $settings->get_selected_taxonomies();

		// only run if free version
		if ( ! empty( $selected_taxonomies ) && ( $global_settings->license_type() < 1 || ! $rop_active_status ) ) {

			$taxonomies = array_column( $selected_taxonomies, 'tax', 'value' );

			$taxonomies_slug = array_values( $taxonomies );
			$taxonomies_ids = array_keys( $taxonomies );

			// get term ids for the taxonomies selected on General Settings of ROP that are present in the current post
			$post_term_ids = wp_get_post_terms( $post_id, $taxonomies_slug, array( 'fields' => 'ids' ) );

			// get the common term ids between what's assigned to the post and what's selected in General Settings
			$common = array_intersect( $taxonomies_ids, $post_term_ids );

			// check if "Exclude" is checked
			$taxonomies_are_excluded = $settings->get_exclude_taxonomies();

			// if the post contains any of the taxonomies that are excluded, bail
			if ( count( $common ) > 0 && $taxonomies_are_excluded ) {
				return;
			}
			// if the post doesn't contain any of the selected taxonomies that are whitelisted for posting, bail
			if ( count( $common ) < 1 && ! $taxonomies_are_excluded ) {
				return;
			}
		}

		$this->rop_cron_job_publish_now( $post_id, $active_accounts, true );
	}


	/**
	 * The publish now Cron Job for the plugin.
	 *
	 * @since   8.1.0
	 * @access  public
	 * @param int   $post_id the Post ID.
	 * @param array $accounts_data The accounts data, may either be the accounts the user has selected to share the post to (by clicking the instant sharing checkbox on post edit screen, would also contain the custom share message if any was entered), or an array of active accounts to share to by the share_scheduled_future_post() method.
	 * @param bool  $is_future_post Whether method was called by share_scheduled_future_post() method.
	 */
	public function rop_cron_job_publish_now( $post_id = '', $accounts_data = array(), $is_future_post = false ) {
		$queue           = new Rop_Queue_Model();
		$services_model  = new Rop_Services_Model();
		$logger          = new Rop_Logger();
		$service_factory = new Rop_Services_Factory();
		$settings = new Rop_Settings_Model();

		$queue_stack = $queue->build_queue_publish_now( $post_id, $accounts_data, $is_future_post, $settings->get_true_instant_share() );
		$logger->info( 'Fetching publish now queue', array( 'queue' => $queue_stack ) );
		foreach ( $queue_stack as $account => $events ) {
			foreach ( $events as $index => $event ) {
				$post    = $event['post'];
				$message = ! empty( $event['custom_instant_share_message'] ) ? $event['custom_instant_share_message'] : '';
				$account_data = $services_model->find_account( $account );
				try {
					$service = $service_factory->build( $account_data['service'] );
					$service->set_credentials( $account_data['credentials'] );
					foreach ( $post as $post_id ) {
						$post_data = $queue->prepare_post_object( $post_id, $account );
						$custom_instant_share_message = $message;
						if ( ! empty( $custom_instant_share_message ) ) {
							$post_data['content'] = $custom_instant_share_message;
						}
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
	 * Used for Cron Job sharing that will run once.
	 *
	 * @since 8.5.0
	 */
	public function rop_cron_job_once() {
		$this->rop_cron_job();

	}

	/**
	 * The Cron Job for the plugin.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function rop_cron_job() {
		$queue           = new Rop_Queue_Model();
		$queue_stack     = $queue->build_queue();
		$services_model  = new Rop_Services_Model();
		$logger          = new Rop_Logger();
		$service_factory = new Rop_Services_Factory();
		$refresh_rop_data = false;

		$cron = new Rop_Cron_Helper();
		$cron->create_cron( false );

		foreach ( $queue_stack as $account => $events ) {

			if ( strpos( json_encode( $queue_stack ), 'gmb_' ) !== false ) {
				$refresh_rop_data = true;
			}

			foreach ( $events as $index => $event ) {
				/**
				 * Trigger share if we have an event in the past, and the timestamp of that event is in the last 15mins.
				 */
				if ( $event['time'] <= Rop_Scheduler_Model::get_current_time() ) {
					$posts = $event['posts'];
					$logger->info( $account );
					// if current account is not gmb, and gmb is active, refresh options data in case gmb updated it's options
					if ( $refresh_rop_data && ( strpos( $account, 'gmb_' ) === false ) ) {
						$queue->remove_from_queue( $event['time'], $account, true );
						$logger->info( 'refreshed rop data' );
					} else {
						$queue->remove_from_queue( $event['time'], $account );
						$logger->info( 'did not refresh rop data' );
					}

					if ( ( Rop_Scheduler_Model::get_current_time() - $event['time'] ) < ( 15 * MINUTE_IN_SECONDS ) ) {
						$account_data = $services_model->find_account( $account );
						try {
							$service = $service_factory->build( $account_data['service'] );
							$service->set_credentials( $account_data['credentials'] );
							foreach ( $posts as $post ) {
								$post_shared = $account . '_post_id_' . $post;
								if ( get_option( 'rop_last_post_shared' ) === $post_shared ) {
									$logger->info( ucfirst( $account_data['service'] ) . ': ' . Rop_I18n::get_labels( 'sharing.post_already_shared' ) );
									continue;
								}
								$post_data = $queue->prepare_post_object( $post, $account );
								$logger->info( 'Posting', array( 'extra' => $post_data ) );
								$service->share( $post_data, $account_data );
								update_option( 'rop_last_post_shared', $post_shared );
							}
						} catch ( Exception $exception ) {
							$error_message = sprintf( Rop_I18n::get_labels( 'accounts.service_error' ), $account_data['service'] );
							$logger->alert_error( $error_message . ' Error: ' . $exception->getMessage() );
						}
					}
				}
			}
		}
		$cron->create_cron( false );
	}

	/**
	 * Linkedin API upgrade notice.
	 *
	 * @since   8.2.3
	 * @access  public
	 */
	public function rop_linkedin_api_v2_notice() {

		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		// This option was introduced the same time we updated Linkedin API to v2.
		// Gets created on plugin activation hook, old installs would not have this option.
		// So we return in case this is a brand new install.
		if ( ! empty( get_option( 'rop_first_install_version' ) ) ) {
			return;
		}

		$user_id = get_current_user_id();

		if ( get_user_meta( $user_id, 'rop-linkedin-api-notice-dismissed' ) ) {
			return;
		}

		$show_notice = false;

		$services_model = new Rop_Services_Model();

		$services = $services_model->get_authenticated_services();

		foreach ( $services as $key => $value ) {

			if ( $value['service'] == 'linkedin' ) {
				$show_notice = true;
				break;
			}
		}

		if ( $show_notice == false ) {
			return;
		}

		?>
		<div class="notice notice-error">
			<?php echo sprintf( __( '%1$s%2$sRevive Old Posts:%3$s The Linkedin API Has been updated. You need to reconnect your LinkedIn account to continue posting to LinkedIn. Please see %4$sthis article for instructions.%5$s%6$s%7$s', 'tweet-old-post' ), '<p>', '<b>', '</b>', '<a href="https://docs.revive.social/article/1040-how-to-move-to-linkedin-api-v2" target="_blank">', '</a>', '<a style="float: right;" href="?rop-linkedin-api-notice-dismissed">Dismiss</a>', '</p>' ); ?>

		</div>
		<?php

	}

	/**
	 * Dismiss Linkedin API upgrade notice.
	 *
	 * @since   8.2.3
	 * @access  public
	 */
	public function rop_dismiss_linkedin_api_v2_notice() {
		$user_id = get_current_user_id();
		if ( isset( $_GET['rop-linkedin-api-notice-dismissed'] ) ) {
			add_user_meta( $user_id, 'rop-linkedin-api-notice-dismissed', 'true', true );
		}

	}


	/**
	 * If the option "rop_is_sharing_cron_active" value is off/false/no then the WP Cron Jobs will be cleared.
	 *
	 * @since 8.5.0
	 */
	public function check_cron_status() {
		$key             = 'rop_is_sharing_cron_active';
		$should_cron_run = get_option( $key, 'yes' );
		$should_cron_run = filter_var( $should_cron_run, FILTER_VALIDATE_BOOLEAN );
		if ( false === $should_cron_run ) {
			wp_clear_scheduled_hook( Rop_Cron_Helper::CRON_NAMESPACE );
			wp_clear_scheduled_hook( Rop_Cron_Helper::CRON_NAMESPACE_ONCE );
		}
	}

	/**
	 * WordPress Cron disabled notice.
	 *
	 * @since   8.2.5
	 * @access  public
	 */
	public function rop_wp_cron_notice() {

		if ( ! defined( 'DISABLE_WP_CRON' ) ) {
			return;
		}

		$user_id = get_current_user_id();

		if ( get_user_meta( $user_id, 'rop-wp-cron-notice-dismissed' ) ) {
			return;
		}

		if ( DISABLE_WP_CRON ) {

			?>
			<div class="notice notice-error">
				<?php echo sprintf( __( '%1$s%2$sRevive Old Posts:%3$s The WordPress Cron seems is disabled on your website. This can cause sharing issues with Revive Old Posts. If sharing is not working, then see %4$shere for solutions.%5$s%6$s%7$s', 'tweet-old-post' ), '<p>', '<b>', '</b>', '<a href="https://docs.revive.social/article/686-fix-revive-old-post-not-posting" target="_blank">', '</a>', '<a style="float: right;" href="?rop-wp-cron-notice-dismissed">Dismiss</a>', '</p>' ); ?>

			</div>
			<?php

		}

	}

	/**
	 * Dismiss WordPress Cron disabled notice.
	 *
	 * @since   8.2.5
	 * @access  public
	 */
	public function rop_dismiss_cron_disabled_notice() {

		$user_id = get_current_user_id();
		if ( isset( $_GET['rop-wp-cron-notice-dismissed'] ) ) {
			add_user_meta( $user_id, 'rop-wp-cron-notice-dismissed', 'true', true );
		}

	}

	/**
	 * Migrate the taxonomies from General Settings to Post Format for Pro users.
	 *
	 * @since 8.5.4
	 */
	public function migrate_taxonomies_to_post_format() {

		$installed_at_version = get_option( 'rop_first_install_version' );
		// If this option does not exist, stop the process.
		if ( empty( $installed_at_version ) ) {
			return;
		}

		// Apply the migration process only for users that installed an older version.
		if ( version_compare( $installed_at_version, '8.5.4', '>' ) ) {
			return;
		}

		// Fetch the plugin global settings.
		$global_settings = new Rop_Global_Settings();

		// If there is no pro licence, cut process early.
		if ( $global_settings->license_type() < 1 ) {
			return;
		}

		// If any type of Pro is installed and active.
		if ( $global_settings->license_type() > 0 ) {
			// Get the current plugin options.
			$option = get_option( 'rop_data' );

			// Get the custom options.
			// If this option exists, then the migration took place, and it will not happen again.
			// Should return false the first time as it does not exist.
			$update_took_place = get_option( 'rop_data_migrated_tax' );

			// If the update already took place and the general settings array value does not exist, cut process early.
			if ( ! empty( $update_took_place ) && ! isset( $option['general_settings'] ) ) {
				return;
			}

			$general_settings = array();
			// Making sure the option we need, exists.
			if ( empty( $update_took_place ) && isset( $option['general_settings'] ) ) {
				$general_settings = $option['general_settings'];

				$selected_taxonomies = array();
				$exclude_taxonomies  = '';
				if ( isset( $general_settings['selected_taxonomies'] ) ) {
					// Get the selected Taxonomies from General Settings tab.
					$selected_taxonomies = $general_settings['selected_taxonomies'];
				}

				// Making sure to check "Excluded" if the main General Tab ahs it checked.
				if ( isset( $general_settings['exclude_taxonomies'] ) && ! empty( $general_settings['exclude_taxonomies'] ) ) {
					$exclude_taxonomies = $general_settings['exclude_taxonomies'];
				}

				// If there are any taxonomies selected in the general tab.
				if ( ! empty( $selected_taxonomies ) ) {

					if ( isset( $option['post_format'] ) && ! empty( $option['post_format'] ) ) {

						foreach ( $option['post_format'] as &$social_media_account_data ) {
							// If the options exists in Post Format but it's empty or,
							// If the option does not exist at all.
							if (
								! isset( $social_media_account_data['taxonomy_filter'] ) ||
								(
									isset( $social_media_account_data['taxonomy_filter'] ) &&
									empty( $social_media_account_data['taxonomy_filter'] )
								)
							) {
								// Add the taxonomies to all social media accounts.
								$social_media_account_data['taxonomy_filter'] = $selected_taxonomies;

								// If excluded is checked, we also add it to post format.
								$social_media_account_data['exclude_taxonomies'] = $exclude_taxonomies;

							}

							// inform that the update took place.
							$update_took_place = true;
						}
					}
				}

				if ( true === $update_took_place ) {
					// Create the option so that the migrate code will not run again.
					add_option( 'rop_data_migrated_tax', 'yes', null, 'no' );
					// Update the plugin data containing the changes.
					update_option( 'rop_data', $option );
				}
			}
		}
	}

	/**
	 * Checks to see if the cron schedule is firing.
	 *
	 * @since   8.4.3
	 * @access  public
	 */
	public function rop_cron_event_status_notice() {

		$user_id = get_current_user_id();

		if ( get_user_meta( $user_id, 'rop-cron-event-status-notice-dismissed' ) ) {
			return;
		}

		$rop_next_task_hit = wp_next_scheduled( 'rop_cron_job' );
		$rop_current_time  = time();

		// if sharing not started cron event will not be present
		if ( ! $rop_next_task_hit ) {
			return;
		}

		$rop_cron_elapsed_time = ( $rop_current_time - $rop_next_task_hit ) / 60;
		$rop_cron_elapsed_time = absint( $rop_cron_elapsed_time );

		// default: 60 minutes
		$rop_cron_event_excess_elapsed_time = apply_filters( 'rop_cron_event_excess_elapsed_time', 60 );

		if ( $rop_cron_elapsed_time >= $rop_cron_event_excess_elapsed_time ) {

			?>
			<div class="notice notice-error">
				<?php echo sprintf( __( '%1$s%2$sRevive Old Posts:%3$s There might be an issue preventing Revive Old Posts from sharing to your connected accounts. If sharing is not working, then see %4$shere for solutions.%5$s%6$s%7$s', 'tweet-old-post' ), '<p>', '<b>', '</b>', '<a href="https://docs.revive.social/article/686-fix-revive-old-post-not-posting" target="_blank">', '</a>', '<a style="float: right;" href="?rop-cron-event-status-notice-dismissed">Dismiss</a>', '</p>' ); ?>

			</div>
			<?php

		}

	}

	/**
	 * Dismiss rop_cron_job not firing notice.
	 *
	 * @since   8.4.3
	 * @access  public
	 */
	public function rop_dismiss_rop_event_not_firing_notice() {

		$user_id = get_current_user_id();
		if ( isset( $_GET['rop-cron-event-status-notice-dismissed'] ) ) {
			add_user_meta( $user_id, 'rop-cron-event-status-notice-dismissed', 'true', true );
		}

	}

	/**
	 * Buffer addon disabled notice.
	 *
	 * @since   8.4.0
	 * @access  public
	 */
	public function rop_buffer_addon_notice() {

		if ( is_plugin_active( 'rop-buffer-addon/rop-buffer-addon.php' ) ) {
			deactivate_plugins( 'rop-buffer-addon/rop-buffer-addon.php' );
		} else {
			return;
		}

		$user_id = get_current_user_id();

		if ( get_user_meta( $user_id, 'rop-buffer-addon-notice-dismissed' ) ) {
			return;
		}

		?>

		<div class="notice notice-error">
			<?php echo sprintf( __( '%1$s We\'ve bundled the Buffer feature into Revive Old Posts Pro, and therefore deactivated the Buffer Addon automatically to prevent any conflicts. If you were a free user testing out the addon then please send us a support request %2$shere%3$s. %4$s %5$s', 'tweet-old-post' ), '<p>', '<a href="https://revive.social/support/" target="_blank">', '</a>', '<a style="float: right;" href="?rop-wp-cron-notice-dismissed">Dismiss</a>', '</p>' ); ?>
		</div>
		<?php

	}

	/**
	 * Dismiss WordPress Cron disabled notice.
	 *
	 * @since   8.4.0
	 * @access  public
	 */
	public function rop_dismiss_buffer_addon_disabled_notice() {

		$user_id = get_current_user_id();
		if ( isset( $_GET['rop-buffer-addon-notice-dismissed'] ) ) {
			add_user_meta( $user_id, 'rop-buffer-addon-notice-dismissed', 'true', true );
		}

	}

	/**
	 * Clears the array of account IDs.
	 *
	 * Delete the db option holding the account IDs used to determine when to send an email
	 * To website admin, letting them know that all posts have been shared; when the share more than once option is unchecked.
	 *
	 * @since   8.3.3
	 * @access  public
	 */
	public function rop_clear_one_time_share_accounts() {

		$settings = new Rop_Settings_Model();

		if ( ! $settings->get_more_than_once() ) {
			delete_option( 'rop_one_time_share_accounts' );
		}

	}

	/**
	 * Update default shortener from rviv.ly.
	 *
	 * Rviv.ly domain is currently blacklisted by some social media networks.
	 * This code changes the rviv.ly shortener to is.id
	 *
	 * @since   8.5.6
	 * @access  public
	 */
	public function rop_update_shortener() {

		$user_id = get_current_user_id();

		if ( get_user_meta( $user_id, 'rop-shortener-changed-notice-dismissed' ) ) {
			return;
		}

		$updated_shortener = get_option( 'rop_changed_shortener' );

		if ( ! empty( $updated_shortener ) ) {
			return;
		}

		$options = get_option( 'rop_data' );

		if ( empty( $options ) ) {
			return;
		}

		$post_format = array_key_exists( 'post_format', $options ) ? $options['post_format'] : '';

		if ( empty( $post_format ) ) {
			return;
		}

		foreach ( $post_format as $account => $settings ) {

			foreach ( $settings as $key => $value ) {

				if ( $key === 'short_url_service' && $value === 'rviv.ly' ) {
					update_option( 'rop_changed_shortener', true );
					$post_format[ $account ][ $key ] = 'is.gd';
				}
			}
		}

		$options['post_format'] = $post_format;
		update_option( 'rop_data', $options );

	}

	/**
	 * Shortener changed notice.
	 *
	 * @since   8.5.6
	 * @access  public
	 */
	public function rop_shortener_changed_notice() {

		$updated_shortener = get_option( 'rop_changed_shortener' );

		if ( empty( $updated_shortener ) ) {
			return;
		}

		$user_id = get_current_user_id();

		if ( get_user_meta( $user_id, 'rop-shortener-changed-notice-dismissed' ) ) {
			return;
		}

		?>

		<div class="notice notice-error">
			<?php echo sprintf( __( '%1$s We\'ve automatically changed your Revive Old Posts\' shortener from rviv.ly to is.gd. Read the reason for the change %2$shere%3$s. %4$s %5$s', 'tweet-old-post' ), '<p>', '<a href="https://docs.revive.social/article/1244-why-we-automatically-changed-your-shortener-from-rviv-ly-to-is-gd" target="_blank">', '</a>', '<a style="float: right;" href="?rop-shortener-changed-notice-dismissed">Dismiss</a>', '</p>' ); ?>
		</div>
		<?php

	}

	/**
	 * Dismiss Shortener changed notice.
	 *
	 * @since   8.5.6
	 * @access  public
	 */
	public function rop_shortener_changed_disabled_notice() {

		$user_id = get_current_user_id();
		if ( isset( $_GET['rop-shortener-changed-notice-dismissed'] ) ) {
			add_user_meta( $user_id, 'rop-shortener-changed-notice-dismissed', 'true', true );
		}

	}

}
