// jshint ignore: start

/**
 * WordPress dependencies.
*/
const { __ } = wp.i18n;

const { apiFetch } = wp;

const { CheckboxControl } = wp.components;

const { withSelect } = wp.data;

const { Component } = wp.element;

const { PluginPostStatusInfo } = wp.editPost;

const { registerPlugin } = wp.plugins;

class ROPPublish extends Component {
	constructor() {
		super( ...arguments );

		this.toggleStatus = this.toggleStatus.bind( this );
		this.toggleAccount = this.toggleAccount.bind( this );

		this.state = {
			default: false,
			accounts: {}
		};
	}

	async componentDidMount() {
		await apiFetch( { path: `tweet-old-post/v8/gutenberg/get_meta/?id=${ this.props.postId }` } )
			.then( response => {
				let accounts = {};

				{ Object.keys( window.ropApiPublish.accounts ).map( i => {
					accounts[i] = response.rop_publish_now_accounts.includes( i );
				} ) }

				return this.setState( {
					default: this.props.postPublished ? Boolean( response.rop_publish_now ) : Boolean( window.ropApiPublish.action ),
					accounts
				} );
			} )
			.catch( error => {
				let accounts = {};

				{ Object.keys( window.ropApiPublish.accounts ).map( i => {
					accounts[i] = this.props.postPublished ? false : Boolean( window.ropApiPublish.action );
				} ) }

				return this.setState( {
					default: this.props.postPublished ? false : Boolean( window.ropApiPublish.action ),
					accounts
				} );
			} );;;
	}

	static getDerivedStateFromProps( nextProps, state ) {
		if ( ( nextProps.isPublishing || ( nextProps.postPublished && nextProps.isSaving ) ) && ! nextProps.isAutoSaving ) {
			wp.apiRequest( { path: `/tweet-old-post/v8/gutenberg/update_meta/?id=${ nextProps.postId }`, method: 'POST', data: state } ).then(
				( data ) => {
					return data;
				},
				( err ) => {
					return err;
				}
			);
		}
	}

	toggleStatus( value ) {
		if ( value ) {
			let accounts = {};

			{ Object.keys( window.ropApiPublish.accounts ).map( i => {
				accounts[i] = true;
			} ) }

			this.setState( { accounts } );
		}

		this.setState( { default: ! this.state.default } );
	}

	toggleAccount( key, value ) {
		const accounts = this.state.accounts;
		accounts[key] = value;
		this.setState( { accounts } );
	}

	render() {
		if ( 0 < Object.keys( window.ropApiPublish.accounts ).length  ) {
			return (
				<PluginPostStatusInfo>
					<div className="rop-publish-guten">
						<CheckboxControl
							label={ __( 'Share immediately via Revive Old Post' ) }
							checked={ this.state.default }
							onChange={ this.toggleStatus }
						/>

						{ this.state.default && (
							<div className="rop-publish-guten__list">
								{ Object.keys( window.ropApiPublish.accounts ).map( i => {
									return (
										<CheckboxControl
											label={ window.ropApiPublish.accounts[i].user }
											checked={ this.state.accounts[i] }
											className={ `rop-icon rop-icon-${ window.ropApiPublish.accounts[i].service }` }
											onChange={ () => this.toggleAccount( i, ! this.state.accounts[i] ) }
										/>
									)
								} ) }
							</div>
						) }
					</div>
				</PluginPostStatusInfo>
			);
		}

		return null;
	}
}

const ROP = withSelect( ( select, { forceIsSaving } ) => {
	const {
		getCurrentPostId,
		isCurrentPostPublished,
		isSavingPost,
		isPublishingPost,
		isAutosavingPost,
	} = select( 'core/editor' );

	return {
		postId: getCurrentPostId(),
		postPublished: isCurrentPostPublished(),
		isSaving: forceIsSaving || isSavingPost(),
		isAutoSaving: isAutosavingPost(),
		isPublishing: isPublishingPost(),
	};
} )( ROPPublish );

registerPlugin( 'revive-old-post', {
	render: ROP
} );
