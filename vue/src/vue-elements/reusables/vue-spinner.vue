<template>
    <transition v-if="display_the_preloader">
        <div class="preloader-container">
            <div class="preloader-body text-center">
                <p class="empty-title h5" v-html="loading_message"></p>
                <component v-bind:is="spinner_style" :loading="loading" :color="color" :size="size" :margin="margin" :radius="radius"></component>
            </div>
        </div>
    </transition>
</template>

<script>

    import Loader_Style from './preload_three_dots.vue'

    const style_components = {
        'loader-style': Loader_Style
    }

    export default {
        name: "vue-spinner",
        mounted () {

        },
        props: {
            spinner_style: {
                type: String,
                default: 'loader-style'
            },
            loading: {
                type: Boolean,
                default: true
            },
            color: {
                type: String,
                default: '#429bf4'
            },
            size: {
                type: String,
                default: '30px'
            },
            margin: {
                type: String,
                default: '2px'
            },
            radius: {
                type: String,
                default: '100%'
            },
            preloader_message: {
                type: String,
                default: 'Loading...'
            }
        },
        data () {
            return {
                display_the_preloader: false,
                loading_message: this.preloader_message
            }
        },
        components: style_components,
        methods: {
            show: function () {
                this.display_the_preloader = true
            },
            hide: function () {
                this.display_the_preloader = false
            }
        }
    }
</script>

<style scoped>
    .preloader-container {
        position: relative;
        z-index: 9998;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        margin: auto;
        width: 100%;
        height: 218px;
        background-color: rgba(255, 255, 255, 0.1);
        display: block;
    }

    .preloader-body {
        width: 350px;
        height: 80px;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        margin: auto;
        text-align: center;
        display: block;
    }
</style>