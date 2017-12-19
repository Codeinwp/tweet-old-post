<script type="text/javascript">
	jQuery(document).ready(function( $ ){
		$( '#add-row' ).on('click', function() {
			var row = $( '.empty-row.screen-reader-text' ).clone(true);
			row.removeClass( 'empty-row screen-reader-text' );
			row.insertBefore( '#repeatable-fieldset-one tbody>tr:last' );
			return false;
		});

		$( '.remove-row' ).on('click', function() {
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
				<b>' . __( 'Custom message #', 'tweet-old-post' ) . $i++ . '</b><br/>
				<small><i>' . __( 'A custom message that will be selected randomly for each share and will overwrite the default post.', 'tweet-old-post' ) . '</i></small>
			</td> 
			<td width="70%">
				<textarea placeholder="Description" cols="55" rows="5" name="rop_custom_description[]" style="width: 100%;">' . ( ( $field['rop_custom_description'] != '') ? esc_attr( $field['rop_custom_description'] ) : '' ) . '</textarea></td>
			<td width="15%"><a class="button remove-row" href="#1">' . __( 'Remove Custom Message', 'twwet-old-post' ) . '</a></td>
		</tr>
		';
	}
} else {
	echo '
		<tr>
			<td width="15%">
				<b>' . __( 'Custom message #', 'tweet-old-post' ) . '</b><br/>
				<small><i>' . __( 'A custom message that will be selected randomly for each share and will overwrite the default post.', 'tweet-old-post' ) . '</i></small>
			</td>
			<td width="70%"> 
				<textarea  placeholder="Description" name="rop_custom_description[]" cols="55" rows="5" style="width: 100%;"></textarea>
			</td>
			<td width="15%"></td>
	    </tr>
		';
}
?>
		<tr class="empty-row screen-reader-text">
			<td width="15%">
				<b><?php echo __( 'Custom message #', 'tweet-old-post' ) ?></b><br/>
				<small><i><?php echo __( 'A custom message that will be selected randomly for each share and will overwrite the default post.', 'tweet-old-post' ) ?></i></small>
			<td width="70%">
				<textarea placeholder="Description" cols="55" rows="5" name="rop_custom_description[]" style="width: 100%;"></textarea>
			</td>
			<td width="15%"><a class="button remove-row" href="#"><?php echo __( 'Remove Custom Message', 'twwet-old-post' ); ?></a></td>
		</tr>
	</tbody>
</table>
<p><a id="add-row" class="button" href="#"><?php echo __( 'Add New Custom Message', 'twwet-old-post' ); ?></a></p>
