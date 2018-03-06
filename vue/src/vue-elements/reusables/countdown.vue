<template>
	<div class="toast toast-success countdownS" v-if="to.isOn" >
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

<style>
    @keyframes move {
        0% {
            background-position: 0 0;
        }
        100% {
            background-position: 64px 64px;
        }
    }

    .countdownS {
        position: relative;
    }
    .countdownS:after {
        content: "";
        position: absolute;
        top: 0; left: 0; bottom: 0; right: 0;
        background-image: linear-gradient(
                -45deg,
                rgba(255, 255, 255, .1) 25%,
                transparent 25%,
                transparent 50%,
                rgba(255, 255, 255, .1) 50%,
                rgba(255, 255, 255, .1) 75%,
                transparent 75%,
                transparent
        );
        z-index: 1;
        background-size: 64px 64px;
        animation: move 2s linear infinite;
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
        border-top-left-radius: 20px;
        border-bottom-left-radius: 20px;
        overflow: hidden;

        animation: move 2s linear infinite;
    }
</style>