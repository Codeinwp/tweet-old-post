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
		$labels = array(
			'accounts'    => array(
				'menu_item'               => __( 'Accounts', 'tweet-old-post' ),
				'service_popup_title'     => __( 'Service Credentials', 'tweet-old-post' ),
				'sign_in_btn'             => __( 'Sign In', 'tweet-old-post' ),
				'at'                      => __( 'at', 'tweet-old-post' ),
				'remove_account'          => __( 'Remove account from the list.', 'tweet-old-post' ),
				'no_accounts'             => __( 'No accounts!', 'tweet-old-post' ),
				'no_active_accounts'      => __( 'No active accounts!', 'tweet-old-post' ),
				'no_active_accounts_desc' => __( 'Add one from the <b>"Accounts"</b> section.', 'tweet-old-post' ),
				'go_to_accounts_btn'      => __( 'Go to Accounts', 'tweet-old-post' ),
				'no_accounts_desc'        => __( 'Sign in and add your social accounts.', 'tweet-old-post' ),
				'has_accounts_desc'       => __( ' Authenticate a new service (eg. Facebook,Twitter etc. ), select the accounts you want to add from that service and <b>activate</b> them. Only the active accounts will be used for sharing.', 'tweet-old-post' ),
				'remove_all_cta'          => __( 'Remove all accounts', 'tweet-old-post' ),
				'accounts_selector'       => __( 'Each <b>account</b> can have it\'s own options for sharing, on the left you can see the current selected account and network, bellow are the options for the account. Don\'t forget to save after each change and remember, you can always reset an account to the network defaults.', 'tweet-old-post' ),
				'save_selector_btn'       => __( 'Save', 'tweet-old-post' ),
				'reset_selector_btn'      => __( 'Reset', 'tweet-old-post' ),
				'for'                     => __( 'for', 'tweet-old-post' ),
				'add_account'             => __( 'Add Account', 'tweet-old-post' ),
				'upsell_accounts'         => __( 'You are allowed to add a maximum 1 account for Twitter and 1 account for Facebook. For using more accounts and networks, you need to check the <strong>Extended</strong> version.', 'tweet-old-post' ),
				'fb_app_id_title'         => __( 'Please add the APP ID from your Facebook app.', 'tweet-old-post' ),
				'fb_app_secret_title'     => __( 'Please add the APP SECRET from your Facebook app.', 'tweet-old-post' ),
				'fb_app_desc'             => sprintf( __( 'You can check %1$shere%2$s how you get this details.', 'tweet-old-post' ), '<a class="text-bold" href="https://docs.revive.social/article/349-how-to-create-a-facebook-application-for-revive-old-post" target="_blank">', '</a>' ),
				'twt_app_desc'            => sprintf( __( 'You can check %1$shere%2$s how to get this details.', 'tweet-old-post' ), '<a class="text-bold " href="https://docs.revive.social/article/914-how-to-create-a-twitter-application-for-revive-old-post" target="_blank">', '</a>' ),
				'service_error'           => __( 'The %1$s service can not be used or was not found', 'tweet-old-post' ),
				'twitter_warning'         => __(
					'
					 It seems like you are using more than 1 Twitter account for sharing. On March 23rd Twitter changed it\'s policy regarding automatic posting across multiple accounts. You should ensure you comply with this new policy by sharing to only one twitter account at a time or risk getting one of your accounts banned.  Read more about this change <a href="https://blog.twitter.com/developer/en_us/topics/tips/2018/automation-and-the-use-of-multiple-accounts.html" target="_blank"><b>here</b></a>.
			',
					'tweet-old-post'
				),
			),
			'settings'    => array(
				'menu_item'                   => __( 'General Settings', 'tweet-old-post' ),
				'min_interval_title'          => __( 'Minimum interval between shares', 'tweet-old-post' ),
				'min_interval_desc'           => __( 'Minimum time between shares (hour/hours), 0.4 can be used.', 'tweet-old-post' ),
				'min_days_title'              => __( 'Minimum post age', 'tweet-old-post' ),
				'min_days_desc'               => __( 'Minimum age of posts available for sharing, in days.', 'tweet-old-post' ),
				'max_days_title'              => __( 'Maximum post age', 'tweet-old-post' ),
				'max_days_desc'               => __( 'Maximum age of posts available for sharing, in days.', 'tweet-old-post' ),
				'no_posts_title'              => __( 'Number of posts', 'tweet-old-post' ),
				'no_posts_desc'               => __( 'Number of posts to share per. account per. trigger of scheduled job.', 'tweet-old-post' ),
				'share_once_title'            => __( 'Share more than once?', 'tweet-old-post' ),
				'share_once_yes'              => __( 'Yes', 'tweet-old-post' ),
				'share_once_desc'             => __(
					'If there are no more posts to share, we should start re-sharing the one we
							previously shared.',
					'tweet-old-post'
				),
				'post_types_title'            => __( 'Post types', 'tweet-old-post' ),
				'post_types_attachament_info' => sprintf( __( 'You need to select the media files which you want to share, find out more information %1$shere%2$s.', 'tweet-old-post' ), '<a href="https://docs.revive.social/article/934-how-to-use-revive-old-post-media-sharing-feature" target="_blank">', '</a>' ),
				'post_types_desc'             => __( 'Post types available to share - what post types are available for share', 'tweet-old-post' ),
				'post_types_upsell'           => __(
					'Selecting custom post types is available in the pro
							version.',
					'tweet-old-post'
				),
				'taxonomies_title'            => __( 'Taxonomies', 'tweet-old-post' ),
				'taxonomies_desc'             => __(
					'Taxonomies available for the selected post types. Use to include or exclude
							posts.',
					'tweet-old-post'
				),
				'taxonomies_exclude'          => __( 'Exclude?', 'tweet-old-post' ),
				'posts_title'                 => __( 'Posts', 'tweet-old-post' ),
				'posts_desc'                  => __( 'Posts excluded from sharing, filtered based on previous selections.', 'tweet-old-post' ),
				'ga_title'                    => __( 'Enable Google Analytics Tracking', 'tweet-old-post' ),
				'ga_desc'                     => __(
					'If checked, UTM query tags will be added to URL of shares so that you can better track
							traffic from Revive Old Posts.',
					'tweet-old-post'
				),
				'ga_yes'                      => __( 'Yes', 'tweet-old-post' ),
				'custom_share_title'          => __( 'Enable Share Variations (Custom Share Messages)', 'tweet-old-post' ),
				'custom_share_desc'           => __( 'These messages will override the Post Content option in Post Format settings. You can go to each post and add multiple share content variations.', 'tweet-old-post' ),
				'custom_share_yes'            => __( 'Yes', 'tweet-old-post' ),
				'custom_share_upsell'         => __( 'Using a custom share message is available in the pro version.', 'tweet-old-post' ),
				'instant_share_title'         => __( 'Enable Instant Sharing option', 'tweet-old-post' ),
				'instant_share_desc'          => __( 'Allow sharing immediately posts on publish/update.', 'tweet-old-post' ),
				'instant_share_yes'           => __( 'Yes', 'tweet-old-post' ),
				'instant_share_default_title' => __( 'Enable instant sharing by default.', 'tweet-old-post' ),
				'instant_share_default_desc'  => __( 'Instant sharing option will be checked by default on new posts.', 'tweet-old-post' ),
				'instant_share_default_yes'   => __( 'Yes', 'tweet-old-post' ),
				'housekeeping'                => __( 'Housekeeping', 'tweet-old-post' ),
				'housekeeping_desc'           => __( 'Should we delete all saved settings on deletion of Revive Old Posts?', 'tweet-old-post' ),
				'housekeeping_yes'            => __( 'Yes', 'tweet-old-post' ),
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
			),
			'post_format' => array(
				'menu_item'                         => __( 'Post Format', 'tweet-old-post' ),
				'post_content_title'                => __( 'Post Content', 'tweet-old-post' ),
				'post_content_desc'                 => __( 'From where to fetch the content which will be shared.', 'tweet-old-post' ),
				'post_content_option_title'         => __( 'Post Title', 'tweet-old-post' ),
				'post_content_option_content'       => __( 'Post Content', 'tweet-old-post' ),
				'post_content_option_title_content' => __( 'Post Title & Content', 'tweet-old-post' ),
				'post_content_option_custom_field'  => __( 'Custom Field', 'tweet-old-post' ),
				'custom_meta_title'                 => __( 'Custom Meta Field', 'tweet-old-post' ),
				'custom_meta_desc'                  => __( 'Meta field name from which to get the content.', 'tweet-old-post' ),
				'max_char_title'                    => __( 'Maximum chars', 'tweet-old-post' ),
				'max_char_desc'                     => __( 'Maximum length of the message.', 'tweet-old-post' ),
				'add_char_title'                    => __( 'Additional text', 'tweet-old-post' ),
				'add_char_desc'                     => sprintf( __( 'Add custom content to published items. Supports %1$smagic tags.%2$s', 'tweet-old-post' ), '<a href="https://docs.revive.social/article/952-available-magic-tags-in-revive-old-posts" target="_blank">', '</a>' ),
				'add_char_placeholder'              => __( 'written by {author} on {date}.', 'tweet-old-post' ),
				'add_pos_title'                     => __( 'Where to add the custom text.', 'tweet-old-post' ),
				'add_pos_option_start'              => __( 'Beginning', 'tweet-old-post' ),
				'add_pos_option_end'                => __( 'End', 'tweet-old-post' ),
				'add_link_title'                    => __( 'Include link', 'tweet-old-post' ),
				'add_link_desc'                     => __( 'Should include the post permalink or not?', 'tweet-old-post' ),
				'add_link_yes'                      => __( 'Yes', 'tweet-old-post' ),
				'meta_link_title'                   => __( 'Custom field', 'tweet-old-post' ),
				'meta_link_desc'                    => __( 'Fetch URL from custom field?', 'tweet-old-post' ),
				'meta_link_yes'                     => __( 'Yes', 'tweet-old-post' ),
				'meta_link_name_title'              => __( 'Custom Field', 'tweet-old-post' ),
				'meta_link_name_desc'               => __( 'Custom Field from which to get the URL.', 'tweet-old-post' ),
				'use_shortner_title'                => __( 'Use url shortener', 'tweet-old-post' ),
				'use_shortner_desc'                 => __( 'Should we use a shortener when adding the links to the content?', 'tweet-old-post' ),
				'use_shortner_yes'                  => __( 'Yes', 'tweet-old-post' ),
				'shortner_title'                    => __( 'URL Shortener Service', 'tweet-old-post' ),
				'shortner_desc'                     => __( 'Which service to use for URL shortening?', 'tweet-old-post' ),
				'shortner_api_field'                => __( 'service API', 'tweet-old-post' ),
				'shortner_field_desc_start'         => __( 'Add the', 'tweet-old-post' ),
				'shortner_field_desc_end'           => __( 'required by the', 'tweet-old-post' ),
				'hashtags_title'                    => __( 'Hashtags', 'tweet-old-post' ),
				'hashtags_desc'                     => __( 'Hashtags to published content.', 'tweet-old-post' ),
				'hashtags_option_no'                => __( 'Dont add any hashtags', 'tweet-old-post' ),
				'hashtags_option_common'            => __( 'Common hastags for all shares', 'tweet-old-post' ),
				'hashtags_option_cats'              => __( 'Create hashtags from categories', 'tweet-old-post' ),
				'hashtags_option_tags'              => __( 'Create hashtags from tags', 'tweet-old-post' ),
				'hashtags_option_field'             => __( 'Create hashtags from custom field', 'tweet-old-post' ),
				'hastags_common_title'              => __( 'Common Hashtags', 'tweet-old-post' ),
				'hastags_common_desc'               => __( 'List of hastags to use separated by comma', 'tweet-old-post' ),
				'hastags_field_title'               => __( 'Custom Hashtags', 'tweet-old-post' ),
				'hastags_field_desc'                => __( 'The name of the meta field that contains the hashtags.', 'tweet-old-post' ),
				'hashtags_length_title'             => __( 'Maximum Hashtags length', 'tweet-old-post' ),
				'hashtags_length_desc'              => __( 'The maximum hashtags length to be used when publishing.', 'tweet-old-post' ),
				'image_title'                       => __( 'Post with image', 'tweet-old-post' ),
				'image_desc'                        => __( 'Use the featured image when posting?', 'tweet-old-post' ),
				'image_yes'                         => __( 'Yes', 'tweet-old-post' ),
				'utm_campaign_medium'               => __( 'Campaign Medium', 'tweet-old-post' ),
				'utm_campaign_medium_desc'          => __( 'The marketing medium you want to show in Google Analytics e.g: "social", "website", etc.', 'tweet-old-post' ),
				'utm_campaign_name'                 => __( 'Campaign Name', 'tweet-old-post' ),
				'utm_campaign_name_desc'            => __( 'The campaign name you want to show in Google Analytics e.g: "november_sale" etc.', 'tweet-old-post' ),
				'custom_utm_upsell'                 => __( 'Custom UTMs are only available in the pro version.', 'tweet-old-post' ),
				'image_upsell'                      => __( 'Posting with images is available in the pro version.', 'tweet-old-post' ),
				'media_post_title'                  => __( 'Media Posts Content', 'tweet-old-post' ),
				'media_post_desc'                   => __( 'Which content should we share for media posts?', 'tweet-old-post' ),
				'media_post_option_title'           => __( 'Title', 'tweet-old-post' ),
				'media_post_option_caption'         => __( 'Caption', 'tweet-old-post' ),
				'media_post_option_alt_text'        => __( 'Alt Text', 'tweet-old-post' ),
				'media_post_option_description'     => __( 'Description', 'tweet-old-post' ),
				'media_post_upsell'                 => __( 'Media posting is available in the Business version.', 'tweet-old-post' ),
			),
			'schedule'    => array(
				'menu_item'                 => __( 'Custom Schedule', 'tweet-old-post' ),
				'schedule_type_title'       => __( 'Schedule Type', 'tweet-old-post' ),
				'schedule_type_desc'        => __( 'What type of schedule to use.', 'tweet-old-post' ),
				'schedule_type_option_fix'  => __( 'Fixed', 'tweet-old-post' ),
				'schedule_type_option_rec'  => __( 'Recurring', 'tweet-old-post' ),
				'schedule_fixed_days_title' => __( 'Fixed Schedule Days', 'tweet-old-post' ),
				'schedule_fixed_days_desc'  => __( 'The days when to share for this account.', 'tweet-old-post' ),
				'schedule_fixed_time_title' => __( 'Fixed Schedule Time.', 'tweet-old-post' ),
				'schedule_fixed_time_desc'  => __( 'The time at witch to share for this account.', 'tweet-old-post' ),
				'schedule_rec_title'        => __( 'Recurring Schedule Interval.', 'tweet-old-post' ),
				'schedule_rec_desc'         => __( 'A recurring interval to use for sharing. Once every \'X\' hours.', 'tweet-old-post' ),
				'schedule_upsell'           => __( 'The Custom Schedule is available only in the Business version.', 'tweet-old-post' ),
			),
			'queue'       => array(
				'menu_item'                => __( 'Sharing Queue', 'tweet-old-post' ),
				'sharing_not_started'      => __( 'Sharing is not started!', 'tweet-old-post' ),
				'sharing_not_started_desc' => __( 'You need to start sharing in order to see any posts in the queue.', 'tweet-old-post' ),
				'queue_desc'               => __( 'You can choose to edit any of the post, skip the sharing or block a specific one from sharing in the future.', 'tweet-old-post' ),
				'biz_only'                 => sprintf( __( 'You can edit the posts from the queue only the Business version of the plugin. View more details %1$shere%2$s.', 'tweet-old-post' ), '<a href="' . self::UPSELL_LINK . '" target="_blank">', '</a>' ),
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
				'menu_item' => __( 'Logs', 'tweet-old-post' ),
				'clear_btn' => __( 'Clear logs', 'tweet-old-post' ),
				'no_logs'   => __( 'No recent logs!', 'tweet-old-post' ),
			),
			'general'     => array(
				'by'                     => __( 'by', 'tweet-old-post' ),
				'tweet_about_it'         => __( 'Show your love', 'tweet-old-post' ),
				'review_it'              => __( 'Leave a review', 'tweet-old-post' ),
				'in'                     => __( 'in', 'tweet-old-post' ),
				'now'                    => __( 'Now', 'tweet-old-post' ),
				'start'                  => __( 'Start', 'tweet-old-post' ),
				'stop'                   => __( 'Stop', 'tweet-old-post' ),
				'sharing'                => __( 'Sharing', 'tweet-old-post' ),
				'active_account_warning' => __( 'You will need at least one active account to start sharing.', 'tweet-old-post' ),
				'upgrade_pro_cta'        => __( 'Upgrade to Pro.', 'tweet-old-post' ),
				'upgrade_biz_cta'        => __( 'Upgrade to Business.', 'tweet-old-post' ),
				'multiselect_not_found'  => __( 'Nothing found matching', 'tweet-old-post' ),
				'next_share'             => __( 'Next share', 'tweet-old-post' ),
				'sharing_now'            => __( 'Sharing...', 'tweet-old-post' ),
				'cron_interval'          => __( 'Once every 1 min', 'tweet-old-post' ),
				'staging_status'         => __( 'This is a staging website, posts will not share to your accounts.', 'tweet-old-post' ),
				'api_not_available'      => __(
					'It seems there is an issue with your WordPress configuration and the core REST API functionality is not available. This is crucial as Revive Old Posts relies on this functionality in order to work.<br/>
The root cause might be either a security plugin which blocks this feature or some faulty server configuration which constrain this WordPress feature. <br/>
You can try to disable any of the security plugins that you use in order to see if the issue persists or ask the hosting company to further investigate.',
					'tweet-old-post'
				),
			),
			'post_editor' => array(
				'remove_message'      => __( 'Remove Share Variation', 'tweet-old-post' ),
				'add_message'         => __( 'Add New Share Variation', 'tweet-old-post' ),
				'random_message_info' => sprintf( __( 'A share variation that will be selected randomly for each share and will overwrite the post share content. Supports %1$smagic tags%2$s.', 'tweet-old-post' ), '<a class="text-bold" href="https://docs.revive.social/article/952-available-magic-tags-in-revive-old-posts" target="_blank">', '</a>' ),
				'message_no'          => __( 'Share Variation #', 'tweet-old-post' ),
			),
			// pro only.
			'publish_now' => array(
				'share_on_update' => __( 'Share <b>immediately</b> via <small>Revive Old Posts</small>  ', 'tweet-old-post' ),
				'clear_on_share'  => __( 'These checkboxes will be cleared once the post is shared.', 'tweet-old-post' ),
			),

		);
		if ( empty( $key ) ) {
			return $labels;
		}
		/**
		 * Allow accesing labels by key.
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
