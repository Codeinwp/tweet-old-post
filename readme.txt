=== Revive Old Post (Former Tweet Old Post) ===
Contributors: codeinwp,marius2012,marius_codeinwp,hardeepasrani,Madalin_Themeisle, rsocial
Tags: admin, ajax, plugin, twitter, facebook, linkedin, automatic, tweet, share, wordpress, marketing, sharing, Tweet old post, Tweets,evergreen,  Promote old post by tweeting about them, Twitter, Auto Tweet, Hashtags, Twitter Hashtags, Tweet Posts, Tweet, Post Tweets, Wordpress Twitter Plugin, Twitter Plugin, Tweet Selected Posts, Twitter, Promote Posts, Tweet Random Post, Share Post, Promote Post, Post Tweets, Wordpress Twitter, Drive Traffic, Tweet Selected Posts
Requires at least: 2.7
Tested up to: 4.5.2
Stable tag: trunk


Plugin to share about your old posts on twitter, facebook, linkedin to get more hits for them and keep them alive.

== Description ==

### What the plugin can do ?

This plugin helps you to keeps your old posts alive by sharing them and driving more traffic to them from social networks. It also helps you to promote your content. You can set time and no of posts to share to drive more traffic.For questions, comments, or feature requests, <a href="http://revive.social/support/?utm_source=readmetop&utm_medium=announce&utm_campaign=top">contact us</a>!



**Revive Old Post provides following features**

- Share new and old posts.
- Choose the time between posts.
- Choose the number of posts to share.
- Use hashtags to focus on topics.
- Include links back to your site.
- Exclude categories
- Exclude specific posts.


> ### Why to upgrade to PRO ?

> Using the <a rel="friend" href="http://revive.social/plugins/revive-old-post/">PRO version</a> of the plugin you will unleash the most important functionality : images in tweets . Using those your RT/CTR will go crazy.

> Other notable features :

> * Multiple Social Accounts
> * Custom Post Types support
> * Linkedin support
> * Post with image
> * Custom Schedule
> * Post to Xing / Tumblr

Some of you reported some scheduling issues, after investigation work looks like this is coming from some web hosts, make sure you check-out this post about <a rel="friend" href="http://www.codeinwp.com/blog/best-wordpress-shared-hosting-providers/">shared WordPress hosting</a>, which should help you pick a good one that works.

For updates follow https://twitter.com/ReviveSocial If you have anything you can let us know <a href="http://revive.social/support/?utm_source=readmetop&utm_medium=announce&utm_campaign=top">here</a>.

** Useful Resources **

- Check-out our <a href="http://docs.revive.social" rel="friend" target="_blank">tutorials site</a>
- Take a look at our other <a href="http://themeisle.com/wordpress-plugins/" rel="friend" target="_blank">plugins</a>.
- Read more about WordPress on our <a href="http://www.codeinwp.com/blog/" rel="friend" target="_blank">blog</a>.


= Translations =

* sk_SK translation by Patrik Žec (PATWIST) of http://patwist.com

== Installation ==

Following are the steps to install the Revive Old Post plugin

1. Download the latest version of the Revive Old Posts Plugin to your computer from here.
2. With an FTP program, access your sites server.
3. Upload (copy) the Plugin file(s) or folder to the /wp-content/plugins folder.
4. In your WordPress Administration Panels, click on Plugins from the menu.
5. You should see Revive Old Posts Plugin listed. If not, with your FTP program, check the folder to see if it is installed. If it isn�t, upload the file(s) again. If it is, delete the files and upload them again.
6. To turn the Revive Old Posts Plugin on, click Activate.
7. Check your Administration Panels or WordPress blog to see if the Plugin is working.
8. You can change the plugin options from Revive Old Posts under settings menu.

Alternatively you can also follow the following steps to install the Revive Old Post plugin

1. In your WordPress Administration Panels, click on Add New option under Plugins from the menu.
2. Click on upload at the top.
3. Browse the location and select the Revive Old Post Plugin and click install now.
4. To turn the Revive Old Posts Plugin on, click Activate.
5. Check your Administration Panels or WordPress blog to see if the Plugin is working.
6. You can change the plugin options from Revive Old Posts under settings menu.

== Frequently Asked Questions ==
If you have any questions please get in touch with us at,
http://revive.social/support/

**Before asking any question you need to check if you have the latest version, either PRO or FREE.**


**Plugin doesn't post at the regular interval or sends more posts than it should send to social networks.**

