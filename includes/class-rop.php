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
		$this->version     = '8.5.6';

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
		$this->loader->add_action( 'admin_head', $plugin_admin, 'rop_roadmap_new_tab' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'rop_dismiss_rop_event_not_firing_notice' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'rop_cron_event_status_notice' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'rop_dismiss_buffer_addon_disabled_notice' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'rop_buffer_addon_notice' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'rop_dismiss_linkedin_api_v2_notice' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'rop_linkedin_api_v2_notice' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'bitly_shortener_upgrade_notice' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'rop_dismiss_cron_disabled_notice' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'rop_wp_cron_notice' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'rop_shortener_changed_disabled_notice' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'rop_shortener_changed_notice' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'rop_update_shortener' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'migrate_taxonomies_to_post_format' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_head', $tutorial_pointers, 'rop_pointer_button_css' );
		$this->loader->add_action( 'admin_enqueue_scripts', $tutorial_pointers, 'rop_setup_pointer_support' );
		$this->loader->add_action( 'admin_print_footer_scripts', $tutorial_pointers, 'rop_enqueue_pointers' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'menu_pages' );
		$this->loader->add_action( 'rop_cron_job', $plugin_admin, 'rop_cron_job' );
		$this->loader->add_action( 'rop_cron_job_once', $plugin_admin, 'rop_cron_job_once' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'check_cron_status', 20 );
		$this->loader->add_action( 'rop_cron_job_publish_now', $plugin_admin, 'rop_cron_job_publish_now' );
		$this->loader->add_action( 'future_to_publish', $plugin_admin, 'share_scheduled_future_post', 10, 1 );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'rop_publish_now_metabox' );

		// Not being used in as of v8.5.0. Feature moved to metabox until proper Gutenberg support
		// $this->loader->add_action( 'post_submitbox_misc_actions', $plugin_admin, 'add_publish_actions' );
		// $this->loader->add_action( 'post_submitbox_misc_actions', $plugin_admin, 'publish_now_upsell' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'maybe_publish_now' );
		$this->loader->add_filter( 'rop_publish_now_attributes', $plugin_admin, 'publish_now_attributes' );

		$this->loader->add_action( 'wp_loaded', $this, 'register_service_api_endpoints', 1 );

		$this->loader->add_action( 'wp_loaded', $this, 'upgrade', 2 );

		// Themeisle SDK tweaks
		$this->loader->add_filter( 'tweet_old_post_feedback_review_message', $this, 'change_review_message' );
		$this->loader->add_filter( 'tweet_old_post_feedback_review_button_do', $this, 'change_review_do_message' );
		$this->loader->add_filter( 'tweet_old_post_feedback_review_button_cancel', $this, 'change_review_cancel_message' );
		$this->loader->add_filter( 'tweet-old-post_uninstall_feedback_icon', $this, 'add_icon' );
		$this->loader->add_filter( 'tweet-old-post_themeisle_sdk_disclosure_content_labels', $this, 'change_labels_uf' );

		$rop_cron_helper = new Rop_Cron_Helper();
		/**
		 * Use PHP_INT_MAX to make sure the schedule is added. Some plugins add their schedule by clearing the previous values.
		 */
		$this->loader->add_filter( 'cron_schedules', $rop_cron_helper, 'rop_cron_schedules', PHP_INT_MAX );
	}

	/**
	 * Change uninstall feedback icon, add RS one.
	 *
	 * @return string New icon url.
	 */
	public function add_icon() {
		return ROP_LITE_URL . 'assets/img/logo_rop.png';
	}

	/**
	 * Change disclosure policy labels from uninstall feedback.
	 *
	 * @return array New labels.
	 */
	public function change_labels_uf() {

		return array(
			'title' => __( 'Below is a detailed view of all data that ReviveSocial will receive if you fill in this survey. No domain name, email address or IP addresses are transmited after you submit the survey.', 'tweet-old-post' ),
		);
	}

	/**
	 * Change review confirm text.
	 *
	 * @return string New text.
	 */
	public function change_review_do_message() {
		return __( 'Sure!', 'tweet-old-post' );
	}

	/**
	 * Change cancel button text.
	 *
	 * @return string New message.
	 */
	public function change_review_cancel_message() {
		return __( 'No, thanks', 'tweet-old-post' );
	}

	/**
	 * Change old message asking for review.
	 *
	 * @param string $old_message Old message.
	 *
	 * @return string New message.
	 */
	public function change_review_message( $old_message ) {
		return __( 'Hi there, <br/><strong>Revive Social</strong> team here, we noticed you\'ve been using our plugin for a while now, has it been a great help? If so, would you mind leaving us a review? It would help a ton, thanks!<br/>', 'tweet-old-post' );
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

			// Skip if the buffer addon is not active.
			if ( ! class_exists( 'Rop_Buffer_Service' ) && $service === 'buffer' ) {
				continue;
			}

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
