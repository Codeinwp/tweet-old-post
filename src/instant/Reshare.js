import apiFetch from '@wordpress/api-fetch';

import {
    Button,
    __experimentalSpacer as Spacer,
    __experimentalVStack as VStack
} from '@wordpress/components';

import {
    useDispatch,
    useSelect
} from '@wordpress/data';

import { store as editorStore } from '@wordpress/editor';

import { useState } from '@wordpress/element';

import { store as noticesStore } from '@wordpress/notices';

import ListItem from './ListItem';

const Reshare = ({
    accounts,
    isPro,
    setHistory
}) => {
    const [ meta, setMeta ] = useState( {
        rop_publish_now_accounts: {}
    } );

    const [ isLoading, setIsLoading ] = useState( false );

    const postId = useSelect( select => select( editorStore ).getCurrentPostId(), [] );

    const { createNotice } = useDispatch( noticesStore );

    const shareRequest = async ( id, data ) => {
        setIsLoading( true );

        try {
            const request = await apiFetch({
                path: `tweet-old-post/v8/share/${ id }`,
                method: 'POST',
                data: {
                    ...data,
                },
            });

            if ( true !== request.success ) {
                createNotice(
                    'error',
                    request.message,
                    {
                        isDismissible: true,
                        type: 'snackbar',
                    }
                );

                return request;
            }

            if ( request?.history ) {
                setHistory( request.history );
            }

            createNotice(
                'info',
                request.message,
                {
                    isDismissible: true,
                    type: 'snackbar',
                }
            );

            updateMetaValue( 'rop_publish_now_accounts', {} );

            return request;
        } catch ( error ) {
            createNotice(
                'error',
                error?.message,
                {
                    isDismissible: true,
                    type: 'snackbar',
                }
            );

            throw error;
        } finally {
            setIsLoading( false );
        }
    };

    // We basically create a dummy meta object to hold the accounts
    // and their respective messages.
    const updateMetaValue = ( key, value ) => {
        setMeta( {
            ...meta,
            [ key ]: value
        } )
    };

    return (
        <>
			<p>{ ropApiSettings.labels.publish_now.reshare_description }</p>

            <VStack spacing="4" style={{ marginTop: '1.5rem' }}>
                { accounts?.map( key => (
                    <ListItem
                        key={ key }
                        id={ key }
                        platform={ ropApiSettings.publish_now.accounts[ key ] }
                        meta={ meta }
                        updateMetaValue={ updateMetaValue }
                        isPro={ isPro }
                    />
                ))}
            </VStack>

            <Spacer
                paddingTop="4"
                paddingBottom="1"
            >
                <Button
                    variant="primary"
                    disabled={ isLoading || Object.keys( meta.rop_publish_now_accounts || {} ).length === 0 }
                    onClick={ () => shareRequest( postId, meta ) }
                    isBusy={ isLoading }
                    style={{
                        width: '100%',
                        justifyContent: 'center',
                    }}
                >
                    { ropApiSettings.labels.publish_now.reshare_button }
                </Button>
            </Spacer>
        </>
    );
};

export default Reshare;
