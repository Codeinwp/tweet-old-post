<?php
#     /*
#     Plugin Name: Revive Old Post (Former Tweet Old Post)
#     Plugin URI: https://revive.social/
#     Description: Wordpress plugin that helps you to keeps your old posts alive by sharing them and driving more traffic to them from twitter/facebook or linkedin. It also helps you to promote your content. You can set time and no of posts to share to drive more traffic.For questions, comments, or feature requests, <a href="http://revive.social/support/?utm_source=plugindesc&utm_medium=announce&utm_campaign=top">contact </a> us!
#     Author: revive.social
#     Version: 7.4.8
#     Author URI: https://revive.social/
#	  Text Domain: tweet-old-post
#	  Domain Path: /languages
#     */

// Config Constants
define ("ROP_PRO_URL", "http://revive.social/plugins/revive-old-post/");
define("ROPPLUGINPATH", realpath(dirname(__FILE__) ));
define("ROPCSSFILE", plugins_url('css/style.css',__FILE__ ));
define("ROPCUSTOMDASHBOARDICON", plugins_url("css/custom_dashboard_icon.css", __FILE__));
define("ROPJSFILE", plugins_url('js/master.js',__FILE__ ));
define("ROPJSCOUNTDOWN", plugins_url('js/countdown.js',__FILE__ ));
define("ROPPLUGINBASENAME", plugin_basename(__FILE__));
define('ROP_TOP_FB_API_VERSION','v2.0');
define('ROP_VERSION','7.4.8');
// Added by Ash/Upwork
define("ROP_ROOT", trailingslashit(plugins_url("", __FILE__)));
// Added by Ash/Upwork

// Require core.
require_once(ROPPLUGINPATH."/inc/core.php");
// Require core.
require_once(ROPPLUGINPATH."/inc/exclude-posts.php");

// Clear scheduled tweets on plugin deactivation
register_deactivation_hook(__FILE__, array($CWP_TOP_Core, 'deactivationHook'));

// Reset all settings on plugin activation.
register_activation_hook(__FILE__, array($CWP_TOP_Core, 'resetAllOptions'));

add_action("admin_head", array($CWP_TOP_Core, 'rop_load_dashboard_icon'));
