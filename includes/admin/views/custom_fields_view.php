<script type="text/javascript">
	jQuery(document).ready(function ($) {
		$('#add-row').on('click', function () {
			var row = $('.empty-row.screen-reader-text').clone(true);
			row.removeClass('empty-row screen-reader-text');
			row.insertBefore('#repeatable-fieldset-one tbody>tr:last');
			return false;
		});

		$('.remove-row').on('click', function () {
			$(this).parents('tr').remove();
			return false;
		});
	});
</script>

<table id="repeatable-fieldset-one" width="100%">
	<tbody>
	<?php
	if ( $rop_custom_messages_group ) {
		$i = 1;
		foreach ( $rop_custom_messages_group as $field ) {
			echo '
		<tr>
			<td width="15%">
				<b>' . Rop_I18n::get_labels( 'post_editor.message_no' ) . $i ++ . '</b><br/>
				<small><i>' . Rop_I18n::get_labels( 'post_editor.random_message_info' ) . '</i></small>
			</td>
			<td width="70%">
				<textarea placeholder="' . Rop_Pro_I18n::get_labels( 'magic_tags.example' ) . '" cols="55" rows="5" name="rop_custom_description[]" style="width: 100%;">' . ( ( $field['rop_custom_description'] != '' ) ? esc_attr( $field['rop_custom_description'] ) : '' ) . '</textarea></td>
			<td width="15%"><a class="button remove-row" href="#1">' . Rop_I18n::get_labels( 'post_editor.remove_message' ) . '</a></td>
		</tr>
		';
		}
	} else {
		echo '
		<tr>
			<td width="15%">
				<b>' . Rop_I18n::get_labels( 'post_editor.message_no' ) . '</b><br/>
				<small><i>' . Rop_I18n::get_labels( 'post_editor.random_message_info' ) . '</i></small>
			</td>
			<td width="70%">
				<textarea  placeholder="' . Rop_Pro_I18n::get_labels( 'magic_tags.example' ) . '" name="rop_custom_description[]" cols="55" rows="5" style="width: 100%;"></textarea>
			</td>
			<td width="15%"></td>
	    </tr>
		';
	}
	?>
	<tr class="empty-row screen-reader-text">
		<td width="15%">
			<b><?php echo Rop_I18n::get_labels( 'post_editor.message_no' ); ?></b><br/>
			<small>
				<i><?php echo Rop_I18n::get_labels( 'post_editor.random_message_info' ); ?></i>
			</small>
		<td width="70%">
			<textarea placeholder="<?php echo Rop_Pro_I18n::get_labels( 'magic_tags.example' ); ?>" cols="55" rows="5" name="rop_custom_description[]"
					  style="width: 100%;"></textarea>
		</td>
		<td width="15%"><a class="button remove-row"
						   href="#"><?php echo Rop_I18n::get_labels( 'post_editor.remove_message' ); ?></a></td>
	</tr>
	</tbody>
</table>
<p><a id="add-row" class="button" href="#"><?php echo Rop_I18n::get_labels( 'post_editor.add_message' ); ?></a></p>
