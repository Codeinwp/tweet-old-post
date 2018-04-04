<template>
	<div :class="'rop-control-container-'+ ( license>1 ) ">
		<div class="columns mt-0">
			<div class="column col-12 mt-0">
				<span class="divider"></span>
				<h4 class="label my-2">Custom Schedule</h4>
			</div>
		</div>

		<!-- Schedule Type -->
		<div class="columns text-right py-2 rop-control">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>Schedule Type</b>
				<p class="text-gray">What type of schedule to use.</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<select class="form-select" v-model="schedule.type">
						<option value="recurring">Recurring</option>
						<option value="fixed">Fixed</option>
					</select>
				</div>
			</div>
		</div>

		<!-- Fixed Schedule Days -->
		<div class="columns text-right py-2 rop-control" v-if="schedule.type === 'fixed'">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>Fixed Schedule Days</b>
				<p class="text-gray">The days when to share for this account.</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group input-group">
					<button-checkbox v-for="( data, label ) in daysObject" :key="label" :value="data.value"
					                 :label="label" :checked="data.checked" @add-day="addDay" @rmv-day="rmvDay"
					></button-checkbox>
				</div>
			</div>
		</div>

		<!-- Fixed Schedule time -->
		<div class="columns text-right py-2 rop-control" v-if="schedule.type === 'fixed'">
			<div class="column col-6 col-sm-12 vertical-align">
				<b>Fixed Schedule Time</b>
				<p class="text-gray">The time at witch to share for this account.</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<div class="input-group" v-for="( time, index ) in schedule.interval_f.time">
						<vue-timepicker :minute-interval="5" class="timepicker-style-fix" :value="getTime( index )"
						                @change="syncTime( $event, index )" hide-clear-button
						></vue-timepicker>
						<button class="btn btn-danger input-group-btn" v-if="schedule.interval_f.time.length > 1"
						        @click="rmvTime( index )">
							<i class="fa fa-fw fa-minus"></i>
						</button>
						<button class="btn btn-success input-group-btn"
						        v-if="index == schedule.interval_f.time.length - 1" @click="addTime()"
						>
							<i class="fa fa-fw fa-plus"></i>
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Recurring Schedule Interval -->
		<div class="columns text-right py-2 rop-control" v-else>
			<div class="column col-6 col-sm-12 vertical-align">
				<b>Recurring Schedule Interval</b>
				<p class="text-gray">A recurring interval to use for sharing. Once every 'X' hours.</p>
			</div>
			<div class="column col-6 col-sm-12 vertical-align">
				<div class="form-group">
					<input type="number" class="form-input" v-model="schedule.interval_r"
					       placeholder="hours.min (Eg. 2.5)"/>
				</div>
			</div>
		</div>

		<!-- Upsell -->
		<div class="columns py-2" v-if="license < 1">
			<div class="column text-center">
				<p class="upsell"><i class="fa fa-lock"></i> The Custom Schedule is available only in the Business version.</p>
			</div>
		</div>
		<span class="divider"></span>
	</div>
</template>

<script>
	import ButtonCheckbox from './reusables/button-checkbox.vue'
	import VueTimepicker from 'vue2-timepicker'

	module.exports = {
		name: 'account-schedule',
		props: ['account_id', 'license'],
		data: function () {
			return {
				days: {
					'Mon': {
						'value': '1',
						'checked': false
					},
					'Tue': {
						'value': '2',
						'checked': false
					},
					'Wed': {
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
		computed: {
			schedule: function () {
				return this.$store.state.activeSchedule[this.account_id] ? this.$store.state.activeSchedule[this.account_id] : [];
			},
			daysObject: function () {
				let daysObject = this.days
				for (let day in daysObject) {
					daysObject[day].checked = this.isChecked(daysObject[day].value)
				}
				return daysObject
			},
		},
		methods: {
			isChecked(value) {
				return (this.schedule.interval_f !== undefined && this.schedule.interval_f.week_days.indexOf(value) > -1);

			},
			getTime(index) {
				let currentTime = this.schedule.interval_f.time[index]
				let timeParts = currentTime.split(':')
				return {
					'HH': timeParts[0],
					'mm': timeParts[1]
				}
			},
			syncTime(dataEvent, index) {
				if (this.schedule.interval_f.time[index] !== undefined) {
					this.schedule.interval_f.time[index] = dataEvent.data.HH + ':' + dataEvent.data.mm
				}
			},
			addTime() {
				this.schedule.interval_f.time.push('00:00')
			},
			rmvTime(index) {
				this.schedule.interval_f.time.splice(index, 1)
			},
			addDay(value) {
				this.schedule.interval_f.week_days.push(value)
			},
			rmvDay(value) {
				let index = this.schedule.interval_f.week_days.indexOf(value)
				if (index > -1) {
					this.schedule.interval_f.week_days.splice(index, 1)
				}
			},
		},
		components: {
			ButtonCheckbox,
			VueTimepicker
		}
	}
</script>
<style scoped>
	#rop_core .panel-body .text-gray {
		margin: 0;
		line-height: normal;
	}
	b {
		margin-bottom :5px;
		display: block;
	}
	#rop_core .input-group .input-group-addon {
		padding: 3px 5px;
	}
	.time-picker {
		margin-bottom: 10px;
	}
	@media( max-width: 600px ) {
		#rop_core .panel-body .text-gray {
			margin-bottom: 10px;
		}
		#rop_core .text-right {
			text-align: left;
		}
	}
</style>