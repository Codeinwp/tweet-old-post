import { __ } from '@wordpress/i18n';

import {
	Button,
	ToggleControl,
	__experimentalSpacer as Spacer,
	__experimentalVStack as VStack,
} from '@wordpress/components';

import { plus } from '@wordpress/icons';

import ListItem from './ListItem';

const isPro = Boolean( ropApiSettings.license_type ) > 0;

const InstantSharing = ({
	meta,
	updateMetaValue
}) => {
	const accounts = Object.keys( ropApiSettings.publish_now.accounts ).filter( key => true === ropApiSettings.publish_now.accounts[ key ].active );

	if ( ropApiSettings.publish_now.accounts?.length < 1 ) {
		return (
			<>
				<p>{ ropApiSettings.labels.publish_now.add_account_to_use_instant_share }</p>

				<Spacer paddingY="4">
					<Button
						variant="secondary"
						icon={ plus }
						style={{
							width: '100%',
							justifyContent: 'center',
						}}
						target="_blank"
						href={ ropApiSettings.dashboard }
					>
						{ ropApiSettings.labels.publish_now.add_platform }
					</Button>
				</Spacer>
			</>
		);
	}

	return (
		<>
			<p>{ ropApiSettings.labels.publish_now.instant_sharing_desc }</p>

			<ToggleControl
				label={ ropApiSettings.labels.publish_now.share_immediately }
				className="revive-social__toggle"
				checked={ 'yes' === meta.rop_publish_now }
				onChange={ value => updateMetaValue( 'rop_publish_now', value ? 'yes' : 'no' ) }
			/>

			{ 'yes' === meta.rop_publish_now && (
				<>
					<VStack spacing="4" style={{ marginTop: '1.5rem' }}>
						{ accounts?.map( key => (
							<ListItem
								key={ key }
								id={ key }
								platform={ ropApiSettings.publish_now.accounts[ key ] }
								meta={ meta}
								updateMetaValue={ updateMetaValue }
								isPro={ isPro }
							/>
						))}
					</VStack>

					<Spacer paddingY="4">
						<Button
							variant="secondary"
							icon={ plus }
							style={{
								width: '100%',
								justifyContent: 'center',
							}}
							target="_blank"
							href={ ropApiSettings.dashboard }
						>
							{ ropApiSettings.labels.publish_now.add_platform }
						</Button>
					</Spacer>
				</>
			) }
		</>
	);
};

export default InstantSharing;
