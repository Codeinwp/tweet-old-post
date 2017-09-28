<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Rop
 * @subpackage Rop/admin
 * @author     ThemeIsle <friends@themeisle.com>
 */
class Rop_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    8.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    8.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    8.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    8.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rop_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rop_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
        $screen = get_current_screen();
        if( in_array( $screen->id, array( 'toplevel_page_rop_main' ) ) ) {
            wp_enqueue_style($this->plugin_name . '_core', ROP_LITE_URL . 'assets/css/rop_core.css', array(), $this->version, 'all');
            wp_enqueue_style($this->plugin_name, ROP_LITE_URL . 'assets/css/rop.css', array($this->plugin_name . '_core'), $this->version, 'all');
        }

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    8.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rop_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rop_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
        $screen = get_current_screen();

        if( in_array( $screen->id, array( 'toplevel_page_rop_main' ) ) ) {
            wp_enqueue_script( $this->plugin_name . '_fa', 'https://use.fontawesome.com/af4c3f0b39.js', array(), $this->version, false );

            wp_register_script( $this->plugin_name . '_main',  ROP_LITE_URL . 'assets/js/build/rop.js', array( $this->plugin_name . '_fa' ), $this->version, false );
            wp_localize_script( $this->plugin_name . '_main', 'ROP_REST_API', rest_url( '/tweet-old-post/v8/api/' ) );
            wp_localize_script( $this->plugin_name . '_main', 'ROP_ASSETS_URL', ROP_LITE_URL . 'assets/' );
            wp_enqueue_script( $this->plugin_name . '_main' );
        }

	}

}
