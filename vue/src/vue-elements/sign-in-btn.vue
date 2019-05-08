<template>
	<div id="rop-sign-in-area">
		<div class="input-group text-right buttons-wrap">
			<button v-for="( service, network ) in services"
			        :disabled="checkDisabled( service, network )"
			        class="btn input-group-btn"
			        :class="'btn-' + network"
			        @click="requestAuthorization( network )">
				<i class="fa fa-fw" :class="'fa-' + network"></i>{{service.name}}
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
						<div class="auth-app" v-if="isFacebook && isAllowedFacebook && showBtn">
							<button class="btn btn-primary big-btn" @click="openPopupFB()">Sign in to Facebook</button>
							<span class="text-center">or sign in using your own Facebook app</span>
						</div>
						<div class="form-group" v-for="( field, id ) in modal.data">
							<label class="form-label" :for="field.id">{{ field.name }}</label>
							<input class="form-input" type="text" :id="field.id" v-model="field.value"
							       :placeholder="field.name"/>
							<p class="text-gray">{{ field.description }}</p>
						</div>
					</div>
				</div>
				<div class="modal-footer">
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
				labels: this.$store.state.labels.accounts,
				upsell_link: ropApiSettings.upsell_link,
				activePopup: '',
				appOrigin: 'https://ropauth.oldrobot.us', // Please change to the real auth app URL
				appPathFB: '/fb_auth',
				windowParameters: 'top=20,left=100,width=560,height=670',
				authPopupWindow: null,
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
					this.openPopup(response.url)
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
				// this.modal.isOpen = true
				this.isAllowedFacebook();
			},
			closeModal: function () {
				let credentials = {}
				for (const index of Object.keys(this.modal.data)) {
					credentials[index] = ''
					if ('value' in this.modal.data[index]) {
						credentials[index] = this.modal.data[index]['value']
					}
				}

				this.activePopup = this.selected_network
				this.getUrlAndGo(credentials)

				this.modal.isOpen = false
			},
			cancelModal: function () {
				this.$store.state.auth_in_progress = false
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
			getChildWindowMessage: function (event) {
				if (~event.origin.indexOf(this.appOrigin)) {
					this.addAccountFB(JSON.parse(event.data));
					console.log(event.data);
				} else {
					return;
				}
			},
			openPopupFB: function () {
				let loginUrl = this.appOrigin + this.appPathFB + '?callback_url=' + window.location.origin;
				try {
					this.authPopupWindow.close();
				} catch (e) {
                    // nothin to do
				} finally {
					this.authPopupWindow = window.open( loginUrl, 'authFB', this.windowParameters);
					this.cancelModal();
				}
				window.addEventListener("message", event => this.getChildWindowMessage(event));
			},
			isAllowedFacebook: function () {
				if (this.modal.serviceName === 'Facebook') {
					this.$store.dispatch('fetchAJAXPromise', {
						req: 'check_account_fb',
						updateState: false
					}).then(response => {
						if (response === 1) {
							this.showBtn = true;
							this.modal.isOpen = true;
							return true;
						} else {
							this.showBtn = false;
							this.modal.isOpen = true;
							return false;
						}
					}, error => {
						this.showBtn = false;
						Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error);
						this.modal.isOpen = true;
						return false;
					});
				} else {
					this.showBtn = false;
					this.modal.isOpen = true;
					return false;
				}
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
			}
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
