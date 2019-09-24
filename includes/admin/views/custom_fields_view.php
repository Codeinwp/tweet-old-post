<script type="text/javascript">
    jQuery(document).ready(function ($) {
        console.log($('.rop-content-variation').last());
        var file_names = {};

        $('#add-row').on('click', function () {
            var row = $('.empty-row.rop-content-variation.screen-reader-text').clone(true);
            row.removeClass('empty-row screen-reader-text');
            row.insertAfter($('[class^="rop-content-variation"]').last());
            return false;
        });

        $('.remove-row').on('click', function () {
            $(this).parents('.rop-content-variation').remove();
            return false;
        });

        // ROP image upload for variation
        $('#rop-custom-messages-group').on("click tap", ".rop-image-attach", function (e) {
            e.preventDefault();
            var identifier = this.dataset.ropImgId;
            var row_container = $(this).closest('.rop-content-variation');
            var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
            var set_to_post_id = row_container.find('.rop-hidden-attachment-id').val();
            var this_image = file_names[identifier];

            if (this_image) {
                // Set the post ID to what we want
                this_image.uploader.uploader.param('post_id', set_to_post_id);
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
                    attachment = this_image.state().get('selection').first().toJSON();

                    // Do something with attachment.id and/or attachment.url here
                    //$('#image-preview').attr('src', attachment.url).css('width', 'auto');
                    //$('#image_attachment_id').val(attachment.id);

                    // Restore the main post ID
                    wp.media.model.settings.post.id = wp_media_post_id;
                });

                // Finally, open the modal
                this_image.open();
            }

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
	$label_new_variation = Rop_I18n::get_labels( 'post_editor.new_variation' );
	$label_example       = Rop_Pro_I18n::get_labels( 'magic_tags.example' );
	$label_button        = __( 'Upload image', 'pacpac' );

	echo <<<MSG_GROUP
    <div class="rop-content-variation">
        <p>
            <b>{$label_new_variation}</b>
        </p>
        <p>
            <div style="width: 70%; display:inline-block">
                <textarea placeholder="{$label_example}" 
                name="rop_custom_description[]"
                cols="55"
                rows="5"
                style="width: 100%;"
                ></textarea>
            </div>
            <div style="width: 28%; display:inline-block; text-align: center;">
                <div class='image-preview-wrapper'>
                    <img id='image-preview_0' class="rop-img-attached" src='' width='100' height='100' style='max-height: 100%; height: auto' alt="">
                </div>
                <input id="upload_image_button" class="rop-image-attach" type="button" class="button" value="{$label_button}" data-rop-img-id="0"/>
                <input type='hidden' value='' class="rop-hidden-attachment-id">
            </div>
        </p>
    </div>
MSG_GROUP;

}
?>

<div class="empty-row rop-content-variation screen-reader-text"><p><b><?php echo Rop_I18n::get_labels( 'post_editor.new_variation' ); ?></b></p>
    <p>
        <textarea placeholder="<?php echo Rop_Pro_I18n::get_labels( 'magic_tags.example' ); ?>" cols="55" rows="5" name="rop_custom_description[]" style="width: 100%;"></textarea>
    </p>
    <p><a class="button remove-row" href="#1"><?php echo Rop_I18n::get_labels( 'post_editor.remove_variation' ); ?></a></p>
</div>


<p><a id="add-row" class="button" href="#"><?php echo Rop_I18n::get_labels( 'post_editor.add_variation' ); ?></a></p>
