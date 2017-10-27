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
		return $this->settings;
	}

	public function save_settings( $data = array() ) {
	    return $this->set( 'general_settings', $data );
	}

	public function get_minimum_post_age() {
		return $this->settings['minimum_post_age'];
	}

	public function get_maximum_post_age() {
		return $this->settings['maximum_post_age'];
	}

	public function get_number_of_posts() {
	    return $this->settings['number_of_posts'];
	}

	public function get_more_than_once() {
		return $this->settings['more_than_once'];
	}

	public function get_selected_post_types() {
		return $this->settings['selected_post_types'];
	}

	public function get_selected_taxonomies() {
		return $this->settings['selected_taxonomies'];
	}

	public function get_exclude_taxonomies() {
		return $this->settings['exclude_taxonomies'];
	}

	public function get_selected_posts() {
		return $this->settings['selected_posts'];
	}

	public function get_exclude_posts() {
		return $this->settings['exclude_posts'];
	}
}
