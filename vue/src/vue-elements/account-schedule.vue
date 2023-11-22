<template>
  <div :class="'rop-control-container-'+ ( license > 1 && license !== 7 ) + '  rop-schedule-tab-container'">
    <div class="columns py-2 rop-control">
      <div class="column col-6 col-sm-12 vertical-align">
        <b>{{ labels.schedule_type_title }}</b>
        <p class="text-gray">
          {{ labels.schedule_type_desc }}
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align">
        <div class="form-group">
          <select
            v-model="schedule.type"
            class="form-select"
          >
            <option value="recurring">
              {{ labels.schedule_type_option_rec }}
            </option>
            <option value="fixed">
              {{ labels.schedule_type_option_fix }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Fixed Schedule Days -->
    <div
      v-if="schedule.type === 'fixed'"
      class="columns py-2 rop-control"
    >
      <div class="column col-6 col-sm-12 vertical-align">
        <b>{{ labels.schedule_fixed_days_title }}</b>
        <p class="text-gray">
          {{ labels.schedule_fixed_days_desc }}
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align">
        <div class="form-group input-group">
          <button-checkbox
            v-for="( data, label ) in daysObject"
            :key="label"
            :value="data.value"
            :label="label"
            :checked="data.checked"
            @add-day="addDay"
            @rmv-day="rmvDay"
          />
        </div>
      </div>
    </div>

    <!-- Fixed Schedule time -->
    <div
      v-if="schedule.type === 'fixed'"
      class="columns py-2 rop-control"
    >
      <div class="column col-6 col-sm-12 vertical-align">
        <b>{{ labels.schedule_fixed_time_title }}</b>
        <p class="text-gray">
          {{ labels.schedule_fixed_time_desc }}
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align">
        <div class="form-group">
          <div
            v-for="( time, index ) in schedule.interval_f.time"
            class="input-group"
          >
            <vue-timepicker
              :minute-interval="generalSettings.minute_interval"
              class="timepicker-style-fix"
              :value="getTime( index )"
              hide-clear-button
              @change="syncTime( $event, index )"
            />
            <button
              v-if="schedule.interval_f.time.length > 1"
              class="btn btn-danger input-group-btn"
              @click="rmvTime( index )"
            >
              <i class="fa fa-fw fa-minus" />
            </button>
            <button
              v-if="index == schedule.interval_f.time.length - 1"
              class="btn btn-success input-group-btn"
              @click="addTime()"
            >
              <i class="fa fa-fw fa-plus" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Current time -->
    <div
      v-if="schedule.type === 'fixed'"
      class="column col-6 col-sm-12 vertical-align float-right"
    >
      <div
        v-if="formatedDate"
        class="toast rop-current-time text-center"
      >
        {{ labels.time_now }}: {{ formatedDate }}
      </div>
    </div>

    <div
      v-else
      class="columns py-2 rop-control"
    >
      <div class="column col-6 col-sm-12 vertical-align">
        <b>{{ labels.schedule_rec_title }}</b>
        <p class="text-gray">
          {{ labels.schedule_rec_desc }}
        </p>
      </div>
      <div class="column col-6 col-sm-12 vertical-align">
        <div class="form-group">
          <counter-input
            id="interval_r"
            :value.sync="schedule.interval_r"
            :min-val="generalSettings.min_interval"
            :step-val="generalSettings.step_interval"
          />
        </div>
      </div>
    </div>

    <!-- Upsell -->
    <div
      v-if="license < 2 || license === 7"
      class="columns py-2"
    >
      <div class="column text-center">
        <p class="upsell">
          <i class="fa fa-lock" /> {{ labels.schedule_upsell }}
        </p>
      </div>
    </div>
    <span class="divider" />
  </div>
</template>

<script>
	import ButtonCheckbox from './reusables/button-checkbox.vue'
	import VueTimepicker from 'vue2-timepicker'
	import CounterInput from './reusables/counter-input.vue'
	import moment from 'moment'

	export default {
		name: 'AccountSchedule',
		components: {
			ButtonCheckbox,
			CounterInput,
			VueTimepicker
		},
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
				},
				labels: this.$store.state.labels.schedule,
				upsell_link: ropApiSettings.upsell_link,
			}
		},
		computed: {
    generalSettings: function () {
        return this.$store.state.generalSettings;
    },
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
			/**
			 * Get general settings.
			 * @returns {module.exports.computed.generalSettings|Array|*}
			 */
			formatedDate: function () {
					if (typeof this.date_format === 'undefined') {
							return '';
					}
					return moment.utc(this.current_time, 'X').format(this.date_format.replace('mm', 'mm:ss'));
			},
			current_time: {
					get: function () {
							return this.$store.state.cron_status.current_time;
					},
					set: function (value) {
							this.$store.state.cron_status.current_time = value;
					}
			},
			date_format: function () {

					return this.$store.state.cron_status.date_format;
			},
		},
    mounted: function () {
        this.$log.info('In General Settings state ');
        this.getGeneralSettings();
    },
		methods: {
    getGeneralSettings() {
        if (this.$store.state.generalSettings.length === 0) {
            this.is_loading = true;
            this.$log.info('Fetching general settings.');
            this.$store.dispatch('fetchAJAXPromise', {req: 'get_general_settings'}).then(response => {
                this.is_loading = false;
                this.$log.debug('Succesfully fetched.');
            }, error => {
                this.is_loading = false;
                this.$log.error('Can not fetch the general settings.')
            })
        }

    },
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
		}
	}
</script>
<style lang="scss" scoped>
	.rop-control-container-false  {
		cursor:not-allowed !important;
	}
	#rop_core {
    .panel-body .text-gray {
      margin: 0;
      line-height: normal;
    }

    .input-group .input-group-addon {
      padding: 3px 5px;
    }
  } 

	b {
		margin-bottom: 5px;
		display: block;
	}

	.time-picker {
		margin-bottom: 10px;
	}

	@media ( max-width: 600px ) {
		#rop_core {
      .panel-body .text-gray {
        margin-bottom: 10px;
      }

      .text-right {
        text-align: left;
      }
    } 
	}

</style>
