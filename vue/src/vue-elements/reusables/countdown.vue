<template>
	<div class="toast toast-success rop-current-time" v-if="isOn && accounts_no > 0">
		<span v-if="diff_seconds>0"> <b><i class="fa fa-fast-forward"></i> Next share</b> in</span>
		<small v-if="timediff !== ''">{{timediff}}</small>
	</div>
</template>

<script>

	import moment from 'moment'
	import 'moment-duration-format';

	module.exports = {
		name: 'cowntdown',
		props: ['current_time'],
		data() {
			return {
				now: Math.trunc((new Date()).getTime() / 1000),
				timediff: '',
				diff_seconds: 0
			}
		},
		computed: {

			toTime: function () {
				return this.$store.state.cron_status.next_event_on
			},
			isOn: function () {
				return this.$store.state.cron_status.current_status
			},
			accounts_no: function () {
				return Object.keys(this.$store.state.activeAccounts).length
			},
		},
		watch: {
			/**
			 * Watcher to change coundown based on the current time.
			 *
			 * @param value
			 */
			current_time: function (value) {
				if (!this.isOn) {
					return;
				}
				let curent_moment = moment.utc(value, 'X');
				let next_event = moment.utc(this.toTime, 'X');
				let diff = moment.duration(next_event.diff(curent_moment));
				this.diff_seconds = diff.as("second")
				if (this.diff_seconds > 0) {
					this.timediff = diff.format("d [days], h [hours], m [minutes], s [seconds]");
				} else {
					this.$store.dispatch('fetchAJAX', {req: 'manage_cron'});
					this.timediff = 'Sharing .... ';
				}
			}
		}
	}
</script>
