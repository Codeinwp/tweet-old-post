import {
    Button,
    __experimentalHStack as HStack,
} from '@wordpress/components';

import { useCopyToClipboard } from '@wordpress/compose';

import {
    useDispatch,
    useSelect
} from '@wordpress/data';

import { store as editorStore } from '@wordpress/editor';

import { store as noticesStore } from '@wordpress/notices';

const allowedPlatforms = [
    'facebook',
    'twitter',
    'linkedin',
    'bluesky',
    'tumblr',
    'telegram',
    'whatsapp',
    'link'
];

import { getIcon } from '../utils';

const getSocialShareLinks = ( title, url ) => {
	const encodedTitle = encodeURIComponent( title );
	const encodedURL = encodeURIComponent( url );

	return {
		facebook: `https://www.facebook.com/sharer/sharer.php?u=${ encodedURL }`,
		twitter: `https://x.com/intent/post?text=${ encodedTitle }%20-%20${ encodedURL }`,
		linkedin: `https://www.linkedin.com/shareArticle?mini=true&url=${ encodedURL }&title=${ encodedTitle }`,
		tumblr: `https://www.tumblr.com/widgets/share/tool?canonicalUrl=${ encodedURL }&title=${ encodedTitle }`,
		telegram: `https://t.me/share/url?url=${ encodedURL }&text=${ encodedTitle }`,
		whatsapp: `https://api.whatsapp.com/send?text=${ encodedTitle }%20-%20${ encodedURL }`,
		bluesky: `https://bsky.app/intent/compose?text=${ encodedTitle }%20-%20${ encodedURL }`,
        link: `${ encodedTitle } - ${ encodedURL }`
	};
}

const ManualSharing = () => {
    const { title, permalink } = useSelect( select => {
        const getAttr = select( editorStore ).getCurrentPostAttribute;
        return {
            title: getAttr( 'title' ),
            permalink: getAttr( 'link' ),
        };
    }, [] );

    const { createNotice } = useDispatch( noticesStore );

    const links = getSocialShareLinks( title, permalink );

    const ref = useCopyToClipboard(
        links.link,
		() => createNotice(
            'info',
            ropApiSettings.labels.publish_now.copied_to_clipboard,
            {
                isDismissible: true,
                type: 'snackbar',
            }
        )
    );

    return (
        <>
            <p>{ ropApiSettings.labels.publish_now.manual_sharing_desc }</p>

            <HStack
                wrap="wrap"
                justify="flex-start"
                className="revive-social__sharing-buttons"
            >
                { allowedPlatforms.map( ( service ) => (
                    <Button
                        key={ service }
                        { ...(
                            service === 'link'
                                ? {
                                    ref
                                }
                                : {
                                    target: "_blank",
                                    rel: "noopener noreferrer",
                                    href: links[ service ]
                                }
                        ) }
                    >
                        { getIcon( service ) }
                    </Button>
                ) ) }
            </HStack>
        </>
    );
};

export default ManualSharing;
