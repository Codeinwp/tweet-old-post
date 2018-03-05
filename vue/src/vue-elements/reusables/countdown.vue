<template>
	<div class="toast toast-success" v-if="to.isOn" >
		<b><i class="fa fa-fast-forward"></i> Next share</b> in <small v-if="days">{{ days | twoDigits }} days</small> <small v-if="hours">{{ hours | twoDigits }} hours</small> <small>{{ minutes | twoDigits }} minutes</small> <small>{{ seconds | twoDigits }} seconds</small>
	</div>
</template>

<script>
	module.exports = {
		name: 'cowntdown',
		props: {
			to: {
				default: function () { return { toTime: null, isOn: false } },
				type: Object
			}
		},
		mounted () {
			window.setInterval( () => {
				this.now = Math.trunc( ( new Date() ).getTime() / 1000 )
				this.updateQueueIfNeeded()
			}, 1000 )
		},
		data () {
			return {
				now: Math.trunc( ( new Date() ).getTime() / 1000 )
			}
		},
		computed: {
			date: function () {
				return Math.trunc( Date.parse( this.to.toTime ) / 1000 )
			},
			seconds () {
				return ( this.date - this.now ) % 60
			},

			minutes () {
				return Math.trunc( ( this.date - this.now ) / 60 ) % 60
			},

			hours () {
				return Math.trunc( ( this.date - this.now ) / 60 / 60 ) % 24
			},

			days () {
				return Math.trunc( ( this.date - this.now ) / 60 / 60 / 24 )
			}
		},
		methods: {
			updateQueueIfNeeded () {
				if ( this.now === this.date ) {
					this.$store.dispatch( 'fetchAJAX', { req: 'get_queue' } )
				}
			}
		},
		filters: {
			twoDigits: function ( value ) {
				if ( value.toString().length <= 1 ) {
					return '0' + value.toString()
				}
				return value.toString()
			}
		}
	}
</script>