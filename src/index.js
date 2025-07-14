import { Icon } from '@wordpress/components';

import { PanelBody } from '@wordpress/components';

import { useEntityProp } from '@wordpress/core-data';

import { useSelect } from '@wordpress/data';

import {
	PluginPrePublishPanel,
	PluginPostPublishPanel,
	PluginSidebar,
	PluginSidebarMoreMenuItem,
	store as editorStore,
} from '@wordpress/editor';

import { useEffect } from '@wordpress/element';

import { registerPlugin } from '@wordpress/plugins';

import InstantSharing from './instant';
import ManualSharing from './manual';
import Variations from './variations';
import Upsell from './variations/Upsell';

const icon = (
	<Icon
		icon={
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 122.3 122.3">
				<path
					className="a"
					d="M61.15,0A61.15,61.15,0,1,0,122.3,61.15,61.22,61.22,0,0,0,61.15,0Zm40.54,60.11L86.57,75.62,47.93,32.39l-33.07,27H12a49.19,49.19,0,0,1,98.35,1.24ZM109.35,71a49.2,49.2,0,0,1-96.63-1.2h5.84L46.8,46.74,86.24,90.86l19.57-20.07Z"
				/>
			</svg>
		}
	/>
);

const isPro = Number(ropApiSettings.license_type) > 0;

const render = () => {
	const postType = useSelect(
		(select) => select(editorStore).getCurrentPostType(),
		[]
	);
	const postStatus = useSelect(
		(select) => select(editorStore).getCurrentPostAttribute('status'),
		[]
	);

	const [ meta, setMeta ] = useEntityProp('postType', postType, 'meta');

	const updateMetaValue = (keyOrObject, newValue) => {
		if (typeof keyOrObject === 'object' && keyOrObject !== null) {
			setMeta({ ...meta, ...keyOrObject });
		} else {
			setMeta({ ...meta, [keyOrObject]: newValue });
		}
	};

	useEffect(() => {
		if ('initial' === meta.rop_publish_now && postStatus !== 'publish') {
			updateMetaValue('rop_publish_now', 'yes');
		}
	}, []);

	return (
		<>
			<PluginSidebarMoreMenuItem target="rop-sidebar" icon={icon}>
				{ropApiSettings.labels.general.plugin_name}
			</PluginSidebarMoreMenuItem>

			<PluginSidebar
				name="rop-sidebar"
				icon={icon}
				title={ropApiSettings.labels.general.plugin_name}
				className="revive-social-sidebar"
			>
				{Boolean(ropApiSettings.publish_now.instant_share_enabled) && (
					<>
						<PanelBody
							title={
								ropApiSettings.labels.publish_now
									.instant_sharing
							}
						>
							<InstantSharing
								screen="pre-publish"
								meta={meta}
								updateMetaValue={updateMetaValue}
								postStatus={postStatus}
								publishStatus={meta.rop_publish_now_status}
							/>
						</PanelBody>

						{postStatus === 'publish' && (
							<PanelBody
								title={
									ropApiSettings.labels.publish_now
										.manual_sharing
								}
							>
								<ManualSharing />
							</PanelBody>
						)}
					</>
				)}

				{isPro && Boolean(ropApiSettings.custom_messages) && (
					<Variations meta={meta} updateMetaValue={updateMetaValue} />
				)}

				{!isPro && <Upsell />}
			</PluginSidebar>

			{Boolean(ropApiSettings.publish_now.instant_share_enabled) && (
				<>
					<PluginPrePublishPanel
						title={
							ropApiSettings.labels.publish_now.instant_sharing
						}
						isInitialOpen={true}
						icon={icon}
					>
						<InstantSharing
							screen="pre-publish"
							meta={meta}
							updateMetaValue={updateMetaValue}
							postStatus={postStatus}
							publishStatus={meta.rop_publish_now_status}
						/>
					</PluginPrePublishPanel>

					{postStatus === 'publish' && (
						<>
							<PluginPostPublishPanel
								icon={icon}
								isInitialOpen={true}
							>
								<InstantSharing
									screen="post-publish"
									meta={meta}
									updateMetaValue={updateMetaValue}
									postStatus={postStatus}
									publishStatus={meta.rop_publish_now_status}
								/>
							</PluginPostPublishPanel>

							<PluginPostPublishPanel
								title={
									ropApiSettings.labels.publish_now
										.manual_sharing
								}
								icon={icon}
								isInitialOpen={true}
							>
								<ManualSharing />
							</PluginPostPublishPanel>
						</>
					)}
				</>
			)}
		</>
	);
};

if (
	Boolean(ropApiSettings.publish_now.instant_share_enabled) ||
	(isPro && Boolean(ropApiSettings.custom_messages))
) {
	registerPlugin('revive-social', { render });
}
