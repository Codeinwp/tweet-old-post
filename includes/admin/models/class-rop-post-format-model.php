<?php
/**
 * The model for the post format options of the plugin.
 *
 * @link       https://themeisle.com
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/admin/models
 */

/**
 * Class Rop_Post_Format_Model
 */
class Rop_Post_Format_Model extends Rop_Model_Abstract {

	/**
	 * Holds the post format data.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $post_format The post format options array.
	 */
	private $post_format = array();

	/**
	 * Holds the default post format global options.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $defaults The post format default options.
	 */
	private $defaults;

	/**
	 * Holds the current service name.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $service_name The current service name.
	 */
	private $service_name;

	/**
	 * Rop_Post_Format_Model constructor.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $service_name The name of the service. Default false. Returns all.
	 */
	public function __construct( $service_name = false ) {
		parent::__construct();
		$global_settings    = new Rop_Global_Settings();
		$this->defaults     = $global_settings->get_default_post_format( $service_name );
		$this->service_name = $service_name;
		$this->post_format  = ( $this->get( 'post_format' ) != null ) ? $this->get( 'post_format' ) : array();
	}

	/**
	 * Utility method to retrieve post_format from DB
	 * and merge them with the global defaults,
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $account_id The account ID for which to retrieve options.
	 * @return array
	 */
	public function get_post_format( $account_id = false ) {
		$post_format_from_db  = $this->get( 'post_format' );
		$selected_post_format = array();
		if ( $account_id != false && isset( $post_format_from_db[ $account_id ] ) ) {
			$selected_post_format = $post_format_from_db[ $account_id ];
		}
		return wp_parse_args( $selected_post_format, $this->defaults );
	}

	/**
	 * Method to add a post format per. account.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $account_id The account ID for which to add post format.
	 * @param   array  $data The post format data.
	 * @return bool
	 */
	public function add_update_post_format( $account_id, $data ) {
		$data                             = wp_parse_args( $data, $this->defaults );
		$this->post_format[ $account_id ] = $data;
		return $this->set( 'post_format', $this->post_format );
	}

	/**
	 * Method to remove a post format per. account.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   string $account_id The account ID for which to update post format.
	 * @return mixed
	 */
	public function remove_post_format( $account_id ) {
		unset( $this->post_format[ $account_id ] );
		return $this->set( 'post_format', $this->post_format );
	}

}
