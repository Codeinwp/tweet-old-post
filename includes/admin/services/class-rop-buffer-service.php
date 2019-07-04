<?php

if ( ! function_exists( 'is_plugin_active' ) ){
     require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

if ( is_plugin_active( 'rop-buffer-addon/rop-buffer-addon.php' ) ) {
		include ROP_BUFFER_ADDON;
}else{
	return;
}
