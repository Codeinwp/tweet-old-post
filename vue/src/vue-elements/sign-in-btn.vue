<template>
	<div class="sign-in-btn">
		<div class="input-group">
			<select class="form-select" v-model="selected_network">
				<option v-for="( service, network ) in services" v-bind:value="network" :disabled="checkDisabled( service, network )">{{ service.name }}</option>
			</select>

			<button class="btn input-group-btn" :class="serviceClass" @click="requestAuthorization()" :disabled="checkDisabled( selected_service, selected_network )" >
				<i class="fa fa-fw" :class="serviceIcon" aria-hidden="true"></i> Sign In
			</button>
			<i class="badge" data-badge="PRO" v-if="checkDisabled( selected_service, selected_network ) && !has_pro">More available in the <b>PRO</b> versions.</i>
		</div>
		<div class="modal" :class="modalActiveClass">
			<div class="modal-overlay"></div>
			<div class="modal-container">
				<div class="modal-header">
					<button class="btn btn-clear float-right" @click="cancelModal()"></button>
					<div class="modal-title h5">{{ modal.serviceName }} Service Credentials</div>
				</div>
				<div class="modal-body">
					<div class="content">
						<div class="form-group" v-for="( field, id ) in modal.data">
							<label class="form-label" :for="field.id">{{ field.name }}</label>
							<input class="form-input" type="text" :id="field.id" v-model="field.value" :placeholder="field.name" />
							<i>{{ field.description }}</i>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-primary" @click="closeModal()">Sign in</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	module.exports = {
		name: 'sign-in-btn',
		created () {
		},
		data: function () {
			return {
				modal: {
					isOpen: false,
					serviceName: '',
					data: {}
				},
				activePopup: ''
			}
		},
		methods: {
			checkDisabled ( service, network ) {
				if ( service !== undefined && service.active === false ) {
					return true
				}

				let countAuthServices = 0
				for ( let authService in this.$store.state.authenticatedServices ) {
					if ( this.$store.state.authenticatedServices[authService].service === network ) {
						countAuthServices++
					}
				}

				let countActiveAccounts = 0
				for ( let activeAccount in this.$store.state.activeAccounts ) {
					if ( this.$store.state.activeAccounts[activeAccount].service === network ) {
						countActiveAccounts++
					}
				}

				if ( service !== undefined && ( service.allowed_accounts <= countAuthServices || service.allowed_accounts <= countActiveAccounts ) ) {
					return true
				}

				return this.$store.state.auth_in_progress
			},
			requestAuthorization: function () {
				this.$store.state.auth_in_progress = true
				if ( this.$store.state.availableServices[this.selected_network].two_step_sign_in ) {
					this.modal.serviceName = this.$store.state.availableServices[this.selected_network].name
					this.modal.data = this.$store.state.availableServices[this.selected_network].credentials
					this.openModal()
				} else {
					this.activePopup = this.selected_network
					this.getUrlAndGo( [] )
				}
			},
			openPopup ( url ) {
				this.$store.commit( 'logMessage', [ 'Trying to open popup for url:' + url, 'notice' ] )
				let w = 560
				let h = 340
				let y = window.top.outerHeight / 2 + window.top.screenY - ( w / 2 )
				let x = window.top.outerWidth / 2 + window.top.screenX - ( h / 2 )
				let newWindow = window.open( url, this.activePopup, 'width=' + w + ', height=' + h + ', toolbar=0, menubar=0, location=0, target=_self, top=' + y + ', left=' + x )
				if ( window.focus ) { newWindow.focus() }
				console.log( newWindow.external )
				let instance = this
				let pollTimer = window.setInterval( function () {
					if ( newWindow.closed !== false ) {
						window.clearInterval( pollTimer )
						instance.requestAuthentication()
					}
				}, 200 )
			},
			getUrlAndGo ( credentials ) {
				console.log( 'Credentials recieved:', credentials )
				this.$store.dispatch( 'fetchAJAXPromise', { req: 'get_service_sign_in_url', updateState: false, data: { service: this.selected_network, credentials: credentials } } ).then( response => {
					console.log( 'Got some data, now lets show something in this component', response )
					this.openPopup( response.url )
				}, error => {
					console.error( 'Got nothing from server. Prompt user to check internet connection and try again', error )
				} )
			},
			requestAuthentication () {
				this.$store.dispatch( 'fetchAJAX', { req: 'authenticate_service', data: { service: this.selected_network } } )
			},
			openModal: function () {
				this.modal.isOpen = true
			},
			closeModal: function () {
				let credentials = {}
				for ( const index of Object.keys( this.modal.data ) ) {
					credentials[index] = ''
					if ( 'value' in this.modal.data[index] ) {
						credentials[index] = this.modal.data[index]['value']
					}
				}

				this.activePopup = this.selected_network
				this.getUrlAndGo( credentials )

				this.modal.isOpen = false
			},
			cancelModal: function () {
				this.$store.state.auth_in_progress = false
				this.modal.isOpen = false
			}
		},
		computed: {
			has_pro: function () {
				return this.$store.state.has_pro
			},
			selected_service: function () {
				return this.services[this.selected_network]
			},
			selected_network: {
				get: function () {
					let defaultNetwork = this.modal.serviceName
					if ( Object.keys( this.services )[0] && defaultNetwork === '' ) {
						defaultNetwork = Object.keys( this.services )[0]
					}
					return defaultNetwork.toLowerCase()
				},
				set: function ( newNetwork ) {
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
			serviceClass: function () {
				return {
					'btn-twitter': this.selected_network === 'twitter',
					'btn-facebook': this.selected_network === 'facebook',
					'btn-linkedin': this.selected_network === 'linkedin',
					'btn-tumblr': this.selected_network === 'tumblr',
					'loading': this.$store.state.auth_in_progress
				}
			},
			serviceIcon: function () {
				return {
					'fa-twitter': this.selected_network === 'twitter',
					'fa-facebook-official': this.selected_network === 'facebook',
					'fa-linkedin': this.selected_network === 'linkedin',
					'fa-tumblr': this.selected_network === 'tumblr'
				}
			},
			serviceId: function () {
				return 'service-' + this.modal.serviceName.toLowerCase()
			}
		}
	}
</script>

<style scoped>
	#rop_core .sign-in-btn > .modal {
		position: absolute;
		top: 20px;
	}

	#rop_core .sign-in-btn > .modal > .modal-container {
		width: 100%;
	}

</style>