import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';

import {
	Button,
	Card,
	CardBody,
	PanelBody,
	TextareaControl,
	__experimentalHStack as HStack,
	__experimentalSpacer as Spacer,
	__experimentalVStack as VStack,
} from '@wordpress/components';

import { store as coreStore } from '@wordpress/core-data';

import { useSelect } from '@wordpress/data';

import { useEffect } from '@wordpress/element';

import { plus } from '@wordpress/icons';

const ALLOWED_MEDIA_TYPES = [ 'image' ];

const Image = ({ id }) => {
	const image = useSelect(
		(select) => (id ? select(coreStore).getMedia(id) : null),
		[ id ]
	);

	if (!image) {
		return null;
	}

	return (
		<img
			src={image.source_url}
			alt={image.alt_text || image.title || ''}
			style={{
				maxWidth: '100%',
				height: 'auto',
			}}
		/>
	);
};

const Variations = ({ meta, updateMetaValue }) => {
	useEffect(() => {
		if (!Boolean(meta.rop_custom_messages_group?.length)) {
			addMessageRow();
		}
	}, []);

	const addMessageRow = () => {
		const currentMessages = meta.rop_custom_messages_group || [];
		const updatedMessages = [
			...currentMessages,
			{
				rop_custom_description: '',
			},
		];
		updateMetaValue('rop_custom_messages_group', updatedMessages);
	};

	const updateMessageRow = (index, value) => {
		const currentMessages = meta.rop_custom_messages_group || [];
		const updatedMessages = [ ...currentMessages ];
		updatedMessages[index] = {
			rop_custom_description: value,
		};
		updateMetaValue('rop_custom_messages_group', updatedMessages);
	};

	const removeMessageRow = (index) => {
		const currentMessages = meta.rop_custom_messages_group || [];
		const updatedMessages = currentMessages.filter((_, i) => i !== index);

		const currentImages = meta.rop_custom_images_group || {};
		const updatedImages = {};

		Object.entries(currentImages).forEach(([ key, value ]) => {
			const numericKey = parseInt(key, 10);
			if (numericKey < index) {
				updatedImages[numericKey] = value;
			} else if (numericKey > index) {
				updatedImages[numericKey - 1] = value; // Shift down
			}
		});

		updateMetaValue({
			rop_custom_messages_group: updatedMessages,
			rop_custom_images_group: updatedImages,
		});
	};

	const addImage = (index, imageId) => {
		const currentImages = meta.rop_custom_images_group || {};
		const updatedImages = { ...currentImages };

		if (!updatedImages[index]) {
			updatedImages[index] = {};
		}
		updatedImages[index].rop_custom_image = imageId;

		updateMetaValue('rop_custom_images_group', updatedImages);
	};

	const removeImage = (index) => {
		const currentImages = meta.rop_custom_images_group || {};
		const updatedImages = { ...currentImages };

		delete updatedImages[index];

		updateMetaValue('rop_custom_images_group', updatedImages);
	};

	return (
		<PanelBody
			title={ropApiSettings.labels.post_editor.new_variation}
			initialOpen={false}
		>
			<p
				dangerouslySetInnerHTML={{
					__html: ropApiSettings.labels.post_editor
						.custom_message_info,
				}}
			/>

			{meta.rop_custom_messages_group?.map(
				({ rop_custom_description }, index) => (
					<Spacer paddingY={2}>
						<Card isRounded={false}>
							<CardBody>
								<TextareaControl
									label={
										ropApiSettings.labels.post_editor
											.new_variation
									}
									placeholder={
										ropApiSettings.labels.post_format
											.add_char_placeholder_custom_content
									}
									value={rop_custom_description}
									onChange={(value) =>
										updateMessageRow(index, value)
									}
								/>

								<VStack>
									<Image
										id={
											meta.rop_custom_images_group?.[
												index
											]?.rop_custom_image
										}
									/>

									<HStack justify="flex-start">
										<MediaUploadCheck>
											<MediaUpload
												onSelect={(media) =>
													addImage(index, media?.id)
												}
												allowedTypes={
													ALLOWED_MEDIA_TYPES
												}
												value={
													meta
														.rop_custom_images_group?.[
														index
													]?.rop_custom_image || null
												}
												render={({ open }) => (
													<Button
														variant="primary"
														onClick={open}
													>
														{meta
															.rop_custom_images_group?.[
															index
														]?.rop_custom_image
															? ropApiSettings
																	.labels
																	.post_editor
																	.variation_image_change
															: ropApiSettings
																	.labels
																	.post_editor
																	.variation_image}
													</Button>
												)}
											/>
										</MediaUploadCheck>

										{meta.rop_custom_images_group?.[index]
											?.rop_custom_image && (
											<Button
												variant="secondary"
												onClick={() =>
													removeImage(index)
												}
											>
												{
													ropApiSettings.labels
														.post_editor
														.variation_remove_image
												}
											</Button>
										)}
									</HStack>
								</VStack>

								<Spacer />

								{index > 0 && (
									<Button
										variant="secondary"
										isDestructive
										style={{
											width: '100%',
											justifyContent: 'center',
										}}
										onClick={() => removeMessageRow(index)}
									>
										{
											ropApiSettings.labels.post_editor
												.remove_variation
										}
									</Button>
								)}
							</CardBody>
						</Card>
					</Spacer>
				)
			)}

			<Spacer paddingY={4}>
				<Button
					variant="secondary"
					onClick={addMessageRow}
					icon={plus}
					style={{
						width: '100%',
						justifyContent: 'center',
					}}
				>
					{ropApiSettings.labels.post_editor.add_variation}
				</Button>
			</Spacer>
		</PanelBody>
	);
};

export default Variations;
