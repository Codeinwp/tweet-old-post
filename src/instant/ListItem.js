import {
    Button,
    CheckboxControl,
    TextareaControl,
    __experimentalHStack as HStack,
    __experimentalVStack as VStack
} from '@wordpress/components';

import { useState } from '@wordpress/element';

import {
    commentEditLink,
    check
} from '@wordpress/icons';

import { getIcon } from '../utils';

const ListItem = ({
    id,
    platform,
    meta,
    updateMetaValue,
    isPro
}) => {
    const [ isEditing, setIsEditing ] = useState( false );
    const [ socialMessage, setSocialMessage ] = useState( '' );

    const toggleAccount = value => {
        const currentAccounts = meta.rop_publish_now_accounts || {};
        const updatedAccounts = { ...currentAccounts };

        if ( value ) {
            updatedAccounts[ id ] = socialMessage;
        } else {
            delete updatedAccounts[ id ];
        }

        updateMetaValue( 'rop_publish_now_accounts', updatedAccounts );
    };

    const handleMessageChange = value => {
        setSocialMessage( value );
        const currentAccounts = meta.rop_publish_now_accounts || {};
        const updatedAccounts = { ...currentAccounts, [ id ]: value };
        updateMetaValue( 'rop_publish_now_accounts', updatedAccounts );
    }

    return (
        <VStack>
            <HStack
                alignment="center"
                justify="flex-start"
                spacing={ 2 }
            >
                <CheckboxControl
                    __nextHasNoMarginBottom
                    aria-describedby={ `revive-social-checkbox__${ id }` }
                    checked={ Object.keys( meta.rop_publish_now_accounts || {} ).includes( id ) }
                    onChange={ value => toggleAccount( value ) }
                    id={ `revive-social-checkbox__${ id }` }
                    className="revive-social__checkbox"
                />

                <HStack>
                    <HStack
                        as="label"
                        justify="flex-start"
                        htmlFor={ `revive-social-checkbox__${ id }` }
                        expanded={ false }
                        wrap={ false }
                    >
                        { getIcon( platform?.service ) }

                        <div>{ platform?.user }</div>
                    </HStack>

                    <Button
                        variant="tertiary"
                        icon={ isEditing ? check : commentEditLink }
                        label={ isEditing ? ropApiSettings.labels.settings.save : ropApiSettings.labels.publish_now.edit_message }
                        showTooltip={ true }
                        disabled={ ! Object.keys( meta.rop_publish_now_accounts || {} ).includes( id ) }
                        onClick={ () => setIsEditing( ! isEditing ) }
                    />
                </HStack>
            </HStack>

            { isEditing && (
                <TextareaControl
                    label= { ropApiSettings.labels.publish_now.custom_share_message }
                    placeholder={ ropApiSettings.labels.publish_now.custom_share_message_placeholder }
                    help={ <p dangerouslySetInnerHTML={{ __html: ropApiSettings.labels.publish_now.custom_instant_share_messages_upsell }} /> }
                    value={ socialMessage }
                    onChange={ handleMessageChange }
                    disabled={ ! isPro }
                />
            ) }
        </VStack>
    );
};

export default ListItem;
