<?php
// Basic configuration
require_once(ROPPLUGINPATH."/inc/config.php");
// RopTwitterOAuth class
require_once(ROPPLUGINPATH."/inc/oAuth/twitteroauth.php");

// Added by Ash/Upwork
define("ROP_IS_TEST", false);
define("ROP_IS_DEBUG", false);
// Added by Ash/Upwork


if (!class_exists('CWP_TOP_Core')) {
	class CWP_TOP_Core {

		// All fields
		public static $fields;
		public static $networks;
		// Number of fields
		public static $noFields;
		public $notices;
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

		public $users;
		private $user_info;

		// Plugin Status
		public $pluginStatus;
		// Interval Set
		public $intervalSet;
		public $cwp_twitter;
		public static $date_format;
		public function __construct() {
            if (ROP_IS_DEBUG) @mkdir(ROPPLUGINPATH . "/tmp");

			// Get all fields
			global $cwp_top_fields;
			global $cwp_top_networks;


			$this->setAlloAuthSettings();
			// Load all hooks
			$this->loadAllHooks();

			// Set all authentication settings
			// Check if the user added any account
			$this->afterAddAccountCheck();

			// Save all fields in static var
			self::$fields = $cwp_top_fields;
			self::$networks = $cwp_top_networks;

			// Save all number of fields in static var
			self::$noFields = count(self::$fields);
		}
		public static function addNotice($message,$type){
			$errors = get_option('rop_notice_active');
			if(count($errors)  > 30) $errors = array();
			$errors[] = array(
								"time"=>date('j-m-Y h:i:s A'),
								'message'=>$message,
								"type" => $type
							);
			update_option("rop_notice_active",$errors);

		}
		public static function clearLog(){
			if(!is_admin()) return false;
			update_option("rop_notice_active",array());

		}
		public static function addLog($m){
			$m .= date('l jS \of F Y h:i:s A')." - ".$m." \n\n  ";
			file_put_contents("rop.log",$m,FILE_APPEND);

		}
		public function addLocalization() {

			load_plugin_textdomain('tweet-old-post', false, dirname(ROPPLUGINBASENAME).'/languages/');
		}
		function checkUsers(){
			if(!is_array($this->users)) $this->users = array();
			if(count($this->users) == 0){

				self::addNotice(__("You have no account set to post !", 'tweet-old-post'),"error");
				die();
			}

		}
		public function startTweetOldPost( )
		{
			if(!is_admin()) return false;
			$this->checkUsers();
			if($this->pluginStatus !== 'true' ) {
				do_action("rop_start_posting");
			}
			die();
		}
		public function startPosting(){

			update_option('cwp_topnew_active_status', 'true');
			update_option('top_opt_already_tweeted_posts',array());
			update_option('top_last_tweets',array());
			$timeNow =  $this->getTime();
			$timeNow = $timeNow+15;
            // Added by Ash/Upwork for advanced scheduling
            set_transient('top_firstpost_time', $timeNow, 15);
            //error_log("first post time " . date("g:i:s A", $timeNow));
            // Added by Ash/Upwork for advanced scheduling

			$this->clearScheduledTweets();
			$networks = $this->getAvailableNetworks();

			foreach($networks as $network){
				wp_schedule_single_event($timeNow,$network.'roptweetcron',array($network));
			}

		}

		public function stopPosting(){

			// Set it to inactive status
			update_option('cwp_topnew_active_status', 'false');
			update_option('cwp_topnew_notice', '');
			update_option('top_opt_already_tweeted_posts',array());

			// Clear all scheduled tweets
			$this->clearScheduledTweets();
		}
		public function stopTweetOldPost()
		{
			if(!is_admin()) return false;
			//echo $this->pluginStatus;
			// If the plugin is active
			if($this->pluginStatus !== 'false') {
				do_action("rop_stop_posting");
			}

			die(); // Required for AJAX
		}

		public function getExcludedPosts($ntk=null) {

			$postQueryPosts = "";
			$postPosts = get_option('top_opt_excluded_post');

			if(!empty($postPosts) && is_array($postPosts)) {
				 $postQueryPosts = implode(',',$postPosts);
			}
			else
				$postQueryPosts = get_option('top_opt_excluded_post');

            // Added by Ash/Upwork
            if($ntk){
                $excludePosts   = get_option("cwp_top_exclude_from_" . $ntk);
                if(!is_array($excludePosts)){
                    $excludePosts   = array();
                }
                if(!is_array($postQueryPosts)){
                    $postQueryPosts = explode(",", $postQueryPosts);
                }
                if(!is_array($postQueryPosts)){
                    $postQueryPosts = array();
                }
                $postQueryPosts = array_merge($postQueryPosts, $excludePosts);
                $postQueryPosts = implode(",", $postQueryPosts);
            }
            // Added by Ash/Upwork
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

        // Added by Ash/Upwork
        /**
        * This will get the postIDs for the particular network
        */
        private function getAlreadyTweetedPosts($ntk){
            $array      = array();
            if(!$ntk) return $array;

            $opt        = get_option("top_opt_posts_buffer_" . $ntk, array());
            if(!$opt || !is_array($opt)) return $array;
            
            return $opt;
        }

        /**
        * This will add the postID to the network buffer to note which post was posted on which network
        */
        public function setAlreadyTweetedPosts($ntk, $postID){
            if(!$ntk || !$postID) return;
            $opt        = get_option("top_opt_posts_buffer_" . $ntk, array());
            if(!$opt || !is_array($opt)){
                $opt    = array();
            }
            $opt[]      = $postID;


            update_option("top_opt_posts_buffer_" . $ntk, $opt);
        }

        /**
        * This will clear the network buffer
        */
        private function clearAlreadyTweetedPosts($ntk){
            if(!$ntk) return;
            delete_option("top_opt_posts_buffer_" . $ntk);
        }

        /**
        * This is a test method that will be fired on admin_init. Only to be used for testing.
        * In production, this function will just return harmlessly
        */
        public function doTestAction(){
            if(!ROP_IS_TEST) return;

            $networks   = array("twitter", "facebook", "linkedin", "tumblr");
            $ntk        = $networks[array_rand($networks)];
            for($x = 0; $x < rand(0,5); $x++) $this->setAlreadyTweetedPosts($ntk, rand(0,100));
            echo "<pre>network=".$ntk.print_r($this->getAlreadyTweetedPosts($ntk),true). "</pre>";
        }
        
        // Added by Ash/Upwork

		public function getTweetsFromDB($ntk=null, $clearNetworkBuffer=false, $tweetCount=null)
		{
			global $wpdb;
            $limit      = 50;
			// Generate the Tweet Post Date Range
			$dateQuery = $this->getTweetPostDateRange();
			if(!is_array($dateQuery)) return false;
			// Get the number of tweets to be tweeted each interval.
            if($tweetCount){
                $limit  = $tweetCount;
            }else{
			    $tweetCount = intval(get_option('top_opt_no_of_tweet'));
            }
			if($tweetCount == 0 ) {
				self::addNotice("Invalid number for  Number of Posts to share. It must be a value greater than 0 ",'error');
				return false;
			}
			// Get post categories set.
//			$postQueryCategories =  $this->getTweetCategories();
			$excludedIds = "";
            // Added by Ash/Upwork
            $tweetedPosts   = $this->getAlreadyTweetedPosts($ntk);
            /*
			$tweetedPosts = get_option("top_opt_already_tweeted_posts");
			if(!is_array($tweetedPosts)) $tweetedPosts = array();

			if (get_option('top_opt_tweet_multiple_times')=="on") {

				$tweetedPosts = array();
			}
            */
			$postQueryExcludedPosts = $this->getExcludedPosts($ntk);
			$postQueryExcludedPosts = explode (',',$postQueryExcludedPosts);
			$excluded = array_merge($tweetedPosts,$postQueryExcludedPosts);
			$excluded = array_unique($excluded);
			$excluded = array_filter($excluded);
			$specificCategories = $this->getExcludedCategories();
			$somePostType = $this->getTweetPostType();
			// Generate dynamic query.
			$query =   "
				SELECT {$wpdb->prefix}posts.ID
				FROM  {$wpdb->prefix}posts
				LEFT JOIN {$wpdb->prefix}term_relationships ON ({$wpdb->prefix}posts.ID = {$wpdb->prefix}term_relationships.object_id)
				WHERE 1=1 ";
             
            // Added by Ash/Upwork
            if(array_key_exists("before", $dateQuery)){
                $query  .= "AND post_date >= '{$dateQuery['before']}' ";
            }
            if(array_key_exists("after", $dateQuery)){
                $query  .= "AND post_date <= '{$dateQuery['after']}' ";
            }
            // Added by Ash/Upwork

			// If there are no categories set, select the post from all.
			//if(!empty($postQueryCategories)) {
			//		$query .= "AND (wp_term_relationships.term_taxonomy_id IN ({$postQueryCategories})) ";
			//	}

			if(!empty($specificCategories)) {
                $categoryFilter = get_option("top_opt_cat_filter", "exclude") == "exclude" ? "NOT IN" : "IN";
				$query          .= "AND ( {$wpdb->prefix}posts.ID {$categoryFilter} (
					SELECT object_id
					FROM {$wpdb->prefix}term_relationships
					INNER JOIN {$wpdb->prefix}term_taxonomy ON ( {$wpdb->prefix}term_relationships.term_taxonomy_id = {$wpdb->prefix}term_taxonomy.term_taxonomy_id )
                    WHERE    {$wpdb->prefix}term_taxonomy.term_id IN ({$specificCategories}))) ";
			}

			if(!empty($excluded)) {
				$excluded = implode(',',$excluded);
				$query .= "AND ( {$wpdb->prefix}posts.ID NOT IN ({$excluded})) ";
			}
			if(!empty($somePostType)){
				$somePostType = explode(',',$somePostType);
				$somePostType = "'".implode("','",$somePostType)."'";
			}

            // Added by Ash/Upwork
            // Use a more efficient = condition than a costly IN condition if only one type is present
            if(!empty($somePostType)){
                if(strpos($somePostType, ",") !== FALSE){
                    $query .= "AND {$wpdb->prefix}posts.post_type IN ({$somePostType}) ";
                }else{
                    $query .= "AND {$wpdb->prefix}posts.post_type = ({$somePostType}) ";
                }
            }
            // Added by Ash/Upwork

			$query .= "AND ({$wpdb->prefix}posts.post_status = 'publish')
						GROUP BY {$wpdb->prefix}posts.ID ";
            if(!ROP_IS_TEST){
                $query  .= "order by RAND() ";
            }
            $query  .= "limit {$limit}";

            $returnedPost = $wpdb->get_results( $query);

            self::writeDebug("rows " . count($returnedPost) . " from " . $query);

            // Added by Ash/Upwork
            // If the number of posts found is zero and a post can be shared multiple times, lets clear the buffer and fetch again
            if($ntk && !$clearNetworkBuffer && count($returnedPost) == 0 && get_option('top_opt_tweet_multiple_times') == "on"){
                //self::addLog("clearing for $ntk!!");
                $this->clearAlreadyTweetedPosts($ntk);
                return $this->getTweetsFromDB($ntk, true);
            }
            // Added by Ash/Upwork

			if(count($returnedPost) >   $tweetCount) {
				$rand_keys = array_rand( $returnedPost, $tweetCount );

				if ( is_int( $rand_keys ) ) {
					$rand_keys = array( $rand_keys );
				}
				$return = array();

				foreach ( $rand_keys as $rk ) {
					$return[] = $returnedPost[ $rk ];
				}
				$returnedPost = $return;
				if ( count( $returnedPost ) > $tweetCount ) {
					$returnedPost = array_slice( $returnedPost, 0, $tweetCount );
				}
			}
			$ids = array();
			foreach($returnedPost as $rp){
				$ids[] = $rp->ID;

			}


			$returnedPost  = array();
			if(!empty($ids))
				$returnedPost = $wpdb->get_results("select * from {$wpdb->prefix}posts where ID in (".implode(",",$ids).") ");

			return $returnedPost;

		}
		public function isPostWithImageEnabled ($ntk = "twitter") {
			$options = get_option("top_opt_post_formats");
			$format_fields = $this->getFormatFields();
			$value = isset($options[$ntk."_".$format_fields[$ntk]["use-image"]['option']]) ? $options[$ntk."_".$format_fields[$ntk]["use-image"]['option']] : get_option("top_opt_post_with_image")  ;
			if ($value !='on')
				return false;
			else
				return true;
		}
		public function getUsers(){
			$users = apply_filters("rop_users_filter",get_option('cwp_top_logged_in_users'));
			return is_array($users) ? $users : array();


		}
		public function checkNetworkLock($ntk){
			if ( wp_using_ext_object_cache() ) {
			    $value = wp_cache_get( $ntk.'roplock', 'transient', true );
				return ( false !== $value) ;
			} else {


				return (false !== ( $value = get_transient( $ntk.'roplock' ) ));
			}




		}
		public function setNetworkLock($ntk){
			if ( wp_using_ext_object_cache() ) {
				wp_cache_set(  $ntk.'roplock', "lock", 'transient', 5 * MINUTE_IN_SECONDS );
			} else {
				set_transient(  $ntk.'roplock' , "lock",5 * MINUTE_IN_SECONDS);
			}

		}
		public function deleteNetworkLock($ntk){
			if ( wp_using_ext_object_cache() ) {
				wp_cache_delete($ntk.'roplock','transient');
			} else {
				delete_transient($ntk.'roplock');
			}

		}

		public function tweetOldPost($ntk = "",$byID = false)

		{
			if ( $this->checkNetworkLock($ntk) && $byID === false ) return false;


			$this->setNetworkLock($ntk);
			if ($byID!==false) {

				$returnedPost = $this->getTweetsFromDBbyID($byID);
			}else{
				$returnedPost = $this->getTweetsFromDB($ntk);
				if(!is_array($returnedPost)) return false;
			}
			if (count($returnedPost) == 0 ) {
				self::addNotice(__('There is no suitable post to tweet make sure you excluded correct categories and selected the right dates.','tweet-old-post'),'error');
			}
			$users = $this->getUsers();
			foreach($returnedPost as $post){
					$oknet = false;
					foreach($users as $u){
						if($u['service'] == $ntk){
							$oknet = true;
							break;
						}
					}
					if(!$oknet) return false;
					$finalTweet = $this->generateTweetFromPost($post,$ntk);
				 	$this->tweetPost( $finalTweet, $ntk, $post );
                    $this->setAlreadyTweetedPosts($ntk, $post->ID);
			}
			if ($byID===false) {
				$this->scheduleTweet($ntk);
			}


			$this->deleteNetworkLock($ntk);
		}

		public function scheduleTweet($ntk){
			$time = $this->getNextTweetTime( $ntk );

			if($time != 0 && $time > $this->getTime()){
				if(wp_next_scheduled( $ntk.'roptweetcron',array($ntk) ) === false) {
					wp_schedule_single_event( $time, $ntk . 'roptweetcron', array( $ntk ) );
				}
			}else{
				self::addNotice(__("Invalid next schedule: ",'tweet-old-post').date (  'M j, Y @ G:i',$time),'error');
			}
		}
		public function getAvailableNetworks(){
			$networks = array();
			$users = is_array($this->users) ? $this->users : array();
			foreach($users  as $u){
				if(!in_array($u['service'],$networks))
					$networks[] = $u['service'];
			}

			return $networks;
		}

		public function getAllNetworks($all = false){
			global $cwp_rop_all_networks;
			if(empty($cwp_rop_all_networks)) return array();

			return ($all) ? $cwp_rop_all_networks : array_keys($cwp_rop_all_networks);
		}
		public function findInString($where,$what) {
			if (!is_string($where)) {
				return false;
			}
			else
				return strpos($where,$what);
		}

		public function getNotice() {
			if(!is_admin()) return false;
			$notice = get_option('rop_notice_active');
			if(!is_array($notice)) $notice = array();

			foreach($notice as $k=>$n){
				$notice[$k]['message'] = strip_tags($n['message']);
			}
            echo json_encode($notice);
			die();
		}

		public function tweetNow() {
			if(!is_admin()) return false;
			$networks = $this->getAvailableNetworks();
			foreach($networks as $net){

				$this->tweetOldPost($net,get_option('top_lastID'));
			}
			die();
		}

		public function viewSampleTweet()
		{

			if(!is_admin()) return false;
			$returnedTweets = $this->getTweetsFromDB();

			$messages = array();
			$networks = $this->getAvailableNetworks();

			if(count($returnedTweets) == 0) {
				foreach($networks as $net){
					$messages[$net] = __("No posts to share",'tweet-old-post');
				}
				$networks = array();
			}
			foreach($networks as $n) {

				$finalTweetsPreview = $this->generateTweetFromPost($returnedTweets[0],$n);
				if (is_array($finalTweetsPreview)){
					$finalTweetsPreview = $finalTweetsPreview['message']." ".$finalTweetsPreview['link'];
				}
				$messages[$n] = $finalTweetsPreview;
			}
			if(isset($returnedTweets[0]))
				update_option( 'top_lastID', $returnedTweets[0]->ID);

			foreach($networks as $n) {
                $image      = $this->getImageForPost($n, $returnedTweets[0]->ID);
                if(!empty($image)){
                    $messages[$n] =  $image.$messages[$n];
                }
			}

			echo json_encode($messages);


			die(); // required
		}

        // Added by Ash/Upwork
        function sortPosts($all){
            uasort($all, array($this, "sortPostsByTimeDesc"));
            return $all;
        }

        function sortPostsByTimeDesc($x, $y){
            if($x["time"] == $y["time"]) return 0;

            return $x["time"] < $y["time"] ? -1 : 1;
        }

        function getImageForPost($n, $postID){
            if (ROP_IS_TEST || (CWP_TOP_PRO && $this->isPostWithImageEnabled($n))) {
                if(defined('ROP_PRO_VERSION')){
                    global $CWP_TOP_Core_PRO;
                    $image = $CWP_TOP_Core_PRO->getPostImage($postID, $n);

                }else {
                    if ( has_post_thumbnail($postID) ) :

                        $image_array = wp_get_attachment_image_src( get_post_thumbnail_id($postID) );

                        $image       = $image_array[0];
                    else :
                        $post  = get_post($postID);
                        $image = '';
                        ob_start();
                        ob_end_clean();
                        $output = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches );
                        if(isset($matches [1] [0]))
                            $image = $matches [1] [0];

                    endif;
                }

                $top_opt_saved_images   = get_option("top_opt_saved_images");
                if($top_opt_saved_images && is_array($top_opt_saved_images) && in_array($postID, $top_opt_saved_images)){
                    $imageID            = get_post_meta($postID, "top_opt_saved_post_image_" . $n, true);
                    if($imageID){
                        $image          = wp_get_attachment_url($imageID);
                    }
                }

                if(!empty($image)){
                    return '<img class="top_preview" src="'.$image.'"/>';
                }
            }
            return null;
        }

        function getFutureTime($network_name, $time, $array){
            $firstPostTime  = get_transient('top_firstpost_time');
            if($firstPostTime !== false){
                delete_transient('top_firstpost_time');
                return $firstPostTime;
            }

            if(array_key_exists("time", $array)){
                $time       = $array["time"];
            }else{
                $time       = CWP_TOP_Core::getNextTweetTime($network_name, $time ? $time : $this->getTime());
            }
            return $time;
        }
        // Added by Ash/Upwork

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

		public function getStrLen($string) {
            // multibyte string support for multibyte strings: DO NOT REMOVE
			if (function_exists("mb_strlen"))
				return mb_strlen($string);
			else
				return strlen($string);
		}

		public function ropSubstr($string,$nr1,$nr2	) {
            // multibyte string support for multibyte strings: DO NOT REMOVE
            if (function_exists("mb_substr")) {
                return mb_substr($string, $nr1,$nr2);
            } else {
                return substr($string, $nr1, $nr2);
            }
		}

		/**
		 * Author Daniel Brown (contact info at http://ymb.tc/contact)
		 * Includes hashtag in $tweet_content if possible
		 * to save space, avoid redundancy, and allow more hashtags
		 * @param  [string] $tweetContent The regular text in the Tweet
		 * @param  [string] $hashtag The hashtag to include in $tweetContent, if possible
		 * @return [mixed]  The new $tweetContent or FALSE if the $tweetContent doesn't contain the hashtag
		 */

		public function tweetContentHashtag ($tweetContent, $hashtag) {
			$location = stripos($tweetContent, ' ' . $hashtag . ' ');
			if ( $location !== false ) $location++; // the actual # location will be past the space
			elseif ( stripos($tweetContent, $hashtag . ' ') === 0 ) { // see if the hashtag is at the beginning
				$location = 0;
			}
			elseif ( stripos($tweetContent, ' ' . $hashtag) !== FALSE && stripos($tweetContent, ' ' . $hashtag) + strlen(' ' . $hashtag) == strlen($tweetContent) ) { // see if the hashtag is at the end
				$location = stripos($tweetContent, ' ' . $hashtag) + 1;
			}
			if ( $location !== false ) {
				return substr_replace($tweetContent, '#', $location, 0);
			}
			else return false;
		}

		/**
		 * Generates the tweet based on the user settings
		 * @param  [type] $postQuery Returned post from database
		 * @return [type]            Generated Tweet
		 */

		public function generateTweetFromPost($postQuery, $network, $fromManageQueue=false)
		{

			$format_fields = $this->getFormatFields();
			$tweetedPosts 					= get_option("top_opt_already_tweeted_posts");
			$formats  = get_option('top_opt_post_formats');
			$tweet_content               = isset($formats[$network."_"."top_opt_tweet_type"]) ? $formats[$network."_"."top_opt_tweet_type"] : get_option( 'top_opt_tweet_type' );
			$tweet_content_custom_field  = isset($formats[$network."_"."top_opt_tweet_type_custom_field"]) ? $formats[$network."_"."top_opt_tweet_type_custom_field"] : get_option( 'top_opt_tweet_type_custom_field' );
			$additional_text             = isset($formats[$network."_"."top_opt_add_text"]) ? $formats[$network."_"."top_opt_add_text"] : get_option( 'top_opt_tweet_type_custom_field' );
			$additional_text_at          = isset($formats[$network."_"."top_opt_add_text_at"]) ? $formats[$network."_"."top_opt_add_text_at"] : get_option( 'top_opt_add_text_at' );
			$max_length         = isset($formats[$network."_"."top_opt_tweet_length"]) ? $formats[$network."_"."top_opt_tweet_length"] : $format_fields[$network]['top_opt_tweet_length']['default_value'];
			$include_link                = isset($formats[$network."_"."top_opt_include_link"]) ? $formats[$network."_"."top_opt_include_link"] : get_option( 'top_opt_include_link' );  get_option( 'top_opt_include_link' );
			$fetch_url_from_custom_field =  isset($formats[$network."_"."top_opt_custom_url_option"]) ? $formats[$network."_"."top_opt_custom_url_option"] : get_option( 'top_opt_custom_url_option' );
			$custom_field_url            =  isset($formats[$network."_"."top_opt_custom_url_field"]) ? $formats[$network."_"."top_opt_custom_url_field"] : get_option( 'top_opt_custom_url_field' );  get_option( 'top_opt_custom_url_field' );
			$use_url_shortner            =  isset($formats[$network."_"."top_opt_use_url_shortner"]) ? $formats[$network."_"."top_opt_use_url_shortner"] : get_option( 'top_opt_use_url_shortner' );
			$url_shortner_service        = isset($formats[$network."_"."top_opt_url_shortner"]) ? $formats[$network."_"."top_opt_url_shortner"] : get_option( 'top_opt_url_shortner' );
			$hashtags                    = isset($formats[$network."_"."top_opt_custom_hashtag_option"]) ? $formats[$network."_"."top_opt_custom_hashtag_option"] : get_option( 'top_opt_custom_hashtag_option' );
			$common_hashtags             = isset($formats[$network."_"."top_opt_hashtags"]) ? $formats[$network."_"."top_opt_hashtags"] : get_option( 'top_opt_hashtags' );
			$maximum_hashtag_length      = isset($formats[$network."_"."top_opt_hashtag_length"]) ? $formats[$network."_"."top_opt_hashtag_length"] : get_option( 'top_opt_hashtag_length' );
			$hashtag_custom_field        = isset($formats[$network."_"."top_opt_custom_hashtag_field"]) ? $formats[$network."_"."top_opt_custom_hashtag_field"] : get_option( 'top_opt_custom_hashtag_field' );
			$post_with_image             =  isset($formats[$network."_". 'top_opt_post_with_image']) ? $formats[$network."_". 'top_opt_post_with_image'] : get_option( 'top_opt_bitly_user' );
			$ga_tracking                 = get_option( 'top_opt_ga_tracking' );
			$additionalTextBeginning     = "";
			$additionalTextEnd           = "";

			// If the user set to not use hashtags, set it to empty variable.
			if ( $hashtags == 'nohashtag' ) {
				$newHashtags = "";
			}
			// Generate the tweet content.
			switch ( $tweet_content ) {
				case 'title':
					$tweetContent = $postQuery->post_title;
					break;
				case 'body':
					$tweetContent = get_post_field( 'post_content', $postQuery->ID );
					break;
				case 'titlenbody':
					$tweetContent = $postQuery->post_title . " " . get_post_field( 'post_content', $postQuery->ID );
					break;
				case 'custom-field':
					$tweetContent = get_post_meta( $postQuery->ID, $tweet_content_custom_field, true );
					break;
				default:
					$tweetContent = "";
					break;
			}
			// Trim new empty lines.
			if(!is_string($tweetContent)) $tweetContent = '';
			$tweetContent = strip_tags( html_entity_decode( $tweetContent,ENT_QUOTES ) );
			//$tweetContent = esc_html($tweetContent);
			//$tweetContent = esc_html($tweetContent);
			//$tweetContent = trim(preg_replace('/\s+/', ' ', $tweetContent));
			// Remove html entinies.
			//$tweetContent = preg_replace("/&#?[a-z0-9]+;/i","", $tweetContent);
			// Strip all shortcodes from content.
			$tweetContent   = strip_shortcodes( $tweetContent );
			$fTweet         = array();
			$post_url = get_permalink( $postQuery->ID );
			// Generate the post link.
			if ( $include_link == 'true' ) {
				if ( $fetch_url_from_custom_field == 'on' ) {
					//$post_url = preg_replace('/https?:\/\/[^\s"<>]+/', '$0', );
					preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', get_post_meta( $postQuery->ID, $custom_field_url, true ), $match);
					if(isset($match[0])){
						if(isset($match[0][0]))
							$post_url = $match[0][0];
					}
				} else {
					$post_url = "" . get_permalink( $postQuery->ID );
				}
				if ( $post_url == "" ) {
					$post_url = "" . get_permalink( $postQuery->ID );
				}
				if ( $ga_tracking == "on" ) {
					$param    = 'utm_source=ReviveOldPost&utm_medium=social&utm_campaign=ReviveOldPost';
					$post_url = rtrim( $post_url );
					if ( strpos( $post_url, "?" ) === false ) {
						$post_url .= '?' . $param;
					} else {
						$post_url .= '&' . $param;
					}
				}
				if ( $use_url_shortner == 'on' ) {
                    if ($fromManageQueue) {
                        // Added by Ash/Upwork so that the pro version can use this to generate the shortened url when required
                        update_post_meta($postQuery->ID, "rop_post_url_" . $network, $post_url);
                        // Added by Ash/Upwork
                    }
                    // $fromManageQueue Added by Ash/Upwork
                    $post_url = "" . self::shortenURL( $post_url, $url_shortner_service, $postQuery->ID, $formats, $network, $fromManageQueue );
                    // $fromManageQueue Added by Ash/Upwork
				}
				if ( $post_url == "" ) {
					$post_url = "" . get_permalink( $postQuery->ID );
				}
				$post_url = $post_url . "";
			} else {
				$post_url = "";
			}
			// Generate the hashtags
			$newHashtags = "";
			if ( $hashtags != 'nohashtag' ) {
				switch ( $hashtags ) {
					case 'common':
						$newHashtags = $common_hashtags;
						break;
					case 'categories':
						if ( $postQuery->post_type == "post" ) {
							$postCategories = get_the_category( $postQuery->ID );
							foreach ( $postCategories as $category ) {
								$thisHashtag = $category->slug;
								if ( $this->tweetContentHashtag($tweetContent, $thisHashtag) !== false ) { // if the hashtag exists in $tweetContent
									$tweetContent = $this->tweetContentHashtag($tweetContent, $thisHashtag); // simply add a # there
									$maximum_hashtag_length--; // subtract 1 for the # we added to $tweetContent
								}
								elseif ( strlen( $thisHashtag . $newHashtags ) <= $maximum_hashtag_length || $maximum_hashtag_length == 0 ) {
									$newHashtags = $newHashtags . " #" . preg_replace( '/-/', '', strtolower( $thisHashtag ) );
								}
							}
						} else {
							if ( CWP_TOP_PRO ) {
								global $CWP_TOP_Core_PRO;
								$newHashtags = $CWP_TOP_Core_PRO->topProGetCustomCategories( $postQuery, $maximum_hashtag_length );
							}
						}
						break;
					case 'tags':
						$postTags = wp_get_post_tags( $postQuery->ID );
						foreach ( $postTags as $postTag ) {
							$thisHashtag = $postTag->slug;
							if ( $this->tweetContentHashtag($tweetContent, $thisHashtag) !== false ) { // if the hashtag exists in $tweetContent
								$tweetContent = $this->tweetContentHashtag($tweetContent, $thisHashtag); // simply add a # there
								$maximum_hashtag_length--; // subtract 1 for the # we added to $tweetContent
							}
							elseif ( strlen( $thisHashtag . $newHashtags ) <= $maximum_hashtag_length || $maximum_hashtag_length == 0 ) {
								$newHashtags = $newHashtags . " #" . preg_replace( '/-/', '', strtolower( $thisHashtag ) );
							}
						}
						break;
					case 'custom':
						if(empty($hashtag_custom_field)){
							self::addNotice("You need to add a custom field name in order to fetch the hashtags. Please set it from Post Format > $network > Hashtag Custom Field ",'error');
							break;
						}
						$newHashtags = get_post_meta( $postQuery->ID, $hashtag_custom_field, true );
						if($maximum_hashtag_length != 0){
							if(strlen(  $newHashtags ) <= $maximum_hashtag_length)
							{
								$newHashtags = $this->ropSubstr($newHashtags,0,$maximum_hashtag_length);
							}
						}
						break;
					default:
						break;
				}
			}
			// Generate the additional text
			if ( $additional_text_at == 'beginning' ) {
				$additionalTextBeginning = $additional_text . " ";
			}
			if ( $additional_text_at == 'end' ) {
				$additionalTextEnd = " " . $additional_text;
			}

			$fTweet['link'] = $post_url;
			$adTextELength = 0;
			if(is_string($additionalTextEnd)){
				$adTextELength = $this->getStrLen($additionalTextEnd);
			}
			$adTextBLength = 0;
			if(is_string($additionalTextBeginning)){
				$adTextBLength = $this->getStrLen($additionalTextBeginning);
			}
			$hashLength = 0;
			if(is_string($newHashtags) && $network != 'tumblr'){
				$hashLength = $this->getStrLen($newHashtags);
			}
			$finalTweetSize = $max_length - $hashLength - $adTextELength - $adTextBLength ;
			if($network == 'twitter' && !empty($fTweet['link']) ){
				$finalTweetSize = $finalTweetSize - 25;


			}
			if($network == 'twitter' && CWP_TOP_PRO && $this->isPostWithImageEnabled($network)){
				$finalTweetSize = $finalTweetSize - 25;
			}
			$tweetContent = $this->ropSubstr( $tweetContent,0,$finalTweetSize);

			if($network == 'twitter'){
				if(!empty($fTweet['link'])) $fTweet['link'] = " ".$fTweet['link']." ";
				$finalTweet = $additionalTextBeginning . $tweetContent  .$fTweet['link'].$newHashtags . $additionalTextEnd;
				$fTweet['link'] = '';
				$finalTweet =  preg_replace('/\s+/', ' ', trim( $finalTweet));
			}else{
				if($network === 'tumblr') {
					$fTweet['tags']  = implode(",",array_filter(explode("#", $newHashtags)));
					$finalTweet = $additionalTextBeginning . $tweetContent . $additionalTextEnd;
				}else{
					$finalTweet = $additionalTextBeginning . $tweetContent .$newHashtags . $additionalTextEnd;
				}
			}

            // Added by Ash/Upwork
            $top_opt_saved_posts    = get_option("top_opt_saved_posts");
            if($top_opt_saved_posts && is_array($top_opt_saved_posts) && in_array($postQuery->ID, $top_opt_saved_posts)){
                $newContent         = get_post_meta($postQuery->ID, "top_opt_saved_post_content_" . $network, true);
                if($newContent){
                    $finalTweet     = $newContent;
                }
            }
            // Added by Ash/Upwork

			$fTweet['message'] =  $finalTweet ;

			return $fTweet;
		}

		/**
		 * Tweets the returned post from generateTweetFromPost()
		 * @param  [type] $finalTweet Generated tweet
		 */

		public function tweetPost($finalTweet,$network = 'twitter',$post)
		{
            // Added by Ash/Upwork
            if(ROP_IS_TEST){
                self::addLog("Not posting because ROP_IS_TEST is set");
                return;
            }
            // Added by Ash/Upwork

			$users = $this->getUsers();
			foreach ($users as $user) {
				if($network == $user['service']  ){

					switch ($user['service']) {
						case 'twitter':
							// Create a new twitter connection using the stored user credentials.
							$connection = new RopTwitterOAuth($this->consumer, $this->consumerSecret, $user['oauth_token'], $user['oauth_token_secret']);
							$args = array('status' =>  $finalTweet['message'].$finalTweet['link']);
							$response = false;
							//self::addNotice(strlen($args["status"]),"error");
							if($this->isPostWithImageEnabled($network) && CWP_TOP_PRO) {
								global $CWP_TOP_Core_PRO;


								if(defined('ROP_IMAGE_CHECK')){
									$args = $CWP_TOP_Core_PRO->topProImage( $connection, $finalTweet, $post->ID, $network );
                                    // Added by Ash/Upwork: !empty($args['media[]'])
									if ( isset( $args['media[]'] ) && !empty($args['media[]'])) {
										$image = array("media"=>$args['media[]']);
										$response = $connection->upload( 'https://upload.twitter.com/1.1/media/upload.json', $image );
										unset($args['media[]']);
										$args["media_ids"] = $response->media_id_string;
										$response = $connection->post( 'statuses/update', $args );
									} else {
										$response = $connection->post( 'statuses/update', $args );
									}
								}else{
									$response = $CWP_TOP_Core_PRO->topProImage( $connection, $finalTweet['message']. " " .$finalTweet['link'], $post->ID, $network );
								}
							}else{

								$response = $connection->post('statuses/update',$args);
							}

							if($response !== false){
								$status = '';
								if(!is_object($response))
									$status = json_decode($response);
								if(!is_object($status)){

								//	self::addNotice("Error for post ".$post->post_title." when sending to Twitter: Invalid response - ".$response,'error');

								}
								else{

										if($status->errors[0]->code != 200) {
												//	self::addNotice("Error for post ".$post->post_title." when sending to Twitter: ".$status->errors[0]->message,'error');


										}
										else{

										}
								}
								if($connection->http_code == 200 ){
												self::addNotice("Post ".$post->post_title." has been successfully sent to Twitter.",'notice');

								}
							}
						break;
						case 'facebook':

							$args =  array(

								'body' => array( 'message' => $finalTweet['message'],'link' => $finalTweet['link']),
								'timeout'=>20

							);
							if($this->isPostWithImageEnabled($network) && CWP_TOP_PRO){
								global $CWP_TOP_Core_PRO;
								if(defined('ROP_IMAGE_CHECK'))
									$args = $CWP_TOP_Core_PRO->topProImage($connection, $finalTweet, $post->ID,$network);
							}

							$pp=wp_remote_post("https://graph.facebook.com/".ROP_TOP_FB_API_VERSION."/".$user['id']."/feed?access_token=".$user['oauth_token'],$args);
							if(is_wp_error( $pp )){
								self::addNotice(__("Error for posting on facebook for:",'tweet-old-post')." ".$post->post_title."".$pp->get_error_message(),'error' );

							}else{
								if($pp['response']['code'] == 200){

									self::addNotice(sprintf(__("Post %s has been successfully sent to facebook",'tweet-old-post'), $post->post_title),'notice');
								}else{
									$fb_error = "";
									$pp = json_decode($pp["body"]);

									if(isset($pp->error)){
										$fb_error = $pp->error->message;
									}
									self::addNotice(__("Error for facebook share on post ",'tweet-old-post'). $post->post_title." ".$fb_error." @".$user['oauth_user_details']->name,'error');


								}

							}

							break;

						case 'linkedin':

							$lk_message = str_replace("&", "&amp;",$finalTweet['message']);
							$sharedLink = str_replace("&", "&amp;",$finalTweet['link']);
							$content_xml = "";
							$visibility="anyone";
							$content_xml.="<content><title>".$lk_message."</title><submitted-url>".$sharedLink."</submitted-url></content>";
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
								self::addNotice(__("Your host does not support CURL",'tweet-old-post'),'error');
							$ch = curl_init();
							curl_setopt($ch, CURLOPT_URL,$url);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
							curl_setopt($ch, CURLOPT_TIMEOUT, 10);
							curl_setopt($ch, CURLOPT_POST, true);
							curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
							curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

							$data = curl_exec($ch);


							if(curl_errno($ch))
								{

									self::addNotice("Curl error: ". curl_error($ch),'error');
								}
							else{
								self::addNotice("Post ". $post->post_title." has been successfully sent to LinkedIN.",'notice');
								curl_close($ch);
							}
							break;
						default:
							if(CWP_TOP_PRO){
								global $CWP_TOP_Core_PRO;
								if(method_exists($CWP_TOP_Core_PRO,"tweetPostPro")){
									$CWP_TOP_Core_PRO->tweetPostPro($finalTweet,$network ,$post,$user);
								}

							}

							break;



					}

				}

			}
		}
        public function getRestrictedShowFields(){

	        global $cwp_rop_restricted_show;
	        return $cwp_rop_restricted_show;
        }
		public function system_info(){

			global $wpdb;
			$restricted = $this->getRestrictedShowFields();

			if(CWP_TOP_PRO){

				$pro  = get_plugin_data(ROPPROPLUGINPATH."/tweet-old-post-pro.php");
			}
			$lite  = get_plugin_data(ROPPLUGINPATH."/tweet-old-post.php");
			if ( get_bloginfo( 'version' ) < '3.4' ) {
				$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
				$theme      = $theme_data['Name'] . ' ' . $theme_data['Version'];
			} else {
				$theme_data = wp_get_theme();
				$theme      = $theme_data->Name . ' ' . $theme_data->Version;
			}

			// Try to identifty the hosting provider
			$host = false;
			if( defined( 'WPE_APIKEY' ) ) {
				$host = 'WP Engine';
			} elseif( defined( 'PAGELYBIN' ) ) {
				$host = 'Pagely';
			}

			?>
				<div class="wrap">
					<h2><?php _e( 'System Information', 'tweet-old-post'); ?></h2><br/>
					<form action="" method="post" dir="ltr">
						<textarea readonly="readonly" onclick="this.focus();this.select()" cols="100" id="system-info-textarea" name="cwp-top-sysinfo" rows="20" title="<?php _e( 'To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'tweet-old-post' ); ?>">

## Please include this information when posting support requests ##

## BEGIN ROP CONFIGS ##

<?php
				$options = $this->getPostFormatValues();
				$cwp_top_global_schedule = $this->getSchedule();
		         $cwp_rop_all_networks = $this->getFormatFields();
			echo "## ROP POST FORMAT";
				foreach($cwp_rop_all_networks as $n=>$d){
					echo "\n \n \n ##".$n." \n \n \n";




					foreach($d as $fname => $f){
						if(!in_array($fname,$restricted))
							echo $f['name']. " : ". $options[$n."_".$f['option']]." \n";
					}

				}
			?>

## END ROP CONFIGS ##


## Begin Remote Data

	Beta User: <?php  echo $this->getBetaUserStatus(); ?><?php echo "\n"; ?>
	Remote Check: <?php  echo $this->getRemoteCheck(); ?><?php echo "\n"; ?>

## End Remote Data



## Begin CRON Info

CRON Active:              <?php echo (defined("DISABLE_WP_CRON")  ? ((DISABLE_WP_CRON) ? "no" : "yes")  : "yes" ); ?><?php echo "\n"; ?>
Alternate WP Cron:        <?php echo defined("ALTERNATE_WP_CRON") ? ((ALTERNATE_WP_CRON) ? "yes" : "no" ) : "no"; ?><?php echo "\n";

			?>
Time now: <?php echo date ( 'M j, Y @ G:i',time()); ?> <?php echo "\n"; ?>
ROP Crons:
<?php
			$all = $this->getAllNetworks();
			foreach($all as $nn ){
				if(wp_next_scheduled($nn.'roptweetcron',array($nn)) === false) continue;
				echo date (  'M j, Y @ G:i', wp_next_scheduled($nn.'roptweetcron',array($nn)) );
			}

			?>

## End Cron Info

##Begin General Settings:

<?php
			$cwp_top_fields = $this->getGeneralFields();
			foreach($cwp_top_fields as $general_field){
				echo $general_field['name']. " : ";
				if(is_array(get_option($general_field['option'])))
					echo implode(",",get_option($general_field['option']))." \n" ;
				else
					echo get_option($general_field['option'])." \n";
			}
			?>

##End General Settings


<?php
			if(CWP_TOP_PRO):?>
##Begin Custom schedule settings:

<?php  foreach($all as $a) {

				if( $cwp_top_global_schedule[$a.'_schedule_type_selected'] == 'each')
				{
					echo strtoupper($a)." post on every ".$cwp_top_global_schedule[$a.'_top_opt_interval']." hours"." \n" ;
				}else{
					echo strtoupper($a)." post each ".$cwp_top_global_schedule[$a.'_top_opt_interval']['days']." days of the week at: "." \n" ;
					foreach($cwp_top_global_schedule[$a.'_top_opt_interval']['times'] as $time){
						echo ''.$time['hour']." : ".$time['minute']." \n  " ;


					}
				}
								?>
<?php } ?>

##End Custom schedule settings
<?php			endif;
			?>

### Begin System Info ###


Multisite:                <?php echo is_multisite() ? 'Yes' . "\n" : 'No' . "\n" ?>

SITE_URL:                 <?php echo site_url() . "\n"; ?>
HOME_URL:                 <?php echo home_url() . "\n"; ?>
<?php if(CWP_TOP_PRO): ?>
ROP PRO Version:              <?php echo $pro['Version'] . "\n"; ?>
<?php endif; ?>
ROP Lite Version:              <?php echo $lite['Version'] . "\n"; ?>
WordPress Version:        <?php echo get_bloginfo( 'version' ) . "\n"; ?>
Permalink Structure:      <?php echo get_option( 'permalink_structure' ) . "\n"; ?>
Active Theme:             <?php echo $theme . "\n"; ?>
<?php if( $host ) : ?>
	Host:                     <?php echo $host . "\n"; ?>
<?php endif; ?>
PHP Version:              <?php echo PHP_VERSION . "\n"; ?>
MySQL Version:            <?php echo mysqli_get_client_info() . "\n"; ?>
Web Server Info:          <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>
WordPress Memory Limit:   <?php echo  WP_MEMORY_LIMIT  ; ?><?php echo "\n"; ?>
PHP Safe Mode:            <?php echo ini_get( 'safe_mode' ) ? "Yes" : "No\n"; ?>
PHP Memory Limit:         <?php echo ini_get( 'memory_limit' ) . "\n"; ?>
PHP Upload Max Size:      <?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>
PHP Post Max Size:        <?php echo ini_get( 'post_max_size' ) . "\n"; ?>
PHP Upload Max Filesize:  <?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>
PHP Time Limit:           <?php echo ini_get( 'max_execution_time' ) . "\n"; ?>
PHP Max Input Vars:       <?php echo ini_get( 'max_input_vars' ) . "\n"; ?>
PHP Arg Separator:        <?php echo ini_get( 'arg_separator.output' ) . "\n"; ?>
PHP Allow URL File Open:  <?php echo (ini_get( 'allow_url_fopen' ) ? "Yes" : "No" ). "\n"; ?>
WP_DEBUG:                 <?php echo defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n" ?>
WP Table Prefix:          <?php echo "Length: ". strlen( $wpdb->prefix ); echo " Status:"; if ( strlen( $wpdb->prefix )>16 ) {echo " ERROR: Too Long";} else {echo " Acceptable";} echo "\n"; ?>
Show On Front:            <?php echo get_option( 'show_on_front' ) . "\n" ?>
Page On Front:            <?php $id = get_option( 'page_on_front' ); echo get_the_title( $id ) . ' (#' . $id . ')' . "\n" ?>
Page For Posts:           <?php $id = get_option( 'page_for_posts' ); echo get_the_title( $id ) . ' (#' . $id . ')' . "\n" ?>
Session:                  <?php echo isset( $_SESSION ) ? 'Enabled' : 'Disabled'; ?><?php echo "\n"; ?>
Session Name:             <?php echo esc_html( ini_get( 'session.name' ) ); ?><?php echo "\n"; ?>
Cookie Path:              <?php echo esc_html( ini_get( 'session.cookie_path' ) ); ?><?php echo "\n"; ?>
Save Path:                <?php echo esc_html( ini_get( 'session.save_path' ) ); ?><?php echo "\n"; ?>
Use Cookies:              <?php echo ini_get( 'session.use_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>
Use Only Cookies:         <?php echo ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>
DISPLAY ERRORS:           <?php echo ( ini_get( 'display_errors' ) ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A'; ?><?php echo "\n"; ?>
FSOCKOPEN:                <?php echo ( function_exists( 'fsockopen' ) ) ? 'Your server supports fsockopen.' : 'Your server does not support fsockopen.'; ?><?php echo "\n"; ?>
cURL:                     <?php echo ( function_exists( 'curl_init' ) ) ? 'Your server supports cURL.' : 'Your server does not support cURL.'; ?><?php echo "\n"; ?>
SOAP Client:              <?php echo ( class_exists( 'SoapClient' ) ) ? 'Your server has the SOAP Client enabled.' : 'Your server does not have the SOAP Client enabled.'; ?><?php echo "\n"; ?>

ACTIVE PLUGINS:


<?php
$plugins = get_plugins();
$active_plugins = get_option( 'active_plugins', array() );

foreach ( $plugins as $plugin_path => $plugin ) {
	// If the plugin isn't active, don't show it.
	if ( ! in_array( $plugin_path, $active_plugins ) )
		continue;

	echo $plugin['Name'] . ': ' . $plugin['Version'] ."\n";
}

if ( is_multisite() ) :
	?>

	NETWORK ACTIVE PLUGINS:

	<?php
	$plugins = wp_get_active_network_plugins();
	$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

	foreach ( $plugins as $plugin_path ) {
		$plugin_base = plugin_basename( $plugin_path );

		// If the plugin isn't active, don't show it.
		if ( ! array_key_exists( $plugin_base, $active_plugins ) )
			continue;

		$plugin = get_plugin_data( $plugin_path );

		echo "\t"."\t"."\t"."\t".$plugin['Name'] . ' :' . $plugin['Version'] ."\n";
	}

endif;
?>



### End System Info ###

##Begin Log info

<?php       $logs = get_option('rop_notice_active');
			if(!is_array($logs)) $logs = array();
			foreach($logs as $log){
				echo strtoupper($log['type']). " @ ".$log['time']. ' - '. $log['message']." \n ";
			}
			?>
<?php ?>

#End log info

##Begin user info

<?php
			$users = $this->getUsers();
			foreach($all as $a ){
				if(!isset($$a)) $$a = 0;
				foreach($users  as $us){
					if($us['service'] == $a) $$a ++;

				}

			}
			foreach($all as $a){
				echo  strtoupper($a)." accounts - ".$$a." \n";

			}

			?>

##End user info

</textarea>
						<p class="submit">
							<input type="hidden" name="cwp-action" value="download_sysinfo" />
							<?php submit_button( __('Download System Info File','tweet-old-post'), 'primary', 'cwp-download-sysinfo', false ); ?>
						</p>
					</form>
					</div>
				</div>
			<?php
		}
		/*public function tweetPostwithImage($finalTweet, $id,$ntk =  'twitter')
		{

			$k=1;
			$tw=0;
			$nrOfUsers = count($this->users);
			$time = get_option("top_last_tweets");

			foreach ($this->users as $user) {

				if($ntk == $user['service']  ){
					if(isset($time[$ntk])){
						if(time() - $time[$ntk] < 60)
							return false;

					}

					$time[$ntk] = time();
					update_option("top_last_tweets",$time);
				switch ($user['service']) {
					case 'twitter':
						// Create a new twitter connection using the stored user credentials.
						$connection = new RopTwitterOAuth($this->consumer, $this->consumerSecret, $user['oauth_token'], $user['oauth_token_secret']);
						// Post the new tweet
						if (CWP_TOP_PRO){
							global $CWP_TOP_Core_PRO;
							$status = $CWP_TOP_Core_PRO->topProImage($connection, $finalTweet['message'], $id);
						}
						//var_dump($status);
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

						$lk_message = str_replace("&", "&amp;",$finalTweet['message']);
						$sharedLink = str_replace("&", "&amp;",$finalTweet['link']);
						$content_xml = "";
						$visibility="anyone";
						$content_xml.="<content><title>".$lk_message."</title><submitted-url>".$sharedLink."</submitted-url></content>";
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
						if (CWP_TOP_PRO) {
							global $$CWP_TOP_Core_PRO;
							$status = $CWP_TOP_Core_PRO->topProImage( $connection, $finalTweet['message'], $id );
						}
						if ($nrOfUsers == $k)
							return $status;
						else
							$k++;


				}
				}
				//sleep(100);
			}
		}*/

		// Generates the tweet date range based on the user input.
        // Corrected by Ash/Upwork: added a few efficiencies
		public function getTweetPostDateRange()
		{
			$max = intval(get_option('top_opt_max_age_limit'));

			if( !is_int($max) ) {
				self::addNotice(__("Incorect value for Maximum age of post to be eligible for sharing. Please check the value to be a number greater or equal than 0 ",'tweet-old-post'),'error');
				return false;
			}

			$min = intval(get_option('top_opt_age_limit'));

			if(!is_int($min)  ){
				self::addNotice(__("Incorect value for Minimum age of post to be eligible for sharing. Please check the value to be a number greater  or equal than 0 ",'tweet-old-post'),'error');
				return false;

			}
			if($max > 0 && $min > 0 && $max < $min){
				self::addNotice(__("Maximum age of post to be eligible for sharing must be greater than Minimum age of post to be eligible for sharing. Please check the value to be a number greater  or equal than 0 ",'tweet-old-post'),'error');
				return false;

			}

			$dateQuery = array();
            if($max > 0){
                $maxLimit       = time() - $max*24*60*60;
                $dateQuery['before'] = date("Y-m-d H:i:s", $maxLimit);
            }

            if($min > 0){
			    $minLimit       = time() - $min*24*60*60;
                $dateQuery['after'] = date("Y-m-d H:i:s", $minLimit);
            }

            return $dateQuery;


		}

		// Gets the omited tweet categories

		public function getExcludedCategories()
		{
			$postQueryCategories = "";
			$postCategories = get_option('top_opt_omit_cats');

			if(!empty($postCategories) && is_array($postCategories)) {
				$postQueryCategories = implode(',',$postCategories);
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
				$postQueryPostTypes = implode(',',$top_opt_post_type);
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
				'display'	=> __("Custom Tweet User Interval", 'tweet-old-post')
			);

			return $schedules;
		}

		// Clears the custom Tweet Old Post cron job.
		public function clearScheduledTweets()
		{
			$all = $this->getAllNetworks();
			foreach($all as $n){
				wp_clear_scheduled_hook($n.'roptweetcron',array($n));
			}
		}

		// Deactivation hook
		public function deactivationHook()
		{
			$this->clearScheduledTweets();
			$this->deleteAllOptions();
			$this->remoteTrigger("off");
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
			$this->users = apply_filters("rop_users_filter",get_option('cwp_top_logged_in_users'));

			$this->pluginStatus = get_option('cwp_topnew_active_status');
			$this->intervalSet = get_option('top_opt_interval');

			self::$date_format = 'M j, Y @ G:i';
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
			$cnetwork = self::getCurrentNetwork();
			if(isset($_REQUEST['oauth_token'])  && $cnetwork == 'twitter') {

				if($_REQUEST['oauth_token'] == $this->cwp_top_oauth_token) {

					$twitter = new RopTwitterOAuth($this->consumer, $this->consumerSecret, $this->cwp_top_oauth_token, $this->cwp_top_oauth_token_secret );
					$access_token = $twitter->getAccessToken($_REQUEST['oauth_verifier']);
					$user_details = $twitter->get('account/verify_credentials');
					$user_details->status = array();

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

					header("Location: " . top_settings_url());
					exit;
				}
			}
			if(isset($_REQUEST['state']) && (get_option('top_fb_session_state') === $_REQUEST['state'])) {

				$token_url = "https://graph.facebook.com/".ROP_TOP_FB_API_VERSION."/oauth/access_token?"
				             . "client_id=" . get_option('cwp_top_app_id') . "&redirect_uri=" . top_settings_url()
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
				header("Location: " . top_settings_url().'&fbadd');
			}

			if (isset($_GET['code'])&&isset($_GET['state'])&&get_option('top_lk_session_state') == $_GET['state']) {

				$lk_auth_token = get_option('cwp_top_lk_app_id');
				$lk_auth_secret = get_option('cwp_top_lk_app_secret');
				$params = array('grant_type' => 'authorization_code',
				                'client_id' => $lk_auth_token,
				                'client_secret' => $lk_auth_secret,
				                'code' => $_GET['code'],
				                'redirect_uri' => top_settings_url(),
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



				header("Location: " . top_settings_url());
			}

			if(CWP_TOP_PRO){
				global $CWP_TOP_Core_PRO;
				if(method_exists($CWP_TOP_Core_PRO,"afterCheckPro")){
					$CWP_TOP_Core_PRO->afterCheckPro();
				}

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
					$url = 'https://www.linkedin.com/uas/oauth2/authorization?response_type=code&client_id='.get_option("cwp_top_lk_app_id").'&scope=w_share&state='.$top_session_state.'&redirect_uri='.top_settings_url();
					header("Location: " . $url);

					update_option('top_lk_session_state',$top_session_state);

				}

				if ($user['service'] === "facebook"&&$fb===0) {
					$top_session_state_fb = md5(uniqid(rand(), TRUE));
					$fb++;
					update_option('top_fb_session_state',$top_session_state_fb);
					$dialog_url = "https://www.facebook.com/".ROP_TOP_FB_API_VERSION."/dialog/oauth?client_id="
					              . get_option("cwp_top_app_id") . "&redirect_uri=" . top_settings_url() . "&state="
					              . $top_session_state_fb . "&scope=publish_actions,manage_pages,publish_pages,user_posts,user_photos";

					header("Location: " . $dialog_url);
				}
			}

		}

		// Adds pages
		public function displayPages()
		{

			if(!is_admin()) return false;
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
		public function adminNotice(){
			if(is_array($this->notices)){

				foreach($this->notices as $n){
					?>
					<div class="error">
       					 <p><?php _e( $n, 'tweet-old-post' ); ?></p>
   				 </div>
				<?php
				}
			}

		}
		public function checkVersion(){
			if(!defined("ROP_PRO_VERSION") && CWP_TOP_PRO) echo 'rop-not-version';

		}
		public function addPages()
		{

			if(!is_admin()) return false;
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
						echo top_settings_url();
					}


					break;
			}
			die(); // Required
		}
		public static function setCurrentNetwork($network){
			update_option("rop_current_network_oauth",$network);

		}
		public static function getCurrentNetwork(){
			$network = get_option("rop_current_network_oauth");
			if($network == false) return '';
			return $network;
		}
		// Adds new account
		public function addNewAccount()
		{

			if(!is_admin()) return false;
			if(!function_exists('curl_version')){

				self::addNotice(__("You need to have cURL library enabled in order to use our plugin! Please check it with your hosting company to enable this."),'tweet-old-post');
				return false;
			}
			global $cwp_top_settings;
			$social_network = $_POST['social_network'];
			self::setCurrentNetwork($social_network);
			$networks = $this->getAvailableNetworks();
			$allnetworks = $this->getAllNetworks(true);
			$response = array();

			if($allnetworks[$social_network] && !CWP_TOP_PRO){
				self::addNotice("You need to <a target='_blank' href='http://revive.social/plugins/revive-old-post/?utm_source=topplusacc&utm_medium=announce&utm_campaign=top&upgrade=true'>upgrade to the PRO version</a> in order to add a ".ucwords($social_network)." account, fellow pirate!",'error');

			}else if(in_array($social_network,$networks) && !CWP_TOP_PRO) {
				self::addNotice("You need to <a target='_blank' href='http://revive.social/plugins/revive-old-post/?utm_source=topplusacc&utm_medium=announce&utm_campaign=top&upgrade=true'>upgrade to the PRO version</a> in order to add more accounts, fellow pirate!",'error');


			}else{
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
								$response['url'] = $url;
								break;

							default:
								self::addNotice(__("Could not connect to Twitter!"),'tweet-old-post');

								break;
						}
						break;
					case 'facebook':
							if (empty($_POST['extra']['app_id'])){
								self::addNotice(__("Could not connect to Facebook! You need to add the App ID",'tweet-old-post'),'error');
							}else
							if (empty($_POST['extra']['app_secret'])){
								self::addNotice(__("Could not connect to Facebook! You need to add the App Secret",'tweet-old-post'),'error');

							}else{
								update_option('cwp_top_app_id', $_POST['extra']['app_id']);
								update_option('cwp_top_app_secret', $_POST['extra']['app_secret']);

								$top_session_state = md5(uniqid(rand(), TRUE));

								update_option('top_fb_session_state',$top_session_state);
								$dialog_url = "https://www.facebook.com/".ROP_TOP_FB_API_VERSION."/dialog/oauth?client_id="
								              . $_POST['extra']['app_id'] . "&redirect_uri=" . top_settings_url() . "&state="
	 							              . $top_session_state . "&scope=publish_actions,manage_pages,publish_pages,user_posts,user_photos";

								$response['url'] = $dialog_url;

							}

						break;
					default:
						if(CWP_TOP_PRO){
							global $CWP_TOP_Core_PRO;
							$CWP_TOP_Core_PRO->topProAddNewAccount();
						}
				}

			}

			echo json_encode($response);

			die(); // Required
		}

		// Adds more than one account
		public function addNewAccountPro()
		{

			if(!is_admin()) return false;
			if (CWP_TOP_PRO) {
				global $CWP_TOP_Core_PRO;
				$CWP_TOP_Core_PRO->topProAddNewAccount($_POST['social_network']);
			}
			else{
				update_option('cwp_topnew_notice',"You need to <a target='_blank' href='http://revive.sociahttp://revive.social/plugins/revive-old-post/?utm_source=topplusacc&utm_medium=announce&utm_campaign=top&upgrade=true'>upgrade to the PRO version</a> in order to add more accounts, fellow pirate!");
				echo "You need to <a target='_blank' href='http://revive.social/plugins/revive-old-post/?utm_source=topplusacc&utm_medium=announce&utm_campaign=top&upgrade=true'>upgrade to the PRO version</a> in order to add more accounts, fellow pirate!";

			}
			die(); // Required
		}

		// Gets the next tweet interval.
		public function getNextTweetInterval()
		{
			$timestamp = wp_next_scheduled( 'cwptoptweetcronnew' );

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
			if(!is_admin()) return false;
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

		function getUpperDays($day,$days){
			$tmp = array();
			foreach($days as $d){
				if($day <= $d){
					$tmp[] = $d;
				}
			}
			return $tmp;
		}
		public function getSchedule(){
			$db = get_option("cwp_top_global_schedule" ) ;
			if(!is_array($db)) $db = array();
			$networks = $this->getAllNetworks();
			foreach($networks as $network){
				if(!isset($db[$network.'_schedule_type_selected']))  $db[$network.'_schedule_type_selected'] = "each";
				if(!isset($db[$network.'_top_opt_interval']))  $db[$network.'_top_opt_interval'] = 8;

			}
			return $db;
		}
		function   getNextTweetTime($network, $nowTime=null){
			$time = 0;
			if(!CWP_TOP_PRO){
				 $time =  $this->getTime($nowTime) + ( floatval(get_option('top_opt_interval'))  * 3600 ) ;
				 if($time > $this->getTime($nowTime)) {
					 return $time;
				 }else{
					 return 0;
				 }
			}
			$cwp_top_global_schedule = $this->getSchedule();
			$type = $cwp_top_global_schedule[$network.'_schedule_type_selected'];
			if($type == 'each'){
				$time =  $this->getTime($nowTime) + floatval($cwp_top_global_schedule[$network.'_top_opt_interval']) * 3600;
				if($time > $this->getTime($nowTime)) {
					return $time;
				}else{
					return 0;
				}
			}else{
				if (date('N', $this->getTime($nowTime)) == 1){
					$start = strtotime("monday this week",$this->getTime($nowTime)) ;
				}else{
					$start = strtotime("last Monday",$this->getTime($nowTime)) ;
				}

				$days = explode(",",$cwp_top_global_schedule[$network.'_top_opt_interval']['days']);

				$times = $cwp_top_global_schedule[$network.'_top_opt_interval']['times'];
				$schedules_days = array();
				if(count($times) == 0 ) return false;
				if(count($days) == 0 ) return false;
				foreach($days as $rday){
					$schedules_days[] = $start +  ($rday-1) * 3600 * 24;

				}
				$schedules = array();
				foreach($schedules_days as $schedule){

					foreach($times as $time){

						$schedules[] = $schedule + floatval($time['hour']) * 3600 + floatval($time['minute']) * 60;

					}

				}
				sort($schedules,SORT_REGULAR);
				$ctime = $this->getTime($nowTime);

				foreach($schedules as $s ){
					if($s > $ctime ) {
						return $s;
					}

				}
				foreach($schedules  as $s){
					$s += 7 * 24 * 3600;
					if($s > $ctime) return $s;

				}
				return 0;
			}
			return 0;
		}
		public function getAllOptions(){
			$options = array();

			$format_fields = $this->getFormatFields();
			$all = $this->getAllNetworks();
			foreach($format_fields as $n=>$detail){
				foreach($detail  as $df){

					$options[] = $n."_".$df['option'];

				}
			}
			foreach($all as $a){

				$options[] = $a."_schedule_type_selected";
				$options[] = $a."_top_schedule_days";
				$options[] = $a."_time_choice_hour";
				$options[] = $a."_top_opt_interval";
				$options[] = $a."_time_choice_min";
			}
			$cwp_top_fields = $this->getGeneralFields();
			foreach ($cwp_top_fields as $field)
			{
				$options[] = $field['option'];
			}
			return $options;
		}
		public function sanitizeRequest(){

			$dataSent = $_POST['dataSent']['dataSent'];
			$valid = array();
			parse_str($dataSent, $options);
			$all_options = $this->getAllOptions();
			$invalid = array();
			foreach($options as $k => $option ){
				if(in_array($k,$all_options)){
					$valid[$k] = $option;

				}else{
					$invalid[] = $k;
				}

			}
			$_POST['dataSent']['dataSent'] = http_build_query($valid);
		}
		// Updates all options.
		public function updateAllOptions()
		{
			if(!is_admin()) return false;
			$dataSent = $_POST['dataSent']['dataSent'];
			$this->sanitizeRequest();
			$options = array();

			parse_str($dataSent, $options);
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
			$this->updateAllPostFormat();
			if(CWP_TOP_PRO){

				global $CWP_TOP_Core_PRO;
				$CWP_TOP_Core_PRO->updateTopProAjax();
			}
			die();
		}

		public function updateAllPostFormat()
		{
			$all = $this->getAllNetworks();
			$dataSent = $_POST['dataSent']['dataSent'];

			$options = array();
			parse_str($dataSent, $options);

			//print_r($options);
			foreach($all as $n){

				if(!array_key_exists($n.'_top_opt_custom_url_option', $options)) {
					$options[$n.'_top_opt_custom_url_option'] =  'off';
				}

				if(!array_key_exists($n.'_top_opt_use_url_shortner', $options)) {
					$options[$n.'_top_opt_use_url_shortner'] =  'off';
				}

				if(!array_key_exists($n.'_top_opt_post_with_image', $options)) {
					$options[$n.'_top_opt_post_with_image'] =  'off';
				}

				if(!array_key_exists($n.'_top_opt_tweet_multiple_times', $options)) {
					$options[$n.'_top_opt_tweet_multiple_times'] =  'off';
				}

				if(!array_key_exists($n.'_top_opt_ga_tracking', $options)) {
					$options[$n.'_top_opt_ga_tracking'] = 'off';
				}

				//if(!array_key_exists('top_opt_tweet_specific_category', $options)) {
				//	update_option('top_opt_tweet_specific_category', '');
				//}

				if(!array_key_exists($n.'_top_opt_omit_cats', $options)) {
					$options[$n.'_top_opt_omit_cats'] =  '';
				}

				if(!array_key_exists($n.'_top_opt_post_type', $options)) {
					$options[$n.'_top_opt_post_type']  =  'post';
				}

			}

			update_option('top_opt_post_formats', $options);

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

			if(!is_admin()) return false;

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
				'top_opt_interval'					=> '8',
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
				'top_fb_token'						=>'',
				'top_opt_post_formats'				=>'',
                // Added by Ash/Upwork
                'top_opt_posts_buffer_twitter'      => '',
                'top_opt_posts_buffer_facebook'     => '',
                'top_opt_posts_buffer_linkedin'     => '',
                'top_opt_posts_buffer_tumblr'       => '',
                'top_opt_posts_buffer_xing'         => '',
				'top_opt_shortest_key'              =>'',
				'top_opt_googl_key'                 =>'',
				'top_opt_owly_key'                  =>'',
				'top_opt_tweet_multiple_times'      => 'on',
				'rop_opt_cat_filter'                => 'exclude',
                // Added by Ash/Upwork
			);

			foreach ($defaultOptions as $option => $defaultValue) {
				update_option($option, $defaultValue);
			}


			update_option("top_opt_post_formats",array());
			update_option("cwp_top_global_schedule",array());
			$this->clearScheduledTweets();
			//die();
		}
		public function getPostFormatValues(){
			$cwp_rop_all_networks = $this->getFormatFields();
			$options = get_option("top_opt_post_formats");
			$return = array();
			foreach($cwp_rop_all_networks as $n=>$d){

				foreach($d as $fname => $f){

				    if(!isset($options[$n."_".$f['option']])){
					    $return[$n."_".$f['option']] = $f['default_value'];

				    }else{
					    $return[$n."_".$f['option']] = $options[$n."_".$f['option']];

				    }

				}

			}

			return $return;


		}
		public function deleteAllOptions()
		{
			global $defaultOptions;
			foreach ($defaultOptions as $option => $defaultValue) {
				delete_option($option);
			}
            // Added by Ash/Upwork
            do_action("rop_pro_deactivateFree");
            // Added by Ash/Upwork
		}

		// Generate all fields based on settings
		public static function generateFieldType($field)
		{
			$disabled = "";
			$pro = "";

			switch ($field['type']) {
				case 'image-list':
					$pro = "";
					$disabled = "";
					if (isset($field['available_pro'])) {


						if(!CWP_TOP_PRO){
							$pro = CWP_TOP_PRO_STRING;
							$disabled = "disabled='disabled'";
						}
					}
					if(isset($field['available_business'])){
						if(!apply_filters('rop_is_business_user', false)){
							$pro = $field["pro_text"];
							$disabled = "disabled='disabled'";
						}
					}
					$images = array();
					$images[] = "full";
					$images = array_merge($images,get_intermediate_image_sizes());

					echo "<select  name='".$field['option']."' id='".$field['option']."'   ".$disabled ." >";
							foreach($images as $image){
								echo "<option ".(($field["option_value"] == $image) ? "selected" : "")." value='".$image."'>".$image."</option>";
							}

					echo "</select><br/>".$pro;
					break;
				case 'text':
					if (isset($field['available_pro'])) {


						if(!CWP_TOP_PRO && $field['available_pro'] == 'yes') {
							if ( isset( $field["pro_text"] ) ) {
								$pro = __($field["pro_text"],'tweet-old-post');
							} else {
								$pro = CWP_TOP_PRO_STRING;
							}
							$disabled = "disabled='disabled'";
						}
					}

					if(isset($field['available_business'])){

						if(!apply_filters('rop_is_business_user', false)){
							$pro = $field["pro_text"];
							$disabled = "disabled='disabled'";

						}
					}
					echo "<input type='text' placeholder='".__($field['description'],'tweet-old-post')."' ".$disabled." value='".$field['option_value']."' name='".$field['option']."' id='".$field['option']."'><br/>".$pro;
					break;

				case 'number':
					if (isset($field['available_pro'])) {


						if(!CWP_TOP_PRO){
							$pro = CWP_TOP_PRO_STRING;
							$disabled = "disabled='disabled'";
						}
					}
					if(isset($field['available_business'])){
						if(!apply_filters('rop_is_business_user', false)){
							$pro = $field["pro_text"];
							$disabled = "disabled='disabled'";
						}
					}
					echo "<input type='number' placeholder='".__($field['description'],'tweet-old-post')."' ".$disabled." value='".$field['option_value']."' max='".$field['max-length']."' name='".$field['option']."' id='".$field['option']."'><br/>".$pro;
					break;

				case 'select':
					$noFieldOptions = intval(count($field['options']));
					$fieldOptions = array_keys($field['options']);
					if (isset($field['available_pro'])) {


						if(!CWP_TOP_PRO && $field['available_pro'] == 'yes'){
							$pro = CWP_TOP_PRO_STRING;
							$disabled = "disabled='disabled'";
						}
					}
					if(isset($field['available_business'])){
						if(!apply_filters('rop_is_business_user', false)){
							$pro = $field["pro_text"];
							$disabled = "disabled='disabled'";
						}
					}
					//if ($field['option']=='top_opt_post_type') $disabled = "disabled";
					print "<select id='".$field['option']."' name='".$field['option']."'".$disabled.">";
					for ($i=0; $i < $noFieldOptions; $i++) {
						print "<option value=".$fieldOptions[$i];
						if($field['option_value'] == $fieldOptions[$i]) { echo " selected='selected'"; }
						print ">".__($field['options'][$fieldOptions[$i]],'tweet-old-post')."</option>";
					}
					print "</select>".$pro;
					break;

				case 'checkbox':

					if (isset($field['available_pro'])) {

						if(!CWP_TOP_PRO){
							$pro = CWP_TOP_PRO_STRING;
							$disabled = "disabled='disabled'";
						}
					}
					if(isset($field['available_business'])){
						if(!apply_filters('rop_is_business_user', false)){
							$pro = $field["pro_text"];
							$disabled = "disabled='disabled'";
						}
					}
					print "<input id='".$field['option']."' type='checkbox' ".$disabled." name='".$field['option']."'";
					if($field['option_value'] == 'on') { echo "checked=checked"; }
					print " />".$pro;


					break;

				case 'categories-list':

					$taxs = get_taxonomies(array(
						'public'   => true
					),"object","and");

					$post_types = get_post_types( array(
						'public'   => true,
					), "object","and");
					$post_types["post"] = get_post_type_object( 'post' );
					$post_types["page"] = get_post_type_object( 'page' );

                    $taxonomies         = array();
					foreach($post_types as $pt=>$pd){
						foreach($taxs as $tx){

							if(in_array($pt,$tx->object_type)){

								$terms = get_terms($tx->name, array(
									'hide_empty'        => true,
									'number'            =>400

								) );
								if(!empty($terms)){
                                    // Added by Ash/Upwork
                                    $options                        = array();
									foreach ($terms as $t) {
                                        $options[$t->name]          = $t->term_id;
                                    }
                                    $taxonomies[$tx->labels->name]  = $options;
                                    // Added by Ash/Upwork
								}
							}
						}
					}

                    // Added by Ash/Upwork
                    ob_start();
                    include_once ROPPLUGINPATH . "/inc/view-categories-list.php";
                    echo ob_get_clean();
                    // Added by Ash/Upwork

					break;

				case 'custom-post-type':
					print "<div class='post-type-list clearfix'>";
					$args = array(
						'public'   => true,
						'_builtin' => false
					);

					$output = 'names'; // names or objects, note names is the default
					$operator = 'and'; // 'and' or 'or'
					if (isset($field['available_pro'])) {
						if(!CWP_TOP_PRO){
							$pro = CWP_TOP_PRO_STRING;
							$disabled = "disabled='disabled'";
						}
					}
					if(isset($field['available_business'])){
						if(!apply_filters('rop_is_business_user', false)){
							$pro = $field["pro_text"];
							$disabled = "disabled='disabled'";
						}
					}
					$post_types = get_post_types( $args, $output, $operator );
					array_push($post_types,"post","page");
					foreach ($post_types as $post_type) {

						//$top_opt_tweet_specific_category = get_option('top_opt_tweet_specific_category');

						if (!is_array(get_option('top_opt_post_type')))
							$top_opt_post_types = explode(',',get_option('top_opt_post_type'));
						else
							$top_opt_post_types = get_option('top_opt_post_type');

						print "<div class='cwp-cat '>";
						print "<input ".$disabled." type='checkbox' class='cwp-cpt-checkbox' name='".$field['option']."[]'  value='".$post_type."' id='".$field['option']."_cat_".$post_type."'";

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
					print "</div> ".$pro;
					break;

			}

		}


		public function echoTime() {

			echo $this->getTime();

			die();
		}
		public function getTime($nowTime=null) {
            if($nowTime) return $nowTime;
			return time() ;

			//return  time() - 253214 + 2 * 3600 + 24 * 3600;
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
		public function getRemoteCheck(){
			$remote_check = get_option("cwp_rop_remote_trigger");
			if($remote_check === false) $remote_check = "off";
			return $remote_check;
		}
		public function getBetaUserStatus(){
			$beta_user = get_option("cwp_rop_beta_user");
			if($beta_user === false) $beta_user  = "off";
			return $beta_user;
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



			}
		}
		public function clearOldCron(){
			if(isset($_POST['cwp-action'])){
				if($_POST['cwp-action'] == 'download_sysinfo'){
					header('Content-Disposition: attachment; filename="report.txt"');
					header('Content-type: text/plain');
					echo $_POST['cwp-top-sysinfo'];
					die();

				}

			}
			if ( false === ( $remote_calls = get_transient( 'rop_remote_calls' ) ) ) {
				$beta_user = $this->getBetaUserStatus();
				if($beta_user == "on" )	{
					$this->sendBetaUserTrigger($beta_user);
				}
				$remote_call = $this->getRemoteCheck();
				if($remote_call == "on" )	{
					$this->sendBetaUserTrigger($remote_call);
				}

				$this->sendRemoteTrigger($this->getRemoteCheck());
				set_transient( 'rop_remote_calls', "done", 24 * HOUR_IN_SECONDS );
			}
			if(!defined("VERSION_CHECK") && function_exists('topProImage')){
					$this->notices[] = "You need to have the latest version of the Revive Old Post Pro addon in order to use it. Please download it from the revive.social account";

			}
			$all = $this->getAllNetworks();

			if($this->pluginStatus !== 'true'){

				foreach($all as $a){

					wp_clear_scheduled_hook($a.'roptweetcron',array($a));


				}
				return false;
			}


			$networks = $this->getAvailableNetworks();
			if(wp_next_scheduled( 'cwp_top_tweet_cron' ) !== false) {

				$timestamp = wp_next_scheduled( 'cwp_top_tweet_cron' );
				wp_clear_scheduled_hook('cwp_top_tweet_cron');
				foreach($networks as $network){
					wp_schedule_single_event($timestamp,$network.'roptweetcron',array($network));
				}

			}else{

				if(wp_next_scheduled( 'cwptoptweetcronnew' ) !== false) {
					$timestamp = wp_next_scheduled( 'cwptoptweetcronnew' );
					wp_clear_scheduled_hook('cwptoptweetcronnew');
					foreach($networks as $network){
						wp_schedule_single_event($timestamp,$network.'roptweetcron',array($network));
					}
				}
				else{

						foreach($all as $a){
							if(wp_next_scheduled( $a.'cwptoptweetcron',array($a) ) !== false) {

								$timestamp = wp_next_scheduled($a.'cwptoptweetcron',array($a) );
								wp_clear_scheduled_hook($a.'cwptoptweetcron',array($a));
								wp_schedule_single_event($timestamp,$a.'roptweetcron',array($a));
							}
						}
						foreach($all as $a){
							if(!in_array($a,$networks)){
								wp_clear_scheduled_hook($a.'roptweetcron',array($a));
							}

						}

				}

			}

			if($this->pluginStatus === 'true'){
				foreach($networks as $avn){
					if(wp_next_scheduled( $avn.'roptweetcron',array($avn) ) === false) {
						$this->scheduleTweet($avn);
					}
				}
			}
		}

		public function clear_delete_type(){

			update_option('cwp_top_delete_type',-1);
		}
		public function loadAllHooks()
		{

			// loading all actions and filters
			add_action('admin_menu', array($this, 'addAdminMenuPage'));

			add_action('admin_enqueue_scripts', array($this, 'loadAllScriptsAndStyles'));

			add_action( 'admin_notices', array($this, 'adminNotice') );

			add_filter('plugin_action_links',array($this,'top_plugin_action_links'), 10, 2);

            // Added by Ash/Upwork
            add_filter('cwp_check_ajax_capability', array($this, 'checkAjaxCapability'), 10, 0);
            // Added by Ash/Upwork

			add_action( 'plugins_loaded', array($this, 'addLocalization') );

			//ajax actions

			// Update all options ajax action.
			add_action('wp_ajax_update_response', array($this, 'ajax'));

			// Reset all options ajax action.
			add_action('wp_ajax_reset_options', array($this, 'ajax'));

			// Add new twitter account ajax action
			add_action('wp_ajax_add_new_account', array($this, 'ajax'));

			// Display managed pages ajax action
			add_action('wp_ajax_display_pages', array($this, 'ajax'));

			// Add new account managed pages ajax action
			add_action('wp_ajax_add_pages', array($this, 'ajax'));

			// Add more than one twitter account ajax action
			add_action('wp_ajax_add_new_account_pro', array($this, 'ajax'));

			// Log Out Twitter user ajax action
			add_action('wp_ajax_log_out_user', array($this, 'ajax'));

			//
			add_action("rop_stop_posting", array($this,"clear_delete_type"));

			//start ROP
			add_action('wp_ajax_tweet_old_post_action', array($this, 'ajax'));

			//clear Log messages
			add_action('wp_ajax_rop_clear_log', array($this, 'ajax'));

			//remote trigger cron
			add_action('wp_ajax_remote_trigger', array($this, 'ajax'));
			add_action('wp_ajax_beta_user_trigger', array($this, 'ajax'));

			//sample tweet messages
			add_action('wp_ajax_view_sample_tweet_action', array($this, 'ajax'));

			// Tweet Old Post tweet now action.
			add_action('wp_ajax_tweet_now_action', array($this, 'ajax'));

			add_action('wp_ajax_gettime_action', array($this, 'ajax'));

			//get notice
			add_action('wp_ajax_getNotice_action', array($this, 'ajax'));

			//stop ROP
			add_action('wp_ajax_stop_tweet_old_post', array($this, 'ajax'));

			//custom actions

			add_action("rop_start_posting",array($this,"startPosting"));
			add_action("rop_stop_posting",array($this,"stopPosting"));
			$networks = $this->getAllNetworks();

			foreach($networks as $network){
				add_action($network.'roptweetcron',array($this,"tweetOldPost"));

			}

			//admin_init actions

			add_action('admin_init', array($this,'top_nag_ignore'));
			add_action('admin_init', array($this,'clearOldCron'));
            // Added by Ash/Upwork
            add_filter('template_include', array($this, 'captureRewrites'), 1, 1);
            add_filter('query_vars', array($this, 'addRewriteVars'));
            // Added by Ash/Upwork

			//filters

			add_filter("rop_users_filter",array($this,"rop_users_filter_free"),1,1);

            // Added by Ash/Upwork
            // test action, only to be used for testing
            //if(ROP_IS_TEST) add_action("admin_init", array($this, "doTestAction"));
            // Added by Ash/Upwork

			if(isset($_GET['debug']) ) {
	 			//$this->getNextTweetTime('twitter');
			    //$this->tweetOldPost("twitter"); 	global $CWP_TOP_Core_PRO;
				$this->tweetOldPost("twitter");

				die();
			}

		}

        function checkAjaxCapability() {
            $cap        = false;
			if (!current_user_can('manage_options') && $this->top_check_user_role( 'Administrator' )) {
				$cap    = true;
			} else {
				$cap    = current_user_can('manage_options');
            }
            return $cap;
        }

        function ajax() {
            if (!$this->checkAjaxCapability()) wp_die();
            check_ajax_referer("cwp-top-" . ROP_VERSION, "security");

            switch ($_POST["action"]) {
                case 'update_response':
                    $this->updateAllOptions();
                    break;
                case 'reset_options':
                    $this->resetAllOptions();
                    break;
                case 'add_new_account':
                    $this->addNewAccount();
                    break;
                case 'display_pages':
                    $this->displayPages();
                    break;
                case 'add_pages':
                    $this->addPages();
                    break;
                case 'add_new_account_pro':
                    $this->addNewAccountPro();
                    break;
                case 'log_out_user':
                    $this->logOutUser();
                    break;
                case 'tweet_old_post_action':
                    $this->startTweetOldPost();
                    break;
                case 'rop_clear_log':
                    $this->clearLog();
                    break;
                case 'remote_trigger':
                    $this->remoteTrigger();
                    break;
                case 'beta_user_trigger':
                    $this->betaUserTrigger();
                    break;
                case 'view_sample_tweet_action':
                    $this->viewSampleTweet();
                    break;
                case 'tweet_now_action':
                    $this->tweetNow();
                    break;
                case 'gettime_action':
                    $this->echoTime();
                    break;
                case 'getNotice_action':
                    $this->getNotice();
                    break;
                case 'stop_tweet_old_post':
                    $this->stopTweetOldPost();
                    break;
            }
            wp_die();
        }

        // Added by Ash/Upwork
        function addRewriteVars($vars){
            global $cwp_rop_self_endpoint;
            $vars[] = $cwp_rop_self_endpoint;
            return $vars;
        }

        function captureRewrites($template){
            global $wp_query, $cwp_rop_self_endpoint;
            if (get_query_var($cwp_rop_self_endpoint, false)){
                $this->processServerRequest();
                return null;
            }
            return $template;
        }

        private function processServerRequest(){
            if(
                !get_option("cwp_rop_remote_trigger", false)
                ||
                !get_option("cwp_topnew_active_status", false)
            ) return;

            $crons      = _get_cron_array();
            $this->clearScheduledTweets();

            foreach($crons as $time => $cron){
                foreach($cron as $hook => $dings){
                    if(strpos($hook, "roptweetcron") === FALSE) continue;

                    $network    = trim(str_replace("roptweetcron", "", $hook));
                    if($time > $this->getTime()){
                      //  echo "FUTURE $hook for $time (current time is " . $this->getTime() . ") <br>";
                        wp_schedule_single_event($time, $network.'roptweetcron', array($network));
                        continue;
                    }
                    
                    //echo "NOW $hook for $network for $time (current time is " . $this->getTime() . ") <br>";

                    foreach($dings as $hash => $data){
                        do_action($hook, $network);
                    }
                }
            }
        }
        // Added by Ash/Upwork

		public function rop_users_filter_free($users){

			if(!is_array($users)) $users = array();
			foreach($users as $k=>$user){
				if(!isset($user['service'])) {
					if ( strpos( $user['oauth_user_details']->profile_image_url, 'twimg' ) ) {

						$users[ $k ]['service'] = 'twitter';
					}
					if ( strpos( $user['oauth_user_details']->profile_image_url, 'facebook' ) ) {

						$users[ $k ]['service'] = 'facebook';
					}
				}
			}

			return $users;
		}

		public function remoteTrigger($status = ""){
			if(!is_admin()) return false;
			$state = isset($_POST["state"]) ? $_POST["state"] : "";

			if(!empty($status)) $state = $status;

			if(!empty($state) &&( $state == "on" || $state == "off")){

				update_option("cwp_rop_remote_trigger",$state);
                // Added by Ash/Upwork
				$response = $this->sendRemoteTrigger($state);
                if($response){
                    $error  = __('Error: ','tweet-old-post') . $response;
                    self::addNotice($error, 'error');
                    update_option("cwp_rop_remote_trigger", "off");
                    // if you want to show the user an alert, make showAlert true
                    wp_send_json_error(array("error" => $error, "showAlert" => false));
                }
                // Added by Ash/Upwork
			}

			if(empty($status)) die();
		}

		public function sendRemoteTrigger($state){

			global $cwp_rop_remote_trigger_url;
			$state = ($state == "on") ? "yes" : "no";

		    $response = wp_remote_post( $cwp_rop_remote_trigger_url, array(
					'method' => 'POST',
					'timeout' => 1,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array(),
					'body' => array( 'url' => get_site_url(), 'status' => $state ),
					'cookies' => array()
				)
			);

            // Added by Ash/Upwork
            if(is_wp_error($response)){
                return $response->get_error_message();
            }
            return null;
            // Added by Ash/Upwork
		}

		public function betaUserTrigger($status = ""){
			if(!is_admin()) return false;
			$state = $_POST["state"];
			if(!empty($status)) $state = $status;

			if(!empty($state) &&( $state == "on" || $state == "off")){

				update_option("cwp_rop_beta_user",$state);
				$this->sendBetaUserTrigger($state);

			}

			die();
		}


		public function sendBetaUserTrigger($state){

			global $cwp_rop_beta_trigger_url;
			$state = ($state == "on") ? "yes" : "no";

			wp_remote_post( $cwp_rop_beta_trigger_url, array(
					'method' => 'POST',
					'timeout' => 1,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array(),
					'body' => array( 'email' =>  get_bloginfo('admin_email'), 'status' => $state ),
					'cookies' => array()
				)
			);

		}
		public function loadAllScriptsAndStyles()
		{
			global $cwp_top_settings; // Global Tweet Old Post Settings

			// Enqueue and register all scripts on plugin's page
			if(isset($_GET['page'])) {
				if ($_GET['page'] == $cwp_top_settings['slug'] || $_GET['page'] == "ExcludePosts") {

                    // Added by Ash/Upwork
                    wp_enqueue_script("jquery");
                    wp_enqueue_script("jquery-ui-button");
					wp_register_script("jquery.chosen", ROP_ROOT . "js/chosen.jquery.min.js", array("jquery"), time(), true);
					wp_enqueue_script("jquery.chosen");
					wp_register_style("jquery.chosen", ROP_ROOT . "css/chosen.min.css", array(), time());
					wp_enqueue_style("jquery.chosen");
                    wp_register_style("jquery.ui-smoothness", "//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css");
                    wp_enqueue_style("jquery.ui-smoothness");
                    // Added by Ash/Upwork

                    // Enqueue and Register Main CSS File
					wp_register_style( 'cwp_top_stylesheet', ROPCSSFILE, array("jquery.chosen"), time());
					wp_enqueue_style( 'cwp_top_stylesheet' );

					// Register Main JS File
					wp_enqueue_script( 'cwp_top_js_countdown', ROPJSCOUNTDOWN, array(), time(), true );
					wp_enqueue_script( 'cwp_top_javascript', ROPJSFILE, array(), time(), true );
					wp_localize_script( 'cwp_top_javascript', 'cwp_top_ajaxload', array(
                        'ajaxurl'   => admin_url( 'admin-ajax.php' ),
                        'ajaxnonce' => wp_create_nonce("cwp-top-" . ROP_VERSION),
                    ) );
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
			add_submenu_page($cwp_top_settings['slug'], __('Exclude Posts','tweet-old-post'), __('Exclude Posts','tweet-old-post'), 'manage_options', 'ExcludePosts', 'rop_exclude_posts');

			add_submenu_page($cwp_top_settings['slug'], __('System Info','tweet-old-post'), __('System Info','tweet-old-post'), 'manage_options', 'SystemInfo', array($this,'system_info'));

		}

		public function getFormatFields(){

			$all = $this->getAllNetworks(true);
			global $cwp_format_fields;
			$networks_fields = array();

			foreach($all as $network=>$pro){
				if(CWP_TOP_PRO == $pro) {
					$networks_fields[ $network ] = $cwp_format_fields;
				}else{
					foreach($cwp_format_fields as $k=>$v){
						$v[ "available_pro"] = "yes";
						$networks_fields[ $network][$k] = $v;
					}

				}
			}
			return $networks_fields;
		}
		public function getGeneralFields(){
			global $cwp_top_fields;

			return $cwp_top_fields;

		}
		public function loadMainView()
		{
			$cwp_top_fields = $this->getGeneralFields();
			foreach ($cwp_top_fields as $field => $value) {
				$cwp_top_fields[$field]['option_value'] = get_option($cwp_top_fields[$field]['option']);
			}
			$all_networks  = $this->getAllNetworks();
			$format_fields  = $this->getFormatFields();
			$options = get_option("top_opt_post_formats");
			global $cwp_top_global_schedule;
			$cwp_top_global_schedule = $this->getSchedule();
			if($options === false ) $options = array();
			if($cwp_top_global_schedule === false ) $cwp_top_global_schedule = array();

			$schedule = $cwp_top_fields["interval"]['option_value'];
			foreach ($format_fields as $network_name => $network_details) {
				foreach ($network_details as $field => $vvalue) {
					$value = isset($options[$network_name."_".$format_fields[$network_name][$field]['option']]) ? $options[$network_name."_".$format_fields[$network_name][$field]['option']] : false ;
					if($value === false) {
						$value = get_option($format_fields[$network_name][$field]['option']);
						if($value === false) {
							if(isset($vvalue['default_value']))
								$value =  $vvalue['default_value'];
						}
					}
					$format_fields[$network_name][$field]['option_value'] = $value;
				}
				if(!isset($cwp_top_global_schedule[$network_name."_schedule_type_selected"])){
					$cwp_top_global_schedule[$network_name."_schedule_type_selected"] = "each";
					$cwp_top_global_schedule[$network_name."_top_opt_interval"] = $schedule;
				}
			}
			require_once(plugin_dir_path( __FILE__ )."view.php");
		}

		// Shortens the url.
		public static function shortenURL($url, $service, $id, $formats, $network, $showPlaceholder=false) {
            // Added by Ash/Upwork
            if ($showPlaceholder) {
                return "[$service]";
            }
            // Added by Ash/Upwork
            if (ROP_IS_TEST) {
                $url = "http://www.google.com/" . time();
            }

            $shortURL   = trim($url);
			$url        = urlencode($shortURL);
            switch ($service) {
                case "bit.ly":
                    $key            = trim(isset($formats[$network."_"."top_opt_bitly_key"]) ? $formats[$network."_"."top_opt_bitly_key"] : get_option( 'top_opt_bitly_key' ));
                    $user           = trim(isset($formats[$network."_"."top_opt_bitly_user"]) ? $formats[$network."_"."top_opt_bitly_user"] : get_option( 'top_opt_bitly_user' ));
                    $response       = self::callAPI(
                        "http://api.bit.ly/v3/shorten",
                        array("method" => "get"),
                        array("longUrl" => $url, "format" => "txt", "login" => $user, "apiKey" => $key),
                        null
                    );

                    if (intval($response["error"]) == 200) {
                        $shortURL   = $response["response"];
                    }
                    break;
			    case "shorte.st":
                    $key            = trim(isset($formats[$network."_"."top_opt_shortest_key"]) ? $formats[$network."_"."top_opt_shortest_key"] : get_option( 'top_opt_shortest_key' ));
                    $response       = self::callAPI(
                        "https://api.shorte.st/v1/data/url",
                        array("method" => "put", "json" => true),
                        array("urlToShorten" => $url),
                        array("public-api-token" => $key)
                    );

                    if (intval($response["error"]) == 200 && $response["response"]["status"] == "ok") {
                        $shortURL   = $response["response"]["shortenedUrl"];
                    }
                    break;
			    case "goo.gl":
                    $key            = trim(isset($formats[$network."_"."top_opt_googl_key"]) ? $formats[$network."_"."top_opt_googl_key"] : get_option( 'top_opt_googl_key' ));
                    $response       = self::callAPI(
                        "https://www.googleapis.com/urlshortener/v1/url?key=" . $key,
                        array("method" => "json", "json" => true),
                        array("longUrl" => urldecode($url)),
                        array("Content-Type" => "application/json")
                    );

                    if (intval($response["error"]) == 200 && !isset($response["response"]["error"])) {
                        $shortURL   = $response["response"]["id"];
                    }
                    break;
			    case "ow.ly":
                    $key            = trim(isset($formats[$network."_"."top_opt_owly_key"]) ? $formats[$network."_"."top_opt_owly_key"] : get_option( 'top_opt_owly_key' ));
                    $response       = self::callAPI(
                        "http://ow.ly/api/1.1/url/shorten",
                        array("method" => "get", "json" => true),
                        array("longUrl" => $url, "apiKey" => $key),
                        null
                    );

                    if (intval($response["error"]) == 200 && !isset($response["response"]["error"])) {
                        $shortURL   = $response["response"]["results"]["shortUrl"];
                    }
                    break;
			    case "is.gd":
                    $response       = self::callAPI(
                        "https://is.gd/api.php",
                        array("method" => "get"),
                        array("longurl" => $url),
                        null
                    );

                    if (intval($response["error"]) == 200) {
                        $shortURL   = $response["response"];
                    }
                    break;
			    default:
				    $shortURL = wp_get_shortlink($id);
                    break;
			}
			if($shortURL != ' 400 '&& $shortURL!="500" && $shortURL!="0") {
				return $shortURL;
			}
			else
				update_option('cwp_topnew_notice','Looks like is an error with your url shortner');
		}

		public function rop_load_dashboard_icon()
		{
			wp_register_style( 'rop_custom_dashboard_icon', ROPCUSTOMDASHBOARDICON, false, ROP_VERSION );
		    wp_enqueue_style( 'rop_custom_dashboard_icon' );

		}

        private static function callAPI($url, $props=array(), $params=array(), $headers=array())
        {
            $body       = null;
            $error      = null;
            if ($props && isset($props["method"]) && $props["method"] === "get") {
                $url    .= "?";
                foreach ($params as $k=>$v) {
                    $url    .= "$k=$v&";
                }
            }
            $conn       = curl_init($url);

            curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($conn, CURLOPT_FRESH_CONNECT, true);
            curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($conn, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($conn, CURLOPT_HEADER, 0);
            curl_setopt($conn, CURLOPT_NOSIGNAL, 1);

            if ($headers) {
                $header     = array();
                foreach ($headers as $key=>$val) {
                    $header[]   = "$key: $val";
                }
                curl_setopt($conn, CURLOPT_HTTPHEADER, $header);
            }

            if ($props && isset($props["method"])) {
                if (in_array($props["method"], array("post", "put"))) {
                    curl_setopt($conn, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
                }

                if ($props["method"] === "json") {
                    curl_setopt($conn, CURLOPT_POSTFIELDS, json_encode($params));
                }

                if (!in_array($props["method"], array("get", "post", "json"))) {
                    curl_setopt($conn, CURLOPT_CUSTOMREQUEST, strtoupper($props["method"]));
                }
            }

            try {
                $body           = curl_exec($conn);
                $error          = curl_getinfo($conn, CURLINFO_HTTP_CODE);
            } catch (Exception $e) {
                self::writeDebug("Exception " . $e->getMessage());
            }

            if (curl_errno($conn)) {
                self::addNotice("Error for request: " . $url . " : ". curl_error($conn), 'error');
                self::writeDebug("curl_errno ".curl_error($conn));
            }

            curl_close($conn);

            if ($props && isset($props["json"]) && $props["json"]) {
                $body   = json_decode($body, true);
            }

            $array          = array(
                "response"  => $body,
                "error"     => $error,
            );

            self::writeDebug("Calling ". $url. " with headers = " . print_r($header, true) . ", fields = " . print_r($params, true) . " returning raw response " . print_r($body,true) . " and finally returning " . print_r($array,true));

            return $array;
        }

        public static function writeDebug($msg)
        {
            if (ROP_IS_DEBUG) file_put_contents(ROPPLUGINPATH . "/tmp/log.log", date("F j, Y H:i:s", current_time("timestamp")) . " - " . $msg."\n", FILE_APPEND);
        }


	}
}

if(class_exists('CWP_TOP_Core')) {
	$CWP_TOP_Core = new CWP_TOP_Core;
}
