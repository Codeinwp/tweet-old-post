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
	 * @var array
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
				array(
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
						'allowed_accounts' => 2,
					),
					'twitter' => array(
						'active' => true,
						'name' => 'Twitter',
						'two_step_sign_in' => false,
					),
					'linkedin' => array(
						'active' => true,
						'name' => 'LinkedIn',
						'two_step_sign_in' => true,
						'credentials' => array(
							'client_id' => array(
								'name' => 'Client ID',
								'description' => 'Please add the Client ID from your LinkedIn app.',
							),
							'secret' => array(
								'name' => 'Client Secret',
								'description' => 'Please add the Client Secret from your LinkedIn app.',
							),
						),
					),
					'tumblr' => array(
						'active' => true,
						'name' => 'Tumblr',
						'two_step_sign_in' => true,
						'credentials' => array(
							'consumer_key' => array(
								'name' => 'Consumer Key',
								'description' => 'Please add the Consumer Key from your Tumblr app.',
							),
							'consumer_secret' => array(
								'name' => 'Consumer Secret',
								'description' => 'Please add the Consumer Secret from your Tumblr app.',
							),
						),
					),
				)
			);

			$post_types = get_post_types( array(), 'objects' );
			self::$instance->settings = apply_filters(
				'rop_general_settings_defaults',
				array(
					'minimum_post_age' => 15,
					'maximum_post_age' => 60,
					'number_of_posts' => 5,
					'more_than_once' => true,
					'available_post_types' => $post_types,
					'selected_post_types' => array(),
					'available_taxonomies' => array(),
					'selected_taxonomies' => array(),
					'exclude_taxonomies' => false,
					'available_posts' => array(), // get_posts(),
					'selected_posts' => array(),
					'exclude_posts' => false,
				)
			);

			self::$instance->post_format = apply_filters(
			    'rop_post_format_defaults',
				array(
					'facebook' => array(
						'post_content' => '',
						'maximum_length' => '160',
						'custom_text_pos' => '',
						'include_link' => true,
						'url_from_meta' => false,
						'short_url' => true,
						'hashtags' => '',
						'image' => true,
					),
					'twitter' => array(
						'post_content' => '',
						'maximum_length' => '160',
						'custom_text_pos' => '',
						'include_link' => true,
						'url_from_meta' => false,
						'short_url' => true,
						'hashtags' => '',
						'image' => true,
					),
					'linkedin' => array(
						'post_content' => '',
						'maximum_length' => '160',
						'custom_text_pos' => '',
						'include_link' => true,
						'url_from_meta' => false,
						'short_url' => true,
						'hashtags' => '',
						'image' => true,
					),
					'tumblr' => array(
						'post_content' => '',
						'maximum_length' => '160',
						'custom_text_pos' => '',
						'include_link' => true,
						'url_from_meta' => false,
						'short_url' => true,
						'hashtags' => '',
						'image' => true,
					),
				)
			);
		}// End if().

		return self::$instance;
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
	    if ( isset( $service_name ) && $service_name != false && isset( self::instance()->post_format[$service_name] ) ) {
	        return self::instance()->post_format[$service_name];
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
	public static function distroy_instance() {
		static::$instance = null;
	}
}
