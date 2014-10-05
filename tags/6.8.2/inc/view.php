<div class="cwp_top_wrapper">
	<!--<div class="announcement clearfix">
		<h2><?php _e("After 6 months of hard work, we have just released", CWP_TEXTDOMAIN); ?> <b>ThemeIsle</b>, <?php _e("the island of WordPress themes.", CWP_TEXTDOMAIN); ?></h2>
		<a class="show-me" href="https://themeisle.com/?utm_source=topadmin&utm_medium=announce&utm_campaign=top"><?php _e("Show Me", CWP_TEXTDOMAIN); ?></a>
	</div> end .announcement -->

	<header id="cwp_top_header" class='clearfix'>
		<h1 class="top_logo">
			<?php if (function_exists('topProAddNewAccount')) {
				echo "Revive Old Post PRO";
			} else echo "Revive Old Post"; ?>

		</h1>
		<span class="slogan"><?php _e("by", CWP_TEXTDOMAIN); ?> <a href="https://themeisle.com/?utm_source=topadmin&utm_medium=announce&utm_campaign=top">ThemeIsle</a></span>

		<div class="cwp_top_actions">
			<a href="https://twitter.com/intent/tweet?text=Check-out%20this%20awesome%20plugin%20-%20&url=http%3A%2F%2Fthemeisle.com%2Fplugins%2Ftweet-old-post-lite%2F&via=themeisle" class="tweet-about-it"><span></span> <?php _e("Show your love", CWP_TEXTDOMAIN); ?></a>
			<a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/tweet-old-post#postform" class="leave-a-review"><span></span> <?php _e("Leave A Review", CWP_TEXTDOMAIN); ?></a>		
		</div><!-- end .cwp_top_actions -->
	</header><!-- end .cwp_top_header -->

	<section class="cwp_top_container clearfix">


		<div class="cwp_top_status">
		
		<?php if($this->pluginStatus == 'true') { ?>
			<p class='active'>
				<?php _e("Revive Old Post is set to post on a", CWP_TEXTDOMAIN); ?>
				<span class='tweetInterval'> <?php echo $this->intervalSet; ?></span>
				<?php _e("hours interval, ", CWP_TEXTDOMAIN); ?>
				<?php _e("and the next share will take place in: ", CWP_TEXTDOMAIN); ?>
				<span class='nextTweet'><?php $this->getNextTweetInterval(); update_option('cwp_topnew_notice', ""); ?></span>
			</p>
		<?php } else { ?>
			<p class='inactive'>
				<?php _e("Revive Old Post is not set to post!", CWP_TEXTDOMAIN); ?>
			</p>
		<?php } ?>
			<p class='inactive'>

				<?php _e("Once you click start sharing a post will be sent in 15 sec, also here you can see the error message if is any.", CWP_TEXTDOMAIN); $this->fixCron(); ?>
			</p>

		</div><!-- end .cwp_top_status -->

		<form action="" method="post" id="cwp_top_form" class="clearfix">
			<input id="cwp_top_currenturl" type="hidden" value="">

			<fieldset class="option">
				<div class="left">
					<label for="twitter-login"> <?php _e("Twitter Login", CWP_TEXTDOMAIN); ?> </label>
					<span class='description'> <?php _e("Login using your Twitter account.", CWP_TEXTDOMAIN); ?> </span>
				</div><!-- end .left -->

				<div class='right'>
					<?php
					

					$tw_users = 0;
					//$this->tweetPost(array("message"=>"Testinsd sg tpo","link"=>"http://wicked-stallion-64-120688.euw1.nitrousbox.com/"));
					//echo count($this->users);
				 	if(!empty($this->users))
					foreach ($this->users as $user) {
					if ($user['service']=='twitter'|| !isset($user['service'])) { 
						$tw_users++;
						?>
						<div class="user_details">
							<div class='user_avatar'><img src="<?php echo $user['oauth_user_details']->profile_image_url; ?>"></div>
							<div class="user_name"><?php echo $user['oauth_user_details']->name; ?></div>
							<div class="remove_user"><a href='#' id='<?php echo $user['user_id']; ?>' class='logout_user'></a></div>
						</div><!-- end .user_details -->
					<?php  } }
					if ($tw_users!==0) { ?>
						<button id='twitter-login' class='another-account login' service='twitter'>+</button>
					<?php } else { ?>
						<button id='twitter-login' class='login' service='twitter'> <?php _e("Add Account", CWP_TEXTDOMAIN) ?> </button>
					<?php } ?>
				</div><!-- end .right -->
			</fieldset><!-- end .option -->

			<fieldset class="option">
				<div class="left">
					<label for="facebook-login"> <?php _e("Facebook Login", CWP_TEXTDOMAIN); ?> </label>
					<span class='description'> <?php _e("Login using your Facebook account.", CWP_TEXTDOMAIN); ?> </span>
				</div><!-- end .left -->

				<div class='right'>
					<?php

					$fb_users = 0;
					
					if(!empty($this->users))
					foreach ($this->users as $user) {
					if ($user['service']=='facebook') { 
						$fb_users++;
						?>
						<div class="user_details">
							<div class='user_avatar'><img src="<?php echo $user['oauth_user_details']->profile_image_url; ?>"></div>
							<div class="user_name"><?php echo $user['oauth_user_details']->name; ?></div>
							<div class="remove_user"><a href='#' id='<?php echo $user['user_id']; ?>' service='facebook' class='logout_user'></a></div>
						</div><!-- end .user_details -->
					<?php  } }
					if ($fb_users!==0){ ?>
						<button id='facebook-login' class='another-account login' service='facebook'>+</button>
					<?php } else { 
						if (get_option('top_fb_token')===""||!get_option('top_fb_token')) {
						?>
						<button id='facebook-login' class='login' service='facebook'> <?php _e("Authorize", CWP_TEXTDOMAIN) ?> </button>
						<?php } else {?>
						<button id='facebook-login' class='login' service='facebook'> <?php _e("Add Account", CWP_TEXTDOMAIN) ?> </button>
					<?php }} ?>
				</div><!-- end .right -->
			</fieldset><!-- end .option -->

			<fieldset class="option">
				<div class="left">
					<label for="linkedin-login"> <?php _e("Linkedin Login", CWP_TEXTDOMAIN); ?> </label>
					<span class='description'> <?php _e("Login using your Linkedin account.", CWP_TEXTDOMAIN); ?> </span>
				</div><!-- end .left -->

				<div class='right'>
					<?php

					$lk_users = 0;
					
					if(!empty($this->users))
					foreach ($this->users as $user) {
					if ($user['service']=='linkedin') { 
						$lk_users++;
						?>
						<div class="user_details">
							<div class='user_avatar'><img src="<?php echo $user['oauth_user_details']->profile_image_url; ?>"></div>
							<div class="user_name"><?php echo $user['oauth_user_details']->name; ?></div>
							<div class="remove_user"><a href='#' id='<?php echo $user['user_id']; ?>' service='linkedin' class='logout_user'></a></div>
						</div><!-- end .user_details -->
					<?php  } }
					if ($lk_users!==0){ ?>
						<button id='linkedin-login' class='another-account login' service='linkedin'>+</button>
					<?php } else { ?>
						<button id='linkedin-login' class='login' service='linkedin'> <?php _e("Add account", CWP_TEXTDOMAIN); ?> </button>
					<?php } ?>
				</div><!-- end .right -->
			</fieldset><!-- end .option -->

			
			<?php foreach ($cwp_top_fields as $field) { ?>
					<fieldset class="option twp<?php echo $field['option']; ?>" >
						<div class="left">
							<label for="<?php echo $field['option']; ?>"> <?php _e($field['name'],CWP_TEXTDOMAIN); ?> </label>
							<span class="description"> <?php _e($field['description'],CWP_TEXTDOMAIN); ?> </span>

							<?php if($field['type'] == 'categories-list') { ?>
								<button class='select-all'><?php _e("Select All",CWP_TEXTDOMAIN);?></button>
							<?php } ?>

						</div><!-- end .left -->
						<div class="right">
							<?php CWP_TOP_Core::generateFieldType( $field ); ?>
						</div><!-- end .right -->
					</fieldset><!-- end .option -->
			<?php } ?>

			<div class="cwp_top_footer">
				<a class="reset-settings" href="#"><span></span> <?php  _e("Reset", CWP_TEXTDOMAIN); ?></a>
				<a class="update-options" href="#"><span></span><?php _e("Save", CWP_TEXTDOMAIN); ?></a>
				<a class="tweet-now" href="#"><span></span> <?php _e("Start Sharing", CWP_TEXTDOMAIN); ?></a>
				<a class="stop-tweet-old-post" href="#"><span></span> <?php _e("Stop Sharing", CWP_TEXTDOMAIN); ?></a>
				<a class="see-sample-tweet" href="#"><span></span> <?php _e("See Sample Post", CWP_TEXTDOMAIN); ?></a>				
			</div><!-- end .cwp_top_footer -->
			<p><?php _e("We are not affiliated or partner with Twitter/Facebook/Linkedin in any way.", CWP_TEXTDOMAIN); ?>
		</form><!-- end #cwp_top_form -->
		
		<aside class="sidebar">
			<ul>
				<li class="upgrade"><a target="_blank" href="https://themeisle.com/plugins/tweet-old-post-pro/?utm_source=bannerright&utm_medium=announce&utm_campaign=top&upgrade=true"> <?php _e("Upgrade Tweet Old Post for only $9.99 - Upgrade To Pro Now!", CWP_TEXTDOMAIN); ?></a></li>
				<li class="readythemes"><a target="_blank" href="https://themeisle.com/allthemes/?utm_source=bannerright&utm_medium=announce&utm_campaign=top"> <?php _e("ThemeIsle - Deadly Simple WordPress Themes", CWP_TEXTDOMAIN); ?></a></li>
				<li class="affiliate-readythemes"><a target="_blank" href="https://themeisle.com/contact/?utm_source=bannerright&utm_medium=announce&utm_campaign=top"> <?php _e("ThemeIsle - Promote Our Themes and Plugins and get 55% Comission", CWP_TEXTDOMAIN); ?></a></li>
			</ul>
		</aside><!-- end .sidebar -->
	</section><!-- end .cwp_top_container -->

	<div class="cwp_sample_tweet_preview">
		<div class="cwp_sample_tweet_preview_inner">
			<h2><?php _e('Sample Post Preview',CWP_TEXTDOMAIN);?></h2>
			<span class="sample_tweet">Lorem ipsum dolor sit amet consectetutem! <a href="#">Lorem ipsum</a></span>
			<button class="top_close_popup"><?php _e('Close preview',CWP_TEXTDOMAIN);?></button>
			<button class="tweetitnow"><?php _e('Share now',CWP_TEXTDOMAIN);?></button>
		</div><!-- end .cwp_sample_tweet_preview_inner -->
	</div><!-- end .cwp_sample_tweet_preview -->

	<div class="cwp_user_pages">
		<div class="cwp_sample_tweet_preview_inner">
			<h2><?php _e('Choose a Profile or Page',CWP_TEXTDOMAIN);?></h2>
			<div class="cwp_user_pages_inner"></div>
			<button class="top_close_popup"><?php _e('Close preview',CWP_TEXTDOMAIN);?></button>
					</div><!-- end .cwp_sample_tweet_preview_inner -->
	</div><!-- end .cwp_sample_tweet_preview -->

	<div class="cwp_fbapp_preview">
		<div class="cwp_sample_tweet_preview_inner top_auth_inner">
			<h2><?php _e('Add Your Facebook Account Following The Instructions On The Left',CWP_TEXTDOMAIN);?></h2>
			<span class="sample_tweet top_sample_auth">
				<div class="top_left_instructions">
				<ol>
					<li><?php _e('Go on',CWP_TEXTDOMAIN);?>  <a href="https://developers.facebook.com/apps/" target="_blank">developers.facebook.com/apps</a></li>
					<li><?php _e('Click on <strong>Create New App</strong> from the top right corner',CWP_TEXTDOMAIN);?> </li>
					<li><?php _e('Enter a <strong>Display Name</strong> and <strong>Namespace</strong> and click on Create App',CWP_TEXTDOMAIN);?> </li>
					<li><?php _e('Once you arrive on the app dashboard, copy your <strong>App ID</strong> and <strong>App Secret</strong> in the fields on the right',CWP_TEXTDOMAIN);?> </li>
					<li><?php _e('Go on Settings tab from the left sidebar menu add the contact email and click on <strong>Add Platform</strong> and select <strong>Website</strong>',CWP_TEXTDOMAIN);?> </li>
					<li><?php _e('Copy/Paste this url : <strong><?php echo SETTINGSURL; ?></strong> into App Domains and Site URL fields and <strong>Save</strong>',CWP_TEXTDOMAIN);?> </li>
					<li><?php _e('Go on Status & Review tab and set your app live from the top-right switch.',CWP_TEXTDOMAIN);?> </li>
					<li><?php _e('Now everything is done, click on <strong>Authorize App</strong> button.',CWP_TEXTDOMAIN);?> </li>
				</ol>
			</div>
			<form action="" method="post" id="cwp_top_form" class="clearfix top_auth_form">
			<fieldset class="option twptop_opt_app_id">
				<div class="left">
					<label for="top_opt_app_id"><?php _e('Facebook App ID',CWP_TEXTDOMAIN);?>   </label>
					<span class="description"><?php _e('ID from your app created on facebook website.',CWP_TEXTDOMAIN);?>   </span>

					
				</div><!-- end .left -->
				<div class="right">
					<input type="text" placeholder="1487991504767913" value="<?php get_option('cwp_top_app_id');?>" name="top_opt_app_id" id="top_opt_app_id">						
				</div><!-- end .right -->
			</fieldset>

			<fieldset class="option twptop_opt_app_secret">
				<div class="left">
					<label for="top_opt_app_secret"><?php _e('Facebook App Secret',CWP_TEXTDOMAIN);?>  </label>
					<span class="description"><?php _e('Secret from your app created on facebook website. ',CWP_TEXTDOMAIN);?> </span>
					
				</div><!-- end .left -->
				<div class="right">
					<input type="text" placeholder="5124ea6d46e64da3c306f12812d0e4fx" value="<?php get_option('cwp_top_app_secret');?>" name="top_opt_app_secret" id="top_opt_app_secret">						
				</div><!-- end .right -->
			</fieldset>

			<button class="top_authorize" service="facebook"><?php _e('Authorize App',CWP_TEXTDOMAIN);?></button>
			</form>
		</span><button class="top_close_popup"><?php _e('Close preview',CWP_TEXTDOMAIN);?></button>
		</div><!-- end .cwp_sample_tweet_preview_inner -->
	</div><!-- end .cwp_sample_tweet_preview -->

		<div class="cwp_lkapp_preview">
		<div class="cwp_sample_tweet_preview_inner top_auth_inner">
			<h2><?php _e('Add Your Linkedin Account Following The Instructions On The Left',CWP_TEXTDOMAIN);?></h2>
			<span class="sample_tweet top_sample_auth">
				<div class="top_left_instructions">
				<ol>
					<li><?php _e('Go on',CWP_TEXTDOMAIN);?> <a href="https://www.linkedin.com/secure/developer?newapp=" target="_blank">linkedin.com/secure/developer?newapp=</a></li>
					<li><?php _e('Enter the required details and pay special attention to the further fields :',CWP_TEXTDOMAIN);?></li>
					<li><?php _e('Make sure you set Live Status to LIVE',CWP_TEXTDOMAIN);?> </li>
					<li><?php _e('Default Scope should have r_basicprofile and rw_nus checked',CWP_TEXTDOMAIN);?></li>
					<li><?php _e('Copy/Paste this url : <strong><?php echo SETTINGSURL; ?></strong> into OAuth 2.0 Redirect URLs',CWP_TEXTDOMAIN);?></li>
					<li><?php _e('Once all required fields are filled click on Add Application button, get the API Key and Secret Key and paste them in the fields on the right',CWP_TEXTDOMAIN);?></li>
					
					<li><?php _e('Now everything is done, click on <strong>Authorize App</strong> button.',CWP_TEXTDOMAIN);?></li>
				</ol>
			</div>
			<form action="" method="post" id="cwp_top_form" class="clearfix top_auth_form">
			<fieldset class="option twptop_opt_app_id">
				<div class="left">
					<label for="top_opt_app_id"><?php _e('Linkedin API Key',CWP_TEXTDOMAIN);?>  </label>
					<span class="description"><?php _e('API Key that you get once you create an app',CWP_TEXTDOMAIN);?>  </span>

					
				</div><!-- end .left -->
				<div class="right">
					<input type="text" placeholder="1487991504767913" value="<?php get_option('cwp_top_lk_app_id');?>" name="top_opt_app_id" id="top_opt_app_id_lk">						
				</div><!-- end .right -->
			</fieldset>

			<fieldset class="option twptop_opt_app_secret">
				<div class="left">
					<label for="top_opt_app_secret"><?php _e('Linkedin Secret Key',CWP_TEXTDOMAIN);?>  </label>
					<span class="description"><?php _e('Secret Key that you get once you create an app',CWP_TEXTDOMAIN);?> </span>

					
				</div><!-- end .left -->
				<div class="right">
					<input type="text" placeholder="5124ea6d46e64da3c306f12812d0e4fx" value="<?php get_option('cwp_top_lk_app_id');?>" name="top_opt_app_secret" id="top_opt_app_secret_lk">						
				</div><!-- end .right -->
			</fieldset>

			<button class="top_authorize" service="linkedin"><?php _e('Authorize App',CWP_TEXTDOMAIN);?></button>
			</form>
		</span><button class="top_close_popup"><?php _e('Close preview',CWP_TEXTDOMAIN);?></button>
		</div><!-- end .cwp_sample_tweet_preview_inner -->
	</div><!-- end .cwp_sample_tweet_preview -->

</div><!-- end .cwp_top_wrapper -->