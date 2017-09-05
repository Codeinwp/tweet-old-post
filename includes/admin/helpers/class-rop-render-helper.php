<?php
/**
 * The file that defines the render helper plugin class
 *
 * A class definition that includes attributes and functions used for rendering
 * across both the public-facing side of the site and the admin area.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes/admin/helpers
 */

/**
 * Class Rop_Render_Helper
 *
 * @link    https://themeisle.com/
 * @since   8.0.0
 */
class Rop_Render_Helper {

	/**
	 * Stores the root of the plugin.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $root The root of the plugin.
	 */
	protected $root = ROP_PATH;

	/**
	 * Stores the path to admin.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $core_admin The path to admin.
	 */
	protected $core_admin = '/includes/admin/';

	/**
	 * Stores the path to public.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $core_public The path to public.
	 */
	protected $core_public = '/includes/public/';

	/**
	 * Stores the name of the partials folder.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $partials_dir The name of the partials folder.
	 */
	protected $partials_dir = 'partials';

	/**
	 * Stores the name of the views folder.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $views_dir The name of the views folder.
	 */
	protected $views_dir = 'views';

	/**
	 * Stores the name of the folder in user themes
	 * where we can look for custom views and partials.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $theme_dir The name of the theme custom folder.
	 */
	protected $theme_dir = 'rop_views';

	/**
	 * Defines the default name for a file that is not allowed.
	 *
	 * @since   8.0.0
	 * @access  protected
	 * @var     string $default_file The default file name.
	 */
	protected $default_file = 'default.php';

	/**
	 * Holds the base path for the requested view or partial.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     string $base_path The base path for the active request.
	 */
	private $base_path;

	/**
	 * Holds an array of allowed files for the request.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @var     array $allowed The files allowed for import.
	 */
	private $allowed = array();

	/**
	 * Rop_Render_Helper constructor.
	 *
	 * @since   8.0.0
	 * @access  public
     * @param   boolean $is_vue Determine if is Vue.js view/template file.
	 */
	public function __construct( $is_vue = false ) {
	    if( $is_vue ) {
            $this->set_base_path_vue();
        } else {
            $this->set_base_path();
        }
	}

	/**
	 * Utility method to define the base path.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   bool $public Flag for specifying if base is admin or public.
	 */
	public function set_base_path( $public = false ) {
		$this->base_path = $this->root . $this->core_admin . $this->views_dir . '/';
		if ( $public ) {
			$this->base_path = $this->root . $this->core_public . $this->views_dir . '/';
		}
	}

    /**
     * Utility method to define the base path for Vue.js.
     *
     * @since   8.0.0
     * @access  public
     * @param   bool $public Flag for specifying if base is admin or public.
     */
    public function set_base_path_vue( $public = false ) {
        $this->partials_dir = 'vue_templates';
        $this->base_path = $this->root . $this->core_admin . 'vue_' . $this->views_dir . '/';
        if ( $public ) {
            $this->base_path = $this->root . $this->core_public . 'vue_' . $this->views_dir . '/';
        }
    }

	/**
	 * Utility method to define allowed files for import.
	 *
	 * @since   8.0.0
	 * @access  public
	 * @param   array $file_names The array of files with ext.
	 */
	public function allowed_file_names( array $file_names ) {
		$this->allowed = $file_names;
	}

	/**
	 * Method to check if file is allowed or default should be returned.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   string $file The file to check.
	 * @return string
	 */
	private function is_allowed_file( $file ) {
		$default = $this->default_file;
		if ( in_array( $file, $this->allowed ) ) {
			return $file;
		}
		return $default;
	}

	/**
	 * Method to sanitize variable names passed to template.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   mixed $var_name The variable name.
	 * @return string
	 */
	private function sanitize_name( $var_name ) {
		if ( is_numeric( $var_name ) ) {
			$var_name = '_' . number_format( $var_name , 0 );
		}

		$var_name = strtolower( $var_name );
		$var_name = str_replace(
			array( ';', ':', '\\', '"', '\'', '/', '$', '|', '@', '#', '%', '^', '&', '*', '(', ')', '[', ']', '{', '}', '.', ',', '?', '<', '>', '~', '`', '+', '-' ),
			'',
			$var_name
		);
		$var_name = preg_replace( '/\s+/', '_', $var_name );
		$safe_name = str_replace( '-', '_', $var_name );

		return $safe_name;
	}

	/**
	 * Method to retrieve a file form the name provided.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   string $name The file name w/o ext.
	 * @param   string $path Optional. A path to be appended to base path.
	 * @return string
	 */
	private function get_file( $name = '', $path = '' ) {
		$file_name = $name . '.php';
		//$file_name = $this->is_allowed_file( $file_name );
		$default_path = $this->base_path . $path . '/';
		$theme_path = get_template_directory() . '/' . $this->theme_dir . '/';
		$child_theme_path = get_stylesheet_directory() . '/' . $this->theme_dir . '/';

		$directory = $default_path;
		if ( file_exists( $theme_path ) ) {
			$directory = $theme_path;
		}
		if ( file_exists( $child_theme_path ) ) {
			$directory = $child_theme_path;
		}
		$file = apply_filters( 'rop_views_dir', $directory, $file_name ) . $file_name;
		if ( ! file_exists( $file ) ) {
			$file = $default_path . $file_name;
		}

		return $file;
	}

	/**
	 * Utility method to return the template specified.
	 *
	 * @since   8.0.0
	 * @access  private
	 * @param   string $name The name of the template.
	 * @param   string $args Params to be passed to template.
	 * @param   string $path Optional. A path option to be passed to get_file() method.
	 * @return string
	 */
	private function render( $name, $args, $path = '' ) {
		ob_start();
		$file = $this->get_file( $name, $path );
		if ( ! empty( $args ) ) {
			foreach ( $args as $var_name => $var_value ) {
				$var_name = $this->sanitize_name( $var_name );
				$$var_name = $var_value;
			}
		}
		if ( file_exists( $file ) ) {
			include $file;
		}
		return ob_get_clean();
	}

	/**
	 * Utility method to render a partial template.
	 *
	 * @since   8.0.0
	 * @acces   public
	 * @param   string $name The name of the partial template.
	 * @param   array  $args Optional. Params to be passed to template.
	 * @return string
	 */
	public function render_partial( $name = '', $args = array() ) {
		return $this->render( $name, $args, $this->partials_dir );
	}

	/**
	 * Utility method to render a view template.
	 *
	 * @since   8.0.0
	 * @acces   public
	 * @param   string $name The name of the view template.
	 * @param   array  $args Optional. Params to be passed to template.
	 * @return string
	 */
	public function render_view( $name = '', $args = array() ) {
		return $this->render( $name, $args, '' );
	}

}
