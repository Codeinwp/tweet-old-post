<?php
// Basic configuration 
require_once(ROPPLUGINPATH."/inc/config.php");
// RopTwitterOAuth class 
require_once(ROPPLUGINPATH."/inc/oAuth/twitteroauth.php");

if (!class_exists('CWP_TOP_Core')) {
	class CWP_TOP_Core {

		// All fields
		public static $fields;
		// Number of fields
		public static $noFields;

		// Consumer key, Consumer Secret key, oAuth Callback Key
		public $consumer;
		public $lastID;
		public $consumerSecret;
		public $oAuthCallback;
		public $bitly_key;
		public $bitly_user;
		// Access token, oAuth Token, oAuth Token Secret and User Information
		private $cwp_top_access_token;
		private $cwp_top_oauth_token;
		private $cwp_top_oauth_token_secret;


		private $users;
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

		public function addLocalization() {
 
 			load_plugin_textdomain(CWP_TEXTDOMAIN, false, dirname(ROPPLUGINBASENAME).'/languages/');
 		}

		public function startTweetOldPost()
		{
			// If the plugin is deactivated
			if($this->pluginStatus !== 'true') {
				// Set it to active status
				update_option('cwp_topnew_active_status', 'true');
				update_option('cwp_topnew_notice', '');
				update_option('top_opt_already_tweeted_posts',array());
				// Schedule the next tweet
				//$timeNow = date("Y-m-d H:i:s", time());
				//$timeNow = get_date_from_gmt($timeNow);
				//$timeNow= strtotime($timeNow);
				$timeNow =  current_time('timestamp',1);
				$interval = floatval($this->intervalSet) * 60 * 60;
				$timeNow = $timeNow+25;
				wp_schedule_event($timeNow, 'cwp_top_schedule', 'cwp_top_tweet_cron');
			} else { 
				
				// Report that is already started
				_e("Tweet Old Post is already active!", CWP_TEXTDOMAIN);
			}

			die(); // Required for AJAX
		}

		public function stopTweetOldPost()
		{
			//echo $this->pluginStatus;
			// If the plugin is active
			if($this->pluginStatus !== 'false') {
				// Set it to inactive status
				update_option('cwp_topnew_active_status', 'false');
				update_option('cwp_topnew_notice', '');
				update_option('top_opt_already_tweeted_posts',array());

				// Clear all scheduled tweets
				$this->clearScheduledTweets();
			} else {
				// Report that is already inactive
				_e("ROP is already inactive!", CWP_TEXTDOMAIN);
			}

			die(); // Required for AJAX
		}

		public function getExcludedPosts() {

			$postQueryPosts = "";
			$postPosts = get_option('top_opt_excluded_post');

			if(!empty($postPosts) && is_array($postPosts)) {
				$lastPostPosts = end($postPosts);
				foreach ($postPosts as $key => $cat) {
					if($cat == $lastPostPosts) {
						$postQueryPosts .= $cat;
					} else { 
						$postQueryPosts .= $cat . ", ";
					}
				}
			}
			else
				$postQueryPosts = get_option('top_opt_excluded_post');

			return $postQueryPosts;

		}

		public function getTweetsFromDBbyID($id)
		{
			global $wpdb;
			$query = "
			SELECT * FROM {$wpdb->prefix}posts where ID = '{$id}'";
			$returnedPost = $wpdb->get_results($query);
			//echo $query;
			return $returnedPost;
		}

		public function getTweetsFromDB()
		{
			// Global WordPress $wpdb object.
			global $wpdb;

			// Generate the Tweet Post Date Range
			$dateQuery = $this->getTweetPostDateRange();

			// Get the number of tweets to be tweeted each interval.
			$tweetCount = intval(get_option('top_opt_no_of_tweet'));

			// Get post categories set.
//			$postQueryCategories =  $this->getTweetCategories();
			$excludedIds = "";
			$tweetedPosts = get_option("top_opt_already_tweeted_posts");
			if (!$tweetedPosts || get_option('top_opt_tweet_multiple_times')=="on") {
				$tweetedPosts = array();
			}
			$postQueryExcludedPosts = $this->getExcludedPosts();
			if ($postQueryExcludedPosts=="")
				$postQueryExcludedPosts = array();
			//print_r($postQueryExcludedPosts);
			$excludedPosts = array_merge($tweetedPosts,(array)$postQueryExcludedPosts);
			$nrOfExcludedPosts = count($excludedPosts);
			for ($k=0;$k<$nrOfExcludedPosts-1;$k++)
				$excludedIds .=$excludedPosts[$k].", ";
			if ($nrOfExcludedPosts>0) {
				$lastId = $nrOfExcludedPosts-1;
				$excludedIds .=$excludedPosts[$lastId];
			}
			//print_r($excludedIds);
			// Get excluded categories.
			$postQueryExcludedCategories = $this->getExcludedCategories();			
			//echo $postQueryExcludedCategories;
			//print_r($postQueryExcludedCategories);
			// Get post type set.
			$somePostType = $this->getTweetPostType();

			// Generate dynamic query.
			$query = "
				SELECT *
				FROM {$wpdb->prefix}posts
				LEFT JOIN {$wpdb->prefix}term_relationships ON ({$wpdb->prefix}posts.ID = {$wpdb->prefix}term_relationships.object_id)
				WHERE 1=1
				  AND ((post_date >= '{$dateQuery['before']}'
				        AND post_date <= '{$dateQuery['after']}')) ";

			// If there are no categories set, select the post from all.
			//if(!empty($postQueryCategories)) {
		//		$query .= "AND (wp_term_relationships.term_taxonomy_id IN ({$postQueryCategories})) ";
		//	}

			if(!empty($postQueryExcludedCategories)) {
				$query .= "AND ( {$wpdb->prefix}posts.ID NOT IN (
					SELECT object_id
					FROM {$wpdb->prefix}term_relationships
					INNER JOIN {$wpdb->prefix}term_taxonomy ON ( {$wpdb->prefix}term_relationships.term_taxonomy_id = {$wpdb->prefix}term_taxonomy.term_taxonomy_id ) 
WHERE {$wpdb->prefix}term_taxonomy.taxonomy =  'category'
					AND {$wpdb->prefix}term_taxonomy.term_id IN ({$postQueryExcludedCategories}))) ";
			}

			if(!empty($excludedIds)) {
				$query .= "AND ( {$wpdb->prefix}posts.ID NOT IN ({$excludedIds})) ";
			}
						  
			$query .= "AND {$wpdb->prefix}posts.post_type IN ({$somePostType})
					  AND ({$wpdb->prefix}posts.post_status = 'publish')
					GROUP BY {$wpdb->prefix}posts.ID
					ORDER BY RAND() DESC LIMIT 0,{$tweetCount}
			";

			// Save the result in a var for future use.
			$returnedPost = $wpdb->get_results($query);
			//echo $query;
			return $returnedPost;
		}

		public function isPostWithImageEnabled () {

			if (get_option("top_opt_post_with_image")!='on')
				return false;
			else
				return true;
		}

		public function tweetOldPost($byID = false)
		
		{
			$returnedPost = $this->getTweetsFromDB();
			if ($byID!==false) {

				$returnedPost = $this->getTweetsFromDBbyID($byID);
			}


			$k = 0; // Iterator
			
			// Get the number of tweets to be tweeted each interval.
			$tweetCount = intval(get_option('top_opt_no_of_tweet'));

			if (count($returnedPost) == 0 ) update_option('cwp_topnew_notice', 'There is no suitable post to tweet make sure you excluded correct categories and selected the right dates.');
				
			// While haven't reached the limit
			while($k != $tweetCount) {
				// If the post is not already tweeted
				$isNotAlreadyTweeted = $this->isNotAlreadyTweeted($returnedPost[$k]->ID);
				
				if (get_option('top_opt_tweet_multiple_times')=="on") $isNotAlreadyTweeted = true;

				if($isNotAlreadyTweeted && ($k<count($returnedPost))) {

					// Foreach returned post
					$post = $returnedPost[$k];
					//foreach ($returnedPost as $post) {
						// Generate a tweet from it based on user settings.
						$finalTweet = $this->generateTweetFromPost($post);
						// Tweet the post
						if ($this->isPostWithImageEnabled()=="on") {
							$resp = $this->tweetPostwithImage($finalTweet, $post->ID);
							update_option('cwp_topnew_notice', $resp);
						}
						else {
							$resp = $this->tweetPost($finalTweet);
							update_option('cwp_topnew_notice', $resp);
						}
						// Get already tweeted posts array.
						$tweetedPosts = get_option("top_opt_already_tweeted_posts");
						if ($tweetedPosts=="")	$tweetedPosts = array();
						// Push the tweeted post at the end of the array.
						array_push($tweetedPosts, $post->ID);
						// Update the new already tweeted posts array.
						if ( function_exists('w3tc_pgcache_flush') ) {
							
							w3tc_dbcache_flush();
							
							w3tc_objectcache_flush();
							$cache = ' and W3TC Caches cleared';
						}     
						add_option("top_opt_already_tweeted_posts");
						update_option("top_opt_already_tweeted_posts", $tweetedPosts);
						// Increase
						$k = $k + 1;
					//}
				} else {
					if (count($returnedPost)!=$tweetCount)
						update_option('cwp_topnew_notice', 'You have tried to post more tweets that they are available, try to include more categories or increase the date range');
					else
						update_option('cwp_topnew_notice', 'Tweet was already tweeted, if you want to tweet your old tweets more than once, select "Tweet old posts more than once" option');
				}
			}

		}

		public function findInString($where,$what) {
			if (!is_string($where)) {
				return false;
			}
			else
				return strpos($where,$what);
		}

		public function getNotice() {
			$notice = get_option('cwp_topnew_notice');
				
			//$notice = strpos($notice,'UPDAT');
			if (is_object($notice) && $notice->errors[0]->message)
				echo "Error for your last tweet was :'".$notice->errors[0]->message."'";
			else if ( $notice !== "OK" && !is_object($notice) && $this->findInString($notice,'UPDAT')===false && $notice!=="")
				echo "Error for your last post was :'".$notice."'";
			else
				if (is_object($notice) && $notice->text || $notice=="OK" || strpos($notice,'UPDAT')!==false) {
				echo "Congrats! Your last post was revived successfully";
			} else if ($notice!="") {
				echo "Error for your last post was : ".$notice;
			}

			
			die();
		}

		public function tweetNow() {
			$this->tweetOldPost(get_option('top_lastID'));
		}

		public function viewSampleTweet()
		{

			$returnedTweets = $this->getTweetsFromDB();
			$image="";
			//var_dump($returnedTweets);
			$finalTweetsPreview = $this->generateTweetFromPost($returnedTweets[0]);
			if (is_array($finalTweetsPreview)){
				$finalTweetsPreview = $finalTweetsPreview['message'];
			}
			$result = $finalTweetsPreview;			
			update_option( 'top_lastID', $returnedTweets[0]->ID);

			if (function_exists('topProImage') && get_option('top_opt_post_with_image')=="on") {

				if ( strlen( $img = get_the_post_thumbnail( $returnedTweets[0]->ID, array( 150, 150 ) ) ) ) :
				    $image_array = wp_get_attachment_image_src( get_post_thumbnail_id( $returnedTweets[0]->ID ), 'optional-size' );
				    $image = $image_array[0];
				else :
				    $post = get_post($returnedTweets[0]->ID);
					$image = '';
					ob_start();
					ob_end_clean();
					$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);

					$image = $matches [1] [0];
				endif;

				$result = '<img class="top_preview" src="'.$image.'"/>'.$finalTweetsPreview;
			}
			
			echo $result;

			
			die(); // required
		}

		/**
		 * Returns if the post is already tweeted
		 * @param  [type]  $postId Post ID
		 * @return boolean         True if not already tweeted / False if already tweeted
		 */
		
		public function isNotAlreadyTweeted($postId) {
			// Get all already tweeted posts

			$tweetedPosts = get_option("top_opt_already_tweeted_posts");

			if (!$tweetedPosts) {
				add_option("top_opt_already_tweeted_posts");
				return true;
			}
			
				// If the new post ID is in the array, which means that is already tweeted
			if (!empty($tweetedPosts) && is_array($tweetedPosts) ) {

			    if (in_array($postId, $tweetedPosts))
			    	return false;
				else
					return true;
				}
			else
			{	
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
			$common_hashtags				= get_option('top_opt_hashtags');
			$maximum_hashtag_length 		= get_option('top_opt_hashtag_length');
			$hashtag_custom_field 			= get_option('top_opt_custom_hashtag_field');
			$bitly_key 						= get_option('top_opt_bitly_key');
            $bitly_user 					= get_option('top_opt_bitly_user');
            $ga_tracking  					= get_option('top_opt_ga_tracking');
            $post_with_image 				= get_option('top_opt_post_with_image');
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
					$tweetContent = get_post_meta($postQuery->ID, $tweet_content_custom_field,true);
					break;
				default:
					$tweetContent = $finalTweet;
					break;
			}

			// Trim new empty lines.
			
			$tweetContent = strip_tags($tweetContent);
			$tweetContent = esc_html($tweetContent);
			$tweetContent = trim(preg_replace('/\s+/', ' ', $tweetContent));

			// Remove html entinies.
			$tweetContent = preg_replace("/&#?[a-z0-9]+;/i","", $tweetContent);

			// Strip all shortcodes from content.
			$tweetContent = strip_shortcodes($tweetContent);

			// Generate the post link.
			if($include_link == 'true') {
				if($fetch_url_from_custom_field == 'on') {
					$post_url = " " . get_post_meta($postQuery->ID, $custom_field_url,true);
				} else { 
					$post_url = " " . get_permalink($postQuery->ID);
				}

				if ($post_url==" ")
					$post_url = " " . get_permalink($postQuery->ID);

				if ($ga_tracking=="on") {
					$param = 'utm_source=ReviveOldPost&utm_medium=social&utm_campaign=ReviveOldPost';
					$post_url = rtrim($post_url);
					if (strpos($post_url,"?")===FALSE)
						$post_url.='?'.$param;
					else
						$post_url.='&'.$param;
				}

				if($use_url_shortner == 'on') {
					$post_url = " " . $this->shortenURL($post_url, $url_shortner_service, $postQuery->ID, $bitly_key, $bitly_user);
				}

				if ($post_url==" ")
					$post_url = " " . get_permalink($postQuery->ID);

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
							if(strlen($category->cat_name.$newHashtags) <= $maximum_hashtag_length || $maximum_hashtag_length == 0) { 
						 		$newHashtags = $newHashtags . " #" . preg_replace('/-/','',strtolower($category->slug)); 
						 	}
						} 

						break;

					case 'tags':
						$postTags = wp_get_post_tags($postQuery->ID);
						
						foreach ($postTags as $postTag) {
							if(strlen($postTag->slug.$newHashtags) <= $maximum_hashtag_length || $maximum_hashtag_length == 0) {
								$newHashtags = $newHashtags . " #" . preg_replace('/-/','',strtolower($postTag->slug));
							}
						}
						break;

					case 'custom':
						$newHashtags = get_post_meta($postQuery->ID, $hashtag_custom_field, true);
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
				$post_url = htmlentities($post_url);
				$postURLLength = strlen($post_url); 
				if ($postURLLength > 21) $postURLLength = 22;
				$finalTweetLength += intval($postURLLength);
			}

			if(!empty($newHashtags)) {
				$hashtagsLength = strlen($newHashtags); 
				$finalTweetLength += intval($hashtagsLength);
			}

			if ($post_with_image == "on")
				$finalTweetLength += 25;

			$finalTweetLength = 139 - $finalTweetLength - 5;

			$tweetContent = mb_substr($tweetContent,0, $finalTweetLength) . " ";

			$finalTweet = $additionalTextBeginning . $tweetContent . "%short_urlshort_urlurl%" . $newHashtags . $additionalTextEnd;
			$finalTweet = substr($finalTweet,0, 139);
			$finalTweet = str_replace("%short_urlshort_urlurl%",$post_url,$finalTweet);
			$fTweet = array();
			$fTweet['message'] = strip_tags($finalTweet);
			$fTweet['link'] = $post_url;
			// Strip any tags and return the final tweet
			return $fTweet; 
		}

		/**
		 * Tweets the returned post from generateTweetFromPost()
		 * @param  [type] $finalTweet Generated tweet
		 */
		
		public function tweetPost($finalTweet)
		{	
			$k=1;
			$nrOfUsers = count($this->users);

			foreach ($this->users as $user) {

				switch ($user['service']) {
					case 'twitter':
						// Create a new twitter connection using the stored user credentials.
						$connection = new RopTwitterOAuth($this->consumer, $this->consumerSecret, $user['oauth_token'], $user['oauth_token_secret']);
						// Post the new tweet
						$status = $connection->post('statuses/update', array('status' => $finalTweet['message']));	
						//return $status;
						if ($nrOfUsers == $k)
							return $status;
						else
							$k++;
						break;
					
					case 'facebook':
						$args =  array(
							
							'body' => array( 'message' => $finalTweet['message'],'link' => $finalTweet['link']),
							
										);

						$pp=wp_remote_post("https://graph.facebook.com/".ROP_TOP_FB_API_VERSION."/$user[id]/feed?access_token=$user[oauth_token]",$args);
						if ($nrOfUsers == $k)
							return $pp['response']['message'];
						else
							$k++;
						
						break;

					case 'linkedin':

						$visibility="anyone";
						$content_xml.="<content><title>".$finalTweet['message']."</title><submitted-url>".$finalTweet['link']."</submitted-url></content>";
						$url = 'https://api.linkedin.com/v1/people/~/shares?oauth2_access_token='.$user["oauth_token"];


						$xml       = '<?xml version="1.0" encoding="UTF-8"?><share>
			             ' . $content_xml . '
			             <visibility>
			               <code>' . $visibility . '</code>
			             </visibility>
			           </share>';
			           				$headers = array(
						    "Content-type: text/xml",
						    "Content-length: " . strlen($xml),
						    "Connection: close",
						);
			           	if (!function_exists('curl_version'))
       						update_option('cwp_topnew_notice',"You host does not support CURL");       				
						$ch = curl_init(); 
						curl_setopt($ch, CURLOPT_URL,$url);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_TIMEOUT, 10);
						curl_setopt($ch, CURLOPT_POST, true);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
						curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

						$data = curl_exec($ch);

						if ($nrOfUsers == $k)
							return $data;
						else
							$k++;
						
						if(curl_errno($ch))
						    print curl_error($ch);
						else
						    curl_close($ch);
						
						break;

					default:
						// Create a new twitter connection using the stored user credentials.
						$connection = new RopTwitterOAuth($this->consumer, $this->consumerSecret, $user['oauth_token'], $user['oauth_token_secret']);
						// Post the new tweet
						$status = $connection->post('statuses/update', array('status' => $finalTweet['message']));	
						//return $status;
						if ($nrOfUsers == $k)
							return $status;
						else
							$k++;
						break;


				}
								
				
				
			}
		}


		public function tweetPostwithImage($finalTweet, $id)
		{	

			$k=1;
			$tw=0;
			$nrOfUsers = count($this->users);

			foreach ($this->users as $user) {

				switch ($user['service']) {
					case 'twitter':
						// Create a new twitter connection using the stored user credentials.
						$connection = new RopTwitterOAuth($this->consumer, $this->consumerSecret, $user['oauth_token'], $user['oauth_token_secret']);
						// Post the new tweet
						if (function_exists('topProImage')) 
							$status = topProImage($connection, $finalTweet['message'], $id);
							//$tw++;
						//} else {
						///	//$connection = new RopTwitterOAuth($this->consumer, $this->consumerSecret, $user['oauth_token'], $user['oauth_token_secret']);
							//$status = $connection->post('statuses/update', array('status' => "acesta e un tweet"));	
							//$tw++;
						//}

						if ($nrOfUsers == $k)
							return $status;
						else
							$k++;	

					case 'facebook':
						$args =  array(
							
							'body' => array( 'message' => $finalTweet['message'],'link' => $finalTweet['link']),
							
										);

						$pp=wp_remote_post("https://graph.facebook.com/".ROP_TOP_FB_API_VERSION."/$user[id]/feed?access_token=$user[oauth_token]",$args);
						if ($nrOfUsers == $k)
							return $pp['response']['message'];
						else
							$k++;
						
						break;

					case 'linkedin':

						$visibility="anyone";
						$content_xml.="<content><title>".$finalTweet['message']."</title><submitted-url>".$finalTweet['link']."</submitted-url></content>";
						$url = 'https://api.linkedin.com/v1/people/~/shares?oauth2_access_token='.$user["oauth_token"];


						$xml       = '<?xml version="1.0" encoding="UTF-8"?><share>
			             ' . $content_xml . '
			             <visibility>
			               <code>' . $visibility . '</code>
			             </visibility>
			           </share>';
			           				$headers = array(
						    "Content-type: text/xml",
						    "Content-length: " . strlen($xml),
						    "Connection: close",
						);
			           	if (!function_exists('curl_version'))
       						update_option('cwp_topnew_notice',"You host does not support CURL");       					
						$ch = curl_init(); 
						curl_setopt($ch, CURLOPT_URL,$url);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_TIMEOUT, 10);
						curl_setopt($ch, CURLOPT_POST, true);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
						curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

						$data = curl_exec($ch);

						if ($nrOfUsers == $k)
							return $data;
						else
							$k++;

						if(curl_errno($ch))
						    print curl_error($ch);
						else
						    curl_close($ch);
						
						break;
					default:
						$connection = new RopTwitterOAuth($this->consumer, $this->consumerSecret, $user['oauth_token'], $user['oauth_token_secret']);
						// Post the new tweet
						if (function_exists('topProImage')) 
							$status = topProImage($connection, $finalTweet['message'], $id);

						if ($nrOfUsers == $k)
							return $status;
						else
							$k++;	

					
				}	
				//sleep(100);
			}
		}
		
		// Generates the tweet date range based on the user input.
		public function getTweetPostDateRange()
		{
			if (get_option('top_opt_max_age_limit')==0 )
				$limit = 9999;
			else
				$limit = get_option('top_opt_max_age_limit');
				
			$minAgeLimit = "-" . get_option('top_opt_age_limit') . " days";
			
			$maxAgeLimit = "-" . $limit . " days";
			
			
			
			$minLimit = current_time('timestamp') - get_option('top_opt_age_limit')*24*60*60;
			$maxLimit = current_time('timestamp') - $limit*24*60*60;

			$minAgeLimit = date("Y-m-d H:i:s", $minLimit);
			$maxAgeLimit = date("Y-m-d H:i:s", $maxLimit);
	
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

		// Gets the omited tweet categories
		
		public function getExcludedCategories()
		{
			$postQueryCategories = "";
			$postCategories = get_option('top_opt_omit_cats');

			if(!empty($postCategories) && is_array($postCategories)) {
				$lastPostCategory = end($postCategories);
				foreach ($postCategories as $key => $cat) {
					if($cat == $lastPostCategory) {
						$postQueryCategories .= $cat;
					} else { 
						$postQueryCategories .= $cat . ", ";
					}
				}
			}
			else
				$postQueryCategories = get_option('top_opt_omit_cats');

			return $postQueryCategories;
		}

		// Gets the tweet post type.
		public function getTweetPostType()
		{
			$postQueryPostTypes = "";
			$top_opt_post_type = get_option('top_opt_post_type');

			if(!empty($top_opt_post_type) && is_array($top_opt_post_type)) {
				$lastPostCategory = end($top_opt_post_type);
				foreach ($top_opt_post_type as $key => $cat) {
					if($cat == $lastPostCategory) {
						$postQueryPostTypes .= "'".$cat."'";
					} else { 
						$postQueryPostTypes .= "'".$cat."'" . ", ";
					}
				}
			}
			else
				$postQueryPostTypes = get_option('top_opt_post_type');

			return $postQueryPostTypes;

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

		// Deactivation hook
		public function deactivationHook()
		{
			delete_option('activation_hook_test_motherfucker');
			$this->clearScheduledTweets();
			$this->deleteAllOptions();
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
			$this->users = get_option('cwp_top_logged_in_users');

			$this->pluginStatus = get_option('cwp_topnew_active_status');
			$this->intervalSet = get_option('top_opt_interval');

			//update_option('cwp_top_logged_in_users', '');
		}

		// Checks if twitter returned any temporary credentials to log in the user.
		public function afterAddAccountCheck()
		{
			if( time() - get_option("top_reauthorize") > 2592000 )
				$this->reAuthorize();
			global $cwp_top_settings;
			$code="";
			if(isset($_REQUEST['code']))
				$code = $_REQUEST["code"];

			if(isset($_REQUEST['oauth_token'])) {
				if($_REQUEST['oauth_token'] == $this->cwp_top_oauth_token) {

					$twitter = new RopTwitterOAuth($this->consumer, $this->consumerSecret, $this->cwp_top_oauth_token, $this->cwp_top_oauth_token_secret );
					$access_token = $twitter->getAccessToken($_REQUEST['oauth_verifier']);
					$user_details = $twitter->get('account/verify_credentials');

					$newUser = array(
						'user_id'				=> $user_details->id,
						'oauth_token'			=> $access_token['oauth_token'],
						'oauth_token_secret'	=> $access_token['oauth_token_secret'],
						'oauth_user_details'	=> $user_details,
						'service'				=> 'twitter'
					);

					$loggedInUsers = get_option('cwp_top_logged_in_users');
					if(empty($loggedInUsers)) { $loggedInUsers = array(); }


					if(in_array($newUser, $loggedInUsers)) {
						echo "You already added that user! no can do !";
					} else { 
						array_push($loggedInUsers, $newUser);
						update_option('cwp_top_logged_in_users', $loggedInUsers);
					}

					header("Location: " . SETTINGSURL);
					exit;
				}
			}

			if(isset($_REQUEST['state']) && (get_option('top_fb_session_state') === $_REQUEST['state'])) {
			
				$token_url = "https://graph.facebook.com/".ROP_TOP_FB_API_VERSION."/oauth/access_token?"
				. "client_id=" . get_option('cwp_top_app_id') . "&redirect_uri=" . SETTINGSURL
				. "&client_secret=" . get_option('cwp_top_app_secret') . "&code=" . $code;

				$params = null;$access_token="";
				$response = wp_remote_get($token_url);
				
				if(is_array($response))
				{
					if(isset($response['body']))
					{
						parse_str($response['body'], $params);
						if(isset($params['access_token']))
						$access_token = $params['access_token'];
					}
				}

				if($access_token!="")
				{									
					update_option('top_fb_token',$access_token);
						
				}
				header("Location: " . SETTINGSURL.'#fbadd');
			}
			
			if (isset($_GET['code'])&&isset($_GET['state'])&&get_option('top_lk_session_state') == $_GET['state']) {

				$lk_auth_token = get_option('cwp_top_lk_app_id');
				$lk_auth_secret = get_option('cwp_top_lk_app_secret');
				   $params = array('grant_type' => 'authorization_code',
                    'client_id' => $lk_auth_token,
                    'client_secret' => $lk_auth_secret,
                    'code' => $_GET['code'],
                    'redirect_uri' => SETTINGSURL,
              	);
				
				$url = 'https://www.linkedin.com/uas/oauth2/accessToken?' . http_build_query($params);
				//echo $url;
     			$response = wp_remote_post($url);
     			$token = json_decode($response['body']);
     			//print_r($response);
     			//print_r($token);
		        if($token->access_token) {
		          // the request went through without an error, gather user's 'access' tokens
		        	//AQVBBQ6_ggJaUVFYmJ5oVF_kSH-wn6VNREGgC_sYPWp0YV0U4r2CFwptnLXUbJra5Glp0ZMax96CrD2azzf_HkJ2UdLp5q5zoiT_rbl5bmTMf50XnDfRcdm8Vl2k2XoYhGQ-LkYTnddFz1K-OBcW0CWsapzgZH2hepMVMhc1Lw7bhwTab04"
		        	update_option('top_linkedin_token',$token->access_token);
	          		update_option('top_linkedin_token_expires',$token->expires_in);
				}

				$url = 'https://api.linkedin.com/v1/people/~:(id,picture-url,first_name,last_name)?oauth2_access_token='.$token->access_token;
				//echo $url;
				$response = wp_remote_get($url);
				$response = wp_remote_retrieve_body($response);
				//print_r($response);
				$person = simplexml_load_string($response);
				
				if (isset($person->id)) {
					$user_details = array('profile_image_url' => (string)$person->{'picture-url'},'name'=> (string)$person->{'first-name'} );
					
					$newUser = array(
							'user_id'				=> (string)$person->id,
							'oauth_token'			=> $token->access_token,
							'oauth_token_secret'	=> '',
							'oauth_user_details'	=> (object)$user_details,
							'service'				=> 'linkedin'
						);

					$loggedInUsers = get_option('cwp_top_logged_in_users');
					if(empty($loggedInUsers)) { $loggedInUsers = array(); }

					foreach ($loggedInUsers as $key=>$user) {
						if ($user['user_id'] == $person->id) 
							unset($loggedInUsers[$key]);
					}

					if(in_array($newUser, $loggedInUsers)) {
						echo "You already added that user! no can do !";
					} else { 
						array_push($loggedInUsers, $newUser);
						update_option('cwp_top_logged_in_users', $loggedInUsers);
					}
				}
					header("Location: " . SETTINGSURL);
			}


		}

		// Used to display the login buttons
		public function displayLoginButton($social_network)
		{
			// display the twitter login button
			if($this->userIsLoggedIn($social_network)) {
				$this->setAlloAuthSettings($social_network);
				return true;
			} else {
				return false;
			}
		}

		public function reAuthorize() {
			$top_session_state = uniqid('', true);
			update_option('top_reauthorize',time());
			$loggedInUsers = get_option('cwp_top_logged_in_users');
			if(empty($loggedInUsers)) { $loggedInUsers = array(); }
			$lk = 0;
			$fb = 0;

			foreach ($loggedInUsers as $key=>$user) {
				if ($user['service'] === "linkedin"&&$lk===0) {
					$lk++;
					 $url = 'https://www.linkedin.com/uas/oauth2/authorization?response_type=code&client_id='.get_option("cwp_top_lk_app_id").'&scope=rw_nus&state='.$top_session_state.'&redirect_uri='.SETTINGSURL;
			        header("Location: " . $url);
			        
			        update_option('top_lk_session_state',$top_session_state);

				}

				if ($user['service'] === "facebook"&&$fb===0) {
					$top_session_state_fb = md5(uniqid(rand(), TRUE));
			        $fb++;
			        update_option('top_fb_session_state',$top_session_state_fb);
			        $dialog_url = "https://www.facebook.com/".ROP_TOP_FB_API_VERSION."/dialog/oauth?client_id="
				. get_option("cwp_top_app_id") . "&redirect_uri=" . SETTINGSURL . "&state="
						. $top_session_state_fb . "&scope=publish_stream,publish_actions,manage_pages";

					header("Location: " . $dialog_url);
				}
			}
	        
		}

		// Adds pages
		public function displayPages()
		{
			$social_network = $_POST['social_network'];
			$access_token = get_option('top_fb_token');

			switch ($social_network) {
			    case 'facebook':
			    	$result1="";$pagearray1="";
					$pp=wp_remote_get("https://graph.facebook.com/".ROP_TOP_FB_API_VERSION."/me/accounts?access_token=$access_token&limit=100&offset=0");
					//print_r($pp);
					$me=wp_remote_get("https://graph.facebook.com/".ROP_TOP_FB_API_VERSION."/me/?access_token=$access_token&limit=100&offset=0");
					if(is_array($pp))
					{
						$result1=$pp['body'];
						$result2 = $me['body'];
						$pagearray2 = json_decode($result2);
						//print_r($pagearray2);
						$pagearray1 = json_decode($result1);
						$profile['name'] = $pagearray2->first_name.' '.$pagearray2->last_name;
						$profile['id'] = $pagearray2->id;
						$profile['category'] ='profile';
						$profile['access_token'] = $access_token;
						if(is_array($pagearray1->data))
							array_unshift($pagearray1->data, $profile);
							//$pagearray1->data[count($pagearray1->data)] = $profile;
						$result1 = json_encode($pagearray1);
						//print_r($results1);
						echo $result1;

					}
					break;
			}
			die(); // Required
		}

		// Adds pages
		public function addPages()
		{
			$social_network = $_POST['social_network'];
			$access_token = $_POST['page_token'];
			$page_id= $_POST['page_id'];

			switch ($social_network) {
			    case 'facebook':
			    	$user_details['profile_image_url'] = $_POST['picture_url'];
			    	$user_details['name'] = $_POST['page_name'];
			    	$user_details = (object) $user_details;
			    	$newUser = array(
						'user_id'				=> $page_id,
						'oauth_token'			=> $access_token,
						'oauth_token_secret'	=> "",
						'oauth_user_details'	=> $user_details,
						'service'				=> 'facebook'
					);

					$loggedInUsers = get_option('cwp_top_logged_in_users');
					if(empty($loggedInUsers)) { $loggedInUsers = array(); }

					foreach ($loggedInUsers as $key=>$user) {
						if ($user['user_id'] == $page_id) 
							unset($loggedInUsers[$key]);
					}

					if(in_array($newUser, $loggedInUsers)) {
						echo "You already added that user! no can do !";
					} else { 
						array_push($loggedInUsers, $newUser);
						update_option('cwp_top_logged_in_users', $loggedInUsers);
						echo SETTINGSURL;
					}

					
					break;
			}
			die(); // Required
		}

		// Adds new account
		public function addNewAccount()
		{
			global $cwp_top_settings;
			$social_network = $_POST['social_network'];
			switch ($social_network) {
			    case 'twitter':
			        $this->oAuthCallback = $_POST['currentURL'];
					$twitter = new RopTwitterOAuth($this->consumer, $this->consumerSecret);
					$requestToken = $twitter->getRequestToken($this->oAuthCallback);

					update_option('cwp_top_oauth_token', $requestToken['oauth_token']);
					update_option('cwp_top_oauth_token_secret', $requestToken['oauth_token_secret']);

					switch ($twitter->http_code) {
						case 200:
							$url = $twitter->getAuthorizeURL($requestToken['oauth_token']);
							echo $url;
							break;
						
						default:
							return __("Could not connect to Twitter!", CWP_TEXTDOMAIN);
							break;
					}
			        break;
			    case 'facebook':
			    	if (isset($_POST['app_id'])){
				    	update_option('cwp_top_app_id', $_POST['app_id']);
						update_option('cwp_top_app_secret', $_POST['app_secret']);
					
				        $top_session_state = md5(uniqid(rand(), TRUE));
				        
				        update_option('top_fb_session_state',$top_session_state);
				        $dialog_url = "https://www.facebook.com/".ROP_TOP_FB_API_VERSION."/dialog/oauth?client_id="
					. $_POST['app_id'] . "&redirect_uri=" . SETTINGSURL . "&state="
							. $top_session_state . "&scope=publish_stream,publish_actions,manage_pages";
						echo $dialog_url;
					}
			        break;
			    case 'linkedin':
			    	$top_session_state = uniqid('', true);

	              	$url = 'https://www.linkedin.com/uas/oauth2/authorization?response_type=code&client_id='.$_POST["app_id"].'&scope=rw_nus&state='.$top_session_state.'&redirect_uri='.SETTINGSURL;

	              	update_option('top_lk_session_state',$top_session_state);
					if (isset($_POST['app_id'])){ 
						update_option('cwp_top_lk_app_id', $_POST['app_id']);
						update_option('cwp_top_lk_app_secret', $_POST['app_secret']);
					}
					if (function_exists('topProAddNewAccount')) {
						echo $url;	    	
					}
					else{
						update_option('cwp_topnew_notice',"You need to <a target='_blank' href='https://themeisle.com/plugins/tweet-old-post-pro/?utm_source=topplusacc&utm_medium=announce&utm_campaign=top&upgrade=true'>upgrade to the PRO version</a> in order to add a Linkedin account, fellow pirate!");
						echo "You need to <a target='_blank' href='https://themeisle.com/plugins/tweet-old-post-pro/?utm_source=topplusacc&utm_medium=announce&utm_campaign=top&upgrade=true'>upgrade to the PRO version</a> in order to add more accounts, fellow pirate!";

					}
					
					break;

					
			    }
			




			die(); // Required
		}

		// Adds more than one account
		public function addNewAccountPro()
		{
			if (function_exists('topProAddNewAccount')) {
				topProAddNewAccount($_POST['social_network']);
			}
			else{
				update_option('cwp_topnew_notice',"You need to <a target='_blank' href='https://themeisle.com/plugins/tweet-old-post-pro/?utm_source=topplusacc&utm_medium=announce&utm_campaign=top&upgrade=true'>upgrade to the PRO version</a> in order to add more accounts, fellow pirate!");
				echo "You need to <a target='_blank' href='https://themeisle.com/plugins/tweet-old-post-pro/?utm_source=topplusacc&utm_medium=announce&utm_campaign=top&upgrade=true'>upgrade to the PRO version</a> in order to add more accounts, fellow pirate!";

			}
			die(); // Required
		}

		// Gets the next tweet interval.
		public function getNextTweetInterval()
		{
			$timestamp = wp_next_scheduled( 'cwp_top_tweet_cron' );
			//echo $timestamp;
			//$timestamp = date("Y-m-d H:i:s", $timestamp);
			//$timeLeft = get_date_from_gmt($timestamp);
			//$timeLeft = strtotime($timeLeft);
			echo $timestamp;
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
		public function logOutUser()
		{
			$userID = $_POST['user_id'];

			$users = get_option('cwp_top_logged_in_users');

			foreach ($users as $id => $user) {
				foreach ($user as $key => $value) {
					if($userID == $value) {
						$user_id = array_search($user, $users);
						unset($users[$user_id]);
					}
				}
			}

			update_option('cwp_top_logged_in_users', $users);

			$this->setAlloAuthSettings();
			die();
		}

		// Updates all options.
		public function updateAllOptions()
		{
			$dataSent = $_POST['dataSent']['dataSent'];

			$options = array();
			parse_str($dataSent, $options);

			//print_r($options);

			foreach ($options as $option => $newValue) {
				//$newValue = sanitize_text_field($newValue);
				update_option($option, $newValue);
			}

			//update_option('top_opt_post_type', 'post');

			if(!array_key_exists('top_opt_custom_url_option', $options)) {
				update_option('top_opt_custom_url_option', 'off');
			}

			if(!array_key_exists('top_opt_use_url_shortner', $options)) {
				update_option('top_opt_use_url_shortner', 'off');
			}

			if(!array_key_exists('top_opt_post_with_image', $options)) {
				update_option('top_opt_post_with_image', 'off');
			}

			if(!array_key_exists('top_opt_tweet_multiple_times', $options)) {
				update_option('top_opt_tweet_multiple_times', 'off');
			}

			if(!array_key_exists('top_opt_ga_tracking', $options)) {
				update_option('top_opt_ga_tracking', 'off');
			}

			//if(!array_key_exists('top_opt_tweet_specific_category', $options)) {
			//	update_option('top_opt_tweet_specific_category', '');
			//}

			if(!array_key_exists('top_opt_omit_cats', $options)) {
				update_option('top_opt_omit_cats', '');
			}

			if(!array_key_exists('top_opt_post_type', $options)) {
				update_option('top_opt_post_type', 'post');
			}

			//update_option("top_opt_already_tweeted_posts", array());

			die();
		}

		public function top_admin_notice() {
			global $current_user ;
		        $user_id = $current_user->ID;
		        /* Check that the user hasn't already clicked to ignore the message */
			if ( ! get_user_meta($user_id, 'top_ignore_notice3') ) {
		      //  echo '<div class="error"><p>';
		      //  printf(__(' We just fixed the interrupted posting issue and scheduling issue, if you don\'t see any tweets you need to re-authentificate your twitter accounts. | <a href="'.SETTINGSURL.'&top_nag_ignore=0">Hide Notice</a>'));
		       // echo "</p></div>";
			}
		}
		public function top_nag_ignore() {
			global $current_user;
		        $user_id = $current_user->ID;
		        /* If user clicks to ignore the notice, add that to their user meta */
		        if ( isset($_GET['top_nag_ignore']) && '0' == $_GET['top_nag_ignore'] ) {
		             add_user_meta($user_id, 'top_ignore_notice3', 'true', true);
			}
		}

		public function resetAllOptions()
		{
			update_option('activation_hook_test_motherfucker', "Well, the plugin was activated!");

			$defaultOptions = array(
				'top_opt_tweet_type'				=> 'title',
				'top_opt_post_with_image'			=> 'off',
				'top_opt_bitly_user'				=>'',
				'top_opt_bitly_key'					=>'',
				'top_opt_post_type_custom_field'	=> '',
				'top_opt_add_text'					=> '',
				'top_opt_add_text_at'				=> 'beginning',
				'top_opt_include_link'				=> 'true',
				'top_opt_custom_url_option'			=> 'off',
				'top_opt_use_url_shortner'			=> 'off',
				'top_opt_ga_tracking'				=> 'on',
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
				'top_opt_omit_cats'					=> '',
				'cwp_topnew_active_status'			=> 'false',
				'cwp_topnew_notice'					=> '',
				'top_opt_excluded_post'				=> '',
				'top_opt_tweet-multiple-times'		=> 'off',
				'cwp_top_logged_in_users'			=> '',
				'top_fb_token'						=>''
			);

			foreach ($defaultOptions as $option => $defaultValue) {
				update_option($option, $defaultValue);
			}
			//die();
		}

		public function deleteAllOptions()
		{
			global $defaultOptions;
			foreach ($defaultOptions as $option => $defaultValue) {
				delete_option($option);
			}
		}

		// Generate all fields based on settings
		public static function generateFieldType($field)
		{	
			$disabled = "";
			$pro = "";
			switch ($field['type']) {

				case 'text':
					echo "<input type='text' placeholder='".__($field['description'],CWP_TEXTDOMAIN)."' value='".$field['option_value']."' name='".$field['option']."' id='".$field['option']."'>";
					break;
			
				case 'select':
					$noFieldOptions = intval(count($field['options']));
					$fieldOptions = array_keys($field['options']);
					
					//if ($field['option']=='top_opt_post_type') $disabled = "disabled";
					print "<select id='".$field['option']."' name='".$field['option']."'".$disabled.">";
						for ($i=0; $i < $noFieldOptions; $i++) { 
							print "<option value=".$fieldOptions[$i];
							if($field['option_value'] == $fieldOptions[$i]) { echo " selected='selected'"; }
							print ">".__($field['options'][$fieldOptions[$i]],CWP_TEXTDOMAIN)."</option>";
						}
					print "</select>";
					break;

				case 'checkbox':
					if ($field['option']=='top_opt_post_with_image'&& !function_exists('topProImage')) {
						$disabled = "disabled='disabled'";
						$pro = __("This is only available in the",CWP_TEXTDOMAIN)."<a href='https://themeisle.com/plugins/tweet-old-post-pro/?utm_source=imagepro&utm_medium=link&utm_campaign=top&upgrade=true' target='_blank'> ".__("PRO version")."</a>";
					}
					print "<input id='".$field['option']."' type='checkbox' ".$disabled." name='".$field['option']."'";
					if($field['option_value'] == 'on') { echo "checked=checked"; }
					print " />".$pro;
          
         
					break;

				case 'categories-list':
					print "<div class='categories-list'>";
						$categories = get_categories();

						foreach ($categories as $category) {

							$top_opt_tweet_specific_category = get_option('top_opt_tweet_specific_category');

							if (!is_array(get_option('top_opt_omit_cats')))
								$top_opt_omit_specific_cats = explode(',',get_option('top_opt_omit_cats'));
							else
								$top_opt_omit_specific_cats = get_option('top_opt_omit_cats');

						print "<div class='cwp-cat'>";
								print "<input type='checkbox' name='".$field['option']."[]' value='".$category->cat_ID."' id='".$field['option']."_cat_".$category->cat_ID."'";

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

					case 'custom-post-type':
						print "<div class='post-type-list'>";
						$args = array(
						   'public'   => true,
						   '_builtin' => false
						);

						$output = 'names'; // names or objects, note names is the default
						$operator = 'and'; // 'and' or 'or'
						if (!function_exists('topProImage')) {
							$disabled = "disabled='disabled'";
							$pro = __("This is only available in the",CWP_TEXTDOMAIN)."<a href='https://themeisle.com/plugins/tweet-old-post-pro/?utm_source=imagepro&utm_medium=link&utm_campaign=top&upgrade=true' target='_blank'> ".__("PRO version")."</a>";
						}
						$post_types = get_post_types( $args, $output, $operator ); 
						array_push($post_types,"post","page");
						foreach ($post_types as $post_type) {

							//$top_opt_tweet_specific_category = get_option('top_opt_tweet_specific_category');

							if (!is_array(get_option('top_opt_post_type')))
								$top_opt_post_types = explode(',',get_option('top_opt_post_type'));
							else
								$top_opt_post_types = get_option('top_opt_post_type');

						print "<div class='cwp-cat'>";
								print "<input ".$disabled." type='checkbox' name='".$field['option']."[]' value='".$post_type."' id='".$field['option']."_cat_".$post_type."'";

								if($field['option'] == 'top_opt_post_type') {
									if(is_array($top_opt_post_types)) {
										if(in_array($post_type, $top_opt_post_types)) {
											print "checked=checked";
										}
									}					
								}


								print ">";
								print "<label for='".$field['option']."_cat_".$post_type."'>".$post_type."</label>";							
							print "</div>";
							
						}
					print "</div>.$pro";
					break;

			}

		}


		public function getTime() {
		    
		    echo current_time('timestamp',1);

		    die();
		}

		function top_plugin_action_links($links, $file) {

		    if ($file == ROPPLUGINBASENAME) {
		        // The "page" query string value must be equal to the slug
		        // of the Settings admin page we defined earlier, which in
		        // this case equals "myplugin-settings".
		        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=TweetOldPost">Settings</a>';
		        array_unshift($links, $settings_link);
		    }

		    return $links;
		}

		public function fixCron() {
			update_option('cwp_topnew_notice','');

			if ( defined( 'ALTERNATE_WP_CRON' ) ) {

				//delete_option( 'hmbkp_wp_cron_test_failed' );

				//echo 1;

				return 0;

			}
			else {

				$response = wp_remote_head( site_url( 'wp-cron.php' ), array( 'timeout' => 30 ) );

				if ( is_wp_error( $response ) ) {

					update_option('cwp_topnew_notice', "Looks like there is an issue with your WP Cron and Tweet Old Post relies on wp-cron to schedule tweets, see the <a href='http://wordpress.org/plugins/tweet-old-post/faq/'>FAQ</a> for more details!");

				} elseif ( wp_remote_retrieve_response_code( $response ) != 200 ) {

					update_option('cwp_topnew_notice', "Looks like there is an issue with your WP Cron and Tweet Old Post relies on wp-cron to schedule tweets, see the <a href='http://wordpress.org/plugins/tweet-old-post/faq/'>FAQ</a> for more details!");

				}
			

				$timestamp = wp_next_scheduled( 'cwp_top_tweet_cron' );
				$timenow = current_time('timestamp',1);

				if ($this->pluginStatus == 'true' && $timenow > $timestamp) {
					update_option('cwp_topnew_notice', "Looks like there is an issue with your WP Cron, read more <a href='http://wordpress.org/plugins/tweet-old-post/faq/'>here</a>");
					
				}
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
			add_action('wp_ajax_nopriv_add_new_account', array($this, 'addNewAccount'));
			add_action('wp_ajax_add_new_account', array($this, 'addNewAccount'));

			// Display managed pages ajax action
			add_action('wp_ajax_nopriv_display_pages', array($this, 'displayPages'));
			add_action('wp_ajax_display_pages', array($this, 'displayPages'));

			// Add new account managed pages ajax action
			add_action('wp_ajax_nopriv_add_pages', array($this, 'addPages'));
			add_action('wp_ajax_add_pages', array($this, 'addPages'));

			// Add more than one twitter account ajax action
			add_action('wp_ajax_nopriv_add_new_account_pro', array($this, 'addNewAccountPro'));
			add_action('wp_ajax_add_new_account_pro', array($this, 'addNewAccountPro'));

			// Log Out Twitter user ajax action
			add_action('wp_ajax_nopriv_log_out_user', array($this, 'logOutUser'));
			add_action('wp_ajax_log_out_user', array($this, 'logOutUser'));

			// Tweet Old Post ajax action.
			add_action('wp_ajax_nopriv_tweet_old_post_action', array($this, 'startTweetOldPost'));
			add_action('wp_ajax_tweet_old_post_action', array($this, 'startTweetOldPost'));

			// Tweet Old Post view sample tweet action.
			add_action('wp_ajax_nopriv_view_sample_tweet_action', array($this, 'viewSampleTweet'));
			add_action('wp_ajax_view_sample_tweet_action', array($this, 'viewSampleTweet'));

			// Tweet Old Post tweet now action.
			add_action('wp_ajax_nopriv_tweet_now_action', array($this, 'tweetNow'));
			add_action('wp_ajax_tweet_now_action', array($this, 'tweetNow'));

			add_action('wp_ajax_nopriv_gettime_action', array($this, 'getTime'));
			add_action('wp_ajax_gettime_action', array($this, 'getTime'));

			add_action('wp_ajax_nopriv_getNotice_action', array($this, 'getNotice'));
			add_action('wp_ajax_getNotice_action', array($this, 'getNotice'));

			// Tweet Old Post ajax action
			add_action('wp_ajax_nopriv_stop_tweet_old_post', array($this, 'stopTweetOldPost'));
			add_action('wp_ajax_stop_tweet_old_post', array($this, 'stopTweetOldPost'));

			//Settings link

			//add_filter('plugin_action_links', array($this,'top_plugin_action_links'), 10, 2);

			//add_action('admin_notices', array($this,'top_admin_notice'));

			add_action('admin_init', array($this,'top_nag_ignore'));

			// Filter to add new custom schedule based on user input
			add_filter('cron_schedules', array($this, 'createCustomSchedule'));

			add_filter('plugin_action_links',array($this,'top_plugin_action_links'), 10, 2);

			add_action('cwp_top_tweet_cron', array($this, 'tweetOldPost'));
			add_action( 'plugins_loaded', array($this, 'addLocalization') );
		}

		public function loadAllScriptsAndStyles()
		{
			global $cwp_top_settings; // Global Tweet Old Post Settings

			// Enqueue and register all scripts on plugin's page
			if(isset($_GET['page'])) {
				if ($_GET['page'] == $cwp_top_settings['slug'] || $_GET['page'] == "ExcludePosts") {

					// Enqueue and Register Main CSS File
					wp_register_style( 'cwp_top_stylesheet', ROPCSSFILE, false, '1.0.0' );
			        wp_enqueue_style( 'cwp_top_stylesheet' );

			        // Register Main JS File
			        wp_enqueue_script( 'cwp_top_js_countdown', ROPJSCOUNTDOWN, array(), '1.0.0', true );
			        wp_enqueue_script( 'cwp_top_javascript', ROPJSFILE, array(), '1.0.0', true );
			        wp_localize_script( 'cwp_top_javascript', 'cwp_top_ajaxload', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
				 }				
			}
 	
		}

		function top_check_user_role( $role, $user_id = null ) {
 
		    if ( is_numeric( $user_id ) )
			$user = get_userdata( $user_id );
		    else
		        $user = wp_get_current_user();
		 
		    if ( empty( $user ) )
			return false;
		 
		    return in_array( $role, (array) $user->roles );
		}

		public function addAdminMenuPage()
		{
			global $cwp_top_settings; // Global Tweet Old Post Settings
			if (!current_user_can('manage_options') && $this->top_check_user_role( 'Administrator' ))
				$cap = 1;
			else
				$cap='manage_options';
			add_menu_page($cwp_top_settings['name'], $cwp_top_settings['name'], $cap, $cwp_top_settings['slug'], array($this, 'loadMainView'), '','99.87514');
			add_submenu_page($cwp_top_settings['slug'], __('Exclude Posts',CWP_TEXTDOMAIN), __('Exclude Posts',CWP_TEXTDOMAIN), 'manage_options', 'ExcludePosts', 'top_exclude');
		}

		public function loadMainView()
		{
			global $cwp_top_fields;
			foreach ($cwp_top_fields as $field => $value) {
				$cwp_top_fields[$field]['option_value'] = get_option($cwp_top_fields[$field]['option']); 
			}
			require_once(plugin_dir_path( __FILE__ )."view.php");
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
		public function shortenURL($url, $service, $id, $bitly_key, $bitly_user) {
			
			if ($service == "bit.ly") {
				//$shortURL = $url;
				$url = trim($url);
				$bitly_key = trim($bitly_key);
				$bitly_user = trim($bitly_user);
		        $shortURL = "http://api.bit.ly/v3/shorten?format=txt&login=".$bitly_user."&apiKey=".$bitly_key."&longUrl={$url}";
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
		    } elseif ($service == "is.gd") {
		        $shortURL = "http://is.gd/api.php?longurl={$url}";
		        $shortURL = $this->sendRequest($shortURL, 'GET');
		    } elseif ($service == "t.co") {
		        $shortURL = "http://twitter.com/share?url={$url}";
		        $shortURL = $this->sendRequest($shortURL, 'GET');
		    } else {
		    	$shortURL = wp_get_shortlink($id);
		    }

		    if($shortURL != ' 400 '&& $shortURL!="500" && $shortURL!="0") {
		    	return $shortURL;
		    }
		    else
		    	update_option('cwp_topnew_notice','Looks like is an error with your url shortner');
		}

		public function rop_load_dashboard_icon()
		{
			wp_register_style( 'rop_custom_dashboard_icon', ROPCUSTOMDASHBOARDICON, false, '1.0.0' );
			wp_enqueue_style( 'rop_custom_dashboard_icon' );
		}

	}
}

if(class_exists('CWP_TOP_Core')) {
	$CWP_TOP_Core = new CWP_TOP_Core;
}