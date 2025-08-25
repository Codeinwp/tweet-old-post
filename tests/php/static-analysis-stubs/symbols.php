<?php
define( 'ROP_CRON_ALTERNATIVE', WP_DEBUG );

define( 'ROP_LITE_VERSION', '9.3.2' );
define( 'ROP_LITE_BASE_FILE', __FILE__ );
define( 'ROP_DEBUG', WP_DEBUG );
define( 'ROP_LITE_PATH', plugin_dir_path( __FILE__ ) );
define( 'ROP_PRO_PATH', WP_PLUGIN_DIR . '/tweet-old-post-pro/' );
define( 'ROP_PATH', plugin_dir_path( __FILE__ ) );
define( 'ROP_LITE_URL', plugin_dir_url( __FILE__ ) );
define( 'ROP_STATUS_ALERT', 6 ); // How many consecutive errors count towards status alert "Status: Error (check logs)"
define( 'ROP_TEMP_IMAGES', plugin_dir_path( __FILE__ ) . 'temp-images/' ); // Path for external images downloaded for sharing
define( 'ROP_PRODUCT_SLUG', basename( ROP_PATH ) );

// Authorization APP Data
define( 'ROP_AUTH_APP_URL', 'https://app.revive.social' );
define( 'ROP_APP_FACEBOOK_PATH', '/fb_auth' );
define( 'ROP_APP_TWITTER_PATH', '/tw_auth' );
define( 'ROP_APP_LINKEDIN_PATH', '/li_auth' );
define( 'ROP_APP_TUMBLR_PATH', '/tumblr_auth' );
define( 'ROP_APP_GMB_PATH', '/gmb_auth' );
define( 'ROP_APP_VK_PATH', '/vk_auth' );
define( 'ROP_INSTALL_TOKEN_OPTION', 'rop_install_token' );
define( 'ROP_POST_SHARING_CONTROL_API', ROP_AUTH_APP_URL . '/wp-json/auth-option/v1/post-sharing-control' );
define( 'ROP_POST_ON_X_API', ROP_AUTH_APP_URL . '/wp-json/auth-option/v1/post-on-x' );
define( 'ROP_POST_LOGS_API', ROP_AUTH_APP_URL . '/wp-json/auth-option/v1/logs' );