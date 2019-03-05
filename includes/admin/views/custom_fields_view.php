<script type="text/javascript">
jQuery(document).ready(function ($) {
	console.log($('.rop-content-variation').last());
	$('#add-row').on('click', function () {
		var row = $('.empty-row.rop-content-variation.screen-reader-text').clone(true);
		row.removeClass('empty-row screen-reader-text');
		row.insertAfter( $('[class^="rop-content-variation"]').last() );
		return false;
	});

	$('.remove-row').on('click', function () {
		$(this).parents('.rop-content-variation').remove();
		return false;
	});
});
</script>

<?php echo Rop_I18n::get_labels( 'post_editor.custom_message_info' ); ?>


<?php

if ( $rop_custom_messages_group ) {
	$i = 1;
	foreach ( $rop_custom_messages_group as $field ) {
		echo '
		<div class="rop-content-variation"><p><b>' . Rop_I18n::get_labels( 'post_editor.variation_num' ) . $i ++ . '</b></p>
		<p>
		<textarea placeholder="' . Rop_Pro_I18n::get_labels( 'magic_tags.example' ) . '" cols="55" rows="5" name="rop_custom_description[]" style="width: 100%;">' . ( ( $field['rop_custom_description'] != '' ) ? esc_attr( $field['rop_custom_description'] ) : '' ) . '</textarea>
		</p>';

		echo '<p><a class="button remove-row" href="#1">' . Rop_I18n::get_labels( 'post_editor.remove_variation' ) . '</a></p>
		</div>';
	}
} else {
	echo '
	<div class="rop-content-variation">
	<p><b>' . Rop_I18n::get_labels( 'post_editor.new_variation' ) . '</b></p>

	<p><textarea  placeholder="' . Rop_Pro_I18n::get_labels( 'magic_tags.example' ) . '" name="rop_custom_description[]" cols="55" rows="5" style="width: 100%;"></textarea></p>
';

	echo'</div>';
}
?>

<div class="empty-row rop-content-variation screen-reader-text" ><p><b><?php echo Rop_I18n::get_labels( 'post_editor.new_variation' ); ?></b></p>
<p>
<textarea placeholder="<?php echo Rop_Pro_I18n::get_labels( 'magic_tags.example' ); ?>" cols="55" rows="5" name="rop_custom_description[]" style="width: 100%;"></textarea>
</p>
<p><a class="button remove-row" href="#1"><?php echo Rop_I18n::get_labels( 'post_editor.remove_variation' ); ?></a></p>
</div>


<p><a id="add-row" class="button" href="#"><?php echo Rop_I18n::get_labels( 'post_editor.add_variation' ); ?></a></p>
