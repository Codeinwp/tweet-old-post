<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://themeisle.com/
 * @since      8.0.0
 *
 * @package    Rop
 * @subpackage Rop/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      8.0.0
 * @package    Rop
 * @subpackage Rop/includes
 * @author     ThemeIsle <friends@themeisle.com>
 */
class Rop_I18n {
	/**
	 * Setup upsell link.
	 *
	 * @var string Upsell link.
	 */
	const UPSELL_LINK = 'https://revive.social/plugins/revive-old-post/';

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    8.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'tweet-old-post',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
		add_filter( 'rop_available_services', array( $this, 'load_service_locals' ), 99 );

	}

	/**
	 * Localize service labels.
	 *
	 * @param array $services Services available.
	 *
	 * @return mixed Services localized.
	 */
	public function load_service_locals( $services ) {
		$services['facebook']['credentials']['secret']['description'] = Rop_I18n::get_labels( 'accounts.fb_app_secret_title' );
		$services['facebook']['credentials']['app_id']['description'] = Rop_I18n::get_labels( 'accounts.fb_app_id_title' );
		$services['facebook']['description']                          = Rop_I18n::get_labels( 'accounts.fb_app_desc' );
		$services['twitter']['description']                           = Rop_I18n::get_labels( 'accounts.twt_app_desc' );

		return $services;
	}

	/**
	 * Get labels by key or return all of them.
	 *
	 * @param string $key Access key.
	 *
	 * @return array|mixed|string String localized
	 */
	public static function get_labels( $key = '' ) {
		$tw_new_name = __( 'X (Twitter)', 'tweet-old-post' );
		$labels = array(
			'accounts'    => array(
				'menu_item'                  => __( 'Accounts', 'tweet-old-post' ),
				'service_popup_title'        => __( 'Service Credentials', 'tweet-old-post' ),
				'show_advance_config'        => __( 'Use your own keys', 'tweet-old-post' ),
				'show_own_keys_config'       => __( 'Use my own API keys', 'tweet-old-post' ),
				'tw_app_signin_tooltip'      => sprintf( __( 'Due to the %1$s changes in network limits, we cannot guarantee stable sharing using this mode. We recommend using your own API keys.', 'tweet-old-post' ), $tw_new_name ),
				'tw_new_name'                => $tw_new_name,
				'fb_app_signin_btn'          => __( 'Sign in to Facebook', 'tweet-old-post' ),
				'tw_app_signin_btn'          => __( 'Sign in to X', 'tweet-old-post' ),
				'li_app_signin_btn'          => __( 'Sign in to LinkedIn', 'tweet-old-post' ),
				'tumblr_app_signin_btn'      => __( 'Sign in to Tumblr', 'tweet-old-post' ),
				'gmb_app_signin_btn'         => __( 'Sign in to Google My Business', 'tweet-old-post' ),
				'vk_app_signin_btn'          => __( 'Sign in to Vkontake', 'tweet-old-post' ),
				'app_option_signin'          => __( 'Or', 'tweet-old-post' ),
				'service_popup_title'        => __( 'Service Credentials', 'tweet-old-post' ),
				'sign_in_btn'                => __( 'Sign In', 'tweet-old-post' ),
				'field_required'             => __( 'This field is required', 'tweet-old-post' ),
				'at'                         => __( 'at', 'tweet-old-post' ),
				'remove_account'             => __( 'Remove account from the list.', 'tweet-old-post' ),
				'no_accounts'                => __( 'You Need to Connect an Account', 'tweet-old-post' ),
				'no_active_accounts'         => __( 'No active accounts!', 'tweet-old-post' ),
				'no_active_accounts_desc'    => __( 'Add one from the <b>"Accounts"</b> section.', 'tweet-old-post' ),
				'go_to_accounts_btn'         => __( 'Go to Accounts', 'tweet-old-post' ),
				'no_accounts_desc'           => __( 'Use the network buttons below to sign in and add your social media accounts to the plugin.', 'tweet-old-post' ),
				'has_accounts_desc'          => __( ' Authenticate a new service (eg. Facebook, Twitter etc. ), select the account you want to add from that service and ensure the switch is in the <b>ON</b> position. Only the active accounts will be used for sharing.', 'tweet-old-post' ),
				'add_all_cta'                => __( ' Add more accounts', 'tweet-old-post' ),
				'remove_all_cta'             => __( 'Remove all accounts', 'tweet-old-post' ),
				'accounts_selector'          => __( 'Each <b>account</b> can have it\'s own options for sharing, on the left you can see the current selected account and network, below are the options for the account. Don\'t forget to save after each change and remember, you can always reset an account to the network defaults.', 'tweet-old-post' ),
				'save_selector_btn'          => __( 'Save', 'tweet-old-post' ),
				'reset_selector_btn'         => __( 'Reset', 'tweet-old-post' ),
				'for'                        => __( 'for', 'tweet-old-post' ),
				'add_account'                => __( '<b>Add Your Accounts:</b>', 'tweet-old-post' ),
				'upsell_accounts'            => sprintf( __( 'A maximum of 1 Facebook and Twitter account can be connected to the Lite version of Revive Old Posts. Upgrade to unlock more great features including more social networks! Check out the Lite vs Pro %1$stable here%2$s.', 'tweet-old-post' ), '<a class="text-bold" href="https://docs.revive.social/article/941-revive-old-post-free-vs-pro" target="_blank">', '</a>' ),
				'activate_license'           => __( 'You need to activate your license key <a href="/wp-admin/options-general.php#tweet_old_post_pro_license" style="cursor: pointer">HERE</a> to unlock the Pro features of Revive Old Posts.', 'tweet-old-post' ),
				'fb_app_id_title'            => __( 'Please add the APP ID from your Facebook app.', 'tweet-old-post' ),
				'fb_app_secret_title'        => __( 'Please add the APP SECRET from your Facebook app.', 'tweet-old-post' ),
				'fb_app_desc'                => sprintf( __( 'You can check %1$shere%2$s for how to get these details.', 'tweet-old-post' ), '<a class="text-bold" href="https://docs.revive.social/article/349-how-to-create-a-facebook-application-for-revive-old-post" target="_blank">', '</a>' ),
				'twt_app_desc'               => sprintf( __( 'You can check %1$shere%2$s for how to get these details.', 'tweet-old-post' ), '<a class="text-bold " href="https://docs.revive.social/article/914-how-to-create-a-twitter-application-for-revive-old-post" target="_blank">', '</a>' ),
				'service_error'              => __( 'The %1$s service can not be used or was not found', 'tweet-old-post' ),
				'twitter_warning'            => __(
					'
					 You have more than one Twitter account connected. Twitter has changed it\'s policy regarding automatic posting of same content across multiple accounts. You should ensure you comply with this policy by sharing to only one twitter account at a time, or you risk getting one of your accounts BANNED. Disconnect one of your Twitter accounts to avoid having your account banned.
			',
					'tweet-old-post'
				),
				'preloader_message_accounts' => __( 'Loading Your Dashboard...', 'tweet-old-post' ),
				'preloader_message_default'  => __( 'Loading...', 'tweet-old-post' ),
				'only_in_pro'                => __( 'Available in Pro', 'tweet-old-post' ),
				'limit_reached'              => __( 'Limit reached', 'tweet-old-post' ),
			),
			'settings'    => array(
				'yes_text' => __( 'Yes', 'tweet-old-post' ),
				'available_in_pro' => __( ' This feature is only available in the Pro version.', 'tweet-old-post' ),
				'post_types_exclude_limit' => sprintf( __( 'Upgrade to Pro version to select more than 30 posts. You can upgrade %1$shere%2$s.', 'tweet-old-post' ), '<a href="https://revive.social/plugins/revive-old-post/" target="_blank">', '</a>' ),
				'post_types_exclude_limit_tooltip' => __( 'Upgrade to Pro version to select more than 30 posts.', 'tweet-old-post' ),
				'menu_item'                   => __( 'General Settings', 'tweet-old-post' ),
				'min_interval_title'          => __( 'Minimum Interval Between Shares', 'tweet-old-post' ),
				'min_interval_desc'           => __( 'How many hours between each share?', 'tweet-old-post' ),
				'min_days_title'              => __( 'Minimum Post Age', 'tweet-old-post' ),
				'min_days_desc'               => __( 'Minimum age of posts available for sharing, in days.', 'tweet-old-post' ),
				'max_days_title'              => __( 'Maximum Post Age', 'tweet-old-post' ),
				'max_days_desc'               => __( 'Maximum age of posts available for sharing, in days.', 'tweet-old-post' ),
				'no_posts_title'              => __( 'Number of Posts', 'tweet-old-post' ),
				'no_posts_desc'               => __( 'Number of posts to share per account when a share occurs.', 'tweet-old-post' ),
				'share_once_title'            => __( 'Share More Than Once?', 'tweet-old-post' ),
				'share_once_desc'             => __(
					'If all available posts have been shared to your active accounts, should we automatically restart the sharing?',
					'tweet-old-post'
				),
				'post_types_title'                      => __( 'Post Types', 'tweet-old-post' ),
				'post_types_taxonomy_limit'             => sprintf( __( 'Upgrade to Pro version to select more than 4 taxonomies. You can upgrade %1$shere%2$s.', 'tweet-old-post' ), '<a href="https://revive.social/plugins/revive-old-post/" target="_blank">', '</a>' ),
				'post_types_attachament_info'           => sprintf( __( 'You need to select the media files which you want to share, find out more information %1$shere%2$s.', 'tweet-old-post' ), '<a href="https://docs.revive.social/article/934-how-to-use-revive-old-post-media-sharing-feature" target="_blank">', '</a>' ),
				'post_types_desc'                       => __( 'Which post types should Revive Old Posts share? <a href="https://docs.revive.social/article/968-how-to-share-different-wordpress-post-types-to-social-media-w-revive-old-posts" target="_blank">Learn more</a>.', 'tweet-old-post' ),
				'update_post_published_date_title'      => __( 'Update Post Published Date After Share', 'tweet-old-post' ),
				'update_post_published_date_desc'       => sprintf( __( 'Update the post published date after it has been shared to your social media account. %1$sLearn more%2$s.', 'tweet-old-post' ), '<a href="https://docs.revive.social/article/1489-automatically-updating-post-publish-date-after-sharing-a-post" target="_blank">', '</a>' ),
				'filter_by_post_types_desc'             => __( 'Filter posts list by Post Type', 'tweet-old-post' ),
				'post_types_upsell'                     => __(
					'Selecting custom post types is available in the Pro
							version.',
					'tweet-old-post'
				),
				'taxonomies_title'            => __( 'Taxonomies', 'tweet-old-post' ),
				'taxonomies_desc'             => __(
					'Taxonomies available for the selected post types. Use to include or exclude
							posts. <a href="https://docs.revive.social/article/457-how-to-exclude-taxonomies-in-revive-old-post" target="_blank">Learn more</a>.',
					'tweet-old-post'
				),
				'filter_by_taxonomies_desc'     => __( 'Filter posts list by Taxonomy', 'tweet-old-post' ),
				'taxonomies_exclude'          => __( 'Exclude?', 'tweet-old-post' ),
				'posts_title'                 => __( 'Posts', 'tweet-old-post' ),
				'posts_desc'                  => __( 'Posts excluded from sharing, filtered based on previous selections.', 'tweet-old-post' ),
				'ga_title'                    => __( 'Enable Google Analytics Tracking', 'tweet-old-post' ),
				'ga_desc'                     => __(
					'If checked, UTM query tags will be added to URL of shares so that you can better track
							traffic from Revive Old Posts.',
					'tweet-old-post'
				),
				'custom_share_title'          => __( 'Enable Share Content Variations', 'tweet-old-post' ),
				'custom_share_desc'           => __( 'These messages will override the Post Content option in Post Format settings. You can go to each post and add multiple share content variations. <a href="https://docs.revive.social/article/971-how-to-add-variations-to-revive-old-posts-shares" target="_blank">Learn more</a>.', 'tweet-old-post' ),
				'custom_share_order_title'    => __( 'Share Message Variations In the Order They Are Added.', 'tweet-old-post' ),
				'custom_share_order_desc'     => __( 'By default message variations are shared randomly. Checking this box will cause them to share in the order they were added.', 'tweet-old-post' ),
				'instant_share_title'         => __( 'Enable Instant Sharing Feature (Post on Publish)', 'tweet-old-post' ),
				'instant_share_desc'          => __( 'Allows you to share posts immediately on publish/update. <a href="https://docs.revive.social/article/933-how-to-share-posts-immediately-with-revive-old-posts" target="_blank">Learn more</a>.', 'tweet-old-post' ),
				'true_instant_share_title'    => __( 'Use True Instant Share', 'tweet-old-post' ),
				'true_instant_share_desc'     => __( 'This option sends the post out as soon as you click the publish button, instead of relying on a Cron task. <a href="https://docs.revive.social/article/1259-how-to-make-instant-share-feature-truly-immediate" target="_blank">Learn more</a>.', 'tweet-old-post' ),
				'instant_share_default_title' => __( 'Enable Instant Sharing By Default', 'tweet-old-post' ),
				'instant_share_default_desc'  => __( 'Instant sharing option will be checked by default when creating new posts.', 'tweet-old-post' ),
				'instant_share_choose_accounts_manually_title' => __( 'Choose Accounts Manually', 'tweet-old-post' ),
				'instant_share_choose_accounts_manually_desc' => __( 'This option allows you to choose which accounts you\'d like to share to instead of having them all checked automatically.', 'tweet-old-post' ),
				'instant_share_future_scheduled_title'  => __( 'Share Scheduled Posts to Social Media On Publish', 'tweet-old-post' ),
				'instant_share_future_scheduled_desc'   => __( 'Allows for the sharing of posts scheduled to publish at a future date by WordPress to your active social media accounts as soon as they change from "Scheduled" to "Published". <a href="https://docs.revive.social/article/1194-share-scheduled-posts-to-social-media-on-publish-with-revive-old-posts" target="_blank">Learn more</a>.', 'tweet-old-post' ),
				'cron_type_label'                => __( 'Cron Job Type', 'tweet-old-post' ),
				'cron_type_label_desc'           => sprintf( __( 'Select the between your local built-in WordPress task scheduler, or Revive Social\'s, %1$sLearn More%2$s.', 'tweet-old-post' ), '<a href="https://docs.revive.social/article/1303-rop-local-cron-vs-remote-cron" target="_blank">', '</a>' ),
				'cron_type_label_desc_terms'           => __( 'I understand that some site data is stored on the ROP\'s Remote Cron System to provide this service <a href="https://docs.revive.social/article/1317-info-we-collect-for-remote-cron-service" target="_blank">Read More Here</a>.', 'tweet-old-post' ),
				'cron_type_notice'           => sprintf( __( '%1$sNOTE:%2$s This is a BETA Remote Cron feature to be used mainly if your %1$sposts aren\'t sharing%2$s. If the Remote Cron feature is used, and you notice that your posts are still not sharing, then please %1$sturn the setting back to "Local Cron"%2$s and read the following guide for alternative solutions that are sure to work: %1$s%3$sLearn More%4$s%2$s.', 'tweet-old-post' ), '<strong>', '</strong>', '<a href="https://docs.revive.social/article/686-fix-revive-old-post-not-posting" target="_blank">', '</a>' ),
				'housekeeping'                => __( 'Housekeeping', 'tweet-old-post' ),
				'housekeeping_desc'           => __( 'Should we delete all saved settings on deletion of the Revive Old Posts plugin?', 'tweet-old-post' ),
				'save'                        => __( 'Save', 'tweet-old-post' ),
				'taxonomies_exclude_explicit' => __( 'Exclude taxononmies', 'tweet-old-post' ),
				'save_filters'                => __( 'Save filters', 'tweet-old-post' ),
				'search_posts_to_exclude'     => __( 'Search post to exclude...', 'tweet-old-post' ),
				'search_posts_show_excluded'  => __( 'Show only excluded posts', 'tweet-old-post' ),
				'exclude_matching'            => __( 'Exclude all matching', 'tweet-old-post' ),
				'include_single_post'         => __( 'Include this post', 'tweet-old-post' ),
				'exclude_single_post'         => __( 'Exclude this post', 'tweet-old-post' ),
				'no_posts_found'              => __( 'No posts found.', 'tweet-old-post' ),
				'load_more_posts'             => __( 'Load more posts.', 'tweet-old-post' ),
				'min_interval_upsell'         => __( 'Choosing a lower interval is available in the Pro version.', 'tweet-old-post' ),
				'tracking_field'              => __( 'Contributing', 'tweet-old-post' ),
				'tracking'                    => __( 'Send anonymous data to help us understand how you use the plugin.', 'tweet-old-post' ),
				'tracking_info'               => __( 'What do we track?', 'tweet-old-post' ),
			),
			'post_format' => array(
				'yes_text' => __( 'Yes', 'tweet-old-post' ),
				'menu_item'                         => __( 'Post Format', 'tweet-old-post' ),
				'language_title'                        => __( 'Language', 'tweet-old-post' ),
				'language_title_desc'               => __( 'We\'ve detected that this is a multilingual website. Select the post language you want to share to this account.', 'tweet-old-post' ),
				'post_content_title'                => __( 'Share Content', 'tweet-old-post' ),
				'post_content_desc'                 => __( 'Which part of the post should we use as the caption?', 'tweet-old-post' ),
				'post_content_option_title'         => __( 'Post Title', 'tweet-old-post' ),
				'post_content_option_content'       => __( 'Post Content', 'tweet-old-post' ),
				'post_content_option_title_content' => __( 'Post Title & Content', 'tweet-old-post' ),
				'post_content_option_excerpt'       => __( 'Post Excerpt', 'tweet-old-post' ),
				'post_content_option_custom_field'  => __( 'Custom Field', 'tweet-old-post' ),
				'post_content_option_yoast_seo_title'  => __( 'Yoast SEO Title', 'tweet-old-post' ),
				'post_content_option_yoast_seo_description' => __( 'Yoast SEO Description', 'tweet-old-post' ),
				'custom_meta_title'                 => __( 'Custom Meta Field', 'tweet-old-post' ),
				'custom_meta_desc'                  => __( 'Meta field name from which to get the content.', 'tweet-old-post' ),
				'max_char_title'                    => __( 'Maximum Characters', 'tweet-old-post' ),
				'max_char_desc'                     => __( 'Maximum length of the message, in characters. Each letter is considered a character.', 'tweet-old-post' ),
				'add_char_title'                    => __( 'Additional Text', 'tweet-old-post' ),
				'add_char_desc'                     => sprintf( __( 'Add custom content to shared posts. It supports magic tags in the Pro version of ROP %1$sLearn More%2$s', 'tweet-old-post' ), '<a href="https://docs.revive.social/article/952-available-magic-tags-in-revive-old-posts" target="_blank">', '</a>' ),
				'add_char_placeholder'              => __( '...written by {author} on {date}.', 'tweet-old-post' ),
				'add_pos_title'                     => __( 'Choose where you want the Additional Text to appear.', 'tweet-old-post' ),
				'add_pos_option_start'              => __( 'Beginning of Caption', 'tweet-old-post' ),
				'add_pos_option_end'                => __( 'End of Caption', 'tweet-old-post' ),
				'add_link_title'                    => __( 'Include Link', 'tweet-old-post' ),
				'add_link_desc'                     => __( 'Should ROP include the post permalink or not?', 'tweet-old-post' ),
				'meta_link_title'                   => __( 'Custom Field', 'tweet-old-post' ),
				'meta_link_desc'                    => __( 'Fetch URL from custom field?', 'tweet-old-post' ),
				'meta_link_name_title'              => __( 'Custom Field', 'tweet-old-post' ),
				'meta_link_name_desc'               => __( 'Custom Field from which to get the URL.', 'tweet-old-post' ),
				'taxonomy_based_sharing_upsell'         => __( 'Per account Taxonomy filters feature is available in the Pro version (Personal Plan and higher).', 'tweet-old-post' ),
				'use_shortner_title'                => __( 'Use URL Shortener', 'tweet-old-post' ),
				'use_shortner_desc'                 => __( 'Should we use a shortener when adding the links to the content?', 'tweet-old-post' ),
				'shortner_title'                    => __( 'URL Shortener Service', 'tweet-old-post' ),
				'shortner_desc'                     => __( 'Which service to use for URL shortening?', 'tweet-old-post' ),
				'shortner_api_field'                => __( 'Service API', 'tweet-old-post' ),
				'shortner_field_desc_start'         => __( 'Add the', 'tweet-old-post' ),
				'shortner_field_desc_end'           => __( 'required by the', 'tweet-old-post' ),
				'hashtags_title'                    => __( 'Hashtags', 'tweet-old-post' ),
				'hashtags_desc'                     => __( 'Hashtags for published content.', 'tweet-old-post' ),
				'hashtags_option_no'                => __( 'Don\'t add any hashtags', 'tweet-old-post' ),
				'hashtags_option_common'            => __( 'Common hashtags for all shares', 'tweet-old-post' ),
				'hashtags_option_cats'              => __( 'Create hashtags from categories', 'tweet-old-post' ),
				'hashtags_option_tags'              => __( 'Create hashtags from tags', 'tweet-old-post' ),
				'hashtags_option_field'             => __( 'Create hashtags from a custom field', 'tweet-old-post' ),
				'hastags_common_title'              => __( 'Common Hashtags', 'tweet-old-post' ),
				'hastags_common_desc'               => __( 'List of hastags to use separated by comma', 'tweet-old-post' ),
				'hastags_field_title'               => __( 'Custom Hashtags', 'tweet-old-post' ),
				'hastags_field_desc'                => __( 'The name of the meta field that contains the hashtags.', 'tweet-old-post' ),
				'hashtags_length_title'             => __( 'Maximum Hashtags length', 'tweet-old-post' ),
				'hashtags_length_desc'              => __( 'The maximum hashtags length to be used when publishing.', 'tweet-old-post' ),
				'hashtags_randomize'                => __( 'Randomize hashtags', 'tweet-old-post' ),
				'hashtags_randomize_desc'           => __( 'Randomize the list of hashtags on every successful share. You won\'t see this change in the Sharing Queue, the randomization happens at share time.', 'tweet-old-post' ),
				'image_title'                       => __( 'Share As Image Post', 'tweet-old-post' ),
				'image_desc'                        => __( 'Should ROP share your posts as an image post? <a href="https://docs.revive.social/article/958-how-to-share-posts-as-image-posts-to-social-accounts" target="_blank">Learn more</a>.', 'tweet-old-post' ),
				'image_aspect_ratio_title'          => __( 'Automatically correct aspect ratio', 'tweet-old-post' ),
				'image_aspect_ratio_title_desc'     => __( 'Should ROP automatically crop images with the wrong aspect ratio? <a href="https://docs.revive.social/article/1661-how-to-fix-invalid-aspect-ratio-when-posting-to-instagram" target="_blank">Learn more</a>.', 'tweet-old-post' ),
				'utm_campaign_medium'               => __( 'Campaign Medium', 'tweet-old-post' ),
				'utm_campaign_medium_desc'          => __( 'The marketing medium you want to show in Google Analytics e.g: "social", "website", etc.', 'tweet-old-post' ),
				'utm_campaign_name'                 => __( 'Campaign Name', 'tweet-old-post' ),
				'utm_campaign_name_desc'            => __( 'The campaign name you want to show in Google Analytics e.g: "november_sale" etc.', 'tweet-old-post' ),
				'custom_utm_upsell'                 => __( 'Custom UTMs are only available in the Pro version.', 'tweet-old-post' ),
				'image_upsell'                      => __( 'Sharing as an Image Post is available in the Pro version.', 'tweet-old-post' ),
				'full_wpml_support_upsell'          => sprintf( __( 'Language-based sharing only available in the %1$sPro version%2$s', 'tweet-old-post' ), '<a href="https://docs.revive.social/article/1338-how-to-share-different-wpml-languages-to-different-social-media-accounts" target="_blank">', '</a>' ),

				'wpml_select_language'                  => __( 'Choose language', 'tweet-old-post' ),
				'media_post_title'                  => __( 'Media Posts Content', 'tweet-old-post' ),
				'media_post_desc'                   => __( 'Which content should we share for media posts?', 'tweet-old-post' ),
				'media_post_option_title'           => __( 'Title', 'tweet-old-post' ),
				'media_post_option_caption'         => __( 'Caption', 'tweet-old-post' ),
				'media_post_option_alt_text'        => __( 'Alt Text', 'tweet-old-post' ),
				'media_post_option_description'     => __( 'Description', 'tweet-old-post' ),
				'media_post_upsell'                 => __( 'Media posting is available in the Business version.', 'tweet-old-post' ),
				'no_post_format_error'              => __( 'Post Format option empty, "Share scheduled posts to social media on publish" cannot work. Please go to the Post Format tab and click "Save" for this feature to work', 'tweet-old-post' ),
				'active_account_no_post_format_error' => __( 'No post format found for the following network, please go to "Post Format" tab and save your changes for: ', 'tweet-old-post' ),
				'twitter_max_characters_notice' => sprintf( __( '%1$sNote:%2$s Maximum characters supported by Twitter is 280.', 'tweet-old-post' ), '<strong>', '</strong>' ),
				'instagram_disable_link_recommendation' => sprintf( __( '%1$sNote:%2$s We recommend that you disable links for Instagram posts. If you do leave this option checked, then we recommend that you enable a shortener.', 'tweet-old-post' ), '<strong>', '</strong>' ),
				'instagram_image_post_default' => sprintf( __( '%1$sNote:%2$s Instagram posts need to be an image.', 'tweet-old-post' ), '<strong>', '</strong>' ),
				'vk_unsupported_shorteners' => sprintf( __( '%1$sNote:%2$s is.gd shortener is not currently supported by VK.com.', 'tweet-old-post' ), '<strong>', '</strong>' ),
				'not_available_with_rop_server'     => __( 'This feature is not available for X accounts authorized via Revival Social.', 'tweet-old-post' ),
			),
			'schedule'    => array(
				'menu_item'                 => __( 'Custom Schedule', 'tweet-old-post' ),
				'time_now'                  => __( 'Time now', 'tweet-old-post' ),
				'schedule_type_title'       => __( 'Schedule Type', 'tweet-old-post' ),
				'schedule_type_desc'        => __( 'What type of schedule to use.', 'tweet-old-post' ),
				'schedule_type_option_fix'  => __( 'Fixed', 'tweet-old-post' ),
				'schedule_type_option_rec'  => __( 'Recurring', 'tweet-old-post' ),
				'schedule_fixed_days_title' => __( 'Fixed Schedule Days', 'tweet-old-post' ),
				'schedule_fixed_days_desc'  => __( 'The days when to share for this account.', 'tweet-old-post' ),
				'schedule_fixed_time_title' => __( 'Fixed Schedule Time.', 'tweet-old-post' ),
				'schedule_fixed_time_desc'  => __( 'The time at witch to share for this account.', 'tweet-old-post' ),
				'schedule_rec_title'        => __( 'Recurring Schedule Interval', 'tweet-old-post' ),
				'schedule_rec_desc'         => __( 'A recurring interval to use for sharing. Once every \'X\' hours.', 'tweet-old-post' ),
				'schedule_upsell'           => __( 'The Custom Schedule is available only in the Business and Marketer versions of the plugin.', 'tweet-old-post' ),
			),
			'queue'       => array(
				'menu_item'                => __( 'Sharing Queue', 'tweet-old-post' ),
				'sharing_not_started'      => __( 'Sharing is not started!', 'tweet-old-post' ),
				'sharing_not_started_desc' => __( 'You need to start sharing in order to see any posts in the queue.', 'tweet-old-post' ),
				'queue_desc'               => __( 'You can choose to edit any of the post, skip the sharing or block a specific one from sharing in the future.', 'tweet-old-post' ),
				'business_or_higher_only'  => sprintf( __( 'You can edit the posts from the queue with the Business or Marketer versions of the plugin. View more details %1$shere%2$s.', 'tweet-old-post' ), '<a href="' . self::UPSELL_LINK . '" target="_blank">', '</a>' ),
				'no_posts'                 => __( 'No queued posts!', 'tweet-old-post' ),
				'no_posts_desc'            => __( 'Check if you have at least an <b>"Active account"</b>, what posts and pages are selected in <b>"General Settings"</b> and if a <b>"Schedule"</b> is defined.', 'tweet-old-post' ),
				'refresh_btn'              => __( 'Refresh Queue', 'tweet-old-post' ),
				'queue_image'              => __( 'Image', 'tweet-old-post' ),
				'upload_image'             => __( 'Upload', 'tweet-old-post' ),
				'remove_image'             => __( 'Remove', 'tweet-old-post' ),
				'queue_content'            => __( 'Content', 'tweet-old-post' ),
				'reschedule_post'          => __( 'Reschedule this post.', 'tweet-old-post' ),
				'ban_post'                 => __( 'Ban this post from sharing in the future.', 'tweet-old-post' ),
				'edit_queue'               => __( 'Edit', 'tweet-old-post' ),
				'link_title'               => __( 'Link', 'tweet-old-post' ),
				'link_shortned_start'      => __( 'Link using', 'tweet-old-post' ),
				'save_edit'                => __( 'Save', 'tweet-old-post' ),
				'cancel_edit'              => __( 'Cancel', 'tweet-old-post' ),
				'queue_no_image'           => __( 'No Image', 'tweet-old-post' ),
				'skip_btn_queue'           => __( 'Skip', 'tweet-old-post' ),
				'block_btn_queue'          => __( 'Block', 'tweet-old-post' ),
				'insert_media_title'       => __( 'Insert a media', 'tweet-old-post' ),
				'insert_media_btn'         => __( 'Insert', 'tweet-old-post' ),

			),
			'logs'        => array(
				'menu_item'  => __( 'Sharing Logs', 'tweet-old-post' ),
				'clear_btn'  => __( 'Clear logs', 'tweet-old-post' ),
				'no_logs'    => __( 'No recent logs!', 'tweet-old-post' ),
				'export_btn' => __( 'Export logs', 'tweet-old-post' ),
			),
			'general'     => array(
				'plugin_name'                => __( 'Revive Old Posts', 'tweet-old-post' ),
				'status_error_global'        => sprintf( __( 'Issues encountered when trying to share content on social media, check the <a href="%s">Logs menu</a> for more information. ', 'tweet-old-post' ), esc_url( get_admin_url( get_current_blog_id(), 'admin.php?page=TweetOldPost' ) ) ),
				'sharing_not_started'        => __( 'Sharing Not Started', 'tweet-old-post' ),
				'sharing_to_account'         => __( 'Sharing to Accounts', 'tweet-old-post' ),
				'error_check_log'            => __( 'Error (check logs)', 'tweet-old-post' ),
				'status'                     => __( 'Status', 'tweet-old-post' ),
				'click'                      => __( 'Click', 'tweet-old-post' ),
				'to'                         => __( 'to', 'tweet-old-post' ),
				'by'                         => __( 'by', 'tweet-old-post' ),
				'review_it'                  => __( 'Leave a review', 'tweet-old-post' ),
				'in'                         => __( 'in', 'tweet-old-post' ),
				'start'                      => __( 'Start', 'tweet-old-post' ),
				'stop'                       => __( 'Stop', 'tweet-old-post' ),
				'sharing'                    => __( 'Sharing', 'tweet-old-post' ),
				'active_account_warning'     => __( 'You will need at least one active account to start sharing.', 'tweet-old-post' ),
				'upgrade_pro_cta'            => __( 'Upgrade to Pro.', 'tweet-old-post' ),
				'upgrade_biz_cta'            => __( 'Upgrade to Business.', 'tweet-old-post' ),
				'multiselect_not_found'      => __( 'Nothing found matching', 'tweet-old-post' ),
				'next_share'                 => __( 'Next share', 'tweet-old-post' ),
				'sharing_now'                => __( 'Sharing...', 'tweet-old-post' ),
				'cron_interval'              => __( 'Once every 5 min', 'tweet-old-post' ),
				'staging_status'             => sprintf( __( 'This seems to be a staging or development website. Some post types will not share to your accounts. %1$sLearn How to Turn Off%2$s', 'tweet-old-post' ), '<a href="https://docs.revive.social/article/1321-allow-revive-old-posts-to-work-on-staging-or-development-websites" target="_blank">', '</a>' ),
				'api_not_available'          => __(
					'It seems there is an issue with your WordPress configuration and the core REST API functionality is not available. This is crucial as Revive Old Posts relies on this functionality in order to work.<br/>
The root cause might be either a security plugin which blocks this feature or some faulty server configuration which constrain this WordPress feature. <br/>
You can try to disable any of the security plugins that you use in order to see if the issue persists or ask the hosting company to further investigate.',
					'tweet-old-post'
				),
				'rop_support'                => __( 'Get Support', 'tweet-old-post' ),
				'rop_support_url'            => defined( 'ROP_PRO_BASEFILE' ) ? tsdk_support_link( ROP_PRO_BASEFILE ) : '',
				'rop_facebook_domain_toast'  => __(
					'You need to verify your website domain with Facebook so your shares can show as article posts on Facebook. [ <a href="https://docs.revive.social/article/1136-facebook-text-posts-vs-article-posts" target="_blank">Read this doc</a> ] for more information',
					'tweet-old-post'
				),
				'rop_docs'                   => __( 'Documentation', 'tweet-old-post' ),
				'rop_roadmap'                => __( 'Roadmap & Voting', 'tweet-old-post' ),
				'rop_linkedin_refresh_token' => __( 'Your Linkedin access token is about to expire. You need to refresh your LinkedIn token to continue sharing without issue. Paste this link in your browser to find out why and how: https://is.gd/refresh_linkedin_token', 'tweet-old-post' ),
			),
			'post_editor' => array(
				'remove_variation'       => __( 'Delete', 'tweet-old-post' ),
				'add_variation'          => __( 'Add New', 'tweet-old-post' ),
				'new_variation'          => __( 'New Content Variation', 'tweet-old-post' ),
				'custom_message_info'    => sprintf( __( 'Add share message variations to this post %1$sLearn More%2$s.', 'tweet-old-post' ), '<a class="text-bold" href="https://docs.revive.social/article/971-how-to-add-variations-to-revive-old-posts-shares" target="_blank">', '</a>' ),
				'variation_num'          => __( 'Content Variation #', 'tweet-old-post' ),
				'variation_image'        => __( 'Upload image', 'tweet-old-post' ),
				'variation_image_change' => __( 'Change image', 'tweet-old-post' ),
				'variation_remove_image' => __( 'Remove image', 'tweet-old-post' ),
			),
			'emails'      => array(
				'share_once_sharing_done_subject' => __( 'Revive Old Posts - All Posts Shared', 'tweet-old-post' ),
				'refresh_linkedin_token_subject'  => __( 'Revive Old Posts - Refresh Your LinkedIn Token', 'tweet-old-post' ),
				'refresh_linkedin_token_subject_final'  => __( 'Final - Refresh Your LinkedIn Token', 'tweet-old-post' ),
				'share_once_sharing_done_message' => __( 'All posts have been shared to your connected social media accounts. No previously shared posts will be re-shared until you click the button to "Stop Sharing" and "Start Sharing" on the Revive Old Posts plugin dashboard.', 'tweet-old-post' ),
				'refresh_linkedin_token_message'  => sprintf( __( 'Hi! This email was sent by Revive Old Posts on your website. Your LinkedIn token is about to expire. You need to refresh it to continue sharing without issue. Click the link below to find out why and how: %1$s %2$s', 'tweet-old-post' ), '<br><br>', '<a href="https://docs.revive.social/article/1151-how-to-refresh-linkedin-access-token">https://docs.revive.social/article/1151-how-to-refresh-linkedin-access-token</a>' ),
				'refresh_linkedin_token_message_final'  => sprintf( __( 'Hi! This email was sent by Revive Old Posts on your website. Your LinkedIn token is about to expire. You need to refresh it to continue sharing without issue. Click the link below to find out why and how: %1$s %2$s. %3$sThis is the final notice email you will receieve from Revive Old Posts.', 'tweet-old-post' ), '<br><br>', '<a href="https://docs.revive.social/article/1151-how-to-refresh-linkedin-access-token">https://docs.revive.social/article/1151-how-to-refresh-linkedin-access-token</a>', '<br><br>' ),
			),
			'cron_system' => array(
				'delete_cron_service_account_info' => sprintf( __( 'This option will delete your website information from our Remote Cron Service. %1$s Revive Old Posts will then fallback to using the Local Cron System built into WordPress. %1$s You can re-enable the Remote Cron System at anytime from General Settings > Cron Type, switch from Local Cron to Remote.', 'tweet-old-post' ), '<br>' ),
				'clear_local_cron_info' => sprintf( __( 'This will remove the Cron server authentication key from your local database. %1$s A new authentication key will be created when you register to the remote Cron server.', 'tweet-old-post' ), '<br>' ),
			),
			'notices' => array(
				'revive_network_upsell_notice_title' => sprintf( __( '%1$sRSS Sharing In Revive Old Posts%2$s', 'tweet-old-post' ), '<b>', '</b>' ),
				'revive_network_upsell_notice_body' => sprintf( __( 'Expose your followers to other relevant content sources, and keep their interest by sharing posts from your favorite blogs to your social media accounts.%1$s %2$sRevive Network%3$s is an Addon plugin for Revive Old Posts that lets you share content from any RSS or Atom feed to your connected accounts.', 'tweet-old-post' ), '<br><br>', '<b>', '</b>' ),
				'revive_network_upsell_notice_product_pag' => sprintf( __( 'Expose your followers to other relevant content sources, and keep their interest by sharing posts from your favorite blogs to your social media accounts.%1$s %2$sRevive Network%3$s is an Addon plugin for Revive Old Posts that lets you share content from any RSS or Atom feed to your connected accounts.', 'tweet-old-post' ), '<br><br>', '<b>', '</b>' ),
				'dismiss_permanently' => __( 'Dismiss Permanently', 'tweet-old-post' ),

			),
			// Pro only.
			'publish_now' => array(
				'add_account_to_use_instant_share' => __( 'Connect or switch on an account in the Revive Old Posts dashboard to use the Instant Share (Post on Publish) feature.', 'tweet-old-post' ),
				'share_on_update' => __( 'Share <b>immediately</b> via <small>Revive Old Posts</small>  ', 'tweet-old-post' ),
				'clear_on_share'  => __( 'These checkboxes will be cleared once the post is shared.', 'tweet-old-post' ),
				'custom_instant_share_messages_upsell'  => sprintf( __( '%1$sCustom instant share messages are available in the %2$sPro version%3$s of the plugin.%4$s', 'tweet-old-post' ), '<small>', '<a href="https://revive.social/plugins/revive-old-post/" target="_blank">', '</a>', '</small>' ),
			),

			'sharing' => array(
				'post_already_shared' => __( 'This post went out on the last share event and might be a duplicate. Skipping...', 'tweet-old-post' ),
				'share_attempted_on_staging' => __( 'ROP has detected that this is a development website. Share process skipped.', 'tweet-old-post' ),
				'reached_sharing_limit' => __( 'You\'ve reached your daily post sharing limit of %1$d posts. Want to share more? Consider upgrading to enjoy a higher limit.', 'tweet-old-post' ),
				'invalid_license' => __( 'Sorry, your license is invalid.', 'tweet-old-post' ),
			),
			'errors' => array(
				'wordpress_api_error' => __( 'Cannot post to network. WordPress Error: ', 'tweet-old-post' ),
				'gmb_failed_access_token_refresh' => __( 'Failed to retrieve Google My Business access token: ', 'tweet-old-post' ),
				'gmb_failed_share' => __( 'Could not share post to Google My Business with LIVE state: ', 'tweet-old-post' ),
				'gmb_no_valid_accounts' => __( 'Google My Business error: No valid accounts found. Please make sure you have access to a Google My Business location.', 'tweet-old-post' ),
				'gmb_missing_main_class' => __( 'Unable to find Google_Client Class. Please ensure you have the Revive Old Posts Pro Addon activated.', 'tweet-old-post' ),
				'gmb_missing_lib_class' => __( 'Unable to find Google_Service_MyBusiness Class. Please ensure you have the Revive Old Posts Pro Addon activated.', 'tweet-old-post' ),
				'linkedin_missing_exif_imagetype' => __( 'Cannot share image to LinkedIn. exif_imagetype() function is missing from your system. Please contact your web host and ask that this function be enabled on your hosting.', 'tweet-old-post' ),
				'linkedin_issue_fetching_token' => __( 'There was an issue fetching the LinkedIn Token. Please contact Revive Old Posts support for assistance.', 'tweet-old-post' ),
				'no_image_found' => __( 'No image was found for post %1$s cannot share as an image post to: %2$s. Please double check that you have a featured image set.', 'tweet-old-post' ),
				'license_not_active' => __( 'An active Pro license is needed to share to %1$s', 'tweet-old-post' ),
			),
			'generic' => array(
				'only_pro_suffix' => ' (' . __( 'Available in Pro', 'tweet-old-post' ) . ')',
			),
			'misc' => array(
				'curl_not_detected' => __( 'cURL was not detected on your website. Please contact your Web Host and ask that they enable cURL for your website.', 'tweet-old-post' ),
				'no_post_data' => __( 'Post data for share empty.', 'tweet-old-post' ),
				'revive_network_desc' => __( 'Revive Network allows you to share content from multiple RSS or Atom feeds from any website on the web to your connected social media accounts. An active Revive Old Posts Pro subscription is required to download and use Revive Network.', 'tweet-old-post' ),
				'revive_network_learn_more_btn' => __( 'Learn More', 'tweet-old-post' ),
				'learn_more' => __( 'Learn More!', 'tweet-old-post' ),
				'min_interval_6_mins' => __( 'Minimum interval between consecutive shares is 6 minutes.', 'tweet-old-post' ),
				'min_interval_between_shares' => __( 'Lowest allowed value for "Minimum Interval Between Shares" is %s hours. Upgrade to Business Plan or higher to fine tune posting times and days.', 'tweet-old-post' ),
				'min_recurring_schedule_interval' => __( 'Lowest allowed value for "Recurring Schedule Interval" is %d minutes.', 'tweet-old-post' ),
				'no_post_types_selected' => __( 'You need to have at least one post type to share.', 'tweet-old-post' ),
				'min_number_of_concurrent_posts' => __( 'At least one posts need to be shared.', 'tweet-old-post' ),
				'max_number_of_concurrent_posts' => __( 'Maximum concurrent post shares is 4.', 'tweet-old-post' ),
			),
		);
		if ( empty( $key ) ) {
			return $labels;
		}
		/**
		 * Allow accessing labels by key.
		 */
		$keys = explode( '.', $key );
		if ( count( $keys ) === 1 ) {
			if ( isset( $labels[ $keys[0] ] ) ) {
				return $labels[ $keys[0] ];
			}
		}
		if ( count( $keys ) === 2 ) {
			if ( isset( $labels[ $keys[0] ][ $keys[1] ] ) ) {
				return $labels[ $keys[0] ][ $keys[1] ];
			}
		}

		return '';
	}


}
