import {
    Button,
    Modal,
    Spinner,
    __experimentalHStack as HStack,
} from '@wordpress/components';

import { store as coreStore } from '@wordpress/core-data';

import { select } from '@wordpress/data';

import { dateI18n } from '@wordpress/date';

import { store as editorStore } from '@wordpress/editor';

import {
    useEffect,
    useState
} from '@wordpress/element';

import { capitalize } from 'lodash';

import { getIcon } from '../utils';

const getPostMeta = () => {
    const data = select( coreStore ).getEntityRecord(
        'postType',
        select( editorStore ).getCurrentPostType(),
        select( editorStore ).getCurrentPostId(),
        {
            cache: Date.now(),
        }
    );

    return data.meta;
};

let interval

const getLabels = history => {
    const length = history.reduce( ( acc, item ) => {
        if ( 'error' === item.status ) {
            return 'failed';
        }
        if ( 'success' === item.status ) {
            return 'success';
        }

        return 'partially_shared';
    }, '' );

    switch ( length ) {
        case 'failed':
            return {
                title: ropApiSettings.labels.publish_now.share_failed_title,
                description: ropApiSettings.labels.publish_now.share_failed_desc,
            };
        case 'partially_shared':
            return {
                title: ropApiSettings.labels.publish_now.share_partially_shared_title,
                description: ropApiSettings.labels.publish_now.share_partially_shared_desc,
            }
        default:
            return {
                title: ropApiSettings.labels.publish_now.shared_title,
                description: ropApiSettings.labels.publish_now.shared_desc,
            };
    }
};

const formatTimestamp = timestamp => {
	return dateI18n( 'j F, Y g:i A', timestamp );
};

const TableRow = ({
    service,
    account,
    timestamp,
    status
}) => {
    return (
        <tr>
            <td>
                <HStack
                    justify="flex-start"
                >
                    <>{ getIcon( service ) }</>
                    <>{ ropApiSettings.publish_now.accounts[account]?.user }</>
                </HStack>
            </td>
            <td>{ formatTimestamp( Number( timestamp + '000' ) ) }</td>
            <td>{ capitalize( status ) }</td>
        </tr>
    );
};

const HistoryTable = ({ data }) => {
    return (
        <table>
            <thead>
                <tr>
                    <th
                        style={{ width: '50%' }}
                    >
                        { ropApiSettings.labels.publish_now.account}
                    </th>
                    <th
                        style={{ width: '25%' }}
                    >
                        { ropApiSettings.labels.publish_now.time}
                    </th>
                    <th
                        style={{ width: '25%' }}
                    >
                        { ropApiSettings.labels.publish_now.status}
                    </th>
                </tr>
            </thead>
            <tbody>
                { data.map( ( item, index ) => {
                    return (
                        <TableRow
                            key={ index }
                            service={ item.service }
                            account={ item.account }
                            timestamp={ item.timestamp }
                            status={ item.status }
                        />
                    );
                } ) }
            </tbody>
        </table>
    );
};

const HistoryModal = ({
    history,
    isOpen,
    setOpen
}) => {
    const onClose = () => {
        setOpen( ! isOpen );
    }

    if ( ! isOpen ) {
        return null;
    }

    return (
        <Modal
            title={ ropApiSettings.labels.publish_now.sharing_history }
            onRequestClose={ onClose }
            size="large"
            className="revive-social__modal"
        >
            <HistoryTable data={ history } />
        </Modal>
    );
};

const PostUpdate = ({
    status,
    history,
    setStatus,
    setHistory
}) => {
    const [ isOpen, setOpen ] = useState( false );
    const isQueued = history.some( item => 'queued' === item.status );

    useEffect(() => {
        interval = setInterval(() => {
            const currentStatus = getPostMeta();
            setStatus( currentStatus?.rop_publish_now_status );
            setHistory( currentStatus?.rop_publish_now_history || [] );
        }, 5000 );
        return () => clearInterval( interval );
    }, []);

    useEffect( () => {
        if ( 'done' === status && ! isQueued ) {
            clearInterval( interval );
        }
    }, [ status ] );

    if ( 'queued' === status || isQueued ) {
        return (
            <HStack
                justify="flex-start"
                className="revive-social__spinner"
            >
                <Spinner />
                <p>{ ropApiSettings.labels.publish_now.queued }</p>
            </HStack>
        );
    }

    if ( 'done' === status && history.length === 0 ) {
        return null;
    }

    const labels = getLabels( history );

    return (
        <>
            <h4>{ labels.title}</h4>
            <p>{ labels.description }</p>

            <HistoryModal
                history={ history }
                isOpen={ isOpen }
                setOpen={ setOpen }
            />

            <Button
                variant="secondary"
                style={{
                    width: '100%',
                    justifyContent: 'center',
                }}
                onClick={ () => setOpen( ! isOpen ) }
            >
                { ropApiSettings.labels.publish_now.view_history }
            </Button>
        </>
    );
};

export default PostUpdate;
