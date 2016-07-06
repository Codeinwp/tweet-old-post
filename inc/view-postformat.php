<?php
	global $cwp_global_schedule;
	$cfgnets = $this->getAllNetworks(true);
	$networks = $this->getAvailableNetworks();
	if(empty($networks))$networks[] = "twitter";
?><div class="cwp_top_tabs_vertical <?php echo (count($networks) > 1) ? "rop-tab-with-sidebar" : "rop-tab-full-width"; ?>" >
	<?php if(count($networks) > 1): ?>
		<ul class="cwp_top_tabs_btns">
			<?php $first = true; ?>
			<?php foreach($networks  as $network_name) : ?>
				<li class="<?php if($first): ?>active<?php endif; ?>"><?php echo $network_name; ?></li>
				<?php $first = false; ?>
			<?php endforeach;?>
		</ul>
	<?php endif; ?>
	<?php $first = true; foreach($networks  as $network_name) { ?>

		<div class="tab-vertical <?php if($first){ ?> active  <?php } ?>">

				<?php foreach ($format_fields[$network_name] as $key=>$field) {
					$field['option'] = $network_name."_".$field['option'];
                ?>
					<fieldset class="option twp<?php echo $key; ?>" <?php if(isset($field['dependency'])) { ?> data-dependent='<?php echo json_encode($field['dependency']); ?>'  <?php } ?>>
						<div class="left">
							<label for="<?php echo $field['option']; ?>"> <?php _e( $field['name'], 'tweet-old-post' ); ?> </label>
							<span class="description"> <?php _e( $field['description'], 'tweet-old-post' ); ?> <?php if($field['type'] == 'number'):
						?>(<?php _e("Max",'tweet-old-post'); ?>: <?php echo $field['max-length']; ?>)<?php
						endif;?> </span>
						</div><!-- end .left -->
						<div class="right">
							<?php CWP_TOP_Core::generateFieldType( $field ); ?>
						</div><!-- end .right -->
					</fieldset><!-- end .option -->
				<?php } ?>
		</div>
		<?php $first = false; } ?>


</div>