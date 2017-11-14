<template>
	<div class="tab-view">
		<div class="panel-body" style="overflow: inherit;">
			<h3>Custom Schedule</h3>
			<figure class="avatar avatar-lg" style="text-align: center;">
				<img :src="img" v-if="img">
				<i class="fa" :class="icon" style="line-height: 48px;" aria-hidden="true" v-else></i>
				<i class="avatar-icon fa" :class="icon" aria-hidden="true" v-if="img"></i>
				<!--<img src="img/avatar-5.png" class="avatar-icon" alt="...">-->
			</figure>
			<div class="d-inline-block" style="vertical-align: top; margin-left: 16px;">
				<h6>{{user_name}}</h6>
				<b class="service" :class="service">{{service_name}}</b>
			</div>
			<div class="d-inline-block" style="vertical-align: top; margin-left: 16px; width: 80%">
				<h4><i class="fa fa-info-circle"></i> Info</h4>
				<p><i>Each <b>account</b> can have it's own <b>Schedule</b> for sharing, on the left you can see the
					current selected account and network, bellow are the <b>Schedule</b> options for the account.
					Don't forget to save after each change and remember, you can always reset an account to the defaults.
				</i></p>
			</div>
			<div class="container">
				<div class="columns">
					<div class="column col-sm-12 col-md-12 col-lg-12">
						<div class="columns">
							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
								<b>Account</b><br/>
								<i>Specify an account to change the settings of.</i>
							</div>
							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
								<div class="form-group">
									<select class="form-select" v-model="selected_account" @change="getAccountSchedule()">
										<option v-for="( account, id ) in active_accounts" :value="id" >{{account.user}} - {{account.service}} </option>
									</select>
								</div>
							</div>
						</div>
						<hr/>

						<h4>Schedule</h4>
						<!-- Schedule Type - Can be 'recurring' or 'fixed'
							 If Recurring than an repeating interval is filled (float) Eg. 2.5 hours
							 If Fixed days of the week are selected and a specific time is selected. -->
						<div class="columns">
							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
								<b>Schedule Type</b><br/>
								<i>What type of schedule to use.</i>
							</div>
							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
								<div class="form-group">
									<select class="form-select" v-model="schedule.type">
										<option value="recurring">Recurring</option>
										<option value="fixed">Fixed</option>
									</select>
								</div>
							</div>
						</div>

						<div class="columns" v-if="schedule.type === 'fixed'">
							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
								<b>Fixed Schedule Days</b><br/>
								<i>The days when to share for this account.</i>
							</div>
							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
								<div class="form-group">
									<button-checkbox v-for="( data, label ) in daysObject" :key="label" :value="data.value" :label="label" :checked="data.checked" @add-day="addDay" @rmv-day="rmvDay"></button-checkbox>
								</div>
							</div>
						</div>
						<div class="columns" v-if="schedule.type === 'fixed'">
							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
								<b>Fixed Schedule Time</b><br/>
								<i>The time at witch to share for this account.</i>
							</div>
							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
								<div class="form-group">
									<vue-timepicker :minute-interval="5" class="timepicker-style-fix" :value="timeObject" @change="syncTime"></vue-timepicker>
								</div>
							</div>
						</div>
						<div class="columns" v-else>
							<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
								<b>Recurring Schedule Interval</b><br/>
								<i>A recurring interval to use for sharing. Once every 'X' hours.</i>
							</div>
							<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
								<div class="form-group">
									<input type="number" class="form-input" v-model="schedule.interval_r" placeholder="hours.min (Eg. 2.5)" />
								</div>
							</div>
						</div>



						<hr/>
					</div>
				</div>
			</div>
		</div>
		<div class="panel-footer">
			<button class="btn btn-primary" @click="saveSchedule()"><i class="fa fa-check"></i> Save Schedule</button>
			<button class="btn btn-secondary" @click="resetSchedule()"><i class="fa fa-ban"></i> Reset to Defaults</button>
		</div>
	</div>
</template>

