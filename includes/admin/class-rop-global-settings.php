<?php
/**
 * The global settings of the plugin.
 *
 * @link       https://themeisle.com
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/admin
 */

/**
 * The global settings of the plugin.
 *
 * Defines the plugin global settings instance and modules.
 *
 * @package    Rop
 * @subpackage Rop/admin
 * @author     Themeisle <friends@themeisle.com>
 */
class Rop_Global_Settings {

	/**
	 * The main instance var.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @var     Rop_Global_Settings $instance The instance of this class.
	 */
	public static $instance;

	/**
	 * Stores the default general settings defaults.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @var     array $settings Default settings values.
	 */
	public $settings = array();

	/**
	 * Stores the default post format default options.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @var     array $post_format Default post format options.
	 */
	public $post_format = array();

	/**
	 * Stores the default available services data.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @var     array $services Available Services List.
	 */
	public $services = array();

	/**
	 * Stores the default schedule options.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @var     array $schedule Default schedule options.
	 */
	public $schedule = array();

	/**
	 * The services defaults.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $services_defaults The class defaults for services.
	 */
	private $services_defaults = array(
		'facebook'  => array(
			'active'           => true,
			'name'             => 'Facebook',
			'two_step_sign_in' => true,
			'credentials'      => array(
				'app_id' => array(
					'name'        => 'APP ID',
					'description' => '',
				),
				'secret' => array(
					'name'        => 'APP SECRET',
					'description' => '',
				),
			),
			'allowed_accounts' => 1,
			'description'      => '',
			'allowed_comment'  => false,
		),
		'twitter'   => array(
			'active'           => true,
			'name'             => 'Twitter',
			'credentials'      => array(
				'consumer_key'    => array(
					'name'        => 'API Key',
					'description' => 'Your X (Twitter) application api key',
				),
				'consumer_secret' => array(
					'name'        => 'API secret key',
					'description' => 'Your X (Twitter) application api secret',
				),
			),
			'two_step_sign_in' => true,
			'allowed_accounts' => 1,
			'allowed_comment'  => false,
		),
		'linkedin'  => array(
			'active' => false,
			'name'   => 'LinkedIn',
			'allowed_comment'  => false,
		),
		'tumblr'    => array(
			'active' => false,
			'name'   => 'Tumblr',
			'allowed_comment'  => false,
		),
		'pinterest' => array(
			'active' => false,
			'name'   => 'Pinterest',
			'allowed_comment'  => false,
		),
		'gmb'    => array(
			'active' => false,
			'name'   => 'Gmb',
			'fullname' => 'Google My Business',
			'allowed_comment'  => false,
		),
		'vk'    => array(
			'active' => false,
			'name'   => 'Vk',
			'allowed_comment'  => false,
		),
		'webhook' => array(
			'active' => false,
			'name'   => 'Webhook',
			'two_step_sign_in' => true,
			'allowed_comment'  => false,
		),
		'telegram'    => array(
			'active' => false,
			'name'   => 'Telegram',
			'allowed_comment'  => false,
		),
		'mastodon' => array(
			'active' => false,
			'name'   => 'Mastodon',
			'two_step_sign_in' => true,
			'allowed_comment'  => false,
		),
		'bluesky'  => array(
			'active' => false,
			'name' => 'Bluesky',
			'two_step_sign_in' => true,
			'allowed_comment'  => false,
		),
	);

