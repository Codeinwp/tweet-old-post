<?php
// Basic configuration 
require_once("config.php");
// twitteroauth class 
require_once("oAuth/twitteroauth.php");

if (!class_exists('CWP_TOP_Core')) {
	class CWP_TOP_Core {

		// All fields
		public static $fields;
		// Number of fields
		public static $noFields;

		// Consumer key, Consumer Secret key, oAuth Callback Key
		private $consumer;
		private $consumerSecret;
		private $oAuthCallback;

		// Access token, oAuth Token, oAuth Token Secret and User Information
		private $cwp_top_access_token;
		private $cwp_top_oauth_token;
		private $cwp_top_oauth_token_secret;
		private $user_info;

		// Plugin Status
		public $pluginStatus;
		// Interval Set
		public $intervalSet;
		public $cwp_twitter;

		public function __construct() {
			// Get all fields
			global $cwp_top_fields;

			// Set all authentication settings
			$this->setAlloAuthSettings();

			// Load all hooks
			$this->loadAllHooks();

			// Check if the user added any account
			$this->afterAddAccountCheck();

			// Save all fields in static var
			self::$fields = $cwp_top_fields;

			// Save all number of fields in static var
			self::$noFields = count(self::$fields);

		}

		public function startTweetOldPost()
		{
			// If the plugin is deactivated
			if($this->pluginStatus == 'false') {
				// Set it to active status
				update_option('cwp_top_active_status', 'true');
				// Schedule the next tweet
				wp_schedule_event(time(), 'cwp_top_schedule', 'cwp_top_tweet_cron');
			} else { 
				// Report that is already started
				_e("Tweet Old Post is already active!", CWP_TEXTDOMAIN);
			}

			die(); // Required for AJAX
		}

		public function stopTweetOldPost()
		{
			// If the plugin is active
			if($this->pluginStatus == 'true') {
				// Set it to inactive status
				update_option('cwp_top_active_status', 'false');
				// Clear all scheduled tweets
				$this->clearScheduledTweets();
			} else {
				// Report that is already inactive
				_e("Tweet Old Post is already inactive!", CWP_TEXTDOMAIN);
			}

			die(); // Required for AJAX
		}

		public function tweetOldPost()
		{
			// Global WordPress $wpdb object.
			global $wpdb;

			// Generate the Tweet Post Date Range
			$dateQuery = $this->getTweetPostDateRange();

			// Get the number of tweets to be tweeted each interval.
			$tweetCount = intval(get_option('top_opt_no_of_tweet'));

			// Get post categories set.
			$postQueryCategories =  $this->getTweetCategories();

			// Get excluded categories.
			$postQueryExcludedCategories = $this->getExcludedCategories();			

			// Get post type set.
			$somePostType = $this->getTweetPostType();

			// Generate dynamic query.
			$query = "
				SELECT *
				FROM wp_posts
				INNER JOIN wp_term_relationships ON (wp_posts.ID = wp_term_relationships.object_id)
				WHERE 1=1
				  AND ((post_date >= '{$dateQuery['after']}'
				        AND post_date <= '{$dateQuery['before']}')) ";

			// If there are no categories set, select the post from all.
			if(!empty($postQueryCategories)) {
				$query .= "AND (wp_term_relationships.term_taxonomy_id IN ({$postQueryCategories})) ";
			}

			if(!empty($postQueryExcludedCategories)) {
				$query .= "AND ( wp_posts.ID NOT IN (
					SELECT object_id
					FROM wp_term_relationships
					WHERE term_taxonomy_id IN ({$postQueryExcludedCategories})))";
			}
						  
			$query .= "AND wp_posts.post_type IN ({$this->getTweetPostType()})
					  AND (wp_posts.post_status = 'publish')
					GROUP BY wp_posts.ID
					ORDER BY RAND() DESC LIMIT 0,{$tweetCount}
			";

			// Save the result in a var for future use.
			$returnedPost = $wpdb->get_results($query);

			$k = 0; // Iterator

			// While haven't reached the limit
			while($k != $tweetCount) {
				// If the post is not already tweeted
				if($this->isNotAlreadyTweeted($returnedPost[$k]->ID)) {
					// Foreach returned post
					foreach ($returnedPost as $post) {
						// Generate a tweet from it based on user settings.
						$finalTweet = $this->generateTweetFromPost($post);
						// Tweet the post
						$this->tweetPost($finalTweet);
						// Get already tweeted posts array.
						$tweetedPosts = get_option("top_opt_already_tweeted_posts");
						// Push the tweeted post at the end of the array.
						array_push($tweetedPosts, $post->ID);
						// Update the new already tweeted posts array.
						update_option("top_opt_already_tweeted_posts", $tweetedPosts);
						// Increase
						$k = $k + 1;
					}
				}
			}

		}

		/**
		 * Returns if the post is already tweeted
		 * @param  [type]  $postId Post ID
		 * @return boolean         True if not already tweeted / False if already tweeted
		 */
		
		public function isNotAlreadyTweeted($postId) {
			// Get all already tweeted posts
			$tweetedPosts = get_option("top_opt_already_tweeted_posts");
			// If the new post ID is in the array, which means that is already tweeted
			if(in_array($postId, $tweetedPosts)) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Generates the tweet based on the user settings
		 * @param  [type] $postQuery Returned post from database
		 * @return [type]            Generated Tweet
		 */
		
		public function generateTweetFromPost($postQuery)
		{
			// Save all user settings in variables.
			$tweetedPosts 					= get_option("top_opt_already_tweeted_posts");
			$tweet_content 					= get_option('top_opt_tweet_type');
			$tweet_content_custom_field 	= get_option('top_opt_tweet_type_custom_field');
			$additional_text 				= get_option('top_opt_add_text');
			$additional_text_at 			= get_option('top_opt_add_text_at');
			$include_link 					= get_option('top_opt_include_link');
			$fetch_url_from_custom_field 	= get_option('top_opt_custom_url_option');
			$custom_field_url 				= get_option('top_opt_custom_url_field');
			$use_url_shortner 				= get_option('top_opt_use_url_shortner');
			$url_shortner_service 			= get_option('top_opt_url_shortner');
			$hashtags 						= get_option('top_opt_custom_hashtag_option');
			$common_hashtags				= get_option('top_opt_common_hashtags');
			$maximum_hashtag_length 		= get_option('top_opt_hashtag_length');
			$hashtag_custom_field 			= get_option('top_opt_custom_hashtag_field');
			$additionalTextBeginning 		= "";
			$additionalTextEnd 				= "";

			// If the user set to not use hashtags, set it to empty variable.
			if($hashtags == 'nohashtag') { 
				$newHashtags = "";
			}

			// Generate the tweet content.
			switch ($tweet_content) {
				case 'title':
					$tweetContent = $postQuery->post_title . " ";
					break;
				
				case 'body':
					$tweetContent = get_post_field('post_content', $postQuery->ID) . " ";
					break;

				case 'titlenbody':
					$tweetContent = $postQuery->post_title . " " . get_post_field('post_content', $postQuery->ID) . " ";
					break;

				case 'custom-field':
					$tweetContent = get_post_meta($postQuery->ID, $tweet_content_custom_field);
					break;
				default:
					$tweetContent = $finalTweet;
					break;
			}

			// Trim new empty lines.
			$tweetContent = trim(preg_replace('/\s+/', ' ', $tweetContent));

			// Remove html entinies.
			$tweetContent = preg_replace("/&#?[a-z0-9]+;/i","", $tweetContent);

			// Strip all shortcodes from content.
			$tweetContent = strip_shortcodes($tweetContent);

			// Generate the post link.
			if($include_link == 'true') {
				if($fetch_url_from_custom_field == 'on') {
					$post_url = " " . get_post_meta($postQuery->ID, $custom_field_url) . " ";
				} else { 
					$post_url = " " . get_permalink($postQuery->ID);
				}

				if($use_url_shortner == 'on') {
					$post_url = " " . $this->shortenURL($post_url, $url_shortner_service);
				}
				$post_url = $post_url . " ";
			} else { $post_url = ""; }

			// Generate the hashtags
			$newHashtags = "";
			if($hashtags != 'nohashtag') {
				
				switch ($hashtags) {
					case 'common':
						$newHashtags = $common_hashtags;
						break;
					
					case 'categories':
						$postCategories = get_the_category($postQuery->ID);

						foreach ($postCategories as $category) {
							if(strlen($category->cat_name) <= $maximum_hashtag_length || $maximum_hashtag_length == 0) { 
						 		$newHashtags = $newHashtags . " #" . $category->cat_name; 
						 	}
						} 

						break;

					case 'tags':
						$postTags = wp_get_post_tags($postQuery->ID);
						foreach ($postTags as $postTag) {
							if(strlen($postTag->slug) <= $maximum_hashtag_length || $maximum_hashtag_length == 0) {
								$newHashtags = $newHashtags . " #" . $postTag->slug;
							}
						}
						break;

					case 'custom':
						$newHashtags = get_post_meta($postQuery->ID, $hashtag_custom_field);
						break;	
					default:
						break;
				}
			}


			// Generate the additional text
			if($additional_text_at == 'beginning') {
				$additionalTextBeginning = $additional_text . " ";
			}

			if($additional_text_at == 'end') {
				$additionalTextEnd = " " . $additional_text;
			}

			// Calculate the final tweet length
			$finalTweetLength = 0;

			if(!empty($additional_text)) {
				$additionalTextLength = strlen($additional_text); $finalTweetLength += intval($additionalTextLength);
			}

			if(!empty($post_url)) {
				$postURLLength = strlen($post_url); $finalTweetLength += intval($postURLLength);
			}

			if(!empty($newHashtags)) {
				$hashtagsLength = strlen($newHashtags); $finalTweetLength += intval($hashtagsLength);
			}

			$finalTweetLength = 139 - $finalTweetLength - 3;

			$tweetContent = substr($tweetContent,0, $finalTweetLength) . "...";

			$finalTweet = $additionalTextBeginning . $tweetContent . $post_url . $newHashtags . $additionalTextEnd;
			$finalTweet = substr($finalTweet,0, 140);

			// Strip any tags and return the final tweet
			return strip_tags($finalTweet); 
		}

		/**
		 * Tweets the returned post from generateTweetFromPost()
		 * @param  [type] $finalTweet Generated tweet
		 */
		
		public function tweetPost($finalTweet)
		{	
			// Create a new twitter connection using the stored user credentials.
			$connection = new TwitterOAuth($this->consumer, $this->consumerSecret, $this->cwp_top_oauth_token, $this->cwp_top_oauth_token_secret);
			// Post the new tweet
			$status = $connection->post('statuses/update', array('status' => $finalTweet));
		}
		
		// Generates the tweet date range based on the user input.
		public function getTweetPostDateRange()
		{
			$minAgeLimit = "-" . get_option('top_opt_age_limit') . " days";
			$maxAgeLimit = "-" . get_option('top_opt_max_age_limit') . " days";

			$minAgeLimit = date("Y-m-d H:i:s", strtotime($minAgeLimit));
			$maxAgeLimit = date("Y-m-d H:i:s", strtotime($maxAgeLimit));

			if(isset($minAgeLimit) || isset($maxAgeLimit)) {

				$dateQuery = array();

				if(isset($minAgeLimit)) {
					$dateQuery['before'] = $maxAgeLimit;
				}

				if(isset($maxAgeLimit)) {
					$dateQuery['after'] = $minAgeLimit;
				}

				$dateQuery['inclusive'] = true;

			}

			if(!empty($dateQuery)) {
				return $dateQuery;
			}

		}

		// Gets the tweet categories.
		public function getTweetCategories()
		{
			$postQueryCategories = "";
			$postsCategories = get_option('top_opt_tweet_specific_category');

			if(!empty($postCategories)) {
				$lastPostCategory = end($postsCategories);
				foreach ($postsCategories as $key => $cat) {
					if($cat == $lastPostCategory) {
						$postQueryCategories .= $cat;
					} else { 
						$postQueryCategories .= $cat . ", ";
					}
				}
			}

			return $postQueryCategories;
		}

		// Gets the omited tweet categories
		
		public function getExcludedCategories()
		{
			$postQueryCategories = "";
			$postsCategories = get_option('top_opt_omit_cats');

			if(!empty($postCategories)) {
				$lastPostCategory = end($postsCategories);
				foreach ($postsCategories as $key => $cat) {
					if($cat == $lastPostCategory) {
						$postQueryCategories .= $cat;
					} else { 
						$postQueryCategories .= $cat . ", ";
					}
				}
			}

			return $postQueryCategories;
		}

		// Gets the tweet post type.
		public function getTweetPostType()
		{
			$top_opt_tweet_type = get_option('top_opt_post_type');

			switch ($top_opt_tweet_type) {
				case 'post':
					return "'post'";
					break;
				
				case 'page':
					return "'page'";
					break;

				case 'custom-post-type':
					return "'" . get_option('top_opt_post_type_value') . "'";
					break;

				case 'both':
					return "'post', 'page'";
					break;

				default:
					break;
			}

		}


		// Creates a custom Tweet Old Post schedule
		public function createCustomSchedule($schedules)
		{
			$schedules['cwp_top_schedule'] = array(
					'interval'	=> floatval($this->intervalSet) * 60 * 60,
					'display'	=> __("Custom Tweet User Interval", CWP_TEXTDOMAIN)
				);

			return $schedules;
		}

		// Clears the custom Tweet Old Post cron job.
		public function clearScheduledTweets()
		{
			wp_clear_scheduled_hook('cwp_top_tweet_cron');
		}

		// Sets all authentication settings
		public function setAlloAuthSettings() 
		{
			global $cwp_top_settings;

			$this->consumer = $cwp_top_settings['oAuth_settings']['consumer_key'];
			$this->consumerSecret = $cwp_top_settings['oAuth_settings']['consumer_secret'];
			$this->oAuthCallback = CURRENTURL;

			$this->cwp_top_access_token = get_option('cwp_top_access_token');			
			$this->cwp_top_oauth_token = get_option('cwp_top_oauth_token');
			$this->cwp_top_oauth_token_secret = get_option('cwp_top_oauth_token_secret');
			$this->user_info = get_option('cwp_top_oauth_user_details');

			$this->pluginStatus = get_option('cwp_top_active_status');
			$this->intervalSet = get_option('top_opt_interval');

		}

		// Checks if twitter returned any temporary credentials to log in the user.
		public function afterAddAccountCheck()
		{
			if(isset($_REQUEST['oauth_token'])) {
				if($_REQUEST['oauth_token'] == $this->cwp_top_oauth_token) {

					$pluginURL = get_option('cwp_top_first_plugin_url');
					$twitter = new TwitterOAuth($this->consumer, $this->consumerSecret, $this->cwp_top_oauth_token, $this->cwp_top_oauth_token_secret );
					$access_token = $twitter->getAccessToken($_REQUEST['oauth_verifier']);

					$user_details = $twitter->get('account/verify_credentials');

					update_option('cwp_top_oauth_token', $access_token['oauth_token']);
					update_option('cwp_top_oauth_token_secret', $access_token['oauth_token_secret']);
					update_option('cwp_top_oauth_user_details', $user_details);

					header("Location: " . SETTINGSURL);
					exit;
				}
			}
		}

		// Used to display the twitter login button
		public function displayTwitterLoginButton()
		{
			// display the twitter login button
			if($this->userIsLoggedIn()) {
				$this->setAlloAuthSettings();
				return true;
			} else {
				return false;
			}
		}

		// Adds new twitter account
		public function addNewTwitterAccount()
		{
			$this->oAuthCallback = $_POST['currentURL'];
			$twitter = new TwitterOAuth($this->consumer, $this->consumerSecret);
			$requestToken = $twitter->getRequestToken($this->oAuthCallback);

			$token = $requestToken['oauth_token'];
			update_option('cwp_top_oauth_token', $token);
			update_option('cwp_top_oauth_token_secret', $requestToken['oauth_token_secret']);

			switch ($twitter->http_code) {
				case 200:
					$url = $twitter->getAuthorizeURL($token);
					echo $url;
					break;
				
				default:
					return "Could not connect to twitter!";
					break;
			}
			die(); // Required
		}

		// Gets the next tweet interval.
		public function getNextTweetInterval()
		{
			$timestamp = wp_next_scheduled( 'cwp_top_tweet_cron' );

			$timestamp = date('Y-m-d H:i:s', $timestamp);

			$timeLeft = get_date_from_gmt($timestamp);
			echo $timeLeft;
		}

		// Checks if the user is logged in/
		public function userIsLoggedIn()
		{
			if(!empty($this->cwp_top_oauth_token) && !empty($this->cwp_top_oauth_token_secret)) {
				return true;
			} else { 
				return false;
			}
		}

		// Clears all Twitter user credentials.
		public function logOutTwitterUser()
		{
			update_option('cwp_top_oauth_token', '');
			update_option('cwp_top_oauth_token_secret', '');
			update_option('cwp_top_oauth_user_details', '');

			$this->setAlloAuthSettings();
			die();
		}

		// Updates all options.
		public function updateAllOptions()
		{
			$dataSent = $_POST['dataSent']['dataSent'];

			$options = array();
			parse_str($dataSent, $options);

			print_r($options);
			

			foreach ($options as $option => $newValue) {
				//$newValue = sanitize_text_field($newValue);
				update_option($option, $newValue);
			}

			if(!array_key_exists('top_opt_custom_url_option', $options)) {
				update_option('top_opt_custom_url_option', 'off');
			}

			if(!array_key_exists('top_opt_use_url_shortner', $options)) {
				update_option('top_opt_use_url_shortner', 'off');
			}

			if(!array_key_exists('top_opt_tweet_specific_category', $options)) {
				update_option('top_opt_tweet_specific_category', '');
			}

			if(!array_key_exists('top_opt_omit_cats', $options)) {
				update_option('top_opt_tweet_specific_category', '');
			}

			update_option("top_opt_already_tweeted_posts", array());

			die();
		}

		public function resetAllOptions()
		{
			global $defaultOptions;
			foreach ($defaultOptions as $option => $defaultValue) {
				update_option($option, $defaultValue);
			}
			die();
		}

		// Generate all fields based on settings
		public static function generateFieldType($field)
		{	

			switch ($field['type']) {

				case 'text':
					print "<input type='text' placeholder='".$field['description']."' value='".$field['option_value']."' name='".$field['option']."' id='".$field['option']."'>";
					break;
			
				case 'select':
					$noFieldOptions = intval(count($field['options']));
					$fieldOptions = array_keys($field['options']);

					print "<select id='".$field['option']."' name='".$field['option']."'>";
						for ($i=0; $i < $noFieldOptions; $i++) { 
							print "<option value=".$fieldOptions[$i];
							if($field['option_value'] == $fieldOptions[$i]) { echo " selected='selected'"; }
							print ">".$field['options'][$fieldOptions[$i]]."</option>";
						}
					print "</select>";
					break;

				case 'checkbox':
					print "<input id='".$field['option']."' type='checkbox' name='".$field['option']."'";
					if($field['option_value'] == 'on') { echo "checked=checked"; }
					print " />";
					break;

				case 'custom-post-type':
					print "<select id='".$field['option']."' name='".$field['option']."' >";
						$post_types = get_post_types(array('_builtin' => false));
						foreach ($post_types as $post_type) {
							print "<option value='".$post_type."'";
							if($field['option_value'] == $post_type) { print "selected=selected"; }
							print ">" . $post_type . "</option>";
						}
					print "</select>";
					break;

				case 'categories-list':
					print "<div class='categories-list'>";
						$categories = get_categories();

						foreach ($categories as $category) {

							$top_opt_tweet_specific_category = get_option('top_opt_tweet_specific_category');
							$top_opt_omit_specific_cats = get_option('top_opt_omit_cats');

							print "<div class='cwp-cat'>";
								print "<input type='checkbox' name='".$field['option']."[]' value='".$category->cat_ID."' id='".$field['option']."_cat_".$category->cat_ID."'";

								if($field['option'] == 'top_opt_tweet_specific_category' ) {
									if(is_array($top_opt_tweet_specific_category)) {
										if(in_array($category->cat_ID, $top_opt_tweet_specific_category)) {
											print "checked=checked";
										}
									}
								}

								if($field['option'] == 'top_opt_omit_cats') {
									if(is_array($top_opt_omit_specific_cats)) {
										if(in_array($category->cat_ID, $top_opt_omit_specific_cats)) {
											print "checked=checked";
										}
									}					
								}


								print ">";
								print "<label for='".$field['option']."_cat_".$category->cat_ID."'>".$category->name."</label>";							
							print "</div>";
						}
					print "</div>";
					break;

			}

		}

		public function loadAllHooks() 
		{
			// loading all actions and filters
			add_action('admin_menu', array($this, 'addAdminMenuPage'));
			add_action('admin_enqueue_scripts', array($this, 'loadAllScriptsAndStyles'));

			// Update all options ajax action.
			add_action('wp_ajax_nopriv_update_response', array($this, 'updateAllOptions'));
			add_action('wp_ajax_update_response', array($this, 'updateAllOptions'));

			// Reset all options ajax action.
			add_action('wp_ajax_nopriv_reset_options', array($this, 'resetAllOptions'));
			add_action('wp_ajax_reset_options', array($this, 'resetAllOptions'));

			// Add new twitter account ajax action
			add_action('wp_ajax_nopriv_add_new_twitter_account', array($this, 'addNewTwitterAccount'));
			add_action('wp_ajax_add_new_twitter_account', array($this, 'addNewTwitterAccount'));

			// Log Out Twitter user ajax action
			add_action('wp_ajax_nopriv_log_out_twitter_user', array($this, 'logOutTwitterUser'));
			add_action('wp_ajax_log_out_twitter_user', array($this, 'logOutTwitterUser'));

			// Tweet Old Post ajax action
			add_action('wp_ajax_nopriv_tweet_old_post_action', array($this, 'startTweetOldPost'));
			add_action('wp_ajax_tweet_old_post_action', array($this, 'startTweetOldPost'));

			// Tweet Old Post ajax action
			add_action('wp_ajax_nopriv_stop_tweet_old_post', array($this, 'stopTweetOldPost'));
			add_action('wp_ajax_stop_tweet_old_post', array($this, 'stopTweetOldPost'));

			// Clear scheduled tweets on plugin deactivation
			register_deactivation_hook(__FILE__, array($this, 'clearScheduledTweets'));

			// Filter to add new custom schedule based on user input
			add_filter('cron_schedules', array($this, 'createCustomSchedule'));

			add_action('cwp_top_tweet_cron', array($this, 'tweetOldPost'));

		}

		public function loadAllScriptsAndStyles()
		{
			global $cwp_top_settings; // Global Tweet Old Post Settings

			// Enqueue and register all scripts on plugin's page
			if(isset($_GET['page'])) {
				if ($_GET['page'] == $cwp_top_settings['slug']) {

					// Enqueue and Register Main CSS File
					wp_register_style( 'cwp_top_stylesheet', CSSFILE, false, '1.0.0' );
			        wp_enqueue_style( 'cwp_top_stylesheet' );

			        // Register Main JS File
			        wp_enqueue_script( 'cwp_top_js_countdown', JSCOUNTDOWN, array(), '1.0.0', true );
			        wp_enqueue_script( 'cwp_top_javascript', JSFILE, array(), '1.0.0', true );
			        wp_localize_script( 'cwp_top_javascript', 'cwp_top_ajaxload', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
				 }				
			}
 	
		}

		public function addAdminMenuPage()
		{
			global $cwp_top_settings; // Global Tweet Old Post Settings
			add_menu_page($cwp_top_settings['name'], $cwp_top_settings['name'], "edit_pages", $cwp_top_settings['slug'], array($this, 'loadMainView'));
		}

		public function loadMainView()
		{
			global $cwp_top_fields;
			foreach ($cwp_top_fields as $field => $value) {
				$cwp_top_fields[$field]['option_value'] = get_option($cwp_top_fields[$field]['option']); 
			}
			require_once("view.php");
		}

		// Sends a request to the passed URL
		public function sendRequest($url, $method='GET', $data='', $auth_user='', $auth_pass='') {

		    $ch = curl_init($url);

		    if (strtoupper($method) == "POST") {
		        curl_setopt($ch, CURLOPT_POST, 1);
		        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		    }

		    if (ini_get('open_basedir') == '' && ini_get('safe_mode') == 'Off') {
		        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		    }

		    curl_setopt($ch, CURLOPT_HEADER, 0);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		    if ($auth_user != '' && $auth_pass != '') {
		        curl_setopt($ch, CURLOPT_USERPWD, "{$auth_user}:{$auth_pass}");
		    }

		    $response = curl_exec($ch);
		    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		    curl_close($ch);

		    if ($httpcode != 200) {
		        return $httpcode;
		    }

		    return $response;
		}

		// Shortens the url.
		public function shortenURL($url, $service) {

			if ($service == "su.pr") {
		        $shortURL = "http://su.pr/api/simpleshorten?url={$url}";
		        $shortURL = $this->sendRequest($shortURL, 'GET');
		    } elseif ($service == "tr.im") {
		        $shortURL = "http://api.tr.im/api/trim_simple?url={$url}";
		        $shortURL = $this->sendRequest($shortURL, 'GET');
		    } elseif ($service == "3.ly") {
		        $shortURL = "http://3.ly/?api=em5893833&u={$url}";
		        $shortURL = $this->sendRequest($shortURL, 'GET');
		    } elseif ($service == "tinyurl") {
		        $shortURL = "http://tinyurl.com/api-create.php?url=" . $url;
		        $shortURL = $this->sendRequest($shortURL, 'GET');
		    } elseif ($service == "u.nu") {
		        $shortURL = "http://u.nu/unu-api-simple?url={$url}";
		        $shortURL = $this->sendRequest($shortURL, 'GET');
		    } elseif ($service == "1click.at") {
		        $shortURL = "http://1click.at/api.php?action=shorturl&url={$url}&format=simple";
		        $shortURL = $this->sendRequest($shortURL, 'GET');
		    } else {
		        $shortURL = "http://is.gd/api.php?longurl={$url}";
		        $shortURL = $this->sendRequest($shortURL, 'GET');
		    }

		    if($shortURL != ' 400 ') {
		    	return $shortURL;
		    }
		}

	}

	$CWP_TOP_Core = new CWP_TOP_Core; 
}
