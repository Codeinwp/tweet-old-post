<?php

/**
 * Renders a variation block with textarea, image upload controls, and optional remove button.
 *
 * @param string     $variation_title The title to display for this variation block.
 * @param string     $description_value The initial value for the textarea field.
 * @param string|int $image_id The attachment ID for the selected image (empty string or 0 if no image).
 * @param int        $img_id The unique identifier for this variation's image controls.
 * @param bool       $show_remove_variation Whether to show the "Remove Variation" button.
 * @return void
 */
function render_variation_block( $variation_title, $description_value = '', $image_id = '', $img_id = 0, $show_remove_variation = true ) {
	$label_button = Rop_I18n::get_labels( 'post_editor.variation_image' );
	$label_button_change = Rop_I18n::get_labels( 'post_editor.variation_image_change' );
	$button_remove = Rop_I18n::get_labels( 'post_editor.variation_remove_image' );
	$label_example = Rop_Pro_I18n::get_labels( 'magic_tags.example' );
	$label_remove_variation = Rop_I18n::get_labels( 'post_editor.remove_variation' );
	
	$image_path = '';
	$hide_remove = ' style="display:none"';
	
	if ( ! empty( $image_id ) ) {
		$get_image_path = wp_get_attachment_url( absint( $image_id ) );
		if ( ! empty( $get_image_path ) ) {
			$image_path = esc_url( $get_image_path );
			$hide_remove = '';
		}
	}
	
	$button_label_switch = empty( $image_id ) ? $label_button : $label_button_change;
	
	echo '<div class="rop-content-variation">';
	echo '<p><b>' . esc_html( $variation_title ) . '</b></p>';
	echo '<div style="width: 70%; display:inline-block; float: left;">';
	echo '<textarea placeholder="' . esc_attr( $label_example ) . '" name="rop_custom_description[]" cols="55" rows="5" style="width: 100%;">' . esc_textarea( $description_value ) . '</textarea>';
	echo '</div>';
	echo '<div style="width: 28%; display:inline-block; text-align: center; float: left;">';
	echo '<div class="image-preview-wrapper">';
	echo '<img class="rop-img-attached" src="' . esc_url( $image_path ) . '" style="width: auto; height: 100px;" alt="">';
	echo '</div>';
	echo '<input class="rop-image-attach button button-primary" type="button" value="' . esc_attr( $button_label_switch ) . '" data-rop-img-id="' . esc_attr( $img_id ) . '"/>';
	echo '<input class="rop-image-remove button button-secondary" type="button" value="' . esc_attr( $button_remove ) . '" data-rop-img-id="' . esc_attr( $img_id ) . '" ' . esc_attr( $hide_remove ) . '/>';
	echo '<input type="hidden" value="' . esc_attr( $image_id ) . '" class="rop-hidden-attachment-id" name="rop_custom_image[]">';
	echo '</div>';
	echo '<div style="clear:both"></div>';
	if ( $show_remove_variation ) {
		echo '<p><a class="button remove-row" href="#1">' . esc_html( $label_remove_variation ) . '</a></p>';
	}
	echo '</div>';
}
?>

<script type="text/javascript">
	jQuery(document).ready(function ($ ) {
		var file_names = {};
		var rop_elements = $('.rop-content-variation');
		var count_elements = parseInt(rop_elements.length);
		var label_upload = '<?php echo esc_attr( Rop_I18n::get_labels( 'post_editor.variation_image' ) ); ?>';
		var label_change = '<?php echo esc_attr( Rop_I18n::get_labels( 'post_editor.variation_image_change' ) ); ?>';

		$('#add-row').on('click', function () {
			count_elements++;
			var row = $('.empty-row.rop-content-variation.screen-reader-text').clone(true);
			row.removeClass('empty-row screen-reader-text');
			row.find('input.rop-image-attach').attr('data-rop-img-id', count_elements);
			row.insertAfter($('[class^="rop-content-variation"]').last());
			return false;
		});

		$('.remove-row').on('click', function () {
			$(this).parents('.rop-content-variation').remove();
			return false;
		});

		var $variations_container = $('#rop-custom-messages-group');

		// ROP remove variation image
		$variations_container.on('click tap', '.rop-image-remove', function (e) {
			e.preventDefault();
			var row_container = $(this).closest('.rop-content-variation');
			var upload_button = row_container.find('.rop-image-attach');
			var image_id = row_container.find('.rop-hidden-attachment-id');
			var image_src = row_container.find('.rop-img-attached');

			image_src.attr('src', '');
			image_id.val('');
			$(this).hide();
			upload_button.val(label_upload);
		});


		// ROP image upload for variation
		$variations_container.on('click tap', '.rop-image-attach', function (e) {
			e.preventDefault();
			var identifier = this.dataset.ropImgId;
			var row_container = $(this).closest('.rop-content-variation');
			var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
			var image_id = row_container.find('.rop-hidden-attachment-id');
			var image_src = row_container.find('.rop-img-attached');
			var remove_button = row_container.find('.rop-image-remove');
			var this_image = file_names[identifier];
			var this_button = $(this);

			if (this_image) {
				// Set the post ID to what we want
				this_image.uploader.uploader.param('post_id', image_id.val());
				// Open frame
				this_image.open();
			} else {
				// Create the media frame.
				this_image = wp.media.frames.this_image = wp.media({
					title: 'Select a image to upload',
					button: {
						text: 'Use this image',
					},
					multiple: false	// Set to true to allow multiple files to be selected
				});


				// When an image is selected, run a callback.
				this_image.on('select', function () {
					// We set multiple to false so only get one image from the uploader
					var attachment = this_image.state().get('selection').first().toJSON();

					// Do something with attachment.id and/or attachment.url here
					image_src.attr('src', attachment.url).css('width', 'auto');
					image_id.val(attachment.id);
					remove_button.show();
					this_button.val(label_change);

					// Restore the main post ID
					wp.media.model.settings.post.id = wp_media_post_id;
				});

				// Finally, open the modal
				this_image.open();
			}

		});
	});
</script>

<?php echo esc_html( Rop_I18n::get_labels( 'post_editor.custom_message_info' ) ); ?>


<?php
if ( ! empty( $rop_custom_messages_group ) ) {
	$i = 1;
	$label_variation_number = Rop_I18n::get_labels( 'post_editor.variation_num' );
	$img_index = 0;
	
	foreach ( $rop_custom_messages_group as $field ) {
		$description_value = ( ( ! empty( trim( $field['rop_custom_description'] ) ) ) ? esc_textarea( $field['rop_custom_description'] ) : '' );
		$image_value = ( ! empty( $rop_custom_images_group ) && isset( $rop_custom_images_group[ $img_index ] ) ) ? $rop_custom_images_group[ $img_index ]['rop_custom_image'] : 0;
		
		$variation_title = $label_variation_number . $i;
		render_variation_block( $variation_title, $description_value, $image_value, $i, true );
		
		++$i;
		++$img_index;
	}
} else {
	$label_new_variation = Rop_I18n::get_labels( 'post_editor.new_variation' );
	render_variation_block( $label_new_variation, '', '', 0, false );
}

// Template for cloning - add classes for hidden state
echo '<div class="empty-row screen-reader-text">';
$label_new_variation = Rop_I18n::get_labels( 'post_editor.new_variation' );
render_variation_block( $label_new_variation, '', '', 0, true );
echo '</div>';
?>

<p>
	<a id="add-row" class="button" href="#">
		<?php echo esc_html( Rop_I18n::get_labels( 'post_editor.add_variation' ) ); ?>
	</a>
</p>