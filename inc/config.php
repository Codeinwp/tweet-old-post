<?php
define("CURRENTURL", top_current_page());
if(!defined("CWP_TEXTDOMAIN"))
	define("CWP_TEXTDOMAIN", "tweet-old-post");

if(class_exists("CWP_TOP_Core_PRO")){
	define("CWP_TOP_PRO", TRUE);
}else{
	define("CWP_TOP_PRO", FALSE);
}
// Settings Array
$cwp_top_settings = array(
	'name' 				=> "Revive Old Post",
	'slug' 				=> "TweetOldPost",
	'oAuth_settings'	=> array( // Based on TOP Dev Application settings.
	    'oauth_access_token' 		=> "2256465193-KDpAFIYfxpWugX2OU025b1CPs3WB0RJpgA4Gd4h",
	    'oauth_access_token_secret' => "abx4Er8qEJ4jI7XDW8a90obzgy8cEtovPXCUNSjmwlpb9",
	    'consumer_key' 				=> "ofaYongByVpa3NDEbXa2g",
	    'consumer_secret' 			=> "vTzszlMujMZCY3mVtTE6WovUKQxqv3LVgiVku276M"
		)
);
$cwp_rop_remote_trigger_url = "http://portal.themeisle.com/remote_trigger";
$cwp_rop_beta_trigger_url = "http://portal.themeisle.com/beta_user";
$cwp_top_global_schedule = array();
if(!defined('ROP_PRO_VERSION'))
	$cwp_top_networks = array();
define("CWP_TOP_PRO_STRING",'<span class="cwp-pro-string">'.__("This is only available in the",CWP_TEXTDOMAIN)."<a href='https://themeisle.com/plugins/tweet-old-post-pro/?utm_source=imagepro&utm_medium=link&utm_campaign=top&upgrade=true' target='_blank'> ".__("PRO version")."</a></span>");