	/**
	 * The settings defaults.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $settings_defaults The class defaults for settings.
	 */
	private $settings_defaults = array(
		'default_interval'      => 12,
		'min_interval'          => 12,
		'step_interval'         => 0.5,
		'minimum_post_age'      => 30,
		'maximum_post_age'      => 365,
		'number_of_posts'       => 1,
		'more_than_once'        => true,
		'available_post_types'  => array(),
		'selected_post_types'   => array( array( 'name' => 'Posts', 'value' => 'post', 'selected' => true ) ),
		'available_taxonomies'  => array(),
		'selected_taxonomies'   => array(),
		'exclude_taxonomies'    => false,
		'available_posts'       => array(), // get_posts(),
		'selected_posts'        => array(),
		'exclude_posts'         => true,
		'ga_tracking'           => true,
		'beta_user'             => false,
		'remote_check'          => false,
		'custom_messages'       => false,
		'custom_messages_share_order'    => false,
		'instant_share'         => true,
		'instant_share_default' => true,
		'start_time'            => false,
		'minute_interval'      => 5,
	);

	/**
	 * The post format defaults.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $post_format_defaults The class defaults for post format.
	 */
	private $post_format_defaults = array(
		'facebook'  => array(
			'wpml_language'         => '',
			'post_content'          => 'post_title',
			'custom_meta_field'     => '',
			'maximum_length'        => '1000',
			'custom_text'           => '',
			'custom_text_pos'       => 'beginning',
			'include_link'          => true,
			'url_from_meta'         => false,
			'url_meta_key'          => '',
			'short_url'             => false,
			'short_url_service'     => 'rviv.ly',
			'hashtags'              => 'no-hashtags',
			'hashtags_length'       => '200',
			'hashtags_common'       => '',
			'hashtags_custom'       => '',
			'hashtags_randomize'    => false,
			'shortner_credentials'  => array(),
			'image'                 => false,
			'utm_campaign_medium'   => 'social',
			'utm_campaign_name'     => 'ReviveOldPost',
			'share_link_in_comment' => false,
			'share_link_text'       => '',
		),
		'twitter'   => array(
			'wpml_language' => '',
			'post_content'         => 'post_title',
			'custom_meta_field'    => '',
			'maximum_length'       => '240',
			'custom_text'          => '',
			'custom_text_pos'      => 'beginning',
			'include_link'         => true,
			'url_from_meta'        => false,
			'url_meta_key'         => '',
			'short_url'            => false,
			'short_url_service'    => 'rviv.ly',
			'hashtags'             => 'no-hashtags',
			'hashtags_length'      => '200',
			'hashtags_common'      => '',
			'hashtags_custom'      => '',
			'hashtags_randomize'   => false,
			'shortner_credentials' => array(),
			'image'                => false,
			'utm_campaign_medium'  => 'social',
			'utm_campaign_name'    => 'ReviveOldPost',
		),
		'linkedin'    => array(
			'wpml_language' => '',
			'post_content'         => 'post_title',
			'custom_meta_field'    => '',
			'maximum_length'       => '1000',
			'custom_text'          => '',
			'custom_text_pos'      => 'beginning',
			'include_link'         => true,
			'url_from_meta'        => false,
			'url_meta_key'         => '',
			'short_url'            => false,
			'short_url_service'    => 'rviv.ly',
			'hashtags'             => 'no-hashtags',
			'hashtags_length'      => '200',
			'hashtags_common'      => '',
			'hashtags_custom'      => '',
			'hashtags_randomize'   => false,
			'shortner_credentials' => array(),
			'image'                => false,
			'utm_campaign_medium'  => 'social',
			'utm_campaign_name'    => 'ReviveOldPost',
		),
		'tumblr'  => array(
			'wpml_language' => '',
			'post_content'         => 'post_title',
			'custom_meta_field'    => '',
			'maximum_length'       => '1000',
			'custom_text'          => '',
			'shortner_credentials' => array(),
			'custom_text_pos'      => 'beginning',
			'include_link'         => true,
			'url_from_meta'        => false,
			'url_meta_key'         => '',
			'short_url'            => false,
			'short_url_service'    => 'rviv.ly',
			'hashtags'             => 'no-hashtags',
			'hashtags_length'      => '200',
			'hashtags_common'      => '',
			'hashtags_custom'      => '',
			'hashtags_randomize'   => false,
			'image'                => false,
			'utm_campaign_medium'  => 'social',
			'utm_campaign_name'    => 'ReviveOldPost',
		),
		'pinterest'    => array(
			'wpml_language' => '',
			'post_content'         => 'post_title',
			'custom_meta_field'    => '',
			'maximum_length'       => '1000',
			'custom_text'          => '',
			'custom_text_pos'      => 'beginning',
			'include_link'         => true,
			'url_from_meta'        => false,
			'url_meta_key'         => '',
			'short_url'            => false,
			'short_url_service'    => 'rviv.ly',
			'hashtags'             => 'no-hashtags',
			'hashtags_length'      => '200',
			'hashtags_common'      => '',
			'hashtags_custom'      => '',
			'hashtags_randomize'   => false,
			'shortner_credentials' => array(),
			'image'                => true,
			'utm_campaign_medium'  => 'social',
			'utm_campaign_name'    => 'ReviveOldPost',
		),
		'gmb' => array(
			'post_content'         => 'post_title',
			'custom_meta_field'    => '',
			'maximum_length'       => '1000',
			'custom_text'          => '',
			'custom_text_pos'      => 'beginning',
			'include_link'         => true,
			'url_from_meta'        => false,
			'url_meta_key'         => '',
			'short_url'            => false,
			'short_url_service'    => 'rviv.ly',
			'hashtags'             => 'no-hashtags',
			'hashtags_length'      => '200',
			'hashtags_common'      => '',
			'hashtags_custom'      => '',
			'hashtags_randomize'   => false,
			'shortner_credentials' => array(),
			'image'                => false,
			'utm_campaign_medium'  => 'social',
			'utm_campaign_name'    => 'ReviveOldPost',
		),
		'vk' => array(
			'wpml_language' => '',
			'post_content'         => 'post_title',
			'custom_meta_field'    => '',
			'maximum_length'       => '1000',
			'custom_text'          => '',
			'custom_text_pos'      => 'beginning',
			'include_link'         => true,
			'url_from_meta'        => false,
			'url_meta_key'         => '',
			'short_url'            => false,
			'short_url_service'    => 'rviv.ly',
			'hashtags'             => 'no-hashtags',
			'hashtags_length'      => '200',
			'hashtags_common'      => '',
			'hashtags_custom'      => '',
			'hashtags_randomize'   => false,
			'shortner_credentials' => array(),
			'image'                => false,
			'utm_campaign_medium'  => 'social',
			'utm_campaign_name'    => 'ReviveOldPost',
		),
		'webhook' => array(
			'wpml_language' => '',
			'post_content'         => 'post_title',
			'custom_meta_field'    => '',
			'maximum_length'       => '1000',
			'custom_text'          => '',
			'custom_text_pos'      => 'beginning',
			'include_link'         => true,
			'url_from_meta'        => false,
			'url_meta_key'         => '',
			'short_url'            => false,
			'short_url_service'    => 'is.gd',
			'hashtags'             => 'no-hashtags',
			'hashtags_length'      => '200',
			'hashtags_common'      => '',
			'hashtags_custom'      => '',
			'hashtags_randomize'   => false,
			'shortner_credentials' => array(),
			'image'                => false,
			'utm_campaign_medium'  => 'social',
			'utm_campaign_name'    => 'ReviveOldPost',
		),
		'telegram'  => array(
			'wpml_language'        => '',
			'post_content'         => 'post_title',
			'custom_meta_field'    => '',
			'maximum_length'       => '1000',
			'custom_text'          => '',
			'custom_text_pos'      => 'beginning',
			'include_link'         => true,
			'url_from_meta'        => false,
			'url_meta_key'         => '',
			'short_url'            => false,
			'short_url_service'    => 'rviv.ly',
			'hashtags'             => 'no-hashtags',
			'hashtags_length'      => '200',
			'hashtags_common'      => '',
			'hashtags_custom'      => '',
			'hashtags_randomize'   => false,
			'shortner_credentials' => array(),
			'image'                => false,
			'utm_campaign_medium'  => 'social',
			'utm_campaign_name'    => 'ReviveOldPost',
		),
		'mastodon'  => array(
			'wpml_language'        => '',
			'post_content'         => 'post_title',
			'custom_meta_field'    => '',
			'maximum_length'       => '1000',
			'custom_text'          => '',
			'custom_text_pos'      => 'beginning',
			'include_link'         => true,
			'url_from_meta'        => false,
			'url_meta_key'         => '',
			'short_url'            => false,
			'short_url_service'    => 'rviv.ly',
			'hashtags'             => 'no-hashtags',
			'hashtags_length'      => '200',
			'hashtags_common'      => '',
			'hashtags_custom'      => '',
			'hashtags_randomize'   => false,
			'shortner_credentials' => array(),
			'image'                => false,
			'utm_campaign_medium'  => 'social',
			'utm_campaign_name'    => 'ReviveOldPost',
		),
		'bluesky'  => array(
			'wpml_language'        => '',
			'post_content'         => 'post_title',
			'custom_meta_field'    => '',
			'maximum_length'       => '300',
			'custom_text'          => '',
			'custom_text_pos'      => 'beginning',
			'include_link'         => true,
			'url_from_meta'        => false,
			'url_meta_key'         => '',
			'short_url'            => false,
			'short_url_service'    => 'rviv.ly',
			'hashtags'             => 'no-hashtags',
			'hashtags_length'      => '200',
			'hashtags_common'      => '',
			'hashtags_custom'      => '',
			'hashtags_randomize'   => false,
			'shortner_credentials' => array(),
			'image'                => false,
			'utm_campaign_medium'  => 'social',
			'utm_campaign_name'    => 'ReviveOldPost',
		),
	);

