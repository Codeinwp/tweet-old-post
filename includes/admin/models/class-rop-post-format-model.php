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
     * Rop_Post_Format_Model constructor.
     *
     * @since   8.0.0
     * @access  public
     * @param   bool|string The name of the service. Default false. Returns all.
     */
    public function __construct( $service_name = false ) {
        parent::__construct();
        $this->get_post_format( $service_name );
    }

    /**
     * Utility method to retrieve post_format from DB
     * and merge them with the global defaults,
     *
     * @since   8.0.0
     * @access  public
     * @param   string  $account_id The account ID for which to retrieve options.
     * @param   bool|string $service_name The name of the service if any.
     * @return array
     */
    public function get_post_format( $account_id, $service_name ) {
        $global_settings = new Rop_Global_Settings();
        $default = $global_settings->get_default_post_format( $service_name );
        $post_format_from_db = $this->get( 'post_format' );
        $selected_post_format = array();
        if ( isset( $post_format_from_db[$account_id] ) ) {
            $selected_post_format = $post_format_from_db[$account_id];
        }
        $this->post_format = wp_parse_args( $selected_post_format, $default );
        return $this->post_format;
    }

    public function add_post_format( $account_id ) {

    }

    public function remove_post_format( $account_id ) {
        
    }

}