- Unfortunately wp_cron function isn't perfect, it trigger just when somebody visit your site, so you nobody visit your site in 3 hours, ROP won't trigger. In order to achieve this you need to enable Remote Check and add this line to your wp-config.php right after the lines with database credentials:

> define('DISABLE_WP_CRON', true);

**Post are not sent to the social networks and i always see the green badge with 'You can refresh the page to see the next schedule !'**

- You need to add this line to your wp-config.php right after the lines with database credentials:

  > define('ALTERNATE_WP_CRON', true);

**How do i add a facebook account**

 - Please fallow this tutorial : http://docs.revive.social/article/349-how-to-create-a-facebook-application-for-revive-old-post

**If new version doesn't works**

- Try other versions from http://wordpress.org/extend/plugins/tweet-old-post/download/
- Manually upload it in your plugins folder, activate and use.
- Note: Do not upgrade your plugin if you want to use the older version.


**Any more questions or doubts?**

- Contact us at http://revive.social/support/ and send us also a copy from Revive Old Post -> System Info



== Screenshots ==

1. Screenshot 1 Basic configurable options for Tweet Old Post to function, with ability to tweet at random interval.


for more you can check out

http://revive.social/plugins/revive-old-post


== Changelog ==

**New in v7.3.8**

*  Improved categories excluding UI in the General tab
*  Improved design of the social networks authorization popups
*  Added more shortners
*  Fixed issue with wrong tags fetch



**New in v7.3.7**

* Fixed issue with inverted settings in post format and custom schedule

**New in v7.3.6**

* Fixed issue sample post rendering
* Improved error logging for facebook request
* Fixed typos in facebook description
* Added default tab for Manage Queue

**New in v7.3.5**

* Fixed issue with encoding
* Fixed issue with shortners and slow loading
* Fixed layout issue for posts with images in Manage Queue

**New in v7.3.2**

* Fixed issue with exclude posts

**New in v7.3.1**

* Fixed compatibility with old php versions.  

**New in v7.3**

* Added Manage queue timeline.
* Fixed responsive issues
* Fixed issue with is.gd

**New in v7.2**

* Fixed randomization algorithm, preventing sharing of same post twice until the end of cycle.
* Fixed date range selection when both values are 0


**New in v7.1**

* Fixed inconsistency in the schedule. Now posting is more accurate.
* Fixed image sharing issue which was not working for some server configuration


**New in v7.0.8**

* Added facebook tutorial for facebook share.
* Fixed multisite issue for redirect url
* Making translation ready for new wordpress.org system
* Fixed single quotes problem
* Fixed tumblr tags

**New in v7.0.4**

* Changed pro banner.
* Fixed bugs with the new facebook api changes.

**New in v7.0.2**

* Removed twitter update_with_media call.
* Fixed activation error notices when WP_DEBUG was enabled

**New in v7.0**

* Fixed issue with duplicate posting
* Added Xing and Tumbr Networks
* Fixed issue with random posts on large databases.

**New in v6.9.6**

* Fixed issue cron stop
* Fixed issue for excluded post
* Added exclude posts from custom post types.


**New in v6.9.4**

* Fixed issue with share more than once option


**New in v6.9.3**

* Improved logging system
* Fixed vulnerability issue with update options
* Fixed issues with custom schedule timing
* Improved excluded category design
* Fixed excluded post selection issue


**New in v6.8.8**

Added a more complex logging system
Fixed multiple accounts/posts issue
Fixed 404 twitter login error

**New in v6.8.5**

Completely reworked how cron is working
Separated post format by network
Added support for custom schedule
Added remote cron trigger feature

**New in v6.8.2**

Fixed no available posts issue

**New in v6.8.1**

Added language support and custom post types

**New in v6.8**

Added Facebook and Linkedin

**New in v6.7**

Fixed interrupted posting issue

**New in v6.6**

Fixed excluded category issue and some small others.

**New in v6.7.7**

Added Facebook and Linkedin, Facebook is also enabled for the free users
Improved Post with image feature, we can also pull the image from post
Fixed Tweet over 140 chars error
Added Google Analytics Campaign Tracking
Rebranded into Revive Old Post

**New in v6.7.5**

Fixed some debug messages

**New in v6.7.3**

Added settings link, fixed tweet cutting and added cron debug messages

**New in v6.6**

Fixed excluded category issue and some small others.

**New in v6.0**

Tweets now are posted immediately, fixed scheduling and added debug messages

**New in v5.9**

Tags are converted to lowercase automatically now

**New in v5.8**

Added post by image options in the pro version and some fixes.

**New in v5.7**

Fixed permissions