	/**
	 * The schedule defaults.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $schedule_defaults The class schedule defaults.
	 */
	private $schedule_defaults = array(
		'type'       => 'recurring',
		'interval_r' => '2.5',
		'interval_f' => array(
			'week_days' => array( '1', '2', '3', '4', '5', '6', '7' ),
			'time'      => array( '9:30' ),
		),
	);

	/**
	 * Method to destroy singleton.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public static function destroy_instance() {
		static::$instance = null;
	}

	/**
	 * Method to retrieve instance of schedule.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array
	 */
	public function get_default_schedule() {
		$schedule               = self::instance()->schedule;
		$settings_model         = new Rop_Settings_Model();
		$schedule['interval_r'] = $settings_model->get_interval();
		$schedule['type']       = 'recurring';

		return $schedule;
	}

	/**
	 * The instance method for the static class.
	 * Defines and returns the instance of the static class.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return Rop_Global_Settings
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Rop_Global_Settings ) ) {
			self::$instance           = new Rop_Global_Settings;
			self::$instance->services = apply_filters(
				'rop_available_services',
				self::$instance->services_defaults
			);

			$is_new_user  = (int) get_option( 'rop_is_new_user', 0 );
			$install_time = ! $is_new_user ? (int) get_option( 'rop_first_install_date', 0 ) : 0;
			if ( ! $is_new_user && ( $install_time && $install_time >= strtotime( '-1 hour' ) ) ) {
				$is_new_user = update_option( 'rop_is_new_user', 1 );
			}

			$min_interval = 0.5;
			// Apply new limit for new free users.
			if ( $is_new_user && 1 < self::$instance->license_type() ) {
				$min_interval = 12;
			}
			self::$instance->settings_defaults['min_interval'] = apply_filters( 'rop_min_interval_bw_shares_min', ROP_DEBUG ? 0.05 : $min_interval );
			self::$instance->settings_defaults['step_interval'] = apply_filters( 'rop_min_interval_bw_shares_step', 0.1 );

			self::$instance->settings = apply_filters(
				'rop_general_settings_defaults',
				self::$instance->settings_defaults
			);

			self::$instance->post_format = apply_filters(
				'rop_post_format_defaults',
				self::$instance->post_format_defaults
			);

			self::$instance->schedule_defaults['timestamp'] = current_time( 'timestamp', 0 );
			self::$instance->schedule                       = apply_filters(
				'rop_schedule_defaults',
				self::$instance->schedule_defaults
			);
		}// End if().

		return self::$instance;
	}

	/**
	 * Get the license data.
	 *
	 * @since   9.1.0
	 * @access  public
	 *
	 * @return  object|int
	 */
	public function get_license_data() {
		if ( ! defined( 'ROP_PRO_VERSION' ) ) {
			return -1;
		}

		$license_data = get_option( 'tweet_old_post_pro_license_data', '' );

		if ( empty( $license_data ) ) {
			return -1;
		}

		if ( ! isset( $license_data->license ) ) {
			return -1;
		}

		return $license_data;
	}