$cwp_rop_all_networks = array("twitter"=>false,"facebook"=>false,"linkedin"=>true, "xing"=>true,"tumblr"=>true);
$cwp_rop_restricted_show = array("bitly-key","bitly-user");
$cwp_format_fields = array(

		'tweet-content' 	=> array(
			'id' 				=> '1',
			'name'  			=> __('Post Content', CWP_TEXTDOMAIN),
			'type'				=> 'select',
			'slug'				=> 'tweet-content',
			'option'			=> 'top_opt_tweet_type',
			'description'		=> __('What do you want to share?', CWP_TEXTDOMAIN),
			'options'			=> array(
				'title'			=> __('Title Only', CWP_TEXTDOMAIN),
				'body'			=> __('Body Only', CWP_TEXTDOMAIN),
				'titlenbody'	=> __('Title & Body', CWP_TEXTDOMAIN),
				'custom-field'	=> __('Custom Field', CWP_TEXTDOMAIN)
			),
			'default_value'=>'title'
		),
		'top_opt_tweet_length' 	=> array(
			'id' 				=> '1',
			'name'  			=> __('Length', CWP_TEXTDOMAIN),
			'type'				=> 'number',
			'slug'				=> 'tweet-length',
			'option'			=> 'top_opt_tweet_length',
			'description'		=> __('The length of the tweet', CWP_TEXTDOMAIN),
			'max-length'        => 140,
			'default_value'     =>140
		),

		'tweet-content-field'	=> array(
			'id'			=> '2',
			'name'			=> __('Post Content Custom Field', CWP_TEXTDOMAIN),
			'type'			=> 'text',
			'slug'			=> 'tweet-content-field',
			'option'		=> 'top_opt_tweet_type_custom_field',
			'description'	=> __('Which custom field do you want to fetch info from?', CWP_TEXTDOMAIN),
			'options'		=> array(),
			'default_value'=>""
		),

		'additional-text'	=> array(
			'id'			=> '3',
			'name'			=> __('Additional Text', CWP_TEXTDOMAIN),
			'type'			=> 'text',
			'slug'			=> 'additional-text',
			'option'		=> 'top_opt_add_text',
			'description'	=> __('Text added to your auto posts', CWP_TEXTDOMAIN),
			'options'		=> array(),
			'default_value'=>''
		),

		'additional-text-at' 	=> array(
			'id' 			=> '4',
			'name'  		=> __('Additional Text At', CWP_TEXTDOMAIN),
			'type'			=> 'select',
			'slug'			=> 'additional-text-at',
			'option'		=> 'top_opt_add_text_at',
			'description'	=> __('Where do you want the text to be added?', CWP_TEXTDOMAIN),
			'options'		=> array(
				'beginning'	=> __('Beginning of Post', CWP_TEXTDOMAIN),
				'end'		=> __('End of Post', CWP_TEXTDOMAIN)
			),
			"default_value"=>"beginning"
		),

		'include-link' 			=> array(
			'id' 			=> '5',
			'name'  		=> __('Include Link', CWP_TEXTDOMAIN),
			'type'			=> 'select',
			'slug'			=> 'include-link',
			'option'		=> 'top_opt_include_link',
			'description'	=> __('Include a link to your post?', CWP_TEXTDOMAIN),
			'options'		=> array(
				'true'		=> __('Yes', CWP_TEXTDOMAIN),
				'false'		=> __('No', CWP_TEXTDOMAIN)
			),
			'dependency'=>  array(
									"url-from-custom-field"=>"true",
									"use-url-shortner"=>"true",
								  ),
			'default_value'   =>"yes"


		),

		'url-from-custom-field' => array(
			'id' 			=> '6',
			'name'  		=> __('Fetch URL From Custom Field', CWP_TEXTDOMAIN),
			'type'			=> 'checkbox',
			'slug'			=> 'url-from-custom-field',
			'option'		=> 'top_opt_custom_url_option',
			'description'	=> __('URL will be fetched from a custom field.', CWP_TEXTDOMAIN),
			'options'		=> '',
			'dependency'=>  array(
				"custom-field-url"=>"true"
			),
			'default_value'=>''

		),

		'custom-field-url'		=> array(
			'id'			=> '7',
			'name'			=> __('URL Custom Field', CWP_TEXTDOMAIN),
			'type'			=> 'text',
			'slug'			=> 'custom-field-url',
			'option'		=> 'top_opt_custom_url_field',
			'description'	=> __('URL will be fetched from the specified custom field.', CWP_TEXTDOMAIN),
			'options'		=> array(),
			'default_value'=>""
		),

		'use-url-shortner' => array(
			'id'			=> '8',
			'name'			=> __('Use URL Shortner', CWP_TEXTDOMAIN),
			'type'			=> 'checkbox',
			'slug'			=> 'use-url-shortner',
			'option'		=> 'top_opt_use_url_shortner',
			'description'	=> '',
			'options'		=> '',
			'dependency'=>  array(
				"url-shortner"=>"true"
			),
			'default_value'=>"off"
		),



		'url-shortner' => array(
			'id' 			=> '9',
			'name'  		=> __('URL Shortner Service', CWP_TEXTDOMAIN),
			'type'			=> 'select',
			'slug'			=> 'url-shortner',
			'option'		=> 'top_opt_url_shortner',
			'description'	=> __('Shorten the link to your post.', CWP_TEXTDOMAIN),
			'options'		=> array(
				'wp_short_url'		=> __('wp short url', CWP_TEXTDOMAIN),
				//'t.co'		=> __('t.co', CWP_TEXTDOMAIN),
				'is.gd'		=> __('is.gd', CWP_TEXTDOMAIN),
				'bit.ly'	=> __('bit.ly', CWP_TEXTDOMAIN),
				//'tr.im'		=> __('tr.im', CWP_TEXTDOMAIN),
				//'3.ly'		=> __('3.ly', CWP_TEXTDOMAIN),
				//'u.nu'		=> __('u.nu', CWP_TEXTDOMAIN),
				//'1click.at'	=> __('1click.at', CWP_TEXTDOMAIN),
				//'tinyurl'	=> __('TinyUrl', CWP_TEXTDOMAIN)

			),
			'dependency'=>  array(
								"bitly-key"=>"bit.ly",
								"bitly-user"=>"bit.ly"
							),
			'default_value'=>'wp_short_url'
		),

		'bitly-key' => array(
			'id'			=> '22',
			'name'			=> __('Bitly Key', CWP_TEXTDOMAIN),
			'type'			=> 'text',
			'slug'			=> 'bitly-key',
			'option'		=> 'top_opt_bitly_key',
			'description'	=> '',
			'options'		=> '',
			'default_value'		=> '',
		),

		'bitly-user' => array(
			'id'			=> '23',
			'name'			=> __('Bitly User', CWP_TEXTDOMAIN),
			'type'			=> 'text',
			'slug'			=> 'bitly-user',
			'option'		=> 'top_opt_bitly_user',
			'description'	=> '',
			'options'		=> '',
			'default_value'		=> '',
		),

		'custom-hashtag-option' => array(
			'id' 			=> '10',
			'name'  		=> __('Hashtags', CWP_TEXTDOMAIN),
			'type'			=> 'select',
			'slug'			=> 'custom-hashtag-option',
			'option'		=> 'top_opt_custom_hashtag_option',
			'description'	=> __('Include #hashtags in your auto posts?', CWP_TEXTDOMAIN),
			'options'		=> array(
				'nohashtag'	=> __('Don\'t add any hashtags', CWP_TEXTDOMAIN),
				'common'	=> __('Common hashtags for all shares', CWP_TEXTDOMAIN),
				'categories'=> __('Create hashtags from Categories', CWP_TEXTDOMAIN),
				'tags'		=> __('Create hashtags from Tags', CWP_TEXTDOMAIN),
				'custom'	=> __('Create hashtags from Custom Fields', CWP_TEXTDOMAIN)
			),
			'dependency'=>  array(
				"common-hashtags" =>"common",
				"hashtags-length" =>"common,categories,tags,custom",
				"common-hashtags" =>"common",
				"custom-hashtag-field"=>"custom"
			),
			'default_value'=>'nohashtag'
		),

		'common-hashtags'		=> array(
			'id'			=> '11',
			'name'			=> __('Common Hashtags', CWP_TEXTDOMAIN),
			'type'			=> 'text',
			'slug'			=> 'common-hashtags',
			'option'		=> 'top_opt_hashtags',
			'description'	=> __('Specify which hashtags you want to be used. eg. #example, #example2', CWP_TEXTDOMAIN),
			'options'		=> array(),
			'default_value'=>''
		),

		'hashtags-length'		=> array(
			'id'			=> '12',
			'name'			=> __('Maximum Hashtags Length', CWP_TEXTDOMAIN),
			'type'			=> 'text',
			'slug'			=> 'hashtags-length',
			'option'		=> 'top_opt_hashtag_length',
			'description'	=> __('Set to 0 (characters) to include all.', CWP_TEXTDOMAIN),
			'options'		=> array(),
			'default_value'=>'20'
		),

		'custom-hashtag-field'	=> array(
			'id'			=> '13',
			'name'			=> __('Hashtag Custom Field', CWP_TEXTDOMAIN),
			'type'			=> 'text',
			'slug'			=> 'custom-hashtag-field',
			'option'		=> 'top_opt_custom_hashtag_field',
			'description'	=> __('Fetch hashtags from specified custom field', CWP_TEXTDOMAIN),
			'options'		=> array(),
			'default_value'=>'',

		)
		,
			'use-image' => array(
				'id' 					=> '24',
				'name'  				=> __('Post with Image', CWP_TEXTDOMAIN),
				'type'					=> 'checkbox',
				'slug'					=> 'post-with-image',
				'option'				=> 'top_opt_post_with_image',
				'description'			=> __('Check if you want to add the post featured image to the share', CWP_TEXTDOMAIN),
				'options'				=> array(),
				"available_pro"         => "yes",
				'default_value'=>"off"
			)
);

