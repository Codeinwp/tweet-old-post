<div class="cwp_top_wrapper">
	<div class="announcement clearfix">
		<h2><?php _e("We have just released", CWP_TEXTDOMAIN); ?> <b>Reviewgine</b>, <?php _e("a pixel perfect WordPress Theme.", CWP_TEXTDOMAIN); ?></h2>
		<a class="show-me" href="#"><?php _e("Show Me", CWP_TEXTDOMAIN); ?></a>
	</div><!-- end .announcement -->

	<header id="cwp_top_header" class='clearfix'>
		<h1 class="logo"><?php _e("Tweet Old Post", CWP_TEXTDOMAIN); ?></h1>
		<span class="slogan"><?php _e("by", CWP_TEXTDOMAIN); ?> <a href="#">ReadyThemes</a></span>

		<div class="cwp_top_actions">
			<a href="#" class="tweet-about-it"><span></span> <?php _e("Tweet About It", CWP_TEXTDOMAIN); ?></a>
			<a href="#" class="leave-a-review"><span></span> <?php _e("Leave A Review", CWP_TEXTDOMAIN); ?></a>		
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
				<span class='nextTweet'><?php $this->getNextTweetInterval(); ?></span>
			</p>
		<?php } else { ?>
			<p class='inactive'>
				<?php _e("Tweet Old Post is not set to tweet!", CWP_TEXTDOMAIN); ?>
			</p>
		<?php } ?>
	

		</div><!-- end .cwp_top_status -->

		<form action="" method="post" id="cwp_top_form" class="clearfix">
			<input id="cwp_top_currenturl" type="hidden" value="">

			<fieldset class="option">
				<div class="left">
					<label for="twitter-login"> <?php _e("Twitter Login", CWP_TEXTDOMAIN); ?> </label>
					<span class='description'> <?php _e("Login using your Twitter account.", CWP_TEXTDOMAIN); ?> </span>
				</div><!-- end .left -->

				<div class='right'>
					<?php if($this->displayTwitterLoginButton()) { ?>
						<div class="user_details">
							<div class='user_avatar'><img src="<?php echo $this->user_info->profile_image_url; ?>"></div>
							<div class="user_name"><?php echo $this->user_info->name; ?></div>
							<div class="remove_user"><a href='#' class='logout_user'></a></div>
						</div><!-- end .user_details -->
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
				<a class="update-options" href="#"><span></span><?php _e("Update", CWP_TEXTDOMAIN); ?></a>
				<a class="reset-settings" href="#"><span></span> <?php  _e("Reset Settings", CWP_TEXTDOMAIN); ?></a>
				<a class="tweet-now" href="#"><span></span> <?php _e("Start Tweet", CWP_TEXTDOMAIN); ?></a>
				<a class="stop-tweet-old-post" href="#"><span></span> <?php _e("Stop Tweet", CWP_TEXTDOMAIN); ?></a>				
			</div><!-- end .cwp_top_footer -->
		</form><!-- end #cwp_top_form -->
		
		<aside class="sidebar">
			<ul>
				<li class="upgrade"><a href="#"> <?php _e("Upgrade Tweet Old Post for only 19$ - Upgrade To Pro Now!", CWP_TEXTDOMAIN); ?></a></li>
				<li class="readythemes"><a href="#"> <?php _e("ReadyThemes - Product Review WordPress Themes", CWP_TEXTDOMAIN); ?></a></li>
				<li class="affiliate-readythemes"><a href="#"> <?php _e("ReadyThemes - Promote Our Themes and Plugins and get 50% Comission", CWP_TEXTDOMAIN); ?></a></li>
			</ul>
		</aside><!-- end .sidebar -->
	</section><!-- end .cwp_top_container -->
</div><!-- end .cwp_top_wrapper -->