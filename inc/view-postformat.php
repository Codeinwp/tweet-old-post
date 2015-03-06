<?php
	global $cwp_global_schedule;
	$cfgnets = $this->getAllNetworks(true);
?><div class="cwp_top_tabs_vertical">
	<ul class="cwp_top_tabs_btns">
		<?php $first = true; foreach($format_fields  as $network_name=>$network_details) { ?>
			<li <?php if($first){ ?>class="active" <?php }else{

				if($cfgnets[$network_name] && !CWP_TOP_PRO) echo 'class="pro-version"';

			} ?>  <?php ?>><?php echo $network_name; ?></li>

		<?php $first = false; } ?>

	</ul>

	<?php $first = true; foreach($format_fields  as $network_name=>$network_details) { ?>

		<div class="tab-vertical <?php if($first){ ?> active  <?php } ?>">

				<?php foreach ($network_details as $key=>$field) {
					$field['option'] = $network_name."_".$field['option'];
                ?>
					<fieldset class="option twp<?php echo $key; ?>" <?php if(isset($field['dependency'])) { ?> data-dependent='<?php echo json_encode($field['dependency']); ?>'  <?php } ?>>
						<div class="left">
							<label for="<?php echo $field['option']; ?>"> <?php _e( $field['name'], CWP_TEXTDOMAIN ); ?> </label>
							<span class="description"> <?php _e( $field['description'], CWP_TEXTDOMAIN ); ?> <?php if($field['type'] == 'number'):
						?>(<?php _e("Max",CWP_TEXTDOMAIN); ?>: <?php echo $field['max-length']; ?>)<?php
						endif;?> </span>

							<?php if($field['type'] == 'categories-list') { ?>
								<button class='select-all'><?php _e( "Select All", CWP_TEXTDOMAIN );?></button>
							<?php } ?>

						</div><!-- end .left -->
						<div class="right">
							<?php CWP_TOP_Core::generateFieldType( $field ); ?>
						</div><!-- end .right -->
					</fieldset><!-- end .option -->
				<?php } ?>
		</div>
		<?php $first = false; } ?>


</div>