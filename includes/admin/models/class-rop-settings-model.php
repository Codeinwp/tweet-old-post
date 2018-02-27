<?php
/**
 * The model for the general settings of the plugin.
 *
 * @link       https://themeisle.com
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/admin/models
 */

/**
 * Class Rop_Settings_Model
 */
class Rop_Settings_Model extends Rop_Model_Abstract {

	/**
	 * Holds the general settings data.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $settings The settings array.
	 */
	private $settings = array();

	/**
	 * Rop_Settings_Model constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function __construct() {
		parent::__construct();
		$this->get_settings();
	}

	/**
	 * Utility method to retrieve settings form DB
	 * and merge them with the global defaults,
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array
	 */
	public function get_settings() {
		$global_settings = new Rop_Global_Settings();
		$default = $global_settings->get_default_settings();
		$this->settings = wp_parse_args( $this->get( 'general_settings' ), $default );
		// $this->settings = wp_parse_args( array(), $default );
		return $this->settings;
	}

	/**
	 * Method to save general settings.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $data The array data to save.
	 * @return mixed
	 */
	public function save_settings( $data = array() ) {
		unset( $data['available_post_types'] );
	    return $this->set( 'general_settings', $data );
	}

	/**
	 * Method to retrieve the default interval that should be used.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_interval() {
		return $this->settings['default_interval'];
	}

	/**
	 * Method to retrieve if Google Analytics tracking should be used.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_ga_tracking() {
		return $this->settings['ga_tracking'];
	}

	/**
	 * Getter for minimum post age.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_minimum_post_age() {
		return $this->settings['minimum_post_age'];
	}

	/**
	 * Getter for maximum post age.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_maximum_post_age() {
		return $this->settings['maximum_post_age'];
	}

	/**
	 * Getter for number of posts to use per. account.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_number_of_posts() {
	    return $this->settings['number_of_posts'];
	}

	/**
	 * Getter for more than once flag.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_more_than_once() {
		return $this->settings['more_than_once'];
	}

	/**
	 * Getter for selected post types.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_selected_post_types() {
		return $this->settings['selected_post_types'];
	}

	/**
	 * Getter for selected taxonomies.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_selected_taxonomies() {
		return $this->settings['selected_taxonomies'];
	}

	/**
	 * Getter for excluded taxonomies.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_exclude_taxonomies() {
		return $this->settings['exclude_taxonomies'];
	}

	/**
	 * Getter for selected posts.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_selected_posts() {
		return $this->settings['selected_posts'];
	}

	/**
	 * Getter for excluded posts.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_exclude_posts() {
		return $this->settings['exclude_posts'];
	}

	/**
	 * Getter for the post limit.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_post_limit() {
		return $this->settings['post_limit'];
	}

	/**
	 * Getter for the beta user option.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_beta_user() {
		return $this->settings['beta_user'];
	}

	/**
	 * Getter for remote check option.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_remote_check() {
		return $this->settings['remote_check'];
	}

	/**
	 * Getter for custom messages option.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_custom_messages() {
		return $this->settings['custom_messages'];
	}
}