$cwp_top_fields = array(


	'interval'				=> array(
			'id'			=> '14',
			'name'			=> __('Minimum interval between shares', CWP_TEXTDOMAIN),
			'type'			=> 'text',
			'slug'			=> 'interval',
			'option'		=> 'top_opt_interval',
			"available_pro" =>"no",
			'description'	=> __('Minimum time between shares (Hour/Hours), 0.4 can be used also.', CWP_TEXTDOMAIN),
			'options'		=> array()

	),

	'age-limit'				=> array(
			'id'			=> '15',
			'name'			=> __('Minimum age of post to be eligible for sharing', CWP_TEXTDOMAIN),
			'type'			=> 'text',
			'slug'			=> 'age-limit',
			'option'		=> 'top_opt_age_limit',
			'description'	=> __('Day/Days - 0 for Disabled', CWP_TEXTDOMAIN),
			'options'		=> array()
	),

	'max-age-limit'				=> array(
			'id'			=> '16',
			'name'			=> __('Maximum age of post to be eligible for sharing', CWP_TEXTDOMAIN),
			'type'			=> 'text',
			'slug'			=> 'max-age-limit',
			'option'		=> 'top_opt_max_age_limit',
			'description'	=> __('Day/Days - 0 for Disabled', CWP_TEXTDOMAIN),
			'options'		=> array()
	),

	'no-of-tweet'			=> array(
			'id'			=> '17',
			'name'			=> __('Number of Posts to share', CWP_TEXTDOMAIN),
			'type'			=> 'text',
			'slug'			=> 'no-of-tweet',
			'option'		=> 'top_opt_no_of_tweet',
			'description'	=> __('Number of posts to share each time', CWP_TEXTDOMAIN),
			'options'		=> array()
	),




	'tweet-multiple-times' => array(
			'id' 					=> '25',
			'name'  				=> __('Share old posts more than once', CWP_TEXTDOMAIN),
			'type'					=> 'checkbox',
			'slug'					=> 'tweet-multiple-times',
			'option'				=> 'top_opt_tweet_multiple_times',
			'description'			=> __('By default once a post is shared it will not be shared again until you stop/start the plugin', CWP_TEXTDOMAIN),
			'options'				=> array()
	),



	'post-type' => array(
			'id' 					=> '18',
			'name'  				=> __('Post Type', CWP_TEXTDOMAIN),
			'type'					=> 'custom-post-type',
			'slug'					=> 'post-type',
			'option'				=> 'top_opt_post_type',
			'description'			=> __('What type of items do you want to share?', CWP_TEXTDOMAIN),
			'options'				=> array(),
			"available_pro"         => "yes"

	),

		'analytics-tracking' => array(
			'id' 					=> '26',
			'name'  				=> __('Google Analytics Campaign Tracking', CWP_TEXTDOMAIN),
			'type'					=> 'checkbox',
			'slug'					=> 'ga-tracking',
			'option'				=> 'top_opt_ga_tracking',
			'description'			=> __('Enabling Campaign Tracking you would be able to see how much traffic Revive Old Post generated.', CWP_TEXTDOMAIN),
			'options'				=> array()
	),

	'exclude-specific-categories' => array(
			'id' 					=> '21',
			'name'  				=> __('Exclude Specific Categories', CWP_TEXTDOMAIN),
			'type'					=> 'categories-list',
			'slug'					=> 'exclude-specific-category',
			'option'				=> 'top_opt_omit_cats',
			'description'			=> __('Select which categories do you want to exclude to share from? Blank - None', CWP_TEXTDOMAIN),
			'options'				=> array()
	),

);

