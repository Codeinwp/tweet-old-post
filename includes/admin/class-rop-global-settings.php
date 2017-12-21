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
			'active' => true,
			'name' => 'Facebook',
			'two_step_sign_in' => true,
			'credentials' => array(
				'app_id' => array(
					'name' => 'APP ID',
					'description' => 'Please add the APP ID from your Facebook app.',
				),
				'secret' => array(
					'name' => 'APP SECRET',
					'description' => 'Please add the APP SECRET from your Facebook app.',
				),
			),
			'allowed_accounts' => 1,
		),
		'twitter' => array(
			'active' => true,
			'name' => 'Twitter',
			'two_step_sign_in' => false,
			'allowed_accounts' => 1,
		),
		'linkedin' => array(
			'active' => false,
			'name' => 'LinkedIn',
	// 'two_step_sign_in' => true,
	// 'credentials' => array(
	// 'client_id' => array(
	// 'name' => 'Client ID',
	// 'description' => 'Please add the Client ID from your LinkedIn app.',
	// ),
	// 'secret' => array(
	// 'name' => 'Client Secret',
	// 'description' => 'Please add the Client Secret from your LinkedIn app.',
	// ),
	// ),
		),
		'tumblr' => array(
			'active' => false,
			'name' => 'Tumblr',
	// 'two_step_sign_in' => true,
	// 'credentials' => array(
	// 'consumer_key' => array(
	// 'name' => 'Consumer Key',
	// 'description' => 'Please add the Consumer Key from your Tumblr app.',
	// ),
	// 'consumer_secret' => array(
	// 'name' => 'Consumer Secret',
	// 'description' => 'Please add the Consumer Secret from your Tumblr app.',
	// ),
	// ),
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
		'minimum_post_age' => 15,
		'maximum_post_age' => 60,
		'number_of_posts' => 5,
		'more_than_once' => true,
		'available_post_types' => array(),
		'selected_post_types' => array( array( 'name' => 'Posts', 'value' => 'post', 'selected' => true ) ),
		'available_taxonomies' => array(),
		'selected_taxonomies' => array(),
		'exclude_taxonomies' => false,
		'available_posts' => array(), // get_posts(),
		'selected_posts' => array(),
		'exclude_posts' => false,
		'post_limit' => 20,
		'beta_user' => false,
		'remote_check' => false,
		'custom_messages' => false,
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
			'post_content' => 'post_title',
			'custom_meta_field' => '',
			'maximum_length' => '160',
			'custom_text' => '',
			'custom_text_pos' => 'beginning',
			'include_link' => true,
			'url_from_meta' => false,
			'url_meta_key' => '',
			'short_url' => true,
			'short_url_service' => 'rviv.ly',
			'hashtags' => 'no-hashtags',
			'hashtags_length' => '10',
			'hashtags_common' => '',
			'hashtags_custom' => '',
			'image' => true,
		),
		'twitter' => array(
			'post_content' => 'post_title',
			'custom_meta_field' => '',
			'maximum_length' => '160',
			'custom_text' => '',
			'custom_text_pos' => 'beginning',
			'include_link' => true,
			'url_from_meta' => false,
			'url_meta_key' => '',
			'short_url' => true,
			'short_url_service' => 'rviv.ly',
			'hashtags' => 'no-hashtags',
			'hashtags_length' => '10',
			'hashtags_common' => '',
			'hashtags_custom' => '',
			'image' => true,
		),
		'linkedin' => array(
			'post_content' => 'post_title',
			'custom_meta_field' => '',
			'maximum_length' => '160',
			'custom_text' => '',
			'custom_text_pos' => 'beginning',
			'include_link' => true,
			'url_from_meta' => false,
			'url_meta_key' => '',
			'short_url' => true,
			'short_url_service' => 'rviv.ly',
			'hashtags' => 'no-hashtags',
			'hashtags_length' => '10',
			'hashtags_common' => '',
			'hashtags_custom' => '',
			'image' => true,
		),
		'tumblr' => array(
			'post_content' => 'post_title',
			'custom_meta_field' => '',
			'maximum_length' => '160',
			'custom_text' => '',
			'custom_text_pos' => 'beginning',
			'include_link' => true,
			'url_from_meta' => false,
			'url_meta_key' => '',
			'short_url' => true,
			'short_url_service' => 'rviv.ly',
			'hashtags' => 'no-hashtags',
			'hashtags_length' => '10',
			'hashtags_common' => '',
			'hashtags_custom' => '',
			'image' => true,
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
		'type' => 'fixed',
		'interval_r' => '2.5',
		'interval_f' => array(
			'week_days' => array( '1', '3', '5' ),
			'time' => array( '10:30', '11:30' ),
		),
		'timestamp' => null,
		'first_share' => null,
		'last_share' => null,
	);

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
			self::$instance = new Rop_Global_Settings;
			self::$instance->services = apply_filters(
				'rop_available_services',
				self::$instance->services_defaults
			);

			$post_types = get_post_types( array(), 'objects' );
			$post_types_array = array();
			foreach ( $post_types as $type ) {
				array_push( $post_types_array, array( 'name' => $type->label, 'value' => $type->name, 'selected' => false ) );
			}

			self::$instance->settings_defaults['available_post_types'] = $post_types_array;
			self::$instance->settings = apply_filters(
				'rop_general_settings_defaults',
				self::$instance->settings_defaults
			);

			self::$instance->post_format = apply_filters(
				'rop_post_format_defaults',
				self::$instance->post_format_defaults
			);

			self::$instance->schedule_defaults['timestamp'] = current_time( 'timestamp', 0 );
			self::$instance->schedule = apply_filters(
				'rop_schedule_defaults',
				self::$instance->schedule_defaults
			);
		}// End if().

		return self::$instance;
	}

	/**
	 * Method to retrieve instance of schedule.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array
	 */
	public function get_default_schedule() {
		return self::instance()->schedule;
	}

	/**
	 * Method to retrieve instance of post_format.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   bool|string $service_name The name of the service. Default false. Returns all.
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
	 * Method to retrieve instance of services.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array
	 */
	public function get_available_services() {
		return self::instance()->services;
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
			if ( $data['active'] == true ) { array_push( $active, $handle );
			}
		}
		return $active;
	}

	/**
	 * Method to destroy singleton.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public static function destroy_instance() {
		static::$instance = null;
	}
}
