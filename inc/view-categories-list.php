<?php

if (!is_array(get_option('top_opt_omit_cats'))) {
    $top_opt_omit_specific_cats = explode(',',get_option('top_opt_omit_cats'));
} else {
    $top_opt_omit_specific_cats = get_option('top_opt_omit_cats');
}
?>

<select name="<?php echo $field['option'];?>[]" data-placeholder="<?php _e("Exclude categories", "tweet-old-post");?>" class="chosen-select" multiple>
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