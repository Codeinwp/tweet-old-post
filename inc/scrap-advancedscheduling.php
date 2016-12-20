	<?php 
        $networks   = array();
        $collect    = array();
        foreach($available  as $network_name) {
            $networks[] = $network_name;
        }
        if(count($networks) > 1){

            $networks[] = null;

        }
        $count      = 20;
        if(apply_filters('rop_is_business_user', false)){
            $count  = 100;
        }

        foreach($networks as $network){
            $all    = null;
            if(get_option("cwp_topnew_active_status", 'no') === 'yes'){
                $all    = array();
                if($network){
                    $posts      = CWP_TOP_Core::getTweetsFromDB($network, false, $count);
                    foreach($posts as $post){
                        $all[]  = array("post" => $post, "network" => $network);
                    }
                }else{
                    $all        = CWP_TOP_Core::sortPosts($collect);
                }
            }
    ?>

		<div class="tab-vertical <?php if(count($available) == 1 || !$network ){ ?> active  <?php } ?>">
        <?php if(!apply_filters('rop_is_business_user', false)): ?>
                <div class="rop-features-available"><p><span>Editing features are available on the <a href="<?php echo ROP_PRO_URL; ?>" target="_blank"><strong>Business version</strong></a></span></p></div>
        <?php endif; ?>
        <?php
            if(is_null($all)){
                    echo '<div class="rop-box-with-padding rop-message-tab">';
                        _e("Please start the plugin to view the future shares", "tweet-old-post");
                    echo '</div>';
                echo "</div>";
                continue;
            }
            if(count($all) == 0){
                    echo '<div class="rop-box-with-padding rop-message-tab">';
                        _e('There is no suitable post to tweet make sure you excluded correct categories and selected the right dates.','tweet-old-post');
                    echo '</div>';
                echo "</div>";
                continue;
            }

            $time               = null;
            $prevDate           = "";
            foreach ($all as $array){
                $post           = $array["post"];
                $network_name   = $array["network"];
                $finalTweet     = CWP_TOP_Core::generateTweetFromPost($post, $network_name, true);
                $time           = CWP_TOP_Core::getFutureTime($network_name, $time, $array);
                $image          = CWP_TOP_Core::getImageForPost($network_name, $post->ID);
                if($network){
                    $collect[]  = array("post" => $post, "network" => $network_name, "time" => $time);
                }

                $tweetTime      = date("g:i:s A", $time);
                $tweetDate      = date("j M Y", $time);

                $data_postID    = $post->ID . $network_name;

                if($tweetDate != $prevDate){
                    if($prevDate != ""){
        ?>
                </div>
        <?php
                    }
        ?>
                <div class="rop_date_container">
                    <div class="rop_tweet_date"><?php echo $tweetDate;?></div>
        <?php
                }
        ?>
					<fieldset class="option twp<?php echo $key; ?> cwp_restrict_image rop-post-<?php echo $network_name; ?>">
						<div class="rop_left">
                            <div class="cwp_post_image" data-post-id="<?php echo $post->ID;?>" data-network="<?php echo $network_name;?>">
                                <?php echo $image;?>
                            </div>
						</div><!-- end .left -->
						<div class="rop_right <?php echo !$image ? "cwp_extend_post" : ""?>">
                            <div class="cwp_post_message">
                                <span data-post="<?php echo $data_postID;?>" class="cwp-quick-edit-span">
                                    <?php echo $finalTweet['message'];?>
                                </span>
                                <textarea data-post="<?php echo $data_postID;?>" data-network="<?php echo $network_name;?>" class="cwp-quick-edit-text" style="display: none" rows="3"><?php echo $finalTweet['message'];?></textarea>
                            </div>
                            <div class="cwp_quick_edit clearfix">
                                <div class="cwp_quick_edit_actions">
                                    <span class="cwp_quick_edit_actions_after" style="display: none" data-post="<?php echo $data_postID;?>">
                                        <?php submit_button(__("Save", "tweet-old-post"), "primary", "cwp-quick-save-button-" . $data_postID, false, array("data-post" => $data_postID, "data-post-id" => $post->ID));?>
                                        <?php submit_button(__("Cancel", "tweet-old-post"), "secondary", "cwp-quick-cancel-button-" . $data_postID, false, array("data-post" => $data_postID));?>
                                    </span>
                                    <span class="cwp_quick_edit_actions_before" data-post="<?php echo $data_postID;?>">
                                        <a href="#" class="cwp-quick-edit" data-post="<?php echo $data_postID;?>"><?php _e("Edit", "tweet-old-post");?></a>
                                        <a href="#" class="cwp-quick-delete" data-post="<?php echo $data_postID;?>" data-post-id="<?php echo $post->ID;?>" data-network="<?php echo $network_name;?>"><?php _e("Delete", "tweet-old-post");?></a>
                                        <a href="#TB_inline?width=400&height=150&inlineId=cwp_delete_thickbox" id="thickbox-<?php echo $data_postID;?>" class="thickbox"></a>
                                    </span>
                                </div>
                                <div class="cwp_post_time">
                                    <?php echo $tweetTime;?>
                                </div>
                            </div>
						</div><!-- end .right -->
					</fieldset><!-- end .option -->
            <?php
                $prevDate   = $tweetDate;
            }
            ?>
            </div>
		</div>
		<?php
            }
        ?>