**New in v5.6**

Added bit.ly back

**New in v5.5**

Fixed the table prefix issue

**New in v5.4**

Fixed the hashtags length issue

**New in v5.3**

Fixed the custom field issue

**New in v5.2**

Fixed exclude categories error, added wp short url, fixed oauth error, removed broken shorten services.

**New in v5.0**

- Whole plugin was rewrote from scratch and a pro version was added, so after 50 hours of work, here we are. This change will allow us to easier fix issues/ release new features and maintain the plugin.

**New in v4.0.9**

- Resolved twitter connectivity issue, for users who were not able to connect in 4.0.8. Twitter has changed their policy
as per https://dev.twitter.com/discussions/24239



**New in v4.0.8**

- Resolved twitter connectivity issue. Twitter has changed their policy
as per https://dev.twitter.com/discussions/24239



**New in v4.0.7**

- Resolved tweet not posting issue.


**New in v4.0.6**

- Changed how pages are navigated. Should not conflict with any of the plugin that interacts with twitter ever.
- For "Page not found", update the settings and then authorize with twitter.
- If you are not able to update anything or you are redirecting to your home page, reset the settings and try again.
- Code Cleanup.


**New in v4.0.5**

- Implemented Twitter API v1.1 as Twitter is retiring API v1 from 7th May.
- Handled conflict with BackWPup plugin.
- Some performance improvements with WPSuperCache plugin.
- Some design changes.
- Code Cleanup.


**New in v4.0.4**

- Resolved issue of tweet not posting automatically. Thanks to Daniel Lopez Gonzalez for helping me.
- Minor Fixes


**New in v4.0.3**

- Handled too many tweets when W3 Total Cache plugin is installed. Please check and let me know.
- Bug fixes


**New in v4.0.2**

- Removed the option to specify the consumer key and secret as twitter does not show the application from which its tweeted anymore.
- Most probably, the tweet not posting automatically issue is resolved. Please check and let me know.
- Bug fixes


**New in v4.0.1**

- Resolved issue of page getting blank after returning from twitter
- added pages to exclude post option
- Bug fixes
- updated the steps of creating twitter application check here http://www.ajaymatharu.com/major-update-to-tweet-old-post/


**New in v4.0.0**

- You can now change the application name. Change via Tweet Old Post to your specified name. Follow the Steps here,
http://www.ajaymatharu.com/major-update-to-tweet-old-post/
- Pages can now be included in tweets. Added an option to select what is to be tweeted (pages, posts, or both).
- Removed "." and used "-" when adding additional text, "." was causing grammatical mistakes if sentence was trimmed.
- Added option to specify number of posts that can be tweeted simultaneously. You can specify how many tweets you want at a time.
- Last but not the least, removed random time slot was causing lot of confusion.



**New in v3.3.3**

- Resolved permission issue of exclude post.



**New in v3.3.2**

- Resolved too many redirects issue
	If its still not working try these steps
		- Make sure "Tweet Old Post Admin URL (Current URL)" is showing your current URL.
		- Click on "Update Tweet Old Post Options".
		- Try to authorize again.
- Removed "_" from hashtags. Previously space in hashtag was replaced with "_". Now there will be no spaces or "_" in hashtags.



**New in v3.3.1**

- Changed logic for posting data to twitter.
- Resolved bit.ly issue.



**New in v3.3.0**

- Attempt to fix logs out issue (Tweet Old Post pushes out when any action is performed).



**New in v3.2.9**

- Option to reset setting. When something goes wrong, please reset the settings and setup again.
- For people still facing issues of conflict with Google Analytics Plugin, this version should work.
- Minor bug fixes.



**New in v3.2.8**

- Resolved conflict with Google Analytics Plugin.
- Changed the log file location to root of plugin folder.
- Maintained Tweet Cycle. Repeat only when all post have been tweeted.
- Made other optimizations and resolved some minor bugs.



**New in v3.2.7**

- Added logging for people who cant make it work can enable and check the log, or mail me the log file.
- Brought back the exclude post option.
- Made other optimizations and resolved some minor bugs.
- Check http://www.ajaymatharu.com/tweet-old-post-update-3-2-7/ for more detailed explanation.



**New in v3.2.6**

- removed exclude post due to security threat. Will work on it and bring it up back.



**New in v3.2.5**

- Resolved hashtag not posting issue.
- other bug fixes.



**New in v3.2.4**

- Bug fixes



**New in v3.2.3**

- Bug fixes



**New in v3.2.2**

- Resolved bit.ly issue
- new option for hashtags
- other bug fixes



