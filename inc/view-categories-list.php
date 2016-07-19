<?php

if (!is_array(get_option('top_opt_omit_cats'))) {
    $top_opt_omit_specific_cats = explode(',',get_option('top_opt_omit_cats'));
} else {
    $top_opt_omit_specific_cats = get_option('top_opt_omit_cats');
}

$filterType = get_option("top_opt_cat_filter", "exclude");
?>

<div id="top_exc_inc_radio">
    <input type="radio" name="top_opt_cat_filter" value="exclude" <?php echo $filterType == "exclude" ? "checked" : ""?> id="top_opt_category_exclude"><label for="top_opt_category_exclude"><?php _e("Exclude", "tweet-old-post");?></label>
    <input type="radio" name="top_opt_cat_filter" value="include" <?php echo $filterType == "include" ? "checked" : ""?> id="top_opt_category_include"><label for="top_opt_category_include"><?php _e("Include", "tweet-old-post");?></label>
</div>
<select name="<?php echo $field['option'];?>[]" data-placeholder="<?php _e("Categories", "tweet-old-post");?>" class="top-chosen-select" multiple>
    <option value=""></option>
<?php
    foreach ($taxonomies as $type=>$options) {
?>
    <optgroup label="<?php echo $type?>">
<?php
        foreach ($options as $label=>$id) {
            $extra      = in_array($id, $top_opt_omit_specific_cats) ? "selected" : "";
?>
      <option value="<?php echo $id;?>" <?php echo $extra;?>><?php echo $label;?></option>
<?php
        }
?>
    </optgroup>
<?php
    }
?>
</select>
<div class="clear"></div>