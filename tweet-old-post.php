<?php   
#     /* 
#     Plugin Name: Tweet old post
#     Plugin URI: http://themeisle.com/plugins/tweet-old-post-lite/
#     Description: Wordpress plugin that helps you to keeps your old posts alive by tweeting about them and driving more traffic to them from twitter. It also helps you to promote your content. You can set time and no of tweets to post to drive more traffic.For questions, comments, or feature requests, <a href="http://themeisle.com/contact/?utm_source=plugindesc&utm_medium=announce&utm_campaign=top">contact </a> us!
#     Author: ThemeIsle 
#     Version: 6.7
#     Author URI: http://themeisle.com/
#     */  

// Config Constants
define("PLUGINPATH", realpath(dirname(__FILE__) ));
define("CSSFILE", plugins_url('css/style.css',__FILE__ ));
define("JSFILE", plugins_url('js/master.js',__FILE__ ));
define("JSCOUNTDOWN", plugins_url('js/countdown.js',__FILE__ ));

// Require core.
require_once(PLUGINPATH."/inc/core.php");
// Require core.
require_once(PLUGINPATH."/inc/exclude-posts.php");
if (!class_exists('TAV_Remote_Notification_Client')) {
	require( PLUGINPATH.'/inc/class-remote-notification-client.php' );
}
$notification = new TAV_Remote_Notification_Client( 37, 'a8be784b898fa2fb', 'http://themeisle.com?post_type=notification' );
// Clear scheduled tweets on plugin deactivation
register_deactivation_hook(__FILE__, array($CWP_TOP_Core, 'deactivationHook'));
// Reset all settings on plugin activation.
register_activation_hook(__FILE__, array($CWP_TOP_Core, 'resetAllOptions'));