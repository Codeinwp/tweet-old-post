<?php

require_once(ROPPLUGINPATH.'/tweet-old-post.php');
require_once(ROPPLUGINPATH.'/inc/xml.php');

function rop_exclude_posts() {

    if (current_user_can('manage_options'))
        {

            global $wpdb;
            $message_updated = __("Tweet Old Post Options Updated.", 'TweetOldPost');
            $records_per_page = 20;
            $twp_obj = new CWP_TOP_Core;
            $paged = isset($_POST['paged']) ? $_POST['paged'] : 1;
            $postTypes = $twp_obj->getTweetPostType();
            $postTypes = explode(',',$postTypes);
            $selected_post_type = isset($_POST['rop_select_post_type']) ? $_POST['rop_select_post_type'] :                              $postTypes[0];
            $selected_tax = isset($_POST['rop_select_category']) ? $_POST['rop_select_category'] :                              "none";
            $displayed_posts = array();
            $exclude_search = isset($_POST['s']) ? $_POST['s'] : "";
            $excluded_cats=get_option('top_opt_omit_cats');
            if(is_array($excluded_cats)) $excluded_cats = implode(",",$excluded_cats);
            if(empty($excluded_cats)) $excluded_cats = '';
            if(!is_string($excluded_cats)) $excluded_cats = '';
            $excluded_cats = trim($excluded_cats);
            $excluded_display_option = array("all"=>"All","excluded"=>"Excluded","unexcluded"=>"Unexcluded");
            $excluded_display_selected = isset($_POST['rop_display_selection']) ? $_POST['rop_display_selection'] :                    key($excluded_display_option);
            $taxs = get_taxonomies(array(
                'public'   => true,
                'hierarchical'   => true
            ),"object","and");
            $available_taxonomy = array();
            foreach($postTypes as $pt){
                foreach($taxs as $kt=>$tx){

                    if(in_array($pt,$tx->object_type)){
                        if(!isset($available_taxonomy[$kt])){
                                $available_taxonomy[$kt] = array("label"=>$tx->label);
                        }
                        if(!isset($available_taxonomy[$kt]['post_types'])){
                                $available_taxonomy[$kt]['post_types'] = array();

                        }
                        $available_taxonomy[$kt]['post_types'] = array_merge( $available_taxonomy[$kt]['post_types'],$tx->object_type);
                        if(!isset($available_taxonomy[$kt]['taxs'])){
                                $available_taxonomy[$kt]['taxs'] = array();

                        }
                        $terms = get_terms($tx->name, array(
                            'hide_empty'        => false,
                            'exclude'   =>$excluded_cats
                        ));
                        foreach ($terms as $t) {
                            $available_taxonomy[$kt]['taxs'][$t->term_id] = $t->name;
                        }
                    }
                }

            }
            $excluded_ids = get_option('top_opt_excluded_post');
            $excluded_ids = array_filter(explode(',',$excluded_ids));
            if(isset($_POST['exclude']) ){
                    if(!isset($_POST['rop_post_id'])) $_POST['rop_post_id'] =array();
                    $show_items = explode(',',$_POST['rop_show_posts']);
                    $dif = array_diff($show_items,$_POST['rop_post_id']);
                    $com  = array_intersect($dif,$excluded_ids);
                    $excluded_ids = array_diff($excluded_ids,$com);
                    $excluded_ids = array_merge ($excluded_ids,$_POST['rop_post_id']);
                    $excluded_ids = array_unique($excluded_ids);
                    update_option('top_opt_excluded_post',implode(',',$excluded_ids));
            }
             if(isset($_POST['exclude']) || isset($_POST['search'])){
                 $paged  = 1;

             }
            if(isset($_POST['exclude']) ) {
                print( '
			<div id="message" style="margin-top:30px" class="updated fade">
				<p>' . __( 'Posts excluded successfully.', 'TweetOldPost' ) . '</p>
			</div>' );
            }

    require_once(plugin_dir_path( __FILE__ )."view-exclude.php");


    $sql = "SELECT p.ID,p.post_title,p.post_date,u.user_nicename,p.guid,p.post_type FROM $wpdb->posts p join  $wpdb->users u on p.post_author=u.ID WHERE (post_type = '{$selected_post_type}')
                  AND post_status = 'publish'";
    if($selected_tax != 'none'){
    $sql = $sql . " and p.ID IN ( SELECT tr.object_id FROM ".$wpdb->prefix."term_relationships AS tr INNER JOIN ".$wpdb->prefix."term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.term_id=" . $selected_tax . " ";
        if(!empty($excluded_cats)){
           $sql .= " and tr.object_id NOT IN ( SELECT tr.object_id FROM ".$wpdb->prefix."term_relationships AS tr INNER JOIN ".$wpdb->prefix."term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.term_id  IN (" . $excluded_cats . ")) ) ";

        }else{
            $sql .= ")";

        }
    }
    else
        {

            if(!empty($excluded_cats)){
                $sql = $sql . " and p.ID NOT IN ( SELECT tr.object_id FROM ".$wpdb->prefix."term_relationships AS tr INNER JOIN ".$wpdb->prefix."term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.term_id  IN (" . $excluded_cats . "))";

            }
    }
        if($excluded_display_selected == "excluded" )
        {

            $sql = $sql . " and p.ID IN (".implode(",",$excluded_ids).")";
        }
        if($excluded_display_selected == "unexcluded" && !empty($excluded_ids))
        {

            $sql = $sql . " and p.ID NOT in (".implode(",",$excluded_ids).")";
        }
    if(!empty($exclude_search))
         $sql = $sql . " and post_title like '%" . trim($exclude_search ) . "%'";
    $sql = $sql . " order by post_date desc";
    $posts = $wpdb->get_results($sql);



    $from = $paged * $records_per_page - $records_per_page;
    $to = min($paged  * $records_per_page, count($posts));
    $post_count =count($posts);

 print('<form id="top_TweetOldPost" name="top_TweetOldPost" action="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=ExcludePosts" method="post">');
            print('
			<script language="javascript">

            function ROPshowCorectTax(){
                var post_type = jQuery("#rop_select_post_type").val();
                jQuery("#rop_select_category optgroup").each(function(){
                var pt = jQuery(this).attr("data-post-type").split(",");
                var th = jQuery(this);
                if(jQuery.inArray(post_type,pt) > -1){
                    th.show();
                }else{
                    th.hide();
                }


            });

}
            jQuery(function() {
                jQuery("#rop_select_post_type").on("change",function(){
                    ROPshowCorectTax();

                });
                ROPshowCorectTax();
            });
            function checkedAll() {
                if(jQuery("rop-header-check").is("checked")){
                    jQuery(".rop_post_id").attr("checked","checked");

                }else{

                    jQuery(".rop_post_id").removeAttr("checked" );

                }
             }
			jQuery(document).ready(function(){
                    jQuery("#top_TweetOldPost").on("click",".page-numbers",function(){
                        var paged = jQuery(this).text();
                        jQuery("#post-search-page").val(paged);
                        jQuery("#top_TweetOldPost").submit();
                        return false;
                    })
			})

			</script>
		');
        print('<div class="tablenav"><div class="alignleft actions">');
        ?>  <p class="rop-exclude-filter">
                <label>View: </label>
                <select name="rop_display_selection" id="selFilter" style="width:100px">
                    <?php foreach($excluded_display_option as $value=>$name): ?>
                        <option value="<?php echo $value; ?>" <?php selected($value,$excluded_display_selected); ?> > <?php echo $name; ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p class="rop-exclude-filter">
                <label>Post type</label>
                <select name="rop_select_post_type" id="rop_select_post_type">
                    <?php
                     foreach($postTypes as $pt):
                         ?>
                        <option value="<?php echo $pt ?>" <?php selected($pt,$selected_post_type); ?>><?php echo $pt?></option>
                     <?php
                         endforeach;
                    ?>

                </select>
            </p>

            <p class="rop-exclude-filter">
                <label>Category</label>
                <select name="rop_select_category" id="rop_select_category">
                    <option value="none"> All </option>
                    <?php
                    foreach($available_taxonomy as $at):
                        ?>
                        <optgroup label="<?php echo $at['label']; ?>" data-post-type="<?php echo implode(',',$at['post_types']);?>">
                            <?php
                                foreach($at['taxs'] as $id=>$tax): ?>
                                    <option value="<?php echo $id ?>" <?php selected($id,$selected_tax); ?>><?php echo $tax; ?></option>
                            <?php
                                    endforeach;
                            ?>
                        </optgroup>

                    <?php
                    endforeach;
                    ?>

                </select>
            </p>
            <?php

        print('<p class="search-box" style="margin:0px">
	<input type="text" id="post-search-input" name="s" value="'.$exclude_search.'" />
	<input type="hidden" id="post-search-page" name="paged" value="'.$paged.'" />
	<input type="submit" value="Search Posts" name="search" class="button" />
	<input type="submit" value="Exclude selected" name="exclude" class="button" />
</p>');
        print('</div>');

                $page_links = paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => ceil(count($posts) / $records_per_page),
                    'current' => $paged
                ));

        if ($page_links) {

            print('<div class="tablenav-pages">');
            $page_links_text = sprintf('<span class="displaying-num">' . __('Displaying %s&#8211;%s of %s') . '</span>%s',
                            number_format_i18n(( $paged - 1 ) * $records_per_page + 1),
                            number_format_i18n(min($paged * $records_per_page, count($posts))),
                            number_format_i18n(count($posts)),
                            $page_links
            );
            echo $page_links_text;
            print('</div>');
        }
        print('</div>');//tablenav div
            if (count($posts) > 0) {
        print('	<div class="wrap">
				<table class="widefat fixed">
					<thead>
					<tr>
						<th class="manage-column column-cb check-column"><input name="headchkbx" id="rop-header-check" onchange="javascript:checkedAll();" type="checkbox" value="checkall"/></th>
						<th>No.</th>
						<th>Id</th>
						<th>Post Title</th>
						<th>Author</th>
						<th>Post Date</th>
					</tr>
					</thead>
					<tbody>
		');



        for ($i = $from; $i < $to; $i++) {

            $displayed_posts[] = $posts[$i]->ID;
            if (in_array($posts[$i]->ID, $excluded_ids)) {
                $checked = "Checked";
                $bgcolor="#FFCC99";
            } else {
                $checked = "";
                $bgcolor="#FFF";
            }
            $displayed_posts[] =    $posts[$i]->ID;
            print('

				<tr style="background-color:'.$bgcolor.';">
					<th class="check-column">
						<input type="checkbox" name="rop_post_id[]" class="rop_post_id" id="del' . $posts[$i]->ID . '"  value="' . $posts[$i]->ID . '" ' . $checked . '/>
					</th>
					<td>
						' . ($i + 1) . '
					</td>
					<td>
						' . $posts[$i]->ID . '
					</td>
					<td>
						<a href=' . get_permalink($posts[$i]->ID) . ' target="_blank">' . $posts[$i]->post_title . '</a>
					</td>
					<td>
                                            ' . $posts[$i]->user_nicename . '
                                        </td>
                                        <td>
                                            ' . $posts[$i]->post_date . '
                                        </td>
				</tr>

			');
        }
        print('
				</tbody>
				</table>
			</div>
		');
        ?>
            <input type="hidden" name="rop_show_posts" value="<?php echo implode(',',$displayed_posts); ?>"/>
        <?php

        if ($page_links) {

            print('<div class="tablenav"> <div class="tablenav-pages">');
            $page_links_text = sprintf('<span class="displaying-num">' . __('Displaying %s&#8211;%s of %s') . '</span>%s',
                            number_format_i18n(( $paged - 1 ) * $records_per_page + 1),
                            number_format_i18n(min($paged * $records_per_page, count($posts))),
                            number_format_i18n(count($posts)),
                            $page_links
            );
            echo $page_links_text;
            print('</div></div> ');
        }




    }
else
{
    print('<div class="wrap">
			<div id="message" style="margin-top:30px" class="updated fade">
				<p>' . __('No Posts found. Review your search or filter criteria/term.', 'TweetOldPost') . '</p>
			</div></div>');
}
print('</form></section>');
    } else {
            print('
                <div id="message" class="updated fade">
                    <p>' . __('You do not have enough permission to set the option. Please contact your admin.', 'TweetOldPost') . '</p>
                </div>');
        }
}
?>