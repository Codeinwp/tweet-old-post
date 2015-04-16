<?php
#     /*
#     Plugin Name: Revive Old Post (Former Tweet Old Post)
#     Plugin URI: https://themeisle.com/plugins/tweet-old-post-lite/
#     Description: Wordpress plugin that helps you to keeps your old posts alive by sharing them and driving more traffic to them from twitter/facebook or linkedin. It also helps you to promote your content. You can set time and no of posts to share to drive more traffic.For questions, comments, or feature requests, <a href="https://themeisle.com/contact/?utm_source=plugindesc&utm_medium=announce&utm_campaign=top">contact </a> us!
#     Author: ThemeIsle
#     Version: 7.0.4
#     Author URI: https://themeisle.com/
#	  Text Domain: tweet-old-post
#	  Domain Path: /languages
#     */

// Config Constants
define("ROPPLUGINPATH", realpath(dirname(__FILE__) ));
define("ROPCSSFILE", plugins_url('css/style.css',__FILE__ ));
define("ROPCUSTOMDASHBOARDICON", plugins_url("css/custom_dashboard_icon.css", __FILE__));
define("ROPJSFILE", plugins_url('js/master.js',__FILE__ ));
define("ROPJSCOUNTDOWN", plugins_url('js/countdown.js',__FILE__ ));
define("ROPPLUGINBASENAME", plugin_basename(__FILE__));
define('ROP_TOP_FB_API_VERSION','v2.0');
define('ROP_VERSION','7.0.3');
// Require core.
require_once(ROPPLUGINPATH."/inc/core.php");
// Require core.
require_once(ROPPLUGINPATH."/inc/exclude-posts.php");
if (!class_exists('TAV_Remote_Notification_Client')) {
	require( ROPPLUGINPATH.'/inc/class-remote-notification-client.php' );
}
if (CWP_TOP_PRO)
    $notification = new TAV_Remote_Notification_Client( 37, 'a8be784b898fa2fb', 'https://themeisle.com?post_type=notification' );
else
    $notification = new TAV_Remote_Notification_Client( 38, 'b7fbcc8d0c58614a', 'https://themeisle.com?post_type=notification' );

// Clear scheduled tweets on plugin deactivation
register_deactivation_hook(__FILE__, array($CWP_TOP_Core, 'deactivationHook'));

// Reset all settings on plugin activation.
register_activation_hook(__FILE__, array($CWP_TOP_Core, 'resetAllOptions'));

add_action("admin_head", array($CWP_TOP_Core, 'rop_load_dashboard_icon'));