<script>
	import ButtonCheckbox from './reusables/button-checkbox.vue'
	import VueTimepicker from 'vue2-timepicker'

	module.exports = {
		name: 'schedule-view',
		data: function () {
			let key = null
			if ( Object.keys( this.$store.state.activeAccounts )[0] !== undefined ) key = Object.keys( this.$store.state.activeAccounts )[0]
			return {
				selected_account: key,
				days: {
					'Mon': {
						'value': '1',
						'checked': false
					},
					'Tue': {
						'value': '2',
						'checked': false
					},
					'Wen': {
						'value': '3',
						'checked': false
					},
					'Thu': {
						'value': '4',
						'checked': false
					},
					'Fri': {
						'value': '5',
						'checked': false
					},
					'Sat': {
						'value': '6',
						'checked': false
					},
					'Sun': {
						'value': '7',
						'checked': false
					}
				}
			}
		},
		mounted: function () {
			// Uncomment this when not fixed tab on schedule
			// this.getAccountSchedule()
		},
		filters: {
			capitalize: function ( value ) {
				if ( !value ) return ''
				value = value.toString()
				return value.charAt( 0 ).toUpperCase() + value.slice( 1 )
			}
		},
		computed: {
			schedule: function () {
				return this.$store.state.activeSchedule
			},
			daysObject: function () {
				let daysObject = this.days
				for ( let day in daysObject ) {
					daysObject[day].checked = this.isChecked( daysObject[day].value )
				}
				console.log( daysObject )
				return daysObject
			},
			timeObject: function () {
				let currentTime = this.schedule.interval_f.time
				let timeParts = currentTime.split( ':' )
				return {
					'HH': timeParts[0],
					'mm': timeParts[1]
				}
			},
			active_accounts: function () {
				return this.$store.state.activeAccounts
			},
			icon: function () {
				let serviceIcon = 'fa-user'
				if ( this.selected_account !== null ) {
					serviceIcon = 'fa-'
					let account = this.active_accounts[this.selected_account]
					if ( account.service === 'facebook' ) serviceIcon = serviceIcon.concat( 'facebook-official' )
					if ( account.service === 'twitter' ) serviceIcon = serviceIcon.concat( 'twitter' )
					if ( account.service === 'linkedin' ) serviceIcon = serviceIcon.concat( 'linkedin' )
					if ( account.service === 'tumblr' ) serviceIcon = serviceIcon.concat( 'tumblr' )
				}
				return serviceIcon
			},
			img: function () {
				let img = ''
				if ( this.selected_account !== null && this.active_accounts[this.selected_account].img !== '' && this.active_accounts[this.selected_account].img !== undefined ) {
					img = this.active_accounts[this.selected_account].img
				}
				return img
			},
			service: function () {
				let serviceClass = ''
				if ( this.selected_account !== null && this.active_accounts[this.selected_account].service ) {
					serviceClass = this.active_accounts[this.selected_account].service
				}
				return serviceClass
			},
			service_name: function () {
				if ( this.service !== '' ) return this.service.charAt( 0 ).toUpperCase() + this.service.slice( 1 )
				return 'Service'
			},
			user_name: function () {
				if ( this.selected_account !== null && this.active_accounts[this.selected_account].user ) return this.active_accounts[this.selected_account].user
				return 'John Doe'
			}
		},
		watch: {
			active_accounts: function () {
				console.log( 'Accounts changed' )
				if ( Object.keys( this.$store.state.activeAccounts )[0] && this.selected_account === null ) {
					let key = Object.keys( this.$store.state.activeAccounts )[0]
					this.selected_account = key
					this.getAccountSchedule()
				}
			}
		},
		methods: {
			isChecked ( value ) {
				if ( this.schedule.interval_f !== undefined && this.schedule.interval_f.week_days.indexOf( value ) > -1 )	{
					return true
				}
				return false
			},
			syncTime ( dataEvent ) {
				this.schedule.interval_f.time = dataEvent.data.HH + ':' + dataEvent.data.mm
			},
			addDay ( value ) {
				console.log( 'Add day', value )
				this.schedule.interval_f.week_days.push( value )
			},
			rmvDay ( value ) {
				console.log( 'Rmv day', value )
				let index = this.schedule.interval_f.week_days.indexOf( value )
				if ( index > -1 )	{
					this.schedule.interval_f.week_days.splice( index, 1 )
				}
			},
			getAccountSchedule () {
				console.log( 'Get Schedule for', this.selected_account )
				this.$store.dispatch( 'fetchSchedule', { service: this.active_accounts[ this.selected_account ].service, account_id: this.selected_account } )
			},
			saveSchedule () {
				console.log( 'Save Schedule for', this.selected_account )
				this.$store.dispatch( 'saveSchedule', { service: this.active_accounts[ this.selected_account ].service, account_id: this.selected_account, schedule: this.schedule } )
			},
			resetSchedule () {
				console.log( 'Reset Schedule for', this.selected_account )
				this.$store.dispatch( 'resetSchedule', { service: this.active_accounts[ this.selected_account ].service, account_id: this.selected_account } )
				this.$forceUpdate()
			}
		},
		components: {
			ButtonCheckbox,
			VueTimepicker
		}
	}
</script>

<style scoped>
	#rop_core .avatar .avatar-icon {
		background: #333;
		border-radius: 50%;
		font-size: 16px;
		text-align: center;
		line-height: 20px;
	}
	#rop_core .avatar .avatar-icon.fa-facebook-official { background-color: #3b5998; }
	#rop_core .avatar .avatar-icon.fa-twitter { background-color: #55acee; }
	#rop_core .avatar .avatar-icon.fa-linkedin { background-color: #007bb5; }
	#rop_core .avatar .avatar-icon.fa-tumblr { background-color: #32506d; }

	#rop_core .service.facebook {
		color: #3b5998;
	}

	#rop_core .service.twitter {
		color: #55acee;
	}

	#rop_core .service.linkedin {
		color: #007bb5;
	}

	#rop_core .service.tumblr {
		color: #32506d;
	}
</style>
<style>
	#rop_core .time-picker.timepicker-style-fix .dropdown {
		top: 4px;
	}
	#rop_core .time-picker.timepicker-style-fix ul {
		margin: 0;
	}
	#rop_core .time-picker.timepicker-style-fix ul li {
		list-style: none;
	}

	#rop_core .time-picker.timepicker-style-fix .dropdown ul li.active,
	#rop_core .time-picker.timepicker-style-fix .dropdown ul li.active:hover {
		background: #e85407;
	}
</style>