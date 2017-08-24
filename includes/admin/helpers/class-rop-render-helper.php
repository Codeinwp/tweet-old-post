<?php
class Rop_Render_Helper {

    protected $root = ROP_PATH;
    protected $core_admin = '/includes/admin/views/';
    protected $core_public = '/includes/public/views/';
    private $base_path;

    public function __construct() {
        $this->set_base_path();
    }

    public function set_base_path( $public = false ) {
        $this->base_path = $this->root . $this->core_admin;
        if( $public ) {
            $this->base_path = $this->root . $this->core_public;
        }
    }

    private function is_valid_name( $name ) {

    }

    private function sanitize_name( $var_name ) {
        $safe_name = $var_name;
        if( is_numeric( $var_name ) ) {
            $safe_name =
        }

        return $safe_name;
    }

    private function get_file( $name = '', $path = '' ) {
        $file_name = $name . '-tpl.php';
        $default_path = $this->base_path . $path . '/';
        $theme_path = get_template_directory() . '/rop_views/';
        $child_theme_path = get_stylesheet_directory() . '/rop_views/';

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

    public function render_partial( $name = '', $args = array() ) {
        ob_start();
        $file = $this->get_file( $name, 'partials' );
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

    public function render_view( $name = '', $args = array() ) {
        ob_start();
        $file = $this->get_file( $name );
        if ( ! empty( $args ) ) {
            foreach ( $args as $var_name => $var_value ) {
                $$var_name = $var_value;
            }
        }
        if ( file_exists( $file ) ) {
            include $file;
        }
        return ob_get_clean();
    }

}

$rh = new Rop_Render_Helper();
$rh->render_view( 'default', array(
    'name' => $name
) );