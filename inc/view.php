<div class="cwp_top_wrapper">
	<div class="announcement clearfix">
		<h2><?php _e("After 6 months of hard work, we have just released", CWP_TEXTDOMAIN); ?> <b>ThemeIsle</b>, <?php _e("th’ island ‘o WordPress themes.", CWP_TEXTDOMAIN); ?></h2>
		<a class="show-me" href="http://themeisle.com/?utm_source=topadmin&utm_medium=announce&utm_campaign=top"><?php _e("Show Me", CWP_TEXTDOMAIN); ?></a>
	</div><!-- end .announcement -->

	<header id="cwp_top_header" class='clearfix'>
		<h1 class="logo">
			<?php if (function_exists('topProAddNewAccount')) {
				_e("Tweet Old Post PRO", CWP_TEXTDOMAIN);
			} else _e("Tweet Old Post", CWP_TEXTDOMAIN); ?>

		</h1>
		<span class="slogan"><?php _e("by", CWP_TEXTDOMAIN); ?> <a href="http://themeisle.com/?utm_source=topadmin&utm_medium=announce&utm_campaign=top">ThemeIsle</a></span>

		<div class="cwp_top_actions">
			<a href="https://twitter.com/intent/tweet?text=Check-out%20this%20awesome%20plugin%20-%20&url=http%3A%2F%2Fthemeisle.com%2Fplugins%2Ftweet-old-post-lite%2F&via=themeisle" class="tweet-about-it"><span></span> <?php _e("Show your love", CWP_TEXTDOMAIN); ?></a>
			<a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/tweet-old-post#postform" class="leave-a-review"><span></span> <?php _e("Leave A Review", CWP_TEXTDOMAIN); ?></a>		
		</div><!-- end .cwp_top_actions -->
	</header><!-- end .cwp_top_header -->

	<section class="cwp_top_container clearfix">


		<div class="cwp_top_status">
		
		<?php if($this->pluginStatus == 'true') { ?>
			<p class='active'>
				<?php _e("Tweet Old Post is set to tweet on a", CWP_TEXTDOMAIN); ?>
				<span class='tweetInterval'> <?php echo $this->intervalSet; ?></span>
				<?php _e("hours interval, ", CWP_TEXTDOMAIN); ?>
				<?php _e("and the next tweet will take place in: ", CWP_TEXTDOMAIN); ?>
				<span class='nextTweet'><?php $this->getNextTweetInterval(); update_option('cwp_topnew_notice', ""); ?></span>
			</p>
		<?php } else { ?>
			<p class='inactive'>
				<?php _e("Tweet Old Post is not set to tweet!", CWP_TEXTDOMAIN); ?>
			</p>
		<?php } ?>
			<p class='inactive'>
				<?php _e("Once you click start tweet a tweet will be sent in 15 sec, also here you can see the error message if is any.", CWP_TEXTDOMAIN); ?>
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
					<?php if(!empty($this->users)) { ?>


					<?php foreach ($this->users as $user) { ?>
						<div class="user_details">
							<div class='user_avatar'><img src="<?php echo $user['oauth_user_details']->profile_image_url; ?>"></div>
							<div class="user_name"><?php echo $user['oauth_user_details']->name; ?></div>
							<div class="remove_user"><a href='#' id='<?php echo $user['user_id']; ?>' class='logout_user'></a></div>
						</div><!-- end .user_details -->
					<?php  } ?>
						<button id='twitter-login' class='another-account'>+</button>
					<?php } else { ?>
						<button id='twitter-login'> <?php _e("Add Account", CWP_TEXTDOMAIN) ?> </button>
					<?php } ?>
				</div><!-- end .right -->
			</fieldset><!-- end .option -->

			
			<?php foreach ($cwp_top_fields as $field) { ?>
					<fieldset class="option">
						<div class="left">
							<label for="<?php echo $field['option']; ?>"> <?php echo $field['name'] ?> </label>
							<span class="description"> <?php echo $field['description']; ?> </span>

							<?php if($field['type'] == 'categories-list') { ?>
								<button class='select-all'>Select All</button>
							<?php } ?>

						</div><!-- end .left -->
						<div class="right">
							<?php CWP_TOP_Core::generateFieldType( $field ); ?>
						</div><!-- end .right -->
					</fieldset><!-- end .option -->
			<?php } ?>

			<div class="cwp_top_footer">
				<a class="update-options" href="#"><span></span><?php _e("Save", CWP_TEXTDOMAIN); ?></a>
				<a class="reset-settings" href="#"><span></span> <?php  _e("Reset Settings", CWP_TEXTDOMAIN); ?></a>
				<a class="tweet-now" href="#"><span></span> <?php _e("Start Tweet", CWP_TEXTDOMAIN); ?></a>
				<a class="stop-tweet-old-post" href="#"><span></span> <?php _e("Stop Tweet", CWP_TEXTDOMAIN); ?></a>
				<a class="see-sample-tweet" href="#"><span></span> <?php _e("See Sample Tweet", CWP_TEXTDOMAIN); ?></a>				
			</div><!-- end .cwp_top_footer -->
		</form><!-- end #cwp_top_form -->
		
		<aside class="sidebar">
			<ul>
				<li class="upgrade"><a target="_blank" href="http://themeisle.com/plugins/tweet-old-post-pro/?utm_source=bannerright&utm_medium=announce&utm_campaign=top&upgrade=true"> <?php _e("Upgrade Tweet Old Post for only $9.99 - Upgrade To Pro Now!", CWP_TEXTDOMAIN); ?></a></li>
				<li class="readythemes"><a target="_blank" href="http://themeisle.com/allthemes/?utm_source=bannerright&utm_medium=announce&utm_campaign=top"> <?php _e("ThemeIsle - Deadly Simple WordPress Themes", CWP_TEXTDOMAIN); ?></a></li>
				<li class="affiliate-readythemes"><a target="_blank" href="http://themeisle.com/contact/?utm_source=bannerright&utm_medium=announce&utm_campaign=top"> <?php _e("ThemeIsle - Promote Our Themes and Plugins and get 55% Comission", CWP_TEXTDOMAIN); ?></a></li>
			</ul>
		</aside><!-- end .sidebar -->
	</section><!-- end .cwp_top_container -->

	<div class="cwp_sample_tweet_preview">
		<div class="cwp_sample_tweet_preview_inner">
			<h2>Sample Tweet Preview</h2>
			<span class="sample_tweet">Lorem ipsum dolor sit amet consectetutem! <a href="#">Lorem ipsum</a></span>
			<button>Ok</button>
		</div><!-- end .cwp_sample_tweet_preview_inner -->
	</div><!-- end .cwp_sample_tweet_preview -->
</div><!-- end .cwp_top_wrapper -->