	/**
	 * Get the license data for the public view (display).
	 *
	 * @since   9.1.0
	 * @access  public
	 *
	 * @return  array With data for display.
	 */
	public function get_license_data_view() {
		$license_data      = $this->get_license_data();
		$view_license_data = array(
			'installed'    => defined( 'ROP_PRO_VERSION' ),
			'license'      => 'invalid',
			'expires'      => '',
			'passwordMask' => __( 'Add your license key here...', 'tweet-old-post' ),
		);

		if ( -1 === $license_data ) {
			return $view_license_data;
		}

		// Pick only the necessary data.
		if ( isset( $license_data->license ) ) {
			$view_license_data['license'] = $license_data->license;
		}

		if ( isset( $license_data->expires ) ) {
			$view_license_data['expires'] = date( 'F j, Y', strtotime( $license_data->expires ) );
			if ( 'valid' === $view_license_data['license'] ) {
				$view_license_data['expires'] = sprintf( Rop_I18n::get_labels( 'general.expires' ), $view_license_data['expires'] );
			} else {
				$view_license_data['expires'] = sprintf( Rop_I18n::get_labels( 'general.expired' ), $view_license_data['expires'] );
			}
		}

		if ( isset( $license_data->key ) ) {
			$view_license_data['passwordMask'] = str_repeat( '*', strlen( $license_data->key ) - 4 ) . substr( $license_data->key, -4 );
		}

		return $view_license_data;
	}

