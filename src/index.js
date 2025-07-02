import { Icon } from '@wordpress/components';

import { PanelBody } from '@wordpress/components';

import { useEntityProp } from '@wordpress/core-data';

import { useSelect } from '@wordpress/data';

import {
	PluginPrePublishPanel,
	PluginSidebar,
	PluginSidebarMoreMenuItem,
	store as editorStore
} from '@wordpress/editor';

import { registerPlugin } from '@wordpress/plugins';

import InstantSharing from './instant';
import Variations from './variations';

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

const isPro = Boolean( ropApiSettings.license_type ) > 0;

const render = () => {
	const postType = useSelect( select => select( editorStore ).getCurrentPostType(), [] );

	const [ meta, setMeta ] = useEntityProp( 'postType', postType, 'meta' );

	const updateMetaValue = ( keyOrObject, newValue ) => {
		if ( typeof keyOrObject === 'object' && keyOrObject !== null ) {
			setMeta( { ...meta, ...keyOrObject } );
		} else {
			setMeta( { ...meta, [keyOrObject]: newValue } );
		}
	};

	return (
		<>
			<PluginSidebarMoreMenuItem
                target="rop-sidebar"
                icon={ icon }
            >
				{ ropApiSettings.labels.general.plugin_name }
			</PluginSidebarMoreMenuItem>

			<PluginSidebar
				name="rop-sidebar"
				icon={ icon }
				title={ ropApiSettings.labels.general.plugin_name }
				className="revive-social-sidebar"
			>
				{ Boolean( ropApiSettings.publish_now.instant_share_enabled ) && (
					<PanelBody title={ ropApiSettings.labels.publish_now.instant_sharing }>
						<InstantSharing
							meta={ meta }
							updateMetaValue={ updateMetaValue }
						/>
					</PanelBody>
				) }

				{ ( isPro && Boolean( ropApiSettings.custom_messages ) ) && (
					<Variations
						meta={ meta }
						updateMetaValue={ updateMetaValue }
					/>
				) }
			</PluginSidebar>

			<PluginPrePublishPanel
				title={ ropApiSettings.labels.publish_now.instant_sharing }
				icon={ icon }
			>
				{ Boolean( ropApiSettings.publish_now.instant_share_enabled ) && (
					<InstantSharing
						meta={ meta }
						updateMetaValue={ updateMetaValue }
					/>
				) }
			</PluginPrePublishPanel>
		</>
	);
};

if ( Boolean( ropApiSettings.publish_now.instant_share_enabled ) || ( isPro && Boolean( ropApiSettings.custom_messages ) ) ) {
	registerPlugin( 'revive-social', { render } );
}