// Default option values
$defaultOptions = array(
	'top_opt_tweet_type'				=> 'title',
	'top_opt_tweet_type_custom_field'	=> '',
	'top_opt_add_text'					=> '',
	'top_opt_add_text_at'				=> 'beginning',
	'top_opt_include_link'				=> 'true',
	'top_opt_custom_url_option'			=> 'off',
	'top_opt_use_url_shortner'			=> 'off',
	'top_opt_ga_tracking'				=>'on',
	'top_opt_url_shortner'				=> 'is.gd',
	'top_opt_custom_hashtag_option'		=> 'nohashtag',
	'top_opt_hashtags'			=> '',
	'top_opt_hashtag_length'			=> '0',
	'top_opt_custom_hashtag_field'		=> '',
	'top_opt_interval'					=> '4',
	'top_opt_age_limit'					=> '30',
	'top_opt_max_age_limit'				=> '0',
	'top_opt_no_of_tweet'				=> '1',
	'top_opt_post_type'					=> 'post',
	'top_opt_post_type_value'			=> 'post',
	'top_opt_custom_url_field'			=> '',
	//'top_opt_tweet_specific_category'	=> '',
	'top_opt_omit_cats'					=> '',

	// Not field related
	'cwp_topnew_active_status'				=> 'false'
);

// Define "array_column" function for PHP versions older than 5.5
if(!function_exists("array_column")) {
	function array_column($array, $column)
		{
		    $ret = array();
		    foreach ($array as $row) $ret[] = $row[$column];
		    return $ret;
		}
}

function top_current_page(){
	$pageURL = 'http';
	if (array_key_exists('HTTPS', $_SERVER) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
	if (@$_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= @$_SERVER["SERVER_NAME"].":".@$_SERVER["SERVER_PORT"].@$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= @$_SERVER["SERVER_NAME"].@$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

function top_settings_url(){
	$pageURL = admin_url('admin.php?page=TweetOldPost');
	return str_replace(":80","",$pageURL);
}

// Store all options in array.
$cwp_top_options_list = array_column($cwp_top_fields, 'option');