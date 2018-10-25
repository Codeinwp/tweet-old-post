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
	 * Holds the logger
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     Rop_Logger $logger The logger handler.
	 */
	protected $logger;
	/**
	 * Holds the general settings data.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $settings The settings array.
	 */
	private $settings = array();
	/**
	 * Holds the defaults settings data.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $settings The settings array.
	 */
	private $defaults = array();

	/**
	 * Rop_Settings_Model constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function __construct() {
		parent::__construct();
		$this->setup_settings();
	}

	/**
	 * Setup settings var.
	 */
	private function setup_settings() {
		$global_settings = new Rop_Global_Settings();
		$this->logger    = new Rop_Logger();
		$default         = $global_settings->get_default_settings();
		$this->defaults  = $default;
		$settings        = wp_parse_args( $this->get( 'general_settings' ), $default );
		$this->settings  = $this->normalize_settings( $settings );
	}

	/**
	 * Normalize settings.
	 */
	private function normalize_settings( $settings ) {

		foreach ( $settings as $key => $setting ) {
			if ( ! is_array( $setting ) ) {
				continue;
			}
			$settings[ $key ] = array_map(
				function ( $value ) {
					if ( ! is_numeric( $value ) ) {
						return $value;
					}
					$value['value'] = intval( $value['value'] );

					return $value;

				},
				$setting
			);
		}
		if ( empty( $settings['selected_post_types'] ) ) {
			$settings['selected_post_types'] = $this->defaults['selected_post_types'];
		}

		return $settings;
	}

	/**
	 * Utility method to retrieve settings form DB
	 * and merge them with the global defaults,
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param bool $include_dynamic Either we include the dinamyc settings or not.
	 *
	 * @return array
	 */
	public function get_settings( $include_dynamic = false ) {
		/**
		 * Load dynamic lists.
		 */
		if ( $include_dynamic ) {
			$this->settings['available_taxonomies'] = $this->get_available_taxonomies( $this->get_selected_post_types() );
			$this->settings['available_post_types'] = $this->get_available_post_types();
		}

		return $this->settings;
	}

	/**
	 * Defines the available taxonomies.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array
	 */
	public function get_available_taxonomies( $selected_post_types ) {
		$post_selector = new Rop_Posts_Selector_Model();
		$taxonomies    = $post_selector->get_taxonomies( $selected_post_types );

		return $taxonomies;
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
	 * Defines the available post types.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return array
	 */
	public function get_available_post_types() {

		$args             = array( 'public' => true, 'show_ui' => true );
		$post_types       = get_post_types( $args, 'objects' );
		$post_types_array = array();
		$selected         = $this->get_selected_post_types();
		$selected         = wp_list_pluck( $selected, 'value' );
		foreach ( $post_types as $type ) {

			array_push(
				$post_types_array,
				array(
					'name'     => $type->label,
					'value'    => $type->name,
					'selected' => in_array( $type->name, $selected ),
				)
			);
		}

		return $post_types_array;
	}

	/**
	 * Getter for start time types.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return  int
	 */
	public function get_start_time() {
		return $this->settings['start_time'];
	}

	/**
	 * Getter for minimum post age.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_minimum_post_age() {
		return intval( $this->settings['minimum_post_age'] );
	}

	/**
	 * Getter for maximum post age.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_maximum_post_age() {
		return intval( $this->settings['maximum_post_age'] );
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
		return (bool) $this->settings['more_than_once'];
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
	 * Add one post or a list of posts to the excluded posts list.
	 *
	 * @since   8.0.4
	 * @access  public
	 *
	 * @param int|array $post_id Post id.
	 *
	 * @return bool
	 */
	public function add_excluded_posts( $post_id ) {
		if ( ! is_numeric( $post_id ) && ! is_array( $post_id ) ) {
			return false;
		}
		$posts = $this->get_selected_posts();
		$check = wp_list_pluck( $posts, 'value' );
		if ( is_numeric( $post_id ) ) {
			$post_id = intval( $post_id );
			$post_id = array(
				$post_id,
			);
		}
		$post_id                = array_map(
			function ( $value ) {
				return array(
					'value' => intval( $value ),
				);
			},
			$post_id
		);
		$post_id                = array_filter(
			$post_id,
			function ( $value ) use ( $check ) {
				return ! in_array( $value['value'], $check );
			}
		);
		$posts                  = array_merge( $posts, $post_id );
		$data['selected_posts'] = $posts;
		$this->save_settings( $data );

		return true;
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
	 * Method to save general settings.
	 *
	 * @since   8.0.0
	 * @access  public
	 *
	 * @param   array $data The array data to save.
	 *
	 * @return mixed
	 */
	public function save_settings( $data = array() ) {
		$data = $this->validate_settings( $data );
		$data = wp_parse_args( $data, $this->settings );

		/**
		 * Check if we need to update timeline.
		 */
		if ( $this->get_interval() != $data['default_interval'] ) {
			$schedule = new Rop_Scheduler_Model();
			$schedule->refresh_events();
		}

		$this->settings = $data;
		unset( $data['available_post_types'] );
		$this->set( 'general_settings', $data );
		$queue = new Rop_Queue_Model();
		$queue->clear_queue();
	}

	/**
	 * Sanitize settings data.
	 *
	 * @param array $data Data to validate.
	 *
	 * @return mixed Sanitized data.
	 */
	private function validate_settings( $data ) {
		if ( isset( $data['default_interval'] ) ) {
			$data['default_interval'] = floatval( $data['default_interval'] );
			if ( $data['default_interval'] < 0.1 ) {
				$this->logger->alert_error( 'Minimum interval between consecutive shares is 6mins.' );
				$data['default_interval'] = 0.1;
			}
			$data['default_interval'] = round( $data['default_interval'], 1 );
		}
		if ( empty( $data['selected_post_types'] ) ) {
			$this->logger->alert_error( 'You need to have at least one post type to share.' );
			$data['selected_post_types'] = $this->defaults['selected_post_types'];
		}
		if ( isset( $data['number_of_posts'] ) ) {
			$data['number_of_posts'] = intval( $data['number_of_posts'] );
			if ( $data['number_of_posts'] < 0 ) {
				$this->logger->alert_error( 'Minimum posts to share is 1.' );
				$data['number_of_posts'] = 1;
			}
			if ( $data['number_of_posts'] > 5 ) {
				$this->logger->alert_error( 'Maximum posts to share is 5.' );
				$data['number_of_posts'] = 5;
			}
		}

		return $data;
	}

	/**
	 * Method to retrieve the default interval that should be used.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_interval() {
		return round( $this->settings['default_interval'], 1 );
	}

	/**
	 * Remove post id from excluded list.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function remove_excluded_posts( $post_id ) {
		if ( ! is_numeric( $post_id ) ) {
			return false;
		}
		$post_id = intval( $post_id );
		$posts   = $this->get_selected_posts();
		$values  = wp_list_pluck( $posts, 'value' );
		$key     = array_search( $post_id, $values );
		if ( $key === false ) {
			return false;
		}
		unset( $posts[ $key ] );
		$posts                  = array_values( $posts );
		$data['selected_posts'] = $posts;
		$this->save_settings( $data );

		return true;
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
	 * Method to retrieve if Google Analytics tracking should be used.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_ga_tracking() {
		return isset( $this->settings['ga_tracking'] ) ? $this->settings['ga_tracking'] : false;
	}

	/**
	 * Getter for custom messages option.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @return mixed
	 */
	public function get_custom_messages() {
		return isset( $this->settings['custom_messages'] ) ? $this->settings['custom_messages'] : false;
	}

	/**
	 * Getter for instant sharing option.
	 *
	 * @since   8.1.1
	 * @access  public
	 * @return mixed
	 */
	public function get_instant_sharing() {
		return isset( $this->settings['instant_share'] ) ? $this->settings['instant_share'] : false;
	}

	/**
	 * Getter for instant sharing enabled by default option.
	 *
	 * @since   8.1.3
	 * @access  public
	 * @return bool
	 */
	public function get_instant_sharing_by_default() {
		return isset( $this->settings['instant_share_default'] ) ? $this->settings['instant_share_default'] : false;
	}

}
