<template>
	<div id="rop-sign-in-area">
		<div class="input-group text-right buttons-wrap">
			<button v-for="( service, network ) in services"
					:disabled="checkDisabled( service, network )"
					class="btn input-group-btn"
					:class="'btn-' + network"
					@click="requestAuthorization( network )">
				<i v-if="network !== 'buffer'" class="fa fa-fw" :class="'fa-' + network"></i>
				<i v-if="network === 'buffer'" class="fa fa-fw fa-plus-square"></i>
				{{service.name}}
			</button>

		</div>

		<div class="modal" :class="modalActiveClass">
			<div class="modal-overlay"></div>
			<div class="modal-container">
				<div class="modal-header">
					<button class="btn btn-clear float-right" @click="cancelModal()"></button>
					<div class="modal-title h5">{{ modal.serviceName }} {{labels.service_popup_title}}</div>
				</div>
				<div class="modal-body">
					<div class="content">
						<div class="auth-app" v-if="isFacebook">
							<button class="btn btn-primary big-btn" @click="openPopupFB()">{{labels.fb_app_signin_btn}}</button>
							<span class="text-center">{{labels.fb_own_app_signin}}</span>
						</div>
						<div class="auth-app" v-if="isTwitter">
							<button class="btn btn-primary big-btn" @click="openPopupTW()">{{labels.tw_app_signin_btn}}</button>
							<span class="text-center">{{labels.tw_own_app_signin}}</span>
						</div>
						<div class="auth-app" v-if="isLinkedIn && isAllowedLinkedIn">
							<button class="btn btn-primary big-btn" @click="openPopupLI()">{{labels.li_app_signin_btn}}</button>
							<span class="text-center">{{labels.li_own_app_signin}}</span>
						</div>
						<div class="auth-app" v-if="isBuffer">
							<button class="btn btn-primary big-btn" @click="openPopupBuffer()">{{labels.buffer_app_signin_btn}}</button>
							<span class="text-center">{{labels.li_own_app_signin}}</span>
						</div>
						<div id="rop-advanced-config" v-if="isFacebook || isTwitter || (isLinkedIn && isAllowedLinkedIn)">
						<button class="btn btn-primary" v-on:click="showAdvanceConfig = !showAdvanceConfig">{{labels.show_advance_config}}</button>
					</div>
						<div v-if="showAdvanceConfig && (isFacebook || isTwitter || (isLinkedIn && isAllowedLinkedIn) )">
						<div class="form-group" v-for="( field, id ) in modal.data">
							<label class="form-label" :for="field.id">{{ field.name }}</label>
							<input :class="[ 'form-input', field.error ? ' is-error' : '' ]" type="text" :id="field.id" v-model="field.value"
								   :placeholder="field.name"/>
							<small class="text-error" v-if="field.error">{{labels.field_required}}</small>
							<p class="text-gray">{{ field.description }}</p>
						</div>
					</div>
						<div v-if="(!isTwitter && !isFacebook && !isLinkedIn) || (isLinkedIn && !isAllowedLinkedIn)">
						<div class="form-group" v-for="( field, id ) in modal.data">
							<label class="form-label" :for="field.id">{{ field.name }}</label>
							<input :class="[ 'form-input', field.error ? ' is-error' : '' ]" type="text" :id="field.id" v-model="field.value"
								   :placeholder="field.name"/>
							<small class="text-error" v-if="field.error">{{labels.field_required}}</small>
							<p class="text-gray">{{ field.description }}</p>
						</div>
					</div>
				</div>
				</div>
				<div v-if="isFacebook || isTwitter || (isLinkedIn && isAllowedLinkedIn)" class="modal-footer">
					<p class="text-left pull-left mr-2" v-html="labels.rs_app_info"></p>
				</div>
				<div v-if="showAdvanceConfig && (isFacebook || isTwitter || isLinkedIn)" class="modal-footer">
					<div class="text-left pull-left mr-2" v-html="modal.description"></div>
					<button class="btn btn-primary" @click="closeModal()">{{labels.sign_in_btn}}</button>
				</div>
				<div v-if="(!isTwitter && !isFacebook && !isLinkedIn) || (isLinkedIn && !isAllowedLinkedIn)" class="modal-footer">
					<div class="text-left pull-left mr-2" v-html="modal.description"></div>
					<button class="btn btn-primary" @click="closeModal()">{{labels.sign_in_btn}}</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	module.exports = {
		name: 'sign-in-btn',
		created() {
		},
		data: function () {
			return {
				modal: {
					isOpen: false,
					serviceName: '',
					description: '',
					data: {}
				},
				showAdvanceConfig: false,
				labels: this.$store.state.labels.accounts,
				upsell_link: ropApiSettings.upsell_link,
				activePopup: '',
				appOrigin: ropAuthAppData.authAppUrl,
				appPathFB: ropAuthAppData.authAppFacebookPath,
        appPathTW: ropAuthAppData.authAppTwitterPath,
        appPathLI: ropAuthAppData.authAppLinkedInPath,
        appPathBuffer: ropAuthAppData.authAppBufferPath,
				appAdminEmail: ropAuthAppData.adminEmail,
				siteAdminUrl: ropAuthAppData.adminUrl,
				appUniqueId: ropAuthAppData.authToken,
				appSignature: ropAuthAppData.authSignature,
				windowParameters: 'top=20,left=100,width=560,height=670',
				authPopupWindow: null,
				showLiAppBtn: ropApiSettings.show_li_app_btn,
				showBtn: false
			}
		},
		methods: {
			/**
			 * Check status for the service.
			 *
			 *
			 * @param service
			 * @param network
			 * @returns {boolean}
			 */
			checkDisabled(service, network) {
				if (service !== undefined && service.active === false) {
					return true
				}

				let countAuthServices = 0
				for (let authService in this.$store.state.authenticatedServices) {
					if (this.$store.state.authenticatedServices[authService].service === network) {
						countAuthServices++
					}
				}

				let countActiveAccounts = 0
				for (let activeAccount in this.$store.state.activeAccounts) {
					if (this.$store.state.activeAccounts[activeAccount].service === network) {
						countActiveAccounts++
					}
				}

				if (service !== undefined && (service.allowed_accounts <= countAuthServices || service.allowed_accounts <= countActiveAccounts)) {
					return true
				}

				return this.$store.state.auth_in_progress
			},
			/**
			 * Request authorization popup.
			 */
			requestAuthorization: function (network) {
				this.selected_network = network;
				this.$store.state.auth_in_progress = true
				if (this.$store.state.availableServices[this.selected_network].two_step_sign_in) {
					this.modal.serviceName = this.$store.state.availableServices[this.selected_network].name
					this.modal.description = this.$store.state.availableServices[this.selected_network].description
					this.modal.data = this.$store.state.availableServices[this.selected_network].credentials
					this.openModal()
				} else {
					this.activePopup = this.selected_network
					this.getUrlAndGo([])
				}
			},
			/**
			 * Open popup to specific url.
			 * @param url
			 */
			openPopup(url) {
				this.$log.debug('Opening popup for url ', url)
				this.$store.commit('logMessage', ['Trying to open popup for url:' + url, 'notice'])
				window.open(url, '_self')
			},
			/**
			 * Get signin url.
			 * @param credentials
			 */
			getUrlAndGo(credentials) {
				this.$store.dispatch('fetchAJAXPromise', {
					req: 'get_service_sign_in_url',
					updateState: false,
					data: {service: this.selected_network, credentials: credentials}
				}).then(response => {
					//  console.log( 'Got some data, now lets show something in this component', response )
					if ( ! response.url || response.url == '' ) {
						this.cancelModal()
						alert( 'Could not authenticate, please make sure you entered the correct credentials.' );
					} else {
						this.openPopup(response.url)
					}
				}, error => {
					Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				})
			},
			requestAuthentication() {
				this.$store.dispatch('fetchAJAX', {req: 'authenticate_service', data: {service: this.selected_network}})
			},
			/**
			 * Open the modal.
			 */
			openModal: function () {
				this.modal.isOpen = true
			},
			closeModal: function () {
				let credentials = {}
				let valid = true;
				for (const index of Object.keys(this.modal.data)) {
					credentials[index] = ''
					if ('value' in this.modal.data[index] && '' !== this.modal.data[index].value) {
						credentials[index] = this.modal.data[index].value
						this.modal.data[index].error = false
					} else {
						this.modal.data[index].error = true
						valid = false;
					}
				}

				if ( ! valid ) {
					this.$forceUpdate()
					return;
				}

				this.activePopup = this.selected_network
				this.getUrlAndGo(credentials)
				this.modal.isOpen = false
			},
			cancelModal: function () {
				this.$store.state.auth_in_progress = false
				this.showAdvanceConfig = false
				this.modal.isOpen = false
			},
			/**
			 * Add Facebook account.
			 *
			 * @param data Data.
			 */
			addAccountFB(data) {
				this.$store.dispatch('fetchAJAXPromise', {
					req: 'add_account_fb',
					updateState: false,
					data: data
				}).then(response => {
					window.removeEventListener("message", event => this.getChildWindowMessage(event));
					this.authPopupWindow.close();
					window.location.reload();
				}, error => {
					this.is_loading = false;
					Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
                });
            },
            /**
             * Add Twitter account.
             *
             * @param data Data.
             */
            addAccountTW(data) {
                this.$store.dispatch('fetchAJAXPromise', {
                    req: 'add_account_tw',
                    updateState: false,
                    data: data
                }).then(response => {
                    window.removeEventListener("message", event => this.getChildWindowMessage(event));
                    this.authPopupWindow.close();
                    window.location.reload();
                }, error => {
                    this.is_loading = false;
                    Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				});
			},
            /**
             * Add LinkedIn account.
             *
             * @param data Data.
             */
            addAccountLI(data) {
                this.$store.dispatch('fetchAJAXPromise', {
                    req: 'add_account_li',
                    updateState: false,
                    data: data
                }).then(response => {
                    window.removeEventListener("message", event => this.getChildWindowMessage(event));
                    this.authPopupWindow.close();
                    window.location.reload();
                }, error => {
                    this.is_loading = false;
                    Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				});
			},
            /**
             * Add Buffer account.
             *
             * @param data Data.
             */
            addAccountBuffer(data) {
                this.$store.dispatch('fetchAJAXPromise', {
                    req: 'add_account_buffer',
                    updateState: false,
                    data: data
                }).then(response => {
                    window.removeEventListener("message", event => this.getChildWindowMessage(event));
                    this.authPopupWindow.close();
                    window.location.reload();
                }, error => {
                    this.is_loading = false;
                    Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				});
			},
			getChildWindowMessage: function (event) {
				if (~event.origin.indexOf(this.appOrigin)) {
            if ('Twitter' === this.modal.serviceName) {
            this.addAccountTW(JSON.parse(event.data));
            } else if ('Facebook' === this.modal.serviceName) {
					    this.addAccountFB(JSON.parse(event.data));
						} else if ('LinkedIn' === this.modal.serviceName) {
					    this.addAccountLI(JSON.parse(event.data));
						} else if ('Buffer' === this.modal.serviceName) {
					    this.addAccountBuffer(JSON.parse(event.data));
            }

				} else {
					return;
				}
			},
			openPopupFB: function () {
				let loginUrl = this.appOrigin + this.appPathFB + '?callback_url=' + this.siteAdminUrl + '&token=' + this.appUniqueId + '&signature=' + this.appSignature + '&data=' + this.appAdminEmail;
				try {
					this.authPopupWindow.close();
				} catch (e) {
					// nothing to do
				} finally {
					this.authPopupWindow = window.open( loginUrl, 'authFB', this.windowParameters);
					this.cancelModal();
				}
				window.addEventListener("message", event => this.getChildWindowMessage(event));
            },
            openPopupTW: function () { // Open the popup specific for Twitter
                let loginUrl = this.appOrigin + this.appPathTW + '?callback_url=' + this.siteAdminUrl + '&token=' + this.appUniqueId + '&signature=' + this.appSignature + '&data=' + this.appAdminEmail;
                try {
                    this.authPopupWindow.close();
                } catch (e) {
                    // nothing to do
                } finally {
                    this.authPopupWindow = window.open(loginUrl, 'authTW', this.windowParameters);
                    this.cancelModal();
                }
                window.addEventListener("message", event => this.getChildWindowMessage(event));
			},
            openPopupLI: function () { // Open the popup specific for LinkedIn
                let loginUrl = this.appOrigin + this.appPathLI + '?callback_url=' + this.siteAdminUrl + '&token=' + this.appUniqueId + '&signature=' + this.appSignature + '&data=' + this.appAdminEmail;
                try {
                    this.authPopupWindow.close();
                } catch (e) {
                    // nothing to do
                } finally {
                    this.authPopupWindow = window.open(loginUrl, 'authLI', this.windowParameters);
                    this.cancelModal();
                }
                window.addEventListener("message", event => this.getChildWindowMessage(event));
			},
            openPopupBuffer: function () { // Open the popup specific for Buffer
                let loginUrl = this.appOrigin + this.appPathBuffer + '?callback_url=' + this.siteAdminUrl + '&token=' + this.appUniqueId + '&signature=' + this.appSignature + '&data=' + this.appAdminEmail;
                try {
                    this.authPopupWindow.close();
                } catch (e) {
                    // nothing to do
                } finally {
                    this.authPopupWindow = window.open(loginUrl, 'authLI', this.windowParameters);
                    this.cancelModal();
                }
                window.addEventListener("message", event => this.getChildWindowMessage(event));
			}
		},
		computed: {
			selected_service: function () {
				return this.services[this.selected_network]
			},
			selected_network: {
				get: function () {
					let defaultNetwork = this.modal.serviceName
					if (Object.keys(this.services)[0] && defaultNetwork === '') {
						defaultNetwork = Object.keys(this.services)[0]
					}
					return defaultNetwork.toLowerCase()
				},
				set: function (newNetwork) {
					this.modal.serviceName = newNetwork
				}
			},
			services: function () {
				return this.$store.state.availableServices
			},
			modalActiveClass: function () {
				return {
					'active': this.modal.isOpen === true
				}
			},
			serviceId: function () {
				return 'service-' + this.modal.serviceName.toLowerCase()
			},
			isFacebook() {
				return this.modal.serviceName === 'Facebook';
			},
            // will return true if the current service actions are for Twitter.
            isTwitter() {
                return this.modal.serviceName === 'Twitter';
            },
            // will return true if the current service actions are for LinkedIn.
            isLinkedIn() {
                return this.modal.serviceName === 'LinkedIn';
            },
						isAllowedLinkedIn: function () {
							let showButton = true;
							if (!this.showLiAppBtn) {
									showButton = false;
							}
							return showButton;
					},
					// will return true if the current service actions are for Buffer.
					isBuffer() {
							return this.modal.serviceName === 'Buffer';
					},
	}
}
</script>
<style scoped>
	#rop-sign-in-area .btn[disabled]{
		cursor:not-allowed;
		pointer-events: auto;
		opacity: 0.3;
	}
</style>