**New in v3.2.1**

- Bug fixes



**New in v3.2**

- Bug fixes
- Option to choose to include link in post
- option to post only title or body or both title and body
- option to set additional text either at beginning or end of tweet
- option to pick hashtags from custom field



**New in v3.1.2**

- Resolved tweets not getting posted when categories are excluded.
- If you are not able to authorise your twitter account set you blog URL in Administration → Settings → General.



**New in v3.1**

- Resolved issue of plugin flooding twitter account with tweets.
- added provision to exclude some post from selected categories



**New in v3.0**

- added OAuth authentication
- user defined intervals
- may not work under php 4 requires php 5



**New in v2.0**

- added provision to select if you want to shorten the URL or not.
- Cleaned other options.



**New in v1.9**

- Removed PHP 4 support as it was creating problem for lot of people



**New in v1.8**

- Bug Fixes
- Provision to fetch tweet url from custom field



**New in v1.7**

- Removed api option from 1click.at not needed api key



**New in v1.6**

- Made the plugin PHP 4 compatible. Guys try it out and please let me know if that worked.
- Better error prompting. If your tweets are not appearing on twitter. Try "Tweet Now" button you'll see if there is any problem in tweeting.
- Added 1click.at shortning service you need to get the api key from http://theeasyapi.com/ you need to add your machine IP address in the server of http://theeasyapi.com/ for this api key to work.



**New in v1.5**

- Maximum age of post to be eligible for tweet - allows you to set Maximum age of the post to be eligible for tweet
- Added one more shortner service was looking for j.mp but they dont have the api yet.



**New in v1.4**

- Hashtags - allows you to set default hashtags for your tweets



**New in v1.3**

- URL Shortener Service - allows you to select which URL shortener service you want to use.



**New in v1.2**

- Tweet Prefix - Allows you to set prefix to the tweets.
- Add Data - Allows you to add post data to the tweets
- Tweet now - Button that will tweet at that moment without wanting you to wait for scheduled tweet



**v1.1**

- Twitter Username & Password - Using this twitter account credentials plugin will tweet.
- Minimum interval between tweets - allows you to determine how often the plugin will automatically choose and tweet a blog post for you.
- Randomness interval - This is a contributing factor in minimum interval so that posts are randomly chosen and tweeted from your blog.
- Minimum age of post to be eligible for tweet - This allows you to set how old your post should be in order to be eligible for the tweet.
- Categories to omit from tweets - This will protect posts from the selected categories from being tweeted.


== Other Notes ==



**New in v4.0.9**

- Resolved twitter connectivity issue, for users who were not able to connect in 4.0.8. Twitter has changed their policy
as per https://dev.twitter.com/discussions/24239


**New in v4.0.8**

- Resolved twitter connectivity issue. Twitter has changed their policy
as per https://dev.twitter.com/discussions/24239


**New in v4.0.7**

- Resolved tweet not posting issue.


**New in v4.0.6**

- Changed how pages are navigated. Should not conflict with any of the plugin that interacts with twitter ever.
- For "Page not found", update the settings and then authorize with twitter.
- If you are not able to update anything or you are redirecting to your home page, reset the settings and try again.
- Code Cleanup.


**New in v4.0.5**

- Implemented Twitter API v1.1 as Twitter is retiring API v1 from 7th May.
- Handled conflict with BackWPup plugin.
- Some performance improvements with WPSuperCache plugin.
- Some design changes.
- Code Cleanup.


**New in v4.0.4**

- Resolved issue of tweet not posting automatically . Thanks to Daniel Lopez Gonzalez for helping me.
- Minor Fixes


**New in v4.0.3**

- Handled too many tweets when W3 Total Cache plugin is installed. Please check and let me know.
- Bug fixes


**New in v4.0.2**

- Removed the option to specify the consumer key and secret as twitter does not show the application from which its tweeted anymore.
- Most probably, the tweet not posting automatically issue is resolved. Please check and let me know.
- Bug fixes


**New in v4.0.1**

- Resolved issue of page getting blank after returning from twitter
- added pages to exclude post option
- Bug fixes
- updated the steps of creating twitter application check here http://www.ajaymatharu.com/major-update-to-tweet-old-post/


**New in v4.0.0**

- You can now change the application name. Change via Tweet Old Post to your specified name. Follow the Steps here,
http://www.ajaymatharu.com/major-update-to-tweet-old-post/
- Pages can now be included in tweets. Added an option to select what is to be tweeted (pages, posts, or both).
- Removed "." and used "-" when adding additional text, "." was causing grammatical mistakes if sentence was trimmed.
- Added option to specify number of posts that can be tweeted simultaneously. You can specify how many tweets you want at a time.
- Last but not the least, removed random time slot was causing lot of confusion.


