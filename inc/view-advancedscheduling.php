<?php
	$cfgnets = $this->getAllNetworks(true);
    $available  = $this->getAvailableNetworks();
    if(empty($available)) $available[] = "twitter";
?>
<div class="cwp_top_tabs_vertical <?php echo (count($available) > 1) ? "rop-tab-with-sidebar" : "rop-tab-full-width"; ?> ">
    <?php
    if( count( $available ) > 1) :  ?>
		<ul class="cwp_top_tabs_btns">
			<?php
				foreach($available  as $network_name) : ?>
					<li class="<?php if(count($available) == 1){ ?>active<?php } ?>"><?php echo $network_name; ?></li>
				 <?php endforeach;  ?>
	        <?php if(count($available) > 1): ?>
					<li class="active"><?php _e("All", "tweet-old-post"); ?></li>
	        <?php endif; ?>
		</ul>
	<?php endif; ?>
    <div id="cwp_delete_thickbox" style="display: none">
        <table class="cwp_delete_table">
            <tr>
                <th align="center"><h3><?php _e("How should the DELETE button work for you henceforth?", "tweet-old-post");?></h3></th>
            </tr>
            <tr>
                <th>
                    <input type="radio" name="delete" value="1" id="rop_delete1">
                    <label for="delete1"><?php _e("Exclude this post from publishing only on this network", "tweet-old-post");?></label>
                </th>
            </tr>
        <?php if(count($available) > 1){ ?>
            <tr>
                <th>
                    <input type="radio" name="delete" value="0" id="rop_delete0">
                    <label for="delete0"><?php _e("Exclude this post from publishing on all networks", "tweet-old-post");?></label>
                </th>
            </tr>
        <?php } ?>
            <tr>
                <th>
                    <input type="radio" name="delete" value="2" id="rop_delete2">
                    <label for="delete2"><?php _e("Just remove the post from schedule now", "tweet-old-post");?></label>
                </th>
            </tr>
        </table>

        <input type="hidden" id="rop-delete-type" value="<?php echo get_option("cwp_top_delete_type", -1);?>">
    </div>

    <div id="rop-queue"></div>
</div>