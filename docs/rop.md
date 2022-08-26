# Introduction

Revive Old Posts (ROP) is the one of the first plugins acquired by Themeisle. Before the acquisition the main purpose of the plugin was simply sharing WordPress posts to Twitter. Since then, it's received some new networks and in v8.0 received a major rewrite of it's code to bring it up to speed.

Since then, at around v8.x the plugin's active development and improvement has slowed and there are is lot of room for improvement.

Some of those things include:

### 1) Removal of the dead code that exists in the plugin

In previous versions of ROP, users were allowed to connect their own developer apps to the plugin and which ROP would then use to acquire the **access token** for sharing. 

During the Facebook Cambridge analytica saga, we realized that this was not the best way to go about things since facebook required every owner of an App to go through a tedious review process which most got declined for resuling in a loss of ~10k active installs.

From that incident we started getting our own Developer Apps from the social media networks, and allowing users to connect via app.revive.social to receive access tokens for posting.

The current released version of the plugin still has the code which handles users adding their own API key and secret, however, the fields aren't being shown and users only see a "Connect using [Social media network]" button:

![connect network image](images/connect-network.png)

Though some of ROP's networks had this functionality before (users being able to connect their own apps), the newer networks were added without that ability and users are only able to connect using our developer app via app.revive.social. These networks are:

- Google My Business
- Vkontakte

