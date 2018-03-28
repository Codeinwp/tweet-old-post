<template>
	<div :class="'rop-control-container-'+ ( license>1 ) ">
		<div class="columns  rop-control">
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
		
		<div class="columns  rop-control" v-if="schedule.type === 'fixed'">
			<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
				<b>Fixed Schedule Days</b><br/>
				<i>The days when to share for this account.</i>
			</div>
			<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
				<div class="form-group">
					<button-checkbox v-for="( data, label ) in daysObject" :key="label" :value="data.value"
					                 :label="label" :checked="data.checked" @add-day="addDay" @rmv-day="rmvDay"
					></button-checkbox>
				</div>
			</div>
		</div>
		<div class="columns  rop-control" v-if="schedule.type === 'fixed'">
			<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
				<b>Fixed Schedule Time</b><br/>
				<i>The time at witch to share for this account.</i>
			</div>
			<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
				<div class="form-group">
					<div class="input-group" v-for="( time, index ) in schedule.interval_f.time">
						<vue-timepicker :minute-interval="5" class="timepicker-style-fix" :value="getTime( index )"
						                @change="syncTime( $event, index )" hide-clear-button
						></vue-timepicker>
						<button class="btn btn-success input-group-btn" v-if="schedule.interval_f.time.length > 1"
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
		<div class="columns rop-control" v-else>
			<div class="column col-sm-12 col-md-4 col-xl-3 col-ml-2 col-4 text-right">
				<b>Recurring Schedule Interval</b><br/>
				<i>A recurring interval to use for sharing. Once every 'X' hours.</i>
			</div>
			<div class="column col-sm-12 col-md-8 col-xl-9 col-mr-4 col-7 text-left">
				<div class="form-group">
					<input type="number" class="form-input" v-model="schedule.interval_r"
					       placeholder="hours.min (Eg. 2.5)"/>
				</div>
			</div>
		</div>
		<div class="columns rop-upsell-message" v-if=" license < 1 ">
			<div class="column col-12">
				<div class="columns">
					<div class="column col-12 text-center">
						<h5><i class="fa fa-lock "></i> Extend</h5>
						<p>The Custom Schedule is available only in the Business version.
						</p>
					</div>
				</div>
			</div>
		</div>
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