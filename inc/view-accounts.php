<input id="cwp_top_currenturl" type="hidden" value="<?php echo CURRENTURL; ?>" data-cnetwork="<?php echo  CWP_TOP_Core::getCurrentNetwork(); ?>">
<?php
	$networks = $this->getAllNetworks(true);
	foreach($networks as $an => $pro){

		?>

		<fieldset class="option <?php if($pro !==  CWP_TOP_PRO):  ?> pro-ver <?php endif; ?> ">
				<div class="left">
					<label for="<?php echo $an; ?>-login"> <?php _e(ucwords($an)." Login", CWP_TEXTDOMAIN); ?> </label>
					<span class='description'> <?php _e("Login using your ".ucwords($an)." account.", CWP_TEXTDOMAIN); ?> </span>
				</div><!-- end .left -->

				<div class='right'>
					<?php
					$tw_users = 0;
					if(!empty($this->users))
						foreach ($this->users as $user) {
							if ($user['service']==$an|| !isset($user['service'])) {
								$tw_users++;
								?>
								<div class="user_details">
							<div class='user_avatar'><img src="<?php echo $user['oauth_user_details']->profile_image_url; ?>"></div>
							<div class="user_name"><?php echo $user['oauth_user_details']->name; ?></div>
							<div class="remove_user"><a href='#' id='<?php echo $user['user_id']; ?>' class='logout_user'></a></div>
						</div><!-- end .user_details -->
							<?php  } }
					if ($tw_users!==0) { ?>
						<button id='<?php echo $an; ?>-login' class='another-account login <?php if(! CWP_TOP_PRO) echo 'pro-only'?>' service='<?php echo $an; ?>'>+</button>
					<?php } else { ?>
						<button id='<?php echo $an; ?>-login' class='login  <?php $this->checkVersion(); ?> <?php if($pro &&  !CWP_TOP_PRO) echo 'pro-only'?>' service='<?php echo $an; ?>'> <?php _e("Add Account", CWP_TEXTDOMAIN) ?> </button>
					<?php } ?>
				</div><!-- end .right -->
			</fieldset><!-- end .option -->

	<?php
	}
?>

