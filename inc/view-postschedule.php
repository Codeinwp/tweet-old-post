<?php
	global $cwp_top_global_schedule;

?><div class="cwp_top_tabs_vertical">
	<ul class="cwp_top_tabs_btns">
		<?php $first = true; foreach($all_networks  as $network_name ) { ?>
			<li class="<?php if($first){ ?>active <?php } ?>    " ><?php echo $network_name; ?></li>
			<?php $first = false; } ?>

	</ul>

	<?php $first = true; foreach($all_networks  as $network_name ) { ?>

		<div class="tab-vertical <?php if($first){ ?> active  <?php } ?>" data-network="<?php echo $network_name; ?>">
					 <input type="hidden" value="<?php echo @$cwp_top_global_schedule[$network_name.'_schedule_type_selected'];?>" id="<?php echo $network_name.'_schedule_type_selected' ?>" name="<?php echo $network_name.'_schedule_type_selected' ?>" />
			<input type="hidden" value="<?php echo isset($cwp_top_global_schedule[$network_name.'_top_opt_interval']['days']) ? $cwp_top_global_schedule[$network_name.'_top_opt_interval']['days'] : '1,2,3,4,5,6,7'; ?>" id="<?php echo $network_name.'_top_schedule_days' ?>" name="<?php echo $network_name.'_top_schedule_days'; ?>" />
					 <div class="cwp-according-item cwp-populate-hidden-radio   <?php if( $cwp_top_global_schedule[$network_name.'_schedule_type_selected'] == 'each'): ?> active <?php endif; ?>" data-hidden="<?php echo $network_name.'_schedule_type_selected' ?>" data-value="each" >
						 <div class="cwp-according-header  cwp-populate-hidden-radio" data-hidden="<?php echo $network_name.'_schedule_type_selected' ?>"  data-value="each">
								<span> <?php _e("Each x hours", CWP_TEXTDOMAIN);  ?></span>
						 </div>
						 <div class="cwp-according-body">
							<fieldset class="option twpinterval"  >
								<div class="left">
									<label for="<?php echo $network_name;?>_top_opt_interval"> <?php _e( "Interval", CWP_TEXTDOMAIN ); ?> </label>
									<span class="description"> <?php _e('Minimum time between shares (Hour/Hours), 0.4 can be used also.', CWP_TEXTDOMAIN)?> </span>

								</div><!-- end .left -->
								<div class="right">

									<?php CWP_TOP_Core::generateFieldType(array(
												'id'			=> '14',
												'name'			=> __('Minimum interval between shares', CWP_TEXTDOMAIN),
												'type'			=> 'text',
												'slug'			=> 'interval',
												'option_value'			=>($cwp_top_global_schedule[$network_name.'_schedule_type_selected'] == 'each') ? $cwp_top_global_schedule[$network_name.'_top_opt_interval'] : '8',
				"available_pro"         => "yes",
												'option'		=> $network_name.'_top_opt_interval',
												'description'	=> __('Minimum time between shares (Hour/Hours), 0.4 can be used also.', CWP_TEXTDOMAIN),
												'options'		=> array()
										)); ?>
								</div><!-- end .right -->
							</fieldset><!-- end .option -->

						 </div>

					 </div>
					 <div class="cwp-according-item <?php if(   $cwp_top_global_schedule[$network_name.'_schedule_type_selected'] == 'custom'): ?> active <?php endif; ?>" >
						 <div class="cwp-according-header cwp-populate-hidden-radio" data-hidden="<?php echo $network_name.'_schedule_type_selected' ?>"  data-value="custom">

								<span><?php _e("Custom Schedule", CWP_TEXTDOMAIN);  ?></span>
						 </div>
						 <div class="cwp-according-body">
								 <div class="cwp-custom-schedule-days">

									 <ul class="cwp-schedule-days clearfix cwp-populate-hidden-checkbox-group" data-hidden="<?php echo $network_name.'_top_schedule_days' ?>" >
										 <li class=" cwp-populate-hidden-checkbox"   data-value="1"><?php _e("Monday", CWP_TEXTDOMAIN);  ?></li>
										 <li class=" cwp-populate-hidden-checkbox"  data-value="2"><?php _e("Tuesday", CWP_TEXTDOMAIN);  ?></li>
										 <li class=" cwp-populate-hidden-checkbox" data-value="3"><?php _e("Wednesday", CWP_TEXTDOMAIN);  ?></li>
										 <li class="  cwp-populate-hidden-checkbox" data-value="4"><?php _e("Thursday", CWP_TEXTDOMAIN);  ?></li>
										 <li class="  cwp-populate-hidden-checkbox" data-value="5"><?php _e("Friday", CWP_TEXTDOMAIN);  ?></li>
										 <li class="  cwp-populate-hidden-checkbox" data-value="6"><?php _e("Saturday", CWP_TEXTDOMAIN);  ?></li>
										 <li class="  cwp-populate-hidden-checkbox" data-value="7"><?php _e("Sunday", CWP_TEXTDOMAIN);  ?></li>
									 </ul>
									 <p class="cwp-posts-time-info"><?php _e("Posts this "); ?><b class="cwp-posts-time-info-times"></b> <?php _e(" times every"); ?> <b class="cwp-posts-time-info-days"></b></p>
									<ul class="cwp-posting-times" data-times='<?php echo isset($cwp_top_global_schedule[$network_name.'_top_opt_interval']['times']) ? json_encode($cwp_top_global_schedule[$network_name.'_top_opt_interval']['times']) : "[]";?>'>


									</ul>
									 <P><A  href="#" class="cwp-btn cwp-primary cwp-add-posting-time"><?php _e("Add posting time",CWP_TEXTDOMAIN); ?></a></P>
								 </div>
							 	<?php
							    	if(!CWP_TOP_PRO){
								    	echo CWP_TOP_PRO_STRING;
								    }
							    ?>
						 </div>

					 </div>
		</div>
		<?php $first = false; } ?>


</div>

