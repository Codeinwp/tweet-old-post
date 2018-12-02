<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      8.0.0
 * @package    Rop
 * @subpackage Rop/includes
 * @author     ThemeIsle <friends@themeisle.com>
 */
class Rop {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    8.0.0
	 * @access   protected
	 * @var      Rop_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    8.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    8.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    8.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'rop';
		$this->version     = '8.1.4';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Rop_Loader. Orchestrates the hooks of the plugin.
	 * - Rop_i18n. Defines internationalization functionality.
	 * - Rop_Admin. Defines all hooks for the admin area.
	 * - Rop_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    8.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		$this->loader = new Rop_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Rop_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    8.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Rop_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    8.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Rop_Admin( $this->get_plugin_name(), $this->get_version() );

		$tutorial_pointers = new Rop_Pointers();

		$this->loader->add_action( 'admin_init', $plugin_admin, 'legacy_auth', 2 );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_head', $tutorial_pointers, 'rop_pointer_button_css' );
		$this->loader->add_action( 'admin_enqueue_scripts', $tutorial_pointers, 'rop_setup_pointer_support' );
		$this->loader->add_action( 'admin_print_footer_scripts', $tutorial_pointers, 'rop_enqueue_pointers' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'menu_pages' );
		$this->loader->add_action( 'rop_cron_job', $plugin_admin, 'rop_cron_job' );
		$this->loader->add_action( 'rop_cron_job_publish_now', $plugin_admin, 'rop_cron_job_publish_now' );
		$this->loader->add_action( 'wp_loaded', $this, 'register_service_api_endpoints', 1 );

		$this->loader->add_action( 'wp_loaded', $this, 'upgrade', 2 );

		$rop_cron_helper = new Rop_Cron_Helper();
		/**
		 * Use PHP_INT_MAX to make sure the schedule is added. Some plugins add their schedule by clearing the previous values.
		 */
		$this->loader->add_filter( 'cron_schedules', $rop_cron_helper, 'rop_cron_schedules', PHP_INT_MAX );
		$this->loader->add_action( 'post_submitbox_misc_actions', $plugin_admin, 'publish_now_upsell' );
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     8.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     8.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Method used to register API endpoints later.
	 * Before it did not register some endpoints.
	 *
	 * @since   8.0.0rc
	 * @access  public
	 * @throws Exception An exception is thrown if a service can not be built.
	 */
	public function register_service_api_endpoints() {
		$plugin_rest_api = new Rop_Rest_Api();
		$plugin_rest_api->register();

		$factory         = new Rop_Services_Factory();
		$global_settings = new Rop_Global_Settings();
		foreach ( $global_settings->get_all_services_handle() as $service ) {
			try {
				${$service . '_service'} = $factory->build( $service );
				${$service . '_service'}->expose_endpoints();
			} catch ( Exception $exception ) {
				// Service can't be built. Not found or otherwise. Maybe log this.
				$log = new Rop_Logger();
				$log->warn( 'The service "' . $service . '" can NOT be built or was not found', $exception->getMessage() );
			}
		}

	}

	/**
	 * Upgrade method called by plugins_loaded hook.
	 *
	 * @since   8.0.0
	 * @access  public
	 */
	public function upgrade() {
		$upgrade_helper = new Rop_Db_Upgrade();
		if ( $upgrade_helper->is_upgrade_required() ) {
			$upgrade_helper->do_upgrade();
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    8.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     8.0.0
	 * @return    Rop_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

}
