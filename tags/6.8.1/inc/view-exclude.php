 <?php $twp_obj = new CWP_TOP_Core; ?>
<div class="cwp_top_wrapper_full cwp_top_wrapper">
	<div class="announcement clearfix">
		<h2><?php _e("After 6 months of hard work, we have just released", CWP_TEXTDOMAIN); ?> <b>ThemeIsle</b>, <?php _e("the island of WordPress themes.", CWP_TEXTDOMAIN); ?></h2>
		<a class="show-me" href="https://themeisle.com/?utm_source=topadmin&utm_medium=announce&utm_campaign=top"><?php _e("Show Me", CWP_TEXTDOMAIN); ?></a>
	</div><!-- end .announcement -->

	<header id="cwp_top_header" class='clearfix'>
		<h1 class="top-logo" style="float:left">
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
		
		<?php if($twp_obj->pluginStatus == 'true') { ?>
			<p class='active'>
				<?php _e("Revive Old Post is set to share on a", CWP_TEXTDOMAIN); ?>
				<span class='tweetInterval'> <?php echo $twp_obj->intervalSet; ?></span>
				<?php _e("hours interval, ", CWP_TEXTDOMAIN); ?>
				<?php _e("and the next share will take place in: ", CWP_TEXTDOMAIN); ?>
				<span class='nextTweet'><?php $twp_obj->getNextTweetInterval(); update_option('cwp_topnew_notice', ""); ?></span>
			</p>
		<?php } else { ?>
			<p class='inactive'>
				<?php _e("Revive Old Post is not set to share!", CWP_TEXTDOMAIN); ?>
			</p>
		<?php } ?>
			<p class='inactive'>
				<?php _e("You have selected following POST IDs to be excluded from tweeting: <span id='excludeList' style='font-weight:bold;font-style:italic;'></span>.
Note: If you have made any change and dint hit 'Exclude Selected' button changes will not be saved. ", CWP_TEXTDOMAIN); ?>
			</p>

		</div><!-- end .cwp_top_status -->