	/**
	 * Get license plan.
	 *      -1 - Pro is not present nor installed.
	 *      0 - Pro is installed but not active.
	 *      1,2,3,4,5,6,7 - Plans that the user is using.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return  int
	 */
	public function license_type() {

		$license_data = $this->get_license_data();

		if ( - 1 === $license_data ) {
			return - 1;
		}

		/**
		 * If we have an invalid license but the pro is installed.
		 */
		if ( $license_data->license !== 'valid' ) {
			if ( defined( 'ROP_PRO_VERSION' ) ) {
				return 0;
			}

			return ( - 1 );
		}

		if ( isset( $license_data->price_id ) ) {
			return intval( $license_data->price_id );
		}

		$plan = intval( get_option( 'tweet_old_post_pro_license_data', - 1 ) );

		/**
		 * If the plan is not fetched but we have pro.
		 */
		if ( $plan < 1 ) {
			if ( defined( 'ROP_PRO_VERSION' ) ) {
				return 0;
			}

			return - 1;
		}

		return $plan;
	}

	/**
	 * Method to retrieve instance of post_format.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   bool|string $service_name The name of the service. Default false. Returns all.
	 *
	 * @return array|mixed
	 */
	public function get_default_post_format( $service_name = false ) {
		if ( isset( $service_name ) && $service_name != false && isset( self::instance()->post_format[ $service_name ] ) ) {
			return self::instance()->post_format[ $service_name ];
		}

		return self::instance()->post_format;
	}

