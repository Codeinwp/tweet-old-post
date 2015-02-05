<input id="cwp_top_currenturl" type="hidden" value="<?php echo CURRENTURL; ?>">

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


			<fieldset class="option pro-ver">
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


			<fieldset class="option pro-ver">
				<div class="left">
					<label for="googleplus-login"> <?php _e("Google Plus Login", CWP_TEXTDOMAIN); ?> </label>
					<span class='description'> <?php _e("Login using your Google plus account.", CWP_TEXTDOMAIN); ?> </span>
				</div><!-- end .left -->

				<div class='right'>
					<?php

					$lk_users = 0;

					if(!empty($this->users))
						foreach ($this->users as $user) {
							if ($user['service']=='googleplus') {
								$lk_users++;
								?>
								<div class="user_details">
							<div class='user_avatar'><img src="<?php echo $user['oauth_user_details']->profile_image_url; ?>"></div>
							<div class="user_name"><?php echo $user['oauth_user_details']->name; ?></div>
							<div class="remove_user"><a href='#' id='<?php echo $user['user_id']; ?>' service='googleplus' class='logout_user'></a></div>
						</div><!-- end .user_details -->
							<?php  } }
					if ($lk_users!==0){ ?>
						<button id='googleplus-login' class='another-account login' service='googleplus'>+</button>
					<?php } else { ?>
						<button id='googleplus-login' class='login' service='googleplus'> <?php _e("Add account", CWP_TEXTDOMAIN); ?> </button>
					<?php } ?>
				</div><!-- end .right -->
			</fieldset><!-- end .option -->


			<fieldset class="option pro-ver">
				<div class="left">
					<label for="xing-login"> <?php _e("XING Login", CWP_TEXTDOMAIN); ?> </label>
					<span class='description'> <?php _e("Login using your XING account.", CWP_TEXTDOMAIN); ?> </span>
				</div><!-- end .left -->

				<div class='right'>
					<?php

					$lk_users = 0;

					if(!empty($this->users))
						foreach ($this->users as $user) {
							if ($user['service']=='xing') {
								$lk_users++;
								?>
								<div class="user_details">
							<div class='user_avatar'><img src="<?php echo $user['oauth_user_details']->profile_image_url; ?>"></div>
							<div class="user_name"><?php echo $user['oauth_user_details']->name; ?></div>
							<div class="remove_user"><a href='#' id='<?php echo $user['user_id']; ?>' service='xing' class='logout_user'></a></div>
						</div><!-- end .user_details -->
							<?php  } }
					if ($lk_users!==0){ ?>
						<button id='xing-login' class='another-account login' service='xing'>+</button>
					<?php } else { ?>
						<button id='xing-login' class='login' service='xing'> <?php _e("Add account", CWP_TEXTDOMAIN); ?> </button>
					<?php } ?>
				</div><!-- end .right -->
			</fieldset><!-- end .option -->


			<fieldset class="option pro-ver">
				<div class="left">
					<label for="stumbleupon-login"> <?php _e("Stumbleupon Login", CWP_TEXTDOMAIN); ?> </label>
					<span class='description'> <?php _e("Login using your Stumbleupon account.", CWP_TEXTDOMAIN); ?> </span>
				</div><!-- end .left -->

				<div class='right'>
					<?php

					$lk_users = 0;

					if(!empty($this->users))
						foreach ($this->users as $user) {
							if ($user['service']=='stumbleupon') {
								$lk_users++;
								?>
								<div class="user_details">
							<div class='user_avatar'><img src="<?php echo $user['oauth_user_details']->profile_image_url; ?>"></div>
							<div class="user_name"><?php echo $user['oauth_user_details']->name; ?></div>
							<div class="remove_user"><a href='#' id='<?php echo $user['user_id']; ?>' service='stumbleupon' class='logout_user'></a></div>
						</div><!-- end .user_details -->
							<?php  } }
					if ($lk_users!==0){ ?>
						<button id='stumbleupon-login' class='another-account login' service='stumbleupon'>+</button>
					<?php } else { ?>
						<button id='stumbleupon-login' class='login' service='stumbleupon'> <?php _e("Add account", CWP_TEXTDOMAIN); ?> </button>
					<?php } ?>
				</div><!-- end .right -->
			</fieldset><!-- end .option -->


			<fieldset class="option pro-ver">
				<div class="left">
					<label for="tumblr-login"> <?php _e("Tumblr Login", CWP_TEXTDOMAIN); ?> </label>
					<span class='description'> <?php _e("Login using your Tumblr account.", CWP_TEXTDOMAIN); ?> </span>
				</div><!-- end .left -->

				<div class='right'>
					<?php

					$lk_users = 0;

					if(!empty($this->users))
						foreach ($this->users as $user) {
							if ($user['service']=='tumblr') {
								$lk_users++;
								?>
								<div class="user_details">
							<div class='user_avatar'><img src="<?php echo $user['oauth_user_details']->profile_image_url; ?>"></div>
							<div class="user_name"><?php echo $user['oauth_user_details']->name; ?></div>
							<div class="remove_user"><a href='#' id='<?php echo $user['user_id']; ?>' service='tumblr' class='logout_user'></a></div>
						</div><!-- end .user_details -->
							<?php  } }
					if ($lk_users!==0){ ?>
						<button id='tumblr-login' class='another-account login' service='tumblr'>+</button>
					<?php } else { ?>
						<button id='tumblr-login' class='login' service='tumblr'> <?php _e("Add account", CWP_TEXTDOMAIN); ?> </button>
					<?php } ?>
				</div><!-- end .right -->
			</fieldset><!-- end .option -->