[A pull request](https://github.com/Codeinwp/tweet-old-post/pull/849) exists where most of the dead code relating to the old way of connecting apps have been removed. It has not yet been merged out of concerns that some users are still using the old method.

### 2) Remove or work on remote cron features

The ROP plugin has code to faciliate a remote cron system that we piloted but had to stop because of multiple issues and bugs. The code for this cron system still exists in the plugin but it's not currently doing anything. The goal was to create a system that would send out pings to user's websites to fire the ROP share instead of relying on their WP CRON thus enhancing the user experience for sites with low traffic. The accompanying plugin for this exists on app.revive.social and it as well is disabled.

### 3) Rewrite the Auth Service App

As mentioned previously, when a user connects their social media account to the ROP plugin, the request goes through app.revive.social where the tokens are created and sent back to the user's website where it is saved.

The plugin for facilitating this workflow exists on the app.revive.social website. 

The plugin was first created to only facilitate the connecting of Facebook accounts after the Cambridge Analytica scandal but grew afterwards to support all of the networks provided by ROP. The thing is, in it's current procedural programming form, it's not ideal. The plugin should ideally be rewritten and made more easily extendable.

[The repository can be found here.](https://github.com/Codeinwp/rop-auth-service)

### 4) Improve the Revive Network Plugin

Revive Network was a totally separate plugin introduced to the Revive Social lineup. However, it fell by the wayside as ROP gained far more popularity than it.

The plugin was reimagined and rewritten in [v2.0.0](https://github.com/Codeinwp/revive-network/pull/193) to work as an Add-on to ROP. 

It's main purpose now is to pull in items from RSS feeds, save them to the website as a Custom Post Type, and then in ROP those posts can be shared to social media by selecting the post type from the General Settings of ROP.

This introduced new opportunities for ROP to grow and bring out the idea of keeping social media followers engaged with not only content from the website owner but also any other website with an RSS feed.

### 5) Automatically refresh LinkedIn Tokens.

Right now a user needs to go back to their ROP dashboard and refresh the LinkedIn token after it has expired. This is because there was no way to automatically refresh a LinkedIn token automatically at the time the network was added to ROP.

This is now possible, but the current library we are using for LinkedIn, does not support it. There is a pull request available on the library to allow this, but the library looks abandoned: https://github.com/zoonman/linkedin-api-php-client/pull/56

Two possible solutions exist here:

1 - Use the a forked version of the library that has the refresh token capability. 
2 - Implement this manually with code and abandon using the library since it seems to be abandoned. We are already doing some LinkedIn API work manually in some parts of the ROP Auth Plugin.

An issue for this was created here: https://github.com/Codeinwp/tweet-old-post-pro/issues/397

# The Rundown

ROP works on the basis of creating a "queue" for each active social media account then grabbing posts (and/or custom post types) from the site's database, and filling the queue's timestamps with those posts.

When the cron job `rop_cron` hits, various logic is run to check if there are any posts to send out to the social media accounts.

This is the basic overview of how ROP works. 

In this doc, I will try to explain some of the plugin's logic to hopefully help you quicker get started with ROP development.

# Connecting Social Media Accounts to ROP

When the "Connect to [network]" button is clicked, a window pointing to the endpoint for authenticating that network opens on app.revive.social.

Some logic is ran to send the user to the login page of the social network where they can then authorize our Developer App to post content to their website.

The plugin responsible for this logic exists on the app.revive.social website and it's called "ROP Authentication Service" [The repository can be found here.](https://github.com/Codeinwp/rop-auth-service).

The files reponsible for this workflow are called `[shortname]_login.php`. Example, if the user tried to connect Facebook, then the file responsible for authenticating the user and sending back their `access token` to their website would be `fb_login.php`.

Once the authentication workflow has completed, the `access_token` (and possibly also a `refresh token` depending on the network) as well as the account details will be sent back to the user's website where ROP will be responsible for saving those details to the database.

The methods responsible for kickstarting the saving process exists in the `Rop_Rest_Api` class. In the example above, the method responsible for this would be `add_account_fb()`. This method calls the respective `add_account_with_app()` in the service file to setup the account details for saving to the database.

# Queuing Posts

Everytime the `rop_cron` scheduled event fires, the queue is built for every currently active social media account. Each account queue can hold 10 posts at a time, this is set by the constant: `EVENTS_PER_ACCOUNT`. 

By default the value for the `EVENTS_PER_ACCOUNT` constant is 10, so if any of the social media accounts do not have 10 posts in its queue at the time the schedule event fires, ROP will add the missing posts to meet that threshhold (logic explained further below). The class responsible for this is `Rop_Queue_Model`, particularly the `get_queue()` method. 

The queue is made up of the social media account IDs, the timestamps at which the share should happen, and the Post IDs that will be shared. A dump of this is visualized below:

![dump](images/queue_stack.png)

In the example above the **"Number of Posts"** option in **"General Settings"** of ROP was left at *1*. If the number was increased to *2*, then the "posts" array would contain contain two post IDs.

## Selecting Which Posts Get Queued

During the building of the queue, ROP selects posts from the database using a query that is built according to the options set in "General Settings" of the plugin dashboard. 

The class responsible for this is the `Rop_Posts_Selector_Model`, when the `select()` method runs(called by the `get_queue()` method), it does the following:

- Get the Post types that should be queried.
- Get the taxonomies (categories and tags) that should be included/excluded from the pool.
- Get the posts that have been excluded by the user using the "Exclude Posts" feature.
- Get posts that have already been shared.
- Query the database with the generated query to create the pool of post IDs that are eligible to be added (**randomly**) to the queue of the active social media accounts.

&nbsp;
### Below is a general overview of what happens when the "Start Sharing" button is first clicked
&nbsp;
&nbsp;

![overview](images/first_start_workflow.png)

# Sharing Posts

During the initial click of the "Start Sharing" button, ROP sets a scheduled event(cron job) called `rop_cron`, this scheduled event is fired **every 5 minutes**, and runs the method `rop_cron_job()` located in the `Rop_Admin` class. This is the method that kicks off the building of the queue. 

Once the queue has been built and the sharing is active, the `rop_cron` scheduled task will continue to be fired every 5 minutes.

If there are any timestamps in the past when the `rop_cron_job()` method is called, then ROP will start the process of sharing the post(s) in the queue to social media.

It will build the service object for the social media services and run the `share()` method located in every social media's service class file.

This `share()` method is what sends out the actual post share to social media and then returns a success or error message.

The `rop_cron_job()` method will also remove the shared post's ID(s) from the sharing queue as well as update the **Post Buffer** with the post ID(s) that were just shared. 

The next time the `rop_cron` event fires, the `get_queue()` method's logic will notice that the queue for the respective account no longer has 10 posts, so it will fill the missing slots with posts queried by the `Rop_Posts_Selector_Model::select()` method.

&nbsp;
### Below is an overview of the autopilot sharing process
&nbsp;
&nbsp;

![overview](images/sharing_workflow.png)

# Posts Buffer

The post buffer acts as a "bucket" for all the posts that have been shared to the social media accounts. It is used during the queue building process to also sift out posts that have already been shared so that the query to grab posts from the database does not grab post IDs that have already been shared; See `Rop_Posts_Selector_Model::build_exclude()`

# Miscellaneous

The ROP dashboard is built using VueJS. The saving of the options are done using the WP REST API. This means that if the WP REST API is not working on the user's website, or a security plugin is blocking access, then ROP settings would not be saved.

The class responsible for faciliating clientside interations with the server is `Rop_Rest_Api`
# Quirks

## Log error about no posts gets triggered repeatedly.

When no posts are available to be used by ROP, logs an error. Since the cron job for triggering shares fires every 5 minutes, this error also shows over and over in the log until posts become available:

![repeated errors](images/no-posts.png)

This error is triggered [here.](https://github.com/Codeinwp/tweet-old-post/blob/v9.0.9/includes/admin/models/class-rop-queue-model.php#L219)