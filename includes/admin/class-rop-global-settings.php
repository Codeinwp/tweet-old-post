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
		'facebook' => array(
			'active'           => true,
			'name'             => 'Facebook',
			'two_step_sign_in' => true,
			'credentials'      => array(
				'app_id' => array(
					'name'        => 'APP ID',
					'description' => 'Please add the APP ID from your Facebook app.',
				),
				'secret' => array(
					'name'        => 'APP SECRET',
					'description' => 'Please add the APP SECRET from your Facebook app.',
				),
			),
			'allowed_accounts' => 1,
		),
		'twitter'  => array(
			'active'           => true,
			'name'             => 'Twitter',
			'two_step_sign_in' => false,
			'allowed_accounts' => 1,
		),
		'linkedin' => array(
			'active' => false,
			'name'   => 'LinkedIn',
		),
		'tumblr'   => array(
			'active' => false,
			'name'   => 'Tumblr',
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
		'default_interval'     => 8,
		'minimum_post_age'     => 15,
		'maximum_post_age'     => 60,
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
		'ga_tracking'          => false,
		'post_limit'           => 20,
		'beta_user'            => false,
		'remote_check'         => false,
		'custom_messages'      => false,
	);

	/**
	 * The post format defaults.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $post_format_defaults The class defaults for post format.
	 */
	private $post_format_defaults = array(
		'facebook' => array(
			'post_content'      => 'post_title',
			'custom_meta_field' => '',
			'maximum_length'    => '160',
			'custom_text'       => '',
			'custom_text_pos'   => 'beginning',
			'include_link'      => true,
			'url_from_meta'     => false,
			'url_meta_key'      => '',
			'short_url'         => true,
			'short_url_service' => 'rviv.ly',
			'hashtags'          => 'no-hashtags',
			'hashtags_length'   => '10',
			'hashtags_common'   => '',
			'hashtags_custom'   => '',
			'image'             => false,
		),
		'twitter'  => array(
			'post_content'      => 'post_title',
			'custom_meta_field' => '',
			'maximum_length'    => '160',
			'custom_text'       => '',
			'custom_text_pos'   => 'beginning',
			'include_link'      => true,
			'url_from_meta'     => false,
			'url_meta_key'      => '',
			'short_url'         => true,
			'short_url_service' => 'rviv.ly',
			'hashtags'          => 'no-hashtags',
			'hashtags_length'   => '10',
			'hashtags_common'   => '',
			'hashtags_custom'   => '',
			'image'             => false,
		),
		'linkedin' => array(
			'post_content'      => 'post_title',
			'custom_meta_field' => '',
			'maximum_length'    => '160',
			'custom_text'       => '',
			'custom_text_pos'   => 'beginning',
			'include_link'      => true,
			'url_from_meta'     => false,
			'url_meta_key'      => '',
			'short_url'         => true,
			'short_url_service' => 'rviv.ly',
			'hashtags'          => 'no-hashtags',
			'hashtags_length'   => '10',
			'hashtags_common'   => '',
			'hashtags_custom'   => '',
			'image'             => false,
		),
		'tumblr'   => array(
			'post_content'      => 'post_title',
			'custom_meta_field' => '',
			'maximum_length'    => '160',
			'custom_text'       => '',
			'custom_text_pos'   => 'beginning',
			'include_link'      => true,
			'url_from_meta'     => false,
			'url_meta_key'      => '',
			'short_url'         => true,
			'short_url_service' => 'rviv.ly',
			'hashtags'          => 'no-hashtags',
			'hashtags_length'   => '10',
			'hashtags_common'   => '',
			'hashtags_custom'   => '',
			'image'             => false,
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
		'type'        => 'fixed',
		'interval_r'  => '2.5',
		'interval_f'  => array(
			'week_days' => array( '1', '3', '5' ),
			'time'      => array( '10:30', '11:30' ),
		),
		'timestamp'   => null,
		'first_share' => null,
		'last_share'  => null,
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
	 * Method to check if the PRO classes exist.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return bool
	 */
	public function has_pro() {
		// return 'business';
		if ( class_exists( 'Rop_Pro' ) ) {
			$pro = new Rop_Pro();

			return $pro->is_business(); // TODO should return a string 'pro' or 'business' based on licence type
		}

		return false;
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
			$registered = reset( $registered );
			if ( empty( $registered['public_credentials'] ) ) {

				continue;
			}
			$service['credentials']      = array();
			$service['two_step_sign_in'] = false;
			$available_services[ $key ]  = $service;
		}

		return $available_services;
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