	/**
	 * Method to retrieve instance of settings.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array
	 */
	public function get_default_settings() {
		return self::instance()->settings;
	}

	/**
	 * Method to retrieve only the active services handle.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array
	 */
	public function get_active_services_handle() {
		$active = array();
		foreach ( $this->get_available_services() as $handle => $data ) {
			if ( $data['active'] == true ) {
				array_push( $active, $handle );
			}
		}

		return $active;
	}

	/**
	 * Method to retrieve instance of services.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array
	 */
	public function get_available_services() {
		$available_services = apply_filters(
			'rop_available_services',
			self::instance()->services_defaults
		);
		/**
		 * Don't show credentials popup if the service is already authenticated.
		 */
		$service_model = new Rop_Services_Model();

		foreach ( $available_services as $key => $service ) {
			$registered = $service_model->get_authenticated_services( $key );

			if ( empty( $registered ) ) {
				continue;
			}
			$registered = array_filter(
				$registered,
				function ( $value ) {
					return ! empty( $value['public_credentials'] );
				}
			);
			if ( empty( $registered ) ) {
				continue;
			}
			$service['credentials'] = array();
			/**
			 * These variables prevent Twitter service to register multiple accounts.
			 * $service['two_step_sign_in'] = false; For Twitter, this prevent the modal to open up
			 * Even if the modal displays, the variable $available_services[ $key ] will prevent the form to show up.
			 */
			if ( 'twitter' !== $key && 'tumblr' !== $key && 'mastodon' !== $key ) {
				$service['two_step_sign_in'] = false;
				$available_services[ $key ]  = $service;
			}
		}
		// Check is new user.
		$is_new_user = $this->check_is_new_license();
		// Apply new plan(middle) for new free users.
		$is_middle_plan = $is_new_user ? $this->license_type() > 1 : $this->license_type() > 0;

		$available_services['instagram']           = $available_services['facebook'];
		$available_services['instagram']['name']   = 'Instagram';
		$available_services['instagram']['active'] = $is_middle_plan ? true : false;
		$available_services['instagram']['allowed_comment'] = false;
		return $available_services;
	}

	/**
	 * Update the time.
	 *
	 * @return void
	 */
	public function update_start_time() {
		$settings_model         = new Rop_Settings_Model();
		$settings               = $settings_model->get_settings();
		$settings['start_time'] = time();
		$settings_model->save_settings( $settings );

	}

	/**
	 * Update the time.
	 *
	 * @return int
	 */
	public function get_start_time() {
		$settings_model = new Rop_Settings_Model();

		return $settings_model->get_start_time();

	}

	/**
	 * Update the time.
	 *
	 * @return void
	 */
	public function reset_start_time() {
		$settings_model         = new Rop_Settings_Model();
		$settings               = $settings_model->get_settings();
		$settings['start_time'] = false;
		$settings_model->save_settings( $settings );
	}

	/**
	 * Method to retrieve all the services handles.
	 *
	 * @since   8.0.0rc
	 * @access  public
	 * @return array
	 */
	public function get_all_services_handle() {
		$all = array();
		foreach ( $this->get_available_services() as $handle => $data ) {
			array_push( $all, $handle );
		}

		return $all;
	}

	/**
	 * Check license is new or not.
	 *
	 * @return bool
	 */
	public function check_is_new_license() {
		if ( $this->license_type() <= 0 ) { // Ignore free users.
			return false;
		}

		$product_key  = 'tweet_old_post_pro';
		$license_data = get_option( $product_key . '_license_data', '' );

		if ( ! isset( $license_data->created_at ) ) {
			return false;
		}

		// Is after 9.1?
		$created_at = strtotime( date( 'Y-m-d', strtotime( $license_data->created_at ) ) );
		$compare_to = strtotime( '2024-07-21' );

		return $created_at > $compare_to;
	}
}
