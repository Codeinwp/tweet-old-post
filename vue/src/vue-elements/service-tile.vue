<template>
	<div class="service-tile">
		<label class="show-md hide-xl"><b>{{service_url}}/</b></label>
		<div class="input-group">
			<button class="btn input-group-btn btn-danger" @click="removeService()">
				<i class="fa fa-fw fa-trash" aria-hidden="true"></i>
			</button>
			<button class="btn input-group-btn btn-info" @click="toggleCredentials()" v-if="service.public_credentials">
				<i class="fa fa-fw fa-info-circle" aria-hidden="true"></i>
			</button>
			<span class="input-group-addon hide-md" style="min-width: 115px; text-align: right;">{{service_url}}/</span>
			<service-autocomplete :accounts="available_accounts" :to_be_activated="to_be_activated"
			                      :disabled="isDisabled" :limit="limit"></service-autocomplete>
			<button class="btn input-group-btn" :class="serviceClass" @click="activateSelected( service.id )"
			        :disabled="isDisabled">
				<i class="fa fa-fw fa-plus" aria-hidden="true"></i> <span class="hide-md">Activate</span>
			</button>
		</div>
		<div class="card centered" :class="credentialsDisplayClass" v-if="service.public_credentials">
			<div class="card-header">
				<div class="card-title h5">{{serviceName}}</div>
				<div class="card-subtitle text-gray">{{service.id}}</div>
			</div>
			<div class="card-body">
				<div class="form-horizontal">
					<div class="form-group" v-for="( credential, index ) in service.public_credentials">
						<div class="col-3">
							<label class="form-label" :for="credentialID(index)">{{credential.name}}:</label>
						</div>
						<div class="col-9">
							<secret-input :id="credentialID(index)" :value="credential.value"
							              :secret="credential.private"/>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="divider clearfix"></div>
	</div>
</template>

<script>
	import ServiceAutocomplete from './service-autocomplete.vue'
	import SecretInput from './reusables/secret-input.vue'

	function capitalizeFirstLetter(string) {
		return string.charAt(0).toUpperCase().concat(string.slice(1))
	}

	module.exports = {
		name: 'service-tile',
		props: {
			service: {
				type: Object,
				required: true
			}
		},
		data: function () {
			return {
				show_credentials: false,
				to_be_activated: []
			}
		},
		computed: {
			service_url: function () {
				if (this.service.service === 'facebook') {
					return 'facebook.com'
				}
				if (this.service.service === 'twitter') {
					return 'twitter.com'
				}
				if (this.service.service === 'linkedin') {
					return 'linkedin.com'
				}
				if (this.service.service === 'tumblr') {
					return 'tumblr.com'
				}

				return 'service.url'
			},
			available_accounts: function () {
				//console.log('Available accounts changed');
				return this.service.available_accounts;
			},
			serviceName: function () {
				return capitalizeFirstLetter(this.service.service)
			},
			serviceClass: function () {
				return {
					'btn-twitter': this.service.service === 'twitter',
					'btn-facebook': this.service.service === 'facebook',
					'btn-linkedin': this.service.service === 'linkedin',
					'btn-tumblr': this.service.service === 'tumblr'
				}
			},
			credentialsDisplayClass: function () {
				return {
					'd-block': this.show_credentials === true,
					'd-none': this.show_credentials === false
				}
			},
			limit: function () {
				let network = this.service.service
				let service = this.$store.state.availableServices[network]
				if (service !== undefined) {
					return service.allowed_accounts
				}
				return -1
			},
			isDisabled: function () {
				let network = this.service.service
				let service = this.$store.state.availableServices[network]

				if (service !== undefined && service.active === false) {
					return true
				}

				let countActiveAccounts = 0
				for (let activeAccount in this.$store.state.activeAccounts) {
					if (this.$store.state.activeAccounts[activeAccount].service === network) {
						countActiveAccounts++
					}
				}

				if (service !== undefined && (service.allowed_accounts <= countActiveAccounts)) {
					return true
				}

				return this.$store.state.auth_in_progress
			}
		},
		methods: {
			credentialID(index) {
				return 'service-' + index + '-field'
			},
			toggleCredentials() {
				this.show_credentials = !this.show_credentials
			},
			activateSelected(serviceId) {
				this.$store.dispatch('fetchAJAXPromise', {
					req: 'update_active_accounts',
					data: {
						service_id: serviceId,
						service: this.service.service,
						to_be_activated: this.to_be_activated,
						current_active: this.$store.state.activeAccounts
					}
				}).then(response => {
					this.$store.dispatch('fetchAJAX', {req: 'get_queue'});
					this.$store.dispatch('fetchAJAX', {req: 'get_authenticated_services'});
				}, error => {
					Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				})
			},
			removeService() {
				this.$store.dispatch('fetchAJAXPromise', {
					req: 'remove_service',
					data: {id: this.service.id, service: this.service.service}
				}).then(response => {
					this.$store.dispatch('fetchAJAXPromise', {req: 'get_active_accounts'}).then(response => {
						this.$store.dispatch('fetchAJAX', {req: 'get_queue'})
					}, error => {
						Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
					})
				}, error => {
					Vue.$log.error('Got nothing from server. Prompt user to check internet connection and try again', error)
				})
			}
		},
		components: {
			ServiceAutocomplete,
			SecretInput
		}
	}
</script>

<style scoped>
	
	#rop_core .btn.btn-danger {
		background-color: #d50000;
		color: #efefef;
		border-color: #b71c1c;
	}
	
	#rop_core .btn.btn-danger:hover, #rop_core {
		background-color: #efefef;
		color: #d50000;
		border-color: #b71c1c;
	}
	
	#rop_core .btn.btn-info {
		background-color: #2196f3;
		color: #efefef;
		border-color: #1565c0;
	}
	
	#rop_core .btn.btn-info:hover, #rop_core {
		background-color: #efefef;
		color: #2196f3;
		border-color: #1565c0;
	}

</style>