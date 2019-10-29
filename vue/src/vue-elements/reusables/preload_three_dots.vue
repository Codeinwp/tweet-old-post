<template>
    <div class="preloader-loading-item" v-show="loading">
        <div class="normal-dot normal-dot-odd" v-bind:style="dot_style"></div>
        <div class="normal-dot normal-dot-even" v-bind:style="dot_style"></div>
        <div class="normal-dot normal-dot-odd" v-bind:style="dot_style"></div>
    </div>
</template>

<script>
    export default {

        name: 'preload_three_dots',

        props: {
            loading: {
                type: Boolean,
                default: true
            },
            color: {
                type: String,
                default: '#5dc596'
            },
            size: {
                type: String,
                default: '15px'
            },
            margin: {
                type: String,
                default: '2px'
            },
            radius: {
                type: String,
                default: '100%'
            }
        },
        data() {
            return {
                dot_style: {
                    backgroundColor: this.color,
                    height: this.size,
                    width: this.size,
                    margin: this.margin,
                    borderRadius: this.radius,
                }
            }
        }

    }
</script>

<style scoped>

    .preloader-loading-item .normal-dot {
        -webkit-animation: dot-do-animation 0.7s infinite linear;
        animation: dot-do-animation 0.7s infinite linear;
        -webkit-animation-fill-mode: both;
        animation-fill-mode: both;
        display: inline-block;
    }

    .preloader-loading-item .normal-dot-odd {
        animation-delay: 0s;
    }

    .preloader-loading-item .normal-dot-even {
        animation-delay: 0.50s;
    }

    @-webkit-keyframes dot-do-animation {
        50% {
            -webkit-transform: scale(0.75);
            transform: scale(0.75);
            -webkit-opacity: 0.2;
            opacity: 0.2;
        }
        100% {
            -webkit-transform: scale(1);
            transform: scale(1);
            -webkit-opacity: 1;
            opacity: 1;
        }
    }

    @keyframes dot-do-animation {
        50% {
            -webkit-transform: scale(0.75);
            transform: scale(0.75);
            -webkit-opacity: 0.2;
            opacity: 0.2;
        }
        100% {
            -webkit-transform: scale(1);
            transform: scale(1);
            -webkit-opacity: 1;
            opacity: 1;
        }
    }
</style>