**New in v3.3.3**

- Resolved permission issue of exclude post.


**New in v3.3.2**

- Resolved too many redirects issue
	If its still not working try these steps
		- Make sure "Tweet Old Post Admin URL (Current URL)" is showing your current URL.
		- Click on "Update Tweet Old Post Options".
		- Try to authorize again.
- Removed "_" from hashtags. Previously space in hashtag was replaced with "_". Now there will be no spaces or "_" in hashtags.


**New in v3.3.1**

- Changed logic for posting data to twitter.
- Resolved bit.ly issue.


**New in v3.3.0**

- Attempt to fix logs out issue (Tweet Old Post pushes out when any action is performed).


**New in v3.2.9**

- Option to reset setting. When something goes wrong, please reset the settings and setup again.
- For people still facing issues of conflict with Google Analytics Plugin, this version should work.
- Minor bug fixes.


**New in v3.2.8**

- Resolved conflict with Google Analytics Plugin.
- Changed the log file location to root of plugin folder.
- Maintained Tweet Cycle. Repeat only when all post have been tweeted.
- Made other optimizations and resolved some minor bugs.


**New in v3.2.7**

- Added logging for people who cant make it work can enable and check the log, or mail me the log file.
- Brought back the exclude post option.
- Made other optimizations and resolved some minor bugs.
- Check http://www.ajaymatharu.com/tweet-old-post-update-3-2-7/ for more detailed explanation.


**New in v3.2.6**

- removed exclude post due to security threat. Will work on it and bring it up back.


**New in v3.2.5**

- Resolved hashtag not posting issue.
- other bug fixes.


**New in v3.2.4**

- Bug fixes


**New in v3.2.3**

- Bug fixes


**New in v3.2.2**

- Resolved bit.ly issue
- new option for hashtags
- other bug fixes


**New in v3.2.1**

- Bug fixes


**New in v3.2**

- Bug fixes
- Option to choose to include link in post
- option to post only title or body or both title and body
- option to set additional text either at beginning or end of tweet
- option to pick hashtags from custom field


**New in v3.1.2**

- Resolved tweets not getting posted when categories are excluded.
- If you are not able to authorise your twitter account set you blog URL in Administration → Settings → General.


**New in v3.1**

- Resolved issue of plugin flooding twitter account with tweets.
- added provision to exclude some post from selected categories


**New in v3.0**

- added OAuth authentication
- user defined intervals
- may not work under php 4 requires php 5


**New in v2.0**

- added provision to select if you want to shorten the URL or not.
- Cleaned other options.


**New in v1.9**

- Removed PHP 4 support as it was creating problem for lot of people


**New in v1.8**

- Bug Fixes
- Provision to fetch tweet url from custom field


**New in v1.7**

- Removed api option from 1click.at not needed api key


**New in v1.6**

- Made the plugin PHP 4 compatible. Guys try it out and please let me know if that worked.
- Better error prompting. If your tweets are not appearing on twitter. Try "Tweet Now" button you'll see if there is any problem in tweeting.
- Added 1click.at shortning service you need to get the api key from http://theeasyapi.com/ you need to add your machine IP address in the server of http://theeasyapi.com/ for this api key to work.


**New in v1.5**

- Maximum age of post to be eligible for tweet - allows you to set Maximum age of the post to be eligible for tweet
- Added one more shortner service was looking for j.mp but they dont have the api yet.


**New in v1.4**

- Hashtags - allows you to set default hashtags for your tweets


**New in v1.3**

- URL Shortener Service - allows you to select which URL shortener service you want to use.


**New in v1.2**

- Tweet Prefix - Allows you to set prefix to the tweets.
- Add Data - Allows you to add post data to the tweets
- Tweet now - Button that will tweet at that moment without wanting you to wait for scheduled tweet


**v1.1**

- Twitter Username & Password - Using this twitter account credentials plugin will tweet.
- Minimum interval between tweets - allows you to determine how often the plugin will automatically choose and tweet a blog post for you.
- Randomness interval - This is a contributing factor in minimum interval so that posts are randomly chosen and tweeted from your blog.
- Minimum age of post to be eligible for tweet - This allows you to set how old your post should be in order to be eligible for the tweet.
- Categories to omit from tweets - This will protect posts from the selected categories from being tweeted.

