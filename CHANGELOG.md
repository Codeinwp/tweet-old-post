
 ### v8.5.14 - 2020-09-08 
 **Changes:** 
 * Change Pro: Removed Buffer integration. To connect your Facebook Groups to ROP, simply reconnect your Facebook account to the plugin, your groups will be imported automatically. Instagram sharing will cease to work on September 30th. [Please see this doc for more info.](https://docs.revive.social/article/1297-why-were-removing-buffer)
* New PRO: Your admined groups are imported into ROP by default along with your pages.
* Info: Added known error log message for Facebook permissions error. If receiving permission errors in the ROP logs while connecting different Facebook pages to different websites. Then [please see this doc for the solution](https://docs.revive.social/article/1031-fix-error-200-requires-managepages-and-publishpages)
 
 ### v8.5.13 - 2020-08-28 
 **Changes:** 
 * Change: Made account names clickable.
* Fix: WP 5.5 missing permissions callback notice.
* Info: Tested on WP 5.5
 
 ### v8.5.12 - 2020-08-03 
 **Changes:** 
 * Fix PRO: Edge case where "Share Scheduled Posts to Social Media On Publish" feature would not share posts because of empty array.
* Change: Switched Facebook API calls to Graph API 7.0
* Info: Tested on WP 5.5 RC1
 
 ### v8.5.11 - 2020-07-23 
 **Changes:** 
 * Fix: Posts always sharing as image posts even with option unchecked.
* Info: Added "rop_instant_share_message" filter for manipulating custom instant share messages.
 
 ### v8.5.10 - 2020-07-21 
 **Changes:** 
 * New PRO: You can now share your website posts to your Google My Business location(s).
* Fix PRO: Unknown service error when using "Share Scheduled Posts to Social Media On Publish" feature.
* Fix PRO: Missing media type error(silent) when "Share as image Post" feature was used for Twitter.
* Info: Added checks to help prevent duplicate post issues which can occur in some environments with database caching.
 
 ### v8.5.9 - 2020-07-20 
 **Changes:** 
 * New PRO: You can now share your website posts to your Google My Business location(s).
* Fix PRO: Unknown service error when using "Share Scheduled Posts to Social Media On Publish" feature.
* Fix PRO: Missing media type error(silent) when "Share as image Post" feature was used for Twitter.
* Info: Added checks to help prevent duplicate post issues which can occur in some environments with database caching.
 
 ### v8.5.8 - 2020-06-16 
 **Changes:** 
 * New PRO: Custom instant sharing messages. You can now alter the caption that would be sent to the social media network.
* Change: Opened up easier LinkedIn login workflow for old installs.
* Change: Increased default category hashtag length. Old value was causing users categories to get dropped before sharing if they were too long
 
 ### v8.5.7 - 2020-05-14 
 **Changes:** 
 * New PRO: Made it easier to connect Tumblr accounts for new installs.
* New: Added an option in general settings to make Instant Share feature truly immediate and more reliable.
* Change: Edited some text titles and descriptions in the plugin dashboard to make them easier to understand. 
* 
 
 ### v8.5.6 - 2020-04-15 
 **Changes:** 
 * Change: Switched rviv.ly shortener with is.gd shortener to prevent issues with sharing.
 
 ### v8.5.5 - 2020-04-11 
 **Changes:** 
 * Fix PRO: PHP Error when "Post with image" is checked in Post format for LinkedIn and post has no featured image set.
* Fix PRO: PHP Error when no post format option is available in the database and ROP tries to share a WP scheduled post that has become published.  
* Change: Bit.ly now uses bit.ly's v4 API endpoint.
* Info: Tested on WP 5.4.
 
 ### v8.5.4 - 2020-03-18 
 **Changes:** 
 * New PRO: Taxonomy filtering is now account-based
* Fix: Fixed an issue where the connected accounts would not drop down after clicking the "Share immediately using Revive Old Posts" button
* Change: Changed the function the "share immediately using Revive Old Posts" feature uses to set the cron task time
* Info: Tested on WP 5.4 RC
 
 ### v8.5.3 - 2020-02-13 
 **Changes:** 
 * Fix PRO: Fixed an issue where custom images could not be uploaded from the share queue. 
* Fix PRO: Fixed an issue where it would not be possible to activate some LinkedIn accounts due to their LinkedIn ID format. 
* Fix: Strip Divi shortcodes that are created by the Divi theme before the content is shared.
* Fix: Share immediately details were being saved in the DB for posts that were still drafts.
* Change: Optimized text for some log error messages and introduced known error fixes for a few others.
 
 ### v8.5.2 - 2019-12-31 
 **Changes:** 
 * New PRO: Share posts that have been scheduled for future publication by WordPress when their status change from "Scheduled" to "Publish" [Learn more](https://docs.revive.social/article/1194-share-scheduled-posts-to-social-media-on-publish-with-revive-old-posts)
* Fix PRO: Better compatibility for grabbing images for Tumblr shares on WPEngine hosted websites.
* Fix PRO: Tumblr URL shares were not showing the Link preview image.
* Fix: Instant sharing options set on the General Settings tab were not being honored.
* Fix: Corrected support doc URL for a LinkedIn error.
* Fix: Strip Divi builder shortcodes from the content before sharing to prevent undesired share output.
* Change: Videos selected for sharing in Media gallery will always be uploaded as a native video to Twitter regardless of "Post with image" option.
* Change: Call for Facebook URL scrape will now occur on original URL instead of the processed URL.
* Info: Added "Learn more" links for certain setting options on the plugin dashboard
 
 ### v8.5.1 - 2019-11-21 
 **Changes:** 
 * V851
 
 ### v8.5.0 - 2019-11-21 
 **Changes:** 
 * New PRO: Made it easier to connect LinkedIn Accounts for New Users
* New PRO: Plugin will now grab LinkedIn company pages using LinkedIn service in ROP dashboard
* New PRO: Easier addition of Instagram accounts and Facebook Groups using Buffer integration
* New PRO: Ability to add custom images to post share variations
* Fix PRO: Fixed an issue where Pinterest sharing failed on some hosts
* Fix: Post immediately feature not working in Gutenberg
* Change: Moved post immediately feature to metabox
* Change: Opened up easier Twitter login workflow for old installs
* Change: Sharing as an article post to Facebook no longer requires varifying domain with Facebook Business Manager
* Change: Scrape post details before sharing to Facebook to ensure link preview is always up to date
* Change: Enhance tutorial pointers
* Change: Plugin Roadmap link added to submenu (Vote for features you want to see in ROP!)
* Info: Tested on WP 5.3
 
 ### v8.4.4 - 2019-10-03 
 **Changes:** 
 * New: Label to show sharing status.
* Change: Removed some UI buttons and made UX improvements to dashboard.
* Change: Send posts to Facebook as text posts if users have not verified their domain with Facebook; inform users on how to go about the process. .
 
 ### v8.4.3 - 2019-09-12 
 **Changes:** 
 * New: Toast message will now show on ROP dashboard when an error is present in the log.
* New: Added a check to detect when ROP cron event is not firing.
* Fix: Switching from queue tab to another tab would refresh queue order.
* Fix: Linkedin 411 length required error.
* Fix: Users would be redirected to Tumblr homepage if credentials were wrong. An error will now be displayed.
* Fix: Pressing Twitter account button after deleting a Twitter account from ROP would refresh the page.
* Change: Edited "no accounts" text area with more details to help users get started.
* Change: Moved Support & Documentation buttons to top section of ROP dashboard.
* Change: Set rviv.ly back as default shortener.
* Change: Delete icon will now show whenever an account is deactivated
 
 ### v8.4.2 - 2019-08-23 
 **Changes:** 
 * PRO Fix: Unauthorized error when updating Pro plugin 
* Fix: Error would occur if the user tried to sign in while the API credentials form fields were empty
* Fix: Timer was not stopped if all accounts were removed using "remove all accounts" button
* Fix: Removing an account and then immediately trying to add it back would attempt to validate with old API credentials
 
 ### v8.4.1 - 2019-08-19 
 **Changes:** 
 * Fix: Use own keys button was appearing in Twitter modal for old installs
 
 ### v8.4.0 - 2019-08-19 
 **Changes:** 
 * PRO: Share to Instagram, Facebook Groups, LinkedIn Company Pages via Buffer integration.
* New: Made it easier to connect Twitter accounts for new users.
* Fix: Wrong error solution doc link would sometimes be given in Log.
* Fix: Fixed a bug where the sharing queue would be duplicated when switching tabs.
* Fix: Sites with Jetpack Photon feature activated would have issues with sharing images to twitter.
 
 ### v8.3.5 - 2019-08-02 
 **Changes:** 
 * Change: Made some UX changes to plugin dashboard
 
 ### v8.3.4 - 2019-07-21 
 **Changes:** 
 * New: Roadmap & Voting button! See where ROP is headed and vote on or recommend features which matter to you.
* Fix: PHP warning when Share Immediately feature would receive a non-array of selected accounts in rare cases.
* Fix: Silent Undefined Index error where Cron would try to fire for non-set actions.
* Change: Allow users who installed ROP prior to v8.3.0 to connect their Facebook accounts using the Revive Social Facebook App. No more need to go through an App review.
 
 ### v8.3.3 - 2019-07-10 
 **Changes:** 
 * New: An email will be sent to admin email address if the "Share more than once" option is unchecked and sharing is complete
* New: Documentation and support buttons on plugin dashboard
* Fix: LinkedIn Image sharing
* Fix: Facebook sharing timeout on some servers
 
 ### v8.3.2 - 2019-05-27 
 **Changes:** 
 * Fix: Log would some times show the wrong status message for the share
* Change: Use button to show app credential fields on new installs
 
 ### v8.3.1 - 2019-05-24 
 **Changes:** 
 * Fix: Use wp_remote_request functions in favor of guzzle which was causing issues on some websites
* Fix: Posting to Pinterest board names with commas
 
 ### v8.3.0 - 2019-05-24 
 **Changes:** 
 * New: Made connecting Facebook pages to plugin much simpler.
* Fix: When using publish now feature, all services would be checked after page reload even though only one was selected.
 
 ### v8.2.5 - 2019-05-17 
 **Changes:** 
 * New: Show admin notice when WP Cron is turned off, which can cause posting issues with ROP
* Fix: LinkedIn Image posts were not going through
* Fix: Posting to some Pinterest boards with special characters was not working
* Info: Tested on WP 5.2
 
 ### v8.2.4 - 2019-04-15 
 **Changes:** 
 * Fix: Minor bugs
 
 ### v8.2.3 - 2019-04-10 
 **Changes:** 
 * New: Filter introduced for Post Title & Content separator (check revive.social docs)
* New: Known errors will now show a link to the fix in the log area
* Fix: Twitter images would not share for sites which moved to a different protocol but didn't update their image links in the database
* PRO Fix: Moved to LinkedIn API v2 (check revive.social docs)
 
 ### v8.2.2 - 2019-03-20 
 **Changes:** 
 * New: Feedback button on plugin dashboard. Help us make ROP better by filling out the form!
* Fix: Minor typos
* PRO: You can now share custom messages/share variations in the order they were added.
* PRO Change: Updated custom messages/share variations metabox design
 
 ### v8.2.1 - 2019-03-01 
 **Changes:** 
 * Fix: Sharing queue issue with sites running WPML plugin
 
 ### v8.2.0 - 2019-02-09 
 **Changes:** 
 * New: The share post on publish feature is now in the lite version of the plugin. This should help with Facebook app review process (see revive.social docs)
 
 ### v8.1.8 - 2019-01-29 
 **Changes:** 
 * Fix: Minor bugs
 
 ### v8.1.7 - 2019-01-18 
 **Changes:** 
 * New: Adds basic support for WPML content sharing(see revive.social docs)
* Fix: Low PHP version notice was not showing the right text
* Fix: Minor bugs
 
 ### v8.1.6 - 2018-12-13 
 **Changes:** 
 * Fixed undefined variable error
 
 ### v8.1.5 - 2018-12-13 
 **Changes:** 
 * New: Made post share content filterable, you can now use post excerpt field (see docs)
* New: Pinterest shares will now link to the post on your website
* Changed: Bit.ly authentication method, old method will be deprecated in the future
* Changed: Custom message labels
* Fix: Pointer JavaScript error
* PRO Fix: Publish now feature not always showing
 
 ### v8.1.4 - 2018-12-03 
 **Changes:** 
 * New: Admin pointers for new plugin installs
* Change: Rename custom messages to "Share Variations"
* Fix: Automatically remove whitespace when adding credentials
* Fix: Excess blank space in shares caused by Gutenberg Editor
* PRO Fix: Publish now not showing on custom post types edit screens
 
 ### v8.1.3 - 2018-11-01 
 **Changes:** 
 * - Adds: Option to delete plugin settings on uninstall
* - Fix: Change twitter credential labels to match that on developer.twitter.com apps
* - Fix: Various typos
* - Fix: Issue with media library not loading when PRO plugin is installed in some cases
* - Fix: Error when other plugins also try to authenticate with Facebook
* - PRO: Adds support for magic tags for Custom Share Messages and Additional Text
* - PRO: Adds support for custom post type taxonomy hashtags
* - PRO: Adds Option to make share instantly option checked by default
 
 ### v8.1.2 - 2018-10-08 
 **Changes:** 
 * Fixed issue with hashtags in content
* Adds notice for PHP versions lower than 5.6
* Replaced goo.gl shortener with firebase dynamic links
 
 ### v8.1.1 - 2018-09-22 
 **Changes:** 
 * Fix rebrandly shortner missing feature.
* Adds option to disable the instant sharing feature.
 
 ### v8.1.0 - 2018-09-04 
 **Changes:** 
 * Adds support for Pinterest sharing feature
* Adds support for library media sharing feature
* Adds support for immediate post sharing feature
* Changed hashtags placement for Twitter
* Fixed hashtags for Tumblr
* Fixed Jetpack staging mode check
 
 ### v8.0.9 - 2018-06-18 
 **Changes:** 
 * Fix issue with Exclude posts blank page on non-English websites.
* Adds dedicated app workflow for Twitter authentication. 
* Adds tweet intent and review buttons in the header.
* Adds filter for content before sharing.
 
 ### v8.0.8 - 2018-05-25 
 **Changes:** 
 * Prevent sharing when the website is in the staging environment.
* Improve UI accessibility. 
* Adds possibility to fetch more post types.  
* Strip redundant shortcodes on post content sharing.
 
 ### v8.0.7 - 2018-05-10 
 **Changes:** 
 * Fix status migration issue from v7.
* Fix compatibility with the PRO version for the linkedin sharing on company pages.
* Fix compatibility with the PRO version for the thumblr sharing issues.
* Fix small typos in the plugin settings. 
 
 ### v8.0.6 - 2018-05-08 
 **Changes:** 
 * Fix hashtags issue when using post content as a source.
* Fix LinkedIn broken link when no image is used.
* Fix issue with sharing when multiple accounts are used with different custom schedules.
* Adds link only in the preview, remove from facebook message content.
* Adds limit for the number of logs.
 
 ### v8.0.5 - 2018-05-04 
 **Changes:** 
 * Fix issue with common hashtags using post content.
* Fix issue with add service when an account was removed from the list.
* Fix issue with cron lag between shares
* Improve disable state for pro services.
* Fix exclude posts inconsistency. 
* Fix incomplete UTM tags on certain shortners. 
* Fix refresh queue on start sharing. 
* Fix freezing message in frontend when the sharing is happening. 
* Fix Facebook limits regarding the number of accounts fetched. 
* Fix compatibility with PRO version regarding sharing on LinkedIn. 
 
 ### v8.0.4 - 2018-05-02 
 **Changes:** 
 * Fix issue with UTM tags and shortner consistency.
* Adds Exclude Posts as a separate page. 
* Fix issue with sharing stopped after the first share. 
* Fix timeline events refresh when the min interval changes. 
* Fix Facebook page accounts not showing in certain environments.
* Adds remove account feature for permanently delete an account from the list.
 
 ### v8.0.3 - 2018-04-28 
 **Changes:** 
 * Fix schedule synchronization issues.
* Fix LinkedIn authentication with the wrong redirect_url.
 
 ### v8.0.2 - 2018-04-27 
 **Changes:** 
 * Fix issue with old Facebook applications and strict OAuth urls settings.
* Fix issue taxonomies filter setting. 
* Fix filter by excluded posts issue.
* Fix issue when LinkedIn exceptions on login.
* Adds more exceptions handling for Facebook authentications.
* Fix compatibility with pro version for post_types and custom share messages.
 
 ### v8.0.1 - 2018-04-26 
 **Changes:** 
 * Fix Linkedin error on loading SDK class.
* Fix multiple twitter accounts warning message.
* Fix foreach loop on the services model.
* Fix Facebook authentication issues with application url.
* Adds notice when using an old Pro version.
 
 ### v8.0.0 - 2018-04-26 
 **Changes:** 
 * Major improvements to the codebase.
* Adds schedule and format per accounts, not per networks as it was before.
* Improve settings UI as well as the accounts authentication workflow.
* Improve posts selections per accounts.
* Improves logs reporting and messages.
* Adds major improvements to schedules trigger, implementing a new way of using wp-cron events for the plugin sharing.
 
### 7.4.8 - 06/04/2017
**Changes:** 
- update description

### 7.4.8 - 03/04/2017
**Changes:** 
- Fixed bad chars in name for twitter.
- Fixed facebook display users.

### 7.4.7 - 23/01/2017
**Changes:** 
- Fixed remote check for pro version.
- Added rviv.ly.
- Fixed clear setting on uninstall.

### 7.4.6 - 03/11/2016
**Changes:** 
- Fixed deactivation error

### 7.4.5 - 02/11/2016
**Changes:** 
- Improved schedule trigger mechanism
- Fixed post format saving issues
- Fixed status saving issue
- Updated readme and assets

### 7.4.0 - 28/09/2016
**Changes:** 
- Added support for custom messages
- Fixes issue with multiple taxonomies having the same name
- Fixed instructions for popups

### 7.3.8 - 19/07/2016
**Changes:** 
- Improved categories excluding UI in the General tab
- Improved design of the social networks authorization popups
- Added more shortners
- Fixed issue with wrong tags fetch

### 7.3.7 - 30/05/2016
**Changes:** 
- Fixed issue with inverted settings in post format and custom schedule

### 7.3.6 - 27/05/2016
**Changes:** 
- Fixed issue sample post rendering
- Improved error logging for facebook request
- Fixed typos in facebook description
- Added default tab for Manage Queue

### 7.3.5 - 26/05/2016
**Changes:** 
- Fixed issue with shortners and slow loading page
- Fixed layout issues for post with images on Manage Queue section
- Fixed issue with encoding

### 7.3.2 - 22/05/2016
**Changes:** 
- Fixed exclude posts bug
 ### 7.3.1 - 20/05/2016 **Changes:** - Fixed compatibility with old PHP versions where anonymous functions are not supported.  ### 7.3.0 - 19/05/2016 **Changes:** - Added advanced scheduling option - Added fix for is.gd - Fixed responsive issue - Fixed some security issues  ### 7.2 - 11/02/2016 Changes: tweet-old-post Fix readme tweet-old-post per network buffer 1) removed the global buffer and added a per-network buffer 2) if min and/or max date of posts is 0, do not include that part in the query tweet-old-post Fixed randomization algorithm, preventing share twice of same post until the end of cycle Fixed date filter when both of them are 0 ### 7.1 - 14/01/2016 Changes: tweet-old-post Add confirmation box to reset settings. Add confirmation box to reset settings in the plugin options. tweet-old-post Bug fixes for cron and image posting 1) Create endpoint for remote check 2) White posting to twitter, post without image if media is invalid tweet-old-post Bug fix Show alert only when showAlert is being sent from the back end tweet-old-post Bux fix: Support from cron If cron is disabled/inoperative, fire events through the endpoint if they are due tweet-old-post Call ROP endpoint Call the ROP endpoint with the correct network arguments tweet-old-post If cron is not running, reschedule future tweets In case the cron is only temporarily disabled, the future schedule should not be cleared. So, when the endpoint is called, we fetch the cron, clear it, execute the passed schedule and schedule the future ones. tweet-old-post Fixed schedule inconsistency Fixed posting with image tweet-old-post Merge pull request #41 from HardeepAsrani/development Add confirmation box to reset settings. tweet-old-post Fixed menu icon ### 7.0.8 - 18/09/2015 Changes: tweet-old-post tweet-old-post removed new lines for tweets tweet-old-post tweet-old-post fixed problems with custom icons tweet-old-post *fixed problem with mysql_get_client_info tweet-old-post Fixed multisite issue for redirect url tweet-old-post making translation ready tweet-old-post fixed single quotes problem tweet-old-post fixed tumblr tags ### 7.0.6 - 28/05/2015 Changes: tweet-old-post tweet-old-post removed redundant code tweet-old-post tweet-old-post added compatibility for wordpress 4.2.2 tweet-old-post tweet-old-post fixed compatibility with pro version ### 7.0.4 - 25/05/2015 Changes: tweet-old-post tweet-old-post fixed issue with tweet length tweet-old-post fixed issue with facebook hashtag tweet-old-post tweet-old-post added bussines version ### 7.0.4 - 16/04/2015 Changes: tweet-old-post tweet-old-post fixed bug with the new facebook api changes. tweet-old-post changed pro banner tweet-old-post Merge branch 'development' of https://github.com/Codeinwp/tweet-old-post into development ### 7.0.3 - 03/04/2015 Changes: tweet-old-post tweet-old-post fixed problem with media_id tweet-old-post tweet-old-post fixed bug with logs reporting in system info where there is no log available. tweet-old-post Update readme.txt tweet-old-post Update readme.txt ### 7.0.2 - 26/03/2015 Changes: tweet-old-post tweet-old-post Removed twitter update_with_media call. tweet-old-post Fixed activation error notices when WP_DEBUG was enabled tweet-old-post Merge remote-tracking branch 'origin/development' into development ### 7.0.1 - 06/03/2015 Changes: tweet-old-post http bug tweet-old-post Final version of tweet old post tweet-old-post final vers tweet-old-post Added Top 5.7 version tweet-old-post added strlower tweet-old-post Tweets now are posted immediately, fixed scheduling and added debug messages tweet-old-post latest stable version 6.2 tweet-old-post added exclude posts back tweet-old-post latest major fixes tweet-old-post fixed interrupted posting tweet-old-post Added settings link, fixed tweet cutting and added cron debug messages Added settings link, fixed tweet cutting and added cron debug messages tweet-old-post latest top fixes [stable version ] tweet-old-post almost added fb tweet-old-post stable version with linkedin + facebook tweet-old-post custom icon, final fixes tweet-old-post fixed fb instructions + small css things tweet-old-post Set up localization and translation tweet-old-post 6.8.1 with multi-language + cpt support tweet-old-post fix post issue tweet-old-post various small fixes to encoding tweet-old-post various fixes tweet-old-post fix for coma on the exclude post page tweet-old-post fixed the shortner problem that was not adding the analytics. tweet-old-post fixed the cron issue tweet-old-post increased notification time check by *5 increased notification time check by *5 tweet-old-post tweet-old-post Added post format per network tweet-old-post Rearranged the options tweet-old-post Merge remote-tracking branch 'origin/development' into development tweet-old-post tweet-old-post Fixes for no account post sharing issue tweet-old-post Added immediately share after start tweet-old-post redesign and on/off button tweet-old-post tweet-old-post Improved tabs graphic tweet-old-post Added remote cron check tweet-old-post tweet-old-post added pro badge for linkedin post format tweet-old-post fixed compatibility issue with older versions tweet-old-post tweet-old-post added pro badge for linkedin post format tweet-old-post fixed compatibility issue with older versions tweet-old-post tweet-old-post fixed the relative path issue tweet-old-post Update readme.txt tweet-old-post tweet-old-post fixed issue with notices tweet-old-post solved some cron compatibilty problems tweet-old-post tweet-old-post fixed double posting issue for cron tweet-old-post tweet-old-post fixed backwards compatibility issue tweet-old-post Removed useless spaces in post format tweet-old-post fixed custom field from url issue tweet-old-post tweet-old-post fixed problem with oauth time tweet-old-post fixed problem with cron time tweet-old-post tweet-old-post fixed problem with oauth time tweet-old-post fixed problem with cron time tweet-old-post Merge remote-tracking branch 'origin/development' into development tweet-old-post Update core.php tweet-old-post Update tweet-old-post.php tweet-old-post Update view.php tweet-old-post Update OAuth.php tweet-old-post Update tweet-old-post.php tweet-old-post tweet-old-post added more complex log system tweet-old-post improved cron schedule tweet-old-post Merge remote-tracking branch 'origin/development' into development tweet-old-post tweet-old-post fixed sample tweet tweet-old-post added support for pro version tweet-old-post tweet-old-post added clock feature tweet-old-post tweet-old-post fix for old cron tweet-old-post fix sample tweet image tweet-old-post tweet-old-post removed console clock tweet-old-post Update readme.txt tweet-old-post Fixed https request tweet-old-post Update tweet-old-post.php tweet-old-post Update core.php tweet-old-post Update style.css tweet-old-post Update tweet-old-post.php tweet-old-post tweet-old-post fixed typo bugs. tweet-old-post Update readme.txt tweet-old-post tweet-old-post fixed excluded post bug tweet-old-post fixed cron time issue tweet-old-post added more log types messages tweet-old-post improved system info tweet-old-post Merge remote-tracking branch 'origin/development' into development tweet-old-post tweet-old-post update version tweet-old-post tweet-old-post added sib banner tweet-old-post tweet-old-post updated changelog tweet-old-post tweet-old-post fixed bug with share more than once. tweet-old-post Update tweet-old-post.php tweet-old-post Update view.php tweet-old-post tweet-old-post Fixed issue cron stop tweet-old-post Fixed issue for excluded post tweet-old-post Added exclude posts from custom post types. tweet-old-post Merge remote-tracking branch 'origin/development' into development tweet-old-post tweet-old-post Fixed issue cron stop tweet-old-post Fixed issue for excluded post tweet-old-post Added exclude posts from custom post types. tweet-old-post updated version tweet-old-post tweet-old-post fixed sample post issue tweet-old-post tweet-old-post fixed tweet custom field url tweet-old-post tweet-old-post changed version tweet-old-post fixed minutes typo tweet-old-post tweet-old-post rollback generate tweet tweet-old-post Merge remote-tracking branch 'origin/development' into development tweet-old-post tweet-old-post fixed image compatibility tweet-old-post tweet-old-post fixed pro compatibility tweet-old-post Update readme.txt tweet-old-post buttons added: Google Plus, XING , Stumbleupon , Tumblr tweet-old-post strip html response. tweet-old-post tweet-old-post rewrite the tweet generation tweet-old-post added usefull filtes and hooks tweet-old-post tweet-old-post Fixed issue with duplicate posting tweet-old-post Added Xing and Tumbr Networks tweet-old-post Fixed issue with random posts on large databases. tweet-old-post tweet-old-post Fixed issue with duplicate posting tweet-old-post Added Xing and Tumbr Networks tweet-old-post Fixed issue with random posts on large databases. tweet-old-post tweet-old-post Fixed no link issue tweet-old-post Added new info to System Log tweet-old-post tweet-old-post FIxed compatibilty issues with old pro version tweet-old-post tweet-old-post added chacter count when media is active tweet-old-post tweet-old-post fixed link position in tweets. tweet-old-post tweet-old-post removed only pro badge on custom schedule secundar tabs tweet-old-post tweet-old-post fixed problem with strange chars in tweets.
