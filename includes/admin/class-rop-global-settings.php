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
		),
		'twitter'   => array(
			'active'           => true,
			'name'             => 'Twitter',
			'credentials'      => array(
				'consumer_key'    => array(
					'name'        => 'API Key',
					'description' => 'Your Twitter application api key',
				),
				'consumer_secret' => array(
					'name'        => 'API secret key',
					'description' => 'Your Twitter application api secret',
				),
			),
			'two_step_sign_in' => true,
			'allowed_accounts' => 1,
		),
		'linkedin'  => array(
			'active' => false,
			'name'   => 'LinkedIn',
		),
		'tumblr'    => array(
			'active' => false,
			'name'   => 'Tumblr',
		),
		'pinterest' => array(
			'active' => false,
			'name'   => 'Pinterest',
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
		'default_interval'     => 10,
		'minimum_post_age'     => 30,
		'maximum_post_age'     => 0,
		'number_of_posts'      => 1,
		'more_than_once'       => true,
		'available_post_types' => array(),
		'selected_post_types'  => array( array( 'name' => 'Posts', 'value' => 'post', 'selected' => true ) ),
		'available_taxonomies' => array(),
		'selected_taxonomies'  => array(),
		'exclude_taxonomies'   => false,
		'available_posts'      => array(), // get_posts(),
		'selected_posts'       => array(),
		'exclude_posts'        => true,
		'ga_tracking'          => true,
		'beta_user'            => false,
		'remote_check'         => false,
		'custom_messages'      => false,
		'instant_share'        => true,
		'instant_share_default' => false,
		'start_time'           => false,
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
			'post_content'         => 'post_title',
			'custom_meta_field'    => '',
			'maximum_length'       => '1000',
			'custom_text'          => '',
			'custom_text_pos'      => 'beginning',
			'include_link'         => true,
			'url_from_meta'        => false,
			'url_meta_key'         => '',
			'short_url'            => true,
			'short_url_service'    => 'rviv.ly',
			'hashtags'             => 'no-hashtags',
			'hashtags_length'      => '20',
			'hashtags_common'      => '',
			'hashtags_custom'      => '',
			'shortner_credentials' => array(),
			'image'                => false,
			'utm_campaign_medium'  => 'social',
			'utm_campaign_name'    => 'ReviveOldPost',
		),
		'twitter'   => array(
			'post_content'         => 'post_title',
			'custom_meta_field'    => '',
			'maximum_length'       => '140',
			'custom_text'          => '',
			'custom_text_pos'      => 'beginning',
			'include_link'         => true,
			'url_from_meta'        => false,
			'url_meta_key'         => '',
			'short_url'            => true,
			'short_url_service'    => 'rviv.ly',
			'hashtags'             => 'no-hashtags',
			'hashtags_length'      => '10',
			'hashtags_common'      => '',
			'shortner_credentials' => array(),
			'hashtags_custom'      => '',
			'image'                => false,
			'utm_campaign_medium'  => 'social',
			'utm_campaign_name'    => 'ReviveOldPost',
		),
		'linkedin'  => array(
			'post_content'         => 'post_title',
			'custom_meta_field'    => '',
			'maximum_length'       => '1000',
			'custom_text'          => '',
			'custom_text_pos'      => 'beginning',
			'include_link'         => true,
			'url_from_meta'        => false,
			'shortner_credentials' => array(),
			'url_meta_key'         => '',
			'short_url'            => true,
			'short_url_service'    => 'rviv.ly',
			'hashtags'             => 'no-hashtags',
			'hashtags_length'      => '10',
			'hashtags_common'      => '',
			'hashtags_custom'      => '',
			'image'                => false,
			'utm_campaign_medium'  => 'social',
			'utm_campaign_name'    => 'ReviveOldPost',
		),
		'tumblr'    => array(
			'post_content'         => 'post_title',
			'custom_meta_field'    => '',
			'maximum_length'       => '1000',
			'custom_text'          => '',
			'shortner_credentials' => array(),
			'custom_text_pos'      => 'beginning',
			'include_link'         => true,
			'url_from_meta'        => false,
			'url_meta_key'         => '',
			'short_url'            => true,
			'short_url_service'    => 'rviv.ly',
			'hashtags'             => 'no-hashtags',
			'hashtags_length'      => '10',
			'hashtags_common'      => '',
			'hashtags_custom'      => '',
			'image'                => false,
			'utm_campaign_medium'  => 'social',
			'utm_campaign_name'    => 'ReviveOldPost',
		),
		'pinterest' => array(
			'post_content'         => 'post_title',
			'custom_meta_field'    => '',
			'maximum_length'       => '1000',
			'custom_text'          => '',
			'custom_text_pos'      => 'beginning',
			'include_link'         => true,
			'url_from_meta'        => false,
			'url_meta_key'         => '',
			'short_url'            => true,
			'short_url_service'    => 'rviv.ly',
			'hashtags'             => 'no-hashtags',
			'hashtags_length'      => '20',
			'hashtags_common'      => '',
			'hashtags_custom'      => '',
			'shortner_credentials' => array(),
			'image'                => true,
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
	 * Get license plan.
	 *      -1 - Pro is not present nor installed.
	 *      0 - Pro is installed but not active.
	 *      1,2,3 - Plans that the user is using.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return  int
	 */
	public function license_type() {
		$pro_check = defined( 'ROP_PRO_VERSION' );
		if ( ! $pro_check ) {
			return - 1;
		}
		$product_key  = 'tweet_old_post_pro';
		$license_data = get_option( $product_key . '_license_data', '' );
		if ( empty( $license_data ) ) {
			return - 1;
		}

		if ( ! isset( $license_data->license ) ) {
			return - 1;
		}
		/**
		 * If we have an invalid license but the pro is installed.
		 */
		if ( $license_data->license !== 'valid' ) {
			if ( $pro_check ) {
				return 0;
			}

			return ( - 1 );
		}
		if ( isset( $license_data->price_id ) ) {
			return intval( $license_data->price_id );
		}
		$plan = get_option( $product_key . '_license_plan', - 1 );

		$plan = intval( $plan );
		/**
		 * If the plan is not fetched but we have pro.
		 */
		if ( $plan < 1 ) {
			if ( $pro_check ) {
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
			$service['credentials']      = array();
			$service['two_step_sign_in'] = false;
			$available_services[ $key ]  = $service;
		}

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
}
