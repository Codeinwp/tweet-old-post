 <?php
	global $cwp_top_networks;
    $remote_check = get_option("cwp_rop_remote_trigger");
    if($remote_check === false) $remote_check = "off";
?>
<div class="cwp_top_wrapper">
	<!--<div class="announcement clearfix">
		<h2><?php _e("After 6 months of hard work, we have just released", CWP_TEXTDOMAIN); ?> <b>ThemeIsle</b>, <?php _e("the island of WordPress themes.", CWP_TEXTDOMAIN); ?></h2>
		<a class="show-me" href="https://themeisle.com/?utm_source=topadmin&utm_medium=announce&utm_campaign=top"><?php _e("Show Me", CWP_TEXTDOMAIN); ?></a>
	</div> end .announcement -->

	<header id="cwp_top_header" class='clearfix'>
		<h1 class="top_logo">
			<?php if (CWP_TOP_PRO) {
				echo "Revive Old Post PRO";
			} else echo "Revive Old Post"; ?>

		</h1>
		<span class="slogan"><?php _e("by", CWP_TEXTDOMAIN); ?> <a href="https://themeisle.com/?utm_source=topadmin&utm_medium=announce&utm_campaign=top">ThemeIsle</a></span>

		<div class="cwp_top_actions">
			<span class="cwp-remote-check-info">Remote check</span> <a class="on-off-button <?php echo $remote_check; ?>" id="cwp_remote_check"></a>
			<a href="https://twitter.com/intent/tweet?text=Check-out%20this%20awesome%20plugin%20-%20&url=http%3A%2F%2Fthemeisle.com%2Fplugins%2Ftweet-old-post-lite%2F&via=themeisle" class="tweet-about-it"><span></span> <?php _e("Show your love", CWP_TEXTDOMAIN); ?></a>
			<a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/tweet-old-post#postform" class="leave-a-review"><span></span> <?php _e("Leave A Review", CWP_TEXTDOMAIN); ?></a>
		</div><!-- end .cwp_top_actions -->
	</header><!-- end .cwp_top_header -->

	<section class="cwp_top_container clearfix">


		<div class="cwp_top_status">

		<?php if($this->pluginStatus != 'true') {  ?>
			<p class='inactive'>
				<?php _e("Revive Old Post is not set to post!", CWP_TEXTDOMAIN); ?>
			</p>
		<?php } ?>
			<p class='inactive cwp-error-label inactive-rop-error-label'>

				<?php
					_e("Here you can see errors and notifications if they exist.", CWP_TEXTDOMAIN);

				?>
			</p>
			<p class='active-rop-error-label cwp-error-label'>

			</p>

		</div><!-- end .cwp_top_status -->
		<div id="cwp-top-container-form" class="clearfix">
		<div id="cwp_top_tabs" class="clearfix">
			<ul id="tabs_menu">
				<li class="active" > <?php _e('Accounts',CWP_TEXTDOMAIN); ?> </li>
				<li > <?php _e('General settings',CWP_TEXTDOMAIN); ?> </li>
				<li ><?php _e('Post Format',CWP_TEXTDOMAIN); ?></li>
				<li <?php if(!CWP_TOP_PRO): ?> class="pro-version" <?php endif; ?>><?php _e('Custom Schedule',CWP_TEXTDOMAIN); ?></li>
				<li class="rop-error-log"><span class="no-error"> </span></span><?php _e('Log',CWP_TEXTDOMAIN); ?></li>

			</ul>

				<form action="" method="post" id="cwp_top_form" class="clearfix">


			<div class="tab  active"><?php  require_once(ROPPLUGINPATH."/inc/view-accounts.php"); ?></div>
					<div class="tab">


			<?php foreach ($cwp_top_fields as $field) {

				if(CWP_TOP_PRO){
					if(isset($field['available_pro'])){
						if($field['available_pro'] == 'no')
							continue ;

					}

				}

				?>

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


			</div>

			<div class="tab"><?php  require_once(ROPPLUGINPATH."/inc/view-postformat.php"); ?></div>
			<div class="tab"><?php  require_once(ROPPLUGINPATH."/inc/view-postschedule.php"); ?></div>
			<div class="tab clearfix">
				<div class="rop-log-container clearfix">

					<a href="#" id="rop-clear-log"><?php _e('Clear Log',CWP_TEXTDOMAIN); ?></a>
					<ul id="rop-log-list">


					</ul>
				</div>
			</div>
			<!-- end #cwp_top_form -->
		</div><div class="cwp_top_footer">
				<a class="reset-settings" id="reset-settings" href="#"><span></span> <?php  _e("Reset", CWP_TEXTDOMAIN); ?></a>
				<a class="update-options" id="update-options"href="#"><span></span><?php _e("Save", CWP_TEXTDOMAIN); ?></a>
				<?php if($this->pluginStatus != 'true' ): ?>

					<a class="tweet-now" id="tweet-now" href="#"><span></span> <?php _e("Start Sharing", CWP_TEXTDOMAIN); ?></a>
				<?php else: ?>

					<a class="stop-tweet-old-post" id="stop-tweet-old-post" href="#"><span></span> <?php _e("Stop Sharing", CWP_TEXTDOMAIN); ?></a>

				<?php endif; ?>
				<a class="see-sample-tweet" id="see-sample-tweet" href="#"><span></span> <?php _e("See Sample Post", CWP_TEXTDOMAIN); ?></a>
			</div><!-- end .cwp_top_footer -->
			<p><?php _e("We are not affiliated or partner with Twitter/Facebook/Linkedin in any way.", CWP_TEXTDOMAIN); ?></p>
				</form></div>
		<aside class="sidebar">
			<ul>
				<li class="rop-twitter-clock" data-current="<?php echo $this->getTime(); ?>"><?php echo __("Now is: ",CWP_TEXTDOMAIN)." <b> </b> " ?></li>
				<?php
				if($this->pluginStatus == 'true' ): ?>
						<?php
							foreach($cwp_top_networks  as $nn=>$nd) {
								if(wp_next_scheduled($nn.'roptweetcron',array($nn)) === false) continue;
					     ?>
						<li class="rop-twitter-countdown rop-network-countdown"><?php echo __("Revive Old Post will post on",CWP_TEXTDOMAIN)." <span class='rop-network-name'>".$nn."</span> ".__("in the next",CWP_TEXTDOMAIN); ?>: <span data-timestamp="<?php echo  wp_next_scheduled($nn.'roptweetcron',array($nn)) ; ?>" class='rop-network-timestamp'></span></li>
				<?php } ?>

				<?php endif; ?>
				<li class="upgrade"><a target="_blank" href="https://themeisle.com/plugins/tweet-old-post-pro/?utm_source=bannerright&utm_medium=announce&utm_campaign=top&upgrade=true"> <?php _e("Upgrade Tweet Old Post for only $9.99 - Upgrade To Pro Now!", CWP_TEXTDOMAIN); ?></a></li>
				<li class="sib"><a target="_blank" href="https://www.sendinblue.com/?utm_source=rop&utm_medium=display&utm_campaign=iplugins&utm_content=free"> <?php _e("ThemeIsle - Deadly Simple WordPress Themes", CWP_TEXTDOMAIN); ?></a></li>
				<li class="affiliate-readythemes"><a target="_blank" href="https://themeisle.com/contact/?utm_source=bannerright&utm_medium=announce&utm_campaign=top"> <?php _e("ThemeIsle - Promote Our Themes and Plugins and get 55% Comission", CWP_TEXTDOMAIN); ?></a></li>
			</ul>
		</aside><!-- end .sidebar -->
	</section><!-- end .cwp_top_container -->

	<div class="cwp_sample_tweet_preview">
		<div class="cwp_sample_tweet_preview_inner">
			<h2><?php _e('Sample Post Preview',CWP_TEXTDOMAIN);?> Twitter</h2>
			<span class="sample_tweet sample_tweet_twitter"></span>
			<h2><?php _e('Sample Post Preview',CWP_TEXTDOMAIN);?> Facebook</h2>
			<span class="sample_tweet sample_tweet_facebook"></span>
			<h2><?php _e('Sample Post Preview',CWP_TEXTDOMAIN);?>   LinkedIn</h2>
			<span class="sample_tweet sample_tweet_linkedin"></span>
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
					<li><?php printf(__('Copy/Paste this url : <strong>%s</strong> into App Domains and Site URL fields and <strong>Save</strong>',CWP_TEXTDOMAIN),top_settings_url());?> </li>
					<li><?php _e('Go on Status & Review tab and set your app live from the top-right switch.',CWP_TEXTDOMAIN);?> </li>
					<li><?php _e('Now everything is done, click on <strong>Authorize App</strong> button.',CWP_TEXTDOMAIN);?> </li>
				</ol>
			</div>
			<form action="" method="post" id="cwp_top_form" class="  top_auth_form">
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
					<li><?php printf(__('Copy/Paste this url : <strong>%s</strong> into OAuth 2.0 Redirect URLs field and <strong>Save</strong>',CWP_TEXTDOMAIN),top_settings_url());?> </li>
					<li><?php _e('Once all required fields are filled click on Add Application button, get the API Key and Secret Key and paste them in the fields on the right',CWP_TEXTDOMAIN);?></li>

					<li><?php _e('Now everything is done, click on <strong>Authorize App</strong> button.',CWP_TEXTDOMAIN);?></li>
				</ol>
			</div>
			<form action="" method="post" id="cwp_top_form" class="  top_auth_form">
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
