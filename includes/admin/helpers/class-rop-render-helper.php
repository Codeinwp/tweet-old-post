<?php
class Rop_Render_Helper {

    protected $root = ROP_PATH;
    protected $core_admin = '/includes/admin/';
    protected $core_public = '/includes/public/';

    protected $partials_dir = 'partials';
    protected $views_dir = 'views';
    protected $theme_dir = 'rop_views';

    protected $default_file = 'default.php';

    private $base_path;
    private $allowed = array();

    public function __construct() {
        $this->set_base_path();
    }

    public function set_base_path( $public = false ) {
        $this->base_path = $this->root . $this->core_admin . $this->views_dir . '/';
        if( $public ) {
            $this->base_path = $this->root . $this->core_public . $this->views_dir . '/';
        }
    }

    public function allowed_file_names( array $file_names ) {
        $this->allowed = $file_names;
    }

    private function is_allowed_file( $file ) {
        $default = $this->default_file;
        if( in_array( $file, $this->allowed ) ) {
            return $file;
        }
        return $default;
    }

    private function sanitize_name( $var_name ) {
        if( is_numeric( $var_name ) ) {
            $var_name = '_' . number_format( $var_name , 0 );
        }

        $var_name = strtolower( $var_name );
        $var_name = str_replace(
            array(
                ';', ':', '\\', '"', '\'', '/',
                '$', '|', '@', '#', '%', '^', '&',
                '*', '(', ')', '[', ']', '{', '}',
                '.', ',', '?', '<', '>', '~', '`',
                '+', '-'
            ),
            '',
            $var_name
        );
        $var_name = preg_replace( '/\s+/', '_', $var_name );
        $safe_name = str_replace( '-', '_', $var_name );

        return $safe_name;
    }

    private function get_file( $name = '', $path = '' ) {
        $file_name = $name . '.php';
        $file_name = $this->is_allowed_file( $file_name );
        $default_path = $this->base_path . $path . '/';
        $theme_path = get_template_directory() . '/' . $this->theme_dir . '/';
        $child_theme_path = get_stylesheet_directory() . '/' . $this->theme_dir . '/';

        $directory = $default_path;
        if( file_exists( $theme_path ) ) {
            $directory = $theme_path;
        }
        if( file_exists( $child_theme_path ) ) {
            $directory = $child_theme_path;
        }
        $file = apply_filters( 'rop_views_dir', $directory, $file_name ) . $file_name;
        if( ! file_exists( $file ) ) {
            $file =  $default_path . $file_name;
        }

        return $file;
    }

    private function render( $name, $args, $type = '' ) {
        ob_start();
        $file = $this->get_file( $name, $type );
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

    public function render_partial( $name = '', $args = array() ) {
        return $this->render( $name, $args, $this->partials_dir );
    }

    public function render_view( $name = '', $args = array() ) {
        return $this->render( $name, $args, '' );
    }

}